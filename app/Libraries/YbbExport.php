<?php

namespace App\Libraries;

use Config\Services;

/**
 * YBB Export Library for CodeIgniter 4
 * 
 * Provides seamless integration with the YBB Export API service
 * for handling large-scale data exports.
 * 
 * Based on YBB API CodeIgniter Integration Documentation
 */
class YbbExport 
{
    private $config;
    private string $apiUrl;
    
    public function __construct($config = [])
    {
        // Load configuration using CodeIgniter 4 method
        $this->config = \Config\Services::config('YbbExport');
        
        // Fallback if config service fails
        if (!$this->config) {
            $this->config = new \Config\YbbExport();
        }
        
        // Override with passed config
        foreach ($config as $key => $value) {
            if (property_exists($this->config, $key)) {
                $this->config->$key = $value;
            }
        }
        
        $this->apiUrl = rtrim($this->config->apiUrl, '/');
        
        // Validate API URL
        if (empty($this->config->apiUrl)) {
            throw new \RuntimeException('YBB Export API URL is not configured. Please set YBB_EXPORT_API_URL environment variable or configure apiUrl in YbbExport config.');
        }
        
        // Ensure temp storage directory exists
        $this->_ensureTempDirectory();
        
        if ($this->config->enableDebugLogging) {
            log_message('info', 'YBB Export Library initialized with API URL: ' . $this->apiUrl);
        }
    }
    
    /**
     * Export participants directly from database using filters (DB-Direct Mode)
     * This is the ONLY recommended approach as per YBB_DB_EXPORT_API_INTEGRATION_GUIDE.md
     * 
     * @param array $filters Database filters (program_id, status, country, has_paid, has_submitted_form, etc.)
     * @param array $options Export options (template, format, filename, sheet_name, include_related)
     * @return array API response with export_id and download information
     */
    public function exportParticipantsFromDB(array $filters, array $options = []): array
    {
        if (empty($filters['program_id'])) {
            return [
                'success' => false,
                'message' => 'program_id is required for database export'
            ];
        }

        // Prepare payload according to YBB DB Export API documentation
        $payload = [
            'filters' => $filters,
            'options' => [
                'template' => $options['template'] ?? 'standard',
                'format' => $options['format'] ?? 'excel',
                'filename' => $options['filename'] ?? null,
                'sheet_name' => $options['sheet_name'] ?? null,
                'include_related' => $options['include_related'] ?? true
            ]
        ];

        // Remove null values from options
        $payload['options'] = array_filter($payload['options'], function($value) {
            return $value !== null;
        });

        // According to documentation: base URL should be without /api/ybb/db
        // The library appends the full path
        $url = $this->apiUrl . "/api/ybb/db/export/participants";

        if ($this->config->enableDebugLogging) {
            log_message('info', "YBB DB Export: Creating participants export with filters: " . json_encode($filters));
        }

        $result = $this->_makeRequest('POST', $url, $payload);

        // Transform API response to match expected format
        if ($result['success']) {
            $apiData = $result['data'];
            
            // Handle YBB DB API response format from documentation
            if (isset($apiData['status']) && $apiData['status'] === 'success') {
                // Build download URL according to documentation: /api/ybb/export/{id}/download (no /db/)
                $exportId = $apiData['data']['export_id'];
                $downloadUrl = $apiData['data']['download_url'] ?? "/api/ybb/export/{$exportId}/download";
                
                $responseData = [
                    'export_id' => $exportId,
                    'download_url' => $downloadUrl,
                    'record_count' => $apiData['data']['record_count'] ?? 0,
                    'created_at' => $apiData['data']['generated_at'] ?? null,
                    'expires_at' => $apiData['data']['expires_at'] ?? null,
                    'file_name' => $apiData['data']['file_name'] ?? null,
                    'file_size' => $apiData['data']['file_size'] ?? null,
                    'file_size_mb' => $apiData['data']['file_size_mb'] ?? null
                ];

                // Add metadata for tracking
                if (isset($apiData['metadata'])) {
                    $responseData['metadata'] = $apiData['metadata'];
                }

                if ($this->config->enableDebugLogging) {
                    log_message('info', "YBB DB Export: Export completed successfully. Export ID: " . $responseData['export_id']);
                }

                return [
                    'success' => true,
                    'data' => $responseData,
                    'metadata' => $apiData['metadata'] ?? []
                ];
            }
        }

        // Handle error cases according to API documentation
        if (!$result['success']) {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Export request failed',
                'error_code' => $result['error_code'] ?? 'UNKNOWN_ERROR',
                'details' => $result['details'] ?? [],
                'request_id' => $result['request_id'] ?? null
            ];
        }

