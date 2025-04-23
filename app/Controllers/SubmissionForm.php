<?php

namespace App\Controllers;

use App\Models\ProgramModel;
// competition categories
use App\Models\CompetitionCategoryModel;
// program essays
use App\Models\ProgramEssayModel;
// program submtheme
use App\Models\ProgramSubthemeModel;

class SubmissionForm extends BaseController
{
    protected $programModel;
    protected $competitionCategoryModel;
    protected $programEssayModel;
    protected $programSubThemeModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
        $this->competitionCategoryModel = new CompetitionCategoryModel();
        $this->programEssayModel = new ProgramEssayModel();
        $this->programSubThemeModel = new ProgramSubthemeModel();
    }

    // get data
    public function getData()
    {
        // get program id from session
        $programId = session()->get('current_program');

        // get current program details
        $currentProgram = $this->programModel->find($programId);

        // get competititon categories 
        $competitionCategories = $this->competitionCategoryModel->where('program_id', $programId)->findAll();

        // check if empty
        if (empty($competitionCategories)) {
            $competitionCategories = [];
        }

        // get program essays
        $programEssays = $this->programEssayModel->getActiveEssays($programId);

        // check if empty
        if (empty($programEssays)) {
            $programEssays = [];
        }

        // get program sub themes
        $programSubThemes = $this->programSubThemeModel->where('program_id', $programId)->findAll();

        if (empty($programSubThemes)) {
            $programSubThemes = [];
        }

        return [
            'currentProgram' => $currentProgram,
            'competitionCategories' => $competitionCategories,
            'programEssays' => $programEssays,
            'programSubThemes' => $programSubThemes,
        ];
    }

    public function index()
    {
        $data = [
            'title' => 'Submission Form',

        ];

        // get data to data
        $data = array_merge($data, $this->getData());

        return view('master-data/submission-form/index', $data);
    }

    // Category Management Methods
    public function addCategory()
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Validate the request
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'description' => 'permit_empty|max_length[255]',
            'is_active' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Prepare data
        $programId = session()->get('current_program');
        $data = [
            'program_id' => $programId,
            'category' => $this->request->getPost('name'),
            'desc' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Insert data
        try {
            $this->competitionCategoryModel->insert($data);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Category added successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to add category',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateCategory($id = null)
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Validate ID
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Category ID is required']);
        }

        // Check if category exists
        $category = $this->competitionCategoryModel->find($id);
        if (!$category) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Category not found']);
        }

        // Validate the request
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'description    ' => 'permit_empty|max_length[255]',
            'is_active' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Prepare data
        $data = [
            'category' => $this->request->getPost('name'),
            'desc' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Update data
        try {
            $this->competitionCategoryModel->update($id, $data);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteCategory($id = null)
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Validate ID
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Category ID is required']);
        }

        // Check if category exists
        $category = $this->competitionCategoryModel->find($id);
        if (!$category) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Category not found']);
        }

        // Delete data
        try {
            $this->competitionCategoryModel->delete($id);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getCategoryById($id = null)
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Validate ID
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Category ID is required']);
        }

        // Get category
        $category = $this->competitionCategoryModel->find($id);
        if (!$category) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Category not found']);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $category
        ]);
    }

    // Sub Theme Management Methods
    public function addSubTheme()
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Validate the request
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'description' => 'permit_empty|max_length[255]',
            'is_active' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Prepare data
        $programId = session()->get('current_program');
        $data = [
            'program_id' => $programId,
            'name' => $this->request->getPost('name'),
            'desc' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Insert data
        try {
            $this->programSubThemeModel->insert($data);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Sub Theme added successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to add sub theme',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateSubTheme($id = null)
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Validate ID
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Sub Theme ID is required']);
        }

        // Check if sub theme exists
        $subTheme = $this->programSubThemeModel->find($id);
        if (!$subTheme) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Sub Theme not found']);
        }

        // Validate the request
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'description' => 'permit_empty|max_length[255]',
            'is_active' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Prepare data
        $data = [
            'name' => $this->request->getPost('name'),
            'desc' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Update data
        try {
            $this->programSubThemeModel->update($id, $data);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Sub Theme updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to update sub theme',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteSubTheme($id = null)
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Validate ID
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Sub Theme ID is required']);
        }

        // Check if sub theme exists
        $subTheme = $this->programSubThemeModel->find($id);
        if (!$subTheme) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Sub Theme not found']);
        }

        // Delete data
        try {
            $this->programSubThemeModel->delete($id);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Sub Theme deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to delete sub theme',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getSubThemeById($id = null)
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Validate ID
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Sub Theme ID is required']);
        }

        // Get sub theme
        $subTheme = $this->programSubThemeModel->find($id);
        if (!$subTheme) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Sub Theme not found']);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $subTheme
        ]);
    }    // Essay Management Methods
    public function addEssay()
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Validate the request
        $rules = [
            'questions' => 'required|min_length[3]',
            'max_word_count' => 'permit_empty|numeric',
            'is_active' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Prepare data
        $programId = session()->get('current_program');
        $data = [
            'program_id' => $programId,
            'questions' => $this->request->getPost('questions'),
            'max_word_count' => $this->request->getPost('max_word_count') ?: null,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Insert data
        try {
            $this->programEssayModel->insert($data);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Essay added successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to add essay',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateEssay($id = null)
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Validate ID
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Essay ID is required']);
        }

        // Check if essay exists
        $essay = $this->programEssayModel->find($id);
        if (!$essay) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Essay not found']);
        }

        // Validate the request
        $rules = [
            'questions' => 'required|min_length[3]',
            'max_word_count' => 'permit_empty|numeric',
            'is_active' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Prepare data
        $data = [
            'questions' => $this->request->getPost('questions'),
            'max_word_count' => $this->request->getPost('max_word_count') ?: null,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Update data
        try {
            $this->programEssayModel->update($id, $data);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Essay updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to update essay',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteEssay($id = null)
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Validate ID
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Essay ID is required']);
        }

        // Check if essay exists
        $essay = $this->programEssayModel->find($id);
        if (!$essay) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Essay not found']);
        }

        // Delete data
        try {
            $this->programEssayModel->delete($id);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Essay deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to delete essay',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getEssayById($id = null)
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Validate ID
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Essay ID is required']);
        }

        // Get essay
        $essay = $this->programEssayModel->find($id);
        if (!$essay) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Essay not found']);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $essay
        ]);
    }
}
