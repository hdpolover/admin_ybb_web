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
     * Request password reset with OTP
     * POST /api/auth/forgot-password
     */
    public function forgotPassword()
    {
        $email = $this->request->getPost('email');
        $web_url = $this->request->getPost('web_url');

        if (empty($email) || empty($web_url)) {
            return $this->respondValidationErrors('Email and web_url are required.');
        }

        // Check if user exists
        $userModel = new UserModel();
        $user = $userModel->getUserByEmailAndWebUrl($email, $web_url);

        if (!$user) {
            return $this->respondNotFound('User not found.');
        }

        try {
            // Generate OTP
            $otp = rand(100000, 999999);

            // Save into database
            $passwordResetModel = new PasswordResetModel();
            $passwordResetModel->createOtp($email, $user->id, $otp);

            // Send email with OTP
            $emailService = new EmailService();
            $emailSent = $emailService->sendPasswordResetEmail($email, $otp, $web_url);

            if (!$emailSent) {
                return $this->respondError('Failed to send OTP email. Please try again later.');
            }

            log_message('info', "OTP for $email is $otp");

            return $this->respondSuccess(null, self::HTTP_OK, 'OTP sent successfully. Please check your email.');
        } catch (\Exception $e) {
            return $this->respondError('Failed to send OTP: ' . $e->getMessage());
        }
    }

    /**
     * Verify OTP for password reset
     * POST /api/auth/verify-otp
     */
    public function verifyOtp()
    {
        $email = $this->request->getPost('email');
        $otp = $this->request->getPost('otp');

        if (empty($email) || empty($otp)) {
            return $this->respondValidationErrors('Email and OTP are required.');
        }

        try {
            $passwordResetModel = new PasswordResetModel();
            $resetData = $passwordResetModel->getOtpByEmailAndOtp($email, $otp);

            if (!$resetData) {
                return $this->respondNotFound('Invalid OTP. Please try again.');
            }

            return $this->respondSuccess($resetData, self::HTTP_OK, 'OTP valid. Please reset your password.');
        } catch (\Exception $e) {
            return $this->respondError('Failed to verify OTP: ' . $e->getMessage());
        }
    }

    /**
     * Reset password after OTP verification
     * POST /api/auth/reset-password
     */
    public function resetPassword()
    {
        $user_id = $this->request->getPost('user_id');
        $newPassword = $this->request->getPost('password');

        if (empty($user_id) || empty($newPassword)) {
            return $this->respondValidationErrors('User ID and new password are required.');
        }

        try {
            // Update password
            $userModel = new UserModel();
            $userModel->updatePassword($user_id, $newPassword);

            // Delete OTP
            $passwordResetModel = new PasswordResetModel();
            $passwordResetModel->deleteOtp($user_id);

            return $this->respondSuccess(null, self::HTTP_OK, 'Password reset successfully. You can now sign in with your new password.');
        } catch (\Exception $e) {
            return $this->respondError('Failed to reset password: ' . $e->getMessage());
        }
    }
}