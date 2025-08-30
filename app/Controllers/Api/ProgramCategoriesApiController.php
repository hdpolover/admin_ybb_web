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
        return $programCategory ? $this->respondSuccess($programCategory, self::HTTP_OK, 'Program Category retrieved successfully') : $this->failNotFound("Program Category not found");
    }

    // get programs based on program category id
    public function getProgramsByCatId($id = null)
    {
        if (is_null($id)) {
            return $this->respondValidationErrors("Program Category ID is required");
        }

        $programs = $this->model->getPrograms($id);
        return $programs ? $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully') : $this->failNotFound("Programs not found");
    }


    /**
     * programs - GET {{api_url}}/program-categories/1/programs
     * Auto-generated method
     */
    public function programs($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement programs logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'programs executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute programs',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
