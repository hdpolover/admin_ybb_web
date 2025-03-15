<?php

namespace App\Models;

use App\Entities\UserEntity;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'full_name',
        'email',
        'password',
        'is_verified',
        'program_category_id',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'password'
    ];

    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getUsers($limit = 10, $offset = 0, $filters = [])
    {
        $builder = $this->builder();

        // Apply filters if any
        if (!empty($filters)) {
            $builder->where($filters);
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        // Apply pagination
        $builder->limit($limit, $offset);
        
        // Select fields (exclude password for security)
        $builder->select('*');

        // Execute query
        $result = $builder->get()->getResultArray();

        return [
            'data' => $result,
            'total' => $total
        ];
    }
}
?>