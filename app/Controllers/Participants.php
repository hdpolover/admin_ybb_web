<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use CodeIgniter\Controller;

class Participants extends Controller
{
    protected $participantModel;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
    }

    public function index()
    {
        $data['participants'] = $this->participantModel->getAllParticipants();
        return view('users/participants/index', $data);
    }

    public function view($id)
    {
        $data['participant'] = $this->participantModel->getParticipant($id);
        if (empty($data['participant'])) {
            return redirect()->to('/participants');
        }
        
        return view('users/participants/view', $data);
    }
}
