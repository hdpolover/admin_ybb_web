<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramRundownModel;

class ProgramRundownsApiController extends ApiBaseController
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
        $this->model = new ProgramRundownModel(); 
    }

    /**
     * Get All Program Photos
     * GET /api/program-photos
     */
    public function index()
    {
        $programRundowns = $this->model->findAll();
        return $this->respondSuccess($programRundowns, self::HTTP_OK, 'Program rundowns retrieved successfully');
    }

    // get rundowns by program id
    public function getByProgramId($programId = null)
    {
        if ($programId === null) {
            return $this->respondValidationErrors('Program ID is required');
        }

        $programRundowns = $this->model->getByProgramId($programId);

        if (empty($programRundowns)) {
            return $this->respondNotFound('No program rundowns found for this program');
        }

        return $this->respondSuccess($programRundowns, self::HTTP_OK, 'Program rundowns retrieved successfully');
    }

    /**
     * Get Single Program Rundown
     * GET /api/program-rundowns/{id}
     */
    public function show($id = null)
    {
        $programRundown = $this->model->find($id);
        
        if (!$programRundown) {
            return $this->respondNotFound('Program rundown not found');
        }
        
        return $this->respondSuccess($programRundown, self::HTTP_OK, 'Program rundown retrieved successfully');
    }

    /**
     * Get program photos by category ID
     * GET /api/program-photos/category/{categoryId}
     */
    public function getByCategory($categoryId = null)
    {
        if ($categoryId === null) {
            return $this->respondValidationErrors('Category ID is required');
        }

        $programPhotos = $this->model->getActivePhotos($categoryId);

        if (empty($programPhotos)) {
            return $this->respondNotFound('No program photos found for this category');
        }

        return $this->respondSuccess($programPhotos, self::HTTP_OK, 'Program photos retrieved successfully');
    }
}