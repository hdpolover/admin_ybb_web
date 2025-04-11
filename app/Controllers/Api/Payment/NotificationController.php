<?php

namespace App\Controllers\Api\Payment;

use CodeIgniter\HTTP\ResponseInterface;

class NotificationController extends BasePaymentController
{
    /**
     * Handle notifications from Midtrans payment gateway
     * 
     * @return ResponseInterface
     */
    public function handleMidtransNotification(): ResponseInterface
    {
        try {
            log_message('info', 'NotificationController::handleMidtransNotification - Received webhook notification from Midtrans');

            // Get notification data
            $notification = json_decode(file_get_contents('php://input'), true);
            log_message('debug', 'NotificationController::handleMidtransNotification - Raw notification data: ' . json_encode($notification));

            // Ensure Midtrans is configured
            $midtransConfig = new \App\Config\Midtrans\Config();
            \Midtrans\Config::$serverKey = $midtransConfig->getServerKey();
            \Midtrans\Config::$isProduction = $midtransConfig->isProduction();

            // Verify notification from Midtrans
            $statusResponse = \Midtrans\Transaction::status($notification['order_id']);
            log_message('debug', 'NotificationController::handleMidtransNotification - Status response: ' . json_encode($statusResponse));

            // Process the payment based on the status
            $orderId = $notification['order_id'];
            $transactionStatus = $notification['transaction_status'];
            $fraudStatus = $notification['fraud_status'] ?? null;
            $transactionId = $notification['transaction_id'] ?? null;

            // Find payment by order_id
            $payment = $this->paymentModel->where('order_id', $orderId)->first();

            if (!$payment) {
                log_message('error', "NotificationController::handleMidtransNotification - Payment not found with order_id: {$orderId}");
                return $this->respondError('Payment not found', 404);
            }

            // Map Midtrans transaction status to our payment status
            $newStatus = $this->mapTransactionStatus($transactionStatus, $fraudStatus);

            // Prepare update data
            $updateData = [
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Add transaction ID if available
            if ($transactionId) {
                $updateData['transaction_id'] = $transactionId;
            }

            // Add notes about the transaction
            $existingNotes = $payment->notes ?? '';
            $statusNote = date('Y-m-d H:i:s') . " - Payment status updated via Midtrans notification. Status: {$transactionStatus}";
            if ($fraudStatus) {
                $statusNote .= ", Fraud status: {$fraudStatus}";
            }

            $updateData['notes'] = trim($existingNotes . "\n\n" . $statusNote);

            // Update payment record
            $this->paymentModel->update($payment->id, $updateData);

            log_message('info', "NotificationController::handleMidtransNotification - Payment {$payment->id} updated with status: {$newStatus}");

            // If payment is successful, you might want to update other related records
            if ($newStatus === self::STATUS_SUCCESS) {
                $this->handleSuccessfulPayment($payment);
            }

            // Return OK response to Midtrans
            return $this->respond(['status' => 'OK'], 200);
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::handleMidtransNotification - Error processing notification: ' . $e->getMessage());
            return $this->respondError('Error processing notification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Map Midtrans transaction status to our payment status constants
     * 
     * @param string $transactionStatus Midtrans transaction status
     * @param string|null $fraudStatus Midtrans fraud status
     * @return int Our payment status constant
     */
    private function mapTransactionStatus(string $transactionStatus, ?string $fraudStatus): int
    {
        // Handle fraud status first
        if ($fraudStatus === 'deny') {
            return self::STATUS_REJECTED;
        }

        // Map transaction status
        switch ($transactionStatus) {
            case 'capture':
            case 'settlement':
                return self::STATUS_SUCCESS;

            case 'pending':
                return self::STATUS_PENDING;

            case 'deny':
            case 'expire':
            case 'cancel':
                return self::STATUS_CANCELLED;

            default:
                return self::STATUS_PENDING; // Default to pending for unknown status
        }
    }

    /**
     * Handle additional actions needed for successful payments
     * 
     * @param object $payment The payment record
     * @return void
     */
    private function handleSuccessfulPayment(object $payment): void
    {
        try {
            log_message('info', "NotificationController::handleSuccessfulPayment - Processing successful payment for payment ID: {$payment->id}");

            // You can add code here to:
            // 1. Update participant status (e.g., mark as paid)
            // 2. Send confirmation email to participant
            // 3. Create any necessary program enrollment records
            // 4. Generate receipts or invoices

            // Example: Update participant payment status
            if ($payment->participant_id) {
                $participantModel = new \App\Models\ParticipantModel();
                $participant = $participantModel->find($payment->participant_id);

                if ($participant) {
                    // Update participant payment status or any other relevant fields
                    $participantModel->update($payment->participant_id, [
                        'payment_status' => 'paid',
                        'payment_date' => date('Y-m-d H:i:s')
                    ]);

                    log_message('info', "NotificationController::handleSuccessfulPayment - Updated payment status for participant ID: {$payment->participant_id}");

                    // You could also trigger an email notification here
                    $this->sendPaymentConfirmationEmail($participant, $payment);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::handleSuccessfulPayment - Error processing successful payment: ' . $e->getMessage());
            // Don't throw the exception, just log it - we don't want to interrupt the webhook response
        }
    }
    
    /**
     * Send payment confirmation email to participant
     * 
     * @param object $participant Participant object
     * @param object $payment Payment object
     * @return void
     */
    private function sendPaymentConfirmationEmail(object $participant, object $payment): void
    {
        try {
            log_message('info', "NotificationController::sendPaymentConfirmationEmail - Sending confirmation email for payment ID: {$payment->id}");

            // Get user email
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($participant->user_id ?? null);

            if (!$user || !$user->email) {
                log_message('error', "NotificationController::sendPaymentConfirmationEmail - Cannot send confirmation email, no valid email found for participant: {$participant->id}");
                return;
            }

            // Get program payment details
            $programPaymentModel = new \App\Models\ProgramPaymentModel();
            $programPayment = $programPaymentModel->find($payment->program_payment_id ?? null);

            if (!$programPayment) {
                log_message('error', "NotificationController::sendPaymentConfirmationEmail - Program payment not found for ID: {$payment->program_payment_id}");
                return;
            }

            // Get program details
            $programModel = new \App\Models\ProgramModel();
            $program = $programModel->find($programPayment->program_id ?? null);

            if (!$program) {
                log_message('error', "NotificationController::sendPaymentConfirmationEmail - Program not found for ID: {$programPayment->program_id}");
                return;
            }

            // Get web settings for organization info
            $webSettingModel = $this->webSettingModel;
            $webSettings = $webSettingModel->first();

            // Format amount with currency
            $formattedAmount = ($payment->currency === 'USD' ? '$' : 'IDR ') . number_format($payment->amount, 2);

            // Prepare email data
            $emailData = [
                'participant_name' => $participant->full_name,
                'program_name' => $program->name,
                'transaction_id' => $payment->transaction_id,
                'order_id' => $payment->order_id,
                'formatted_amount' => $formattedAmount,
                'payment_date' => date('Y-m-d H:i:s'),
                'organization_name' => $webSettings->site_name ?? 'Your Brilliant Brand',
                'logo' => base_url('assets/images/logo-dark.png'),
            ];

            // Use the EmailService to send the confirmation email
            $emailService = new \App\Services\EmailService();
            $emailService->sendEmail(
                $user->email,
                'Payment Confirmation - ' . $program->name,
                'payment_confirmation',
                $emailData
            );

            log_message('info', "NotificationController::sendPaymentConfirmationEmail - Confirmation email sent to: {$user->email}");
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::sendPaymentConfirmationEmail - Error sending confirmation email: ' . $e->getMessage());
            // Don't throw the exception, just log it
        }
    }
}
