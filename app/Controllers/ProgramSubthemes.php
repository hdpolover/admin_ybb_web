<?php

namespace App\Controllers;

use App\Models\ProgramSubthemeModel;
use App\Models\ProgramModel;
use App\Traits\Cacheable;

class ProgramSubthemes extends AdminBaseController
{
    use Cacheable;
    
    protected $programSubthemeModel;
    protected $programModel;

    public function __construct()
    {
        $this->programSubthemeModel = new ProgramSubthemeModel();
        $this->programModel = new ProgramModel();
    }

    /**
     * Display the program subthemes index page
     */
    public function index()
    {
        try {
            $programId = session('current_program');
            if (!$programId) {
                return redirect()->to('/welcome')->with('error', 'Please select a program first.');
            }

            // Get program data
            $program = $this->programModel->find($programId);
            if (!$program) {
                return redirect()->to('/welcome')->with('error', 'Selected program not found.');
            }

            // Get topbar data from session (already loaded by AdminBaseController)
            $topbarData = $this->session->get('topbar_data', []);

            $data = [
                'title' => 'Program Subthemes',
                'program' => $program,
                'topbarData' => $topbarData
            ];

            return view('master-data/program-subthemes/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load program subthemes index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load page: ' . $e->getMessage());
        }
    }

    /**
     * Get subthemes data for DataTables
     */
    public function getData()
    {
        try {
            $programId = session('current_program');
            if (!$programId) {
                return $this->response->setJSON(['error' => 'No program selected']);
            }

            // Process DataTables server-side request
            $request = $this->request->getGet();
            
            $draw = $request['draw'] ?? 1;
            $start = $request['start'] ?? 0;
            $length = $request['length'] ?? 10;
            $search = $request['search']['value'] ?? '';

            // Get filtered and paginated data
            $subthemes = $this->programSubthemeModel->getDataTablesData(
                $programId,
                $start,
                $length,
                $search
            );

            $totalRecords = $this->programSubthemeModel->where('program_id', $programId)->countAllResults();
            $filteredRecords = $this->programSubthemeModel->getFilteredCount($programId, $search);

            // Format data for DataTables
            $data = [];
            foreach ($subthemes as $subtheme) {
                $data[] = [
                    'id' => $subtheme->id,
                    'name' => htmlspecialchars($subtheme->name),
                    'description' => htmlspecialchars($subtheme->description ?? ''),
                    'is_active' => $subtheme->is_active ? 
                        '<span class="badge bg-success">Active</span>' : 
                        '<span class="badge bg-secondary">Inactive</span>',
                    'created_at' => date('Y-m-d H:i', strtotime($subtheme->created_at)),
                    'actions' => $this->generateActionButtons($subtheme->id)
                ];
            }

            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get subthemes data: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load data']);
        }
    }

    /**
     * Generate action buttons for DataTables
     */
    private function generateActionButtons($id)
    {
        return '
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSubtheme(' . $id . ')" title="View">
                    <i class="ri-eye-line"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning" onclick="editSubtheme(' . $id . ')" title="Edit">
                    <i class="ri-edit-line"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSubtheme(' . $id . ')" title="Delete">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        ';
    }

