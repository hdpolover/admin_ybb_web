<?php

namespace App\Controllers\Api\Payment;

use CodeIgniter\HTTP\ResponseInterface;

class TransactionController extends BasePaymentController
{
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
}