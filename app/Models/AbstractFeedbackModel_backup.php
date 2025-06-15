<?php

namespace App\Models;

use CodeIgniter\Model;

class AbstractFeedbackModel extends Model
{
    protected $table = 'abstract_feedbacks';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'abstract_version_id',
        'abstract_reviewer_id',
        'feedback',
        'is_active',
        'is_deleted'
    ];    /**
     * Get available abstracts for a reviewer based on their assigned subthemes
     * 
     * @param int $reviewer_id
     * @param array $filters
     * @return array
     */
    public function getFeedbacksByReviewer($reviewer_id, $filters = [])
    {
        $builder = $this->db->table('abstracts a');
        $builder->select('a.id as abstract_id, a.title as abstract_title, a.status as abstract_status,
                         a.active_version_id, a.created_at as submission_date,
                         av.id as version_id, av.title as version_title, av.abstract as abstract_content,
                         av.keywords, p.name as participant_name, 
                         prog.name as program_name, ps.name as subtheme_name,
                         af.id as feedback_id, af.feedback, af.created_at as feedback_created_at,
                         af.updated_at as feedback_updated_at');
        
        // Join with active version
        $builder->join('abstract_versions av', 'av.id = a.active_version_id');
        $builder->join('participants p', 'p.id = a.participant_id');
        $builder->join('programs prog', 'prog.id = a.program_id');
        
        // Join with participant subthemes to get the abstract's subtheme
        $builder->join('participant_subthemes ps_link', 'ps_link.participant_id = a.participant_id');
        $builder->join('program_subthemes ps', 'ps.id = ps_link.program_subtheme_id');
          // Join with reviewer to get program_id
        $builder->join('abstract_reviewers ar', "ar.id = {$reviewer_id}");
        
        // Join with reviewer subthemes to ensure reviewer is assigned to this subtheme
        $builder->join('abstract_reviewer_subthemes ars', 
                      'ars.abstract_reviewer_id = ar.id AND ars.program_subtheme_id = ps_link.program_subtheme_id');
        
        // Left join with feedback to see if reviewer has already provided feedback
        $builder->join('abstract_feedbacks af', 
                      "af.abstract_version_id = av.id AND af.abstract_reviewer_id = {$reviewer_id}", 'left');
        
        // Security and status constraints
        $builder->where('ar.id', $reviewer_id);
        $builder->where('ar.is_active', 1);
        $builder->where('ar.is_deleted', 0);
        $builder->where('ars.is_active', 1);
        $builder->where('ars.is_deleted', 0);
        $builder->where('ps_link.is_active', 1);
        $builder->where('ps_link.is_deleted', 0);
        
        // Ensure abstract belongs to reviewer's program
        $builder->where('a.program_id = ar.program_id');
        
        // Exclude draft abstracts - only show submitted, under_review, accepted
        $builder->whereIn('a.status', ['submitted', 'under_review', 'accepted']);
        
        // Apply filters
        if (isset($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $builder->where('af.feedback IS NOT NULL');
                $builder->where('af.feedback !=', '');
            } elseif ($filters['status'] === 'pending') {
                $builder->where('(af.feedback IS NULL OR af.feedback = "")');
            }
        }
        
        if (isset($filters['program_id'])) {
            $builder->where('a.program_id', $filters['program_id']);
        }
        
        if (isset($filters['abstract_status'])) {
            $builder->where('a.status', $filters['abstract_status']);
        }
        
        $builder->orderBy('a.created_at', 'DESC');
        
        return $builder->get()->getResult();
    }    /**
     * Get specific abstract details for a reviewer
     * 
     * @param int $abstract_id
     * @param int $reviewer_id
     * @return object|null
     */
    public function getFeedbackDetails($abstract_id, $reviewer_id)
    {
        $builder = $this->db->table('abstracts a');
        $builder->select('a.id as abstract_id, a.title as abstract_title, a.status as abstract_status,
                         a.active_version_id, a.created_at as submission_date,
                         av.id as version_id, av.title as version_title, av.abstract as abstract_content,
                         av.keywords, av.methodology, av.created_at as version_created_at,
                         p.id as participant_id, p.name as participant_name, 
                         p.email as participant_email, prog.id as program_id, prog.name as program_name, 
                         ps.id as subtheme_id, ps.name as subtheme_name,
                         af.id as feedback_id, af.feedback, af.created_at as feedback_created_at,
                         af.updated_at as feedback_updated_at');
        
        // Join with active version
        $builder->join('abstract_versions av', 'av.id = a.active_version_id');
        $builder->join('participants p', 'p.id = a.participant_id');
        $builder->join('programs prog', 'prog.id = a.program_id');
        
        // Join with participant subthemes to get the abstract's subtheme
        $builder->join('participant_subthemes ps_link', 'ps_link.participant_id = a.participant_id');
        $builder->join('program_subthemes ps', 'ps.id = ps_link.program_subtheme_id');
        
        // Join with reviewer to get program_id
        $builder->join('abstract_reviewers ar', "ar.id = {$reviewer_id}");
        
        // Join with reviewer subthemes to ensure reviewer is assigned to this subtheme
        $builder->join('abstract_reviewer_subthemes ars', 
                      'ars.abstract_reviewer_id = ar.id AND ars.program_subtheme_id = ps_link.program_subtheme_id');
        
        // Left join with feedback to see if reviewer has already provided feedback
        $builder->join('abstract_feedbacks af', 
                      "af.abstract_version_id = av.id AND af.abstract_reviewer_id = {$reviewer_id}", 'left');
        
        $builder->where('a.id', $abstract_id);
        $builder->where('ar.id', $reviewer_id);
        $builder->where('ar.is_active', 1);
        $builder->where('ar.is_deleted', 0);
        $builder->where('ars.is_active', 1);
        $builder->where('ars.is_deleted', 0);
        $builder->where('ps_link.is_active', 1);
        $builder->where('ps_link.is_deleted', 0);
        
        // Ensure abstract belongs to reviewer's program
        $builder->where('a.program_id = ar.program_id');
        
        // Exclude draft abstracts
        $builder->whereIn('a.status', ['submitted', 'under_review', 'accepted']);
        
        return $builder->get()->getRow();
    }    /**
     * Submit or update feedback for an abstract
     * 
     * @param int $abstract_id
     * @param int $reviewer_id
     * @param string $feedback_text
     * @return bool
     */
    public function submitFeedback($abstract_id, $reviewer_id, $feedback_text)
    {
        // Get the abstract details first
        $abstract = $this->getFeedbackDetails($abstract_id, $reviewer_id);
        
        if (!$abstract) {
            return false; // Abstract not found or no access
        }
        
        // Check if feedback already exists
        $existingFeedback = $this->where('abstract_version_id', $abstract->active_version_id)
                                ->where('abstract_reviewer_id', $reviewer_id)
                                ->where('is_deleted', 0)
                                ->first();
        
        if ($existingFeedback) {
            // Update existing feedback
            return $this->update($existingFeedback->id, ['feedback' => $feedback_text]);
        } else {
            // Create new feedback
            $data = [
                'abstract_version_id' => $abstract->active_version_id,
                'abstract_reviewer_id' => $reviewer_id,
                'feedback' => $feedback_text,
                'is_active' => 1
            ];
            
            return $this->insert($data) !== false;
        }
    }    /**
     * Get feedback statistics for a reviewer
     * 
     * @param int $reviewer_id
     * @return array
     */
    public function getReviewerStats($reviewer_id)
    {
        // Get available abstracts for this reviewer
        $available_abstracts = $this->getFeedbacksByReviewer($reviewer_id, []);
        $total_available = count($available_abstracts);
        
        // Count completed feedbacks
        $completed_feedbacks = array_filter($available_abstracts, function($abstract) {
            return !empty($abstract->feedback);
        });
        $total_completed = count($completed_feedbacks);
        
        // Count pending feedbacks
        $total_pending = $total_available - $total_completed;

        return [
            'total_assigned' => $total_available,
            'total_completed' => $total_completed,
            'total_pending' => $total_pending,
            'total_in_review' => 0, // Not applicable in this structure
            'completion_rate' => $total_available > 0 ? round(($total_completed / $total_available) * 100, 2) : 0
        ];
    }

    /**
     * Check if reviewer has access to specific abstract version
     * 
     * @param int $abstract_version_id
     * @param int $reviewer_id
     * @return bool
     */
    public function hasReviewAccess($abstract_version_id, $reviewer_id)
    {
        $builder = $this->db->table('abstract_feedbacks af');
        $builder->join('abstract_versions av', 'av.id = af.abstract_version_id');
        $builder->join('abstracts a', 'a.id = av.abstract_id');
        $builder->join('abstract_reviewers ar', 'ar.id = af.abstract_reviewer_id');
        $builder->join('abstract_reviewer_subthemes ars', 
                      'ars.abstract_reviewer_id = af.abstract_reviewer_id AND ars.program_subtheme_id = a.program_subtheme_id');
        
        $builder->where('af.abstract_version_id', $abstract_version_id);
        $builder->where('af.abstract_reviewer_id', $reviewer_id);
        $builder->where('af.is_deleted', 0);
        $builder->where('ar.is_active', 1);
        $builder->where('ar.is_deleted', 0);
        $builder->where('ars.is_active', 1);
        $builder->where('ars.is_deleted', 0);
        
        // Ensure abstract belongs to reviewer's program
        $builder->where('a.program_id = ar.program_id');
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Assign abstract version to reviewer (with validation)
     * 
     * @param int $abstract_version_id
     * @param int $reviewer_id
     * @return int|false
     */
    public function assignReview($abstract_version_id, $reviewer_id)
    {
        // First, verify the reviewer is eligible for this abstract
        $builder = $this->db->table('abstract_versions av');
        $builder->select('a.program_id, a.program_subtheme_id');
        $builder->join('abstracts a', 'a.id = av.abstract_id');
        $builder->where('av.id', $abstract_version_id);
        $abstract = $builder->get()->getRow();
        
        if (!$abstract) {
            return false; // Abstract version not found
        }
        
        // Check if reviewer belongs to the same program
        $reviewerBuilder = $this->db->table('abstract_reviewers');
        $reviewer = $reviewerBuilder->where('id', $reviewer_id)
                                   ->where('program_id', $abstract->program_id)
                                   ->where('is_active', 1)
                                   ->where('is_deleted', 0)
                                   ->get()->getRow();
        
        if (!$reviewer) {
            return false; // Reviewer not found or not in the same program
        }
        
        // Check if reviewer is assigned to the abstract's subtheme
        $subthemeBuilder = $this->db->table('abstract_reviewer_subthemes');
        $isAssigned = $subthemeBuilder->where('abstract_reviewer_id', $reviewer_id)
                                     ->where('program_subtheme_id', $abstract->program_subtheme_id)
                                     ->where('is_active', 1)
                                     ->where('is_deleted', 0)
                                     ->countAllResults() > 0;
        
        if (!$isAssigned) {
            return false; // Reviewer not assigned to this subtheme
        }
        
        // Check if assignment already exists
        $existing = $this->where('abstract_version_id', $abstract_version_id)
                        ->where('abstract_reviewer_id', $reviewer_id)
                        ->where('is_deleted', 0)
                        ->first();
        
        if ($existing) {
            return $existing->id; // Already assigned
        }
        
        // Create the assignment
        $data = [
            'abstract_version_id' => $abstract_version_id,
            'abstract_reviewer_id' => $reviewer_id,
            'is_active' => 1
        ];
        
        return $this->insert($data);
    }

    /**
     * Get feedbacks by abstract version
     * 
     * @param int $abstract_version_id
     * @return array
     */
    public function getFeedbacksByAbstractVersion($abstract_version_id)
    {
        $builder = $this->db->table('abstract_feedbacks af');
        $builder->select('af.*, ar.name as reviewer_name, ar.institution');
        $builder->join('abstract_reviewers ar', 'ar.id = af.abstract_reviewer_id');
        $builder->where('af.abstract_version_id', $abstract_version_id);
        $builder->where('af.is_deleted', 0);
        $builder->where('ar.is_deleted', 0);
        $builder->orderBy('af.created_at', 'ASC');
        
        return $builder->get()->getResult();
    }

    /**
     * Get available reviewers for a specific abstract
     * Only returns reviewers who are assigned to the abstract's subtheme and in the same program
     * 
     * @param int $abstract_id
     * @return array
     */
    public function getAvailableReviewersForAbstract($abstract_id)
    {
        $builder = $this->db->table('abstracts a');
        $builder->select('ar.id, ar.name, ar.email, ar.institution, 
                         COUNT(existing_af.id) as current_assignments');
        $builder->join('abstract_reviewers ar', 'ar.program_id = a.program_id');
        $builder->join('abstract_reviewer_subthemes ars', 
                      'ars.abstract_reviewer_id = ar.id AND ars.program_subtheme_id = a.program_subtheme_id');
        $builder->join('abstract_feedbacks existing_af', 
                      'existing_af.abstract_reviewer_id = ar.id', 'left');
        $builder->where('a.id', $abstract_id);
        $builder->where('ar.is_active', 1);
        $builder->where('ar.is_deleted', 0);
        $builder->where('ars.is_active', 1);
        $builder->where('ars.is_deleted', 0);
        $builder->groupBy('ar.id');
        $builder->orderBy('current_assignments', 'ASC'); // Prefer reviewers with fewer assignments
        $builder->orderBy('ar.name', 'ASC');
        
        return $builder->get()->getResult();
    }

    /**
     * Bulk assign multiple abstracts to a reviewer
     * 
     * @param array $abstract_version_ids
     * @param int $reviewer_id
     * @return array Results with success/failure for each assignment
     */
    public function bulkAssignReviews($abstract_version_ids, $reviewer_id)
    {
        $results = [];
        
        foreach ($abstract_version_ids as $abstract_version_id) {
            $result = $this->assignReview($abstract_version_id, $reviewer_id);
            $results[] = [
                'abstract_version_id' => $abstract_version_id,
                'success' => $result !== false,
                'assignment_id' => $result
            ];
        }
        
        return $results;
    }

    /**
     * Get reviewer workload statistics
     * 
     * @param int $program_id
     * @return array
     */
    public function getReviewerWorkloadStats($program_id)
    {
        $builder = $this->db->table('abstract_reviewers ar');
        $builder->select('ar.id, ar.name, ar.email, ar.institution,
                         COUNT(af.id) as total_assignments,
                         SUM(CASE WHEN af.feedback IS NOT NULL AND af.feedback != "" THEN 1 ELSE 0 END) as completed_reviews,
                         SUM(CASE WHEN af.feedback IS NULL OR af.feedback = "" THEN 1 ELSE 0 END) as pending_reviews');
        $builder->join('abstract_feedbacks af', 'af.abstract_reviewer_id = ar.id', 'left');
        $builder->where('ar.program_id', $program_id);
        $builder->where('ar.is_active', 1);
        $builder->where('ar.is_deleted', 0);
        $builder->where('(af.is_deleted = 0 OR af.is_deleted IS NULL)');
        $builder->groupBy('ar.id');
        $builder->orderBy('total_assignments', 'DESC');
        
        return $builder->get()->getResult();
    }
}
