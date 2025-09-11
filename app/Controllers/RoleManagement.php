<?php

namespace App\Controllers;

use App\Models\AdminRoleModel;
use App\Models\PermissionModel;
use App\Models\AdminRolePermissionModel;
use App\Models\MenuItemModel;
use App\Services\DynamicMenuService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class RoleManagement extends AdminBaseController
{
    protected $roleModel;
    protected $permissionModel;
    protected $rolePermissionModel;
    protected $menuItemModel;
    protected $menuService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->roleModel = new AdminRoleModel();
        $this->permissionModel = new PermissionModel();
        $this->rolePermissionModel = new AdminRolePermissionModel();
        $this->menuItemModel = new MenuItemModel();
        $this->menuService = new DynamicMenuService();
    }

    public function index()
    {
        $this->requireAuth();
        
        // Check permission
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Insufficient permissions.');
        }

        $data = $this->prepareViewData([
            'pageTitle' => 'Role & Permission Management',
            'roles' => $this->roleModel->getRolesWithPermissionCounts(),
            'permissions' => $this->permissionModel->getPermissionsByCategory(),
            'permissionStats' => $this->permissionModel->getPermissionStats()
        ]);

        return $this->renderView('admin/role_management/index', $data);
    }

    public function getRoles()
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
        }

        $roles = $this->roleModel->getRolesWithPermissionCounts();
        return $this->response->setJSON(['data' => $roles]);
    }

    /**
     * Show the create role form (GET /roles/create)
     */
    public function showCreateForm()
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Insufficient permissions.');
        }

        $data = $this->prepareViewData([
            'pageTitle' => 'Create New Role',
            'permissions' => $this->permissionModel->getPermissionsByCategory()
        ]);
        
        return $this->renderView('admin/role_management/create', $data);
    }

    /**
     * Process the create role form submission (POST /roles/create)
     */
    public function storeRole()
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
        }

        // Get form data
        $data = [
            'name' => $this->request->getPost('name'),
            'display_name' => $this->request->getPost('display_name'),
            'description' => $this->request->getPost('description'),
            'access_level' => $this->request->getPost('access_level'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        // Validate required fields
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = 'Role name is required';
        }
        if (empty($data['display_name'])) {
            $errors['display_name'] = 'Display name is required';
        }
        if (empty($data['access_level'])) {
            $errors['access_level'] = 'Access level is required';
        }

        if (!empty($errors)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ])->setStatusCode(400);
        }

        // Attempt to create the role
        if ($this->roleModel->insert($data)) {
            $roleId = $this->roleModel->getInsertID();
            
            // Assign permissions if any were selected
            $permissions = $this->request->getPost('permissions') ?? [];
            if (!empty($permissions)) {
                $this->roleModel->assignPermissions($roleId, $permissions, $this->currentUser->id);
            }

            // Clear caches
            $this->menuService->clearAllRoleCaches();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Role created successfully',
                'role_id' => $roleId
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create role',
                'errors' => $this->roleModel->errors()
            ])->setStatusCode(400);
        }
    }

    /**
     * Show the edit role form (GET /roles/edit/{id})
     */
    public function showEditForm($id)
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $role = $this->roleModel->getRoleWithPermissions($id);
        if (!$role) {
            return redirect()->to('/roles')->with('error', 'Role not found');
        }

        $data = $this->prepareViewData([
            'pageTitle' => 'Edit Role: ' . $role->display_name,
            'role' => $role,
            'permissions' => $this->permissionModel->getPermissionsByCategory(),
            'rolePermissions' => array_column($role->permissions, 'id')
        ]);
        
        return $this->renderView('admin/role_management/edit', $data);
    }

    /**
     * View role details (GET /roles/view/{id})
     */
    public function view($id)
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied'])->setStatusCode(403);
        }

        try {
            $role = $this->roleModel->getRoleWithPermissions($id);
            if (!$role) {
                return $this->response->setJSON(['success' => false, 'message' => 'Role not found'])->setStatusCode(404);
            }

            // Get additional role statistics
            $adminModel = new \App\Models\AdminModel();
            $adminsWithRole = $adminModel->where('role', $role->name)->where('is_deleted', 0)->findAll();
            
            $roleDetails = [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'access_level' => $role->access_level,
                'is_active' => $role->is_active,
                'permissions' => $role->permissions,
                'permission_count' => count($role->permissions),
                'admins' => $adminsWithRole,
                'admin_count' => count($adminsWithRole),
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => $roleDetails
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Role view error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'An error occurred while loading role details'
            ])->setStatusCode(500);
        }
    }

    /**
     * Process the edit role form submission (POST /roles/edit/{id})
     */
    public function updateRole($id)
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Role not found'
            ])->setStatusCode(404);
        }

        // Get form data
        $updateData = [
            'display_name' => $this->request->getPost('display_name'),
            'description' => $this->request->getPost('description'),
            'access_level' => $this->request->getPost('access_level'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        if ($this->roleModel->update($id, $updateData)) {
            // Update permissions
            $permissions = $this->request->getPost('permissions') ?? [];
            $this->roleModel->assignPermissions($id, $permissions, $this->currentUser->id);

            // Clear caches
            $this->menuService->clearAllRoleCaches();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Role updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update role',
                'errors' => $this->roleModel->errors()
            ])->setStatusCode(400);
        }
    }

    public function deleteRole($id)
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Role not found'
            ])->setStatusCode(404);
        }

        // Check if role is in use
        $db = \Config\Database::connect();
        $adminCount = $db->query("SELECT COUNT(*) as count FROM admins WHERE role_id = ? AND is_active = 1", [$id])->getRow()->count;
        
        if ($adminCount > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "Cannot delete role. It is currently assigned to {$adminCount} admin(s)."
            ])->setStatusCode(400);
        }

        if ($this->roleModel->delete($id)) {
            // Clear caches
            $this->menuService->clearAllRoleCaches();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete role'
            ])->setStatusCode(500);
        }
    }

    public function permissions()
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = $this->prepareViewData([
            'pageTitle' => 'Permission Management',
            'permissions' => $this->permissionModel->getPermissionsByCategory(),
            'stats' => $this->permissionModel->getPermissionStats()
        ]);

        return $this->renderView('admin/role_management/permissions', $data);
    }

    public function createPermission()
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'display_name' => $this->request->getPost('display_name'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        if ($this->permissionModel->insert($data)) {
            // Clear caches
            $this->menuService->clearAllRoleCaches();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Permission created successfully',
                'permission_id' => $this->permissionModel->getInsertID()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create permission',
                'errors' => $this->permissionModel->errors()
            ])->setStatusCode(400);
        }
    }

    public function menuItems()
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = $this->prepareViewData([
            'pageTitle' => 'Menu Management',
            'menuItems' => $this->menuItemModel->getMenuItemsWithHierarchy(),
            'permissions' => $this->permissionModel->getActivePermissions()
        ]);

        return $this->renderView('admin/role_management/menu_items', $data);
    }

    public function createMenuItem()
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'label' => $this->request->getPost('label'),
            'icon' => $this->request->getPost('icon'),
            'url' => $this->request->getPost('url'),
            'route_name' => $this->request->getPost('route_name'),
            'parent_id' => $this->request->getPost('parent_id') ?: null,
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'required_permission' => $this->request->getPost('required_permission') ?: null,
            'badge_text' => $this->request->getPost('badge_text') ?: null,
            'badge_color' => $this->request->getPost('badge_color') ?: null,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        if ($this->menuItemModel->insert($data)) {
            // Clear caches
            $this->menuService->clearAllRoleCaches();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Menu item created successfully',
                'menu_id' => $this->menuItemModel->getInsertID()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create menu item',
                'errors' => $this->menuItemModel->errors()
            ])->setStatusCode(400);
        }
    }

    public function updateMenuOrder()
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
        }

        $orders = $this->request->getJSON(true);
        
        if ($this->menuItemModel->updateSortOrder($orders)) {
            // Clear caches
            $this->menuService->clearAllRoleCaches();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Menu order updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update menu order'
            ])->setStatusCode(500);
        }
    }

    public function testPermissions()
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = $this->prepareViewData([
            'pageTitle' => 'Test User Permissions',
            'roles' => $this->roleModel->getActiveRoles(),
            'currentUserPermissions' => $this->menuService->getUserPermissions($this->currentUser),
            'menuVisibility' => $this->menuService->getMenuVisibility($this->currentUser)
        ]);

        return $this->renderView('admin/role_management/test', $data);
    }
}