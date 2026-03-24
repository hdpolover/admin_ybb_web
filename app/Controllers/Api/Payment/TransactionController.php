<?php

namespace App\Controllers\Api\Payment;

use CodeIgniter\HTTP\ResponseInterface;

class TransactionController extends BasePaymentController
{
    /**
     * Generate a unique transaction code
     * 
     * @param int $paymentId The ID of the payment record
     * @param string $prefix Optional prefix for the transaction code, defaults to 'YBB-PMT'
     * @return string The generated transaction code
     */
    private function generateTransactionCode(int $paymentId, string $prefix = 'TR'): string
    {
        // Generate a unique identifier using payment ID and timestamp
        $uniqueId = $paymentId . '-' . time();

        // Combine prefix and unique identifier
        $transactionCode = $prefix . '-' . $uniqueId;

        return $transactionCode;
    }

    /**
     * Generate a unique order ID
     * 
     * @param int $paymentId The ID of the payment record
     * @return string The generated order ID (numeric only)
     */
    private function generateOrderId(int $paymentId): string
    {
        // Generate a unique identifier using payment ID, timestamp and random numeric suffix
        $timestamp = time();
        $random = mt_rand(100000, 999999); // 6-digit random number

        // Combine payment ID, timestamp and random number, numbers only
        $orderId = $paymentId . $timestamp . $random;

        return $orderId;
    }

