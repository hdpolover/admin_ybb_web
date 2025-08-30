<?php

namespace App\Controllers;

use App\Models\ProgramModel;
use App\Models\ProgramCategoryModel;
use App\Models\AdminModel;

class Welcome extends AdminBaseController
{
    protected $programModel;
    protected $programCategoryModel;
    protected $adminModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
        $this->programCategoryModel = new ProgramCategoryModel();
        $this->adminModel = new AdminModel();
    }

    public function index()
    {
        // Ensure user authentication
        $this->requireAuth();
        
        // Get all available programs
        $allPrograms = $this->programCategoryModel->getAllCategoriesWithPrograms();
        
        // Filter programs based on admin access
        $accessiblePrograms = $this->filterProgramsByAdminAccess($allPrograms, $this->adminModel);
        
        // Sort programs by category name
        usort($accessiblePrograms, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });

        // Group by active and inactive
        $activePrograms = array_filter($accessiblePrograms, function ($program) {
            return $program->is_active == 1;
        });

        $inactivePrograms = array_filter($accessiblePrograms, function ($program) {
            return $program->is_active == 0;
        });

        $currentProgramId = session('current_program');
        
        // Check if a program is already selected and if admin has access to it
        $selectedProgram = null;
        if ($currentProgramId) {
            $selectedProgram = $this->validateProgramAccess($currentProgramId, $accessiblePrograms);
            
            // If the selected program is not accessible, unset the session variable
            if (!$selectedProgram) {
                session()->remove('current_program');
                
                // If admin has limited access, auto-select the first available program
                if (!empty($activePrograms) && $this->currentUser->role !== 'super_admin') {
                    $firstCategory = reset($activePrograms);
                    if (!empty($firstCategory->programs)) {
                        $firstProgram = reset($firstCategory->programs);
                        session()->set('current_program', $firstProgram->id);
                        $selectedProgram = $firstProgram;
                    }
                }
            }
        }

        $data = [
            'title' => 'Welcome',
            'selectedProgram' => $selectedProgram,
            'activePrograms' => $activePrograms,
            'inactivePrograms' => $inactivePrograms,
            'programs' => $accessiblePrograms,
            'currentUser' => $this->currentUser,
            'hasLimitedAccess' => $this->currentUser->role !== 'super_admin'
        ];

        return view('welcome/index', $data);
    }

    
    /**
     * Set the selected program (with access control)
     */
    public function setProgram($program_id)
    {
        // Ensure user authentication
        $this->requireAuth();
        
        // Get all available programs for access checking
        $allPrograms = $this->programCategoryModel->getAllCategoriesWithPrograms();
        $accessiblePrograms = $this->filterProgramsByAdminAccess($allPrograms, $this->adminModel);
        
        // Validate that the admin has access to this program
        $program = $this->validateProgramAccess($program_id, $accessiblePrograms);
        
        if (!$program) {
            return redirect()->to('welcome')->with('error', 'You do not have access to the selected program.');
        }
        
        // Ensure the program is active
        if (!$program->is_active) {
            return redirect()->to('welcome')->with('error', 'The selected program is not currently active.');
        }
        
        // Set the program in session
        session()->set('current_program', $program_id);
        
        // Clear topbar cache to force refresh with new program selection
        session()->remove('topbar_data');
        session()->remove('topbar_data_updated');
        
        // Force immediate preparation of topbar data with the new program selection
        $topbarData = $this->prepareTopbarData();
        session()->set('topbar_data', $topbarData);
        session()->set('topbar_data_updated', time());
        
        // Set a cookie to indicate a program has been selected (for JavaScript detection)
        $this->response->setCookie('has_program_selected', 'true', time() + 86400, '', '/', '', false, true);
        
        // If coming from another page (like dashboard), redirect back there
        $referer = $this->request->getServer('HTTP_REFERER');
        if (!empty($referer) && strpos($referer, 'welcome') === false) {
            return redirect()->to($referer)->with('success', "Program switched to: {$program->name}");
        }
        
        // Otherwise redirect to dashboard
        return redirect()->to('dashboard')->with('success', "Program selected: {$program->name}");
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth()
    {
        if (!session()->get('isLoggedIn') || !$this->currentUser) {
            return redirect()->to('/')->with('error', 'Please log in to continue.');
        }
    }
}