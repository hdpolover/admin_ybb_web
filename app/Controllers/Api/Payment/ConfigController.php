<?php

namespace App\Controllers\Api\Payment;

use CodeIgniter\HTTP\ResponseInterface;

class ConfigController extends BasePaymentController
{
    /**
     * Get Midtrans client key for frontend initialization
     *
     * @return ResponseInterface
     */
    public function getConfig(): ResponseInterface
    {
        try {
            return $this->respond([
                'status' => 200,
                'error' => false,
                'data' => [
                    'clientKey' => getenv('midtrans.clientKey'),
                    'isProduction' => getenv('midtrans.isProduction') === 'true',
                    'supportedPaymentMethods' => [
                        ['id' => self::PAYMENT_METHOD_MIDTRANS, 'name' => 'Online Payment (Credit Card, Virtual Account, E-wallet)'],
                        ['id' => self::PAYMENT_METHOD_MANUAL, 'name' => 'Manual Bank Transfer']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting payment config: ' . $e->getMessage());
            return $this->fail('Error retrieving payment configuration', 500);
        }
    }
}