<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\ApiBaseController;
use App\Models\AdminModel;
use App\Models\PasswordResetModel;
use App\Models\ProgramCategoryModel;


class AuthApiController extends ApiBaseController
{
   
    public function signIn($email = null, $password = null, $type = null, $web_url = null)
    {
        // type 1 = participant, 2 = ambassador, 3 = reviewer, 4 = admin

        // If parameters are not provided, try to get from request
        if ($type === null) {
            $type = $this->request->getPost('type');
        }

        if (empty($type)) {
            return $this->respondValidationErrors('Type is required.');
        }

        switch ($type) {
            case 1:
                return $this->participantSignIn($email, $password, $web_url);
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
    public function participantSignIn($email = null, $password = null, $web_url = null)
    {
        // If parameters are not provided, try to get from request
        if ($email === null) {
            $email = $this->request->getPost('email');
        }

        if ($password === null) {
            $password = $this->request->getPost('password');
        }

        if ($web_url === null) {
            $web_url = $this->request->getPost('web_url');
        }

        // Validate input
        if (empty($email) || empty($password) || empty($web_url)) {
            return $this->respondValidationErrors('Email and password are required.');
        }

        try {
            // Check credentials
            $model = new UserModel();
            $user = $model->signIn($email, $password, $web_url);

            if (!$user) {
                return $this->respondUnauthorized('Invalid email or password.');
            }

            // if participant found, check if the account is active
            if (!property_exists($user, 'is_active') || !$user->is_active) {
                return $this->respondForbidden('Your account is not active.');
            }

            // return data as participant
            return $this->respondSuccess($user, self::HTTP_OK, 'Sign in successful');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred during sign in: ' . $e->getMessage());
        }
    }

    public function signUp()
    {
        return $this->respondNotImplemented('Sign up not implemented yet.');
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

    public function forgotPassword()
    {
        $email = $this->request->getPost('email');
        $web_url = $this->request->getPost('web_url');
        if (empty($email) || empty($web_url)) {
            return $this->respondValidationErrors('Email and web_url are required.');
        }

        // Check if user exists
        $userModel = new UserModel();
        $user = $userModel->getUserByEmailAndWebUrl($email, $web_url);

        if (!$user) {
            return $this->respondNotFound('User not found.');
        }

        try {
             // Generate OTP
            $otp = rand(100000, 999999);

            // Save into database
            $passwordResetModel = new PasswordResetModel();
            $passwordResetModel->createOtp($email, $otp);

            log_message('info', "OTP for $email is $otp");

            return $this->respondSuccess(['otp' => $otp], self::HTTP_OK, 'OTP sent successfully. Please check your email.');
        } catch (\Exception $e) {
            return $this->responError('Failed to sent OTP: ' . $e->getMessage());
        }
    }

    public function verifyOtp()
    {
        $email = $this->request->getPost('email');
        $otp = $this->request->getPost('otp');

        if (empty($email) || empty($otp)) {
            return $this->respondValidationErrors('Email and OTP are required.');
        }
        
        try {
            $passwordResetModel = new PasswordResetModel();
            $resetData = $passwordResetModel->getOtpByEmailAndOtp($email, $otp);

            if (!$resetData) {
                return $this->respondNotFound('Invalid OTP. Please try again.');
            }

            return $this->respondSuccess($resetData, self::HTTP_OK, 'OTP valid. Please reset your password.');

        } catch (\Exception $e) {
            return $this->respondError('Failed to verify OTP: ' . $e->getMessage());
        }
    }

    public function resetPassword()
    {
        $email = $this->request->getPost('email');
        $web_url = $this->request->getPost('web_url');
        $otp = $this->request->getPost('otp');
        $newPassword = $this->request->getPost('password');

        if (empty($email) || empty($otp) || empty($newPassword)) {
            return $this->respondValidationErrors('Email, OTP, and new password are required.');
        }

        try {
            $passwordResetModel = new PasswordResetModel();
            $resetData = $passwordResetModel->getOtpByEmailAndOtp($email, $otp);

            if (!$resetData) {
                return $this->respondNotFound('Invalid OTP. Please try again.');
            }

            // Validate new password
            if (strlen($newPassword) < 6) {
                return $this->respondValidationErrors('Password must be at least 6 characters long.');
            }

            // Update password user
            $userModel = new UserModel();
            $updatePassword = $userModel->updatePassword($resetData->email, $newPassword);
            if (!$updatePassword) {
                return $this->respondNotFound('Failed to update password. Please try again.');
            }

            // Delete OTP from database
            $passwordResetModel->deleteOtp($resetData->email);

            // Optionally, you can send a success email or notification here
            return $this->respondSuccess(['message' => 'Password reset successfully.'], self::HTTP_OK, 'Password reset successful.');
        } catch (\Exception $e) {
            return $this->respondError('Failed to validate password: ' . $e->getMessage());
        }
    }
}
