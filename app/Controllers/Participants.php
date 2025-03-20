<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use App\Controllers\Api\ParticipantsApiController;

class Participants extends BaseController
{
    protected $participantModel;
    protected $participantsApi;

    public function __construct()
    {
        helper(['api']); // Load the API helper
        $this->participantModel = new ParticipantModel();
        $this->participantsApi = new ParticipantsApiController();
    }

    public function index()
    {
        $page = (int)($this->request->uri->getQuery(['only' => ['page']]) ?? 1);
        $limit = 10;  // Items per page
        $offset = ($page - 1) * $limit;

        // Use the API controller through helper function
        $response = handle_api_form(
            ['page' => $page, 'limit' => $limit],
            $this->participantsApi,
            'getCurrentProgramParticipants'
        );
        
        $result = parse_api_response($response);
        
        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }
        
        $data = [
            'participants' => $result['data'],
            'pager' => [
                'total' => $result['data']['total'] ?? 0,
                'perPage' => $limit,
                'currentPage' => $page,
                'totalPages' => ceil(($result['data']['total'] ?? 0) / $limit)
            ]
        ];
        
        return view('users/participants/index', $data);
    }

    public function view($id)
    {
        // Use API controller through helper function
        $response = call_api_controller($this->participantsApi, 'show', [$id]);
        $result = parse_api_response($response);
        
        if (!$result['success']) {
            return redirect()->to('/participants')->with('error', $result['message']);
        }
        
        return view('users/participants/view', ['participant' => $result['data']]);
    }

    /**
     * Create a new participant form
     */
    public function new()
    {
        return view('users/participants/create');
    }

    /**
     * Create a new participant (process the form)
     */
    public function create()
    {
        // Use API helper to handle form submission
        $response = handle_api_form(
            $_POST,
            $this->participantsApi,
            'create'
        );
        
        $result = parse_api_response($response);
        
        if ($result['success']) {
            return redirect()->to('/participants')
                ->with('success', $result['message']);
        }
        
        return redirect()->back()
            ->with('error', $result['message'])
            ->withInput();
    }

    /**
     * Edit participant form
     */
    public function edit($id)
    {
        // Get participant data using API
        $response = call_api_controller($this->participantsApi, 'show', [$id]);
        $result = parse_api_response($response);
        
        if (!$result['success']) {
            return redirect()->to('/participants')
                ->with('error', $result['message']);
        }
        
        return view('users/participants/edit', ['participant' => $result['data']]);
    }

    /**
     * Update participant (process the form)
     */
    public function update($id)
    {
        // Use API helper to handle form update
        $response = handle_api_form(
            $_POST,
            $this->participantsApi,
            'update',
            [$id]
        );
        
        $result = parse_api_response($response);
        
        if ($result['success']) {
            return redirect()->to('/participants')
                ->with('success', $result['message']);
        }
        
        return redirect()->back()
            ->with('error', $result['message'])
            ->withInput();
    }

    /**
     * Delete participant
     */
    public function delete($id)
    {
        // Use API helper to handle deletion
        $response = handle_api_form(
            [],
            $this->participantsApi,
            'delete',
            [$id]
        );
        
        $result = parse_api_response($response);
        
        if ($result['success']) {
            return redirect()->to('/participants')
                ->with('success', $result['message']);
        }
        
        return redirect()->to('/participants')
            ->with('error', $result['message']);
    }

    /**
     * Get participants for a specific program
     */
    public function byProgram($programId)
    {
        $page = (int)($this->request->uri->getQuery(['only' => ['page']]) ?? 1);
        $limit = 10;
        
        // Use API helper to get program participants
        $response = handle_api_form(
            [
                'page' => $page,
                'limit' => $limit,
                'program_id' => $programId
            ],
            $this->participantsApi,
            'getByProgram',
            [$programId]
        );
        
        $result = parse_api_response($response);
        
        if (!$result['success']) {
            return redirect()->to('/participants')
                ->with('error', $result['message']);
        }
        
        $data = [
            'participants' => $result['data'],
            'pager' => [
                'total' => $result['data']['total'] ?? 0,
                'perPage' => $limit,
                'currentPage' => $page,
                'totalPages' => ceil(($result['data']['total'] ?? 0) / $limit)
            ],
            'programId' => $programId
        ];
        
        return view('users/participants/program', $data);
    }
}
