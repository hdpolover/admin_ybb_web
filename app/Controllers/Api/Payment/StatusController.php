<?php

namespace App\Controllers\Api\Payment;

use CodeIgniter\HTTP\ResponseInterface;

class StatusController extends BasePaymentController
{
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
}