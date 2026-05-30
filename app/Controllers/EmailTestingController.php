<?php

namespace App\Controllers;

use App\Services\EmailService;
use CodeIgniter\Controller;

class EmailTestingController extends Controller
{
    /**
     * Send a real password-reset email to verify the Resend pipeline end-to-end.
     */
    public function testForgotPassword()
    {
        $testEmail = $this->validatedEmail();
        if ($testEmail instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $testEmail;
        }

        try {
            $emailService = new EmailService();
            $testToken = bin2hex(random_bytes(32));
            $result = $emailService->sendPasswordResetEmail($testEmail, $testToken, null);

            return $this->response->setJSON([
                'success'     => $result,
                'message'     => $result
                    ? 'Password reset email sent to ' . $testEmail
                    : 'Failed to send password reset email. Check server logs.',
                'method_used' => 'Resend',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'EmailTesting: forgot password test failed: {error}', ['error' => $e->getMessage()]);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Internal error. Check server logs for details.',
            ]);
        }
    }

    /**
     * Send a real verification email to verify the Resend pipeline end-to-end.
     */
    public function testVerificationEmail()
    {
        $testEmail = $this->validatedEmail();
        if ($testEmail instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $testEmail;
        }

        try {
            $emailService = new EmailService();
            $testToken = bin2hex(random_bytes(32));
            $result = $emailService->sendVerificationEmail($testEmail, $testToken, 1);

            return $this->response->setJSON([
                'success'     => $result,
                'message'     => $result
                    ? 'Verification email sent to ' . $testEmail
                    : 'Failed to send verification email. Check server logs.',
                'method_used' => 'Resend',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'EmailTesting: verification test failed: {error}', ['error' => $e->getMessage()]);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Internal error. Check server logs for details.',
            ]);
        }
    }

    /**
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    private function validatedEmail()
    {
        $request = service('request');
        $email = $request->getPost('email') ?: $request->getGet('email');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please provide a valid test email address',
            ]);
        }

        return $email;
    }
}
