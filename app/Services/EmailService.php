<?php

namespace App\Services;

use App\Models\ProgramCategoryModel;
use Resend\Client as ResendClient;
use Resend\Exceptions\ErrorException as ResendErrorException;

class EmailService
{
    protected ResendClient $resend;
    protected string $defaultFrom;

    public function __construct(?ResendClient $resend = null, ?string $defaultFrom = null)
    {
        $this->resend = $resend ?? \Resend::client((string) env('RESEND_API_KEY'));
        $this->defaultFrom = $defaultFrom
            ?? (string) env('RESEND_FROM', 'no-reply@example.com');
    }

    /**
     * Render a view template and send the result as HTML via Resend.
     */
    public function sendEmail(string $to, string $subject, string $template, array $data = []): bool
    {
        try {
            $message = view('emails/' . $template, $data);
        } catch (\Throwable $e) {
            log_message('error', 'EmailService: failed rendering template {template}: {error}', [
                'template' => $template,
                'error'    => $e->getMessage(),
            ]);
            return false;
        }

        return $this->sendRawEmail($to, $subject, $message);
    }

    /**
     * Send an HTML email via Resend.
     *
     * Returns true on a successful API call. Returns false on any transport
     * or API error; the underlying error is logged server-side and never
     * surfaced to the caller (callers used to bubble exception messages into
     * API responses, which leaked SMTP/transport debug info).
     */
    public function sendRawEmail(string $to, string $subject, string $html, ?string $from = null): bool
    {
        $payload = [
            'from'    => $from ?? $this->defaultFrom,
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $html,
        ];

        try {
            $this->dispatch($payload);
            log_message('info', 'EmailService: Resend accepted email to {to}', ['to' => $to]);
            return true;
        } catch (ResendErrorException $e) {
            log_message('error', 'EmailService: Resend API rejected email to {to}: {error}', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        } catch (\Throwable $e) {
            log_message('error', 'EmailService: Resend transport failure for {to}: {error}', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Testing seam: subclasses/tests override this to intercept the Resend
     * call without going over the network.
     */
    protected function dispatch(array $payload): void
    {
        $this->resend->emails->send($payload);
    }

    /**
     * Send a password reset email containing the reset link.
     */
    public function sendPasswordResetEmail(string $to, string $token, ?string $web_url): bool
    {
        $subject = 'Password Reset Request';

        $programCategoryModel = new ProgramCategoryModel();
        $programData = $web_url
            ? $programCategoryModel->getProgramCategoryByParams(['web_url' => $web_url])
            : null;

        $resetUrl = base_url('reset-password') . '?token=' . $token;

        if (!$programData) {
            log_message('warning', 'EmailService: program not found for web_url {web_url}; sending generic reset email', [
                'web_url' => $web_url,
            ]);
            $data = [
                'reset_link'    => $resetUrl,
                'email_contact' => null,
                'program_name'  => null,
                'web_url'       => $web_url,
            ];
            return $this->sendEmail($to, $subject, 'reset_password_link', $data);
        }

        $data = array_merge(
            [
                'reset_link'    => $resetUrl,
                'email_contact' => $programData->contact_email ?? null,
            ],
            (array) $programData,
        );

        return $this->sendEmail($to, $subject, 'reset_password_link', $data);
    }

    /**
     * Send an email verification message.
     */
    public function sendVerificationEmail(string $to, string $token, $program_category_id): bool
    {
        $subject = 'Verify Your Email Address';

        $programCategoryModel = new ProgramCategoryModel();
        $programData = $programCategoryModel->getProgramCategoryByParams(['id' => $program_category_id]);

        if (!$programData) {
            log_message('error', 'EmailService: program not found for program_category_id {id}', [
                'id' => $program_category_id,
            ]);
            return false;
        }

        $webUrl = $programData->web_url ?? null;
        $verificationUrl = $webUrl . '/verify-email?token=' . $token . '&email=' . $to;

        $data = array_merge(
            [
                'verification_token' => $token,
                'verification_url'   => $verificationUrl,
            ],
            (array) $programData,
        );

        return $this->sendEmail($to, $subject, 'verify_email', $data);
    }

    /**
     * Diagnostic helper used by EmailTestingController.
     */
    public function testEmail(?string $testEmailAddress = null): array
    {
        try {
            $testToken = bin2hex(random_bytes(16));
            $emailSent = $this->sendVerificationEmail(
                $testEmailAddress ?: 'test@example.com',
                $testToken,
                1
            );
            return [
                'success' => $emailSent,
                'message' => $emailSent ? 'Test email sent successfully.' : 'Failed to send test email.',
            ];
        } catch (\Throwable $e) {
            log_message('error', 'EmailService: testEmail failure: {error}', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Email test failed. Check server logs for details.',
            ];
        }
    }

    /**
     * Notify a participant of a document status change.
     */
    public function sendStatus(string $to, string $name, string $status): bool
    {
        $subject = 'Important Notification: Document Status Update';

        switch (strtolower($status)) {
            case 'accepted':
                $statusMessage = "we are pleased to inform you that your submitted document has been <b style='color:green;'>APPROVED</b>. Congratulations on successfully completing this stage of the process. You may proceed with the next steps as required.";
                break;
            case 'rejected':
                $statusMessage = "we regret to inform you that your submitted document has been <b style='color:red;'>REJECTED</b> after a thorough review. We kindly advise you to revise the document according to the given requirements and resubmit it for further consideration.";
                break;
            default:
                $statusMessage = "your document is currently under review and its status is: <b>" . esc($status) . "</b>. We will provide you with further updates once the review process has been completed.";
        }

        $safeName = esc($name);
        $safeTo = esc($to);
        $message = <<<HTML
        <p>Dear <b>{$safeName}</b>,</p>

        <p>We hope this message finds you well. This is an official notification regarding the current status of your document submission registered under the email address <b>{$safeTo}</b>. After the review process, {$statusMessage}</p>

        <p>If you have any questions or require further clarification regarding this matter, please do not hesitate to contact our support team. We are committed to assisting you throughout the process.</p>

        <br>
        <p>Thank you for your kind attention and cooperation.<br>
        Sincerely,<br>
        <b>Administrative Team</b></p>
        HTML;

        return $this->sendRawEmail($to, $subject, $message);
    }
}
