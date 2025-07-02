<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantAwardModel extends Model
{
    protected $table = 'participant_awards';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'participant_id',
        'award_id',
        'assigned_by',
        'assigned_at',
        'notes',
        'is_active',
        'is_deleted'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'participant_id' => 'required|integer',
        'award_id' => 'required|integer',
        'assigned_by' => 'required|integer',
        'assigned_at' => 'required|valid_date',
        'notes' => 'permit_empty|string',
        'is_active' => 'permit_empty|in_list[0,1]',
        'is_deleted' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'participant_id' => [
            'required' => 'Participant ID is required',
            'integer' => 'Participant ID must be a valid integer'
        ],
        'award_id' => [
            'required' => 'Award ID is required',
            'integer' => 'Award ID must be a valid integer'
        ],
        'assigned_by' => [
            'required' => 'Assigned by user ID is required',
            'integer' => 'Assigned by must be a valid integer'
        ],
        'assigned_at' => [
            'required' => 'Assignment date is required',
            'valid_date' => 'Assignment date must be a valid datetime'
        ]
    ];

    /**
     * Get awards for a specific participant
     */
    public function getParticipantAwards($participantId)
    {
        return $this->select('participant_awards.*, program_awards.title as award_title, program_awards.award_type, users.username as assigned_by_name')
                   ->join('program_awards', 'program_awards.id = participant_awards.award_id', 'left')
                   ->join('users', 'users.id = participant_awards.assigned_by', 'left')
                   ->where('participant_awards.participant_id', $participantId)
                   ->where('participant_awards.is_active', 1)
                   ->where('participant_awards.is_deleted', 0)
                   ->orderBy('participant_awards.assigned_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get participants for a specific award
     */
    public function getAwardParticipants($awardId)
    {
        return $this->select('participant_awards.*, participants.full_name, participants.account_id, users.email')
                   ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                   ->join('users', 'users.id = participants.user_id', 'left')
                   ->where('participant_awards.award_id', $awardId)
                   ->where('participant_awards.is_active', 1)
                   ->where('participant_awards.is_deleted', 0)
                   ->orderBy('participant_awards.assigned_at', 'DESC')
                   ->findAll();
    }

    /**
     * Check if participant already has this award
     */
    public function hasParticipantAward($participantId, $awardId)
    {
        log_message('info', "Checking if participant ID: $participantId has award ID: $awardId");
        
        $result = $this->where('participant_id', $participantId)
                   ->where('award_id', $awardId)
                   ->where('is_active', 1)
                   ->where('is_deleted', 0)
                   ->first();
                   
        $hasAward = $result !== null;
        log_message('info', "Participant ID: $participantId has award ID: $awardId - " . ($hasAward ? 'YES' : 'NO'));
        
        return $hasAward;
    }

    /**
     * Get participant award with details
     */
    public function getParticipantAwardWithDetails($id)
    {
        return $this->select('participant_awards.*, program_awards.title as award_title, program_awards.award_type, participants.full_name, users.username as assigned_by_name')
                   ->join('program_awards', 'program_awards.id = participant_awards.award_id', 'left')
                   ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                   ->join('users', 'users.id = participant_awards.assigned_by', 'left')
                   ->where('participant_awards.id', $id)
                   ->where('participant_awards.is_deleted', 0)
                   ->first();
    }

    /**
     * Soft delete participant award
     */
    public function softDelete($id)
    {
        return $this->update($id, [
            'is_deleted' => 1,
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
