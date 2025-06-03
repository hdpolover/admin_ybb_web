<?php

namespace App\Models;

use CodeIgniter\Model;

class AbstractReviewerModel extends Model
{
    // `id`, `program_id`, `name`, `email`, `institution`, `password`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table = 'abstract_reviewers';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $fillable = [
        'id',
        'program_id',
        'name',
        'email',
        'institution',
        'password',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $allowedFields = [
        'program_id',
        'name',
        'email',
        'institution',
        'password',
        'is_active',
        'is_deleted',
        'is_participant',
    ];

    protected $validationRules = [
        'program_id' => 'required|integer',
        'name' => 'required|string|max_length[100]',
        'email' => 'required|string|valid_email|max_length[100]',
        'institution' => 'required|string|max_length[100]',
        'password' => 'required|string|min_length[6]|max_length[100]',
        'is_active' => 'in_list[0,1]',
        'is_deleted' => 'in_list[0,1]',
    ];

    /**
     * Get reviewer by email
     *
     * @param string $email
     * @return object|null
     */
    public function getReviewerByEmail($email)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('email', $email)
            ->where('is_deleted', 0)
            ->where('is_active', 1);

        return $builder->get()->getRow();
    }

    /**
     * Get reviewers by program ID
     *
     * @param int $programId
     * @return array
     */

    public function getReviewersByProgramId($programId)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->where('is_active', 1);

        return $builder->get()->getResult();
    }

    /**
     * Get all active reviewers
     *
     * @return array
     */
    public function getAllActiveReviewers()
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('is_deleted', 0)
            ->where('is_active', 1);

        return $builder->get()->getResult();
    }

    /**
     * Get reviewer by ID
     *
     * @param int $id
     * @return object|null
     */
    public function getReviewerById($id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('id', $id)
            ->where('is_deleted', 0)
            ->where('is_active', 1);

        return $builder->get()->getRow();
    }

    /**
     * Create a new reviewer
     *
     * @param array $data
     * @return int|false
     */
    public function createReviewer(array $data)
    {
        // Validate data
        if (!$this->validate($data)) {
            return false;
        }

        // Insert data
        $this->insert($data);
        return $this->insertID();
    }

    /**
     * Update reviewer by ID
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateReviewerById($id, array $data)
    {
        // Validate data
        if (!$this->validate($data)) {
            return false;
        }

        // Update data
        $this->update($id, $data);
        return true;
    }
    /**
     * Delete reviewer by ID
     *
     * @param int $id
     * @return bool
     */
    public function deleteReviewerById($id)
    {
        // Check if reviewer exists
        $reviewer = $this->getReviewerById($id);
        if (!$reviewer) {
            return false;
        }

        // Soft delete reviewer
        $this->update($id, ['is_deleted' => 1]);
        return true;
    }
    
}