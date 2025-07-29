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
     * Export participants data
     */
    public function exportParticipants(array $data, array $options = []): array
    {
        return $this->_createExport('participants', $data, $options);
    }
    
    /**
     * Export payments data
     */
    public function exportPayments(array $data, array $options = []): array
    {
        return $this->_createExport('payments', $data, $options);
    }
    
    /**
     * Export ambassadors data
     */
    public function exportAmbassadors(array $data, array $options = []): array
    {
        return $this->_createExport('ambassadors', $data, $options);
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
     * Estimate export size and processing time
     */
    public function estimateExport(array $data, array $options = []): array
    {
        $payload = array_merge([
            'data_count' => count($data),
            'template' => 'standard',
            'format' => 'excel'
        ], $options);
        
        $url = $this->apiUrl . "/api/ybb/export/estimate";
        
        return $this->_makeRequest('POST', $url, $payload);
    }
    
    /**
     * Create export request according to YBB API specification
     */
    private function _createExport(string $exportType, array $data, array $options = []): array
    {
        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'No data provided for export'
            ];
        }
        
        if (count($data) > $this->config->maxRecords) {
            return [
                'success' => false,
                'message' => "Data exceeds maximum limit of {$this->config->maxRecords} records"
            ];
        }
        
        // Prepare payload according to API specification
        $payload = [
            'data' => $data,
            'template' => $options['template'] ?? 'standard',
            'format' => $options['format'] ?? 'excel'
        ];
        
        // Add optional parameters
        if (isset($options['filename'])) {
            $payload['filename'] = $options['filename'];
        }
        
        if (isset($options['sheet_name'])) {
            $payload['sheet_name'] = $options['sheet_name'];
        }
        
        $url = $this->apiUrl . "/api/ybb/export/{$exportType}";
        
        if ($this->config->enableDebugLogging) {
            log_message('info', "YBB Export: Creating {$exportType} export with " . count($data) . " records");
        }
        
        $result = $this->_makeRequest('POST', $url, $payload);
        
        // Transform API response to match expected format
        if ($result['success']) {
            $apiData = $result['data'];
            
            // Handle direct success response (new API format)
            if (isset($apiData['status']) && $apiData['status'] === 'success') {
                return [
                    'success' => true,
                    'data' => [
                        'export_id' => $apiData['data']['export_id'],
                        'file_name' => $apiData['data']['file_name'] ?? null,
                        'file_size' => $apiData['data']['file_size'] ?? null,
                        'record_count' => $apiData['data']['record_count'] ?? count($data),
                        'download_url' => $apiData['data']['download_url'] ?? null,
                        'expires_at' => $apiData['data']['expires_at'] ?? null,
                        'export_strategy' => $apiData['export_strategy'] ?? 'single_file',
                        'estimated_time' => $apiData['metadata']['processing_time'] ?? null,
                        'total_files' => $apiData['data']['total_files'] ?? 1,
                        'individual_files' => $apiData['data']['individual_files'] ?? null,
                        'archive' => $apiData['data']['archive'] ?? null
                    ],
                    'metadata' => $apiData['metadata'] ?? []
                ];
            }
            
            // Handle enhanced API response format with filename support
            if (isset($apiData['export_id'])) {
                return [
                    'success' => true,
                    'data' => [
                        'export_id' => $apiData['export_id'],
                        'file_name' => $apiData['file_name'] ?? null,
                        'file_size' => $apiData['file_size'] ?? null,
                        'record_count' => $apiData['record_count'] ?? count($data),
                        'download_url' => $apiData['download_url'] ?? null,
                        'expires_at' => $apiData['expires_at'] ?? null,
                        'export_strategy' => $apiData['export_strategy'] ?? 'single_file',
                        'estimated_time' => $result['metadata']['processing_time'] ?? null,
                        'total_files' => $apiData['total_files'] ?? 1,
                        'individual_files' => $apiData['individual_files'] ?? null,
                        'archive' => $apiData['archive'] ?? null
                    ],
                    'metadata' => $result['metadata'] ?? []
                ];
            }
        }
        
        return $result;
    }
    
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
                $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
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
}
