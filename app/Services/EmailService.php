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
