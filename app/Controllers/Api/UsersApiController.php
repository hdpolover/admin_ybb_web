<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\ApiBaseController;

class UsersApiController extends ApiBaseController
{
    protected $model;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->model = new UserModel();
    }

    /**
     * 🟢 Get All Users (READ)
     * GET /api/users
     */
    public function index()
    {
        $limit = $this->request->getGet('limit') ?? 10;
        $offset = $this->request->getGet('offset') ?? 0;
        $filters = $this->request->getGet('filters') ?? [];

        // Validate limit and offset
        if (!is_numeric($limit) || !is_numeric($offset)) {
            return $this->respondValidationErrors("Limit and offset must be numeric values.");
        }

        // Get users from model
        $users = $this->model->getUsers($limit, $offset, $filters);

        if ($users) {
            return $this->respondSuccess($users, self::HTTP_OK, "Users retrieved successfully.");
        } else {
            return $this->respondNotFound("No users found.");
        }
    }

    /**
     * 🟢 Update User 
     * PUT|PATCH /api/users/{id}
     */
    public function update($id = null)
    {
        // Check if ID is provided
        if ($id === null) {
            return $this->respondValidationErrors("User ID is required.");
        }

        // Validate that ID is numeric
        if (!is_numeric($id)) {
            return $this->respondValidationErrors("User ID must be numeric.");
        }

        // Check if user exists
        $user = $this->model->find($id);
        if (!$user) {
            return $this->respondNotFound("User with ID {$id} not found.");
        }

        // Get input data from any source (JSON, form-data, x-www-form-urlencoded)
        $data = $this->getInput();
        
        if (empty($data)) {
            return $this->respondValidationErrors("No data provided for update.");
        }

        // Only allow updating specific fields
        $allowedFields = ['full_name', 'email', 'password', 'is_verified', 'program_category_id', 'is_active'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        if (empty($updateData)) {
            return $this->respondValidationErrors("No valid fields to update.");
        }
        
        // Update user
        if ($this->model->update($id, $updateData)) {
            // Get the updated user data to return
            $updatedUser = $this->model->find($id);
            return $this->respondSuccess($updatedUser, self::HTTP_OK, "User updated successfully.");
        } else {
            return $this->respondError($this->model->errors(), self::HTTP_BAD_REQUEST, "Failed to update user.");
        }
    }

    /**
     * 🔑 Update User Password
     * POST /api/users/:id/password
     */
    public function updatePassword($id = null)
    {
        // Check if ID is provided
        if ($id === null) {
            return $this->respondValidationErrors("User ID is required.");
        }

        // Validate that ID is numeric
        if (!is_numeric($id)) {
            return $this->respondValidationErrors("User ID must be numeric.");
        }

        // Check if user exists
        $user = $this->model->find($id);
        if (!$user) {
            return $this->respondNotFound("User with ID {$id} not found.");
        }

        // Get POST data (supporting both JSON and form inputs)
        $password = $this->request->getJSON(true)['password'] ?? $this->request->getPost('password');
        
        if (empty($password)) {
            return $this->respondValidationErrors("Password is required.");
        }

        // Prepare data for update (only password)
        $data = [
            'password' => $password
        ];

        // Update user password
        if ($this->model->update($id, $data)) {
            return $this->respondSuccess(null, self::HTTP_OK, "Password updated successfully.");
        } else {
            return $this->respondError($this->model->errors(), self::HTTP_BAD_REQUEST, "Failed to update password.");
        }
    }

    /**
     * 🔍 Check Users Based on Parameters
     * GET /api/users/check
     */
    public function checkUserByParams()
    {
        $params = $this->request->getGet();
        
        // Debug: Log the parameters received
        log_message('debug', 'checkUserByParams called with parameters: ' . json_encode($params));
        
        // If parameters are empty or not properly formatted, return error
        if (empty($params)) {
            return $this->respondError('No parameters provided');
        }
        
        // Debug: Log the exact query that will be executed
        log_message('debug', 'Looking for user with parameters: ' . json_encode($params));
        
        $user = $this->model->getUserByParams($params);

        if ($user) {
            return $this->respondSuccess($user, self::HTTP_OK, "User found");
        } else {
            return $this->respondNotFound("User not found with parameters: " . json_encode($params));
        }
    }


    /**
     * check - GET {{api_url}}/users/check?email=user@example.com
     * Auto-generated method
     */
    public function check($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement check logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'check executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute check',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
