<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramSubthemeModel;

class ProgramSubthemeApiController extends ApiBaseController
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
        $this->model = new ProgramSubthemeModel();
    }

    /**
     * Get All Program Subthemes
     * GET /api/program-subthemes
     */
    public function index()
    {
        $programSubthemes = $this->model->where('is_active', 1)
                                        ->where('is_deleted', 0)
                                        ->findAll();
        return $this->respondSuccess($programSubthemes, self::HTTP_OK, 'Program subthemes retrieved successfully');
    }

    /**
     * Get Single Program Subtheme
     * GET /api/program-subthemes/{id}
     */
    public function show($id = null)
    {
        $programSubtheme = $this->model->where('id', $id)
                                      ->where('is_active', 1)
                                      ->where('is_deleted', 0)
                                      ->first();

        if (!$programSubtheme) {
            return $this->respondNotFound('Program subtheme not found');
        }

        return $this->respondSuccess($programSubtheme, self::HTTP_OK, 'Program subtheme retrieved successfully');
    }
    /**
     * Get program subthemes by program ID
     * Get program subthemes by program ID
     * GET /api/program-subthemes/program/{programId}
     */
    public function getByProgramId($programId = null)
    {
        if ($programId === null) {
            return $this->respondValidationErrors('Program ID is required');
        }

        $programSubthemes = $this->model->getActiveSubthemes($programId);

        if (empty($programSubthemes)) {
            return $this->respondNotFound('No active subthemes found for this program');
        }

        return $this->respondSuccess($programSubthemes, self::HTTP_OK, 'Program subthemes retrieved successfully');
    }
}
