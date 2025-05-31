<?php

namespace App\Controllers;

use App\Models\ProgramModel;
use App\Models\AbstractModel;
use App\Models\ParticipantModel;

class AbstractPapers extends BaseController
{
    protected $abstractModel;
    protected $programModel;
    protected $participantModel;
    protected $abstractVersionModel;
    protected $abstractAuthorModel;
    protected $abstractTopicModel;

    public function __construct()
    {
        $this->abstractModel = new AbstractModel();
        $this->programModel = new ProgramModel();
        $this->participantModel = new ParticipantModel();        $this->abstractVersionModel = new \App\Models\AbstractVersionModel();
        $this->abstractAuthorModel = new \App\Models\AbstractAuthorModel();
        $this->abstractTopicModel = new \App\Models\AbstractTopicModel();
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
            'program' => $program        ];

        return view('submissions/abstract-paper/index', $data);
    }

    public function getAbstractsByProgram($programId = null)
    {
        if (!$programId) {
            $programId = session('current_program');
        }

        $abstracts = $this->abstractModel->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->findAll();

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
                usort($versions, function($a, $b) {
                    return $b->version_number - $a->version_number;
                });
                
                $abstract->title = $versions[0]->title;
            } else {
                $abstract->title = 'No title available';
            }
              // Get abstract topic
            if (!empty($abstract->abstract_topic_id)) {
                $topicData = $this->abstractTopicModel->find($abstract->abstract_topic_id);
                $abstract->topic_name = $topicData ? $topicData->name : 'No topic selected';
            } else {
                $abstract->topic_name = 'No topic selected';
            }
            
            // Get authors count
            $authorModel = new \App\Models\AbstractAuthorModel();
            $authors = $authorModel->where('abstract_id', $abstract->id)
                                  ->where('is_deleted', 0)
                                  ->findAll();
            $abstract->authors_count = count($authors);
            
            // Extract main authors for display (limit to 2)
            $abstract->authors_list = array_slice(array_map(function($author) {
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

        $programId = session('current_program');

        if ($abstract->program_id != $programId) {
            return redirect()->to('/submissions/abstracts-papers')->with('error', 'You do not have access to this abstract');
        }

        // Get participant details
        $participant = $this->participantModel->find($abstract->primary_participant_id);
          // Get abstract topic
        $topic = $abstract->abstract_topic_id ? $this->abstractTopicModel->find($abstract->abstract_topic_id) : null;
        
        // Get all versions
        $versions = $this->abstractVersionModel->getAllAbstractVersionsByAbstractId($id);
        
        // Sort versions by version number in descending order
        usort($versions, function($a, $b) {
            return $b->version_number - $a->version_number;
        });
        
        // Get all authors
        $authors = $this->abstractAuthorModel->getAllAbstractAuthorsByAbstractId($id);
        
        $data = [
            'title' => 'View Abstract',
            'abstract' => $abstract,
            'participant' => $participant,
            'topic' => $topic,
            'versions' => $versions,
            'authors' => $authors
        ];

        return view('submissions/abstract-paper/view', $data);
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
        ];        return view('submissions/abstract-paper/create', $data);
    }

    public function store()
    {
        // Validate form input
        $validation = \Config\Services::validation();        $rules = [
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
        }

        $programId = session('current_program');        $data = [
            'program_id' => $programId,
            'primary_participant_id' => $this->request->getPost('participant_id'),
            'abstract_topic_id' => $this->request->getPost('abstract_topic_id'),
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
            usort($versions, function($a, $b) {
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
        ];        return view('submissions/abstract-paper/edit', $data);
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
        $validation = \Config\Services::validation();        $rules = [
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

        // Start a transaction
        $this->abstractModel->db->transBegin();        $data = [
            'status' => $this->request->getPost('status') ?? 'draft',
            'abstract_topic_id' => $this->request->getPost('abstract_topic_id'),
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

        return $this->response->setJSON([            'success' => true, 
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
                'success' => false,                'message' => 'Failed to delete abstract'
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

        $programId = session('current_program');

        if ($abstract->program_id != $programId) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have access to this abstract']);
        }        
        
        // Get participant details
        $participant = $this->participantModel->find($abstract->primary_participant_id);
        $abstract->participant_name = $participant ? $participant->full_name : 'N/A';
        $abstract->institution = $participant ? $participant->institution : 'N/A';
          // Get topic details
        if (!empty($abstract->abstract_topic_id)) {
            $topic = $this->abstractTopicModel->find($abstract->abstract_topic_id);
            $abstract->topic_name = $topic ? $topic->name : 'No topic selected';
            $abstract->topic_description = $topic ? $topic->description : '';
        } else {
            $abstract->topic_name = 'No topic selected';
            $abstract->topic_description = '';
        }
        
        // Get latest abstract version
        $versions = $this->abstractVersionModel->getAllAbstractVersionsByAbstractId($id);
        if (!empty($versions)) {
            // Sort versions by version number in descending order
            usort($versions, function($a, $b) {
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
        usort($versions, function($a, $b) {
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
        }        $topics = $this->abstractTopicModel->getAllAbstractTopicsByProgramId($programId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $topics
        ]);
    }
}
