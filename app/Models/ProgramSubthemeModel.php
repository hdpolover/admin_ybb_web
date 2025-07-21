<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramSubthemeModel extends Model
{
// `id`, `program_id`, `name`, `desc`, `is_active`, `is_deleted`, `created_at`, `updated_at`

    protected $table          = 'program_subthemes';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'object'; // Set to return objects
    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $protectFields  = true;
    protected $allowedFields  = [
        'program_id',
        'name',
        'desc',
        'is_active',
        'is_deleted'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = ''; // Not using soft deletes

    // Validation
    protected $validationRules      = [
        'program_id' => 'required|numeric',
        'name'       => 'required|min_length[3]',
        'desc'       => 'permit_empty',
        'is_active'  => 'permit_empty|in_list[0,1]',
        'is_deleted' => 'permit_empty|in_list[0,1]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get active subthemes for a program
     *
     * @param int $programId
     * @return object[]
     */
    public function getActiveSubthemes($programId)
    {
        return $this->where('program_id', $programId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
    }
    

    public function getAllSubthemes($programId)
    {
        return $this->where('program_id', $programId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
    }

    /**
     * Get subthemes by program ID
     *
     * @param int $programId
     * @return object[]
     */
    public function getSubthemesByProgramId($programId)
    {
        return $this->where('program_id', $programId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
    }
}