    /**
     * Create a new payment transaction (supports both Midtrans and Manual)
     *
     * @return ResponseInterface
     */
    public function createTransaction(): ResponseInterface
    {
        try {
            log_message('info', 'TransactionController::createTransaction - Request started');

            // Handle both JSON and form data (for file uploads)
            $isMultipart = strpos($this->request->getHeaderLine('Content-Type'), 'multipart/form-data') !== false;
            log_message('debug', 'TransactionController::createTransaction - Content-Type: ' . $this->request->getHeaderLine('Content-Type'));
            log_message('debug', 'TransactionController::createTransaction - isMultipart: ' . ($isMultipart ? 'true' : 'false'));

            if ($isMultipart) {
                $data = $this->request->getPost();
                log_message('debug', 'TransactionController::createTransaction - POST data: ' . json_encode($this->request->getPost()));
            } else {
                $data = $this->request->getJSON(true);
                log_message('debug', 'TransactionController::createTransaction - JSON body: ' . json_encode($data));
            }

            // Validate input
            log_message('info', 'TransactionController::createTransaction - Validating input data');
            $validation = \Config\Services::validation();
            $validation->setRules([
                'participant_id' => 'required|integer',
                'program_payment_id' => 'required|integer',
                'payment_method_id' => 'required|integer',
                'account_name' => 'permit_empty|string',
                'source_name' => 'permit_empty|string',
                'notes' => 'permit_empty|string',
                'payment_date' => 'permit_empty|valid_date[Y-m-d]',
                'amount' => 'permit_empty|numeric|greater_than[0]',
            ]);

            if (!$validation->run($data)) {
                $errors = $validation->getErrors();
                log_message('error', 'TransactionController::createTransaction - Validation failed: ' . json_encode($errors));
                return $this->fail($errors, 400);
            }

            log_message('info', 'TransactionController::createTransaction - Validation successful');            // Get participant data
            log_message('info', 'TransactionController::createTransaction - Getting participant data for ID: ' . $data['participant_id']);
            $participant = $this->participantModel->find($data['participant_id']);

            if (!$participant) {
                log_message('error', 'TransactionController::createTransaction - Participant not found with ID: ' . $data['participant_id']);
                return $this->respondError('Participant not found', 404);
            }
            log_message('debug', 'TransactionController::createTransaction - Participant found: ' . json_encode($participant));

            // get payment method data
            log_message('info', 'TransactionController::createTransaction - Getting payment method for ID: ' . $data['payment_method_id']);
            $paymentMethod = $this->paymentMethodModel->find($data['payment_method_id']);

            if (!$paymentMethod) {
                log_message('error', 'TransactionController::createTransaction - Payment method not found with ID: ' . $data['payment_method_id']);
                return $this->respondError('Payment method not found', 404);
            }
            log_message('debug', 'TransactionController::createTransaction - Payment method found: ' . json_encode($paymentMethod));

            // Determine payment method
            $paymentMethodType = ($paymentMethod->type === 'gateway') ?
                self::PAYMENT_METHOD_MIDTRANS : self::PAYMENT_METHOD_MANUAL;            // get program payment data
            log_message('info', 'TransactionController::createTransaction - Getting program payment data for ID: ' . $data['program_payment_id']);
            $programPayment = $this->programPaymentModel->find($data['program_payment_id']);

            if (!$programPayment) {
                log_message('error', 'TransactionController::createTransaction - Program payment not found with ID: ' . $data['program_payment_id']);
                return $this->respondError('Program payment not found', 404);
            }
            log_message('debug', 'TransactionController::createTransaction - Program payment found: ' . json_encode($programPayment));
            log_message('debug', 'TransactionController::createTransaction - Program payment USD amount: ' . ($programPayment->usd_amount ?? 'NULL'));

            // get program data using program_id from program_payment
            log_message('info', 'TransactionController::createTransaction - Getting program data for ID: ' . $programPayment->program_id);
            $program = $this->programModel->find($programPayment->program_id);

            if (!$program) {
                log_message('error', 'TransactionController::createTransaction - Program not found with ID: ' . $programPayment->program_id);
                return $this->respondError('Program not found', 404);
            }
            log_message('debug', 'TransactionController::createTransaction - Program found: ' . json_encode($program));
            log_message('debug', 'TransactionController::createTransaction - Program ID: ' . $program->id . ', Program Category ID: ' . $program->program_category_id);            // get web setting data using program category ID
            log_message('info', 'TransactionController::createTransaction - Getting web setting data for program category ID: ' . $program->program_category_id);
            log_message('debug', 'TransactionController::createTransaction - Program data: ID=' . $program->id . ', Name=' . ($program->name ?? 'N/A') . ', Category_ID=' . $program->program_category_id);
            
            // Get exchange rate: prefer per-program rate, fall back to web_settings category rate
            $usdInIdr = 0;
            if (!empty($program->usd_in_idr) && $program->usd_in_idr > 0) {
                $usdInIdr = $program->usd_in_idr;
                log_message('debug', 'TransactionController::createTransaction - Using per-program exchange rate: ' . $usdInIdr);
            } else {
                $webSetting = $this->webSettingModel->getSettingByProgramCategoryId($program->program_category_id);
                if ($webSetting) {
                    $usdInIdr = $webSetting->usd_in_idr ?? 0;
                    log_message('debug', 'TransactionController::createTransaction - Falling back to web_settings exchange rate: ' . $usdInIdr);
                } else {
                    log_message('error', 'TransactionController::createTransaction - No exchange rate found for program ID: ' . $program->id . ' or category ID: ' . $program->program_category_id);
                    return $this->respondError('Exchange rate not configured for this program', 404);
                }
            }
            log_message('debug', 'TransactionController::createTransaction - Final USD to IDR conversion rate: ' . $usdInIdr);

            if ($usdInIdr <= 0) {
                log_message('error', 'TransactionController::createTransaction - Invalid USD to IDR conversion rate: ' . $usdInIdr);
                return $this->respondError('Invalid USD to IDR conversion rate: ' . $usdInIdr, 400);
            }

            // Get the USD amount from program payment
            $usdAmount = $programPayment->usd_amount ?? 0;
            log_message('debug', 'TransactionController::createTransaction - USD amount from program payment: ' . $usdAmount);

            if ($usdAmount <= 0) {
                log_message('error', 'TransactionController::createTransaction - USD amount must be greater than zero. USD Amount: ' . $usdAmount);
                return $this->respondError('USD amount must be greater than zero', 400);
            }

            // Convert USD amount to IDR using web setting conversion rate
            $amount = $usdAmount * $usdInIdr;
            $currency = 'IDR'; // Always use IDR for Midtrans transactions

            log_message('info', 'TransactionController::createTransaction - USD to IDR conversion calculation:');
            log_message('info', 'TransactionController::createTransaction - USD Amount: ' . $usdAmount);
            log_message('info', 'TransactionController::createTransaction - Conversion Rate (USD to IDR): ' . $usdInIdr);
            log_message('info', 'TransactionController::createTransaction - Final IDR Amount: ' . $amount . ' (' . $usdAmount . ' * ' . $usdInIdr . ')');
            log_message('info', 'TransactionController::createTransaction - Currency: ' . $currency);            log_message('info', 'TransactionController::createTransaction - DATA FLOW SUMMARY:');
            log_message('info', 'TransactionController::createTransaction - 1. Program Payment ID: ' . $data['program_payment_id']);
            log_message('info', 'TransactionController::createTransaction - 2. Program Payment -> Program ID: ' . $programPayment->program_id);
            log_message('info', 'TransactionController::createTransaction - 3. Program -> Program Category ID: ' . $program->program_category_id);
            log_message('info', 'TransactionController::createTransaction - 4. Web Setting (by Program Category ID): Found = ' . ($webSetting ? 'YES' : 'NO'));
            log_message('info', 'TransactionController::createTransaction - 5. USD Amount (from Program Payment): ' . $usdAmount);
            log_message('info', 'TransactionController::createTransaction - 6. Conversion Rate (from Web Setting): ' . $usdInIdr);
            log_message('info', 'TransactionController::createTransaction - 7. Final IDR Amount: ' . $amount);

            // Create payment record with status "created" (0)
            $paymentData = [
                'participant_id' => $data['participant_id'],
                'program_payment_id' => $data['program_payment_id'],
                'payment_method_id' => $paymentMethod->id,
                'amount' => (float) $amount,
                'usd_amount' => (float) $usdAmount,
                'exchange_rate' => (float) $usdInIdr,
                'currency' => $currency,
                'status' => self::STATUS_PENDING,
                'notes' => $data['notes'] ?? 'Payment for YBB Program',
                'account_name' => $data['account_name'] ?? null,
                'source_name' => $data['source_name'] ?? null,
                'payment_date' => $data['payment_date'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            log_message('info', 'TransactionController::createTransaction - Payment data to be saved:');
            log_message('info', 'TransactionController::createTransaction - Amount (IDR): ' . $paymentData['amount']);
            log_message('info', 'TransactionController::createTransaction - USD Amount: ' . $paymentData['usd_amount']);
            log_message('info', 'TransactionController::createTransaction - Currency: ' . $paymentData['currency']);

            // Save to database to get payment ID
            $paymentId = $this->paymentModel->insert($paymentData);

            if (!$paymentId) {
                log_message('error', 'Failed to create payment record');
                return $this->respondError('Failed to create payment record', 500);
            }

            // Generate transaction code
            $transactionCode = $this->generateTransactionCode($paymentId);

            // For payments, generate a unique order ID
            $orderId = $this->generateOrderId($paymentId);

            // Update payment with transaction_code
            $this->paymentModel->update($paymentId, ['transaction_code' => $transactionCode, 'order_id' => $orderId]);            // For manual payment, handle proof upload if available

            if ($paymentMethodType === self::PAYMENT_METHOD_MANUAL) {
                // Process proof file upload if provided
                $paymentProofUrl = null;
                $file = $this->request->getFile('proof');

                if ($file && $file->isValid()) {
                    if ($file->getSize() > 5242880) { // 5MB max
                        return $this->respondError('File size exceeds the 5MB limit', 400);
                    }

                    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
                    if (!in_array($file->getMimeType(), $allowedTypes)) {
                        return $this->respondError('Only JPEG, PNG, and PDF files are allowed', 400);
                    }

                    // Generate new filename
                    $newName = $orderId . '_' . $file->getRandomName();

                    $multipartData[] = [
                        'name'     => 'image',
                        'contents' => fopen($file->getTempName(), 'r'),
                        'filename' => $file->getClientName()
                    ];

                    // Prepare file data for storage helper
                    $fileData = [
                        'name' => $file->getName(),
                        'type' => $file->getMimeType(),
                        'tmp_name' => $file->getTempName(),
                        'error' => $file->getError(),
                        'size' => $file->getSize()
                    ];

                    // Upload to storage using helper
                    $uploadResult = upload_file_to_storage(
                        $fileData,
                        'payments/' . $program->id,
                        $newName,
                        $allowedTypes
                    );

                    if (!$uploadResult['status']) {
                        return $this->respondError('Failed to upload payment proof: ' . $uploadResult['message'], 500);
                    }

                    // Set payment proof URL from upload result
                    $paymentProofUrl = $uploadResult['url'];

                    // Additional notes about proof upload
                    $existingNotes = $paymentData['notes'] ?? '';
                    $paymentData['notes'] = trim($existingNotes . "\n\n" . date('Y-m-d H:i:s') . " - Payment proof uploaded during transaction creation.");

                    // Update payment with proof file info
                    $this->paymentModel->update($paymentId, [
                        'proof_url' => $paymentProofUrl,
                        'notes' => $paymentData['notes']
                    ]);
                }

                // Update payment status to 'pending' for manual payments
                $this->paymentModel->update($paymentId, ['status' => self::STATUS_PENDING]);

                // get payment data
                $payment = $this->paymentModel->find($paymentId);

                if (!$payment) {
                    return $this->respondError('Payment not found', 404);
                }

                // update participant category to self-funded if applicable
                if ($this->updateParticipantCategoryIfSelfFunded($paymentData['participant_id'], $paymentData['program_payment_id'])) {
                    log_message('info', 'TransactionController::createTransaction - Participant category updated to self_funded successfully');
                } else {
                    log_message('info', 'TransactionController::createTransaction - No update needed for participant category');
                }

                // Return success response with payment details
                return $this->respondSuccess(
                    $payment,
                    self::HTTP_OK,
                    'Manual transaction payment created successfully'
                );
            }

            // update participant category to self-funded if applicable
            if ($this->updateParticipantCategoryIfSelfFunded($paymentData['participant_id'], $paymentData['program_payment_id'])) {
                log_message('info', 'TransactionController::createTransaction - Participant category updated to self_funded successfully');
            } else {
                log_message('info', 'TransactionController::createTransaction - No update needed for participant category');
            }

            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($participant->user_id);

            if (!$user) {
                return $this->respondError('User not found', 404);
            }

            // For Midtrans payments
            try {
                // Make sure Midtrans is properly configured with server key directly from our config
                $midtransConfig = new \App\Config\Midtrans\Config();
                \Midtrans\Config::$serverKey = $midtransConfig->getServerKey();
                \Midtrans\Config::$isProduction = $midtransConfig->isProduction();
                \Midtrans\Config::$isSanitized = true;
                \Midtrans\Config::$is3ds = true;

                // Log the server key to help with debugging (mask it for security)
                $serverKeyLength = strlen(\Midtrans\Config::$serverKey);
                $maskedServerKey = substr(\Midtrans\Config::$serverKey, 0, 4) . str_repeat('*', $serverKeyLength - 8) . substr(\Midtrans\Config::$serverKey, -4);
                log_message('debug', 'TransactionController::createTransaction - Configuring Midtrans with server key: ' . $maskedServerKey);
                log_message('debug', 'TransactionController::createTransaction - Production mode: ' . (\Midtrans\Config::$isProduction ? 'true' : 'false'));                // Set transaction parameters for Midtrans
                log_message('info', 'TransactionController::createTransaction - Preparing Midtrans transaction parameters:');
                log_message('info', 'TransactionController::createTransaction - Order ID: ' . $orderId);
                log_message('info', 'TransactionController::createTransaction - Gross Amount (IDR): ' . $amount);
                
                $params = [
                    'transaction_details' => [
                        'order_id' => $orderId,
                        'gross_amount' => (float) $amount
                    ],
                    'customer_details' => [
                        'first_name' => $participant->full_name ?? 'Customer',
                        'email' => $user->email ?? ''
                    ],
                    'item_details' => [
                        [
                            'id' => $data['program_payment_id'],
                            'price' => (float) $amount,
                            'quantity' => 1,
                            'name' => $program->name ?? 'YBB Program Payment',
                            'category' => $programPayment->category ?? 'registration'
                        ]
                    ],
                    'custom_field1' => 'USD Amount: $' . number_format($usdAmount, 2),
                    'custom_field2' => 'Conversion Rate: 1 USD = ' . number_format($usdInIdr, 0) . ' IDR'
                ];

                log_message('debug', 'TransactionController::createTransaction - Midtrans params: ' . json_encode($params));

                // Add phone to customer_details only if it exists
                if (!empty($participant->phone_number)) {
                    if (!empty($participant->country_code)) {
                        $phoneNumber = $participant->country_code . $participant->phone_number;
                    } else {
                        $phoneNumber = $participant->phone_number;
                    }

                    // remove non-numeric characters from phone number
                    $phoneNumber = preg_replace('/\D/', '', $phoneNumber);
                    $params['customer_details']['phone'] = $phoneNumber;
                }                // Create Snap Token
                log_message('info', 'TransactionController::createTransaction - Creating Midtrans Snap Token for Order ID: ' . $orderId . ', Amount: IDR ' . number_format($amount, 0) . ' (from USD ' . $usdAmount . ')');
                log_message('info', 'TransactionController::createTransaction - FINAL VERIFICATION - USD: ' . $usdAmount . ' * Rate: ' . $usdInIdr . ' = IDR: ' . $amount);
                
                $snapToken = \Midtrans\Snap::getSnapToken($params);
                log_message('info', 'TransactionController::createTransaction - Snap token created successfully: ' . $snapToken);

                // Generate redirect URL based on environment
                $redirectUrl = \Midtrans\Config::$isProduction
                    ? "https://app.midtrans.com/snap/v2/vtweb/{$snapToken}"
                    : "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}";

                // Update payment status to 'pending' and save the redirect URL
                $this->paymentModel->update($paymentId, [
                    'status' => self::STATUS_PENDING,
                    'payment_url' => $redirectUrl
                ]);
                $returnData = [
                    'order_id' => $orderId,
                    'payment_id' => $paymentId,
                    'token' => $snapToken,
                    'redirect_url' => $redirectUrl,
                    'amount_details' => [
                        'usd_amount' => $usdAmount,
                        'idr_amount' => $amount,
                        'conversion_rate' => $usdInIdr,
                        'currency' => 'IDR'
                    ],
                    'program_details' => [
                        'program_name' => $program->name ?? 'YBB Program',
                        'payment_category' => $programPayment->category ?? 'registration'
                    ]
                ];                // Return success response with token
                return $this->respondSuccess(
                    $returnData,
                    self::HTTP_OK,
                    'Midtrans transaction created successfully. USD ' . $usdAmount . ' converted to IDR ' . number_format($amount, 0) . ' at rate 1:' . number_format($usdInIdr, 0)
                );
            } catch (\Exception $e) {
                // Delete the payment record if Midtrans transaction fails
                $this->paymentModel->delete($paymentId);

                log_message('error', 'Midtrans API Error: ' . $e->getMessage());
                return $this->fail('Payment failed. Please try again later. Error: ' . $e->getMessage(), 500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Create Transaction Error: ' . $e->getMessage());
            return $this->fail('An unexpected error occurred', 500);
        }
    }

    /**
     * Update participant category to self-funded when applicable
     *
     * @param int $participantId The participant ID
     * @param int $programPaymentId The program payment ID
     * @return bool Whether the update was successful
     */
    private function updateParticipantCategoryIfSelfFunded(int $participantId, int $programPaymentId): bool
    {
        try {
            // Get program payment data
            $programPayment = $this->programPaymentModel->find($programPaymentId);

            if (!$programPayment) {
                log_message('error', 'updateParticipantCategoryIfSelfFunded - Program payment not found with ID: ' . $programPaymentId);
                return false;
            }            // Check if payment category is self-funded and category is registration
            if ($programPayment->type === 'self_funded' && $programPayment->category === 'registration') {
                log_message('info', 'Updating participant category to self_funded for participant ID: ' . $participantId);

                // Update participant category
                $this->participantModel->update($participantId, [
                    'category' => 'self_funded',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                log_message('info', 'Participant category updated to self_funded successfully');
                return true;
            }

            return false;
        } catch (\Exception $e) {
            log_message('error', 'Error updating participant category: ' . $e->getMessage());
            return false;
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
