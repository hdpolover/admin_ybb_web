<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramTypeModel extends Model
{
    protected $table = 'program_types';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;

    // `id`, `name`, `description`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $allowedFields = [
        'name',
        'description',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

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

    /**
     * Invalidate cache after insert operation
     */
    protected function invalidateCacheAfterInsert(array $data): array
    {
        if (isset($data['id'])) {
            $this->invalidateProgramTypeCaches($data['id']);
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
                $this->invalidateProgramTypeCaches($id);
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
                $this->invalidateProgramTypeCaches($id);
            }
        }
        return $data;
    }

    /**
     * Invalidate all caches related to program types
     */
    private function invalidateProgramTypeCaches(int $programTypeId): void
    {
        try {
            // Ensure cache helper is loaded
            if (!function_exists('invalidate_program_category_cache')) {
                helper(['cache']);
            }
            
            $cache = \Config\Services::cache();
            
            // Clear program type specific caches
            $cache->delete("program_type_{$programTypeId}");
            $cache->delete("program_types_all");
            
            // Clear related caches
            if (function_exists('invalidate_program_category_cache')) {
                invalidate_program_category_cache();
            }
            if (function_exists('invalidate_topbar_data_cache')) {
                invalidate_topbar_data_cache();
            }
            
            log_message('info', "ProgramTypeModel: Cache invalidated for program type ID: {$programTypeId}");
            
        } catch (\Exception $e) {
            log_message('error', 'ProgramTypeModel: Error invalidating cache - ' . $e->getMessage());
        }
    }
}
?>