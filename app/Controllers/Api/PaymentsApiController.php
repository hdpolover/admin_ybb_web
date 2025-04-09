<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\Payment\ConfigController;
use App\Controllers\Api\Payment\TransactionController;
use App\Controllers\Api\Payment\WebhookController;
use App\Controllers\Api\Payment\StatusController;

/**
 * Main Payments API Controller that routes to specialized payment controllers
 * Maintains backward compatibility with the existing API endpoints
 */
class PaymentsApiController extends ApiBaseController
{
    protected $configController;
    protected $transactionController;
    protected $webhookController;
    protected $statusController;

    /**
     * Initialize controller, set models
     */    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);

        // Initialize controllers and pass the request, response and logger
        $this->configController = new ConfigController();
        $this->configController->initController($request, $response, $logger);
        
        $this->transactionController = new TransactionController();
        $this->transactionController->initController($request, $response, $logger);
        
        $this->webhookController = new WebhookController();
        $this->webhookController->initController($request, $response, $logger);
        
        $this->statusController = new StatusController();
        $this->statusController->initController($request, $response, $logger);
    }

    /**
     * Get payment details by payment ID
     *
     * @param int|null $id Payment ID
     * @return ResponseInterface
     */
    public function getPayment($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->respondValidationErrors('Payment ID is required');
        }

        $paymentModel = new \App\Models\PaymentModel();

        try {
            $payment = $paymentModel->find($id);

            if (!$payment) {
                return $this->respondNotFound('Payment not found');
            }

            return $this->respondSuccess($payment, self::HTTP_OK, 'Payment retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get Midtrans client key for frontend initialization
     * Routes to ConfigController::getConfig()
     *
     * @return ResponseInterface
     */
    public function getConfig(): ResponseInterface
    {
        return $this->configController->getConfig();
    }

    /**
     * Create a new payment transaction (supports both Midtrans and Manual)
     * Routes to TransactionController::createTransaction()
     *
     * @return ResponseInterface
     */
    public function createTransaction(): ResponseInterface
    {
        return $this->transactionController->createTransaction();
    }

    /**
     * Upload proof of payment for manual payments
     * Routes to TransactionController::uploadPaymentProof()
     *
     * @return ResponseInterface
     */
    public function uploadPaymentProof(): ResponseInterface
    {
        return $this->transactionController->uploadPaymentProof();
    }

    /**
     * Handle webhook notifications from Midtrans
     * Routes to WebhookController::webhook()
     *
     * @return ResponseInterface
     */
    public function webhook(): ResponseInterface
    {
        return $this->webhookController->webhook();
    }

    /**
     * Check payment status
     * Routes to StatusController::getStatus()
     *
     * @param int|null $id
     * @return ResponseInterface
     */
    public function getStatus($id = null): ResponseInterface
    {
        return $this->statusController->getStatus($id);
    }

    /**
     * Get payments by participant ID
     *
     * @param int|null $participantId
     * @return ResponseInterface
     */
    public function getPaymentsByParticipantId($participantId = null): ResponseInterface
    {
        if (!$participantId) {
            return $this->respondValidationErrors('Participant ID is required');
        }

        $paymentModel = new \App\Models\PaymentModel();

        try {
            $payments = $paymentModel->getPaymentsByParticipantId($participantId);

            if (!$payments) {
                return $this->respondNotFound('No payments found for this participant ID');
            }

            return $this->respondSuccess($payments, self::HTTP_OK, 'Payments retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get payments by program payment ID and participant ID
     * @param int|null $programPaymentId
     * @param int|null $participantId
     * @return ResponseInterface
     */
    public function getPaymentsByProgramPaymentIdAndParticipantId($programPaymentId = null, $participantId = null): ResponseInterface
    {
        // Validate required parameters
        if (!$programPaymentId || !$participantId) {
            return $this->respondValidationErrors('Program payment ID and participant ID are required');
        }

        $paymentModel = new \App\Models\PaymentModel();
        $programPaymentModel = new \App\Models\ProgramPaymentModel();

        $payments = $paymentModel->getPaymentsByParticipantIdAndProgramPaymentId($participantId, $programPaymentId);

        // if no payments found, return empty array
        if (!$payments) {
            $payments = [];
        }

        $programPayment = $programPaymentModel->find($programPaymentId);

        if (!$programPayment) {
            return $this->respondNotFound('Program payment not found');
        }

        $data = [
            'program_payment' => $programPayment,
            'payments' => $payments
        ];

        return $this->respondSuccess($data, self::HTTP_OK, 'Payments retrieved successfully');
    }

    /**
     * Handle successful payment redirect from Midtrans
     * 
     * @return ResponseInterface
     */
    public function finishRedirect(): ResponseInterface
    {
        // Get transaction ID and order ID from the query parameters
        $orderId = $this->request->getGet('order_id');
        $transactionId = $this->request->getGet('transaction_id');
        $status = $this->request->getGet('transaction_status');

        // Log the successful payment
        log_message('info', "Payment finished: Order ID {$orderId}, Transaction ID {$transactionId}, Status {$status}");

        // Optional: Update payment status if needed
        if ($orderId) {
            $paymentModel = new \App\Models\PaymentModel();
            $payment = $paymentModel->where('order_id', $orderId)->first();

            // Only update if payment exists and status is not already success
            if ($payment && $payment->status !== 'success') {
                // You might want to verify with Midtrans before updating
                // This is where you'd call your StatusController to check
            }
        }

        // You can return a JSON response or redirect to a success page
        return $this->respondSuccess([
            'order_id' => $orderId,
            'transaction_id' => $transactionId,
            'status' => $status
        ], self::HTTP_OK, 'Payment completed successfully');
    }

    /**
     * Handle unfinished payment redirect from Midtrans
     * 
     * @return ResponseInterface
     */
    public function unfinishRedirect(): ResponseInterface
    {
        $orderId = $this->request->getGet('order_id');
        log_message('info', "Payment unfinished: Order ID {$orderId}");

        return $this->respondSuccess([
            'order_id' => $orderId,
        ], self::HTTP_OK, 'Payment is pending or unfinished');
    }

    /**
     * Handle error payment redirect from Midtrans
     * 
     * @return ResponseInterface
     */
    public function errorRedirect(): ResponseInterface
    {
        $orderId = $this->request->getGet('order_id');
        $message = $this->request->getGet('message') ?? 'Payment error occurred';

        log_message('error', "Payment error: Order ID {$orderId}, Message: {$message}");

        return $this->respondError(
            $message,
            self::HTTP_BAD_REQUEST,
            [
                'order_id' => $orderId,
            ]
        );
    }
}
