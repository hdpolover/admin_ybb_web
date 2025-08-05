<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreWeightModel extends Model
{
    protected $table = 'score_weights';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;

    //`id`, `program_id`, `questions`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $allowedFields = [
        'program_id',
        'description',
        'reference',
        'reference',
        'weight',
        'weight2',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = ''; // Not using soft deletes
    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';
    protected $protectFields = true;
    protected $validationRules = [
        'program_id' => 'required|numeric',
        'description'  => 'required|min_length[3]',
        'is_active'  => 'permit_empty|in_list[0,1]',
        'is_deleted' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [];

    // get active score weight for a program
    public function getActiveScoreWeights($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->findAll();
    }

    // Get score weight by program ID
    public function getScoreWeightsByProgramId($programId)
    {
        return $this->where('program_id', $programId)
                   ->where('is_deleted', 0)
                   ->findAll();
    }
}