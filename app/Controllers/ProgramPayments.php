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
        
        // Clear cache if requested
        if ($this->request->getGet('clear_cache')) {
            cache()->deleteMatching('payment_*');
            cache()->deleteMatching('program_' . $programId . '_*');
            log_message('info', "Payment cache cleared manually for program {$programId}");
        }
        
        // $program data
        $program = $this->programModel->find($programId);
        
        // Get program payments for the current program
        $programPayments = $this->programPaymentModel->getByProgramId($programId, false);
        
        // Debug logging to see if periods are enhanced
        log_message('debug', "Retrieved " . count($programPayments) . " program payments for program {$programId}");
        foreach ($programPayments as $payment) {
            $hasStartDate = isset($payment->start_date) && !empty($payment->start_date);
            $hasPeriodName = isset($payment->current_period_name) && !empty($payment->current_period_name);
            log_message('debug', "Payment {$payment->id} ({$payment->name}): start_date=" . ($hasStartDate ? $payment->start_date : 'NULL') . ", period_name=" . ($hasPeriodName ? $payment->current_period_name : 'NULL'));
        }
        
        $webSettings = $this->webSettingModel->getSettingByProgramCategoryId($program->program_category_id);
        
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
            'is_deleted' => 0,
            // Set default dates - will be managed through periods
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => date('Y-m-d H:i:s', strtotime('+1 year'))
        ];
        
        // Get the highest order number and add 1
        $highestOrder = $this->programPaymentModel->where('program_id', $programId)
            ->selectMax('order_number')
            ->first();
            
        $data['order_number'] = ($highestOrder && $highestOrder->order_number) ? $highestOrder->order_number + 1 : 1;
        
        // Create the payment option
        $paymentId = $this->programPaymentModel->insert($data);
        if ($paymentId) {
            // Create a default period for this payment
            $periodData = [
                'name' => 'Main Period',
                'description' => 'Default availability period - please update as needed',
                'start_date' => date('Y-m-d H:i:s'),
                'end_date' => date('Y-m-d H:i:s', strtotime('+6 months'))
            ];
            
            $this->programPaymentModel->addPaymentPeriod($paymentId, $periodData);
            
            // Invalidate program and landing cache after successful payment option creation
            $this->invalidateProgramCache($programId);
            $this->invalidateLandingCache();
            
            return redirect()->to('/master-data/program-payments')
                ->with('success', 'Payment option created successfully! Please use "Manage Periods" to set availability dates.');
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
            'is_active' => 'permit_empty|in_list[0,1]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->to('/master-data/program-payments')
                ->with('error', 'Failed to update payment option: ' . implode(', ', $this->validator->getErrors()));
        }
        
        // Prepare data (dates are now managed through periods)
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'usd_amount' => $this->request->getPost('usd_amount'),
            'category' => $this->request->getPost('category'),
            'type' => $this->request->getPost('type'),
            'is_active' => $this->request->getPost('is_active') ?: 0
        ];
        
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
        
        // Get periods for this payment option
        $periodModel = new \App\Models\ProgramPaymentPeriodModel();
        $periods = $periodModel->getByPaymentId($id, false, false); // Get all periods (active and inactive)
        
        // Get current active period
        $currentPeriod = $periodModel->getCurrentActivePeriod($id);
        
        // Get next upcoming period
        $upcomingPeriod = $periodModel->getNextUpcomingPeriod($id);
        
        // Prepare response data
        $responseData = [
            'payment' => $paymentOption,
            'periods' => $periods,
            'current_period' => $currentPeriod,
            'upcoming_period' => $upcomingPeriod,
            'total_periods' => count($periods)
        ];
        
        return $this->response->setJSON(['success' => true, 'data' => $responseData]);
    }

    /**
     * Get current server time with timezone information
     * This helps frontend synchronize with server time for accurate period status
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function getCurrentServerTime()
    {
        helper('date');
        $appTimezone = app_timezone();
        
        // Create DateTime in application timezone
        $timezone = new \DateTimeZone($appTimezone);
        $currentDateTime = new \DateTime('now', $timezone);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'timestamp' => $currentDateTime->getTimestamp(),
                'datetime' => $currentDateTime->format('Y-m-d H:i:s'),
                'iso_format' => $currentDateTime->format('c'),
                'timezone' => $appTimezone,
                'timezone_offset' => $currentDateTime->format('P')
            ]
        ]);
    }
}