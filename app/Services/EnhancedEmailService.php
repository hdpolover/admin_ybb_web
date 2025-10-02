<?php

namespace App\Services;

use App\Services\GoogleOAuthService;

class EnhancedEmailService
{
    protected $config;
    protected $googleService;

    public function __construct()
    {
        $this->config = config('Email');
        $this->googleService = new GoogleOAuthService();
    }

    /**
     * Send email with OAuth2 or fallback to SMTP
     */
    public function send(array $data): bool
    {
        $to = $data['to'] ?? '';
        $subject = $data['subject'] ?? '';
        $message = $data['message'] ?? '';
        $from = $data['from'] ?? env('email.fromEmail');

        // Try OAuth2 first
        if ($this->tryOAuthSend($to, $subject, $message, $from)) {
            return true;
        }

        // Fallback to traditional SMTP
        return $this->fallbackSMTPSend($data);
    }

    /**
     * Try sending via OAuth2
     */
    private function tryOAuthSend(string $to, string $subject, string $message, string $from): bool
    {
        try {
            // Get OAuth token for sender email
            $token = $this->getOAuthToken($from);
            
            if (!$token) {
                log_message('info', 'No OAuth token found for ' . $from . ', falling back to SMTP');
                return false;
            }

            // Set token and send
            $this->googleService->setAccessToken($token);
            
            if (!$this->googleService->isTokenValid()) {
                log_message('info', 'OAuth token expired for ' . $from . ', falling back to SMTP');
                return false;
            }

            return $this->googleService->sendEmail($to, $subject, $message, $from);

        } catch (\Exception $e) {
            log_message('error', 'OAuth send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fallback to traditional SMTP
     */
    private function fallbackSMTPSend(array $data): bool
    {
        $email = \Config\Services::email();
        
        $email->setTo($data['to']);
        $email->setSubject($data['subject']);
        $email->setMessage($data['message']);
        
        if (isset($data['from'])) {
            $email->setFrom($data['from'], $data['fromName'] ?? '');
        }

        return $email->send();
    }

    /**
     * Get OAuth token for email
     */
    private function getOAuthToken(string $email): ?array
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
     * Check if OAuth is available for email
     */
    public function hasOAuthToken(string $email): bool
    {
        $token = $this->getOAuthToken($email);
        return $token !== null;
    }
}