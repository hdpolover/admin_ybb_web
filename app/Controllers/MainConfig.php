<?php

namespace App\Controllers;

use App\Models\WebSettingModel;
use App\Traits\Cacheable;

class MainConfig extends AdminBaseController
{
    use Cacheable;
    
    protected $webSettingModel;

    public function __construct()
    {
        $this->webSettingModel = new WebSettingModel();
    }
    public function index()
    {
        log_message('debug', 'MainConfig::index - Starting to load main config page');

        // Get current program ID from session
        $programId = session('current_program');
        log_message('debug', 'MainConfig::index - Program ID from session: ' . $programId);

        // Load program model to get is_registration_open
        $programModel = new \App\Models\ProgramModel();
        $program = $programModel->find($programId);

        if ($program) {
            log_message('debug', 'MainConfig::index - Program found: ' . json_encode($program));
        } else {
            log_message('warning', 'MainConfig::index - Program not found for ID: ' . $programId);
        }

        // Get current settings for this program
        $settings = $this->webSettingModel->getSettingByProgramCategoryId($program->program_category_id);
        log_message('debug', 'MainConfig::index - Settings: ' . ($settings ? json_encode($settings) : 'Not found'));

        // If settings don't exist yet, create default settings
        if (!$settings) {
            $data = [
                'program_category_id' => $program->program_category_id,
                'is_maintenance_mode' => 0,
                'is_verification_required' => 0,
                'usd_in_idr' => 15000,
            ];

            $this->webSettingModel->insert($data);
            $settings = $this->webSettingModel->getSettingByProgramCategoryId($program->program_category_id);
        }

        // Get topbar data from session (already loaded by AdminBaseController)
        $topbarData = $this->session->get('topbar_data', []);

        $data = [
            'title' => 'Main Configuration',
            'settings' => $settings,
            'program' => $program,
            'topbarData' => $topbarData
        ];

        return view('settings/main-config/index', $data);
    }

    public function update($id)
    {
        log_message('debug', 'MainConfig::update - Starting update method with ID: ' . $id);
        log_message('debug', 'MainConfig::update - POST data received: ' . json_encode($this->request->getPost()));        // Validate form data
        $rules = [
            'is_maintenance_mode' => 'permit_empty|in_list[0,1]',
            'is_verification_required' => 'permit_empty|in_list[0,1]',
            'usd_in_idr' => 'permit_empty|numeric',
            'is_registration_open' => 'permit_empty|in_list[0,1]',
            'is_active' => 'permit_empty|in_list[0,1]',
        ];
        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            log_message('debug', 'MainConfig::update - Validation failed: ' . json_encode($errors));
            $session = session();
            $session->setFlashdata('swalError', 'Failed to update settings: ' . implode(', ', $errors));
            return redirect()->to('/settings/main-config');
        }

        log_message('debug', 'MainConfig::update - Form validation passed');

        // Get current program ID from session
        $programId = session('current_program');

        // Get current settings
        $settings = $this->webSettingModel->find($id);
        if (!$settings) {
            $session = session();
            $session->setFlashdata('swalError', 'Settings not found or you do not have permission to update them');
            return redirect()->to('/settings/main-config');
        }

        // Load program model to update is_registration_open
        $programModel = new \App\Models\ProgramModel();
        $program = $programModel->find($programId);
        if (!$program) {
            $session = session();
            $session->setFlashdata('swalError', 'Program not found');
            return redirect()->to('/settings/main-config');
        }

        // Debug log - Start of update process
        log_message('debug', 'MainConfig::update - Starting update process. ID: ' . $id . ', Program ID: ' . $programId);
        log_message('debug', 'MainConfig::update - Form data: ' . json_encode($this->request->getPost()));        // Update program registration status and active status
        $is_registration_open = $this->request->getPost('is_registration_open') ? 1 : 0;
        $is_active = $this->request->getPost('is_active') ? 1 : 0;
        log_message('debug', 'MainConfig::update - Registration status: ' . $is_registration_open);
        log_message('debug', 'MainConfig::update - Active status: ' . $is_active);

        $programModel->update($programId, [
            'is_registration_open' => $is_registration_open,
            'is_active' => $is_active,
        ]);

        // Prepare data for web settings update
        $data = [
            'is_maintenance_mode' => $this->request->getPost('is_maintenance_mode') ? 1 : 0,
            'is_verification_required' => $this->request->getPost('is_verification_required') ? 1 : 0,
        ];

        // Only update usd_in_idr if it's provided (handle empty string case)
        $usdInIdr = $this->request->getPost('usd_in_idr');
        log_message('debug', 'MainConfig::update - USD in IDR raw value: "' . $usdInIdr . '", Type: ' . gettype($usdInIdr));

