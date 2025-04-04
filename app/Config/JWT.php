<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class JWT extends BaseConfig
{
    /**
     * JWT Secret Key - This should be stored in an .env file in production
     * @var string
     */
    public $secretKey = 'your-secret-key-change-this-for-production';
    
    /**
     * Token expiration time in seconds
     * Default: 1 hour
     * @var int
     */
    public $tokenExpiration = 3600;
    
    /**
     * Token issuer name
     * @var string
     */
    public $tokenIssuer = 'https://yourdomain.com';
    
    /**
     * Token audience
     * @var string
     */
    public $tokenAudience = 'https://yourdomain.com';
    
    /**
     * The algorithm used to sign the token
     * @var string
     */
    public $tokenAlgorithm = 'HS256';
}