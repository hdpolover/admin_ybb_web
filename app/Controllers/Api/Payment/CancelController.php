<?php

namespace App\Controllers\Api\Payment;

use App\Gateways\GatewayFactory;
use CodeIgniter\HTTP\ResponseInterface;

class CancelController extends BasePaymentController
{
    /**
     * Cancel a pending payment.
     * For gateway payments, attempts cancellation on the gateway side first,
     * then marks our local record as cancelled regardless of the gateway outcome
     * (gateway may not have a record if the user never visited the payment page).
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

            // For gateway payments, attempt cancellation on the gateway side
            if (!empty($payment->order_id)) {
                $paymentMethod = $this->paymentMethodModel->find($payment->payment_method_id);

                if ($paymentMethod && $paymentMethod->type === 'gateway' && !empty($paymentMethod->gateway_provider)) {
                    try {
                        $gateway = GatewayFactory::make($paymentMethod->gateway_provider);
                        $gateway->cancelTransaction($payment->order_id);
                        log_message('info', "CancelController::cancelPayment - {$paymentMethod->gateway_provider} transaction cancelled for order: {$payment->order_id}");
                    } catch (\Exception $e) {
                        // Non-fatal: gateway may not have a record yet (user never opened payment page)
                        log_message('info', "CancelController::cancelPayment - gateway cancel skipped: " . $e->getMessage());
                    }
                }
            }

            $existingNotes  = $payment->notes ?? '';
            $cancelNote     = date('Y-m-d H:i:s') . ' - Payment cancelled by participant.';
            $combinedNotes  = trim($existingNotes . "\n\n" . $cancelNote);

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
