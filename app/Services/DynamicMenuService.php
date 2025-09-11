<?php

namespace App\Services;

use App\Models\AdminRoleModel;
use App\Models\PermissionModel;
use App\Models\MenuItemModel;

class DynamicMenuService
{
    protected $roleModel;
    protected $permissionModel;
    protected $menuItemModel;
    protected $cache;

    public function __construct()
    {
        $this->roleModel = new AdminRoleModel();
        $this->permissionModel = new PermissionModel();
        $this->menuItemModel = new MenuItemModel();
        $this->cache = \Config\Services::cache();
    }

    /**
     * Get menu items for user based on their role
     */
    public function getMenuForUser($user): array
    {
        if (!$user || !isset($user->role)) {
            return [];
        }

        // Get role ID from role name
        $role = $this->roleModel->where('name', $user->role)->first();
        if (!$role) {
            return [];
        }

        $cacheKey = "menu_user_role_{$role->id}";
        $menu = $this->cache->get($cacheKey);

        if ($menu === null) {
            $menu = $this->menuItemModel->getMenuForUser($role->id);
            $this->cache->save($cacheKey, $menu, 1800); // Cache for 30 minutes
        }

        return $menu;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($user, string $permission): bool
    {
        if (!$user || !isset($user->role)) {
            return false;
        }

        // Super admin has all permissions
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Get role ID from role name
        $role = $this->roleModel->where('name', $user->role)->first();
        if (!$role) {
            return false;
        }

        $cacheKey = "user_permission_{$role->id}_{$permission}";
        $hasPermission = $this->cache->get($cacheKey);

        if ($hasPermission === null) {
            $hasPermission = $this->roleModel->hasPermission($role->id, $permission);
            $this->cache->save($cacheKey, $hasPermission, 1800); // Cache for 30 minutes
        }

        return $hasPermission;
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin($user): bool
    {
        if (!$user) return false;

        // Check by role name or access level
        return (isset($user->role) && $user->role === 'super_admin') || 
               (isset($user->access_level) && $user->access_level >= 10);
    }

    /**
     * Get user role permissions
     */
    public function getUserPermissions($user): array
    {
        if (!$user || !isset($user->role_id)) {
            return [];
        }

        $cacheKey = "user_permissions_{$user->role_id}";
        $permissions = $this->cache->get($cacheKey);

        if ($permissions === null) {
            $permissions = $this->permissionModel->getPermissionsForRole($user->role_id);
            $this->cache->save($cacheKey, $permissions, 1800);
        }

        return $permissions;
    }

    /**
     * Check multiple permissions at once
     */
    public function hasAnyPermission($user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($user, $permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all permissions
     */
    public function hasAllPermissions($user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($user, $permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get role hierarchy for access level comparison
     */
    public function getRoleHierarchy(): array
    {
        $cacheKey = "role_hierarchy";
        $hierarchy = $this->cache->get($cacheKey);

        if ($hierarchy === null) {
            $hierarchy = $this->roleModel->getRoleHierarchy();
            $this->cache->save($cacheKey, $hierarchy, 3600); // Cache for 1 hour
        }

        return $hierarchy;
    }

    /**
     * Check if user can access admin management
     */
    public function canAccessAdminManagement($user): bool
    {
        return $this->hasPermission($user, 'manage_admins');
    }

    /**
     * Check if user can access role management
     */
    public function canAccessRoleManagement($user): bool
    {
        return $this->hasPermission($user, 'manage_roles');
    }

    /**
     * Check if user can access system settings
     */
    public function canAccessSystemSettings($user): bool
    {
        return $this->hasPermission($user, 'system_settings');
    }

    /**
     * Check if user can view participants
     */
    public function canViewParticipants($user): bool
    {
        return $this->hasPermission($user, 'view_participants');
    }

    /**
     * Check if user can manage participants
     */
    public function canManageParticipants($user): bool
    {
        return $this->hasPermission($user, 'manage_participants');
    }

    /**
     * Check if user can view essays
     */
    public function canViewEssays($user): bool
    {
        return $this->hasPermission($user, 'view_essays');
    }

    /**
     * Check if user can manage essays
     */
    public function canManageEssays($user): bool
    {
        return $this->hasPermission($user, 'manage_essays');
    }

    /**
     * Check if user can view scores
     */
    public function canViewScores($user): bool
    {
        return $this->hasPermission($user, 'view_scores');
    }

    /**
     * Check if user can manage scores
     */
    public function canManageScores($user): bool
    {
        return $this->hasPermission($user, 'manage_scores');
    }

    /**
     * Check if user can view ambassadors
     */
    public function canViewAmbassadors($user): bool
    {
        return $this->hasPermission($user, 'view_ambassadors');
    }

    /**
     * Check if user can manage ambassadors
     */
    public function canManageAmbassadors($user): bool
    {
        return $this->hasPermission($user, 'manage_ambassadors');
    }

    /**
     * Check if user can view news
     */
    public function canViewNews($user): bool
    {
        return $this->hasPermission($user, 'view_news');
    }

    /**
     * Check if user can manage news
     */
    public function canManageNews($user): bool
    {
        return $this->hasPermission($user, 'manage_news');
    }

    /**
     * Check if user can view analytics
     */
    public function canViewAnalytics($user): bool
    {
        return $this->hasPermission($user, 'view_analytics');
    }

    /**
     * Check if user can export data
     */
    public function canExportData($user): bool
    {
        return $this->hasPermission($user, 'export_data');
    }

    /**
     * Clear user permission cache
     */
    public function clearUserCache($userId): void
    {
        $patterns = [
            "menu_user_role_{$userId}",
            "user_permissions_{$userId}",
        ];

        foreach ($patterns as $pattern) {
            $this->cache->delete($pattern);
        }

        // Clear permission-specific caches
        $permissions = $this->permissionModel->getActivePermissions();
        foreach ($permissions as $permission) {
            $this->cache->delete("user_permission_{$userId}_{$permission->name}");
        }
    }

    /**
     * Clear all role-related caches
     */
    public function clearAllRoleCaches(): void
    {
        $this->cache->delete('role_hierarchy');
        
        // Clear all menu and permission caches
        $roles = $this->roleModel->getActiveRoles();
        foreach ($roles as $role) {
            $this->clearUserCache($role->id);
        }
    }

    /**
     * Get breadcrumb for current page
     */
    public function getBreadcrumb(string $currentUrl): array
    {
        return $this->menuItemModel->getBreadcrumb($currentUrl);
    }

    /**
     * Helper method to get permission-based menu visibility for legacy code
     */
    public function getMenuVisibility($user): array
    {
        return [
            'canViewDashboard' => $this->hasPermission($user, 'view_dashboard'),
            'canViewParticipants' => $this->canViewParticipants($user),
            'canManageParticipants' => $this->canManageParticipants($user),
            'canViewEssays' => $this->canViewEssays($user),
            'canManageEssays' => $this->canManageEssays($user),
            'canViewScoring' => $this->canViewScores($user),
            'canManageScoring' => $this->canManageScores($user),
            'canViewAmbassadors' => $this->canViewAmbassadors($user),
            'canManageAmbassadors' => $this->canManageAmbassadors($user),
            'canViewNews' => $this->canViewNews($user),
            'canManageNews' => $this->canManageNews($user),
            'canViewAnalytics' => $this->canViewAnalytics($user),
            'canExportData' => $this->canExportData($user),
            'canViewSettings' => $this->canAccessSystemSettings($user),
            'canManageAdmins' => $this->canAccessAdminManagement($user),
            'canManageRoles' => $this->canAccessRoleManagement($user),
            
            // Legacy compatibility
            'canViewUsers' => $this->canViewParticipants($user),
            'canViewSubmissions' => $this->canViewEssays($user),
            'canViewDocuments' => $this->hasPermission($user, 'view_documents'),
            'canViewAnnouncements' => $this->hasPermission($user, 'view_announcements'),
            'canViewMasterData' => $this->canAccessSystemSettings($user),
            'canViewPayments' => $this->hasPermission($user, 'view_payments'),
        ];
    }
}