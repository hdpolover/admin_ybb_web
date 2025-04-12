<?php

namespace App\Models;

use CodeIgniter\Model;


class PaymentMethodModel extends Model
{
    // `id`, `program_id`, `name`, `description`, `type`, `img_url`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table      = 'payment_methods';
    protected $primaryKey = 'id';
    protected $allowedFields = ['program_id', 'name', 'description', 'type', 'img_url', 'is_active', 'is_deleted', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $returnType     = 'object';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false; // Using is_deleted field manually

    protected $validationRules = [
        'program_id' => 'required|integer',
        'name' => 'required|string|max_length[255]',
        'description' => 'string|max_length[255]',
        'type' => 'required|string|max_length[50]',
        'img_url' => 'string|max_length[255]',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean'
    ];

    protected $validationMessages = [
        'program_id' => [
            'required' => 'Program ID is required',
            'integer' => 'Program ID must be an integer'
        ],
        'name' => [
            'required' => 'Name is required',
            'string' => 'Name must be a string',
            'max_length' => 'Name cannot exceed 255 characters'
        ],
        'description' => [
            'string' => 'Description must be a string',
            'max_length' => 'Description cannot exceed 255 characters'
        ],
        'type' => [
            'required' => 'Type is required',
            'string' => 'Type must be a string',
            'max_length' => 'Type cannot exceed 50 characters'
        ],
        'img_url' => [
            'string' => 'Image URL must be a string',
            'max_length' => 'Image URL cannot exceed 255 characters'
        ],
        'is_active' => [
            'boolean' => 'Is Active must be a boolean value'
        ],
        'is_deleted' => [
            'boolean' => 'Is Deleted must be a boolean value'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;

    // get all payment methods for a program
    public function getByProgramId($programId)
    {
        return $this->where('program_id', $programId)
            ->findAll();
    }

    // get all active payment methods for a program

    // get payment method by id
    public function getPaymentMethodById($id)
    {
        return $this->where('id', $id)
            ->first();
    }
    
}
