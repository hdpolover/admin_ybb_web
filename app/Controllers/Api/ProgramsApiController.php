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
     * High Priority Cache: 2 hours TTL
     */
    public function index()
    {
        $programs = $this->cacheResponse(function() {
            return $this->model->getPrograms();
        }, [], null, 7200); // 2 hours cache
        
        return $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully');
    }

    /**
     * Get Program by Slug
     * GET /api/programs/slug/{slug}
     * High Priority Cache: 2 hours TTL
     */
    public function getBySlug($slug = null)
    {
        if ($slug === null) {
            return $this->respondValidationErrors('Slug is required');
        }
        
        // Check if slug contains valid characters
        if (!preg_match('/^[a-zA-Z0-9\- ]+$/', $slug)) {
            return $this->respondValidationErrors('Invalid slug format');
        }
        
        // Convert slug to program name format (replace hyphens with spaces and capitalize words)
        $programName = str_replace('-', ' ', $slug);
        
        $program = $this->cacheResponse(function() use ($programName) {
            return $this->model->getProgramByName($programName);
        }, ['slug' => $slug], null, 7200); // 2 hours cache
        
        if (!$program) {
            return $this->respondNotFound('Program not found');
        }
        
        return $this->respondSuccess($program, self::HTTP_OK, 'Program retrieved successfully');
    }

    /**
     * Get Single Program
     * GET /api/programs/{id}
     * High Priority Cache: 2 hours TTL
     */
    public function show($id = null)
    {
        $program = $this->cacheProgramData(function() use ($id) {
            return $this->model->find($id);
        }, $id);
        
        if (!$program) {
            return $this->respondNotFound('Program not found');
        }
        
        return $this->respondSuccess($program, self::HTTP_OK, 'Program retrieved successfully');
    }
    
    /**
     * Get programs by category ID
     * GET /api/programs/category/{categoryId}
     * High Priority Cache: 1 hour TTL
     */
    public function getByCategory($categoryId = null)
    {
        if ($categoryId === null) {
            return $this->respondValidationErrors('Category ID is required');
        }
        
        $programs = $this->cacheResponse(function() use ($categoryId) {
            return $this->model->getAllPrograms($categoryId);
        }, ['category_id' => $categoryId], null, 3600); // 1 hour cache

        if (empty($programs)) {
            return $this->respondNotFound('No programs found for this category ID');
        }

        return $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully');
    }

    /**
     * Get programs not in a category
     * GET /api/programs/not-in-category/{categoryId}
     * Medium Priority Cache: 30 minutes TTL
     */
    public function getNotInCategory($categoryId = null)
    {
        if ($categoryId === null) {
            return $this->respondValidationErrors('Category ID is required');
        }
        
        // Validate category ID is numeric
        if (!is_numeric($categoryId)) {
            return $this->respondValidationErrors('Invalid category ID format');
        }
        
        $programs = $this->cacheResponse(function() use ($categoryId) {
            return $this->model->getOtherPrograms($categoryId);
        }, ['exclude_category_id' => $categoryId], null, 1800); // 30 minutes cache
        
        if (empty($programs)) {
            return $this->respondNotFound('No programs found outside this category');
        }
        
        return $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully');
    }
    
    /**
     * Get programs by user ID
     * GET /api/programs/user/{userId}
     * Medium Priority Cache: 15 minutes TTL
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
        
        $programs = $this->cacheUserData(function() use ($userId) {
            return $this->model->getProgramsByUserId($userId);
        }, $userId);
        
        if (empty($programs)) {
            return $this->respondNotFound('No programs found for this user');
        }
        
        return $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully');
    }
}