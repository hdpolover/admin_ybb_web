<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use App\Models\UserModel;
use App\Models\ProgramModel;
use App\Models\PaymentModel;
use App\Models\ParticipantEssayModel;

class Participants extends BaseController
{
    protected $participantModel;
    protected $userModel;
    protected $programModel;
    protected $paymentModel;
    protected $participantEssayModel;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
        $this->userModel = new UserModel();
        $this->programModel = new ProgramModel();
        $this->paymentModel = new PaymentModel();
        $this->participantEssayModel = new ParticipantEssayModel();
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
}
