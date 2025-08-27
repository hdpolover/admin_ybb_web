<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminProgramModel extends Model
{
    protected $table = 'admin_programs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'admin_id', 'program_id', 'assigned_at', 'assigned_by'
    ];

    // Validation rules
    protected $validationRules = [
        'admin_id' => 'required|integer',
        'program_id' => 'required|integer',
        'assigned_by' => 'permit_empty|integer'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    /**
     * Get programs for admin
     */
    public function getAdminPrograms(int $adminId): array
    {
        return $this->select('admin_programs.*, programs.name as program_name, programs.banner_url')
                   ->join('programs', 'programs.id = admin_programs.program_id')
                   ->where('admin_programs.admin_id', $adminId)
                   ->where('programs.is_active', 1)
                   ->orderBy('programs.name', 'ASC')
                   ->findAll();
    }

    /**
     * Get admins for program
     */
    public function getProgramAdmins(int $programId): array
    {
        return $this->select('admin_programs.*, admins.name as admin_name, admins.email, admins.role')
                   ->join('admins', 'admins.id = admin_programs.admin_id')
                   ->where('admin_programs.program_id', $programId)
                   ->where('admins.is_active', 1)
                   ->orderBy('admins.name', 'ASC')
                   ->findAll();
    }

    /**
     * Check if admin is assigned to program
     */
    public function isAdminAssigned(int $adminId, int $programId): bool
    {
        return $this->where('admin_id', $adminId)
                   ->where('program_id', $programId)
                   ->countAllResults() > 0;
    }

    /**
     * Assign admin to program
     */
    public function assignAdminToProgram(int $adminId, int $programId, int $assignedBy = null): bool
    {
        // Check if already assigned
        if ($this->isAdminAssigned($adminId, $programId)) {
            return true;
        }

        $data = [
            'admin_id' => $adminId,
            'program_id' => $programId,
            'assigned_at' => date('Y-m-d H:i:s'),
            'assigned_by' => $assignedBy
        ];

        return $this->insert($data) !== false;
    }

    /**
     * Remove admin from program
     */
    public function removeAdminFromProgram(int $adminId, int $programId): bool
    {
        return $this->where('admin_id', $adminId)
                   ->where('program_id', $programId)
                   ->delete();
    }

    /**
     * Remove admin from all programs
     */
    public function removeAdminFromAllPrograms(int $adminId): bool
    {
        return $this->where('admin_id', $adminId)->delete();
    }

    /**
     * Get admin programs with details
     */
    public function getAdminProgramsWithDetails(int $adminId): array
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                ap.*,
                p.name as program_name,
                p.banner_url,
                p.start_date,
                p.end_date,
                p.is_active as program_active,
                pc.name as category_name
            FROM admin_programs ap
            INNER JOIN programs p ON p.id = ap.program_id
            LEFT JOIN program_categories pc ON pc.id = p.program_category_id
            WHERE ap.admin_id = ?
            ORDER BY p.name ASC
        ", [$adminId]);
        
        return $query->getResultArray();
    }

    /**
     * Get statistics for admin program assignments
     */
    public function getAssignmentStats(): array
    {
        $db = \Config\Database::connect();
        
        // Count total assignments
        $totalAssignments = $this->countAllResults();
        
        // Count assignments by role
        $roleStats = $db->query("
            SELECT 
                a.role,
                COUNT(ap.id) as assignment_count,
                COUNT(DISTINCT ap.admin_id) as admin_count,
                COUNT(DISTINCT ap.program_id) as program_count
            FROM admin_programs ap
            INNER JOIN admins a ON a.id = ap.admin_id
            GROUP BY a.role
            ORDER BY assignment_count DESC
        ")->getResultArray();
        
        // Get most assigned programs
        $topPrograms = $db->query("
            SELECT 
                p.name,
                COUNT(ap.id) as assignment_count
            FROM admin_programs ap
            INNER JOIN programs p ON p.id = ap.program_id
            GROUP BY ap.program_id, p.name
            ORDER BY assignment_count DESC
            LIMIT 5
        ")->getResultArray();
        
        return [
            'total_assignments' => $totalAssignments,
            'role_stats' => $roleStats,
            'top_programs' => $topPrograms
        ];
    }
}