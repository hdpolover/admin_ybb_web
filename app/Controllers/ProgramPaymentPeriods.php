<?php

namespace App\Controllers;

use App\Models\ProgramPaymentModel;
use App\Models\ProgramPaymentPeriodModel;
use App\Traits\Cacheable;

class ProgramPaymentPeriods extends AdminBaseController
{
    use Cacheable;
    
    protected $programPaymentModel;
    protected $periodModel;
    
    public function __construct()
    {
        $this->programPaymentModel = new ProgramPaymentModel();
        $this->periodModel = new ProgramPaymentPeriodModel();
    }

    /**
     * View periods for a specific payment
     * 
     * @param int $paymentId Payment ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function index($paymentId = null)
    {
        if (!$paymentId) {
            return redirect()->to('/master-data/program-payments')->with('error', 'Payment ID is required');
        }
        
        // Find the payment
        $payment = $this->programPaymentModel->find($paymentId);
        
        if (!$payment) {
            return redirect()->to('/master-data/program-payments')->with('error', 'Payment not found');
        }
        
        // Check if payment belongs to the current program
        $programId = session('current_program');
        if ($payment->program_id != $programId) {
            return redirect()->to('/master-data/program-payments')->with('error', 'You do not have access to this payment');
        }
        
        // Get all periods for this payment
        $periods = $this->programPaymentModel->getPaymentPeriods($paymentId);
        
        $data = [
            'title' => 'Payment Periods - ' . $payment->name,
            'payment' => $payment,
            'periods' => $periods
        ];
        
        return view('master-data/program-payments/periods', $data);
    }
    
    /**
     * Create a new period for a payment (AJAX)
     * 
     * @param int $paymentId Payment ID
     * @return \CodeIgniter\HTTP\Response
     */
    public function create($paymentId = null)
    {
        if (!$paymentId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment ID is required'
            ]);
        }
        
        // Find the payment and validate access
        $payment = $this->programPaymentModel->find($paymentId);
        
        if (!$payment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment not found'
            ]);
        }
        
        $programId = session('current_program');
        if ($payment->program_id != $programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have access to this payment'
            ]);
        }
        
        // Validate form data
        $rules = [
            'name' => 'required|max_length[255]',
            'description' => 'permit_empty|max_length[500]',
            'start_date' => 'required|valid_date[Y-m-d H:i:s]',
            'end_date' => 'required|valid_date[Y-m-d H:i:s]'
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $this->validator->getErrors())
            ]);
        }
        
        // Prepare period data
        $periodData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date')
        ];
        
        // Add the period
        $result = $this->programPaymentModel->addPaymentPeriod($paymentId, $periodData);
        
        if ($result['success']) {
            // Invalidate relevant caches - program payment periods affect real-time status
            $this->invalidateProgramCache($programId);
            $this->invalidateLandingCache();
            
            // Clear payment-related caches that might be affected by period changes
            $this->clearProgramPaymentCaches($programId, $paymentId);
            
            log_message('info', "Period added for payment {$paymentId}, all related caches cleared");
        }
        
        return $this->response->setJSON($result);
    }
    
    /**
     * Update an existing period (AJAX)
     * 
     * @param int $periodId Period ID
     * @return \CodeIgniter\HTTP\Response
     */
    public function update($periodId = null)
    {
        if (!$periodId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Period ID is required'
            ]);
        }
        
        // Find the period and validate access
        $period = $this->periodModel->find($periodId);
        
        if (!$period) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Period not found'
            ]);
        }
        
        // Get the payment and validate access
        $payment = $this->programPaymentModel->find($period->payment_id);
        $programId = session('current_program');
        
        if (!$payment || $payment->program_id != $programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have access to this period'
            ]);
        }
        
        // Validate form data
        $rules = [
            'name' => 'required|max_length[255]',
            'description' => 'permit_empty|max_length[500]',
            'start_date' => 'required|valid_date[Y-m-d H:i:s]',
            'end_date' => 'required|valid_date[Y-m-d H:i:s]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $this->validator->getErrors())
            ]);
        }
        
        // Prepare period data
        $periodData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'is_active' => $this->request->getPost('is_active') ?? 1
        ];
        
        // Update the period
        $result = $this->programPaymentModel->updatePaymentPeriod($periodId, $periodData);
        
        if ($result['success']) {
            // Invalidate relevant caches - program payment periods affect real-time status
            $this->invalidateProgramCache($programId);
            $this->invalidateLandingCache();
            
            // Clear payment-related caches that might be affected by period changes
            $this->clearProgramPaymentCaches($programId, $period->payment_id);
            
            log_message('info', "Period updated for period {$periodId}, all related caches cleared");
        }
        
        return $this->response->setJSON($result);
    }
    
    /**
     * Delete a period (AJAX)
     * 
     * @param int $periodId Period ID
     * @return \CodeIgniter\HTTP\Response
     */
    public function delete($periodId = null)
    {
        if (!$periodId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Period ID is required'
            ]);
        }
        
        // Find the period and validate access
        $period = $this->periodModel->find($periodId);
        
        if (!$period) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Period not found'
            ]);
        }
        
        // Get the payment and validate access
        $payment = $this->programPaymentModel->find($period->payment_id);
        $programId = session('current_program');
        
        if (!$payment || $payment->program_id != $programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have access to this period'
            ]);
        }
        
        // Delete the period
        $result = $this->programPaymentModel->deletePaymentPeriod($periodId);
        
        if ($result['success']) {
            // Invalidate relevant caches - program payment periods affect real-time status
            $this->invalidateProgramCache($programId);
            $this->invalidateLandingCache();
            
            // Clear payment-related caches that might be affected by period changes
            $this->clearProgramPaymentCaches($programId, $period->payment_id);
            
            log_message('info', "Period deleted for period {$periodId}, all related caches cleared");
        }
        
        return $this->response->setJSON($result);
    }
    
    /**
     * Get period details (AJAX)
     * 
     * @param int $periodId Period ID
     * @return \CodeIgniter\HTTP\Response
     */
    public function getPeriod($periodId = null)
    {
        if (!$periodId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Period ID is required'
            ]);
        }
        
        // Find the period and validate access
        $period = $this->periodModel->find($periodId);
        
        if (!$period) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Period not found'
            ]);
        }
        
        // Get the payment and validate access
        $payment = $this->programPaymentModel->find($period->payment_id);
        $programId = session('current_program');
        
        if (!$payment || $payment->program_id != $programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have access to this period'
            ]);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $period
        ]);
    }
    
    /**
     * Clear program payment related caches when periods are modified
     * This ensures that payment status and availability reflect real-time period data
     * 
     * @param int $programId Program ID
     * @param int $paymentId Payment ID
     * @return void
     */
    private function clearProgramPaymentCaches($programId, $paymentId)
    {
        try {
            $cache = \Config\Services::cache();
            
            // Clear specific cache keys that might contain stale payment data
            $cacheKeys = [
                "program_payments_{$programId}",
                "payment_{$paymentId}",
                "payment_stats_{$programId}",
                "payment_stats_currency_{$programId}",
                "pending_manual_payments_{$programId}",
                "payments_with_details_{$programId}",
                "dashboard_summary_{$programId}",
                "registration_payment_flags_{$programId}",
                "available_registration_payment_{$programId}",
                "participants_export_{$programId}",
                "total_countries_{$programId}",
                "countries_data_{$programId}"
            ];
            
            foreach ($cacheKeys as $key) {
                $cache->delete($key);
            }
            
            // Clear Redis cache if available (more comprehensive for API caches)
            if (class_exists('\App\Services\RedisCacheService')) {
                $redisCache = new \App\Services\RedisCacheService();
                
                if ($redisCache->isCacheAvailable()) {
                    // Clear program-specific API caches
                    $redisCache->invalidateProgramCache($programId);
                    
                    log_message('info', "Cleared Redis cache for program {$programId}");
                }
            }
            
            log_message('info', "Successfully cleared all payment-related caches for program {$programId}, payment {$paymentId}");
            
        } catch (\Exception $e) {
            // Don't throw exception - cache clearing failure shouldn't break the period operation
            log_message('error', "Error clearing payment caches: " . $e->getMessage());
        }
    }
}