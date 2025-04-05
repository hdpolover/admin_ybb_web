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
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);

        // Initialize models
        $this->configController = new ConfigController();
        $this->transactionController = new TransactionController();
        $this->webhookController = new WebhookController();
        $this->statusController = new StatusController();
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
}