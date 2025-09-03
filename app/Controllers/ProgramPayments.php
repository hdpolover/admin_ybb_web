<?php

namespace App\Controllers;

use App\Models\ProgramPaymentModel;
use App\Models\ProgramModel;
use App\Models\WebSettingModel;
use App\Traits\Cacheable;

class ProgramPayments extends AdminBaseController
{
    use Cacheable;
    
    protected $programPaymentModel;
    protected $programModel;
    protected $webSettingModel;
    
    public function __construct()
    {
        $this->programPaymentModel = new ProgramPaymentModel();
        $this->programModel = new ProgramModel();
        $this->webSettingModel = new WebSettingModel();
    }

    public function index()
    {
        // Get current program ID from session
        $programId = session('current_program');
        // $program data
        $program = $this->programModel->find($programId);
        
        // Get program payments for the current program
        $programPayments = $this->programPaymentModel->getByProgramId($programId, false);
        
        $webSettings = $this->webSettingModel->getSettingByProgramId($program->program_category_id);
        
        $data = [
            'title' => 'Program Payments',
            'programPayments' => $programPayments,
            'webSettings' => $webSettings,
        ];
        return view('master-data/program-payments/index', $data);
    }
    
    /**
     * View a single payment option
     * 
     * @param int $id Payment option ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function view($id = null)
    {
        if (!$id) {
            return redirect()->to('/master-data/program-payments')->with('error', 'Payment option ID is required');
        }
        
        // Find the payment option
        $paymentOption = $this->programPaymentModel->find($id);
        
        // Check if payment option exists
        if (!$paymentOption) {
            return redirect()->to('/master-data/program-payments')->with('error', 'Payment option not found');
        }
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Check if payment option belongs to the current program
        if ($paymentOption->program_id != $programId) {
            return redirect()->to('/master-data/program-payments')->with('error', 'You do not have access to this payment option');
        }
        
        $data = [
            'title' => 'View Payment Option',
            'paymentOption' => $paymentOption
        ];
        
        return view('master-data/program-payments/view', $data);
    }
      /**
     * Create a new payment option
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function create()
    {
        // Validate form data
        $rules = [
            'name' => 'required|max_length[255]',
            'description' => 'permit_empty|max_length[255]',
            'usd_amount' => 'permit_empty|numeric',
            'category' => 'permit_empty|max_length[50]',
            'type' => 'permit_empty|max_length[50]',
            'start_date' => 'permit_empty|valid_date[Y-m-d]',
            'end_date' => 'permit_empty|valid_date[Y-m-d]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->to('/master-data/program-payments')
                ->with('error', 'Failed to create payment option: ' . implode(', ', $this->validator->getErrors()));
        }
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Prepare data
        $data = [
            'program_id' => $programId,
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'usd_amount' => $this->request->getPost('usd_amount'),
            'category' => $this->request->getPost('category') ?: 'registration',
            'type' => $this->request->getPost('type') ?: 'all',
            'is_active' => $this->request->getPost('is_active') ?: 1,
            'is_deleted' => 0
        ];
        
        // Add dates if provided
        $startDate = $this->request->getPost('start_date');
        if ($startDate) {
            $data['start_date'] = $startDate;
        }
        
        $endDate = $this->request->getPost('end_date');
        if ($endDate) {
            $data['end_date'] = $endDate;
        }
        
        // Get the highest order number and add 1
        $highestOrder = $this->programPaymentModel->where('program_id', $programId)
            ->selectMax('order_number')
            ->first();
            
        $data['order_number'] = ($highestOrder && $highestOrder->order_number) ? $highestOrder->order_number + 1 : 1;
        
        // Create the payment option
        if ($this->programPaymentModel->insert($data)) {
            // Invalidate program and landing cache after successful payment option creation
            $this->invalidateProgramCache($programId);
            $this->invalidateLandingCache();
            
            return redirect()->to('/master-data/program-payments')
                ->with('success', 'Payment option created successfully');
        } else {
            return redirect()->to('/master-data/program-payments')
                ->with('error', 'Failed to create payment option');
        }
    }
    
    /**
     * Update a payment option
     * 
     * @param int $id Payment option ID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update($id = null)
    {
        if (!$id) {
            return redirect()->to('/master-data/program-payments')->with('error', 'Payment option ID is required');
        }
        
        // Find the payment option
        $paymentOption = $this->programPaymentModel->find($id);
        
        // Check if payment option exists
        if (!$paymentOption) {
            return redirect()->to('/master-data/program-payments')->with('error', 'Payment option not found');
        }
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Check if payment option belongs to the current program
        if ($paymentOption->program_id != $programId) {
            return redirect()->to('/master-data/program-payments')->with('error', 'You do not have access to this payment option');
        }
          // Validate form data
        $rules = [
            'name' => 'required|max_length[255]',
            'description' => 'permit_empty|max_length[255]',
            'usd_amount' => 'permit_empty|numeric',
            'category' => 'permit_empty|max_length[50]',
            'type' => 'permit_empty|max_length[50]',
            'start_date' => 'permit_empty|valid_date[Y-m-d]',
            'end_date' => 'permit_empty|valid_date[Y-m-d]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->to('/master-data/program-payments')
                ->with('error', 'Failed to update payment option: ' . implode(', ', $this->validator->getErrors()));
        }
        
        // Prepare data
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'usd_amount' => $this->request->getPost('usd_amount'),
            'category' => $this->request->getPost('category'),
            'type' => $this->request->getPost('type'),
            'is_active' => $this->request->getPost('is_active') ?: 0
        ];
        
        // Add dates if provided
        $startDate = $this->request->getPost('start_date');
        if ($startDate) {
            $data['start_date'] = $startDate;
        }
        
        $endDate = $this->request->getPost('end_date');
        if ($endDate) {
            $data['end_date'] = $endDate;
        }
        
        // Update the payment option
        if ($this->programPaymentModel->update($id, $data)) {
            // Invalidate program and landing cache after successful payment option update
            $this->invalidateProgramCache($paymentOption->program_id);
            $this->invalidateLandingCache();
            
            return redirect()->to('/master-data/program-payments')
                ->with('success', 'Payment option updated successfully');
        } else {
            return redirect()->to('/master-data/program-payments')
                ->with('error', 'Failed to update payment option');
        }
    }
    
    /**
     * Delete a payment option (soft delete)
     * 
     * @param int $id Payment option ID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete($id = null)
    {
        if (!$id) {
            return redirect()->to('/master-data/program-payments')->with('error', 'Payment option ID is required');
        }
        
        // Find the payment option
        $paymentOption = $this->programPaymentModel->find($id);
        
        // Check if payment option exists
        if (!$paymentOption) {
            return redirect()->to('/master-data/program-payments')->with('error', 'Payment option not found');
        }
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Check if payment option belongs to the current program
        if ($paymentOption->program_id != $programId) {
            return redirect()->to('/master-data/program-payments')->with('error', 'You do not have access to this payment option');
        }
        
        // Soft delete the payment option
        if ($this->programPaymentModel->update($id, ['is_deleted' => 1])) {
            // Invalidate program and landing cache after successful payment option deletion
            $this->invalidateProgramCache($programId);
            $this->invalidateLandingCache();
            
            return redirect()->to('/master-data/program-payments')
                ->with('success', 'Payment option deleted successfully');
        } else {
            return redirect()->to('/master-data/program-payments')
                ->with('error', 'Failed to delete payment option');
        }
    }
    
    /**
     * Get payment option by ID (AJAX)
     * 
     * @param int $id Payment option ID
     * @return \CodeIgniter\HTTP\Response
     */
    public function getPaymentOption($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Payment option ID is required']);
        }
        
        // Find the payment option
        $paymentOption = $this->programPaymentModel->find($id);
        
        // Check if payment option exists
        if (!$paymentOption) {
            return $this->response->setJSON(['success' => false, 'message' => 'Payment option not found']);
        }
        
        // Get current program ID from session
        $programId = session('current_program');
        
        // Check if payment option belongs to the current program
        if ($paymentOption->program_id != $programId) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have access to this payment option']);
        }
        
        return $this->response->setJSON(['success' => true, 'data' => $paymentOption]);
    }
}