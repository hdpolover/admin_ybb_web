<?php

namespace App\Models;

use CodeIgniter\Model;

class CompetitionCategoryModel extends Model
{
    // `id`, `program_category_id`, `program_id`, `category`, `desc`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table          = 'competition_categories';
    protected $primaryKey     = 'id';
    protected $returnType     = 'object';
    protected $useAutoIncrement = true;

    protected $allowedFields  = [
        'program_category_id',
        'program_id',
        'category',
        'desc',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = ''; // Not using soft deletes

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $protectFields  = true;

    protected $validationRules      = [
        'program_id'          => 'required|numeric',
        'category'            => 'required|min_length[3]',
        'desc'                => 'permit_empty',
        'is_active'           => 'permit_empty|in_list[0,1]',
        'is_deleted'          => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages   = [
        'program_id' => [
            'required' => 'Program ID is required.',
            'numeric'  => 'Program ID must be a number.'
        ],
        'category' => [
            'required'    => 'Category name is required.',
            'min_length'  => 'Category name must be at least 3 characters long.'
        ],
        'desc' => [
            'permit_empty' => 'Description is optional.'
        ],
    ];


    // get competition categories by program ID
    public function getCategoriesByProgramId($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->findAll();
    }
}
