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
     * Midtrans Server Key
     * 
     * Your Midtrans server key (secret key) - keep this confidential
     * @var string
     */
    protected $serverKey = 'Mid-server-gXaK3X0M-oZhY4RPL0g2Mt_z';
    
    /**
     * Midtrans Client Key
     * 
     * Your Midtrans client key for frontend integration
     * @var string
     */
    protected $clientKey = 'Mid-client-KKoCMEQRJeeFcpOS';
    
    /**
     * Production Mode Flag
     * 
     * Set to true when in production environment
     * @var bool
     */
    protected $isProduction = true;
    
    /**
     * 3DS Transaction Flag
     * 
     * Set to true to enable 3DS for card transactions
     * @var bool
     */
    protected $is3ds = true;
    
    /**
     * Sanitize Flag
     * 
     * Set to true to sanitize sensitive customer data
     * @var bool
     */
    protected $isSanitized = true;
    
    /**
     * Overridden Constructor
     * 
     * Allows for environment-based configuration
     */
    public function __construct()
    {
        // Apply environment-specific settings
        if (getenv('MIDTRANS_SERVER_KEY')) {
            $this->serverKey = getenv('MIDTRANS_SERVER_KEY');
        }
        
        if (getenv('MIDTRANS_CLIENT_KEY')) {
            $this->clientKey = getenv('MIDTRANS_CLIENT_KEY');
        }
        
        if (getenv('MIDTRANS_IS_PRODUCTION')) {
            $this->isProduction = (getenv('MIDTRANS_IS_PRODUCTION') === 'true');
        }
    }
    
    /**
     * Get Server Key
     * 
     * @return string
     */
    public function getServerKey()
    {
        return $this->serverKey;
    }
    
    /**
     * Get Client Key
     * 
     * @return string
     */
    public function getClientKey()
    {
        return $this->clientKey;
    }
    
    /**
     * Check if in production mode
     * 
     * @return bool
     */
    public function isProduction()
    {
        return $this->isProduction;
    }
    
    /**
     * Check if 3DS is enabled
     * 
     * @return bool
     */
    public function is3ds()
    {
        return $this->is3ds;
    }
    
    /**
     * Check if sanitization is enabled
     * 
     * @return bool
     */
    public function isSanitized()
    {
        return $this->isSanitized;
    }
    
    /**
     * Get Midtrans API URL based on current environment
     * 
     * @return string
     */
    public function getApiUrl()
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
    public function getSnapUrl()
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
    public function getAuthorizationString()
    {
        return base64_encode($this->serverKey . ':');
    }
}