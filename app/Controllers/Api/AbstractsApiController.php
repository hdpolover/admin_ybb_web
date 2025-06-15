<?php

namespace App\Controllers\Api;

use App\Models\AbstractModel;
use App\Models\AbstractVersionModel;
use App\Models\AbstractAuthorModel;
use App\Models\AbstractFeedbackModel;
use App\Models\AbstractReviewerModel;
use App\Models\ProgramModel;
use App\Models\ParticipantModel;
use App\Models\ParticipantStatusModel;
use App\Models\AbstractTopicModel;
use App\Models\AbstractSettingModel;
use App\Models\UserModel;
// participant subtheme model
use App\Models\ParticipantSubthemeModel;

use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;

class AbstractsApiController extends ApiBaseController
{
    protected $abstractModel;
    protected $abstractVersionModel;
    protected $abstractAuthorModel;
    protected $abstractFeedbackModel;
    protected $abstractReviewerModel;
    protected $programModel;
    protected $participantModel;
    protected $abstractTopicModel;
    protected $abstractSettingModel;
    protected $participantStatusModel;
    protected $userModel;
    protected $participantSubthemeModel;

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
        $this->abstractModel = new AbstractModel();
        $this->abstractVersionModel = new AbstractVersionModel();
        $this->abstractAuthorModel = new AbstractAuthorModel();
        $this->abstractFeedbackModel = new AbstractFeedbackModel();
        $this->abstractReviewerModel = new AbstractReviewerModel();
        $this->programModel = new ProgramModel();
        $this->participantModel = new ParticipantModel();
        $this->abstractTopicModel = new AbstractTopicModel();
        $this->abstractSettingModel = new AbstractSettingModel();
        $this->participantStatusModel = new ParticipantStatusModel();
        $this->userModel = new UserModel();
        $this->participantSubthemeModel = new ParticipantSubthemeModel();
    }

    /**
     * Get abstract by id
     * GET /api/abstracts/{id}
     */
    public function getAbstractById($id)
    {
        // Check if abstract exists
        $abstract = $this->abstractModel->find($id);

        if (!$abstract) {
            return $this->failNotFound('Abstract not found');
        }

        return $this->respondSuccess(
            $abstract
        );
    }


    /**
     * Get all abstract versions by abstract id
     * GET /api/abstracts/{abstract_id}/versions
     */
    public function getAllAbstractVersionsByAbstractId($abstract_id)
    {
        // Check if abstract exists
        $abstract = $this->abstractModel->find($abstract_id);

        if (!$abstract) {
            return $this->failNotFound('Abstract not found');
        }

        try {
            // Get all abstract versions by abstract id
            $abstractVersions = $this->abstractVersionModel->getAllAbstractVersionsByAbstractId($abstract_id);

            return $this->respondSuccess(
                $abstractVersions
            );
        } catch (\Exception $e) {
            return $this->failServerError('Failed to retrieve abstract versions: ' . $e->getMessage());
        }
    }

    /**
     * Get all abstracts by program id
     * GET /api/abstracts/program/{program_id}
     */
    public function getAllAbstractsByProgramId($program_id)
    {
        // Check if program exists
        $program = $this->programModel->find($program_id);

        if (!$program) {
            return $this->failNotFound('Program not found');
        }

        try {
            // Get all abstracts by program id
            $abstracts = $this->abstractModel->getAllAbstractsByProgramId($program_id);

            return $this->respondSuccess([
                'abstracts' => $abstracts,
                'total' => count($abstracts)
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to retrieve abstracts: ' . $e->getMessage());
        }
    }

    /**
     * Get all abstracts by participant id
     * GET /api/abstracts/participant/{participant_id}
     */
    public function getAbstractByParticipantId($participant_id)
    {
        // Check if participant exists
        $participant = $this->participantModel->find($participant_id);

        if (!$participant) {
            return $this->failNotFound('Participant not found');
        }

        $data = [];

        try {
            // Get all abstracts by participant id
            $abstracts = $this->abstractModel->getAllAbstractsByParticipantId($participant_id);

            return $this->respondSuccess([
                'abstracts' => $abstracts,
                'total' => count($abstracts)
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to retrieve abstracts: ' . $e->getMessage());
        }
    }

    /**
     * Get abstract version by id
     * GET /api/abstracts/version/{id}
     */
    public function getAbstractVersionById($id)
    {
        // Check if abstract version exists
        $abstractVersion = $this->abstractVersionModel->find($id);

        if (!$abstractVersion) {
            return $this->failNotFound('Abstract version not found');
        }

        try {
            // Get abstract version by id
            $abstractVersionDetails = $this->abstractVersionModel->getAbstractVersionById($id);

            return $this->respondSuccess([
                'abstract_version' => $abstractVersionDetails
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to retrieve abstract version: ' . $e->getMessage());
        }
    }


    function isEligibleForSubmission($participant_id)
    {
        // Check if participant is eligible for abstract submission
        $hasSubmittedForm = false;

        // get participant status from the model
        $participantStatusResult = $this->participantStatusModel->getParticipantStatusById($participant_id);

        if ($participantStatusResult && is_object($participantStatusResult)) {
            // get form status from the field
            // form_status of 2 means the participant has already submitted the form and is eligible for abstract submission
            $hasSubmittedForm = $participantStatusResult->form_status == 2;
        }

        return $hasSubmittedForm;
    }

    /**
     * Get abstract details by participant id
     * GET /api/abstracts/participant/{participant_id}/details
     */
    public function getAbstractDetailsByParticipantId($participant_id)
    {
        // Check if participant exists
        $participant = $this->participantModel->find($participant_id);

        if (!$participant) {
            return $this->failNotFound('Participant not found');
        }

        $participantSubtheme = $this->participantSubthemeModel->getSubthemesByParticipantId($participant_id);

        $data = [
            'participant_id' => $participant->id,
            'selected_subtheme' => $participantSubtheme ? $participantSubtheme : null,
            'eligible_for_abstract' => $this->isEligibleForSubmission($participant_id),
            'abstract' => null,
        ];

        try {
            // Get abstract where participant is primary author
            $abstract = $this->abstractModel->getByPrimaryParticipantId($participant_id);

            if (!$abstract) {
                // Check if participant is listed as an author on any abstract
                $abstractAsAuthor = $this->abstractAuthorModel->getAbstractByAuthorParticipantId($participant_id);

                if ($abstractAsAuthor) {
                    // Get the full abstract details
                    $abstract = $this->abstractModel->find($abstractAsAuthor->abstract_id);
                }
            }

            if ($abstract) {
                // Convert the abstract object to an array
                $data['abstract'] = (array) $abstract;

                // Get additional details for the abstract
                $abstractVersions = $this->abstractVersionModel->getAllAbstractVersionsByAbstractId($abstract->id);
                $abstractAuthors = $this->abstractAuthorModel->getAllAbstractAuthorsByAbstractId($abstract->id);

                $data['abstract']['versions'] = $abstractVersions;
                $data['abstract']['authors'] = $abstractAuthors;

                // Get feedbacks for the abstract
                $abstractFeedbacks = $this->abstractFeedbackModel->getAllFeedbacksForAbstract($abstract->id);
                $data['abstract']['feedbacks'] = $abstractFeedbacks;

                // Get assigned reviewers for the abstract
                // $assignedReviewers = $this->abstractFeedbackModel->getAvailableReviewersForAbstract($abstract->id);
                // $data['abstract']['reviewers'] = $assignedReviewers;
            }

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to retrieve abstract details: ' . $e->getMessage());
        }
    }

    /**
     * Get all abstract authors by abstract id
     * GET /api/abstracts/{abstract_id}/authors
     */
    public function getAllAbstractAuthorsByAbstractId($abstract_id)
    {
        // Check if abstract exists
        $abstract = $this->abstractModel->find($abstract_id);

        if (!$abstract) {
            return $this->failNotFound('Abstract not found');
        }

        try {
            // Get all abstract authors by abstract id
            $abstractAuthors = $this->abstractAuthorModel->getAllAbstractAuthorsByAbstractId($abstract_id);

            return $this->respondSuccess([
                'abstract_authors' => $abstractAuthors,
                'total' => count($abstractAuthors)
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to retrieve abstract authors: ' . $e->getMessage());
        }
    }

    /**
     * Add a new abstract author
     * POST /api/abstracts/{abstract_id}/authors
     */
    public function addAbstractAuthor($abstract_id)
    {
        // Check if abstract exists
        $abstract = $this->abstractModel->find($abstract_id);

        if (!$abstract) {
            return $this->failNotFound('Abstract not found');
        }

        // Get is_participant value first to determine validation rules
        $is_participant = (int)$this->request->getPost('is_participant');        // Set validation rules based on is_participant value
        $rules = [
            'full_name' => 'required|string',
            'institution' => 'string',
            'email' => 'required|valid_email',
            'is_participant' => 'required|in_list[0,1]'
        ];

        // participant_id is required only if is_participant is 1
        if ($is_participant === 1) {
            $rules['participant_id'] = 'required|numeric';
        }

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }        // Process participant_id based on is_participant value
        $participant_id = $this->request->getPost('participant_id');

        // Check if participant exists only if is_participant is 1
        if ($is_participant === 1) {
            if (empty($participant_id)) {
                return $this->fail('Participant ID is required when is_participant is true', 400);
            }
            $participant = $this->participantModel->find($participant_id);
            if (!$participant) {
                return $this->failNotFound('Participant not found');
            }

            // Check if participant is eligible for abstract submission
            if (!$this->isEligibleForSubmission($participant_id)) {
                return $this->fail('Participant is not eligible for abstract submission. Form status must be 2 (submitted).', 403);
            }
        }        // Check if author already exists for this abstract (only if participant_id is provided)
        if (!empty($participant_id)) {
            $existingAuthor = $this->abstractAuthorModel->where('abstract_id', $abstract_id)
                ->where('participant_id', $participant_id)
                ->first();

            if ($existingAuthor) {
                return $this->failResourceExists('This participant is already an author of this abstract');
            }
        } else {
            // If no participant_id, check by email since that's unique
            $existingAuthor = $this->abstractAuthorModel->where('abstract_id', $abstract_id)
                ->where('email', $this->request->getPost('email'))
                ->first();

            if ($existingAuthor) {
                return $this->failResourceExists('An author with this email is already associated with this abstract');
            }
        }

        // Check if author email is already assigned to any abstract within the same program
        // This ensures one participant can only be in one abstract at a time per program
        $authorEmail = $this->request->getPost('email');
        $existingAuthorInProgram = $this->abstractAuthorModel->checkAuthorEmailInProgram($authorEmail, $abstract->program_id, $abstract_id);

        if ($existingAuthorInProgram) {
            return $this->fail(
                'This author email is already assigned to another abstract (ID: ' . $existingAuthorInProgram->abstract_id . ') in the same program. One participant can only be assigned to one abstract at a time per program.',
                409
            );
        }

        // Additional checks only if is_participant is true and participant_id is provided
        if ($is_participant === 1 && !empty($participant_id)) {
            // Check if participant is already associated with any abstract as a primary submitter
            $existingAbstract = $this->abstractModel->where('participant_id', $participant_id)->first();

            if ($existingAbstract) {
                return $this->fail('This participant is already a primary submitter for another abstract', 409);
            }
        }
        // If not a participant, no additional checks are needed as they can be associated with multiple abstracts

        try {            $authorData = [
                'abstract_id' => $abstract_id,
                'full_name' => $this->request->getPost('full_name'),
                'institution' => $this->request->getPost('institution'),
                'email' => $this->request->getPost('email'),
                'is_participant' => $is_participant,
                'is_active' => 1,
                'is_deleted' => 0
            ];

            // Add participant_id only if it's provided and is_participant is 1
            if ($is_participant === 1 && !empty($participant_id)) {
                $authorData['participant_id'] = $participant_id;
            }
            // For non-participants, we don't include participant_id field at all// Add new abstract author
            log_message('debug', 'Attempting to insert author data: ' . json_encode($authorData));
            
            $insertResult = $this->abstractAuthorModel->insert($authorData);
            
            // Check if insert was successful
            if ($insertResult === false) {
                $errors = $this->abstractAuthorModel->errors();
                log_message('error', 'Failed to insert author: ' . json_encode($errors));
                return $this->failServerError('Failed to insert author: ' . implode(', ', $errors));
            }
            
            $authorId = $this->abstractAuthorModel->getInsertID();
            
            // Debug: Log the author ID
            log_message('debug', 'Author ID after insert: ' . $authorId);
            
            // Check if we got a valid ID
            if (!$authorId || $authorId === 0) {
                log_message('error', 'Insert seemed successful but got invalid author ID: ' . $authorId);
                return $this->failServerError('Failed to get author ID after insert');
            }            // Get the newly created author using a more reliable method
            $newAuthor = null;
            if ($authorId) {
                // First, verify the record exists in the database
                $db = \Config\Database::connect();
                $dbCheck = $db->query("SELECT * FROM abstract_authors WHERE id = ?", [$authorId]);
                $dbResult = $dbCheck->getRow();
                
                log_message('debug', 'Direct DB check result: ' . ($dbResult ? json_encode($dbResult) : 'No record found'));
                
                // Try model methods
                $newAuthor = $this->abstractAuthorModel->where('id', $authorId)->first();
                
                // If still null, try without any conditions
                if (!$newAuthor) {
                    log_message('debug', 'Author not found with where clause, trying direct select');
                    $builder = $this->abstractAuthorModel->builder();
                    $newAuthor = $builder->select('*')->where('id', $authorId)->get()->getRow();
                }
                
                // If still null, construct the author object from the data we have
                if (!$newAuthor && $dbResult) {
                    log_message('debug', 'Using direct DB result as fallback');
                    $newAuthor = $dbResult;
                } elseif (!$newAuthor) {
                    log_message('debug', 'Creating author object from inserted data');
                    $newAuthor = (object) array_merge($authorData, ['id' => $authorId]);
                    $newAuthor->created_at = date('Y-m-d H:i:s');
                    $newAuthor->updated_at = date('Y-m-d H:i:s');
                }
            }

            // Debug: Log if author was found
            log_message('debug', 'Author found after insert: ' . ($newAuthor ? 'Yes' : 'No'));
            if ($newAuthor) {
                log_message('debug', 'Author data: ' . json_encode($newAuthor));
            }

            return $this->respondCreated([
                'message' => 'Abstract author created successfully',
                'author' => $newAuthor
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to add abstract author: ' . $e->getMessage());
        }
    }

    /**
     * Create a new abstract and set the creator as primary participant and author
     * POST /api/abstracts
     */    public function createAbstract()
    {
        // Validate request
        $rules = [
            'program_id' => 'required|numeric',
            'primary_participant_id' => 'required|numeric',
            'title' => 'required|string',
            'content' => 'permit_empty|string',
            'keywords' => 'permit_empty|string',
            'refs' => 'permit_empty|string',
            'status' => 'permit_empty|string|in_list[draft,submitted,under_review,accepted,rejected]', // Default to 'draft'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Check if program exists
        $program_id = $this->getInput('program_id');
        $program = $this->programModel->find($program_id);

        if (!$program) {
            return $this->failNotFound('Program not found');
        }

        // Check if participant exists
        $participant_id = $this->getInput('primary_participant_id');
        $participant = $this->participantModel->find($participant_id);
        if (!$participant) {
            return $this->failNotFound('Participant not found');
        }

        // Check if participant is eligible for abstract submission
        if (!$this->isEligibleForSubmission($participant_id)) {
            return $this->fail('Participant is not eligible for abstract submission. Form status must be 2 (submitted).', 403);
        }

        // Check if participant is already a primary submitter for any abstract
        $existingAbstract = $this->abstractModel->where('primary_participant_id', $participant_id)->first();

        if ($existingAbstract) {
            return $this->fail('This participant is already a primary submitter for another abstract', 409);
        }        // Check if participant is already an author in any abstract
        $existingAsAuthor = $this->abstractAuthorModel->where('participant_id', $participant_id)->first();
        if ($existingAsAuthor) {
            return $this->fail('This participant is already an author for another abstract', 409);
        }

        // Get participant's selected subtheme
        $participantSubtheme = $this->participantSubthemeModel->getSubthemesByParticipantId($participant_id);

        if (!$participantSubtheme) {
            return $this->fail('Participant must select a subtheme before creating an abstract', 400);
        }

        // Validate word limits based on program's abstract settings
        $title = $this->getInput('title');
        $content = $this->getInput('content');
        $keywords = $this->getInput('keywords');
        $refs = $this->getInput('refs') ?? '';

        $validation = $this->validateWordLimits($program_id, $title, $content, $keywords, $refs);
        if (!$validation['valid']) {
            return $this->fail('Word limit validation failed: ' . implode(', ', $validation['errors']), 400);
        }

        try { // Begin transaction
            $this->abstractModel->db->transBegin();            // Create abstract data
            $abstractData = [
                'program_id' => $program_id,
                'primary_participant_id' => $participant_id, // Set participant as primary submitter
                'program_subtheme_id' => $participantSubtheme->program_subtheme_id, // Set from participant's selected subtheme
                'status' => 'draft', // Default status
                'is_active' => 1,
                'is_deleted' => 0
            ];

            // Insert abstract
            $this->abstractModel->insert($abstractData);
            $abstract_id = $this->abstractModel->getInsertID(); // Create first version of abstract
            $versionData = [
                'abstract_id' => $abstract_id,
                'version_number' => 1,
                'title' => $this->getInput('title'),
                'content' => $this->getInput('content'),
                'keywords' => $this->getInput('keywords'),
                'refs' => $this->getInput('refs') ?? '',
                'is_active' => 1,
                'is_deleted' => 0
            ];

            // Insert abstract version
            $this->abstractVersionModel->insert($versionData);

            // get user by participant_id
            $participant = $this->participantModel->find($participant_id);
            if (!$participant) {
                return $this->failNotFound('Participant not found');
            }

            // Check if participant is a user
            $user = $this->userModel->find($participant->user_id);

            if (!$user) {
                return $this->failNotFound('User not found for the participant');
            }

            // Add the participant as an author
            $authorData = [
                'abstract_id' => $abstract_id,
                'participant_id' => $participant_id,
                'full_name' => $participant->full_name,
                'institution' => $participant->institution,
                'email' => $user->email,
                'is_participant' => 1, // Mark as a participant
                'is_active' => 1,
                'is_deleted' => 0
            ];

            // Insert author
            $this->abstractAuthorModel->insert($authorData);

            // Commit transaction if all operations are successful
            if ($this->abstractModel->db->transStatus() === false) {
                $this->abstractModel->db->transRollback();
                return $this->failServerError('Failed to create abstract');
            } else {
                $this->abstractModel->db->transCommit();
            }

            // Get the newly created abstract with its details
            $newAbstract = $this->abstractModel->getAbstractById($abstract_id);

            return $this->respondCreated([
                'message' => 'Abstract created successfully',
                'abstract' => $newAbstract
            ]);
        } catch (\Exception $e) {
            // Rollback transaction if an error occurs
            $this->abstractModel->db->transRollback();
            log_message('error', 'Failed to create abstract: ' . $e->getMessage());
            return $this->failServerError('Failed to create abstract: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing abstract
     * POST /api/abstracts/{abstract_id}/update
     */
    public function updateAbstract($abstract_id)
    {
        // Check if abstract exists
        $abstract = $this->abstractModel->find($abstract_id);

        if (!$abstract) {
            return $this->failNotFound('Abstract not found');
        }

        // Validate request
        $rules = [
            'title' => 'permit_empty|string',
            'status' => 'permit_empty|string',
            'program_id' => 'permit_empty|numeric'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        try {
            // Build update data
            $updateData = [];

            // Only update fields that were provided
            if ($this->request->getVar('program_id') !== null) {
                $program_id = $this->request->getVar('program_id');

                // Check if program exists
                $program = $this->programModel->find($program_id);
                if (!$program) {
                    return $this->failNotFound('Program not found');
                }

                $updateData['program_id'] = $program_id;
            }

            if ($this->request->getVar('status') !== null) {
                $updateData['status'] = $this->request->getVar('status');
            }

            // Only update if there's data to update
            if (!empty($updateData)) {
                // Begin transaction
                $this->abstractModel->db->transBegin();

                // Update abstract
                $this->abstractModel->update($abstract_id, $updateData);

                // If title is provided, create a new abstract version
                if ($this->request->getVar('title') !== null) {
                    // Get the latest version
                    $latestVersion = $this->abstractVersionModel->where('abstract_id', $abstract_id)
                        ->orderBy('version_number', 'DESC')
                        ->first();

                    $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

                    $versionData = [
                        'abstract_id' => $abstract_id,
                        'title' => $this->request->getVar('title'),
                        'content' => $this->request->getVar('content') ?? $latestVersion->content ?? '',
                        'keywords' => $this->request->getVar('keywords') ?? $latestVersion->keywords ?? '',
                        'version_number' => $newVersionNumber,
                        'is_active' => 1,
                        'is_deleted' => 0
                    ];

                    $this->abstractVersionModel->insert($versionData);
                }

                // Commit transaction if successful
                if ($this->abstractModel->db->transStatus() === false) {
                    $this->abstractModel->db->transRollback();
                    return $this->failServerError('Failed to update abstract');
                } else {
                    $this->abstractModel->db->transCommit();
                }
            }

            // Get the updated abstract
            $updatedAbstract = $this->abstractModel->getAbstractById($abstract_id);

            return $this->respondUpdated([
                'message' => 'Abstract updated successfully',
                'abstract' => $updatedAbstract
            ]);
        } catch (\Exception $e) {
            // Rollback transaction if an error occurs
            if (isset($this->abstractModel->db) && $this->abstractModel->db->transStatus() !== false) {
                $this->abstractModel->db->transRollback();
            }
            return $this->failServerError('Failed to update abstract: ' . $e->getMessage());
        }
    }

    /**
     * Delete an abstract (soft delete)
     * DELETE /api/abstracts/{abstract_id}
     */
    public function deleteAbstract($abstract_id)
    {
        // Check if abstract exists
        $abstract = $this->abstractModel->find($abstract_id);

        if (!$abstract) {
            return $this->failNotFound('Abstract not found');
        }

        try {
            // Begin transaction
            $this->abstractModel->db->transBegin();

            // Soft delete by setting is_deleted to 1
            $this->abstractModel->update($abstract_id, ['is_deleted' => 1]);

            // Also mark all versions as deleted
            $this->abstractVersionModel->where('abstract_id', $abstract_id)
                ->set(['is_deleted' => 1])
                ->update();

            // Also mark all authors as deleted
            $this->abstractAuthorModel->where('abstract_id', $abstract_id)
                ->set(['is_deleted' => 1])
                ->update();

            // Commit transaction if successful
            if ($this->abstractModel->db->transStatus() === false) {
                $this->abstractModel->db->transRollback();
                return $this->failServerError('Failed to delete abstract');
            } else {
                $this->abstractModel->db->transCommit();
            }

            return $this->respondDeleted([
                'message' => 'Abstract deleted successfully',
                'id' => $abstract_id
            ]);
        } catch (\Exception $e) {
            // Rollback transaction if an error occurs
            $this->abstractModel->db->transRollback();
            return $this->failServerError('Failed to delete abstract: ' . $e->getMessage());
        }
    }
    /**
     * Update an existing abstract author
     * POST /api/abstracts/authors/{author_id}/update
     */
    public function updateAbstractAuthor($author_id)
    {
        // Check if author exists
        $author = $this->abstractAuthorModel->find($author_id);

        if (!$author) {
            return $this->failNotFound('Abstract author not found');
        }

        // Get is_participant value
        $is_participant = $this->request->getVar('is_participant') !== null
            ? (int)$this->request->getVar('is_participant')
            : $author->is_participant;        // Set validation rules based on is_participant value
        $rules = [
            'full_name' => 'permit_empty|string',
            'institution' => 'permit_empty|string',
            'email' => 'permit_empty|valid_email',
            'is_participant' => 'permit_empty|in_list[0,1]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        // participant_id can only be updated if is_participant is 1
        if ($is_participant === 1 && $this->request->getVar('participant_id') !== null) {
            $rules['participant_id'] = 'numeric';
        }

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        try {
            // Build update data
            $updateData = [];

            // Only update fields that were provided
            foreach (['full_name', 'institution', 'email', 'is_participant', 'is_active'] as $field) {
                if ($this->request->getVar($field) !== null) {
                    $updateData[$field] = $this->request->getVar($field);
                }
            }

            // Process participant_id if provided and is_participant is 1
            $participant_id = $this->request->getVar('participant_id');
            if ($is_participant === 1 && $participant_id !== null) {
                // Check if participant exists
                $participant = $this->participantModel->find($participant_id);
                if (!$participant) {
                    return $this->failNotFound('Participant not found');
                }

                // Only if participant_id is changing
                if ($author->participant_id != $participant_id) {
                    // Check if participant is already an author for this abstract
                    $existingAuthor = $this->abstractAuthorModel
                        ->where('abstract_id', $author->abstract_id)
                        ->where('participant_id', $participant_id)
                        ->where('id !=', $author_id)
                        ->first();

                    if ($existingAuthor) {
                        return $this->failResourceExists('This participant is already an author of this abstract');
                    }

                    // Check if participant is already associated with any abstract as a primary submitter
                    $existingAbstract = $this->abstractModel->where('primary_participant_id', $participant_id)->first();

                    if ($existingAbstract && $existingAbstract->id != $author->abstract_id) {
                        return $this->fail('This participant is already a primary submitter for another abstract', 409);
                    }

                    // Check if participant is already an author in other abstracts
                    $existingAsAuthor = $this->abstractAuthorModel
                        ->where('participant_id', $participant_id)
                        ->where('abstract_id !=', $author->abstract_id)
                        ->first();

                    if ($existingAsAuthor) {
                        return $this->fail('This participant is already an author for another abstract', 409);
                    }

                    $updateData['participant_id'] = $participant_id;
                }
            } else if ($is_participant === 0 && $this->request->getVar('participant_id') === '') {
                // If is_participant is 0 and participant_id is explicitly set to empty string, set to null
                $updateData['participant_id'] = null;
            }

            // Only update if there's data to update
            if (!empty($updateData)) {
                $this->abstractAuthorModel->update($author_id, $updateData);
            }

            // Get the updated author
            $updatedAuthor = $this->abstractAuthorModel->find($author_id);

            return $this->respondUpdated([
                'message' => 'Abstract author updated successfully',
                'author' => $updatedAuthor
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to update abstract author: ' . $e->getMessage());
        }
    }
    /**
     * Delete an abstract author (soft delete)
     * DELETE /api/abstracts/authors/{author_id}
     */
    public function deleteAbstractAuthor($author_id)
    {
        // Check if author exists
        $author = $this->abstractAuthorModel->find($author_id);

        if (!$author) {
            return $this->failNotFound('Abstract author not found');
        }

        // Get the abstract to check primary participant
        $abstract = $this->abstractModel->find($author->abstract_id);

        // Don't allow deletion if the author is the primary participant
        if ($abstract && !empty($author->participant_id) && $author->participant_id == $abstract->primary_participant_id) {
            return $this->fail('Cannot delete the primary participant author', 409);
        }

        // Count the number of authors for this abstract
        $authorCount = $this->abstractAuthorModel->where('abstract_id', $author->abstract_id)
            ->where('is_deleted', 0)
            ->countAllResults();

        // Don't allow deletion if this is the last author
        if ($authorCount <= 1) {
            return $this->fail('Cannot delete the only author of an abstract', 409);
        }

        try {
            // Soft delete by setting is_deleted to 1
            $this->abstractAuthorModel->update($author_id, ['is_deleted' => 1]);

            return $this->respondDeleted([
                'message' => 'Abstract author deleted successfully',
                'id' => $author_id
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to delete abstract author: ' . $e->getMessage());
        }
    }

    /**
     * Validate if an author can be added to an abstract
     * POST /api/abstracts/{abstract_id}/authors/validate
     */
    public function validateAuthorForAbstract($abstract_id)
    {
        // Check if abstract exists
        $abstract = $this->abstractModel->find($abstract_id);

        if (!$abstract) {
            return $this->failNotFound('Abstract not found');
        }

        // Validate required fields
        $rules = [
            'email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $authorEmail = $this->request->getPost('email');

        try {
            // Check if author email is already assigned to any abstract within the same program
            $existingAuthorInProgram = $this->abstractAuthorModel->checkAuthorEmailInProgram($authorEmail, $abstract->program_id, $abstract_id);

            if ($existingAuthorInProgram) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'This author email is already assigned to another abstract in the same program. One participant can only be assigned to one abstract at a time per program.',
                    'data' => [
                        'can_add' => false,
                        'existing_abstract_id' => $existingAuthorInProgram->abstract_id,
                        'conflict_reason' => 'email_already_in_program'
                    ]
                ], 409);
            }

            // Check if author already exists for this specific abstract
            $existingAuthorInAbstract = $this->abstractAuthorModel->where('abstract_id', $abstract_id)
                ->where('email', $authorEmail)
                ->first();

            if ($existingAuthorInAbstract) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'An author with this email is already associated with this abstract',
                    'data' => [
                        'can_add' => false,
                        'conflict_reason' => 'email_already_in_abstract'
                    ]
                ], 409);
            }

            // If we get here, the author can be added
            return $this->respondSuccess([
                'can_add' => true,
                'message' => 'Author can be added to this abstract',
                'data' => [
                    'email' => $authorEmail,
                    'abstract_id' => $abstract_id,
                    'program_id' => $abstract->program_id
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to validate author: ' . $e->getMessage());
        }
    }

    /**
     * Get all abstracts with optional pagination and filtering
     * GET /api/abstracts
     */
    public function getAllAbstracts()
    {
        // Get pagination params
        $page = $this->request->getGet('page') ? (int)$this->request->getGet('page') : 1;
        $limit = $this->request->getGet('limit') ? (int)$this->request->getGet('limit') : 10;

        // Get filter params
        $status = $this->request->getGet('status');
        $program_id = $this->request->getGet('program_id');

        // Start building query
        $builder = $this->abstractModel->builder();
        $builder->select('*');

        // Apply filters
        if ($status) {
            $builder->where('status', $status);
        }

        if ($program_id) {
            $builder->where('program_id', $program_id);
        }

        // By default, only show active and not deleted abstracts
        $builder->where('is_active', 1);
        $builder->where('is_deleted', 0);

        try {
            // Count total before pagination
            $total = $builder->countAllResults(false);

            // Apply pagination
            $offset = ($page - 1) * $limit;
            $builder->limit($limit, $offset);

            // Get results
            $abstracts = $builder->get()->getResult();

            return $this->respondSuccess([
                'abstracts' => $abstracts,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to retrieve abstracts: ' . $e->getMessage());
        }
    }
    /**
     * Debug helper method to test abstract insertion
     */
    public function debugInsertAbstract()
    {
        // For debugging: print allowed fields for the abstract model
        $field = $this->abstractModel->fillable;
        print_r($field);

        // Check the allowed fields for the abstract model
        $allowedFields = $this->abstractModel->allowedFields;
        echo "Allowed fields for AbstractModel: \n";
        print_r($allowedFields);

        // Insert data
        $data = [
            'program_id' => $this->getInput('program_id'),
            'primary_participant_id' => $this->getInput('primary_participant_id'),
            'status' => 'draft',
            'is_active' => 1,
            'is_deleted' => 0
        ];

        // Log the data
        log_message('debug', 'Abstract data to be inserted: ' . json_encode($data));

        // Try to insert abstract
        try {
            $this->abstractModel->insert($data);
            $abstract_id = $this->abstractModel->getInsertID();
            echo "Abstract created successfully with ID: " . $abstract_id;
        } catch (\Exception $e) {
            echo "Failed to create abstract: " . $e->getMessage();
            log_message('error', 'Insert abstract exception: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing abstract version
     * PUT /api/abstracts/version/{version_id}
     */
    /**
     * Update an existing abstract version
     * POST /api/abstracts/version/{version_id}/update
     */
    public function updateAbstractVersion($version_id)
    {
        // Check if abstract version exists
        $abstractVersion = $this->abstractVersionModel->find($version_id);

        if (!$abstractVersion) {
            return $this->failNotFound('Abstract version not found');
        }

        // Get the abstract to check permissions
        $abstract = $this->abstractModel->find($abstractVersion->abstract_id);

        if (!$abstract) {
            return $this->failNotFound('Parent abstract not found');
        }

        // Validate request
        $rules = [
            'title' => 'required|string',
            'content' => 'required|string',
            'keywords' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        try {
            // Build update data
            $updateData = [
                'title' => $this->request->getVar('title'),
                'content' => $this->request->getVar('content'),
                'keywords' => $this->request->getVar('keywords') ?? '',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Begin transaction
            $this->abstractVersionModel->db->transBegin();

            // Update abstract version
            $this->abstractVersionModel->update($version_id, $updateData);

            // Commit transaction if successful
            if ($this->abstractVersionModel->db->transStatus() === false) {
                $this->abstractVersionModel->db->transRollback();
                return $this->failServerError('Failed to update abstract version');
            } else {
                $this->abstractVersionModel->db->transCommit();
            }

            // Get the updated abstract version
            $updatedVersion = $this->abstractVersionModel->getAbstractVersionById($version_id);

            return $this->respondUpdated([
                'message' => 'Abstract version updated successfully',
                'abstract_version' => $updatedVersion
            ]);
        } catch (\Exception $e) {
            // Rollback transaction if an error occurs
            if (isset($this->abstractVersionModel->db) && $this->abstractVersionModel->db->transStatus() !== false) {
                $this->abstractVersionModel->db->transRollback();
            }
            return $this->failServerError('Failed to update abstract version: ' . $e->getMessage());
        }
    }

    /**
     * Save an abstract version
     * POST /api/abstracts/{abstract_id}/save-version
     *
     * Handles saving an abstract version for a participant based on various conditions:
     * - If there is an existing draft version, update it.
     * - If editing a submitted version, create a new draft version with incremented version_number.
     * - If submitting a version, mark it as submitted and set all other versions as inactive.
     * - Only one version should be active per abstract at a time.
     * - Reviewers can only see versions where status = 'submitted' and is_active = true.
     * - Participants can see all versions (drafts and submitted).
     */
    public function saveAbstractVersion($abstract_id)
    {
        // Check if abstract exists
        $abstract = $this->abstractModel->find($abstract_id);
        if (!$abstract) {
            return $this->failNotFound('Abstract not found');
        }

        // Check if the primary participant is eligible for abstract submission
        if (!$this->isEligibleForSubmission($abstract->primary_participant_id)) {
            return $this->fail('Primary participant is not eligible for abstract submission. Form status must be 2 (submitted).', 403);
        }

        // Validate request
        $rules = [
            'title' => 'required|string',
            'content' => 'required|string',
            'keywords' => 'permit_empty|string',
            'refs' => 'permit_empty|string',
            'status' => 'required|in_list[draft,submitted]',
            'version_id' => 'permit_empty|numeric' // If editing an existing version
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Extract input variables
        $title = $this->request->getVar('title');
        $content = $this->request->getVar('content');
        $keywords = $this->request->getVar('keywords') ?? '';
        $refs = $this->request->getVar('refs') ?? '';
        $status = $this->request->getVar('status');
        $version_id = $this->request->getVar('version_id');        // Validate word limits based on program's abstract settings
        $validation = $this->validateWordLimits($abstract->program_id, $title, $content, $keywords, $refs);

        if (!$validation['valid']) {
            return $this->fail('Word limit validation failed: ' . implode(', ', $validation['errors']), 400);
        }

        // Start a database transaction
        $this->abstractVersionModel->db->transBegin();

        try {
            if ($version_id) {
                $existingVersion = $this->abstractVersionModel->find($version_id);

                if (!$existingVersion) {
                    return $this->failNotFound('Abstract version not found');
                }

                // If editing a submitted version, create a new draft
                if ($existingVersion->status === 'submitted') {
                    // Get the latest version number and increment
                    $latestVersion = $this->abstractVersionModel->where('abstract_id', $abstract_id)
                        ->orderBy('version_number', 'DESC')
                        ->first();

                    $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

                    // Create a new draft version
                    $versionData = [
                        'abstract_id' => $abstract_id,
                        'title' => $title,
                        'content' => $content,
                        'keywords' => $keywords,
                        'refs' => $refs,
                        'version_number' => $newVersionNumber,
                        'status' => 'draft', // Always start as draft when creating from a submitted version
                        'is_active' => 0, // Not active until submitted
                        'is_deleted' => 0
                    ];

                    // Insert new version
                    $this->abstractVersionModel->insert($versionData);
                    $newVersionId = $this->abstractVersionModel->getInsertID();
                    $resultVersionId = $newVersionId;
                } else {
                    // If editing a draft version, update it
                    $updateData = [
                        'title' => $title,
                        'content' => $content,
                        'keywords' => $keywords,
                        'refs' => $refs,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    // If submitting, update status
                    if ($status === 'submitted') {
                        $updateData['status'] = 'submitted';
                    }

                    // Update existing version
                    $this->abstractVersionModel->update($version_id, $updateData);
                    $resultVersionId = $version_id;
                }
            } else {
                // Check if there's a draft version first
                $draftVersion = $this->abstractVersionModel->where('abstract_id', $abstract_id)
                    ->where('status', 'draft')
                    ->where('is_deleted', 0)
                    ->first();

                if ($draftVersion) {
                    // Update the existing draft
                    $updateData = [
                        'title' => $title,
                        'content' => $content,
                        'keywords' => $keywords,
                        'refs' => $refs,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    // If submitting, update status
                    if ($status === 'submitted') {
                        $updateData['status'] = 'submitted';
                    }

                    // Update existing draft
                    $this->abstractVersionModel->update($draftVersion->id, $updateData);
                    $resultVersionId = $draftVersion->id;
                } else {
                    // Create new version (no draft exists)
                    $latestVersion = $this->abstractVersionModel->where('abstract_id', $abstract_id)
                        ->orderBy('version_number', 'DESC')
                        ->first();

                    $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

                    $versionData = [
                        'abstract_id' => $abstract_id,
                        'title' => $title,
                        'content' => $content,
                        'keywords' => $keywords,
                        'refs' => $refs,
                        'version_number' => $newVersionNumber,
                        'status' => $status,
                        'is_active' => 0, // Not active until submitted and processed
                        'is_deleted' => 0
                    ];

                    // Insert new version
                    $this->abstractVersionModel->insert($versionData);
                    $resultVersionId = $this->abstractVersionModel->getInsertID();
                }
            }

            // If submitting a version, update active status and abstract record
            if ($status === 'submitted') {
                // First, set all versions of this abstract to inactive
                $this->abstractVersionModel->where('abstract_id', $abstract_id)
                    ->set(['is_active' => 0])
                    ->update();

                // Then set this version as active
                $this->abstractVersionModel->update($resultVersionId, ['is_active' => 1]);

                // Update the abstract record to point to this active version
                $this->abstractModel->update($abstract_id, [
                    'active_version_id' => $resultVersionId,
                    'status' => 'submitted' // Update abstract status too
                ]);
            }

            // Commit transaction if all operations are successful
            if ($this->abstractVersionModel->db->transStatus() === false) {
                $this->abstractVersionModel->db->transRollback();
                return $this->failServerError('Failed to save abstract version');
            } else {
                $this->abstractVersionModel->db->transCommit();
            }

            // Get the saved version
            $savedVersion = $this->abstractVersionModel->getAbstractVersionById($resultVersionId);

            return $this->respondSuccess([
                'abstract_version' => $savedVersion,
                'status' => $status
            ], SELF::HTTP_OK, 'Abstract version saved successfully',);
        } catch (\Exception $e) {
            // Rollback transaction if an error occurs
            $this->abstractVersionModel->db->transRollback();
            log_message('error', 'Failed to save abstract version: ' . $e->getMessage());
            return $this->failServerError('Failed to save abstract version: ' . $e->getMessage());
        }
    }
    /**
     * Validate word limits based on program's abstract settings
     */ private function validateWordLimits($program_id, $title, $content, $keywords, $references = '')
    {
        // Get abstract settings for the program
        $abstractSettings = $this->abstractSettingModel->where('program_id', $program_id)
            ->where('is_active', 1)
            ->first();

        if (!$abstractSettings) {
            // If no settings found, return true (no limits enforced)
            return ['valid' => true];
        }

        $errors = [];

        // Validate title word count
        if (!empty($title) && $this->countWords($title) > $abstractSettings->title_length) {
            $errors['title'] = "Title exceeds maximum word limit of {$abstractSettings->title_length} words.";
        }

        // Validate content word count
        if (!empty($content) && $this->countWords($content) > $abstractSettings->content_length) {
            $errors['content'] = "Content exceeds maximum word limit of {$abstractSettings->content_length} words.";
        }

        // Validate keywords word count
        if (!empty($keywords) && $this->countWords($keywords) > $abstractSettings->keywords_length) {
            $errors['keywords'] = "Keywords exceed maximum word limit of {$abstractSettings->keywords_length} words.";
        }

        // Validate references word count
        if (!empty($references) && $this->countWords($references) > $abstractSettings->refs_length) {
            $errors['references'] = "References exceed maximum word limit of {$abstractSettings->refs_length} words.";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'limits' => [
                'title_limit' => $abstractSettings->title_length,
                'content_limit' => $abstractSettings->content_length,
                'keywords_limit' => $abstractSettings->keywords_length,
                'references_limit' => $abstractSettings->refs_length
            ]
        ];
    }

    /**
     * Count words in a text string
     * 
     * @param string $text
     * @return int
     */
    private function countWords($text)
    {
        // Remove HTML tags if present
        $text = strip_tags($text);

        // Remove extra whitespace and trim
        $text = preg_replace('/\s+/', ' ', trim($text));

        // If empty after cleaning, return 0
        if (empty($text)) {
            return 0;
        }

        // Split by spaces and count
        return count(explode(' ', $text));
    }

    /**
     * Create a new abstract version
     * POST /api/abstracts/{abstract_id}/versions
     */
    public function createAbstractVersion($abstract_id)
    {
        // Check if abstract exists
        $abstract = $this->abstractModel->find($abstract_id);

        if (!$abstract) {
            return $this->failNotFound('Abstract not found');
        }

        // Check if the primary participant is eligible for abstract submission
        if (!$this->isEligibleForSubmission($abstract->primary_participant_id)) {
            return $this->fail('Primary participant is not eligible for abstract submission. Form status must be 2 (submitted).', 403);
        }

        // Validate request
        $rules = [
            'title' => 'required|string',
            'content' => 'required|string',
            'keywords' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Check word limits based on program settings
        $title = $this->request->getPost('title');
        $content = $this->request->getPost('content');
        $keywords = $this->request->getPost('keywords');
        $refs = $this->request->getPost('refs');

        $limitValidation = $this->validateWordLimits($abstract->program_id, $title, $content, $keywords, $refs);

        if (!$limitValidation['valid']) {
            return $this->failValidationErrors($limitValidation['errors']);
        }

        try {
            // Get the latest version
            $latestVersion = $this->abstractVersionModel->where('abstract_id', $abstract_id)
                ->orderBy('version_number', 'DESC')
                ->first();

            $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

            // Create new version data
            $versionData = [
                'abstract_id' => $abstract_id,
                'title' => $this->request->getPost('title'),
                'content' => $this->request->getPost('content'),
                'keywords' => $this->request->getPost('keywords') ?? '',
                'version_number' => $newVersionNumber,
                'is_active' => 1,
                'is_deleted' => 0
            ];

            // Insert new version
            $this->abstractVersionModel->insert($versionData);
            $versionId = $this->abstractVersionModel->getInsertID();

            // Get the newly created version
            $newVersion = $this->abstractVersionModel->getAbstractVersionById($versionId);

            return $this->respondCreated([
                'message' => 'Abstract version created successfully',
                'abstract_version' => $newVersion
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to create abstract version: ' . $e->getMessage());
        }
    }
    /**
     * Get abstract word limits for a program
     * GET /api/abstracts/programs/{program_id}/limits
     */
    public function getAbstractLimits($program_id)
    {
        // Check if program exists
        $program = $this->programModel->find($program_id);

        if (!$program) {
            return $this->failNotFound('Program not found');
        }

        // Get abstract settings for the program
        $abstractSettings = $this->abstractSettingModel->where('program_id', $program_id)
            ->where('is_active', 1)
            ->first();

        if (!$abstractSettings) {
            return $this->respond([
                'status' => 'success',
                'message' => 'No word limits configured for this program',
                'data' => [
                    'has_limits' => false,
                    'limits' => null
                ]
            ]);
        }
        return $this->respond([
            'status' => 'success',
            'message' => 'Abstract word limits retrieved successfully',
            'data' => [
                'has_limits' => true,
                'limits' => [
                    'title_limit' => $abstractSettings->title_length,
                    'content_limit' => $abstractSettings->content_length,
                    'keywords_limit' => $abstractSettings->keywords_length,
                    'references_limit' => $abstractSettings->refs_length
                ],
                'settings' => [
                    'id' => $abstractSettings->id,
                    'program_id' => $abstractSettings->program_id,
                    'is_active' => $abstractSettings->is_active,
                    'created_at' => $abstractSettings->created_at,
                    'updated_at' => $abstractSettings->updated_at
                ]
            ]
        ]);
    }
}
