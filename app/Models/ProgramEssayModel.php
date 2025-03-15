<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramEssayModel extends Model
{
    protected $table = 'program_essays';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;

    //`id`, `program_id`, `questions`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $allowedFields = [
        'program_id',
        'questions',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

}