<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Libraries\JWTHandler;
use Config\Services;

class JWTAuthFilter implements FilterInterface
{
    use ResponseTrait;
    
    /**
     * Filter for JWT authentication
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $jwtHandler = new JWTHandler();
        $token = $jwtHandler->getTokenFromHeader();
        
        // No token found
        if (empty($token)) {
            return Services::response()
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Authentication failed - No token provided'
                ])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        // Validate token
        $userData = $jwtHandler->getUserFromToken($token);
        
        // Invalid token
        if (!$userData) {
            return Services::response()
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Authentication failed - Invalid token'
                ])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        // Set user data in request for controllers to use
        $request->jwtUser = $userData;
    }
    
    /**
     * We don't have anything to do here
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}