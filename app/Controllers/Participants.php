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
            // Use find() to get only the participant's own fields (avoids heavy JOIN in getById)
            $participant = $this->participantModel->where('id', $id)->where('is_deleted', 0)->first();

            if (!$participant) {
                return redirect()->to('/users/participants')
                    ->with('error', 'Participant not found');
            }

            // Convert object to array if necessary
            if (is_object($participant)) {
                $participant = (array) $participant;
            }

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
            ]);

            if (!$validation->run($data)) {
                return redirect()->back()
                    ->with('error', 'Validation failed: ' . implode(', ', $validation->getErrors()))
                    ->withInput();
            }

            // Only update allowed profile fields (exclude system fields passed via POST)
            unset($data['program_id'], $data['user_id'], $data['account_id'], $data['csrf_token']);

            // Update participant
            $this->participantModel->update($id, $data);

            return redirect()->to('/users/participants/view/' . $id)
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
     * Export participants data using YBB DB Export API (Database-Direct Mode)
     * This is the recommended approach per YBB_DB_EXPORT_API_INTEGRATION_GUIDE.md
     */
    public function export($id = null)
    {
        try {
            // For single participant exports, use DB-direct with specific filter
            if ($id) {
                log_message('debug', 'Exporting single participant with ID: ' . $id);
                return $this->exportUsingDbFilters(['participant_id' => $id]);
            }
            
            // For bulk exports, always use DB-direct mode
            log_message('debug', 'Using DB-direct export mode (recommended)');
            return $this->exportUsingDbFilters();


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
     * Get export filters from request
     * Only includes filters documented in YBB_DB_EXPORT_API_INTEGRATION_GUIDE.md
     * Handles both JSON body (from frontend) and direct POST/GET parameters
     */
    private function getExportFilters()
    {
        // Try to get JSON body first (from AJAX requests)
        $jsonBody = $this->request->getJSON(true); // true = return as array
        $filters = $jsonBody['filters'] ?? [];
        
        // If no JSON body, fall back to traditional POST/GET parameters
        if (empty($filters)) {
            $filters = [
                'status' => $this->request->getGet('status') ?: $this->request->getPost('status'),
                'category' => $this->request->getGet('category') ?: $this->request->getPost('category'),
                'form_status' => $this->request->getGet('form_status') !== null ? $this->request->getGet('form_status') : $this->request->getPost('form_status'),
                'has_submitted_form' => $this->request->getGet('has_submitted_form') ?: $this->request->getPost('has_submitted_form'),
                'has_paid' => $this->request->getGet('has_paid') ?: $this->request->getPost('has_paid'),
                'payment_status' => $this->request->getGet('payment_status') ?: $this->request->getPost('payment_status'),
                'country' => $this->request->getGet('country') ?: $this->request->getPost('country'),
                'search' => $this->request->getGet('search') ?: $this->request->getPost('search'),
                'date_range' => $this->request->getGet('date_range') ?: $this->request->getPost('date_range'),
                'limit' => $this->request->getGet('limit') ?: $this->request->getPost('limit'),
                'sort_by' => $this->request->getGet('sort_by') ?: $this->request->getPost('sort_by'),
                'sort_order' => $this->request->getGet('sort_order') ?: $this->request->getPost('sort_order')
            ];
        }
        
        return $filters;
    }



    /**
     * Export participants using DB-direct filters (recommended approach)
     * This method sends filters to the YBB DB Export API instead of fetching data first
     * Following YBB_DB_EXPORT_API_INTEGRATION_GUIDE.md documentation
     * 
     * @param array $additionalFilters Optional additional filters to merge with request filters
     */
    private function exportUsingDbFilters(array $additionalFilters = [])
    {
        try {
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

            log_message('info', 'Starting DB-direct export for program: ' . $programId);

            // Get export template and format from request
            // Try JSON body first (from AJAX requests with nested structure)
            $jsonBody = $this->request->getJSON(true);
            $options = $jsonBody['options'] ?? [];
            
            $template = $options['template'] ?? ($this->request->getPost('template') ?: $this->request->getGet('template') ?: 'standard');
            $format = $options['format'] ?? ($this->request->getPost('format') ?: $this->request->getGet('format') ?: 'excel');

            // Build filters for DB-direct export according to YBB DB API documentation
            $filters = [
                'program_id' => (int)$programId,
            ];

            // Get filters from request for bulk export
            $requestFilters = $this->getExportFilters();
            
            // Merge with any additional filters passed directly (e.g., for single participant)
            $requestFilters = array_merge($requestFilters, $additionalFilters);
            
            // Map all documented filters from YBB_DB_EXPORT_API_INTEGRATION_GUIDE.md
            
            // Status filter (registration status: approved, pending, rejected, all)
            if (!empty($requestFilters['status'])) {
                $filters['status'] = $requestFilters['status'];
            }
            
            // Category filter (fully_funded, self_funded)
            if (!empty($requestFilters['category'])) {
                $filters['category'] = $requestFilters['category'];
            }
            
            // Country filter
            if (!empty($requestFilters['country'])) {
                $filters['country'] = $requestFilters['country'];
            }
            
            // Date range filter - converts to date_from and date_to
            if (!empty($requestFilters['date_range'])) {
                $dates = explode(' - ', $requestFilters['date_range']);
                if (count($dates) == 2) {
                    $filters['date_from'] = date('Y-m-d', strtotime($dates[0]));
                    $filters['date_to'] = date('Y-m-d', strtotime($dates[1]));
                }
            }
            
            // Search filter (searches name or email)
            if (!empty($requestFilters['search'])) {
                $filters['search'] = $requestFilters['search'];
            }
            
            // has_submitted_form filter (convenience filter for form_status = 2)
            if (isset($requestFilters['has_submitted_form']) && $requestFilters['has_submitted_form'] !== '') {
                $filters['has_submitted_form'] = $requestFilters['has_submitted_form'] === 'yes' || $requestFilters['has_submitted_form'] === true ? 'yes' : 'no';
            }
            
            // has_paid filter (checks for successful payments - status = 2)
            if (isset($requestFilters['has_paid']) && $requestFilters['has_paid'] !== '') {
                $filters['has_paid'] = $requestFilters['has_paid'] === 'yes' || $requestFilters['has_paid'] === true ? 'yes' : 'no';
            }
            
            // registration_form_status filter (not_started=0, in_progress=1, submitted=2)
            if (isset($requestFilters['form_status']) && $requestFilters['form_status'] !== '' && $requestFilters['form_status'] !== null) {
                $formStatus = (int)$requestFilters['form_status'];
                $statusMap = [0 => 'not_started', 1 => 'in_progress', 2 => 'submitted'];
                $filters['registration_form_status'] = $statusMap[$formStatus] ?? $formStatus;
            }
            
            // Legacy payment_status filter - maps to has_paid
            if (!empty($requestFilters['payment_status'])) {
                if ($requestFilters['payment_status'] === 'success' || $requestFilters['payment_status'] === '2') {
                    $filters['has_paid'] = 'yes';
                } else if ($requestFilters['payment_status'] === 'pending' || $requestFilters['payment_status'] === 'failed') {
                    $filters['has_paid'] = 'no';
                }
            }
            
            // Limit filter (maximum records to export)
            if (!empty($requestFilters['limit']) && is_numeric($requestFilters['limit'])) {
                $filters['limit'] = (int)$requestFilters['limit'];
            }
            
            // Sort options (defaults to created_at desc if not provided)
            $filters['sort_by'] = !empty($requestFilters['sort_by']) ? $requestFilters['sort_by'] : 'created_at';
            $filters['sort_order'] = !empty($requestFilters['sort_order']) ? $requestFilters['sort_order'] : 'desc';

            log_message('debug', 'DB Export filters: ' . json_encode($filters));

            // Prepare export options according to YBB DB API documentation
            $options = [
                'template' => $template, // Valid options: standard, detailed, summary, complete
                'format' => $format,
                'filename' => $this->generateExportFilename('participants', $programId),
                'sheet_name' => 'YBB_Participants_' . date('Y'),
                'include_related' => true // Include related data (essays, payments, etc.)
            ];

            log_message('debug', 'DB Export options: ' . json_encode($options));

            // Use DB-direct export method from YbbExport library
            $ybbExport = new YbbExport();
            $result = $ybbExport->exportParticipantsFromDB($filters, $options);

            if ($result['success']) {
                log_message('info', 'DB Export initiated successfully');
                
                $exportData = $result['data'];
                $metadata = $result['metadata'] ?? [];
                
                log_message('info', 'Export data keys: ' . json_encode(array_keys($exportData)));
                log_message('info', 'Export data values: ' . json_encode($exportData));
                
                // Build full download URL if relative path provided
                $downloadUrl = $exportData['download_url'] ?? null;
                if ($downloadUrl && !str_starts_with($downloadUrl, 'http')) {
                    // Get API base URL from environment
                    $apiBaseUrl = getenv('YBB_EXPORT_API_URL') ?: 'http://127.0.0.1:5000';
                    $downloadUrl = rtrim($apiBaseUrl, '/') . $downloadUrl;
                }
                
                $response = [
                    'success' => true,
                    'exportId' => $exportData['export_id'],
                    'export_id' => $exportData['export_id'],
                    'message' => 'Export initiated successfully',
                    'recordCount' => $exportData['record_count'] ?? 0,
                    'record_count' => $exportData['record_count'] ?? 0,
                    
                    // File information from API response
                    'fileName' => $exportData['file_name'] ?? null,
                    'file_name' => $exportData['file_name'] ?? null,
                    'fileSize' => $exportData['file_size'] ?? null,
                    'file_size' => $exportData['file_size'] ?? null,
                    'fileSizeMB' => $exportData['file_size_mb'] ?? null,
                    'file_size_mb' => $exportData['file_size_mb'] ?? null,
                    'downloadUrl' => $downloadUrl,
                    'download_url' => $downloadUrl,
                    'expiresAt' => $exportData['expires_at'] ?? null,
                    'expires_at' => $exportData['expires_at'] ?? null,
                    
                    // Metadata
                    'metadata' => $metadata,
                    'data' => $exportData,
                    
                    // Additional info for frontend
                    'exportType' => 'db_direct',
                    'filters' => $filters
                ];
                
                log_message('info', 'Export response prepared: Export ID = ' . $exportData['export_id']);

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON($response);
                }
                
                return redirect()->to('/users/participants')->with('success', 'Export completed successfully');
            } else {
                log_message('error', 'DB Export failed: ' . ($result['message'] ?? 'Unknown error'));
                
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(500)
                        ->setJSON([
                            'success' => false,
                            'message' => $result['message'] ?? 'Export failed',
                            'error_code' => $result['error_code'] ?? 'EXPORT_FAILED',
                            'details' => $result['details'] ?? []
                        ]);
                }
                return redirect()->to('/users/participants')->with('error', 'Export failed: ' . $result['message']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to export using DB filters: ' . $e->getMessage());

            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)
                    ->setJSON(['success' => false, 'message' => 'Failed to export: ' . $e->getMessage()]);
            }
            return redirect()->to('/users/participants')->with('error', 'Failed to export: ' . $e->getMessage());
        }
    }

    /**
     * Get export statistics preview (as per YBB DB Export API documentation)
     */
    public function export_statistics()
    {
        try {
            $exportType = $this->request->getPost('export_type') ?? 'participants';
            
            // Get program ID
            $programId = $this->request->getPost('program_id') ?? session('current_program');
            
            if (!$programId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Program ID is required'
                ])->setStatusCode(400);
            }
            
            // Build filters same way as export - use all documented filters
            $filters = ['program_id' => (int)$programId];
            $requestFilters = $this->getExportFilters();
            
            // Apply all documented filters
            if (!empty($requestFilters['status'])) {
                $filters['status'] = $requestFilters['status'];
            }
            if (!empty($requestFilters['category'])) {
                $filters['category'] = $requestFilters['category'];
            }
            if (!empty($requestFilters['country'])) {
                $filters['country'] = $requestFilters['country'];
            }
            if (!empty($requestFilters['search'])) {
                $filters['search'] = $requestFilters['search'];
            }
            if (isset($requestFilters['has_submitted_form']) && $requestFilters['has_submitted_form'] !== '') {
                $filters['has_submitted_form'] = $requestFilters['has_submitted_form'];
            }
            if (isset($requestFilters['has_paid']) && $requestFilters['has_paid'] !== '') {
                $filters['has_paid'] = $requestFilters['has_paid'];
            }
            if (isset($requestFilters['form_status']) && $requestFilters['form_status'] !== '') {
                $statusMap = [0 => 'not_started', 1 => 'in_progress', 2 => 'submitted'];
                $filters['registration_form_status'] = $statusMap[(int)$requestFilters['form_status']] ?? $requestFilters['form_status'];
            }
            if (!empty($requestFilters['date_range'])) {
                $dates = explode(' - ', $requestFilters['date_range']);
                if (count($dates) == 2) {
                    $filters['date_from'] = date('Y-m-d', strtotime($dates[0]));
                    $filters['date_to'] = date('Y-m-d', strtotime($dates[1]));
                }
            }
            if (!empty($requestFilters['limit']) && is_numeric($requestFilters['limit'])) {
                $filters['limit'] = (int)$requestFilters['limit'];
            }
            
            // Initialize YBB Export library
            $ybbExport = new YbbExport();
            $result = $ybbExport->getExportStatistics($exportType, $filters);
            
            if ($result['success']) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => $result['data'],
                    'total_count' => $result['total_count'],
                    'status_breakdown' => $result['status_breakdown'] ?? []
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to get statistics'
                ])->setStatusCode(500);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to get export statistics: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ])->setStatusCode(500);
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
                $statusData = $statusResult['data'] ?? $statusResult;
                
                // Standardize status response format
                $response = [
                    'success' => true,
                    'data' => [
                        'export_id' => $exportId,
                        'status' => $statusData['status'] ?? 'unknown',
                        'progress' => $statusData['progress'] ?? 0,
                        'records_processed' => $statusData['records_processed'] ?? 0,
                        'total_records' => $statusData['total_records'] ?? ($statusData['record_count'] ?? 0),
                        'created_at' => $statusData['created_at'] ?? null,
                        'updated_at' => $statusData['updated_at'] ?? null,
                        'estimated_completion' => $statusData['estimated_completion'] ?? null,
                        'file_name' => $statusData['file_name'] ?? null,
                        'file_size' => $statusData['file_size'] ?? null,
                        'download_url' => null,
                        'error_message' => $statusData['error_message'] ?? null
                    ]
                ];
                
                // If export is completed and has a file, construct download URL
                if (in_array(strtolower($statusData['status'] ?? ''), ['completed', 'ready', 'success']) && 
                    !empty($statusData['file_name'])) {
                    
                    // Build download URL using API base URL
                    $downloadUrl = $statusData['download_url'] ?? $statusData['file_url'] ?? null;
                    if (!$downloadUrl) {
                        // Construct download URL according to documentation: /api/ybb/export/{id}/download
                        $apiBaseUrl = getenv('YBB_EXPORT_API_URL') ?: 'http://127.0.0.1:5000';
                        $downloadUrl = rtrim($apiBaseUrl, '/') . "/api/ybb/export/{$exportId}/download";
                    } else if (!str_starts_with($downloadUrl, 'http')) {
                        // Relative URL, make it absolute
                        $apiBaseUrl = getenv('YBB_EXPORT_API_URL') ?: 'http://127.0.0.1:5000';
                        $downloadUrl = rtrim($apiBaseUrl, '/') . $downloadUrl;
                    }
                    
                    $response['data']['download_url'] = $downloadUrl;
                    $response['data']['records_exported'] = $statusData['records_processed'] ?? $statusData['record_count'] ?? ($statusData['total_records'] ?? 0);
                }
                
                log_message('info', "Export status retrieved successfully: " . json_encode($response['data']));
                
                return $this->response->setJSON($response);
                
            } else {
                log_message('error', "Failed to get export status: " . ($statusResult['message'] ?? 'Unknown error'));
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $statusResult['message'] ?? 'Failed to retrieve export status',
                    'error_code' => $statusResult['error_code'] ?? 'STATUS_CHECK_FAILED',
                    'export_id' => $exportId,
                    'suggestion' => $statusResult['suggestion'] ?? 'Please check if the export still exists or create a new export'
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
