<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\MenuItemModel;
use App\Services\DynamicMenuService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class RoleManagement extends AdminBaseController
{
    protected $roleModel;
    protected $permissionModel;
    protected $menuItemModel;
    protected $menuService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
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

    public function createRole()
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
        }

        if ($this->request->getMethod() === 'GET') {
            $data = $this->prepareViewData([
                'pageTitle' => 'Create New Role',
                'permissions' => $this->permissionModel->getPermissionsByCategory()
            ]);
            return $this->renderView('admin/role_management/create', $data);
        }

        // Handle POST request
        $data = [
            'name' => $this->request->getPost('name'),
            'display_name' => $this->request->getPost('display_name'),
            'description' => $this->request->getPost('description'),
            'access_level' => $this->request->getPost('access_level'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        if ($this->roleModel->insert($data)) {
            $roleId = $this->roleModel->getInsertID();
            
            // Assign permissions
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

    public function editRole($id)
    {
        $this->requireAuth();
        
        if (!$this->menuService->canAccessRoleManagement($this->currentUser)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $role = $this->roleModel->getRoleWithPermissions($id);
        if (!$role) {
            return redirect()->to('/settings/roles')->with('error', 'Role not found');
        }

        if ($this->request->getMethod() === 'GET') {
            $data = $this->prepareViewData([
                'pageTitle' => 'Edit Role: ' . $role->display_name,
                'role' => $role,
                'permissions' => $this->permissionModel->getPermissionsByCategory(),
                'rolePermissions' => array_column($role->permissions, 'id')
            ]);
            return $this->renderView('admin/role_management/edit', $data);
        }

        // Handle POST request
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