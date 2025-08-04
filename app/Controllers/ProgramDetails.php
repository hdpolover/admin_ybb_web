<?php

namespace App\Controllers;

use App\Models\ProgramModel;
use App\Models\ProgramCategoryModel;
use App\Models\ProgramTypeModel;
use App\Traits\Cacheable;

class ProgramDetails extends BaseController
{
    use Cacheable;
    
    protected $programModel;
    protected $programCategoryModel;
    protected $programTypeModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
        $this->programCategoryModel = new ProgramCategoryModel();
        $this->programTypeModel = new ProgramTypeModel();
        
        // Initialize request for cache trait
        $this->request = \Config\Services::request();
    }

    public function index()
    {
        // get program id from session
        $programId = session()->get('current_program');

        // get current program details
        $currentProgram = $this->programModel->find($programId);

        // get the one program category for the current program
        $programCategory = $this->programCategoryModel->where('id', $currentProgram->program_category_id)->first();

        // get program types
        $programTypes = $this->programTypeModel->findAll();

        $data = [
            'title' => 'Program Details',
            'currentProgram' => $currentProgram,
            'currentProgramCategory' => $programCategory,
            'programTypes' => $programTypes,
        ];

        return view('master-data/program-details/index', $data);
    }

    // edit
    public function edit($id)
    {
        $program = $this->programModel->find($id);

        if (!$program) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Program not found');
        }

        $data = [
            'title' => 'Edit Program',
            'program' => $program,
        ];

        return view('master-data/program-details/edit', $data);
    }    
    
    // update program category details
    public function updateCategoryDetails($id)
    {
        // Check if this is an AJAX request
        if ($this->request->isAJAX()) {
            // Log the received data for debugging
            log_message('debug', 'Updating category details for ID ' . $id);
            log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));
            
            // Load the storage helper
            helper(['storage']);

            // Validate request - only require the name field, all others are optional
            $rules = [
                'name' => 'required',
                'email' => 'permit_empty|valid_email',
            ];

            // No URL validation - accept any user input for URLs
            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Get program category
            $programCategory = $this->programCategoryModel->find($id);

            if (!$programCategory) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Program category not found'
                ]);
            }
            
            // Update data - collect all fields from the form
            $data = [
                'name' => $this->request->getPost('name'),
                'program_type_id' => $this->request->getPost('program_type_id'),
                'tagline' => $this->request->getPost('tagline'),
                'description' => $this->request->getPost('description'),
                'about' => $this->request->getPost('about'),
                'core_values' => $this->request->getPost('core_values'),
                'objectives' => $this->request->getPost('objectives'),
                'benefits' => $this->request->getPost('benefits'),
                'contact' => $this->request->getPost('contact'),
                'email' => $this->request->getPost('email'),
                'location' => $this->request->getPost('location'),
                'web_url' => $this->request->getPost('web_url'),
                'instagram' => $this->request->getPost('instagram'),
                'tiktok' => $this->request->getPost('tiktok'),
                'youtube' => $this->request->getPost('youtube'),
                'telegram' => $this->request->getPost('telegram'),
                'sponsor_url' => $this->request->getPost('sponsor_url'),
                'main_video_url' => $this->request->getPost('main_video_url'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Handle logo image upload if present
            $logoFile = $this->request->getFile('logo');
            if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
                // Prepare file for upload_file_to_storage
                $fileData = [
                    'name' => $logoFile->getName(),
                    'tmp_name' => $logoFile->getTempName(),
                    'type' => $logoFile->getClientMimeType(),
                    'size' => $logoFile->getSize(),
                    'error' => 0
                ];

                // Upload to storage server
                $uploadResult = upload_file_to_storage(
                    $fileData,
                    'program-categories/' . $id . '/images',
                    'logo_' . time() . '.' . $logoFile->getExtension(),
                    [] // No restriction on MIME types
                );

                if ($uploadResult['status']) {
                    $data['logo_url'] = $uploadResult['url'];
                }
            }

            // Handle main banner image upload if present
            $bannerFile = $this->request->getFile('main_banner');
            if ($bannerFile && $bannerFile->isValid() && !$bannerFile->hasMoved()) {
                // Prepare file for upload_file_to_storage
                $fileData = [
                    'name' => $bannerFile->getName(),
                    'tmp_name' => $bannerFile->getTempName(),
                    'type' => $bannerFile->getClientMimeType(),
                    'size' => $bannerFile->getSize(),
                    'error' => 0
                ];

                // Upload to storage server
                $uploadResult = upload_file_to_storage(
                    $fileData,
                    'program-categories/' . $id . '/images',
                    'banner_' . time() . '.' . $bannerFile->getExtension(),
                    [] // No restriction on MIME types
                );

                if ($uploadResult['status']) {
                    $data['main_banner_url'] = $uploadResult['url'];
                }
            }            // Save data
            if ($this->programCategoryModel->update($id, $data)) {
                // Invalidate related caches after successful update
                $this->invalidateRelatedCaches($id);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Program category updated successfully'
                ]);
            } else {
                // Log the errors for debugging
                log_message('error', 'Failed to update program category with errors: ' . json_encode($this->programCategoryModel->errors()));
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update program category',
                    'errors' => $this->programCategoryModel->errors()
                ]);
            }
        }

        // If not AJAX request, redirect with message
        return redirect()->to('/program-details')->with('message', 'Invalid request method');
    }

    // Update specific program details via AJAX
    public function updateProgramDetails($id)
    {
        // Check if this is an AJAX request
        if ($this->request->isAJAX()) {
            // Load the storage helper
            helper(['storage']);

            // Validate request
            $rules = [
                'name' => 'required',
                'description' => 'permit_empty',
                'theme' => 'permit_empty',
                'start_date' => 'permit_empty|valid_date[Y-m-d]',
                'end_date' => 'permit_empty|valid_date[Y-m-d]',
                'email' => 'permit_empty|valid_email',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Get program
            $program = $this->programModel->find($id);

            if (!$program) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Program not found'
                ]);
            }

            // Update data with all form fields
            $data = [
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'theme' => $this->request->getPost('theme'),
                'start_date' => $this->request->getPost('start_date') ?: null,
                'end_date' => $this->request->getPost('end_date') ?: null,
                'guideline' => $this->request->getPost('guideline'),
                'main_essay_question' => $this->request->getPost('main_essay_question'),
                'essay_guideline_url' => $this->request->getPost('essay_guideline_url'),
                'twibbon' => $this->request->getPost('twibbon'),
                'twibbon_video_url' => $this->request->getPost('twibbon_video_url'),
                'registration_video_url' => $this->request->getPost('registration_video_url'),
                'tshirt_chart_url' => $this->request->getPost('tshirt_chart_url'),
                'share_desc' => $this->request->getPost('share_desc'),
                'confirmation_desc' => $this->request->getPost('confirmation_desc'),
                'is_active' => $this->request->getPost('is_active') !== null ? $this->request->getPost('is_active') : 0,
                'is_registration_open' => $this->request->getPost('is_registration_open') !== null ? $this->request->getPost('is_registration_open') : 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Handle program image upload if present
            $imageFile = $this->request->getFile('banner');
            if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
                $fileData = [
                    'name' => $imageFile->getName(),
                    'tmp_name' => $imageFile->getTempName(),
                    'type' => $imageFile->getClientMimeType(),
                    'size' => $imageFile->getSize(),
                    'error' => 0
                ];

                $uploadResult = upload_file_to_storage(
                    $fileData,
                    'programs/' . $id . '/images',
                    'banner_' . time() . '.' . $imageFile->getExtension(),
                    []
                );

                // Debug upload result
                log_message('debug', 'Banner upload result: ' . json_encode($uploadResult));

                if ($uploadResult['status']) {
                    $data['banner_url'] = $uploadResult['url'];
                    log_message('debug', 'Setting banner_url to: ' . $uploadResult['url']);
                } else {
                    log_message('error', 'Failed to upload banner: ' . ($uploadResult['message'] ?? 'Unknown error'));
                }
            } else {
                if ($imageFile) {
                    log_message('debug', 'Banner file validation failed: Valid=' . ($imageFile->isValid() ? 'true' : 'false') . ', HasMoved=' . ($imageFile->hasMoved() ? 'true' : 'false'));
                } else {
                    log_message('debug', 'No banner file submitted');
                }
            }            // Debug the data being saved
            log_message('debug', 'Data to be updated for program ID ' . $id . ': ' . json_encode($data));

            // Save data
            if ($this->programModel->update($id, $data)) {
                // Invalidate related caches after successful update
                $this->invalidateProgramSpecificCaches($id);
                
                // Verify the update by retrieving the updated record
                $updatedProgram = $this->programModel->find($id);
                log_message('debug', 'Updated program data: ' . json_encode($updatedProgram));

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Program details updated successfully'
                ]);
            } else {
                log_message('error', 'Failed to update program with errors: ' . json_encode($this->programModel->errors()));
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update program details',
                    'errors' => $this->programModel->errors()
                ]);
            }
        }

        // If not AJAX request, redirect with message
        return redirect()->to('/program-details')->with('message', 'Invalid request method');
    }

    /**
     * Invalidate all related caches when program category is updated
     * 
     * @param int $programCategoryId
     * @return void
     */
    private function invalidateRelatedCaches($programCategoryId = null)
    {
        try {
            // Get all programs for this category to invalidate their caches
            if ($programCategoryId) {
                $programs = $this->programModel->where('program_category_id', $programCategoryId)->findAll();
                foreach ($programs as $program) {
                    $this->invalidateProgramCache($program->id);
                }
            }
            
            // Invalidate landing page cache (covers category data)
            $this->invalidateLandingCache();
            
            log_message('info', 'Cache invalidated for program category: ' . $programCategoryId);
        } catch (\Exception $e) {
            log_message('error', 'Failed to invalidate cache: ' . $e->getMessage());
        }
    }

    /**
     * Invalidate caches for a specific program
     * 
     * @param int $programId
     * @return void
     */
    private function invalidateProgramSpecificCaches($programId)
    {
        try {
            // Invalidate program-specific cache
            $this->invalidateProgramCache($programId);
            
            // Invalidate landing page cache
            $this->invalidateLandingCache();
            
            log_message('info', 'Cache invalidated for program: ' . $programId);
        } catch (\Exception $e) {
            log_message('error', 'Failed to invalidate program cache: ' . $e->getMessage());
        }
    }
}
