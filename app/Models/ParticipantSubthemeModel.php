<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantSubthemeModel extends Model
{
    // `id`, `program_subtheme_id`, `participant_id`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table = 'participant_subthemes';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'program_subtheme_id',
        'participant_id',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];
    
    public function getSubthemesByParticipantId($participant_id)
    {
        $builder = $this->builder();

        // Select all fields
        $builder->select('*');

        // Filter by participant ID
        $result = $builder->where('participant_id', $participant_id)->get()->getRow();

        if (empty($result)) {
            return null;
        }

        return $result;
    }
    
}