<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramTestimonyModel extends Model
{
    protected $table          = 'program_testimonies';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'object'; // Set to return objects
    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $protectFields  = true;
    protected $allowedFields  = [
        'program_category_id',
        'person_name',
        'testimony',
        'occupation',
        'institution',
        'img_url',
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
        'program_category_id' => 'required|numeric',
        'person_name'         => 'required|min_length[3]',
        'testimony'           => 'required|min_length[5]',
        'occupation'          => 'permit_empty',
        'institution'         => 'permit_empty',
        'img_url'             => 'permit_empty',
        'is_active'           => 'permit_empty|in_list[0,1]',
        'is_deleted'          => 'permit_empty|in_list[0,1]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get active testimonies for a program
     *
     * @param int $programId
     * @return object[]
     */
    public function getActiveTestimonies($programId)
    {
        return $this->where('program_category_id', $programId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
    }
}