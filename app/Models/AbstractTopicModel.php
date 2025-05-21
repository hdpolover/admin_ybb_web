<?php

namespace App\Models;

use CodeIgniter\Model;

class AbstractTopicModel extends Model
{
    // `id`, `program_id`, `name`, `description`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table = 'abstract_topics';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $fillable = [
        'id',
        'program_id',
        'name',
        'description',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $allowedFields = [
        'program_id',
        'name',
        'description',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    // get abstract topic by id
    public function getAbstractTopicById($id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('id', $id);
        return $builder->get()->getRow();
    }

    // get all abstract topics by program id
    public function getAllAbstractTopicsByProgramId($program_id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('program_id', $program_id)
            ->where('is_deleted', 0) // Only get non-deleted topics
            ->orderBy('id', 'DESC'); // Order by ID in descending order
        return $builder->get()->getResult();
    }

}