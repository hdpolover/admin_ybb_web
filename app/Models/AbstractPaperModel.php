<?php

namespace App\Models;

use CodeIgniter\Model;

class AbstractPaperModel extends Model
{
    // `abstract_papers:`id`, `abstract_id`, `file_url`, `notes`, `status`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table = 'abstract_papers';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $fillable = [
        'id',
        'abstract_id',
        'file_url',
        'notes',
        'status',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $allowedFields = [
        'abstract_id',
        'file_url',
        'notes',
        'status',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $validationRules = [
        'abstract_id' => 'required|integer',
        'file_url' => 'required|string|max_length[255]',
        'notes' => 'string|max_length[500]',
        'status' => 'in_list[submitted,under_review,accepted,rejected]',
        'is_active' => 'in_list[0,1]',
        'is_deleted' => 'in_list[0,1]'
    ];

    // Get abstract paper by ID
    public function getAbstractPaperById($id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('id', $id)
            ->where('is_active', 1)
            ->where('is_deleted', 0);
        return $builder->get()->getRow();
    }
}