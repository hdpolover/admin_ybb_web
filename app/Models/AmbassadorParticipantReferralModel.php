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

    // Get all referrals for a specific ambassador
    public function getReferralsByAmbassadorId($ambassadorId)
    {
        return $this->where('ambassador_id', $ambassadorId)
                    ->where('is_deleted', 0)
                    ->findAll();
    }

    // Get all referrals data
    public function getAllReferrals()
    {
        return $this->where('is_deleted', 0)->findAll();
    }
    
}