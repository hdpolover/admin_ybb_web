<?php

namespace App\Controllers\Api\Payment;

use App\Gateways\GatewayFactory;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * WebhookController
 *
 * Legacy Midtrans webhook handler reachable at /api/payments/webhook.
 * Kept for backward compatibility — the primary Midtrans webhook route is
 * /api/payment/notification/midtrans → NotificationController::handleMidtransNotification.
 *
 * Delegates status mapping entirely to MidtransGateway so logic is not duplicated.
 */
class WebhookController extends BasePaymentController
{
    public function webhook(): ResponseInterface
    {
        try {
            $input = file_get_contents('php://input');
            log_message('info', 'WebhookController::webhook - received: ' . $input);

            $payload = json_decode($input, true) ?? [];

            // Also handle legacy \Midtrans\Notification object-style input
            if (empty($payload)) {
                try {
                    $notification = new \Midtrans\Notification();
                    $payload = [
                        'order_id'           => $notification->order_id,
                        'transaction_status' => $notification->transaction_status,
                        'fraud_status'       => $notification->fraud_status ?? null,
                        'transaction_id'     => $notification->transaction_id ?? null,
                        'payment_type'       => $notification->payment_type ?? '',
                    ];
                } catch (\Exception $e) {
                    log_message('error', 'WebhookController::webhook - could not parse Midtrans notification: ' . $e->getMessage());
                    return $this->fail('Error processing webhook', 500);
                }
            }

            $orderId = $payload['order_id'] ?? null;
            if (!$orderId) {
                log_message('error', 'WebhookController::webhook - Missing order_id');
                return $this->fail('Missing order_id', 400);
            }

            $gateway = GatewayFactory::make('midtrans');
            $result  = $gateway->parseWebhookPayload($payload);

            $payment = $this->paymentModel->where('order_id', $orderId)
                ->orWhere('transaction_id', $orderId)
                ->first();

            if (!$payment) {
                log_message('error', "WebhookController::webhook - Payment not found for order_id: {$orderId}");
                return $this->fail('Payment not found', 404);
            }

            $updateData = [
                'status'     => $result['status'],
                'notes'      => trim(($payment->notes ?? '') . "\n\n" . $result['note']),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if (!empty($payload['payment_type'])) {
                $updateData['payment_method'] = $payload['payment_type'];
            }
            if (!empty($result['transaction_id'])) {
                $updateData['transaction_id'] = $result['transaction_id'];
            }

            $this->paymentModel->update($payment->id, $updateData);

            return $this->respond(['status' => 200, 'error' => false, 'message' => 'Webhook processed successfully']);
        } catch (\Exception $e) {
            log_message('error', 'WebhookController::webhook - Error: ' . $e->getMessage());
            return $this->fail('Error processing webhook: ' . $e->getMessage(), 500);
        }
    }
}
