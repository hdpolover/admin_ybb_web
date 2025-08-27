<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramVideoTestimonyModel extends Model
{
    protected $table          = 'program_video_testimonies';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'object';
    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $protectFields  = true;
    protected $allowedFields  = [
        'program_id',
        'youtube_url',
        'youtube_video_id',
        'description',
        'display_order',
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
        'program_id'      => 'required|numeric',
        'youtube_url'     => 'required|valid_url',
        'description'     => 'permit_empty',
        'display_order'   => 'permit_empty|numeric',
        'is_active'       => 'permit_empty|in_list[0,1]',
        'is_deleted'      => 'permit_empty|in_list[0,1]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get active video testimonies for a program
     *
     * @param int $programId
     * @return object[]
     */
    public function getActiveVideoTestimonies($programId)
    {
        return $this->where('program_id', $programId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->orderBy('display_order', 'ASC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get active video testimonies for a program category
     *
     * @param int $programCategoryId
     * @return object[]
     */
    public function getActiveVideoTestimoniesByCategory($programCategoryId)
    {
        return $this->select('program_video_testimonies.*')
                    ->join('programs', 'programs.id = program_video_testimonies.program_id')
                    ->where('programs.program_category_id', $programCategoryId)
                    ->where('program_video_testimonies.is_active', 1)
                    ->where('program_video_testimonies.is_deleted', 0)
                    ->where('programs.is_deleted', 0)
                    ->orderBy('programs.id', 'ASC')
                    ->orderBy('program_video_testimonies.display_order', 'ASC')
                    ->orderBy('program_video_testimonies.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get all video testimonies for a program category (including inactive videos from active programs)
     *
     * @param int $programCategoryId
     * @return object[]
     */
    public function getAllVideoTestimoniesByCategory($programCategoryId)
    {
        return $this->select('program_video_testimonies.*')
                    ->join('programs', 'programs.id = program_video_testimonies.program_id')
                    ->where('programs.program_category_id', $programCategoryId)
                    ->where('program_video_testimonies.is_deleted', 0)
                    ->where('programs.is_deleted', 0)
                    ->orderBy('programs.id', 'ASC')
                    ->orderBy('program_video_testimonies.display_order', 'ASC')
                    ->orderBy('program_video_testimonies.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get all video testimonies for a program (including inactive)
     *
     * @param int $programId
     * @return object[]
     */
    public function getAllVideoTestimoniesForProgram($programId)
    {
        return $this->where('program_id', $programId)
                    ->where('is_deleted', 0)
                    ->orderBy('display_order', 'ASC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Extract YouTube video ID from URL
     *
     * @param string $url
     * @return string|null
     */
    public function extractYouTubeVideoId($url)
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Generate YouTube thumbnail URL from video ID
     *
     * @param string $videoId
     * @return string
     */
    public function generateYouTubeThumbnail($videoId)
    {
        return "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
    }

    /**
     * Generate YouTube embed URL
     *
     * @param string $videoId
     * @return string
     */
    public function generateYouTubeEmbedUrl($videoId)
    {
        return "https://www.youtube.com/embed/{$videoId}?rel=0&modestbranding=1&controls=1";
    }

    /**
     * Before insert callback to extract video ID
     */
    protected function beforeInsert(array $data)
    {
        if (isset($data['data']['youtube_url'])) {
            $videoId = $this->extractYouTubeVideoId($data['data']['youtube_url']);
            if ($videoId) {
                $data['data']['youtube_video_id'] = $videoId;
            }
        }
        
        return $data;
    }

    /**
     * Before update callback to extract video ID
     */
    protected function beforeUpdate(array $data)
    {
        if (isset($data['data']['youtube_url'])) {
            $videoId = $this->extractYouTubeVideoId($data['data']['youtube_url']);
            if ($videoId) {
                $data['data']['youtube_video_id'] = $videoId;
            }
        }
        
        return $data;
    }

    /**
     * Get next display order for a program
     *
     * @param int $programId
     * @return int
     */
    public function getNextDisplayOrder($programId)
    {
        $maxOrder = $this->where('program_id', $programId)
                         ->where('is_deleted', 0)
                         ->selectMax('display_order')
                         ->first();
        
        return ($maxOrder->display_order ?? 0) + 1;
    }

    /**
     * Update display orders for multiple records
     *
     * @param array $orders Array of ['id' => order] pairs
     * @return bool
     */
    public function updateDisplayOrders($orders)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        foreach ($orders as $id => $order) {
            $this->update($id, ['display_order' => $order]);
        }
        
        $db->transComplete();
        
        return $db->transStatus();
    }
}