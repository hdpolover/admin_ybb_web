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

    // save profile image and upload to storage
    private function saveProfileImage($file, $participantId, $programId)
    {
        // Case 1: If file is a valid uploaded file (from form)
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
            $uploadResult = upload_profile_picture($fileArray, $participantId, $programId);

            if ($uploadResult['status']) {
                return $uploadResult['url'];
            }

            log_message('error', 'Profile image upload failed: ' . $uploadResult['message']);
        }
        // Case 2: If file is a base64 encoded string
        elseif (is_string($file) && preg_match('/^data:image\/(\w+);base64,/', $file, $matches)) {
            // Extract image format and base64 data
            $imageType = $matches[1];
            $base64Data = substr($file, strpos($file, ',') + 1);
            $decodedData = base64_decode($base64Data);

            if (!$decodedData) {
                log_message('error', 'Failed to decode base64 image data');
                return false;
            }

            // Create a temporary file with the decoded data
            $tempFilename = tempnam(sys_get_temp_dir(), 'profile_');
            $tempFilename .= '.' . $imageType;
            file_put_contents($tempFilename, $decodedData);

            // Create a file array for the storage helper
            $fileArray = [
                'name' => 'profile_picture.' . $imageType,
                'type' => 'image/' . $imageType,
                'tmp_name' => $tempFilename,
                'error' => 0,
                'size' => filesize($tempFilename)
            ];

            // Upload using storage helper
            $uploadResult = upload_profile_picture($fileArray, $participantId, $programId);

            // Clean up the temporary file
            @unlink($tempFilename);

            if ($uploadResult['status']) {
                return $uploadResult['url'];
            }

            log_message('error', 'Base64 profile image upload failed: ' . ($uploadResult['message'] ?? 'Unknown error'));
        } else {
            log_message('error', 'Invalid file data provided for profile image upload: ' . gettype($file));
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
            log_message('info', 'Starting updateSubmission for participant ID: ' . $participantId);
            
            if (!$participantId) {
                log_message('error', 'No participant ID provided');
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            // Get participant data to confirm it exists
            $participant = $this->participantModel->find($participantId);
            if (!$participant) {
                log_message('error', 'Participant not found: ' . $participantId);
                return $this->respondError('Participant not found', self::HTTP_NOT_FOUND);
            }
            
            log_message('info', 'Found participant: ' . $participant->id . ' - ' . ($participant->full_name ?? 'unknown'));

            // Get input data (support both JSON and form inputs)
            $input = $this->getInput();
            if (empty($input)) {
                log_message('error', 'No data provided for update');
                return $this->respondError('No data provided for update', self::HTTP_BAD_REQUEST);
            }
            
            log_message('debug', 'Received input data: ' . json_encode($input));

            $db = \Config\Database::connect();
            $db->transStart();
            log_message('info', 'Started database transaction');

            $updatedData = [
                'participant' => null,
                'profile_image' => null,
                'essays' => [],
                'competition_category_id' => null,
                'program_subtheme_id' => null,
                'ambassador_id' => null,
            ];

            // Process profile picture update (if any)
            // Check for profile_image at root level, inside participant object, or in $_FILES
            if (isset($input['profile_image']) || (isset($input['participant']['profile_image']) && !empty($input['participant']['profile_image'])) || isset($_FILES['profile_image'])) {
                log_message('info', 'Processing profile image update');
                
                if (isset($input['profile_image'])) {
                    log_message('debug', 'Found profile_image in root input');
                    $profileImage = $input['profile_image'];
                } elseif (isset($input['participant']['profile_image'])) {
                    log_message('debug', 'Found profile_image in participant data');
                    $profileImage = $input['participant']['profile_image'];
                } else {
                    log_message('debug', 'Found profile_image in $_FILES');
                    $profileImage = $_FILES['profile_image'];
                }

                // Validate and process the profile image
                log_message('debug', 'Attempting to save profile image: ' . (is_string($profileImage) ? 'string data' : 'file data'));
                $pictureUrl = $this->saveProfileImage($profileImage, $participantId, $participant->program_id);

                log_message('debug', 'Profile image upload result: ' . ($pictureUrl ?: 'failed'));
                
                if ($pictureUrl) {
                    $updatedData['picture_url'] = $pictureUrl;
                    log_message('info', 'Updating participant with new picture URL: ' . $pictureUrl);
                    
                    // Update participant's picture_url field
                    $this->participantModel->update($participantId, ['picture_url' => $pictureUrl]);

                    // If participant has a previous profile picture, delete it
                    if (!empty($participant->picture_url)) {
                        // Extract the path from the full URL
                        $oldPath = parse_url($participant->picture_url, PHP_URL_PATH);
                        if ($oldPath) {
                            log_message('debug', 'Deleting old profile picture: ' . $oldPath);
                            delete_storage_file($oldPath);
                        }
                    }
                } else {
                    log_message('error', 'Failed to upload profile picture');
                    return $this->respondError('Failed to upload profile picture', self::HTTP_BAD_REQUEST);
                }
            }

            // Process participant data updates (if any)
            if (isset($input['participant'])) {
                log_message('info', 'Processing participant data updates');
                $participantData = $input['participant'];
                
                log_message('debug', 'Received participant data: ' . json_encode($participantData));

                // Only update allowed fields
                $allowedFields = [
                    'full_name',
                    'birthdate',
                    'gender',
                    'picture_url',
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
                log_message('debug', 'Filtered participant data: ' . json_encode($filteredData));

                if (!empty($filteredData)) {
                    log_message('info', 'Updating participant data');
                    if ($this->participantModel->update($participantId, $filteredData)) {
                        log_message('info', 'Participant data updated successfully');
                        $updatedData['participant'] = $filteredData;
                    } else {
                        log_message('error', 'Failed to update participant data: ' . implode(', ', $this->participantModel->errors()));
                        $db->transRollback();
                        return $this->respondError('Failed to update participant data: ' . implode(', ', $this->participantModel->errors()), self::HTTP_BAD_REQUEST);
                    }
                } else {
                    log_message('debug', 'No valid participant data fields to update');
                }
            }

            // Process essay updates (if any)
            if (isset($input['essays']) && is_array($input['essays'])) {
                log_message('info', 'Processing essays updates, count: ' . count($input['essays']));
                
                foreach ($input['essays'] as $index => $essayData) {
                    // Essay data should contain program_essay_id and answer at minimum
                    if (!isset($essayData['program_essay_id']) || !isset($essayData['answer'])) {
                        log_message('warning', 'Skipping essay at index ' . $index . ', missing required fields');
                        continue;
                    }

                    $essayId = $essayData['program_essay_id'];
                    $answer = $essayData['answer'];
                    log_message('debug', 'Processing essay for program_essay_id: ' . $essayId);

                    // Check if this is an existing essay or a new one
                    $existingEssay = $this->participantEssayModel->where([
                        'participant_id' => $participantId,
                        'program_essay_id' => $essayId
                    ])->first();

                    $saveData = [
                        'participant_id' => $participantId,
                        'program_essay_id' => $essayId,
                        'answer' => $answer,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if ($existingEssay) {
                        // Update existing
                        log_message('debug', 'Updating existing essay, ID: ' . $existingEssay->id);
                        if ($this->participantEssayModel->update($existingEssay->id, $saveData)) {
                            log_message('info', 'Essay updated successfully, ID: ' . $existingEssay->id);
                            $updatedData['essays'][] = array_merge(['id' => $existingEssay->id], $saveData);
                        } else {
                            log_message('error', 'Failed to update essay: ' . implode(', ', $this->participantEssayModel->errors()));
                        }
                    } else {
                        // Create new
                        log_message('debug', 'Creating new essay');
                        $saveData['created_at'] = date('Y-m-d H:i:s');
                        if ($newId = $this->participantEssayModel->insert($saveData)) {
                            log_message('info', 'New essay created successfully, ID: ' . $newId);
                            $updatedData['essays'][] = array_merge(['id' => $newId], $saveData);
                        } else {
                            log_message('error', 'Failed to create new essay: ' . implode(', ', $this->participantEssayModel->errors()));
                        }
                    }
                }
            }

            // Process subtheme selections (if any)
            if (isset($input['program_subtheme_id'])) {
                log_message('info', 'Processing subtheme selection');
                // Get the subtheme ID 
                $subthemeId = $input['program_subtheme_id'];
                log_message('debug', 'Subtheme ID: ' . $subthemeId);

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
                        log_message('debug', 'Updating existing subtheme selection, ID: ' . $existingSelection->id);
                        if ($this->participantSubthemeModel->update($existingSelection->id, $subthemeData)) {
                            log_message('info', 'Subtheme selection updated successfully');
                            $updatedData['program_subtheme_id'] = array_merge(['id' => $existingSelection->id], $subthemeData);
                        } else {
                            log_message('error', 'Failed to update subtheme: ' . implode(', ', $this->participantSubthemeModel->errors()));
                        }
                    } else {
                        // Create new selection
                        log_message('debug', 'Creating new subtheme selection');
                        $subthemeData['created_at'] = date('Y-m-d H:i:s');
                        if ($newId = $this->participantSubthemeModel->insert($subthemeData)) {
                            log_message('info', 'New subtheme selection created successfully, ID: ' . $newId);
                            $updatedData['program_subtheme_id'] = array_merge(['id' => $newId], $subthemeData);
                        } else {
                            log_message('error', 'Failed to create subtheme: ' . implode(', ', $this->participantSubthemeModel->errors()));
                        }
                    }
                }
            }

            // Process competition category (if any)
            if (isset($input['competition_category_id'])) {
                log_message('info', 'Processing competition category');
                // Update the competition category directly in the participants table
                $competitionCategoryId = $input['competition_category_id'];
                log_message('debug', 'Competition category ID: ' . $competitionCategoryId);

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
                        log_message('debug', 'Updating existing competition category, ID: ' . $existingCategory->id);
                        if ($this->participantCompetitionCategoryModel->update($existingCategory->id, $categoryData)) {
                            log_message('info', 'Competition category updated successfully');
                            $updatedData['competition_category_id'] = array_merge(['id' => $existingCategory->id], $categoryData);
                        } else {
                            log_message('error', 'Failed to update competition category: ' . implode(', ', $this->participantCompetitionCategoryModel->errors()));
                        }
                    } else {
                        // Create new selection
                        log_message('debug', 'Creating new competition category');
                        $categoryData['created_at'] = date('Y-m-d H:i:s');
                        if ($newId = $this->participantCompetitionCategoryModel->insert($categoryData)) {
                            log_message('info', 'New competition category created successfully, ID: ' . $newId);
                            $updatedData['competition_category_id'] = array_merge(['id' => $newId], $categoryData);
                        } else {
                            log_message('error', 'Failed to create competition category: ' . implode(', ', $this->participantCompetitionCategoryModel->errors()));
                        }
                    }
                }
            }

            // ambassador referral check
            if (isset($input['ambassador_id'])) {
                log_message('info', 'Processing ambassador referral');
                $ambassadorId = $input['ambassador_id'];
                log_message('debug', 'Ambassador ID: ' . $ambassadorId);

                if ($ambassadorId) {
                    // check if participant already has a referral
                    $existingReferral = $this->ambassadorParticipantReferralModel->where('participant_id', $participantId)->first();

                    if ($existingReferral) {
                        // Update existing referral
                        log_message('debug', 'Updating existing ambassador referral, ID: ' . $existingReferral->id);
                        if ($this->ambassadorParticipantReferralModel->update($existingReferral->id, ['ambassador_id' => $ambassadorId])) {
                            log_message('info', 'Ambassador referral updated successfully');
                            $updatedData['ambassador_id'] = $ambassadorId;
                        } else {
                            log_message('error', 'Failed to update ambassador referral: ' . implode(', ', $this->ambassadorParticipantReferralModel->errors()));
                        }
                    } else {
                        // Create new referral
                        log_message('debug', 'Creating new ambassador referral');
                        $referralData = [
                            'participant_id' => $participantId,
                            'ambassador_id' => $ambassadorId,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];

                        if ($newId = $this->ambassadorParticipantReferralModel->insert($referralData)) {
                            log_message('info', 'New ambassador referral created successfully, ID: ' . $newId);
                            $updatedData['ambassador_id'] = array_merge(['id' => $newId], $referralData);
                        } else {
                            log_message('error', 'Failed to create ambassador referral: ' . implode(', ', $this->ambassadorParticipantReferralModel->errors()));
                        }
                    }
                }
            }

            // Check if any updates were made
            if (
                empty($updatedData['essays']) &&
                empty($updatedData['program_subtheme_id']) &&
                empty($updatedData['participant']) &&
                empty($updatedData['profile_image']) &&
                empty($updatedData['competition_category_id']) &&
                empty($updatedData['ambassador_id'])
            ) {
                log_message('warning', 'No valid data provided for update');
                return $this->respondError('No valid data provided for update', self::HTTP_BAD_REQUEST);
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                log_message('error', 'Transaction failed, rolling back changes');
                return $this->respondError('Failed to save submission data', self::HTTP_INTERNAL_ERROR);
            }
            
            log_message('info', 'Transaction completed successfully');

            $reponseData = [];

            if (!empty($updatedData['participant'])) {
                $reponseData['participant'] = $updatedData['participant'];
            }

            if (!empty($updatedData['profile_image'])) {
                $reponseData['profile_image'] = $updatedData['profile_image'];
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

            // update participant status
            log_message('info', 'Updating participant status');
            $participantStatusModel = new \App\Models\ParticipantStatusModel();
            $participantStatus = $participantStatusModel->where('participant_id', $participantId)->first();
            
            if ($participantStatus) {
                log_message('debug', 'Updating existing participant status, ID: ' . $participantStatus->id);
                $participantStatusModel->update($participantStatus->id, [
                    'form_status' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                log_message('debug', 'Creating new participant status record');
                $participantStatusModel->insert([
                    'participant_id' => $participantId,
                    'form_status' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            log_message('info', 'Participant status updated successfully');
            log_message('info', 'Update submission completed successfully for participant ID: ' . $participantId);

            // Return success response with updated data
            return $this->respondSuccess($reponseData, ResponseInterface::HTTP_OK, 'Submission data updated successfully');
        } catch (\Exception $e) {
            log_message('error', 'Exception in updateSubmission: ' . $e->getMessage());
            log_message('error', 'Exception trace: ' . $e->getTraceAsString());
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
            $pictureUrl = $this->saveProfileImage($_FILES['profile_picture'], $participantId, $participant->program_id);

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

    /**
     * Submit participant form to finalize submission
     * 
     * Updates the participant's form_status to 1 (submitted)
     * 
     * @param int|null $participantId
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function submitForm($participantId = null)
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

            // Update participant's form_status to 2 (submitted)
            $updateData = [
                'form_status' => 2,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $participantStatusModel = new \App\Models\ParticipantStatusModel();

            $participantStatus = $participantStatusModel->where('participant_id', $participantId)->first();

            if ($participantStatus) {
                $participantStatusModel->update($participantStatus->id, $updateData);
            } else {
                $updateData['participant_id'] = $participantId;
                $updateData['created_at'] = date('Y-m-d H:i:s');
                $updateData['updated_at'] = date('Y-m-d H:i:s');

                // Insert new status record
                $updateData['form_status'] = 2;
                $participantStatusModel->insert($updateData);
            }

            // Check if update was successful
            if ($participantStatusModel->errors()) {
                return $this->respondError('Failed to update form status: ' . implode(', ', $participantStatusModel->errors()), self::HTTP_BAD_REQUEST);
            }

            return $this->respondSuccess(
                ['participant_id' => $participantId, 'form_status' => 2],
                ResponseInterface::HTTP_OK,
                'Form submitted successfully'
            );
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }


    /**
     * participants - GET {{api_url}}/submissions/participants/{{participant_id}}
     * Auto-generated method
     */
    public function participants($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement participants logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'participants executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute participants',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * getParticipantSubmissions - GET {{api_url}}/submissions/participants
     * Get submissions for participants
     */
    public function getParticipantSubmissions($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement participants logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'participants executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute participants',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * getByProgramId - GET {{api_url}}/submissions/program/{{program_id}}/form
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
     * update - POST {{api_url}}/submissions/participants/{{participant_id}}/update
     * Auto-generated method
     */
    public function update($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement update logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'update executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute update',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * submit - POST {{api_url}}/submissions/participants/{{participant_id}}/submit
     * Auto-generated method
     */
    public function submit($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement submit logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'submit executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute submit',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
