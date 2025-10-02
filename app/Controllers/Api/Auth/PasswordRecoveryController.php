<?php

namespace App\Controllers\Api\Auth;

use App\Models\UserModel;
use App\Models\PasswordResetModel;
use App\Services\EmailService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Password Recovery Controller
 * 
 * Handles password recovery and reset operations
 */
class PasswordRecoveryController extends BaseAuthController
{
    
    /**
     * Request password reset with a reset link
     * POST /api/auth/forgot-password
     */
    public function forgotPassword()
    {
        // Accept both POST form data and JSON
        $input = $this->request->getJSON(true) ?: $this->request->getPost();
        
        $email = $input['email'] ?? null;
        $web_url = $input['web_url'] ?? null;

        if (empty($email) || empty($web_url)) {
            return $this->respondValidationErrors('Email and web_url are required.');
        }

        // Normalize the web URL
        $web_url = normalize_web_url($web_url);

        // Check if user exists
        $userModel = new UserModel();
        $user = $userModel->getUserByEmailAndWebUrl($email, $web_url);

        if (!$user) {
            return $this->respondNotFound('User not found.');
        }

        try {
            // Generate token
            $token = generate_token(32, true); // Use the enhanced generate_token function

            // Save into database
            $passwordResetModel = new PasswordResetModel();
            $passwordResetModel->createToken($email, $user->id, $token);

            // Send email with reset link
            $emailService = new EmailService();
            $emailSent = $emailService->sendPasswordResetEmail($email, $token, $web_url);

            if (!$emailSent) {
                return $this->respondError('Failed to send password reset email. Please try again later.');
            }

            log_message('info', "Password reset link sent to {$email} with token: {$token}");

            return $this->respondSuccess(null, self::HTTP_OK, 'Password reset instructions sent to your email.');
        } catch (\Exception $e) {
            return $this->respondError('Failed to initiate password reset: ' . $e->getMessage());
        }
    }

    /**
     * Verify reset token and get user info
     * GET /api/auth/verify-token
     */
    public function verifyToken()
    {
        $token = $this->request->getGet('token');

        if (empty($token)) {
            return $this->respondValidationErrors('Reset token is required.');
        }

        try {
            $passwordResetModel = new PasswordResetModel();
            $resetData = $passwordResetModel->getResetByToken($token);

            if (!$resetData) {
                return $this->respondNotFound('Invalid or expired reset token.');
            }

            // Check if token has expired
            if (!$passwordResetModel->isValidToken($token)) {
                return $this->respondError('Reset token has expired. Please request a new password reset.');
            }

            return $this->respondSuccess($resetData, self::HTTP_OK, 'Token valid. Please reset your password.');
        } catch (\Exception $e) {
            return $this->respondError('Failed to verify token: ' . $e->getMessage());
        }
    }

    /**
     * Reset password using token
     * POST /api/auth/reset-password
     */
    public function resetPassword()
    {
        $token = $this->request->getPost('token');
        $newPassword = $this->request->getPost('password');

        if (empty($token) || empty($newPassword)) {
            return $this->respondValidationErrors('Token and new password are required.');
        }

        try {
            // Verify the token is valid
            $passwordResetModel = new PasswordResetModel();
            $resetData = $passwordResetModel->getResetByToken($token);

            if (!$resetData) {
                return $this->respondNotFound('Invalid reset token.');
            }

            // Check if token has expired
            if (!$passwordResetModel->isValidToken($token)) {
                return $this->respondError('Reset token has expired. Please request a new password reset.');
            }

            // Update password
            $userModel = new UserModel();
            $userModel->updatePassword($resetData->user_id, $newPassword);

            // Delete token after successful reset
            $passwordResetModel->deleteToken($resetData->email);

            return $this->respondSuccess(null, self::HTTP_OK, 'Password reset successfully. You can now sign in with your new password.');
        } catch (\Exception $e) {
            return $this->respondError('Failed to reset password: ' . $e->getMessage());
        }
    }
}