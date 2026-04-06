<?php

namespace App\Gateways;

/**
 * PaymentGatewayInterface
 *
 * All payment gateway adapters (Midtrans, Xendit, etc.) must implement this.
 * Controllers depend only on this interface, never on a concrete gateway SDK.
 */
interface PaymentGatewayInterface
{
    /**
     * Create a transaction with the gateway.
     *
     * @param array $params {
     *   order_id: string,
     *   amount: float,        // IDR
     *   usd_amount: float,
     *   customer: array { first_name, email, phone? },
     *   item: array { id, name, category },
     *   meta: array           // provider-specific extras
     * }
     * @return array {
     *   redirect_url: string,
     *   token: string|null,          // snap token (Midtrans) or null (Xendit)
     *   gateway_txn_id: string|null  // gateway-side transaction ID if returned at creation
     * }
     * @throws \RuntimeException on gateway API failure
     */
    public function createTransaction(array $params): array;

    /**
     * Check the current status of a transaction.
     *
     * @param string $orderId Our internal order_id stored in the payments table
     * @return array {
     *   status: int,   // one of BasePaymentController::STATUS_* constants
     *   raw: mixed     // raw gateway response for logging
     * }
     */
    public function checkStatus(string $orderId): array;

    /**
     * Cancel/expire a transaction on the gateway side.
     *
     * @param string $orderId Our internal order_id
     * @return bool True on success; false if the order didn't exist on the gateway
     * @throws \RuntimeException on unexpected gateway failure
     */
    public function cancelTransaction(string $orderId): bool;

    /**
     * Parse an incoming webhook payload and return normalized data.
     *
     * @param array $payload  Decoded JSON body from the webhook POST
     * @param array $headers  Request headers (needed for signature/token validation)
     * @return array {
     *   order_id: string,
     *   status: int,   // one of BasePaymentController::STATUS_* constants
     *   note: string   // human-readable note for the payment's notes field
     * }
     * @throws \RuntimeException if the webhook signature/token is invalid
     */
    public function parseWebhookPayload(array $payload, array $headers = []): array;

    /**
     * Return frontend configuration for this gateway.
     *
     * @return array  Provider-specific config safe to expose to the client
     *                (e.g. clientKey, publicKey, isProduction)
     */
    public function getClientConfig(): array;

    /**
     * Return the canonical provider name for this gateway.
     *
     * @return string  e.g. 'midtrans', 'xendit'
     */
    public function getProviderName(): string;
}
