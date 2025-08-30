<?php

namespace App\Controllers\Api\Auth;

use App\Controllers\Api\ApiBaseController;
use App\Libraries\JWTHandler;

/**
 * Base Authentication Controller
 * 
 * Contains common functionality used by all authentication controllers
 */
class BaseAuthController extends ApiBaseController
{
    protected $jwtHandler;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);
        
        // Initialize JWT Handler
        $this->jwtHandler = new JWTHandler();

         // Load the helper that contains the generate_token function
         helper('otp');
         
         // Load the helper that contains the normalize_web_url function
         helper('web_url');
    }
    
    /**
     * Get JWT token from header and validate it
     * 
     * @return object|false User data from token or false if invalid
     */
    protected function getAuthenticatedUser()
    {
        $token = $this->jwtHandler->getTokenFromHeader();
        
        if (empty($token)) {
            return false;
        }
        
        $userData = $this->jwtHandler->getUserFromToken($token);
        
        if (!$userData) {
            return false;
        }
        
        // Handle different token formats for backward compatibility
        if (isset($userData->type)) {
            // Legacy JwtAuthController format
            switch ($userData->type) {
                case 3: // ambassador
                    return (object) [
                        'ambassador_id' => $userData->id,
                        'user_type' => 'ambassador',
                        'email' => $userData->email,
                        'name' => $userData->name ?? null,
                        'program_id' => $userData->program_id ?? null,
                        'ref_code' => $userData->ref_code ?? null,
                        'is_active' => true // Assume active if token is valid
                    ];
                    
                case 2: // participant
                    return (object) [
                        'user_id' => $userData->id,
                        'user_type' => 'participant',
                        'email' => $userData->email,
                        'full_name' => $userData->full_name ?? null,
                        'role' => $userData->role ?? null,
                        'program_category_id' => $userData->program_category_id ?? null
                    ];
                    
                case 1: // admin
                    return (object) [
                        'user_id' => $userData->id,
                        'user_type' => 'admin',
                        'email' => $userData->email,
                        'role' => $userData->role ?? null
                    ];
                    
                default:
                    return false;
            }
        } elseif (isset($userData->user_type)) {
            // New format: user_type field is already present
            return $userData;
        }
        
        return false;
    }
}