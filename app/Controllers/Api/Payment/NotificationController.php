<?php

namespace App\Controllers\Api\Payment;

use App\Gateways\GatewayFactory;
use CodeIgniter\HTTP\ResponseInterface;

class NotificationController extends BasePaymentController
{
    // =========================================================================
    // MIDTRANS
    // =========================================================================

    /**
     * Handle server-to-server notifications from Midtrans.
     * Route: POST /api/payment/notification/midtrans
     */
    public function handleMidtransNotification(): ResponseInterface
    {
        try {
            log_message('info', 'NotificationController::handleMidtransNotification - received');

            $input = file_get_contents('php://input');
            log_message('debug', 'NotificationController::handleMidtransNotification - raw: ' . $input);

            $payload = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'NotificationController::handleMidtransNotification - JSON parse error: ' . json_last_error_msg());
                return $this->respond(['status' => 'OK', 'message' => 'Notification received but JSON parsing failed'], 200);
            }

            if (empty($payload['order_id'])) {
                log_message('error', 'NotificationController::handleMidtransNotification - Missing order_id');
                return $this->respond(['status' => 'OK', 'message' => 'Missing order_id'], 200);
            }

            return $this->processGatewayWebhook('midtrans', $payload, []);
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::handleMidtransNotification - ' . $e->getMessage());
            // Always 200 to prevent Midtrans retries
            return $this->respond(['status' => 'OK', 'message' => 'Error occurred but notification received'], 200);
        }
    }

    /**
     * Handle successful payment redirect from Midtrans (user browser redirect).
     * Route: GET /api/payment/finish/midtrans
     */
    public function handleMidtransFinish(): ResponseInterface
    {
        try {
            $orderId     = $this->request->getGet('order_id');
            $status      = $this->request->getGet('transaction_status');
            $fraudStatus = $this->request->getGet('fraud_status') ?? null;

            log_message('info', "NotificationController::handleMidtransFinish - Order: {$orderId}, Status: {$status}");

            $paymentData = $this->appendNoteByOrderId(
                $orderId,
                "Payment finish redirect received. Status: {$status}" . ($fraudStatus ? ", Fraud: {$fraudStatus}" : '')
            );

            return $this->respond([
                'status'  => 200,
                'error'   => false,
                'message' => 'Payment completion successfully processed',
                'data'    => [
                    'order_id'          => $orderId,
                    'transaction_status' => $status,
                    'fraud_status'      => $fraudStatus,
                    'payment'           => $paymentData,
                ],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::handleMidtransFinish - ' . $e->getMessage());
            return $this->respond(['status' => 500, 'error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle unfinished payment redirect from Midtrans.
     * Route: GET /api/payment/unfinish/midtrans
     */
    public function handleMidtransUnfinish(): ResponseInterface
    {
        try {
            $orderId = $this->request->getGet('order_id');
            log_message('info', "NotificationController::handleMidtransUnfinish - Order: {$orderId}");

            $paymentData = $this->appendNoteByOrderId($orderId, 'Payment unfinish redirect received. User left payment page.');

            return $this->respond([
                'status'  => 200,
                'error'   => false,
                'message' => 'Payment is unfinished or pending completion',
                'data'    => [
                    'order_id'        => $orderId,
                    'payment'         => $paymentData,
                    'redirect_params' => ['path' => '/payment/pending', 'query' => ['order_id' => $orderId]],
                ],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::handleMidtransUnfinish - ' . $e->getMessage());
            return $this->respond(['status' => 500, 'error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle error redirect from Midtrans.
     * Route: GET /api/payment/error/midtrans
     */
    public function handleMidtransError(): ResponseInterface
    {
        try {
            $orderId       = $this->request->getGet('order_id');
            $statusCode    = $this->request->getGet('status_code') ?? 'unknown';
            $statusMessage = $this->request->getGet('status_message') ?? 'Unknown error';

            log_message('error', "NotificationController::handleMidtransError - Order: {$orderId}, Code: {$statusCode}, Msg: {$statusMessage}");

            $paymentData = null;
            if ($orderId) {
                $payment = $this->paymentModel->where('order_id', $orderId)->first();
                if ($payment) {
                    $note = date('Y-m-d H:i:s') . " - Payment error redirect. Code: {$statusCode}, Message: {$statusMessage}";
                    $this->paymentModel->update($payment->id, [
                        'notes'      => trim(($payment->notes ?? '') . "\n\n" . $note),
                        'status'     => self::STATUS_CANCELLED,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $paymentData = $this->paymentModel->find($payment->id);
                }
            }

            return $this->respond([
                'status'  => 400,
                'error'   => true,
                'message' => "Payment failed: {$statusMessage}",
                'data'    => [
                    'order_id'      => $orderId,
                    'status_code'   => $statusCode,
                    'status_message' => $statusMessage,
                    'payment'       => $paymentData,
                ],
            ], 400);
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::handleMidtransError - ' . $e->getMessage());
            return $this->respond(['status' => 500, 'error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // XENDIT
    // =========================================================================

    /**
     * Handle webhook notifications from Xendit.
     * Route: POST /api/payment/notification/xendit
     *
     * Xendit sends one POST per invoice status change.
     * Authentication: x-callback-token header validated inside XenditGateway.
     */
    public function handleXenditNotification(): ResponseInterface
    {
        try {
            log_message('info', 'NotificationController::handleXenditNotification - received');

            $input = file_get_contents('php://input');
            log_message('debug', 'NotificationController::handleXenditNotification - raw: ' . $input);

            $payload = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'NotificationController::handleXenditNotification - JSON parse error: ' . json_last_error_msg());
                return $this->respond(['status' => 'OK'], 200);
            }

            if (empty($payload['external_id'])) {
                log_message('error', 'NotificationController::handleXenditNotification - Missing external_id');
                return $this->respond(['status' => 'OK', 'message' => 'Missing external_id'], 200);
            }

            // Collect headers for callback token validation
            $headers = [
                'x-callback-token' => $this->request->getHeaderLine('x-callback-token'),
            ];

            return $this->processGatewayWebhook('xendit', $payload, $headers);
        } catch (\Throwable $e) {
            log_message('error', 'NotificationController::handleXenditNotification - [' . get_class($e) . '] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            // Return 200 to prevent Xendit from retrying on our processing errors
            return $this->respond(['status' => 'OK', 'message' => 'Error occurred but notification received'], 200);
        }
    }

    // =========================================================================
    // SHARED WEBHOOK PROCESSING
    // =========================================================================

    /**
     * Parse webhook via the correct gateway adapter and update the payment record.
     *
     * @param string $provider  'midtrans' | 'xendit'
     * @param array  $payload   Decoded JSON body
     * @param array  $headers   Request headers
     */
    private function processGatewayWebhook(string $provider, array $payload, array $headers): ResponseInterface
    {
        $gateway = GatewayFactory::make($provider);
        $result  = $gateway->parseWebhookPayload($payload, $headers);

        $orderId       = $result['order_id'];
        $newStatus     = $result['status'];
        $note          = $result['note'];
        $transactionId = $result['transaction_id'] ?? null;

        $payment = $this->paymentModel->where('order_id', $orderId)->first();

        if (!$payment) {
            log_message('error', "NotificationController::processGatewayWebhook ({$provider}) - Payment not found for order_id: {$orderId}");
            return $this->respond(['status' => 'OK', 'message' => 'Payment not found but notification received'], 200);
        }

        $updateData = [
            'status'     => $newStatus,
            'notes'      => trim(($payment->notes ?? '') . "\n\n" . $note),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($transactionId) {
            $updateData['transaction_id'] = $transactionId;
        }

        try {
            $this->paymentModel->update($payment->id, $updateData);
            log_message('info', "NotificationController::processGatewayWebhook ({$provider}) - Payment {$payment->id} updated to status {$newStatus}");
        } catch (\Throwable $e) {
            log_message('error', "NotificationController::processGatewayWebhook ({$provider}) - DB update error: " . $e->getMessage());
        }

        if ($newStatus === self::STATUS_SUCCESS) {
            $this->handleSuccessfulPayment($payment);
        }

        return $this->respond(['status' => 'OK', 'message' => 'Notification processed successfully'], 200);
    }

    // =========================================================================
    // BUSINESS LOGIC (gateway-agnostic)
    // =========================================================================

    /**
     * Handle post-payment success actions (status updates, email).
     * This logic is intentionally gateway-agnostic.
     */
    private function handleSuccessfulPayment(object $payment): void
    {
        try {
            log_message('info', "NotificationController::handleSuccessfulPayment - Payment ID: {$payment->id}");

            if (empty($payment->participant_id)) {
                return;
            }

            $statusModel       = new \App\Models\ParticipantStatusModel();
            $programPaymentModel = new \App\Models\ProgramPaymentModel();
            $programPayment    = $programPaymentModel->find($payment->program_payment_id);

            if ($programPayment) {
                $statusPayload = [];

                if ($programPayment->category === 'registration') {
                    $statusPayload['payment_status'] = 1;
                } elseif (\in_array($programPayment->category, ['program_fee_1', 'batch_1'])) {
                    $statusPayload['payment_status'] = 2;
                    $statusPayload['general_status'] = 2;
                } elseif (\in_array($programPayment->category, ['program_fee_2', 'batch_2'])) {
                    $statusPayload['payment_status'] = 3;
                    $statusPayload['general_status'] = 3;
                }

                if (!empty($statusPayload)) {
                    $statusRecord = $statusModel->getParticipantStatusById($payment->participant_id);
                    if ($statusRecord) {
                        $statusModel->update($statusRecord->id, $statusPayload);
                        log_message('info', "NotificationController::handleSuccessfulPayment - Participant {$payment->participant_id} status updated");
                    }
                }
            }

            $participantModel = new \App\Models\ParticipantModel();
            $participant      = $participantModel->find($payment->participant_id);

            if ($participant) {
                $participantModel->update($payment->participant_id, ['updated_at' => date('Y-m-d H:i:s')]);
                $this->sendPaymentConfirmationEmail($participant, $payment);
            }
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::handleSuccessfulPayment - ' . $e->getMessage());
        }
    }

    /**
     * Send payment confirmation email to participant.
     */
    private function sendPaymentConfirmationEmail(object $participant, object $payment): void
    {
        try {
            $userModel = new \App\Models\UserModel();
            $user      = $userModel->find($participant->user_id ?? null);

            if (!$user || !$user->email) {
                log_message('error', "NotificationController::sendPaymentConfirmationEmail - No email for participant {$participant->id}");
                return;
            }

            $programPaymentModel = new \App\Models\ProgramPaymentModel();
            $programPayment      = $programPaymentModel->find($payment->program_payment_id ?? null);
            if (!$programPayment) {
                return;
            }

            $programModel = new \App\Models\ProgramModel();
            $program      = $programModel->find($programPayment->program_id ?? null);
            if (!$program) {
                return;
            }

            $webSettings    = $this->webSettingModel->first();
            $formattedAmount = ($payment->currency === 'USD' ? '$' : 'IDR ') . number_format($payment->amount, 2);

            $emailData = [
                'participant_name'  => $participant->full_name,
                'program_name'      => $program->name,
                'transaction_id'    => $payment->transaction_id,
                'order_id'          => $payment->order_id,
                'formatted_amount'  => $formattedAmount,
                'payment_date'      => date('Y-m-d H:i:s'),
                'organization_name' => $webSettings->site_name ?? 'Your Brilliant Brand',
                'logo'              => base_url('assets/images/logo-dark.png'),
            ];

            $emailService = new \App\Services\EmailService();
            $emailService->sendEmail(
                $user->email,
                'Payment Confirmation - ' . $program->name,
                'payment_confirmation',
                $emailData
            );

            log_message('info', "NotificationController::sendPaymentConfirmationEmail - Sent to {$user->email}");
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::sendPaymentConfirmationEmail - ' . $e->getMessage());
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Append a note to a payment record found by order_id, return updated payment.
     */
    private function appendNoteByOrderId(?string $orderId, string $noteText): ?object
    {
        if (!$orderId) {
            return null;
        }

        $payment = $this->paymentModel->where('order_id', $orderId)->first();
        if (!$payment) {
            return null;
        }

        $note = date('Y-m-d H:i:s') . ' - ' . $noteText;
        $this->paymentModel->update($payment->id, [
            'notes'      => trim(($payment->notes ?? '') . "\n\n" . $note),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->paymentModel->find($payment->id);
    }
}
