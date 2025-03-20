<?php

namespace App\Controllers\Api;

use App\Models\ParticipantModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\ApiBaseController;
use App\Models\AdminModel;

class AuthApiController extends ApiBaseController
{
    // sign in
    public function signIn($email = null, $password = null, $type = null)
    {
        // If parameters are not provided, try to get from request
        if ($email === null) {
            $email = $this->request->getPost('email');
        }
        if ($password === null) {
            $password = $this->request->getPost('password');
        }
        if ($type === null) {
            $type = $this->request->getPost('type');
        }

        if (empty($type)) {
            return $this->respondValidationErrors('Type is required.');
        }

        switch ($type) {
            case 1:
                return $this->participantSignIn($email, $password);
            case 2:
                return $this->ambassadorSignIn();
            case 3:
                return $this->reviewerSignIn();
            case 4:
                return $this->adminSignIn($email, $password);
            default:
                return $this->respondValidationErrors('Invalid type.');
        }
    }

    // admin sign in
    public function adminSignIn($email = null, $password = null)
    {
        // If parameters are not provided, try to get from request
        if ($email === null) {
            $email = $this->request->getPost('email');
        }
        if ($password === null) {
            $password = $this->request->getPost('password');
        }

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

    // participant sign in
    public function participantSignIn($email = null, $password = null)
    {
        // If parameters are not provided, try to get from request
        if ($email === null) {
            $email = $this->request->getPost('email');
        }
        if ($password === null) {
            $password = $this->request->getPost('password');
        }

        // Validate input
        if (empty($email) || empty($password)) {
            return $this->respondValidationErrors('Email and password are required.');
        }

        try {
            // Check credentials
            $model = new ParticipantModel();
            $participant = $model->login($email, $password);

            if (!$participant) {
                return $this->respondUnauthorized('Invalid email or password.');
            }

            // if participant found, check if the account is active
            if (!property_exists($participant, 'is_active') || !$participant->is_active) {
                return $this->respondForbidden('Your account is not active.');
            }

            // return data as participant
            return $this->respondSuccess($participant, self::HTTP_OK, 'Sign in successful');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred during sign in: ' . $e->getMessage());
        }
    }

    // ambassador sign in
    public function ambassadorSignIn()
    {
        // Implement ambassador sign in logic here
        return $this->respondNotImplemented('Ambassador sign in not implemented yet.');
    }

    // reviewer sign in
    public function reviewerSignIn()
    {
        // Implement reviewer sign in logic here
        return $this->respondNotImplemented('Reviewer sign in not implemented yet.');
    }
}
