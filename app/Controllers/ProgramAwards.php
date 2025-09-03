<?php

namespace App\Controllers;

use App\Models\ProgramAwardModel;
use App\Traits\Cacheable;

class ProgramAwards extends AdminBaseController
{
    use Cacheable;
    
    protected $programAwardModel;

    public function __construct()
    {
        $this->programAwardModel = new ProgramAwardModel();
    }

    /**
     * Display the program awards index page
     */
    public function index()
    {
        try {
            $programId = session('current_program');
            if (!$programId) {
                return redirect()->to('/welcome')->with('error', 'Please select a program first.');
            }

            $data = [
                'title' => 'Program Awards',
                'awards' => []
            ];

            return view('master-data/program-awards/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load program awards index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load page: ' . $e->getMessage());
        }
    }

    /**
     * Get awards data for DataTables
     */
    public function getData()
    {
        try {
            $programId = session('current_program');
            if (!$programId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No program selected'
                ]);
            }

            $awards = $this->programAwardModel->getActiveAwardsByProgram($programId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $awards,
                'message' => 'Awards retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch program awards: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve awards: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Get a specific program award
     */
    public function getAward($id)
    {
        try {
            $award = $this->programAwardModel->getAwardWithProgram($id);

            if (!$award) {
                return $this->response->setStatusCode(404)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Award not found'
                                     ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $award,
                'message' => 'Award retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch program award: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve award: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Display award details
     */
    public function view($id)
    {
        try {
            $award = $this->programAwardModel->getAwardWithProgram($id);

            if (!$award) {
                return redirect()->to('/master-data/program-awards')->with('error', 'Award not found');
            }

            $data = [
                'title' => 'Award Details',
                'award' => $award
            ];

            return view('master-data/program-awards/view', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load award details: ' . $e->getMessage());
            return redirect()->to('/master-data/program-awards')->with('error', 'Failed to load award details');
        }
    }

    /**
     * Create a new program award
     */
    public function create()
    {
        try {
            $data = $this->request->getPost();
            $programId = session('current_program');

            if (!$programId) {
                return redirect()->back()->with('error', 'No program selected');
            }

            // Set program ID and default values
            $data['program_id'] = $programId;
            $data['is_active'] = $data['is_active'] ?? 1;
            $data['is_deleted'] = 0;

            if ($this->programAwardModel->save($data)) {
                // Invalidate program cache after successful creation
                $this->invalidateProgramCache($programId);
                
                return redirect()->to('/master-data/program-awards')
                                ->with('success', 'Award created successfully');
            } else {
                $errors = $this->programAwardModel->errors();
                return redirect()->back()
                                ->with('error', 'Validation failed: ' . implode(', ', $errors))
                                ->withInput();
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to create program award: ' . $e->getMessage());
            return redirect()->back()
                            ->with('error', 'Failed to create award: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Update a program award
     */
    public function update($id)
    {
        try {
            $award = $this->programAwardModel->where('is_deleted', 0)->find($id);

            if (!$award) {
                return redirect()->to('/master-data/program-awards')
                                ->with('error', 'Award not found');
            }

            $data = $this->request->getPost();

            if ($this->programAwardModel->update($id, $data)) {
                // Invalidate program cache after successful update
                $this->invalidateProgramCache($award['program_id']);
                
                return redirect()->to('/master-data/program-awards')
                                ->with('success', 'Award updated successfully');
            } else {
                $errors = $this->programAwardModel->errors();
                return redirect()->back()
                                ->with('error', 'Validation failed: ' . implode(', ', $errors))
                                ->withInput();
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to update program award: ' . $e->getMessage());
            return redirect()->back()
                            ->with('error', 'Failed to update award: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Delete a program award (soft delete)
     */
    public function delete($id)
    {
        try {
            $award = $this->programAwardModel->where('is_deleted', 0)->find($id);

            if (!$award) {
                return redirect()->to('/master-data/program-awards')
                                ->with('error', 'Award not found');
            }

            if ($this->programAwardModel->softDelete($id)) {
                // Invalidate program cache after successful deletion
                $this->invalidateProgramCache($award['program_id']);
                
                return redirect()->to('/master-data/program-awards')
                                ->with('success', 'Award deleted successfully');
            } else {
                return redirect()->to('/master-data/program-awards')
                                ->with('error', 'Failed to delete award');
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to delete program award: ' . $e->getMessage());
            return redirect()->to('/master-data/program-awards')
                            ->with('error', 'Failed to delete award: ' . $e->getMessage());
        }
    }
}
