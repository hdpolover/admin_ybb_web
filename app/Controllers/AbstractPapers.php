<?php

namespace App\Controllers;

use App\Models\ProgramModel;
use App\Models\AbstractModel;
use App\Models\ParticipantModel;

class AbstractPapers extends BaseController
{
    protected $abstractModel;
    protected $programModel;
    protected $participantModel;

    public function __construct()
    {
        $this->abstractModel = new AbstractModel();
        $this->programModel = new ProgramModel();
        $this->participantModel = new ParticipantModel();
    }

    public function index()
    {
        // Get current program ID from session
        $programId = session('current_program');
        
        // Get program data
        $program = $this->programModel->find($programId);
        
        // Get abstracts for the current program
        $abstracts = $this->abstractModel->where('program_id', $programId)
                                         ->where('is_deleted', 0)
                                         ->findAll();
        
        $data = [
            'title' => 'Abstract Papers',
            'abstracts' => $abstracts,
            'program' => $program
        ];
        
        return view('documents/abstract-paper/index', $data);
    }

    public function getAbstractsByProgram($programId = null)
    {
        if (!$programId) {
            $programId = session('current_program');
        }
        
        $abstracts = $this->abstractModel->where('program_id', $programId)
                                        ->where('is_deleted', 0)
                                        ->findAll();
                                        
        // Get participant details for each abstract
        foreach ($abstracts as &$abstract) {
            $participant = $this->participantModel->find($abstract->participant_id);
            $abstract->participant_name = $participant ? $participant->full_name : 'N/A';
            $abstract->institution = $participant ? $participant->institution : 'N/A';
        }
        
        return $this->response->setJSON([
            'status' => true,
            'data' => $abstracts
        ]);
    }

    public function view($id = null)
    {
        if (!$id) {
            return redirect()->to('/documents/abstracts-papers')->with('error', 'Abstract ID is required');
        }
        
        $abstract = $this->abstractModel->find($id);
        
        if (!$abstract) {
            return redirect()->to('/documents/abstracts-papers')->with('error', 'Abstract not found');
        }
        
        $programId = session('current_program');
        
        if ($abstract->program_id != $programId) {
            return redirect()->to('/documents/abstracts-papers')->with('error', 'You do not have access to this abstract');
        }
        
        // Get participant details
        $participant = $this->participantModel->find($abstract->participant_id);
        
        $data = [
            'title' => 'View Abstract',
            'abstract' => $abstract,
            'participant' => $participant
        ];
        
        return view('documents/abstract-paper/view', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Create New Abstract',
            'programs' => $this->programModel->findAll()
        ];
        return view('documents/abstract-paper/create', $data);
    }

    public function store()
    {
        // Validate form input
        $validation = \Config\Services::validation();
        
        $rules = [
            'participant_id' => 'required',
            'title' => 'required|min_length[5]',
            'content' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        $programId = session('current_program');
        
        $data = [
            'program_id' => $programId,
            'participant_id' => $this->request->getPost('participant_id'),
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'status' => $this->request->getPost('status') ?? 0,
            'submitted_at' => date('Y-m-d H:i:s'),
            'is_deleted' => 0
        ];
        
        if ($this->abstractModel->insert($data)) {
            return redirect()->to('/documents/abstracts-papers')->with('success', 'Abstract has been added successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to add abstract');
        }
    }
    
    public function edit($id = null)
    {
        if (!$id) {
            return redirect()->to('/documents/abstracts-papers')->with('error', 'Abstract ID is required');
        }
        
        $abstract = $this->abstractModel->find($id);
        
        if (!$abstract) {
            return redirect()->to('/documents/abstracts-papers')->with('error', 'Abstract not found');
        }
        
        $programId = session('current_program');
        
        if ($abstract->program_id != $programId) {
            return redirect()->to('/documents/abstracts-papers')->with('error', 'You do not have access to this abstract');
        }
        
        $data = [
            'title' => 'Edit Abstract',
            'abstract' => $abstract,
            'participants' => $this->participantModel->where('program_id', $programId)->findAll()
        ];
        
        return view('documents/abstract-paper/edit', $data);
    }
    
    public function update($id = null)
    {
        if (!$id) {
            return redirect()->to('/documents/abstracts-papers')->with('error', 'Abstract ID is required');
        }
        
        // Validate form input
        $validation = \Config\Services::validation();
        
        $rules = [
            'title' => 'required|min_length[5]',
            'content' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        $data = [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'status' => $this->request->getPost('status') ?? 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->abstractModel->update($id, $data)) {
            return redirect()->to('/documents/abstracts-papers')->with('success', 'Abstract has been updated successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update abstract');
        }
    }
    
    public function delete($id = null)
    {
        if (!$id) {
            return redirect()->to('/documents/abstracts-papers')->with('error', 'Abstract ID is required');
        }
        
        $abstract = $this->abstractModel->find($id);
        
        if (!$abstract) {
            return redirect()->to('/documents/abstracts-papers')->with('error', 'Abstract not found');
        }
        
        $programId = session('current_program');
        
        if ($abstract->program_id != $programId) {
            return redirect()->to('/documents/abstracts-papers')->with('error', 'You do not have access to this abstract');
        }
        
        // Soft delete
        if ($this->abstractModel->update($id, ['is_deleted' => 1])) {
            return redirect()->to('/documents/abstracts-papers')->with('success', 'Abstract has been deleted successfully');
        } else {
            return redirect()->to('/documents/abstracts-papers')->with('error', 'Failed to delete abstract');
        }
    }
    
    public function getAbstractData($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Abstract ID is required']);
        }
        
        $abstract = $this->abstractModel->find($id);
        
        if (!$abstract) {
            return $this->response->setJSON(['success' => false, 'message' => 'Abstract not found']);
        }
        
        $programId = session('current_program');
        
        if ($abstract->program_id != $programId) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have access to this abstract']);
        }
        
        // Get participant details
        $participant = $this->participantModel->find($abstract->participant_id);
        $abstract->participant_name = $participant ? $participant->full_name : 'N/A';
        $abstract->institution = $participant ? $participant->institution : 'N/A';
        
        return $this->response->setJSON(['success' => true, 'data' => $abstract]);
    }
}