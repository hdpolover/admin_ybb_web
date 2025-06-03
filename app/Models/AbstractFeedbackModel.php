<?php

namespace App\Models;

use CodeIgniter\Model;

class AbstractFeedbackModel extends Model
{
    // `id`, `abstract_version_id`, `abstract_reviewer_id`, `feedback`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table = 'abstract_feedback';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $fillable = [
        'id',
        'abstract_version_id',
        'abstract_reviewer_id',
        'feedback',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $allowedFields = [
        'abstract_version_id',
        'abstract_reviewer_id',
        'feedback',
        'is_active',
        'is_deleted',
        'is_participant',
    ];

    protected $validationRules = [
        'abstract_version_id' => 'required|integer',
        'abstract_reviewer_id' => 'required|integer',
        'feedback' => 'required|string|max_length[1000]',
        'is_active' => 'in_list[0,1]',
        'is_deleted' => 'in_list[0,1]',
    ];

    /**
     * Get feedback by abstract version ID
     *
     * @param int $abstractVersionId
     * @return object|null
     */
    public function getFeedbackByAbstractVersionId($abstractVersionId)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('abstract_version_id', $abstractVersionId)
            ->where('is_deleted', 0)
            ->where('is_active', 1);

        return $builder->get()->getRow();
    }

    /**
     * Get all feedback for a specific abstract version
     *
     * @param int $abstractVersionId
     * @return array
     */
    public function getAllFeedbackByAbstractVersionId($abstractVersionId)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('abstract_version_id', $abstractVersionId)
            ->where('is_deleted', 0)
            ->where('is_active', 1);

        return $builder->get()->getResult();
    }

    /**
     * Get feedback by reviewer ID
     *
     * @param int $reviewerId
     * @return array
     */
    public function getFeedbackByReviewerId($reviewerId)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('abstract_reviewer_id', $reviewerId)
            ->where('is_deleted', 0)
            ->where('is_active', 1);

        return $builder->get()->getResult();
    }
    
}