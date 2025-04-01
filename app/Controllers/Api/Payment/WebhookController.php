<?php

namespace App\Controllers\Api\Payment;

use CodeIgniter\HTTP\ResponseInterface;

class WebhookController extends BasePaymentController
{
    /**
     * Handle webhook notifications from Midtrans
     *
     * @return ResponseInterface
     */
    public function webhook(): ResponseInterface
    {
        try {
            $notification = new \Midtrans\Notification();
            
            // Get important data
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? null;
            $orderId = $notification->order_id;
            $paymentType = $notification->payment_type ?? '';
            
            // Log webhook data for debugging
            log_message('info', 'Midtrans webhook received: ' . json_encode($notification));
            
            // Find payment by transaction_id (order_id)
            $payment = $this->paymentModel->where('transaction_id', $orderId)->first();
            
            if (!$payment) {
                log_message('error', 'Payment not found for webhook: ' . $orderId);
                return $this->fail('Payment not found', 404);
            }
            
            // Process transaction status
            $newStatus = self::STATUS_CREATED; // Default: created
            
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    // Payment needs review
                    $newStatus = self::STATUS_PENDING; // Pending
                } else if ($fraudStatus == 'accept') {
                    // Payment success
                    $newStatus = self::STATUS_SUCCESS; // Success
                }
            } else if ($transactionStatus == 'settlement') {
                $newStatus = self::STATUS_SUCCESS; // Success
            } else if ($transactionStatus == 'pending') {
                $newStatus = self::STATUS_PENDING; // Pending
            } else if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $newStatus = self::STATUS_CANCELLED; // Cancelled
            } else if ($transactionStatus == 'refund') {
                $newStatus = self::STATUS_REJECTED; // Rejected/Refunded
            }
            
            // Combine existing notes with the new notification data
            $existingNotes = $payment->notes ?? '';
            $combinedNotes = $existingNotes . "\n\n" . date('Y-m-d H:i:s') . " - Midtrans webhook: " . json_encode($notification);
            
            // Update payment details
            $updated = $this->paymentModel->update($payment->id, [
                'status' => $newStatus,
                'payment_method' => $paymentType,
                'payment_date' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'notes' => trim($combinedNotes)
            ]);
            
            if (!$updated) {
                log_message('error', 'Failed to update payment record from webhook');
                return $this->fail('Failed to update payment status', 500);
            }
            
            return $this->respond([
                'status' => 200,
                'error' => false,
                'message' => 'Webhook processed successfully'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Webhook Error: ' . $e->getMessage());
            return $this->fail('Error processing webhook: ' . $e->getMessage(), 500);
        }
    }
}