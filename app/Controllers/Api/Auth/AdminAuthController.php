<?php

namespace App\Controllers\Api\Auth;

use App\Models\AdminModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Admin Authentication Controller
 * 
 * Handles authentication operations for admin users
 */
class AdminAuthController extends BaseAuthController
{
    /**
     * Admin sign in
     * POST /api/auth/admin/sign-in
     */
    public function signIn()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Validate input
        if (empty($email) || empty($password)) {
            return $this->respondValidationErrors('Email and password are required.');
        }

        try {
            // Check credentials
            $model = new AdminModel();
            $admin = $model->signIn($email, $password);

            if (!$admin) {
                return $this->respondUnauthorized('Invalid email or password.');
            }

            // Check if admin is active
            if (!$admin->is_active) {
                return $this->respondForbidden('Your account has been deactivated.');
            }

            // return data as admin
            return $this->respondSuccess($admin, self::HTTP_OK, 'Sign in successful');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred during sign in: ' . $e->getMessage());
        }
    }

    /**
     * Admin sign up (accessible only to super admins)
     * POST /api/auth/admin/sign-up
     */
    public function signUp()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (empty($email) || empty($password)) {
            return $this->respondValidationErrors('Email and password are required.');
        }

        try {
            $model = new AdminModel();
            $admin = $model->createAdmin($email, $password);

            if (!$admin) {
                return $this->respondError('Failed to register admin.');
            }

            return $this->respondSuccess($admin, self::HTTP_CREATED, 'Admin sign up successful.');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage());
        }
    }
}