<?php

namespace App\Controllers;

use App\Models\AbstractSettingModel;
use App\Models\ProgramModel;
use App\Models\WebSettingModel;

class AbstractSettings extends BaseController
{
    protected $abstractSettingModel;
    protected $programModel;
    protected $webSettingModel;

    public function __construct()
    {
        $this->abstractSettingModel = new AbstractSettingModel();
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

        // Get abstract settings for the current program
        $abstractSettings = $this->abstractSettingModel->getByProgramId($programId);

        $webSettings = $this->webSettingModel->getSettingByProgramId($program->program_category_id);

        $data = [
            'title' => 'Abstract Settings',
            'abstractSettings' => $abstractSettings,
            'webSettings' => $webSettings,
            'program' => $program,
        ];

        return view('master-data/abstract-settings/index', $data);
    }    /**
     * View abstract settings
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function view()
    {
        // Get current program ID from session
        $programId = session('current_program');

        if (!$programId) {
            return redirect()->to('/master-data/abstract-settings')->with('error', 'Please select a program first');
        }

        // Get abstract settings for the current program
        $abstractSettings = $this->abstractSettingModel->getByProgramId($programId);

        $data = [
            'title' => 'View Abstract Settings',
            'abstractSettings' => $abstractSettings,
            'programId' => $programId
        ];

        return view('master-data/abstract-settings/view', $data);
    }    /**
     * Create or update abstract settings
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function save()
    {
        // Validate form data
        $rules = [
            'title_length' => 'required|integer|greater_than[0]',
            'content_length' => 'required|integer|greater_than[0]',
            'keywords_length' => 'required|integer|greater_than[0]',
            'refs_length' => 'required|integer|greater_than[0]',
            'paper_template_url' => 'permit_empty|valid_url',
            'abstract_template_url' => 'permit_empty|valid_url',
            'abstract_submission_deadline' => 'permit_empty|valid_date',
            'full_paper_submission_deadline' => 'permit_empty|valid_date',
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

            return redirect()->to('/master-data/abstract-settings')
                ->with('error', 'Failed to save abstract settings: ' . implode(', ', $this->validator->getErrors()));
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

            return redirect()->to('/master-data/abstract-settings')
                ->with('error', 'No program selected. Please select a program first.');
        }

        // Check if settings already exist for this program
        $existingSettings = $this->abstractSettingModel->getByProgramId($programId);        // Prepare data
        $data = [
            'program_id' => $programId,
            'title_length' => $this->request->getPost('title_length'),
            'content_length' => $this->request->getPost('content_length'),
            'keywords_length' => $this->request->getPost('keywords_length'),
            'refs_length' => $this->request->getPost('refs_length'),
            'paper_template_url' => $this->request->getPost('paper_template_url') ?: null,
            'abstract_template_url' => $this->request->getPost('abstract_template_url') ?: null,
            'abstract_submission_deadline' => $this->request->getPost('abstract_submission_deadline') ?: null,
            'full_paper_submission_deadline' => $this->request->getPost('full_paper_submission_deadline') ?: null,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'is_deleted' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            if ($existingSettings) {
                // Update existing settings
                $this->abstractSettingModel->update($existingSettings->id, $data);
                $message = 'Abstract settings updated successfully.';
            } else {
                // Create new settings
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->abstractSettingModel->insert($data);
                $message = 'Abstract settings created successfully.';
            }

            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->to('/master-data/abstract-settings')
                ->with('success', $message);
        } catch (\Exception $e) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save abstract settings: ' . $e->getMessage()
                ]);
            }

            return redirect()->to('/master-data/abstract-settings')
                ->with('error', 'Failed to save abstract settings: ' . $e->getMessage());
        }
    }/**
     * Reset abstract settings to default values
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function reset()
    {
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

            return redirect()->to('/master-data/abstract-settings')
                ->with('error', 'No program selected. Please select a program first.');
        }

        // Check if settings exist for this program
        $existingSettings = $this->abstractSettingModel->getByProgramId($programId);

        if (!$existingSettings) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No abstract settings found for this program'
                ]);
            }

            return redirect()->to('/master-data/abstract-settings')
                ->with('error', 'No abstract settings found for this program');
        }        // Default settings
        $defaultData = [
            'title_length' => 15,
            'content_length' => 500,
            'keywords_length' => 5,
            'refs_length' => 100,
            'paper_template_url' => null,
            'abstract_submission_deadline' => null,
            'full_paper_submission_deadline' => null,
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $this->abstractSettingModel->update($existingSettings->id, $defaultData);

            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Abstract settings reset to default values successfully'
                ]);
            }

            return redirect()->to('/master-data/abstract-settings')
                ->with('success', 'Abstract settings reset to default values successfully');
        } catch (\Exception $e) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to reset abstract settings: ' . $e->getMessage()
                ]);
            }

            return redirect()->to('/master-data/abstract-settings')
                ->with('error', 'Failed to reset abstract settings: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate abstract settings
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function deactivate()
    {
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

            return redirect()->to('/master-data/abstract-settings')
                ->with('error', 'No program selected. Please select a program first.');
        }

        // Check if settings exist for this program
        $existingSettings = $this->abstractSettingModel->getByProgramId($programId);

        if (!$existingSettings) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No abstract settings found for this program'
                ]);
            }

            return redirect()->to('/master-data/abstract-settings')
                ->with('error', 'No abstract settings found for this program');
        }

        try {
            $this->abstractSettingModel->update($existingSettings->id, [
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Abstract settings deactivated successfully'
                ]);
            }

            return redirect()->to('/master-data/abstract-settings')
                ->with('success', 'Abstract settings deactivated successfully');
        } catch (\Exception $e) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to deactivate abstract settings: ' . $e->getMessage()
                ]);
            }

            return redirect()->to('/master-data/abstract-settings')
                ->with('error', 'Failed to deactivate abstract settings: ' . $e->getMessage());
        }
    }

    /**
     * Get abstract settings via AJAX
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function getSettings()
    {
        // Get current program ID from session
        $programId = session('current_program');

        if (!$programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No program selected. Please select a program first.'
            ]);
        }

        // Get abstract settings for the current program
        $abstractSettings = $this->abstractSettingModel->getByProgramId($programId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $abstractSettings
        ]);
    }

    /**
     * Create default abstract settings for the current program
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function createDefault()
    {
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

            return redirect()->to('/master-data/abstract-settings')
                ->with('error', 'No program selected. Please select a program first.');
        }

        // Check if settings already exist for this program
        $existingSettings = $this->abstractSettingModel->getByProgramIdAll($programId);

        if ($existingSettings) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Abstract settings already exist for this program'
                ]);
            }

            return redirect()->to('/master-data/abstract-settings')
                ->with('error', 'Abstract settings already exist for this program');
        }

        try {
            $this->abstractSettingModel->createDefaultSettings($programId);

            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Default abstract settings created successfully'
                ]);
            }

            return redirect()->to('/master-data/abstract-settings')
                ->with('success', 'Default abstract settings created successfully');
        } catch (\Exception $e) {
            // Check if request is AJAX
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create default abstract settings: ' . $e->getMessage()
                ]);
            }

            return redirect()->to('/master-data/abstract-settings')
                ->with('error', 'Failed to create default abstract settings: ' . $e->getMessage());
        }
    }
}
