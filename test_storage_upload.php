<?php

// Include CodeIgniter bootstrap to access the same autoloader
require_once 'app/Config/Paths.php';
require_once 'system/bootstrap.php';

// Import required classes
use Config\Storage as StorageConfig;

// Define simple test function
function test_ftp_connection() {
    $config = new StorageConfig();
    
    echo "=== FTP Connection Test ===\n";
    echo "Host: {$config->ftpHost}\n";
    echo "Username: {$config->ftpUsername}\n";
    echo "Port: {$config->ftpPort}\n";
    echo "Passive mode: " . ($config->ftpPassive ? "Enabled" : "Disabled") . "\n";
    echo "Root path: {$config->ftpRootPath}\n\n";
    
    // Try to connect
    echo "Attempting to connect to FTP server...\n";
    $conn = @ftp_connect($config->ftpHost, $config->ftpPort);
    
    if (!$conn) {
        echo "FAILED: Could not connect to FTP server.\n";
        return false;
    }
    
    echo "SUCCESS: Connected to FTP server.\n";
    
    // Try to login
    echo "Attempting to login with provided credentials...\n";
    $login = @ftp_login($conn, $config->ftpUsername, $config->ftpPassword);
    
    if (!$login) {
        echo "FAILED: Could not login with the provided credentials.\n";
        ftp_close($conn);
        return false;
    }
    
    echo "SUCCESS: Logged in successfully.\n";
    
    // Set passive mode if configured
    if ($config->ftpPassive) {
        echo "Setting passive mode...\n";
        ftp_pasv($conn, true);
    }
    
    // Try to list the root directory
    echo "Listing contents of root directory...\n";
    $list = @ftp_nlist($conn, $config->ftpRootPath);
    
    if ($list === false) {
        echo "FAILED: Could not list the root directory.\n";
        ftp_close($conn);
        return false;
    }
    
    echo "SUCCESS: Listed " . count($list) . " items in root directory.\n";
    
    // Display the first 10 items
    $count = 0;
    foreach ($list as $item) {
        echo "  - " . basename($item) . "\n";
        $count++;
        if ($count >= 10) {
            echo "  ... and more (showing first 10 items only)\n";
            break;
        }
    }
    
    // Close the connection
    ftp_close($conn);
    
    return true;
}

function test_file_upload() {
    $config = new StorageConfig();
    $testFileName = 'test_file_' . time() . '.txt';
    $testFilePath = sys_get_temp_dir() . '/' . $testFileName;
    $testFileContent = "This is a test file created at " . date('Y-m-d H:i:s') . "\n";
    $testDestination = '/test/' . $testFileName;
    
    echo "\n=== File Upload Test ===\n";
    echo "Creating test file: {$testFilePath}\n";
    
    // Create a test file
    if (file_put_contents($testFilePath, $testFileContent) === false) {
        echo "FAILED: Could not create test file.\n";
        return false;
    }
    
    echo "Test file created successfully.\n";
    
    // Connect to FTP server
    echo "Connecting to FTP server...\n";
    $conn = @ftp_connect($config->ftpHost, $config->ftpPort);
    
    if (!$conn) {
        echo "FAILED: Could not connect to FTP server.\n";
        return false;
    }
    
    // Login to FTP server
    echo "Logging in...\n";
    $login = @ftp_login($conn, $config->ftpUsername, $config->ftpPassword);
    
    if (!$login) {
        echo "FAILED: Could not login to FTP server.\n";
        ftp_close($conn);
        return false;
    }
    
    // Set passive mode if configured
    if ($config->ftpPassive) {
        echo "Setting passive mode...\n";
        ftp_pasv($conn, true);
    }
    
    // Try to create test directory if it doesn't exist
    echo "Creating test directory if it doesn't exist...\n";
    $testDir = $config->ftpRootPath . '/test';
    
    $current_dirs = @ftp_nlist($conn, $config->ftpRootPath);
    
    $testDirExists = false;
    if ($current_dirs) {
        foreach ($current_dirs as $dir) {
            if (basename($dir) == 'test') {
                $testDirExists = true;
                break;
            }
        }
    }
    
    if (!$testDirExists) {
        echo "Test directory does not exist, creating it...\n";
        if (!@ftp_mkdir($conn, $testDir)) {
            echo "FAILED: Could not create test directory.\n";
            ftp_close($conn);
            return false;
        }
        echo "Test directory created successfully.\n";
    } else {
        echo "Test directory already exists.\n";
    }
    
    // Upload test file
    echo "Uploading test file...\n";
    $remotePath = $config->ftpRootPath . $testDestination;
    
    if (!@ftp_put($conn, $remotePath, $testFilePath, FTP_ASCII)) {
        echo "FAILED: Could not upload test file.\n";
        ftp_close($conn);
        return false;
    }
    
    echo "SUCCESS: Test file uploaded successfully.\n";
    
    // Verify file exists
    echo "Verifying file exists on server...\n";
    $testDirContents = @ftp_nlist($conn, $testDir);
    
    $fileExists = false;
    if ($testDirContents) {
        foreach ($testDirContents as $file) {
            if (basename($file) == $testFileName) {
                $fileExists = true;
                break;
            }
        }
    }
    
    if ($fileExists) {
        echo "SUCCESS: File found on server.\n";
    } else {
        echo "FAILED: File not found on server after upload.\n";
    }
    
    // Clean up - remove the test file
    echo "Cleaning up test file...\n";
    if (@ftp_delete($conn, $remotePath)) {
        echo "Test file removed from server.\n";
    } else {
        echo "WARNING: Could not remove test file from server.\n";
    }
    
    // Close connection
    ftp_close($conn);
    
    // Remove local test file
    if (unlink($testFilePath)) {
        echo "Local test file removed.\n";
    } else {
        echo "WARNING: Could not remove local test file.\n";
    }
    
    return $fileExists;
}

