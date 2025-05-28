<?php

namespace App\Controllers\Api\Auth;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\AdminModel;
use App\Services\EmailService;
use App\Controllers\Api\ProgramCategoriesApiController;
use App\Models\ProgramCategoryModel;
use App\Models\AmbassadorModel;

/**
 * JWT Authentication Controller
 * 
 * Handles JWT-based authentication operations
 */
class JwtAuthController extends BaseAuthController
{
    /**
     * Login with JWT authentication
     * POST /api/auth/sign-in
     */
    public function signInJwt()
    {
        $rules = [
            'email' => 'required|valid_email',
            'type' => 'required|integer|in_list[1,2,3]' // 1=admin, 2=participant, 3=ambassador
        ];

        $type = $this->request->getPost('type');

        // if type is 3, require ref_code
        if ($type == 3) {
            $rules['ref_code'] = 'required';
        } else if ($type == 2) {
            $rules['password'] = 'required';
        }

        if (!$this->validate($rules)) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $ref_code = $this->request->getPost('ref_code');
        $type = $this->request->getPost('type');
        $web_url = $this->request->getPost('web_url');

        // normalize the web URL
        $web_url = normalize_web_url($web_url);

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
                $authData = $userModel->signIn($email, $password, $web_url);

                // Handle authentication result based on the authData from UserModel
                if (!$authData || !isset($authData['is_authenticated'])) {
                    return $this->respondError('Invalid email or password', ResponseInterface::HTTP_UNAUTHORIZED);
                }

                // Check if the user account is deleted
                if (isset($authData['user']) && isset($authData['user']->is_deleted) && $authData['user']->is_deleted) {
                    return $this->respondForbidden('Your account has been deleted. Please contact support.');
                }

                // Check if the user account is inactive
                if (isset($authData['user']) && isset($authData['user']->is_active) && !$authData['user']->is_active) {
                    return $this->respondForbidden('Your account is not active. Please contact support.');
                }

                // Check if the user's email is not verified
                if (isset($authData['user']) && isset($authData['user']->is_verified) && !$authData['user']->is_verified) {
                    // Get program category information
                    $programCategoryModel = new ProgramCategoryModel();
                    $programCategory = $programCategoryModel->find($authData['user']->program_category_id);

                    // Check if verification is required for this program
                    if ($programCategory && $programCategory->verification_required) {
                        // Generate a new verification token and send email
                        $updatedUser = $userModel->regenerateVerificationToken($email, $web_url);

                        if ($updatedUser) {
                            // Send verification email
                            $emailService = new EmailService();
                            $emailService->sendVerificationEmail($email, $updatedUser->verification_token, $updatedUser->program_category_id);
                            
                            return $this->respondForbidden(
                                'Your email is not verified. We have sent a new verification email to your address. Please check your inbox and spam folder.'
                            );
                        } else {
                            return $this->respondForbidden(
                                'Your email is not verified. We could not send a verification email. Please contact support.'
                            );
                        }
                    }
                }

                // Check if authentication failed
                if (!$authData['is_authenticated']) {
                    // Return the specific error message from the model
                    return $this->respondError(
                        $authData['message'] ?? 'Invalid credentials', 
                        ResponseInterface::HTTP_UNAUTHORIZED
                    );
                }

                // Authentication successful, set user
                $user = $authData['user'];
                break;

            case 3: // ambassador
                $ambassadorModel = new AmbassadorModel();
                $user = $ambassadorModel->signIn($email, $ref_code);

                // Check if user exists and authentication succeeded
                if (!$user) {
                    return $this->respondError('Invalid email or referral code', ResponseInterface::HTTP_UNAUTHORIZED);
                }

                if (!$user->is_active) {
                    return $this->respondForbidden('Your account has been deactivated.');
                }
                break;
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

        // if type is 3, add ref_code
        if ($type == 3) {
            $userData['ref_code'] = $user->ref_code;
            $userData['name'] = $user->name;
            $userData['program_id'] = $user->program_id;
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
                $ambassadorModel = new AmbassadorModel();
                $user = $ambassadorModel->find($userData->id);
                if (!$user) {
                    return $this->respondNotFound('Ambassador profile not found');
                }
                break;

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
