<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\ApiBaseController;
use App\Models\AdminModel;
use App\Models\PasswordResetModel;
use App\Models\ParticipantModel;
use App\Services\EmailService;

class AuthApiController extends ApiBaseController
{

    // sign in
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

            // Check if email is verified
            if (isset($user->email_not_verified) && $user->email_not_verified) {
                // Generate a new verification token and send email
                $user = $model->regenerateVerificationToken($email, $web_url);

                if ($user) {
                    // Send verification email
                    $emailService = new EmailService();
                    $emailService->sendVerificationEmail($email, $user->verification_token, $web_url);
                }

                return $this->respondForbidden(lang('EmailVerification.verification_required'));
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

    public function participantSignUp()
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
                // check if participant already exists
                if (is_object($existingUser)) {
                    $existingParticipant = $participantModel->getParticipantByParams([
                        'user_id' => $existingUser->id,
                        'program_id' => $programId,
                    ]);

                    if ($existingParticipant) {
                        return $this->respondValidationErrors('Participant already exists for this program. please sign in.');
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

                    // Send verification email
                    $emailService = new EmailService();
                    $emailService->sendVerificationEmail($email, $existingUser->verification_token, $programCategoryId);

                    if (!$participant) {
                        return $this->respondError('Failed to register participant.');
                    }

                    return $this->respondSuccess($participant, self::HTTP_CREATED, 'Participant sign up successful.');
                }
            } else {
                // Create new user and participant
                $userData = [
                    'email' => $email,
                    'password' => $password,
                    'program_category_id' => $programCategoryId,
                    'full_name' => $fullName,
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

                return $this->respondSuccess($participant, self::HTTP_CREATED, 'Participant sign up successful. Please check your email to verify your account.');
            }
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage());
        }
    }

    private function adminSignUp()
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

    private function ambassadorSignUp()
    {
        return $this->respondNotImplemented('Ambassador sign up not implemented yet.');
    }

    private function reviewerSignUp()
    {
        return $this->respondNotImplemented('Reviewer sign up not implemented yet.');
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
            $passwordResetModel->createOtp($email, $user->id, $otp);

            // Send email with OTP
            $emailService = new EmailService();
            $emailSent = $emailService->sendPasswordResetEmail($email, $otp, $web_url);

            if (!$emailSent) {
                return $this->respondError('Failed to send OTP email. Please try again later.');
            }

            log_message('info', "OTP for $email is $otp");

            return $this->respondSuccess(null, self::HTTP_OK, 'OTP sent successfully. Please check your email.');
        } catch (\Exception $e) {
            return $this->respondError('Failed to send OTP: ' . $e->getMessage());
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
        $user_id = $this->request->getPost('user_id');
        $newPassword = $this->request->getPost('password');

        if (empty($user_id) || empty($newPassword)) {
            return $this->respondValidationErrors('User ID and new password are required.');
        }

        try {
            // Update password
            $userModel = new UserModel();
            $userModel->updatePassword($user_id, $newPassword);

            // Delete OTP
            $passwordResetModel = new PasswordResetModel();
            $passwordResetModel->deleteOtp($user_id);

            return $this->respondSuccess(null, self::HTTP_OK, 'Password reset successfully. You can now sign in with your new password.');
        } catch (\Exception $e) {
            return $this->respondError('Failed to reset password: ' . $e->getMessage());
        }
    }

    /**
     * Verify email with token
     * 
     * @return ResponseInterface
     */
    public function verifyEmail()
    {
        $token = $this->request->getGet('token');
        $email = $this->request->getGet('email');
        $web_url = $this->request->getGet('web_url');

        if (empty($token) || empty($email)) {
            return $this->respondValidationErrors(lang('EmailVerification.verification_failed'));
        }

        try {
            $userModel = new UserModel();
            $verified = $userModel->verifyEmail($email, $token);

            if (!$verified) {
                return $this->respondError(lang('EmailVerification.invalid_token'));
            }

            // Redirect to login page with success message
            if (!empty($web_url)) {
                return redirect()->to("https://{$web_url}/login?verified=1&message=" . urlencode(lang('EmailVerification.verification_success')));
            }

            return $this->respondSuccess(null, self::HTTP_OK, lang('EmailVerification.verification_success'));
        } catch (\Exception $e) {
            log_message('error', 'Email verification failed: {error}', ['error' => $e->getMessage()]);
            return $this->respondError(lang('EmailVerification.verification_failed'));
        }
    }

    /**
     * Resend verification email
     * 
     * @return ResponseInterface
     */
    public function resendVerification()
    {
        $email = $this->request->getPost('email');
        $web_url = $this->request->getPost('web_url');

        if (empty($email) || empty($web_url)) {
            return $this->respondValidationErrors(lang('EmailVerification.email_not_found'));
        }

        try {
            $userModel = new UserModel();
            $user = $userModel->getUserByEmailAndWebUrl($email, $web_url);

            if (!$user) {
                return $this->respondNotFound(lang('EmailVerification.email_not_found'));
            }

            // Check if already verified
            if ($user->is_verified) {
                return $this->respondSuccess(null, self::HTTP_OK, lang('EmailVerification.already_verified'));
            }

            // Generate new verification token
            $user = $userModel->regenerateVerificationToken($email, $web_url);

            if (!$user) {
                return $this->respondError(lang('EmailVerification.resend_failed'));
            }

            // Send verification email
            $emailService = new EmailService();
            $emailSent = $emailService->sendVerificationEmail($email, $user->verification_token, $web_url);

            if (!$emailSent) {
                return $this->respondError(lang('EmailVerification.resend_failed'));
            }

            return $this->respondSuccess(null, self::HTTP_OK, lang('EmailVerification.resend_success'));
        } catch (\Exception $e) {
            log_message('error', 'Failed to resend verification email: {error}', ['error' => $e->getMessage()]);
            return $this->respondError(lang('EmailVerification.resend_failed'));
        }
    }

    /**
     * Test email sending functionality
     * 
     * @return ResponseInterface
     */
    public function testEmail()
    {
        try {
            $emailService = new EmailService();
            $testToken = bin2hex(random_bytes(16));

            $emailSent = $emailService->sendVerificationEmail(
                'test@example.com', // Replace with a test email
                $testToken,
                'test.ybbfoundation.com' // Replace with your test domain
            );

            if (!$emailSent) {
                return $this->respondError('Failed to send test email.');
            }

            return $this->respondSuccess(null, self::HTTP_OK, 'Test email sent successfully.');
        } catch (\Exception $e) {
            return $this->respondError('Email test failed: ' . $e->getMessage());
        }
    }
}
