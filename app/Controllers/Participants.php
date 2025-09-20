<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use App\Models\UserModel;
use App\Models\ProgramModel;
use App\Models\PaymentModel;
use App\Models\ParticipantEssayModel;
use App\Models\ParticipantStatusModel;
use App\Models\ParticipantSubthemeModel;
use App\Libraries\YbbExport;

class Participants extends AdminBaseController
{
    protected $participantModel;
    protected $userModel;
    protected $programModel;
    protected $paymentModel;
    protected $participantEssayModel;
    protected $participantStatusModel;
    protected $participantSubthemeModel;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
        $this->userModel = new UserModel();
        $this->programModel = new ProgramModel();
        $this->paymentModel = new PaymentModel();
        $this->participantEssayModel = new ParticipantEssayModel();
        $this->participantStatusModel = new ParticipantStatusModel();
        $this->participantSubthemeModel = new ParticipantSubthemeModel();
    }

    public function index()
    {
        try {
            // Get program for stats
            $programId = session('current_program');
            $program = $this->programModel->find($programId);

            // Get participant stats
            $stats = $this->participantModel->getParticipantStats($programId);

            // Get topbar data from session (already loaded by AdminBaseController)
            $topbarData = $this->session->get('topbar_data', []);

            $data = [
                'program' => $program,
                'stats' => $stats,
                'topbarData' => $topbarData
            ];

            return view('users/participants/index', $data);
        } catch (\Exception $e) {
            // Handle exception and redirect with error message
            log_message('error', 'Failed to fetch participants: ' . $e->getMessage());
            // return redirect()->back()->with('error', 'Failed to fetch participants: ' . $e->getMessage());
        }
    }

    /**
     * Get participants data for DataTables
     */
    public function getData()
    {
        // Process DataTables server-side request
        $request = $this->request->getGet();

        // Get program ID - use consistent session key
        $programId = $request['program_id'] ?? session('current_program');
        
        if (!$programId) {
            return $this->response->setJSON([
                'draw' => intval($request['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'No program selected'
            ])->setStatusCode(400);
        }

        try {
            // Prepare DataTable parameters for the model
            $dataTableParams = [
                'draw' => $request['draw'] ?? 1,
                'start' => intval($request['start'] ?? 0),
                'length' => intval($request['length'] ?? 10),
                'search' => $request['search']['value'] ?? '',
                'order' => [
                    'column' => intval($request['order'][0]['column'] ?? 4),
                    'dir' => $request['order'][0]['dir'] ?? 'desc'
                ],
                'program_id' => $programId,
                'category' => $request['category'] ?? null,
                'form_status' => $request['form_status'] ?? null
            ];

            // Log request for debugging
            log_message('debug', 'Participants DataTable request: ' . json_encode([
                'program_id' => $programId,
                'filters' => [
                    'category' => $dataTableParams['category'],
                    'form_status' => $dataTableParams['form_status'],
                    'search' => $dataTableParams['search']
                ]
            ]));

            // Get data from model using optimized query
            $result = $this->participantModel->getDataTableData($dataTableParams);

            // Log successful response
            log_message('debug', 'Participants DataTable response: ' . json_encode([
                'total_records' => $result['recordsTotal'],
                'filtered_records' => $result['recordsFiltered'],
                'data_count' => count($result['data'])
            ]));

            return $this->response->setJSON($result);

        } catch (\Exception $e) {
            // Log error
            log_message('error', 'Participants getData error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'draw' => intval($request['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load participant data',
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get HTML for category badge
     */
    private function getCategoryBadge($category)
    {
        $category = strtolower($category);
        $badges = [
            'fully_funded' => '<span class="badge bg-success-subtle text-success">Fully Funded</span>',
            'self_funded' => '<span class="badge bg-warning-subtle text-warning">Self Funded</span>',
        ];

        return $badges[$category] ?? '<span class="badge bg-secondary-subtle text-secondary">Unknown</span>';
    }

    public function view($id)
    {
        try {
            // Get participant data directly from model
            $participant = $this->participantModel->find($id);

            if (!$participant) {
                // debug
                log_message('error', 'Failed to retrieve participant: ' . $id);
                return redirect()->to('/users/participants')->with('error', 'Participant not found');
            }

            // Get related data
            $userId = $participant->user_id;

            // Get user data
            $user = $this->userModel->find($userId);
            $participant->user = $user;

            // Get participant essays
            $essays = $this->participantEssayModel->getParticipantEssayByParticipantId($id);
            $participant->essays = $essays;            
            
            // Get payment information
            $payments = $this->paymentModel->getPaymentsByParticipantId($id);
            $participant->payments = $payments;

            // get participant subtheme data with subtheme name
            $participantSubtheme = $this->participantSubthemeModel
                ->select('participant_subthemes.*, program_subthemes.name as subtheme_name')
                ->join('program_subthemes', 'program_subthemes.id = participant_subthemes.program_subtheme_id', 'left')
                ->where('participant_subthemes.participant_id', $id)
                ->where('participant_subthemes.is_deleted', 0)
                ->first();
            
            $participant->subtheme = $participantSubtheme;

            return view('users/participants/view', ['participant' => $participant]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to retrieve participant: ' . $id);
            return redirect()->to('/users/participants')->with('error', 'Failed to retrieve participant: ' . $e->getMessage());
        }
    }

    /**
     * Create a new participant form
     */
    public function new()
    {
        return view('users/participants/create');
    }
    /**
     * Create a new participant (process the form)
     */
    public function create()
    {
        try {
            // Get form data
            $data = $this->request->getPost();

            // Validate required fields
            $validation = \Config\Services::validation();
            $validation->setRules([
                'user_id' => 'required|integer',
                'program_id' => 'required|integer',
                'full_name' => 'required|string|max_length[255]'
            ]);

            if (!$validation->run($data)) {
                return redirect()->back()
                    ->with('error', 'Validation failed: ' . implode(', ', $validation->getErrors()))
                    ->withInput();
            }

            // Create new participant
            $participant = $this->participantModel->createParticipant($data);

            if ($participant) {
                return redirect()->to('/participants')
                    ->with('success', 'Participant created successfully');
            } else {
                return redirect()->back()
                    ->with('error', 'Failed to create participant')
                    ->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating participant: ' . $e->getMessage())
                ->withInput();
        }
    }
    /**
     * Edit participant form
     */
    public function edit($id)
    {
        try {
            // Get participant data directly from model
            $participant = $this->participantModel->getById($id);

            if (!$participant) {
                return redirect()->to('/participants')
                    ->with('error', 'Participant not found');
            }

            // Get user data
            $userId = $participant['user_id'];
            $user = $this->userModel->find($userId);
            $participant['user'] = $user;

            return view('users/participants/edit', ['participant' => $participant]);
        } catch (\Exception $e) {
            return redirect()->to('/participants')
                ->with('error', 'Failed to retrieve participant data: ' . $e->getMessage());
        }
    }
    /**
     * Update participant (process the form)
     */
    public function update($id)
    {
        try {
            // Check if participant exists
            $participant = $this->participantModel->find($id);

            if (!$participant) {
                return redirect()->to('/participants')
                    ->with('error', 'Participant not found');
            }

            // Get form data
            $data = $this->request->getPost();

            // Validate data
            $validation = \Config\Services::validation();
            $validation->setRules([
                'full_name' => 'required|string|max_length[255]',
                'program_id' => 'required|integer',
            ]);

            if (!$validation->run($data)) {
                return redirect()->back()
                    ->with('error', 'Validation failed: ' . implode(', ', $validation->getErrors()))
                    ->withInput();
            }

            // Update participant
            $this->participantModel->update($id, $data);

            return redirect()->to('/participants')
                ->with('success', 'Participant updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating participant: ' . $e->getMessage())
                ->withInput();
        }
    }
    /**
     * Delete participant
     */
    public function delete($id)
    {
        try {
            // Check if participant exists
            $participant = $this->participantModel->find($id);

            if (!$participant) {
                return redirect()->to('/participants')
                    ->with('error', 'Participant not found');
            }

            // Soft delete by updating is_deleted field
            $this->participantModel->update($id, [
                'is_deleted' => 1,
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->to('/participants')
                ->with('success', 'Participant deleted successfully');
        } catch (\Exception $e) {
            return redirect()->to('/participants')
                ->with('error', 'Error deleting participant: ' . $e->getMessage());
        }
    }
    /**
     * Get participants for a specific program
     */
    public function byProgram($programId)
    {
        $page = (int)($this->request->uri->getQuery(['only' => ['page']]) ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            // Check if program exists
            $program = $this->programModel->find($programId);

            if (!$program) {
                return redirect()->to('/participants')
                    ->with('error', 'Program not found');
            }

            // Use model to get participants by program ID
            $result = $this->participantModel->getParticipants($limit, $offset, ['program_id' => $programId]);

            $data = [
                'participants' => $result,
                'pager' => [
                    'total' => $result['total'] ?? 0,
                    'perPage' => $limit,
                    'currentPage' => $page,
                    'totalPages' => ceil(($result['total'] ?? 0) / $limit)
                ],
                'programId' => $programId
            ];

            return view('users/participants/program', $data);
        } catch (\Exception $e) {
            return redirect()->to('/participants')
                ->with('error', 'Failed to fetch program participants: ' . $e->getMessage());
        }
    }
    /**
     * Get HTML for submission status badge
     */
    private function getSubmissionStatusBadge($generalStatus, $formStatus, $documentStatus)
    {
        // Status values: 0 = not started, 1 = on progress, 2 = submitted

        $generalStatusMap = [
            0 => ['Not Started', 'secondary'],
            1 => ['In Progress', 'warning'],
            2 => ['Completed', 'success']
        ];

        $formStatusMap = [
            0 => ['Not Started', 'secondary'],
            1 => ['On Progress', 'warning'],
            2 => ['Submitted', 'success']
        ];

        $documentStatusMap = [
            0 => ['Not Started', 'secondary'],
            1 => ['In Progress', 'warning'],
            2 => ['Submitted', 'success']
        ];

        $generalStatusInfo = $generalStatusMap[$generalStatus] ?? $generalStatusMap[0];
        $formStatusInfo = $formStatusMap[$formStatus] ?? $formStatusMap[0];
        $documentStatusInfo = $documentStatusMap[$documentStatus] ?? $documentStatusMap[0];

        $output = '';

        // General status badge
        $output .= '<div class="mb-1"><span class="fw-medium">General:</span> ';
        $output .= '<span class="badge bg-' . $generalStatusInfo[1] . '-subtle text-' . $generalStatusInfo[1] . '">' . $generalStatusInfo[0] . '</span></div>';

        // Form status badge
        $output .= '<div class="mb-1"><span class="fw-medium">Form:</span> ';
        $output .= '<span class="badge bg-' . $formStatusInfo[1] . '-subtle text-' . $formStatusInfo[1] . '">' . $formStatusInfo[0] . '</span></div>';

        // Document status badge
        $output .= '<div class="mb-1"><span class="fw-medium">Documents:</span> ';
        $output .= '<span class="badge bg-' . $documentStatusInfo[1] . '-subtle text-' . $documentStatusInfo[1] . '">' . $documentStatusInfo[0] . '</span></div>';

        return $output;
    }
    /**
     * Get HTML badge for form status only
     */
    /**
     * Export participants data using YBB Export API
     */
    public function export($id = null)
    {
        try {
            // Increase execution time limit for large exports
            set_time_limit(300); // 5 minutes
            ini_set('memory_limit', '512M'); // Increase memory limit
            
            log_message('debug', 'Starting participant export process with YBB Export API');

            $programId = session('current_program');
            if (!$programId) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No program selected'
                    ]);
                }
                return redirect()->to('/users/participants')->with('error', 'No program selected');
            }

            $participants = [];

            // If ID is provided, export just that participant
            if ($id) {
                log_message('debug', 'Exporting single participant with ID: ' . $id);
                $participant = $this->participantModel->find($id);

                if (!$participant) {
                    log_message('error', 'Participant not found for export, ID: ' . $id);
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Participant not found'
                        ]);
                    }
                    return redirect()->to('/users/participants')->with('error', 'Participant not found');
                }

                // Get related data
                $userId = $participant->user_id;
                $user = $this->userModel->find($userId);
                $participant->user = $user;
                $participant->email = $user->email ?? '';

                // Get participant essays
                $essays = $this->participantEssayModel->getParticipantEssayByParticipantId($id);
                $participant->essays = $essays;

                $participants[] = (array)$participant;
            } else {
                log_message('debug', 'Starting bulk participant export for program ID: ' . $programId);
                
                // Check if this is a batch size check request
                $checkBatchSize = $this->request->getGet('check_batch_size') !== null;
                
                if ($checkBatchSize) {
                    // Just count the records and return batch information
                    $totalCount = $this->getParticipantCount($programId);
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'total_records' => $totalCount,
                        'message' => "Found {$totalCount} records ready for export."
                    ]);
                }

                // Get participants data
                $participantObjects = $this->getParticipantsForExport($programId);

                if (empty($participantObjects)) {
                    if ($this->request->isAJAX()) {
                        return $this->response->setStatusCode(404)
                            ->setJSON(['success' => false, 'message' => 'No participants found to export']);
                    }
                    return redirect()->to('/users/participants')->with('error', 'No participants found to export');
                }

                // Memory management for large datasets
                $memoryUsage = memory_get_usage(true);
                $memoryLimit = $this->_getMemoryLimitBytes();
                $memoryUsageMB = round($memoryUsage / 1024 / 1024, 2);
                $memoryLimitMB = round($memoryLimit / 1024 / 1024, 2);
                
                log_message('debug', "Memory usage after data retrieval: {$memoryUsageMB}MB / {$memoryLimitMB}MB (" . round(($memoryUsage / $memoryLimit) * 100, 1) . "%)");
                
                // If memory usage is over 60%, force aggressive chunking
                if (($memoryUsage / $memoryLimit) > 0.6) {
                    log_message('warning', "High memory usage detected. Forcing small chunk processing to prevent exhaustion.");
                    $maxChunkSize = 1000; // Very small chunks for high memory pressure
                } else {
                    $maxChunkSize = 2500; // Normal chunk size
                }

                // Convert objects to arrays for API
                foreach ($participantObjects as $participant) {
                    $participants[] = (array)$participant;
                }
            }

            log_message('debug', 'Preparing to export ' . count($participants) . ' participants via YBB Export API');

            // Prepare export options according to YBB API documentation
            $options = [
                'template' => 'standard', // Valid options: standard, detailed, summary, complete
                'format' => 'excel',
                'filename' => $this->generateExportFilename('participants', $programId),
                'sheet_name' => 'YBB_Participants_' . date('Y')
            ];

            // Add filter info to options
            $filters = $this->getExportFilters();
            if (!empty($filters)) {
                $options['filters'] = $filters;
            }

            // Handle large datasets with memory-efficient chunking
            $participantCount = count($participants);
            
            // More aggressive chunking for production datasets
            if ($participantCount > $maxChunkSize) {
                log_message('debug', "Large dataset detected ({$participantCount} participants). Processing in chunks of {$maxChunkSize}");
                
                // For very large datasets, process even smaller chunks to be absolutely safe
                $safeChunkSize = min($maxChunkSize, 1500); // Even more conservative for production
                $chunks = array_chunk($participants, $safeChunkSize);
                $totalChunks = count($chunks);
                
                log_message('debug', "Split dataset into {$totalChunks} chunks of {$safeChunkSize} participants each for memory-safe processing");
                
                // Process only the first chunk - let the API service handle the rest
                $ybbExport = new YbbExport();
                $options['force_chunking'] = true;
                $options['chunk_size'] = $safeChunkSize;
                $options['total_chunks'] = $totalChunks;
                $options['total_records'] = $participantCount;
                $options['is_multi_chunk_export'] = true;
                
                log_message('info', "Processing first chunk of {$safeChunkSize} participants out of {$participantCount} total");
                
                $result = $ybbExport->exportParticipants($chunks[0], $options);
                
                if ($result['success']) {
                    // Store information about the chunked export
                    $exportId = $result['data']['export_id'] ?? null;
                    
                    if ($totalChunks > 1 && $exportId) {
                        log_message('info', "Multi-chunk export initiated with export ID: {$exportId}. Processing {$totalChunks} chunks of {$safeChunkSize} participants each.");
                        
                        // Modify the response to indicate this is a chunked export
                        if (isset($result['data'])) {
                            $result['data']['is_chunked_export'] = true;
                            $result['data']['total_chunks'] = $totalChunks;
                            $result['data']['chunk_size'] = $safeChunkSize;
                            $result['data']['total_records'] = $participantCount;
                        }
                    }
                }
            } else {
                // For smaller datasets, use standard processing with minimal chunking
                if ($participantCount > 500) {
                    $options['force_chunking'] = true;
                    $options['chunk_size'] = min(1000, floor($participantCount / 2));
                    log_message('debug', "Medium dataset detected ({$participantCount} participants). Enabling chunking with chunk size: {$options['chunk_size']}");
                }

                // Create export using YBB Export API
                $ybbExport = new YbbExport();
                $result = $ybbExport->exportParticipants($participants, $options);
            }

            if ($result['success']) {
                log_message('info', 'Participants export initiated successfully: ' . json_encode($result));
                
                // Extract data according to YBB API documentation structure
                $exportData = $result['data'] ?? [];
                $metadata = $result['metadata'] ?? [];
                
                // Build response matching the frontend expectations
                $response = [
                    'success' => true,
                    'exportId' => $exportData['export_id'], // Frontend looks for this at root level
                    'export_id' => $exportData['export_id'], // Backup field name
                    'message' => 'Export initiated successfully',
                    'recordCount' => $exportData['record_count'] ?? count($participants),
                    'record_count' => $exportData['record_count'] ?? count($participants), // Backup field name
                    
                    // File information from API response
                    'fileName' => $exportData['file_name'] ?? null,
                    'file_name' => $exportData['file_name'] ?? null, // Backup field name
                    'fileSize' => $exportData['file_size'] ?? null,
                    'file_size' => $exportData['file_size'] ?? null, // Backup field name
                    'fileSizeMB' => $exportData['file_size_mb'] ?? null,
                    'file_size_mb' => $exportData['file_size_mb'] ?? null, // Backup field name
                    'downloadUrl' => $exportData['download_url'] ?? null,
                    'download_url' => $exportData['download_url'] ?? null, // Backup field name
                    'exportStrategy' => $exportData['export_strategy'] ?? 'single_file',
                    'export_strategy' => $exportData['export_strategy'] ?? 'single_file', // Backup field name
                    
                    // Performance metrics if available
                    'performanceMetrics' => $exportData['performance_metrics'] ?? null,
                    'performance_metrics' => $exportData['performance_metrics'] ?? null, // Backup field name
                    
                    // Multi-file export information
                    'fileCount' => $exportData['file_count'] ?? 1,
                    'file_count' => $exportData['file_count'] ?? 1, // Backup field name
                    'batchFiles' => $exportData['batch_files'] ?? null,
                    'batch_files' => $exportData['batch_files'] ?? null, // Backup field name
                    'zipDownloadUrl' => $exportData['zip_download_url'] ?? null,
                    'zip_download_url' => $exportData['zip_download_url'] ?? null, // Backup field name
                    
                    // Pass through all metadata for enhanced metrics display
                    'metadata' => $metadata,
                    
                    // Add data object for frontend compatibility - this preserves the original structure
                    'data' => $exportData
                ];
                
                // Extract and enhance performance metrics from both data and metadata
                $performanceMetrics = $exportData['performance_metrics'] ?? [];
                
                // Add comprehensive metrics to root level for frontend access
                if (!empty($performanceMetrics)) {
                    $response['processingTimeMs'] = $performanceMetrics['processing_time_ms'] ?? null;
                    $response['processingTimeSeconds'] = $performanceMetrics['total_processing_time_seconds'] ?? null;
                    $response['recordsPerSecond'] = $performanceMetrics['records_per_second'] ?? null;
                    $response['memoryUsedMb'] = $performanceMetrics['memory_used_mb'] ?? null;
                    $response['peakMemoryMb'] = $performanceMetrics['peak_memory_mb'] ?? null;
                    
                    // Efficiency metrics
                    if (isset($performanceMetrics['efficiency_metrics'])) {
                        $efficiency = $performanceMetrics['efficiency_metrics'];
                        $response['kbPerRecord'] = $efficiency['kb_per_record'] ?? null;
                        $response['memoryEfficiencyKbPerRecord'] = $efficiency['memory_efficiency_kb_per_record'] ?? null;
                        $response['processingMsPerRecord'] = $efficiency['processing_ms_per_record'] ?? null;
                    }
                }
                
                // Add metadata-level metrics as fallback
                if (!empty($metadata)) {
                    $response['processingTimeMs'] = $response['processingTimeMs'] ?? $metadata['processing_time_ms'] ?? null;
                    $response['recordsPerSecond'] = $response['recordsPerSecond'] ?? $metadata['records_per_second'] ?? null;
                    $response['memoryUsedMb'] = $response['memoryUsedMb'] ?? $metadata['memory_used_mb'] ?? null;
                    $response['peakMemoryMb'] = $response['peakMemoryMb'] ?? $metadata['peak_memory_mb'] ?? null;
                    $response['memoryEfficiency'] = $response['memoryEfficiencyKbPerRecord'] ?? $metadata['memory_efficiency_kb_per_record'] ?? null;
                    
                    // Additional metadata information
                    $response['compressionUsed'] = $metadata['compression_used'] ?? 'none';
                    $response['generatedAt'] = $metadata['generated_at'] ?? null;
                    $response['tempFilesCleanup'] = $metadata['temp_files_cleanup_scheduled'] ?? false;
                }
                
                return $this->response->setJSON($response);
            } else {
                log_message('error', 'Participants export failed: ' . $result['message']);
                
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => $result['message']
                    ]);
                }
                return redirect()->to('/users/participants')->with('error', 'Export failed: ' . $result['message']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to export participants: ' . $e->getMessage());

            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)
                    ->setJSON(['success' => false, 'message' => 'Failed to export participants: ' . $e->getMessage()]);
            }
            return redirect()->to('/users/participants')->with('error', 'Failed to export participants: ' . $e->getMessage());
        }
    }
    
    /**
     * Format file size for display
     */
    private function formatFileSize($bytes)
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
     * Export participants in batches (now handled by YBB Export API)
     */
    public function export_batch()
    {
        // Redirect to main export function since YBB Export API handles batching automatically
        return $this->export();
    }

    /**
     * Get participants count for export with filters
     */
    private function getParticipantCount($programId)
    {
        $query = $this->participantModel->select('participants.id')
            ->join('users', 'users.id = participants.user_id', 'left')
            ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
            ->where('participants.program_id', $programId)
            ->where('participants.is_deleted', 0);

        // Apply filters
        $filters = $this->getExportFilters();
        $this->applyExportFilters($query, $filters);

        return $query->countAllResults();
    }

    /**
     * Get participants data for export with filters
     */
    private function getParticipantsForExport($programId, $limit = null, $offset = null)
    {
        log_message('debug', 'Starting optimized participant data retrieval for program: ' . $programId);
        
        // Build optimized query with JOINs to prevent N+1 queries
        // Note: Use participants.full_name instead of users table name fields
        // Users table only has basic info, participants table has the detailed info
        $query = $this->participantModel->select('
                participants.*, 
                users.email as user_email,
                users.created_at as user_created_at,
                users.updated_at as user_updated_at
            ')
            ->join('users', 'users.id = participants.user_id', 'left')
            ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
            ->where('participants.program_id', $programId)
            ->where('participants.is_deleted', 0);

        // Apply filters
        $filters = $this->getExportFilters();
        $this->applyExportFilters($query, $filters);

        // Apply limit and offset if provided
        if ($limit !== null) {
            $query->limit($limit, $offset ?: 0);
        }

        log_message('debug', 'Executing main participant query...');
        $participantsList = $query->get()->getResult();
        log_message('debug', 'Retrieved ' . count($participantsList) . ' participants from database');
        
        if (empty($participantsList)) {
            return [];
        }

        // Get all participant IDs for batch essay retrieval
        $participantIds = array_column($participantsList, 'id');
        
        // Batch retrieve essays for all participants to avoid N+1 queries
        log_message('debug', 'Batch retrieving essays for ' . count($participantIds) . ' participants...');
        $essaysQuery = $this->participantEssayModel
            ->whereIn('participant_id', $participantIds)
            ->get()->getResult();
        
        // Group essays by participant ID
        $essaysByParticipant = [];
        foreach ($essaysQuery as $essay) {
            $essaysByParticipant[$essay->participant_id][] = $essay;
        }
        
        log_message('debug', 'Processing participant data with essays...');
        $participants = [];
        
        // Process each participant with pre-loaded data
        foreach ($participantsList as $participant) {
            // Create user object from joined data
            // Use participants.full_name since that's where the name is stored
            $participant->user = (object) [
                'id' => $participant->user_id,
                'email' => $participant->user_email,
                'full_name' => $participant->full_name, // Use participants.full_name
                'created_at' => $participant->user_created_at,
                'updated_at' => $participant->user_updated_at
            ];
            
            // Set email for backward compatibility
            $participant->email = $participant->user_email;
            
            // Add essays if available
            $participant->essays = $essaysByParticipant[$participant->id] ?? [];
            
            // Clean up the joined user fields to avoid duplication
            unset($participant->user_email, $participant->user_created_at, $participant->user_updated_at);
            
            $participants[] = $participant;
        }
        
        log_message('debug', 'Completed processing ' . count($participants) . ' participants with related data');
        return $participants;
    }

    /**
     * Get export filters from request
     */
    private function getExportFilters()
    {
        return [
            'category' => $this->request->getGet('category') ?: $this->request->getPost('category'),
            'form_status' => $this->request->getGet('form_status') !== null ? $this->request->getGet('form_status') : $this->request->getPost('form_status'),
            'payment_status' => $this->request->getGet('payment_status') ?: $this->request->getPost('payment_status'),
            'date_range' => $this->request->getGet('date_range') ?: $this->request->getPost('date_range'),
            'program_payment_id' => $this->request->getGet('program_payment_id') ?: $this->request->getPost('program_payment_id'),
            'limit' => $this->request->getGet('limit') ?: $this->request->getPost('limit')
        ];
    }

    /**
     * Apply export filters to query
     */
    private function applyExportFilters($query, $filters)
    {
        log_message('debug', '=== APPLYING EXPORT FILTERS ===');
        log_message('debug', 'Filters to apply: ' . json_encode($filters));
        
        // Category filter
        if (!empty($filters['category'])) {
            log_message('debug', 'Applying category filter: ' . $filters['category']);
            $query->where('participants.category', $filters['category']);
        } else {
            log_message('debug', 'Category filter: EMPTY or NULL, skipping');
        }

        // Form status filter
        if ($filters['form_status'] !== '' && $filters['form_status'] !== null) {
            log_message('debug', 'Applying form_status filter: ' . $filters['form_status']);
            $query->where('participant_statuses.form_status', $filters['form_status']);
        } else {
            log_message('debug', 'Form status filter: EMPTY or NULL, skipping. Value: ' . var_export($filters['form_status'], true));
        }

        // Date range filter
        if (!empty($filters['date_range'])) {
            log_message('debug', 'Applying date_range filter: ' . $filters['date_range']);
            $dates = explode(' - ', $filters['date_range']);
            if (count($dates) == 2) {
                $startDate = date('Y-m-d', strtotime($dates[0]));
                $endDate = date('Y-m-d', strtotime($dates[1]));
                log_message('debug', 'Parsed dates - Start: ' . $startDate . ', End: ' . $endDate);
                $query->where('DATE(participants.created_at) >=', $startDate)
                    ->where('DATE(participants.created_at) <=', $endDate);
            } else {
                log_message('error', 'Invalid date range format: ' . $filters['date_range']);
            }
        } else {
            log_message('debug', 'Date range filter: EMPTY, skipping');
        }

        // Payment status filter
        if (!empty($filters['payment_status']) && $filters['payment_status'] == 'success') {
            log_message('debug', 'Applying payment_status filter: success');
            $db = \Config\Database::connect();
            $subQuery = $db->table('payments')
                ->select('participant_id')
                ->where('status', 2)
                ->where('is_deleted', 0);
            $query->whereIn('participants.id', $subQuery);
        } else {
            log_message('debug', 'Payment status filter: NOT success or EMPTY, skipping. Value: ' . var_export($filters['payment_status'], true));
        }

        // Specific program payment filter
        if (!empty($filters['program_payment_id']) && is_numeric($filters['program_payment_id'])) {
            log_message('debug', 'Applying program_payment_id filter: ' . $filters['program_payment_id']);
            $db = \Config\Database::connect();
            $subQuery = $db->table('payments')
                ->select('participant_id')
                ->where('program_payment_id', $filters['program_payment_id'])
                ->where('status', 2)
                ->where('is_deleted', 0);
            $query->whereIn('participants.id', $subQuery);
        } else {
            log_message('debug', 'Program payment ID filter: NOT numeric or EMPTY, skipping. Value: ' . var_export($filters['program_payment_id'], true));
        }

        // Limit filter
        if (!empty($filters['limit']) && is_numeric($filters['limit'])) {
            log_message('debug', 'Applying limit filter: ' . $filters['limit']);
            $query->limit((int)$filters['limit']);
        } else {
            log_message('debug', 'Limit filter: NOT numeric or EMPTY, skipping. Value: ' . var_export($filters['limit'], true));
        }
        
        log_message('debug', '=== END APPLYING EXPORT FILTERS ===');
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
     * Generate descriptive filename for exports
     */
    private function generateExportFilename($type, $programId = null): string
    {
        $timestamp = date('d-m-Y_H-i-s');
        $typeFormatted = ucfirst($type);
        
        if ($programId) {
            // Try to get program name for more descriptive filename
            $programModel = new \App\Models\ProgramModel();
            $program = $programModel->find($programId);
            $programName = $program ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $program->name) : "Program_{$programId}";
            return "YBB_{$programName}_{$typeFormatted}_{$timestamp}";
        }
        
        return "YBB_{$typeFormatted}_{$timestamp}";
    }

    /**
     * Check export status
     */
    public function export_status()
    {
        try {
            $exportId = $this->request->getGet('export_id');
            
            if (!$exportId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Export ID is required',
                    'error_code' => 'MISSING_EXPORT_ID'
                ]);
            }
            
            log_message('info', "Checking export status for ID: {$exportId}");
            
            // Initialize YBB Export library
            $ybbExport = new YbbExport();
            
            // Check status via YBB Export API
            $statusResult = $ybbExport->getExportStatus($exportId);
            
            if ($statusResult['success']) {
                $statusData = $statusResult['data'];
                
                // Standardize status response format
                $response = [
                    'success' => true,
                    'data' => [
                        'export_id' => $exportId,
                        'status' => $statusData['status'] ?? 'unknown',
                        'progress' => $statusData['progress'] ?? 0,
                        'records_processed' => $statusData['records_processed'] ?? 0,
                        'total_records' => $statusData['total_records'] ?? 0,
                        'created_at' => $statusData['created_at'] ?? null,
                        'updated_at' => $statusData['updated_at'] ?? null,
                        'estimated_completion' => $statusData['estimated_completion'] ?? null,
                        'file_name' => $statusData['file_name'] ?? null,
                        'file_size' => $statusData['file_size'] ?? null,
                        'download_url' => null,
                        'error_message' => $statusData['error_message'] ?? null
                    ]
                ];
                
                // If export is completed and has a file, generate download URL
                if (in_array(strtolower($statusData['status'] ?? ''), ['completed', 'ready']) && 
                    !empty($statusData['file_name'])) {
                    
                    // Generate local download URL that proxies through our controller
                    $response['data']['download_url'] = base_url("participants/download/{$exportId}");
                    $response['data']['records_exported'] = $statusData['records_processed'] ?? $statusData['total_records'] ?? 0;
                }
                
                log_message('info', "Export status retrieved successfully: " . json_encode($response['data']));
                
                return $this->response->setJSON($response);
                
            } else {
                log_message('error', "Failed to get export status: " . ($statusResult['message'] ?? 'Unknown error'));
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $statusResult['message'] ?? 'Failed to retrieve export status',
                    'error_code' => $statusResult['error_code'] ?? 'STATUS_CHECK_FAILED'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', "Exception in export_status: " . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while checking export status: ' . $e->getMessage(),
                'error_code' => 'INTERNAL_ERROR',
                'details' => [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
    }
}
