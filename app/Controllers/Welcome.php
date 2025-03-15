<?php

namespace App\Controllers;

class Welcome extends BaseController
{
    protected $programModel;
    protected $programCategoryModel;


    public function __construct()
    {
        $this->programModel = new \App\Models\ProgramModel();
        $this->programCategoryModel = new \App\Models\ProgramCategoryModel();
    }

    public function index()
    {
        // get program category with programs
        $programs =  $this->programCategoryModel->getAllCategoriesWithPrograms();

        // sort programs by category name
        usort($programs, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });

        $data = [
            'programs' => $programs,
        ];

        return view('welcome/index', $data);
    }

    public function set_program($program_id)
    {
        session()->set('current_program', $program_id);
        
        // If coming from another page (like dashboard), redirect back there
        $referer = $this->request->getServer('HTTP_REFERER');
        if (!empty($referer) && strpos($referer, 'welcome') === false) {
            return redirect()->to($referer);
        }
        
        // Otherwise redirect to dashboard
        return redirect()->to('dashboard');
    }
}