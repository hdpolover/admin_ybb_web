<?php

namespace App\Controllers;
// announcement model
use App\Models\AnnouncementModel;
use App\Traits\Cacheable;

class Announcements extends BaseController
{
    use Cacheable;
    
    protected $announcementModel;

    public function __construct()
    {
        $this->announcementModel = new AnnouncementModel();
    }

    public function index()
    {
        $programId = session('current_program');
        $programAnnouncements = $this->announcementModel->getByProgramId($programId, false, false);

        // Get all statistics data
        $data = [
            'title' => 'Announcements',
            'programAnnouncements' => $programAnnouncements,
        ];

        return view('announcements/index', $data);
    }    
    
    /**
     * Show the form for adding a new announcement
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface|string
     */
    public function add()
    {
        $data = [
            'title' => 'Add Announcement',
            'announcement' => (object)[
                'id' => null,
                'title' => '',
                'content' => '',
                'is_active' => 1,
                'img_url' => '',
                'visible_to' => '1',
                'slug' => '',
                'meta_title' => '',
                'meta_description' => '',
                'tags' => '',
                'program_id' => session('current_program')
            ],
            'isAdd' => true
        ];
        
        return view('announcements/edit', $data);
    }
      /**
     * Create a new announcement
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function create()
    {
        // Check if this is an AJAX request
        $isAjax = $this->request->isAJAX();
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Load the storage helper
        helper(['storage']);
        
        // Define validation rules
        $rules = [
            'title' => 'required',
            'content' => 'required',
        ];
        
        if (!$this->validate($rules)) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()
                ->with('error', 'Failed to create announcement: ' . implode(", ", $this->validator->getErrors()))
                ->withInput();
        }
        
        // Get form data
        $title = $this->request->getPost('title');
        $content = $this->request->getPost('content');
        $isActive = $this->request->getPost('is_active') ? 1 : 0;
        $visibleTo = $this->request->getPost('visible_to') ?? '1';
        $slug = $this->request->getPost('slug');
        $metaTitle = $this->request->getPost('meta_title');
        $metaDescription = $this->request->getPost('meta_description');
        $tags = $this->request->getPost('tags');
        
        // Handle image upload if provided
        $img_url = ''; // Default to empty
        
        $imgFile = $this->request->getFile('img_url');
        if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
            // Prepare file for upload_file_to_storage
            $fileData = [
                'name' => $imgFile->getName(),
                'tmp_name' => $imgFile->getTempName(),
                'type' => $imgFile->getClientMimeType(),
                'size' => $imgFile->getSize(),
                'error' => 0
            ];
            
            // Define allowed image types
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            // Create the announcement first to get the ID
            $announcementId = $this->announcementModel->insert([
                'title' => $title,
                'content' => $content,
                'is_active' => $isActive,
                'visible_to' => $visibleTo,
                'slug' => $slug,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'tags' => $tags,
                'program_id' => $programId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$announcementId) {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to create announcement',
                        'errors' => $this->announcementModel->errors()
                    ]);
                }
                return redirect()->back()
                    ->with('error', 'Failed to create announcement: ' . implode(", ", $this->announcementModel->errors()))
                    ->withInput();
            }
            
            // Upload to storage server with the new announcement ID
            $uploadResult = upload_file_to_storage(
                $fileData, 
                'program-announcements/' . $programId, 
                'image_' . time() . '.' . $imgFile->getExtension(),
                $allowedTypes
            );
            
            // Log upload result for debugging
            log_message('debug', 'Announcement image upload result: ' . json_encode($uploadResult));
            
            if ($uploadResult['status']) {
                // Update the announcement with the image URL
                $this->announcementModel->update($announcementId, [
                    'img_url' => $uploadResult['url']
                ]);
                
                log_message('debug', 'Setting announcement image URL to: ' . $uploadResult['url']);
                
                // Invalidate landing page cache after successful announcement creation
                $this->invalidateLandingCache();
                
                if ($isAjax) {
                    $updatedAnnouncement = $this->announcementModel->find($announcementId);
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Announcement created successfully',
                        'data' => $updatedAnnouncement
                    ]);
                }
                return redirect()->to('/announcements')->with('success', 'Announcement created successfully');
            } else {
                log_message('error', 'Failed to upload announcement image: ' . ($uploadResult['message'] ?? 'Unknown error'));
                
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Announcement created but image failed to upload: ' . ($uploadResult['message'] ?? 'Unknown error')
                    ]);
                }
                return redirect()->to('/announcements')
                    ->with('warning', 'Announcement created but image failed to upload: ' . ($uploadResult['message'] ?? 'Unknown error'));
            }
        } else {
            // No image or invalid image, proceed with regular announcement creation
            $data = [
                'title' => $title,
                'content' => $content,
                'is_active' => $isActive,
                'img_url' => $img_url,
                'visible_to' => $visibleTo,
                'slug' => $slug,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'tags' => $tags,
                'program_id' => $programId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Insert announcement
            $announcementId = $this->announcementModel->insert($data);
            if ($announcementId) {
                // Invalidate landing page cache after successful announcement creation
                $this->invalidateLandingCache();
                
                if ($isAjax) {
                    $announcement = $this->announcementModel->find($announcementId);
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Announcement created successfully',
                        'data' => $announcement
                    ]);
                }
                return redirect()->to('/announcements')->with('success', 'Announcement created successfully');
            } else {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to create announcement',
                        'errors' => $this->announcementModel->errors()
                    ]);
                }
                return redirect()->back()
                    ->with('error', 'Failed to create announcement: ' . implode(", ", $this->announcementModel->errors()))
                    ->withInput();
            }
        }
    }
    
    public function view($id)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Announcement ID is required'
            ]);
        }

        // Find the announcement
        $announcement = $this->announcementModel->find($id);

        // Check if announcement exists
        if (!$announcement) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Announcement not found'
            ]);
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Check if announcement belongs to the current program
        if ($announcement->program_id != $programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have access to this announcement'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $announcement
        ]);
    }
    
    /**
     * Show the edit form for the announcement
     * 
     * @param int $id
     * @return \CodeIgniter\HTTP\ResponseInterface|string
     */
    public function edit($id)
    {
        // Get current program ID from session
        $programId = session('current_program');
        
        // Find the announcement
        $announcement = $this->announcementModel->find($id);
        
        // Check if announcement exists
        if (!$announcement) {
            return redirect()->to('/announcements')->with('error', 'Announcement not found');
        }
        
        // Check if announcement belongs to the current program
        if ($announcement->program_id != $programId) {
            return redirect()->to('/announcements')->with('error', 'You do not have access to this announcement');
        }
        
        $data = [
            'title' => 'Edit Announcement',
            'announcement' => $announcement
        ];
        
        return view('announcements/edit', $data);
    }
    
