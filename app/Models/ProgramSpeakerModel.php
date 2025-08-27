<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramSpeakerModel extends Model
{
    protected $table = 'program_speakers';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    
    protected $useAutoIncrement = true;
    
    protected $allowedFields = [
        'program_id',
        'name',
        'title',
        'bio',
        'photo_url',
        'linkedin_url',
        'instagram_url',
        'email',
        'organization',
        'expertise_areas',
        'is_keynote',
        'session_title',
        'session_description',
        'session_time',
        'order_number',
        'is_active',
        'is_deleted',
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'program_id' => 'required|integer',
        'name' => 'required|max_length[255]',
        'title' => 'permit_empty|max_length[255]',
        'bio' => 'permit_empty',
        'photo_url' => 'permit_empty|max_length[500]',
        'linkedin_url' => 'permit_empty|max_length[500]|valid_url',
        'instagram_url' => 'permit_empty|max_length[500]|valid_url',
        'email' => 'permit_empty|valid_email|max_length[255]',
        'organization' => 'permit_empty|max_length[255]',
        'expertise_areas' => 'permit_empty',
        'is_keynote' => 'permit_empty|in_list[0,1]',
        'session_title' => 'permit_empty|max_length[500]',
        'session_description' => 'permit_empty',
        'session_time' => 'permit_empty|valid_date',
        'order_number' => 'permit_empty|integer',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];
    
    protected $validationMessages = [
        'program_id' => [
            'required' => 'Program ID is required',
            'integer' => 'Program ID must be a valid integer'
        ],
        'name' => [
            'required' => 'Speaker name is required',
            'max_length' => 'Speaker name cannot exceed 255 characters'
        ],
        'email' => [
            'valid_email' => 'Please provide a valid email address'
        ],
        'linkedin_url' => [
            'valid_url' => 'Please provide a valid LinkedIn URL'
        ],
        'twitter_url' => [
            'valid_url' => 'Please provide a valid Twitter URL'
        ]
    ];
    
    /**
     * Get speakers by program ID
     *
     * @param int $programId
     * @param bool $activeOnly
     * @return array
     */
    public function getByProgramId($programId, $activeOnly = true)
    {
        $builder = $this->builder();
        
        $builder->where('program_id', $programId)
                ->where('is_deleted', 0);
        
        if ($activeOnly) {
            $builder->where('is_active', 1);
        }
        
        return $builder->orderBy('is_keynote', 'DESC')
                      ->orderBy('order_number', 'ASC')
                      ->orderBy('name', 'ASC')
                      ->get()
                      ->getResult();
    }
    
    /**
     * Get keynote speakers for a program
     *
     * @param int $programId
     * @return array
     */
    public function getKeynoteSpeakers($programId)
    {
        return $this->where('program_id', $programId)
                   ->where('is_keynote', 1)
                   ->where('is_active', 1)
                   ->where('is_deleted', 0)
                   ->orderBy('order_number', 'ASC')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get regular speakers for a program
     *
     * @param int $programId
     * @return array
     */
    public function getRegularSpeakers($programId)
    {
        return $this->where('program_id', $programId)
                   ->where('is_keynote', 0)
                   ->where('is_active', 1)
                   ->where('is_deleted', 0)
                   ->orderBy('order_number', 'ASC')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get speaker statistics for a program
     *
     * @param int $programId
     * @return object
     */
    public function getSpeakerStats($programId)
    {
        // Get total speakers
        $total = $this->where('program_id', $programId)
                     ->where('is_deleted', 0)
                     ->countAllResults();
        
        // Get active speakers
        $active = $this->where('program_id', $programId)
                      ->where('is_active', 1)
                      ->where('is_deleted', 0)
                      ->countAllResults();
        
        // Get keynote speakers
        $keynote = $this->where('program_id', $programId)
                       ->where('is_keynote', 1)
                       ->where('is_deleted', 0)
                       ->countAllResults();
        
        // Get speakers with sessions
        $withSessions = $this->where('program_id', $programId)
                            ->where('session_title IS NOT NULL')
                            ->where('session_title !=', '')
                            ->where('is_deleted', 0)
                            ->countAllResults();
        
        return (object) [
            'total' => $total,
            'active' => $active,
            'keynote' => $keynote,
            'regular' => $total - $keynote,
            'with_sessions' => $withSessions
        ];
    }
    
    /**
     * Update speaker order
     *
     * @param array $orderData Array of ['id' => order_number]
     * @return bool
     */
    public function updateSpeakerOrder($orderData)
    {
        $this->db->transStart();
        
        foreach ($orderData as $speakerId => $orderNumber) {
            $this->update($speakerId, ['order_number' => $orderNumber]);
        }
        
        $this->db->transComplete();
        
        return $this->db->transStatus();
    }
    
    /**
     * Get next order number for a program
     *
     * @param int $programId
     * @param bool $isKeynote
     * @return int
     */
    public function getNextOrderNumber($programId, $isKeynote = false)
    {
        $builder = $this->builder();
        
        $result = $builder->selectMax('order_number')
                         ->where('program_id', $programId)
                         ->where('is_keynote', $isKeynote ? 1 : 0)
                         ->where('is_deleted', 0)
                         ->get()
                         ->getRow();
        
        return ($result->order_number ?? 0) + 1;
    }
    
    /**
     * Soft delete speaker
     *
     * @param int $id
     * @return bool
     */
    public function softDelete($id)
    {
        return $this->update($id, ['is_deleted' => 1]);
    }
    
    /**
     * Get speakers for export
     *
     * @param int $programId
     * @return array
     */
    public function getSpeakersForExport($programId)
    {
        return $this->select('
                program_speakers.*,
                CASE WHEN is_keynote = 1 THEN "Keynote" ELSE "Regular" END as speaker_type
            ')
            ->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->orderBy('is_keynote', 'DESC')
            ->orderBy('order_number', 'ASC')
            ->findAll();
    }
    
    /**
     * Search speakers
     *
     * @param int $programId
     * @param string $search
     * @param array $filters
     * @return array
     */
    public function searchSpeakers($programId, $search = '', $filters = [])
    {
        $builder = $this->builder();
        
        $builder->where('program_id', $programId)
               ->where('is_deleted', 0);
        
        if (!empty($search)) {
            $builder->groupStart()
                   ->like('name', $search)
                   ->orLike('title', $search)
                   ->orLike('organization', $search)
                   ->orLike('session_title', $search)
                   ->groupEnd();
        }
        
        // Apply filters
        if (isset($filters['is_keynote']) && $filters['is_keynote'] !== '') {
            $builder->where('is_keynote', $filters['is_keynote']);
        }
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $builder->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['has_session']) && $filters['has_session'] !== '') {
            if ($filters['has_session'] == '1') {
                $builder->where('session_title IS NOT NULL')
                       ->where('session_title !=', '');
            } else {
                $builder->groupStart()
                       ->where('session_title IS NULL')
                       ->orWhere('session_title', '')
                       ->groupEnd();
            }
        }
        
        return $builder->orderBy('is_keynote', 'DESC')
                      ->orderBy('order_number', 'ASC')
                      ->orderBy('name', 'ASC')
                      ->get()
                      ->getResult();
    }
    
    /**
     * Cache management - invalidate speaker-related caches
     *
     * @param int $programId
     * @return void
     */
    protected function invalidateSpeakerCache($programId)
    {
        $cache = \Config\Services::cache();
        
        // Clear speaker-related cache keys
        $cacheKeys = [
            "program_speakers_{$programId}",
            "keynote_speakers_{$programId}",
            "speaker_stats_{$programId}",
            "topbar_data_" . (session('userId') ?? 'guest')
        ];
        
        foreach ($cacheKeys as $key) {
            $cache->delete($key);
        }
    }
    
    /**
     * After insert callback
     */
    protected function afterInsert(array $data)
    {
        if (isset($data['data']['program_id'])) {
            $this->invalidateSpeakerCache($data['data']['program_id']);
        }
        
        return $data;
    }
    
    /**
     * After update callback
     */
    protected function afterUpdate(array $data)
    {
        if (isset($data['id'])) {
            $speaker = $this->find($data['id']);
            if ($speaker) {
                $this->invalidateSpeakerCache($speaker->program_id);
            }
        }
        
        return $data;
    }
    
    /**
     * After delete callback
     */
    protected function afterDelete(array $data)
    {
        if (isset($data['id'])) {
            $speaker = $this->find($data['id']);
            if ($speaker) {
                $this->invalidateSpeakerCache($speaker->program_id);
            }
        }
        
        return $data;
    }
}