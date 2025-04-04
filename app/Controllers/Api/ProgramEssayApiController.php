<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramEssayModel;

class ProgramEssayApiController extends ApiBaseController
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
        $this->model = new ProgramEssayModel();
    }

    /**
     * Get All Program Essays
     * GET /api/program-essays
     */
    public function index()
    {
        $programEssays = $this->model->findAll();
        return $this->respondSuccess($programEssays, self::HTTP_OK, 'Program essays retrieved successfully');
    }

    /**
     * Get Single Program Essay
     * GET /api/program-essays/{id}
     */
    public function show($id = null)
    {
        $programEssay = $this->model->find($id);

        if (!$programEssay) {
            return $this->respondNotFound('Program essay not found');
        }

        return $this->respondSuccess($programEssay, self::HTTP_OK, 'Program essay retrieved successfully');
    }

    /**
     * Get program essays by program ID
     * GET /api/program-essays/program/{programId}
     */
    public function getByProgramId($programId = null)
    {
        if ($programId === null) {
            return $this->respondValidationErrors('Program ID is required');
        }

        $programEssays = $this->model->getActiveEssays($programId);

        if (empty($programEssays)) {
            return $this->respondNotFound('No program essays found for this program ID');
        }

        return $this->respondSuccess($programEssays, self::HTTP_OK, 'Program essays retrieved successfully');
    }

    /**
     * Get active program essays by program ID
     * GET /api/program-essays/active/program/{programId}
     */
    public function getActiveByProgramId($programId = null)
    {
        if ($programId === null) {
            return $this->respondValidationErrors('Program ID is required');
        }

        $programEssays = $this->model->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->findAll();

        if (empty($programEssays)) {
            return $this->respondNotFound('No active program essays found for this program ID');
        }

        return $this->respondSuccess($programEssays, self::HTTP_OK, 'Active program essays retrieved successfully');
    }

}
