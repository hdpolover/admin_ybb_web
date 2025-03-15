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
}