<?php

namespace App\Controllers;

use App\Services\GoogleOAuthService;
use CodeIgniter\Controller;

class EmailTestController extends Controller
{
    /**
     * Test page for OAuth and email functionality
     */
    public function index()
    {
        $data = [
            'pageTitle' => 'OAuth Email Test',
            'baseUrl' => base_url()
        ];
        
        return view('email_test', $data);
    }
    
    /**
     * Check OAuth status
     */
    public function checkOAuth()
    {
        $request = service('request');
        $email = $request->getPost('email') ?: 'noreply@ybbfoundation.com';
        
        $db = \Config\Database::connect();
        $builder = $db->table('oauth_tokens');
        $token = $builder->where('email', $email)->get()->getRow();
        
        if ($token) {
            $isExpired = strtotime($token->expires_at) < time();
            return $this->response->setJSON([
                'success' => true,
                'hasToken' => true,
                'email' => $token->email,
                'expires_at' => $token->expires_at,
                'isExpired' => $isExpired,
                'scope' => $token->scope
            ]);
        } else {
            return $this->response->setJSON([
                'success' => true,
                'hasToken' => false,
                'message' => 'No OAuth token found for ' . $email
            ]);
        }
    }
    
    /**
     * Test email sending
     */
    public function testEmail()
    {
        $request = service('request');
        $fromEmail = $request->getPost('fromEmail') ?: 'noreply@ybbfoundation.com';
        $toEmail = $request->getPost('toEmail');
        $subject = $request->getPost('subject') ?: 'OAuth Test Email';
        $message = $request->getPost('message') ?: '<h1>Test Email</h1><p>This email was sent using OAuth2 via Gmail API.</p>';
        
        if (!$toEmail) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Recipient email is required'
            ]);
        }
        
        try {
            $googleService = new GoogleOAuthService();
            
            // Get stored token
            $db = \Config\Database::connect();
            $builder = $db->table('oauth_tokens');
            $tokenData = $builder->where('email', $fromEmail)->get()->getRow();
            
            if (!$tokenData) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No OAuth token found for ' . $fromEmail . '. Please complete OAuth consent first.'
                ]);
            }
            
            // Check if token is expired
            if (strtotime($tokenData->expires_at) < time()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'OAuth token has expired. Please re-authorize.',
                    'expired' => true
                ]);
            }
            
            // Set access token
            $token = [
                'access_token' => $tokenData->access_token,
                'refresh_token' => $tokenData->refresh_token,
                'expires_in' => strtotime($tokenData->expires_at) - time(),
                'token_type' => $tokenData->token_type,
                'scope' => $tokenData->scope
            ];
            
            $googleService->setAccessToken($token);
            
            // Send email
            $success = $googleService->sendEmail($toEmail, $subject, $message);
            
            if ($success) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Email sent successfully via Gmail API!',
                    'from' => $fromEmail,
                    'to' => $toEmail,
                    'subject' => $subject
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to send email via Gmail API'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Email test error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}