<?php

namespace App\Controllers;

use App\Models\AbstractTopicModel;
use App\Models\ProgramModel;
use App\Models\WebSettingModel;

class AbstractTopics extends BaseController
{
    protected $abstractTopicModel;
    protected $programModel;
    protected $webSettingModel;

    public function __construct()
    {
        $this->abstractTopicModel = new AbstractTopicModel();
        $this->programModel = new ProgramModel();
        $this->webSettingModel = new WebSettingModel();
    }

    public function index()
    {
        // Get current program ID from session
        $programId = session('current_program');

        if (!$programId) {
            return redirect()->to('/welcome')->with('error', 'Please select a program first');
        }

        // Get program data
        $program = $this->programModel->find($programId);

        if (!$program) {
            return redirect()->to('/welcome')->with('error', 'Selected program not found');
        }

        // Get abstract topics for the current program
        $abstractTopics = $this->abstractTopicModel->getAllAbstractTopicsByProgramId($programId);
        
        // Initialize as empty array if no topics found
        if (empty($abstractTopics)) {
            $abstractTopics = [];
        }

        $webSettings = $this->webSettingModel->getSettingByProgramId($program->program_category_id);

        $data = [
            'title' => 'Abstract Topics',
            'abstractTopics' => $abstractTopics,
            'webSettings' => $webSettings,
            'program' => $program,
        ];

        return view('master-data/abstract-topics/index', $data);
    }

    /**
     * View a single abstract topic
     * 
     * @param int $id Abstract topic ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function view($id = null)
    {
        if (!$id) {
            return redirect()->to('/master-data/abstract-topics')->with('error', 'Abstract topic ID is required');
        }

        // Find the abstract topic
        $abstractTopic = $this->abstractTopicModel->getAbstractTopicById($id);

        // Check if abstract topic exists
        if (!$abstractTopic) {
            return redirect()->to('/master-data/abstract-topics')->with('error', 'Abstract topic not found');
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Check if abstract topic belongs to the current program
        if ($abstractTopic->program_id != $programId) {
            return redirect()->to('/master-data/abstract-topics')->with('error', 'You do not have access to this abstract topic');
        }

        $data = [
            'title' => 'View Abstract Topic',
            'abstractTopic' => $abstractTopic
        ];

        return view('master-data/abstract-topics/view', $data);
    }
    /**
     * Create a new abstract topic
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function create()
    {
        // Validate form data
        $rules = [
            'name' => 'required|max_length[255]',
            'description' => 'permit_empty|max_length[255]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $this->validator->getErrors())
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')
                ->with('error', 'Failed to create abstract topic: ' . implode(', ', $this->validator->getErrors()));
        }

        // Get current program ID from session
        $programId = session('current_program');

        if (!$programId) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No program selected. Please select a program first.'
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')
                ->with('error', 'No program selected. Please select a program first.');
        }

        // Prepare data
        $data = [
            'program_id' => $programId,
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'is_deleted' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Insert data
        try {
            $this->abstractTopicModel->insert($data);

            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Abstract topic created successfully.'
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')
                ->with('success', 'Abstract topic created successfully.');
        } catch (\Exception $e) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create abstract topic: ' . $e->getMessage()
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')
                ->with('error', 'Failed to create abstract topic: ' . $e->getMessage());
        }
    }
    /**
     * Update an abstract topic
     * 
     * @param int $id Abstract topic ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function update($id = null)
    {
        if (!$id) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Abstract topic ID is required'
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')->with('error', 'Abstract topic ID is required');
        }

        // Find the abstract topic
        $abstractTopic = $this->abstractTopicModel->getAbstractTopicById($id);

        // Check if abstract topic exists
        if (!$abstractTopic) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Abstract topic not found'
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')->with('error', 'Abstract topic not found');
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Check if abstract topic belongs to the current program
        if ($abstractTopic->program_id != $programId) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You do not have access to this abstract topic'
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')->with('error', 'You do not have access to this abstract topic');
        }

        // Validate form data
        $rules = [
            'name' => 'required|max_length[255]',
            'description' => 'permit_empty|max_length[255]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $this->validator->getErrors())
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')
                ->with('error', 'Failed to update abstract topic: ' . implode(', ', $this->validator->getErrors()));
        }

        // Prepare data
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Update the abstract topic
        try {
            $this->abstractTopicModel->update($id, $data);

            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Abstract topic updated successfully'
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')
                ->with('success', 'Abstract topic updated successfully');
        } catch (\Exception $e) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update abstract topic: ' . $e->getMessage()
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')
                ->with('error', 'Failed to update abstract topic: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete an abstract topic (soft delete)
     * 
     * @param int $id Abstract topic ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($id = null)
    {
        if (!$id) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Abstract topic ID is required'
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')->with('error', 'Abstract topic ID is required');
        }

        // Find the abstract topic
        $abstractTopic = $this->abstractTopicModel->getAbstractTopicById($id);

        // Check if abstract topic exists
        if (!$abstractTopic) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Abstract topic not found'
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')->with('error', 'Abstract topic not found');
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Check if abstract topic belongs to the current program
        if ($abstractTopic->program_id != $programId) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You do not have access to this abstract topic'
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')->with('error', 'You do not have access to this abstract topic');
        }

        // Soft delete the abstract topic
        try {
            $this->abstractTopicModel->update($id, [
                'is_deleted' => 1,
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Abstract topic deleted successfully'
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')
                ->with('success', 'Abstract topic deleted successfully');
        } catch (\Exception $e) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete abstract topic: ' . $e->getMessage()
                ]);
            }

            return redirect()->to('/master-data/abstract-topics')
                ->with('error', 'Failed to delete abstract topic: ' . $e->getMessage());
        }
    }

    /**
     * Get a single abstract topic via AJAX
     * 
     * @param int $id Abstract topic ID
     * @return \CodeIgniter\HTTP\Response
     */
    public function getAbstractTopic($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Abstract topic ID is required'
            ]);
        }

        // Find the abstract topic
        $abstractTopic = $this->abstractTopicModel->getAbstractTopicById($id);

        // Check if abstract topic exists
        if (!$abstractTopic) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Abstract topic not found'
            ]);
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Check if abstract topic belongs to the current program
        if ($abstractTopic->program_id != $programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have access to this abstract topic'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $abstractTopic
        ]);
    }
}
