<?php

namespace App\Controllers\Api\Auth;

use App\Models\UserModel;
use App\Models\ProgramCategoryModel;
use App\Services\EmailService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Email Verification Controller
 * 
 * Handles email verification operations
 */
class EmailVerificationController extends BaseAuthController
{
    /**
     * Verify email with token
     * GET /api/auth/verify-email
     * 
     * @return ResponseInterface
     */
    public function verifyEmail()
    {
        $token = $this->request->getGet('token');
        $email = $this->request->getGet('email');

        if (empty($token) || empty($email)) {
            return $this->respondValidationErrors(lang('EmailVerification.verification_failed'));
        }

        try {
            $userModel = new UserModel();
            $verified = $userModel->verifyEmail($email, $token);

            if (!$verified) {
                return $this->respondError(lang('EmailVerification.invalid_token'));
            }

            return $this->respondSuccess(null, self::HTTP_OK, lang('EmailVerification.verification_success'));
        } catch (\Exception $e) {
            log_message('error', 'Email verification failed: {error}', ['error' => $e->getMessage()]);
            return $this->respondError(lang('EmailVerification.verification_failed'));
        }
    }

    /**
     * Resend verification email
     * POST /api/auth/resend-verification
     * 
     * @return ResponseInterface
     */
    public function resendVerification()
    {
        $email = $this->request->getPost('email');
        $web_url = $this->request->getPost('web_url');

        if (empty($email) || empty($web_url)) {
            return $this->respondValidationErrors(lang('EmailVerification.email_not_found'));
        }

        // Normalize the web URL
        $web_url = normalize_web_url($web_url);

        try {
            $userModel = new UserModel();
            $user = $userModel->getUserByEmailAndWebUrl($email, $web_url);

            if (!$user) {
                return $this->respondNotFound(lang('EmailVerification.email_not_found'));
            }

            // Check if already verified
            if ($user->is_verified) {
                return $this->respondSuccess(null, self::HTTP_OK, lang('EmailVerification.already_verified'));
            }

            // Generate new verification token
            $user = $userModel->regenerateVerificationToken($email, $web_url);

            if (!$user) {
                return $this->respondError(lang('EmailVerification.resend_failed'));
            }
            
            // get program category id from web_url
            $programCategoryModel = new ProgramCategoryModel();
            $programData = $programCategoryModel->getProgramCategoryByParams(['web_url' => $web_url]);

            if (!$programData) {
                return $this->respondError(lang('EmailVerification.resend_failed'));
            }

            $programCategoryId = $programData->id ?? null;

            // Send verification email
            $emailService = new EmailService();
            $emailSent = $emailService->sendVerificationEmail($email, $user->verification_token, $programCategoryId);

            if (!$emailSent) {
                return $this->respondError(lang('EmailVerification.resend_failed'));
            }

            return $this->respondSuccess(null, self::HTTP_OK, lang('EmailVerification.resend_success'));
        } catch (\Exception $e) {
            log_message('error', 'Failed to resend verification email: {error}', ['error' => $e->getMessage()]);
            return $this->respondError(lang('EmailVerification.resend_failed'));
        }
    }

    /**
     * Test email sending functionality
     * GET /api/auth/test-email
     * 
     * @return ResponseInterface
     */
    public function testEmail()
    {
        try {
            $emailService = new EmailService();
            $testToken = bin2hex(random_bytes(16));

            $emailSent = $emailService->sendVerificationEmail(
                'test@example.com', // Replace with a test email
                $testToken,
                'test.ybbfoundation.com' // Replace with your test domain
            );

            if (!$emailSent) {
                return $this->respondError('Failed to send test email.');
            }

            return $this->respondSuccess(null, self::HTTP_OK, 'Test email sent successfully.');
        } catch (\Exception $e) {
            return $this->respondError('Email test failed: ' . $e->getMessage());
        }
    }
}