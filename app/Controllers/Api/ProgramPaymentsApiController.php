<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramPaymentModel;

class ProgramPaymentsApiController extends ApiBaseController
{
    protected $model;

    /**
     * Initialize controller, set model
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);

        // Initialize model - this is what was previously in the constructor
        $this->model = new ProgramPaymentModel();
    }

    /**
     * Get All Program Payments
     * GET /api/program-payments
     * 
     * IMPORTANT: Program payment data is NOT cached to ensure real-time period information
     * Payment periods can change availability and dates dynamically
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        // Set cache-prevention headers
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        try {
            // Always fetch fresh data - NO CACHING for program payment data with periods
            $programPayments = $this->model->findAll();
            
            // Enhance each payment with current period information
            foreach ($programPayments as &$payment) {
                $this->model->enhancePaymentWithCurrentPeriod($payment);
            }

            return $this->respondSuccess($programPayments, self::HTTP_OK, 'Program payments retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error retrieving all program payments: ' . $e->getMessage());
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get Single Program Payment
     * GET /api/program-payments/{id}
     * 
     * IMPORTANT: Program payment data is NOT cached to ensure real-time period information
     * Payment periods can change availability and dates dynamically
     */
    public function show($id = null)
    {
        if ($id === null) {
            return $this->respondValidationErrors('Program payment ID is required');
        }

        // Set cache-prevention headers
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        try {
            // Always fetch fresh data - NO CACHING for program payment details
            $programPayment = $this->model->find($id);

            if (!$programPayment) {
                return $this->respondNotFound('Program payment not found');
            }

            // Enhance payment with current period information
            $this->model->enhancePaymentWithCurrentPeriod($programPayment);

            // Log access to program payment details
            log_message('info', "Program payment detail accessed - ID: {$id}, Current period: " . ($programPayment->current_period_name ?? 'none'));

            return $this->respondSuccess($programPayment, self::HTTP_OK, 'Program payment retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error retrieving program payment details: ' . $e->getMessage());
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get program payments by program ID
     * GET /api/program-payments/program/{programId}
     * 
     * Query parameters:
     * - include_inactive: Set to 'true' to include inactive payments (for admin use)
     * 
     * IMPORTANT: Program payment data is NOT cached to ensure real-time period information
     * Payment periods can change availability and dates dynamically
     * 
     * By default, only returns active payments for participants.
     * Admin interfaces can pass include_inactive=true to see all payments.
     */
    public function getByProgramId($programId = null)
    {
        if ($programId === null) {
            return $this->respondValidationErrors('Program ID is required');
        }

        // Set cache-prevention headers
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        try {
            // Check if admin wants to include inactive payments
            $includeInactive = $this->request->getGet('include_inactive') === 'true';
            
            // Always fetch fresh data - NO CACHING for program payment data with periods
            // By default, only show active payments to participants
            // Admin interfaces can request inactive payments via include_inactive parameter
            $activeOnly = !$includeInactive;
            $programPayments = $this->model->getByProgramId($programId, $activeOnly, false);

            if (!$programPayments) {
                return $this->respondNotFound('Program payments not found for this program ID');
            }

            $logSuffix = $includeInactive ? ' (including inactive)' : ' (active only)';
            log_message('info', "Program payments accessed for program {$programId}, Count: " . count($programPayments) . $logSuffix);

            return $this->respondSuccess($programPayments, self::HTTP_OK, 'Program payments retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error retrieving program payments: ' . $e->getMessage());
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }
    /**
     * Get program payment by ID (with current period data)
     * GET /api/program-payments/id/{id}
     * 
     * IMPORTANT: Program payment data is NOT cached to ensure real-time period information
     */
    public function getById($id = null)
    {
        if ($id === null) {
            return $this->respondValidationErrors('ID is required');
        }

        // Set cache-prevention headers
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        try {
            // Always fetch fresh data - NO CACHING for program payment details
            $programPayment = $this->model->find($id);

            if (!$programPayment) {
                return $this->respondNotFound('Program payment not found');
            }

            // Enhance payment with current period information
            $this->model->enhancePaymentWithCurrentPeriod($programPayment);

            return $this->respondSuccess($programPayment, self::HTTP_OK, 'Program payment retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error retrieving program payment by ID: ' . $e->getMessage());
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

}
