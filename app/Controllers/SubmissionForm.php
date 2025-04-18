<?php

namespace App\Controllers;

use App\Models\ProgramModel;

class SubmissionForm extends BaseController
{
    protected $programModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
    }

    public function index()
    {
        // get program id from session
        $programId = session()->get('current_program');

        // get current program details
        $currentProgram = $this->programModel->find($programId);
        
        $data = [
            'title' => 'Submission Form',
            'currentProgram' => $currentProgram,
        ];

        return view('master-data/submission-form/index', $data);
    }

    // edit
    public function edit($id)
    {
        $program = $this->programModel->find($id);

        if (!$program) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Program not found');
        }

        $data = [
            'title' => 'Edit Program',
            'program' => $program,
        ];

        return view('master-data/program-details/edit', $data);
    }

    
}
