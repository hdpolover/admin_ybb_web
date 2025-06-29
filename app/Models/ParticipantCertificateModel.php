<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantCertificateModel extends Model
{
    protected $table = 'participant_certificates';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'participant_id',
        'award_id',
        'certificate_id',
        'generated_at',
        'is_active',
        'is_deleted'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'participant_id' => 'required|integer',
        'award_id' => 'required|integer',
        'certificate_id' => 'required|integer',
        'generated_at' => 'required|valid_date',
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
        'certificate_id' => [
            'required' => 'Certificate ID is required',
            'integer' => 'Certificate ID must be a valid integer'
        ],
        'generated_at' => [
            'required' => 'Generation date is required',
            'valid_date' => 'Generation date must be a valid datetime'
        ]
    ];

    /**
     * Get certificates for a specific participant
     */
    public function getParticipantCertificates($participantId)
    {
        return $this->select('participant_certificates.*, program_awards.title as award_title, program_certificates.template_url, participants.full_name')
                   ->join('program_awards', 'program_awards.id = participant_certificates.award_id', 'left')
                   ->join('program_certificates', 'program_certificates.id = participant_certificates.certificate_id', 'left')
                   ->join('participants', 'participants.id = participant_certificates.participant_id', 'left')
                   ->where('participant_certificates.participant_id', $participantId)
                   ->where('participant_certificates.is_active', 1)
                   ->where('participant_certificates.is_deleted', 0)
                   ->orderBy('participant_certificates.generated_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get participants who have received a specific certificate
     */
    public function getCertificateParticipants($certificateId)
    {
        return $this->select('participant_certificates.*, participants.full_name, participants.account_id, users.email')
                   ->join('participants', 'participants.id = participant_certificates.participant_id', 'left')
                   ->join('users', 'users.id = participants.user_id', 'left')
                   ->where('participant_certificates.certificate_id', $certificateId)
                   ->where('participant_certificates.is_active', 1)
                   ->where('participant_certificates.is_deleted', 0)
                   ->orderBy('participant_certificates.generated_at', 'DESC')
                   ->findAll();
    }

    /**
     * Check if participant already has this certificate
     */
    public function hasParticipantCertificate($participantId, $certificateId)
    {
        return $this->where('participant_id', $participantId)
                   ->where('certificate_id', $certificateId)
                   ->where('is_active', 1)
                   ->where('is_deleted', 0)
                   ->first() !== null;
    }

    /**
     * Get certificate with full details
     */
    public function getCertificateWithDetails($id)
    {
        return $this->select('participant_certificates.*, program_awards.title as award_title, program_certificates.template_url, participants.full_name, participants.account_id')
                   ->join('program_awards', 'program_awards.id = participant_certificates.award_id', 'left')
                   ->join('program_certificates', 'program_certificates.id = participant_certificates.certificate_id', 'left')
                   ->join('participants', 'participants.id = participant_certificates.participant_id', 'left')
                   ->where('participant_certificates.id', $id)
                   ->where('participant_certificates.is_deleted', 0)
                   ->first();
    }

    /**
     * Get certificates by award
     */
    public function getCertificatesByAward($awardId)
    {
        return $this->select('participant_certificates.*, participants.full_name, participants.account_id')
                   ->join('participants', 'participants.id = participant_certificates.participant_id', 'left')
                   ->where('participant_certificates.award_id', $awardId)
                   ->where('participant_certificates.is_active', 1)
                   ->where('participant_certificates.is_deleted', 0)
                   ->orderBy('participant_certificates.generated_at', 'DESC')
                   ->findAll();
    }

    /**
     * Soft delete participant certificate
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
