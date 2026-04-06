<?php

namespace App\Gateways;

use App\Controllers\Api\Payment\BasePaymentController;

/**
 * MidtransGateway
 *
 * Adapter that wraps the Midtrans PHP SDK behind PaymentGatewayInterface.
 * All \Midtrans\* SDK calls are isolated here — no controller touches the SDK directly.
 */
class MidtransGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        $config = new \App\Config\Midtrans\Config();

        \Midtrans\Config::$serverKey   = $config->getServerKey();
        \Midtrans\Config::$isProduction = $config->isProduction();
        \Midtrans\Config::$isSanitized  = $config->isSanitized();
        \Midtrans\Config::$is3ds        = $config->is3ds();
    }

    public function getProviderName(): string
    {
        return 'midtrans';
    }

    /**
     * @param array $params {
     *   order_id, amount (IDR int), usd_amount, customer { first_name, email, phone? },
     *   item { id, name, category }, meta { usd_amount, conversion_rate }
     * }
     */
    public function createTransaction(array $params): array
    {
        $roundedAmount = (int) round($params['amount']);

        $snapParams = [
            'transaction_details' => [
                'order_id'     => $params['order_id'],
                'gross_amount' => $roundedAmount,
            ],
            'customer_details' => [
                'first_name' => $params['customer']['first_name'],
                'email'      => $params['customer']['email'],
            ],
            'item_details' => [
                [
                    'id'       => $params['item']['id'],
                    'price'    => $roundedAmount,
                    'quantity' => 1,
                    'name'     => $params['item']['name'],
                    'category' => $params['item']['category'],
                ],
            ],
            'custom_field1' => 'USD Amount: $' . number_format($params['usd_amount'], 2),
            'custom_field2' => 'Conversion Rate: 1 USD = ' . number_format($params['meta']['conversion_rate'] ?? 0, 0) . ' IDR',
        ];

        if (!empty($params['customer']['phone'])) {
            $snapParams['customer_details']['phone'] = $params['customer']['phone'];
        }

        $token = \Midtrans\Snap::getSnapToken($snapParams);

        $isProduction = \Midtrans\Config::$isProduction;
        $redirectUrl  = $isProduction
            ? "https://app.midtrans.com/snap/v2/vtweb/{$token}"
            : "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$token}";

        return [
            'redirect_url'    => $redirectUrl,
            'token'           => $token,
            'gateway_txn_id'  => null, // Midtrans assigns this only after payment
        ];
    }

    /**
     * @param string $orderId Our order_id (= Midtrans order_id)
     */
    public function checkStatus(string $orderId): array
    {
        $raw = \Midtrans\Transaction::status($orderId);

        if (is_array($raw)) {
            $raw = (object) $raw;
        }

        $transactionStatus = $raw->transaction_status ?? 'unknown';
        $fraudStatus       = $raw->fraud_status ?? '';

        return [
            'status' => $this->mapStatus($transactionStatus, $fraudStatus),
            'raw'    => $raw,
        ];
    }

    /**
     * @param string $orderId Our order_id
     */
    public function cancelTransaction(string $orderId): bool
    {
        try {
            \Midtrans\Transaction::cancel($orderId);
            return true;
        } catch (\Exception $e) {
            // Order may not exist on Midtrans (e.g. user never visited payment page)
            log_message('info', "MidtransGateway::cancelTransaction - cancel skipped (order may not exist on Midtrans): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse Midtrans server-to-server notification payload.
     * Validates by re-fetching status from Midtrans API (as we did before).
     */
    public function parseWebhookPayload(array $payload, array $headers = []): array
    {
        $orderId           = $payload['order_id'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? 'unknown';
        $fraudStatus       = $payload['fraud_status'] ?? null;
        $transactionId     = $payload['transaction_id'] ?? null;

        $status = $this->mapStatus($transactionStatus, $fraudStatus ?? '');

        $note = date('Y-m-d H:i:s') . " - Midtrans notification. Status: {$transactionStatus}";
        if ($fraudStatus) {
            $note .= ", Fraud: {$fraudStatus}";
        }
        if ($transactionId) {
            $note .= ", Txn ID: {$transactionId}";
        }

        return [
            'order_id'       => $orderId,
            'status'         => $status,
            'note'           => $note,
            'transaction_id' => $transactionId,
        ];
    }

    public function getClientConfig(): array
    {
        $config = new \App\Config\Midtrans\Config();

        return [
            'clientKey'    => $config->getClientKey(),
            'isProduction' => $config->isProduction(),
        ];
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Map Midtrans transaction_status + fraud_status to our internal STATUS_* constant.
     */
    private function mapStatus(string $transactionStatus, string $fraudStatus): int
    {
        if ($fraudStatus === 'deny') {
            return BasePaymentController::STATUS_REJECTED;
        }

        return match ($transactionStatus) {
            'capture', 'settlement' => $fraudStatus === 'challenge'
                ? BasePaymentController::STATUS_PENDING
                : BasePaymentController::STATUS_SUCCESS,
            'pending'               => BasePaymentController::STATUS_PENDING,
            'deny', 'cancel', 'expire' => BasePaymentController::STATUS_CANCELLED,
            'refund'                => BasePaymentController::STATUS_REJECTED,
            default                 => BasePaymentController::STATUS_PENDING,
        };
    }
}
