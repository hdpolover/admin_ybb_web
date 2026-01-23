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
            
            // Log server information to help debug
            log_message('debug', 'Server info: ' . json_encode($_SERVER));
            
            // Get notification data
            $input = file_get_contents('php://input');
            log_message('debug', 'NotificationController::handleMidtransNotification - Raw input: ' . $input);
            
            $notification = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'NotificationController::handleMidtransNotification - JSON decode error: ' . json_last_error_msg());
                // Return OK response anyway to prevent Midtrans from retrying
                return $this->respond(['status' => 'OK', 'message' => 'Notification received but JSON parsing failed'], 200);
            }
            
            log_message('debug', 'NotificationController::handleMidtransNotification - Parsed notification data: ' . json_encode($notification));

            // Ensure Midtrans is configured
            $midtransConfig = new \App\Config\Midtrans\Config();
            \Midtrans\Config::$serverKey = $midtransConfig->getServerKey();
            \Midtrans\Config::$isProduction = $midtransConfig->isProduction();

            // Verify notification from Midtrans
            if (empty($notification['order_id'])) {
                log_message('error', 'NotificationController::handleMidtransNotification - Missing order_id in notification');
                return $this->respond(['status' => 'OK', 'message' => 'Notification received but order_id is missing'], 200);
            }
            
            try {
                $statusResponse = \Midtrans\Transaction::status($notification['order_id']);
                log_message('debug', 'NotificationController::handleMidtransNotification - Status response: ' . json_encode($statusResponse));
            } catch (\Exception $e) {
                log_message('error', 'NotificationController::handleMidtransNotification - Error getting transaction status: ' . $e->getMessage());
                // Continue processing with the notification data we have
            }

            // Process the payment based on the status
            $orderId = $notification['order_id'];
            $transactionStatus = $notification['transaction_status'] ?? 'unknown';
            $fraudStatus = $notification['fraud_status'] ?? null;
            $transactionId = $notification['transaction_id'] ?? null;

            // Find payment by order_id
            $payment = $this->paymentModel->where('order_id', $orderId)->first();

            if (!$payment) {
                log_message('error', "NotificationController::handleMidtransNotification - Payment not found with order_id: {$orderId}");
                // Return OK response anyway to prevent Midtrans from retrying
                return $this->respond(['status' => 'OK', 'message' => 'Payment not found but notification received'], 200);
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
            try {
                $this->paymentModel->update($payment->id, $updateData);
                log_message('info', "NotificationController::handleMidtransNotification - Payment {$payment->id} updated with status: {$newStatus}");
            } catch (\Exception $e) {
                log_message('error', "NotificationController::handleMidtransNotification - Error updating payment: " . $e->getMessage());
                // Continue to process the notification
            }

            // If payment is successful, you might want to update other related records
            if ($newStatus === self::STATUS_SUCCESS) {
                $this->handleSuccessfulPayment($payment);
            }

            // Return OK response to Midtrans
            log_message('info', 'NotificationController::handleMidtransNotification - Responding with 200 OK');
            return $this->respond(['status' => 'OK', 'message' => 'Notification processed successfully'], 200);
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::handleMidtransNotification - Error processing notification: ' . $e->getMessage());
            log_message('error', 'Exception trace: ' . $e->getTraceAsString());
            // Always return a 200 OK response to Midtrans to prevent them from retrying
            return $this->respond(['status' => 'OK', 'message' => 'Error occurred but notification received'], 200);
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

            // Update participant status based on program payment category
            if ($payment->participant_id) {
                // Initialize correct models
                $statusModel = new \App\Models\ParticipantStatusModel();
                $programPaymentModel = new \App\Models\ProgramPaymentModel();
                
                // Get program payment details to determine category
                $programPayment = $programPaymentModel->find($payment->program_payment_id);
                
                if ($programPayment) {
                     $statusPayload = [];
                     
                     // Helper logic to map payment category to status
                     // 1 (regist_paid), 2 (batch 1 paid), 3 (batch 2 paid)
                     // General Status: 2 (payment batch 1 submit), 3 (payment batch 2 submit)
                     
                     if ($programPayment->category === 'registration') {
                         $statusPayload['payment_status'] = 1; 
                         // general_status usually stays as is (0 or 1) for registration fee
                     } 
                     elseif ($programPayment->category === 'program_fee_1' || $programPayment->category === 'batch_1') {
                         $statusPayload['payment_status'] = 2;
                         $statusPayload['general_status'] = 2; // Update general status to "payment batch 1 submit"
                     }
                     elseif ($programPayment->category === 'program_fee_2' || $programPayment->category === 'batch_2') {
                         $statusPayload['payment_status'] = 3;
                         $statusPayload['general_status'] = 3; // Update general status to "payment batch 2 submit"
                     }
                     
                     if (!empty($statusPayload)) {
                        // Find status record (or create if missing?) - usually exists upon registration
                        $statusRecord = $statusModel->getParticipantStatusById($payment->participant_id);
                        
                        if ($statusRecord) {
                            $statusModel->update($statusRecord->id, $statusPayload);
                            log_message('info', "NotificationController::handleSuccessfulPayment - Updated payment status ({$statusPayload['payment_status']}) for participant ID: {$payment->participant_id} (Category: {$programPayment->category})");
                        } else {
                             log_message('error', "NotificationController::handleSuccessfulPayment - Status record not found for participant ID: {$payment->participant_id}");
                        }
                     }
                }

                // Legacy update (kept for safety if other parts of the system read this, although it's not in allowedFields)
                $participantModel = new \App\Models\ParticipantModel();
                $participant = $participantModel->find($payment->participant_id);

                if ($participant) {
                    // Update participant payment status or any other relevant fields
                    $participantModel->update($payment->participant_id, [
                        // 'payment_status' => 'paid', // Removed invalid field
                        'updated_at' => date('Y-m-d H:i:s') 
                    ]);

                    log_message('info', "NotificationController::handleSuccessfulPayment - Updated participant record timestamp for participant ID: {$payment->participant_id}");

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

    /**
     * Handle successful transaction completion from Midtrans payment gateway
     * This endpoint is called when user is redirected from Midtrans after successful payment
     * Returns API response instead of redirecting
     * 
     * @return ResponseInterface
     */
    public function handleMidtransFinish(): ResponseInterface
    {
        try {
            log_message('info', 'NotificationController::handleMidtransFinish - Received finish redirect from Midtrans');

            /** @var \CodeIgniter\HTTP\IncomingRequest $request */
            $request = $this->request;

            // Get transaction details from the query parameters
            $orderId = $request->getGet('order_id');
            $status = $request->getGet('transaction_status');
            $fraudStatus = $request->getGet('fraud_status') ?? null;

            log_message('info', "NotificationController::handleMidtransFinish - Order ID: {$orderId}, Status: {$status}, Fraud Status: {$fraudStatus}");

            $paymentData = null;

            // Process payment if we have valid order ID
            if ($orderId) {
                // Find payment by order_id
                $payment = $this->paymentModel->where('order_id', $orderId)->first();

                if ($payment) {
                    // If payment exists, record the redirect in notes
                    $existingNotes = $payment->notes ?? '';
                    $redirectNote = date('Y-m-d H:i:s') . " - Payment finish redirect received. Status: {$status}";
                    if ($fraudStatus) {
                        $redirectNote .= ", Fraud status: {$fraudStatus}";
                    }

                    $this->paymentModel->update($payment->id, [
                        'notes' => trim($existingNotes . "\n\n" . $redirectNote),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    log_message('info', "NotificationController::handleMidtransFinish - Updated payment notes for payment ID: {$payment->id}");

                    // Get updated payment data
                    $paymentData = $this->paymentModel->find($payment->id);
                }
            }

            // Return success API response with payment details
            return $this->respond([
                'status' => 200,
                'error' => false,
                'message' => 'Payment completion successfully processed',
                'data' => [
                    'order_id' => $orderId,
                    'transaction_status' => $status,
                    'fraud_status' => $fraudStatus,
                    'payment' => $paymentData
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::handleMidtransFinish - Error: ' . $e->getMessage());

            /** @var \CodeIgniter\HTTP\IncomingRequest $request */
            $request = $this->request;

            // Return error API response
            return $this->respond([
                'status' => 500,
                'error' => true,
                'message' => 'Error processing payment completion: ' . $e->getMessage(),
                'data' => [
                    'order_id' => $request->getGet('order_id') ?? null
                ]
            ], 500);
        }
    }

    /**
     * Handle unfinished transaction from Midtrans payment gateway
     * This endpoint is called when user is redirected from Midtrans after leaving the payment page
     * Returns API response for client-side handling
     * 
     * @return ResponseInterface
     */
    public function handleMidtransUnfinish(): ResponseInterface
    {
        try {
            log_message('info', 'NotificationController::handleMidtransUnfinish - Received unfinish redirect from Midtrans');

            /** @var \CodeIgniter\HTTP\IncomingRequest $request */
            $request = $this->request;

            // Get transaction details from the query parameters
            $orderId = $request->getGet('order_id');

            log_message('info', "NotificationController::handleMidtransUnfinish - Order ID: {$orderId}");

            $paymentData = null;

            // Process payment if we have valid order ID
            if ($orderId) {
                // Find payment by order_id
                $payment = $this->paymentModel->where('order_id', $orderId)->first();

                if ($payment) {
                    // If payment exists, record the redirect in notes
                    $existingNotes = $payment->notes ?? '';
                    $redirectNote = date('Y-m-d H:i:s') . " - Payment unfinish redirect received. User left payment page.";

                    $this->paymentModel->update($payment->id, [
                        'notes' => trim($existingNotes . "\n\n" . $redirectNote),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    log_message('info', "NotificationController::handleMidtransUnfinish - Updated payment notes for payment ID: {$payment->id}");

                    // Get updated payment data
                    $paymentData = $this->paymentModel->find($payment->id);
                }
            }

            // Return API response with suggestion for frontend redirect
            return $this->respond([
                'status' => 200,
                'error' => false,
                'message' => 'Payment is unfinished or pending completion',
                'data' => [
                    'order_id' => $orderId,
                    'payment' => $paymentData,
                    'redirect_params' => [
                        'path' => '/payment/pending',
                        'query' => ['order_id' => $orderId]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::handleMidtransUnfinish - Error: ' . $e->getMessage());

            /** @var \CodeIgniter\HTTP\IncomingRequest $request */
            $request = $this->request;

            // Return error API response
            return $this->respond([
                'status' => 500,
                'error' => true,
                'message' => 'Error processing unfinished payment: ' . $e->getMessage(),
                'data' => [
                    'order_id' => $request->getGet('order_id') ?? null,
                    'redirect_params' => [
                        'path' => '/payment/error',
                        'query' => ['order_id' => $request->getGet('order_id') ?? '']
                    ]
                ]
            ], 500);
        }
    }
    
    /**
     * Handle error from Midtrans payment gateway
     * This endpoint is called when user is redirected from Midtrans after a payment error
     * Returns API response for client-side handling
     * 
     * @return ResponseInterface
     */
    public function handleMidtransError(): ResponseInterface
    {
        try {
            log_message('info', 'NotificationController::handleMidtransError - Received error redirect from Midtrans');

            /** @var \CodeIgniter\HTTP\IncomingRequest $request */
            $request = $this->request;

            // Get transaction details from the query parameters
            $orderId = $request->getGet('order_id');
            $statusCode = $request->getGet('status_code') ?? 'unknown';
            $statusMessage = $request->getGet('status_message') ?? 'Unknown error';

            log_message('error', "NotificationController::handleMidtransError - Order ID: {$orderId}, Status code: {$statusCode}, Message: {$statusMessage}");

            $paymentData = null;

            // Process payment if we have valid order ID
            if ($orderId) {
                // Find payment by order_id
                $payment = $this->paymentModel->where('order_id', $orderId)->first();

                if ($payment) {
                    // If payment exists, record the error in notes
                    $existingNotes = $payment->notes ?? '';
                    $errorNote = date('Y-m-d H:i:s') . " - Payment error redirect received. Status code: {$statusCode}, Message: {$statusMessage}";

                    $this->paymentModel->update($payment->id, [
                        'notes' => trim($existingNotes . "\n\n" . $errorNote),
                        'updated_at' => date('Y-m-d H:i:s'),
                        // Update payment status to indicate error
                        'status' => self::STATUS_CANCELLED
                    ]);

                    log_message('info', "NotificationController::handleMidtransError - Updated payment notes for payment ID: {$payment->id}");

                    // Get updated payment data
                    $paymentData = $this->paymentModel->find($payment->id);
                }
            }

            // Return API response with error details
            return $this->respond([
                'status' => 400,
                'error' => true,
                'message' => 'Payment failed with error: ' . $statusMessage,
                'data' => [
                    'order_id' => $orderId,
                    'status_code' => $statusCode,
                    'status_message' => $statusMessage,
                    'payment' => $paymentData,
                    'redirect_params' => [
                        'path' => '/payment/error',
                        'query' => ['order_id' => $orderId]
                    ]
                ]
            ], 400);
        } catch (\Exception $e) {
            log_message('error', 'NotificationController::handleMidtransError - Error: ' . $e->getMessage());

            /** @var \CodeIgniter\HTTP\IncomingRequest $request */
            $request = $this->request;

            // Return error API response
            return $this->respond([
                'status' => 500,
                'error' => true,
                'message' => 'Error processing payment failure: ' . $e->getMessage(),
                'data' => [
                    'order_id' => $request->getGet('order_id') ?? null,
                    'redirect_params' => [
                        'path' => '/payment/error',
                        'query' => ['order_id' => $request->getGet('order_id') ?? '']
                    ]
                ]
            ], 500);
        }
    }
}
