<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramPaymentModel extends Model {
    // `id`, `program_id`, `name`, `description`, `start_date`, `end_date`, `order_number`, `idr_amount`, `usd_amount`, `category`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table = 'program_payments';
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
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

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
}