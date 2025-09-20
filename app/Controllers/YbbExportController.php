<?php

namespace App\Controllers;

use App\Libraries\YbbExport;
use App\Models\ParticipantModel;
use App\Models\PaymentModel;
use App\Models\AmbassadorModel;
use App\Helpers\ExportFilenameHelper;

/**
 * YBB Export Controller
 * 
 * Handles data exports using the YBB Export API (Python Flask service)
 */
class YbbExportController extends AdminBaseController
{
    private YbbExport $ybbExport;
    private ParticipantModel $participantModel;
    private PaymentModel $paymentModel;
    private AmbassadorModel $ambassadorModel;

    public function __construct()
    {
        $this->ybbExport = new YbbExport();
        $this->participantModel = new ParticipantModel();
        $this->paymentModel = new PaymentModel();
        $this->ambassadorModel = new AmbassadorModel();
    }

    /**
     * Export dashboard - Enhanced version with real-time polling
     */
    public function index()
    {
        return view('admin/exports/enhanced_dashboard', [
            'title' => 'Enhanced Data Export Dashboard'
        ]);
    }

    /**
     * Export participants data
     */
    public function exportParticipants()
    {
        try {
            // Get filters from request
            $filters = $this->_getFiltersFromRequest();
            
            // Get participants data
            $participants = $this->_getParticipantsData($filters);
            
            if (empty($participants)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No participants found for export'
                ]);
            }

            // Get export options with descriptive filename
            $options = $this->_getExportOptions('participants', $filters);
            
            // Log export request for tracking
            $exportRequestId = $this->_logExportRequest($filters['program_id'], 'participants', $options);
            
            // Get participant count for logging
            $participantCount = count($participants);
            
            log_message('info', "Participant export requested: $participantCount records found");
            
