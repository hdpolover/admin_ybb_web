<?php

namespace App\Controllers\Api;

use App\Models\ParticipantModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\ApiBaseController;

class Participants extends ApiBaseController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ParticipantModel();
    }

   /**
     * 🟢 Get All Participants (READ)
     * GET /api/participants
     * 
     * Query Parameters:
     * @param int page Page number
     * @param int limit Items per page
     * @param int program_id Filter by program ID
     * @param string status Filter by status
     */
    public function index()
    {
        try {
            // Pagination params
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
            $result = $this->model->getParticipants($limit, $offset, $filters);
            
            $totalPages = ceil($result['total'] / $limit);

            // if no data found return 404
            if (empty($result['data'])) {
                return $this->failNotFound("No participants found");
            }
            
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
     * 🔍 Get Single User (READ)
     * GET /api/users/{id}
     */
    public function show($id = null)
    {
        $user = $this->model->find($id);
        return $user ? $this->apiResponse($user) : $this->failNotFound("User not found");
    }

    /**
     * 🆕 Create New User (CREATE)
     * POST /api/users
     */
    public function create()
    {
        $data = $this->request->getJSON(true);

        // Validation
        if (!$this->validate([
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]'
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Hash password before saving
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $this->model->insert($data);
        return $this->apiResponse($data, ResponseInterface::HTTP_CREATED, "User created successfully");
    }

    /**
     * ✏️ Update User (UPDATE)
     * PUT /api/users/{id}
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        // Validation
        if (!$this->validate([
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
            'password' => 'permit_empty|min_length[6]'
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        $this->model->update($id, $data);
        return $this->apiResponse($data, ResponseInterface::HTTP_OK, "User updated successfully");
    }
}
