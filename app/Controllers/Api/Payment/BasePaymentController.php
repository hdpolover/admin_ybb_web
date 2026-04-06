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

    // Payment method type (used for manual bank transfer detection)
    const PAYMENT_METHOD_MANUAL = 2;

    // Payment status constants — used across all gateway adapters
    const STATUS_CREATED   = 0;
    const STATUS_PENDING   = 1;
    const STATUS_SUCCESS   = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_REJECTED  = 4;

    /**
     * Initialize controller, set models.
     *
     * Gateway configuration is no longer initialized here — each gateway adapter
     * (MidtransGateway, XenditGateway) loads its own config on construction.
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->paymentModel       = new PaymentModel();
        $this->participantModel   = new ParticipantModel();
        $this->programPaymentModel = new \App\Models\ProgramPaymentModel();
        $this->programModel       = new \App\Models\ProgramModel();
        $this->webSettingModel    = new \App\Models\WebSettingModel();
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
