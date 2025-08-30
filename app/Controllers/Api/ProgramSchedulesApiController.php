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


    /**
     * program - GET {{api_url}}/program-schedules/program/{{program_id}}
     * Auto-generated method
     */
    public function program($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement program logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'program executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute program',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
