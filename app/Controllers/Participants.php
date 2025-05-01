<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use App\Models\UserModel;
use App\Models\ProgramModel;
use App\Models\PaymentModel;
use App\Models\ParticipantEssayModel;
use App\Models\ParticipantStatusModel;
use App\Services\ExcelExport;

class Participants extends BaseController
{
    protected $participantModel;
    protected $userModel;
    protected $programModel;
    protected $paymentModel;
    protected $participantEssayModel;
    protected $participantStatusModel;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
        $this->userModel = new UserModel();
        $this->programModel = new ProgramModel();
        $this->paymentModel = new PaymentModel();
        $this->participantEssayModel = new ParticipantEssayModel();
        $this->participantStatusModel = new ParticipantStatusModel();
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
            $participant->essays = $essays;            // Get payment information
            $payments = $this->paymentModel->getPaymentsByParticipantId($id);
            $participant->payments = $payments;

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
     * Export participants data to Excel
     */    public function export($id = null)
    {
        try {
            log_message('debug', 'Starting participant export process');

            // Set max execution time and memory limit for large exports
            ini_set('max_execution_time', 600); // 10 minutes
            ini_set('memory_limit', '1024M');   // 1 GB
            log_message('debug', 'Set execution time to 600s and memory limit to 1024M');

            // Clean output buffers to prevent any potential corruption in Excel output
            while (ob_get_level()) {
                ob_end_clean();
            }
            log_message('debug', 'Cleared output buffers');            // Set error handler to catch any errors
            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                log_message('error', "Excel export error: [$errno] $errstr in $errfile on line $errline");
                return true; // Continue execution
            });

            log_message('debug', 'Custom error handler set up for Excel export');            // Check if this is an AJAX check or direct download request
            $checkBatch = $this->request->getGet('check_batch') !== null;
            $directDownload = $this->request->getPost('direct_download') !== null;
            log_message('debug', 'Export request details - check_batch: ' . ($checkBatch ? 'yes' : 'no') . ', direct_download: ' . ($directDownload ? 'yes' : 'no'));