        if ($usdInIdr !== '' && $usdInIdr !== null) {
            $data['usd_in_idr'] = $usdInIdr;
            log_message('debug', 'MainConfig::update - Using submitted USD in IDR value: ' . $usdInIdr);
        } else {
            // If empty, keep the current value
            $data['usd_in_idr'] = $settings->usd_in_idr;
            log_message('debug', 'MainConfig::update - Using existing USD in IDR value: ' . $settings->usd_in_idr);
        }

        log_message('debug', 'MainConfig::update - Data to update: ' . json_encode($data));        // Update settings
        log_message('debug', 'MainConfig::update - Attempting to update settings with ID: ' . $id);

        try {
            $updateResult = $this->webSettingModel->update($id, $data);
            log_message('debug', 'MainConfig::update - Update result: ' . ($updateResult ? 'Success' : 'Failure'));

            if ($updateResult) {
                // Invalidate web settings cache after successful update
                $this->invalidateWebSettingsCache($settings->program_category_id);
                
                $session = session();
                $session->setFlashdata('swalSuccess', 'Settings updated successfully');
                log_message('debug', 'MainConfig::update - Success message set in flashdata');
                return redirect()->to('/settings/main-config');
            } else {
                $session = session();
                $session->setFlashdata('swalError', 'Failed to update settings');
                log_message('debug', 'MainConfig::update - Error message set in flashdata: Update failed');
                return redirect()->to('/settings/main-config');
            }
        } catch (\Exception $e) {
            log_message('error', 'MainConfig::update - Exception: ' . $e->getMessage());
            $session = session();
            $session->setFlashdata('swalError', 'Error: ' . $e->getMessage());
            return redirect()->to('/settings/main-config');
        }
    }

    /**
     * Invalidate all web settings related caches
     * 
     * @param int $programCategoryId The program category ID
     * @return bool Success status
     */
    private function invalidateWebSettingsCache(int $programCategoryId): bool
    {
        try {
            $cache = \Config\Services::cache();
            $deletedCount = 0;
            
            // 1. Clear basic CodeIgniter cache keys
            $keysToDelete = [
                "web_settings_category_{$programCategoryId}",
                "web_settings_program_{$programCategoryId}",
                "web_settings_all"
            ];
            
            foreach ($keysToDelete as $key) {
                if ($cache->delete($key)) {
                    $deletedCount++;
                    log_message('info', "MainConfig: Basic cache cleared - {$key}");
                }
            }
            
            // 2. Clear API controller Redis cache keys
            $redisCacheService = new \App\Services\RedisCacheService();
            
            if ($redisCacheService->isCacheAvailable()) {
                // Get all program categories to clear their URL-specific API caches
                $programCategoryModel = new \App\Models\ProgramCategoryModel();
                $allCategories = $programCategoryModel->findAll();
                
                foreach ($allCategories as $category) {
                    if (!empty($category->web_url)) {
                        // Generate the API cache key for this specific URL
                        // Use the full API endpoint path including /api/ prefix
                        $endpoint = 'api/web-settings';
                        $parameters = ['url' => $category->web_url];
                        $apiCacheKey = $redisCacheService->generateKey($endpoint, $parameters);
                        
                        if ($redisCacheService->delete($apiCacheKey)) {
                            $deletedCount++;
                            log_message('info', "MainConfig: API cache cleared - {$apiCacheKey} for URL: {$category->web_url}");
                        }
                    }
                }
                
                // Also clear the general web-settings API cache (without URL parameter)
                $generalApiCacheKey = $redisCacheService->generateKey('api/web-settings', []);
                if ($redisCacheService->delete($generalApiCacheKey)) {
                    $deletedCount++;
                    log_message('info', "MainConfig: General API cache cleared - {$generalApiCacheKey}");
                }
            } else {
                log_message('warning', 'MainConfig: Redis cache unavailable for API cache invalidation');
            }
            
            // 3. Clear URL-based caches (for backwards compatibility)
            $programCategoryModel = new \App\Models\ProgramCategoryModel();
            $allCategories = $programCategoryModel->findAll();
            
            foreach ($allCategories as $category) {
                if (!empty($category->web_url)) {
                    $urlCacheKey = "web_settings_url_" . md5($category->web_url);
                    if ($cache->delete($urlCacheKey)) {
                        $deletedCount++;
                        log_message('info', "MainConfig: URL cache cleared - {$urlCacheKey} for URL: {$category->web_url}");
                    }
                }
            }
            
            log_message('info', "MainConfig: Cache invalidation complete - Program Category ID: {$programCategoryId}, Keys deleted: {$deletedCount}");
            
            return true;
            
        } catch (\Exception $e) {
            log_message('error', 'MainConfig: Error invalidating web settings caches - ' . $e->getMessage());
            // Don't throw exception - cache invalidation failure shouldn't break the settings update
            return false;
        }
    }

    public function root($path = '')
    {
        if ($path !== '') {
            if (@file_exists(APPPATH . 'Views/' . $path . '.php')) {
                return view($path);
            } else {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
        } else {
            echo 'Page Not Found.';
        }
    }
}
