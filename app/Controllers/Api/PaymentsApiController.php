<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;
use App\Models\PaymentModel;
use App\Models\ParticipantModel;

class PaymentsApiController extends ApiBaseController
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
        // Configure Midtrans
        \Midtrans\Config::$serverKey = getenv('midtrans.serverKey');
        \Midtrans\Config::$isProduction = getenv('midtrans.isProduction') === 'true';
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
        
        $this->paymentModel = new PaymentModel();
        $this->participantModel = new ParticipantModel();
    }

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

    /**
     * Create a new payment transaction (supports both Midtrans and Manual)
     *
     * @return ResponseInterface
     */
    public function createTransaction(): ResponseInterface
    {
        try {
            $data = $this->request->getJSON(true);
            
            // Validate input
            $validation = \Config\Services::validation();
            $validation->setRules([
                'participant_id' => 'required|integer',
                'amount' => 'required|numeric',
                'currency' => 'required|in_list[IDR,USD]',
                'description' => 'permit_empty|string',
                'payment_type' => 'required|in_list[midtrans,manual]'
            ]);
            
            if (!$validation->run($data)) {
                return $this->fail($validation->getErrors(), 400);
            }
            
            // Get participant data
            $participant = $this->participantModel->find($data['participant_id']);
            if (!$participant) {
                return $this->fail('Participant not found', 404);
            }
            
            // Determine payment method
            $paymentMethod = ($data['payment_type'] === 'midtrans') ? 
                self::PAYMENT_METHOD_MIDTRANS : self::PAYMENT_METHOD_MANUAL;
                
            // Create payment record with status "created" (0)
            $paymentData = [
                'participant_id' => $data['participant_id'],
                'amount' => (float) $data['amount'],
                'currency' => $data['currency'],
                'payment_method_id' => $paymentMethod,
                'status' => self::STATUS_CREATED,
                'notes' => $data['description'] ?? 'Payment for YBB Program'
            ];
            
            // Save to database to get payment ID
            $paymentId = $this->paymentModel->insert($paymentData);
            
            if (!$paymentId) {
                log_message('error', 'Failed to create payment record');
                return $this->fail('Failed to create payment record', 500);
            }
            
            // Generate order ID
            $orderId = 'YBB-PMT-' . $paymentId . '-' . time();
            
            // Update payment with order_id
            $this->paymentModel->update($paymentId, ['transaction_id' => $orderId]);
            
            // For manual payment, return immediately with payment details
            if ($paymentMethod === self::PAYMENT_METHOD_MANUAL) {
                // Update payment status to 'pending' for manual payments
                $this->paymentModel->update($paymentId, ['status' => self::STATUS_PENDING]);
                
                // Get bank information for manual transfers
                $bankInfo = $this->getBankDetails();
                
                return $this->respond([
                    'status' => 200,
                    'error' => false,
                    'message' => 'Manual payment record created successfully',
                    'data' => [
                        'transaction_id' => $orderId,
                        'payment_id' => $paymentId,
                        'status' => 'Pending',
                        'payment_type' => 'manual',
                        'bank_details' => $bankInfo,
                        'upload_proof_url' => site_url('api/payments/upload-proof')
                    ]
                ]);
            }
            
            // For Midtrans payments
            try {
                // Set transaction parameters for Midtrans
                $params = [
                    'transaction_details' => [
                        'order_id' => $orderId,
                        'gross_amount' => (float) $data['amount']
                    ],
                    'customer_details' => [
                        'first_name' => $participant->full_name ?? 'Customer',
                        'email' => $participant->email ?? '',
                        'phone' => $participant->phone_number ?? ''
                    ],
                    'item_details' => [
                        [
                            'id' => 'PMT-' . $paymentId,
                            'price' => (float) $data['amount'],
                            'quantity' => 1,
                            'name' => $data['description'] ?? 'Payment for YBB Program'
                        ]
                    ],
                    'enabled_payments' => [
                        'credit_card', 'bca_va', 'bni_va', 'other_va', 'gopay', 'shopeepay'
                    ]
                ];
                
                // Create Snap Token
                $snapToken = \Midtrans\Snap::getSnapToken($params);
                
                // Update payment status to 'pending'
                $this->paymentModel->update($paymentId, ['status' => self::STATUS_PENDING]);
                
                // Return success response with token
                return $this->respond([
                    'status' => 200,
                    'error' => false,
                    'message' => 'Transaction created successfully',
                    'data' => [
                        'transaction_id' => $orderId,
                        'payment_id' => $paymentId,
                        'token' => $snapToken,
                        'redirect_url' => \Midtrans\Config::$isProduction 
                            ? "https://app.midtrans.com/snap/v2/vtweb/{$snapToken}" 
                            : "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}",
                        'payment_type' => 'midtrans'
                    ]
                ]);
                
            } catch (\Exception $e) {
                // If Midtrans fails, update payment status to cancelled and log error
                $this->paymentModel->update($paymentId, [
                    'status' => self::STATUS_CANCELLED,
                    'notes' => 'Failed to create Midtrans transaction: ' . $e->getMessage()
                ]);
                
                log_message('error', 'Midtrans API Error: ' . $e->getMessage());
                return $this->fail('Payment gateway error: ' . $e->getMessage(), 500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Create Transaction Error: ' . $e->getMessage());
            return $this->fail('An unexpected error occurred', 500);
        }
    }
    
    /**
     * Upload proof of payment for manual payments
     *
     * @return ResponseInterface
     */
    public function uploadPaymentProof(): ResponseInterface
    {
        try {
            // Validate payment ID
            $paymentId = $this->request->getPost('payment_id');
            if (!$paymentId) {
                return $this->fail('Payment ID is required', 400);
            }
            
            // Check if payment exists and is a manual payment
            $payment = $this->paymentModel->find($paymentId);
            if (!$payment) {
                return $this->fail('Payment not found', 404);
            }
            
            if ($payment->payment_method_id != self::PAYMENT_METHOD_MANUAL) {
                return $this->fail('This payment is not set for manual processing', 400);
            }
            
            // Allow uploading proof for payments in 'created' or 'pending' state
            // Also allow re-uploading if payment was rejected
            $allowedStatuses = [self::STATUS_CREATED, self::STATUS_PENDING, self::STATUS_REJECTED];
            if (!in_array($payment->status, $allowedStatuses)) {
                return $this->fail('Payment proof cannot be uploaded for this payment status', 400);
            }
            
            // Process file upload
            $file = $this->request->getFile('payment_proof');
            if (!$file || !$file->isValid()) {
                return $this->fail('Valid payment proof file is required', 400);
            }
            
            if ($file->getSize() > 5242880) { // 5MB max
                return $this->fail('File size exceeds the 5MB limit', 400);
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                return $this->fail('Only JPEG, PNG, and PDF files are allowed', 400);
            }
            
            // Generate new filename
            $newName = $payment->transaction_id . '_' . $file->getRandomName();
            
            // Define upload path
            $uploadPath = WRITEPATH . 'uploads/payment_proofs/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Move the file
            if (!$file->move($uploadPath, $newName)) {
                return $this->fail('Failed to upload payment proof', 500);
            }
            
            // Additional notes from the user
            $notes = $this->request->getPost('notes') ?? '';
            $existingNotes = $payment->notes ?? '';
            $combinedNotes = $existingNotes . "\n\n" . date('Y-m-d H:i:s') . " - Manual payment proof uploaded. User notes: {$notes}";
            
            // Update payment record with proof and notes
            $updateData = [
                'payment_proof' => $newName,
                'payment_date' => date('Y-m-d H:i:s'),
                'status' => self::STATUS_PENDING, // Set to pending for admin review
                'notes' => trim($combinedNotes)
            ];
            
            $updated = $this->paymentModel->update($paymentId, $updateData);
            
            if (!$updated) {
                log_message('error', 'Failed to update payment record after proof upload');
                return $this->fail('Failed to update payment details', 500);
            }
            
            return $this->respond([
                'status' => 200,
                'error' => false,
                'message' => 'Payment proof uploaded successfully. Payment will be reviewed by our team.',
                'data' => [
                    'payment_id' => $paymentId,
                    'transaction_id' => $payment->transaction_id,
                    'proof_file' => $newName,
                    'status' => 'Pending Review'
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Payment proof upload error: ' . $e->getMessage());
            return $this->fail('An error occurred while processing your request: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Handle webhook notifications from Midtrans
     *
     * @return ResponseInterface
     */
    public function webhook(): ResponseInterface
    {
        try {
            $notification = new \Midtrans\Notification();
            
            // Get important data
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? null;
            $orderId = $notification->order_id;
            $paymentType = $notification->payment_type ?? '';
            
            // Log webhook data for debugging
            log_message('info', 'Midtrans webhook received: ' . json_encode($notification));
            
            // Find payment by transaction_id (order_id)
            $payment = $this->paymentModel->where('transaction_id', $orderId)->first();
            
            if (!$payment) {
                log_message('error', 'Payment not found for webhook: ' . $orderId);
                return $this->fail('Payment not found', 404);
            }
            
            // Process transaction status
            $newStatus = self::STATUS_CREATED; // Default: created
            
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    // Payment needs review
                    $newStatus = self::STATUS_PENDING; // Pending
                } else if ($fraudStatus == 'accept') {
                    // Payment success
                    $newStatus = self::STATUS_SUCCESS; // Success
                }
            } else if ($transactionStatus == 'settlement') {
                $newStatus = self::STATUS_SUCCESS; // Success
            } else if ($transactionStatus == 'pending') {
                $newStatus = self::STATUS_PENDING; // Pending
            } else if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $newStatus = self::STATUS_CANCELLED; // Cancelled
            } else if ($transactionStatus == 'refund') {
                $newStatus = self::STATUS_REJECTED; // Rejected/Refunded
            }
            
            // Combine existing notes with the new notification data
            $existingNotes = $payment->notes ?? '';
            $combinedNotes = $existingNotes . "\n\n" . date('Y-m-d H:i:s') . " - Midtrans webhook: " . json_encode($notification);
            
            // Update payment details
            $updated = $this->paymentModel->update($payment->id, [
                'status' => $newStatus,
                'payment_method' => $paymentType,
                'payment_date' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'notes' => trim($combinedNotes)
            ]);
            
            if (!$updated) {
                log_message('error', 'Failed to update payment record from webhook');
                return $this->fail('Failed to update payment status', 500);
            }
            
            return $this->respond([
                'status' => 200,
                'error' => false,
                'message' => 'Webhook processed successfully'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Webhook Error: ' . $e->getMessage());
            return $this->fail('Error processing webhook: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Check payment status
     *
     * @param int|null $id
     * @return ResponseInterface
     */
    public function getStatus($id = null): ResponseInterface
    {
        try {
            if (!$id) {
                return $this->fail('Payment ID is required', 400);
            }
            
            $payment = $this->paymentModel->find($id);
            
            if (!$payment) {
                return $this->fail('Payment not found', 404);
            }
            
            // If the payment is still pending and is a Midtrans payment, check with Midtrans
            if ($payment->status == self::STATUS_PENDING && 
                $payment->payment_method_id == self::PAYMENT_METHOD_MIDTRANS && 
                !empty($payment->transaction_id)) {
                try {
                    // Get status from Midtrans API
                    $midtransStatus = \Midtrans\Transaction::status($payment->transaction_id);
                    
                    // Convert to object if it's an array
                    if (is_array($midtransStatus)) {
                        $midtransStatus = (object) $midtransStatus;
                    }
                    
                    // Update local payment status based on Midtrans response
                    if ($midtransStatus && isset($midtransStatus->transaction_status)) {
                        $transactionStatus = $midtransStatus->transaction_status;
                        $fraudStatus = $midtransStatus->fraud_status ?? '';
                        
                        // Map status
                        $newStatus = self::STATUS_PENDING; // Default keep as pending
                        
                        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                            if ($fraudStatus == 'challenge') {
                                $newStatus = self::STATUS_PENDING; // Still pending
                            } else {
                                $newStatus = self::STATUS_SUCCESS; // Success
                            }
                        } else if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                            $newStatus = self::STATUS_CANCELLED; // Cancelled
                        } else if ($transactionStatus == 'refund') {
                            $newStatus = self::STATUS_REJECTED; // Rejected/Refunded
                        }
                        
                        // Update payment in DB if status has changed
                        if ($newStatus != $payment->status) {
                            // Combine existing notes with the new status data
                            $existingNotes = $payment->notes ?? '';
                            $combinedNotes = $existingNotes . "\n\n" . date('Y-m-d H:i:s') . " - Status check: " . json_encode($midtransStatus);
                            
                            $this->paymentModel->update($payment->id, [
                                'status' => $newStatus,
                                'updated_at' => date('Y-m-d H:i:s'),
                                'notes' => trim($combinedNotes)
                            ]);
                            
                            // Refresh payment data
                            $payment = $this->paymentModel->find($id);
                        }
                    }
                    
                } catch (\Exception $e) {
                    log_message('error', 'Error checking Midtrans status: ' . $e->getMessage());
                    // Continue with local data even if there's an error
                }
            }
            
            // Get participant details for the response
            $participant = null;
            if (!empty($payment->participant_id)) {
                $participant = $this->participantModel->find($payment->participant_id);
            }
            
            $statusLabels = [
                self::STATUS_CREATED => 'Created',
                self::STATUS_PENDING => 'Pending',
                self::STATUS_SUCCESS => 'Success',
                self::STATUS_CANCELLED => 'Cancelled',
                self::STATUS_REJECTED => 'Rejected'
            ];
            
            $paymentMethodLabels = [
                self::PAYMENT_METHOD_MIDTRANS => 'Midtrans',
                self::PAYMENT_METHOD_MANUAL => 'Manual Transfer'
            ];
            
            // Prepare the proof file URL if exists
            $proofFileUrl = null;
            if (!empty($payment->payment_proof)) {
                $proofFileUrl = base_url('writable/uploads/payment_proofs/' . $payment->payment_proof);
            }
            
            return $this->respond([
                'status' => 200,
                'error' => false,
                'data' => [
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status_code' => $payment->status,
                    'status' => $statusLabels[$payment->status] ?? 'Unknown',
                    'payment_method_id' => $payment->payment_method_id,
                    'payment_method' => $paymentMethodLabels[$payment->payment_method_id] ?? $payment->payment_method ?? 'Unknown',
                    'payment_proof' => $payment->payment_proof ?? null,
                    'payment_proof_url' => $proofFileUrl,
                    'payment_date' => $payment->payment_date,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at,
                    'participant' => $participant ? [
                        'id' => $participant->id,
                        'full_name' => $participant->full_name
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting payment status: ' . $e->getMessage());
            return $this->fail('Error retrieving payment status: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get bank details for manual transfer
     * 
     * @return array
     */
    private function getBankDetails(): array
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