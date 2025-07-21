<?php

namespace App\Models;

use CodeIgniter\Model;
// import program photo model
use App\Models\ProgramPhotoModel;

class WebSettingModel extends Model
{
    // `id`, `program_category_id`, `is_maintenance_mode`

    protected $table          = 'web_settings';
    protected $primaryKey     = 'id';
    protected $returnType     = 'object'; // Set to return objects
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $protectFields  = true;

    protected $allowedFields  = [
        'program_category_id',
        'is_maintenance_mode',
        'usd_in_idr',
        'is_verification_required',
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
            register_cache_clear_hook($this, 'web_setting');
        }
    }

    // get by program category id
    public function getSettingByProgramCategoryId($programCategoryId)
    {
        // Create a cache key for web settings by program category
        $cacheKey = "web_settings_category_{$programCategoryId}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $webSettings = $cache->get($cacheKey);
        
        if ($webSettings !== null) {
            log_message('info', "WebSettingModel::getSettingByProgramCategoryId - Returning cached settings for category ID: {$programCategoryId}");
            return $webSettings;
        }
        
        // Cache miss - get from database
        log_message('info', "WebSettingModel::getSettingByProgramCategoryId - Cache miss for category ID: {$programCategoryId}");
        $webSettings = $this->where('program_category_id', $programCategoryId)->first();
        
        // Cache for 24 hours (86400 seconds) if settings found
        if ($webSettings) {
            $cache->save($cacheKey, $webSettings, 86400);
        }
        
        return $webSettings;
    }

    // get by program id
    public function getSettingByProgramId($programId)
    {
        // Create a cache key for web settings by program id
        $cacheKey = "web_settings_program_{$programId}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $webSettings = $cache->get($cacheKey);
        
        if ($webSettings !== null) {
            log_message('info', "WebSettingModel::getSettingByProgramId - Returning cached settings for program ID: {$programId}");
            return $webSettings;
        }
        
        // Cache miss - get from database
        log_message('info', "WebSettingModel::getSettingByProgramId - Cache miss for program ID: {$programId}");
        $webSettings = $this->where('program_category_id', $programId)->first();
        
        // Cache for 24 hours (86400 seconds) if settings found
        if ($webSettings) {
            $cache->save($cacheKey, $webSettings, 86400);
        }
        
        return $webSettings;
    }

    /**
     * Get maintenance status by web URL
     * 
     * @param string $webUrl The web URL to check
     * @return object|null Maintenance status and related information
     */
    public function getSettingByWebUrl($webUrl)
    {
        // Create a cache key for web settings by web URL
        $cacheKey = "web_settings_url_" . md5($webUrl);
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $webSetting = $cache->get($cacheKey);
        
        if ($webSetting !== null) {
            log_message('info', "WebSettingModel::getSettingByWebUrl - Returning cached settings for URL: {$webUrl}");
            return $webSetting;
        }
        
        // Cache miss - get from database
        log_message('info', "WebSettingModel::getSettingByWebUrl - Cache miss for URL: {$webUrl}");
        
        // Get the web setting joined with program category and program type
        $webSetting = $this->select('web_settings.*, program_categories.*, program_categories.web_url, program_types.name as program_type_name')
            ->join('program_categories', 'program_categories.id = web_settings.program_category_id')
            ->join('program_types', 'program_types.id = program_categories.program_type_id')
            ->where('program_categories.web_url', $webUrl)
            ->first();

        if ($webSetting) {
            // Get a random photo separately
            $photoModel = new ProgramPhotoModel();
            // Select a random photo from the program photos table
            $randomPhoto = $photoModel->select('img_url')
                ->orderBy('RAND()')
                ->first();

            // Add the random photo to the web setting object
            if ($randomPhoto) {
                $webSetting->img_url = $randomPhoto->img_url;
            }

            // Add is_journal_type flag by checking program type name
            $webSetting->is_journal_type = strtolower($webSetting->program_type_name) === 'journal';
            
            // Cache for 24 hours (86400 seconds)
            $cache->save($cacheKey, $webSetting, 86400);
        }

        return $webSetting;
    }

    /**
     * Get all web settings
     * 
     * @return array|null List of web settings or null
     */
    public function getAllSettings()
    {
        // Create a cache key for all web settings
        $cacheKey = "web_settings_all";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $allSettings = $cache->get($cacheKey);
        
        if ($allSettings !== null) {
            log_message('info', "WebSettingModel::getAllSettings - Returning cached settings");
            return $allSettings;
        }
        
        // Cache miss - get from database
        log_message('info', "WebSettingModel::getAllSettings - Cache miss, fetching all settings");
        $allSettings = $this->findAll();
        
        // Cache for 24 hours (86400 seconds) if settings found
        if ($allSettings) {
            $cache->save($cacheKey, $allSettings, 86400);
        }
        
        return $allSettings;
    }
}
