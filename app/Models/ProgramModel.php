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
        'banner_url',
        'description',
        'guideline',
        'main_essay_question',
        'essay_guideline_url',
        'twibbon',
        'twibbon_video_url',
        'start_date',
        'end_date',
        'registration_video_url',
        'tshirt_chart_url',
        'theme',
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

    // Model for getting payment information
    protected $programPaymentModel;

    /**
     * Get program by name
     *
     * @param string $name
     * @return object|null
     */
    public function getProgramByName($name)
    {
        $program = $this->where('name', $name)
            ->where('is_deleted', 0)
            ->first();
            
        // Attach payment information if program exists
        if ($program) {
            $this->attachPaymentFlags($program);
        }
        
        return $program;
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
        
        $program = $builder->get()->getRow();
        
        // Attach payment information if program exists
        if ($program) {
            $this->attachPaymentFlags($program);
        }
        
        return $program;
    }

    /**
     * Get featured programs by program category ID
     *
     * @param int $programCategoryId
     * @return array
     */
    public function getAllPrograms($programCategoryId)
    {
        $programs = $this->where('program_category_id', $programCategoryId)
            ->where('is_deleted', 0)
            ->findAll();

        // Attach payment information to each program
        foreach ($programs as $program) {
            $this->attachPaymentFlags($program);
        }

        return $programs;
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
        $program = $this->where('id', $id)
            ->where('program_category_id', $programCategoryId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->first();

        // Attach payment information if program exists
        if ($program) {
            $this->attachPaymentFlags($program);
        }

        return $program;
    }

    /**
     * Get program by slug with category filter
     *
     * @param string $slug
     * @param int $programCategoryId
     * @return array|null
     */
    public function getProgramBySlugAndCategory($slug, $programCategoryId)
    {
        // Convert slug to program name format (replace hyphens with spaces)
        $programName = str_replace('-', ' ', $slug);
        
        $program = $this->where('name', $programName)
            ->where('program_category_id', $programCategoryId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->first();

        // Attach payment information if program exists
        if ($program) {
            $this->attachPaymentFlags($program);
        }

        return $program;
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

        // Attach payment information to each program
        foreach ($programs as $program) {
            $this->attachPaymentFlags($program);
        }

        return $programs;
    }

    public function getProgramById($id)
    {
        $program = $this->find($id);
        
        // Attach payment information if program exists
        if ($program) {
            $this->attachPaymentFlags($program);
        }
        
        return $program;
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
        
        $builder->select('programs.*, program_categories.web_url AS web_url, program_categories.logo_url AS logo_url')
               ->join('program_categories', 'program_categories.id = programs.program_category_id')
               ->where('programs.program_category_id !=', $categoryId)
               ->where('programs.is_deleted', 0)
               ;
        
        if ($activeOnly) {
            $builder->where('programs.is_active', 1);
        }
        
        $builder->orderBy('created_at', 'DESC');
        
        if ($limit !== null) {
            $builder->limit($limit);
        }
        
        $programs = $builder->get()->getResult();

        // Attach payment information to each program
        foreach ($programs as $program) {
            $this->attachPaymentFlags($program);
        }
        
        return $programs;
    }

    /**
     * Attach payment flags to a program object
     *
     * @param object $program Program object to attach payment info to
     * @return void
     */
    private function attachPaymentFlags($program)
    {
        if (!$program || !isset($program->id)) {
            return;
        }

        // Load ProgramPaymentModel if not already loaded
        if (!isset($this->programPaymentModel)) {
            $this->programPaymentModel = new \App\Models\ProgramPaymentModel();
        }

        // Get payment flags
        $paymentFlags = $this->programPaymentModel->getRegistrationPaymentFlags($program->id);
        
        // Attach to program object
        $program->registration_payments = $paymentFlags;
    }
}
