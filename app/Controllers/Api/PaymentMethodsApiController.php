<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\PaymentMethodModel;

class PaymentMethodsApiController extends ApiBaseController
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
        $this->model = new PaymentMethodModel();
    }

    /**
     * Get All Payment Methods
     * GET /api/payment-methods
     */
    public function index()
    {
        $paymentMethods = $this->model->findAll();
        return $this->respondSuccess($paymentMethods, self::HTTP_OK, 'Payment methods retrieved successfully');
    }

    /**
     * Get Single Payment Method
     * GET /api/payment-methods/{id}
     */
    public function show($id = null)
    {
        $paymentMethod = $this->model->find($id);
        
        if (!$paymentMethod) {
            return $this->respondNotFound('Payment method not found');
        }
        
        return $this->respondSuccess($paymentMethod, self::HTTP_OK, 'Payment method retrieved successfully');
    }

    /**
     * Get payment methods by program ID
     * GET /api/payment-methods/program/{programId}
     */
    public function getByProgramId($programId = null)
    {
        if ($programId === null) {
            return $this->respondValidationErrors('Program ID is required');
        }

        $paymentMethods = $this->model->getPaymentMethodsByProgramId($programId);

        if (empty($paymentMethods)) {
            return $this->respondNotFound('No payment methods found for this program');
        }

        return $this->respondSuccess($paymentMethods, self::HTTP_OK, 'Payment methods retrieved successfully');
    }

}