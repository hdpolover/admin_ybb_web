<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AdminModel;

class Profile extends BaseController
{
    protected $adminModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    public function index()
    {
        // Get current user from session
        $userId = session('adminId');
        if (!$userId) {
            return redirect()->to('/')->with('error', 'Please log in to access your profile.');
        }

        $user = $this->adminModel->find($userId);
        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }

        $data = [
            'title' => 'My Profile',
            'user' => $user
        ];

        return view('admin/profile/index', $data);
    }

    public function update()
    {
        log_message('info', 'Profile update started');
        
        $userId = session('adminId');
        log_message('info', 'Profile update - User ID from session: ' . ($userId ?? 'null'));
        
        if (!$userId) {
            log_message('error', 'Profile update - No user ID in session');
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $user = $this->adminModel->find($userId);
        log_message('info', 'Profile update - User found: ' . ($user ? 'yes' : 'no'));
        
        if (!$user) {
            log_message('error', 'Profile update - User not found for ID: ' . $userId);
            return $this->response->setJSON(['success' => false, 'message' => 'User not found']);
        }

        $postData = $this->request->getPost();
        log_message('info', 'Profile update - POST data: ' . json_encode($postData));

        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[255]'
        ];

        // Check if email is being changed and if it's unique
        if ($this->request->getPost('email') !== $user->email) {
            $rules['email'] .= '|is_unique[admins.email,id,' . $userId . ']';
            log_message('info', 'Profile update - Email change detected, adding uniqueness check');
        }

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            log_message('error', 'Profile update - Validation failed: ' . json_encode($errors));
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Validation failed',
                'errors' => $errors
            ]);
        }

        $updateData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        log_message('info', 'Profile update - Update data: ' . json_encode($updateData));

        $updateResult = $this->adminModel->update($userId, $updateData);
        log_message('info', 'Profile update - Model update result: ' . ($updateResult ? 'success' : 'failed'));
        
        if ($updateResult) {
            // Update session data
            session()->set([
                'user_name' => $updateData['name'],
                'user_email' => $updateData['email']
            ]);

            log_message('info', 'Profile update - Session updated successfully');
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Profile updated successfully'
            ]);
        } else {
            $dbError = $this->adminModel->errors();
            log_message('error', 'Profile update - Database error: ' . json_encode($dbError));
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Failed to update profile',
                'debug' => $dbError
            ]);
        }
    }

    public function changePassword()
    {
        $userId = session('adminId');
        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $user = $this->adminModel->find($userId);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found']);
        }

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]|max_length[255]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Verify current password
        if (!password_verify($this->request->getPost('current_password'), $user->password)) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Current password is incorrect'
            ]);
        }

        // Update password
        $newPasswordHash = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);
        $updateData = [
            'password' => $newPasswordHash,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->adminModel->update($userId, $updateData)) {
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Password changed successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Failed to change password'
            ]);
        }
    }
}