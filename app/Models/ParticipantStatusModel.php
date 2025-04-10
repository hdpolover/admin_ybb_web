<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantStatusModel extends Model
{

    // `id`, `participant_id`, `general_status`, `form_status`, `document_status`, `payment_status`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table = 'participant_statuses';
    protected $primaryKey = 'id';
    protected $allowedFields = ['participant_id', 'general_status', 'form_status', 'document_status', 'payment_status', 'is_active', 'is_deleted', 'created_at', 'updated_at'];

    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $protectFields = true;

    // get participant status by participant id
    public function getParticipantStatusById($participantId)
    {
        return $this->where('participant_id', $participantId)
            ->where('is_deleted', 0)
            ->first();
    }

    // add participant status by participant id
    public function addParticipantStatus($participantId)
    {
        $data = [
            'participant_id' => $participantId,
            'general_status' => 0,
            'form_status' => 0,
            'document_status' => 0,
            'payment_status' => 0,
            'is_active' => 1,
            'is_deleted' => 0,
        ];

        return $this->save($data);
    }

}
