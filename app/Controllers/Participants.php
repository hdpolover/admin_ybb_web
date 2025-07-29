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

class Participants extends BaseController
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

            $data = [
                'program' => $program,
                'stats' => $stats
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

        $draw = $request['draw'] ?? 1;
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;
        $search = $request['search']['value'] ?? '';
        $order = isset($request['order'][0]) ? [
            'column' => $request['order'][0]['column'],
            'dir' => $request['order'][0]['dir']
        ] : ['column' => 4, 'dir' => 'desc'];

        // Column names
        $columns = [
            'created_at',               // Order number
            'participants.account_id',  // Account ID
            'full_name',               // Participant Details
            'participant_statuses.form_status', // Submission Status
            'created_at',              // Registered On
        ];

        $orderColumn = $columns[$order['column']] ?? 'created_at';
        $programId = session('current_program');

        // Get data from database
        $builder = $this->participantModel->select('
                participants.*, 
                users.email,
                participants.phone_number,
                participant_statuses.form_status
            ')
            ->join('users', 'users.id = participants.user_id')
            ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
            ->where('participants.program_id', $programId)
            ->where('participants.is_deleted', 0)
            ->limit($length, $start);

        // Apply search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('participants.full_name', $search)
                ->orLike('users.email', $search)
                ->orLike('participants.phone_number', $search)
                ->orLike('participants.account_id', $search)
                ->orLike('participants.nationality', $search)
                ->groupEnd();
        }        // Apply filters
        $category = $this->request->getGet('category');
        if ($category !== '' && $category !== null) {
            $builder->where('participants.category', $category);
        }

        // Apply form status filter
        $form_status = $this->request->getGet('form_status');
        if ($form_status !== '' && $form_status !== null) {
            $builder->where('participant_statuses.form_status', $form_status);
        }

        // Get total count
        $totalRecords = $builder->countAllResults(false);

        // Order and limit
        $result = $builder->orderBy($orderColumn, $order['dir'])
            ->limit($length, $start)
            ->get()->getResult();
        // Format data for DataTable
        $data = [];
        $counter = $start + 1;

        foreach ($result as $row) {
            // Get submission status based only on form_status
            $submissionStatus = $this->getFormStatusBadge($row->form_status ?? 0);
            $data[] = [
                'order_number' => $counter++,
                'account_id' => $row->account_id,
                'participant_details' => [
                    'full_name' => $row->full_name,
                    'picture_url' => $row->picture_url,
                    'email' => $row->email,
                    'nationality' => $row->nationality ?? 'N/A'
                ],
                'submission_status' => $submissionStatus,
                'registered_on' => date('M d, Y', strtotime($row->created_at)),
                'actions' => '
                    <div class="d-flex gap-2">
                        <a href="' . base_url('users/participants/view/' . $row->id) . '" class="btn btn-sm btn-soft-primary">
                            <i class="ri-eye-fill align-bottom"></i>
                        </a>
                        <a href="' . base_url('participants/edit/' . $row->id) . '" class="btn btn-sm btn-soft-warning">
                            <i class="ri-pencil-fill align-bottom"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-soft-danger delete-participant" data-id="' . $row->id . '">
                            <i class="ri-delete-bin-2-line align-bottom"></i>
                        </button>
                    </div>'
            ];
        }

        // Response for DataTables
        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ];

        return $this->response->setJSON($response);
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
    private function getFormStatusBadge($formStatus)
    {
        // Status values: 0 = not started, 1 = on progress, 2 = submitted
        $statusInfo = [
            0 => ['Not Started', 'secondary'],
            1 => ['On Progress', 'warning'],
            2 => ['Submitted', 'success']
        ];

        $status = $statusInfo[$formStatus] ?? $statusInfo[0];

        return '<span class="badge bg-' . $status[1] . '-subtle text-' . $status[1] . '">' . $status[0] . '</span>';
    }

    /**
     * Export participants data using YBB Export API
     */
    public function export($id = null)
    {
        try {
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

                // Convert objects to arrays for API
                foreach ($participantObjects as $participant) {
                    $participants[] = (array)$participant;
                }
            }

            log_message('debug', 'Preparing to export ' . count($participants) . ' participants via YBB Export API');

            // Prepare export options
            $options = [
                'template' => 'participants',
                'format' => 'excel',
                'include_essays' => true,
                'program_id' => $programId
            ];

            // Add filter info to options
            $filters = $this->getExportFilters();
            if (!empty($filters)) {
                $options['filters'] = $filters;
            }

            // Create export using YBB Export API
            $ybbExport = new YbbExport();
            $result = $ybbExport->exportParticipants($participants, $options);

            if ($result['success']) {
                log_message('info', 'Participants export initiated successfully: ' . json_encode($result));
                
                // Extract all available data from YBB Export result for enhanced display
                $exportData = $result['data'] ?? [];
                $metadata = $result['metadata'] ?? [];
                
                // Build comprehensive response with all enhanced metrics
                $response = [
                    'success' => true,
                    'exportId' => $exportData['export_id'],
                    'message' => 'Export initiated successfully',
                    'recordCount' => $exportData['record_count'] ?? count($participants),
                    
                    // Enhanced metrics from API
                    'fileName' => $exportData['file_name'] ?? null,
                    'fileSize' => $exportData['file_size'] ?? null,
                    'fileSizeFormatted' => $exportData['file_size'] ? $this->formatFileSize($exportData['file_size']) : null,
                    'downloadUrl' => $exportData['download_url'] ?? null,
                    'expiresAt' => $exportData['expires_at'] ?? null,
                    'exportStrategy' => $exportData['export_strategy'] ?? 'single_file',
                    'processingTime' => $metadata['processing_time'] ?? $exportData['estimated_time'] ?? null,
                    
                    // Multi-file export data
                    'totalFiles' => $exportData['total_files'] ?? 1,
                    'individualFiles' => $exportData['individual_files'] ?? null,
                    'archive' => $exportData['archive'] ?? null,
                    
                    // Pass through all metadata for enhanced metrics display
                    'metadata' => $metadata,
                    
                    // Add data object for frontend compatibility
                    'data' => $exportData
                ];
                
                // If we have enhanced metrics from metadata, add them to the root level for easy access
                if (!empty($metadata)) {
                    if (isset($metadata['processing_time_ms'])) {
                        $response['processingTimeMs'] = $metadata['processing_time_ms'];
                    }
                    if (isset($metadata['records_per_second'])) {
                        $response['recordsPerSecond'] = $metadata['records_per_second'];
                    }
                    if (isset($metadata['memory_used_mb'])) {
                        $response['memoryUsedMb'] = $metadata['memory_used_mb'];
                    }
                    if (isset($metadata['peak_memory_mb'])) {
                        $response['peakMemoryMb'] = $metadata['peak_memory_mb'];
                    }
                    if (isset($metadata['memory_efficiency_kb_per_record'])) {
                        $response['memoryEfficiency'] = $metadata['memory_efficiency_kb_per_record'];
                    }
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
        $query = $this->participantModel->select('participants.*')
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

        $participantsList = $query->get()->getResult();
        $participants = [];

        // Add related data to each participant
        foreach ($participantsList as $participant) {
            // Get user data
            $user = $this->userModel->find($participant->user_id);
            $participant->user = $user;
            $participant->email = $user->email ?? '';

            // Get participant essays
            $essays = $this->participantEssayModel->getParticipantEssayByParticipantId($participant->id);
            $participant->essays = $essays;

            $participants[] = $participant;
        }

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
}
