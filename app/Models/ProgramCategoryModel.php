<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramCategoryModel extends Model
{
    protected $table = 'program_categories';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'name',
        'description',
        'program_type_id',
        'web_url',
        'logo_url',
        'tagline',
        'contact',
        'location',
        'email',
        'instagram',
        'tiktok',
        'youtube',
        'telegram',
        'verification_required',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public function getAllCategoriesWithPrograms() {
        $builder = $this->builder('program_categories pc');
        $builder->select('pc.*, pt.name as program_type_name')
            ->join('program_types pt', 'pt.id = pc.program_type_id', 'left');
            // ->where('pc.is_deleted', 0)
            // ->where('pc.is_active', 1);
            
        $categories = $builder->get()->getResult();

        $programModel = new \App\Models\ProgramModel();

        foreach ($categories as &$category) {
            // Get all programs for this category
            $programs = $programModel->where('program_category_id', $category->id)
                        // ->where('is_deleted', 0)
                        // ->where('is_active', 1)
                        ->findAll();
                        
            $category->programs = $programs;
        }

        return $categories;
    }

    public function getProgramCategories($limit = 10, $offset = 0, $filters = [])
    {
        $builder = $this->builder();

        // Apply filters if any
        if (!empty($filters)) {
            $builder->where($filters);
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        // Apply pagination
        $builder->limit($limit, $offset);
        
        // Select all fields
        $builder->select('*');

        // Execute query
        $result = $builder->get()->getResultArray();

        $programCategories = [];

        // Map to entities
        foreach ($result as $row) {
            $programCategory = $row;
 
            $programTypeId =  $row['program_type_id'];

            // get program type
            $programType = $this->db->table('program_types')->where('id', $programTypeId)->get()->getRowArray();

            $programCategory['program_type'] = $programType;
            
            $programCategories[] = $programCategory;
        }

        return [
            'data' => $programCategories,
            'total' => $total
        ];
    }
}
