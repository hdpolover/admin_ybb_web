<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\FaqModel;
use App\Models\ProgramPhotoModel;

class ProgramFaqsApiController extends ApiBaseController
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
        $this->model = new FaqModel();
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
     * Get program faqs by program id
     * GET /api/program-faqs/program/{programId}
     */
    public function getByProgram($programId = null)
    {
        if ($programId === null) {
            return $this->respondValidationErrors('Program ID is required');
        }

        $programFaqs = $this->model->getActiveFaqsByProgramId($programId);

        if (empty($programFaqs)) {
            return $this->respondNotFound('No program faqs found for this program');
        }

        return $this->respondSuccess($programFaqs, self::HTTP_OK, 'Program faqs retrieved successfully');
    }

}