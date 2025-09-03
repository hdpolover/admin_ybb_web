<?php

namespace App\Controllers;

use App\Models\AbstractReviewerModel;
use App\Models\AbstractReviewerSubthemeModel;
use App\Models\ProgramModel;
use App\Models\ProgramSubthemeModel;
use App\Models\WebSettingModel;

class AbstractReviewers extends AdminBaseController
{
    protected $abstractReviewerModel;
    protected $abstractReviewerSubthemeModel;
    protected $programModel;
    protected $programSubthemeModel;
    protected $webSettingModel;

    public function __construct()
    {
        $this->abstractReviewerModel = new AbstractReviewerModel();
        $this->abstractReviewerSubthemeModel = new AbstractReviewerSubthemeModel();
        $this->programModel = new ProgramModel();
        $this->programSubthemeModel = new ProgramSubthemeModel();
        $this->webSettingModel = new WebSettingModel();
    }

    public function index()
    {
        // Get current program ID from session
        $programId = session('current_program');

        if (!$programId) {
            return redirect()->to('/welcome')->with('error', 'Please select a program first');
        }

        // Get program data
        $program = $this->programModel->find($programId);

        if (!$program) {
            return redirect()->to('/welcome')->with('error', 'Selected program not found');
        }

        // Get reviewers for the current program
        $reviewers = $this->abstractReviewerModel->getReviewersByProgramId($programId);

        // Get program subthemes for dropdowns
        $programSubthemes = $this->programSubthemeModel->getActiveSubthemes($programId);

        $webSettings = $this->webSettingModel->getSettingByProgramId($program->program_category_id);

        $data = [
            'title' => 'Abstract Reviewers',
            'reviewers' => $reviewers,
            'programSubthemes' => $programSubthemes,
            'webSettings' => $webSettings,
            'program' => $program,
        ];

        return view('master-data/abstract-reviewers/index', $data);
    }

    /**
     * Get reviewers data for DataTables
     */
    public function getData()
    {
        $programId = session('current_program');

        if (!$programId) {
            return $this->response->setJSON([
                'draw' => $this->request->getGet('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        $reviewers = $this->abstractReviewerModel->getReviewersByProgramId($programId);

        $data = [];
        foreach ($reviewers as $reviewer) {
            // Get assigned subthemes
            $assignedSubthemes = $this->abstractReviewerSubthemeModel->getSubthemesByReviewerId($reviewer->id);
            $subthemeNames = array_map(function ($subtheme) {
                return $subtheme->subtheme_name;
            }, $assignedSubthemes);
            $data[] = [
                'id' => $reviewer->id,
                'name' => $reviewer->name,
                'email' => $reviewer->email,
                'institution' => $reviewer->institution,
                'role' => $reviewer->role ?? 'external', // Using enum values: 'super', 'internal', 'external'
                'subthemes' => implode(', ', $subthemeNames),
                'is_active' => $reviewer->is_active,
                'created_at' => $reviewer->created_at,
                'updated_at' => $reviewer->updated_at
            ];
        }

        return $this->response->setJSON([
            'draw' => $this->request->getGet('draw'),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data
        ]);
    }

    /**
     * Create a new reviewer
     */
    public function create()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|max_length[100]',
            'institution' => 'required|min_length[3]|max_length[100]',
            'password' => 'required|min_length[6]|max_length[100]',
            'role' => 'required|in_list[super,internal,external]',
            'subthemes' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $programId = session('current_program');
        if (!$programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No program selected'
            ]);
        }

        // Check if email already exists for this program
        $existingReviewer = $this->abstractReviewerModel
            ->where('email', $this->request->getPost('email'))
            ->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->first();

        if ($existingReviewer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'A reviewer with this email already exists for this program'
            ]);
        }

