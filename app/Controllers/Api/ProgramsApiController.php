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
     * 🟢 Get All Program Categories (READ)
     * GET /api/program-categories
     */
    public function index()
    {
        $programs = $this->model->getProgramms();
        return $this->apiResponse($programs);
    }

    /**
     * 🔍 Get Single Program Category (READ)
     * GET /api/program-categories/{id}
     */
    public function show($id = null)
    {
        $programCategory = $this->model->find($id);
        return $programCategory ? $this->apiResponse($programCategory) : $this->failNotFound("Program Category not found");
    }

}