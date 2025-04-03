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
     * Get Program by Slug
     * GET /api/programs/slug/{slug}
     */
    public function getBySlug($slug = null)
    {
        if ($slug === null) {
            return $this->respondValidationErrors('Slug is required');
        }
        
        // Convert slug to program name format (replace hyphens with spaces and capitalize words)
        $programName = str_replace('-', ' ', $slug);
        
        // Check if slug contains valid characters
        if (!preg_match('/^[a-zA-Z0-9\- ]+$/', $slug)) {
            return $this->respondValidationErrors('Invalid slug format');
        }
        
        // Get program by name
        $program = $this->model->getProgramByName($programName);
        
        if (!$program) {
            return $this->respondNotFound('Program not found');
        }
        
        return $this->respondSuccess($program, self::HTTP_OK, 'Program retrieved successfully');
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
        
        $programs = $this->model->getAllPrograms($categoryId);

        if (empty($programs)) {
            return $this->respondNotFound('No programs found for this category ID');
        }

        return $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully');
    }
    
    /**
     * Get programs by user ID
     * GET /api/programs/user/{userId}
     */
    public function getByUser($userId = null)
    {
        if ($userId === null) {
            return $this->respondValidationErrors('User ID is required');
        }
        
        // Validate user ID is numeric
        if (!is_numeric($userId)) {
            return $this->respondValidationErrors('Invalid user ID format');
        }
        
        $programs = $this->model->getProgramsByUserId($userId);
        
        if (empty($programs)) {
            return $this->respondNotFound('No programs found for this user');
        }
        
        return $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully');
    }
}