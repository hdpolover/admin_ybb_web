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
        'description',
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
    protected $deletedField  = ''; // Not using soft deletes

    // Validation
    protected $validationRules      = [
        'program_id'        => 'required|numeric',
        'title'             => 'required|min_length[3]',
        'description'       => 'required',
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
}