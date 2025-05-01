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

    // get by program category id
    public function getSettingByProgramCategoryId($programCategoryId)
    {
        return $this->where('program_category_id', $programCategoryId)->first();
    }

    // get by program id
    public function getSettingByProgramId($programId)
    {
        return $this->where('program_category_id', $programId)->first();
    }

    /**
     * Get maintenance status by web URL
     * 
     * @param string $webUrl The web URL to check
     * @return object|null Maintenance status and related information
     */
    public function getSettingByWebUrl($webUrl)
    {   
        // Get the web setting joined with program category
        $webSetting = $this->select('web_settings.*, program_categories.*, program_categories.web_url')
            ->join('program_categories', 'program_categories.id = web_settings.program_category_id')
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
        return $this->findAll();
    }
}
