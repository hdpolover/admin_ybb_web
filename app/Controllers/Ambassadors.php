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
    }

    /**
     * Get ambassadors data for DataTables
     */
    public function getData()
    {        $programId = session('current_program');

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
            'name',
            'email',
            'institution',
            'is_active',
            'created_at',
            'ref_code',
            'referral_count',
        ];

        $orderColumn = $columns[$order['column']] ?? '#';

        // Get data from database 
        $builder = $this->ambassadorModel->select('ambassadors.*')
            ->where('ambassadors.program_id', $programId)
            ->where('ambassadors.is_deleted', 0);

        // Apply search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->orLike('institution', $search)
                ->orLike('ref_code', $search)
                ->groupEnd();
        }

        // Get total count
        $totalRecords = $builder->countAllResults(false);
        
        // Order and limit
        $result = $builder->orderBy($orderColumn, $order['dir'])
            ->limit($length, $start)
            ->get()->getResult();
            
        // Get comprehensive referral counts that include both old and new referral structures
        $referralCounts = $this->ambassadorModel->getComprehensiveReferralCounts($programId);        // Format data for DataTable
        $data = [];
        foreach ($result as $row) {
            // Get the referral count from our comprehensive count array
            $referralCount = $referralCounts[$row->id] ?? 0;

            $data[] = [
                'id' => $row->id,
                'details' => [
                    'name' => $row->name,
                    'first_letter' => strtoupper(substr($row->name, 0, 1)),
                    'email' => $row->email,
                    'institution' => $row->institution,
                    'created_at' => date('d M Y', strtotime($row->created_at)),
                    'status' => $row->is_active,
                ],
                'ref_code' => $row->ref_code,
                'referral_count' => $referralCount,
                'actions' => $this->generateActionButtons($row)
            ];
        }

        // Response for DataTables
        $response = [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    /**
     * Get HTML for status badge
     */
    private function getStatusBadge($status)
    {
        switch ($status) {
            case '1': // Active
                return '<span class="badge bg-success-subtle text-success">Active</span>';
            case '0': // Inactive
                return '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
            case '2': // Suspended
                return '<span class="badge bg-warning-subtle text-warning">Suspended</span>';
            default:
                return '<span class="badge bg-secondary-subtle text-secondary">Unknown</span>';
        }
    }

    /**
     * Generate action buttons for each ambassador row
     */
    private function generateActionButtons($ambassador)
    {
        $buttons = '<div class="d-flex gap-2">';

        // View button
        $buttons .= '<a href="' . base_url("users/ambassadors/view/{$ambassador->id}") . '" class="btn btn-sm btn-soft-primary">';
        $buttons .= '<i class="ri-eye-fill align-bottom"></i>';
        $buttons .= '</a>';

        // Edit button
        $buttons .= '<a href="' . base_url("users/ambassadors/edit/{$ambassador->id}") . '" class="btn btn-sm btn-soft-warning">';
        $buttons .= '<i class="ri-pencil-fill align-bottom"></i>';
        $buttons .= '</a>';

        // Delete button
        $buttons .= '<button type="button" class="btn btn-sm btn-soft-danger delete-ambassador" data-id="' . $ambassador->id . '">';
        $buttons .= '<i class="ri-delete-bin-2-line align-bottom"></i>';
        $buttons .= '</button>';

        $buttons .= '</div>';

        return $buttons;
    }    
    
    /**
     * View ambassador details
     */
    public function view($id)
    {
        $ambassador = $this->ambassadorModel->select('ambassadors.*')
            ->where('ambassadors.id', $id)
            ->first();

        if (!$ambassador) {
            return redirect()->to('ambassadors')->with('error', 'Ambassador not found');
        }

        $programId = session('current_program');

        // Get referrals from new structure (ambassador_participant_referrals table)
        $newReferrals = $this->ambassadorParticipantReferralModel
            ->select('ambassador_participant_referrals.*, participants.full_name, users.email, participants.id as participant_id')
            ->join('participants', 'participants.id = ambassador_participant_referrals.participant_id')
            ->join('users', 'users.id = participants.user_id', 'left')
            ->where('ambassador_participant_referrals.ambassador_id', $id)
            ->where('ambassador_participant_referrals.is_deleted', 0)
            ->findAll();
            
        // Get the ambassador's reference code
        $refCode = $ambassador->ref_code;
        
        // Get participants using the old structure (ref_code_ambassador field)
        $db = \Config\Database::connect();
        $builder = $db->table('participants');
        $builder->select('participants.*, users.email')
            ->join('users', 'users.id = participants.user_id', 'left')
            ->where('participants.ref_code_ambassador', $refCode)
            ->where('participants.program_id', $programId)
            ->where('participants.is_deleted', 0);
            
        // Exclude participants that are already in the new structure
        $existingParticipantIds = array_column($newReferrals, 'participant_id');
        if (!empty($existingParticipantIds)) {
            $builder->whereNotIn('participants.id', $existingParticipantIds);
        }
        
        $oldReferrals = $builder->get()->getResult();

        // Format old referrals to match the structure expected by the view
        $formattedOldReferrals = [];
        foreach ($oldReferrals as $referral) {
            $formattedOldReferrals[] = (object)[
                'participant_id' => $referral->id,
                'full_name' => $referral->full_name,
                'email' => $referral->email,
                'created_at' => $referral->created_at,
                'referral_type' => 'legacy', // Mark as legacy referral
            ];
        }
        
        // Combine both referral lists
        $allReferrals = array_merge($newReferrals, $formattedOldReferrals);
        
        // Calculate total counts for both types
        $newReferralCount = count($newReferrals);
        $oldReferralCount = count($formattedOldReferrals);
        $totalReferralCount = $newReferralCount + $oldReferralCount;

        // set generated link
        $encryptedQuery = url_encrypt($refCode);

        // get program
        $programModel = new \App\Models\ProgramModel();
        $program = $programModel->find($programId);

        // get program category
        $programCategoryModel = new \App\Models\ProgramCategoryModel();
        $programCategory = $programCategoryModel->find($program->program_category_id);

        $webUrl = $programCategory->web_url ?? 'https://example.com'; // Default to example.com if not set
        $webUrl = rtrim($webUrl, '/'); // Ensure no trailing slash

        // add https:// if not present
        if (!preg_match('/^https?:\/\//', $webUrl)) {
            $webUrl = 'https://' . $webUrl;
        }

        // Generate the full URL
        $generatedUrl = $webUrl . '/sign-up?q=' . urlencode($encryptedQuery);

        $data = [
            'title' => 'Ambassador Details',
            'ambassador' => $ambassador,
            'referrals' => $allReferrals,
            'referralCounts' => [
                'new' => $newReferralCount,
                'legacy' => $oldReferralCount,
                'total' => $totalReferralCount
            ],
            'generated_url' => $generatedUrl,
        ];

        return view('users/ambassadors/view', $data);
    }

    /**
     * Get ambassador data for editing
     */
    public function getAmbassadorData($id)
    {
        // Get ambassador data
        $ambassador = $this->ambassadorModel->find($id);
        
        if (!$ambassador) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ambassador not found'
            ]);
        }
        
        // Add formatted date
        $ambassador->created_at_formatted = date('d M Y', strtotime($ambassador->created_at));
        
        // Get referral count
        $programId = session('current_program');
        $referralCounts = $this->ambassadorModel->getComprehensiveReferralCounts($programId, $id);
        $ambassador->referral_count = $referralCounts[$ambassador->id] ?? 0;
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $ambassador
        ]);
    }
    
    /**
     * Update ambassador data
     */
    public function update()
    {
        $id = $this->request->getPost('id');
        
        // Check if ambassador exists
        $ambassador = $this->ambassadorModel->find($id);
        
        if (!$ambassador) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ambassador not found'
            ]);
        }
        
        // Prepare data for update
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'institution' => $this->request->getPost('institution'),
            'phone_number' => $this->request->getPost('phone_number'),
            'is_active' => $this->request->getPost('is_active'),
            'notes' => $this->request->getPost('notes'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Remove empty values
        $data = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });
        
        // Update ambassador
        $updated = $this->ambassadorModel->update($id, $data);
        
        if ($updated) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Ambassador updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update ambassador'
            ]);
        }
    }
    
    /**
     * Create new ambassador
     */
    public function create()
    {
        // Prepare data
        $programId = session('current_program');
        
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'institution' => $this->request->getPost('institution'),
            'program_id' => $programId,
            'notes' => $this->request->getPost('notes'),
            'gender' => $this->request->getPost('gender'),
            'phone_number' => $this->request->getPost('phone_number'),
            'is_active' => 1, // Active by default
            'is_deleted' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        
        // Save ambassador
        $inserted = $this->ambassadorModel->insert($data);
        
        if ($inserted) {
            // Generate ref code based on 4 characters of name and and id of ambassador
            $name = preg_replace('/[^A-Za-z0-9]/', '', $data['name']); // Remove special characters
            $name = strtoupper(substr($name, 0, 4)); // Get first 4 characters and convert to uppercase
            $name = str_pad($name, 4, 'X'); // Pad with X if less than 4 characters
            $name = substr($name, 0, 4); // Ensure it's exactly 4 characters

            $id = $this->ambassadorModel->insertID(); // Get the inserted ID
            $ref_code = $name . $id;

            // update ambassador with ref code
            $this->ambassadorModel->update($inserted, ['ref_code' => $ref_code]);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Ambassador created successfully',
                'ambassador_id' => $inserted
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create ambassador'
            ]);
        }
    }
    
    /**
     * Generate unique referral code
     */
    private function generateUniqueRefCode()
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Removed similar-looking characters
        $length = 6;
        $isUnique = false;
        $code = '';
        
        while (!$isUnique) {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
            
            // Check if code already exists
            $exists = $this->ambassadorModel->where('ref_code', $code)->countAllResults();
            
            if ($exists === 0) {
                $isUnique = true;
            }
        }
        
        return $code;
    }

    /**
     * Delete ambassador (soft delete)
     */
    public function delete($id)
    {
        try {
            // Check if ambassador exists
            $ambassador = $this->ambassadorModel->find($id);

            if (!$ambassador) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ambassador not found'
                ]);
            }

            // Soft delete by updating is_deleted field
            $updated = $this->ambassadorModel->update($id, [
                'is_deleted' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($updated) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Ambassador deleted successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete ambassador'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error deleting ambassador: ' . $e->getMessage()
            ]);
        }
    }
}
