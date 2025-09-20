<?php

namespace App\Controllers;

use App\Controllers\AdminBaseController;
use App\Models\AdminModel;
use App\Models\AdminRoleModel;
use App\Models\ProgramModel;
use App\Services\MenuService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class AdminManagement extends AdminBaseController
{
    protected $adminModel;
    protected $adminRoleModel;
    protected $programModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->adminModel = new AdminModel();
        $this->adminRoleModel = new AdminRoleModel();
        $this->programModel = new ProgramModel();
    }

    /**
     * Display admin management dashboard
     */
    public function index()
    {
        // Check permission
        if (!$this->adminModel->hasPermission($this->currentUser, 'manage_admins')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. You do not have permission to manage administrators.');
        }

        $data = $this->prepareViewData([
            'pageTitle' => 'Admin Management',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => base_url('dashboard')],
                ['label' => 'Admin Management', 'url' => '', 'active' => true]
            ]
        ]);

        // Get statistics
        $data['statistics'] = $this->adminModel->getAdminStatistics();
        
        // Get all programs for dropdown
        $data['programs'] = $this->programModel->findAll();
        
        // Get current user's manageable roles
        $data['manageableRoles'] = $this->getManageableRoles();

        return $this->renderView('admin/admin_management', $data);
    }

    /**
     * Get admins data for DataTable
     */
    public function getAdminsData()
    {
        if (!$this->adminModel->hasPermission($this->currentUser, 'manage_admins')) {
            return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
        }

        $filters = [
            'search' => $this->request->getGet('search')['value'] ?? '',
            'role' => $this->request->getGet('role'),
            'program_id' => $this->request->getGet('program_id'),
            'is_active' => $this->request->getGet('is_active')
        ];

        $admins = $this->adminModel->searchAdmins($filters);
        $manageableRoles = $this->getManageableRoles();

        $data = [];
        foreach ($admins as $admin) {
            // Only show admins with roles current user can manage
            if (!in_array($admin->role, $manageableRoles)) {
                continue;
            }

            // Format program names
            $programDisplay = '<span class="text-muted">No Programs</span>';
            if (!empty($admin->program_names)) {
                $programs = explode(', ', $admin->program_names);
                if (count($programs) > 2) {
                    $programDisplay = esc(implode(', ', array_slice($programs, 0, 2))) . 
                                    ' <span class="badge bg-secondary">+' . (count($programs) - 2) . ' more</span>';
                } else {
                    $programDisplay = esc($admin->program_names);
                }
            }

            $data[] = [
                'id' => $admin->id,
                'name' => esc($admin->name),
                'email' => esc($admin->email),
                'role' => AdminModel::getRoleDisplayName($admin->role),
                'role_raw' => $admin->role,
                'program_name' => $programDisplay,
                'is_active' => $admin->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>',
                'last_login' => $admin->last_login ? date('M j, Y g:i A', strtotime($admin->last_login)) : '<span class="text-muted">Never</span>',
                'created_at' => date('M j, Y', strtotime($admin->created_at)),
                'can_edit' => $this->canManageRole($this->currentUser->role, $admin->role),
                'can_delete' => $this->canManageRole($this->currentUser->role, $admin->role) && $admin->id != $this->currentUser->id
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($this->request->getGet('draw')),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data
        ]);
    }

    /**
     * Get roles that current admin can manage with details
     */
    private function getManageableRolesWithDetails(): array
    {
        // Get all active roles from database
        $allRoles = $this->adminRoleModel->getActiveRoles();
        $manageableRoles = [];

        foreach ($allRoles as $role) {
            // Check if current user can manage this role based on access level
            if ($this->canManageRole($this->currentUser->role, $role->name)) {
                $manageableRoles[] = [
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description
                ];
            }
        }

        return $manageableRoles;
    }

    /**
     * Get roles that current admin can manage
     */
    private function getManageableRoles(): array
    {
        // Get all active roles from database
        $allRoles = $this->adminRoleModel->getActiveRoles();
        $manageableRoles = [];

        foreach ($allRoles as $role) {
            // Check if current user can manage this role based on access level
            if ($this->canManageRole($this->currentUser->role, $role->name)) {
                $manageableRoles[] = $role->name;
            }
        }

        return $manageableRoles;
    }

    /**
     * Check if current user can manage a specific role
     */
    private function canManageRole(string $currentUserRole, string $targetRole): bool
    {
        // Super admin can manage all roles except themselves
        if ($currentUserRole === 'super_admin') {
            return true;
        }

        // Get role access levels from database
        $currentRole = $this->adminRoleModel->where('name', $currentUserRole)->first();
        $targetRoleObj = $this->adminRoleModel->where('name', $targetRole)->first();

        if (!$currentRole || !$targetRoleObj) {
            return false;
        }

        // Users can only manage roles with lower access levels
        return $currentRole->access_level > $targetRoleObj->access_level;
    }

    /**
     * Get current user's menu based on role
     */
    public function getUserMenu()
    {
        $menu = MenuService::getMenuWithActiveStates('admin', $this->currentUser->role, uri_string());
        
        return $this->response->setJSON([
            'success' => true,
            'menu' => $menu
        ]);
    }

    /**
     * Create new admin
     */
    public function create()
    {
        if (!$this->adminModel->hasPermission($this->currentUser, 'manage_admins')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied'])->setStatusCode(403);
        }

        if ($this->request->getMethod() === 'post') {
            return $this->store();
        }

        $data = [
            'programs' => $this->programModel->where('is_active', 1)->findAll(),
            'roles' => $this->getManageableRolesWithDetails()
        ];

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Store new admin
     */
    public function store()
    {
        if (!$this->adminModel->hasPermission($this->currentUser, 'manage_admins')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied'])->setStatusCode(403);
        }

        $validation = \Config\Services::validation();
        $manageableRoles = $this->getManageableRoles();
        log_message('debug', 'Manageable roles for validation: ' . json_encode($manageableRoles));
        
        $validation->setRules([
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|is_unique[admins.email]',
            'password' => 'required|min_length[8]',
            'role' => 'required|in_list[' . implode(',', $manageableRoles) . ']',
            'program_ids' => 'permit_empty|is_array',
            'is_active' => 'permit_empty|in_list[0,1]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => $this->request->getPost('role'),
            'is_active' => $this->request->getPost('is_active') ?? 1,
            'created_by' => $this->currentUser->id
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            log_message('debug', 'Attempting to create admin with data: ' . json_encode($data));
            
            $adminId = $this->adminModel->insert($data);
            
            log_message('debug', 'AdminModel::insert returned: ' . var_export($adminId, true));
            
            if (!$adminId) {
                // Get more specific error information
                $errors = $this->adminModel->errors();
                log_message('error', 'AdminModel insert failed. Errors: ' . json_encode($errors));
                throw new \Exception('Failed to create admin. Validation errors: ' . json_encode($errors));
            }

            // Assign to programs if specified
            $programIds = $this->request->getPost('program_ids');
            log_message('debug', 'Program IDs received: ' . json_encode($programIds));
            
            if (!empty($programIds)) {
                log_message('debug', 'Calling assignToPrograms for admin ID: ' . $adminId);
                $assignResult = $this->adminModel->assignToPrograms($adminId, $programIds, $this->currentUser->id);
                log_message('debug', 'assignToPrograms result: ' . ($assignResult ? 'success' : 'failed'));
            } else {
                log_message('debug', 'No program IDs to assign - program_ids is empty');
            }

            $db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Admin created successfully',
                'admin_id' => $adminId
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Admin creation failed: ' . $e->getMessage());
            log_message('error', 'Admin creation stack trace: ' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create admin: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * View admin details
     */
    public function view($id)
    {
        if (!$this->adminModel->hasPermission($this->currentUser, 'manage_admins')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied'])->setStatusCode(403);
        }

        $admin = $this->adminModel->find($id);
        if (!$admin || $admin->is_deleted) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admin not found'])->setStatusCode(404);
        }

        // Check if user can manage this admin's role
        if (!$this->canManageRole($this->currentUser->role, $admin->role)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied'])->setStatusCode(403);
        }

        // Get admin's programs
        $admin->programs = $this->adminModel->getAdminPrograms($id);

        return $this->response->setJSON([
            'success' => true,
            'admin' => $admin
        ]);
    }

    /**
     * Edit admin
     */
    public function edit($id)
    {
        if (!$this->adminModel->hasPermission($this->currentUser, 'manage_admins')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied'])->setStatusCode(403);
        }

        if ($this->request->getMethod() === 'post') {
            return $this->update($id);
        }

        $admin = $this->adminModel->find($id);
        if (!$admin || $admin->is_deleted) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admin not found'])->setStatusCode(404);
        }

        // Check if user can manage this admin's role
        if (!$this->canManageRole($this->currentUser->role, $admin->role)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied'])->setStatusCode(403);
        }

        // Get admin's programs
        $admin->programs = $this->adminModel->getAdminPrograms($id);

        $data = [
            'admin' => $admin,
            'programs' => $this->programModel->where('is_active', 1)->findAll(),
            'roles' => $this->getManageableRolesWithDetails()
        ];

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Update admin
     */
    public function update($id)
    {
        if (!$this->adminModel->hasPermission($this->currentUser, 'manage_admins')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied'])->setStatusCode(403);
        }

        $admin = $this->adminModel->find($id);
        if (!$admin || $admin->is_deleted) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admin not found'])->setStatusCode(404);
        }

        // Check if user can manage this admin's role
        if (!$this->canManageRole($this->currentUser->role, $admin->role)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied'])->setStatusCode(403);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => "required|valid_email|is_unique[admins.email,id,{$id}]",
            'password' => 'permit_empty|min_length[8]',
            'role' => 'required|in_list[' . implode(',', $this->getManageableRoles()) . ']',
            'program_ids' => 'permit_empty|is_array',
            'is_active' => 'permit_empty|in_list[0,1]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role' => $this->request->getPost('role'),
            'is_active' => $this->request->getPost('is_active') ?? $admin->is_active,
            'updated_by' => $this->currentUser->id
        ];

        // Update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $this->adminModel->update($id, $data);

            // Update program assignments
            $programIds = $this->request->getPost('program_ids') ?? [];
            log_message('debug', 'Update - Program IDs received: ' . json_encode($programIds));
            log_message('debug', 'Update - Calling assignToPrograms for admin ID: ' . $id);
            $assignResult = $this->adminModel->assignToPrograms($id, $programIds, $this->currentUser->id);
            log_message('debug', 'Update - assignToPrograms result: ' . ($assignResult ? 'success' : 'failed'));

            $db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Admin updated successfully'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Admin update failed: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update admin: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete admin
     */
    public function delete($id)
    {
        if (!$this->adminModel->hasPermission($this->currentUser, 'manage_admins')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied'])->setStatusCode(403);
        }

        $admin = $this->adminModel->find($id);
        if (!$admin || $admin->is_deleted) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admin not found'])->setStatusCode(404);
        }

        // Prevent self-deletion
        if ($admin->id == $this->currentUser->id) {
            return $this->response->setJSON(['success' => false, 'message' => 'You cannot delete your own account'])->setStatusCode(400);
        }

        // Check if user can manage this admin's role
        if (!$this->canManageRole($this->currentUser->role, $admin->role)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied'])->setStatusCode(403);
        }

        try {
            // Soft delete
            $this->adminModel->update($id, [
                'is_deleted' => 1,
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => $this->currentUser->id
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Admin deleted successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Admin deletion failed: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete admin: ' . $e->getMessage()
            ]);
        }
    }
}