            // Check if dataset exceeds API limits (50,000 records)
            if ($participantCount > 50000) {
                log_message('warning', "Dataset too large ($participantCount records). API limit is 50,000 records.");
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Dataset too large ($participantCount records). Maximum allowed is 50,000 records. Please apply additional filters to reduce the dataset size."
                ]);
            }
            
            // Get chunk threshold from config (default: 5000)
            $chunkThreshold = 5000; // Using the configured chunk threshold
            
            // Determine export strategy based on dataset size and template
            if ($participantCount > $chunkThreshold) {
                log_message('info', "Large dataset detected ($participantCount records). Using chunked export strategy.");
                
                // Use 'complete' template for large datasets to ensure chunking at 5k threshold
                $options['template'] = 'complete';
                $options['force_chunking'] = true;
                $options['chunk_size'] = $chunkThreshold;
                $options['total_records'] = $participantCount;
                
                log_message('info', "Chunked export: $participantCount records, template=complete, chunk_size=$chunkThreshold");
                
                $result = $this->ybbExport->exportParticipants($participants, $options);
            } else {
                // Use standard template for smaller datasets (single file)
                log_message('info', "Processing all $participantCount records as single file");
                $options['template'] = 'standard';
                $options['total_records'] = $participantCount;
                
                $result = $this->ybbExport->exportParticipants($participants, $options);
            }
            
            if ($result['success']) {
                // Extract performance metrics for logging and response
                $performanceMetrics = $result['data']['performance_metrics'] ?? [];
                $isChunked = isset($result['data']['export_strategy']) && $result['data']['export_strategy'] === 'chunked';
                
                // Log detailed performance statistics
                $this->_logPerformanceMetrics($performanceMetrics, $participantCount, $isChunked);
                
                // Update log with success details
                $this->_updateExportRequestLog($exportRequestId, [
                    'status' => 'success',
                    'export_id' => $result['data']['export_id'],
                    'file_name' => $result['data']['file_name'] ?? null,
                    'record_count' => $participantCount,
                    'file_size' => $result['data']['file_size'] ?? null,
                    'processing_time' => $performanceMetrics['total_processing_time_seconds'] ?? null,
                    'expires_at' => $result['data']['expires_at'] ?? null,
                    'export_strategy' => $result['data']['export_strategy'] ?? 'single_file',
                    'performance_data' => json_encode($performanceMetrics)
                ]);
                
                // Determine response message with performance info
                $message = $this->_buildExportMessage($result['data'], $participantCount, $performanceMetrics);
                
                log_message('info', $message);
                
                $response = [
                    'success' => true,
                    'exportId' => $result['data']['export_id'],
                    'fileName' => $result['data']['file_name'] ?? null,
                    'downloadUrl' => $result['data']['download_url'] ?? null,
                    'message' => $message,
                    'recordCount' => $participantCount,
                    'createdAt' => $result['data']['created_at'] ?? null,
                    'status' => $result['data']['download_url'] ? 'completed' : 'processing'
                ];
                
                // Add performance statistics to response
                if (!empty($performanceMetrics)) {
                    $response['performanceStats'] = $this->_formatPerformanceStats($performanceMetrics, $isChunked);
                }
                
                // Add chunked export specific fields
                if ($isChunked) {
                    $response['fileType'] = 'chunked';
                    $response['exportStrategy'] = 'chunked';
                    $response['totalFiles'] = $result['data']['total_files'] ?? 1;
                    $response['chunkCount'] = $result['data']['chunk_count'] ?? $result['data']['total_files'];
                    $response['individualFiles'] = $result['data']['individual_files'] ?? null;
                    $response['archive'] = $result['data']['archive'] ?? null;
                    
                    if (isset($result['data']['archive'])) {
                        $response['compressedSize'] = $result['data']['archive']['compressed_size'] ?? null;
                        $response['compressionRatio'] = $result['data']['archive']['compression_ratio'] ?? null;
                    }
                } else {
                    $response['fileType'] = 'single';
                    $response['exportStrategy'] = 'single_file';
                    $response['fileSize'] = $result['data']['file_size'] ?? null;
                    $response['fileSizeMB'] = $result['data']['file_size_mb'] ?? null;
                    $response['totalFiles'] = 1;
                }
                
                // Legacy compatibility fields
                $response['data'] = [
                    'export_id' => $result['data']['export_id'],
                    'download_url' => $result['data']['download_url'] ?? null,
                    'export_strategy' => $response['exportStrategy'],
                    'total_files' => $response['totalFiles']
                ];
                
                return $this->response->setJSON($response);
            } else {
                // Update log with error details
                $this->_updateExportRequestLog($exportRequestId, [
                    'status' => 'error',
                    'error_message' => $result['message']
                ]);
                
                // Enhanced error logging for debugging
                log_message('error', 'Participants export failed: ' . $result['message']);
                log_message('error', 'Export context: ' . json_encode([
                    'participant_count' => count($participants),
                    'program_id' => $filters['program_id'] ?? 'unknown',
                    'payload_size' => strlen(json_encode($participants)),
                    'export_options' => $options
                ]));
                
                // Log sample data for debugging (first record only, sanitized)
                if (!empty($participants)) {
                    $sampleRecord = $participants[0];
                    $sampleSanitized = [];
                    foreach ($sampleRecord as $key => $value) {
                        if (is_string($value) && strlen($value) > 100) {
                            $sampleSanitized[$key] = substr($value, 0, 97) . '...';
                        } else {
                            $sampleSanitized[$key] = $value;
                        }
                    }
                    log_message('debug', 'Sample participant record: ' . json_encode($sampleSanitized));
                }
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in exportParticipants: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred during export: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export payments data with enhanced performance tracking and intelligent chunking
     */
    public function exportPayments()
    {
        try {
            $startTime = microtime(true);
            
            // Get filters from request
            $filters = $this->_getFiltersFromRequest();
            
            // Performance tracking: Enable if requested
            $trackPerformance = $this->request->getPost('track_performance') === 'true' || 
                               $this->request->getGet('track_performance') === 'true';
            
            // Get payments data with performance monitoring
            $dataStartTime = microtime(true);
            $payments = $this->_getPaymentsData($filters);
            $dataFetchTime = microtime(true) - $dataStartTime;
            
            if (empty($payments)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No payments found for export'
                ]);
            }

            $recordCount = count($payments);
            log_message('info', "Payments export initiated for {$recordCount} records with performance tracking: " . ($trackPerformance ? 'enabled' : 'disabled'));

            // Intelligent export strategy selection
            $exportStrategy = $this->_determineExportStrategy($recordCount, 'payments');
            
            // Get enhanced export options with chunking support
            $options = $this->_getExportOptions('payments', $filters);
            $options['export_strategy'] = $exportStrategy;
            $options['chunk_size'] = $this->_getOptimalChunkSize($recordCount, 'payments');
            $options['track_performance'] = $trackPerformance;
            
            // Add performance context to options
            if ($trackPerformance) {
                $options['performance_context'] = [
                    'data_fetch_time' => $dataFetchTime,
                    'record_count' => $recordCount,
                    'memory_before_export' => memory_get_usage(true)
                ];
            }
            
            // Log export request for tracking with enhanced metadata
            $options['export_strategy'] = $exportStrategy; // Add strategy to options for logging
            $exportRequestId = $this->_logExportRequest($filters['program_id'], 'payments', $options);
            
            // Create export using YBB Export API with performance tracking
            $exportStartTime = microtime(true);
            $result = $this->ybbExport->exportPayments($payments, $options);
            $exportTime = microtime(true) - $exportStartTime;
            
            if ($result['success']) {
                $totalTime = microtime(true) - $startTime;
                
                // Enhanced logging with comprehensive metadata
                $logData = [
                    'status' => 'success',
                    'export_id' => $result['exportId'],
                    'record_count' => $recordCount,
                    'export_strategy' => $exportStrategy,
                    'file_type' => $result['fileType'] ?? 'unknown',
                    'chunk_count' => $result['chunkCount'] ?? 1,
                    'total_files' => $result['totalFiles'] ?? 1
                ];

                // Add performance data if tracking is enabled
                if ($trackPerformance && isset($result['performanceStats'])) {
                    $logData['performance_data'] = json_encode($result['performanceStats']);
                }

                $this->_updateExportRequestLog($exportRequestId, $logData);

                // Log performance metrics
                if ($trackPerformance) {
                    $performanceMetrics = [
                        'record_count' => $recordCount,
                        'data_fetch_time' => $dataFetchTime,
                        'export_time' => $exportTime,
                        'total_time' => $totalTime,
                        'export_strategy' => $exportStrategy,
                        'memory_usage' => memory_get_peak_usage(true),
                        'total_processing_time_seconds' => $totalTime,
                        'records_per_second' => $recordCount / max($totalTime, 0.001)
                    ];
                    
                    $this->_logPerformanceMetrics($performanceMetrics, $recordCount, $exportStrategy === 'chunked');
                }
                
                log_message('info', "Payments export completed successfully: {$recordCount} records, strategy: {$exportStrategy}, time: {$totalTime}s");

                // Build user-friendly message - use the correct parameters
                $message = $this->_buildExportMessage($result, $recordCount, $result['performanceStats'] ?? []);
                
                return $this->response->setJSON([
                    'success' => true,
                    'exportId' => $result['exportId'],
                    'message' => $message,
                    'recordCount' => $recordCount,
                    'exportStrategy' => $exportStrategy,
                    'fileType' => $result['fileType'],
                    'totalFiles' => $result['totalFiles'],
                    'chunkCount' => $result['chunkCount'] ?? null,
                    'compressedSize' => $result['compressedSize'] ?? null,
                    'compressionRatio' => $result['compressionRatio'] ?? null,
                    'status' => 'completed',
                    'performanceStats' => $result['performanceStats'] ?? null,
                    'downloadUrl' => $result['downloadUrl'] ?? null
                ]);
            } else {
                // Enhanced error logging
                $this->_updateExportRequestLog($exportRequestId, [
                    'status' => 'error',
                    'error_message' => $result['message'],
                    'record_count' => $recordCount,
                    'export_strategy' => $exportStrategy,
                    'processing_time' => microtime(true) - $startTime
                ]);
                
                log_message('error', "Payments export failed: {$result['message']} (Strategy: {$exportStrategy}, Records: {$recordCount})");
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'],
                    'recordCount' => $recordCount,
                    'exportStrategy' => $exportStrategy
                ]);
            }
            
        } catch (\Exception $e) {
            $processingTime = microtime(true) - ($startTime ?? microtime(true));
            
            log_message('error', "Exception in exportPayments: {$e->getMessage()} (Processing time: {$processingTime}s)");
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred during export: ' . $e->getMessage(),
                'processingTime' => $processingTime
            ]);
        }
    }

    /**
     * Export ambassadors data with enhanced performance tracking and intelligent chunking
     */
    public function exportAmbassadors()
    {
        try {
            $startTime = microtime(true);
            
            // Get filters from request
            $filters = $this->_getFiltersFromRequest();
            
            // Performance tracking: Enable if requested
            $trackPerformance = $this->request->getPost('track_performance') === 'true' || 
                               $this->request->getGet('track_performance') === 'true';
            
            // Get ambassadors data with performance monitoring
            $dataStartTime = microtime(true);
            $ambassadors = $this->_getAmbassadorsData($filters);
            $dataFetchTime = microtime(true) - $dataStartTime;
            
            if (empty($ambassadors)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No ambassadors found for export'
                ]);
            }

            $recordCount = count($ambassadors);
            log_message('info', "Ambassadors export initiated for {$recordCount} records with performance tracking: " . ($trackPerformance ? 'enabled' : 'disabled'));

            // Intelligent export strategy selection (ambassadors can handle larger datasets)
            $exportStrategy = $this->_determineExportStrategy($recordCount, 'ambassadors');
            
            // Get enhanced export options with chunking support
            $options = $this->_getExportOptions('ambassadors', $filters);
            $options['export_strategy'] = $exportStrategy;
            $options['chunk_size'] = $this->_getOptimalChunkSize($recordCount, 'ambassadors');
            $options['track_performance'] = $trackPerformance;
            
            // Add performance context to options
            if ($trackPerformance) {
                $options['performance_context'] = [
                    'data_fetch_time' => $dataFetchTime,
                    'record_count' => $recordCount,
                    'memory_before_export' => memory_get_usage(true)
                ];
            }
            
            // Log export request for tracking with enhanced metadata
            $options['export_strategy'] = $exportStrategy; // Add strategy to options for logging
            $exportRequestId = $this->_logExportRequest($filters['program_id'], 'ambassadors', $options);
            
            // Create export using YBB Export API with performance tracking
            $exportStartTime = microtime(true);
            $result = $this->ybbExport->exportAmbassadors($ambassadors, $options);
            $exportTime = microtime(true) - $exportStartTime;
            
            if ($result['success']) {
                $totalTime = microtime(true) - $startTime;
                
                // Enhanced logging with comprehensive metadata
                $logData = [
                    'status' => 'success',
                    'export_id' => $result['exportId'],
                    'record_count' => $recordCount,
                    'export_strategy' => $exportStrategy,
                    'file_type' => $result['fileType'] ?? 'unknown',
                    'chunk_count' => $result['chunkCount'] ?? 1,
                    'total_files' => $result['totalFiles'] ?? 1
                ];

                // Add performance data if tracking is enabled
                if ($trackPerformance && isset($result['performanceStats'])) {
                    $logData['performance_data'] = json_encode($result['performanceStats']);
                }

                $this->_updateExportRequestLog($exportRequestId, $logData);

                // Log performance metrics
                if ($trackPerformance) {
                    $performanceMetrics = [
                        'record_count' => $recordCount,
                        'data_fetch_time' => $dataFetchTime,
                        'export_time' => $exportTime,
                        'total_time' => $totalTime,
                        'export_strategy' => $exportStrategy,
                        'memory_usage' => memory_get_peak_usage(true),
                        'total_processing_time_seconds' => $totalTime,
                        'records_per_second' => $recordCount / max($totalTime, 0.001)
                    ];
                    
                    $this->_logPerformanceMetrics($performanceMetrics, $recordCount, $exportStrategy === 'chunked');
                }
                
                log_message('info', "Ambassadors export completed successfully: {$recordCount} records, strategy: {$exportStrategy}, time: {$totalTime}s");

                // Build user-friendly message - use the correct parameters
                $message = $this->_buildExportMessage($result, $recordCount, $result['performanceStats'] ?? []);
                
                return $this->response->setJSON([
                    'success' => true,
                    'exportId' => $result['exportId'],
                    'message' => $message,
                    'recordCount' => $recordCount,
                    'exportStrategy' => $exportStrategy,
                    'fileType' => $result['fileType'],
                    'totalFiles' => $result['totalFiles'],
                    'chunkCount' => $result['chunkCount'] ?? null,
                    'compressedSize' => $result['compressedSize'] ?? null,
                    'compressionRatio' => $result['compressionRatio'] ?? null,
                    'status' => 'completed',
                    'performanceStats' => $result['performanceStats'] ?? null,
                    'downloadUrl' => $result['downloadUrl'] ?? null
                ]);
            } else {
                // Enhanced error logging
                $this->_updateExportRequestLog($exportRequestId, [
                    'status' => 'error',
                    'error_message' => $result['message'],
                    'record_count' => $recordCount,
                    'export_strategy' => $exportStrategy,
                    'processing_time' => microtime(true) - $startTime
                ]);
                
                log_message('error', "Ambassadors export failed: {$result['message']} (Strategy: {$exportStrategy}, Records: {$recordCount})");
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'],
                    'recordCount' => $recordCount,
                    'exportStrategy' => $exportStrategy
                ]);
            }
            
        } catch (\Exception $e) {
            $processingTime = microtime(true) - ($startTime ?? microtime(true));
            
            log_message('error', "Exception in exportAmbassadors: {$e->getMessage()} (Processing time: {$processingTime}s)");
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred during export: ' . $e->getMessage(),
                'processingTime' => $processingTime
            ]);
        }
    }

    /**
     * Get export status with enhanced error handling and proper nested data handling
     */
    public function getExportStatus(string $exportId)
    {
        try {
            $result = $this->ybbExport->getExportStatus($exportId);
            
            // Add debug logging to understand the response structure
            log_message('info', 'Export status response for ID ' . $exportId . ': ' . json_encode($result));
            
            // Handle successful response with nested data structure
            if ($result['success'] && isset($result['data'])) {
                $responseData = $result['data'];
                
                // Handle nested data structure from the API (data.data.status)
                $actualData = isset($responseData['data']) ? $responseData['data'] : $responseData;
                
                // Check if export is completed (status: "success")
                $isCompleted = ($actualData['status'] ?? '') === 'success';
                
                // Create export data structure for filename generation
                $exportData = [
                    'export_type' => 'participants', // Default, could be enhanced to detect type
                    'record_count' => $actualData['record_count'] ?? 0,
                    'created_at' => date('c')
                ];
                
                // Transform the response to include enhanced information
                $enhancedResult = [
                    'success' => true,
                    'exportId' => $exportId,
                    'status' => $isCompleted ? 'completed' : ($actualData['status'] ?? 'unknown'),
                    'fileName' => $this->generateDisplayFilename($exportData),
                    'downloadUrl' => $isCompleted ? $this->ybbExport->getConfig()->apiUrl . "/api/ybb/export/{$exportId}/download" : null,
                    'recordCount' => $actualData['record_count'] ?? null,
                    'fileSize' => $actualData['file_size'] ?? null,
                    'fileSizeFormatted' => isset($actualData['file_size']) ? $this->formatFileSize($actualData['file_size']) : null,
                    'processingTime' => $actualData['processing_time'] ?? null,
                    'expiresAt' => $actualData['expires_at'] ?? null,
                    'createdAt' => $actualData['created_at'] ?? null,
                    'exportStrategy' => 'single_file',
                    'totalFiles' => 1,
                    'individualFiles' => null,
                    'archive' => null,
                    'message' => $isCompleted ? 'Export completed and ready for download' : 'Export status retrieved successfully',
                    'raw_response' => $result // Keep for debugging
                ];
                
                // Add debug logging to see what we're returning to the frontend
                log_message('info', 'Frontend response for export ID ' . $exportId . ': ' . json_encode($enhancedResult));
                
                return $this->response->setJSON($enhancedResult);
            }
            
            // Handle successful response without expected data structure
            if ($result['success']) {
                // Sometimes the API might return success but with different structure
                log_message('warning', 'Export status successful but missing data structure for ID: ' . $exportId);
                
                $enhancedResult = [
                    'success' => true,
                    'exportId' => $exportId,
                    'status' => $result['status'] ?? 'processing',
                    'fileName' => null,
                    'downloadUrl' => null,
                    'recordCount' => $result['record_count'] ?? null,
                    'fileSize' => $result['file_size'] ?? null,
                    'processingTime' => $result['processing_time'] ?? null,
                    'expiresAt' => $result['expires_at'] ?? null,
                    'exportStrategy' => 'single_file',
                    'totalFiles' => 1,
                    'individualFiles' => null,
                    'archive' => null,
                    'message' => $result['message'] ?? 'Export status retrieved successfully',
                    'raw_response' => $result // Include raw response for debugging
                ];
                
                return $this->response->setJSON($enhancedResult);
            }
            
            // Handle 404 errors more gracefully
            if (!$result['success'] && isset($result['http_code']) && $result['http_code'] === 404) {
                return $this->response->setJSON([
                    'success' => false,
                    'exportId' => $exportId,
                    'status' => 'not_found',
                    'message' => $result['message'] ?? 'Export not found',
                    'suggestedAction' => $result['suggested_action'] ?? 'retry_or_check_completed',
                    'isTemporary' => true // Indicate this might be a temporary issue
                ]);
            }
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in getExportStatus: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download export file with enhanced streaming
     */
    public function downloadExport(string $exportId)
    {
        try {
            // First check export status to get metadata
            $statusResult = $this->ybbExport->getExportStatus($exportId);
            
            if (!$statusResult['success']) {
                if (isset($statusResult['http_code']) && $statusResult['http_code'] === 404) {
                    return $this->response->setStatusCode(404)
                                         ->setJSON([
                                             'success' => false,
                                             'message' => 'Export not found or has expired'
                                         ]);
                }
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unable to check export status: ' . $statusResult['message']
                ]);
            }
            
            // Handle nested API response structure
            $statusData = $statusResult['data']['data'] ?? $statusResult['data'];
            
            // Check if export is ready for download
            if ($statusData['status'] !== 'success') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Export is not ready for download. Status: ' . $statusData['status']
                ]);
            }
            
            // Download the file from the API
            $downloadResult = $this->ybbExport->downloadExport($exportId);
            
            if (!$downloadResult['success']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Download failed: ' . $downloadResult['message']
                ]);
            }
            
            $filePath = $downloadResult['file_path'];
            
            if (!file_exists($filePath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Downloaded file not found'
                ]);
            }
            
            // Generate appropriate filename using actual export data
            $exportData = [
                'export_type' => $this->_detectExportType($statusData),
                'record_count' => $statusData['record_count'] ?? 0,
                'created_at' => $statusData['created_at'] ?? date('c'),
                'filename' => $statusData['filename'] ?? null
            ];
            
            // Use original API filename if available, otherwise generate one
            $filename = $statusData['filename'] ?? $this->generateDisplayFilename($exportData);
            
            // Get file size for headers
            $fileSize = filesize($filePath);
            
            // Set appropriate headers for file download
            $this->setDownloadHeaders($filename, $fileSize);
            
            // Clean any output buffer to prevent corruption
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Stream the file content in chunks
            $this->streamFile($filePath);
            
            // Clean up temporary file after successful download
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Log successful download
            log_message('info', "Export downloaded successfully: ID={$exportId}, filename={$filename}, size={$fileSize}");
            
            return; // Important: Return nothing after streaming file content
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in downloadExport: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Download failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download export ZIP archive
     */
    public function downloadExportZip(string $exportId)
    {
        try {
            $result = $this->ybbExport->downloadExportZip($exportId);
            
            if ($result['success']) {
                // Use original ZIP filename from API if available, otherwise fall back to export ID
                $filename = $result['data']['zip_file_name'] ?? $result['data']['file_name'] ?? ('export_' . $exportId . '.zip');
                
                // Serve the downloaded ZIP file
                return $this->response->download($result['file_path'], null)->setFileName($filename);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in downloadExportZip: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download specific batch file
     */
    public function downloadBatchFile(string $exportId, int $batchNumber)
    {
        try {
            $result = $this->ybbExport->downloadBatchFile($exportId, $batchNumber);
            
            if ($result['success']) {
                // Use original batch filename from API if available, otherwise fall back to export ID and batch number
                $filename = $result['data']['file_name'] ?? ("export_{$exportId}_batch_{$batchNumber}.xlsx");
                
                // Serve the downloaded batch file
                return $this->response->download($result['file_path'], null)->setFileName($filename);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in downloadBatchFile: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get available export templates
     */
    public function getTemplates(?string $exportType = null)
    {
        try {
            $result = $this->ybbExport->getTemplates($exportType);
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in getTemplates: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test API connection and health
     */
    public function testConnection()
    {
        try {
            $result = $this->ybbExport->testConnection();
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in testConnection: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get storage information from API
     */
    public function getStorageInfo()
    {
        try {
            $url = $this->ybbExport->getConfig()->apiUrl . "/api/ybb/storage/info";
            $result = $this->ybbExport->makeRequest('GET', $url);
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in getStorageInfo: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to get storage info: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get filters from request
     */
    private function _getFiltersFromRequest(): array
    {
        $filters = [];
        
        // CRITICAL: Get program ID from session (current_program) - this is mandatory
        if (session()->has('current_program')) {
            $filters['program_id'] = session('current_program');
            log_message('info', 'Export filter: Using program_id from session: ' . $filters['program_id']);
        } else {
            log_message('error', 'Export filter: No current_program in session - this will cause ALL participants to be exported!');
        }
        
        // Get other filters from POST request body
        $postData = $this->request->getPost();
        if ($postData) {
            if (isset($postData['program_id'])) {
                $filters['program_id'] = $postData['program_id'];
                log_message('info', 'Export filter: Using program_id from POST: ' . $filters['program_id']);
            }
            
            // Handle date_range filter (convert to date_from and date_to)
            if (isset($postData['date_range']) && !empty($postData['date_range'])) {
                $dateRange = trim($postData['date_range']);
                if (strpos($dateRange, ' - ') !== false) {
                    $dates = explode(' - ', $dateRange);
                    if (count($dates) === 2) {
                        $filters['date_from'] = trim($dates[0]) . ' 00:00:00';
                        $filters['date_to'] = trim($dates[1]) . ' 23:59:59';
                        log_message('info', 'Export filter: Converted date_range "' . $dateRange . '" to date_from: ' . $filters['date_from'] . ', date_to: ' . $filters['date_to']);
                    }
                }
            }
            
            // Add other filters
            $filterKeys = ['status', 'category', 'form_status', 'payment_status', 'general_status', 'program_payment_id', 'payment_category', 'limit', 'template', 'format'];
            foreach ($filterKeys as $key) {
                if (isset($postData[$key]) && $postData[$key] !== '' && $postData[$key] !== null) {
                    $filters[$key] = $postData[$key];
                    log_message('debug', 'Export filter: Added ' . $key . ' = ' . $postData[$key]);
                }
            }
        }
        
        // Get other filters from request JSON (if any)
        $request = $this->request->getJSON(true);
        if ($request) {
            $filters = array_merge($filters, $request['filters'] ?? []);
        }
        
        // Ensure program_id is always set to prevent exporting all participants
        if (!isset($filters['program_id']) || empty($filters['program_id'])) {
            // Try to get from URL or fallback
            $programId = $this->request->getGet('program_id') ?? session('current_program');
            if ($programId) {
                $filters['program_id'] = $programId;
                log_message('info', 'Export filter: Using fallback program_id: ' . $filters['program_id']);
            } else {
                log_message('error', 'Export filter: CRITICAL - No program_id found! This will export ALL participants from database!');
                throw new \RuntimeException('program_id is required - No program ID specified for export. Cannot proceed without program filter.');
            }
        }
        
        log_message('info', 'Export filters applied: ' . json_encode($filters));
        return $filters;
    }

    /**
     * Get export options from request
     */
    private function _getExportOptions(string $exportType = '', array $filters = []): array
    {
        $request = $this->request->getJSON(true);
        $postData = $this->request->getPost();
        
        $options = [
            'template' => $postData['template'] ?? $request['template'] ?? $filters['template'] ?? 'standard',
            'format' => $postData['format'] ?? $request['format'] ?? $filters['format'] ?? 'excel',
            'include_images' => $request['include_images'] ?? false,
            'compress' => $request['compress'] ?? true,
            'batch_size' => $request['batch_size'] ?? 5000
        ];
        
        log_message('debug', 'Export options: ' . json_encode($options));
        
        // Generate descriptive filename if export type and filters are provided
        if (!empty($exportType) && !empty($filters)) {
            $options['filename'] = $this->_generateDescriptiveFilename($exportType, $filters);
            $options['sheet_name'] = $this->_generateSheetName($exportType, $filters);
        }
        
        return $options;
    }

    /**
     * Generate descriptive filename based on export type and filters
     */
    private function _generateDescriptiveFilename(string $exportType, array $filters): string
    {
        // Get program data if program_id is provided
        $program = ['name' => 'Unknown Program'];
        
        if (isset($filters['program_id'])) {
            try {
                $db = \Config\Database::connect();
                $programQuery = $db->query("SELECT name FROM programs WHERE id = ?", [$filters['program_id']]);
                $programData = $programQuery->getRowArray();
                
                if ($programData && !empty($programData['name'])) {
                    $program = $programData;
                }
            } catch (\Exception $e) {
                log_message('warning', 'Could not fetch program name for filename: ' . $e->getMessage());
            }
        }
        
        // Use the ExportFilenameHelper to generate descriptive filename
        $filename = ExportFilenameHelper::generateDescriptiveFilename($program, $exportType, $filters);
        
        // Log the generated filename for debugging
        log_message('info', "Generated export filename: {$filename} for export type: {$exportType}");
        
        return $filename;
    }
    
    /**
     * Generate participant export descriptor based on filters
     */
    private function _getParticipantExportDescriptor(array $filters): string
    {
        $descriptors = [];
        
        // Add category filter description
        if (isset($filters['category']) && $filters['category'] !== 'all') {
            $categoryMap = [
                'fully_funded' => 'Fully_Funded',
                'self_funded' => 'Self_Funded'
            ];
            $descriptors[] = $categoryMap[$filters['category']] ?? ucfirst($filters['category']);
        }
        
        // Add status filter description
        if (isset($filters['form_status']) && $filters['form_status'] !== 'all') {
            $statusMap = [
                '0' => 'Draft_Forms',
                '1' => 'Submitted_Forms', 
                '2' => 'Approved_Forms'
            ];
            $descriptors[] = $statusMap[$filters['form_status']] ?? 'Status_' . $filters['form_status'];
        }
        
        if (isset($filters['general_status']) && $filters['general_status'] !== 'all') {
            $generalStatusMap = [
                '0' => 'Pending_Review',
                '1' => 'Under_Review',
                '2' => 'Approved',
                '3' => 'Rejected'
            ];
            $descriptors[] = $generalStatusMap[$filters['general_status']] ?? 'General_Status_' . $filters['general_status'];
        }
        
        // Add payment status description
        if (isset($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $paymentStatusMap = [
                'success' => 'Paid',
                'pending' => 'Payment_Pending',
                'failed' => 'Payment_Failed'
            ];
            $descriptors[] = $paymentStatusMap[$filters['payment_status']] ?? ucfirst($filters['payment_status']);
        }
        
        // Add date range if specified
        if (isset($filters['date_from']) || isset($filters['date_to'])) {
            if (isset($filters['date_from']) && isset($filters['date_to'])) {
                $fromDate = date('d-m', strtotime($filters['date_from']));
                $toDate = date('d-m', strtotime($filters['date_to']));
                $descriptors[] = "({$fromDate}_to_{$toDate})";
            } elseif (isset($filters['date_from'])) {
                $fromDate = date('d-m-Y', strtotime($filters['date_from']));
                $descriptors[] = "(from_{$fromDate})";
            } elseif (isset($filters['date_to'])) {
                $toDate = date('d-m-Y', strtotime($filters['date_to']));
                $descriptors[] = "(until_{$toDate})";
            }
        }
        
        // Base descriptor
        $baseDescriptor = 'Participants';
        
        // If we have specific filters, add them
        if (!empty($descriptors)) {
            $fullDescriptor = $baseDescriptor . '_' . implode('_', $descriptors);
            // Limit total length to prevent overly long filenames
            if (strlen($fullDescriptor) > 60) {
                $fullDescriptor = $baseDescriptor . '_Filtered_Data';
            }
            return $fullDescriptor;
        }
        
        // Default comprehensive export
        return $baseDescriptor . '_Complete_Registration_Data';
    }
    
    /**
     * Generate payment export descriptor based on filters
     */
    private function _getPaymentExportDescriptor(array $filters): string
    {
        $descriptors = [];
        
        // Add payment status description
        if (isset($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $statusMap = [
                '0' => 'Created',
                '1' => 'Pending',
                '2' => 'Successful',
                '3' => 'Cancelled',
                '4' => 'Rejected'
            ];
            $descriptors[] = $statusMap[$filters['payment_status']] ?? 'Status_' . $filters['payment_status'];
        }
        
        // Add payment method description
        if (isset($filters['payment_method_id']) && $filters['payment_method_id'] !== 'all') {
            // Could be enhanced to fetch actual payment method name
            $descriptors[] = 'Method_' . $filters['payment_method_id'];
        }
        
        // Add currency description
        if (isset($filters['currency']) && $filters['currency'] !== 'all') {
            $descriptors[] = strtoupper($filters['currency']);
        }
        
        // Add participant category description
        if (isset($filters['participant_category']) && $filters['participant_category'] !== 'all') {
            $categoryMap = [
                'fully_funded' => 'Fully_Funded_Participants',
                'self_funded' => 'Self_Funded_Participants'
            ];
            $descriptors[] = $categoryMap[$filters['participant_category']] ?? ucfirst($filters['participant_category']);
        }
        
        // Add date range if specified
        if (isset($filters['payment_date_from']) || isset($filters['payment_date_to'])) {
            if (isset($filters['payment_date_from']) && isset($filters['payment_date_to'])) {
                $fromDate = date('d-m', strtotime($filters['payment_date_from']));
                $toDate = date('d-m', strtotime($filters['payment_date_to']));
                $descriptors[] = "({$fromDate}_to_{$toDate})";
            }
        } elseif (isset($filters['date_from']) || isset($filters['date_to'])) {
            if (isset($filters['date_from']) && isset($filters['date_to'])) {
                $fromDate = date('d-m', strtotime($filters['date_from']));
                $toDate = date('d-m', strtotime($filters['date_to']));
                $descriptors[] = "({$fromDate}_to_{$toDate})";
            }
        }
        
        // Base descriptor
        $baseDescriptor = 'Payments';
        
        // If we have specific filters, add them
        if (!empty($descriptors)) {
            $fullDescriptor = $baseDescriptor . '_' . implode('_', $descriptors) . '_Report';
            // Limit total length to prevent overly long filenames
            if (strlen($fullDescriptor) > 60) {
                $fullDescriptor = $baseDescriptor . '_Filtered_Report';
            }
            return $fullDescriptor;
        }
        
        // Default comprehensive export
        return $baseDescriptor . '_Complete_Transaction_Report';
    }
    
    /**
     * Generate sheet name for Excel export
     */
    private function _generateSheetName(string $exportType, array $filters): string
    {
        // Get program data if program_id is provided
        $program = ['name' => 'Program'];
        
        if (isset($filters['program_id'])) {
            try {
                $db = \Config\Database::connect();
                $programQuery = $db->query("SELECT name FROM programs WHERE id = ?", [$filters['program_id']]);
                $programData = $programQuery->getRowArray();
                
                if ($programData && !empty($programData['name'])) {
                    $program = $programData;
                }
            } catch (\Exception $e) {
                log_message('warning', 'Could not fetch program name for sheet name: ' . $e->getMessage());
            }
        }
        
        // Use the ExportFilenameHelper to generate sheet name
        return ExportFilenameHelper::generateSheetName($program, $exportType);
    }

    /**
     * Get participants data based on filters with OPTIMIZED performance for large datasets
     */
    private function _getParticipantsData(array $filters): array
    {
        try {
            // CRITICAL: Apply program filter FIRST - this is mandatory
            if (!isset($filters['program_id']) || empty($filters['program_id'])) {
                throw new \RuntimeException('Program ID filter is required for participant export');
            }

            log_message('info', "Starting OPTIMIZED participant export for program {$filters['program_id']}");
            
            // Check dataset size and choose appropriate method
            $db = \Config\Database::connect();
            $countQuery = $db->query("SELECT COUNT(*) as total FROM participants WHERE program_id = ? AND is_deleted = 0", [$filters['program_id']]);
            $totalCount = $countQuery->getRowArray()['total'] ?? 0;
            
            log_message('info', "Dataset size for program {$filters['program_id']}: {$totalCount} participants");
            
            // Use the advanced optimized model for all exports (avoids chunked processing issues)
            $advancedModel = new \App\Models\AdvancedOptimizedParticipantExportModel();
            
            log_message('info', "Using advanced Python-optimized processing for {$totalCount} participants");
            $result = $advancedModel->getPythonOptimizedParticipantsForExport($filters);
            
            // Extract the data array from the result
            $exportData = $result['data'] ?? [];
            
            log_message('info', "Completed ADVANCED participant export for program {$filters['program_id']}: " . count($exportData) . " records with Python compatibility");
            
            return $exportData;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting optimized participants data: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve optimized participants data: ' . $e->getMessage());
        }
    }

    /**
     * Get payments data based on filters with normalized status translations
     */
    private function _getPaymentsData(array $filters): array
    {
        try {
            // CRITICAL: Apply program filter FIRST - this is mandatory
            if (!isset($filters['program_id']) || empty($filters['program_id'])) {
                throw new \RuntimeException('Program ID filter is required for payment export');
            }

            log_message('info', "Starting normalized payment export for program {$filters['program_id']}");
            
            // Use the new normalized payment export method from PaymentModel
            // Pass program_id as first parameter and remaining filters as second parameter
            $programId = $filters['program_id'];
            unset($filters['program_id']); // Remove program_id from filters since it's passed separately
            $result = $this->paymentModel->getNormalizedPaymentsForExport($programId, $filters);
            
            log_message('info', "Completed normalized payment export for program {$programId}: " . count($result) . " records with human-readable status translations");
            
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting normalized payments data: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve normalized payments data: ' . $e->getMessage());
        }
    }

    /**
     * Get ambassadors data based on filters
     */
    private function _getAmbassadorsData(array $filters): array
    {
        $builder = $this->ambassadorModel->builder();
        
        // Apply program filter
        if (isset($filters['program_id'])) {
            $builder->where('program_id', $filters['program_id']);
        }
        
        // Apply other filters as needed
        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        
        if (isset($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to']);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Log export request for tracking with enhanced metadata
     */
    private function _logExportRequest(int $programId, string $exportType, array $options): ?int
    {
        try {
            $db = \Config\Database::connect();
            
            $data = [
                'program_id' => $programId,
                'export_type' => $exportType,
                'user_id' => session('user_id') ?? 0,
                'filters' => json_encode($this->_getFiltersFromRequest()),
                'custom_filename' => $options['filename'] ?? null,
                'export_strategy' => $options['export_strategy'] ?? 'single_file',
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $builder = $db->table('export_requests');
            $inserted = $builder->insert($data);
            
            if ($inserted) {
                return $db->insertID();
            }
            
            return null;
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log export request: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update export request log
     */
    private function _updateExportRequestLog(?int $exportRequestId, array $data): void
    {
        if (!$exportRequestId) {
            return;
        }
        
        try {
            $db = \Config\Database::connect();
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $builder = $db->table('export_requests');
            $builder->where('id', $exportRequestId)->update($data);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to update export request log: ' . $e->getMessage());
        }
    }

    /**
     * Format file size for display
     */
    private function _formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Format expiry time for display
     */
    private function _formatExpiryTime(?string $expiresAt): ?string
    {
        if (!$expiresAt) {
            return null;
        }
        
        try {
            $expiryTime = new \DateTime($expiresAt);
            $now = new \DateTime();
            $diff = $now->diff($expiryTime);
            
            if ($diff->invert) {
                return 'Expired';
            }
            
            if ($diff->h > 0) {
                return $diff->h . ' hours remaining';
            } elseif ($diff->i > 0) {
                return $diff->i . ' minutes remaining';
            } else {
                return 'Expires soon';
            }
            
        } catch (\Exception $e) {
            return 'Unknown expiry';
        }
    }

    /**
     * Generate display filename for export
     */
    private function generateDisplayFilename(array $exportData): string
    {
        $type = ucfirst($exportData['export_type'] ?? 'export');
        $date = date('Y-m-d_H-i-s', strtotime($exportData['created_at'] ?? 'now'));
        $count = $exportData['record_count'] ?? 0;
        
        return "YBB_{$type}_Export_{$count}_records_{$date}.xlsx";
    }

    /**
     * Format file size for display (public method)
     */
    private function formatFileSize(int $bytes): string
    {
        return $this->_formatFileSize($bytes);
    }

    /**
     * Set appropriate download headers
     */
    private function setDownloadHeaders(string $filename, int $fileSize): void
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Determine content type
        $contentTypes = [
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'csv' => 'text/csv'
        ];
        
        $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
        
        // Set headers
        $this->response->setHeader('Content-Type', $contentType)
                      ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                      ->setHeader('Content-Length', (string)$fileSize)
                      ->setHeader('Cache-Control', 'no-cache, must-revalidate')
                      ->setHeader('Expires', '0')
                      ->setHeader('Pragma', 'public');
    }

    /**
     * Stream file content to browser
     */
    private function streamFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }
        
        $fileHandle = fopen($filePath, 'rb');
        if (!$fileHandle) {
            throw new \Exception("Cannot open file for reading: {$filePath}");
        }
        
        // Clean any output buffer to prevent corruption
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Stream the file in chunks
        while (!feof($fileHandle)) {
            echo fread($fileHandle, 8192); // 8KB chunks
            flush();
        }
        
        fclose($fileHandle);
    }

    /**
     * Detect export type from status data or filename
     */
    private function _detectExportType(array $statusData): string
    {
        // Try to detect from export_type field
        if (isset($statusData['export_type'])) {
            return $statusData['export_type'];
        }
        
        // Try to detect from filename
        if (isset($statusData['filename'])) {
            $filename = strtolower($statusData['filename']);
            if (strpos($filename, 'participant') !== false) {
                return 'participants';
            } elseif (strpos($filename, 'payment') !== false) {
                return 'payments';
            } elseif (strpos($filename, 'ambassador') !== false) {
                return 'ambassadors';
            }
        }
        
        // Default fallback
        return 'participants';
    }

    /**
     * Clean data for Excel compatibility - prevents file corruption
     * 
     * This function fixes the Excel corruption issues caused by:
     * 1. latin1 database encoding corrupting Unicode characters
     * 2. NULL bytes in data
     * 3. Control characters
     * 4. Very long text fields
     * 5. Formula injection risks
     */
    private function _cleanDataForExcel(array $data): array
    {
        log_message('info', 'Starting Excel data cleaning for ' . count($data) . ' records');
        
        $cleanedCount = 0;
        $issuesFound = [];
        
        foreach ($data as &$row) {
            $rowCleaned = false;
            
            foreach ($row as $field => &$value) {
                if (!is_string($value) || $value === '' || $value === null) {
                    continue;
                }
                
                $originalValue = $value;
                
                // 1. Remove NULL bytes (CRITICAL - these cause Excel corruption)
                if (strpos($value, "\0") !== false) {
                    $value = str_replace("\0", '', $value);
                    $issuesFound[] = "Removed NULL bytes from field: $field";
                    $rowCleaned = true;
                }
                
                // 2. Remove other control characters except newlines and tabs
                $beforeControlClean = $value;
                $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
                if ($beforeControlClean !== $value) {
                    $issuesFound[] = "Removed control characters from field: $field";
                    $rowCleaned = true;
                }
                
                // 3. Handle corrupted Unicode from latin1 database
                // Replace common corruption patterns with question marks
                $beforeUnicodeClean = $value;
                $value = preg_replace('/�+/', '?', $value); // Replace corrupted Unicode symbols
                
                // Ensure proper UTF-8 encoding
                if (!mb_check_encoding($value, 'UTF-8')) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    $issuesFound[] = "Fixed UTF-8 encoding for field: $field";
                    $rowCleaned = true;
                }
                
                if ($beforeUnicodeClean !== $value) {
                    $issuesFound[] = "Cleaned corrupted Unicode characters from field: $field";
                    $rowCleaned = true;
                }
                
                // 4. Truncate very long fields (Excel has a 32,767 character limit per cell)
                if (strlen($value) > 32767) {
                    $value = substr($value, 0, 32764) . '...';
                    $issuesFound[] = "Truncated long text in field: $field (was " . strlen($originalValue) . " chars)";
                    $rowCleaned = true;
                }
                
                // 5. Prevent Excel formula injection
                if (preg_match('/^[=+\-@]/', $value)) {
                    $value = "'" . $value; // Prefix with apostrophe to treat as text
                    $issuesFound[] = "Prevented formula injection in field: $field";
                    $rowCleaned = true;
                }
                
                // 6. Remove or replace problematic Unicode characters (BOM, zero-width, etc.)
                $beforeSpecialUnicode = $value;
                $value = preg_replace('/[\x{FEFF}\x{200B}-\x{200D}\x{FFFE}\x{FFFF}]/u', '', $value);
                if ($beforeSpecialUnicode !== $value) {
                    $issuesFound[] = "Removed problematic Unicode characters from field: $field";
                    $rowCleaned = true;
                }
            }
            
            if ($rowCleaned) {
                $cleanedCount++;
            }
        }
        
        // Log summary of cleaning
        if ($cleanedCount > 0) {
            log_message('info', "Excel data cleaning completed: cleaned $cleanedCount records out of " . count($data));
            log_message('info', "Issues found and fixed: " . count($issuesFound));
            
            // Log first few issues as examples
            $sampleIssues = array_slice($issuesFound, 0, 10);
            foreach ($sampleIssues as $issue) {
                log_message('info', "  - $issue");
            }
            
            if (count($issuesFound) > 10) {
                log_message('info', "  ... and " . (count($issuesFound) - 10) . " more issues");
            }
        } else {
            log_message('info', "Excel data cleaning completed: no issues found in " . count($data) . " records");
        }
        
        return $data;
    }
    
    /**
     * Log detailed performance metrics
     */
    private function _logPerformanceMetrics(array $metrics, int $recordCount, bool $isChunked): void
    {
        if (empty($metrics)) {
            return;
        }
        
        $logMessage = "Export Performance Metrics:\n";
        $logMessage .= "  Records: " . number_format($recordCount) . "\n";
        $logMessage .= "  Strategy: " . ($isChunked ? 'Chunked' : 'Single File') . "\n";
        
        if (isset($metrics['total_processing_time_seconds'])) {
            $logMessage .= "  Processing Time: {$metrics['total_processing_time_seconds']}s\n";
        }
        
        if (isset($metrics['records_per_second'])) {
            $logMessage .= "  Throughput: " . number_format($metrics['records_per_second'], 1) . " records/sec\n";
        }
        
        if (isset($metrics['memory_used_mb'])) {
            $logMessage .= "  Memory Used: {$metrics['memory_used_mb']} MB\n";
        }
        
        if (isset($metrics['peak_memory_mb'])) {
            $logMessage .= "  Peak Memory: {$metrics['peak_memory_mb']} MB\n";
        }
        
        if ($isChunked && isset($metrics['average_chunk_processing_time_seconds'])) {
            $logMessage .= "  Avg Chunk Time: {$metrics['average_chunk_processing_time_seconds']}s\n";
            
            if (isset($metrics['efficiency_metrics']['compression_efficiency'])) {
                $logMessage .= "  Compression: {$metrics['efficiency_metrics']['compression_efficiency']}\n";
            }
        }
        
        if (isset($metrics['efficiency_metrics'])) {
            $efficiency = $metrics['efficiency_metrics'];
            if (isset($efficiency['kb_per_record'])) {
                $logMessage .= "  Size Efficiency: {$efficiency['kb_per_record']} KB/record\n";
            }
            if (isset($efficiency['processing_ms_per_record'])) {
                $logMessage .= "  Time Efficiency: {$efficiency['processing_ms_per_record']} ms/record\n";
            }
        }
        
        log_message('info', $logMessage);
    }
    
    /**
     * Build user-friendly export completion message with performance info
     */
    private function _buildExportMessage(array $exportData, int $recordCount, array $metrics): string
    {
        $isChunked = isset($exportData['export_strategy']) && $exportData['export_strategy'] === 'chunked';
        
        if ($isChunked) {
            $fileCount = $exportData['total_files'] ?? 1;
            $message = "Chunked export completed: " . number_format($recordCount) . " records in $fileCount files";
            
            if (isset($metrics['total_processing_time_seconds'])) {
                $message .= " (processed in {$metrics['total_processing_time_seconds']}s)";
            }
            
            if (isset($exportData['archive']['compression_ratio'])) {
                $message .= " - Compressed to {$exportData['archive']['compression_ratio']}";
            }
        } else {
            $message = "Export completed successfully with " . number_format($recordCount) . " records";
            
            if (isset($metrics['total_processing_time_seconds'])) {
                $message .= " (processed in {$metrics['total_processing_time_seconds']}s)";
            }
            
            if (isset($metrics['records_per_second'])) {
                $message .= " at " . number_format($metrics['records_per_second'], 0) . " records/sec";
            }
        }
        
        return $message;
    }
    
    /**
     * Format performance statistics for frontend display
     */
    private function _formatPerformanceStats(array $metrics, bool $isChunked): array
    {
        $stats = [];
        
        // Processing time statistics
        if (isset($metrics['total_processing_time_seconds'])) {
            $stats['processingTime'] = [
                'total_seconds' => $metrics['total_processing_time_seconds'],
                'formatted' => $this->_formatDuration($metrics['total_processing_time_seconds'])
            ];
        }
        
        // Throughput statistics
        if (isset($metrics['records_per_second'])) {
            $stats['throughput'] = [
                'records_per_second' => round($metrics['records_per_second'], 1),
                'formatted' => number_format($metrics['records_per_second'], 0) . ' records/sec'
            ];
        }
        
        // Memory usage statistics
        if (isset($metrics['memory_used_mb'])) {
            $stats['memory'] = [
                'used_mb' => $metrics['memory_used_mb'],
                'peak_mb' => $metrics['peak_memory_mb'] ?? null,
                'formatted' => $metrics['memory_used_mb'] . ' MB used'
            ];
            
            if (isset($metrics['peak_memory_mb'])) {
                $stats['memory']['formatted'] .= ' (peak: ' . $metrics['peak_memory_mb'] . ' MB)';
            }
        }
        
        // Efficiency metrics
        if (isset($metrics['efficiency_metrics'])) {
            $efficiency = $metrics['efficiency_metrics'];
            $stats['efficiency'] = [];
            
            if (isset($efficiency['kb_per_record'])) {
                $stats['efficiency']['size_per_record'] = $efficiency['kb_per_record'] . ' KB/record';
            }
            
            if (isset($efficiency['processing_ms_per_record'])) {
                $stats['efficiency']['time_per_record'] = $efficiency['processing_ms_per_record'] . ' ms/record';
            }
            
            if (isset($efficiency['compression_efficiency'])) {
                $stats['efficiency']['compression'] = $efficiency['compression_efficiency'];
            }
        }
        
        // Chunked export specific stats
        if ($isChunked) {
            if (isset($metrics['average_chunk_processing_time_seconds'])) {
                $stats['chunking'] = [
                    'avg_chunk_time' => $metrics['average_chunk_processing_time_seconds'],
                    'formatted' => $metrics['average_chunk_processing_time_seconds'] . 's avg/chunk'
                ];
            }
            
            if (isset($metrics['chunk_processing_times'])) {
                $times = $metrics['chunk_processing_times'];
                $stats['chunking']['individual_times'] = $times;
                $stats['chunking']['min_time'] = min($times);
                $stats['chunking']['max_time'] = max($times);
            }
        }
        
        return $stats;
    }
    
    /**
     * Format duration in human-readable format
     */
    private function _formatDuration(float $seconds): string
    {
        if ($seconds < 1) {
            return round($seconds * 1000) . 'ms';
        } elseif ($seconds < 60) {
            return round($seconds, 1) . 's';
        } else {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . 'm ' . round($remainingSeconds, 1) . 's';
        }
    }
    
    /**
     * Determine optimal export strategy based on record count and data type
     */
    private function _determineExportStrategy(int $recordCount, string $dataType = 'participants'): string
    {
        // Define thresholds based on data type
        $thresholds = [
            'participants' => 5000,  // Participants are larger with essays/certificates
            'payments' => 8000,      // Payments are smaller, can handle more records
            'ambassadors' => 10000   // Ambassadors are typically smaller datasets
        ];
        
        $threshold = $thresholds[$dataType] ?? 5000;
        
        // Force chunking if dataset is large
        if ($recordCount > $threshold) {
            log_message('info', "Large dataset detected ({$recordCount} > {$threshold}): using chunked export strategy");
            return 'chunked';
        }
        
        log_message('info', "Small dataset detected ({$recordCount} <= {$threshold}): using single file export strategy");
        return 'single_file';
    }
    
    /**
     * Get optimal chunk size based on record count and data type
     */
    private function _getOptimalChunkSize(int $recordCount, string $dataType = 'participants'): int
    {
        // Define optimal chunk sizes based on data type
        $chunkSizes = [
            'participants' => 5000,  // Participants have more data per record
            'payments' => 8000,      // Payments are simpler, can handle larger chunks
            'ambassadors' => 10000   // Ambassadors are typically smaller records
        ];
        
        $defaultChunkSize = $chunkSizes[$dataType] ?? 5000;
        
        // Adjust chunk size based on total record count
        if ($recordCount < $defaultChunkSize) {
            return $recordCount; // Don't chunk if smaller than chunk size
        }
        
        // For very large datasets, consider smaller chunks for better performance
        if ($recordCount > 50000) {
            return intval($defaultChunkSize * 0.8); // 20% smaller chunks for huge datasets
        }
        
        return $defaultChunkSize;
    }
}
