<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Google\Service\Oauth2;
use Exception;

class GoogleOAuthService
{
    protected $client;
    protected $config;

    public function __construct()
    {
        $this->config = new \App\Config\GoogleOAuth();
        $this->initializeClient();
    }

    /**
     * Initialize Google Client
     */
    private function initializeClient()
    {
        $this->client = new Client();
        $this->client->setClientId($this->config->getClientId());
        $this->client->setClientSecret($this->config->getClientSecret());
        $this->client->setRedirectUri($this->config->getRedirectUri());
        $this->client->setScopes($this->config->getScopes());
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');
    }

    /**
     * Get OAuth2 authorization URL
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken(string $code): array
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        
        if (array_key_exists('error', $token)) {
            throw new Exception('Error fetching access token: ' . $token['error_description']);
        }

        return $token;
    }

    /**
     * Set access token
     */
    public function setAccessToken(array $token)
    {
        $this->client->setAccessToken($token);

        // If token is expired and we have refresh token, refresh it
        if ($this->client->isAccessTokenExpired()) {
            $refreshToken = $this->client->getRefreshToken();
            if ($refreshToken) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                if (array_key_exists('error', $newToken)) {
                    log_message('error', 'Token refresh failed: ' . ($newToken['error_description'] ?? $newToken['error']));
                }
            }
        }
    }

    /**
     * Send email via Gmail API
     */
    public function sendEmail(string $to, string $subject, string $body, string $from = null): bool
    {
        try {
            $service = new Gmail($this->client);
            
            // Create message
            $message = new Message();
            
            $rawMessage = $this->createRawMessage($to, $subject, $body, $from);
            $message->setRaw($rawMessage);
            
            // Send message
            $result = $service->users_messages->send('me', $message);
            
            return !empty($result->getId());
            
        } catch (Exception $e) {
            log_message('error', 'Gmail API send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create raw email message
     */
    private function createRawMessage(string $to, string $subject, string $body, string $from = null): string
    {
        if (!$from) {
            $from = env('email.fromEmail') ?: 'noreply@ybbfoundation.com';
        }

        $headers = [
            'To' => $to,
            'From' => $from,
            'Subject' => $subject,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Transfer-Encoding' => 'base64'
        ];

        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= "$key: $value\r\n";
        }

        $message = $headerString . "\r\n" . $body;
        
        return base64url_encode($message);
    }

    /**
     * Get user info from Google
     */
    public function getUserInfo(): array
    {
        $oauth = new Oauth2($this->client);
        $userInfo = $oauth->userinfo->get();
        
        // Convert to array manually since toArray() may not exist
        return [
            'id' => $userInfo->getId(),
            'email' => $userInfo->getEmail(),
            'verified_email' => $userInfo->getVerifiedEmail(),
            'name' => $userInfo->getName(),
            'given_name' => $userInfo->getGivenName(),
            'family_name' => $userInfo->getFamilyName(),
            'picture' => $userInfo->getPicture(),
            'locale' => $userInfo->getLocale()
        ];
    }

    /**
     * Check if token is valid
     */
    public function isTokenValid(): bool
    {
        return !$this->client->isAccessTokenExpired();
    }

    /**
     * Refresh access token
     */
    public function refreshToken(): array
    {
        if ($this->client->getRefreshToken()) {
            return $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
        }
        
        throw new Exception('No refresh token available');
    }
}

/**
 * Helper function for base64url encoding
 */
if (!function_exists('base64url_encode')) {
    function base64url_encode($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}