    /**
     * Show subtheme details
     */
    public function view($id)
    {
        try {
            $subtheme = $this->programSubthemeModel->find($id);
            if (!$subtheme) {
                return redirect()->to('/master-data/program-subthemes')->with('error', 'Subtheme not found');
            }

            // Get topbar data from session (already loaded by AdminBaseController)
            $topbarData = $this->session->get('topbar_data', []);

            $data = [
                'title' => 'Subtheme Details',
                'subtheme' => $subtheme,
                'topbarData' => $topbarData
            ];

            return view('master-data/program-subthemes/view', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load subtheme details: ' . $e->getMessage());
            return redirect()->to('/master-data/program-subthemes')->with('error', 'Failed to load subtheme details');
        }
    }

    /**
     * Show add subtheme form
     */
    public function add()
    {
        try {
            $programId = session('current_program');
            if (!$programId) {
                return redirect()->to('/welcome')->with('error', 'Please select a program first.');
            }

            // Get topbar data from session (already loaded by AdminBaseController)
            $topbarData = $this->session->get('topbar_data', []);

            $data = [
                'title' => 'Add New Subtheme',
                'programId' => $programId,
                'topbarData' => $topbarData
            ];

            return view('master-data/program-subthemes/add', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load add subtheme form: ' . $e->getMessage());
            return redirect()->to('/master-data/program-subthemes')->with('error', 'Failed to load form');
        }
    }

    /**
     * Create new subtheme
     */
    public function create()
    {
        try {
            $programId = session('current_program');
            if (!$programId) {
                return redirect()->to('/welcome')->with('error', 'Please select a program first.');
            }

            // Validate input
            $rules = [
                'name' => 'required|max_length[255]',
                'description' => 'permit_empty|max_length[1000]',
                'is_active' => 'permit_empty|in_list[0,1]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Prepare data
            $data = [
                'program_id' => $programId,
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'is_active' => $this->request->getPost('is_active') ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Save to database
            if ($this->programSubthemeModel->insert($data)) {
                // Clear cache
                $this->clearRelatedCache();
                
                return redirect()->to('/master-data/program-subthemes')
                    ->with('success', 'Subtheme created successfully');
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to create subtheme');
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to create subtheme: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create subtheme: ' . $e->getMessage());
        }
    }

    /**
     * Show edit subtheme form
     */
    public function edit($id)
    {
        try {
            $subtheme = $this->programSubthemeModel->find($id);
            if (!$subtheme) {
                return redirect()->to('/master-data/program-subthemes')->with('error', 'Subtheme not found');
            }

            // Get topbar data from session (already loaded by AdminBaseController)
            $topbarData = $this->session->get('topbar_data', []);

            $data = [
                'title' => 'Edit Subtheme',
                'subtheme' => $subtheme,
                'topbarData' => $topbarData
            ];

            return view('master-data/program-subthemes/edit', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load edit subtheme form: ' . $e->getMessage());
            return redirect()->to('/master-data/program-subthemes')->with('error', 'Failed to load form');
        }
    }

    /**
     * Update subtheme
     */
    public function update($id)
    {
        try {
            $subtheme = $this->programSubthemeModel->find($id);
            if (!$subtheme) {
                return redirect()->to('/master-data/program-subthemes')->with('error', 'Subtheme not found');
            }

            // Validate input
            $rules = [
                'name' => 'required|max_length[255]',
                'description' => 'permit_empty|max_length[1000]',
                'is_active' => 'permit_empty|in_list[0,1]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Prepare data
            $data = [
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'is_active' => $this->request->getPost('is_active') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update in database
            if ($this->programSubthemeModel->update($id, $data)) {
                // Clear cache
                $this->clearRelatedCache();
                
                return redirect()->to('/master-data/program-subthemes')
                    ->with('success', 'Subtheme updated successfully');
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to update subtheme');
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to update subtheme: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update subtheme: ' . $e->getMessage());
        }
    }

    /**
     * Delete subtheme
     */
    public function delete($id)
    {
        try {
            $subtheme = $this->programSubthemeModel->find($id);
            if (!$subtheme) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Subtheme not found'
                ]);
            }

            // Check if subtheme is being used
            // You might want to add checks here for related records

            if ($this->programSubthemeModel->delete($id)) {
                // Clear cache
                $this->clearRelatedCache();
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Subtheme deleted successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete subtheme'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to delete subtheme: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete subtheme: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Clear related cache
     */
    private function clearRelatedCache()
    {
        // Clear topbar cache to refresh program data
        $this->clearTopbarCache();
        
        // If you have other cache keys for subthemes, clear them here
        $cache = \Config\Services::cache();
        $cache->delete('program_subthemes_' . session('current_program'));
    }
}