        return [
            'success' => false,
            'message' => 'Unexpected API response format',
            'error_code' => 'RESPONSE_FORMAT_ERROR'
        ];
    }
    
    /**
     * Export payments directly from database using filters (DB-Direct Mode)
     * This is the recommended approach as per YBB_DB_EXPORT_API_INTEGRATION_GUIDE.md
     * 
     * @param array $filters Database filters (program_id, status, payment_method_id, etc.)
     * @param array $options Export options (template, format, filename, sheet_name, include_related)
     * @return array API response with export_id and download information
     */
    public function exportPaymentsFromDB(array $filters, array $options = []): array
    {
        if (empty($filters['program_id'])) {
            return [
                'success' => false,
                'message' => 'program_id is required for database export'
            ];
        }

        // Prepare payload according to YBB DB Export API documentation
        $payload = [
            'filters' => $filters,
            'options' => [
                'template' => $options['template'] ?? 'standard',
                'format' => $options['format'] ?? 'excel',
                'filename' => $options['filename'] ?? null,
                'sheet_name' => $options['sheet_name'] ?? null,
                'include_related' => $options['include_related'] ?? true
            ]
        ];

        // Remove null values from options
        $payload['options'] = array_filter($payload['options'], function($value) {
            return $value !== null;
        });

        // According to documentation: base URL should be without /api/ybb/db
        // The library appends the full path
        $url = $this->apiUrl . "/api/ybb/db/export/payments";

        if ($this->config->enableDebugLogging) {
            log_message('info', "YBB DB Export: Creating payments export with filters: " . json_encode($filters));
        }

        $result = $this->_makeRequest('POST', $url, $payload);

        // Transform API response to match expected format
        if ($result['success']) {
            // The API returns data directly in result['data']
            $apiData = $result['data'] ?? $result;
            
            // Handle YBB DB API response format from documentation
            // Response format: {status: "success", data: {export_id, file_name, ...}, ...}
            if (isset($apiData['status']) && $apiData['status'] === 'success' && isset($apiData['data'])) {
                // Nested format: {status: "success", data: {export_id, ...}}
                $exportData = $apiData['data'];
                $exportId = $exportData['export_id'];
                $downloadUrl = $exportData['download_url'] ?? "/api/ybb/export/{$exportId}/download";
                
                $responseData = [
                    'export_id' => $exportId,
                    'download_url' => $downloadUrl,
                    'record_count' => $exportData['record_count'] ?? 0,
                    'created_at' => $exportData['generated_at'] ?? ($apiData['system_info']['generated_at'] ?? null),
                    'expires_at' => $exportData['expires_at'] ?? null,
                    'file_name' => $exportData['file_name'] ?? null,
                    'file_size' => $exportData['file_size'] ?? null,
                    'file_size_mb' => $exportData['file_size_mb'] ?? null
                ];

                // Add metadata for tracking
                $responseData['metadata'] = [
                    'export_strategy' => $apiData['export_strategy'] ?? 'single_file',
                    'performance_metrics' => $apiData['performance_metrics'] ?? [],
                    'system_info' => $apiData['system_info'] ?? []
                ];

                if ($this->config->enableDebugLogging) {
                    log_message('info', "YBB DB Export: Payments export completed successfully. Export ID: " . $responseData['export_id']);
                }

                return [
                    'success' => true,
                    'data' => $responseData,
                    'metadata' => $responseData['metadata']
                ];
            } elseif (isset($apiData['export_id'])) {
                // Direct format: {export_id, file_name, record_count, ...} at top level
                $exportId = $apiData['export_id'];
                $downloadUrl = $apiData['download_url'] ?? "/api/ybb/export/{$exportId}/download";
                
                $responseData = [
                    'export_id' => $exportId,
                    'download_url' => $downloadUrl,
                    'record_count' => $apiData['record_count'] ?? 0,
                    'created_at' => $apiData['generated_at'] ?? null,
                    'expires_at' => $apiData['expires_at'] ?? null,
                    'file_name' => $apiData['file_name'] ?? null,
                    'file_size' => $apiData['file_size'] ?? null,
                    'file_size_mb' => $apiData['file_size_mb'] ?? null
                ];

                // Add metadata for tracking
                $responseData['metadata'] = [
                    'export_strategy' => $apiData['export_strategy'] ?? 'single_file',
                    'performance_metrics' => $apiData['performance_metrics'] ?? [],
                    'system_info' => $apiData['system_info'] ?? []
                ];

                if ($this->config->enableDebugLogging) {
                    log_message('info', "YBB DB Export: Payments export completed successfully. Export ID: " . $responseData['export_id']);
                }

                return [
                    'success' => true,
                    'data' => $responseData,
                    'metadata' => $responseData['metadata']
                ];
            }
        }

        // Handle error cases according to API documentation
        if (!$result['success']) {
            log_message('error', "YBB DB Export: Payments export failed. Response: " . json_encode($result));
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Export request failed',
                'error_code' => $result['error_code'] ?? 'UNKNOWN_ERROR',
                'details' => $result['details'] ?? [],
                'request_id' => $result['request_id'] ?? null
            ];
        }

        // Log unexpected response format for debugging
        log_message('error', "YBB DB Export: Unexpected API response format for payments export. Response: " . json_encode($result));
        return [
            'success' => false,
            'message' => 'Unexpected API response format. Check logs for details.',
            'error_code' => 'RESPONSE_FORMAT_ERROR',
            'debug_data' => $result
        ];
    }
    
    /**
     * Get export statistics before creating export (as per documentation)
     * 
     * @param string $exportType Type of export ('participants' or 'payments')
     * @param array $filters Database filters to preview
     * @return array API response with statistics
     */
    public function getExportStatistics(string $exportType, array $filters): array
    {
        $payload = [
            'export_type' => $exportType,
            'filters' => $filters
        ];

        $url = $this->apiUrl . "/api/ybb/db/export/statistics";

        if ($this->config->enableDebugLogging) {
            log_message('info', "YBB DB Export: Getting statistics for {$exportType} with filters: " . json_encode($filters));
        }

        $result = $this->_makeRequest('POST', $url, $payload);

        if ($result['success'] && isset($result['data']['status']) && $result['data']['status'] === 'success') {
            return [
                'success' => true,
                'data' => $result['data']['data'] ?? [],
                'total_count' => $result['data']['data']['total_count'] ?? 0,
                'status_breakdown' => $result['data']['data']['status_breakdown'] ?? []
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'] ?? 'Failed to retrieve statistics'
        ];
    }

    /**
     * Get export status with improved error handling
     */
    public function getExportStatus(string $exportId): array
    {
        $url = $this->apiUrl . "/api/ybb/export/{$exportId}/status";
        
        $result = $this->_makeRequest('GET', $url);
        
        // Handle intermittent 404 errors by providing a more graceful response
        if (!$result['success'] && isset($result['http_code']) && $result['http_code'] === 404) {
            // Check if this might be a temporary issue by waiting and retrying once
            sleep(1);
            $retryResult = $this->_makeRequest('GET', $url);
            
            if ($retryResult['success']) {
                return $retryResult;
            }
            
            // If still 404, return a more informative response
            return [
                'success' => false,
                'message' => 'Export not found. The export may have expired or completed processing.',
                'http_code' => 404,
                'suggested_action' => 'check_completed_exports',
                'export_id' => $exportId
            ];
        }
        
        return $result;
    }
    
    /**
     * Download export file
     */
    public function downloadExport(string $exportId, ?string $savePath = null): array
    {
        $url = $this->apiUrl . "/api/ybb/export/{$exportId}/download";
        
        return $this->_downloadFile($url, $savePath);
    }
    
    /**
     * Download ZIP archive (for large exports)
     */
    public function downloadExportZip(string $exportId, ?string $savePath = null): array
    {
        $url = $this->apiUrl . "/api/ybb/export/{$exportId}/download/zip";
        
        return $this->_downloadFile($url, $savePath);
    }
    
    /**
     * Download specific batch file
     */
    public function downloadBatchFile(string $exportId, int $batchNumber, ?string $savePath = null): array
    {
        $url = $this->apiUrl . "/api/ybb/export/{$exportId}/download/batch/{$batchNumber}";
        
        return $this->_downloadFile($url, $savePath);
    }
    
    /**
     * Get available templates
     */
    public function getTemplates(?string $exportType = null): array
    {
        if ($exportType) {
            $url = $this->apiUrl . "/api/ybb/templates/{$exportType}";
        } else {
            $url = $this->apiUrl . "/api/ybb/templates";
        }
        
        return $this->_makeRequest('GET', $url);
    }
    
    /**
     * Get status mappings
     */
    public function getStatusMappings(): array
    {
        $url = $this->apiUrl . "/api/ybb/status-mappings";
        
        return $this->_makeRequest('GET', $url);
    }
    
    /**
     * Test API connectivity and health
     */
    public function testConnection(): array
    {
        $url = $this->apiUrl . "/health";
        
        $result = $this->_makeRequest('GET', $url);
        
        if ($result['success'] && isset($result['data']['status'])) {
            return [
                'success' => true,
                'message' => 'API connection successful',
                'service' => $result['data']['service'] ?? 'YBB Export API',
                'version' => $result['data']['version'] ?? 'Unknown',
                'status' => $result['data']['status'],
                'timestamp' => $result['data']['timestamp'] ?? date('c')
            ];
        } else {
            return [
                'success' => false,
                'message' => 'API connection failed: ' . ($result['message'] ?? 'Unknown error'),
                'error_details' => $result
            ];
        }
    }
    
    /**
     * Test API connectivity and health
     */
    /**
     * Make HTTP request to API with retry logic
     */
    private function _makeRequest(string $method, string $url, ?array $data = null): array
    {
        $attempts = 0;
        $maxAttempts = $this->config->retryAttempts;
        
        while ($attempts < $maxAttempts) {
            $attempts++;
            
            $curl = curl_init();
            
            $curlOptions = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->config->timeout,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true
            ];
            
            if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
                // Force garbage collection before processing large datasets
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
                
                // Memory-safe JSON encoding for large datasets
                $estimatedSize = $this->_estimateJsonSize($data);
                $memoryUsage = memory_get_usage(true);
                $memoryLimit = $this->_getMemoryLimitBytes();
                $availableMemory = $memoryLimit - $memoryUsage;
                
                if ($this->config->enableDebugLogging) {
                    log_message('debug', "YBB Export: Estimated JSON size: " . round($estimatedSize / 1024 / 1024, 2) . "MB, Available memory: " . round($availableMemory / 1024 / 1024, 2) . "MB, Current usage: " . round($memoryUsage / 1024 / 1024, 2) . "MB");
                }
                
                // Be more conservative - if estimated size would use more than 30% of available memory, use safe encoding
                if ($estimatedSize > ($availableMemory * 0.3)) {
                    log_message('warning', "YBB Export: Large payload detected. Using memory-safe JSON encoding.");
                    
                    // Try to encode with memory monitoring
                    $jsonData = $this->_safeJsonEncode($data);
                    if ($jsonData === false) {
                        return [
                            'success' => false,
                            'message' => 'Failed to encode data - dataset too large for available memory. Consider reducing the dataset size or enabling chunking.',
                            'error_code' => 'MEMORY_EXHAUSTION',
                            'memory_info' => [
                                'estimated_size_mb' => round($estimatedSize / 1024 / 1024, 2),
                                'available_memory_mb' => round($availableMemory / 1024 / 1024, 2),
                                'current_usage_mb' => round($memoryUsage / 1024 / 1024, 2)
                            ]
                        ];
                    }
                    $curlOptions[CURLOPT_POSTFIELDS] = $jsonData;
                } else {
                    $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
                }
            }
            
            curl_setopt_array($curl, $curlOptions);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            
            curl_close($curl);
            
            // Check for cURL errors
            if ($response === false || !empty($error)) {
                if ($this->config->enableDebugLogging) {
                    log_message('error', "YBB Export API request failed (attempt {$attempts}): " . $error);
                }
                
                // Retry on network errors
                if ($attempts < $maxAttempts) {
                    sleep($this->config->retryDelay);
                    continue;
                }
                
                return [
                    'success' => false,
                    'message' => 'API request failed: ' . $error,
                    'http_code' => 0
                ];
            }
            
            $decodedResponse = json_decode($response, true);
            
            // Success response
            if ($httpCode >= 200 && $httpCode < 300) {
                if ($this->config->enableDebugLogging) {
                    log_message('info', "YBB Export API request successful: {$method} {$url}");
                }
                
                return [
                    'success' => true,
                    'data' => $decodedResponse,
                    'http_code' => $httpCode
                ];
            }
            
            // Handle retryable errors (5xx server errors)
            if ($httpCode >= 500 && $attempts < $maxAttempts) {
                if ($this->config->enableDebugLogging) {
                    log_message('warning', "YBB Export API server error (attempt {$attempts}), retrying: HTTP {$httpCode}");
                }
                sleep($this->config->retryDelay);
                continue;
            }
            
            // Non-retryable error
            $errorMessage = isset($decodedResponse['message']) 
                ? $decodedResponse['message'] 
                : 'Unknown API error';
                
            if ($this->config->enableDebugLogging) {
                log_message('error', "YBB Export API error (HTTP {$httpCode}): " . $errorMessage);
            }
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'http_code' => $httpCode,
                'raw_response' => $decodedResponse
            ];
        }
        
        // Should not reach here, but handle edge case
        return [
            'success' => false,
            'message' => 'Maximum retry attempts exceeded'
        ];
    }
    
    /**
     * Download file from API with enhanced error handling and retry logic
     */
    private function _downloadFile(string $url, ?string $savePath = null): array
    {
        if (!$savePath) {
            $savePath = $this->config->tempStoragePath . 'export_' . time() . '_' . rand(1000, 9999) . '.xlsx';
        }
        
        // Ensure directory exists
        $dir = dirname($savePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return [
                    'success' => false,
                    'message' => 'Could not create directory for download: ' . $dir
                ];
            }
        }
        
        $maxAttempts = 3;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $attempt++;
            
            $curl = curl_init();
            $fileHandle = fopen($savePath, 'w+');
            
            if (!$fileHandle) {
                return [
                    'success' => false,
                    'message' => 'Could not create file for download: ' . $savePath
                ];
            }
            
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_TIMEOUT => $this->config->downloadTimeout,
                CURLOPT_FILE => $fileHandle,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/octet-stream, */*'
                ],
                CURLOPT_USERAGENT => 'YBB-Export-Client/1.0'
            ]);
            
            $result = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
            
            curl_close($curl);
            fclose($fileHandle);
            
            // Check for success
            if ($result !== false && empty($error) && $httpCode >= 200 && $httpCode < 300) {
                $fileSize = filesize($savePath);
                
                // Validate file was actually downloaded
                if ($fileSize > 0) {
                    if ($this->config->enableDebugLogging) {
                        log_message('info', "File downloaded successfully: {$url} -> {$savePath} ({$fileSize} bytes, Content-Type: {$contentType})");
                    }
                    
                    return [
                        'success' => true,
                        'file_path' => $savePath,
                        'file_size' => $fileSize,
                        'content_type' => $contentType,
                        'message' => 'File downloaded successfully'
                    ];
                } else {
                    if ($this->config->enableDebugLogging) {
                        log_message('warning', "Downloaded file is empty: {$savePath}");
                    }
                }
            }
            
            // Log the error details
            if ($this->config->enableDebugLogging) {
                log_message('error', "Download attempt {$attempt}/{$maxAttempts} failed: URL={$url}, HTTP={$httpCode}, Error={$error}, ContentType={$contentType}");
            }
            
            // Clean up failed download
            if (file_exists($savePath)) {
                unlink($savePath);
            }
            
            // Check if we should retry
            if ($attempt < $maxAttempts && ($httpCode >= 500 || $httpCode === 0 || !empty($error))) {
                // Server error or network error - retry after delay
                sleep($this->config->retryDelay);
                continue;
            }
            
            // Final failure
            break;
        }
        
        // All attempts failed
        return [
            'success' => false,
            'message' => "Download failed after {$maxAttempts} attempts. Last error: " . ($error ?: "HTTP {$httpCode}"),
            'http_code' => $httpCode,
            'attempts' => $attempt
        ];
    }
    
    /**
     * Ensure temp directory exists
     */
    private function _ensureTempDirectory(): void
    {
        $tempDir = $this->config->tempStoragePath;
        
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0755, true)) {
                log_message('error', 'Could not create YBB export temp directory: ' . $tempDir);
            }
        }
    }
    
    /**
     * Get configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Make HTTP request - public method for external use
     */
    public function makeRequest(string $method, string $url, ?array $data = null): array
    {
        return $this->_makeRequest($method, $url, $data);
    }

    /**
     * Clean up old temporary files
     */
    public function cleanupTempFiles(): int
    {
        $tempDir = $this->config->tempStoragePath;
        $cleanupThreshold = time() - ($this->config->cleanupAfterHours * 3600);
        $cleanedCount = 0;
        
        if (is_dir($tempDir)) {
            $files = glob($tempDir . '*');
            
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cleanupThreshold) {
                    if (unlink($file)) {
                        $cleanedCount++;
                    }
                }
            }
        }
        
        log_message('info', "YBB Export: Cleaned up {$cleanedCount} temporary files");
        
        return $cleanedCount;
    }

    /**
     * Estimate JSON size without actually encoding (to avoid memory issues)
     */
    private function _estimateJsonSize($data): int
    {
        if (is_array($data)) {
            // Rough estimation: 
            // - Each array element adds ~50 bytes overhead
            // - Each string character is ~1.2 bytes in JSON (accounting for escaping)
            // - Each number is ~8 bytes
            // - Boolean is ~5 bytes
            
            $count = count($data);
            $sampleSize = min(10, $count); // Sample first 10 items for estimation
            $sampleItems = array_slice($data, 0, $sampleSize);
            
            $sampleJsonSize = strlen(json_encode($sampleItems));
            
            // Estimate total size with some overhead buffer
            $estimatedSize = ($sampleJsonSize / $sampleSize) * $count * 1.3; // 30% buffer
            
            return (int) $estimatedSize;
        }
        
        // For non-arrays, just use serialize length as approximation
        return strlen(serialize($data)) * 1.5;
    }

    /**
     * Get memory limit in bytes
     */
    private function _getMemoryLimitBytes(): int
    {
        $memoryLimit = ini_get('memory_limit');
        
        if ($memoryLimit === '-1') {
            return PHP_INT_MAX; // No limit
        }
        
        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) substr($memoryLimit, 0, -1);
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return (int) $memoryLimit;
        }
    }

    /**
     * Memory-safe JSON encoding with error handling
     */
    private function _safeJsonEncode($data)
    {
        // Monitor memory before encoding
        $memoryBefore = memory_get_usage(true);
        $memoryLimit = $this->_getMemoryLimitBytes();
        $availableMemory = $memoryLimit - $memoryBefore;
        
        // If we're already using more than 70% of memory, abort
        if (($memoryBefore / $memoryLimit) > 0.7) {
            log_message('error', "YBB Export: Memory usage too high (" . round(($memoryBefore / $memoryLimit) * 100, 1) . "%) to safely encode JSON");
            return false;
        }
        
        // Clear any unnecessary memory before encoding
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
        
        // Try encoding with error handling and memory monitoring
        set_error_handler(function($severity, $message, $file, $line) {
            if (strpos($message, 'memory') !== false) {
                throw new \Exception("Memory error during JSON encoding: " . $message);
            }
        });
        
        try {
            $result = json_encode($data, JSON_UNESCAPED_UNICODE);
            
            if ($result === false) {
                $error = json_last_error_msg();
                log_message('error', "YBB Export: JSON encoding failed: {$error}");
                return false;
            }
            
            $memoryAfter = memory_get_usage(true);
            $memoryUsed = $memoryAfter - $memoryBefore;
            
            if ($this->config->enableDebugLogging) {
                log_message('debug', "YBB Export: JSON encoding used " . round($memoryUsed / 1024 / 1024, 2) . "MB additional memory. Total usage: " . round($memoryAfter / 1024 / 1024, 2) . "MB");
            }
            
            // Check if we're approaching memory limits after encoding
            if (($memoryAfter / $memoryLimit) > 0.8) {
                log_message('warning', "YBB Export: High memory usage after JSON encoding (" . round(($memoryAfter / $memoryLimit) * 100, 1) . "%)");
            }
            
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', "YBB Export: Exception during JSON encoding: " . $e->getMessage());
            return false;
        } finally {
            restore_error_handler();
        }
    }
}
