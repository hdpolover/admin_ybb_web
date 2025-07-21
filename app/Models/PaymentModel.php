<?php

namespace App\Models;

use CodeIgniter\Model;


class PaymentModel extends Model
{
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
            throw new \InvalidArgumentException('Rejection reason is required for rejected status');
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
            // Load helper if not already loaded
            helper(['cache']);
            
            // Invalidate payment caches
            invalidate_payment_cache($id, $participantId);
            
            // Log cache invalidation
            log_message('info', "PaymentModel::updatePaymentStatus - Invalidated cache for payment ID {$id} and participant ID {$participantId}");
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
}
