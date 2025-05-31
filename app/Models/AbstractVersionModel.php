<?php

namespace App\Models;

use CodeIgniter\Model;

class AbstractVersionModel extends Model
{
    // `id`, `abstract_id`, `title`, `content`, `keywords`, 'refs', `version_number`, 'status', `created_at`, `updated_at`, `is_deleted`, `is_active`
    protected $table = 'abstract_versions';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $fillable = [
        'id',
        'abstract_id',
        'title',
        'content',
        'keywords',
        'refs',
        'version_number',

        'created_at',
        'updated_at',
        'is_deleted',
        'is_active',
        'status'
    ];

    protected $allowedFields = [
        'abstract_id',
        'title',
        'content',
        'keywords',
        'refs',
        'version_number',
        'status',
        'created_at',
        'updated_at',
        'is_deleted',
        'is_active'
    ];   
    
    protected $validationRules = [
        'abstract_id' => 'required|integer',
        'title' => 'required|string|max_length[255]',
        'content' => 'required|string',
        'keywords' => 'string|max_length[255]',
        'refs' => 'string|max_length[255]',
        'version_number' => 'required|integer',
        'is_deleted' => 'in_list[0,1]',
        'is_active' => 'in_list[0,1]',
        'status' => 'in_list[draft,submitted]'
    ];

    // get abstract version by id
    public function getAbstractVersionById($id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('id', $id);
        return $builder->get()->getRow();
    }

    // get abstract version by abstract id
    public function getAbstractVersionByAbstractId($abstract_id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('abstract_id', $abstract_id);
        return $builder->get()->getRow();
    }

    // get all abstract versions
    public function getAllAbstractVersions()
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('is_deleted', 0)
            ->where('is_active', 1);
        return $builder->get()->getResult();
    }
    // get all abstract versions by abstract id
    public function getAllAbstractVersionsByAbstractId($abstract_id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('abstract_id', $abstract_id)
            ->where('is_deleted', 0)
            ->where('is_active', 1);
        return $builder->get()->getResult();
    }

}

