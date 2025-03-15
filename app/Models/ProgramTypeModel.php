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

}
?>