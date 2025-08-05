<?php

namespace App\Models;

use CodeIgniter\Model;


class PaymentModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        helper(['cache']);
    }
    
    protected $table      = 'payments';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType     = 'object';

    //`id`, `participant_id`, `program_payment_id`, `payment_method_id`, `status`, `proof_url`, `account_name`, `amount`, `currency`, `source_name`, `is_active`, `is_deleted`, `created_at`, `updated_at
    protected $allowedFields = [
        'participant_id',
        'transaction_code',
        'order_id',
        'payment_date',
        'notes',
        'rejection_reason',
        'program_payment_id',
        'payment_method_id',
        'payment_url',
        'status',
        'proof_url',
        'account_name',
        'amount',
        'usd_amount',
        'currency',
        'source_name',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    // Validation rules
    protected $validationRules = [
        'participant_id' => 'required|integer',
        'program_payment_id' => 'required|integer',
        'payment_method_id' => 'required|integer',
        'amount' => 'required|numeric|greater_than[0]'
    ];
    
    protected $validationMessages = [
        'amount' => [
            'required' => 'Payment amount is required',
            'numeric' => 'Payment amount must be a valid number',
            'greater_than' => 'Payment amount must be greater than zero'
        ]
    ];

    /**
     * Get payments with participant details
     * 
     * @param int $programId Program ID
     * @return array
     */
    public function getPaymentsWithDetails($programId)
    {
        $cacheKey = "payments_with_details_{$programId}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $payments = $cache->get($cacheKey);
        
        if ($payments !== null) {
            return $payments;
        }
        
        // Cache miss - query database
        $payments = $this->select('
                payments.*, 
                participants.full_name as participant_name,
                users.email as participant_email,
                participants.program_id
            ')
            ->join('participants', 'participants.id = payments.participant_id')
            ->join('users', 'users.id = participants.user_id')
            ->where('participants.program_id', $programId)
            ->orderBy('payment_date', 'DESC')
            ->findAll();
            
        // Cache for 30 minutes (1800 seconds)
        $cache->save($cacheKey, $payments, 1800);
            
        return $payments;
    }

    /**
     * Get payment by ID with participant details
     * 
     * @param int $id Payment ID
     * @return object
     */
    public function getPaymentById($id)
    {
        return $this->select('
                payments.*, 
                participants.full_name as participant_name, 
                users.email as participant_email,
                participants.program_id
            ')
            ->join('participants', 'participants.id = payments.participant_id')
            ->join('users', 'users.id = participants.user_id')
            ->where('payments.id', $id)
            ->first();
    }

    /**
     * Get payment statistics
     * 
     * @param int $programId Program ID
     * @return object
     */
    public function getPaymentStats($programId)
    {
        // Create a cache key for payment stats
        $cacheKey = "payment_stats_{$programId}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $stats = $cache->get($cacheKey);
        
        if ($stats !== null) {
            // Return cached stats
            return $stats;
        }
        
        // Cache miss - calculate payment stats from database
        $stats = new \stdClass();

        // Total amount received
        $query = $this->db->query("
            SELECT SUM(payments.amount) as total_amount 
            FROM payments 
            JOIN participants ON participants.id = payments.participant_id
            WHERE participants.program_id = ? AND payments.status = 2
        ", [$programId]);
        $stats->total_amount = $query->getRow()->total_amount ?? 0;

        // Count of payments by status
        $query = $this->db->query("
            SELECT payments.status, COUNT(*) as count 
            FROM payments 
            JOIN participants ON participants.id = payments.participant_id
            WHERE participants.program_id = ?
            GROUP BY payments.status
        ", [$programId]);

        $stats->status_counts = [
            0 => 0, // created
            1 => 0, // pending
            2 => 0, // success
            3 => 0, // cancelled
            4 => 0, // rejected
        ];

        foreach ($query->getResult() as $row) {
            $stats->status_counts[$row->status] = $row->count;
        }

        // Payment methods distribution
        $query = $this->db->query("
            SELECT payments.payment_method_id, COUNT(*) as count 
            FROM payments 
            JOIN participants ON participants.id = payments.participant_id
            WHERE participants.program_id = ?
            GROUP BY payments.payment_method_id
        ", [$programId]);
        $stats->payment_methods = $query->getResult();
        
        // Cache for 1 hour (3600 seconds)
        $cache->save($cacheKey, $stats, 3600);

        return $stats;
    }

    /**
     * Get payment statistics by currency
     * 
     * @param int $programId Program ID
     * @return object
     */
    public function getPaymentStatsByCurrency($programId)
    {
        // Create a cache key for currency stats
        $cacheKey = "payment_stats_currency_{$programId}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $stats = $cache->get($cacheKey);
        
        if ($stats !== null) {
            // Return cached stats
            return $stats;
        }
        
        // Cache miss - calculate from database
        $stats = new \stdClass();

        // Total amount received in IDR
        $query = $this->db->query("
            SELECT SUM(payments.amount) as total_amount 
            FROM payments 
            JOIN participants ON participants.id = payments.participant_id
            WHERE participants.program_id = ? 
            AND payments.status = 2
            AND (payments.currency = 'IDR' OR payments.currency IS NULL)
        ", [$programId]);
        $stats->total_idr = $query->getRow()->total_amount ?? 0;

        // Total amount received in USD
        $query = $this->db->query("
            SELECT SUM(payments.amount) as total_amount 
            FROM payments 
            JOIN participants ON participants.id = payments.participant_id
            WHERE participants.program_id = ? 
            AND payments.status = 2
            AND payments.currency = 'USD'
        ", [$programId]);
        $stats->total_usd = $query->getRow()->total_amount ?? 0;
        
        // Cache for 2 hours (7200 seconds)
        $cache->save($cacheKey, $stats, 7200);

        return $stats;
    }

    /**
     * Get pending manual payments that need admin review
     * 
     * @param int $programId Program ID
     * @return array
     */
    public function getPendingManualPayments($programId)
    {
        // Short cache time since this is critical payment info that admins need to see quickly
        $cacheKey = "pending_manual_payments_{$programId}";
        
        // Try to get from cache with short TTL since this needs to be updated frequently
        $cache = \Config\Services::cache();
        $pendingPayments = $cache->get($cacheKey);
        
        if ($pendingPayments !== null) {
            return $pendingPayments;
        }
        
        // Cache miss - get from database
        $pendingPayments = $this->select('
                payments.*, 
                participants.full_name as participant_name,
                users.email as participant_email,
                participants.program_id
            ')
            ->join('participants', 'participants.id = payments.participant_id')
            ->join('users', 'users.id = participants.user_id')
            ->where('participants.program_id', $programId)
            ->where('payments.payment_method_id', 2) // Manual payment
            ->where('payments.status', 1) // Pending
            ->where('payments.payment_proof IS NOT NULL')
            ->orderBy('payment_date', 'DESC')
            ->findAll();
            
        // Cache for a shorter time (15 minutes) since pending payments are time-sensitive
        $cache->save($cacheKey, $pendingPayments, 900);
            
        return $pendingPayments;
    }

    /**
     * Update payment status
     * 
     * @param int $id Payment ID
     * @param int $status New status
     * @param string $notes Optional notes for the update
     * @return bool
     */
    public function updatePaymentStatus($id, $status, $notes = '', $rejectionReason = '')
    {
        // Validate status
        if (!in_array($status, [0, 1, 2, 3, 4])) {
            throw new \InvalidArgumentException('Invalid payment status');
        }

        // Validate rejection reason for rejected status
        if ($status == 4 && empty($rejectionReason)) {
            // For rejected status, we'll use a default reason if none provided
            $rejectionReason = 'No specific reason provided';
        }

        // Get payment data to find associated participant for cache invalidation
        $payment = $this->find($id);
        $participantId = $payment->participant_id ?? null;

        // Prepare data for update
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // set rejection reason if status is rejected
        if ($status == 4) {
            $data['rejection_reason'] = $rejectionReason;
        }

        if (!empty($notes)) {
            $existingNotes = $payment->notes ?? '';
            $combinedNotes = $existingNotes . "\n\n" . date('Y-m-d H:i:s') . " - Status updated: " . $notes;
            $data['notes'] = trim($combinedNotes);
        }
        
        // Update the payment
        $result = $this->update($id, $data);
        
        // Invalidate related caches if update was successful
        if ($result) {
            // Use centralized cache invalidation
            $paymentData = ['participant_id' => $participantId];
            $this->invalidatePaymentCaches($paymentData);
            
            log_message('info', "PaymentModel::updatePaymentStatus - Updated payment ID {$id} and invalidated related caches");
        }

        return $result;
    }

    /**
     * Get payments by participant ID and program payment ID
     * 
     * @param int $participantId Participant ID
     * @return object|null
     */
    public function getPaymentsByParticipantIdAndProgramPaymentId($participantId, $programPaymentId)
    {
        return $this->where('participant_id', $participantId)
            ->where('program_payment_id', $programPaymentId)
            ->where('is_deleted', 0)
            ->findAll();
    }

    /**
     * Get payments by participant ID
     * 
     * @param int $participantId Participant ID
     * @return object|null
     */
    public function getPaymentsByParticipantId($participantId)
    {
        return $this->where('participant_id', $participantId)
            ->where('is_deleted', 0)
            ->findAll();
    }

    /**
     * Check if a participant has successful payments for a specific program
     * 
     * @param int $participantId Participant ID
     * @param int $programId Program ID
     * @return bool Returns true if participant has successful payments, false otherwise
     */
    public function hasSuccessfulPayments($participantId, $programId)
    {
        // Create cache key
        $cacheKey = "has_payments_{$participantId}_{$programId}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $hasPayments = $cache->get($cacheKey);
        
        if ($hasPayments !== null) {
            return (bool)$hasPayments;
        }
        
        // Cache miss - check database
        $result = $this->select('COUNT(*) as payment_count')
            ->join('participants', 'participants.id = payments.participant_id')
            ->where('payments.participant_id', $participantId)
            ->where('participants.program_id', $programId)
            ->where('payments.status', 2) // Success status
            ->where('payments.is_deleted', 0)
            ->first();

        $hasPayments = ($result && $result->payment_count > 0);
        
        // Cache for 4 hours (14400 seconds) since payment status rarely changes once successful
        $cache->save($cacheKey, $hasPayments, 14400);
        
        return $hasPayments;
    }

    /**
     * Override insert method to invalidate cache after creating new payment
     * 
     * @param array $data
     * @return bool|int
     */
    public function insert($data = null, bool $returnID = true)
    {
        $result = parent::insert($data, $returnID);
        
        if ($result) {
            $this->invalidatePaymentCaches($data);
        }
        
        return $result;
    }

    /**
     * Override update method to invalidate cache after updating payment
     * 
     * @param mixed $id
     * @param array $data
     * @return bool
     */
    public function update($id = null, $data = null): bool
    {
        $result = parent::update($id, $data);
        
        if ($result) {
            // Get payment data for cache invalidation
            $payment = $this->find($id);
            if ($payment) {
                $paymentData = [
                    'participant_id' => $payment->participant_id
                ];
                $this->invalidatePaymentCaches($paymentData);
            }
        }
        
        return $result;
    }

    /**
     * Override delete method to invalidate cache after deleting payment
     * 
     * @param mixed $id
     * @param bool $purge
     * @return bool
     */
    public function delete($id = null, bool $purge = false)
    {
        // Get payment data before deletion for cache invalidation
        $payment = $this->find($id);
        
        $result = parent::delete($id, $purge);
        
        if ($result && $payment) {
            $paymentData = [
                'participant_id' => $payment->participant_id
            ];
            $this->invalidatePaymentCaches($paymentData);
        }
        
        return $result;
    }

    /**
     * Centralized cache invalidation for payment-related operations
     * 
     * @param array $paymentData
     * @return void
     */
    private function invalidatePaymentCaches($paymentData)
    {
        $cache = \Config\Services::cache();
        $participantId = $paymentData['participant_id'] ?? null;
        
        if ($participantId) {
            // Get program ID
            $participantModel = new \App\Models\ParticipantModel();
            $participant = $participantModel->find($participantId);
            $programId = $participant->program_id ?? null;
            
            if ($programId) {
                // Delete program-specific payment caches
                $cache->delete("payment_stats_{$programId}");
                $cache->delete("payment_stats_currency_{$programId}");
                $cache->delete("pending_manual_payments_{$programId}");
                $cache->delete("payments_with_details_{$programId}");
                
                // Delete dashboard-related caches that might include payment data
                $cache->delete("dashboard_summary_{$programId}");
                
                // Clear other dashboard caches that might be affected by payment changes
                $dashboardCachePatterns = [
                    "dashboard_registration_stats_{$programId}_day_30",
                    "dashboard_registration_stats_{$programId}_week_12", 
                    "dashboard_registration_stats_{$programId}_month_12",
                    "dashboard_gender_stats_{$programId}",
                    "dashboard_age_stats_{$programId}",
                    "dashboard_nationality_stats_{$programId}_10",
                    "dashboard_ambassador_stats_{$programId}_10"
                ];
                
                foreach ($dashboardCachePatterns as $cacheKey) {
                    $cache->delete($cacheKey);
                }
                
                // Invalidate participant-related caches that might be affected
                $cache->delete("total_countries_{$programId}");
                $cache->delete("countries_data_{$programId}");
                $cache->delete("participant_stats_{$programId}_" . date('Ymd'));
                $cache->delete("participant_stats_{$programId}_" . date('Ymd', strtotime('-1 day')));
                
                // Invalidate export caches as payment status affects export data
                $cache->delete("participants_export_{$programId}");
                
                // Use helper function to invalidate additional caches
                if (function_exists('invalidate_dashboard_cache')) {
                    invalidate_dashboard_cache($programId);
                }
                
                if (function_exists('invalidate_export_cache')) {
                    invalidate_export_cache($programId);
                }
                
                log_message('info', "PaymentModel::invalidatePaymentCaches - Invalidated payment and related caches for program ID {$programId}");
            }
            
            // Delete participant-specific caches
            $cache->delete("participant_payments_{$participantId}");
            $cache->delete("has_successful_payments_{$participantId}");
            $cache->delete("participant_{$participantId}");
            $cache->delete("participant_details_{$participantId}");
            
            if ($programId) {
                $cache->delete("has_payments_{$participantId}_{$programId}");
            }
            
            // Invalidate search caches that might include participant payment status
            if (function_exists('invalidate_search_cache')) {
                invalidate_search_cache();
            }
            
            // Invalidate participant-specific cache using helper
            if (function_exists('invalidate_participant_cache')) {
                invalidate_participant_cache($participantId, $programId);
            }
            
            log_message('info', "PaymentModel::invalidatePaymentCaches - Invalidated cache for participant ID {$participantId}");
        }
        
        // Invalidate general payment caches regardless of participant ID
        $cache->delete('payment_stats');
        $cache->delete('payment_stats_by_currency');
        $cache->delete('pending_manual_payments');
        
        // Use helper function for payment cache invalidation
        if (function_exists('invalidate_payment_cache')) {
            invalidate_payment_cache(null, $participantId);
        }
    }
    
    /**
     * Get normalized payments data for export
     * This method provides clean, human-readable data optimized for export purposes
     * 
     * @param int $programId Program ID
     * @param array $filters Additional filters
     * @return array Normalized payment data
     */
    public function getNormalizedPaymentsForExport($programId, $filters = [])
    {
        try {
            $builder = $this->db->table('payments');
            
            // Select only the most relevant fields for export
            $builder->select('
                payments.id as payment_id,
                payments.transaction_code,
                payments.order_id,
                payments.status as payment_status_code,
                payments.amount as payment_amount,
                payments.usd_amount as payment_usd_amount,
                payments.currency as payment_currency,
                payments.proof_url as payment_proof_url,
                payments.account_name as payment_account_name,
                payments.source_name as payment_source_name,
                payments.notes as payment_notes,
                payments.rejection_reason as payment_rejection_reason,
                payments.created_at as payment_created_at,
                payments.updated_at as payment_updated_at,
                
                participants.id as participant_id,
                participants.full_name as participant_name,
                participants.account_id as participant_account_id,
                participants.nationality as participant_nationality,
                participants.phone_number as participant_phone,
                participants.category as participant_category,
                
                users.email as participant_email,
                
                programs.name as program_name,
                
                pp.name as program_payment_name,
                pp.idr_amount as program_payment_idr_amount,
                pp.usd_amount as program_payment_usd_amount,
                pp.category as program_payment_category,
                
                pm.name as payment_method_name,
                pm.type as payment_method_type
            ')
            ->join('participants', 'participants.id = payments.participant_id', 'inner')
            ->join('users', 'users.id = participants.user_id', 'left')
            ->join('programs', 'programs.id = participants.program_id', 'left')
            ->join('program_payments pp', 'pp.id = payments.program_payment_id', 'left')
            ->join('payment_methods pm', 'pm.id = payments.payment_method_id', 'left');
            
            // Apply program filter (mandatory)
            $builder->where('participants.program_id', $programId);
            
            // Only get non-deleted records
            $builder->where('payments.is_deleted', 0)
                   ->where('participants.is_deleted', 0);
            
            // Apply additional filters
            if (isset($filters['status']) && $filters['status'] !== '') {
                $builder->where('payments.status', $filters['status']);
            }
            
            if (isset($filters['payment_method_id']) && $filters['payment_method_id'] !== '') {
                $builder->where('payments.payment_method_id', $filters['payment_method_id']);
            }
            
            if (isset($filters['program_payment_id']) && $filters['program_payment_id'] !== '') {
                $builder->where('payments.program_payment_id', $filters['program_payment_id']);
            }
            
            // Date range filter
            if (isset($filters['start_date']) && $filters['start_date'] !== '') {
                $builder->where('DATE(payments.created_at) >=', $filters['start_date']);
            }
            
            if (isset($filters['end_date']) && $filters['end_date'] !== '') {
                $builder->where('DATE(payments.created_at) <=', $filters['end_date']);
            }
            
            // Order by payment date descending (latest first), then by payment ID descending for consistency
            $builder->orderBy('payments.created_at', 'DESC')
                   ->orderBy('payments.id', 'DESC');
            
            $payments = $builder->get()->getResult();
            
            // Normalize the data for export
            $normalizedPayments = [];
            
            foreach ($payments as $payment) {
                $normalizedPayments[] = $this->normalizePaymentForExport($payment);
            }
            
            return $normalizedPayments;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting normalized payments for export: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve payments data: ' . $e->getMessage());
        }
    }
    
    /**
     * Normalize a single payment record for export - Optimized for admin use
     * 
     * @param object $payment Raw payment data from database
     * @return array Normalized payment data with prioritized columns
     */
    private function normalizePaymentForExport($payment)
    {
        return [
            // === CORE IDENTIFICATION (High Priority) ===
            'Payment_ID' => $payment->payment_id ?? 'N/A',
            'Transaction_Code' => $payment->transaction_code ?? 'N/A',
            
            // === PARTICIPANT INFORMATION (High Priority) ===
            'Participant_Name' => $payment->participant_name ?? 'Unknown',
            'Email' => $payment->participant_email ?? 'No Email',
            'Phone' => $payment->participant_phone ?? 'No Phone',
            'Nationality' => $payment->participant_nationality ?? 'Not Specified',
            'Category' => $this->getParticipantCategoryText($payment->participant_category),
            
            // === PAYMENT STATUS & TIMING (High Priority) ===
            'Payment_Status' => $this->getPaymentStatusText($payment->payment_status_code),
            'Payment_Date' => $this->normalizeDate($payment->payment_created_at),
            
            // === AMOUNT INFORMATION (High Priority) ===
            'Amount' => $this->formatCurrencyForExport($payment->payment_amount, $payment->payment_currency),
            'USD_Amount' => $this->formatCurrencyForExport($payment->payment_usd_amount, 'USD'),
            'Currency' => strtoupper($payment->payment_currency ?? 'IDR'),
            
            // === PAYMENT DETAILS (Medium Priority) ===
            'Payment_Method' => $payment->payment_method_name ?? 'Not Specified',
            'Account_Name' => $payment->payment_account_name ?? 'Not Provided',
            'Payment_Source' => $payment->payment_source_name ?? 'Not Provided',
            'Payment_Type' => $this->getPaymentTypeDisplay($payment->program_payment_name, $payment->program_payment_category),
            
            // === PROGRAM INFORMATION (Medium Priority) ===
            'Program' => $payment->program_name ?? 'Unknown Program',
            'Expected_Amount_IDR' => $this->formatCurrencyForExport($payment->program_payment_idr_amount, 'IDR'),
            'Expected_Amount_USD' => $this->formatCurrencyForExport($payment->program_payment_usd_amount, 'USD'),
            
            // === ADMINISTRATIVE NOTES (Lower Priority) ===
            'Payment_Proof' => $payment->payment_proof_url ? 'Uploaded' : 'Not Provided',
            'Notes' => $this->cleanNotesForExport($payment->payment_notes),
            'Rejection_Reason' => $payment->payment_status_code == 3 ? ($payment->payment_rejection_reason ?? 'No reason provided') : 'N/A',
        ];
    }
    
    /**
     * Get human-readable payment status text
     * 
     * @param int|null $statusCode
     * @return string
     */
    private function getPaymentStatusText($statusCode)
    {
        $statuses = [
            0 => 'Created',
            1 => 'Pending Review',
            2 => 'Success/Approved',
            3 => 'Cancelled',
            4 => 'Rejected'
        ];
        
        return $statuses[$statusCode] ?? 'Unknown Status';
    }
    
    /**
     * Get human-readable participant category text
     * 
     * @param string|null $category
     * @return string
     */
    private function getParticipantCategoryText($category)
    {
        $categories = [
            'fully_funded' => 'Fully Funded',
            'self_funded' => 'Self Funded'
        ];
        
        return $categories[$category] ?? ($category ?? 'Not Specified');
    }
    
    /**
     * Normalize date for export (handle null/empty dates)
     * 
     * @param string|null $date
     * @return string
     */
    private function normalizeDate($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return 'Not Specified';
        }
        
        try {
            return date('Y-m-d', strtotime($date));
        } catch (\Exception $e) {
            return 'Invalid Date';
        }
    }
    
    /**
     * Normalize datetime for export (handle null/empty dates)
     * 
     * @param string|null $datetime
     * @return string
     */
    private function normalizeDateTime($datetime)
    {
        if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
            return 'Not Specified';
        }
        
        try {
            return date('Y-m-d H:i:s', strtotime($datetime));
        } catch (\Exception $e) {
            return 'Invalid DateTime';
        }
    }
    
    /**
     * Normalize amount (handle null/empty/invalid amounts)
     * 
     * @param mixed $amount
     * @return string
     */
    private function normalizeAmount($amount)
    {
        if ($amount === null || $amount === '' || !is_numeric($amount)) {
            return '0.00';
        }
        
        return number_format((float)$amount, 2, '.', '');
    }
    
    /**
     * Format currency for export with proper symbols and formatting
     * 
     * @param mixed $amount
     * @param string $currency
     * @return string
     */
    private function formatCurrencyForExport($amount, $currency = 'IDR')
    {
        if ($amount === null || $amount === '' || !is_numeric($amount)) {
            return $this->getCurrencySymbol($currency) . ' 0.00';
        }
        
        $normalizedAmount = (float)$amount;
        $symbol = $this->getCurrencySymbol($currency);
        
        // Format based on currency (handle null currency)
        $currencyUpper = $currency ? strtoupper($currency) : 'UNKNOWN';
        if ($currencyUpper === 'IDR') {
            return $symbol . ' ' . number_format($normalizedAmount, 0, ',', '.');
        } else {
            return $symbol . ' ' . number_format($normalizedAmount, 2, '.', ',');
        }
    }
    
    /**
     * Get currency symbol
     * 
     * @param string $currency
     * @return string
     */
    private function getCurrencySymbol($currency)
    {
        // Handle null or empty currency
        if (empty($currency)) {
            return 'Unknown';
        }
        
        $symbols = [
            'IDR' => 'Rp',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'SGD' => 'S$',
            'MYR' => 'RM'
        ];
        
        $currencyUpper = strtoupper($currency);
        return $symbols[$currencyUpper] ?? $currencyUpper;
    }
    
    /**
     * Get display-friendly payment type combining name and category
     */
    private function getPaymentTypeDisplay($paymentName, $paymentCategory)
    {
        $name = $paymentName ?? 'General Payment';
        $category = $paymentCategory ?? '';
        
        if (!empty($category) && $category !== $name) {
            return $name . ' (' . $category . ')';
        }
        
        return $name;
    }
    
    /**
     * Clean notes for export - remove sensitive info and format nicely
     */
    private function cleanNotesForExport($notes)
    {
        if (empty($notes)) {
            return 'No notes';
        }
        
        // Remove potential sensitive information patterns
        $cleaned = preg_replace('/\b\d{4}[\s\-]?\d{4}[\s\-]?\d{4}[\s\-]?\d{4}\b/', '[CARD NUMBER HIDDEN]', $notes);
        $cleaned = preg_replace('/\b\d{3,4}[\s\-]?\d{3,4}[\s\-]?\d{4}\b/', '[PHONE HIDDEN]', $cleaned);
        
        // Limit length for export readability
        if (strlen($cleaned) > 200) {
            $cleaned = substr($cleaned, 0, 197) . '...';
        }
        
        return trim($cleaned) ?: 'No notes';
    }
}
