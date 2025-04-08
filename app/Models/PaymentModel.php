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
        'program_payment_id',
        'payment_method_id',
        'status',
        'proof_url',
        'account_name',
        'amount',
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

    /**
     * Get payments with participant details
     * 
     * @param int $programId Program ID
     * @return array
     */
    public function getPaymentsWithDetails($programId)
    {
        return $this->select('
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
        return $this->select('
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
    }

    /**
     * Update payment status
     * 
     * @param int $id Payment ID
     * @param int $status New status
     * @param string $notes Optional notes for the update
     * @return bool
     */
    public function updatePaymentStatus($id, $status, $notes = '')
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($notes)) {
            $payment = $this->find($id);
            $existingNotes = $payment->notes ?? '';
            $combinedNotes = $existingNotes . "\n\n" . date('Y-m-d H:i:s') . " - Status updated: " . $notes;
            $data['notes'] = trim($combinedNotes);
        }

        return $this->update($id, $data);
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
        $result = $this->select('COUNT(*) as payment_count')
            ->join('participants', 'participants.id = payments.participant_id')
            ->where('payments.participant_id', $participantId)
            ->where('participants.program_id', $programId)
            ->where('payments.status', 2) // Success status
            ->where('payments.is_deleted', 0)
            ->first();
        
        return ($result && $result->payment_count > 0);
    }
  
}