            $programId = session('current_program');
            log_message('debug', 'Export for program ID: ' . $programId);
            $participants = [];
            $db = \Config\Database::connect();            // If ID is provided, export just that participant
            if ($id) {
                log_message('debug', 'Exporting single participant with ID: ' . $id);
                $participant = $this->participantModel->find($id);

                if (!$participant) {
                    log_message('error', 'Participant not found for export, ID: ' . $id);
                    return redirect()->to('/users/participants')->with('error', 'Participant not found');
                }
                log_message('debug', 'Found participant: ' . $participant->full_name);

                // Get related data
                $userId = $participant->user_id;

                // Get user data
                $user = $this->userModel->find($userId);
                $participant->user = $user;
                $participant->email = $user->email ?? '';

                // Get participant essays
                $essays = $this->participantEssayModel->getParticipantEssayByParticipantId($id);
                $participant->essays = $essays;

                // Add to participants array
                $participants[] = $participant;                // Set filename with participant name
                $filename = 'participant_' . url_title($participant->full_name, '-', true) . '_' . date('Ymd_His');
                log_message('debug', 'Single participant export filename: ' . $filename);
            } else {
                log_message('debug', 'Starting bulk participant export for program ID: ' . $programId);
                // Get all participants for the current program
                $query = $this->participantModel->select('participants.*')
                    ->join('users', 'users.id = participants.user_id', 'left')
                    ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
                    ->where('participants.program_id', $programId)
                    ->where('participants.is_deleted', 0);
                log_message('debug', 'Base query created for participants');                // Apply export filters
                log_message('debug', 'Starting to apply export filters');

                // Limit records if specified
                $limit = $this->request->getGet('limit');
                if (!empty($limit) && is_numeric($limit)) {
                    log_message('debug', 'Applying limit filter: ' . $limit);
                    $query->limit((int)$limit);
                }

                // Filter by category if specified
                $category = $this->request->getGet('category');
                if (!empty($category)) {
                    log_message('debug', 'Applying category filter: ' . $category);
                    $query->where('participants.category', $category);
                }

                // Filter by form status if specified
                $formStatus = $this->request->getGet('form_status');
                if ($formStatus !== '' && $formStatus !== null) {
                    log_message('debug', 'Applying form status filter: ' . $formStatus);
                    $query->where('participant_statuses.form_status', $formStatus);
                }

                // Filter by date range if specified
                $dateRange = $this->request->getGet('date_range');
                if (!empty($dateRange)) {
                    log_message('debug', 'Applying date range filter: ' . $dateRange);
                    $dates = explode(' - ', $dateRange);
                    if (count($dates) == 2) {
                        $startDate = date('Y-m-d', strtotime($dates[0]));
                        $endDate = date('Y-m-d', strtotime($dates[1]));
                        log_message('debug', 'Date range parsed to: ' . $startDate . ' - ' . $endDate);
                        $query->where('DATE(participants.created_at) >=', $startDate)
                            ->where('DATE(participants.created_at) <=', $endDate);
                    }
                }

                // Filter by payment status if specified                $paymentStatus = $this->request->getGet('payment_status');
                if (!empty($paymentStatus) && $paymentStatus == 'success') {
                    log_message('debug', 'Applying payment status filter: success');
                    // Subquery to get participants with successful payments
                    $subQuery = $db->table('payments')
                        ->select('participant_id')
                        ->where('status', 2) // Successful payments have status 2
                        ->where('is_deleted', 0);

                    $query->whereIn('participants.id', $subQuery);
                    log_message('debug', 'Added payment status subquery filter');
                }                // Filter by specific program payment if specified
                $programPaymentId = $this->request->getGet('program_payment_id');
                if (!empty($programPaymentId) && is_numeric($programPaymentId)) {
                    log_message('debug', 'Applying program payment filter for ID: ' . $programPaymentId);
                    // Subquery to get participants who paid for this specific program payment
                    $subQuery = $db->table('payments')
                        ->select('participant_id')
                        ->where('program_payment_id', $programPaymentId)
                        ->where('status', 2) // Successful payments have status 2
                        ->where('is_deleted', 0);

                    $query->whereIn('participants.id', $subQuery);
                    log_message('debug', 'Added program payment subquery filter');
                }                // Get results as array of objects
                $participantsList = $query->get()->getResult();
                $participantCount = count($participantsList);
                log_message('debug', 'Query returned ' . $participantCount . ' participants');

                // Add related data to each participant
                log_message('debug', 'Starting to process each participant record');
                foreach ($participantsList as $participant) {
                    // Get user data
                    $userId = $participant->user_id;
                    $user = $this->userModel->find($userId);
                    $participant->user = $user;
                    $participant->email = $user->email ?? '';

                    // Get participant essays
                    $essays = $this->participantEssayModel->getParticipantEssayByParticipantId($participant->id);
                    $participant->essays = $essays;

                    // Add to participants array
                    $participants[] = $participant;
                }
                log_message('debug', 'Completed processing ' . count($participants) . ' participants with related data');

                // Get program info for filename
                $program = $this->programModel->find($programId);
                $programName = $program ? url_title($program->name, '-', true) : 'filtered-participants';

                // Set filename with program name
                $filename = 'participants_' . $programName . '_' . date('Ymd_His');

                // Add filter info to filename
                if (!empty($category)) {
                    $filename .= '_' . $category;
                }

                if ($formStatus !== '' && $formStatus !== null) {
                    $filename .= '_status' . $formStatus;
                }

                if (!empty($paymentStatus)) {
                    $filename .= '_paid';
                }

                if (!empty($programPaymentId)) {
                    $filename .= '_payment' . $programPaymentId;
                }
            }
            if (empty($participants)) {
                log_message('warning', 'No participants found matching export criteria');
                // Handle AJAX request differently
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(404)
                        ->setJSON(['success' => false, 'message' => 'No participants found to export']);
                }
                return redirect()->to('/users/participants')->with('error', 'No participants found to export');
            }
            // Check if we need to split into batches - use 1000 as batch size
            $batchSize = 1000;
            $totalParticipants = count($participants);
            log_message('debug', 'Total participants to export: ' . $totalParticipants . ' (batch size: ' . $batchSize . ')');

            // If this is a direct form submission (not an AJAX check) and there are many records
            $directDownload = $this->request->getPost('direct_download') || $this->request->getGet('direct_download');
            log_message('debug', 'Direct download mode: ' . ($directDownload ? 'yes' : 'no'));
            if ($totalParticipants > $batchSize && $directDownload) {
                log_message('debug', 'Slicing data to first batch only for direct download');
                // Get just the first batch for now
                $participants = array_slice($participants, 0, $batchSize);
                $batches = ceil($totalParticipants / $batchSize);

                // Modify filename to indicate it's just the first batch
                $filename .= '_batch1of' . $batches;
                log_message('debug', 'Adjusted for batch download: ' . $filename . ' (batch 1 of ' . $batches . ')');
            }
            // For AJAX requests that are just checking batch requirements
            else if ($this->request->isAJAX() && $totalParticipants > $batchSize) {
                $batches = ceil($totalParticipants / $batchSize);
                log_message('debug', 'AJAX batch check found ' . $batches . ' total batches needed');
                $batchInfo = [];                // Create batch info for response
                log_message('debug', 'Creating batch info for frontend');
                for ($i = 0; $i < $batches; $i++) {
                    $start = $i * $batchSize;
                    $count = min($batchSize, $totalParticipants - $start);

                    $batchFilename = $filename . '_batch' . ($i + 1) . 'of' . $batches;                    // Create a filters array that includes both GET and POST parameters
                    $allFilters = array_merge(
                        $this->request->getGet() ?? [],
                        $this->request->getPost() ?? []
                    );

                    // Make sure program_id is included
                    if (empty($allFilters['program_id'])) {
                        $allFilters['program_id'] = session('current_program');
                    }

                    log_message('debug', 'Creating batch with filters: ' . json_encode($allFilters));

                    $batchInfo[] = [
                        'batch' => $i + 1,
                        'total_batches' => $batches,
                        'start' => $start,
                        'count' => $count,
                        'filename' => $batchFilename . '.xlsx', // Add file extension for clarity in UI
                        'url' => site_url('users/participants/export_batch') .
                            '?' . http_build_query([
                                'batch' => $i + 1,
                                'total_batches' => $batches,
                                'batch_size' => $batchSize,
                                'filters' => base64_encode(json_encode($allFilters))
                            ])
                    ];

                    log_message('debug', 'Created batch info for batch ' . ($i + 1) . ': ' . $count . ' records starting at ' . $start);
                }                // Return batch information for the AJAX handler
                log_message('debug', 'Returning batch info response to AJAX handler');
                return $this->response->setJSON([
                    'success' => true,
                    'batches' => $batchInfo,
                    'total_records' => $totalParticipants,
                    'batch_size' => $batchSize,
                    'message' => 'Export will be processed in ' . $batches . ' batches'
                ]);
            }
            // For non-AJAX check requests or direct downloads, proceed with normal export
            if (!$checkBatch || $directDownload) {
                log_message('debug', 'Proceeding with direct Excel export');
                // Clean output buffers to prevent any content before the Excel file
                while (ob_get_level()) {
                    ob_end_clean();
                }
                log_message('debug', 'Output buffers cleaned for direct export');                // Set headers for Excel download
                log_message('debug', 'Setting headers for Excel download');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
                header('Cache-Control: no-cache');
                header('Pragma: public');
                log_message('debug', 'Headers set successfully, filename: ' . $filename . '.xlsx');

                $excelExport = new ExcelExport();
                log_message('debug', 'Initialized ExcelExport class, starting export with ' . count($participants) . ' records');
                $excelExport->exportParticipants($participants, $filename);
                // The script will exit inside the exportParticipants method
                log_message('debug', 'Export completed successfully');
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to export participants: ' . $e->getMessage());
            log_message('error', 'Exception trace: ' . $e->getTraceAsString());

            // Handle AJAX request differently
            if ($this->request->isAJAX()) {
                log_message('debug', 'Returning error response to AJAX request');
                return $this->response->setStatusCode(500)
                    ->setJSON(['success' => false, 'message' => 'Failed to export participants: ' . $e->getMessage()]);
            }
            log_message('debug', 'Redirecting with error after export failure');
            return redirect()->to('/users/participants')->with('error', 'Failed to export participants: ' . $e->getMessage());
        }
    }

    /**
     * Export a batch of participants to Excel
     */
    public function export_batch()
    {
        // This function should never return JSON, only download the Excel file
        // or redirect with an error message
        log_message('debug', 'Starting batch export process');

        // Set max execution time and memory limit for large exports
        ini_set('max_execution_time', 600); // 10 minutes
        ini_set('memory_limit', '1024M');   // 1 GB
        log_message('debug', 'Set execution time to 600s and memory limit to 1024M');

        // Turn off output buffering completely first thing
        while (ob_get_level()) {
            ob_end_clean();
        }
        log_message('debug', 'Output buffers cleared');            // Make sure nothing has been output yet
        if (headers_sent($file, $line)) {
            log_message('error', "Headers already sent in $file:$line");
            // Redirect to an error page instead of returning JSON
            return redirect()->to('/users/participants')->with('error', 'Export failed: Headers already sent');
        }

        try {
            // Set default timeout and memory limit
            set_time_limit(600);  // 10 minutes
            ini_set('memory_limit', '1024M');

            // Register a shutdown function to catch fatal errors
            register_shutdown_function(function () {
                $error = error_get_last();
                if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                    log_message('critical', 'Fatal error during export: ' . $error['message'] . ' in ' .
                        $error['file'] . ' on line ' . $error['line']);

                    // Try to send a clean response
                    if (!headers_sent()) {
                        header('Content-Type: text/plain');
                        echo "Export Error: A fatal error occurred. Please check the logs or try again with fewer records.";
                    }
                }
            });

            log_message('debug', 'Setting up custom error handler for Excel export');
            // Create a custom error handler that will log errors but not display them
            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                log_message('error', "PHP Error ($errno): $errstr in $errfile on line $errline");
                return true; // Don't execute PHP's internal error handler
            });

            // Get batch parameters - support both GET and POST methods
            $request = $this->request;
            $batch = (int)($request->getGet('batch') ?? $request->getPost('batch'));
            $totalBatches = (int)($request->getGet('total_batches') ?? $request->getPost('total_batches'));
            $batchSize = (int)($request->getGet('batch_size') ?? $request->getPost('batch_size'));

            log_message('debug', "Export request details - batch: $batch, total_batches: $totalBatches, batch_size: $batchSize");
            // Decode filters - support both GET and POST methods
            $encodedFilters = $request->getGet('filters') ?? $request->getPost('filters');
            $filters = [];

            if (!empty($encodedFilters)) {
                try {
                    log_message('debug', 'Attempting to decode filters from base64: ' . substr($encodedFilters, 0, 20) . '...');
                    // Use @ to suppress warnings from base64_decode
                    $decoded = @base64_decode($encodedFilters, true);
                    if ($decoded !== false) {
                        log_message('debug', 'Base64 decoded, attempting JSON decode');
                        $jsonDecoded = json_decode($decoded, true);
                        if (is_array($jsonDecoded)) {
                            $filters = $jsonDecoded;
                            log_message('debug', 'Filters decoded successfully: ' . json_encode($filters));
                        } else {
                            log_message('warning', 'JSON decode did not return an array: ' . json_last_error_msg());
                        }
                    } else {
                        log_message('warning', 'Base64 decode failed for filters parameter: ' . $encodedFilters);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to decode filters: ' . $e->getMessage());
                }
            } else {
                log_message('warning', 'No encoded filters parameter found in request');
            }
            // If filters are empty, try to get parameters directly from request
            if (empty($filters)) {
                log_message('debug', 'No filters found in encoded data, getting form filters directly');
                $filters = [
                    'program_id' => $request->getGet('program_id') ?? $request->getPost('program_id'),
                    'category' => $request->getGet('category') ?? $request->getPost('category'),
                    'form_status' => $request->getGet('form_status') ?? $request->getPost('form_status'),
                    'payment_status' => $request->getGet('payment_status') ?? $request->getPost('payment_status'),
                    'limit' => $request->getGet('limit') ?? $request->getPost('limit'),
                    'date_range' => $request->getGet('date_range') ?? $request->getPost('date_range')
                ];
            }

            // Log the filters received by the batch export function
            log_message('debug', 'Batch export filters: ' . json_encode($filters));

            // Additional safety check - make sure program_id exists in filters or get it from session
            if (empty($filters['program_id'])) {
                $filters['program_id'] = session('current_program');
                log_message('debug', 'Using program_id from session: ' . $filters['program_id']);
            }

            // Get program ID from filters or session
            $programId = $filters['program_id'] ?? session('current_program');

            if (!$programId) {
                // Don't return JSON, redirect with error message
                return redirect()->to('/users/participants')
                    ->with('error', 'Export failed: No program selected');
            }

            // Calculate offset for this batch
            $offset = ($batch - 1) * $batchSize;

            // Create database query
            $db = \Config\Database::connect();
            $query = $db->table('participants')
                ->select('participants.*, users.email, participant_statuses.form_status')
                ->join('users', 'users.id = participants.user_id', 'left')
                ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
                ->where('participants.program_id', $programId)
                ->where('participants.is_deleted', 0)
                ->limit($batchSize, $offset);

            // Add filters from the original request
            if (!empty($filters['category'])) {
                $query->where('participants.category', $filters['category']);
            }

            if (isset($filters['form_status']) && $filters['form_status'] !== '') {
                $query->where('participant_statuses.form_status', $filters['form_status']);
            }

            if (!empty($filters['date_range'])) {
                $dates = explode(' - ', $filters['date_range']);
                if (count($dates) == 2) {
                    $startDate = date('Y-m-d', strtotime($dates[0]));
                    $endDate = date('Y-m-d', strtotime($dates[1]));
                    $query->where('DATE(participants.created_at) >=', $startDate)
                        ->where('DATE(participants.created_at) <=', $endDate);
                }
            }

            if (!empty($filters['payment_status']) && $filters['payment_status'] == 'success') {
                $subQuery = $db->table('payments')
                    ->select('participant_id')
                    ->where('status', 2)
                    ->where('is_deleted', 0);

                $query->whereIn('participants.id', $subQuery);
            }

            if (!empty($filters['program_payment_id']) && is_numeric($filters['program_payment_id'])) {
                $subQuery = $db->table('payments')
                    ->select('participant_id')
                    ->where('program_payment_id', $filters['program_payment_id'])
                    ->where('status', 2)
                    ->where('is_deleted', 0);

                $query->whereIn('participants.id', $subQuery);
            }

            // Get results for this batch
            $participantsList = $query->get()->getResult();
            $participants = [];

            // Add related data to each participant
            foreach ($participantsList as $participant) {
                // Get user data
                $userId = $participant->user_id;
                $user = $this->userModel->find($userId);
                $participant->user = $user;
                $participant->email = $user->email ?? '';

                // Get participant essays
                $essays = $this->participantEssayModel->getParticipantEssayByParticipantId($participant->id);
                $participant->essays = $essays;

                // Add to participants array
                $participants[] = $participant;
            }
            if (empty($participants)) {
                // Don't return JSON, redirect with error message
                return redirect()->to('/users/participants')
                    ->with('error', 'Export failed: No participants found in this batch');
            }

            // Get program info for filename
            $program = $this->programModel->find($programId);
            $programName = $program ? url_title($program->name, '-', true) : 'filtered-participants';

            // Set filename with batch info
            $filename = 'participants_' . $programName . '_batch' . $batch . 'of' . $totalBatches . '_' . date('Ymd_His');

            // Add filter info to filename
            if (!empty($filters['category'])) {
                $filename .= '_' . $filters['category'];
            }

            if (isset($filters['form_status']) && $filters['form_status'] !== '') {
                $filename .= '_status' . $filters['form_status'];
            }

            if (!empty($filters['payment_status'])) {
                $filename .= '_paid';
            }

            if (!empty($filters['program_payment_id'])) {
                $filename .= '_payment' . $filters['program_payment_id'];
            }                // Create Excel exporter
            $excelExport = new \App\Services\ExcelExport();

            // Make sure all output buffers are clean before exporting
            while (ob_get_level()) {
                ob_end_clean();
            }                // Make sure all output buffers are clean
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                // Export to Excel and download - wrap in try-catch for additional protection
                try {
                    // Use our own error handling to diagnose any issues better
                    set_error_handler(function ($severity, $message, $file, $line) {
                        log_message('error', "PHP Error ($severity): $message in $file on line $line");
                        return true;  // Don't execute PHP's internal error handler
                    }, E_ALL);
                    
                    // Set PHP to not output compression headers
                    ini_set('zlib.output_compression', 'Off');
    
                    // Make sure we do a deep clone of the participants data to prevent modifying the original objects
                    $exportParticipants = array_map(function ($p) {
                        // Create a plain stdClass with only the fields we need
                        $simple = new \stdClass();
                        $fields = [
                            'id',
                            'full_name',
                            'email',
                            'phone',
                            'phone_number',
                            'address',
                            'nationality',
                            'category',
                            'form_status',
                            'created_at'
                        ];

                    foreach ($fields as $field) {
                        if (isset($p->$field)) {
                            $simple->$field = $p->$field;
                        }
                    }
                    return $simple;
                }, $participants);

                log_message('debug', 'Created simplified participant objects for export');
                $excelExport->exportParticipants($exportParticipants, $filename);
                // The script will exit inside the exportParticipants method
            } catch (\Throwable $innerException) {
                // Log detailed error message
                log_message('critical', 'Final Excel export failure: ' . $innerException->getMessage());
                log_message('critical', 'Exception class: ' . get_class($innerException));
                log_message('critical', 'Stack trace: ' . $innerException->getTraceAsString());

                // Clean up any output that might have started
                while (ob_get_level()) {
                    ob_end_clean();
                }

                // Return plain text error rather than redirect since headers may have been sent
                header('Content-Type: text/plain');
                echo "Export Error: Unable to generate Excel file. Please check the logs for details or try again with fewer records.";
                exit;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to export participants batch: ' . $e->getMessage());
            // Don't return JSON, redirect with error message
            return redirect()->to('/users/participants')
                ->with('error', 'Failed to export participants batch: ' . $e->getMessage());
        }
    }
}
