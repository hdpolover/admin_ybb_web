<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramModel extends Model
{
    protected $table = 'programs';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    // auto increment
    protected $useAutoIncrement = true;

    // `id`, `program_category_id`, `name`, `logo_url`, `description`, `guideline`, `twibbon`, `start_date`, `end_date`, `registration_video_url`, `sponsor_canva_url`, `theme`, `sub_themes`, `share_desc`, `confirmation_desc`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $allowedFields = [
        'program_category_id',
        'name',
        'logo_url',
        'description',
        'guideline',
        'twibbon',
        'start_date',
        'end_date',
        'registration_video_url',
        'sponsor_canva_url',
        'theme',
        'sub_themes',
        'share_desc',
        'confirmation_desc',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getPrograms($program_category_id = null)
    {
        // Get all programs first
        $builder = $this->builder();

        if ($program_category_id !== null) {
            $builder->where('program_category_id', $program_category_id);
        }

        $result = $builder->get()->getResultArray();

        // Get all program categories in a single query to avoid N+1 problem
        $categoryIds = array_column($result, 'program_category_id');
        $categories = [];

        if (!empty($categoryIds)) {
            $categoriesQuery = $this->db->table('program_categories')
                ->whereIn('id', $categoryIds)
                ->get()
                ->getResultArray();

            // Index categories by ID for easy lookup
            foreach ($categoriesQuery as $category) {
                $categories[$category['id']] = $category;
            }
        }

        // Add the category objects to each program
        foreach ($result as &$program) {
            $catId = $program['program_category_id'];
            $program['program_category'] = $categories[$catId] ?? null;
        }

        return $result;
    }
}
