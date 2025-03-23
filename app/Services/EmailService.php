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
     */
    public function sendEmail(string $to, string $subject, string $template, array $data = []): bool
    {
        // Load view with data
        $message = view('emails/' . $template, $data);
        
        $this->email->setTo($to);
        $this->email->setSubject($subject);
        $this->email->setMessage($message);
        
        if ($this->email->send() === false) {
            log_message('error', 'Email sending error: ' . $this->email->printDebugger(['headers']));
            return false;
        }
        
        return true;
    }

    /**
     * Send a password reset email with OTP
     *
     * @param string $to Recipient email
     * @param string $otp One-time password
     * @param string $web_url Web URL to get program information
     * @return bool True if email was sent, false otherwise
     */
    public function sendPasswordResetEmail(string $to, string $otp, string $web_url): bool
    {
        $subject = 'Password Reset OTP';
        
        // Get program information based on web_url
        $programData = $this->getProgramInfoByWebUrl($web_url);
        
        $data = [
            'otp' => $otp,
            // Merge program data with email data
        ];
        
        if ($programData) {
            $data = array_merge($data, (array)$programData);
        }
        
        return $this->sendEmail($to, $subject, 'reset_password', $data);
    }
    
    /**
     * Get program information by web URL
     *
     * @param string $web_url Program's web URL
     * @return object|null Program data or null if not found
     */
    private function getProgramInfoByWebUrl(string $web_url): ?object
    {
        $programModel = new ProgramCategoryModel();
        return $programModel->getProgramByWebUrl($web_url);
    }
}
