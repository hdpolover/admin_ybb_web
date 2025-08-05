<?php

namespace App\Controllers;

// payment method model
use App\Models\PaymentMethodModel;
use App\Models\ProgramModel;
use App\Models\WebSettingModel;
use App\Traits\Cacheable;

class PaymentMethods extends BaseController
{
    use Cacheable;
    
    protected $paymentMethodModel;
    protected $programModel;
    protected $webSettingModel;

    public function __construct()
    {
        // Load models
        $this->paymentMethodModel = new PaymentMethodModel();
        $this->programModel = new ProgramModel();
        $this->webSettingModel = new WebSettingModel();
    }

    public function index()
    {
        // Get current program ID from session
        $programId = session('current_program');

        // Get all payment methods for the current program
        $paymentMethods = $this->paymentMethodModel->getActiveByProgramId($programId);


        $program = $this->programModel->find($programId);
        $webSettings = $this->webSettingModel->getSettingByProgramId($program ? $program->program_category_id : 0);

        $data = [
            'title' => 'Payment Methods',
            'paymentMethods' => $paymentMethods,
            'webSettings' => $webSettings
        ];

        return view('master-data/payment-methods/index', $data);
    }

    /**
     * View a single payment method
     * 
     * @param int $id Payment method ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function view($id = null)
    {
        if (!$id) {
            return redirect()->to('/master-data/payment-methods')->with('error', 'Payment method ID is required');
        }

        // Find the payment method
        $paymentMethod = $this->paymentMethodModel->getPaymentMethodById($id);

        // Check if payment method exists
        if (!$paymentMethod) {
            return redirect()->to('/master-data/payment-methods')->with('error', 'Payment method not found');
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Check if payment method belongs to the current program
        if ($paymentMethod->program_id != $programId) {
            return redirect()->to('/master-data/payment-methods')->with('error', 'You do not have access to this payment method');
        }

        $data = [
            'title' => 'View Payment Method',
            'paymentMethod' => $paymentMethod
        ];

        return view('master-data/payment-methods/view', $data);
    }

    /**
     * Create a new payment method  
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function create()
    {
        // Validate form data
        $rules = [
            'name' => 'required|max_length[255]',
            'description' => 'permit_empty',
            'type' => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/master-data/payment-methods')
                ->with('error', 'Failed to create payment method: ' . implode(', ', $this->validator->getErrors()));
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Prepare data
        $data = [
            'program_id' => $programId,
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'type' => $this->request->getPost('type') ?: 'manual',
            'is_active' => $this->request->getPost('is_active') ?: 1,
            'is_deleted' => 0
        ];

        // Handle image upload if available
        $imageFile = $this->request->getFile('payment_image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            // Prepare file data for upload_file_to_storage
            $fileData = [
                'name' => $imageFile->getName(),
                'tmp_name' => $imageFile->getTempName(),
                'type' => $imageFile->getClientMimeType(),
                'size' => $imageFile->getSize(),
                'error' => 0
            ];

            // Define allowed image types
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            // Upload to storage server
            $uploadResult = upload_file_to_storage(
                $fileData,
                'payment-methods/' . $programId,
                'payment_' . time() . '.' . $imageFile->getExtension(),
                $allowedTypes
            );

            if ($uploadResult['status']) {
                $data['img_url'] = $uploadResult['url'];
            } else {
                log_message('error', 'Payment method image upload failed: ' . ($uploadResult['message'] ?? 'Unknown error'));
            }
        } else if ($this->request->getPost('img_url')) {
            // If URL is provided directly
            $data['img_url'] = $this->request->getPost('img_url');
        }

        // Create the payment method
        if ($this->paymentMethodModel->insert($data)) {
            // Invalidate program cache after successful payment method creation
            $this->invalidateProgramCache($programId);
            
            return redirect()->to('/master-data/payment-methods')
                ->with('success', 'Payment method created successfully');
        } else {
            return redirect()->to('/master-data/payment-methods')
                ->with('error', 'Failed to create payment method: ' . implode(', ', $this->paymentMethodModel->errors()));
        }
    }


    /**
     * Update a payment method
     * 
     * @param int $id Payment method ID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update($id = null)
    {
        if (!$id) {
            return redirect()->to('/master-data/payment-methods')->with('error', 'Payment method ID is required');
        }

        // Find the payment method
        $paymentMethod = $this->paymentMethodModel->find($id);

        // Check if payment method exists
        if (!$paymentMethod) {
            return redirect()->to('/master-data/payment-methods')->with('error', 'Payment method not found');
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Check if payment method belongs to the current program
        if ($paymentMethod->program_id != $programId) {
            return redirect()->to('/master-data/payment-methods')->with('error', 'You do not have access to this payment method');
        }
        // Validate form data
        $rules = [
            'name' => 'required|max_length[255]',
            'description' => 'permit_empty',
            'type' => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/master-data/payment-methods')
                ->with('error', 'Failed to update payment method: ' . implode(', ', $this->validator->getErrors()));
        }

        // Prepare data
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'program_id' => $programId,
            'type' => $this->request->getPost('type') ?: 'manual',
            'is_active' => $this->request->getPost('is_active') ?: 1,
        ];

        // Handle image upload if available
        $imageFile = $this->request->getFile('payment_image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            // Prepare file data for upload_file_to_storage
            $fileData = [
                'name' => $imageFile->getName(),
                'tmp_name' => $imageFile->getTempName(),
                'type' => $imageFile->getClientMimeType(),
                'size' => $imageFile->getSize(),
                'error' => 0
            ];

            // Define allowed image types
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            // Upload to storage server
            $uploadResult = upload_file_to_storage(
                $fileData,
                'payment-methods/' . $programId,
                'payment_' . time() . '.' . $imageFile->getExtension(),
                $allowedTypes
            );

            if ($uploadResult['status']) {
                $data['img_url'] = $uploadResult['url'];
            } else {
                log_message('error', 'Payment method image upload failed: ' . ($uploadResult['message'] ?? 'Unknown error'));
            }
        } else if ($this->request->getPost('img_url')) {
            // If URL is provided directly
            $data['img_url'] = $this->request->getPost('img_url');
        }

        // Update the payment method
        if ($this->paymentMethodModel->update($id, $data)) {
            // Invalidate program cache after successful payment method update
            $this->invalidateProgramCache($paymentMethod->program_id);
            
            return redirect()->to('/master-data/payment-methods')
                ->with('success', 'Payment method updated successfully');
        } else {
            return redirect()->to('/master-data/payment-methods')
                ->with('error', 'Failed to update payment method');
        }
    }

    /**
     * Delete a payment method (soft delete)
     * 
     * @param int $id Payment method ID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete($id = null)
    {
        if (!$id) {
            return redirect()->to('/master-data/payment-methods')->with('error', 'Payment method ID is required');
        }

        // Find the payment method
        $paymentMethod = $this->paymentMethodModel->find($id);

        // Check if payment method exists
        if (!$paymentMethod) {
            return redirect()->to('/master-data/payment-methods')->with('error', 'Payment method not found');
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Check if payment method belongs to the current program
        if ($paymentMethod->program_id != $programId) {
            return redirect()->to('/master-data/payment-methods')->with('error', 'You do not have access to this payment method');
        }

        // Soft delete the payment method
        if ($this->paymentMethodModel->update($id, ['is_deleted' => 1, 'is_active' => 0])) {
            // Invalidate program cache after successful payment method deletion
            $this->invalidateProgramCache($paymentMethod->program_id);
            
            return redirect()->to('/master-data/payment-methods')
                ->with('success', 'Payment method deleted successfully');
        } else {
            return redirect()->to('/master-data/payment-methods')
                ->with('error', 'Failed to delete payment method');
        }
    }

    /**
     * Get payment method by ID (AJAX)
     * 
     * @param int $id Payment method ID
     * @return \CodeIgniter\HTTP\Response
     */
    public function getPaymentMethod($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Payment method ID is required']);
        }

        // Find the payment method
        $paymentMethod = $this->paymentMethodModel->find($id);

        // Check if payment method exists
        if (!$paymentMethod) {
            return $this->response->setJSON(['success' => false, 'message' => 'Payment method not found']);
        }

        // Get current program ID from session
        $programId = session('current_program');

        // Check if payment method belongs to the current program
        if ($paymentMethod->program_id != $programId) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have access to this payment method']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $paymentMethod]);
    }
}
