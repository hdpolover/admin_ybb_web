<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\Payment\ConfigController;
use App\Controllers\Api\Payment\TransactionController;
use App\Controllers\Api\Payment\WebhookController;
use App\Controllers\Api\Payment\StatusController;
use App\Controllers\Api\Payment\CancelController;

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
    protected $cancelController;

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

        $this->cancelController = new CancelController();
        $this->cancelController->initController($request, $response, $logger);
    }

    /**
     * Get payment details by payment ID
     * 
     * IMPORTANT: Payment details are NEVER cached to ensure real-time data
     * for critical payment information and status updates
     *
     * @param int|null $id Payment ID
     * @return ResponseInterface
     */
    public function getPayment($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->respondValidationErrors('Payment ID is required');
        }

        // Set cache-prevention headers
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        $paymentModel = new \App\Models\PaymentModel();

        try {
            // Always fetch fresh data - NO CACHING for payment details
            $payment = $paymentModel->find($id);

            if (!$payment) {
                return $this->respondNotFound('Payment not found');
            }

            // Log access to payment details for security auditing
            log_message('info', "Payment detail accessed - ID: {$id}, Status: {$payment->status}");

            return $this->respondSuccess($payment, self::HTTP_OK, 'Payment retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error retrieving payment details: ' . $e->getMessage());
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
     * IMPORTANT: Payment data is NEVER cached to ensure real-time information
     * for payment status and updates
     *
     * @param int|null $participantId
     * @return ResponseInterface
     */
    public function getPaymentsByParticipantId($participantId = null): ResponseInterface
    {
        if (!$participantId) {
            return $this->respondValidationErrors('Participant ID is required');
        }

        // Set cache-prevention headers
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        $paymentModel = new \App\Models\PaymentModel();

        try {
            // Always fetch fresh data - NO CACHING for payment information
            $payments = $paymentModel->getPaymentsByParticipantId($participantId);
            
            // Normalize to array if null
            if (!$payments) {
                $payments = [];
            }

            // Auto-healing: Check other participants (siblings) for the same user in the same program
            // This fixes issues where payments are attached to a duplicate participant record
            try {
                $participantModel = new \App\Models\ParticipantModel();
                $currentParticipant = $participantModel->find($participantId);
                
                if ($currentParticipant && !empty($currentParticipant->user_id)) {
                    // Find all participants for this user in the same program
                    $siblingParticipants = $participantModel->where('user_id', $currentParticipant->user_id)
                                                          ->where('program_id', $currentParticipant->program_id)
                                                          ->where('id !=', $participantId) // Exclude current
                                                          ->where('is_deleted', 0)
                                                          ->findAll();
                                                          
                    if (!empty($siblingParticipants)) {
                        foreach ($siblingParticipants as $sibling) {
                            $siblingPayments = $paymentModel->getPaymentsByParticipantId($sibling->id);
                            if (!empty($siblingPayments)) {
                                log_message('warning', "Auto-healing dashboard lookup: Found payments for sibling participant {$sibling->id}, merging with requested participant {$participantId}.");
                                
                                // Merge logic to avoid duplicates if any
                                // Assuming payments are objects as per Model definition
                                foreach ($siblingPayments as $sp) {
                                    $exists = false;
                                    foreach ($payments as $existingP) {
                                        if ($existingP->id == $sp->id) {
                                            $exists = true;
                                            break;
                                        }
                                    }
                                    if (!$exists) {
                                        $payments[] = $sp;
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Auto-healing check in getPaymentsByParticipantId failed: ' . $e->getMessage());
                // Continue with just the direct payments
            }

            if (empty($payments)) {
                return $this->respondNotFound('No payments found for this participant ID');
            }

            log_message('info', "Participant payments accessed - Participant ID: {$participantId}, Count: " . count($payments));

            return $this->respondSuccess([
                'payments' => $payments,
                'participant_id' => $participantId
            ], self::HTTP_OK, 'Payments retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error retrieving participant payments: ' . $e->getMessage());
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get payments by program payment ID and participant ID
     * 
     * IMPORTANT: Payment data is NEVER cached to ensure real-time information
     * 
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

        // Set cache-prevention headers
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        $paymentModel = new \App\Models\PaymentModel();
        $programPaymentModel = new \App\Models\ProgramPaymentModel();

        try {
            // Always fetch fresh data - NO CACHING for payment information
            $payments = $paymentModel->getPaymentsByParticipantIdAndProgramPaymentId($participantId, $programPaymentId);

            // if no payments found, try to auto-heal by checking other participants for the same user
            if (!$payments) {
                try {
                    // Determine user ID of the current participant
                    $participantModel = new \App\Models\ParticipantModel();
                    $currentParticipant = $participantModel->find($participantId);
                    
                    if ($currentParticipant && !empty($currentParticipant->user_id)) {
                        // Find all participants for this user in the same program
                        $siblingParticipants = $participantModel->where('user_id', $currentParticipant->user_id)
                                                              ->where('program_id', $currentParticipant->program_id)
                                                              ->where('id !=', $participantId) // Exclude current
                                                              ->where('is_deleted', 0)
                                                              ->findAll();
                                                              
                        foreach ($siblingParticipants as $sibling) {
                            $siblingPayments = $paymentModel->getPaymentsByParticipantIdAndProgramPaymentId($sibling->id, $programPaymentId);
                            if (!empty($siblingPayments)) {
                                log_message('warning', "Auto-healing payment lookup: Requested participant {$participantId} has no payments, but sibling {$sibling->id} does. Returning sibling payments.");
                                $payments = $siblingPayments;
                                break; 
                            }
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Auto-healing check failed: ' . $e->getMessage());
                }
            }

            // if still no payments found, return empty array
            if (!$payments) {
                $payments = [];
            }

            $programPayment = $programPaymentModel->find($programPaymentId);

            if (!$programPayment || $programPayment->is_deleted == 1) {
                return $this->respondNotFound('Program payment not found');
            }

            // Enhance program payment with current period information for accurate date/status
            $programPaymentModel->enhancePaymentWithCurrentPeriod($programPayment);

            $data = [
                'program_payment' => $programPayment,
                'payments' => $payments,
                'participant_id' => $participantId,
                'program_payment_id' => $programPaymentId
            ];

            log_message('info', "Program payment data accessed - Program Payment ID: {$programPaymentId}, Participant ID: {$participantId}");

            return $this->respondSuccess($data, self::HTTP_OK, 'Payments retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error retrieving program payment data: ' . $e->getMessage());
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
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


    /**
     * Cancel a pending payment (automatic or manual)
     * Routes to CancelController::cancelPayment()
     *
     * @param int|null $id Payment ID
     * @return ResponseInterface
     */
    public function cancelPayment($id = null): ResponseInterface
    {
        return $this->cancelController->cancelPayment($id);
    }

    /**
     * index - GET {{api_url}}/payments/{{payment_id}}
     * Auto-generated method
     */
    public function index($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement index logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'index executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute index',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
