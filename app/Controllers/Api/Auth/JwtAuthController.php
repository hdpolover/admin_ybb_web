<?php

namespace App\Controllers\Api\Auth;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\AdminModel;
use App\Services\EmailService;

/**
 * JWT Authentication Controller
 * 
 * Handles JWT-based authentication operations
 */
class JwtAuthController extends BaseAuthController
{
    /**
     * Login with JWT authentication
     * POST /api/auth/sign-in-jwt
     */
    public function signInJwt()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]',
            'type' => 'required|integer|in_list[1,2,3]' // 1=admin, 2=participant, 3=ambassador
        ];

        if (!$this->validate($rules)) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $type = $this->request->getPost('type');
        $web_url = $this->request->getPost('web_url');

        // Get user based on type
        $user = null;
        switch ($type) {
            case 1: // admin
                $adminModel = new AdminModel();
                $user = $adminModel->signIn($email, $password);
                
                // Check if user exists and authentication succeeded
                if (!$user) {
                    return $this->respondError('Invalid email or password', ResponseInterface::HTTP_UNAUTHORIZED);
                }
                
                // Check if admin is active
                if (!$user->is_active) {
                    return $this->respondForbidden('Your account has been deactivated.');
                }
                break;
                
            case 2: // participant
                $userModel = new UserModel();
                $user = $userModel->signIn($email, $password, $web_url);
                
                // Check if user exists and authentication succeeded
                if (!$user) {
                    return $this->respondError('Invalid email or password', ResponseInterface::HTTP_UNAUTHORIZED);
                }
                
                // Check email verification
                if (isset($user->email_not_verified) && $user->email_not_verified) {
                    // Generate a new verification token and send email
                    $user = $userModel->regenerateVerificationToken($email, $web_url);
                    
                    if ($user) {
                        // Send verification email
                        $emailService = new EmailService();
                        $emailService->sendVerificationEmail($email, $user->verification_token, $web_url);
                    }
                    
                    return $this->respondForbidden(lang('EmailVerification.verification_required'));
                }
                
                // Check if account is active
                if (!$user->is_active) {
                    return $this->respondForbidden('Your account is not active.');
                }
                break;
                
            case 3: // ambassador
                return $this->respondNotImplemented('JWT login for ambassador is not implemented yet.');
        }

        // Generate JWT token based on user type
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'type' => $type,
        ];
        
        // Add additional user data based on type
        if ($type == 2 && isset($user->full_name)) {
            $userData['full_name'] = $user->full_name;
        }
        
        if (isset($user->role)) {
            $userData['role'] = $user->role;
        }

        if (isset($user->program_category_id)) {
            $userData['program_category_id'] = $user->program_category_id;
        }

        $token = $this->jwtHandler->generateToken($userData);

        // Return token to client
        return $this->respondSuccess([
            'token' => $token,
            'user' => $userData
        ], ResponseInterface::HTTP_OK, 'Sign in successful');
    }

    /**
     * Get current user profile from JWT token
     * GET /api/auth/profile
     */
    public function profile()
    {
        $userData = $this->getAuthenticatedUser();
        
        if (!$userData) {
            return $this->respondUnauthorized('Invalid token or no token provided');
        }
        
        // Get complete user data from database based on user type
        $user = null;
        switch ($userData->type) {
            case 1: // admin
                $adminModel = new AdminModel();
                $user = $adminModel->find($userData->id);
                break;
                
            case 2: // participant
                $userModel = new UserModel();
                $user = $userModel->find($userData->id);
                break;
                
            case 3: // ambassador
                return $this->respondNotImplemented('Profile retrieval for ambassador is not implemented yet.');
                
            default:
                return $this->respondError('Invalid user type');
        }
        
        if (!$user) {
            return $this->respondNotFound('User not found');
        }
        
        // Remove sensitive data
        if (is_array($user) && isset($user['password'])) {
            unset($user['password']);
        } elseif (is_object($user) && isset($user->password)) {
            unset($user->password);
        }
        
        return $this->respondSuccess($user);
    }

    /**
     * Refresh JWT token
     * POST /api/auth/refresh
     */
    public function refreshToken()
    {
        $userData = $this->getAuthenticatedUser();
        
        if (!$userData) {
            return $this->respondUnauthorized('Invalid token or no token provided');
        }
        
        // Generate new token
        $newToken = $this->jwtHandler->generateToken((array)$userData);
        
        return $this->respondSuccess([
            'token' => $newToken
        ], ResponseInterface::HTTP_OK, 'Token refreshed successfully');
    }
}