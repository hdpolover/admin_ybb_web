<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramPaymentModel extends Model {
    protected $table = 'program_payments';
    // `id`, `program_id`, `name`, `description`, `start_date`, `end_date`, `order_number`, `idr_amount`, `usd_amount`, `category`, `is_active`, `is_deleted`, `created_at`, `updated_at`    protected $table = 'program_payments';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'program_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'order_number',
        'idr_amount',
        'usd_amount',
        'category',
        'type',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    /**
     * Get program payments by program ID
     *
     * @param int $programId The program ID
     * @param bool $activeOnly Whether to get only active payments (default: true)
     * @param bool $includeDeleted Whether to include deleted payments (default: false)
     * @return array The program payments
     */
    public function getByProgramId($programId, $activeOnly = true, $includeDeleted = false)
    {
        $this->where('program_id', $programId);
        
        if ($activeOnly) {
            $this->where('is_active', 1);
        }
        
        if (!$includeDeleted) {
            $this->where('is_deleted', 0);
        }
        
        $this->orderBy('order_number', 'ASC');
        return $this->findAll();
    }

    /**
     * Get registration payment information for a program
     *
     * @param int $programId The program ID
     * @return array Array with self_funded and fully_funded payment info
     */
    public function getRegistrationPaymentFlags($programId)
    {
        $builder = $this->builder();
        $payments = $builder
            ->where('program_id', $programId)
            ->where('category', 'registration')
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->whereIn('type', ['self_funded', 'fully_funded'])
            ->get()
            ->getResult();

        $paymentFlags = [
            'self_funded' => null,
            'fully_funded' => null
        ];

        foreach ($payments as $payment) {
            $currentDate = date('Y-m-d');
            $isAvailable = true;
            
            // Check if payment is within valid date range
            if ($payment->start_date && $currentDate < date('Y-m-d', strtotime($payment->start_date))) {
                $isAvailable = false;
            }
            if ($payment->end_date && $currentDate > date('Y-m-d', strtotime($payment->end_date))) {
                $isAvailable = false;
            }

            $paymentData = [
                'id' => $payment->id,
                'name' => $payment->name,
                'description' => $payment->description,
                'usd_amount' => $payment->usd_amount,
                'start_date' => $payment->start_date,
                'end_date' => $payment->end_date,
                'is_available' => $isAvailable,
                'is_active' => $payment->is_active == 1
            ];

            if ($payment->type === 'self_funded') {
                $paymentFlags['self_funded'] = $paymentData;
            } elseif ($payment->type === 'fully_funded') {
                $paymentFlags['fully_funded'] = $paymentData;
            }
        }

        return $paymentFlags;
    }

    /**
     * Get available registration payment for a specific category
     * Used for category switching validation
     * 
     * @param int $programId The program ID
     * @param string $paymentType The payment type (self_funded or fully_funded)
     * @return object|null The available payment or null if not available
     */
    public function getAvailableRegistrationPayment($programId, $paymentType)
    {
        try {
            $currentDate = date('Y-m-d H:i:s');
            
            $builder = $this->builder();
            $payment = $builder
                ->where('program_id', $programId)
                ->where('category', 'registration')
                ->where('type', $paymentType)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->where('start_date <=', $currentDate)
                ->where('end_date >=', $currentDate)
                ->get()
                ->getFirstRow();

            return $payment;

        } catch (\Exception $e) {
            log_message('error', "Error getting available registration payment: " . $e->getMessage());
            return null;
        }
    }
}