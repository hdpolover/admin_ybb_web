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
class YbbExportController extends BaseController
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
            
            // Create export using YBB Export API
            $result = $this->ybbExport->exportParticipants($participants, $options);
            
            if ($result['success']) {
                // Update log with success details
                $this->_updateExportRequestLog($exportRequestId, [
                    'status' => 'success',
                    'export_id' => $result['data']['export_id'],
                    'file_name' => $result['data']['file_name'],
                    'record_count' => count($participants),
                    'file_size' => $result['data']['file_size'] ?? null,
                    'processing_time' => $result['metadata']['processing_time'] ?? null,
                    'expires_at' => $result['data']['expires_at']
                ]);
                
                log_message('info', 'Participants export initiated: ' . json_encode($result));
                
                return $this->response->setJSON([
                    'success' => true,
                    'exportId' => $result['data']['export_id'],
                    'fileName' => $result['data']['file_name'] ?? null,
                    'downloadUrl' => $result['data']['download_url'] ?? null,
                    'message' => 'Export initiated successfully',
                    'recordCount' => count($participants),
                    'processingTime' => $result['metadata']['processing_time'] ?? null,
                    'exportStrategy' => $result['data']['export_strategy'] ?? 'single_file',
                    'totalFiles' => $result['data']['total_files'] ?? 1,
                    'individualFiles' => $result['data']['individual_files'] ?? null,
                    'archive' => $result['data']['archive'] ?? null,
                    'status' => $result['data']['download_url'] ? 'completed' : 'processing',
                    'data' => [
                        'export_id' => $result['data']['export_id'],
                        'download_url' => $result['data']['download_url'] ?? null,
                        'export_strategy' => $result['data']['export_strategy'] ?? 'single_file',
                        'total_files' => $result['data']['total_files'] ?? 1
                    ]
                ]);
            } else {
                // Update log with error details
                $this->_updateExportRequestLog($exportRequestId, [
                    'status' => 'error',
                    'error_message' => $result['message']
                ]);
                
                log_message('error', 'Participants export failed: ' . $result['message']);
                
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
     * Export payments data
     */
    public function exportPayments()
    {
        try {
            // Get filters from request
            $filters = $this->_getFiltersFromRequest();
            
            // Get payments data
            $payments = $this->_getPaymentsData($filters);
            
            if (empty($payments)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No payments found for export'
                ]);
            }

            // Get export options with descriptive filename
            $options = $this->_getExportOptions('payments', $filters);
            
            // Log export request for tracking
            $exportRequestId = $this->_logExportRequest($filters['program_id'], 'payments', $options);
            
            // Create export using YBB Export API
            $result = $this->ybbExport->exportPayments($payments, $options);
            
            if ($result['success']) {
                // Update log with success details
                $this->_updateExportRequestLog($exportRequestId, [
                    'status' => 'success',
                    'export_id' => $result['data']['export_id'],
                    'file_name' => $result['data']['file_name'],
                    'record_count' => count($payments),
                    'file_size' => $result['data']['file_size'] ?? null,
                    'processing_time' => $result['metadata']['processing_time'] ?? null,
                    'expires_at' => $result['data']['expires_at']
                ]);
                
                log_message('info', 'Payments export initiated: ' . json_encode($result));
                
                return $this->response->setJSON([
                    'success' => true,
                    'exportId' => $result['data']['export_id'],
                    'fileName' => $result['data']['file_name'] ?? null,
                    'downloadUrl' => $result['data']['download_url'] ?? null,
                    'message' => 'Export initiated successfully',
                    'recordCount' => count($payments),
                    'processingTime' => $result['metadata']['processing_time'] ?? null,
                    'exportStrategy' => $result['data']['export_strategy'] ?? 'single_file',
                    'totalFiles' => $result['data']['total_files'] ?? 1,
                    'individualFiles' => $result['data']['individual_files'] ?? null,
                    'archive' => $result['data']['archive'] ?? null,
                    'status' => $result['data']['download_url'] ? 'completed' : 'processing',
                    'data' => [
                        'export_id' => $result['data']['export_id'],
                        'download_url' => $result['data']['download_url'] ?? null,
                        'export_strategy' => $result['data']['export_strategy'] ?? 'single_file',
                        'total_files' => $result['data']['total_files'] ?? 1
                    ]
                ]);
            } else {
                // Update log with error details
                $this->_updateExportRequestLog($exportRequestId, [
                    'status' => 'error',
                    'error_message' => $result['message']
                ]);
                
                log_message('error', 'Payments export failed: ' . $result['message']);
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in exportPayments: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred during export: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export ambassadors data
     */
    public function exportAmbassadors()
    {
        try {
            // Get filters from request
            $filters = $this->_getFiltersFromRequest();
            
            // Get ambassadors data
            $ambassadors = $this->_getAmbassadorsData($filters);
            
            if (empty($ambassadors)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No ambassadors found for export'
                ]);
            }

            // Get export options with descriptive filename
            $options = $this->_getExportOptions('ambassadors', $filters);
            
            // Log export request for tracking
            $exportRequestId = $this->_logExportRequest($filters['program_id'], 'ambassadors', $options);
            
            // Create export using YBB Export API
            $result = $this->ybbExport->exportAmbassadors($ambassadors, $options);
            
            if ($result['success']) {
                // Update log with success details
                $this->_updateExportRequestLog($exportRequestId, [
                    'status' => 'success',
                    'export_id' => $result['data']['export_id'],
                    'file_name' => $result['data']['file_name'],
                    'record_count' => count($ambassadors),
                    'file_size' => $result['data']['file_size'] ?? null,
                    'processing_time' => $result['metadata']['processing_time'] ?? null,
                    'expires_at' => $result['data']['expires_at']
                ]);
                
                log_message('info', 'Ambassadors export initiated: ' . json_encode($result));
                
                return $this->response->setJSON([
                    'success' => true,
                    'exportId' => $result['data']['export_id'],
                    'fileName' => $result['data']['file_name'] ?? null,
                    'downloadUrl' => $result['data']['download_url'] ?? null,
                    'message' => 'Export initiated successfully',
                    'recordCount' => count($ambassadors),
                    'processingTime' => $result['metadata']['processing_time'] ?? null,
                    'exportStrategy' => $result['data']['export_strategy'] ?? 'single_file',
                    'totalFiles' => $result['data']['total_files'] ?? 1,
                    'individualFiles' => $result['data']['individual_files'] ?? null,
                    'archive' => $result['data']['archive'] ?? null,
                    'status' => $result['data']['download_url'] ? 'completed' : 'processing',
                    'data' => [
                        'export_id' => $result['data']['export_id'],
                        'download_url' => $result['data']['download_url'] ?? null,
                        'export_strategy' => $result['data']['export_strategy'] ?? 'single_file',
                        'total_files' => $result['data']['total_files'] ?? 1
                    ]
                ]);
            } else {
                // Update log with error details
                $this->_updateExportRequestLog($exportRequestId, [
                    'status' => 'error',
                    'error_message' => $result['message']
                ]);
                
                log_message('error', 'Ambassadors export failed: ' . $result['message']);
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in exportAmbassadors: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred during export: ' . $e->getMessage()
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
                    'downloadUrl' => $isCompleted ? site_url("admin/exports/download/{$exportId}") : null,
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
            $filterKeys = ['status', 'category', 'form_status', 'payment_status', 'general_status', 'program_payment_id', 'limit', 'template', 'format'];
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
     * Get participants data based on filters
     */
    private function _getParticipantsData(array $filters): array
    {
        try {
            // Use export database connection with extended timeout
            $db = \Config\Database::connect('export');
            
            $builder = $this->participantModel->builder();
            
            // Check if we need to join with participant_statuses table for status data or filtering
            $needsStatusJoin = isset($filters['form_status']) || isset($filters['payment_status']) || isset($filters['general_status']);
            
            // Build SELECT clause based on what joins we need
            if ($needsStatusJoin) {
                // Include status fields in SELECT
                $builder->select('
                    participants.*,
                    users.email as user_email,
                    users.full_name as user_full_name,
                    users.is_verified as user_is_verified,
                    users.created_at as user_created_at,
                    programs.name as program_name,
                    programs.start_date as program_start_date,
                    programs.end_date as program_end_date,
                    programs.theme as program_theme,
                    ps.form_status as participant_form_status,
                    ps.payment_status as participant_payment_status,
                    ps.general_status as participant_general_status,
                    ps.document_status as participant_document_status,
                    MAX(CASE WHEN e.rn = 1 THEN e.answer END) AS question_1,
                    MAX(CASE WHEN e.rn = 2 THEN e.answer END) AS question_2,
                    MAX(CASE WHEN e.rn = 3 THEN e.answer END) AS question_3,
                    MAX(CASE WHEN e.rn = 4 THEN e.answer END) AS question_4,
                    MAX(CASE WHEN e.rn = 5 THEN e.answer END) AS question_5,
                    MAX(CASE WHEN e.rn = 6 THEN e.answer END) AS question_6,
                    MAX(CASE WHEN e.rn = 7 THEN e.answer END) AS question_7,
                    MAX(CASE WHEN e.rn = 8 THEN e.answer END) AS question_8,
                    MAX(CASE WHEN e.rn = 9 THEN e.answer END) AS question_9,
                    MAX(CASE WHEN e.rn = 10 THEN e.answer END) AS question_10
                ');
                log_message('info', 'Participant export: Using extended SELECT with status fields and essays');
            } else {
                // Basic SELECT without status fields but with essays
                $builder->select('
                    participants.*,
                    users.email as user_email,
                    users.full_name as user_full_name,
                    users.is_verified as user_is_verified,
                    users.created_at as user_created_at,
                    programs.name as program_name,
                    programs.start_date as program_start_date,
                    programs.end_date as program_end_date,
                    programs.theme as program_theme,
                    MAX(CASE WHEN e.rn = 1 THEN e.answer END) AS question_1,
                    MAX(CASE WHEN e.rn = 2 THEN e.answer END) AS question_2,
                    MAX(CASE WHEN e.rn = 3 THEN e.answer END) AS question_3,
                    MAX(CASE WHEN e.rn = 4 THEN e.answer END) AS question_4,
                    MAX(CASE WHEN e.rn = 5 THEN e.answer END) AS question_5,
                    MAX(CASE WHEN e.rn = 6 THEN e.answer END) AS question_6,
                    MAX(CASE WHEN e.rn = 7 THEN e.answer END) AS question_7,
                    MAX(CASE WHEN e.rn = 8 THEN e.answer END) AS question_8,
                    MAX(CASE WHEN e.rn = 9 THEN e.answer END) AS question_9,
                    MAX(CASE WHEN e.rn = 10 THEN e.answer END) AS question_10
                ');
                log_message('info', 'Participant export: Using basic SELECT with essays but without status fields');
            }
            
            // Add JOINs only once
            $builder->join('users', 'users.id = participants.user_id', 'left')
                   ->join('programs', 'programs.id = participants.program_id', 'left');
            
            // Add status join if needed
            if ($needsStatusJoin) {
                $builder->join('participant_statuses ps', 'ps.participant_id = participants.id', 'left');
                log_message('info', 'Participant export: Added join with participant_statuses table for comprehensive status data');
            }
            
            // CRITICAL: Apply program filter FIRST - this is mandatory
            if (!isset($filters['program_id']) || empty($filters['program_id'])) {
                throw new \RuntimeException('Program ID filter is required for participant export');
            }
            
            $builder->where('participants.program_id', $filters['program_id']);
            log_message('info', 'Participant export: Filtering by program_id = ' . $filters['program_id']);
            
            // Add essay join using subquery approach (always include essays for the specific program)
            $essaySubquery = "(
                SELECT 
                    pae.participant_id,
                    pae.answer,
                    ROW_NUMBER() OVER (PARTITION BY pae.participant_id ORDER BY pae.id) AS rn
                FROM participant_essays pae
                JOIN program_essays pe 
                    ON pe.id = pae.program_essay_id 
                    AND pe.program_id = {$filters['program_id']}
                    AND pe.is_deleted = 0
                WHERE pae.is_deleted = 0
            )";
            
            $builder->join("{$essaySubquery} e", 'e.participant_id = participants.id', 'left');
            log_message('info', "Participant export: Added essays join for program {$filters['program_id']}");
            
            // Add GROUP BY clause since we're using MAX() functions for essays
            $builder->groupBy([
                'participants.id', 'participants.user_id', 'participants.account_id', 'participants.full_name',
                'participants.gender', 'participants.birthdate', 'participants.nationality', 'participants.nationality_flag',
                'participants.nationality_code', 'participants.occupation', 'participants.education_level',
                'participants.major', 'participants.institution', 'participants.organizations', 'participants.phone_number',
                'participants.country_code', 'participants.phone_flag', 'participants.current_address', 'participants.origin_address',
                'participants.picture_url', 'participants.instagram_account', 'participants.emergency_account',
                'participants.emergency_country_code', 'participants.emergency_phone_flag', 'participants.contact_relation', 'participants.disease_history', 'participants.tshirt_size',
                'participants.category', 'participants.experiences', 'participants.achievements', 'participants.resume_url',
                'participants.knowledge_source', 'participants.source_account_name', 'participants.twibbon_link',
                'participants.requirement_link', 'participants.ref_code_ambassador', 'participants.program_id',
                'participants.is_active', 'participants.is_deleted', 'participants.created_at', 'participants.updated_at',
                'users.email', 'users.full_name', 'users.is_verified', 'users.created_at',
                'programs.name', 'programs.start_date', 'programs.end_date', 'programs.theme'
            ]);
            
            // Add status fields to GROUP BY if needed
            if ($needsStatusJoin) {
                $builder->groupBy([
                    'ps.form_status', 'ps.payment_status', 'ps.general_status', 'ps.document_status'
                ]);
            }
            
            // Apply other filters as needed
            if (isset($filters['status'])) {
                $builder->where('participants.status', $filters['status']);
                log_message('info', 'Participant export: Filtering by status = ' . $filters['status']);
            }
            
            if (isset($filters['payment_status'])) {
                if ($needsStatusJoin) {
                    $builder->where('ps.payment_status', $filters['payment_status']);
                    log_message('info', 'Participant export: Filtering by payment_status = ' . $filters['payment_status']);
                } else {
                    log_message('warning', 'Participant export: payment_status filter ignored - requires participant_statuses join');
                }
            }
            
            if (isset($filters['category'])) {
                $builder->where('participants.category', $filters['category']);
                log_message('info', 'Participant export: Filtering by category = ' . $filters['category']);
            }
            
            if (isset($filters['form_status'])) {
                if ($needsStatusJoin) {
                    $builder->where('ps.form_status', $filters['form_status']);
                    log_message('info', 'Participant export: Filtering by form_status = ' . $filters['form_status']);
                } else {
                    log_message('warning', 'Participant export: form_status filter ignored - requires participant_statuses join');
                }
            }
            
            if (isset($filters['general_status'])) {
                if ($needsStatusJoin) {
                    $builder->where('ps.general_status', $filters['general_status']);
                    log_message('info', 'Participant export: Filtering by general_status = ' . $filters['general_status']);
                } else {
                    log_message('warning', 'Participant export: general_status filter ignored - requires participant_statuses join');
                }
            }
            
            if (isset($filters['date_from'])) {
                $builder->where('participants.created_at >=', $filters['date_from']);
                log_message('info', 'Participant export: Filtering by date_from = ' . $filters['date_from']);
            }
            
            if (isset($filters['date_to'])) {
                $builder->where('participants.created_at <=', $filters['date_to']);
                log_message('info', 'Participant export: Filtering by date_to = ' . $filters['date_to']);
            }
            
            // Handle payment status filter (only paid participants)
            if (isset($filters['payment_status']) && $filters['payment_status'] === 'success') {
                $subQuery = $db->table('payments')
                    ->select('participant_id')
                    ->where('status', 2)
                    ->where('is_deleted', 0);
                $builder->whereIn('participants.id', $subQuery);
                log_message('info', 'Participant export: Filtering by payment_status = success (only paid participants)');
            }
            
            // Handle specific program payment filter
            if (isset($filters['program_payment_id']) && !empty($filters['program_payment_id']) && is_numeric($filters['program_payment_id'])) {
                $subQuery = $db->table('payments')
                    ->select('participant_id')
                    ->where('program_payment_id', $filters['program_payment_id'])
                    ->where('status', 2)
                    ->where('is_deleted', 0);
                $builder->whereIn('participants.id', $subQuery);
                log_message('info', 'Participant export: Filtering by program_payment_id = ' . $filters['program_payment_id']);
            }
            
            // Handle limit filter - apply before count to get accurate total for limited export
            $limitCount = null;
            if (isset($filters['limit']) && !empty($filters['limit']) && is_numeric($filters['limit'])) {
                $limitCount = (int)$filters['limit'];
                log_message('info', 'Participant export: Applying limit = ' . $limitCount);
            }
            
            // Get total count first with all filters applied
            // For GROUP BY queries, we need to count distinct participants
            $countBuilder = clone $builder;
            $countBuilder->select('COUNT(DISTINCT participants.id) as total_count');
            $countResult = $countBuilder->get()->getRowArray();
            $totalCount = $countResult['total_count'] ?? 0;
            
            // If limit is specified and less than total count, use the limit
            if ($limitCount !== null && $limitCount < $totalCount) {
                $totalCount = $limitCount;
                log_message('info', "Participant export: Limited to {$limitCount} records (from {$countResult['total_count']} total available)");
            }
            
            log_message('info', "Starting participant export for program {$filters['program_id']}: $totalCount records found");
            log_message('info', "Found $totalCount records for program {$filters['program_id']}");
            
            // Sanity check - if we have too many records, something is wrong with filtering
            if ($totalCount > 50000) {
                log_message('error', "WARNING: Participant export found $totalCount records for program {$filters['program_id']} - this seems too high, please verify program filter is working");
            }
            
            // If dataset is small or limited, use direct query
            if ($totalCount <= 1000) {
                // Apply limit if specified
                if ($limitCount !== null) {
                    $builder->limit($limitCount);
                }
                $result = $builder->get()->getResultArray();
                log_message('info', "Completed small participant export with essays: " . count($result) . " records");
                
                // Clean data for Excel compatibility before returning
                $result = $this->_cleanDataForExcel($result);
                log_message('info', "Applied Excel data cleaning for small export: " . count($result) . " records");
                
                return $result;
            }
            
            // For large datasets, use chunked processing
            // Note: With GROUP BY and essays, we need to be more careful with chunking
            $chunkSize = 500; // Smaller chunks for better memory management
            $allData = [];
            
            // Apply limit to total count for chunked processing
            $actualTotalCount = $limitCount !== null ? min($limitCount, $totalCount) : $totalCount;
            
            // For GROUP BY queries, we need to use LIMIT with OFFSET differently
            for ($offset = 0; $offset < $actualTotalCount; $offset += $chunkSize) {
                try {
                    // Calculate chunk size for this iteration (handle limit)
                    $currentChunkSize = min($chunkSize, $actualTotalCount - $offset);
                    
                    // Reconnect database for each chunk to prevent timeout
                    $db->reconnect();
                    
                    // Clone builder and apply limit/offset
                    $chunkBuilder = clone $builder;
                    $chunkData = $chunkBuilder->limit($currentChunkSize, $offset)->get()->getResultArray();
                    $allData = array_merge($allData, $chunkData);
                    
                    // Log progress
                    log_message('info', "Processed " . count($allData) . " of $actualTotalCount participants with essays for program {$filters['program_id']}");
                    
                    // Small delay to prevent overwhelming the database
                    usleep(100000); // 0.1 second
                    
                } catch (\Exception $chunkError) {
                    log_message('error', "Error processing chunk at offset $offset: " . $chunkError->getMessage());
                    // Try to reconnect and continue
                    $db->reconnect();
                    throw $chunkError;
                }
            }
            
            log_message('info', "Completed participant export with essays for program {$filters['program_id']}: " . count($allData) . " records");
            
            // Clean data for Excel compatibility before returning
            $allData = $this->_cleanDataForExcel($allData);
            log_message('info', "Applied Excel data cleaning for chunked export: " . count($allData) . " records");
            
            return $allData;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting participants data: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve participants data: ' . $e->getMessage());
        }
    }

    /**
     * Get payments data based on filters
     */
    private function _getPaymentsData(array $filters): array
    {
        try {
            // Use export database connection with extended timeout
            $db = \Config\Database::connect('export');
            
            $builder = $this->paymentModel->builder();
            
            // Comprehensive SELECT for payment export with properly ordered fields
            $builder->select('
                payments.id as payment_id,
                payments.participant_id,
                payments.transaction_code,
                payments.order_id,
                payments.payment_date,
                payments.status as payment_status,
                payments.amount as payment_amount,
                payments.usd_amount as payment_usd_amount,
                payments.currency as payment_currency,
                payments.proof_url as payment_proof_url,
                payments.account_name as payment_account_name,
                payments.source_name as payment_source_name,
                payments.payment_url,
                payments.notes as payment_notes,
                payments.rejection_reason as payment_rejection_reason,
                payments.is_active as payment_is_active,
                payments.created_at as payment_created_at,
                payments.updated_at as payment_updated_at,
                participants.full_name as participant_full_name,
                participants.account_id as participant_account_id,
                participants.gender as participant_gender,
                participants.birthdate as participant_birthdate,
                participants.nationality as participant_nationality,
                participants.nationality_code as participant_nationality_code,
                participants.phone_number as participant_phone,
                participants.country_code as participant_country_code,
                participants.category as participant_category,
                participants.occupation as participant_occupation,
                participants.education_level as participant_education_level,
                participants.institution as participant_institution,
                participants.current_address as participant_current_address,
                participants.instagram_account as participant_instagram,
                participants.tshirt_size as participant_tshirt_size,
                participants.created_at as participant_registered_at,
                users.email as participant_email,
                users.full_name as user_full_name,
                users.is_verified as user_is_verified,
                users.created_at as user_created_at,
                programs.name as program_name,
                programs.start_date as program_start_date,
                programs.end_date as program_end_date,
                programs.theme as program_theme,
                pp.name as program_payment_name,
                pp.description as program_payment_description,
                pp.idr_amount as program_payment_idr_amount,
                pp.usd_amount as program_payment_usd_amount,
                pp.category as program_payment_category,
                pp.type as program_payment_type,
                pp.start_date as program_payment_start_date,
                pp.end_date as program_payment_end_date,
                pp.order_number as program_payment_order,
                pm.name as payment_method_name,
                pm.type as payment_method_type,
                pm.description as payment_method_description
            ')
            ->join('participants', 'participants.id = payments.participant_id', 'left')
            ->join('users', 'users.id = participants.user_id', 'left')
            ->join('programs', 'programs.id = participants.program_id', 'left')
            ->join('program_payments pp', 'pp.id = payments.program_payment_id', 'left')
            ->join('payment_methods pm', 'pm.id = payments.payment_method_id', 'left');
            
            log_message('info', 'Payment export: Added comprehensive joins with participants, users, programs, program_payments, and payment_methods tables');
            
            // CRITICAL: Apply program filter FIRST - this is mandatory
            if (!isset($filters['program_id']) || empty($filters['program_id'])) {
                throw new \RuntimeException('Program ID filter is required for payment export');
            }
            
            $builder->where('participants.program_id', $filters['program_id']);
            log_message('info', 'Payment export: Filtering by program_id = ' . $filters['program_id']);
            
            // Ensure we only get non-deleted records
            $builder->where('payments.is_deleted', 0)
                   ->where('participants.is_deleted', 0);
            
            // Apply other filters as needed
            if (isset($filters['status'])) {
                $builder->where('payments.status', $filters['status']);
                log_message('info', 'Payment export: Filtering by payment status = ' . $filters['status']);
            }
            
            if (isset($filters['payment_status'])) {
                $builder->where('payments.status', $filters['payment_status']);
                log_message('info', 'Payment export: Filtering by payment_status = ' . $filters['payment_status']);
            }
            
            if (isset($filters['payment_method_id'])) {
                $builder->where('payments.payment_method_id', $filters['payment_method_id']);
                log_message('info', 'Payment export: Filtering by payment_method_id = ' . $filters['payment_method_id']);
            }
            
            if (isset($filters['currency'])) {
                $builder->where('payments.currency', $filters['currency']);
                log_message('info', 'Payment export: Filtering by currency = ' . $filters['currency']);
            }
            
            if (isset($filters['participant_category'])) {
                $builder->where('participants.category', $filters['participant_category']);
                log_message('info', 'Payment export: Filtering by participant category = ' . $filters['participant_category']);
            }
            
            if (isset($filters['date_from'])) {
                $builder->where('payments.created_at >=', $filters['date_from']);
                log_message('info', 'Payment export: Filtering by date_from = ' . $filters['date_from']);
            }
            
            if (isset($filters['date_to'])) {
                $builder->where('payments.created_at <=', $filters['date_to']);
                log_message('info', 'Payment export: Filtering by date_to = ' . $filters['date_to']);
            }
            
            if (isset($filters['payment_date_from'])) {
                $builder->where('payments.payment_date >=', $filters['payment_date_from']);
                log_message('info', 'Payment export: Filtering by payment_date_from = ' . $filters['payment_date_from']);
            }
            
            if (isset($filters['payment_date_to'])) {
                $builder->where('payments.payment_date <=', $filters['payment_date_to']);
                log_message('info', 'Payment export: Filtering by payment_date_to = ' . $filters['payment_date_to']);
            }
            
            // Add order by payment date (most recent first) for better organization
            $builder->orderBy('payments.payment_date', 'DESC')
                   ->orderBy('payments.created_at', 'DESC');
            
            // Get total count first
            $countBuilder = clone $builder;
            $countBuilder->select('COUNT(payments.id) as total_count');
            $countResult = $countBuilder->get()->getRowArray();
            $totalCount = $countResult['total_count'] ?? 0;
            
            log_message('info', "Starting payment export for program {$filters['program_id']}: $totalCount records found");
            
            // Sanity check - if we have too many records, log a warning
            if ($totalCount > 20000) {
                log_message('warning', "Payment export found $totalCount records for program {$filters['program_id']} - this is a large dataset");
            }
            
            // If dataset is small, use direct query
            if ($totalCount <= 1000) {
                $result = $builder->get()->getResultArray();
                log_message('info', "Completed small payment export: " . count($result) . " records");
                return $result;
            }
            
            // For large datasets, use chunked processing
            $chunkSize = 1000; // Larger chunks for payments since they're simpler than participants with essays
            $allData = [];
            
            for ($offset = 0; $offset < $totalCount; $offset += $chunkSize) {
                try {
                    // Reconnect database for each chunk to prevent timeout
                    $db->reconnect();
                    
                    // Clone builder and apply limit/offset
                    $chunkBuilder = clone $builder;
                    $chunkData = $chunkBuilder->limit($chunkSize, $offset)->get()->getResultArray();
                    $allData = array_merge($allData, $chunkData);
                    
                    // Log progress
                    log_message('info', "Processed " . count($allData) . " of $totalCount payments for program {$filters['program_id']}");
                    
                    // Small delay to prevent overwhelming the database
                    usleep(50000); // 0.05 second - shorter delay for payments
                    
                } catch (\Exception $chunkError) {
                    log_message('error', "Error processing payment chunk at offset $offset: " . $chunkError->getMessage());
                    // Try to reconnect and continue
                    $db->reconnect();
                    throw $chunkError;
                }
            }
            
            log_message('info', "Completed payment export for program {$filters['program_id']}: " . count($allData) . " records");
            return $allData;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting payments data: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve payments data: ' . $e->getMessage());
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
     * Log export request for tracking
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
}
