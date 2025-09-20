<?php

namespace App\Controllers;

class Topbar extends AdminBaseController
{
    protected $programModel;
    protected $programCategoryModel;

    public function __construct()
    {
        $this->programModel = new \App\Models\ProgramModel();
        $this->programCategoryModel = new \App\Models\ProgramCategoryModel();
    }

    public function setProgram($program_id)
    {
        // Ensure user authentication
        $this->requireAuth();
        
        // Validate program access (using existing method from AdminBaseController)
        $allPrograms = $this->programCategoryModel->getAllCategoriesWithPrograms();
        $accessiblePrograms = $this->filterProgramsByAdminAccess($allPrograms);
        
        // Validate that the admin has access to this program
        $program = $this->validateProgramAccess($program_id, $accessiblePrograms);
        
        if (!$program) {
            return redirect()->back()->with('error', 'You do not have access to the selected program.');
        }
        
        // Allow selection of both active and inactive programs
        // Removed the inactive program check to maintain consistency with Welcome controller
        
        // Set the program in session
        session()->set('current_program', $program_id);
        
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
        if (!empty($referer) && strpos($referer, 'welcome') === false) {
            return redirect()->to($referer)->with('success', "Program switched to: {$program->name}");
        }

        // Otherwise redirect to dashboard
        return redirect()->to('dashboard')->with('success', "Program selected: {$program->name}");
    }
}
