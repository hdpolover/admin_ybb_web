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
        'is_registration_open',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get program by name
     *
     * @param string $name
     * @return object|null
     */
    public function getProgramByName($name)
    {
        return $this->where('name', $name)
            ->where('is_deleted', 0)
            ->first();
    }

    /**
     * Get programs by program category ID
     *
     * @param int $programCategoryId
     * @return array
     */
    public function getActivePrograms($programCategoryId)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('program_category_id', $programCategoryId)
            ->where('is_active', 1)
            ->where('is_deleted', 0);
        
        return $builder->get()->getRow();
    }

    /**
     * Get featured programs by program category ID
     *
     * @param int $programCategoryId
     * @return array
     */
    public function getAllPrograms($programCategoryId)
    {
        return $this->where('program_category_id', $programCategoryId)
            ->where('is_deleted', 0)
            ->findAll();
    }

    /**
     * Get program by ID with category filter
     *
     * @param int $id
     * @param int $programCategoryId
     * @return array|null
     */
    public function getProgramByIdAndCategory($id, $programCategoryId)
    {
        return $this->where('id', $id)
            ->where('program_category_id', $programCategoryId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->first();
    }

    /**
     * Get programs with category information
     *
     * @param int|null $program_category_id
     * @return array
     */
    public function getPrograms($program_category_id = null)
    {
        // Get all programs first
        $builder = $this->builder();

        if ($program_category_id !== null) {
            $builder->where('program_category_id', $program_category_id);
        }

        $builder->where('is_deleted', 0)
                ->orderBy('created_at', 'DESC');

        $programs = $builder->get()->getResult();

        return $programs;
    }

    public function getProgramById($id)
    {
        return $this->find($id);
    }

    /**
     * Get programs not in the specified category
     *
     * @param int $categoryId Category ID to exclude
     * @param int $limit Optional limit of results
     * @param bool $activeOnly Whether to return only active programs
     * @return array
     */
    public function getOtherPrograms($categoryId, $limit = null, $activeOnly = true)
    {
        $builder = $this->builder();
        
        $builder->select('programs.*')
               ->join('program_categories', 'program_categories.id = programs.program_category_id')
               ->where('programs.program_category_id !=', $categoryId)
               ->where('programs.is_deleted', 0);
        
        if ($activeOnly) {
            $builder->where('programs.is_active', 1);
        }
        
        $builder->orderBy('created_at', 'DESC');
        
        if ($limit !== null) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResult();
    }
}
