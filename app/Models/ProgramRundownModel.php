<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramRundownModel extends Model
{
    // `id`, `program_id`, `start_date`, `end_date`, `title`, `description`, `order_number`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table          = 'program_rundowns';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'object'; // Set to return objects
    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $protectFields  = true;
    protected $allowedFields  = [
        'program_id',
        'start_date',
        'end_date',
        'title',
        'description',
        'order_number',
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
        'program_id'     => 'required|numeric',
        'start_date'     => 'required|valid_date[Y-m-d H:i:s]',
        'end_date'       => 'required|valid_date[Y-m-d H:i:s]',
        'title'          => 'required|min_length[3]',
        'description'    => 'permit_empty',
        'order_number'   => 'permit_empty|numeric',
        'is_active'      => 'permit_empty|in_list[0,1]',
        'is_deleted'     => 'permit_empty|in_list[0,1]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get active rundowns for a program
     *
     * @param int $programId
     * @return object[]
     */
    public function getActiveRundowns($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->findAll();
    }

    /**
     * Get a rundown by ID
     *
     * @param int $id
     * @return object|null
     */
    public function getRundownById($id)

    {
        return $this->where('id', $id)
            ->where('is_deleted', 0)
            ->first();
    }

    // getByProgramId
    public function getByProgramId($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->where('is_active', 1)
            ->findAll();
    }
}
