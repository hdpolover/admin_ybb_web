<?php

namespace App\Models;

use CodeIgniter\Model;


class PaymentModel extends Model
{
    protected $table      = 'payments';
    protected $primaryKey = 'id';
    
    protected $useAutoIncrement = true;
    protected $returnType     = 'object';
    
    protected $allowedFields = [
        'program_id', 'participant_id', 'amount', 'currency', 'payment_method', 
        'payment_date', 'transaction_id', 'status', 'notes'
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
}