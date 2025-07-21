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
        'certificate_id' => 'permit_empty|integer',
        'generated_at' => 'permit_empty|valid_date',
        'is_active' => 'permit_empty|in_list[0,1]',
        'is_deleted' => 'permit_empty|in_list[0,1]'
    ];

    /**
     * Check if participant has certificate for award
     */
    public function hasParticipantCertificate($participantId, $awardId)
    {
        return $this->where('participant_id', $participantId)
                   ->where('award_id', $awardId)
                   ->where('is_active', 1)
                   ->where('is_deleted', 0)
                   ->first() !== null;
    }

    /**
     * Check if certificate has been generated
     */
    public function hasCertificateGenerated($participantId, $awardId)
    {
        return $this->where('participant_id', $participantId)
                   ->where('award_id', $awardId)
                   ->where('generated_at IS NOT NULL')
                   ->where('is_active', 1)
                   ->where('is_deleted', 0)
                   ->first();
    }

    /**
     * Get participant certificates with details
     */
    public function getParticipantCertificates($participantId)
    {
        return $this->select('participant_certificates.*, program_awards.title as award_title, 
                             program_awards.description as award_description, program_awards.award_type')
                   ->join('program_awards', 'program_awards.id = participant_certificates.award_id', 'left')
                   ->where('participant_certificates.participant_id', $participantId)
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