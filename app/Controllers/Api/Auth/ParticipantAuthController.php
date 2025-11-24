<?php

namespace App\Controllers\Api\Auth;

use App\Models\AmbassadorModel;
use App\Models\AmbassadorParticipantReferralModel;
use App\Models\UserModel;
use App\Models\ParticipantModel;
use App\Services\EmailService;
use App\Libraries\JWTHandler;
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
        $email = $this->getInput('email');
        $password = $this->getInput('password');
        $web_url = $this->getInput('web_url');

        // Validate input
        if (empty($email) || empty($password) || empty($web_url)) {
            return $this->respondValidationErrors('Email, password, and web_url are required.');
        }

        // Normalize the web URL
        $web_url = normalize_web_url($web_url);

        try {
            // Check credentials
            $model = new UserModel();
            $authData = $model->signIn($email, $password, $web_url);

            // Check if authentication failed
            if (!$authData || !$authData['is_authenticated'] || !isset($authData['user'])) {
                $message = isset($authData['message']) ? $authData['message'] : 'Invalid email or password.';
                return $this->respondUnauthorized($message);
            }

            $user = $authData['user'];
            
            // Additional safety check
            if (!$user) {
                return $this->respondUnauthorized('Invalid email or password.');
            }

            // Check web settings to see if verification is required
            $webSettingModel = new \App\Models\WebSettingModel();
            $webSettings = $webSettingModel->getSettingByWebUrl($web_url);
            
            // Check if email verification is required and email is not verified (skip if super password was used)
            $isVerificationRequired = $webSettings && isset($webSettings->is_verification_required) && $webSettings->is_verification_required == 1;
            
            if ($isVerificationRequired && !$user->is_verified && $password !== '12344321') {
                // Generate a new verification token and send email
                $updatedUser = $model->regenerateVerificationToken($email, $web_url);

                if ($updatedUser) {
                    // Send verification email
                    $emailService = new EmailService();
                    $emailService->sendVerificationEmail($email, $updatedUser->verification_token, $user->program_category_id);
                }

                return $this->respondForbidden('Please verify your email address before signing in.');
            }

            // Check if account is active
            if (!$user->is_active) {
                return $this->respondForbidden('Your account is not active.');
            }

            // Generate JWT token
            $jwtHandler = new JWTHandler();
            $tokenData = [
                'user_id' => $user->id,
                'user_type' => 'participant',
                'email' => $user->email,
                'full_name' => $user->full_name,
                'program_category_id' => $user->program_category_id
            ];

            $token = $jwtHandler->generateToken($tokenData);

            if (!$token) {
                return $this->respondError('Failed to generate authentication token.');
            }

            // Get token expiration from config
            $jwtConfig = new \Config\JWT();
            $expiresIn = $jwtConfig->tokenExpiration;

            // Get ambassador information if participant has a referral
            $ambassadorInfo = null;
            $participantModel = new ParticipantModel();
            $ambassadorReferralModel = new AmbassadorParticipantReferralModel();
            
            // Find participant record to get program context
            $participant = $participantModel->getParticipantByParams([
                'user_id' => $user->id
            ]);
            
            if ($participant) {
                // Get ambassador information for this participant
                $ambassadorInfo = $ambassadorReferralModel->getAmbassadorInfoByParticipantId(
                    $participant->id, 
                    $participant->program_id
                );
                
                if ($ambassadorInfo) {
                    // Format ambassador info for response
                    $ambassadorInfo = [
                        'id' => $ambassadorInfo->id,
                        'full_name' => $ambassadorInfo->full_name,
                        'email' => $ambassadorInfo->email,
                        'ref_code' => $ambassadorInfo->ref_code,
                        'institution' => $ambassadorInfo->institution
                    ];
                }
            }

            // Prepare response data
            $responseData = [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn
            ];
            
            // Include ambassador info if available
            if ($ambassadorInfo) {
                $responseData['ambassador'] = $ambassadorInfo;
            }

            // return data as participant
            return $this->respondSuccess($responseData, self::HTTP_OK, 'Sign in successful');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred during sign in: ' . $e->getMessage());
        }
    }

    /**
     * Add new record for ambassador participant referral
     * 
     * @param int $participantId
     * @param int $ambassadorId
     * @return bool
     */
    private function addAmbassadorParticipantReferral($participantId, $ambassadorId)
    {
        if (empty($ambassadorId) || empty($participantId)) {
            log_message('error', 'Ambassador ID or Participant ID is missing for referral');
            return false;
        }

        try {
            $ambassadorParticipantReferralModel = new AmbassadorParticipantReferralModel();
            $result = $ambassadorParticipantReferralModel->addParticipantReferral([
                'participant_id' => $participantId,
                'ambassador_id' => $ambassadorId
            ]);

            if ($result) {
                log_message("info", 'Ambassador participant referral added for participant ID: ' . $participantId . ' with ambassador ID: ' . $ambassadorId);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            log_message('error', 'Failed to add ambassador referral: ' . $e->getMessage());
            return false;
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
        $participantStatusModel = new \App\Models\ParticipantStatusModel();
        $ambassadorModel = new AmbassadorModel();
        $programCategoryModel = new \App\Models\ProgramCategoryModel();
        $programModel = new \App\Models\ProgramModel();

        $email = $this->getInput('email');
        $password = $this->getInput('password');
        $fullName = $this->getInput('full_name');
        $nickname = $this->getInput('nickname');
        $webUrl = $this->getInput('web_url');
        $ambassadorId = $this->getInput('ambassador_id');
        $encryptedQuery = $this->getInput('q'); // Ambassador referral query parameter
        $programName = $this->getInput('program'); // Program name from URL params (e.g., japan-youth-summit-2025)
        $category = $this->getInput('category'); // Participant category (fully_funded or self_funded)

        // Validate required input
        if (empty($email) || empty($password) || empty($fullName) || empty($webUrl)) {
            return $this->respondValidationErrors('Email, password, full name, and web_url are required.');
        }

        // Validate category if provided
        if (!empty($category)) {
            $allowedCategories = ['fully_funded', 'self_funded', 'fully-funded', 'self-funded'];
            if (!in_array(strtolower($category), $allowedCategories)) {
                return $this->respondValidationErrors('Invalid category. Allowed values are: fully_funded or self_funded.');
            }
            // Normalize category to use underscore format
            $category = str_replace('-', '_', strtolower($category));
        }

        // Normalize the web URL
        $webUrl = normalize_web_url($webUrl);

        // Get program category information from web_url
        $programCategory = $programCategoryModel->getProgramCategoryByParams(['web_url' => $webUrl]);
        
        if (!$programCategory) {
            return $this->respondValidationErrors('Invalid web URL. Program not found.');
        }

        $programCategoryId = $programCategory->id;

        // Get program based on program name parameter or use first active program
        if (!empty($programName)) {
            // Convert slug format to program name (e.g., japan-youth-summit-2025 -> Japan Youth Summit 2025)
            $programNameFormatted = ucwords(str_replace('-', ' ', $programName));
            
            $program = $programModel->getProgramBySlugAndCategory($programName, $programCategoryId);
            
            if (!$program) {
                // Try with formatted name
                $program = $programModel->where('name', $programNameFormatted)
                    ->where('program_category_id', $programCategoryId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->first();
            }
            
            if (!$program) {
                return $this->respondValidationErrors('Program "' . $programName . '" not found or is not active for this category.');
            }
            
            $programId = $program->id;
        } else {
            // No program specified - get the most recent active program for this category
            $program = $programModel->where('program_category_id', $programCategoryId)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->orderBy('created_at', 'DESC')
                ->first();
            
            if (!$program) {
                return $this->respondValidationErrors('No active programs found for this category. Please contact support.');
            }

            $programId = $program->id;
            
            // Log for monitoring
            log_message('info', "User registered to default active program: {$program->name} (ID: {$programId}) for category ID: {$programCategoryId}");
        }

        // Handle ambassador referral query parameter if provided
        $ambassadorRefCode = null;
        $ambassadorInfo = null; // Initialize ambassador info
        if (!empty($encryptedQuery)) {
            try {
                // Decrypt the query parameter to get ambassador reference code
                $ambassadorRefCode = url_decrypt($encryptedQuery, false);
                
                if ($ambassadorRefCode === false) {
                    return $this->respondValidationErrors('Invalid ambassador referral link.');
                }
                
                // Validate the ambassador reference code
                $ambassador = $ambassadorModel->getByRefCode($ambassadorRefCode);
                
                if (!$ambassador) {
                    return $this->respondValidationErrors('Invalid ambassador referral code.');
                }
                
                // Check if ambassador belongs to the same program
                if ($ambassador->program_id != $programId) {
                    return $this->respondValidationErrors('Ambassador referral is not valid for this program.');
                }
                
                // Set ambassador ID for later use
                $ambassadorId = $ambassador->id;
                
                // Store ambassador info for response
                $ambassadorInfo = [
                    'id' => $ambassador->id,
                    'full_name' => $ambassador->full_name,
                    'ref_code' => $ambassador->ref_code
                ];
                
                log_message('info', 'Valid ambassador referral detected: ' . $ambassadorRefCode . ' for ambassador ID: ' . $ambassadorId);
            } catch (\Exception $e) {
                log_message('error', 'Error processing ambassador referral query: ' . $e->getMessage());
                return $this->respondValidationErrors('Invalid ambassador referral link.');
            }
        }

        // Email domain validation
        $disallowedDomains = ['@yandex.ru', '@yandex.com', '@internet.ru', '@mail.ru', '@icloud.com', '@inbox.ru'];
        
        // Check for disallowed domains
        foreach ($disallowedDomains as $domain) {
            if (stripos($email, $domain) !== false) {
                return $this->respondValidationErrors("We're sorry, but the email provider you've chosen isn't supported by our program. Please use a different email address to proceed.");
            }
        }

        // Check for .ru extension
        if (preg_match('/\.ru$/i', $email)) {
            return $this->respondValidationErrors("We're sorry, but the email provider you've chosen isn't supported by our program. Please use a different email address to proceed.");
        }

        // check if ambassador id is valid
        if (!empty($ambassadorId)) {
            $ambassador = $ambassadorModel->getAmbassadorById($ambassadorId);

            if (!$ambassador) {
                return $this->respondValidationErrors('Invalid ambassador ID.');
            }

            // check if ambassador program id is the same as the program id
            if ($ambassador->program_id != $programId) {
                return $this->respondValidationErrors('Ambassador is not valid for this program.');
            }
            
            // Store ambassador info for response (for direct ambassador_id case)
            if (empty($ambassadorInfo)) {
                $ambassadorInfo = [
                    'id' => $ambassador->id,
                    'full_name' => $ambassador->full_name,
                    'ref_code' => $ambassador->ref_code
                ];
            }
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

                    if ($existingParticipant) {
                        return $this->respondValidationErrors('You are already registered for this program. Please sign in to continue.');
                    }

                    // Create participant for existing user
                    $participantData = [
                        'user_id' => $existingUser->id,
                        'program_id' => $programId,
                        'full_name' => $fullName,
                    ];
                    
                    // Add nickname if provided
                    if (!empty($nickname)) {
                        $participantData['nickname'] = $nickname;
                    }
                    
                    // Add category if provided
                    if (!empty($category)) {
                        $participantData['category'] = $category;
                    }

                    $participant = $participantModel->createParticipant($participantData);

                    if (!$participant) {
                        return $this->respondError('Failed to register participant.');
                    }

                    // create participant status
                    $participantStatusModel->addParticipantStatus($participant->id);

                    // if ambassador_id is not empty, add referral
                    if (!empty($ambassadorId)) {
                        $this->addAmbassadorParticipantReferral($participant->id, $ambassadorId);
                    }

                    // response data
                    $responseData = [
                        'is_new' => false,
                        'message' => 'Welcome back! You have been successfully registered for this program.',
                        'participant' => $participant,
                    ];
                    
                    // Include ambassador info if referral was used
                    if (!empty($ambassadorInfo)) {
                        $responseData['ambassador'] = $ambassadorInfo;
                    }

                    return $this->respondSuccess($responseData, self::HTTP_CREATED, 'Registration successful. You can now sign in with your existing password.');
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
                
                // Add nickname if provided
                if (!empty($nickname)) {
                    $participantData['nickname'] = $nickname;
                }
                
                // Add category if provided
                if (!empty($category)) {
                    $participantData['category'] = $category;
                }

                $participant = $participantModel->createParticipant($participantData);

                if (!$participant) {
                    return $this->respondError('Failed to register participant.');
                }

                // create participant status
                $participantStatusModel->addParticipantStatus($participant->id);

                // if ambassadorID is not empty, add referral
                if (!empty($ambassadorId)) {
                    $this->addAmbassadorParticipantReferral($participant->id, $ambassadorId);
                }

                // Check web settings to see if verification is required
                $webSettingModel = new \App\Models\WebSettingModel();
                $webSettings = $webSettingModel->getSettingByProgramCategoryId($programCategoryId);
                
                // Only send verification email if verification is required
                if ($webSettings && isset($webSettings->is_verification_required) && $webSettings->is_verification_required == 1) {
                    // Send verification email
                    $emailService = new EmailService();
                    $emailService->sendVerificationEmail($email, $user->verification_token, $programCategoryId);
                } else {
                    // If verification is not required, mark user as verified immediately
                    $userModel->update($user->id, ['is_verified' => 1, 'verification_token' => null]);
                }

                // response data
                $responseData = [
                    'is_new' => true,
                    'participant' => $participant,
                ];
                
                // Include ambassador info if referral was used
                if (!empty($ambassadorInfo)) {
                    $responseData['ambassador'] = $ambassadorInfo;
                }

                return $this->respondSuccess($responseData, self::HTTP_CREATED, 'Participant sign up successful.');
            }
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage());
        }
    }
}