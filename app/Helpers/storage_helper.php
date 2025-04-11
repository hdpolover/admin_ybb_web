<?php

use Config\Storage as StorageConfig;

if (!function_exists('upload_file_to_storage')) {
    /**
     * Upload a file to external storage domain
     *
     * @param array $file The uploaded file data from $_FILES
     * @param string $destination The destination folder in storage (e.g., '/profile-pictures')
     * @param string|null $filename Optional custom filename, if null, will use a generated unique name
     * @param array $allowedTypes Array of allowed mime types
     * @return array|string Result containing success status and file path or error message
     */
    function upload_file_to_storage(array $file, string $destination, ?string $filename = null, array $allowedTypes = [])
    {
        $storageConfig = new StorageConfig();
        $response = ['status' => false, 'message' => '', 'path' => '', 'url' => ''];

        // Check if file was uploaded properly
        if (!isset($file['tmp_name']) || empty($file['tmp_name']) || $file['error'] !== 0) {
            $response['message'] = 'No file was uploaded or upload error occurred.';
            return $response;
        }

        // Validate file type if specified
        if (!empty($allowedTypes) && !in_array($file['type'], $allowedTypes)) {
            $response['message'] = 'File type not allowed. Accepted types: ' . implode(', ', $allowedTypes);
            return $response;
        }

        // Validate file size
        $maxSizeBytes = $storageConfig->maxFileSize * 1024 * 1024; // Convert MB to bytes
        if ($file['size'] > $maxSizeBytes) {
            $response['message'] = 'File size exceeds the maximum allowed size of ' . $storageConfig->maxFileSize . 'MB.';
            return $response;
        }

        // Generate unique filename if not provided
        if ($filename === null) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = md5(uniqid(rand(), true)) . '.' . $extension;
        }        // Create full destination path - directly use the destination without basePath
        $destinationPath = trim($destination, '/');
        $fullPath = $destinationPath . '/' . $filename;

        // Upload file using appropriate method
        if ($storageConfig->useFtp) {
            $result = upload_via_ftp($file['tmp_name'], $fullPath, $storageConfig);
        } else {
            $result = upload_via_http($file['tmp_name'], $fullPath, $storageConfig);
        }

        if (!$result['status']) {
            return $result;
        }

        // Return success response with file path and URL
        $response['status'] = true;
        $response['message'] = 'File uploaded successfully';
        $response['path'] = $fullPath;
        $response['url'] = $storageConfig->getFileUrl($fullPath);

        return $response;
    }
}

if (!function_exists('upload_via_http')) {
    /**
     * Upload file to storage server via HTTP POST request
     *
     * @param string $filePath The temporary file path
     * @param string $destination The destination path on storage server
     * @param StorageConfig $config Storage configuration
     * @return array Result containing success status and message
     */
    function upload_via_http(string $filePath, string $destination, StorageConfig $config)
    {
        $result = ['status' => false, 'message' => ''];

        try {
            // Initialize cURL session
            $ch = curl_init();

            // Get the filename from path
            $filename = basename($destination);

            // API endpoint for file upload
            $uploadUrl = $config->storageUrl . '/api/upload';

            // Set cURL options
            curl_setopt_array($ch, [
                CURLOPT_URL => $uploadUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'X-API-Key: ' . $config->apiKey,
                    'Accept: application/json',
                ],
                CURLOPT_POSTFIELDS => [
                    'file' => new CURLFile($filePath, mime_content_type($filePath), $filename),
                    'path' => dirname($destination),
                    'filename' => $filename
                ]
            ]);

            // Execute cURL request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Check for errors
            if (curl_errno($ch)) {
                $result['message'] = 'cURL Error: ' . curl_error($ch);
                curl_close($ch);
                return $result;
            }

            curl_close($ch);

            // Parse response
            $responseData = json_decode($response, true);

            // Check response status
            if ($httpCode !== 200 || !isset($responseData['success']) || !$responseData['success']) {
                $errorMsg = isset($responseData['message']) ? $responseData['message'] : 'Unknown error occurred';
                $result['message'] = 'Upload failed: ' . $errorMsg;
                return $result;
            }

            $result['status'] = true;
            $result['message'] = 'File uploaded successfully';

            return $result;
        } catch (\Exception $e) {
            $result['message'] = 'Exception: ' . $e->getMessage();
            return $result;
        }
    }
}

