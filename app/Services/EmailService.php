<?php

namespace App\Services;

use Config\Email;
use App\Models\ProgramCategoryModel;
use App\Services\GoogleOAuthService;
use CodeIgniter\Config\Services;

class EmailService
{
    protected $email;
    protected $googleService;
    protected $db;

    public function __construct()
    {
        $this->email = \Config\Services::email();
        $this->googleService = new GoogleOAuthService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Send email with OAuth/SMTP fallback using template
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
            return $this->sendEmailWithOAuth($to, $subject, $message);
        } catch (\Exception $e) {
            log_message('error', 'Email service error: {error}', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Send email with automatic OAuth/SMTP fallback
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body HTML body content
     * @param string $from Sender email (optional)
     * @return bool True if email was sent, false otherwise
     */
    public function sendEmailWithOAuth(string $to, string $subject, string $body, string $from = null): bool
    {
        $from = $from ?: 'noreply@ybbfoundation.com';
        
        // First try OAuth if token exists
        $oauthResult = $this->tryOAuthEmail($from, $to, $subject, $body);
        if ($oauthResult['success']) {
            log_message('info', 'Email sent successfully via OAuth to {email}', ['email' => $to]);
            return true;
        }
        
        // Fallback to SMTP
        log_message('info', 'OAuth failed ({reason}), falling back to SMTP for {email}', [
            'reason' => $oauthResult['reason'],
            'email' => $to
        ]);
        
        return $this->sendSMTPEmail($to, $subject, $body, $from);
    }

    /**
     * Try sending via OAuth Gmail API
     */
    private function tryOAuthEmail(string $from, string $to, string $subject, string $body): array
    {
        try {
            // Check if OAuth token exists
            $builder = $this->db->table('oauth_tokens');
            $tokenData = $builder->where('email', $from)->get()->getRow();
            
            if (!$tokenData) {
                return [
                    'success' => false,
                    'reason' => 'No OAuth token found for ' . $from
                ];
            }
            
            // Check if token is expired
            if (strtotime($tokenData->expires_at) < time()) {
                return [
                    'success' => false,
                    'reason' => 'OAuth token expired for ' . $from
                ];
            }
            
            // Set access token
            $token = [
                'access_token' => $tokenData->access_token,
                'refresh_token' => $tokenData->refresh_token,
                'expires_in' => strtotime($tokenData->expires_at) - time(),
                'token_type' => $tokenData->token_type,
                'scope' => $tokenData->scope
            ];
            
            $this->googleService->setAccessToken($token);
            
            // Send email
            $success = $this->googleService->sendEmail($to, $subject, $body);
            
            return [
                'success' => $success,
                'reason' => $success ? 'OAuth email sent' : 'Gmail API send failed'
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'OAuth email error: ' . $e->getMessage());
            return [
                'success' => false,
                'reason' => 'OAuth error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send email via SMTP
     */
    private function sendSMTPEmail(string $to, string $subject, string $body, string $from): bool
    {
        try {
            $this->email->setFrom($from, 'YBB Foundation');
            $this->email->setTo($to);
            $this->email->setSubject($subject);
            $this->email->setMessage($body);
            $this->email->setMailType('html');

            if ($this->email->send() === false) {
                $error = $this->email->printDebugger(['headers']);
                log_message('error', 'SMTP email sending error to {email}: {error}', ['email' => $to, 'error' => $error]);
                throw new \Exception('Failed to send email via SMTP: ' . $error);
            }

            log_message('info', 'Email sent successfully via SMTP to {email}', ['email' => $to]);
            return true;
        } catch (\Exception $e) {
            log_message('error', 'SMTP email service error: {error}', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Check OAuth status for an email
     */
    public function checkOAuthStatus(string $email): array
    {
        $builder = $this->db->table('oauth_tokens');
        $tokenData = $builder->where('email', $email)->get()->getRow();
        
        if (!$tokenData) {
            return [
                'has_token' => false,
                'message' => 'No OAuth token found'
            ];
        }
        
        $isExpired = strtotime($tokenData->expires_at) < time();
        
        return [
            'has_token' => true,
            'is_expired' => $isExpired,
            'expires_at' => $tokenData->expires_at,
            'scope' => $tokenData->scope,
            'status' => $isExpired ? 'expired' : 'valid'
        ];
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
        $webUrl = $programData->web_url ?? null;
        $verificationUrl = $webUrl . "/verify-email?token={$token}&email={$to}";

        $data = [
            'verification_token' => $token,
            'verification_url' => $verificationUrl
        ];

        if ($programData) {
            $data = array_merge($data, (array)$programData);
        }

        return $this->sendEmail($to, $subject, 'verify_email', $data);
    }

    public function testEmail($testEmailAddress = null)
    {
        try {
            $testToken = bin2hex(random_bytes(16));
            $emailSent = $this->sendVerificationEmail(
                $testEmailAddress ?: 'test@example.com',
                $testToken,
                1 // Default program category ID
            );
            return [
                'success' => $emailSent,
                'message' => $emailSent ? 'Test email sent successfully.' : 'Failed to send test email.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Email test failed: ' . $e->getMessage()
            ];
        }
    }

    public function sendStatus($to, $name, $status)
    {
        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setSubject('Important Notification: Document Status Update');

        $statusMessage = '';
        switch (strtolower($status)) {
            case 'accepted':
                $statusMessage = "we are pleased to inform you that your submitted document has been <b style='color:green;'>APPROVED</b>. Congratulations on successfully completing this stage of the process. You may proceed with the next steps as required.";
                break;
            case 'rejected':
                $statusMessage = "we regret to inform you that your submitted document has been <b style='color:red;'>REJECTED</b> after a thorough review. We kindly advise you to revise the document according to the given requirements and resubmit it for further consideration.";
                break;
            default:
                $statusMessage = "your document is currently under review and its status is: <b>$status</b>. We will provide you with further updates once the review process has been completed.";
        }

        $message = "
        <p>Dear <b>{$name}</b>,</p>

        <p>We hope this message finds you well. This is an official notification regarding the current status of your document submission registered under the email address <b>{$to}</b>. After the review process, {$statusMessage}</p>

        <p>If you have any questions or require further clarification regarding this matter, please do not hesitate to contact our support team. We are committed to assisting you throughout the process.</p>

        <br>
        <p>Thank you for your kind attention and cooperation.<br>
        Sincerely,<br>
        <b>Administrative Team</b></p>
    ";

        $email->setMessage($message);

        if ($email->send()) {
            return true;
        } else {
            log_message('error', 'Email failed to send to: ' . $to . ' | Debug: ' . print_r($email->printDebugger(['headers']), true));
            return false;
        }
    }
}
