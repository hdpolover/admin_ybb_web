<?php

namespace App\Controllers\Api\Auth;

use App\Models\UserModel;
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
        $web_url = $this->request->getGet('web_url');

        if (empty($token) || empty($email)) {
            return $this->respondValidationErrors(lang('EmailVerification.verification_failed'));
        }

        try {
            $userModel = new UserModel();
            $verified = $userModel->verifyEmail($email, $token);

            if (!$verified) {
                return $this->respondError(lang('EmailVerification.invalid_token'));
            }

            // Redirect to login page with success message
            if (!empty($web_url)) {
                return redirect()->to("https://{$web_url}/login?verified=1&message=" . urlencode(lang('EmailVerification.verification_success')));
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

            // Send verification email
            $emailService = new EmailService();
            $emailSent = $emailService->sendVerificationEmail($email, $user->verification_token, $web_url);

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