<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramModel;

class Programs extends ApiBaseController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
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