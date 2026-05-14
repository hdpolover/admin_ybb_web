<?php

namespace App\Gateways;

use App\Config\Xendit\Config as XenditConfig;
use App\Controllers\Api\Payment\BasePaymentController;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\CustomerObject;
use Xendit\Invoice\InvoiceItem;

/**
 * XenditGateway
 *
 * Adapter that wraps the Xendit PHP SDK v7 behind PaymentGatewayInterface.
 * Uses Xendit Invoices as the primary payment flow (supports VA, e-wallet, card).
 *
 * Xendit flow:
 *   1. Create invoice  → returns invoice_url (redirect user here)
 *   2. Xendit POSTs webhook to /api/payment/notification/xendit on status change
 *   3. Poll GET /v2/invoices?external_id= to check status
 *   4. Expire (cancel) via expireInvoice(invoice_id)
 */
class XenditGateway implements PaymentGatewayInterface
{
    private XenditConfig $config;
    private InvoiceApi   $invoiceApi;

    public function __construct()
    {
        $this->config = new XenditConfig();
        Configuration::setXenditKey($this->config->getSecretKey());
        $this->invoiceApi = new InvoiceApi();
    }

    public function getProviderName(): string
    {
        return 'xendit';
    }

    /**
     * @param array $params {
     *   order_id, amount (IDR float), usd_amount,
     *   customer { first_name, email, phone? },
     *   item { id, name, category },
     *   meta { conversion_rate }
     * }
     */
    public function createTransaction(array $params): array
    {
        $description = ($params['item']['name'] ?? 'YBB Program Payment')
            . ' (USD ' . number_format($params['usd_amount'], 2) . ')';

        $customerData = [
            'given_names' => $params['customer']['first_name'],
            'email'       => $params['customer']['email'],
        ];
        if (!empty($params['customer']['phone'])) {
            $customerData['mobile_number'] = $params['customer']['phone'];
        }
        $customer = new CustomerObject($customerData);

        $item = new InvoiceItem([
            'name'     => $params['item']['name'],
            'quantity' => 1,
            'price'    => (float) $params['amount'],
        ]);

        $createRequest = new CreateInvoiceRequest([
            'external_id' => $params['order_id'],   // maps back to our order_id on webhook
            'amount'      => (float) $params['amount'],
            'description' => $description,
            'currency'    => 'IDR',
            'customer'    => $customer,
            'items'       => [$item],
        ]);

        $invoice = $this->invoiceApi->createInvoice($createRequest);

        return [
            'redirect_url'   => $invoice->getInvoiceUrl(),
            'token'          => null,              // Xendit uses a direct URL, no snap token
            'gateway_txn_id' => $invoice->getId(), // Xendit invoice ID
        ];
    }

    /**
     * Check status by querying invoices with external_id = our order_id.
     *
     * @param string $orderId Our order_id (= Xendit external_id)
     */
    public function checkStatus(string $orderId): array
    {
        $invoices = $this->invoiceApi->getInvoices(
            null,     // for_user_id
            $orderId  // external_id
        );

        if (empty($invoices)) {
            return [
                'status' => BasePaymentController::STATUS_PENDING,
                'raw'    => null,
            ];
        }

        // Most recent invoice for this external_id
        $invoice      = $invoices[0];
        $xenditStatus = strtoupper((string) ($invoice->getStatus() ?? 'PENDING'));

        return [
            'status' => $this->mapStatus($xenditStatus),
            'raw'    => $invoice,
        ];
    }

    /**
     * Expire (cancel) a Xendit invoice.
     * Looks up the Xendit invoice ID via external_id first.
     *
     * @param string $orderId Our order_id (= Xendit external_id)
     */
    public function cancelTransaction(string $orderId): bool
    {
        try {
            $invoices = $this->invoiceApi->getInvoices(null, $orderId);

            if (empty($invoices)) {
                log_message('info', "XenditGateway::cancelTransaction - No invoice found for external_id: {$orderId}");
                return false;
            }

            $invoiceId = $invoices[0]->getId();
            $this->invoiceApi->expireInvoice($invoiceId);

            return true;
        } catch (\Exception $e) {
            log_message('info', "XenditGateway::cancelTransaction - expire failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse a Xendit webhook payload.
     * Validates the x-callback-token header against our configured XENDIT_CALLBACK_TOKEN.
     *
     * Xendit sends: external_id (= our order_id), status, id (= Xendit invoice ID), event.
     */
    public function parseWebhookPayload(array $payload, array $headers = []): array
    {
        // Validate callback token
        $incomingToken = $headers['x-callback-token'] ?? $headers['X-Callback-Token'] ?? '';
        $expectedToken = $this->config->getCallbackToken();

        if ($expectedToken && $incomingToken !== $expectedToken) {
            throw new \RuntimeException('Xendit webhook: invalid callback token');
        }

        $orderId      = $payload['external_id'] ?? '';
        $xenditStatus = strtoupper($payload['status'] ?? 'PENDING');
        $xenditId     = $payload['id'] ?? null;
        $eventType    = $payload['event'] ?? 'invoices.update';

        $status = $this->mapStatus($xenditStatus);

        $note = date('Y-m-d H:i:s') . " - Xendit webhook ({$eventType}). Status: {$xenditStatus}";
        if ($xenditId) {
            $note .= ", Invoice ID: {$xenditId}";
        }

        return [
            'order_id'       => $orderId,
            'status'         => $status,
            'note'           => $note,
            'transaction_id' => $xenditId,
        ];
    }

    public function getClientConfig(): array
    {
        return [
            'publicKey'    => $this->config->getPublicKey(),
            'isProduction' => $this->config->isProduction(),
        ];
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Map Xendit invoice status to our internal STATUS_* constant.
     *
     * Xendit statuses: PENDING, PAID, SETTLED, EXPIRED
     */
    private function mapStatus(string $xenditStatus): int
    {
        return match ($xenditStatus) {
            'PAID', 'SETTLED' => BasePaymentController::STATUS_SUCCESS,
            'EXPIRED'         => BasePaymentController::STATUS_CANCELLED,
            default           => BasePaymentController::STATUS_PENDING,
        };
    }
}