    /**
     * Update announcement
     * 
     * @param int $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function update($id)
    {
        // Check if this is an AJAX request
        $isAjax = $this->request->isAJAX();
        
        if (!$id) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Announcement ID is required'
                ]);
            }
            return redirect()->to('/announcements')->with('error', 'Announcement ID is required');
        }
        
        // Find the announcement
        $announcement = $this->announcementModel->find($id);
        
        // Check if announcement exists
        if (!$announcement) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Announcement not found'
                ]);
            }
            return redirect()->to('/announcements')->with('error', 'Announcement not found');
        }
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Check if announcement belongs to the current program
        if ($announcement->program_id != $programId) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You do not have access to this announcement'
                ]);
            }
            return redirect()->to('/announcements')->with('error', 'You do not have access to this announcement');
        }
        
        // Load the storage helper
        helper(['storage']);
        
        // Define validation rules
        $rules = [
            'title' => 'required',
            'content' => 'required',
        ];
        
        if (!$this->validate($rules)) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()
                ->with('error', 'Failed to update announcement: ' . implode(", ", $this->validator->getErrors()))
                ->withInput();
        }
        
        // Get form data
        $title = $this->request->getPost('title');
        $content = $this->request->getPost('content');
        $isActive = $this->request->getPost('is_active') ? 1 : 0;
        $visibleTo = $this->request->getPost('visible_to') ?? '1';
        $slug = $this->request->getPost('slug');
        $metaTitle = $this->request->getPost('meta_title');
        $metaDescription = $this->request->getPost('meta_description');
        $tags = $this->request->getPost('tags');
        
        // Prepare data to update
        $data = [
            'title' => $title,
            'content' => $content,
            'is_active' => $isActive,
            'visible_to' => $visibleTo,
            'slug' => $slug,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'tags' => $tags,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Handle image upload if provided
        $img_url = $announcement->img_url; // Default to existing image
        
        $imgFile = $this->request->getFile('img_url');
        if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
            // Prepare file for upload_file_to_storage
            $fileData = [
                'name' => $imgFile->getName(),
                'tmp_name' => $imgFile->getTempName(),
                'type' => $imgFile->getClientMimeType(),
                'size' => $imgFile->getSize(),
                'error' => 0
            ];
            
            // Define allowed image types
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            // Upload to storage server
            $uploadResult = upload_file_to_storage(
                $fileData, 
                'program-announcements/' . $programId  ,
                'image_' . time() . '.' . $imgFile->getExtension(),
                $allowedTypes
            );
            
            // Log upload result for debugging
            log_message('debug', 'Announcement image upload result: ' . json_encode($uploadResult));
            
            if ($uploadResult['status']) {
                $data['img_url'] = $uploadResult['url'];
                log_message('debug', 'Setting announcement image URL to: ' . $uploadResult['url']);
            } else {
                log_message('error', 'Failed to upload announcement image: ' . ($uploadResult['message'] ?? 'Unknown error'));
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to upload image: ' . ($uploadResult['message'] ?? 'Unknown error')
                    ]);
                }
            }
        }
        
        // Log the data being updated for debugging
        log_message('debug', 'Data to be updated for announcement ID ' . $id . ': ' . json_encode($data));
        
        // Update announcement
        if ($this->announcementModel->update($id, $data)) {
            // Verify the update by retrieving the updated record
            $updatedAnnouncement = $this->announcementModel->find($id);
            log_message('debug', 'Updated announcement data: ' . json_encode($updatedAnnouncement));
            
            // Invalidate landing page cache after successful announcement update
            $this->invalidateLandingCache();
            
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Announcement updated successfully',
                    'data' => $updatedAnnouncement
                ]);
            }
            return redirect()->to('/announcements')->with('success', 'Announcement updated successfully');
        } else {
            log_message('error', 'Failed to update announcement with errors: ' . json_encode($this->announcementModel->errors()));
            
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update announcement',
                    'errors' => $this->announcementModel->errors()
                ]);
            }
            return redirect()->back()
                ->with('error', 'Failed to update announcement: ' . implode(", ", $this->announcementModel->errors()))
                ->withInput();
        }
    }

    /**
     * Delete announcement
     * 
     * @param int $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($id)
    {
        // Check if this is an AJAX request
        $isAjax = $this->request->isAJAX();
        
        if (!$id) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Announcement ID is required'
                ]);
            }
            return redirect()->to('/announcements')->with('error', 'Announcement ID is required');
        }
        
        // Find the announcement
        $announcement = $this->announcementModel->find($id);
        
        // Check if announcement exists
        if (!$announcement) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Announcement not found'
                ]);
            }
            return redirect()->to('/announcements')->with('error', 'Announcement not found');
        }
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Check if announcement belongs to the current program
        if ($announcement->program_id != $programId) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You do not have access to this announcement'
                ]);
            }
            return redirect()->to('/announcements')->with('error', 'You do not have access to this announcement');
        }
        
        // Soft delete the announcement by setting is_deleted to 1
        if ($this->announcementModel->update($id, ['is_deleted' => 1])) {
            // Invalidate landing page cache after successful announcement deletion
            $this->invalidateLandingCache();
            
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Announcement deleted successfully'
                ]);
            }
            return redirect()->to('/announcements')->with('success', 'Announcement deleted successfully');
        } else {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete announcement',
                    'errors' => $this->announcementModel->errors()
                ]);
            }
            return redirect()->to('/announcements')->with('error', 'Failed to delete announcement: ' . implode(", ", $this->announcementModel->errors()));
        }
    }
}
