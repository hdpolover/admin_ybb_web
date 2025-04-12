<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantModel extends Model
{
    protected $table = 'participants';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // `id`, `user_id`, `account_id`, `full_name`, `birthdate`, `ref_code_ambassador`, `program_id`, `gender`, `origin_address`, `current_address`, `nationality`, `occupation`, `institution`, `organizations`, `country_code`, `phone_number`, `picture_url`, `instagram_account`, `emergency_account`, `contact_relation`, `disease_history`, `tshirt_size`, `category`, `experiences`, `achievements`, `resume_url`, `knowledge_source`, `source_account_name`, `twibbon_link`, `requirement_link`, `is_active`, `is_deleted`, `created_at`, `updated_at
    protected $allowedFields = [
        'user_id',
        'account_id',
        'full_name',
        'program_id',
        'gender',
        'birthdate',
        'origin_address',
        'current_address',
        'nationality',
        'nationality_flag',
        'nationality_code',
        'occupation',
        'education_level',
        'major',
        'institution',
        'organizations',
        'country_code',
        'phone_flag',
        'phone_number',
        'picture_url',
        'instagram_account',
        'emergency_account',
        'emergency_country_code',
        'emergency_phone_flag',
        'contact_relation',
        'disease_history', 
        'tshirt_size',
        'category',
        'experiences',
        'achievements',
        'resume_url',
        'knowledge_source',
        'source_account_name',
        'twibbon_link',
        'requirement_link',
        'is_active',
        'is_deleted'
    ];

    // get by id
    public function getById($id)
    {
        // join participant data with user, program, payment, essay, subtheme etc
        $builder = $this->builder();
        $builder->select('participants.*, users.*, programs.*, payments.*, participant_essays.*')
                ->join('users', 'users.id = participants.user_id', 'left')
                ->join('programs', 'programs.id = participants.program_id', 'left')
                ->join('payments', 'payments.participant_id = participants.id', 'left')
                ->join('participant_essays', 'participant_essays.participant_id = participants.id', 'left')
                ->where('participants.id', $id)
                ->where('participants.is_active', 1)
                ->where('participants.is_deleted', 0);

        $result = $builder->get()->getRow();

        if ($result) {
            // Convert to array and return
            return (array)$result;
        } else {
            return null; // No result found
        }
    }

    /**
     * Get photos of participants for a specific program
     *
     * @param int $programId The program ID
     * @param int $limit Maximum number of photos to return
     * @return array List of participant picture URLs
     */
    public function getProgramParticipantsPhotos($programId, $limit = 5)
    {
        $builder = $this->builder();
        
        $result = $builder->select('picture_url')
                          ->where('program_id', $programId)
                          ->where('is_active', 1)
                          ->where('is_deleted', 0)
                          ->where('picture_url IS NOT NULL')
                          ->where('picture_url !=', '')
                          ->orderBy('RAND()')  // Random order
                          ->limit($limit)
                          ->get()
                          ->getResultArray();
        
        return array_column($result, 'picture_url');
    }

    // get participants by user id
    public function getParticipantsByUserId($userId)
    {
        // there can be more than one participant with the same user id
        // so we need to return all participants with the same user id
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * Get all participants with pagination
     *
     * @param int $limit Number of records to return
     * @param int $offset Starting position for query
     * @param array $filters Optional filters for the query
     * @return array Array containing data and total count
     */
    public function getAllParticipants($limit = 10, $offset = 0, $filters = [])
    {
        $builder = $this->builder();

        // Apply filters if provided
        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                $builder->whereIn($key, $value);
            } else {
                $builder->where($key, $value);
            }
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        // Apply pagination
        $builder->limit($limit, $offset);

        // Get results
        $participants = $builder->get()->getResult();

        return [
            'data' => $participants,
            'total' => $total
        ];
    }

    /**
     * Get participants by program ID
     *
     * @param int $programId
     * @return array
     */
    public function getParticipantsByProgramId($programId)
    {
        return $this->where('program_id', $programId)->findAll();
    }

    /**
     * Get participant by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getParticipant($id)
    {
        return $this->find($id);
    }

    public function getParticipants($limit = 10, $offset = 0, $filters = [])
    {
        $builder = $this->builder();

        // Apply filters dynamically
        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                $builder->whereIn($key, $value);
            } else {
                $builder->where($key, $value);
            }
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        // Apply pagination
        $builder->limit($limit, $offset);

        // Select all fields
        $builder->select('*');

        // Execute query
        $result = $builder->get()->getResultArray();

        $participants = [];

        // Map to entities
        foreach ($result as $row) {
            $tempParticipant = $row;

            $userId = $row['user_id'];

            // set user
            $userModel =  new UserModel();

            $user = $userModel->find($userId);

            $tempParticipant['user'] = $user;

            // set essay
            $participantEssayModel = new ParticipantEssayModel();

            $participantEssay = $participantEssayModel->getParticipantEssayByParticipantId($row['id']);

            $tempParticipant['essays'] = $participantEssay;

            // set payments
            $paymentModel = new PaymentModel();

            $payments = $paymentModel->getPayments($row['id']);

            $tempParticipant['payments'] = $payments;

            $participants[] = $tempParticipant;
        }

        return [
            'data' => $participants,
            'total' => $total
        ];
    }

    /**
     * Get participants for the current program
     *
     * @param int $limit Number of records to return
     * @param int $offset Starting position for query
     * @param array $filters Optional additional filters
     * @return array Array containing data and total count
     */
    public function getCurrentProgramParticipants($limit = 10, $offset = 0, $filters = [])
    {
        $session = session();
        $currentProgramId = $session->get('current_program');

        if (!$currentProgramId) {
            return [
                'data' => [],
                'total' => 0
            ];
        }

        // Merge program_id filter with other filters
        $filters['program_id'] = $currentProgramId;

        // Use existing getParticipants method with the program filter
        return $this->getParticipants($limit, $offset, $filters);
    }

    // get participant by params
    public function getParticipantByParams($params)
    {
        $builder = $this->builder();

        // Apply filters dynamically
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $builder->whereIn($key, $value);
            } else {
                $builder->where($key, $value);
            }
        }

        // Select all fields
        $builder->select('*');

        // Execute query and return one result
        return $builder->get()->getRow();
    }

    public function createParticipant($data)
    {
        // Validate input data
        if (empty($data['user_id']) || empty($data['program_id']) || empty($data['full_name'])) {
            throw new \InvalidArgumentException('Missing required fields: user_id, program_id, full_name');
        }

        // Set default values
        $data['account_id'] = $this->generateAccountId($data['user_id']);
        $data['is_active'] = 1;
        $data['is_deleted'] = 0;

        // Insert participant data
        $this->save($data);
        
        // Return the complete participant object
        return $this->find($this->insertID());
    }

    public function generateAccountId($userId)
    {
        // Generate a unique account ID with uniqid() from user id
        $accountId = uniqid($userId);

        // Check if the account ID already exists
        $existingAccount = $this->where('account_id', $accountId)->first();

        if ($existingAccount) {
            // If it exists, generate a new one
            return $this->generateAccountId($userId);
        }

        // If it doesn't exist, return the new account ID
        return $accountId;
    }

    /**
     * Get all programs that a user is participating in
     *
     * @param int $userId The user ID
     * @return array List of programs the user is participating in
     */
    public function getUserPrograms($userId)
    {
        if (empty($userId)) {
            return [];
        }
        
        // First get all participants entries for this user
        $participants = $this->where('user_id', $userId)
                             ->where('is_active', 1)
                             ->where('is_deleted', 0)
                             ->findAll();
        
        if (empty($participants)) {
            return [];
        }
        
        // Extract program IDs
        $programIds = [];
        foreach ($participants as $participant) {
            $programIds[] = $participant->program_id;
        }
        
        // Get program details
        $programModel = new \App\Models\ProgramModel();
        $programs = $programModel->whereIn('id', $programIds)
                                ->where('is_active', 1)
                                ->where('is_deleted', 0)
                                ->findAll();
        
        return $programs;
    }

    public function getTotalParticipants($programId)
    {
        return $this->where('program_id', $programId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->countAllResults();
    }

    public function getTotalCountries($programId)
    {
        return $this->select('COUNT(DISTINCT nationality) as total_countries')
                    ->where('program_id', $programId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->get()
                    ->getRow()
                    ->total_countries;
    }

    public function getCountriesData($programId)
    {
        return $this->select('nationality, COUNT(*) as participants_count')
            ->where('program_id', $programId)
            ->groupBy('nationality')
            ->orderBy('participants_count', 'DESC')
            ->findAll();
    }
}