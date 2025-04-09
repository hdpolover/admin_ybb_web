<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Main Payments API Controller that routes to specialized payment controllers
 * Maintains backward compatibility with the existing API endpoints
 */
class SubmissionApiController extends ApiBaseController
{

    protected $participantModel;
    protected $programSubthemeModel;
    protected $participantSubthemeModel;
    protected $participantEssayModel;
    protected $programEssayModel;
    protected $competitionCategoryModel;
    protected $participantCompetitionCategoryModel;
    protected $ambassadorModel;
    protected $ambassadorParticipantReferralModel;

    /**
     * Initialize controller, set models
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);

        // Initialize models
        $this->participantModel = new \App\Models\ParticipantModel();
        $this->programSubthemeModel = new \App\Models\ProgramSubthemeModel();
        $this->participantSubthemeModel = new \App\Models\ParticipantSubthemeModel();
        $this->participantEssayModel = new \App\Models\ParticipantEssayModel();
        $this->programEssayModel = new \App\Models\ProgramEssayModel();
        $this->competitionCategoryModel = new \App\Models\CompetitionCategoryModel();
        $this->participantCompetitionCategoryModel = new \App\Models\ParticipantCompetitionCategoryModel();
        $this->ambassadorModel = new \App\Models\AmbassadorModel();
        $this->ambassadorParticipantReferralModel = new \App\Models\AmbassadorParticipantReferralModel();

        // Load helpers
        helper(['storage']);
    }

    /**
     * Get submission of participant data
     * 
     * @param int|null $participantId
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index($participantId = null)
    {
        try {
            if (!$participantId) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            // Get participant data
            $participant = $this->participantModel->find($participantId);

            if (!$participant) {
                return $this->respondError('Participant not found', self::HTTP_NOT_FOUND);
            }

            // get essays by participant id
            $essays = $this->participantEssayModel->getEssaysByParticipantId($participantId);

            // get subthemes by participant id
            $subthemes = $this->participantSubthemeModel->getSubthemesByParticipantId($participantId);

            // get all subthemes by program id
            $programId = $participant->program_id;
            $allSubthemes = $this->programSubthemeModel->getAllSubthemes($programId);

            // get all essays by program id
            $allEssays = $this->programEssayModel->getEssaysByProgramId($programId);

            // get all competition categories by program id
            $competitionCategories = $this->competitionCategoryModel->getCategoriesByProgramId($programId);

            // get all competition categories by participant id
            $participantCompetitionCategories = $this->participantCompetitionCategoryModel->getCompetitionCategoriesByParticipantId($participantId);

            // Compile home data
            $data = [
                'participant' => $participant,
                'participant_essays' => $essays,
                'participant_subtheme' => $subthemes,
                'participant_competition_category' => $participantCompetitionCategories,
                'program_subthemes' => $allSubthemes,
                'program_essays' => $allEssays,
                'competition_categories' => $competitionCategories,
            ];

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get submission of participant data by participant ids
     * 
     * @param array|null $participantIds
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getSubmissionByParticipantId($participantId = null)
    {
        try {
            if (!$participantId) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            // Get participant data
            $participant = $this->participantModel->find($participantId);

            if (!$participant) {
                return $this->respondError('Participant not found', self::HTTP_NOT_FOUND);
            }

            // get essays by participant id
            $essays = $this->participantEssayModel->getParticipantEssaysByParticipantId($participantId);

            // get subthemes by participant id
            $subthemes = $this->participantSubthemeModel->getSubthemesByParticipantId($participantId);

            // get competition categories by participant id
            $competitionCategories = $this->participantCompetitionCategoryModel->getCompetitionCategoriesByParticipantId($participantId);

            // Compile home data
            $data = [
                'participant' => $participant,
                'essays' => $essays,
                'subthemes' => $subthemes,
                'competition_categories' => $competitionCategories,
            ];

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get submission form data including program essays and subthemes
     * 
     * @param int|null $programId
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getSubmissionFormData($programId = null)
    {
        try {
            if (!$programId) {
                return $this->respondError('Program ID is required', self::HTTP_BAD_REQUEST);
            }

            // Get program essays
            $essays = $this->programEssayModel->getEssaysByProgramId($programId);

            // Get program subthemes
            $subthemes = $this->programSubthemeModel->getSubthemesByProgramId($programId);

            // get competition categories
            $competitionCategories = $this->competitionCategoryModel->getCategoriesByProgramId($programId);

            if (empty($essays) && empty($subthemes) && empty($competitionCategories)) {
                return $this->respondError('No submission form data found for this program', self::HTTP_NOT_FOUND);
            }

            // Compile form data
            $data = [
                'program_id' => $programId,
                'essays' => $essays,
                'subthemes' => $subthemes,
                'competition_categories' => $competitionCategories,
            ];

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    // save profile picture and upload to storage
    private function saveProfilePicture($file, $participantId)
    {
        // Check if file is a valid uploaded file
        if (!is_array($file) && is_object($file) && $file instanceof \CodeIgniter\HTTP\Files\UploadedFile) {
            // Convert CodeIgniter UploadedFile object to array format used by storage helper
            $fileArray = [
                'name' => $file->getName(),
                'type' => $file->getClientMimeType(),
                'tmp_name' => $file->getTempName(),
                'error' => $file->getError(),
                'size' => $file->getSize()
            ];

            // Upload using storage helper
            $uploadResult = upload_profile_picture($fileArray, $participantId);

            if ($uploadResult['status']) {
                return $uploadResult['url'];
            }

            log_message('error', 'Profile picture upload failed: ' . $uploadResult['message']);
        } else {
            log_message('error', 'Invalid file data provided for profile picture upload');
        }

        return false;
    }

    /**
     * Update participant submission data
     * 
     * Handles partial updates from multiple form tabs
     * 
     * @param int|null $participantId
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function updateSubmission($participantId = null)
    {
        try {
            if (!$participantId) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            // Get participant data to confirm it exists
            $participant = $this->participantModel->find($participantId);
            if (!$participant) {
                return $this->respondError('Participant not found', self::HTTP_NOT_FOUND);
            }

            // Get input data (support both JSON and form inputs)
            $input = $this->getInput();
            if (empty($input)) {
                return $this->respondError('No data provided for update', self::HTTP_BAD_REQUEST);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $updatedData = [
                'participant' => null,
                'profile_picture' => null,
                'essays' => [],
                'competition_category_id' => null,
                'program_subtheme_id' => null,
                'ambassador_id' => null,
            ];

            // Process profile picture update (if any)
            // Check both for 'profile_picture' in the input data and in $_FILES
            if (isset($input['profile_picture']) || isset($_FILES['profile_picture'])) {
                $profilePicture = isset($input['profile_picture']) ? $input['profile_picture'] : $_FILES['profile_picture'];

                // Validate and process the profile picture
                $pictureUrl = $this->saveProfilePicture($profilePicture, $participantId);

                if ($pictureUrl) {
                    $updatedData['profile_picture'] = $pictureUrl;

                    // Update participant's picture_url field
                    $this->participantModel->update($participantId, ['picture_url' => $pictureUrl]);

                    // If participant has a previous profile picture, delete it
                    if (!empty($participant->picture_url)) {
                        // Extract the path from the full URL
                        $oldPath = parse_url($participant->picture_url, PHP_URL_PATH);
                        if ($oldPath) {
                            delete_storage_file($oldPath);
                        }
                    }
                } else {
                    return $this->respondError('Failed to upload profile picture', self::HTTP_BAD_REQUEST);
                }
            }

            // Process participant data updates (if any)
            if (isset($input['participant'])) {
                $participantData = $input['participant'];

                // Only update allowed fields
                $allowedFields = [
                    'full_name',
                    'birthdate',
                    'gender',
                    'origin_address',
                    'current_address',
                    'nationality',
                    'nationality_code',
                    'nationality_flag',
                    'major',
                    'occupation',
                    'education_level',
                    'institution',
                    'organizations',
                    'phone_number',
                    'phone_flag',
                    'country_code',
                    'emergency_country_code',
                    'emergency_phone_flag',
                    'instagram_account',
                    'emergency_account',
                    'contact_relation',
                    'disease_history',
                    'tshirt_size',
                    'experiences',
                    'achievements',
                    'resume_url',
                    'knowledge_source',
                    'source_account_name',
                    'twibbon_link',
                    'requirement_link',
                ];

                $filteredData = array_intersect_key($participantData, array_flip($allowedFields));

                if (!empty($filteredData)) {
                    if ($this->participantModel->update($participantId, $filteredData)) {
                        $updatedData['participant'] = $filteredData;
                    } else {
                        $db->transRollback();
                        return $this->respondError('Failed to update participant data: ' . implode(', ', $this->participantModel->errors()), self::HTTP_BAD_REQUEST);
                    }
                }
            }

            // Process essay updates (if any)
            if (isset($input['essays']) && is_array($input['essays'])) {
                foreach ($input['essays'] as $essayData) {
                    // Essay data should contain id and answer at minimum
                    if (!isset($essayData['id']) || !isset($essayData['answer'])) {
                        continue;
                    }

                    $essayId = $essayData['id'];
                    // Check if this is an existing essay or a new one
                    $existingEssay = $this->participantEssayModel->where([
                        'participant_id' => $participantId,
                        'program_essay_id' => $essayId
                    ])->first();

                    $saveData = [
                        'participant_id' => $participantId,
                        'program_essay_id' => $essayId,
                        'answer' => $essayData['answer'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if ($existingEssay) {
                        // Update existing
                        if ($this->participantEssayModel->update($existingEssay->id, $saveData)) {
                            $updatedData['essays'][] = array_merge(['id' => $existingEssay->id], $saveData);
                        }
                    } else {
                        // Create new
                        $saveData['created_at'] = date('Y-m-d H:i:s');
                        if ($newId = $this->participantEssayModel->insert($saveData)) {
                            $updatedData['essays'][] = array_merge(['id' => $newId], $saveData);
                        }
                    }
                }
            }

            // Process subtheme selections (if any)
            if (isset($input['program_subtheme_id'])) {
                // Get the subtheme ID 
                $subthemeId = $input['program_subtheme_id'];

                if ($subthemeId) {
                    // Check if participant already has a subtheme
                    $existingSelection = $this->participantSubthemeModel->where('participant_id', $participantId)->first();

                    $subthemeData = [
                        'participant_id' => $participantId,
                        'program_subtheme_id' => $subthemeId,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if ($existingSelection) {
                        // Update existing selection
                        if ($this->participantSubthemeModel->update($existingSelection->id, $subthemeData)) {
                            $updatedData['program_subtheme_id'] = array_merge(['id' => $existingSelection->id], $subthemeData);
                        }
                    } else {
                        // Create new selection
                        $subthemeData['created_at'] = date('Y-m-d H:i:s');
                        if ($newId = $this->participantSubthemeModel->insert($subthemeData)) {
                            $updatedData['program_subtheme_id'] = array_merge(['id' => $newId], $subthemeData);
                        }
                    }
                }
            }

            // Process competition category (if any)
            if (isset($input['competition_category_id'])) {
                // Update the competition category directly in the participants table
                $competitionCategoryId = $input['competition_category_id'];

                if ($competitionCategoryId) {
                    // Check if participant already has a competition category
                    $existingCategory = $this->participantCompetitionCategoryModel->where('participant_id', $participantId)->first();

                    $categoryData = [
                        'participant_id' => $participantId,
                        'competition_category_id' => $competitionCategoryId,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if ($existingCategory) {
                        // Update existing selection
                        if ($this->participantCompetitionCategoryModel->update($existingCategory->id, $categoryData)) {
                            $updatedData['competition_category_id'] = array_merge(['id' => $existingCategory->id], $categoryData);
                        }
                    } else {
                        // Create new selection
                        $categoryData['created_at'] = date('Y-m-d H:i:s');
                        if ($newId = $this->participantCompetitionCategoryModel->insert($categoryData)) {
                            $updatedData['competition_category_id'] = array_merge(['id' => $newId], $categoryData);
                        }
                    }
                }
            }

            // ambassador referral check
            if (isset($input['ambassador_id'])) {
                $ambassadorId = $input['ambassador_id'];

                if ($ambassadorId) {
                    // check if participant already has a referral
                    $existingReferral = $this->ambassadorParticipantReferralModel->where('participant_id', $participantId)->first();

                    if ($existingReferral) {
                        // Update existing referral
                        if ($this->ambassadorParticipantReferralModel->update($existingReferral->id, ['ambassador_id' => $ambassadorId])) {
                            $updatedData['ambassador_id'] = $ambassadorId;
                        }
                    } else {
                        // Create new referral
                        $referralData = [
                            'participant_id' => $participantId,
                            'ambassador_id' => $ambassadorId,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];

                        if ($newId = $this->ambassadorParticipantReferralModel->insert($referralData)) {
                            $updatedData['ambassador_id'] = array_merge(['id' => $newId], $referralData);
                        }
                    }
                }
            }

            // Check if any updates were made
            if (
                empty($updatedData['essays']) &&
                empty($updatedData['program_subtheme_id']) &&
                empty($updatedData['participant']) &&
                empty($updatedData['profile_picture']) &&
                empty($updatedData['competition_category_id']) &&
                empty($updatedData['ambassador_id'])
            ) {
                return $this->respondError('No valid data provided for update', self::HTTP_BAD_REQUEST);
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                return $this->respondError('Failed to save submission data', self::HTTP_INTERNAL_ERROR);
            }

            $reponseData = [];

            if (!empty($updatedData['participant'])) {
                $reponseData['participant'] = $updatedData['participant'];
            }

            if (!empty($updatedData['profile_picture'])) {
                $reponseData['profile_picture'] = $updatedData['profile_picture'];
            }

            if (!empty($updatedData['essays'])) {
                $reponseData['essays'] = $updatedData['essays'];
            }

            if (!empty($updatedData['program_subtheme_id'])) {
                $reponseData['participant_subtheme'] = $updatedData['program_subtheme_id'];
            }

            if (!empty($updatedData['competition_category_id'])) {
                $reponseData['participant_competition_category'] = $updatedData['competition_category_id'];
            }

            if (!empty($updatedData['ambassador_id'])) {
                $reponseData['ambassador_id'] = $updatedData['ambassador_id'];
            }

            // Return success response with updated data
            return $this->respondSuccess($reponseData, ResponseInterface::HTTP_OK, 'Submission data updated successfully');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Upload profile picture for a participant
     * 
     * Separate endpoint just for profile picture uploads
     * 
     * @param int|null $participantId
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function uploadProfilePicture($participantId = null)
    {
        try {
            if (!$participantId) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            // Get participant data to confirm it exists
            $participant = $this->participantModel->find($participantId);
            if (!$participant) {
                return $this->respondError('Participant not found', self::HTTP_NOT_FOUND);
            }

            // Check if file is uploaded
            if (empty($_FILES['profile_picture'])) {
                return $this->respondError('No profile picture file uploaded', self::HTTP_BAD_REQUEST);
            }

            // Upload the profile picture
            $pictureUrl = $this->saveProfilePicture($_FILES['profile_picture'], $participantId);

            if (!$pictureUrl) {
                return $this->respondError('Failed to upload profile picture', self::HTTP_BAD_REQUEST);
            }

            // If participant has a previous profile picture, delete it
            if (!empty($participant->profile_picture)) {
                // Extract the path from the full URL
                $oldPath = parse_url($participant->profile_picture, PHP_URL_PATH);
                if ($oldPath) {
                    delete_storage_file($oldPath);
                }
            }

            // Update participant's profile_picture field
            $this->participantModel->update($participantId, ['profile_picture' => $pictureUrl]);

            return $this->respondSuccess(
                ['profile_picture_url' => $pictureUrl],
                ResponseInterface::HTTP_OK,
                'Profile picture uploaded successfully'
            );
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }
}
