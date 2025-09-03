<?php

namespace App\Controllers;

use App\Models\ProgramModel;
use App\Models\AbstractModel;
use App\Models\ParticipantModel;

class AbstractPapers extends AdminBaseController
{
    protected $abstractModel;
    protected $programModel;
    protected $participantModel;
    protected $abstractVersionModel;    protected $abstractAuthorModel;
    protected $abstractTopicModel;
    protected $programSubthemeModel;

    public function __construct()
    {
        $this->abstractModel = new AbstractModel();
        $this->programModel = new ProgramModel();
        $this->participantModel = new ParticipantModel();
        $this->abstractVersionModel = new \App\Models\AbstractVersionModel();
        $this->abstractAuthorModel = new \App\Models\AbstractAuthorModel();
        $this->abstractTopicModel = new \App\Models\AbstractTopicModel();
        $this->programSubthemeModel = new \App\Models\ProgramSubthemeModel();
    }

    public function index()
    {
        // Get current program ID from session
        $programId = session('current_program');

        // Check if program ID exists
        if (!$programId) {
            return redirect()->to('/dashboard')->with('error', 'Please select a program first');
        }

        // Get program data
        $program = $this->programModel->find($programId);

        if (!$program) {
            return redirect()->to('/dashboard')->with('error', 'Program not found');
        }

        $data = [
            'title' => 'Abstract Papers',
            'program' => $program
        ];

        return view('submissions/abstract-paper/index', $data);
    }    public function getAbstractsByProgram($programId = null)
    {
        if (!$programId) {
            $programId = session('current_program');
        }

        $subthemeFilter = $this->request->getGet('subtheme_id') ?: $this->request->getPost('subtheme_id');
          $builder = $this->abstractModel->builder();
        $builder->where('program_id', $programId)
                ->where('abstracts.is_deleted', 0);
        
        // Filter by subtheme if specified
        if ($subthemeFilter) {
            $builder->join('participant_subthemes ps', 'ps.participant_id = abstracts.primary_participant_id')
                   ->where('ps.program_subtheme_id', $subthemeFilter)
                   ->where('ps.is_active', 1)
                   ->where('ps.is_deleted', 0);
        }
        
        $abstracts = $builder->get()->getResult();

        // Get participant details and latest version for each abstract
        foreach ($abstracts as &$abstract) {
            // Get participant details
            $participant = $this->participantModel->find($abstract->primary_participant_id);
            $abstract->participant_name = $participant ? $participant->full_name : 'N/A';
            $abstract->institution = $participant ? $participant->institution : 'N/A';

            // Get latest abstract version for title
            $versions = $this->abstractVersionModel->getAllAbstractVersionsByAbstractId($abstract->id);
            if (!empty($versions)) {
                // Sort versions by version number in descending order
                usort($versions, function ($a, $b) {
                    return $b->version_number - $a->version_number;
                });

                $abstract->title = $versions[0]->title;
            } else {
                $abstract->title = 'No title available';
            }

            // Get participant subtheme data
            $participantSubthemeModel = new \App\Models\ParticipantSubthemeModel();            $subtheme = $participantSubthemeModel
                ->select('participant_subthemes.*, program_subthemes.name as subtheme_name')
                ->join('program_subthemes', 'program_subthemes.id = participant_subthemes.program_subtheme_id', 'left')
                ->where('participant_subthemes.participant_id', $abstract->primary_participant_id)
                ->where('participant_subthemes.is_deleted', 0)
                ->first();
            $abstract->topic_name = $subtheme ? $subtheme->subtheme_name : 'No subtheme selected';

            // Get authors count
            $authorModel = new \App\Models\AbstractAuthorModel();
            $authors = $authorModel->where('abstract_id', $abstract->id)
                ->where('is_deleted', 0)
                ->findAll();
            $abstract->authors_count = count($authors);

            // Extract main authors for display (limit to 2)
            $abstract->authors_list = array_slice(array_map(function ($author) {
                return $author->full_name;
            }, $authors), 0, 2);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $abstracts
        ]);
    }

    public function view($id = null)
    {
        if (!$id) {
            return redirect()->to('/submissions/abstracts-papers')->with('error', 'Abstract ID is required');
        }

        $abstract = $this->abstractModel->find($id);

        if (!$abstract) {
            return redirect()->to('/submissions/abstracts-papers')->with('error', 'Abstract not found');
        }

        $programId = session('current_program');        if ($abstract->program_id != $programId) {
            return redirect()->to('/submissions/abstracts-papers')->with('error', 'You do not have access to this abstract');
        }

        // Get participant details
        $participant = $this->participantModel->find($abstract->primary_participant_id);

        // Get all versions
        $versions = $this->abstractVersionModel->getAllAbstractVersionsByAbstractId($id);

        // Sort versions by version number in descending order
        usort($versions, function ($a, $b) {
            return $b->version_number - $a->version_number;
        });

        // Get all authors
        $authors = $this->abstractAuthorModel->getAllAbstractAuthorsByAbstractId($id);        $data = [
            'title' => 'View Abstract',
            'abstract' => $abstract,
            'participant' => $participant,
            'versions' => $versions,
            'authors' => $authors
        ];

        return view('submissions/abstract-paper/details', $data);
    }

    public function create()
    {
        $programId = session('current_program');

        if (!$programId) {
            return redirect()->to('/dashboard')->with('error', 'Please select a program first');
        }
        // Get topic options for the current program
        $topics = $this->abstractTopicModel->getAllAbstractTopicsByProgramId($programId);

        $data = [
            'title' => 'Create New Abstract',
            'programs' => $this->programModel->findAll(),
            'topics' => $topics
        ];
        return view('submissions/abstract-paper/create', $data);
    }

    public function store()
    {
        // Validate form input
        $validation = \Config\Services::validation();
        $rules = [
            'participant_id' => 'required',
            'title' => 'required',
            'content' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }        $programId = session('current_program');
        $data = [
            'program_id' => $programId,
            'primary_participant_id' => $this->request->getPost('participant_id'),
            'status' => $this->request->getPost('status') ?? 'draft',
            'is_active' => 1,
            'is_deleted' => 0
        ];

        // Start a transaction
        $this->abstractModel->db->transBegin();

        // Insert the abstract
        $abstract_id = $this->abstractModel->insert($data);

        if (!$abstract_id) {
            $this->abstractModel->db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to add abstract'
            ]);
        }

        // Add abstract version
        $versionData = [
            'abstract_id' => $abstract_id,
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'keywords' => $this->request->getPost('keywords'),
            'version_number' => 1,
            'is_active' => 1,
            'is_deleted' => 0
        ];

        if (!$this->abstractVersionModel->insert($versionData)) {
            $this->abstractModel->db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to add abstract version'
            ]);
        }

        // Add abstract authors
        $authorNames = $this->request->getPost('author_name');
        $authorInstitutions = $this->request->getPost('author_institution');
        $authorEmails = $this->request->getPost('author_email');
        $isParticipantFlags = $this->request->getPost('is_participant') ?? [];

        if (!empty($authorNames)) {
            foreach ($authorNames as $index => $name) {
                $authorData = [
                    'abstract_id' => $abstract_id,
                    'full_name' => $name,
                    'institution' => isset($authorInstitutions[$index]) ? $authorInstitutions[$index] : null,
                    'email' => isset($authorEmails[$index]) ? $authorEmails[$index] : null,
                    'is_participant' => in_array(($index + 1), $isParticipantFlags) ? 1 : 0,
                    'participant_id' => in_array(($index + 1), $isParticipantFlags) ? $this->request->getPost('participant_id') : null,
                    'is_active' => 1,
                    'is_deleted' => 0
                ];

                if (!$this->abstractAuthorModel->insert($authorData)) {
                    $this->abstractModel->db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to add abstract author'
                    ]);
                }
            }
        }

        // Commit the transaction
        $this->abstractModel->db->transCommit();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Abstract has been added successfully'
        ]);
    }

    public function edit($id = null)
    {
        if (!$id) {
            return redirect()->to('/submissions/abstracts-papers')->with('error', 'Abstract ID is required');
        }

        $abstract = $this->abstractModel->find($id);

        if (!$abstract) {
            return redirect()->to('/submissions/abstracts-papers')->with('error', 'Abstract not found');
        }

        $programId = session('current_program');

        if ($abstract->program_id != $programId) {
            return redirect()->to('/submissions/abstracts-papers')->with('error', 'You do not have access to this abstract');
        }

        // Get the latest version
        $versions = $this->abstractVersionModel->getAllAbstractVersionsByAbstractId($id);
        $latestVersion = null;

        if (!empty($versions)) {
            usort($versions, function ($a, $b) {
                return $b->version_number - $a->version_number;
            });
            $latestVersion = $versions[0];
        }

        // Get authors
        $authors = $this->abstractAuthorModel->getAllAbstractAuthorsByAbstractId($id);
        // Get topic options
        $topics = $this->abstractTopicModel->getAllAbstractTopicsByProgramId($programId);

        $data = [
            'title' => 'Edit Abstract',
            'abstract' => $abstract,
            'participants' => $this->participantModel->where('program_id', $programId)->findAll(),
            'latestVersion' => $latestVersion,
            'authors' => $authors,
            'topics' => $topics
        ];
        return view('submissions/abstract-paper/edit', $data);
    }

    public function update($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Abstract ID is required'
            ]);
        }

        // Validate form input
        $validation = \Config\Services::validation();
        $rules = [
            'participant_id' => 'permit_empty',
            'title' => 'required',
            'content' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        // Start a transaction        $this->abstractModel->db->transBegin();
        $data = [
            'status' => $this->request->getPost('status') ?? 'draft',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Only update primary_participant_id if it's provided
        if ($this->request->getPost('participant_id')) {
            $data['primary_participant_id'] = $this->request->getPost('participant_id');
        }

        if (!$this->abstractModel->update($id, $data)) {
            $this->abstractModel->db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update abstract'
            ]);
        }

        // Update the abstract version
        $versionId = $this->request->getPost('version_id');

        if ($versionId) {
            $versionData = [
                'title' => $this->request->getPost('title'),
                'content' => $this->request->getPost('content'),
                'keywords' => $this->request->getPost('keywords'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (!$this->abstractVersionModel->update($versionId, $versionData)) {
                $this->abstractModel->db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update abstract version'
                ]);
            }
        }

        // Update authors
        $authorIds = $this->request->getPost('author_id') ?? [];
        $authorNames = $this->request->getPost('author_name') ?? [];
        $authorInstitutions = $this->request->getPost('author_institution') ?? [];
        $authorEmails = $this->request->getPost('author_email') ?? [];
        $isParticipantFlags = $this->request->getPost('is_participant') ?? [];

        if (!empty($authorNames)) {
            foreach ($authorNames as $index => $name) {
                // Prepare author data
                $authorData = [
                    'abstract_id' => $id,
                    'full_name' => $name,
                    'institution' => isset($authorInstitutions[$index]) ? $authorInstitutions[$index] : null,
                    'email' => isset($authorEmails[$index]) ? $authorEmails[$index] : null,
                    'is_participant' => in_array(($index + 1), $isParticipantFlags) ? 1 : 0,
                    'participant_id' => in_array(($index + 1), $isParticipantFlags) ? $this->request->getPost('participant_id') : null,
                    'is_active' => 1,
                    'is_deleted' => 0
                ];

                // If author ID exists, update it
                if (isset($authorIds[$index]) && !empty($authorIds[$index])) {
                    if (!$this->abstractAuthorModel->update($authorIds[$index], $authorData)) {
                        $this->abstractModel->db->transRollback();
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Failed to update abstract author'
                        ]);
                    }
                } else {
                    // Otherwise, insert a new author
                    if (!$this->abstractAuthorModel->insert($authorData)) {
                        $this->abstractModel->db->transRollback();
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Failed to add new abstract author'
                        ]);
                    }
                }
            }
        }

        // Commit the transaction
        $this->abstractModel->db->transCommit();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Abstract has been updated successfully'
        ]);
    }

    public function delete($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Abstract ID is required'
            ]);
        }

        $abstract = $this->abstractModel->find($id);

        if (!$abstract) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Abstract not found'
            ]);
        }

        $programId = session('current_program');

        if ($abstract->program_id != $programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have access to this abstract'
            ]);
        }

        // Soft delete
        if ($this->abstractModel->update($id, ['is_deleted' => 1])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Abstract has been deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete abstract'
            ]);
        }
    }

    public function getAbstractData($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Abstract ID is required']);
        }

        $abstract = $this->abstractModel->find($id);

        if (!$abstract) {
            return $this->response->setJSON(['success' => false, 'message' => 'Abstract not found']);
        }

        $programId = session('current_program');        if ($abstract->program_id != $programId) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have access to this abstract']);
        }

        // Get participant details
        $participant = $this->participantModel->find($abstract->primary_participant_id);
        $abstract->participant_name = $participant ? $participant->full_name : 'N/A';
        $abstract->institution = $participant ? $participant->institution : 'N/A';

        // Get latest abstract version
        $versions = $this->abstractVersionModel->getAllAbstractVersionsByAbstractId($id);
        if (!empty($versions)) {
            // Sort versions by version number in descending order
            usort($versions, function ($a, $b) {
                return $b->version_number - $a->version_number;
            });

            $abstract->latest_version = $versions[0];
        }

        // Get abstract authors
        $authors = $this->abstractAuthorModel->getAllAbstractAuthorsByAbstractId($id);
        $abstract->authors = $authors;

        return $this->response->setJSON(['success' => true, 'data' => $abstract]);
    }

    // Get abstract versions by abstract ID
    public function getAbstractVersions($abstract_id = null)
    {
        if (!$abstract_id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Abstract ID is required']);
        }

        $abstract = $this->abstractModel->find($abstract_id);

        if (!$abstract) {
            return $this->response->setJSON(['success' => false, 'message' => 'Abstract not found']);
        }

        $programId = session('current_program');

        if ($abstract->program_id != $programId) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have access to this abstract']);
        }

        $versions = $this->abstractVersionModel->getAllAbstractVersionsByAbstractId($abstract_id);

        // Sort by version number in descending order
        usort($versions, function ($a, $b) {
            return $b->version_number - $a->version_number;
        });

        return $this->response->setJSON(['success' => true, 'data' => $versions]);
    }

    // Get abstract authors by abstract ID
    public function getAbstractAuthors($abstract_id = null)
    {
        if (!$abstract_id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Abstract ID is required']);
        }

        $abstract = $this->abstractModel->find($abstract_id);

        if (!$abstract) {
            return $this->response->setJSON(['success' => false, 'message' => 'Abstract not found']);
        }

        $programId = session('current_program');

        if ($abstract->program_id != $programId) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have access to this abstract']);
        }

        $authors = $this->abstractAuthorModel->getAllAbstractAuthorsByAbstractId($abstract_id);

        return $this->response->setJSON(['success' => true, 'data' => $authors]);
    }

    // Create a new version of an abstract
    public function createNewVersion()
    {
        $abstract_id = $this->request->getPost('abstract_id');

        if (!$abstract_id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Abstract ID is required']);
        }

        $abstract = $this->abstractModel->find($abstract_id);

        if (!$abstract) {
            return $this->response->setJSON(['success' => false, 'message' => 'Abstract not found']);
        }

        $programId = session('current_program');

        if ($abstract->program_id != $programId) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have access to this abstract']);
        }

        // Get the latest version number
        $versions = $this->abstractVersionModel->getAllAbstractVersionsByAbstractId($abstract_id);
        $latestVersionNumber = 0;

        if (!empty($versions)) {
            foreach ($versions as $version) {
                if ($version->version_number > $latestVersionNumber) {
                    $latestVersionNumber = $version->version_number;
                }
            }
        }

        $newVersionNumber = $latestVersionNumber + 1;

        $data = [
            'abstract_id' => $abstract_id,
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'keywords' => $this->request->getPost('keywords'),
            'version_number' => $newVersionNumber,
            'is_active' => 1,
            'is_deleted' => 0
        ];

        if ($this->abstractVersionModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'New abstract version created successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create new abstract version'
            ]);
        }
    }

    // Remove an author from an abstract
    public function removeAuthor($author_id = null)
    {
        if (!$author_id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Author ID is required']);
        }

        $author = $this->abstractAuthorModel->find($author_id);

        if (!$author) {
            return $this->response->setJSON(['success' => false, 'message' => 'Author not found']);
        }

        $abstract = $this->abstractModel->find($author->abstract_id);

        if (!$abstract) {
            return $this->response->setJSON(['success' => false, 'message' => 'Abstract not found']);
        }

        $programId = session('current_program');

        if ($abstract->program_id != $programId) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have access to this abstract']);
        }

        // Soft delete
        if ($this->abstractAuthorModel->update($author_id, ['is_deleted' => 1])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Author removed successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to remove author'
            ]);
        }
    }

    // Get topics by program ID
    public function getTopicsByProgram($programId = null)
    {
        if (!$programId) {
            $programId = session('current_program');
        }

        if (!$programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Program ID is required'
            ]);
        }
        $topics = $this->abstractTopicModel->getAllAbstractTopicsByProgramId($programId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $topics
        ]);
    }

    /**
     * Enhanced details view for abstracts
     */
    public function details($id = null)
    {
        if (!$id) {
            return redirect()->to('/submissions/abstracts-papers')->with('error', 'Abstract ID is required');
        }

        $abstract = $this->abstractModel->find($id);

        if (!$abstract) {
            return redirect()->to('/submissions/abstracts-papers')->with('error', 'Abstract not found');
        }

        $programId = session('current_program');

        if ($abstract->program_id != $programId) {
            return redirect()->to('/submissions/abstracts-papers')->with('error', 'You do not have access to this abstract');
        }        // Get participant details
        $participant = $this->participantModel->find($abstract->primary_participant_id);

        // Get all versions with detailed information
        $versions = $this->abstractVersionModel->getAllAbstractVersionsByAbstractId($id);

        // Sort versions by version number in descending order
        usort($versions, function ($a, $b) {
            return $b->version_number - $a->version_number;
        });

        // Get all authors
        $authors = $this->abstractAuthorModel->getAllAbstractAuthorsByAbstractId($id);

        // Calculate version statistics
        $versionStats = [
            'total_versions' => count($versions),
            'latest_version' => !empty($versions) ? $versions[0]->version_number : 0,
            'first_submission' => !empty($versions) ? end($versions)->created_at : null,
            'last_update' => !empty($versions) ? $versions[0]->updated_at : null
        ];        $data = [
            'title' => 'Abstract Details',
            'abstract' => $abstract,
            'participant' => $participant,
            'versions' => $versions,
            'authors' => $authors,
            'version_stats' => $versionStats
        ];

        return view('submissions/abstract-paper/details', $data);
    }

    /**
     * Get specific abstract version details (AJAX endpoint)
     */
    public function getAbstractVersion($version_id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $version = $this->abstractVersionModel->getAbstractVersionById($version_id);
            
            if (!$version) {
                return $this->response->setJSON(['success' => false, 'message' => 'Version not found']);
            }

            // Check access permissions
            $abstract = $this->abstractModel->find($version->abstract_id);
            $programId = session('current_program');

            if (!$abstract || $abstract->program_id != $programId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $version
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting abstract version: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to get version details']);
        }
    }

    /**
     * Get feedback for a specific version (AJAX endpoint)
     */
    public function getVersionFeedback($version_id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            // TODO: Implement AbstractFeedbackModel when available
            // For now, return placeholder data
            $feedback = [
                'version_id' => $version_id,
                'feedback' => 'Feedback functionality will be implemented when AbstractFeedbackModel is available.',
                'reviewer_name' => 'System',
                'created_at' => date('Y-m-d H:i:s')
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => $feedback
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting version feedback: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to get feedback']);
        }
    }

    /**
     * Submit feedback for a version (AJAX endpoint)
     */
    public function submitFeedback()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $versionId = $this->request->getPost('version_id');
        $feedback = $this->request->getPost('feedback');

        if (!$versionId || !$feedback) {
            return $this->response->setJSON(['success' => false, 'message' => 'Version ID and feedback are required']);
        }

        try {
            // TODO: Implement AbstractFeedbackModel when available
            // For now, just return success
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Feedback submitted successfully (placeholder)'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error submitting feedback: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to submit feedback']);
        }
    }

    /**
     * Handle quick actions like accept/reject (AJAX endpoint)
     */
    public function quickAction()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $abstractId = $this->request->getPost('abstract_id');
        $action = $this->request->getPost('action');

        if (!$abstractId || !$action) {
            return $this->response->setJSON(['success' => false, 'message' => 'Abstract ID and action are required']);
        }

        try {
            $abstract = $this->abstractModel->find($abstractId);
            if (!$abstract) {
                return $this->response->setJSON(['success' => false, 'message' => 'Abstract not found']);
            }

            // Check access permissions
            $programId = session('current_program');
            if ($abstract->program_id != $programId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
            }

            // Update abstract status based on action
            $status = '';
            switch ($action) {
                case 'accept':
                    $status = 'accepted';
                    break;
                case 'reject':
                    $status = 'rejected';
                    break;
                case 'review':
                    $status = 'under_review';
                    break;
                default:
                    return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
            }

            $this->abstractModel->update($abstractId, ['status' => $status]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Abstract ' . $action . 'ed successfully',
                'new_status' => $status
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error performing quick action: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to perform action']);
        }
    }

    public function pdf($id)
    {
        try {
            // Check if user has permission to view this abstract
            $programId = session('current_program');
            if (!$programId) {
                throw new \Exception('No program selected');
            }

            $abstract = $this->abstractModel->getAbstractDetails($id, $programId);
            if (!$abstract) {
                throw new \Exception('Abstract not found');
            }

            // Check if Dompdf is available
            if (!class_exists('Dompdf\Dompdf')) {
                throw new \Exception('PDF generation library not available');
            }

            // Generate PDF using dompdf
            $options = new \Dompdf\Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);

            // Get latest version content
            $latestVersion = $this->abstractVersionModel
                ->where('abstract_id', $id)
                ->orderBy('version_number', 'DESC')
                ->first();

            // Get authors
            $authors = $this->abstractAuthorModel->getAbstractAuthors($id);

            // Create PDF content
            $html = view('submissions/abstract-paper/pdf-template', [
                'abstract' => $abstract,
                'version' => $latestVersion,
                'authors' => $authors
            ]);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'abstract_' . $id . '_' . date('Y-m-d') . '.pdf';
            $dompdf->stream($filename, ['Attachment' => true]);

        } catch (\Exception $e) {
            log_message('error', 'Error generating PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function export($id)
    {
        try {
            $format = $this->request->getGet('format') ?? 'excel';
            
            // Check if user has permission to view this abstract
            $programId = session('current_program');
            if (!$programId) {
                throw new \Exception('No program selected');
            }

            $abstract = $this->abstractModel->getAbstractDetails($id, $programId);
            if (!$abstract) {
                throw new \Exception('Abstract not found');
            }

            // Get additional data
            $versions = $this->abstractVersionModel
                ->where('abstract_id', $id)
                ->orderBy('version_number', 'DESC')
                ->findAll();
            
            $authors = $this->abstractAuthorModel->getAbstractAuthors($id);

            $data = [
                'abstract' => $abstract,
                'versions' => $versions,
                'authors' => $authors
            ];

            switch ($format) {
                case 'csv':
                    return $this->exportCSV($data, $id);
                case 'json':
                    return $this->exportJSON($data, $id);
                case 'excel':
                default:
                    return $this->exportExcel($data, $id);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error exporting data: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export data');
        }
    }

    private function exportCSV($data, $id)
    {
        $filename = 'abstract_' . $id . '_export_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Write headers
        fputcsv($output, ['Field', 'Value']);
        
        // Write abstract data
        fputcsv($output, ['Title', $data['abstract']->title]);
        fputcsv($output, ['Status', $data['abstract']->status]);
        fputcsv($output, ['Created', $data['abstract']->created_at]);
        fputcsv($output, ['Updated', $data['abstract']->updated_at]);
        
        // Write authors
        fputcsv($output, ['Authors', implode('; ', array_column($data['authors'], 'full_name'))]);
        
        // Write version info
        foreach ($data['versions'] as $version) {
            fputcsv($output, ['Version ' . $version->version_number, substr($version->content, 0, 100) . '...']);
        }
        
        fclose($output);
        exit;
    }

    private function exportJSON($data, $id)
    {
        $filename = 'abstract_' . $id . '_export_' . date('Y-m-d') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }    private function exportExcel($data, $id)
    {
        // This would require PhpSpreadsheet library
        // For now, fall back to CSV
        return $this->exportCSV($data, $id);
    }

    /**
     * Get program subthemes for dropdown
     */
    public function getSubthemes()
    {
        $programId = session('current_program');

        if (!$programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No program selected'
            ]);
        }

        $subthemes = $this->programSubthemeModel->getActiveSubthemes($programId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $subthemes
        ]);
    }    /**
     * Get reviewer's assigned subthemes for filtering
     */
    public function getReviewerSubthemes()
    {
        $reviewerId = session('reviewerId');
        $adminId = session('adminId');
        $programId = session('current_program');

        if (!$programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No program selected'
            ]);
        }

        // If admin user, return all subthemes for the program
        if ($adminId && !$reviewerId) {
            try {
                $subthemes = $this->programSubthemeModel->getActiveSubthemes($programId);
                
                // Format for consistency with reviewer subthemes
                $formattedSubthemes = array_map(function($subtheme) {
                    return (object)[
                        'program_subtheme_id' => $subtheme->id,
                        'subtheme_name' => $subtheme->name
                    ];
                }, $subthemes);

                return $this->response->setJSON([
                    'success' => true,
                    'data' => $formattedSubthemes
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error retrieving subthemes: ' . $e->getMessage()
                ]);
            }
        }

        // For reviewer users, get only assigned subthemes
        if (!$reviewerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reviewer not found in session'
            ]);
        }

        try {
            $abstractReviewerSubthemeModel = new \App\Models\AbstractReviewerSubthemeModel();
            $assignedSubthemes = $abstractReviewerSubthemeModel->getSubthemesByReviewerId($reviewerId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $assignedSubthemes
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error retrieving subthemes: ' . $e->getMessage()
            ]);
        }
    }
}
