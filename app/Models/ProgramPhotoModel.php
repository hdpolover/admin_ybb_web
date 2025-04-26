<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramPhotoModel extends Model
{
    // `id`, `program_category_id`, `title`, `description`, `img_url`, `is_active`, `is_deleted`, `created_at`, `updated_at

    protected $table          = 'program_photos';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'object'; // Set to return objects
    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $protectFields  = true;
    protected $allowedFields  = [
        'program_category_id',
        'title',
        'year',
        'description',
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
        'year'                => 'required|numeric',
        'title'               => 'required|min_length[3]',
        'description'         => 'permit_empty',
        'img_url'             => 'required|valid_url',
        'is_active'           => 'permit_empty|in_list[0,1]',
        'is_deleted'          => 'permit_empty|in_list[0,1]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get active photos for a program category
     *
     * @param int $programCategoryId
     * @return object[]
     */
    public function getActivePhotos($programCategoryId)
    {
        return $this->where('program_category_id', $programCategoryId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->findAll();
    }

    /**
     * Get all photos regardless of status
     *
     * @param int|null $programCategoryId Optional program category ID filter
     * @return object[]
     */
    public function getAllPhotos($programCategoryId = null)
    {
        $query = $this->where('is_deleted', 0);

        if ($programCategoryId !== null) {
            $query->where('program_category_id', $programCategoryId);
        }

        return $query->findAll();
    }
}
