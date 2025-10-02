<?php

namespace App\Controllers;

use App\Services\GoogleOAuthService;
use CodeIgniter\Controller;

class GoogleAuthController extends Controller
{
    protected $googleService;

    public function __construct()
    {
        $this->googleService = new GoogleOAuthService();
    }

    /**
     * Redirect to Google OAuth
     */
    public function login()
    {
        $authUrl = $this->googleService->getAuthUrl();
        return redirect()->to($authUrl);
    }

    /**
     * Handle OAuth callback
     */
    public function callback()
    {
        $request = service('request');
        $code = $request->getGet('code');
        
        if (!$code) {
            return redirect()->to('/email-test')
                ->with('error', 'Authorization code not received');
        }

        try {
            // Exchange code for token
            $token = $this->googleService->getAccessToken($code);
            
            // Get user info
            $this->googleService->setAccessToken($token);
            $userInfo = $this->googleService->getUserInfo();
            
            // Store token in database or session
            $this->storeTokenForUser($userInfo['email'], $token);
            
            return redirect()->to('/email-test')
                ->with('success', 'Gmail OAuth configured successfully for ' . $userInfo['email']);
                
        } catch (\Exception $e) {
            log_message('error', 'OAuth callback error: ' . $e->getMessage());
            return redirect()->to('/email-test')
                ->with('error', 'OAuth configuration failed: ' . $e->getMessage());
        }
    }

    /**
     * Store OAuth token for user
     */
    private function storeTokenForUser(string $email, array $token)
    {
        // Store in database or cache
        // You might want to create an oauth_tokens table
        
        $db = \Config\Database::connect();
        
        $data = [
            'email' => $email,
            'access_token' => $token['access_token'],
            'refresh_token' => $token['refresh_token'] ?? null,
            'expires_at' => date('Y-m-d H:i:s', time() + ($token['expires_in'] ?? 3600)),
            'token_type' => $token['token_type'] ?? 'Bearer',
            'scope' => $token['scope'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Check if token already exists for this email
        $builder = $db->table('oauth_tokens');
        $existing = $builder->where('email', $email)->get()->getRow();

        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $builder->where('email', $email)->update($data);
        } else {
            $builder->insert($data);
        }
    }

    /**
     * Test email sending
     */
    public function testEmail()
    {
        $request = service('request');
        $email = $request->getPost('email');
        
        if (!$email) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Email address is required'
            ]);
        }

        try {
            // Get stored token
            $token = $this->getStoredToken($email);
            
            if (!$token) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No OAuth token found for this email'
                ]);
            }

            // Set token and send test email
            $this->googleService->setAccessToken($token);
            
            $success = $this->googleService->sendEmail(
                $email,
                'YBB Admin System - OAuth Test',
                '<h1>Gmail OAuth Test</h1><p>This is a test email sent via Gmail API using OAuth2.</p>'
            );

            return $this->response->setJSON([
                'success' => $success,
                'message' => $success ? 'Test email sent successfully!' : 'Failed to send test email'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get stored token for email
     */
    private function getStoredToken(string $email): ?array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('oauth_tokens');
        $token = $builder->where('email', $email)->get()->getRow();

        if (!$token) {
            return null;
        }

        return [
            'access_token' => $token->access_token,
            'refresh_token' => $token->refresh_token,
            'expires_in' => strtotime($token->expires_at) - time(),
            'token_type' => $token->token_type,
            'scope' => $token->scope
        ];
    }

    /**
     * Revoke OAuth token
     */
    public function revoke()
    {
        $request = service('request');
        $email = $request->getPost('email') ?: 'noreply@ybbfoundation.com';
        
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('oauth_tokens');
            $deleted = $builder->where('email', $email)->delete();
            
            return $this->response->setJSON([
                'success' => $deleted > 0,
                'message' => $deleted > 0 ? 
                    'OAuth token revoked successfully for ' . $email :
                    'No token found to revoke for ' . $email
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Token revoke error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error revoking token: ' . $e->getMessage()
            ]);
        }
    }
}