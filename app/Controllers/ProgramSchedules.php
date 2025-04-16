<?php

namespace App\Controllers;

use App\Models\ProgramScheduleModel;
use App\Models\ProgramModel;

class ProgramSchedules extends BaseController
{
    protected $programScheduleModel;
    protected $programModel;
    
    public function __construct()
    {
        $this->programScheduleModel = new ProgramScheduleModel();
        $this->programModel = new ProgramModel();
    }

    public function index()
    {
        // Get current program ID from session
        $programId = session('current_program');
        // $program data
        $program = $this->programModel->find($programId);
        
        // Get program schedules for the current program
        $programSchedules = $this->programScheduleModel->getByProgramId($programId, false);
        
        $data = [
            'program' => $program,
            'schedules' => $programSchedules
        ];
        return view('master-data/timelines/index', $data);
    }
    
    /**
     * View a single schedule
     * 
     * @param int $id Schedule ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function view($id = null)
    {
        if (!$id) {
            return redirect()->to('/master-data/timelines')->with('error', 'Schedule ID is required');
        }
        
        // Find the schedule
        $schedule = $this->programScheduleModel->find($id);
        
        // Check if schedule exists
        if (!$schedule) {
            return redirect()->to('/master-data/timelines')->with('error', 'Schedule not found');
        }
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Check if schedule belongs to the current program
        if ($schedule->program_id != $programId) {
            return redirect()->to('/master-data/timelines')->with('error', 'You do not have access to this schedule');
        }
        
        $data = [
            'schedule' => $schedule
        ];
        
        return view('master-data/timelines/view', $data);
    }
    
    /**
     * Create a new schedule
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function create()
    {
        // Validate form data
        $rules = [
            'name' => 'required|max_length[255]',
            'description' => 'required|max_length[255]',
            'start_date' => 'required|valid_date[Y-m-d]',
            'end_date' => 'required|valid_date[Y-m-d]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->to('/master-data/timelines')
                ->with('error', 'Failed to create schedule: ' . implode(', ', $this->validator->getErrors()));
        }
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Prepare data
        $data = [
            'program_id' => $programId,
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'order_number' => $this->request->getPost('order_number') ?: 0,
            'is_active' => $this->request->getPost('is_active') ?: 1,
            'is_deleted' => 0
        ];
        
        // Create new schedule
        try {
            $this->programScheduleModel->insert($data);
            return redirect()->to('/master-data/timelines')
                ->with('success', 'Schedule created successfully');
        } catch (\Exception $e) {
            return redirect()->to('/master-data/timelines')
                ->with('error', 'Failed to create schedule: ' . $e->getMessage());
        }
    }
    
    /**
     * Update a schedule
     * 
     * @param int $id Schedule ID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update($id = null)
    {
        if (!$id) {
            return redirect()->to('/master-data/timelines')->with('error', 'Schedule ID is required');
        }
        
        // Find the schedule
        $schedule = $this->programScheduleModel->find($id);
        
        // Check if schedule exists
        if (!$schedule) {
            return redirect()->to('/master-data/timelines')->with('error', 'Schedule not found');
        }
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Check if schedule belongs to the current program
        if ($schedule->program_id != $programId) {
            return redirect()->to('/master-data/timelines')->with('error', 'You do not have access to this schedule');
        }
        
        // Validate form data
        $rules = [
            'name' => 'required|max_length[255]',
            'description' => 'required|max_length[255]',
            'start_date' => 'required|valid_date[Y-m-d]',
            'end_date' => 'required|valid_date[Y-m-d]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->to('/master-data/timelines')
                ->with('error', 'Failed to update schedule: ' . implode(', ', $this->validator->getErrors()));
        }
        
        // Prepare data
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'order_number' => $this->request->getPost('order_number') ?: $schedule->order_number,
            'is_active' => $this->request->getPost('is_active') ?: 0
        ];
        
        // Update schedule
        try {
            $this->programScheduleModel->update($id, $data);
            return redirect()->to('/master-data/timelines')
                ->with('success', 'Schedule updated successfully');
        } catch (\Exception $e) {
            return redirect()->to('/master-data/timelines')
                ->with('error', 'Failed to update schedule: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a schedule
     * 
     * @param int $id Schedule ID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete($id = null)
    {
        if (!$id) {
            return redirect()->to('/master-data/timelines')->with('error', 'Schedule ID is required');
        }
        
        // Find the schedule
        $schedule = $this->programScheduleModel->find($id);
        
        // Check if schedule exists
        if (!$schedule) {
            return redirect()->to('/master-data/timelines')->with('error', 'Schedule not found');
        }
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Check if schedule belongs to the current program
        if ($schedule->program_id != $programId) {
            return redirect()->to('/master-data/timelines')->with('error', 'You do not have access to this schedule');
        }
        
        // Soft delete the schedule
        try {
            $this->programScheduleModel->update($id, ['is_deleted' => 1]);
            return redirect()->to('/master-data/timelines')
                ->with('success', 'Schedule deleted successfully');
        } catch (\Exception $e) {
            return redirect()->to('/master-data/timelines')
                ->with('error', 'Failed to delete schedule: ' . $e->getMessage());
        }
    }
    
    /**
     * Get a schedule by ID (for AJAX requests)
     * 
     * @param int $id Schedule ID
     * @return \CodeIgniter\HTTP\Response
     */
    public function getSchedule($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Schedule ID is required'
            ]);
        }
        
        // Find the schedule
        $schedule = $this->programScheduleModel->find($id);
        
        // Check if schedule exists
        if (!$schedule) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Schedule not found'
            ]);
        }
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Check if schedule belongs to the current program
        if ($schedule->program_id != $programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have access to this schedule'
            ]);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $schedule
        ]);
    }
}