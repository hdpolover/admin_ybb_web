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
    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $protectFields = true;
    protected $useTimestamps = true; // Enable timestamps
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime'; // Set date format to datetime
    

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

        // Select fields from both tables
        $builder->select('participant_subthemes.*, program_subthemes.name as subtheme_name, program_subthemes.desc as subtheme_description');
        
        // Join with program_subthemes table
        $builder->join('program_subthemes', 'program_subthemes.id = participant_subthemes.program_subtheme_id', 'left');

        // Filter by participant ID
        $result = $builder->where('participant_subthemes.participant_id', $participant_id)->get()->getRow();

        return $result;
    }
    
}