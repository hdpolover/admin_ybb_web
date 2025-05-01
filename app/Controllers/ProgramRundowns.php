<?php

namespace App\Controllers;

use App\Models\ProgramRundownModel;
use App\Models\ProgramModel;

class ProgramRundowns extends BaseController
{
    protected $programRundownModel;
    protected $programModel;

    public function __construct()
    {
        $this->programRundownModel = new ProgramRundownModel();
        $this->programModel = new ProgramModel();
    }

    public function index()
    {
        // Get current program ID from session
        $programId = session('current_program');
        // $program data
        $program = $this->programModel->find($programId);

        // Get program rundowns for the current program
        $programRundowns = $this->programRundownModel->getByProgramId($programId);

        $data = [
            'program' => $program,
            'rundowns' => $programRundowns
        ];

        return view('master-data/program-rundowns/index', $data);
    }

    /**
     * Create a new rundown
     * 
     * @return \CodeIgniter\HTTP\Response|\CodeIgniter\HTTP\RedirectResponse
     */    public function create()
    {
        // Validate form data
        $rules = [
            'title' => 'required|max_length[255]',
            'description' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            // Handle AJAX request
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create rundown: ' . implode(', ', $this->validator->getErrors()),
                    'errors' => $this->validator->getErrors()
                ])->setStatusCode(422); // Unprocessable Entity
            }

            // Handle normal form submission
            return redirect()->to('/master-data/program-rundowns')
                ->with('error', 'Failed to create rundown: ' . implode(', ', $this->validator->getErrors()));
        }

        // Get current program ID from session
        $programId = session('current_program');        // Get date inputs
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');

        // Convert datetime-local format to MySQL datetime format
        // Input format is typically YYYY-MM-DDTHH:MM from the datetime-local input
        if ($startDate) {
            // Replace 'T' with space to make it MySQL compatible
            $startDate = str_replace('T', ' ', $startDate) . ':00';
        }

        if ($endDate) {
            // Replace 'T' with space to make it MySQL compatible
            $endDate = str_replace('T', ' ', $endDate) . ':00';
        }

        // Prepare data
        $data = [
            'program_id' => $programId,
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'order_number' => $this->request->getPost('order_number') ?: 0,
            'is_active' => $this->request->getPost('is_active') ?: 1,
            'is_deleted' => 0
        ];

        // Create new rundown
        try {
            $insertId = $this->programRundownModel->insert($data);

            // Handle AJAX request
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Rundown created successfully',
                    'data' => ['id' => $insertId]
                ]);
            }

            // Handle normal form submission
            return redirect()->to('/master-data/program-rundowns')
                ->with('success', 'Rundown created successfully');
        } catch (\Exception $e) {
            // Handle AJAX request
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create rundown: ' . $e->getMessage()
                ])->setStatusCode(500);
            }

            // Handle normal form submission
            return redirect()->to('/master-data/program-rundowns')
                ->with('error', 'Failed to create rundown: ' . $e->getMessage());
        }
    }
    /**
     * Update a rundown
     * 
     * @param int $id Rundown ID
     * @return \CodeIgniter\HTTP\Response|\CodeIgniter\HTTP\RedirectResponse
     */
    public function update($id = null)
    {
        if (!$id) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Rundown ID is required'
                ])->setStatusCode(400);
            }
            return redirect()->to('/master-data/program-rundowns')->with('error', 'Rundown ID is required');
        }

        // Find the rundown
        $rundown = $this->programRundownModel->find($id);

        // Check if rundown exists
        if (!$rundown) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Rundown not found'
                ])->setStatusCode(404);
            }
            return redirect()->to('/master-data/program-rundowns')->with('error', 'Rundown not found');
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Check if rundown belongs to the current program
        if ($rundown->program_id != $programId) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You do not have access to this rundown'
                ])->setStatusCode(403);
            }
            return redirect()->to('/master-data/program-rundowns')->with('error', 'You do not have access to this rundown');
        }        // Validate form data
        $rules = [
            'title' => 'required|max_length[255]',
            'description' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update rundown: ' . implode(', ', $this->validator->getErrors()),
                    'errors' => $this->validator->getErrors()
                ])->setStatusCode(422);
            }
            return redirect()->to('/master-data/program-rundowns')
                ->with('error', 'Failed to update rundown: ' . implode(', ', $this->validator->getErrors()));
        }        // Get date inputs
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');

        // Convert datetime-local format to MySQL datetime format
        // Input format is typically YYYY-MM-DDTHH:MM from the datetime-local input
        if ($startDate) {
            // Replace 'T' with space to make it MySQL compatible
            $startDate = str_replace('T', ' ', $startDate) . ':00';
        }

        if ($endDate) {
            // Replace 'T' with space to make it MySQL compatible
            $endDate = str_replace('T', ' ', $endDate) . ':00';
        }

        // Prepare data
        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'order_number' => $this->request->getPost('order_number') ?: $rundown->order_number,
            'is_active' => $this->request->getPost('is_active') ?: 0
        ];

        // Update rundown
        try {
            $this->programRundownModel->update($id, $data);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Rundown updated successfully',
                    'data' => ['id' => $id]
                ]);
            }

            return redirect()->to('/master-data/program-rundowns')
                ->with('success', 'Rundown updated successfully');
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update rundown: ' . $e->getMessage()
                ])->setStatusCode(500);
            }

            return redirect()->to('/master-data/program-rundowns')
                ->with('error', 'Failed to update rundown: ' . $e->getMessage());
        }
    }
    /**
     * Delete a rundown
     * 
     * @param int $id Rundown ID
     * @return \CodeIgniter\HTTP\Response|\CodeIgniter\HTTP\RedirectResponse
     */
    public function delete($id = null)
    {
        if (!$id) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Rundown ID is required'
                ])->setStatusCode(400);
            }
            return redirect()->to('/master-data/program-rundowns')->with('error', 'Rundown ID is required');
        }

        // Find the rundown
        $rundown = $this->programRundownModel->find($id);

        // Check if rundown exists
        if (!$rundown) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Rundown not found'
                ])->setStatusCode(404);
            }
            return redirect()->to('/master-data/program-rundowns')->with('error', 'Rundown not found');
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Check if rundown belongs to the current program
        if ($rundown->program_id != $programId) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You do not have access to this rundown'
                ])->setStatusCode(403);
            }
            return redirect()->to('/master-data/program-rundowns')->with('error', 'You do not have access to this rundown');
        }

        // Soft delete the rundown
        try {
            $this->programRundownModel->update($id, ['is_deleted' => 1]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Rundown deleted successfully'
                ]);
            }

            return redirect()->to('/master-data/program-rundowns')
                ->with('success', 'Rundown deleted successfully');
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete rundown: ' . $e->getMessage()
                ])->setStatusCode(500);
            }

            return redirect()->to('/master-data/program-rundowns')
                ->with('error', 'Failed to delete rundown: ' . $e->getMessage());
        }
    }
    /**
     * Get a rundown by ID (for AJAX requests)
     * 
     * @param int $id Rundown ID
     * @return \CodeIgniter\HTTP\Response
     */    public function getRundown($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Rundown ID is required'
            ]);
        }

        // Find the rundown
        $rundown = $this->programRundownModel->find($id);

        // Check if rundown exists
        if (!$rundown) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Rundown not found'
            ]);
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Check if rundown belongs to the current program
        if ($rundown->program_id != $programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have access to this rundown'
            ]);
        }        // Since we're getting an object directly from the model, just use it directly
        $formattedRundown = $rundown;

        // Log the rundown data for debugging
        log_message('debug', 'Rundown data: ' . json_encode($formattedRundown));

        // Format dates for the datetime-local input
        if (!empty($formattedRundown->start_date)) {
            try {
                $startDate = new \DateTime($formattedRundown->start_date);
                // Return in YYYY-MM-DDTHH:MM format for datetime-local input
                $formattedRundown->start_date_formatted = $startDate->format('Y-m-d\TH:i');
                log_message('debug', 'Formatted start_date: ' . $formattedRundown->start_date_formatted);
            } catch (\Exception $e) {
                log_message('error', 'Error formatting start_date: ' . $e->getMessage());
            }
        }

        if (!empty($formattedRundown->end_date)) {
            try {
                $endDate = new \DateTime($formattedRundown->end_date);
                // Return in YYYY-MM-DDTHH:MM format for datetime-local input
                $formattedRundown->end_date_formatted = $endDate->format('Y-m-d\TH:i');
                log_message('debug', 'Formatted end_date: ' . $formattedRundown->end_date_formatted);
            } catch (\Exception $e) {
                log_message('error', 'Error formatting end_date: ' . $e->getMessage());
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $formattedRundown
        ]);
    }

    /**
     * Get data for DataTables
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function getData()
    {
        // Get current program ID from session
        $programId = session('current_program');

        // Get all rundowns for this program
        $rundowns = $this->programRundownModel->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $rundowns
        ]);
    }
}
