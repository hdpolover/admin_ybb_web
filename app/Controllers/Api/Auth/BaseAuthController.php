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
        
        return $this->jwtHandler->getUserFromToken($token);
    }
}