if (!function_exists('upload_via_ftp')) {
    /**
     * Upload file to storage server via FTP
     *
     * @param string $filePath The temporary file path
     * @param string $destination The destination path on storage server
     * @param StorageConfig $config Storage configuration
     * @return array Result containing success status and message
     */
    function upload_via_ftp(string $filePath, string $destination, StorageConfig $config)
    {
        $result = ['status' => false, 'message' => ''];

        try {
            // Check if FTP extension is available
            if (!extension_loaded('ftp')) {
                $result['message'] = 'FTP extension is required for FTP uploads';
                return $result;
            }

            // Connect to FTP server
            $conn = ftp_connect($config->ftpHost, $config->ftpPort);
            if (!$conn) {
                $result['message'] = 'Failed to connect to FTP server';
                return $result;
            }

            // Login with username and password
            if (!@ftp_login($conn, $config->ftpUsername, $config->ftpPassword)) {
                $result['message'] = 'FTP authentication failed';
                ftp_close($conn);
                return $result;
            }

            // Set passive mode if configured
            if ($config->ftpPassive) {
                ftp_pasv($conn, true);
            }
            // Create destination directories if they don't exist
            $dirs = explode('/', trim(dirname($destination), '/'));
            $path = rtrim($config->ftpRootPath, '/');

            // Try to create each directory in the path
            foreach ($dirs as $dir) {
                if (empty($dir)) continue;

                // Add directory to path
                $path .= '/' . $dir;

                // First try to change to the directory to see if it exists
                if (@ftp_chdir($conn, $path)) {
                    // Directory exists, continue to the next one
                    continue;
                }

                // Directory doesn't exist, go back to parent
                @ftp_chdir($conn, dirname($path));

                // Try to create directory
                if (!@ftp_mkdir($conn, $path)) {
                    $result['message'] = "Failed to create directory: {$path}";
                    ftp_close($conn);
                    return $result;
                }
            }

            // Upload file
            $remotePath = $config->ftpRootPath . '/' . ltrim($destination, '/');

            if (!@ftp_put($conn, $remotePath, $filePath, FTP_BINARY)) {
                $result['message'] = 'Failed to upload file via FTP';
                ftp_close($conn);
                return $result;
            }

            // Close connection
            ftp_close($conn);

            $result['status'] = true;
            $result['message'] = 'File uploaded successfully via FTP';

            return $result;
        } catch (\Exception $e) {
            if (isset($conn) && $conn) {
                ftp_close($conn);
            }
            $result['message'] = 'FTP Exception: ' . $e->getMessage();
            return $result;
        }
    }
}

if (!function_exists('upload_profile_picture')) {
    /**
     * Upload a profile picture to external storage
     *
     * @param array $file The uploaded file data from $_FILES
     * @param int $participantId User ID to use in filename
     * @param int $programId Program ID to use in filename
     * @return array Result containing success status, message, and file URL
     */
    function upload_profile_picture(array $file, int $participantId, int $programId)
    {
        $storageConfig = new StorageConfig();

        // Generate user-specific filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = "user_{$participantId}_" . time() . '.' . $extension;

        // Upload file
        return upload_file_to_storage(
            $file,
            "participants/{$programId}/{$participantId}/",
            $filename,
            $storageConfig->allowedProfilePictureTypes
        );
    
    }
}

if (!function_exists('delete_storage_file')) {
    /**
     * Delete a file from external storage
     *
     * @param string $filePath Path of the file to delete
     * @return array Result containing success status and message
     */
    function delete_storage_file(string $filePath)
    {
        $storageConfig = new StorageConfig();
        $result = ['status' => false, 'message' => ''];

        try {
            if ($storageConfig->useFtp) {
                // Delete via FTP
                if (!extension_loaded('ftp')) {
                    $result['message'] = 'FTP extension is required for FTP operations';
                    return $result;
                }

                // Connect to FTP server
                $conn = ftp_connect($storageConfig->ftpHost, $storageConfig->ftpPort);
                if (!$conn) {
                    $result['message'] = 'Failed to connect to FTP server';
                    return $result;
                }

                // Login with username and password
                if (!@ftp_login($conn, $storageConfig->ftpUsername, $storageConfig->ftpPassword)) {
                    $result['message'] = 'FTP authentication failed';
                    ftp_close($conn);
                    return $result;
                }

                // Set passive mode if configured
                if ($storageConfig->ftpPassive) {
                    ftp_pasv($conn, true);
                }

                // Delete file
                $remotePath = $storageConfig->ftpRootPath . '/' . ltrim($filePath, '/');
                if (!@ftp_delete($conn, $remotePath)) {
                    $result['message'] = 'Failed to delete file via FTP';
                    ftp_close($conn);
                    return $result;
                }

                ftp_close($conn);

                $result['status'] = true;
                $result['message'] = 'File deleted successfully via FTP';
            } else {
                // Delete via HTTP request
                $ch = curl_init();
                $deleteUrl = $storageConfig->storageUrl . '/api/delete';

                curl_setopt_array($ch, [
                    CURLOPT_URL => $deleteUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => 'DELETE',
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTPHEADER => [
                        'X-API-Key: ' . $storageConfig->apiKey,
                        'Accept: application/json',
                        'Content-Type: application/json'
                    ],
                    CURLOPT_POSTFIELDS => json_encode(['path' => $filePath])
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if (curl_errno($ch)) {
                    $result['message'] = 'cURL Error: ' . curl_error($ch);
                    curl_close($ch);
                    return $result;
                }

                curl_close($ch);

                $responseData = json_decode($response, true);

                if ($httpCode !== 200 || !isset($responseData['success']) || !$responseData['success']) {
                    $errorMsg = isset($responseData['message']) ? $responseData['message'] : 'Unknown error occurred';
                    $result['message'] = 'Deletion failed: ' . $errorMsg;
                    return $result;
                }

                $result['status'] = true;
                $result['message'] = 'File deleted successfully';
            }

            return $result;
        } catch (\Exception $e) {
            if (isset($conn) && $conn) {
                ftp_close($conn);
            }
            $result['message'] = 'Exception: ' . $e->getMessage();
            return $result;
        }
    }
}
