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
        'about',
        'core_values',
        'objectives',
        'benefits',
        'sponsor_url',
        'main_banner_url',
        'main_video_url',
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
    
    /**
     * Constructor to register cache hooks
     */
    public function __construct()
    {
        parent::__construct();
        
        // Load cache helper
        helper(['cache']);
        
        // Register cache invalidation hook
        if (function_exists('register_cache_clear_hook')) {
            register_cache_clear_hook($this, 'program_category');
        }
    }

    // get program category by id
    public function getProgramCategoryById($id)
    {
        // Create a cache key for this category
        $cacheKey = "program_category_{$id}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $category = $cache->get($cacheKey);
        
        if ($category !== null) {
            log_message('info', "ProgramCategoryModel::getProgramCategoryById - Returning cached category for ID: {$id}");
            return $category;
        }
        
        // Cache miss - get from database
        log_message('info', "ProgramCategoryModel::getProgramCategoryById - Cache miss for ID: {$id}");
        
        $builder = $this->builder();
        $builder->select('*')
            ->where('id', $id);
        $category = $builder->get()->getRow();
        
        // Cache for 24 hours (86400 seconds) if category found
        if ($category) {
            $cache->save($cacheKey, $category, 86400);
        }
        
        return $category;
    }

    // get program category id by web_url
    public function getProgramCategoryIdByWebUrl($web_url)
    {
        // Create a cache key for this lookup
        $cacheKey = "program_category_web_url_" . md5($web_url);
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $categoryId = $cache->get($cacheKey);
        
        if ($categoryId !== null) {
            log_message('info', "ProgramCategoryModel::getProgramCategoryIdByWebUrl - Returning cached category ID for URL: {$web_url}");
            return $categoryId;
        }
        
        // Cache miss - get from database
        log_message('info', "ProgramCategoryModel::getProgramCategoryIdByWebUrl - Cache miss for URL: {$web_url}");
        
        $builder = $this->builder();
        $builder->select('id')
            ->where('web_url', $web_url);
        $categoryId = $builder->get()->getRowArray();
        
        // Cache for 24 hours (86400 seconds) if category found
        if ($categoryId) {
            $cache->save($cacheKey, $categoryId, 86400);
        }
        
        return $categoryId;
    }

    public function getAllCategoriesWithPrograms() {
        // Create a cache key for all categories with programs
        $cacheKey = "all_categories_with_programs";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $categories = $cache->get($cacheKey);
        
        if ($categories !== null) {
            log_message('info', "ProgramCategoryModel::getAllCategoriesWithPrograms - Returning cached categories with programs");
            return $categories;
        }
        
        // Cache miss - get from database
        log_message('info', "ProgramCategoryModel::getAllCategoriesWithPrograms - Cache miss, fetching all categories with programs");
        
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
        
        // Cache for 24 hours (86400 seconds)
        $cache->save($cacheKey, $categories, 86400);

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

    /**
     * Get program information by web URL
     *
     * @param string $web_url The web URL of the program
     * @return object|null Program data or null if not found
     */
    public function getProgramCategoryByParams($params)
    {
        // Create a cache key based on params
        $cacheKey = "program_category_params_" . md5(json_encode($params));
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $category = $cache->get($cacheKey);
        
        if ($category !== null) {
            log_message('info', "ProgramCategoryModel::getProgramCategoryByParams - Returning cached category for params: " . json_encode($params));
            return $category;
        }
        
        // Cache miss - get from database
        log_message('info', "ProgramCategoryModel::getProgramCategoryByParams - Cache miss for params: " . json_encode($params));
        
        $builder = $this->builder();
        $builder->select('*')
            ->where($params);
        
        $category = $builder->get()->getRow();
        
        // Cache for 24 hours (86400 seconds) if category found
        if ($category) {
            $cache->save($cacheKey, $category, 86400);
        }
        
        return $category;
    }
}
