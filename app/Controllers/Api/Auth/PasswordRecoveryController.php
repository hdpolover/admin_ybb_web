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

        if (empty($email)) {
            return $this->respondValidationErrors('Email is required.');
        }

        // If web_url is missing, try to infer it from the user record or the request host
        $userModel = new \App\Models\UserModel();
        $programCategoryModel = new \App\Models\ProgramCategoryModel();

        if (empty($web_url)) {
            // First try to find a user by email and use their program_category
            $userByEmail = $userModel->getUserByEmail($email);
            if ($userByEmail && !empty($userByEmail->program_category_id)) {
                $program = $programCategoryModel->find($userByEmail->program_category_id);
                if ($program && !empty($program->web_url)) {
                    $web_url = $program->web_url;
                    log_message('warning', "PasswordRecovery: web_url was missing; inferred from user program_category_id for {$email} -> {$web_url}");
                }
            }

            // If still missing, try to use the Host header (normalize later)
            if (empty($web_url)) {
                $host = $this->request->getServer('HTTP_HOST') ?: $this->request->getHeaderLine('Host');
                if (!empty($host)) {
                    $web_url = $host;
                    log_message('warning', "PasswordRecovery: web_url missing; using request host for {$email} -> {$web_url}");
                }
            }
        }

        // Normalize the web URL if present
        if (!empty($web_url)) {
            $web_url = normalize_web_url($web_url);
        }

        // Generic response returned for every outcome below to prevent user enumeration.
        $genericResponse = fn () => $this->respondSuccess(
            null,
            self::HTTP_OK,
            'If an account exists for that email, password reset instructions have been sent.'
        );

        // Try to find user by (email + web_url) when web_url is available, otherwise fall back to email-only
        if (!empty($web_url)) {
            $user = $userModel->getUserByEmailAndWebUrl($email, $web_url);
        } else {
            $user = $userModel->getUserByEmail($email);
        }

        if (!$user) {
            log_message('info', 'PasswordRecovery: forgot-password requested for unknown email {email}', ['email' => $email]);
            return $genericResponse();
        }

        try {
            // Generate token
            $token = generate_token(32, true); // Use the enhanced generate_token function

            // Save into database
            $passwordResetModel = new PasswordResetModel();
            $passwordResetModel->createToken($email, $user->id, $token);

            // Ensure we have a web_url for the Program (required by EmailService to include program info)
            if (empty($web_url) && !empty($user->program_category_id)) {
                $program = $programCategoryModel->find($user->program_category_id);
                if ($program && !empty($program->web_url)) {
                    $web_url = $program->web_url;
                }
            }

            // Send email with reset link
            $emailService = new EmailService();
            $emailSent = $emailService->sendPasswordResetEmail($email, $token, $web_url);

            if (!$emailSent) {
                log_message('error', 'PasswordRecovery: email send returned false for {email}', ['email' => $email]);
                return $genericResponse();
            }

            log_message('info', 'PasswordRecovery: reset link sent to {email}', ['email' => $email]);
            return $genericResponse();
        } catch (\Exception $e) {
            log_message('error', 'PasswordRecovery: failed for {email}: {error}', ['email' => $email, 'error' => $e->getMessage()]);
            return $genericResponse();
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
            log_message('error', 'PasswordRecovery: verifyToken failed: {error}', ['error' => $e->getMessage()]);
            return $this->respondError('Failed to verify token. Please try again later.');
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
            log_message('error', 'PasswordRecovery: resetPassword failed: {error}', ['error' => $e->getMessage()]);
            return $this->respondError('Failed to reset password. Please try again later.');
        }
    }
}