<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramScheduleModel;

class ProgramSchedulesApiController extends ApiBaseController
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
        
        // Initialize model
        $this->model = new ProgramScheduleModel(); 
    }

    /**
     * Get All Program Schedules
     * GET /api/program-schedules
     */
    public function index()
    {
        $programSchedules = $this->model->where('is_deleted', 0)->findAll();
        return $this->respondSuccess($programSchedules, self::HTTP_OK, 'Program schedules retrieved successfully');
    }

    /**
     * Get Single Program Schedule
     * GET /api/program-schedules/{id}
     */
    public function show($id = null)
    {
        $programSchedule = $this->model->getProgramScheduleById($id);
        
        if (!$programSchedule) {
            return $this->respondNotFound('Program schedule not found');
        }
        
        return $this->respondSuccess($programSchedule, self::HTTP_OK, 'Program schedule retrieved successfully');
    }

    /**
     * Get program schedules by program ID
     * GET /api/program-schedules/program/{programId}
     */
    public function getByProgramId($programId = null)
    {
        if ($programId === null) {
            return $this->respondValidationErrors('Program ID is required');
        }

        $programSchedules = $this->model->getByProgramId($programId);

        if (empty($programSchedules)) {
            return $this->respondNotFound('No program schedules found for this program');
        }

        return $this->respondSuccess($programSchedules, self::HTTP_OK, 'Program schedules retrieved successfully');
    }

}
