<?php

namespace App\Controllers;

use App\Models\ProgramPhotoModel;
// program model
use App\Models\ProgramModel;

class ProgramPhotos extends BaseController
{
    protected $programPhotoModel;
    protected $programModel;
    public function __construct()
    {
        $this->programPhotoModel = new ProgramPhotoModel();
        $this->programModel = new ProgramModel();

        // Load the storage helper for file uploads
        helper('storage');
    }

    public function index()
    {
        // Get current program ID from session
        $programId = session('current_program');

        if (!$programId) {
            // Handle the case where the program ID is not set in the session
            return redirect()->to('/welcome'); // Redirect to a suitable page
        }

        // get program data
        $program = $this->programModel->find($programId);

        if (!$program) {
            // Handle the case where the program is not found
            return redirect()->to('/welcome'); // Redirect to a suitable page
        }

        // get program photos
        $programPhotos = $this->programPhotoModel->getActivePhotos($program->program_category_id);

        $data = [
            'title' => 'Program Photos',
            'program_photos' => $programPhotos,
        ];

        return view('master-data/program-photos/index', $data);
    }
    public function create()
    {
        // Get current program ID from session
        $programId = session('current_program');

        if (!$programId) {
            return redirect()->to('/welcome')->with('error', 'No program selected');
        }

        // Get program data to get category ID
        $program = $this->programModel->find($programId);

        if (!$program) {
            return redirect()->to('/welcome')->with('error', 'Program not found');
        }

        // Validate form input
        $rules = [
            'title' => 'required|min_length[3]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        // Check if file is uploaded
        $file = $this->request->getFile('photo_file');

        if (!$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->withInput()->with('error', 'Invalid file or no file uploaded');
        }

        // Prepare file for upload_file_to_storage
        $fileData = [
            'name' => $file->getName(),
            'type' => $file->getClientMimeType(),
            'tmp_name' => $file->getTempName(),
            'error' => $file->getError(),
            'size' => $file->getSize()
        ];

        // Set the destination folder based on program category
        $destination = 'program-photos/' . $program->program_category_id;

        // Generate a unique filename using timestamp
        $filename = time() . '.' . $file->getExtension();

        // Upload the file to storage
        $uploadResult = upload_file_to_storage(
            $fileData,
            $destination,
            $filename,
            ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp']
        );

        if (!$uploadResult['status']) {
            return redirect()->back()->withInput()->with('error', 'File upload failed: ' . $uploadResult['message']);
        }

        // Prepare data for insertion
        $data = [
            'program_category_id' => $program->program_category_id,
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'img_url' => $uploadResult['url'],
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'is_deleted' => 0
        ];        // Insert data
        try {
            $this->programPhotoModel->insert($data);
            return redirect()->to('master-data/program-photos')->with('success', 'Photo "' . $this->request->getPost('title') . '" has been added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add photo: ' . $e->getMessage());
        }
    }

    public function view($id)
    {
        // Get photo details
        $photo = $this->programPhotoModel->find($id);

        if (!$photo) {
            return redirect()->to('master-data/program-photos')->with('error', 'Photo not found');
        }

        $data = [
            'title' => 'View Photo',
            'photo' => $photo
        ];

        return view('master-data/program-photos/view', $data);
    }
    public function update($id)
    {
        // Check if photo exists
        $photo = $this->programPhotoModel->find($id);

        if (!$photo) {
            return redirect()->to('master-data/program-photos')->with('error', 'Photo not found');
        }

        // Validate form input
        $rules = [
            'title' => 'required|min_length[3]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        // Prepare data for update - start with existing values
        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'img_url' => $this->request->getPost('img_url'), // Will be overwritten if new file is uploaded
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        // Check if a new file has been uploaded
        $file = $this->request->getFile('photo_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Get the program data to know where to store the photo
            $programId = session('current_program');
            $program = $this->programModel->find($programId);

            if (!$program) {
                return redirect()->to('/welcome')->with('error', 'Program not found');
            }

            // Prepare file for upload_file_to_storage
            $fileData = [
                'name' => $file->getName(),
                'type' => $file->getClientMimeType(),
                'tmp_name' => $file->getTempName(),
                'error' => $file->getError(),
                'size' => $file->getSize()
            ];

            // Set the destination folder based on program category
            $destination = 'program-photos/' . $program->program_category_id;

            // Generate a unique filename using timestamp
            $filename = time() . '.' . $file->getExtension();

            // Upload the file to storage
            $uploadResult = upload_file_to_storage(
                $fileData,
                $destination,
                $filename,
                ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp']
            );

            if (!$uploadResult['status']) {
                return redirect()->back()->withInput()->with('error', 'File upload failed: ' . $uploadResult['message']);
            }

            // Update the image URL with the newly uploaded file URL
            $data['img_url'] = $uploadResult['url'];
        }        // Update data
        try {
            $this->programPhotoModel->update($id, $data);
            return redirect()->to('master-data/program-photos')->with('success', 'Photo "' . $this->request->getPost('title') . '" has been updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update photo: ' . $e->getMessage());
        }
    }    public function delete($id)
    {
        // Check if photo exists
        $photo = $this->programPhotoModel->find($id);

        if (!$photo) {
            return redirect()->to('master-data/program-photos')->with('error', 'Photo not found');
        }

        // Store the photo title for the success message
        $photoTitle = $photo->title;

        // Perform soft delete by setting is_deleted to 1
        try {
            $this->programPhotoModel->update($id, ['is_deleted' => 1, 'is_active' => 0]);
            return redirect()->to('master-data/program-photos')->with('success', 'Photo "' . $photoTitle . '" has been deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete photo: ' . $e->getMessage());
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

        // Get all photos for AJAX requests
        $photos = $this->programPhotoModel->getAllPhotos($program->program_category_id);

        return $this->response->setJSON($photos);
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
