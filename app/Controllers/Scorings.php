<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use App\Models\UserModel;
use App\Models\ProgramModel;
use App\Models\PaymentModel;
use App\Models\ParticipantEssayModel;
use App\Models\ParticipantStatusModel;
use App\Models\ParticipantSubthemeModel;
use App\Models\ScoreWeightModel;
use App\Models\ScoreModel;
// use App\Services\ExcelExport;

class Scorings extends AdminBaseController
{
    protected $participantModel;
    protected $userModel;
    protected $programModel;
    protected $paymentModel;
    protected $participantEssayModel;
    protected $participantStatusModel;
    protected $participantSubthemeModel;
    protected $scoreWeightModel;
    protected $scoreModel;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
        $this->userModel = new UserModel();
        $this->programModel = new ProgramModel();
        $this->paymentModel = new PaymentModel();
        $this->participantEssayModel = new ParticipantEssayModel();
        $this->participantStatusModel = new ParticipantStatusModel();
        $this->participantSubthemeModel = new ParticipantSubthemeModel();
        $this->scoreWeightModel = new ScoreWeightModel();
        $this->scoreModel = new ScoreModel();
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

            return view('scorings/fully_funded/index', $data);
        } catch (\Exception $e) {
            // Handle exception and redirect with error message
            log_message('error', 'Failed to fetch participants: ' . $e->getMessage());
            // return redirect()->back()->with('error', 'Failed to fetch participants: ' . $e->getMessage());
        }
    }

    public function interview()
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

            return view('scorings/interview/index', $data);
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
        ] : ['column' => 4, 'dir' => 'asc'];

        // Column names
        $columns = [
            'participant_statuses.updated_at', // Order # (Stable if sorted by submission time)
            'participants.account_id',  // Account ID
            'full_name',               // Participant Details
            'participant_statuses.form_status', // Submission Status
            'participants.score_status', // Score Status
            'participant_statuses.updated_at', // Submitted On
        ];

        $orderColumn = $columns[$order['column']] ?? 'participant_statuses.updated_at';
        $programId = session('current_program');

        // Get data from database
        $builder = $this->participantModel->select('
                participants.*,
                users.email,
                participants.phone_number,
                participants.category,
                participant_statuses.form_status,
                participant_statuses.updated_at as submitted_at
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
        $category = $this->request->getGet('category') ?: 'fully_funded';
        if ($category !== 'all') {
            $builder->where('participants.category', $category);
        }

        // Apply form status filter
        $form_status = '2';
        if ($form_status !== '' && $form_status !== null) {
            $builder->where('participant_statuses.form_status', $form_status);
        }

        // Apply Submission Date filter
        $submissionDate = $request['submission_date'] ?? '';
        if (!empty($submissionDate)) {
            $dates = explode(' - ', $submissionDate);
            if (count($dates) == 2) {
                $builder->where('DATE(participant_statuses.updated_at) >=', $dates[0])
                        ->where('DATE(participant_statuses.updated_at) <=', $dates[1]);
            }
        }

        // Get total count
        $totalRecords = $builder->countAllResults(false);

        // Order and limit
        $result = $builder->orderBy($orderColumn, $order['dir'])
            ->orderBy('participants.id', 'ASC')
            ->limit($length, $start)
            ->get()->getResult();
        // Format data for DataTable
        $data = [];
        $counter = $start + 1;

        foreach ($result as $row) {
            // Get submission status based only on form_status
            $submissionStatus = $this->getFormStatusBadge($row->form_status ?? 0);
            
            // Format score status badge
            $scoreStatusBadge = '<span class="badge bg-secondary-subtle text-secondary">No Score</span>';
            if ($row->score_status == 'rejected') {
                $scoreStatusBadge = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
            } elseif ($row->score_status == 'go_to_interview') {
                $scoreStatusBadge = '<span class="badge bg-success-subtle text-success">Interview</span>';
            }

            $currentScore = $row->score_total > 0 ? $row->score_total : 0;
            $displayScore = '<div><span class="fs-12 text-muted">Total: </span><span class="fw-bold">' . number_format($currentScore, 2) . '</span></div>' . $scoreStatusBadge;

            $data[] = [
                'order_number' => $counter++,
                'account_id' => $row->account_id,
                'participant_details' => [
                    'full_name' => $row->full_name,
                    'picture_url' => $row->picture_url,
                    'email' => $row->email,
                    'nationality' => $row->nationality ?? 'N/A'
                ],
                'category' => $this->getCategoryBadge($row->category ?? ''),
                'submission_status' => $submissionStatus,
                'score_status' => $displayScore,
                'submitted_on' => $row->submitted_at ? date('M d, Y H:i', strtotime($row->submitted_at)) : 'N/A',
                'actions' => '
                    <div class="d-flex gap-2">
                        <a href="' . base_url('scorings/fully_funded/view/' . $row->id) . '" class="btn btn-sm btn-soft-primary">
                            <i class="ri-eye-fill align-bottom"></i>
                        </a>
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

    public function getData2()
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
        ] : ['column' => 4, 'dir' => 'asc'];

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
                participants.category,
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
        }
        // Apply filters
        $category = $this->request->getGet('category') ?: 'fully_funded';
        if ($category !== 'all') {
            $builder->where('participants.category', $category);
        }
        // Apply filters
        $score_status = 'go_to_interview';
        if ($score_status !== '' && $score_status !== null) {
            $builder->where('participants.score_status', $score_status);
        }

        // Apply form status filter
        $form_status = '2';
        if ($form_status !== '' && $form_status !== null) {
            $builder->where('participant_statuses.form_status', $form_status);
        }

        // Get total count
        $totalRecords = $builder->countAllResults(false);

        // Order and limit
        $result = $builder->orderBy($orderColumn, $order['dir'])
            ->orderBy('participants.id', 'ASC')
            ->limit($length, $start)
            ->get()->getResult();
        // Format data for DataTable
        $data = [];
        $counter = $start + 1;

        foreach ($result as $row) {
            // Get submission status based only on form_status
            $submissionStatus = $this->getFormStatusBadge($row->form_status ?? 0);
            $scoreStatus = $row->score_status ?? 0;
            $data[] = [
                'order_number' => $counter++,
                'account_id' => $row->account_id,
                'participant_details' => [
                    'full_name' => $row->full_name,
                    'picture_url' => $row->picture_url,
                    'email' => $row->email,
                    'nationality' => $row->nationality ?? 'N/A'
                ],
                'category' => $this->getCategoryBadge($row->category ?? ''),
                'submission_status' => $submissionStatus,
                'registered_on' => date('M d, Y', strtotime($row->created_at)),
                'actions' => $scoreStatus
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
            
            $programId = $participant->program_id;

            // ambil semua komponen bobot penilaian milik program ini
            $components1 = $this->scoreWeightModel
                                ->where('program_id', $programId)
                                ->where('reference', 'score_ach')
                                ->where('is_deleted', 0)
                                ->where('is_active', 1)
                                ->findAll(); // returnType = object → good

            $scores1 = [];

            $components2 = $this->scoreWeightModel
                                ->where('program_id', $programId)
                                ->where('reference', 'score_essay')
                                ->where('is_deleted', 0)
                                ->where('is_active', 1)
                                ->findAll(); // returnType = object → good

            $scores2 = [];

            foreach ($components1 as $c1) {
                // cek apakah peserta ini sudah ada score untuk komponen tersebut
                $score = $this->scoreModel
                            ->where('participant_id', $id)
                            ->where('score_weight_id', $c1->id)
                            ->first(); // return object atau null

                $scores1[] = (object)[
                    'component_id'   => $c1->id,
                    'component_name' => $c1->description,
                    'weight'         => $c1->weight,
                    'weight2'        => $c1->weight2,
                    'value'          => $score ? $score->score_input : 0,
                    'calc'           => $score ? $score->score_calculated : 0
                ];
            }

            foreach ($components2 as $c2) {
                // cek apakah peserta ini sudah ada score untuk komponen tersebut
                $score = $this->scoreModel
                            ->where('participant_id', $id)
                            ->where('score_weight_id', $c2->id)
                            ->first(); // return object atau null

                $scores2[] = (object)[
                    'component_id'   => $c2->id,
                    'component_name' => $c2->description,
                    'weight'         => $c2->weight,
                    'weight2'        => $c2->weight2,
                    'value'          => $score ? $score->score_input : 0,
                    'calc'           => $score ? $score->score_calculated : 0
                ];
            }

            // tambahkan ke objek participant
            $participant->scores1 = $scores1;
            $participant->scores2 = $scores2;
            
            return view('scorings/fully_funded/view', ['participant' => $participant]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to retrieve participant: ' . $id);
            return redirect()->to('/scorings/fully_funded')->with('error', 'Failed to retrieve participant: ' . $e->getMessage());
        }
    }

    public function saveScore()
    {
        $participantId     = $this->request->getPost('participant_id');
        $scoreWeightIds    = $this->request->getPost('score_weight_id');
        $scoreInputs       = $this->request->getPost('score_input');
        $weights           = $this->request->getPost('weight'); // ambil bobot 1
        $weights2          = $this->request->getPost('weight2'); // ambil bobot 2

        $dataToInsert = [];

        foreach ($scoreWeightIds as $index => $weightId) {
        $val    = floatval($scoreInputs[$index] ?? 0);
        $we = floatval($weights[$index] ?? 0);
        $we2 = floatval($weights2[$index] ?? 0);
            $dataToInsert[] = [
                'participant_id'  => $participantId,
                'program_id'      => session('current_program'),
                'score_weight_id' => $weightId,
                'score_input'     => $val,
                'score_calculated'=> ($val*$we)*$we2,  // jika mau dihitung nanti
                'is_active'       => 1,
                'is_deleted'      => 0,
                'created_at'      => date('Y-m-d H:i:s')
            ];
        }

        // hapus skor lama agar replace
        $this->scoreModel
            ->where('participant_id', $participantId)
            ->whereIn('score_weight_id', $scoreWeightIds)
            ->delete();
            
        // insert yg baru secara batch
        $this->scoreModel->insertBatch($dataToInsert);

        // 1. Hitung total score_input milik peserta ini
        $total = $this->scoreModel
                    ->selectSum('score_calculated')
                    ->where('participant_id', $participantId)
                    ->first()
                    ->score_calculated;   // hasilnya akan jadi property object
        $statusp = '';
        if ($total < 75.00){
            $statusp = 'rejected';
        }else if (($total >= 75.00) && ($total <= 100.00)){
            $statusp = 'go_to_interview';
        }else{
            $statusp = 'no_score';
        }
        // 2. Update ke table participants kolom score_total
        $this->participantModel->update($participantId, [
            'score_total' => $total,
            'score_status' => $statusp
        ]);
        return redirect()->back()->with('success', 'Scoring berhasil disimpan.');
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
     */
    public function export($id = null)
    {
        try {
            log_message('debug', 'Starting participant export process');

            // Set max execution time and memory limit for large exports
            ini_set('max_execution_time', 600); // 10 minutes
            ini_set('memory_limit', '1024M');   // 1 GB

            $programId = session('current_program');
            if (!$programId) {
                return redirect()->to('/users/participants')->with('error', 'No program selected');
            }

            $participants = [];
            $db = \Config\Database::connect();

            // If ID is provided, export just that participant
            if ($id) {
                log_message('debug', 'Exporting single participant with ID: ' . $id);
                $participant = $this->participantModel->find($id);

                if (!$participant) {
                    log_message('error', 'Participant not found for export, ID: ' . $id);
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

                $participants[] = $participant;
                $filename = 'participant_' . url_title($participant->full_name, '-', true) . '_' . date('Ymd_His');
            } else {
                log_message('debug', 'Starting bulk participant export for program ID: ' . $programId);
                
                // Check if this is a batch size check request
                $checkBatchSize = $this->request->getGet('check_batch_size') !== null;
                
                if ($checkBatchSize) {
                    // Just count the records and return batch information
                    $totalCount = $this->getParticipantCount($programId);
                    $batchSize = 1000;
                    
                    if ($totalCount > $batchSize) {
                        $batches = ceil($totalCount / $batchSize);
                        return $this->response->setJSON([
                            'success' => true,
                            'needs_batching' => true,
                            'total_records' => $totalCount,
                            'batch_size' => $batchSize,
                            'total_batches' => $batches,
                            'message' => "Found {$totalCount} records. Will be exported in {$batches} separate files."
                        ]);
                    } else {
                        return $this->response->setJSON([
                            'success' => true,
                            'needs_batching' => false,
                            'total_records' => $totalCount,
                            'message' => "Found {$totalCount} records. Will be exported in single file."
                        ]);
                    }
                }

                // Get participants data
                $participants = $this->getParticipantsForExport($programId);

                if (empty($participants)) {
                    if ($this->request->isAJAX()) {
                        return $this->response->setStatusCode(404)
                            ->setJSON(['success' => false, 'message' => 'No participants found to export']);
                    }
                    return redirect()->to('/users/participants')->with('error', 'No participants found to export');
                }

                // Generate filename
                $program = $this->programModel->find($programId);
                $programName = $program ? url_title($program->name, '-', true) : 'participants';
                $filename = 'participants_' . $programName . '_' . date('Ymd_His');

                // Add filter info to filename
                $filters = $this->getExportFilters();
                if (!empty($filters['category'])) {
                    $filename .= '_' . $filters['category'];
                }
                if ($filters['form_status'] !== '' && $filters['form_status'] !== null) {
                    $filename .= '_status' . $filters['form_status'];
                }
                if (!empty($filters['payment_status'])) {
                    $filename .= '_paid';
                }
            }

            log_message('debug', 'Exporting ' . count($participants) . ' participants to file: ' . $filename);

            // Clean all output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Set headers for Excel download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
            header('Cache-Control: no-cache');
            header('Pragma: public');

            // Execute Excel export with proper error handling
            $this->executeExcelExport($participants, $filename);

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
     * Execute Excel export with proper error handling
     */
    private function executeExcelExport($participants, $filename)
    {
        try {
            log_message('debug', 'Starting Excel export with ' . count($participants) . ' participants');
            
            // TODO: Implement ExcelExport service or use alternative export method
            throw new \Exception('ExcelExport service not implemented');
            
            // The script will exit inside the exportParticipants method
            exit;
        } catch (\Exception $exportException) {
            log_message('error', 'Excel export failed: ' . $exportException->getMessage());
            log_message('error', 'Export exception trace: ' . $exportException->getTraceAsString());
            
            // Clean output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)
                    ->setJSON(['success' => false, 'message' => 'Export failed: ' . $exportException->getMessage()]);
            }
            return redirect()->to('/users/participants')->with('error', 'Export failed: ' . $exportException->getMessage());
        }
    }

    /**
     * Export participants in batches
     */
    public function exportBatch()
    {
        try {
            $batch = (int)$this->request->getGet('batch', FILTER_SANITIZE_NUMBER_INT);
            $batchSize = (int)$this->request->getGet('batch_size', FILTER_SANITIZE_NUMBER_INT) ?: 1000;
            
            if ($batch < 1) {
                return redirect()->to('/users/participants')->with('error', 'Invalid batch number');
            }

            $programId = session('current_program');
            if (!$programId) {
                return redirect()->to('/users/participants')->with('error', 'No program selected');
            }

            // Calculate offset
            $offset = ($batch - 1) * $batchSize;

            // Get participants for this batch
            $participants = $this->getParticipantsForExport($programId, $batchSize, $offset);

            if (empty($participants)) {
                return redirect()->to('/users/participants')->with('error', 'No participants found for this batch');
            }

            // Generate filename with batch info
            $program = $this->programModel->find($programId);
            $programName = $program ? url_title($program->name, '-', true) : 'participants';
            $totalRecords = $this->getParticipantCount($programId);
            $totalBatches = ceil($totalRecords / $batchSize);
            
            $filename = 'participants_' . $programName . '_batch' . $batch . 'of' . $totalBatches . '_' . date('Ymd_His');

            // Add filter info to filename
            $filters = $this->getExportFilters();
            if (!empty($filters['category'])) {
                $filename .= '_' . $filters['category'];
            }
            if ($filters['form_status'] !== '' && $filters['form_status'] !== null) {
                $filename .= '_status' . $filters['form_status'];
            }

            log_message('debug', 'Exporting batch ' . $batch . ' with ' . count($participants) . ' participants');

            // Clean all output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Set headers for Excel download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
            header('Cache-Control: no-cache');
            header('Pragma: public');

            // Execute Excel export with proper error handling
            $this->executeExcelExport($participants, $filename);

        } catch (\Exception $e) {
            log_message('error', 'Failed to export participants batch: ' . $e->getMessage());
            return redirect()->to('/users/participants')->with('error', 'Failed to export batch: ' . $e->getMessage());
        }
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
        // Category filter
        if (!empty($filters['category'])) {
            $query->where('participants.category', $filters['category']);
        }

        // Form status filter
        if ($filters['form_status'] !== '' && $filters['form_status'] !== null) {
            $query->where('participant_statuses.form_status', $filters['form_status']);
        }

        // Date range filter
        if (!empty($filters['date_range'])) {
            $dates = explode(' - ', $filters['date_range']);
            if (count($dates) == 2) {
                $startDate = date('Y-m-d', strtotime($dates[0]));
                $endDate = date('Y-m-d', strtotime($dates[1]));
                $query->where('DATE(participants.created_at) >=', $startDate)
                    ->where('DATE(participants.created_at) <=', $endDate);
            }
        }

        // Payment status filter
        if (!empty($filters['payment_status']) && $filters['payment_status'] == 'success') {
            $db = \Config\Database::connect();
            $subQuery = $db->table('payments')
                ->select('participant_id')
                ->where('status', 2)
                ->where('is_deleted', 0);
            $query->whereIn('participants.id', $subQuery);
        }

        // Specific program payment filter
        if (!empty($filters['program_payment_id']) && is_numeric($filters['program_payment_id'])) {
            $db = \Config\Database::connect();
            $subQuery = $db->table('payments')
                ->select('participant_id')
                ->where('program_payment_id', $filters['program_payment_id'])
                ->where('status', 2)
                ->where('is_deleted', 0);
            $query->whereIn('participants.id', $subQuery);
        }

        // Limit filter
        if (!empty($filters['limit']) && is_numeric($filters['limit'])) {
            $query->limit((int)$filters['limit']);
        }
    }
}