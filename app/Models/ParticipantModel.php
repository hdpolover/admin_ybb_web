<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantModel extends Model
{
    protected $table = 'participants';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // `id`, `user_id`, `account_id`, `full_name`, `birthdate`, `ref_code_ambassador`, `program_id`, `gender`, `origin_address`, `current_address`, `nationality`, `occupation`, `institution`, `organizations`, `country_code`, `phone_number`, `picture_url`, `instagram_account`, `emergency_account`, `contact_relation`, `disease_history`, `tshirt_size`, `category`, `experiences`, `achievements`, `resume_url`, `knowledge_source`, `source_account_name`, `twibbon_link`, `requirement_link`, `is_active`, `is_deleted`, `created_at`, `updated_at
    protected $allowedFields = [
        'name',
        'email',
        'phone',
        'registration_date',
        'status'
    ];

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
}
