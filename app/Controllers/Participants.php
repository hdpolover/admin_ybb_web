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
        $page = (int)($this->request->uri->getQuery(['only' => ['page']]) ?? 1);
        $limit = 10;  // Items per page
        $offset = ($page - 1) * $limit;

        try {
            // Use the model directly to get participants
            $result = $this->participantModel->getCurrentProgramParticipants($limit, $offset);

            $data = [
                'participants' => $result,
                'pager' => [
                    'total' => $result['total'] ?? 0,
                    'perPage' => $limit,
                    'currentPage' => $page,
                    'totalPages' => ceil(($result['total'] ?? 0) / $limit)
                ]
            ];

            return view('users/participants/index', $data);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch participants: ' . $e->getMessage());
        }
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
