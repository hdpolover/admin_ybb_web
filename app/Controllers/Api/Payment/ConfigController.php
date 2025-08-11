<?php

namespace App\Controllers\Api\Payment;

use CodeIgniter\HTTP\ResponseInterface;

class ConfigController extends BasePaymentController
{
    /**
     * Get Midtrans client key for frontend initialization
     *
     * This endpoint is NEVER cached to ensure payment configuration
     * is always current and secure
     *
     * @return ResponseInterface
     */
    public function getConfig(): ResponseInterface
    {
        try {
            // Set cache-prevention headers to ensure no browser/proxy caching
            $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
            $this->response->setHeader('Pragma', 'no-cache');
            $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
            
            // Always create a fresh instance to prevent any object-level caching
            $midtransConfig = new \App\Config\Midtrans\Config();
            
            return $this->respond([
                'status' => 200,
                'error' => false,
                'data' => [
                    'clientKey' => $midtransConfig->getClientKey(),
                    'isProduction' => $midtransConfig->isProduction(),
                    'supportedPaymentMethods' => [
                        ['id' => self::PAYMENT_METHOD_MIDTRANS, 'name' => 'Online Payment (Credit Card, Virtual Account, E-wallet)'],
                        ['id' => self::PAYMENT_METHOD_MANUAL, 'name' => 'Manual Bank Transfer']
                    ],
                    // Add timestamp to ensure unique response
                    'timestamp' => time(),
                    'cache_disabled' => true
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting payment config: ' . $e->getMessage());
            return $this->fail('Error retrieving payment configuration', 500);
        }
    }
}