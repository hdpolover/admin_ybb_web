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
        ] : ['column' => 0, 'dir' => 'desc'];

        // Column names
        $columns = [
            'created_at',
            'full_name',
            'email',
            'phone_number',
            'category',
        ];

        $orderColumn = $columns[$order['column']] ?? 'created_at';

        // Get data from database
        $builder = $this->participantModel->select('
                participants.*, 
                users.email,
                participants.phone_number,
            ')
            ->join('users', 'users.id = participants.user_id')
            ->where('participants.program_id', session('current_program'))
            ->where('participants.is_deleted', 0)
            ->limit($length, $start);

        // Apply search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('participants.full_name', $search)
                ->orLike('users.email', $search)
                ->orLike('participants.phone_number', $search)
                ->groupEnd();
        }

        // Apply filters
        $category = $this->request->getGet('category');
        if ($category !== '' && $category !== null) {
            $builder->where('participants.category', $category);
        }

        // Get total count
        $totalRecords = $builder->countAllResults(false);

        // Order and limit
        $result = $builder->orderBy($orderColumn, $order['dir'])
            ->limit($length, $start)
            ->get()->getResult();

        // Format data for DataTable
        $data = [];
        foreach ($result as $row) {
            $categoryBadge = $this->getCategoryBadge($row->category);

            $data[] = [
                'id' => $row->id,
                'name' => [
                    'full_name' => $row->full_name,
                    'first_letter' => strtoupper(substr($row->full_name, 0, 1))
                ],
                'email' => $row->email,
                'phone' => $row->phone_number,
                'registration_date' => date('M d, Y', strtotime($row->created_at)),
                'category' => $categoryBadge ,
                'actions' => '
                    <div class="d-flex gap-2">
                        <a href="' . base_url('participants/view/' . $row->id) . '" class="btn btn-sm btn-soft-primary">
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
            $participant = $this->participantModel->getById($id);

            if (!$participant) {
                return redirect()->to('/participants')->with('error', 'Participant not found');
            }

            // Get related data
            $userId = $participant['user_id'];

            // Get user data
            $user = $this->userModel->find($userId);
            $participant['user'] = $user;

            // Get participant essays
            $essays = $this->participantEssayModel->getParticipantEssayByParticipantId($id);
            $participant['essays'] = $essays;

            // Get payment information
            $payments = $this->paymentModel->getPayments($id);
            $participant['payments'] = $payments;

            return view('users/participants/view', ['participant' => $participant]);
        } catch (\Exception $e) {
            return redirect()->to('/participants')->with('error', 'Failed to retrieve participant: ' . $e->getMessage());
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
}
