<?php

namespace App\Models;

use CodeIgniter\Model;

class AbstractModel extends Model
{
    // `abstracts` ADD FULLTEXT(`id`, `primary_participant_id`, `program_id`, `active_version_id`, `program_subtheme_id`, `status`, `is_active`, `is_deleted`, `created_at`, `updated_at`)
    protected $table = 'abstracts';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $fillable = [
        'id',
        'primary_participant_id',
        'program_id',
        'active_version_id',
        'program_subtheme_id',
        'status',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $allowedFields = [
        'primary_participant_id',
        'program_id',
        'active_version_id',
        'program_subtheme_id',
        'status',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $validationRules = [
        'primary_participant_id' => 'required|integer',
        'program_id' => 'required|integer',
        'active_version_id' => 'permit_empty|integer',
        'status' => 'in_list[draft,submitted,under_review,accepted]',
        'program_subtheme_id' => 'permit_empty|integer',
        'is_active' => 'in_list[0,1]',
        'is_deleted' => 'in_list[0,1]'
    ];

    // get by primary participant id
    public function getByPrimaryParticipantId($primary_participant_id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('primary_participant_id', $primary_participant_id);
        return $builder->get()->getRow();
    }

    // get abstract by id
    public function getAbstractById($id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('id', $id);
        return $builder->get()->getRow();
    }

    // get all abstracts by program id
    public function getAllAbstractsByProgramId($program_id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('program_id', $program_id);
        return $builder->get()->getResult();
    }    // get all abstracts by participant id
    public function getAllAbstractsByParticipantId($participant_id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('primary_participant_id', $participant_id);
        return $builder->get()->getResult();
    }
}
