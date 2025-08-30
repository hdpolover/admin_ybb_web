<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramPaymentModel;

class ProgramPaymentsApiController extends ApiBaseController
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
        $this->model = new ProgramPaymentModel();
    }

    /**
     * Get All Program Payments
     * GET /api/program-payments
     * Medium Priority Cache: 30 minutes TTL
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        $programPayments = $this->cacheResponse(function() {
            return $this->model->findAll();
        }, [], null, 1800); // 30 minutes cache

        return $this->respondSuccess($programPayments, self::HTTP_OK, 'Program payments retrieved successfully');
    }

    /**
     * Get Single Program Payment
     * GET /api/program-payments/{id}
     * Medium Priority Cache: 30 minutes TTL
     */
    public function show($id = null)
    {
        $programPayment = $this->cacheResponse(function() use ($id) {
            return $this->model->find($id);
        }, ['program_payment_id' => $id], null, 1800); // 30 minutes cache

        if (!$programPayment) {
            return $this->respondNotFound('Program payment not found');
        }

        return $this->respondSuccess($programPayment, self::HTTP_OK, 'Program payment retrieved successfully');
    }

    /**
     * Get program payments by program ID
     * GET /api/program-payments/program/{programId}
     * Medium Priority Cache: 30 minutes TTL
     */
    public function getByProgramId($programId = null)
    {
        if ($programId === null) {
            return $this->respondValidationErrors('Program ID is required');
        }

        $programPayments = $this->cacheProgramData(function() use ($programId) {
            // Get all payments (active and inactive, but exclude deleted)
            // Frontend will handle filtering, so we return all available payments
            return $this->model->getByProgramId($programId, false, false);
        }, $programId);

        if (!$programPayments) {
            return $this->respondNotFound('Program payments not found for this program ID');
        }

        return $this->respondSuccess($programPayments, self::HTTP_OK, 'Program payments retrieved successfully');
    }
    /**
     * Get program schedule by ID
     * GET /api/program-schedules/{id}
     */
    public function getById($id = null)
    {
        if ($id === null) {
            return $this->respondValidationErrors('ID is required');
        }

        $programSchedule = $this->model->getProgramScheduleById($id);

        if (!$programSchedule) {
            return $this->respondNotFound('Program schedule not found');
        }

        return $this->respondSuccess($programSchedule, self::HTTP_OK, 'Program schedule retrieved successfully');
    }

}
