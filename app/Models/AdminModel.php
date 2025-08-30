<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class AdminModel extends Model
{
    protected $table = 'admins';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false; // Disabled - using custom is_deleted field
    protected $protectFields = true;
    protected $allowedFields = [
        'name', 'email', 'password', 'role', 'department', 'phone', 'bio', 
        'profile_url', 'avatar', 'timezone', 'is_active', 'last_login', 
        'permissions', 'access_level', 'can_manage_users', 'session_token', 'is_deleted',
        'deleted_at', 'deleted_by', 'created_by', 'updated_by'
    ];

    // Validation rules
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[255]',
        'email' => 'required|valid_email|is_unique[admins.email,id,{id}]',
        'password' => 'required|min_length[8]',
        'role' => 'required|in_list[super_admin,tnd,reviewer,ambassador_coordinator,news_writer]',
        'department' => 'permit_empty|max_length[100]',
        'phone' => 'permit_empty|max_length[20]',
        'access_level' => 'permit_empty|integer|greater_than[0]|less_than[6]',
        'can_manage_users' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email address is already registered.',
            'valid_email' => 'Please enter a valid email address.'
        ],
        'password' => [
            'min_length' => 'Password must be at least 8 characters long.'
        ],
        'role' => [
            'in_list' => 'Please select a valid role.'
        ]
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Caching
    protected $cacheName = 'admin_';
    protected $cacheExpiry = 3600; // 1 hour

    /**
     * Role hierarchy for access control
     */
    private static $roleHierarchy = [
        'super_admin' => 5,
        'tnd' => 4,
        'reviewer' => 3,
        'ambassador_coordinator' => 2,
        'news_writer' => 1
    ];

    /**
     * Custom finder that respects soft deletes
     */
    public function findActive($id = null)
    {
        if ($id === null) {
            return $this->where('is_deleted', 0)->findAll();
        }
        
        return $this->where(['id' => $id, 'is_deleted' => 0])->first();
    }

    public function getAdminByEmail($email)
    {
        return $this->where(['email' => $email, 'is_deleted' => 0])->first();
    }

    // Enhanced login with modern password hashing
    public function signIn($email, $password)
    {
        $admin = $this->getAdminByEmail($email);

        if ($admin) {
            // Support both old MD5 and new password_hash
            if (password_verify($password, $admin->password) || md5($password) === $admin->password) {
                // If using old MD5, upgrade to new hash
                if (md5($password) === $admin->password) {
                    $this->update($admin->id, ['password' => password_hash($password, PASSWORD_DEFAULT)]);
                }
                return $admin;
            }
        }

        return false;
    }

    /**
     * Get admin by email with active status
     */
    public function getByEmail(string $email): ?object
    {
        $cacheKey = $this->cacheName . 'email_' . md5($email);
        
        if ($cached = cache()->get($cacheKey)) {
            return $cached;
        }

        $result = $this->where(['email' => $email, 'is_active' => 1, 'is_deleted' => 0])
                      ->first();

        if ($result) {
            cache()->save($cacheKey, $result, $this->cacheExpiry);
        }

        return $result;
    }

    /**
     * Get admins with role-based filtering
     */
    public function getAdminsByRole(string $role = null, int $programId = null): array
    {
        $cacheKey = $this->cacheName . 'role_' . ($role ?? 'all') . '_prog_' . ($programId ?? 'all');
        
        if ($cached = cache()->get($cacheKey)) {
            return $cached;
        }

        $builder = $this->select('admins.*, programs.name as program_name')
                       ->join('programs', 'programs.id = admins.program_id', 'left')
                       ->where(['admins.is_active' => 1, 'admins.is_deleted' => 0]);

        if ($role) {
            $builder->where('admins.role', $role);
        }

        if ($programId) {
            $builder->where('admins.program_id', $programId);
        }

        $builder->orderBy('admins.role', 'DESC')
                ->orderBy('admins.name', 'ASC');

        $result = $builder->findAll();
        cache()->save($cacheKey, $result, $this->cacheExpiry);

        return $result;
    }

    /**
     * Check if admin has permission to access resource
     */
    public function hasPermission(object $admin, string $permission): bool
    {
        // Super admin has all permissions
        if ($admin->role === 'super_admin') {
            return true;
        }

        // Check role-based permissions
        $rolePermissions = self::getRolePermissions();
        $adminPermissions = $rolePermissions[$admin->role] ?? [];
        
        if (in_array($permission, $adminPermissions)) {
            return true;
        }

        // Check custom permissions
        if (isset($admin->permissions) && $admin->permissions) {
            $customPerms = json_decode($admin->permissions, true);
            return in_array($permission, $customPerms ?? []);
        }

        return false;
    }

    /**
     * Get permission level required for specific actions
     */
    private function getPermissionLevel(string $permission): int
    {
        $permissionLevels = [
            'manage_programs' => 3,
            'manage_participants' => 2,
            'manage_content' => 2,
            'manage_announcements' => 2,
            'manage_faqs' => 2,
            'view_reports' => 2,
            'manage_settings' => 4,
            'manage_admins' => 4,
            'manage_payments' => 3,
            'review_abstracts' => 1,
            'manage_speakers' => 2
        ];

        return $permissionLevels[$permission] ?? 1;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $id, string $sessionToken = null): bool
    {
        $data = ['last_login' => Time::now()->toDateTimeString()];
        
        if ($sessionToken) {
            $data['session_token'] = $sessionToken;
        }

        $result = $this->update($id, $data);
        
        // Clear cache
        $this->clearAdminCache($id);
        
        return $result;
    }

    /**
     * Get admin statistics
     */
    public function getAdminStatistics(): array
    {
        $cacheKey = $this->cacheName . 'statistics';
        
        if ($cached = cache()->get($cacheKey)) {
            return $cached;
        }

        $stats = [
            'total_admins' => $this->where(['is_deleted' => 0])->countAllResults(),
            'active_admins' => $this->where(['is_active' => 1, 'is_deleted' => 0])->countAllResults(),
            'super_admins' => $this->where(['role' => 'super_admin', 'is_active' => 1, 'is_deleted' => 0])->countAllResults(),
            'recent_logins' => $this->where(['last_login >=' => date('Y-m-d H:i:s', strtotime('-7 days')), 'is_deleted' => 0])->countAllResults(),
            'by_role' => []
        ];

        // Get counts by role
        $roleStats = $this->select('role, COUNT(*) as count')
                         ->where(['is_active' => 1, 'is_deleted' => 0])
                         ->groupBy('role')
                         ->findAll();

        foreach ($roleStats as $stat) {
            $stats['by_role'][$stat->role] = $stat->count;
        }

        cache()->save($cacheKey, $stats, 1800); // 30 minutes
        
        return $stats;
    }

    /**
     * Search admins with filters for DataTable
     */
    public function searchAdmins(array $filters = []): array
    {
        $builder = $this->db->table($this->table . ' a')
                           ->select('a.*, GROUP_CONCAT(DISTINCT p.name SEPARATOR ", ") as program_names')
                           ->join('admin_programs ap', 'a.id = ap.admin_id', 'left')
                           ->join('programs p', 'ap.program_id = p.id AND p.is_active = 1', 'left')
                           ->where('a.is_deleted', 0)
                           ->groupBy('a.id');
        
        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                   ->like('a.name', $search)
                   ->orLike('a.email', $search)
                   ->orLike('a.role', $search)
                   ->orLike('p.name', $search)
                   ->groupEnd();
        }
        
        // Apply role filter
        if (!empty($filters['role'])) {
            $builder->where('a.role', $filters['role']);
        }
        
        // Apply program filter
        if (!empty($filters['program_id'])) {
            $builder->having('FIND_IN_SET(?, GROUP_CONCAT(DISTINCT ap.program_id))', [$filters['program_id']]);
        }
        
        // Apply active status filter
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $builder->where('a.is_active', $filters['is_active']);
        }
        
        $builder->orderBy('a.created_at', 'DESC');
        
        $query = $builder->get();
        return $query->getResult();
    }

    /**
     * Create new admin with proper validation
     */
    public function createAdmin(array $data): bool
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Set default access level based on role
        if (!isset($data['access_level']) && isset($data['role'])) {
            $data['access_level'] = self::$roleHierarchy[$data['role']] ?? 1;
        }

        $result = $this->insert($data);
        
        if ($result) {
            $this->clearAllAdminCache();
        }
        
        return $result !== false;
    }

    /**
     * Clear specific admin cache
     */
    private function clearAdminCache(int $id): void
    {
        $admin = $this->find($id);
        if ($admin) {
            cache()->delete($this->cacheName . $id);
            cache()->delete($this->cacheName . 'email_' . md5($admin->email));
        }
        
        $this->clearAllAdminCache();
    }

    /**
     * Clear all admin-related cache
     */
    private function clearAllAdminCache(): void
    {
        cache()->delete($this->cacheName . 'statistics');
        
        // Clear role-based caches
        $roles = ['super', 'program_admin', 'editor', 'moderator'];
        foreach ($roles as $role) {
            cache()->delete($this->cacheName . 'role_' . $role . '_prog_all');
        }
        
        cache()->delete($this->cacheName . 'role_all_prog_all');
    }

    /**
     * Get role display name
     */
    public static function getRoleDisplayName(string $role): string
    {
        $roleNames = [
            'super_admin' => 'Super Administrator',
            'tnd' => 'Training & Development', 
            'reviewer' => 'Content Reviewer',
            'ambassador_coordinator' => 'Ambassador Coordinator',
            'news_writer' => 'News Writer'
        ];

        return $roleNames[$role] ?? ucfirst($role);
    }

    /**
     * Get all available roles
     */
    public static function getAllRoles(): array
    {
        return array_keys(self::$roleHierarchy);
    }

    /**
     * Get role permissions mapping
     */
    public static function getRolePermissions(): array
    {
        return [
            'super_admin' => [
                'manage_users', 'manage_admins', 'view_all_data', 'system_settings',
                'manage_programs', 'view_participants', 'view_scores', 'manage_essays',
                'manage_ambassadors', 'manage_news', 'view_analytics', 'export_data'
            ],
            'tnd' => [
                'view_participants', 'manage_participants', 'view_scores', 'manage_scores',
                'export_participants', 'view_analytics'
            ],
            'reviewer' => [
                'view_participants', 'view_essays', 'manage_essays', 'review_content',
                'view_scores'
            ],
            'ambassador_coordinator' => [
                'view_ambassadors', 'manage_ambassadors', 'view_ambassador_dashboard',
                'export_ambassadors'
            ],
            'news_writer' => [
                'view_news', 'manage_news', 'create_articles', 'publish_content'
            ]
        ];
    }

    /**
     * Check if role can manage other role
     */
    public static function canManageRole(string $userRole, string $targetRole): bool
    {
        $userLevel = self::$roleHierarchy[$userRole] ?? 0;
        $targetLevel = self::$roleHierarchy[$targetRole] ?? 0;
        
        return $userLevel > $targetLevel;
    }

    /**
     * Get admin programs
     */
    public function getAdminPrograms(int $adminId): array
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT p.*, ap.assigned_at 
            FROM programs p 
            INNER JOIN admin_programs ap ON p.id = ap.program_id 
            WHERE ap.admin_id = ? AND p.is_active = 1
            ORDER BY p.name
        ", [$adminId]);
        
        return $query->getResultArray();
    }

    /**
     * Assign admin to programs
     */
    public function assignToPrograms(int $adminId, array $programIds, int $assignedBy = null): bool
    {
        $db = \Config\Database::connect();
        
        try {
            $db->transStart();
            
            // Remove existing assignments
            $db->query("DELETE FROM admin_programs WHERE admin_id = ?", [$adminId]);
            
            // Add new assignments
            if (!empty($programIds)) {
                foreach ($programIds as $programId) {
                    $db->query("INSERT INTO admin_programs (admin_id, program_id, assigned_at, assigned_by) VALUES (?, ?, NOW(), ?)", 
                              [$adminId, $programId, $assignedBy]);
                }
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            $db->transRollback();
            return false;
        }
    }

    /**
     * Get menu permissions for role
     */
    public static function getMenuPermissions(string $role): array
    {
        $menus = [
            'super_admin' => [
                'dashboard', 'participants', 'essays', 'scoring', 'ambassadors', 
                'news', 'analytics', 'settings', 'admin_management', 'exports'
            ],
            'tnd' => [
                'dashboard', 'participants', 'scoring', 'analytics', 'exports'
            ],
            'reviewer' => [
                'dashboard', 'participants', 'essays', 'scoring'
            ],
            'ambassador_coordinator' => [
                'dashboard', 'ambassadors', 'analytics'
            ],
            'news_writer' => [
                'dashboard', 'news'
            ]
        ];
        
        return $menus[$role] ?? [];
    }
}