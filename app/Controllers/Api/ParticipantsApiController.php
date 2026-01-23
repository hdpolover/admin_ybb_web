<?php

namespace App\Controllers\Api;

use App\Models\ParticipantModel;
use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;
use OpenApi\Annotations as OA;

class ParticipantsApiController extends ApiBaseController
{
    protected $model;
    protected $participantStatusModel;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->model = new ParticipantModel();
        $this->participantStatusModel = new \App\Models\ParticipantStatusModel();
    }

    /**
     * 🟢 Get All Participants (READ)
     * GET /api/participants

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
     * 
     * IMPORTANT: Payment data is NEVER cached to ensure real-time information
     */
    public function show($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            // Set cache-prevention headers to ensure fresh participant data (including payments)
            $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
            $this->response->setHeader('Pragma', 'no-cache');
            $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
            
            // Always fetch fresh participant data - NO CACHING for payment-related information
            $participant = $this->model->getParticipant($id);

            if (!$participant) {
                return $this->respondNotFound("Participant not found");
            }

            log_message('info', "Participant data accessed - Participant ID: {$id}, Fresh data served");

            return $this->respondSuccess([
                'participant' => $participant,
                'timestamp' => time(),
                'cache_disabled' => true
            ], self::HTTP_OK, 'Participant retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🆕 Create New Participant (CREATE)
     * POST /api/participants
     * Invalidates user cache after creation
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

            // Check if participant already exists for this user and program
            $existingParticipant = $this->model->where('user_id', $data['user_id'])
                                              ->where('program_id', $data['program_id'])
                                              ->where('is_deleted', 0)
                                              ->first();
            
            if ($existingParticipant) {
                log_message('warning', "Prevented duplicate participant creation (API) for User ID: {$data['user_id']}, Program ID: {$data['program_id']}");
                return $this->respondError('Participant already exists for this program', self::HTTP_CONFLICT);
            }

            // Insert the data
            $participantId = $this->model->insert($data);

            if (!$participantId) {
                return $this->respondError('Failed to create participant', self::HTTP_INTERNAL_ERROR);
            }

            // Invalidate user and program cache after creation
            $this->invalidateUserCache($data['user_id']);
            $this->invalidateProgramCache($data['program_id']);

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
     * Invalidates participant and user cache after update
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

            // Invalidate related cache after update
            $this->invalidateUserCache($participant->user_id);
            $this->invalidateProgramCache($participant->program_id);

            // Get the updated participant
            $updatedParticipant = $this->model->getParticipant($id);

            return $this->respondSuccess($updatedParticipant, self::HTTP_OK, 'Participant updated successfully');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 📸 Get Participants Photos by Program ID
     * GET /api/participants/program/{programId}/photos
     * 
     * Returns up to 5 participant photos from a specific program
     */
    public function getProgramParticipantsPhotos($programId = null)
    {
        try {
            if (!$programId) {
                return $this->respondError('Program ID is required', self::HTTP_BAD_REQUEST);
            }

            // Get up to 5 participants from the program with their photos
            $participants = $this->model->getProgramParticipantsPhotos($programId, 5);

            if (empty($participants)) {
                return $this->respondNotFound("No participant photos found for this program");
            }

            return $this->respondSuccess($participants);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🗑️ Delete Participant (DELETE)
     * DELETE /api/participants/{id}
     * 
  
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
     
     * )
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

    /**
     * 🔍 Get Participant by User ID
     * GET /api/participants/user/{userId}
     * 
     * IMPORTANT: Payment data is NEVER cached to ensure real-time information
     * This endpoint is commonly used by participants to get their own payment data
     **/
    public function getByUserId($userId = null)
    {
        try {
            if (!$userId) {
                return $this->respondError('User ID is required', self::HTTP_BAD_REQUEST);
            }

            // Set cache-prevention headers to ensure fresh participant data (including payments)
            $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
            $this->response->setHeader('Pragma', 'no-cache');
            $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

            // Always fetch fresh participant data - NO CACHING for payment-related information
            $participant = $this->model->getParticipantsByUserId($userId);

            if (!$participant) {
                return $this->respondNotFound("Participant not found for this user");
            }

            log_message('info', "Participant data by user ID accessed - User ID: {$userId}, Fresh data served");

            return $this->respondSuccess([
                'participant' => $participant,
                'timestamp' => time(),
                'cache_disabled' => true
            ], self::HTTP_OK, 'Participant retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 📝 Get Participant Essays
     * GET /api/participants/{id}/essays
     * 
     * Returns all essays submitted by a specific participant
     */
    public function getParticipantEssays($id = null)
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

            // participant essay model
            $participantEssayModel = new \App\Models\ParticipantEssayModel();

            // Get essays from participant
            $essays = $participantEssayModel->getEssaysByParticipantId($id);

            if (empty($essays)) {
                return $this->respondNotFound("No essays found for this participant");
            }

            return $this->respondSuccess($essays);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🔍 Get Participant Subthemes
     * GET /api/participants/{id}/subthemes
     * 
     * Returns all subthemes assigned to a specific participant
     */
    public function getParticipantSubthemes($id = null)
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

            // Get participant subthemes model
            $participantSubthemeModel = new \App\Models\ParticipantSubthemeModel();

            // Get subthemes assigned to the participant
            $subthemes = $participantSubthemeModel->getSubthemesByParticipantId($id);

            if (empty($subthemes)) {
                return $this->respondNotFound("No subthemes found for this participant");
            }

            return $this->respondSuccess($subthemes);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 📊 Get Participant Statuses
     * GET /api/participants/{id}/statuses
     * 
     * Returns all status records for a specific participant
     */
    public function getParticipantStatuses($id = null)
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

            // Get statuses for the participant
            $statuses = $this->participantStatusModel->getParticipantStatusById($id);

            if (empty($statuses)) {
                return $this->respondNotFound("No status records found for this participant");
            }

            return $this->respondSuccess($statuses);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🔍 Get Participant Ambassador Referrals
     * GET /api/participants/{id}/referrals
     * 
     * Returns all ambassador referrals made by a specific participant
     */
    public function getParticipantReferrals($id = null)
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

            // Get ambassador referrals model
            $referralModel = new \App\Models\AmbassadorParticipantReferralModel();

            // Get referrals made by the participant
            $referral = $referralModel->getReferralByParticipantId($id);

            if (empty($referral)) {
                return $this->respondNotFound("No referrals found for this participant");
            }

            // get ambassador details
            $ambassadorModel = new \App\Models\AmbassadorModel();

            $ambassador = $ambassadorModel->getAmbassadorById($referral->ambassador_id);

            if (!$ambassador) {
                return $this->respondNotFound("Ambassador not found for this referral");
            }

            $data = [
                'referral_data' => $referral,
                'ambassador' => $ambassador,
            ];

            return $this->respondSuccess($data, self::HTTP_OK, 'Referral details retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🆕 Create Participant from User ID (CREATE)
     * POST /api/participants/users/{userId}/create
     * 
     * Creates a new participant based on user ID and copies only the full name
     */
    public function createFromUserId($userId = null)
    {
        try {
            if (!$userId) {
                return $this->respondError('User ID is required', self::HTTP_BAD_REQUEST);
            }
            
            // Get input from POST instead of JSON
            $data = $this->request->getPost();

            // Check if POST data is empty
            if (empty($data)) {
                return $this->respondError('Request data is empty', self::HTTP_BAD_REQUEST);
            }

            // Check if user exists
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($userId);
            if (!$user) {
                return $this->respondNotFound("User not found");
            }

            // Check if program_id is provided
            if (!isset($data['program_id'])) {
                return $this->respondError('Program ID is required', self::HTTP_BAD_REQUEST);
            }

            // Check if program exists
            $programModel = new \App\Models\ProgramModel();
            $program = $programModel->find($data['program_id']);
            if (!$program) {
                return $this->respondNotFound("Program with ID {$data['program_id']} not found");
            }

            // Check if participant already exists for this user and program
            $existingParticipant = $this->model->where('user_id', $userId)
                                              ->where('program_id', $data['program_id'])
                                              ->where('is_deleted', 0)
                                              ->first();
            
            if ($existingParticipant) {
                log_message('info', "Prevented duplicate participant creation (User Flow) for User ID: {$userId}, Program ID: {$data['program_id']}");
                // Return existing participant so the flow continues smoothly
                return $this->respondCreated($existingParticipant, 'Participant already exists');
            }

            // Initialize participant data
            $participantData = [
                'user_id' => $userId,
                'program_id' => $data['program_id'],
                'full_name' => isset($data['full_name']) ? $data['full_name'] : $user->full_name,
            ];

            // Add any other provided fields from the request
            foreach ($data as $key => $value) {
                $participantData[$key] = $value;
            }

            // Insert the participant
            $participant = $this->model->createParticipant($participantData);

            if (!$participant) {
                return $this->respondError('Failed to create participant', self::HTTP_INTERNAL_ERROR);
            }

            // save default participant status
            $statusData = [
                'participant_id' => $participant->id,
            ];

            $this->participantStatusModel->save($statusData);

            // Get the newly created participant
            $participant = $this->model->getParticipant($participant->id);

            return $this->respondCreated($participant, 'Participant created successfully');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🔄 Update Participant with Data from Another Participant
     * PUT /api/participants/{targetId}/copy-from/{sourceId}
     * 
     * Copies data from source participant to target participant
     */
    public function updateParticipantWithOtherData($targetId = null, $sourceId = null)
    {
        try {
            if (!$targetId || !$sourceId) {
                return $this->respondError('Both Target and Source Participant IDs are required', self::HTTP_BAD_REQUEST);
            }

            // Check if target participant exists
            $targetParticipant = $this->model->getParticipant($targetId);
            if (!$targetParticipant) {
                return $this->respondNotFound("Target participant not found");
            }

            // Check if source participant exists
            $sourceParticipant = $this->model->getParticipant($sourceId);
            if (!$sourceParticipant) {
                return $this->respondNotFound("Source participant not found");
            }

            // Convert source participant to array and remove id field
            $sourceData = json_decode(json_encode($sourceParticipant), true);

            // Fields to exclude from copying
            $excludeFields = ['id', 'user_id', 'program_id', 'created_at', 'updated_at', 'deleted_at'];

            foreach ($excludeFields as $field) {
                if (isset($sourceData[$field])) {
                    unset($sourceData[$field]);
                }
            }

            // Update target participant with source participant data
            $updated = $this->model->update($targetId, $sourceData);

            if (!$updated) {
                return $this->respondError('Failed to update participant data', self::HTTP_INTERNAL_ERROR);
            }

            // Get the updated participant
            $updatedParticipant = $this->model->getParticipant($targetId);

            return $this->respondSuccess(
                $updatedParticipant,
                self::HTTP_OK,
                'Participant updated successfully with data from source participant'
            );
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🔍 Check Participant Category Switch Eligibility (READ)
     * GET /api/participants/{participantId}/switch-category/check
     * 
     * Checks if a participant is eligible to switch category between 'fully_funded' and 'self_funded'
     * 
     * @param int participantId The participant ID to check eligibility for
     */
    public function checkSwitchCategoryEligibility($participantId = null)
    {
        try {
            if (!$participantId) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            // Check eligibility using model method
            $eligibilityResult = $this->model->checkCategorySwitchEligibility($participantId);

            if (!$eligibilityResult['eligible']) {
                return $this->respondError($eligibilityResult['reason'], self::HTTP_BAD_REQUEST);
            }

            return $this->respondSuccess([
                'eligible' => true,
                'current_category' => $eligibilityResult['current_category'],
                'target_category' => $eligibilityResult['target_category'],
                'target_payment' => $eligibilityResult['target_payment'],
                'message' => "Participant is eligible to switch from {$eligibilityResult['current_category']} to {$eligibilityResult['target_category']}"
            ], self::HTTP_OK, 'Eligibility check completed');

        } catch (\Exception $e) {
            log_message('error', "Error checking category switch eligibility - Participant ID: {$participantId}, Error: " . $e->getMessage());
            return $this->respondError('An error occurred while checking eligibility: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🔄 Switch Participant Category (UPDATE)
     * POST /api/participants/{participantId}/switch-category
     * 
     * Switches participant category between 'fully_funded' and 'self_funded'
     * Uses model-based eligibility checking for optimized queries
     * 
     * @param int participantId The participant ID to switch category for
     */
    public function switchCategory($participantId = null)
    {
        try {
            if (!$participantId) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            // Check eligibility using optimized model method
            $eligibilityResult = $this->model->checkCategorySwitchEligibility($participantId);

            if (!$eligibilityResult['eligible']) {
                return $this->respondError($eligibilityResult['reason'], self::HTTP_BAD_REQUEST);
            }

            // Extract data from eligibility result
            $participant = $eligibilityResult['participant'];
            $currentCategory = $eligibilityResult['current_category'];
            $newCategory = $eligibilityResult['target_category'];

            // Perform the category switch using model method
            $switchResult = $this->model->switchParticipantCategory($participantId, $newCategory);

            if (!$switchResult) {
                return $this->respondError('Failed to switch participant category', self::HTTP_INTERNAL_ERROR);
            }

            // Log the category switch
            log_message('info', "Participant category switched - Participant ID: {$participantId}, From: {$currentCategory}, To: {$newCategory}");

            // Invalidate related cache
            $this->invalidateUserCache($participant->user_id);
            $this->invalidateProgramCache($participant->program_id);

            // Get updated participant data
            $updatedParticipant = $this->model->getParticipant($participantId);

            return $this->respondSuccess([
                'participant' => $updatedParticipant,
                'category_switch' => [
                    'from' => $currentCategory,
                    'to' => $newCategory,
                    'switched_at' => date('Y-m-d H:i:s')
                ]
            ], self::HTTP_OK, "Participant category successfully switched from {$currentCategory} to {$newCategory}");

        } catch (\Exception $e) {
            log_message('error', "Error switching participant category - Participant ID: {$participantId}, Error: " . $e->getMessage());
            return $this->respondError('An error occurred while switching category: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 🔍 Search Participants by Custom Parameters (READ)
     * GET /api/participants/search
     * 
     * IMPORTANT: Payment data is NEVER cached to ensure real-time information
     * When include=payments is used, caching is completely disabled
     *
     * Query Parameters:
     * @param string email Filter by user email
     * @param string full_name Filter by participant full name (partial match)
     * @param int program_id Filter by program ID
     * @param int program_category_id Filter by program category ID
     * @param string gender Filter by gender
     * @param string phone_number Filter by phone number (partial match)
     * @param string nationality Filter by nationality
     * @param string user_full_name Filter by user full name (partial match)
     * @param int page Page number for pagination
     * @param int limit Items per page
     * @param string include Optional comma-separated list of related data to include (essays,payments)
     */
    public function search()
    {
        try {
            // Get search parameters from query string
            $searchParams = $this->request->getGet();
            
            // Remove pagination and special params from search criteria
            $page = (int)($searchParams['page'] ?? 1);
            $limit = (int)($searchParams['limit'] ?? 10);
            $include = $searchParams['include'] ?? '';
            unset($searchParams['page'], $searchParams['limit'], $searchParams['include']);
            
            // Validate that at least one search parameter is provided
            if (empty($searchParams)) {
                return $this->respondError('At least one search parameter is required', self::HTTP_BAD_REQUEST);
            }
            
            // Parse include parameter
            $includeOptions = [];
            if (!empty($include)) {
                $includeOptions = array_map('trim', explode(',', $include));
            }
            
            // Check if payment data is requested - if so, disable caching completely
            $includePayments = in_array('payments', $includeOptions);
            
            if ($includePayments) {
                // Set cache-prevention headers when payments are included
                $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
                $this->response->setHeader('Pragma', 'no-cache');
                $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
                
                // Always fetch fresh data when payments are involved - NO CACHING
                $result = $this->model->searchParticipants($searchParams, $limit, $page, $includeOptions);
                
                log_message('info', "Participant search with payments - Fresh data served, Count: " . count($result['data']));
            } else {
                // Use caching only when payments are NOT requested
                $result = $this->cacheResponse(function() use ($searchParams, $limit, $page, $includeOptions) {
                    return $this->model->searchParticipants($searchParams, $limit, $page, $includeOptions);
                }, array_merge($searchParams, ['page' => $page, 'limit' => $limit, 'include' => $include]), null, 600); // 10 minutes cache
            }
            
            // If no results found
            if (empty($result['data'])) {
                return $this->respondNotFound("No participants found matching the search criteria");
            }
            
            // Prepare response data
            $responseData = [
                'data' => $result['data'],
                'timestamp' => time(),
                'cache_disabled' => $includePayments
            ];
            
            // If only one result, return as object, otherwise return as list
            if (count($result['data']) === 1) {
                return $this->respondSuccess($result['data'][0], self::HTTP_OK, "Participant found", [
                    'total_results' => $result['total'],
                    'timestamp' => time(),
                    'cache_disabled' => $includePayments
                ]);
            } else {
                // Return as paginated list
                $totalPages = ceil($result['total'] / $limit);
                
                return $this->respondSuccess($result['data'], self::HTTP_OK, "Participants found", [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total_items' => $result['total'],
                    'total_pages' => $totalPages,
                    'timestamp' => time(),
                    'cache_disabled' => $includePayments
                ]);
            }
            
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 📄 Get Participant Documents (READ)
     * GET /api/participants/{participant_id}/documents
     * 
     * Returns all program documents visible to this participant.
     * Only shows documents if participant has submitted the form (form_status = 2).
     * For upload documents (is_upload = 1), includes participant-specific details if available.
     */
    public function getParticipantDocuments($participantId = null)
    {
       
        try {
            if (!$participantId) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            // Check if participant exists
            $participant = $this->model->getParticipant($participantId);
            if (!$participant) {
                return $this->respondNotFound("Participant not found");
            }

            // Check if participant has submitted form
            $participantStatus = $this->participantStatusModel->where('participant_id', $participantId)
                                                              ->first();
            
            // If no status record exists or form is not submitted (form_status != 2), return empty
            if (!$participantStatus || $participantStatus->form_status != 2) {
                return $this->respondError(
                    'Documents are only available after submitting the application form', 
                    self::HTTP_FORBIDDEN
                );
            }

            // Get all program documents for this participant's program
            $programDocumentModel = new \App\Models\ProgramDocumentModel();
            $programDocuments = $programDocumentModel->getProgramDocumentsByProgramId($participant->program_id);

            if (empty($programDocuments)) {
                return $this->respondNotFound("No documents found for this program");
            }

            // Process each document and add participant-specific details for upload documents
            $participantProgramDocumentModel = new \App\Models\ParticipantProgramDocumentModel();
            $documentsWithDetails = [];

            foreach ($programDocuments as $document) {
                // Convert to array for easier manipulation
                $documentArray = json_decode(json_encode($document), true);

                // If this is an upload document (is_upload = 1), get participant-specific details
                if ($document->is_upload == 1) {
                    $participantDocument = $participantProgramDocumentModel->getParticipantDocument(
                        $participantId, 
                        $document->id
                    );

                    // Add participant-specific document details
                    $documentArray['participant_document'] = $participantDocument ? 
                        json_decode(json_encode($participantDocument), true) : null;
                } else {
                    // For non-upload documents, set participant_document to null
                    $documentArray['participant_document'] = null;
                }

                $documentsWithDetails[] = $documentArray;
            }

            return $this->respondSuccess($documentsWithDetails, self::HTTP_OK, 'Participant documents retrieved successfully');

        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
        
    }


    /**
     * user - GET {{api_url}}/participants/user/{{user_id}}
     * Auto-generated method
     */
    public function user($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement user logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'user executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute user',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * getByProgramId - GET {{api_url}}/participants/program/{{program_id}}/photos
     * Auto-generated method
     */
    public function getByProgramId($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement getByProgramId logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'getByProgramId executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute getByProgramId',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * essays - GET {{api_url}}/participants/{{participant_id}}/essays
     * Auto-generated method
     */
    public function essays($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement essays logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'essays executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute essays',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * subthemes - GET {{api_url}}/participants/{{participant_id}}/subthemes
     * Auto-generated method
     */
    public function subthemes($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement subthemes logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'subthemes executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute subthemes',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * status - GET {{api_url}}/participants/{{participant_id}}/status
     * Auto-generated method
     */
    public function status($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement status logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'status executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute status',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * documents - GET {{api_url}}/participants/{{participant_id}}/documents
     * Auto-generated method
     */
    public function documents($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement documents logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'documents executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute documents',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * referrals - GET {{api_url}}/participants/{{participant_id}}/referrals
     * Auto-generated method
     */
    public function referrals($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement referrals logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'referrals executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute referrals',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * check - GET {{api_url}}/participants/{{participant_id}}/switch-category/check
     * Auto-generated method
     */
    public function check($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement check logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'check executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute check',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * switchCategoryById - POST {{api_url}}/participants/{{participant_id}}/switch-category
     * Switch participant category by ID
     */
    public function switchCategoryById($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement switch-category logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'switch-category executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute switch-category',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * upload-picture - POST {{api_url}}/participants/{{participant_id}}/upload-picture
     * Auto-generated method
     */
    public function uploadPicture($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement upload-picture logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'upload-picture executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute upload-picture',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
