<?php

namespace App\Controllers;

class Topbar extends BaseController
{
    protected $programModel;
    protected $programCategoryModel;


    public function __construct()
    {
        $this->programModel = new \App\Models\ProgramModel();
        $this->programCategoryModel = new \App\Models\ProgramCategoryModel();
    }

    // public function index()
    // {
    //     // get program category with programs
    //     $programs =  $this->programCategoryModel->getAllCategoriesWithPrograms();

    //     // sort programs by category name
    //     usort($programs, function ($a, $b) {
    //         return strcmp($a->name, $b->name);
    //     });

    //     // group by active and inactive
    //     $activePrograms = array_filter($programs, function ($program) {
    //         return $program->is_active == 1;
    //     });

    //     $inactivePrograms = array_filter($programs, function ($program) {
    //         return $program->is_active == 0;
    //     });

    //     $currentProgramId = session('current_program');
        
    //     // Check if a program is already selected
    //     if ($currentProgramId) {
    //         // Find the selected program in the list
    //         $selectedProgram = null;
    //         foreach ($programs as $category) {
    //             foreach ($category->programs as $program) {
    //                 if ($program->id == $currentProgramId) {
    //                     $selectedProgram = $program;
    //                     break 2; // Break out of both loops
    //                 }
    //             }
    //         }

    //         // If the selected program is not found, unset the session variable
    //         if (!$selectedProgram) {
    //             session()->remove('current_program');
    //         }
    //     }

    //     $data = [
    //         'title' => 'Welcome',
    //         'selectedProgram' => $selectedProgram,
    //         'activePrograms' => $activePrograms,
    //         'inactivePrograms' => $inactivePrograms,
    //     ];

    //     var_dump($data); // Debugging line to check the data structure
    //     // return view('welcome/index', $data);
    // }

    public function setProgram($program_id)
    {
        session()->set('current_program', $program_id);

        // Set a cookie to indicate a program has been selected (for JavaScript detection)
        $this->response->setCookie('has_program_selected', 'true', time() + 86400, '', '/', '', false, true);

        // If coming from another page (like dashboard), redirect back there
        $referer = $this->request->getServer('HTTP_REFERER');
        if (!empty($referer) && strpos($referer, 'welcome') === false) {
            return redirect()->to($referer);
        }

        // Otherwise redirect to dashboard
        return redirect()->to('dashboard');
    }
}
