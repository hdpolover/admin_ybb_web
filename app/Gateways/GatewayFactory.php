<?php

namespace App\Gateways;

/**
 * GatewayFactory
 *
 * Resolves the correct PaymentGatewayInterface implementation from a provider
 * name string or from a payment_method DB record.
 *
 * Adding a new gateway = add one case to the match expression + create the adapter class.
 */
class GatewayFactory
{
    /**
     * Resolve a gateway by provider name.
     *
     * @param string $provider  'midtrans' | 'xendit'
     * @throws \InvalidArgumentException for unknown providers
     */
    public static function make(string $provider): PaymentGatewayInterface
    {
        return match ($provider) {
            'midtrans' => new MidtransGateway(),
            'xendit'   => new XenditGateway(),
            default    => throw new \InvalidArgumentException(
                "GatewayFactory: unknown payment gateway provider '{$provider}'"
            ),
        };
    }

    /**
     * Resolve a gateway from a payment_method DB record.
     * The record must have a non-null gateway_provider field.
     *
     * @param object $paymentMethod  A row from the payment_methods table
     * @throws \InvalidArgumentException if gateway_provider is missing or unknown
     */
    public static function makeFromPaymentMethod(object $paymentMethod): PaymentGatewayInterface
    {
        $provider = $paymentMethod->gateway_provider ?? null;

        if (empty($provider)) {
            throw new \InvalidArgumentException(
                "GatewayFactory: payment method ID {$paymentMethod->id} has no gateway_provider set"
            );
        }

        return self::make($provider);
    }
}
