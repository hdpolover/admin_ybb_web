<?php

namespace App\Controllers;

use App\Models\AmbassadorModel;
use App\Models\ProgramModel;
use App\Models\AmbassadorParticipantReferralModel;

class Ambassadors extends BaseController
{
    protected $ambassadorModel;
    protected $programModel;
    protected $ambassadorParticipantReferralModel;

    public function __construct()
    {   
        // Load the required models
        $this->ambassadorModel = new AmbassadorModel();
        $this->programModel = new ProgramModel();
        $this->ambassadorParticipantReferralModel = new AmbassadorParticipantReferralModel();
    }
    
    public function index()
    {
        $programId = session('current_program');
        $program = $this->programModel->find($programId);

        // Get ambassador statistics
        $stats = $this->ambassadorModel->getAmbassadorStats($programId);

        $data = [
            'title' => 'Ambassadors',
            'program' => $program,
            'stats' => $stats,
        ];

        return view('users/ambassadors/index', $data);
    }    /**
     * Get ambassadors data for DataTables
     */
    public function getData()
    {
        $programId = session('current_program');
        
        // Process DataTables server-side request
        $request = $this->request->getGet();

        $draw = isset($request['draw']) ? intval($request['draw']) : 1;
        $start = isset($request['start']) ? intval($request['start']) : 0;
        $length = isset($request['length']) ? intval($request['length']) : 10;
        $search = isset($request['search']['value']) ? $request['search']['value'] : '';
        $order = isset($request['order'][0]) ? [
            'column' => intval($request['order'][0]['column']),
            'dir' => $request['order'][0]['dir']
        ] : ['column' => 0, 'dir' => 'desc'];

        // Column names
        $columns = [
            'created_at',
            'full_name',
            'email',
            'phone',
            'ref_code',
            'status'
        ];

        $orderColumn = $columns[$order['column']] ?? 'created_at';

        // Get data from database
        $builder = $this->ambassadorModel->select('
                ambassadors.*,
                users.email,
                users.phone
            ')
            ->join('users', 'users.id = ambassadors.user_id', 'left')
            ->where('ambassadors.program_id', $programId);
        
        // Apply search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('ambassadors.full_name', $search)
                ->orLike('users.email', $search)
                ->orLike('users.phone', $search)
                ->orLike('ambassadors.ref_code', $search)
                ->groupEnd();
        }

        // Apply filters
        $status = $this->request->getGet('status');
        if ($status !== '' && $status !== null) {
            $builder->where('ambassadors.status', $status);
        }

        // Get total count
        $totalRecords = $builder->countAllResults(false);

        // Order and limit
        $result = $builder->orderBy($orderColumn, $order['dir'])
            ->limit($length, $start)
            ->get()->getResult();
        
        // Format data for DataTable
        $data = [];
        foreach ($result as $row) {
            // Format status badge
            $statusBadge = $this->getStatusBadge($row->status);
            
            // Count referrals using ambassador_participant_referrals table
            $referralCount = $this->ambassadorParticipantReferralModel->where('ambassador_id', $row->id)
                ->where('program_id', $programId)
                ->countAllResults();
            
            $data[] = [
                'id' => $row->id,
                'created_date' => format_date($row->created_at, 'M j, Y'),
                'ambassador' => [
                    'name' => $row->full_name,
                    'email' => $row->email,
                    'phone' => $row->phone
                ],
                'ref_code' => $row->ref_code,
                'referral_count' => $referralCount,
                'status' => $statusBadge,
                'actions' => '<a href="' . base_url('ambassadors/view/' . $row->id) . '" class="btn btn-sm btn-primary">View</a>'
            ];
        }

        // Response for DataTables
        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    /**
     * Helper method to generate status badge HTML
     */
    private function getStatusBadge($status)
    {
        $badges = [
            0 => '<span class="badge bg-danger">Inactive</span>',
            1 => '<span class="badge bg-success">Active</span>',
            2 => '<span class="badge bg-warning">Suspended</span>'
        ];

        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }
      /**
     * View ambassador details
     */
    public function view($id)
    {
        $ambassador = $this->ambassadorModel->select('ambassadors.*, users.email, users.phone')
            ->join('users', 'users.id = ambassadors.user_id', 'left')
            ->where('ambassadors.id', $id)
            ->first();
        
        if (!$ambassador) {
            return redirect()->to('ambassadors')->with('error', 'Ambassador not found');
        }
        
        $programId = session('current_program');
        
        // Get referrals from ambassador_participant_referrals table
        $referrals = $this->ambassadorParticipantReferralModel
            ->select('ambassador_participant_referrals.*, participants.full_name, users.email, participants.id as participant_id')
            ->join('participants', 'participants.id = ambassador_participant_referrals.participant_id')
            ->join('users', 'users.id = participants.user_id', 'left')
            ->where('ambassador_participant_referrals.ambassador_id', $id)
            ->where('ambassador_participant_referrals.program_id', $programId)
            ->findAll();
        
        $data = [
            'title' => 'Ambassador Details',
            'ambassador' => $ambassador,
            'referrals' => $referrals
        ];
        
        return view('users/ambassadors/view', $data);
    }
}