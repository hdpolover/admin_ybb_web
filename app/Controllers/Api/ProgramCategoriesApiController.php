<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramCategoryModel;

class ProgramCategoriesApiController extends ApiBaseController
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
        $this->model = new ProgramCategoryModel();
    }

    /**
     * 🟢 Get All Program Categories (READ)
     * GET /api/program-categories
     */
    public function index()
    {
        try {
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 10);
            $offset = ($page - 1) * $limit;

            // Get data using custom method
            $result = $this->model->getProgramCategories($limit, $offset);
            
            $totalPages = ceil($result['total'] / $limit);
            
            return $this->apiResponse($result['data'], 200, "Success", [
                'current_page' => $page,
                'per_page' => $limit,
                'total_items' => $result['total'],
                'total_pages' => $totalPages
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError('An error occurred: ' . $e->getMessage());
        }
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

    // get programs based on program category id
    public function getProgramsByCatId($id = null)
    {
        $programs = $this->model->getPrograms($id);
        return $programs ? $this->apiResponse($programs) : $this->failNotFound("Programs not found");
    }
}