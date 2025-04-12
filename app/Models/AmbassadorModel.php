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
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $useSoftDeletes = false; // Using is_deleted field manually

    // get ambassador statistics
    public function getAmbassadorStats($programId)
    {
        $builder = $this->builder();

        // get data
        $builder->select('COUNT(*) as total_ambassadors, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_ambassadors, SUM(CASE WHEN is_deleted = 1 THEN 1 ELSE 0 END) as deleted_ambassadors');

        // Execute the query and get the result as an array of objects
        $result  = $builder->where('program_id', $programId)->get()->getRowArray();

        // check if result is empty
        if (empty($result)) {
            return null;
        } else {
            return $result;
        }
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

    // get all ambassadors
    public function getAmbassadors($limit = 10, $offset = 0, $filters = [])
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
            return null;
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
}
