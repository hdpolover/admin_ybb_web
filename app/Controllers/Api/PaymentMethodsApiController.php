<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramPhotoModel;

class PaymentMethodsApiController extends ApiBaseController
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
        $this->model = new ProgramPhotoModel();
    }

    /**
     * Get All Program Photos
     * GET /api/program-photos
     */
    public function index()
    {
        $programPhotos = $this->model->findAll();
        return $this->respondSuccess($programPhotos, self::HTTP_OK, 'Program photos retrieved successfully');
    }

    /**
     * Get Single Program Photo
     * GET /api/program-photos/{id}
     */
    public function show($id = null)
    {
        $programPhoto = $this->model->find($id);
        
        if (!$programPhoto) {
            return $this->respondNotFound('Program photo not found');
        }
        
        return $this->respondSuccess($programPhoto, self::HTTP_OK, 'Program photo retrieved successfully');
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