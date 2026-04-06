<?php

namespace App\Controllers\Api\Payment;

use App\Gateways\GatewayFactory;
use CodeIgniter\HTTP\ResponseInterface;

class ConfigController extends BasePaymentController
{
    /**
     * Return frontend configuration for a payment gateway.
     *
     * Default (no ?provider param) returns Midtrans config for backward
     * compatibility with existing frontend JS that calls /api/payments/config.
     *
     * Pass ?provider=xendit to get Xendit config.
     *
     * This endpoint is NEVER cached.
     *
     * @return ResponseInterface
     */
    public function getConfig(): ResponseInterface
    {
        try {
            $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
            $this->response->setHeader('Pragma', 'no-cache');
            $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

            $provider = $this->request->getGet('provider') ?? 'midtrans';

            $gateway      = GatewayFactory::make($provider);
            $clientConfig = $gateway->getClientConfig();

            return $this->respond([
                'status'    => 200,
                'error'     => false,
                'data'      => array_merge($clientConfig, [
                    'provider'        => $provider,
                    'timestamp'       => time(),
                    'cache_disabled'  => true,
                ]),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->fail('Unknown payment provider: ' . $this->request->getGet('provider'), 400);
        } catch (\Exception $e) {
            log_message('error', 'ConfigController::getConfig - ' . $e->getMessage());
            return $this->fail('Error retrieving payment configuration', 500);
        }
    }
}
