<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class GoogleOAuth extends BaseConfig
{
    /**
     * Google OAuth2 Configuration
     * All sensitive values should be set in .env file
     */
    public array $google = [
        'scopes'        => [
            'https://www.googleapis.com/auth/gmail.send',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile'
        ],
        'access_type'   => 'offline',
        'approval_prompt' => 'force'
    ];

    /**
     * Get configuration from environment variables
     * These MUST be set in your .env file
     */
    public function getClientId(): string
    {
        $clientId = env('GOOGLE_CLIENT_ID');
        if (empty($clientId)) {
            throw new \RuntimeException('GOOGLE_CLIENT_ID must be set in .env file');
        }
        return $clientId;
    }

    public function getClientSecret(): string
    {
        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        if (empty($clientSecret)) {
            throw new \RuntimeException('GOOGLE_CLIENT_SECRET must be set in .env file');
        }
        return $clientSecret;
    }

    public function getRedirectUri(): string
    {
        $redirectUri = env('GOOGLE_REDIRECT_URI');
        if (empty($redirectUri)) {
            throw new \RuntimeException('GOOGLE_REDIRECT_URI must be set in .env file');
        }
        return $redirectUri;
    }

    public function getScopes(): array
    {
        return $this->google['scopes'];
    }
}