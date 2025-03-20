<?php

namespace App\Controllers\Api;

use App\Models\ParticipantModel;
use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;

class ParticipantsApiController extends ApiBaseController
{
    protected $model;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->model = new ParticipantModel();
    }

   /**
     * 🟢 Get All Participants (READ)
     * GET /api/participants
     * 
     * Query Parameters:
     * @param int page Page number
     * @param int limit Items per page
     * @param int program_id Filter by program ID
     * @param string status Filter by status
     */
    public function index()
    {
        try {
            // Pagination params
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 10);
            $offset = ($page - 1) * $limit;

            // Build filters from query params
            $filters = [];
         
            // Add any additional filters from query params
            foreach ($this->request->getGet() as $key => $value) {
                if (!in_array($key, ['page', 'limit'])) {
                    $filters[$key] = $value;
                }
            }
            
            // Get data using model method
            $result = $this->model->getParticipants($limit, $offset, $filters);
            
            $totalPages = ceil($result['total'] / $limit);

            // If no data found return 404
            if (empty($result['data'])) {
                return $this->respondNotFound("No participants found");
            }
            
            return $this->respondSuccess($result['data'], self::HTTP_OK, "Success", [
                'current_page' => $page,
                'per_page' => $limit,
                'total_items' => $result['total'],
                'total_pages' => $totalPages
            ]);
            
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🔍 Get Single Participant (READ)
     * GET /api/participants/{id}
     */
    public function show($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            $participant = $this->model->getParticipant($id);
            
            if (!$participant) {
                return $this->respondNotFound("Participant not found");
            }
            
            return $this->respondSuccess($participant);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🆕 Create New Participant (CREATE)
     * POST /api/participants
     */
    public function create()
    {
        try {
            $data = $this->request->getJSON(true);
            
            // Validation rules for creating a participant
            $validationRules = [
                'full_name' => 'required|min_length[3]',
                'user_id' => 'required|numeric|is_not_unique[users.id]',
                'program_id' => 'required|numeric',
                'phone_number' => 'required',
                'gender' => 'required|in_list[male,female]',
                'birthdate' => 'required|valid_date'
            ];
            
            if (!$this->validate($validationRules)) {
                return $this->respondValidationErrors($this->validator->getErrors());
            }
            
            // Insert the data
            $participantId = $this->model->insert($data);
            
            if (!$participantId) {
                return $this->respondError('Failed to create participant', self::HTTP_INTERNAL_ERROR);
            }
            
            // Get the newly created participant
            $participant = $this->model->getParticipant($participantId);
            
            return $this->respondCreated($participant, 'Participant created successfully');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * ✏️ Update Participant (UPDATE)
     * PUT /api/participants/{id}
     */
    public function update($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }
            
            // Check if participant exists
            $participant = $this->model->getParticipant($id);
            if (!$participant) {
                return $this->respondNotFound("Participant not found");
            }
            
            $data = $this->request->getJSON(true);
            
            // Validation rules for updating a participant
            $validationRules = [
                'full_name' => 'permit_empty|min_length[3]',
                'program_id' => 'permit_empty|numeric',
                'phone_number' => 'permit_empty',
                'gender' => 'permit_empty|in_list[male,female]',
                'birthdate' => 'permit_empty|valid_date'
            ];
            
            if (!$this->validate($validationRules)) {
                return $this->respondValidationErrors($this->validator->getErrors());
            }
            
            // Update the participant
            $updated = $this->model->update($id, $data);
            
            if (!$updated) {
                return $this->respondError('Failed to update participant', self::HTTP_INTERNAL_ERROR);
            }
            
            // Get the updated participant
            $updatedParticipant = $this->model->getParticipant($id);
            
            return $this->respondSuccess($updatedParticipant, self::HTTP_OK, 'Participant updated successfully');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🗑️ Delete Participant (DELETE)
     * DELETE /api/participants/{id}
     */
    public function delete($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }
            
            // Check if participant exists
            $participant = $this->model->getParticipant($id);
            if (!$participant) {
                return $this->respondNotFound("Participant not found");
            }
            
            // Delete participant (or soft delete depending on your model)
            $deleted = $this->model->delete($id);
            
            if (!$deleted) {
                return $this->respondError('Failed to delete participant', self::HTTP_INTERNAL_ERROR);
            }
            
            return $this->respondNoContent('Participant deleted successfully');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🔍 Get Participants by Program ID
     * GET /api/participants/program/{programId}
     */
    public function getByProgram($programId = null)
    {
        try {
            if (!$programId) {
                return $this->respondError('Program ID is required', self::HTTP_BAD_REQUEST);
            }
            
            $participants = $this->model->getParticipantsByProgramId($programId);
            
            if (empty($participants)) {
                return $this->respondNotFound("No participants found for this program");
            }
            
            return $this->respondSuccess($participants);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🔍 Get Current Program Participants
     * GET /api/participants/current-program
     */
    public function getCurrentProgramParticipants()
    {
        try {
            // Pagination params
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 10);
            $offset = ($page - 1) * $limit;
            
            // Build filters from query params (excluding page and limit)
            $filters = [];
            foreach ($this->request->getGet() as $key => $value) {
                if (!in_array($key, ['page', 'limit'])) {
                    $filters[$key] = $value;
                }
            }
            
            // Get participants from current program
            $result = $this->model->getCurrentProgramParticipants($limit, $offset, $filters);
            
            if (empty($result['data'])) {
                return $this->respondNotFound("No participants found in current program");
            }
            
            $totalPages = ceil($result['total'] / $limit);
            
            return $this->respondSuccess($result['data'], self::HTTP_OK, "Success", [
                'current_page' => $page,
                'per_page' => $limit,
                'total_items' => $result['total'],
                'total_pages' => $totalPages
            ]);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }
}
