<?php

namespace App\Controllers;

use App\Models\ProgramCertificateModel;
use App\Models\ProgramAwardModel;
use App\Models\ProgramCertificateContentBlockModel;
use App\Traits\Cacheable;
use Exception;

class ProgramCertificates extends AdminBaseController
{
    use Cacheable;
    
    protected $programCertificateModel;
    protected $programAwardModel;
    protected $contentBlockModel;

    public function __construct()
    {
        $this->programCertificateModel = new ProgramCertificateModel();
        $this->programAwardModel = new ProgramAwardModel();
        $this->contentBlockModel = new ProgramCertificateContentBlockModel();
    }

    /**
     * Display the program certificates index page
     */
    public function index()
    {
        try {
            $programId = session('current_program');
            if (!$programId) {
                return redirect()->to('/welcome')->with('error', 'Please select a program first.');
            }

            // Get awards for dropdown
            $awards = $this->programAwardModel->getActiveAwardsByProgram($programId);

            // Get topbar data from session (already loaded by AdminBaseController)
            $topbarData = $this->session->get('topbar_data', []);

            $data = [
                'title' => 'Program Certificates',
                'certificates' => [],
                'awards' => $awards,
                'topbarData' => $topbarData
            ];

            return view('master-data/program-certificates/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load program certificates index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load page: ' . $e->getMessage());
        }
    }

    /**
     * Get certificates data for DataTables
     */
    public function getData()
    {
        try {
            $programId = session('current_program');
            if (!$programId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No program selected'
                ]);
            }

