<?php

namespace App\Controllers\Api;

use App\Models\AmbassadorModel;
use App\Controllers\Api\ApiBaseController;

class AmbassadorsApiController extends ApiBaseController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new AmbassadorModel();
    }

    /**
     * 🟢 Get All Ambassadors (READ)
     * GET /api/ambassadors
     */
    public function index()
    {
        try {
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 10);
            $offset = ($page - 1) * $limit;

            // Build filters from query params
            $filters = [];
         
            // Add any additional filters from query params
            foreach ($this->request->getGet() as $key => $value) {
                if (!in_array($key, ['page', 'limit'])) {
                    $filters[$key] = $value;
                }
            }

            // Get data using custom method
            $result = $this->model->getAmbassadors($limit, $offset, $filters);

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
     * 🔍 Get Single Ambassador (READ)
     * GET /api/ambassadors/{id}
     */
    public function show($id = null)
    {
        $ambassador = $this->model->find($id);
        return $ambassador ? $this->apiResponse($ambassador) : $this->failNotFound("Ambassador not found");
    }

    // get participants based on ambassador ref code
    public function getParticipantsByRefCode($refCode = null)
    {
        try {
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 10);
            $offset = ($page - 1) * $limit;

            // Build filters from query params
            $filters = [];
         
            // Add any additional filters from query params
            foreach ($this->request->getGet() as $key => $value) {
                if (!in_array($key, ['page', 'limit'])) {
                    $filters[$key] = $value;
                }
            }

            $filters['ref_code_ambassador'] = $refCode;

            // Get data using custom method
            $result = $this->model->getReferredParticipants($limit, $offset, $filters);

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
}