        try {
            $data = [
                'program_id' => $programId,
                'name' => $this->request->getPost('name'),
                'email' => $this->request->getPost('email'),
                'institution' => $this->request->getPost('institution'),
                'role' => $this->request->getPost('role'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'is_active' => 1,
                'is_deleted' => 0
            ];

            $reviewerId = $this->abstractReviewerModel->insert($data);

            if ($reviewerId) {
                // Assign subthemes if provided
                $subthemes = $this->request->getPost('subthemes');
                if (!empty($subthemes) && is_array($subthemes)) {
                    $this->abstractReviewerSubthemeModel->assignSubthemes($reviewerId, $subthemes);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Reviewer created successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create reviewer'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get reviewer data for editing
     */
    public function edit($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reviewer ID is required'
            ]);
        }

        $reviewer = $this->abstractReviewerModel->getReviewerById($id);
        if (!$reviewer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reviewer not found'
            ]);
        }

        // Get assigned subthemes
        $assignedSubthemes = $this->abstractReviewerSubthemeModel->getSubthemesByReviewerId($id);
        $assignedSubthemeIds = array_map(function ($subtheme) {
            return $subtheme->program_subtheme_id;
        }, $assignedSubthemes);

        $reviewer->assigned_subthemes = $assignedSubthemeIds;

        return $this->response->setJSON([
            'success' => true,
            'data' => $reviewer
        ]);
    }

    /**
     * Update a reviewer
     */
    public function update($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reviewer ID is required'
            ]);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|max_length[100]',
            'institution' => 'required|min_length[3]|max_length[100]',
            'password' => 'permit_empty|min_length[6]|max_length[100]',
            'role' => 'required|in_list[super,internal,external]',
            'subthemes' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $reviewer = $this->abstractReviewerModel->getReviewerById($id);
        if (!$reviewer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reviewer not found'
            ]);
        }

        // Check if email already exists for another reviewer in this program
        $programId = session('current_program');
        $existingReviewer = $this->abstractReviewerModel
            ->where('email', $this->request->getPost('email'))
            ->where('program_id', $programId)
            ->where('id !=', $id)
            ->where('is_deleted', 0)
            ->first();

        if ($existingReviewer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'A reviewer with this email already exists for this program'
            ]);
        }

        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'email' => $this->request->getPost('email'),
                'institution' => $this->request->getPost('institution'),
                'role' => $this->request->getPost('role'),
            ];

            // Update password only if provided
            $password = $this->request->getPost('password');
            if (!empty($password)) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $updated = $this->abstractReviewerModel->update($id, $data);

            if ($updated) {
                // Update subtheme assignments
                $subthemes = $this->request->getPost('subthemes');
                if (is_array($subthemes)) {
                    $this->abstractReviewerSubthemeModel->assignSubthemes($id, $subthemes);
                } else {
                    // Remove all assignments if no subthemes selected
                    $this->abstractReviewerSubthemeModel->removeAllAssignments($id);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Reviewer updated successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update reviewer'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a reviewer (soft delete)
     */
    public function delete($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reviewer ID is required'
            ]);
        }

        $reviewer = $this->abstractReviewerModel->getReviewerById($id);
        if (!$reviewer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reviewer not found'
            ]);
        }

        try {
            $deleted = $this->abstractReviewerModel->update($id, [
                'is_deleted' => 1,
                'is_active' => 0
            ]);

            if ($deleted) {
                // Also remove subtheme assignments
                $this->abstractReviewerSubthemeModel->removeAllAssignments($id);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Reviewer deleted successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete reviewer'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Toggle reviewer active status
     */
    public function toggleStatus($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reviewer ID is required'
            ]);
        }

        $reviewer = $this->abstractReviewerModel->getReviewerById($id);
        if (!$reviewer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reviewer not found'
            ]);
        }

        try {
            $newStatus = $reviewer->is_active ? 0 : 1;
            $updated = $this->abstractReviewerModel->update($id, ['is_active' => $newStatus]);

            if ($updated) {
                $statusText = $newStatus ? 'activated' : 'deactivated';
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Reviewer {$statusText} successfully"
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update reviewer status'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get program subthemes for dropdown
     */
    public function getSubthemes()
    {
        $programId = session('current_program');

        if (!$programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No program selected'
            ]);
        }

        $subthemes = $this->programSubthemeModel->getActiveSubthemes($programId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $subthemes
        ]);
    }
}
