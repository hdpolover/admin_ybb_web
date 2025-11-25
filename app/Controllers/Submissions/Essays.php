<?php

namespace App\Controllers\Submissions;

use App\Controllers\AdminBaseController;
use App\Models\ParticipantEssayModel;
use App\Models\ParticipantModel;
use App\Models\ProgramEssayModel;
use App\Models\UserModel;

class Essays extends AdminBaseController
{
    protected $participantEssayModel;
    protected $participantModel;
    protected $programEssayModel;
    protected $userModel;

    public function __construct()
    {
        $this->participantEssayModel = new ParticipantEssayModel();
        $this->participantModel = new ParticipantModel();
        $this->programEssayModel = new ProgramEssayModel();
        $this->userModel = new UserModel();
    }

    /**
     * Display list of all participant essays for current program
     */
    public function index()
    {
        // Get current program ID from session
        $programId = session('current_program');

        if (!$programId) {
            session()->setFlashdata('error', 'Please select a program first');
            return redirect()->to(base_url('welcome'));
        }

        // Get program essays (questions) for this program
        $programEssays = $this->programEssayModel
            ->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->orderBy('id', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Participant Essays',
            'programEssays' => $programEssays,
            'programId' => $programId
        ];

        return view('submissions/essays/index', $data);
    }

    /**
     * Get essays data for DataTable via AJAX
     */
    public function getData()
    {
        try {
            $programId = session('current_program');

            if (!$programId) {
                return $this->response->setJSON([
                    'draw' => intval($this->request->getPost('draw')),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }

            // Get DataTable parameters
            $draw = intval($this->request->getPost('draw'));
            $start = intval($this->request->getPost('start'));
            $length = intval($this->request->getPost('length'));
            $searchValue = $this->request->getPost('search')['value'] ?? '';

            // Get filter parameters
            $categoryFilter = $this->request->getPost('category');
            $statusFilter = $this->request->getPost('status');

            // Build query to get participants with essays
            $builder = $this->participantModel->builder();
            
            $builder->select('
                participants.id,
                participants.user_id,
                participants.category,
                users.full_name,
                users.email,
                participants.created_at
            ')
            ->join('users', 'users.id = participants.user_id', 'left')
            ->where('participants.program_id', $programId)
            ->where('participants.is_deleted', 0)
            ->groupStart()
                ->where('participants.id IN (SELECT DISTINCT participant_id FROM participant_essays WHERE is_deleted = 0)')
            ->groupEnd();

            // Apply filters
            if (!empty($categoryFilter)) {
                $builder->where('participants.category', $categoryFilter);
            }

            // Apply search
            if (!empty($searchValue)) {
                $builder->groupStart()
                    ->like('users.full_name', $searchValue)
                    ->orLike('users.email', $searchValue)
                    ->orLike('participants.id', $searchValue)
                ->groupEnd();
            }

            // Get total records before pagination
            $totalRecords = $builder->countAllResults(false);

            // Apply pagination
            $builder->orderBy('participants.created_at', 'DESC');
            $builder->limit($length, $start);

            $participants = $builder->get()->getResult();

            // Get essays for each participant
            $data = [];
            foreach ($participants as $participant) {
                $essays = $this->participantEssayModel->getEssaysByParticipantId($participant->id);
                
                // Count answered essays
                $answeredCount = 0;
                if ($essays) {
                    foreach ($essays as $essay) {
                        if (!empty($essay['answer'])) {
                            $answeredCount++;
                        }
                    }
                }
                
                $totalEssays = is_array($essays) ? count($essays) : 0;

                $data[] = [
                    'id' => $participant->id,
                    'user_id' => $participant->user_id,
                    'full_name' => $participant->full_name ?? 'N/A',
                    'email' => $participant->email ?? 'N/A',
                    'category' => $participant->category ?? 'N/A',
                    'essay_count' => $totalEssays,
                    'answered_count' => $answeredCount,
                    'created_at' => $participant->created_at
                ];
            }

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in Essays::getData: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => intval($this->request->getPost('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * View detailed essays for a specific participant
     */
    public function view($participantId)
    {
        $programId = session('current_program');

        if (!$programId) {
            session()->setFlashdata('error', 'Please select a program first');
            return redirect()->to(base_url('welcome'));
        }

        // Get participant data directly from model to avoid ID conflicts
        $participant = $this->participantModel->find($participantId);

        if (!$participant || $participant->program_id != $programId) {
            session()->setFlashdata('error', 'Participant not found or does not belong to current program');
            return redirect()->to(base_url('submissions/essays'));
        }

        // Get user data
        $user = $this->userModel->find($participant->user_id);
        $participant->user = $user;

        // Get participant's essays with questions
        $essays = $this->participantEssayModel->getEssaysByParticipantId($participantId);

        $data = [
            'title' => 'Essay Details - ' . ($participant->full_name ?? 'Participant'),
            'participant' => $participant,
            'essays' => $essays ?? []
        ];

        return view('submissions/essays/view', $data);
    }
}