function test_storage_helper() {
    echo "\n=== Storage Helper Test ===\n";
    
    // Check if the helper file exists
    if (!file_exists('app/Helpers/storage_helper.php')) {
        echo "FAILED: storage_helper.php not found.\n";
        return false;
    }
    
    // Include helper
    require_once 'app/Helpers/storage_helper.php';
    
    // Check if the upload_file_to_storage function exists
    if (!function_exists('upload_file_to_storage')) {
        echo "FAILED: upload_file_to_storage function not found in helper.\n";
        return false;
    }
    
    echo "Helper functions found and loaded.\n";
    
    // Create test file
    $testFileName = 'helper_test_' . time() . '.txt';
    $testFilePath = sys_get_temp_dir() . '/' . $testFileName;
    $testFileContent = "This is a test file for the storage helper created at " . date('Y-m-d H:i:s') . "\n";
    
    echo "Creating test file for helper test: {$testFilePath}\n";
    
    if (file_put_contents($testFilePath, $testFileContent) === false) {
        echo "FAILED: Could not create test file for helper test.\n";
        return false;
    }
    
    // Create mock file data like $_FILES
    $fileData = [
        'name' => $testFileName,
        'type' => 'text/plain',
        'tmp_name' => $testFilePath,
        'error' => 0,
        'size' => filesize($testFilePath)
    ];
    
    echo "Calling upload_file_to_storage()...\n";
    $result = upload_file_to_storage(
        $fileData, 
        'test',
        $testFileName,
        ['text/plain']
    );
    
    echo "Upload result: " . print_r($result, true) . "\n";
    
    // Clean up local test file
    if (unlink($testFilePath)) {
        echo "Local test file removed.\n";
    } else {
        echo "WARNING: Could not remove local test file.\n";
    }
    
    return $result['status'] === true;
}

// Run tests
echo "Starting storage system tests...\n";
echo "-------------------------------\n\n";

echo "Testing FTP connection...\n";
$connectionResult = test_ftp_connection();

if ($connectionResult) {
    echo "\nTesting file upload...\n";
    $uploadResult = test_file_upload();
    
    echo "\nTesting storage helper functions...\n";
    $helperResult = test_storage_helper();
} else {
    echo "\nSkipping file upload test due to connection failure.\n";
    echo "\nSkipping helper test due to connection failure.\n";
}

echo "\n-------------------------------\n";
echo "Test Results:\n";
echo "- FTP Connection: " . ($connectionResult ? "PASSED" : "FAILED") . "\n";

if ($connectionResult) {
    echo "- File Upload: " . ($uploadResult ? "PASSED" : "FAILED") . "\n";
    echo "- Storage Helper: " . ($helperResult ? "PASSED" : "FAILED") . "\n";
    
    if ($uploadResult && $helperResult) {
        echo "\nAll tests PASSED! File upload functionality is working correctly.\n";
    } else {
        echo "\nSome tests FAILED. Please check the output above for details.\n";
    }
} else {
    echo "Remaining tests were skipped due to connection failure.\n";
}
