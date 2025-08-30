<?php

namespace App\Models;

use CodeIgniter\Model;

class AmbassadorModel extends Model
{
    protected $table = 'ambassadors';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;

    // `id`, `name`, `email`, `ref_code`, `program_id`, `institution`, `gender`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $allowedFields = [
        'name',
        'email',
        'ref_code',
        'program_id',
        'institution',
        'gender',
        'notes',
        'phone_number',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $useSoftDeletes = false; // Using is_deleted field manually

    // sign in ambassador by email and ref_code
    public function signIn($email, $refCode)
    {
        $builder = $this->builder();

        // get data
        $builder->select('*');

        // Execute the query and get the result as an object
        $result  = $builder->where('email', $email)->where('ref_code', $refCode)->get()->getRow();

        // check if result is empty
        if (empty($result)) {
            return null;
        } else {
            return $result;
        }
    }

    // get ambassador by ref code
    public function getByRefCode($refCode)
    {
        $builder = $this->builder();

        // get data
        $builder->select('*');

        // Execute the query and get the result as an object
        $result  = $builder->where('ref_code', $refCode)->get()->getRow();

        // check if result is empty
        if (empty($result)) {
            return null;
        } else {
            return $result;
        }
    }

    // get ambassador statistics
    public function getAmbassadorStats($programId)
    {
        $builder = $this->builder();

        // Get basic ambassador statistics - fix the deleted ambassadors query
        $builder->select('COUNT(*) as total_ambassadors, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_ambassadors, SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_ambassadors');

        // Execute the query and get the result as an array
        $result = $builder->where('program_id', $programId)->where('is_deleted', 0)->get()->getRowArray();

        // Check if result is empty
        if (empty($result)) {
            return [
                'total_ambassadors' => 0,
                'active_ambassadors' => 0,
                'inactive_ambassadors' => 0,
                'total_referrals' => 0
            ];
        }

        // Get total referrals count using the comprehensive referral counting method
        $referralCounts = $this->getComprehensiveReferralCounts($programId);
        $totalReferrals = array_sum($referralCounts);

        // Add total referrals to the result
        $result['total_referrals'] = $totalReferrals;

        return $result;
    }

    // get referrals by program id
    public function getReferrals($programId)
    {
        $builder = $this->builder();

        // get data
        $builder->select('*');

        // Execute the query and get the result as an array of objects
        $result  = $builder->where('program_id', $programId)->get()->getResultArray();

        // check if result is empty
        if (empty($result)) {
            return null;
        } else {
            return $result;
        }
    }

    // get ambassador by id
    public function getById($id)
    {
        $builder = $this->builder();

        // get data
        $builder->select('*');

        // Execute the query and get the result as an array of objects
        $result  = $builder->where('id', $id)->get()->getRowArray();

        // check if result is empty
        if (empty($result)) {
            return null;
        } else {
            return $result;
        }
    }

