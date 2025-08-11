<?php

namespace App\Config\Midtrans;

/**
 * Midtrans Configuration Class
 * 
 * This class handles the configuration settings for Midtrans payment gateway.
 */
class Config
{
    /**
     * Midtrans Server Key (Production)
     * 
     * Your Midtrans production server key (secret key) - keep this confidential
     * @var string
     */
    public $serverKey = '';

    /**
     * Midtrans Client Key (Production)
     * 
     * Your Midtrans production client key for frontend integration
     * @var string
     */
    public $clientKey = '';

    /**
     * Midtrans Sandbox Server Key
     * 
     * Your Midtrans sandbox server key for testing
     * @var string
     */
    public $sbServerKey = '';

    /**
     * Midtrans Sandbox Client Key
     * 
     * Your Midtrans sandbox client key for testing
     * @var string
     */
    public $sbClientKey = '';

    /**
     * Production Mode Flag
     * 
     * Set to true when in production environment
     * @var bool
     */
    public $isProduction = false;

    /**
     * 3DS Transaction Flag
     * 
     * Set to true to enable 3DS for card transactions
     * @var bool
     */
    public $is3ds = true;

    /**
     * Sanitize Flag
     * 
     * Set to true to sanitize sensitive customer data
     * @var bool
     */
    public $isSanitized = true;

    /**
     * Constructor - Load from environment variables
     * 
     * IMPORTANT: This configuration is NEVER cached and always loads fresh
     * from environment variables to ensure payment security and prevent
     * configuration drift issues.
     */
    public function __construct()
    {
        // Force reload environment variables - bypassing any potential PHP opcode caching
        // This ensures payment configuration is ALWAYS current from .env file
        $this->loadEnvironmentVariables();
    }

    /**
     * Load environment variables fresh from source
     * This method ensures no variable caching occurs for payment configuration
     * 
     * @return void
     */
    private function loadEnvironmentVariables(): void
    {
        // Clear any existing environment cache if available
        if (function_exists('opcache_reset')) {
            // Reset opcache to ensure fresh environment variable loading
            @opcache_reset();
        }

        // Load configuration from environment variables with multiple fallback methods
        // to ensure we always get the most current values
        $this->serverKey = $this->getEnvVariable('MIDTRANS_SERVER_KEY');
        $this->clientKey = $this->getEnvVariable('MIDTRANS_CLIENT_KEY');
        $this->sbServerKey = $this->getEnvVariable('MIDTRANS_SB_SERVER_KEY');
        $this->sbClientKey = $this->getEnvVariable('MIDTRANS_SB_CLIENT_KEY');
        
        $prodEnv = $this->getEnvVariable('MIDTRANS_IS_PRODUCTION', 'false');
        $this->isProduction = ($prodEnv === 'true' || $prodEnv === '1' || $prodEnv === 1);
        
        $is3dsEnv = $this->getEnvVariable('MIDTRANS_IS_3DS', 'true');
        $this->is3ds = ($is3dsEnv === 'true' || $is3dsEnv === '1' || $is3dsEnv === 1);
        
        $isSanitizedEnv = $this->getEnvVariable('MIDTRANS_IS_SANITIZED', 'true');
        $this->isSanitized = ($isSanitizedEnv === 'true' || $isSanitizedEnv === '1' || $isSanitizedEnv === 1);
    }

    /**
     * Get environment variable with multiple fallback methods
     * This prevents any caching issues by trying multiple sources
     * 
     * @param string $key Environment variable key
     * @param string $default Default value if not found
     * @return string
     */
    private function getEnvVariable(string $key, string $default = ''): string
    {
        // Try getenv() first (direct system environment)
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }

        // Try $_ENV superglobal (parsed environment)
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        // Try $_SERVER superglobal (server environment)
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }

        // Return default value
        return $default;
    }

    /**
     * Get the appropriate server key based on environment
     * 
     * @return string
     */
    public function getServerKey(): string
    {
        return $this->isProduction ? $this->serverKey : $this->sbServerKey;
    }

    /**
     * Get the appropriate client key based on environment
     * 
     * @return string
     */
    public function getClientKey(): string
    {
        return $this->isProduction ? $this->clientKey : $this->sbClientKey;
    }

    /**
     * Check if in production mode
     * 
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    /**
     * Check if 3DS is enabled
     * 
     * @return bool
     */
    public function is3ds(): bool
    {
        return $this->is3ds;
    }

    /**
     * Check if sanitization is enabled
     * 
     * @return bool
     */
    public function isSanitized(): bool
    {
        return $this->isSanitized;
    }

    /**
     * Get Midtrans API URL based on current environment
     * 
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->isProduction ?
            'https://api.midtrans.com' :
            'https://api.sandbox.midtrans.com';
    }

    /**
     * Get Midtrans Snap URL based on current environment
     * 
     * @return string
     */
    public function getSnapUrl(): string
    {
        return $this->isProduction ?
            'https://app.midtrans.com/snap/v1/transactions' :
            'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Get base64 encoded authorization string
     * 
     * @return string
     */
    public function getAuthorizationString(): string
    {
        return base64_encode($this->getServerKey() . ':');
    }
}
