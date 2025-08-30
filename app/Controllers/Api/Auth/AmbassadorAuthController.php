<?php

namespace App\Controllers\Api\Auth;

use App\Models\AmbassadorModel;
use App\Models\UserModel;
use App\Libraries\JWTHandler;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Ambassador Authentication Controller
 * 
 * Handles authentication operations for ambassadors
 */
class AmbassadorAuthController extends BaseAuthController
{
    /**
     * Ambassador sign in
     * POST /api/auth/ambassador/sign-in
     */
    public function signIn()
    {
        $email = $this->getInput('email');
        $refCode = $this->getInput('ref_code');

        // Validate input
        if (empty($email) || empty($refCode)) {
            return $this->respondValidationErrors('Email and reference code are required.');
        }

        try {
            // Check ambassador credentials
            $ambassadorModel = new AmbassadorModel();
            $ambassador = $ambassadorModel->signIn($email, $refCode);

            if (!$ambassador) {
                return $this->respondUnauthorized('Invalid email or reference code.');
            }

            // Check if ambassador is active
            if (!$ambassador->is_active) {
                return $this->respondForbidden('Your ambassador account is not active.');
            }

            // Generate JWT token
            $jwtHandler = new JWTHandler();
            $tokenData = [
                'ambassador_id' => $ambassador->id,
                'user_type' => 'ambassador',
                'email' => $ambassador->email,
                'name' => $ambassador->name,
                'program_id' => $ambassador->program_id,
                'ref_code' => $ambassador->ref_code,
                'institution' => $ambassador->institution,
                'is_active' => $ambassador->is_active,
                'created_at' => $ambassador->created_at
            ];

            $token = $jwtHandler->generateToken($tokenData);

            if (!$token) {
                return $this->respondError('Failed to generate authentication token.');
            }

            // Get token expiration from config
            $jwtConfig = new \Config\JWT();
            $expiresIn = $jwtConfig->tokenExpiration;

            // Get ambassador details with referral count
            $ambassadorDetails = $ambassadorModel->getAmbassadorDetails($ambassador->id);

            // Prepare response data
            $responseData = [
                'ambassador' => $ambassador,
                'ambassador_info' => [
                    'id' => $ambassadorDetails->id,
                    'full_name' => $ambassadorDetails->full_name,
                    'email' => $ambassadorDetails->email,
                    'ref_code' => $ambassadorDetails->ref_code,
                    'institution' => $ambassadorDetails->institution,
                    'referral_count' => $ambassadorDetails->referral_count
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn
            ];

            return $this->respondSuccess($responseData, self::HTTP_OK, 'Ambassador sign in successful');
        } catch (\Exception $e) {
            log_message('error', 'Ambassador sign in error: ' . $e->getMessage());
            return $this->respondError('An error occurred during sign in: ' . $e->getMessage());
        }
    }
}