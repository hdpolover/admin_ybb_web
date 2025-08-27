<?php

namespace App\Controllers;

use App\Models\ProgramVideoTestimonyModel;
use App\Models\ProgramModel;
use App\Traits\Cacheable;

class ProgramVideoTestimonies extends BaseController
{
    use Cacheable;
    
    protected $programVideoTestimonyModel;
    protected $programModel;

    public function __construct()
    {
        $this->programVideoTestimonyModel = new ProgramVideoTestimonyModel();
        $this->programModel = new ProgramModel();
    }

    public function index()
    {
        // Get current program ID from session
        $programId = session('current_program');

        if (!$programId) {
            return redirect()->to('/welcome');
        }

        // Get program data
        $program = $this->programModel->find($programId);

        if (!$program) {
            return redirect()->to('/welcome')->with('error', 'Program not found');
        }

        // Get all video testimonies for this program
        $videoTestimonies = $this->programVideoTestimonyModel->getAllVideoTestimoniesForProgram($programId);

        $data = [
            'title' => 'Program Video Testimonies',
            'program' => $program,
            'videoTestimonies' => $videoTestimonies
        ];

        return view('master-data/program-video-testimonies/index', $data);
    }

    public function create()
    {
        // Get current program ID from session
        $programId = session('current_program');

        if (!$programId) {
            return redirect()->to('/welcome');
        }

        // Validate form input
        $rules = [
            'youtube_url' => 'required|valid_url',
            'description' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your input and try again.');
        }

        // Get form data
        $youtubeUrl = $this->request->getPost('youtube_url');
        
        // Validate YouTube URL format
        if (!$this->isValidYouTubeUrl($youtubeUrl)) {
            return redirect()->back()->withInput()->with('error', 'Please provide a valid YouTube URL.');
        }

        // Extract video ID
        $videoId = $this->programVideoTestimonyModel->extractYouTubeVideoId($youtubeUrl);
        if (!$videoId) {
            return redirect()->back()->withInput()->with('error', 'Could not extract video ID from YouTube URL.');
        }

        // Get next display order
        $displayOrder = $this->programVideoTestimonyModel->getNextDisplayOrder($programId);

        // Prepare data for insertion
        $data = [
            'program_id' => $programId,
            'youtube_url' => $youtubeUrl,
            'youtube_video_id' => $videoId,
            'description' => $this->request->getPost('description'),
            'display_order' => $displayOrder,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'is_deleted' => 0
        ];

        // Insert data
        try {
            $this->programVideoTestimonyModel->insert($data);
            
            // Invalidate landing page cache after successful video testimony creation
            $this->invalidateLandingCache();
            
            return redirect()->to('master-data/program-video-testimonies')->with('success', 'Video testimony has been added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add video testimony: ' . $e->getMessage());
        }
    }

    public function view($id)
    {
        // Get video testimony details
        $videoTestimony = $this->programVideoTestimonyModel->find($id);

        if (!$videoTestimony) {
            return redirect()->to('master-data/program-video-testimonies')->with('error', 'Video testimony not found');
        }

        $data = [
            'title' => 'View Video Testimony',
            'videoTestimony' => $videoTestimony
        ];

        return view('master-data/program-video-testimonies/view', $data);
    }

    public function update($id)
    {
        // Check if video testimony exists
        $videoTestimony = $this->programVideoTestimonyModel->find($id);

        if (!$videoTestimony) {
            return redirect()->to('master-data/program-video-testimonies')->with('error', 'Video testimony not found');
        }

        // Validate form input
        $rules = [
            'youtube_url' => 'required|valid_url',
            'description' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your input and try again.');
        }

        // Get form data
        $youtubeUrl = $this->request->getPost('youtube_url');
        
        // Validate YouTube URL format
        if (!$this->isValidYouTubeUrl($youtubeUrl)) {
            return redirect()->back()->withInput()->with('error', 'Please provide a valid YouTube URL.');
        }

        // Extract video ID
        $videoId = $this->programVideoTestimonyModel->extractYouTubeVideoId($youtubeUrl);
        if (!$videoId) {
            return redirect()->back()->withInput()->with('error', 'Could not extract video ID from YouTube URL.');
        }

        // Prepare data for update
        $data = [
            'youtube_url' => $youtubeUrl,
            'youtube_video_id' => $videoId,
            'description' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        // Update data
        try {
            $this->programVideoTestimonyModel->update($id, $data);
            
            // Invalidate landing page cache after successful video testimony update
            $this->invalidateLandingCache();
            
            return redirect()->to('master-data/program-video-testimonies')->with('success', 'Video testimony has been updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update video testimony: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        // Check if video testimony exists
        $videoTestimony = $this->programVideoTestimonyModel->find($id);

        if (!$videoTestimony) {
            return redirect()->to('master-data/program-video-testimonies')->with('error', 'Video testimony not found');
        }

        // Store the video details for the success message
        $description = $videoTestimony->description ? character_limiter($videoTestimony->description, 50) : 'Video testimony';

        // Perform soft delete by setting is_deleted to 1
        try {
            $this->programVideoTestimonyModel->update($id, ['is_deleted' => 1, 'is_active' => 0]);
            
            // Invalidate landing page cache after successful video testimony deletion
            $this->invalidateLandingCache();
            
            return redirect()->to('master-data/program-video-testimonies')->with('success', '"' . $description . '" has been deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete video testimony: ' . $e->getMessage());
        }
    }

    public function updateOrder()
    {
        $orderData = $this->request->getJSON(true);
        
        if (empty($orderData)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No order data provided']);
        }

        try {
            $success = $this->programVideoTestimonyModel->updateDisplayOrders($orderData);
            
            if ($success) {
                // Invalidate cache after order update
                $this->invalidateLandingCache();
                
                return $this->response->setJSON(['success' => true, 'message' => 'Display order updated successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to update display order']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error updating display order: ' . $e->getMessage()]);
        }
    }

    public function getData()
    {
        // Get current program ID from session
        $programId = session('current_program');

        if (!$programId) {
            return $this->response->setJSON(['error' => 'No program selected']);
        }

        // Get program data
        $program = $this->programModel->find($programId);

        if (!$program) {
            return $this->response->setJSON(['error' => 'Program not found']);
        }

        // Get all video testimonies for AJAX requests
        $videoTestimonies = $this->programVideoTestimonyModel->getAllVideoTestimoniesForProgram($programId);

        return $this->response->setJSON($videoTestimonies);
    }

    /**
     * Validate YouTube URL format
     *
     * @param string $url
     * @return bool
     */
    private function isValidYouTubeUrl($url)
    {
        $patterns = [
            '/^https?:\/\/(www\.)?(youtube\.com|youtu\.be)\//',
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        return false;
    }
}