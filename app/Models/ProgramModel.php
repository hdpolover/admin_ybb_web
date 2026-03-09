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

    // Model event callbacks for cache invalidation
    protected $beforeInsert   = [];
    protected $afterInsert    = ['invalidateCacheAfterInsert'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['invalidateCacheAfterUpdate'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = ['invalidateCacheAfterDelete'];

    // Model for getting payment information
    protected $programPaymentModel;

    /**
     * Constructor to load cache helper
     */
    public function __construct()
    {
        parent::__construct();
        helper(['cache']);
    }

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
        $currentDate = date('Y-m-d');
        $builder = $this->builder();
        $builder->select('*')
            ->where('program_category_id', $programCategoryId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->groupStart() // Add logic to only show programs that have not ended yet OR have no end date
                ->where('end_date >=', $currentDate)
                ->orWhere('end_date', null)
                ->orWhere('end_date', '0000-00-00')
            ->groupEnd();
        
        $programs = $builder->get()->getResult();
        
        // Attach payment information if programs exist
        if ($programs) {
            foreach ($programs as $program) {
                $this->attachPaymentFlags($program);
            }
        }
        
        return $programs;
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

    /**
     * Get previous (inactive) programs by category
     *
     * @param string|null $webUrl Web URL to match category
     * @param int|null $categoryId Program category ID
     * @return array
     */
    public function getPreviousPrograms($webUrl = null, $categoryId = null)
    {
        $currentDate = date('Y-m-d');
        
        $builder = $this->db->table($this->table);
        $builder->select('programs.*, program_categories.web_url as category_web_url');
        $builder->join('program_categories', 'program_categories.id = programs.program_category_id', 'left');
        $builder->where('programs.is_deleted', 0);
        
        // Show as previous if:
        // 1. Manually set to inactive (is_active = 0)
        // 2. OR active but end date has passed
        $builder->groupStart()
            ->where('programs.is_active', 0)
            ->orGroupStart()
                ->where('programs.is_active', 1)
                ->where('programs.end_date <', $currentDate)
                ->where('programs.end_date IS NOT NULL')
                ->where('programs.end_date !=', '0000-00-00')
            ->groupEnd()
        ->groupEnd();
        
        // Filter by category
        if ($webUrl) {
            $builder->where('program_categories.web_url', $webUrl);
        } elseif ($categoryId) {
            $builder->where('programs.program_category_id', $categoryId);
        }
        
        $builder->orderBy('programs.start_date', 'DESC');
        
        $query = $builder->get();
        $programs = $query->getResult();
        
        // Attach payment flags to each program
        foreach ($programs as $program) {
            $this->attachPaymentFlags($program);
        }
        
        return $programs;
    }

    /**
     * Invalidate cache after insert operation
     */
    protected function invalidateCacheAfterInsert(array $data): array
    {
        if (isset($data['id'])) {
            $this->invalidateProgramCaches($data['id']);
        }
        return $data;
    }

    /**
     * Invalidate cache after update operation
     */
    protected function invalidateCacheAfterUpdate(array $data): array
    {
        if (isset($data['id'])) {
            $ids = is_array($data['id']) ? $data['id'] : [$data['id']];
            foreach ($ids as $id) {
                $this->invalidateProgramCaches($id);
            }
        }
        return $data;
    }

    /**
     * Invalidate cache after delete operation
     */
    protected function invalidateCacheAfterDelete(array $data): array
    {
        if (isset($data['id'])) {
            $ids = is_array($data['id']) ? $data['id'] : [$data['id']];
            foreach ($ids as $id) {
                $this->invalidateProgramCaches($id);
            }
        }
        return $data;
    }

    /**
     * Invalidate all caches related to a specific program
     */
    private function invalidateProgramCaches(int $programId): void
    {
        try {
            // Ensure cache helper is loaded
            if (!function_exists('invalidate_program_cache')) {
                helper(['cache']);
            }
            
            // Use helper functions to invalidate caches
            if (function_exists('invalidate_program_cache')) {
                invalidate_program_cache($programId);
            }
            
            // Get program to find category ID
            $program = $this->find($programId);
            if ($program && isset($program->program_category_id)) {
                if (function_exists('invalidate_program_category_cache')) {
                    invalidate_program_category_cache($program->program_category_id);
                }
                if (function_exists('invalidate_web_settings_cache')) {
                    invalidate_web_settings_cache($program->program_category_id);
                }
            }
            
            // Invalidate dashboard and topbar caches
            if (function_exists('invalidate_dashboard_cache')) {
                invalidate_dashboard_cache($programId);
            }
            if (function_exists('invalidate_topbar_data_cache')) {
                invalidate_topbar_data_cache();
            }
            
            log_message('info', "ProgramModel: Cache invalidated for program ID: {$programId}");
            
        } catch (\Exception $e) {
            log_message('error', 'ProgramModel: Error invalidating cache - ' . $e->getMessage());
        }
    }
}
