<?php

namespace App\Controllers;

use App\Services\EmailService;
use CodeIgniter\Controller;

class ConfigTestController extends Controller
{
    /**
     * Test email configuration
     */
    public function email()
    {
        $emailService = new EmailService();
        
        // Check OAuth status
        $oauthStatus = $emailService->checkOAuthStatus('noreply@ybbfoundation.com');
        
        // Get email config
        $config = config('Email');
        
        return $this->response->setJSON([
            'oauth_status' => $oauthStatus,
            'smtp_config' => [
                'protocol' => $config->protocol ?? 'Not configured',
                'smtp_host' => $config->SMTPHost ?? 'Not configured',
                'smtp_user' => $config->SMTPUser ?? 'Not configured',
                'smtp_port' => $config->SMTPPort ?? 'Not configured',
                'from_email' => $config->fromEmail ?? 'Not configured',
                'from_name' => $config->fromName ?? 'Not configured'
            ],
            'ready_to_send' => $oauthStatus['has_token'] || !empty($config->SMTPHost),
            'recommended_method' => $oauthStatus['has_token'] && !$oauthStatus['is_expired'] ? 'OAuth (Gmail API)' : 'SMTP'
        ]);
    }
    
    /**
     * Send test email
     */
    public function testEmail()
    {
        $request = service('request');
        $toEmail = $request->getPost('email');
        
        if (!$toEmail || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please provide a valid email address'
            ]);
        }
        
        try {
            $emailService = new EmailService();
            
            $subject = 'YBB Email Configuration Test - ' . date('Y-m-d H:i:s');
            $body = '<h1>Email Configuration Test</h1>' .
                   '<p>This is a test email to verify that the YBB email system is working correctly.</p>' .
                   '<p><strong>Test Details:</strong></p>' .
                   '<ul>' .
                   '<li>Sent at: ' . date('Y-m-d H:i:s') . '</li>' .
                   '<li>From: noreply@ybbfoundation.com</li>' .
                   '<li>System: YBB Admin Platform</li>' .
                   '</ul>' .
                   '<p>If you received this email, the configuration is working properly!</p>';
            
            $success = $emailService->sendEmailWithOAuth($toEmail, $subject, $body);
            
            return $this->response->setJSON([
                'success' => $success,
                'message' => $success ? 
                    'Test email sent successfully to ' . $toEmail : 
                    'Failed to send test email'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Test email error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error sending test email: ' . $e->getMessage()
            ]);
        }
    }
}