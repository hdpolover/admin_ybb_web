<?php

namespace App\Controllers\Api\Payment;

use App\Controllers\Api\ApiBaseController;
use App\Models\PaymentModel;
use App\Models\ParticipantModel;

class BasePaymentController extends ApiBaseController
{
    protected $paymentModel;
    protected $participantModel;
    protected $programPaymentModel;
    protected $paymentMethodModel;
    protected $programModel;
    protected $webSettingModel;

    // Payment method constants
    const PAYMENT_METHOD_MIDTRANS = 1;
    const PAYMENT_METHOD_MANUAL = 2;

    // Payment status constants
    const STATUS_CREATED = 0;
    const STATUS_PENDING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_REJECTED = 4;

    /**
     * Initialize controller, set models
     * 
     * IMPORTANT: Payment configuration is loaded fresh on EVERY request
     * to prevent any caching issues that could affect payment processing
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);

        // Configure Midtrans using our custom config
        // ALWAYS create a fresh instance to prevent caching
        $midtransConfig = new \App\Config\Midtrans\Config();
        
        // Set Midtrans configuration directly (no caching)
        \Midtrans\Config::$serverKey = $midtransConfig->getServerKey();
        \Midtrans\Config::$isProduction = $midtransConfig->isProduction();
        \Midtrans\Config::$isSanitized = $midtransConfig->isSanitized();
        \Midtrans\Config::$is3ds = $midtransConfig->is3ds();

        // Log configuration state for debugging (without exposing sensitive data)
        log_message('info', 'Payment Controller - Midtrans configured: ' . 
            'Production=' . ($midtransConfig->isProduction() ? 'true' : 'false') . 
            ', 3DS=' . ($midtransConfig->is3ds() ? 'true' : 'false') . 
            ', Sanitized=' . ($midtransConfig->isSanitized() ? 'true' : 'false'));

        $this->paymentModel = new PaymentModel();
        $this->participantModel = new ParticipantModel();
        $this->programPaymentModel = new \App\Models\ProgramPaymentModel();
        $this->programModel = new \App\Models\ProgramModel();
        $this->webSettingModel = new \App\Models\WebSettingModel();
        $this->paymentMethodModel = new \App\Models\PaymentMethodModel();
    }

    /**
     * Get bank details for manual transfer
     * 
     * @return array
     */
    protected function getBankDetails(): array
    {
        // These should ideally come from a database or environment config
        return [
            [
                'bank_name' => 'Bank Central Asia (BCA)',
                'account_number' => '1234567890',
                'account_holder' => 'YBB Foundation',
                'branch' => 'Jakarta Main Branch'
            ],
            [
                'bank_name' => 'Bank Mandiri',
                'account_number' => '0987654321',
                'account_holder' => 'YBB Foundation',
                'branch' => 'Jakarta Main Branch'
            ]
        ];
    }
}