    // get all ambassadors with mandatory program_id filtering for security
    public function getAmbassadors($limit = 10, $offset = 0, $filters = [])
    {
        $builder = $this->builder();

        // Ensure program_id filter is always present for security
        if (!isset($filters['program_id'])) {
            log_message('warning', 'getAmbassadors called without program_id filter - potential security issue');
            return null;
        }

        // Apply filters dynamically
        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                $builder->whereIn($key, $value);
            } else {
                $builder->where($key, $value);
            }
        }

        // Always filter out deleted records for security
        if (!isset($filters['is_deleted'])) {
            $builder->where('is_deleted', 0);
        }

        // get data
        $builder->select('*');

        // get total count before pagination
        $total = $builder->countAllResults(false);

        // apply pagination
        $builder->limit($limit, $offset);

        // Execute the query and get the result as an array of objects
        $result  = $builder->get()->getResultArray();

        // check if result is empty
        if (empty($result)) {
            return [
                'data' => [],
                'total' => 0
            ];
        } else {
            return [
                'data' => $result,
                'total' => $total
            ];
        }
    }

    // get ambassador by id
    public function getAmbassadorByRefCodeAndProgramId($refCode, $programId)
    {
        $builder = $this->builder();

        // get data
        $builder->select('*');

        // Execute the query and get the result as an array of objects
        $result  = $builder->where('ref_code', $refCode)->where('program_id', $programId)->get()->getRow();

        // check if result is empty
        if (empty($result)) {
            return null;
        } else {
            return $result;
        }
    }

    // get referred participants by ambassador id
    public function getReferredParticipants($limit = 10, $offset = 0, $filters = [])
    {
        $builder = $this->builder();

        // get data
        $builder->select('*');

        // get ambassador ref code
        $refCode = $filters['ref_code_ambassador'];

        // get ambassador by ref code
        $builder->where('ref_code', $refCode);

        // Execute the query and get the result as an array of objects
        $result  = $builder->get()->getRowArray();

        // Load the ParticipantModel
        $participantModel = new ParticipantModel();

        // Get participants referred by the ambassador
        $participants = $participantModel->getParticipants($limit, $offset, $filters);

        // check if result is empty
        if (empty($participants)) {
            $participants = [];
        }

        $total = count($participants);

        $result['participants'] = $participants['data'];

        return [
            'data' => $result,
            'total' => $total
        ];
    }

    // get ambassador by id
    public function getAmbassadorById($id)
    {
        $builder = $this->builder();

        // get data
        $builder->select('*');

        // Execute the query and get the result as an array of objects
        $result  = $builder->where('id', $id)->get()->getRow();

        // check if result is empty
        if (empty($result)) {
            return null;
        } else {
            return $result;
        }
    }

    /**
     * Get comprehensive referral counts for ambassadors
     * Combines both old data (from participants.ref_code_ambassador) and new data (from ambassador_participant_referrals)
     * 
     * @param int $programId The program ID
     * @param int|null $ambassadorId Optional ambassador ID to filter for a specific ambassador
     * @return array Array with ambassador IDs as keys and total referral counts as values
     */
    public function getComprehensiveReferralCounts($programId, $ambassadorId = null)
    {
        log_message('debug', 'getComprehensiveReferralCounts called with programId: ' . $programId . ', ambassadorId: ' . ($ambassadorId ?? 'null'));
        
        // Initialize return array
        $referralCounts = [];

        $db = \Config\Database::connect();

        // Step 1: Get all relevant ambassadors
        $ambassadorBuilder = $this->builder();
        $ambassadorBuilder->select('id, ref_code')
            ->where('program_id', $programId)
            ->where('is_deleted', 0);

        if ($ambassadorId !== null) {
            $ambassadorBuilder->where('id', $ambassadorId);
        }

        $ambassadors = $ambassadorBuilder->get()->getResult();
        log_message('debug', 'Found ambassadors: ' . count($ambassadors));

        // Step 2: Prepare ID to ref_code mapping for later use
        $ambassadorCodes = [];
        foreach ($ambassadors as $ambassador) {
            $referralCounts[$ambassador->id] = 0; // Initialize count
            $ambassadorCodes[$ambassador->ref_code] = $ambassador->id;
        }

        // If no ambassadors found, return empty array
        if (empty($ambassadors)) {
            log_message('debug', 'No ambassadors found, returning empty array');
            return [];
        }

        // Step 3: Get counts from new structure (ambassador_participant_referrals table)
        // Instead of complex join, use simple whereIn with ambassador IDs
        $ambassadorIds = array_column($ambassadors, 'id');
        log_message('debug', 'Ambassador IDs: ' . json_encode($ambassadorIds));
        
        if (!empty($ambassadorIds)) {
            $newReferralsBuilder = $db->table('ambassador_participant_referrals');
            $newReferralsBuilder->select('ambassador_id, COUNT(*) as count')
                ->where('is_deleted', 0)
                ->whereIn('ambassador_id', $ambassadorIds);

            if ($ambassadorId !== null) {
                $newReferralsBuilder->where('ambassador_id', $ambassadorId);
            }

            $newReferralsBuilder->groupBy('ambassador_id');
            $newReferrals = $newReferralsBuilder->get()->getResult();
            log_message('debug', 'New referrals found: ' . count($newReferrals));

            // Add new referral counts to the result array
            foreach ($newReferrals as $referral) {
                if (isset($referralCounts[$referral->ambassador_id])) {
                    $referralCounts[$referral->ambassador_id] += $referral->count;
                }
            }
        }

        // Step 4: Get counts from old structure (participants.ref_code_ambassador field)
        $refCodes = array_keys($ambassadorCodes);
        log_message('debug', 'Ref codes: ' . json_encode($refCodes));

        if (!empty($refCodes)) {
            $oldReferralsBuilder = $db->table('participants');
            $oldReferralsBuilder->select('ref_code_ambassador, COUNT(*) as count')
                ->where('program_id', $programId)
                ->where('is_deleted', 0)
                ->whereIn('ref_code_ambassador', $refCodes)
                ->whereNotIn('id', function ($subquery) {
                    // Exclude participants that already exist in the new structure
                    return $subquery->select('participant_id')
                        ->from('ambassador_participant_referrals')
                        ->where('is_deleted', 0);
                })
                ->groupBy('ref_code_ambassador');

            $oldReferrals = $oldReferralsBuilder->get()->getResult();
            log_message('debug', 'Old referrals found: ' . count($oldReferrals));

            // Add old referral counts to the result array
            foreach ($oldReferrals as $referral) {
                if (!empty($referral->ref_code_ambassador) && isset($ambassadorCodes[$referral->ref_code_ambassador])) {
                    $ambassadorId = $ambassadorCodes[$referral->ref_code_ambassador];
                    $referralCounts[$ambassadorId] += $referral->count;
                }
            }
        }

        log_message('debug', 'Final referral counts: ' . json_encode($referralCounts));
        return $referralCounts;
    }

    /**
     * Get comprehensive list of participant IDs referred by an ambassador
     * Combines both old data (from participants.ref_code_ambassador) and new data (from ambassador_participant_referrals)
     * 
     * @param int $programId The program ID (optional)
     * @param int|null $ambassadorId The ambassador ID (required)
     * @return array Array of participant IDs
     */
    public function getComprehensiveReferralParticipantIds($ambassadorId, $programId = null)
    {
        if (!$ambassadorId) {
            return [];
        }
        
        $participantIds = [];
        $db = \Config\Database::connect();
        
        // Get the ambassador's reference code
        $ambassador = $this->find($ambassadorId);
        if (!$ambassador) {
            return [];
        }
        $refCode = $ambassador->ref_code;
        
        // Step 1: Get participant IDs from the new structure (ambassador_participant_referrals table)
        $newReferralsBuilder = $db->table('ambassador_participant_referrals');
        $newReferralsBuilder->select('ambassador_participant_referrals.participant_id')
            ->where('ambassador_participant_referrals.ambassador_id', $ambassadorId)
            ->where('ambassador_participant_referrals.is_deleted', 0);
            
        if ($programId !== null) {
            // Join with participants table to filter by program_id
            $newReferralsBuilder->join('participants', 'participants.id = ambassador_participant_referrals.participant_id')
                ->where('participants.program_id', $programId)
                ->where('participants.is_deleted', 0);
        } 
            
        $newReferrals = $newReferralsBuilder->get()->getResult();
        
        // Add new referral participant IDs to the result array
        foreach ($newReferrals as $referral) {
            if (!in_array($referral->participant_id, $participantIds)) {
                $participantIds[] = $referral->participant_id;
            }
        }
        
        // Step 2: Get participant IDs from the old structure (participants.ref_code_ambassador field)
        $oldReferralsBuilder = $db->table('participants');
        $oldReferralsBuilder->select('id')
            ->where('ref_code_ambassador', $refCode)
            ->where('is_deleted', 0);
            
        if ($programId !== null) {
            $oldReferralsBuilder->where('program_id', $programId);
        }
            
        // Exclude participants that already exist in the new structure to avoid duplicates
        if (!empty($participantIds)) {
            $oldReferralsBuilder->whereNotIn('id', $participantIds);
        }
            
        $oldReferrals = $oldReferralsBuilder->get()->getResult();
        
        // Add old referral participant IDs to the result array
        foreach ($oldReferrals as $referral) {
            if (!in_array($referral->id, $participantIds)) {
                $participantIds[] = $referral->id;
            }
        }
        
        return $participantIds;
    }

    /**
     * Get individual ambassador details with referral count
     * 
     * @param int $ambassadorId
     * @return object Ambassador details with stats
     */
    public function getAmbassadorDetails($ambassadorId)
    {
        $ambassador = $this->find($ambassadorId);
        
        if (!$ambassador) {
            return null;
        }

        // Get referral count using existing method
        $referralParticipantIds = $this->getComprehensiveReferralParticipantIds($ambassadorId, $ambassador->program_id);
        $referralCount = count($referralParticipantIds);

        return (object) [
            'id' => $ambassador->id,
            'name' => $ambassador->name,
            'full_name' => $ambassador->name, // Add alias for consistency
            'email' => $ambassador->email,
            'ref_code' => $ambassador->ref_code,
            'institution' => $ambassador->institution,
            'program_id' => $ambassador->program_id,
            'referral_count' => $referralCount
        ];
    }
}
