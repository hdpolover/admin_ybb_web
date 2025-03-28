<?php

namespace App\Controllers\Api;

use App\Controllers\Api\Auth\AdminAuthController;
use App\Controllers\Api\Auth\ParticipantAuthController;
use App\Controllers\Api\Auth\JwtAuthController;
use App\Controllers\Api\Auth\PasswordRecoveryController;
use App\Controllers\Api\Auth\EmailVerificationController;
use App\Libraries\JWTHandler;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Authentication API Controller
 * 
 * Main controller that delegates to specialized auth controllers
 */
class AuthApiController extends ApiBaseController
{
    protected $jwtHandler;
    protected $participantAuth;
    protected $adminAuth;
    protected $jwtAuth;
    protected $passwordRecovery;
    protected $emailVerification;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);
        
        // Initialize JWT Handler
        $this->jwtHandler = new JWTHandler();
        
        // Initialize specialized controllers
        $this->participantAuth = new ParticipantAuthController();
        $this->adminAuth = new AdminAuthController();
        $this->jwtAuth = new JwtAuthController();
        $this->passwordRecovery = new PasswordRecoveryController();
        $this->emailVerification = new EmailVerificationController();
        
        // Initialize each specialized controller
        $this->participantAuth->initController($request, $response, $logger);
        $this->adminAuth->initController($request, $response, $logger);
        $this->jwtAuth->initController($request, $response, $logger);
        $this->passwordRecovery->initController($request, $response, $logger);
        $this->emailVerification->initController($request, $response, $logger);
    }

    /**
     * JWT-based sign in
     * POST /api/auth/sign-in
     */
    public function signIn()
    {
        return $this->jwtAuth->signInJwt();
    }
    
    /**
     * Get user profile from JWT token
     * GET /api/auth/profile
     */
    public function profile()
    {
        return $this->jwtAuth->profile();
    }
    
    /**
     * Refresh JWT token
     * POST /api/auth/refresh
     */
    public function refreshToken()
    {
        return $this->jwtAuth->refreshToken();
    }
    
    /**
     * Participant sign up
     * POST /api/auth/participant-signup
     */
    public function participantSignUp()
    {
        return $this->participantAuth->signUp();
    }
    
    // Password Recovery Methods
    
    /**
     * Forgot password
     * POST /api/auth/forgot-password
     */
    public function forgotPassword()
    {
        return $this->passwordRecovery->forgotPassword();
    }
    
    /**
     * Verify OTP
     * POST /api/auth/verify-otp
     */
    public function verifyOtp()
    {
        return $this->passwordRecovery->verifyOtp();
    }
    
    /**
     * Reset password
     * POST /api/auth/reset-password
     */
    public function resetPassword()
    {
        return $this->passwordRecovery->resetPassword();
    }
    
    // Email Verification Methods
    
    /**
     * Verify email
     * GET /api/auth/verify-email
     */
    public function verifyEmail()
    {
        return $this->emailVerification->verifyEmail();
    }
    
    /**
     * Resend verification email
     * POST /api/auth/resend-verification
     */
    public function resendVerification()
    {
        return $this->emailVerification->resendVerification();
    }
    
    /**
     * Test email
     * GET /api/auth/test-email
     */
    public function testEmail()
    {
        return $this->emailVerification->testEmail();
    }
}
