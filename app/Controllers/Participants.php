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
        $page = (int)($this->request->uri->getQuery(['only' => ['page']]) ?? 1);
        $limit = 10;  // Items per page
        $offset = ($page - 1) * $limit;

        $result = $this->participantModel->getCurrentProgramParticipants($limit, $offset);
        
        $data = [
            'participants' => $result['data'],
            'pager' => [
                'total' => $result['total'],
                'perPage' => $limit,
                'currentPage' => $page,
                'totalPages' => ceil($result['total'] / $limit)
            ]
        ];
        
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
