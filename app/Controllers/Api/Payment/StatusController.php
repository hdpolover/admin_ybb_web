<?php

namespace App\Controllers\Api\Payment;

use App\Gateways\GatewayFactory;
use CodeIgniter\HTTP\ResponseInterface;

class StatusController extends BasePaymentController
{
    /**
     * Check payment status.
     * For gateway payments that are still pending, we poll the gateway API
     * to get the current status and update our DB if it has changed.
     *
     * @param int|null $id Payment ID
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

            // Poll the gateway if payment is pending and has a trackable order_id
            if ($payment->status == self::STATUS_PENDING && !empty($payment->order_id)) {
                $paymentMethod = $this->paymentMethodModel->find($payment->payment_method_id);

                if ($paymentMethod && $paymentMethod->type === 'gateway' && !empty($paymentMethod->gateway_provider)) {
                    try {
                        $gateway = GatewayFactory::make($paymentMethod->gateway_provider);
                        $result  = $gateway->checkStatus($payment->order_id);
                        $newStatus = $result['status'];

                        if ($newStatus !== $payment->status) {
                            $existingNotes  = $payment->notes ?? '';
                            $combinedNotes  = trim($existingNotes . "\n\n" . date('Y-m-d H:i:s') . " - Status poll ({$paymentMethod->gateway_provider}): " . json_encode($result['raw']));

                            $this->paymentModel->update($payment->id, [
                                'status'     => $newStatus,
                                'updated_at' => date('Y-m-d H:i:s'),
                                'notes'      => $combinedNotes,
                            ]);

                            $payment = $this->paymentModel->find($id);
                        }
                    } catch (\Exception $e) {
                        log_message('error', "StatusController::getStatus - gateway poll failed for payment {$id}: " . $e->getMessage());
                        // Continue with locally stored status
                    }
                }
            }

            // Get participant details for the response
            $participant = null;
            if (!empty($payment->participant_id)) {
                $participant = $this->participantModel->find($payment->participant_id);
            }

            $statusLabels = [
                self::STATUS_CREATED   => 'Created',
                self::STATUS_PENDING   => 'Pending',
                self::STATUS_SUCCESS   => 'Success',
                self::STATUS_CANCELLED => 'Cancelled',
                self::STATUS_REJECTED  => 'Rejected',
            ];

            // Prepare the proof file URL if exists
            $proofFileUrl = null;
            if (!empty($payment->payment_proof)) {
                $proofFileUrl = base_url('writable/uploads/payment_proofs/' . $payment->payment_proof);
            }

            return $this->respond([
                'status' => 200,
                'error'  => false,
                'data'   => [
                    'payment_id'       => $payment->id,
                    'transaction_id'   => $payment->transaction_id,
                    'amount'           => $payment->amount,
                    'currency'         => $payment->currency,
                    'status_code'      => $payment->status,
                    'status'           => $statusLabels[$payment->status] ?? 'Unknown',
                    'payment_method_id' => $payment->payment_method_id,
                    'payment_proof'    => $payment->payment_proof ?? null,
                    'payment_proof_url' => $proofFileUrl,
                    'payment_date'     => $payment->payment_date,
                    'created_at'       => $payment->created_at,
                    'updated_at'       => $payment->updated_at,
                    'participant'      => $participant ? [
                        'id'        => $participant->id,
                        'full_name' => $participant->full_name,
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'StatusController::getStatus - Error: ' . $e->getMessage());
            return $this->fail('Error retrieving payment status: ' . $e->getMessage(), 500);
        }
    }
}
