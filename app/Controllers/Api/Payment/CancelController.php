<?php

namespace App\Controllers\Api\Payment;

use CodeIgniter\HTTP\ResponseInterface;

class CancelController extends BasePaymentController
{
    /**
     * Cancel a pending payment
     * Supports both Midtrans (automatic) and manual payments.
     *
     * @param int|null $id Payment ID
     * @return ResponseInterface
     */
    public function cancelPayment($id = null): ResponseInterface
    {
        try {
            if (!$id) {
                return $this->fail('Payment ID is required', 400);
            }

            $payment = $this->paymentModel->find($id);

            if (!$payment) {
                return $this->respondError('Payment not found', 404);
            }

            if ($payment->status != self::STATUS_PENDING) {
                return $this->fail('Only pending payments can be cancelled', 400);
            }

            // For Midtrans payments, attempt to cancel on Midtrans side first
            if ($payment->payment_method_id == self::PAYMENT_METHOD_MIDTRANS && !empty($payment->order_id)) {
                try {
                    \Midtrans\Transaction::cancel($payment->order_id);
                    log_message('info', 'CancelController::cancelPayment - Midtrans transaction cancelled for order ID: ' . $payment->order_id);
                } catch (\Exception $e) {
                    // Midtrans may not have a record if the user never visited the payment page
                    log_message('info', 'CancelController::cancelPayment - Midtrans cancel skipped (order may not exist on Midtrans): ' . $e->getMessage());
                }
            }

            $existingNotes = $payment->notes ?? '';
            $cancelNote = date('Y-m-d H:i:s') . ' - Payment cancelled by participant.';
            $combinedNotes = trim($existingNotes . "\n\n" . $cancelNote);

            $this->paymentModel->update($id, [
                'status'     => self::STATUS_CANCELLED,
                'notes'      => $combinedNotes,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $payment = $this->paymentModel->find($id);

            return $this->respondSuccess($payment, self::HTTP_OK, 'Payment cancelled successfully');
        } catch (\Exception $e) {
            log_message('error', 'CancelController::cancelPayment - Error: ' . $e->getMessage());
            return $this->fail('An unexpected error occurred', 500);
        }
    }
}
