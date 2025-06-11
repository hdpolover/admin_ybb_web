<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewerModel extends Model
{
    protected $table = 'reviewers';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'name',
        'email',
        'password',
        'role',
        'specialization',
        'is_active',
        'is_deleted'
    ];

    protected $hidden = [
        'password'
    ];

    /**
     * Get reviewer by email
     * 
     * @param string $email
     * @return object|null
     */
    public function getReviewerByEmail($email)
    {
        return $this->where('email', $email)
                   ->where('is_deleted', 0)
                   ->first();
    }

    /**
     * Reviewer sign in
     * 
     * @param string $email
     * @param string $password
     * @return object|false
     */
    public function signIn($email, $password)
    {
        $reviewer = $this->getReviewerByEmail($email);

        if ($reviewer && $reviewer->is_active && password_verify($password, $reviewer->password)) {
            return $reviewer;
        }

        return false;
    }

    /**
     * Get active reviewers
     * 
     * @param int $limit
     * @param int $offset
     * @param array $filters
     * @return array
     */
    public function getReviewers($limit = 10, $offset = 0, $filters = [])
    {
        $builder = $this->builder();

        // Default filter for active and non-deleted
        $builder->where('is_active', 1);
        $builder->where('is_deleted', 0);

        // Apply additional filters if any
        if (!empty($filters)) {
            $builder->where($filters);
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        // Apply pagination
        $builder->limit($limit, $offset);

        // Select fields (exclude password for security)
        $builder->select('id, name, email, role, specialization, is_active, created_at, updated_at');

        $data = $builder->get()->getResult();

        return [
            'data' => $data,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    /**
     * Create new reviewer
     * 
     * @param array $data
     * @return int|false
     */
    public function createReviewer($data)
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Set default role if not provided
        if (!isset($data['role'])) {
            $data['role'] = 'reviewer';
        }

        return $this->insert($data);
    }

    /**
     * Update reviewer
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateReviewer($id, $data)
    {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // Remove empty password field
            unset($data['password']);
        }

        return $this->update($id, $data);
    }

    /**
     * Soft delete reviewer
     * 
     * @param int $id
     * @return bool
     */
    public function deleteReviewer($id)
    {
        return $this->update($id, ['is_deleted' => 1, 'is_active' => 0]);
    }

    /**
     * Activate/deactivate reviewer
     * 
     * @param int $id
     * @param bool $status
     * @return bool
     */
    public function setActiveStatus($id, $status)
    {
        return $this->update($id, ['is_active' => $status ? 1 : 0]);
    }

    /**
     * Get reviewers by specialization
     * 
     * @param string $specialization
     * @return array
     */
    public function getReviewersBySpecialization($specialization)
    {
        return $this->where('specialization LIKE', "%{$specialization}%")
                   ->where('is_active', 1)
                   ->where('is_deleted', 0)
                   ->findAll();
    }
}
