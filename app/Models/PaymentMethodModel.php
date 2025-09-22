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

    // Model event callbacks for cache invalidation
    protected $beforeInsert   = [];
    protected $afterInsert    = ['invalidateCacheAfterInsert'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['invalidateCacheAfterUpdate'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = ['invalidateCacheAfterDelete'];

    /**
     * Constructor to load cache helper
     */
    public function __construct()
    {
        parent::__construct();
        helper(['cache']);
    }    

    protected $validationRules = [
        'program_id' => 'required|integer',
        'name' => 'required|string|max_length[255]',
        'description' => 'string',
        'type' => 'required|string|max_length[50]',
        'img_url' => 'string|max_length[255]',
        'is_active' => 'in_list[0,1]',
        'is_deleted' => 'in_list[0,1]'
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
            'in_list' => 'Is Active must be either 0 or 1'
        ],
        'is_deleted' => [
            'in_list' => 'Is Deleted must be either 0 or 1'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;

    // get all payment methods for a program
    public function getByProgramId($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->findAll();
    }

    // get all active payment methods for a program
    public function getActiveByProgramId($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->findAll();
    }

    // get payment method by id
    public function getPaymentMethodById($id)
    {
        return $this->where('id', $id)
            ->first();
    }

    /**
     * Invalidate cache after insert operation
     */
    protected function invalidateCacheAfterInsert(array $data): array
    {
        if (isset($data['id'])) {
            $this->invalidatePaymentMethodCaches($data['id']);
        }
        return $data;
    }

    /**
     * Invalidate cache after update operation
     */
    protected function invalidateCacheAfterUpdate(array $data): array
    {
        if (isset($data['id'])) {
            $ids = is_array($data['id']) ? $data['id'] : [$data['id']];
            foreach ($ids as $id) {
                $this->invalidatePaymentMethodCaches($id);
            }
        }
        return $data;
    }

    /**
     * Invalidate cache after delete operation
     */
    protected function invalidateCacheAfterDelete(array $data): array
    {
        if (isset($data['id'])) {
            $ids = is_array($data['id']) ? $data['id'] : [$data['id']];
            foreach ($ids as $id) {
                $this->invalidatePaymentMethodCaches($id);
            }
        }
        return $data;
    }

    /**
     * Invalidate all caches related to payment methods
     */
    private function invalidatePaymentMethodCaches(int $paymentMethodId): void
    {
        try {
            // Ensure cache helper is loaded
            if (!function_exists('invalidate_program_cache')) {
                helper(['cache']);
            }
            
            $cache = \Config\Services::cache();
            
            // Get payment method to find program ID
            $paymentMethod = $this->find($paymentMethodId);
            
            // Clear payment method specific caches
            $cache->delete("payment_method_{$paymentMethodId}");
            $cache->delete("payment_methods_all");
            
            if ($paymentMethod && isset($paymentMethod->program_id)) {
                $cache->delete("payment_methods_program_{$paymentMethod->program_id}");
                if (function_exists('invalidate_program_cache')) {
                    invalidate_program_cache($paymentMethod->program_id);
                }
            }
            
            if (function_exists('invalidate_payment_cache')) {
                invalidate_payment_cache();
            }
            
            log_message('info', "PaymentMethodModel: Cache invalidated for payment method ID: {$paymentMethodId}");
            
        } catch (\Exception $e) {
            log_message('error', 'PaymentMethodModel: Error invalidating cache - ' . $e->getMessage());
        }
    }
}
