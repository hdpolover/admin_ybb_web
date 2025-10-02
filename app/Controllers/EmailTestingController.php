<?php

namespace App\Controllers;

use App\Services\EmailService;
use CodeIgniter\Controller;

class EmailTestingController extends Controller
{
    /**
     * Test forgot password email
     */
    public function testForgotPassword()
    {
        $request = service('request');
        $testEmail = $request->getPost('email') ?: $request->getGet('email');
        
        if (!$testEmail || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please provide a valid test email address'
            ]);
        }
        
        try {
            $emailService = new EmailService();
            
            // Generate a test token
            $testToken = bin2hex(random_bytes(32));
            
            // Test sending forgot password email
            $result = $emailService->sendPasswordResetEmail(
                $testEmail,
                $testToken,
                'ybbfoundation.com' // Using base domain for testing
            );
            
            return $this->response->setJSON([
                'success' => $result,
                'message' => $result ? 
                    'Password reset email sent successfully to ' . $testEmail : 
                    'Failed to send password reset email',
                'token' => $testToken,
                'method_used' => 'Template-based email with OAuth/SMTP fallback'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Forgot password test error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Test direct OAuth email sending
     */
    public function testDirectOAuth()
    {
        $request = service('request');
        $testEmail = $request->getPost('email') ?: $request->getGet('email');
        
        if (!$testEmail || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please provide a valid test email address'
            ]);
        }
        
        try {
            $emailService = new EmailService();
            
            $subject = 'YBB OAuth Test - Direct Gmail API - ' . date('Y-m-d H:i:s');
            $body = '<h1>OAuth Gmail API Test</h1>' .
                   '<p>This email was sent directly via Gmail API using OAuth2 authentication.</p>' .
                   '<p><strong>Test Details:</strong></p>' .
                   '<ul>' .
                   '<li>Sent at: ' . date('Y-m-d H:i:s') . '</li>' .
                   '<li>Method: Gmail API with OAuth2</li>' .
                   '<li>From: noreply@ybbfoundation.com</li>' .
                   '<li>System: YBB Admin Platform</li>' .
                   '</ul>' .
                   '<p>If you received this email, OAuth Gmail API is working correctly!</p>';
            
            $success = $emailService->sendEmailWithOAuth($testEmail, $subject, $body);
            
            return $this->response->setJSON([
                'success' => $success,
                'message' => $success ? 
                    'Direct OAuth email sent successfully to ' . $testEmail : 
                    'Failed to send OAuth email',
                'method_used' => 'Direct OAuth Gmail API'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Direct OAuth test error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Test verification email
     */
    public function testVerificationEmail()
    {
        $request = service('request');
        $testEmail = $request->getPost('email') ?: $request->getGet('email');
        
        if (!$testEmail || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please provide a valid test email address'
            ]);
        }
        
        try {
            $emailService = new EmailService();
            
            // Generate a test token
            $testToken = bin2hex(random_bytes(32));
            
            // Test sending verification email (using program category ID 1)
            $result = $emailService->sendVerificationEmail(
                $testEmail,
                $testToken,
                1 // Default program category ID
            );
            
            return $this->response->setJSON([
                'success' => $result,
                'message' => $result ? 
                    'Verification email sent successfully to ' . $testEmail : 
                    'Failed to send verification email',
                'token' => $testToken,
                'method_used' => 'Template-based email with OAuth/SMTP fallback'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Verification email test error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}