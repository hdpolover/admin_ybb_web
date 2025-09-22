<?php

namespace App\Models;

use CodeIgniter\Model;

class AnnouncementModel extends Model
{
    protected $table          = 'program_announcements';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'object';
    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $protectFields  = true;
    protected $allowedFields  = [
        'program_id',
        'title',
        'content',
        'img_url',
        'visible_to',
        'is_active',
        'is_deleted',
        'slug',
        'meta_title',
        'meta_description',
        'tags'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Model event callbacks for cache invalidation
    protected $beforeInsert   = [];
    protected $afterInsert    = ['invalidateCacheAfterInsert'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['invalidateCacheAfterUpdate'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = ['invalidateCacheAfterDelete'];

    /**
     * Constructor to load cache helper
     */
    public function __construct()
    {
        parent::__construct();
        helper(['cache']);
    }
    protected $deletedField  = ''; // Not using soft deletes

    // Validation
    protected $validationRules      = [
        'program_id'        => 'required|numeric',
        'title'             => 'required|min_length[3]',
        'content'           => 'required',
        'img_url'           => 'permit_empty',
        'visible_to'        => 'permit_empty',
        'is_active'         => 'permit_empty|in_list[0,1]',
        'is_deleted'        => 'permit_empty|in_list[0,1]',
        'slug'              => 'permit_empty|alpha_dash',
        'meta_title'        => 'permit_empty',
        'meta_description'  => 'permit_empty',
        'tags'              => 'permit_empty'
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // get by program id
    public function getByProgramId($programId, $activeOnly = true, $includeDeleted = false)
    {
        $this->where('program_id', $programId);

        if ($activeOnly) {
            $this->where('is_active', 1);
        }

        if (!$includeDeleted) {
            $this->where('is_deleted', 0);
        }

        $this->orderBy('created_at', 'DESC');

        return $this->findAll();
    }

    /**
     * Get active announcements for a program
     *
     * @param int $programId
     * @param int $limit
     * @return object[]
     */
    public function getActiveAnnouncements($programId, $limit = null)
    {
        $builder = $this->builder();
        $builder->where('program_id', $programId)
               ->where('is_active', 1)
               ->where('is_deleted', 0)
               ->orderBy('created_at', 'DESC');
               
        if ($limit !== null) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResult();
    }

    /**
     * Get announcements by program category
     *
     * @param int $programCategoryId
     * @param int $limit
     * @return object[]
     */
    public function getAnnouncementsByProgramCategory($programCategoryId, $limit = null)
    {
        $builder = $this->builder('program_announcements a');
        $builder->select('a.*')
               ->join('programs p', 'p.id = a.program_id')
               ->where('p.program_category_id', $programCategoryId)
               ->where('a.is_active', 1)
               ->where('a.is_deleted', 0)
               ->orderBy('a.created_at', 'DESC');
               
        if ($limit !== null) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResult();
    }
    
    /**
     * Get paginated announcements by program category
     *
     * @param int $programCategoryId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getPaginatedAnnouncementsByProgramCategory($programCategoryId, $limit = 10, $offset = 0)
    {
        $builder = $this->builder('program_announcements a');
        $builder->select('a.*')
               ->join('programs p', 'p.id = a.program_id')
               ->where('p.program_category_id', $programCategoryId)
               ->where('a.is_active', 1)
               ->where('a.is_deleted', 0);
        
        // Get total count before pagination
        $total = $builder->countAllResults(false);
        
        $builder->orderBy('a.created_at', 'DESC')
               ->limit($limit, $offset);
        
        $data = $builder->get()->getResult();
        
        return [
            'announcements' => $data,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Get announcement by ID with program category filter
     *
     * @param int $id
     * @param int $programCategoryId
     * @return object|null
     */
    public function getAnnouncementByIdAndProgramCategory($id, $programCategoryId)
    {
        $builder = $this->builder('program_announcements a');
        $builder->select('a.*')
               ->join('programs p', 'p.id = a.program_id')
               ->where('a.id', $id)
               ->where('p.program_category_id', $programCategoryId)
               ->where('a.is_active', 1)
               ->where('a.is_deleted', 0);
               
        return $builder->get()->getRow();
    }
    
    /**
     * Get announcement by slug with program category filter
     *
     * @param string $slug
     * @param int $programCategoryId
     * @return object|null
     */
    public function getAnnouncementBySlugAndProgramCategory($slug, $programCategoryId)
    {
        $builder = $this->builder('program_announcements a');
        $builder->select('a.*')
               ->join('programs p', 'p.id = a.program_id')
               ->where('a.slug', $slug)
               ->where('p.program_category_id', $programCategoryId)
               ->where('a.is_active', 1)
               ->where('a.is_deleted', 0);
               
        return $builder->get()->getRow();
    }

    /**
     * Invalidate cache after insert operation
     */
    protected function invalidateCacheAfterInsert(array $data): array
    {
        if (isset($data['id'])) {
            $this->invalidateAnnouncementCaches($data['id']);
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
                $this->invalidateAnnouncementCaches($id);
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
                $this->invalidateAnnouncementCaches($id);
            }
        }
        return $data;
    }

    /**
     * Invalidate all caches related to announcements
     */
    private function invalidateAnnouncementCaches(int $announcementId): void
    {
        try {
            // Ensure cache helper is loaded
            if (!function_exists('invalidate_program_cache')) {
                helper(['cache']);
            }
            
            $cache = \Config\Services::cache();
            
            // Get announcement to find program ID
            $announcement = $this->find($announcementId);
            
            // Clear announcement specific caches
            $cache->delete("announcement_{$announcementId}");
            $cache->delete("announcements_all");
            $cache->delete("announcements_active");
            $cache->delete("announcements_latest");
            
            if ($announcement && isset($announcement->program_id)) {
                $cache->delete("announcements_program_{$announcement->program_id}");
                if (function_exists('invalidate_program_cache')) {
                    invalidate_program_cache($announcement->program_id);
                }
            }
            
            if (function_exists('invalidate_topbar_data_cache')) {
                invalidate_topbar_data_cache();
            }
            
            log_message('info', "AnnouncementModel: Cache invalidated for announcement ID: {$announcementId}");
            
        } catch (\Exception $e) {
            log_message('error', 'AnnouncementModel: Error invalidating cache - ' . $e->getMessage());
        }
    }
}