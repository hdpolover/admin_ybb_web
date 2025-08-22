<?php

namespace App\Models;

use CodeIgniter\Model;

class AmbassadorParticipantReferralModel extends Model
{
    // `id`, `participant_id`, `ambassador_id`, `created_at`, `updated_at`, `is_active`, `is_deleted`
    protected $table = 'ambassador_participant_referrals';
    protected $primaryKey = 'id';
    protected $allowedFields = ['participant_id', 'ambassador_id', 'created_at', 'updated_at', 'is_active', 'is_deleted'];
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $protectFields = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = ''; // Not using soft deletes

    protected $validationRules = [
        'participant_id' => 'required|numeric',
        'ambassador_id'  => 'required|numeric',
        'is_active'      => 'permit_empty|in_list[0,1]',
        'is_deleted'     => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // add participant referral
    public function addParticipantReferral($data)
    {
        $this->save($data);
        return $this->insertID(); // Return the ID of the newly inserted record
    }

    // Get all referrals for a specific ambassador with program validation
    public function getReferralsByAmbassadorId($ambassadorId, $programId = null)
    {
        $builder = $this->builder();
        $builder->where('ambassador_participant_referrals.ambassador_id', $ambassadorId)
                ->where('ambassador_participant_referrals.is_deleted', 0);
        
        // Add program validation by joining with ambassadors table
        if ($programId !== null) {
            $builder->join('ambassadors', 'ambassadors.id = ambassador_participant_referrals.ambassador_id')
                    ->where('ambassadors.program_id', $programId)
                    ->where('ambassadors.is_deleted', 0);
        }
        
        return $builder->get()->getResult();
    }

    // get referral by participant id with program validation
    public function getReferralByParticipantId($participantId, $programId = null)
    {
        $builder = $this->builder();
        $builder->where('ambassador_participant_referrals.participant_id', $participantId)
                ->where('ambassador_participant_referrals.is_deleted', 0);
        
        // Add program validation by joining with participants table
        if ($programId !== null) {
            $builder->join('participants', 'participants.id = ambassador_participant_referrals.participant_id')
                    ->where('participants.program_id', $programId)
                    ->where('participants.is_deleted', 0);
        }
        
        return $builder->get()->getFirstRow();
    }

    // get ambassador by ref_code
    public function getAmbassadorByRefCode($ref_code)
    {
        return $this->where('ref_code', $ref_code)->first();
    }

    // Get all referrals data
    public function getAllReferrals()
    {
        return $this->where('is_deleted', 0)->findAll();
    }
}
