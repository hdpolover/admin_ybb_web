<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name', 'display_name', 'description', 'access_level', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[50]|is_unique[roles.name,id,{id}]',
        'display_name' => 'required|min_length[2]|max_length[100]',
        'access_level' => 'required|integer|greater_than[0]|less_than[11]'
    ];

    /**
     * Get all active roles
     */
    public function getActiveRoles(): array
    {
        return $this->where('is_active', 1)
                   ->orderBy('access_level', 'DESC')
                   ->findAll();
    }

    /**
     * Get role with permissions
     */
    public function getRoleWithPermissions(int $roleId): ?object
    {
        $role = $this->find($roleId);
        if (!$role) return null;

        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT p.*, rp.granted_at
            FROM permissions p
            INNER JOIN role_permissions rp ON rp.permission_id = p.id
            WHERE rp.role_id = ? AND p.is_active = 1
            ORDER BY p.category, p.display_name
        ", [$roleId]);

        $role->permissions = $query->getResult();
        return $role;
    }

    /**
     * Get all roles with permission counts
     */
    public function getRolesWithPermissionCounts(): array
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                r.*,
                COUNT(rp.permission_id) as permission_count,
                COUNT(a.id) as admin_count
            FROM roles r
            LEFT JOIN role_permissions rp ON rp.role_id = r.id
            LEFT JOIN admins a ON a.role_id = r.id AND a.is_active = 1
            WHERE r.is_active = 1
            GROUP BY r.id
            ORDER BY r.access_level DESC
        ")->getResult();
    }

    /**
     * Assign permissions to role
     */
    public function assignPermissions(int $roleId, array $permissionIds, int $grantedBy = null): bool
    {
        $db = \Config\Database::connect();
        
        try {
            $db->transStart();
            
            // Remove existing permissions
            $db->query("DELETE FROM role_permissions WHERE role_id = ?", [$roleId]);
            
            // Add new permissions
            if (!empty($permissionIds)) {
                $values = [];
                $params = [];
                
                foreach ($permissionIds as $permissionId) {
                    $values[] = "(?, ?, NOW(), ?)";
                    $params[] = $roleId;
                    $params[] = $permissionId;
                    $params[] = $grantedBy;
                }
                
                $sql = "INSERT INTO role_permissions (role_id, permission_id, granted_at, granted_by) VALUES " . implode(', ', $values);
                $db->query($sql, $params);
            }
            
            $db->transComplete();
            return $db->transStatus();
            
        } catch (\Exception $e) {
            $db->transRollback();
            return false;
        }
    }

    /**
     * Check if role has permission
     */
    public function hasPermission(int $roleId, string $permissionName): bool
    {
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT COUNT(*) as count
            FROM role_permissions rp
            INNER JOIN permissions p ON p.id = rp.permission_id
            WHERE rp.role_id = ? AND p.name = ? AND p.is_active = 1
        ", [$roleId, $permissionName]);
        
        $result = $query->getRow();
        return $result->count > 0;
    }

    /**
     * Get role hierarchy for access control
     */
    public function getRoleHierarchy(): array
    {
        $roles = $this->getActiveRoles();
        $hierarchy = [];
        
        foreach ($roles as $role) {
            $hierarchy[$role->name] = $role->access_level;
        }
        
        return $hierarchy;
    }

    /**
     * Create default roles
     */
    public function createDefaultRoles(): bool
    {
        $defaultRoles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access, can manage all admins and settings',
                'access_level' => 10,
                'is_active' => 1
            ],
            [
                'name' => 'tnd',
                'display_name' => 'Training & Development',
                'description' => 'Training & Development: scoring, participants, analytics',
                'access_level' => 8,
                'is_active' => 1
            ],
            [
                'name' => 'reviewer',
                'display_name' => 'Content Reviewer',
                'description' => 'Content review: essays, submissions, participant data',
                'access_level' => 6,
                'is_active' => 1
            ],
            [
                'name' => 'ambassador_coordinator',
                'display_name' => 'Ambassador Coordinator',
                'description' => 'Ambassador management and dashboard overview',
                'access_level' => 4,
                'is_active' => 1
            ],
            [
                'name' => 'news_writer',
                'display_name' => 'News Writer',
                'description' => 'News content management across all programs',
                'access_level' => 2,
                'is_active' => 1
            ]
        ];

        try {
            foreach ($defaultRoles as $role) {
                // Check if role already exists
                $existing = $this->where('name', $role['name'])->first();
                if (!$existing) {
                    $this->insert($role);
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}