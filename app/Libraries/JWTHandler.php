<?php

namespace App\Libraries;

use Config\Services;
use Config\JWT as JWTConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTHandler
{
    protected $config;
    
    public function __construct()
    {
        $this->config = new JWTConfig();
    }
    
    /**
     * Generate a new JWT token
     *
     * @param array $userData User data to include in the token payload
     * @return string The JWT token
     */
    public function generateToken(array $userData): string
    {
        $issuedAtTime = time();
        $tokenExpiration = $issuedAtTime + $this->config->tokenExpiration;
        
        $payload = [
            'iss' => $this->config->tokenIssuer,      // Issuer
            'aud' => $this->config->tokenAudience,    // Audience
            'iat' => $issuedAtTime,                   // Issued at time
            'nbf' => $issuedAtTime,                   // Not before time
            'exp' => $tokenExpiration,                // Expiration time
            'data' => $userData                       // User data
        ];
        
        return JWT::encode($payload, $this->config->secretKey, $this->config->tokenAlgorithm);
    }
    
    /**
     * Validate and decode the JWT token
     *
     * @param string $token JWT token to validate
     * @return object|bool Decoded token payload or false if invalid
     */
    public function validateToken(string $token)
    {
        try {
            return JWT::decode($token, new Key($this->config->secretKey, $this->config->tokenAlgorithm));
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get user data from token
     *
     * @param string $token
     * @return object|bool User data or false if token is invalid
     */
    public function getUserFromToken(string $token)
    {
        $decoded = $this->validateToken($token);
        
        if (!$decoded || empty($decoded->data)) {
            return false;
        }
        
        return $decoded->data;
    }
    
    /**
     * Extract token from Authorization header
     *
     * @return string|null JWT token or null if not found
     */
    public function getTokenFromHeader()
    {
        $request = Services::request();
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader) || strpos($authHeader, 'Bearer') === false) {
            return null;
        }
        
        return trim(str_replace('Bearer', '', $authHeader));
    }
}