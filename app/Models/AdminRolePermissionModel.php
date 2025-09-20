<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminRolePermissionModel extends Model
{
    protected $table = 'admin_role_permissions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'role_id', 'permission_id', 'granted_at', 'granted_by'
    ];

    protected $useTimestamps = false; // We handle granted_at manually
    protected $dateFormat = 'datetime';

    // Validation rules
    protected $validationRules = [
        'role_id' => 'required|integer|greater_than[0]',
        'permission_id' => 'required|integer|greater_than[0]'
    ];

    /**
     * Get all permissions for a specific role
     */
    public function getPermissionsForRole(int $roleId): array
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                p.*,
                rp.granted_at,
                rp.granted_by,
                granted_admin.name as granted_by_name
            FROM permissions p
            INNER JOIN admin_role_permissions rp ON rp.permission_id = p.id
            LEFT JOIN admins granted_admin ON granted_admin.id = rp.granted_by
            WHERE rp.role_id = ? AND p.is_active = 1
            ORDER BY p.category, p.display_name
        ", [$roleId])->getResult();
    }

    /**
     * Get all roles that have a specific permission
     */
    public function getRolesWithPermission(int $permissionId): array
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                r.*,
                rp.granted_at,
                rp.granted_by,
                granted_admin.name as granted_by_name
            FROM admin_roles r
            INNER JOIN admin_role_permissions rp ON rp.role_id = r.id
            LEFT JOIN admins granted_admin ON granted_admin.id = rp.granted_by
            WHERE rp.permission_id = ? AND r.is_active = 1
            ORDER BY r.access_level DESC
        ", [$permissionId])->getResult();
    }

    /**
     * Assign a single permission to a role
     */
    public function assignPermissionToRole(int $roleId, int $permissionId, ?int $grantedBy = null): bool
    {
        // Check if already exists
        $existing = $this->where(['role_id' => $roleId, 'permission_id' => $permissionId])->first();
        if ($existing) {
            return true; // Already assigned
        }

        $data = [
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'granted_at' => date('Y-m-d H:i:s'),
            'granted_by' => $grantedBy
        ];

        return $this->insert($data) !== false;
    }

    /**
     * Remove a single permission from a role
     */
    public function removePermissionFromRole(int $roleId, int $permissionId): bool
    {
        return $this->where(['role_id' => $roleId, 'permission_id' => $permissionId])->delete();
    }

    /**
     * Assign multiple permissions to a role (replaces existing)
     */
    public function assignPermissionsToRole(int $roleId, array $permissionIds, ?int $grantedBy = null): bool
    {
        $db = \Config\Database::connect();
        
        try {
            $db->transStart();
            
            // Remove existing permissions for this role
            $this->where('role_id', $roleId)->delete();
            
            // Add new permissions
            if (!empty($permissionIds)) {
                $insertData = [];
                foreach ($permissionIds as $permissionId) {
                    $insertData[] = [
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'granted_at' => date('Y-m-d H:i:s'),
                        'granted_by' => $grantedBy
                    ];
                }
                
                $this->insertBatch($insertData);
            }
            
            $db->transComplete();
            return $db->transStatus();
            
        } catch (\Exception $e) {
            $db->transRollback();
            return false;
        }
    }

    /**
     * Remove all permissions from a role
     */
    public function removeAllPermissionsFromRole(int $roleId): bool
    {
        return $this->where('role_id', $roleId)->delete();
    }

    /**
     * Check if a role has a specific permission
     */
    public function roleHasPermission(int $roleId, int $permissionId): bool
    {
        $count = $this->where(['role_id' => $roleId, 'permission_id' => $permissionId])->countAllResults();
        return $count > 0;
    }

    /**
     * Get permission assignment statistics
     */
    public function getAssignmentStats(): array
    {
        $db = \Config\Database::connect();
        
        // Most assigned permissions
        $mostAssigned = $db->query("
            SELECT 
                p.display_name,
                p.category,
                COUNT(rp.role_id) as role_count
            FROM permissions p
            LEFT JOIN admin_role_permissions rp ON rp.permission_id = p.id
            WHERE p.is_active = 1
            GROUP BY p.id, p.display_name, p.category
            ORDER BY role_count DESC
            LIMIT 10
        ")->getResult();

        // Roles with most permissions
        $rolesWithMostPerms = $db->query("
            SELECT 
                r.display_name,
                COUNT(rp.permission_id) as permission_count
            FROM admin_roles r
            LEFT JOIN admin_role_permissions rp ON rp.role_id = r.id
            WHERE r.is_active = 1
            GROUP BY r.id, r.display_name
            ORDER BY permission_count DESC
        ")->getResult();

        // Recent assignments
        $recentAssignments = $db->query("
            SELECT 
                r.display_name as role_name,
                p.display_name as permission_name,
                rp.granted_at,
                granted_admin.name as granted_by_name
            FROM admin_role_permissions rp
            INNER JOIN admin_roles r ON r.id = rp.role_id
            INNER JOIN permissions p ON p.id = rp.permission_id
            LEFT JOIN admins granted_admin ON granted_admin.id = rp.granted_by
            ORDER BY rp.granted_at DESC
            LIMIT 10
        ")->getResult();

        return [
            'most_assigned_permissions' => $mostAssigned,
            'roles_with_most_permissions' => $rolesWithMostPerms,
            'recent_assignments' => $recentAssignments,
            'total_assignments' => $this->countAllResults()
        ];
    }

    /**
     * Get permission matrix (roles vs permissions)
     */
    public function getPermissionMatrix(): array
    {
        $db = \Config\Database::connect();
        
        $matrix = $db->query("
            SELECT 
                r.id as role_id,
                r.name as role_name,
                r.display_name as role_display_name,
                p.id as permission_id,
                p.name as permission_name,
                p.display_name as permission_display_name,
                p.category,
                CASE WHEN rp.id IS NOT NULL THEN 1 ELSE 0 END as has_permission
            FROM admin_roles r
            CROSS JOIN permissions p
            LEFT JOIN admin_role_permissions rp ON rp.role_id = r.id AND rp.permission_id = p.id
            WHERE r.is_active = 1 AND p.is_active = 1
            ORDER BY r.access_level DESC, p.category, p.display_name
        ")->getResult();

        return $matrix;
    }

    /**
     * Copy permissions from one role to another
     */
    public function copyPermissions(int $sourceRoleId, int $targetRoleId, ?int $grantedBy = null): bool
    {
        $sourcePermissions = $this->where('role_id', $sourceRoleId)->findAll();
        
        if (empty($sourcePermissions)) {
            return true; // Nothing to copy
        }

        $permissionIds = array_column($sourcePermissions, 'permission_id');
        return $this->assignPermissionsToRole($targetRoleId, $permissionIds, $grantedBy);
    }
}