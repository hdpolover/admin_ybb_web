<?php

namespace App\Controllers;

use App\Models\PasswordResetModel;
use App\Models\UserModel;
use App\Models\ProgramCategoryModel;

class PasswordResetController extends BaseController
{
    /**
     * Display password reset form
     * GET /reset-password
     */
    public function index()
    {
        $token = $this->request->getGet('token');
        
        if (empty($token)) {
            $data = [
                'pageTitle' => 'Invalid Reset Link',
                'error' => 'Reset token is missing. Please request a new password reset link.',
                'showForm' => false
            ];
            return view('password_reset', $data);
        }

        // Verify the token
        $passwordResetModel = new PasswordResetModel();
        $resetData = $passwordResetModel->getResetByToken($token);

        if (!$resetData || !$passwordResetModel->isValidToken($token)) {
            $data = [
                'pageTitle' => 'Invalid or Expired Token',
                'error' => 'Invalid or expired token. Please request a new password reset link.',
                'showForm' => false
            ];
            return view('password_reset', $data);
        }

        // Get user and program information
        $userModel = new UserModel();
        $user = $userModel->find($resetData->user_id);
        
        $programData = null;
        if ($user && $user->program_category_id) {
            $programCategoryModel = new ProgramCategoryModel();
            $programData = $programCategoryModel->find($user->program_category_id);
            
            // Debug logging
            log_message('debug', 'Password Reset Debug - User ID: ' . $resetData->user_id . 
                       ', Program Category ID: ' . $user->program_category_id . 
                       ', Program Data: ' . json_encode($programData));
        } else {
            log_message('debug', 'Password Reset Debug - No user or program category ID found. User: ' . json_encode($user));
        }

        // Token is valid, show the reset form
        $data = [
            'pageTitle' => 'Reset Your Password',
            'token' => $token,
            'email' => $resetData->email,
            'showForm' => true,
            'error' => null,
            'programData' => $programData
        ];

        return view('password_reset', $data);
    }

    /**
     * Handle password reset form submission
     * POST /reset-password
     */
    public function resetPassword()
    {
        $token = $this->request->getPost('token');
        $newPassword = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validation
        if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
            return redirect()->back()->with('error', 'All fields are required.');
        }

        if ($newPassword !== $confirmPassword) {
            return redirect()->back()->with('error', 'Passwords do not match.');
        }

        if (strlen($newPassword) < 8) {
            return redirect()->back()->with('error', 'Password must be at least 8 characters long.');
        }

        try {
            // Verify the token is still valid
            $passwordResetModel = new PasswordResetModel();
            $resetData = $passwordResetModel->getResetByToken($token);

            if (!$resetData || !$passwordResetModel->isValidToken($token)) {
                return redirect()->back()->with('error', 'Invalid or expired reset token. Please request a new password reset.');
            }

            // Get user and program information for success page
            $userModel = new UserModel();
            $user = $userModel->find($resetData->user_id);
            
            $programData = null;
            if ($user && $user->program_category_id) {
                $programCategoryModel = new ProgramCategoryModel();
                $programData = $programCategoryModel->find($user->program_category_id);
            }
            
            // Update password
            $userModel->updatePassword($resetData->user_id, $newPassword);

            // Delete the used token
            $passwordResetModel->deleteToken($resetData->email);

            $data = [
                'pageTitle' => 'Password Reset Successful',
                'success' => 'Your password has been reset successfully. You can now sign in with your new password.',
                'showForm' => false,
                'error' => null,
                'programData' => $programData
            ];

            return view('password_reset', $data);

        } catch (\Exception $e) {
            log_message('error', 'Password reset error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reset password. Please try again.');
        }
    }
}