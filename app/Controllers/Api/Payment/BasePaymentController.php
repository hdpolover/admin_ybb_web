<?php

namespace App\Controllers\Api\Payment;

use App\Controllers\Api\ApiBaseController;
use App\Models\PaymentModel;
use App\Models\ParticipantModel;

class BasePaymentController extends ApiBaseController
{
    protected $paymentModel;
    protected $participantModel;
    
    // Payment method constants
    const PAYMENT_METHOD_MIDTRANS = 1;
    const PAYMENT_METHOD_MANUAL = 2;
    
    // Payment status constants
    const STATUS_CREATED = 0;
    const STATUS_PENDING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_REJECTED = 4;
    
    public function __construct()
    {
        parent::__construct();
        
        // Configure Midtrans
        \Midtrans\Config::$serverKey = getenv('midtrans.serverKey');
        \Midtrans\Config::$isProduction = getenv('midtrans.isProduction') === 'true';
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
        
        $this->paymentModel = new PaymentModel();
        $this->participantModel = new ParticipantModel();
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