            $certificates = $this->programCertificateModel->getCertificatesByProgram($programId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $certificates,
                'message' => 'Certificates retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch program certificates: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve certificates: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Get a specific program certificate
     */
    public function getCertificate($id)
    {
        try {
            $certificate = $this->programCertificateModel->getCertificateWithDetails($id);

            if (!$certificate) {
                return $this->response->setStatusCode(404)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Certificate not found'
                                     ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $certificate,
                'message' => 'Certificate retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch program certificate: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve certificate: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Display certificate details
     */
    public function view($id)
    {
        try {
            $certificate = $this->programCertificateModel->getCertificateWithDetails($id);

            if (!$certificate) {
                return redirect()->to('/master-data/program-certificates')->with('error', 'Certificate not found');
            }

            // Get topbar data from session (already loaded by AdminBaseController)
            $topbarData = $this->session->get('topbar_data', []);

            $data = [
                'title' => 'Certificate Details',
                'certificate' => $certificate,
                'topbarData' => $topbarData
            ];

            return view('master-data/program-certificates/view', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load certificate details: ' . $e->getMessage());
            return redirect()->to('/master-data/program-certificates')->with('error', 'Failed to load certificate details');
        }
    }

    /**
     * Show add certificate form
     */
    public function add()
    {
        try {
            $programId = session('current_program');
            if (!$programId) {
                return redirect()->to('/welcome')->with('error', 'Please select a program first.');
            }

            // Get awards for dropdown
            $awards = $this->programAwardModel->getActiveAwardsByProgram($programId);

            $data = [
                'title' => 'Add Certificate Template',
                'awards' => $awards
            ];

            return view('master-data/program-certificates/add', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load add certificate page: ' . $e->getMessage());
            return redirect()->to('/master-data/program-certificates')->with('error', 'Failed to load page');
        }
    }

    /**
     * Show edit certificate form
     */
    public function edit($id)
    {
        try {
            $certificate = $this->programCertificateModel->getCertificateWithDetails($id);

            if (!$certificate) {
                return redirect()->to('/master-data/program-certificates')->with('error', 'Certificate not found');
            }

            $programId = session('current_program');
            $awards = $this->programAwardModel->getActiveAwardsByProgram($programId);
            $contentBlocks = $this->contentBlockModel->getContentBlocksByCertificate($id);

            $data = [
                'title' => 'Edit Certificate Template',
                'certificate' => $certificate,
                'awards' => $awards,
                'contentBlocks' => $contentBlocks
            ];

            return view('master-data/program-certificates/edit', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load edit certificate page: ' . $e->getMessage());
            return redirect()->to('/master-data/program-certificates')->with('error', 'Failed to load page');
        }
    }

    /**
     * Create a new program certificate
     */
    public function create()
    {
        try {
            // Load the storage helper
            helper(['storage']);
            
            $data = $this->request->getPost();
            $programId = session('current_program');

            // Debug: Log all received data
            log_message('info', 'Certificate create method called with POST data: ' . json_encode($data));
            log_message('info', 'Files uploaded: ' . json_encode($_FILES));

            if (!$programId) {
                return redirect()->back()->with('error', 'No program selected');
            }

            // Handle file upload for template
            $templateFile = $this->request->getFile('template_file');
            
            // Debug file upload
            log_message('info', 'Template file object: ' . json_encode([
                'exists' => $templateFile !== null,
                'is_valid' => $templateFile ? $templateFile->isValid() : false,
                'has_moved' => $templateFile ? $templateFile->hasMoved() : false,
                'error' => $templateFile ? $templateFile->getError() : 'no file object',
                'error_string' => $templateFile ? $templateFile->getErrorString() : 'no file object',
                'size' => $templateFile ? $templateFile->getSize() : 0,
                'name' => $templateFile ? $templateFile->getName() : 'no name',
                'temp_name' => $templateFile ? $templateFile->getTempName() : 'no temp name'
            ]));
            
            if ($templateFile && $templateFile->isValid() && !$templateFile->hasMoved()) {
                $fileExtension = $templateFile->getExtension();
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
                
                if (!in_array(strtolower($fileExtension), $allowedTypes)) {
                    return redirect()->back()->with('error', 'Invalid file type. Only JPG, PNG, GIF, and PDF files are allowed.')->withInput();
                }

                // Prepare file for upload_file_to_storage
                $fileData = [
                    'name' => $templateFile->getName(),
                    'tmp_name' => $templateFile->getTempName(),
                    'type' => $templateFile->getClientMimeType(),
                    'size' => $templateFile->getSize(),
                    'error' => 0
                ];

                // Upload to storage server with timeout handling
                log_message('info', 'Starting file upload to storage. File size: ' . $templateFile->getSize() . ' bytes');
                
                // Increase execution time for large files
                $originalTimeLimit = ini_get('max_execution_time');
                set_time_limit(300); // 5 minutes for upload
                
                try {
                    $uploadResult = upload_file_to_storage(
                        $fileData,
                        'certificates/' . $programId,
                        'template_' . time() . '.' . $fileExtension,
                        [] // No restriction on MIME types for templates
                    );
                } catch (Exception $e) {
                    log_message('error', 'Exception during file upload: ' . $e->getMessage());
                    set_time_limit($originalTimeLimit);
                    return redirect()->back()->with('error', 'File upload failed due to server error: ' . $e->getMessage())->withInput();
                }
                
                // Restore original time limit
                set_time_limit($originalTimeLimit);

                if ($uploadResult['status']) {
                    $data['template_url'] = $uploadResult['url']; // Use full URL instead of path
                    $data['template_type'] = strtolower($fileExtension) === 'pdf' ? 'pdf' : 'image';
                    
                    // Log successful upload
                    log_message('info', 'Certificate template uploaded successfully: ' . $uploadResult['url']);
                    
                    // If PDF, we might want to generate a preview image
                    if (strtolower($fileExtension) === 'pdf') {
                        // Note: You would need to implement PDF to image conversion here
                        // This could be done server-side or as a separate process
                        $data['preview_url'] = $this->generatePdfPreview($uploadResult['url']);
                    }
                } else {
                    log_message('error', 'Certificate template upload failed: ' . $uploadResult['message']);
                    return redirect()->back()->with('error', 'Failed to upload template file: ' . $uploadResult['message'])->withInput();
                }
            } else {
                // Better error message for file upload issues
                $errorMessage = 'Template file is required. Please select a valid image or PDF file.';
                if ($templateFile) {
                    if (!$templateFile->isValid()) {
                        $errorMessage = 'File upload failed: ' . $templateFile->getErrorString();
                    } elseif ($templateFile->hasMoved()) {
                        $errorMessage = 'File has already been processed. Please try again.';
                    }
                }
                log_message('error', 'Template file validation failed: ' . $errorMessage);
                return redirect()->back()->with('error', $errorMessage)->withInput();
            }

            // Set program ID and default values
            $data['program_id'] = $programId;
            $data['is_active'] = $data['is_active'] ?? 1;
            $data['is_deleted'] = 0;

            // Debug logging
            log_message('info', 'Creating certificate with data: ' . json_encode([
                'program_id' => $data['program_id'],
                'award_id' => $data['award_id'] ?? 'not set',
                'template_url' => $data['template_url'] ?? 'not set',
                'template_type' => $data['template_type'] ?? 'not set',
                'issue_date' => $data['issue_date'] ?? 'not set',
                'published_at' => $data['published_at'] ?? 'not set',
                'is_active' => $data['is_active']
            ]));

            // Remove content blocks from certificate data
            $contentBlocks = json_decode($data['content_blocks'] ?? '[]', true);
            unset($data['content_blocks']);

            // Debug logging for content blocks
            log_message('info', 'Content blocks received: ' . json_encode($contentBlocks));

            if ($certificateId = $this->programCertificateModel->insert($data)) {
                log_message('info', 'Certificate created with ID: ' . $certificateId);
                
                // Invalidate program cache after successful creation
                $this->invalidateProgramCache($programId);
                
                // Save content blocks
                $savedBlocks = 0;
                foreach ($contentBlocks as $index => $block) {
                    // Remove the 'id' field as it's generated by the database
                    unset($block['id']);
                    
                    // Set certificate_id
                    $block['certificate_id'] = $certificateId;
                    
                    // Ensure required defaults
                    $block['is_active'] = $block['is_active'] ?? 1;
                    $block['is_deleted'] = $block['is_deleted'] ?? 0;
                    
                    log_message('info', "Saving content block {$index}: " . json_encode($block));
                    
                    if ($this->contentBlockModel->save($block)) {
                        $savedBlocks++;
                        log_message('info', "Content block {$index} saved successfully");
                    } else {
                        $errors = $this->contentBlockModel->errors();
                        log_message('error', "Failed to save content block {$index}: " . json_encode($errors));
                    }
                }
                
                log_message('info', "Saved {$savedBlocks} out of " . count($contentBlocks) . " content blocks");

                return redirect()->to('/master-data/program-certificates')
                                ->with('success', 'Certificate created successfully');
            } else {
                $errors = $this->programCertificateModel->errors();
                log_message('error', 'Certificate validation failed: ' . json_encode($errors));
                
                if (!empty($errors)) {
                    return redirect()->back()
                                    ->with('validation', $errors)
                                    ->withInput();
                } else {
                    return redirect()->back()
                                    ->with('error', 'Failed to create certificate. Please check your data and try again.')
                                    ->withInput();
                }
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to create program certificate: ' . $e->getMessage());
            return redirect()->back()
                            ->with('error', 'Failed to create certificate: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Update a program certificate
     */
    public function update($id)
    {
        try {
            // Load the storage helper
            helper(['storage']);
            
            $certificate = $this->programCertificateModel->where('is_deleted', 0)->find($id);

            if (!$certificate) {
                return redirect()->to('/master-data/program-certificates')
                                ->with('error', 'Certificate not found');
            }

            $data = $this->request->getPost();
            $programId = session('current_program');

            // Handle file upload for template
            $templateFile = $this->request->getFile('template_file');
            if ($templateFile && $templateFile->isValid() && !$templateFile->hasMoved()) {
                $fileExtension = $templateFile->getExtension();
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
                
                if (!in_array(strtolower($fileExtension), $allowedTypes)) {
                    return redirect()->back()->with('error', 'Invalid file type. Only JPG, PNG, GIF, and PDF files are allowed.')->withInput();
                }

                // Prepare file for upload_file_to_storage
                $fileData = [
                    'name' => $templateFile->getName(),
                    'tmp_name' => $templateFile->getTempName(),
                    'type' => $templateFile->getClientMimeType(),
                    'size' => $templateFile->getSize(),
                    'error' => 0
                ];

                // Upload to storage server
                $uploadResult = upload_file_to_storage(
                    $fileData,
                    'certificates/' . $programId,
                    'template_' . time() . '.' . $fileExtension,
                    [] // No restriction on MIME types for templates
                );

                if ($uploadResult['status']) {
                    $data['template_url'] = $uploadResult['url']; // Use full URL instead of path
                    $data['template_type'] = strtolower($fileExtension) === 'pdf' ? 'pdf' : 'image';
                    
                    // Delete old template file if exists
                    if (!empty($certificate['template_url'])) {
                        // Extract path from full URL for deletion
                        $storageConfig = new \Config\Storage();
                        $pathFromUrl = str_replace($storageConfig->storageUrl, '', $certificate['template_url']);
                        delete_storage_file($pathFromUrl);
                    }
                    
                    // If PDF, we might want to generate a preview image
                    if (strtolower($fileExtension) === 'pdf') {
                        $data['preview_url'] = $this->generatePdfPreview($uploadResult['url']);
                    }
                } else {
                    return redirect()->back()->with('error', 'Failed to upload template file: ' . $uploadResult['message'])->withInput();
                }
            }

            // Handle content blocks
            $contentBlocks = json_decode($data['content_blocks'] ?? '[]', true);
            unset($data['content_blocks']);

            if ($this->programCertificateModel->update($id, $data)) {
                // Invalidate program cache after successful update
                $this->invalidateProgramCache($certificate['program_id']);
                
                // Delete existing content blocks for this certificate
                $this->contentBlockModel->deleteBlocksByCertificate($id);

                // Save new content blocks
                foreach ($contentBlocks as $block) {
                    $block['certificate_id'] = $id;
                    $this->contentBlockModel->save($block);
                }

                return redirect()->to('/master-data/program-certificates')
                                ->with('success', 'Certificate updated successfully');
            } else {
                $errors = $this->programCertificateModel->errors();
                return redirect()->back()
                                ->with('error', 'Validation failed: ' . implode(', ', $errors))
                                ->withInput();
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to update program certificate: ' . $e->getMessage());
            return redirect()->back()
                            ->with('error', 'Failed to update certificate: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Delete a program certificate (soft delete)
     */
    public function delete($id)
    {
        try {
            $certificate = $this->programCertificateModel->where('is_deleted', 0)->find($id);

            if (!$certificate) {
                return redirect()->to('/master-data/program-certificates')
                                ->with('error', 'Certificate not found');
            }

            if ($this->programCertificateModel->softDelete($id)) {
                // Invalidate program cache after successful deletion
                $this->invalidateProgramCache($certificate['program_id']);
                
                return redirect()->to('/master-data/program-certificates')
                                ->with('success', 'Certificate deleted successfully');
            } else {
                return redirect()->to('/master-data/program-certificates')
                                ->with('error', 'Failed to delete certificate');
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to delete program certificate: ' . $e->getMessage());
            return redirect()->to('/master-data/program-certificates')
                            ->with('error', 'Failed to delete certificate: ' . $e->getMessage());
        }
    }

    /**
     * Publish a certificate
     */
    public function publish($id)
    {
        try {
            $certificate = $this->programCertificateModel->where('is_deleted', 0)->find($id);

            if (!$certificate) {
                return redirect()->to('/master-data/program-certificates')
                                ->with('error', 'Certificate not found');
            }

            $data = ['published_at' => date('Y-m-d H:i:s')];

            if ($this->programCertificateModel->update($id, $data)) {
                return redirect()->to('/master-data/program-certificates')
                                ->with('success', 'Certificate published successfully');
            } else {
                return redirect()->to('/master-data/program-certificates')
                                ->with('error', 'Failed to publish certificate');
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to publish certificate: ' . $e->getMessage());
            return redirect()->to('/master-data/program-certificates')
                            ->with('error', 'Failed to publish certificate: ' . $e->getMessage());
        }
    }

    /**
     * Get content blocks for a certificate
     */
    public function getContentBlocks($certificateId)
    {
        try {
            $contentBlocks = $this->contentBlockModel->getContentBlocksByCertificate($certificateId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $contentBlocks,
                'message' => 'Content blocks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch content blocks: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve content blocks: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Upload template image
     */
    public function uploadTemplate()
    {
        try {
            $templateFile = $this->request->getFile('template');
            
            if (!$templateFile || !$templateFile->isValid()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No valid file uploaded'
                ]);
            }

            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($templateFile->getMimeType(), $allowedTypes)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Only image files (JPEG, PNG, GIF) are allowed'
                ]);
            }

            $newName = $templateFile->getRandomName();
            if ($templateFile->move(WRITEPATH . 'uploads/certificate-templates', $newName)) {
                return $this->response->setJSON([
                    'success' => true,
                    'file_path' => 'uploads/certificate-templates/' . $newName,
                    'file_url' => base_url('writable/uploads/certificate-templates/' . $newName),
                    'message' => 'Template uploaded successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to upload template'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to upload template: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate preview image from PDF template
     */
    private function generatePdfPreview($pdfPath)
    {
        // This is a placeholder method - you would need to implement PDF to image conversion
        // Options include:
        // 1. Using Imagick extension: $imagick = new Imagick($pdfPath . '[0]');
        // 2. Using PDF.js for client-side rendering
        // 3. Using external service like CloudConvert
        // 4. Using command line tools like ImageMagick or Ghostscript
        
        // For now, return null - the frontend will handle PDF display differently
        return null;
    }
}
