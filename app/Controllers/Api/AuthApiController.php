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
            return $this->failValidationErrors("Type is required.");
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
                return $this->failValidationErrors("Invalid type.");
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
            return $this->failValidationErrors("Email and password are required.");
        }

        // Check credentials
        $model = new AdminModel();
        $admin = $model->signIn($email, $password);

        // return data as admin
        return $this->respond([
            'status' => 'success',
            'message' => 'Sign in successful',
            'data' => $admin,
        ], ResponseInterface::HTTP_OK);
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
            return $this->failValidationErrors("Email and password are required.");
        }

        // Check credentials
        $model = new ParticipantModel();
        $participant = $model->login($email, $password);

        // if participant found, check if the account is active
        if (!$participant->is_active) {
            return $this->failForbidden("Your account is not active.");
        }

        // return data as participant
        return $this->respond([
            'status' => 'success',
            'message' => 'Sign in successful',
            'data' => $participant,
        ], ResponseInterface::HTTP_OK);
    }

    // ambassador sign in
    public function ambassadorSignIn()
    {
        // Implement ambassador sign in logic here
        return $this->respond([
            'status' => 'error',
            'message' => 'Ambassador sign in not implemented yet.'
        ], ResponseInterface::HTTP_NOT_IMPLEMENTED);
    }

    // reviewer sign in
    public function reviewerSignIn()
    {
        // Implement reviewer sign in logic here
        return $this->respond([
            'status' => 'error',
            'message' => 'Reviewer sign in not implemented yet.'
        ], ResponseInterface::HTTP_NOT_IMPLEMENTED);
    }
}
