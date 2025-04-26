<?php

namespace App\Controllers;
use App\Models\ProgramTestimonyModel;
// Program model for getting program category id
use App\Models\ProgramModel;

class ProgramTestimonies extends BaseController
{
    protected $programTestimonyModel;
    protected $programModel;

    public function __construct()
    {
        $this->programTestimonyModel = new ProgramTestimonyModel();
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

        // Get program data
        $program = $this->programModel->find($programId);

        if (!$program) {
            // Handle the case where the program is not found
            return redirect()->to('/welcome'); // Redirect to a suitable page
        }

        // Get program testimonies for the current program category
        $testimonies = $this->programTestimonyModel->where('program_category_id', $program->program_category_id)
                                                  ->where('is_deleted', 0)
                                                  ->findAll();

        $data = [
            'title' => 'Program Testimonies',
            'testimonies' => $testimonies,
        ];
        
        return view('master-data/program-testimonies/index', $data);
    }

    public function root($path = '')
    {
        if ($path !== '') {
            if(@file_exists(APPPATH.'Views/'.$path.'.php')) {
                return view($path);
            } else {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
        } else {
            echo 'Page Not Found.';
        }
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
            'person_name' => 'required|min_length[3]',
            'testimony' => 'required|min_length[5]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        // Check if file is uploaded
        $file = $this->request->getFile('photo_file');

        // Initialize img_url as empty
        $imgUrl = '';

        // Only process file if it exists and is valid
        if ($file !== null && $file->isValid() && !$file->hasMoved()) {
            // Prepare file for upload_file_to_storage
            $fileData = [
                'name' => $file->getName(),
                'type' => $file->getClientMimeType(),
                'tmp_name' => $file->getTempName(),
                'error' => $file->getError(),
                'size' => $file->getSize()
            ];

            // Set the destination folder based on program category
            $destination = 'program-testimonies/' . $program->program_category_id;

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

            $imgUrl = $uploadResult['url'];
        }

        // Prepare data for insertion
        $data = [
            'program_category_id' => $program->program_category_id,
            'person_name' => $this->request->getPost('person_name'),
            'testimony' => $this->request->getPost('testimony'),
            'occupation' => $this->request->getPost('occupation'),
            'institution' => $this->request->getPost('institution'),
            'img_url' => $imgUrl,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'is_deleted' => 0
        ];

        // Insert data
        try {
            $this->programTestimonyModel->insert($data);
            return redirect()->to('master-data/program-testimonies')->with('success', 'Testimony from "' . $this->request->getPost('person_name') . '" has been added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add testimony: ' . $e->getMessage());
        }
    }

    public function view($id)
    {
        // Get testimony details
        $testimony = $this->programTestimonyModel->find($id);

        if (!$testimony) {
            return redirect()->to('master-data/program-testimonies')->with('error', 'Testimony not found');
        }

        $data = [
            'title' => 'View Testimony',
            'testimony' => $testimony
        ];

        return view('master-data/program-testimonies/view', $data);
    }

    public function update($id)
    {
        // Check if testimony exists
        $testimony = $this->programTestimonyModel->find($id);

        if (!$testimony) {
            return redirect()->to('master-data/program-testimonies')->with('error', 'Testimony not found');
        }

        // Validate form input
        $rules = [
            'person_name' => 'required|min_length[3]',
            'testimony' => 'required|min_length[5]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        // Prepare data for update - start with existing values
        $data = [
            'person_name' => $this->request->getPost('person_name'),
            'testimony' => $this->request->getPost('testimony'),
            'occupation' => $this->request->getPost('occupation'),
            'institution' => $this->request->getPost('institution'),
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
            $destination = 'program-testimonies/' . $program->program_category_id;

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
        }

        // Update data
        try {
            $this->programTestimonyModel->update($id, $data);
            return redirect()->to('master-data/program-testimonies')->with('success', 'Testimony from "' . $this->request->getPost('person_name') . '" has been updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update testimony: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        // Check if testimony exists
        $testimony = $this->programTestimonyModel->find($id);

        if (!$testimony) {
            return redirect()->to('master-data/program-testimonies')->with('error', 'Testimony not found');
        }

        // Store the testimony name for the success message
        $personName = $testimony->person_name;

        // Perform soft delete by setting is_deleted to 1
        try {
            $this->programTestimonyModel->update($id, ['is_deleted' => 1, 'is_active' => 0]);
            return redirect()->to('master-data/program-testimonies')->with('success', 'Testimony from "' . $personName . '" has been deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete testimony: ' . $e->getMessage());
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

        // Get all testimonies for AJAX requests
        $testimonies = $this->programTestimonyModel->where('program_category_id', $program->program_category_id)
                                                  ->findAll();

        return $this->response->setJSON($testimonies);
    }
}
