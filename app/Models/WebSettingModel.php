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
