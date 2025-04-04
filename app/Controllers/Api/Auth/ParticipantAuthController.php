<?php

namespace App\Controllers\Api\Auth;

use App\Models\UserModel;
use App\Models\ParticipantModel;
use App\Services\EmailService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Participant Authentication Controller
 * 
 * Handles authentication operations for participants
 */
class ParticipantAuthController extends BaseAuthController
{
    /**
     * Participant sign in
     * POST /api/auth/participant/sign-in
     */
    public function signIn()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $web_url = $this->request->getPost('web_url');

        // Validate input
        if (empty($email) || empty($password) || empty($web_url)) {
            return $this->respondValidationErrors('Email, password, and web_url are required.');
        }

        try {
            // Check credentials
            $model = new UserModel();
            $user = $model->signIn($email, $password, $web_url);

            if (!$user) {
                return $this->respondUnauthorized('Invalid email or password.');
            }

            // Check if email is verified
            if (isset($user->is_verified) && !$user->is_verified) {
                // Generate a new verification token and send email
                $user = $model->regenerateVerificationToken($email, $web_url);

                if ($user) {
                    // Send verification email
                    $emailService = new EmailService();
                    $emailService->sendVerificationEmail($email, $user->verification_token, $web_url);
                }

                return $this->respondForbidden(lang('EmailVerification.verification_required'));
            }

            // Check if account is active
            if (!property_exists($user, 'is_active') || !$user->is_active) {
                return $this->respondForbidden('Your account is not active.');
            }

            // return data as participant
            return $this->respondSuccess($user, self::HTTP_OK, 'Sign in successful');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred during sign in: ' . $e->getMessage());
        }
    }

    /**
     * Participant sign up
     * POST /api/auth/participant/sign-up
     */
    public function signUp()
    {
        $userModel = new UserModel();
        $participantModel = new ParticipantModel();

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $programCategoryId = $this->request->getPost('program_category_id');
        $programId = $this->request->getPost('program_id');
        $fullName = $this->request->getPost('full_name');

        if (empty($email) || empty($password) || empty($programCategoryId) || empty($programId) || empty($fullName)) {
            return $this->respondValidationErrors('Email, password, program_category_id, program_id, and full_name are required.');
        }

        try {
            // Check if user already exists by params
            $params = [
                'email' => $email,
                'program_category_id' => $programCategoryId,
            ];

            $existingUser = $userModel->getUserByParams($params);

            if ($existingUser) {
                // update user's password
                $userModel->updatePassword($existingUser->id, $password);

                // check if participant already exists
                if (is_object($existingUser)) {
                    $existingParticipant = $participantModel->getParticipantByParams([
                        'program_id' => $programId,
                        'user_id' => $existingUser->id,
                    ]);

                    log_message("error", 'Existing participant: ' . json_encode($existingParticipant));

                    if ($existingParticipant) {
                        return $this->respondValidationErrors('Participant is already registered for this program. Please sign in to continue.');
                    }

                    // Create participant for existing user
                    $participantData = [
                        'user_id' => $existingUser->id,
                        'program_id' => $programId,
                        'full_name' => $fullName,
                    ];

                    $participant = $participantModel->createParticipant($participantData);

                    if (!$participant) {
                        return $this->respondError('Failed to register participant.');
                    }

                    // response data
                    $responseData = [
                        'is_new' => false,
                        'participant' => $participant,
                    ];

                    log_message("info", 'Response Data: ' . json_encode($responseData));

                    return $this->respondSuccess($responseData, self::HTTP_CREATED, 'Participant sign up successful.');
                }
            } else {
                // generate verification token
                $verificationToken = generate_token(6);

                // Create new user and participant
                $userData = [
                    'email' => $email,
                    'password' => $password,
                    'program_category_id' => $programCategoryId,
                    'full_name' => $fullName,
                    'verification_token' => $verificationToken,
                ];

                $user = $userModel->createUser($userData);

                if (!$user) {
                    return $this->respondError('Failed to register user.');
                }

                $participantData = [
                    'user_id' => $user->id,
                    'program_id' => $programId,
                    'full_name' => $fullName,
                ];

                $participant = $participantModel->createParticipant($participantData);

                if (!$participant) {
                    return $this->respondError('Failed to register participant.');
                }

                // Send verification email
                $emailService = new EmailService();
                $emailService->sendVerificationEmail($email, $user->verification_token, $programCategoryId);
                
                // response data
                $responseData = [
                    'is_new' => true,
                    'participant' => $participant,
                ];

                log_message("info", 'Response Data: ' . json_encode($responseData));

                return $this->respondSuccess($responseData, self::HTTP_CREATED, 'Participant sign up successful.');
            }
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage());
        }
    }
}
