<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewerModel extends Model
{    protected $table = 'abstract_reviewers';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'program_id',
        'name',
        'email',
        'institution',
        'password',
        'role',
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
    }    /**
     * Get reviewers by specialization
     * 
     * @param string $specialization
     * @return array
     */
    public function getReviewersBySpecialization($specialization)
    {
        return $this->where('institution LIKE', "%{$specialization}%")
                   ->where('is_active', 1)
                   ->where('is_deleted', 0)
                   ->findAll();
    }

    /**
     * Update reviewer profile
     * 
     * @param int $reviewer_id
     * @param array $data
     * @return bool
     */
    public function updateProfile($reviewer_id, $data)
    {
        // Remove password from profile update if empty
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        
        return $this->update($reviewer_id, $data);
    }

    /**
     * Change reviewer password
     * 
     * @param int $reviewer_id
     * @param string $new_password
     * @return bool
     */
    public function changePassword($reviewer_id, $new_password)
    {
        return $this->update($reviewer_id, ['password' => password_hash($new_password, PASSWORD_DEFAULT)]);
    }

    /**
     * Get reviewer statistics
     * 
     * @param int $reviewer_id
     * @return array
     */
    public function getReviewerStatistics($reviewer_id)
    {
        // Get review statistics for the reviewer
        $db = \Config\Database::connect();
        
        $builder = $db->table('abstract_feedbacks');
        $total_assigned = $builder->where('abstract_reviewer_id', $reviewer_id)
                                 ->where('is_deleted', 0)
                                 ->countAllResults();
        
        $builder = $db->table('abstract_feedbacks');
        $total_completed = $builder->where('abstract_reviewer_id', $reviewer_id)
                                 ->where('feedback IS NOT NULL')
                                 ->where('feedback !=', '')
                                 ->where('is_deleted', 0)
                                 ->countAllResults();
        
        $builder = $db->table('abstract_feedbacks');
        $total_pending = $builder->where('abstract_reviewer_id', $reviewer_id)
                               ->where('(feedback IS NULL OR feedback = "")')
                               ->where('is_deleted', 0)
                               ->countAllResults();

        return [
            'total_assigned' => $total_assigned,
            'total_completed' => $total_completed,
            'total_pending' => $total_pending,
            'completion_rate' => $total_assigned > 0 ? round(($total_completed / $total_assigned) * 100, 2) : 0
        ];
    }

    /**
     * Get reviewers by program
     * 
     * @param int $program_id
     * @return array
     */
    public function getReviewersByProgram($program_id)
    {
        return $this->where('program_id', $program_id)
                   ->where('is_active', 1)
                   ->where('is_deleted', 0)
                   ->findAll();
    }
}
