<?php

namespace App\Models;

use CodeIgniter\Model;

class AbstractAuthorModel extends Model
{
    // `id`, `abstract_id`, `full_name`, `institution`, `email`, `updated_at`, `created_at`, `is_active`, `is_deleted`, `is_participant`, `participant_id`
    protected $table = 'abstract_authors';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $fillable = [
        'id',
        'abstract_id',
        'full_name',
        'institution',
        'email',
        'updated_at',
        'created_at',
        'is_active',
        'is_deleted',
        'is_participant',
        'participant_id'
    ];
    protected $allowedFields = [
        'abstract_id',
        'full_name',
        'institution',
        'email',
        'updated_at',
        'created_at',
        'is_active',
        'is_deleted',
        'is_participant',
        'participant_id'
    ];

    protected $validationRules = [
        'abstract_id' => 'required|integer',
        'full_name' => 'required|string|max_length[255]',
        'institution' => 'string|max_length[255]',
        'email' => 'required|valid_email|max_length[255]',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'is_participant' => 'boolean',
        'participant_id' => 'integer'
    ];

    // get abstract author by id
    public function getAbstractAuthorById($id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('id', $id);
        return $builder->get()->getRow();
    }

    // get all abstract authors by abstract id
    public function getAllAbstractAuthorsByAbstractId($abstract_id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('abstract_id', $abstract_id);
        return $builder->get()->getResult();
    }

    // get all abstract authors by participant id
    public function getAllAbstractAuthorsByParticipantId($participant_id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('participant_id', $participant_id);
        return $builder->get()->getResult();
    }

    // getAbstractByAuthorParticipantId
    public function getAbstractByAuthorParticipantId($participant_id)
    {
        $builder = $this->builder();
        $builder->select('abstract_authors.*, abstracts.*')
            ->join('abstracts', 'abstract_authors.abstract_id = abstracts.id')
            ->where('abstract_authors.participant_id', $participant_id);
        return $builder->get()->getResult();
    }
}