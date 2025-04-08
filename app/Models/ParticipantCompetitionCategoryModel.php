<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantCompetitionCategoryModel extends Model
{
    // `id`, `participant_id`, `competition_category_id`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table = 'participant_competition_categories';
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
        'participant_id',
        'competition_category_id',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];


    public function getCompetitionCategoriesByParticipantId($participant_id)
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