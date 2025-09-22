<?php

namespace App\Controllers;

use App\Models\ProgramSpeakerModel;
use App\Models\ProgramModel;
use App\Traits\Cacheable;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ProgramSpeakers extends AdminBaseController
{
    use Cacheable;
    
    protected $programSpeakerModel;
    protected $programModel;
    
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->programSpeakerModel = new ProgramSpeakerModel();
        $this->programModel = new ProgramModel();
        
        // Load storage helper for file uploads
        helper('storage');
    }

    public function index()
    {
        // Require authentication
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        // Get current program ID from session
        $programId = session('current_program');
        $program = $this->programModel->find($programId);
        
        // Get program speakers for the current program
        $programSpeakers = [];
        $speakerStats = null;
        
        if ($programId) {
            $programSpeakers = $this->programSpeakerModel->getByProgramId($programId, false);
            $speakerStats = $this->programSpeakerModel->getSpeakerStats($programId);
        }
        
        $data = $this->prepareViewData([
            'pageTitle' => 'Program Speakers',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => base_url('dashboard')],
                ['label' => 'Program Speakers', 'url' => '', 'active' => true]
            ],
            'program' => $program,
            'speakers' => $programSpeakers,
            'stats' => $speakerStats
        ]);
        
        return $this->renderView('master-data/program-speakers/index', $data);
    }
    
    /**
     * View a single speaker
     * @param int $id Speaker ID
     */
    public function view($id)
    {
        $speaker = $this->programSpeakerModel->find($id);
        
        if (!$speaker) {
            return redirect()->to('/master-data/program-speakers')
                           ->with('error', 'Speaker not found');
        }
        
        $program = $this->programModel->find($speaker->program_id);
        
        $data = [
            'title' => 'Speaker Details',
            'speaker' => $speaker,
            'program' => $program
        ];
        
        return view('master-data/program-speakers/view', $data);
    }
    
    /**
     * Get speaker data for AJAX
     */
    public function getSpeaker($id)
    {
        try {
            $speaker = $this->programSpeakerModel->find($id);
            
            if (!$speaker) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Speaker not found'
                ])->setStatusCode(404);
            }
            
            // Format session time for datetime-local input
            if ($speaker->session_time) {
                $speaker->session_time_formatted = date('Y-m-d\TH:i', strtotime($speaker->session_time));
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $speaker
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in ProgramSpeakers::getSpeaker: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load speaker details'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Create a new speaker
     */
    public function create()
    {
        // Validate form data
        $rules = [
            'name' => 'required|max_length[255]',
            'title' => 'permit_empty|max_length[255]',
            'bio' => 'permit_empty',
            'email' => 'permit_empty|valid_email',
            'organization' => 'permit_empty|max_length[255]',
            'linkedin_url' => 'permit_empty|valid_url',
            'instagram_url' => 'permit_empty|valid_url',
            'expertise_areas' => 'permit_empty',
            'session_title' => 'permit_empty|max_length[500]',
            'session_description' => 'permit_empty',
            'session_time' => 'permit_empty|valid_date',
            'is_keynote' => 'permit_empty|in_list[0,1]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $this->validator->getErrors()),
                    'errors' => $this->validator->getErrors()
                ])->setStatusCode(422);
            }

            return redirect()->to('/master-data/program-speakers')
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        // Get current program ID
        $programId = session('current_program');
        
        if (!$programId) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No program selected'
                ])->setStatusCode(400);
            }
            
            return redirect()->to('/master-data/program-speakers')
                           ->with('error', 'No program selected');
        }

        // Handle photo upload
        $photoUrl = '';
        $imgFile = $this->request->getFile('photo_url');
        
        if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
            // Validate file type and size
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($imgFile->getMimeType(), $allowedTypes)) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid file type. Only JPG, PNG and GIF files are allowed.'
                    ])->setStatusCode(422);
                }
                return redirect()->back()->with('error', 'Invalid file type. Only JPG, PNG and GIF files are allowed.');
            }
            
            if ($imgFile->getSize() > $maxSize) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'File size too large. Maximum size is 5MB.'
                    ])->setStatusCode(422);
                }
                return redirect()->back()->with('error', 'File size too large. Maximum size is 5MB.');
            }
            
            // Process file upload
            log_message('debug', 'Speaker photo upload - file received: ' . $imgFile->getName() . ', size: ' . $imgFile->getSize() . ', type: ' . $imgFile->getMimeType());
        }

        // Prepare data
        $isKeynote = (int)($this->request->getPost('is_keynote') ?? 0);
        $data = [
            'program_id' => $programId,
            'name' => $this->request->getPost('name'),
            'title' => $this->request->getPost('title'),
            'bio' => $this->request->getPost('bio'),
            'email' => $this->request->getPost('email'),
            'organization' => $this->request->getPost('organization'),
            'linkedin_url' => $this->request->getPost('linkedin_url'),
            'instagram_url' => $this->request->getPost('instagram_url'),
            'expertise_areas' => $this->request->getPost('expertise_areas'),
            'is_keynote' => $isKeynote,
            'session_title' => $this->request->getPost('session_title'),
            'session_description' => $this->request->getPost('session_description'),
            'session_time' => $this->request->getPost('session_time') ?: null,
            'order_number' => $this->programSpeakerModel->getNextOrderNumber($programId, $isKeynote),
            'is_active' => (int)($this->request->getPost('is_active') ?? 1),
            'is_deleted' => 0
        ];

        // Create new speaker first
        try {
            $insertId = $this->programSpeakerModel->insert($data);
            
            if (!$insertId) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to create speaker',
                        'errors' => $this->programSpeakerModel->errors()
                    ]);
                }
                return redirect()->back()
                    ->with('error', 'Failed to create speaker: ' . implode(", ", $this->programSpeakerModel->errors()))
                    ->withInput();
            }

            // Upload photo if provided
            if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
                try {
                    // Prepare file data for upload
                    $fileData = [
                        'name' => $imgFile->getName(),
                        'tmp_name' => $imgFile->getTempName(),
                        'type' => $imgFile->getClientMimeType(),
                        'size' => $imgFile->getSize(),
                        'error' => 0
                    ];
                    
                    // Upload to storage server
                    $uploadResult = upload_file_to_storage(
                        $fileData, 
                        'profile-pictures', 
                        'speaker_' . $insertId . '_' . time() . '.' . $imgFile->getExtension(),
                        $allowedTypes
                    );
                    
                    // Log upload result for debugging
                    log_message('debug', 'Speaker photo upload result: ' . json_encode($uploadResult));
                    
                    if ($uploadResult['status']) {
                        // Update the speaker with the photo URL
                        $this->programSpeakerModel->update($insertId, [
                            'photo_url' => $uploadResult['url']
                        ]);
                        
                        log_message('info', 'Speaker photo uploaded successfully: ' . $uploadResult['url']);
                    } else {
                        log_message('error', 'Failed to upload speaker photo: ' . ($uploadResult['message'] ?? 'Unknown error'));
                        // Don't fail the entire operation, just log the error
                    }
                } catch (\Exception $uploadEx) {
                    log_message('error', 'Exception during speaker photo upload: ' . $uploadEx->getMessage());
                    // Don't fail the entire operation, just log the error
                }
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Speaker created successfully',
                    'data' => ['id' => $insertId]
                ]);
            }

            return redirect()->to('/master-data/program-speakers')
                           ->with('success', 'Speaker created successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error creating speaker: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create speaker: ' . $e->getMessage()
                ])->setStatusCode(500);
            }

            return redirect()->to('/master-data/program-speakers')
                           ->withInput()
                           ->with('error', 'Failed to create speaker');
        }
    }
    
    /**
     * Update a speaker
     */
    public function update($id)
    {
        $speaker = $this->programSpeakerModel->find($id);
        
        if (!$speaker) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Speaker not found'
                ])->setStatusCode(404);
            }
            
            return redirect()->to('/master-data/program-speakers')
                           ->with('error', 'Speaker not found');
        }

        // Validate form data
        $rules = [
            'name' => 'required|max_length[255]',
            'title' => 'permit_empty|max_length[255]',
            'bio' => 'permit_empty',
            'email' => 'permit_empty|valid_email',
            'organization' => 'permit_empty|max_length[255]',
            'linkedin_url' => 'permit_empty|valid_url',
            'instagram_url' => 'permit_empty|valid_url',
            'expertise_areas' => 'permit_empty',
            'session_title' => 'permit_empty|max_length[500]',
            'session_description' => 'permit_empty',
            'session_time' => 'permit_empty|valid_date',
            'is_keynote' => 'permit_empty|in_list[0,1]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $this->validator->getErrors()),
                    'errors' => $this->validator->getErrors()
                ])->setStatusCode(422);
            }

            return redirect()->to('/master-data/program-speakers')
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        // Handle photo upload
        $imgFile = $this->request->getFile('photo_url');
        
        if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
            // Validate file type and size
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($imgFile->getMimeType(), $allowedTypes)) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid file type. Only JPG, PNG and GIF files are allowed.'
                    ])->setStatusCode(422);
                }
                return redirect()->back()->with('error', 'Invalid file type. Only JPG, PNG and GIF files are allowed.');
            }
            
            if ($imgFile->getSize() > $maxSize) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'File size too large. Maximum size is 5MB.'
                    ])->setStatusCode(422);
                }
                return redirect()->back()->with('error', 'File size too large. Maximum size is 5MB.');
            }
            
            // Process file upload
            log_message('debug', 'Speaker photo update - file received: ' . $imgFile->getName() . ', size: ' . $imgFile->getSize() . ', type: ' . $imgFile->getMimeType());
        }

        // Prepare update data
        $data = [
            'name' => $this->request->getPost('name'),
            'title' => $this->request->getPost('title'),
            'bio' => $this->request->getPost('bio'),
            'email' => $this->request->getPost('email'),
            'organization' => $this->request->getPost('organization'),
            'linkedin_url' => $this->request->getPost('linkedin_url'),
            'instagram_url' => $this->request->getPost('instagram_url'),
            'expertise_areas' => $this->request->getPost('expertise_areas'),
            'is_keynote' => (int)($this->request->getPost('is_keynote') ?? 0),
            'session_title' => $this->request->getPost('session_title'),
            'session_description' => $this->request->getPost('session_description'),
            'session_time' => $this->request->getPost('session_time') ?: null,
            'order_number' => $this->request->getPost('order_number') ?: $speaker->order_number,
            'is_active' => (int)($this->request->getPost('is_active') ?? 0)
        ];

        // Update speaker
        try {
            $this->programSpeakerModel->update($id, $data);
            
            // Upload photo if provided
            if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
                try {
                    // Prepare file data for upload
                    $fileData = [
                        'name' => $imgFile->getName(),
                        'tmp_name' => $imgFile->getTempName(),
                        'type' => $imgFile->getClientMimeType(),
                        'size' => $imgFile->getSize(),
                        'error' => 0
                    ];
                    
                    // Upload to storage server
                    $uploadResult = upload_file_to_storage(
                        $fileData, 
                        'profile-pictures', 
                        'speaker_' . $id . '_' . time() . '.' . $imgFile->getExtension(),
                        $allowedTypes
                    );
                    
                    // Log upload result for debugging
                    log_message('debug', 'Speaker photo upload result (update): ' . json_encode($uploadResult));
                    
                    if ($uploadResult['status']) {
                        // Update the speaker with the new photo URL
                        $this->programSpeakerModel->update($id, [
                            'photo_url' => $uploadResult['url']
                        ]);
                        
                        log_message('info', 'Speaker photo updated successfully: ' . $uploadResult['url']);
                    } else {
                        log_message('error', 'Failed to update speaker photo: ' . ($uploadResult['message'] ?? 'Unknown error'));
                        // Don't fail the entire operation, just log the error
                    }
                } catch (\Exception $uploadEx) {
                    log_message('error', 'Exception during speaker photo update: ' . $uploadEx->getMessage());
                    // Don't fail the entire operation, just log the error
                }
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Speaker updated successfully',
                    'data' => ['id' => $id]
                ]);
            }

            return redirect()->to('/master-data/program-speakers')
                           ->with('success', 'Speaker updated successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error updating speaker: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update speaker: ' . $e->getMessage()
                ])->setStatusCode(500);
            }

            return redirect()->to('/master-data/program-speakers')
                           ->with('error', 'Failed to update speaker');
        }
    }
    
    /**
     * Delete a speaker
     */
    public function delete($id)
    {
        try {
            $speaker = $this->programSpeakerModel->find($id);
            
            if (!$speaker) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Speaker not found'
                    ])->setStatusCode(404);
                }
                
                return redirect()->to('/master-data/program-speakers')
                               ->with('error', 'Speaker not found');
            }
            
            // Soft delete the speaker
            $this->programSpeakerModel->softDelete($id);
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Speaker deleted successfully'
                ]);
            }
            
            return redirect()->to('/master-data/program-speakers')
                           ->with('success', 'Speaker deleted successfully');
                           
        } catch (\Exception $e) {
            log_message('error', 'Error deleting speaker: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete speaker'
                ])->setStatusCode(500);
            }
            
            return redirect()->to('/master-data/program-speakers')
                           ->with('error', 'Failed to delete speaker');
        }
    }
    
    /**
     * Get speakers data for DataTable
     */
    public function getData()
    {
        $programId = session('current_program');
        
        if (!$programId) {
            return $this->response->setJSON([
                'draw' => intval($this->request->getPost('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }
        
        try {
            $speakers = $this->programSpeakerModel->getByProgramId($programId, false);
            
            $data = [];
            foreach ($speakers as $index => $speaker) {
                $data[] = [
                    'DT_RowId' => 'speaker_' . $speaker->id,
                    'number' => $index + 1,
                    'name' => esc($speaker->name),
                    'title' => esc($speaker->title ?? 'N/A'),
                    'organization' => esc($speaker->organization ?? 'N/A'),
                    'type' => $speaker->is_keynote ? '<span class="badge bg-warning-subtle text-warning">Keynote</span>' : '<span class="badge bg-info-subtle text-info">Regular</span>',
                    'session' => esc($speaker->session_title ?? 'No Session'),
                    'status' => $speaker->is_active ? '<span class="badge bg-success-subtle text-success">Active</span>' : '<span class="badge bg-danger-subtle text-danger">Inactive</span>',
                    'actions' => $this->generateActionButtons($speaker->id),
                    'order_number' => $speaker->order_number
                ];
            }
            
            return $this->response->setJSON([
                'draw' => intval($this->request->getPost('draw')),
                'recordsTotal' => count($speakers),
                'recordsFiltered' => count($speakers),
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in ProgramSpeakers::getData: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'draw' => intval($this->request->getPost('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load speakers data'
            ]);
        }
    }
    
    /**
     * Update speaker order
     */
    public function reorder()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/master-data/program-speakers');
        }
        
        try {
            $orderData = $this->request->getJSON(true);
            
            if (empty($orderData) || !is_array($orderData)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid order data'
                ])->setStatusCode(400);
            }
            
            $success = $this->programSpeakerModel->updateSpeakerOrder($orderData);
            
            if ($success) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Speaker order updated successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update speaker order'
                ])->setStatusCode(500);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error updating speaker order: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update speaker order'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Test storage connection
     */
    public function testStorage()
    {
        // For debugging purposes - remove in production
        if (!ENVIRONMENT === 'development') {
            return $this->response->setJSON(['error' => 'Not available'])->setStatusCode(403);
        }
        
        $storageConfig = new \Config\Storage();
        
        $testData = [
            'storage_url' => $storageConfig->storageUrl,
            'use_ftp' => $storageConfig->useFtp,
            'api_key_set' => !empty($storageConfig->apiKey),
            'ftp_host' => $storageConfig->ftpHost,
            'ftp_username' => $storageConfig->ftpUsername,
            'max_file_size' => $storageConfig->maxFileSize,
            'allowed_types' => $storageConfig->allowedProfilePictureTypes
        ];
        
        // Test FTP connection if enabled
        if ($storageConfig->useFtp) {
            try {
                $conn = ftp_connect($storageConfig->ftpHost, $storageConfig->ftpPort, 10);
                if ($conn) {
                    $login = @ftp_login($conn, $storageConfig->ftpUsername, $storageConfig->ftpPassword);
                    $testData['ftp_connection'] = $conn ? 'success' : 'failed';
                    $testData['ftp_login'] = $login ? 'success' : 'failed';
                    ftp_close($conn);
                } else {
                    $testData['ftp_connection'] = 'failed';
                    $testData['ftp_login'] = 'not_tested';
                }
            } catch (\Exception $e) {
                $testData['ftp_connection'] = 'exception: ' . $e->getMessage();
                $testData['ftp_login'] = 'not_tested';
            }
        } else {
            // Test HTTP connection
            try {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $storageConfig->storageUrl . '/api/test',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_HTTPHEADER => [
                        'X-API-Key: ' . $storageConfig->apiKey,
                        'Accept: application/json',
                    ],
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $testData['http_connection'] = $httpCode;
                $testData['http_response'] = $response;
            } catch (\Exception $e) {
                $testData['http_connection'] = 'exception: ' . $e->getMessage();
            }
        }
        
        return $this->response->setJSON($testData);
    }
    
    /**
     * Generate action buttons for DataTable
     */
    private function generateActionButtons($speakerId)
    {
        return '
            <div class="d-flex gap-2">
                <div class="view">
                    <button type="button" class="btn btn-sm btn-info view-speaker" data-id="' . $speakerId . '" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                        <i class="ri-eye-fill"></i>
                    </button>
                </div>
                <div class="edit">
                    <button type="button" class="btn btn-sm btn-success edit-speaker" data-id="' . $speakerId . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                        <i class="ri-pencil-fill"></i>
                    </button>
                </div>
                <div class="remove">
                    <button type="button" class="btn btn-sm btn-danger delete-speaker" data-id="' . $speakerId . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                        <i class="ri-delete-bin-fill"></i>
                    </button>
                </div>
            </div>
        ';
    }
}