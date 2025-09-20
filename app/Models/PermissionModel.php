<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name', 'display_name', 'description', 'category', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]|is_unique[permissions.name,id,{id}]',
        'display_name' => 'required|min_length[2]|max_length[150]',
        'category' => 'required|max_length[50]'
    ];

    /**
     * Get all active permissions
     */
    public function getActivePermissions(): array
    {
        return $this->where('is_active', 1)
                   ->orderBy('category', 'ASC')
                   ->orderBy('display_name', 'ASC')
                   ->findAll();
    }

    /**
     * Get permissions by category
     */
    public function getPermissionsByCategory(): array
    {
        $permissions = $this->getActivePermissions();
        $categorized = [];
        
        foreach ($permissions as $permission) {
            $categorized[$permission->category][] = $permission;
        }
        
        return $categorized;
    }

    /**
     * Get permissions for role
     */
    public function getPermissionsForRole(int $roleId): array
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT p.*
            FROM permissions p
            INNER JOIN admin_role_permissions rp ON rp.permission_id = p.id
            WHERE rp.role_id = ? AND p.is_active = 1
            ORDER BY p.category, p.display_name
        ", [$roleId])->getResult();
    }

    /**
     * Create default permissions
     */
    public function createDefaultPermissions(): bool
    {
        $defaultPermissions = [
            // System Management
            ['name' => 'manage_admins', 'display_name' => 'Manage Administrators', 'description' => 'Create, edit, delete admin accounts', 'category' => 'system'],
            ['name' => 'manage_roles', 'display_name' => 'Manage Roles', 'description' => 'Create and edit user roles and permissions', 'category' => 'system'],
            ['name' => 'system_settings', 'display_name' => 'System Settings', 'description' => 'Access system configuration settings', 'category' => 'system'],
            ['name' => 'view_system_logs', 'display_name' => 'View System Logs', 'description' => 'Access system logs and audit trails', 'category' => 'system'],
            
            // Dashboard & Analytics
            ['name' => 'view_dashboard', 'display_name' => 'View Dashboard', 'description' => 'Access main dashboard', 'category' => 'dashboard'],
            ['name' => 'view_analytics', 'display_name' => 'View Analytics', 'description' => 'Access analytics and reports', 'category' => 'dashboard'],
            ['name' => 'export_data', 'display_name' => 'Export Data', 'description' => 'Export data to various formats', 'category' => 'dashboard'],
            
            // Participant Management
            ['name' => 'view_participants', 'display_name' => 'View Participants', 'description' => 'View participant lists and details', 'category' => 'participants'],
            ['name' => 'manage_participants', 'display_name' => 'Manage Participants', 'description' => 'Create, edit, delete participant records', 'category' => 'participants'],
            ['name' => 'export_participants', 'display_name' => 'Export Participants', 'description' => 'Export participant data', 'category' => 'participants'],
            
            // Scoring & Assessment
            ['name' => 'view_scores', 'display_name' => 'View Scores', 'description' => 'View participant scores and rankings', 'category' => 'scoring'],
            ['name' => 'manage_scores', 'display_name' => 'Manage Scores', 'description' => 'Input and edit participant scores', 'category' => 'scoring'],
            ['name' => 'view_rankings', 'display_name' => 'View Rankings', 'description' => 'View participant rankings and leaderboards', 'category' => 'scoring'],
            
            // Essays & Content Review
            ['name' => 'view_essays', 'display_name' => 'View Essays', 'description' => 'View submitted essays and content', 'category' => 'content'],
            ['name' => 'manage_essays', 'display_name' => 'Manage Essays', 'description' => 'Review, edit, and manage essay submissions', 'category' => 'content'],
            ['name' => 'review_content', 'display_name' => 'Review Content', 'description' => 'Review and approve submitted content', 'category' => 'content'],
            
            // Ambassador Management
            ['name' => 'view_ambassadors', 'display_name' => 'View Ambassadors', 'description' => 'View ambassador lists and details', 'category' => 'ambassadors'],
            ['name' => 'manage_ambassadors', 'display_name' => 'Manage Ambassadors', 'description' => 'Create, edit, delete ambassador records', 'category' => 'ambassadors'],
            ['name' => 'view_ambassador_dashboard', 'display_name' => 'Ambassador Dashboard', 'description' => 'Access ambassador analytics dashboard', 'category' => 'ambassadors'],
            ['name' => 'export_ambassadors', 'display_name' => 'Export Ambassadors', 'description' => 'Export ambassador data', 'category' => 'ambassadors'],
            
            // News & Content Management
            ['name' => 'view_news', 'display_name' => 'View News', 'description' => 'View news articles and content', 'category' => 'news'],
            ['name' => 'manage_news', 'display_name' => 'Manage News', 'description' => 'Create, edit, delete news articles', 'category' => 'news'],
            ['name' => 'publish_content', 'display_name' => 'Publish Content', 'description' => 'Publish news and content to public', 'category' => 'news'],
            ['name' => 'manage_announcements', 'display_name' => 'Manage Announcements', 'description' => 'Create and manage system announcements', 'category' => 'news'],
            
            // Program Management
            ['name' => 'view_programs', 'display_name' => 'View Programs', 'description' => 'View program lists and details', 'category' => 'programs'],
            ['name' => 'manage_programs', 'display_name' => 'Manage Programs', 'description' => 'Create, edit, delete programs', 'category' => 'programs'],
            ['name' => 'view_program_settings', 'display_name' => 'Program Settings', 'description' => 'Access program-specific settings', 'category' => 'programs'],
            
            // Financial & Payments
            ['name' => 'view_payments', 'display_name' => 'View Payments', 'description' => 'View payment records and transactions', 'category' => 'payments'],
            ['name' => 'manage_payments', 'display_name' => 'Manage Payments', 'description' => 'Process and manage payments', 'category' => 'payments'],
            ['name' => 'view_financial_reports', 'display_name' => 'Financial Reports', 'description' => 'Access financial reports and analytics', 'category' => 'payments']
        ];

        try {
            foreach ($defaultPermissions as $permission) {
                // Check if permission already exists
                $existing = $this->where('name', $permission['name'])->first();
                if (!$existing) {
                    $permission['is_active'] = 1;
                    $this->insert($permission);
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get permission statistics
     */
    public function getPermissionStats(): array
    {
        $db = \Config\Database::connect();
        
        // Count total permissions
        $totalQuery = $db->query("SELECT COUNT(*) as total FROM permissions WHERE is_active = 1");
        $total = $totalQuery->getRow()->total;
        
        // Count active roles (roles that have permissions assigned)
        $activeRolesQuery = $db->query("
            SELECT COUNT(DISTINCT rp.role_id) as active_roles
            FROM admin_role_permissions rp
            INNER JOIN admin_roles r ON r.id = rp.role_id
            WHERE r.is_active = 1
        ");
        $activeRoles = $activeRolesQuery->getRow()->active_roles;
        
        // Count by category
        $categoryStats = $db->query("
            SELECT category, COUNT(*) as count
            FROM permissions
            WHERE is_active = 1
            GROUP BY category
            ORDER BY count DESC
        ")->getResult();
        
        // Most assigned permissions
        $mostAssigned = $db->query("
            SELECT 
                p.display_name,
                COUNT(rp.role_id) as role_count
            FROM permissions p
            LEFT JOIN admin_role_permissions rp ON rp.permission_id = p.id
            WHERE p.is_active = 1
            GROUP BY p.id, p.display_name
            ORDER BY role_count DESC
            LIMIT 10
        ")->getResult();
        
        return [
            'total' => $total,
            'active_roles' => $activeRoles,
            'category_stats' => $categoryStats,
            'most_assigned' => $mostAssigned
        ];
    }
}