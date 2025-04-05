<?php

namespace App\Services;

use Config\Email;
use App\Models\ProgramCategoryModel;

class EmailService
{
    protected $email;

    public function __construct()
    {
        $this->email = \Config\Services::email();
    }

    /**
     * Send an email
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $template Email template
     * @param array $data Data to pass to the view
     * @return bool True if email was sent, false otherwise
     * @throws \Exception If there's an error sending the email
     */
    public function sendEmail(string $to, string $subject, string $template, array $data = []): bool
    {
        try {
            // Load view with data
            $message = view('emails/' . $template, $data);
            
            $this->email->setTo($to);
            $this->email->setSubject($subject);
            $this->email->setMessage($message);
            
            if ($this->email->send() === false) {
                $error = $this->email->printDebugger(['headers']);
                log_message('error', 'Email sending error to {email}: {error}', ['email' => $to, 'error' => $error]);
                throw new \Exception('Failed to send email: ' . $error);
            }
            
            log_message('info', 'Email sent successfully to {email}', ['email' => $to]);
            return true;
            
        } catch (\Exception $e) {
            log_message('error', 'Email service error: {error}', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Send a password reset email with a reset link
     *
     * @param string $to Recipient email
     * @param string $token Reset token
     * @param string $web_url Web URL to get program information
     * @return bool True if email was sent, false otherwise
     */
    public function sendPasswordResetEmail(string $to, string $token, string $web_url): bool
    {
        $subject = 'Password Reset Request';
        
        // Get program information based on web_url
        $programCategoryModel = new ProgramCategoryModel();
        $programData = $programCategoryModel->getProgramCategoryByParams(['web_url' => $web_url]);

        // Check if program data is found
        if (!$programData) {
            log_message('error', 'Program not found for web_url: {web_url}', ['web_url' => $web_url]);
            return false;
        }

        // Create reset URL - Using frontend URL with token
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $resetUrl = $protocol . $web_url . '/reset-password?token=' . $token;
        
        $data = [
            'reset_link' => $resetUrl,
            // If program contact email is different from the sender email
            'email_contact' => $programData->contact_email ?? null,
        ];
        
        if ($programData) {
            $data = array_merge($data, (array)$programData);
        }
        
        return $this->sendEmail($to, $subject, 'reset_password_link', $data);
    }
    
    /**
     * Send an email verification with token
     *
     * @param string $to Recipient email
     * @param string $token Verification token
     * @param string $web_url Web URL to get program information
     * @return bool True if email was sent, false otherwise
     */
    public function sendVerificationEmail(string $to, string $token, string $program_category_id): bool
    {
        $subject = 'Verify Your Email Address';
        
        // Get program information based on web_url
        $programCategoryModel = new ProgramCategoryModel();
        $programData = $programCategoryModel->getProgramCategoryByParams(['id' => $program_category_id]);

        // Check if program data is found
        if (!$programData) {
            log_message('error', 'Program not found for program_category_id: {program_category_id}', ['program_category_id' => $program_category_id]);
            return false;
        }
        
        // Create verification URL
        $baseUrl = base_url();
        $verificationUrl = $baseUrl . "verify-email?token={$token}&email={$to}&program_category_id={$program_category_id}";
        
        $data = [
            'verification_token' => $token,
            'verification_url' => $verificationUrl
        ];
        
        if ($programData) {
            $data = array_merge($data, (array)$programData);
        }
        
        return $this->sendEmail($to, $subject, 'verify_email', $data);
    }
    

}
