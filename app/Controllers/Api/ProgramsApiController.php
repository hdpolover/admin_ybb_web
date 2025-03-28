<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramModel;

class ProgramsApiController extends ApiBaseController
{
    protected $model;

    /**
     * Initialize controller, set model
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);
        
        // Initialize model - this is what was previously in the constructor
        $this->model = new ProgramModel();
    }

    /**
     * Get All Programs
     * GET /api/programs
     */
    public function index()
    {
        $programs = $this->model->getPrograms();
        return $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully');
    }

    /**
     * Get Single Program
     * GET /api/programs/{id}
     */
    public function show($id = null)
    {
        $program = $this->model->find($id);
        
        if (!$program) {
            return $this->respondNotFound('Program not found');
        }
        
        return $this->respondSuccess($program, self::HTTP_OK, 'Program retrieved successfully');
    }
    
    /**
     * Get programs by category ID
     * GET /api/programs/category/{categoryId}
     */
    public function getByCategory($categoryId = null)
    {
        if ($categoryId === null) {
            return $this->respondValidationErrors('Category ID is required');
        }
        
        $programs = $this->model->getPrograms($categoryId);
        return $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully');
    }
    
    /**
     * Get current user's programs based on JWT token
     * Requires JWT authentication
     * GET /api/my-programs
     */
    public function getUserPrograms()
    {
        // Get the request instance
        $request = service('request');
        
        // Get the user data from the JWT token
        $userData = $request->jwtUser ?? null;
        
        if (!$userData || !isset($userData->id)) {
            return $this->respondUnauthorized('Invalid user token');
        }
        
        // Get user's programs - implementation depends on your application logic
        // This might involve checking participant records or other relationships
        $participantModel = new \App\Models\ParticipantModel();
        $programs = $participantModel->getUserPrograms($userData->id);
        
        return $this->respondSuccess($programs, self::HTTP_OK, 'User programs retrieved successfully');
    }
}