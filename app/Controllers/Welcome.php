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
        $accessiblePrograms = $this->filterProgramsByAdminAccess($allPrograms);
        
        // Sort programs by category name
        usort($accessiblePrograms, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });

        // Extract individual programs from categories and group by active and inactive
        $allIndividualPrograms = [];
        $activePrograms = [];
        $inactivePrograms = [];
        
        foreach ($accessiblePrograms as $category) {
            if (isset($category->programs) && is_array($category->programs)) {
                foreach ($category->programs as $program) {
                    // Add logo URL and category name to the program
                    $program->logo_url = $category->logo_url ?? null;
                    $program->category_name = $category->name ?? null;
                    
                    $allIndividualPrograms[] = $program;
                    
                    if ($program->is_active == 1) {
                        $activePrograms[] = $program;
                    } else {
                        $inactivePrograms[] = $program;
                    }
                }
            }
        }

        $currentProgramId = session('current_program');
        
        // Check if a program is already selected and if admin has access to it
        $selectedProgram = null;
        if ($currentProgramId) {
            // Create a mock structure that validateProgramAccess expects
            $mockAccessiblePrograms = [
                (object)[
                    'programs' => $allIndividualPrograms,
                    'logo_url' => null,
                    'name' => 'All Programs'
                ]
            ];
            $selectedProgram = $this->validateProgramAccess($currentProgramId, $mockAccessiblePrograms);
            
            // If the selected program is not accessible, unset the session variable
            if (!$selectedProgram) {
                session()->remove('current_program');
                
                // If admin has limited access, auto-select the first available program
                if (!empty($activePrograms) && $this->currentUser->role !== 'super_admin') {
                    $firstProgram = reset($activePrograms);
                    if ($firstProgram) {
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
        // Debug logging
        log_message('debug', "Welcome::setProgram called with program_id: {$program_id}");
        
        // Ensure user authentication
        $this->requireAuth();
        
        // Get all available programs for access checking
        $allPrograms = $this->programCategoryModel->getAllCategoriesWithPrograms();
        $accessiblePrograms = $this->filterProgramsByAdminAccess($allPrograms);
        
        // Extract individual programs from categories for validation
        $allIndividualPrograms = [];
        foreach ($accessiblePrograms as $category) {
            if (isset($category->programs) && is_array($category->programs)) {
                foreach ($category->programs as $program) {
                    // Add logo URL and category name to the program
                    $program->logo_url = $category->logo_url ?? null;
                    $program->category_name = $category->name ?? null;
                    $allIndividualPrograms[] = $program;
                }
            }
        }
        
        log_message('debug', "Welcome::setProgram - Found " . count($accessiblePrograms) . " accessible program categories with " . count($allIndividualPrograms) . " total programs");
        
        // Create mock structure for validateProgramAccess
        $mockAccessiblePrograms = [
            (object)[
                'programs' => $allIndividualPrograms,
                'logo_url' => null,
                'name' => 'All Programs'
            ]
        ];
        
        // Validate that the admin has access to this program
        $program = $this->validateProgramAccess($program_id, $mockAccessiblePrograms);
        
        log_message('debug', "Welcome::setProgram - validateProgramAccess result: " . ($program ? "Found program: {$program->name}" : "Program not found"));
        
        if (!$program) {
            log_message('debug', "Welcome::setProgram - Access denied for program_id: {$program_id}");
            return redirect()->to('welcome')->with('error', 'You do not have access to the selected program.');
        }
        
        // Allow selection of both active and inactive programs
        log_message('debug', "Welcome::setProgram - Program status: " . ($program->is_active ? 'Active' : 'Inactive') . " - {$program_id}");
        
        // Set the program in session
        session()->set('current_program', $program_id);
        log_message('debug', "Welcome::setProgram - Set current_program in session: {$program_id}");
        
        // Clear topbar cache to force refresh with new program selection
        session()->remove('topbar_data');
        session()->remove('topbar_data_updated');
        $this->clearTopbarCache();
        
        // Force refresh of topbar data by clearing cache and reloading
        $this->loadTopbarData();
        
        // Set a cookie to indicate a program has been selected (for JavaScript detection)
        $this->response->setCookie('has_program_selected', 'true', time() + 86400, '', '/', '', false, true);
        
        // If coming from another page (like dashboard), redirect back there
        $referer = $this->request->getServer('HTTP_REFERER');
        log_message('debug', "Welcome::setProgram - Referer: " . ($referer ?? 'None'));
        
        if (!empty($referer) && strpos($referer, 'welcome') === false) {
            log_message('debug', "Welcome::setProgram - Redirecting back to referer");
            return redirect()->to($referer)->with('success', "Program switched to: {$program->name}");
        }
        
        // Otherwise redirect to dashboard
        log_message('debug', "Welcome::setProgram - Redirecting to dashboard");
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