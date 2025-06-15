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
    ];
    /**
     * Get available abstracts for a reviewer based on their assigned subthemes
     * Only shows submitted abstract versions that the reviewer can review
     * 
     * @param int $reviewer_id
     * @param array $filters
     * @return array
     */    public function getFeedbacksByReviewer($reviewer_id, $filters = [])
    {
        log_message('info', '=== getFeedbacksByReviewer START ===');
        log_message('info', 'Reviewer ID: ' . $reviewer_id);
        log_message('info', 'Filters: ' . json_encode($filters));

        $builder = $this->db->table('abstracts a');
        $builder->select('a.id as abstract_id, av.title as abstract_title, a.status as abstract_status,
                         a.active_version_id, a.created_at as submission_date,
                         av.id as version_id, av.title as version_title, av.content as abstract_content,
                         av.keywords, p.full_name as participant_name, 
                         prog.name as program_name, ps.name as subtheme_name,
                         af.id as feedback_id, af.feedback, af.created_at as feedback_created_at,
                         af.updated_at as feedback_updated_at, a.program_id');

        // Join with the active/latest version to get the most current submitted version
        $builder->join('abstract_versions av', 'av.id = a.active_version_id');
        $builder->join('participants p', 'p.id = a.primary_participant_id');
        $builder->join('programs prog', 'prog.id = a.program_id');

        // Join with program subthemes using the direct program_subtheme_id from abstracts table
        $builder->join('program_subthemes ps', 'ps.id = a.program_subtheme_id');

        // Join with reviewer to validate they belong to the same program
        $builder->join('abstract_reviewers ar', "ar.id = {$reviewer_id}");

        // CRITICAL: Only show abstracts where the reviewer is assigned to the abstract's subtheme
        $builder->join(
            'abstract_reviewer_subthemes ars',
            'ars.abstract_reviewer_id = ar.id AND ars.program_subtheme_id = a.program_subtheme_id'
        );

        // Left join with feedback to check if reviewer has already provided feedback
        $builder->join(
            'abstract_feedbacks af',
            "af.abstract_version_id = av.id AND af.abstract_reviewer_id = {$reviewer_id}",
            'left'
        );        // Security and validation constraints
        $builder->where('ar.id', $reviewer_id);
        $builder->where('ar.is_active', 1);
        $builder->where('ar.is_deleted', 0);
        $builder->where('ars.is_active', 1);
        $builder->where('ars.is_deleted', 0);
        $builder->where('av.is_active', 1);
        $builder->where('av.is_deleted', 0);
        $builder->where('a.is_deleted', 0);

        // Ensure abstract belongs to reviewer's program
        $builder->where('a.program_id = ar.program_id');        // MAIN REQUIREMENT: Show all abstracts except drafts
        $builder->where('a.status !=', 'draft'); // Apply additional filters
        if (isset($filters['subtheme_id']) && !empty($filters['subtheme_id'])) {
            $builder->where('a.program_subtheme_id', $filters['subtheme_id']);
            log_message('info', 'Applied subtheme filter: ' . $filters['subtheme_id']);
        }
        if (isset($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $builder->where('af.feedback IS NOT NULL');
                $builder->where('af.feedback !=', '');
                log_message('info', 'Applied completed status filter');
            } elseif ($filters['status'] === 'pending') {
                $builder->where('(af.feedback IS NULL OR af.feedback = "")');
                log_message('info', 'Applied pending status filter');
            }
            // If status is 'all' or not specified, don't add any feedback-based filters - show all abstracts
        }

        if (isset($filters['abstract_status']) && !empty($filters['abstract_status'])) {
            // Override the submitted filter if specific status is requested
            $builder->where('a.status', $filters['abstract_status']);
            log_message('info', 'Applied abstract status filter: ' . $filters['abstract_status']);
        }

        $builder->orderBy('a.created_at', 'DESC');

        // Log the SQL query
        $sql = $builder->getCompiledSelect(false);
        log_message('info', 'Generated SQL Query: ' . $sql);

        // Execute and get results
        $result = $builder->get()->getResult();
        log_message('info', 'Query executed. Result count: ' . count($result));

        if (empty($result)) {
            log_message('warning', 'No abstracts found for reviewer. Let\'s debug step by step...');

            // Debug: Check if reviewer exists
            $reviewerCheck = $this->db->table('abstract_reviewers')->where('id', $reviewer_id)->get()->getRow();
            log_message('info', 'Reviewer exists: ' . ($reviewerCheck ? 'YES' : 'NO'));
            if ($reviewerCheck) {
                log_message('info', 'Reviewer details: ' . json_encode($reviewerCheck));
            }

            // Debug: Check reviewer subtheme assignments
            $subthemeCheck = $this->db->table('abstract_reviewer_subthemes')
                ->where('abstract_reviewer_id', $reviewer_id)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->get()->getResult();
            log_message('info', 'Reviewer subtheme assignments count: ' . count($subthemeCheck));
            log_message('info', 'Reviewer subtheme assignments: ' . json_encode($subthemeCheck));            // Debug: Check non-draft abstracts in the program
            if ($reviewerCheck) {
                $abstractsCheck = $this->db->table('abstracts')
                    ->where('program_id', $reviewerCheck->program_id)
                    ->where('status !=', 'draft')
                    ->where('is_deleted', 0)
                    ->get()->getResult();
                log_message('info', 'Non-draft abstracts in program ' . $reviewerCheck->program_id . ': ' . count($abstractsCheck));

                // Debug: Check abstracts with program_subtheme_id set
                $abstractsWithSubthemes = $this->db->table('abstracts')
                    ->where('program_subtheme_id IS NOT NULL')
                    ->where('is_deleted', 0)
                    ->countAllResults();
                log_message('info', 'Abstracts with subthemes assigned: ' . $abstractsWithSubthemes);
            }
        } else {
            log_message('info', 'Found abstracts for reviewer. Sample: ' . json_encode($result[0]));
        }

        log_message('info', '=== getFeedbacksByReviewer END ===');

        return $result;
    }
    /**
     * Alternative implementation of getFeedbacksByReviewer with better error handling
     */
    public function getFeedbacksByReviewerFixed($reviewer_id, $filters = [])
    {
        log_message('info', '=== getFeedbacksByReviewerFixed START ===');
        log_message('info', 'Reviewer ID: ' . $reviewer_id);
        log_message('info', 'Filters: ' . json_encode($filters));

        // First, get reviewer information
        $reviewer = $this->db->table('abstract_reviewers')
            ->where('id', $reviewer_id)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->get()->getRow();

        if (!$reviewer) {
            log_message('error', 'Reviewer not found or inactive');
            return [];
        }

        log_message('info', 'Reviewer found: Program ID = ' . $reviewer->program_id);

        // Get reviewer's assigned subthemes
        $assignedSubthemes = $this->db->table('abstract_reviewer_subthemes ars')
            ->select('ars.program_subtheme_id')
            ->where('ars.abstract_reviewer_id', $reviewer_id)
            ->where('ars.is_active', 1)
            ->where('ars.is_deleted', 0)
            ->get()->getResult();

        if (empty($assignedSubthemes)) {
            log_message('warning', 'No subthemes assigned to reviewer');
            return [];
        }
        $subthemeIds = array_column($assignedSubthemes, 'program_subtheme_id');
        log_message('info', 'Assigned subtheme IDs: ' . implode(', ', $subthemeIds));

        // Debug: Check what abstracts exist in the database for this program
        $totalAbstracts = $this->db->table('abstracts')
            ->where('program_id', $reviewer->program_id)
            ->where('is_deleted', 0)
            ->countAllResults();
        log_message('info', 'Total abstracts in program ' . $reviewer->program_id . ': ' . $totalAbstracts);

        // Debug: Check abstracts with program_subtheme_id set
        $abstractsWithSubthemes = $this->db->table('abstracts')
            ->where('program_id', $reviewer->program_id)
            ->where('program_subtheme_id IS NOT NULL')
            ->where('is_deleted', 0)
            ->countAllResults();
        log_message('info', 'Abstracts with subthemes set: ' . $abstractsWithSubthemes);

        // Debug: Check abstracts in reviewer's subthemes
        $abstractsInSubthemes = $this->db->table('abstracts')
            ->where('program_id', $reviewer->program_id)
            ->whereIn('program_subtheme_id', $subthemeIds)
            ->where('is_deleted', 0)
            ->countAllResults();
        log_message('info', 'Abstracts in reviewer subthemes: ' . $abstractsInSubthemes);        // Debug: Check non-draft abstracts in reviewer's subthemes
        $nonDraftAbstracts = $this->db->table('abstracts')
            ->where('program_id', $reviewer->program_id)
            ->whereIn('program_subtheme_id', $subthemeIds)
            ->where('status !=', 'draft')
            ->where('is_deleted', 0)
            ->countAllResults();
        log_message('info', 'Non-draft abstracts in reviewer subthemes: ' . $nonDraftAbstracts);

        // Debug: Check what statuses the abstracts actually have
        $abstractStatuses = $this->db->table('abstracts')
            ->select('id, status, active_version_id')
            ->where('program_id', $reviewer->program_id)
            ->whereIn('program_subtheme_id', $subthemeIds)
            ->where('is_deleted', 0)
            ->get()->getResult();
        log_message('info', 'Abstract statuses in reviewer subthemes: ' . json_encode($abstractStatuses));        // Debug: Check if active_version_id is set properly
        $abstractsWithActiveVersions = $this->db->table('abstracts a')
            ->join('abstract_versions av', 'av.id = a.active_version_id')
            ->where('a.program_id', $reviewer->program_id)
            ->whereIn('a.program_subtheme_id', $subthemeIds)
            ->where('a.status !=', 'draft')
            ->where('a.is_deleted', 0)
            ->where('av.is_active', 1)
            ->where('av.is_deleted', 0)
            ->countAllResults();
        log_message('info', 'Abstracts with valid active versions: ' . $abstractsWithActiveVersions);

        // Now build the main query
        $builder = $this->db->table('abstracts a');        $builder->select('a.id as abstract_id, av.title as abstract_title, a.status as abstract_status,
                         a.active_version_id, a.created_at as submission_date,
                         av.id as version_id, av.title as version_title, av.content as abstract_content,
                         av.keywords, p.full_name as participant_name, 
                         prog.name as program_name, ps.name as subtheme_name,
                         af.id as feedback_id, af.feedback, af.created_at as feedback_created_at,
                         af.updated_at as feedback_updated_at, a.program_id,
                         GROUP_CONCAT(DISTINCT aa.full_name ORDER BY aa.id SEPARATOR ", ") as authors_list');

        // Join with the active/latest version to get the most current submitted version
        $builder->join('abstract_versions av', 'av.id = a.active_version_id');
        $builder->join('participants p', 'p.id = a.primary_participant_id');
        $builder->join('programs prog', 'prog.id = a.program_id');

        // Join with program subthemes using the direct program_subtheme_id from abstracts table
        $builder->join('program_subthemes ps', 'ps.id = a.program_subtheme_id');

        // Left join with abstract authors for authors list
        $builder->join('abstract_authors aa', 'aa.abstract_id = a.id AND aa.is_deleted = 0', 'left');

        // Left join with feedback to check if reviewer has already provided feedback
        $builder->join(
            'abstract_feedbacks af',
            "af.abstract_version_id = av.id AND af.abstract_reviewer_id = {$reviewer_id}",
            'left'
        );

        // Apply constraints
        $builder->where('a.program_id', $reviewer->program_id);
        $builder->where('a.status !=', 'draft'); // Show all abstracts except drafts
        $builder->where('a.is_deleted', 0);
        $builder->where('av.is_active', 1);
        $builder->where('av.is_deleted', 0);
        $builder->whereIn('a.program_subtheme_id', $subthemeIds);

        // Apply filters
        if (isset($filters['subtheme_id']) && !empty($filters['subtheme_id'])) {
            $builder->where('a.program_subtheme_id', $filters['subtheme_id']);
            log_message('info', 'Applied subtheme filter: ' . $filters['subtheme_id']);
        }        if (isset($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $builder->where('af.feedback IS NOT NULL');
                $builder->where('af.feedback !=', '');
                log_message('info', 'Applied completed status filter');
            } elseif ($filters['status'] === 'pending') {
                $builder->where('(af.feedback IS NULL OR af.feedback = "")');                log_message('info', 'Applied pending status filter');
            }
        }

        if (isset($filters['abstract_status']) && !empty($filters['abstract_status'])) {
            $builder->where('a.status', $filters['abstract_status']);
            log_message('info', 'Applied abstract status filter: ' . $filters['abstract_status']);
        }

        $builder->groupBy('a.id, av.id, p.id, prog.id, ps.id, af.id');
        $builder->orderBy('a.created_at', 'DESC');

        // Log the SQL query
        $sql = $builder->getCompiledSelect(false);
        log_message('info', 'Generated SQL Query: ' . $sql);

        // Execute and get results
        $result = $builder->get()->getResult();
        log_message('info', 'Query executed. Result count: ' . count($result));

        log_message('info', '=== getFeedbacksByReviewerFixed END ===');
        return $result;
    }
    /**
     * Get specific abstract details for a reviewer
     * 
     * @param int $abstract_id
     * @param int $reviewer_id
     * @return object|null
     */
    public function getFeedbackDetails($abstract_id, $reviewer_id)
    {
        $builder = $this->db->table('abstracts a');
        $builder->select('a.id as abstract_id, av.title as abstract_title, a.status as abstract_status,
                         a.active_version_id, a.created_at as submission_date,
                         av.id as version_id, av.title as version_title, av.content as abstract_content,
                         av.keywords, av.refs, av.created_at as version_created_at,
                         p.id as participant_id, p.full_name as participant_name, 
                         u.email as participant_email, prog.id as program_id, prog.name as program_name, 
                         ps.id as subtheme_id, ps.name as subtheme_name,
                         af.id as feedback_id, af.feedback, af.created_at as feedback_created_at,
                         af.updated_at as feedback_updated_at');        // Join with active version
        $builder->join('abstract_versions av', 'av.id = a.active_version_id');
        $builder->join('participants p', 'p.id = a.primary_participant_id');
        $builder->join('users u', 'u.id = p.user_id');
        $builder->join('programs prog', 'prog.id = a.program_id');

        // Join with program subthemes using the direct program_subtheme_id from abstracts table
        $builder->join('program_subthemes ps', 'ps.id = a.program_subtheme_id');

        // Join with reviewer to get program_id
        $builder->join('abstract_reviewers ar', "ar.id = {$reviewer_id}");

        // Join with reviewer subthemes to ensure reviewer is assigned to this subtheme
        $builder->join(
            'abstract_reviewer_subthemes ars',
            'ars.abstract_reviewer_id = ar.id AND ars.program_subtheme_id = a.program_subtheme_id'
        );

        // Left join with feedback to see if reviewer has already provided feedback
        $builder->join(
            'abstract_feedbacks af',
            "af.abstract_version_id = av.id AND af.abstract_reviewer_id = {$reviewer_id}",
            'left'
        );
        $builder->where('a.id', $abstract_id);
        $builder->where('ar.id', $reviewer_id);
        $builder->where('ar.is_active', 1);
        $builder->where('ar.is_deleted', 0);
        $builder->where('ars.is_active', 1);
        $builder->where('ars.is_deleted', 0);
        $builder->where('av.is_active', 1);
        $builder->where('av.is_deleted', 0);

        // Ensure abstract belongs to reviewer's program
        $builder->where('a.program_id = ar.program_id');        // Exclude draft abstracts
        $builder->where('a.status !=', 'draft');

        return $builder->get()->getRow();
    }
    /**
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

        // Start database transaction
        $this->db->transStart();

        try {
            // Check if feedback already exists
            $existingFeedback = $this->where('abstract_version_id', $abstract->active_version_id)
                ->where('abstract_reviewer_id', $reviewer_id)
                ->where('is_deleted', 0)
                ->first();

            $feedbackResult = false;
            if ($existingFeedback) {
                // Update existing feedback
                $feedbackResult = $this->update($existingFeedback->id, ['feedback' => $feedback_text]);
            } else {
                // Create new feedback
                $data = [
                    'abstract_version_id' => $abstract->active_version_id,
                    'abstract_reviewer_id' => $reviewer_id,
                    'feedback' => $feedback_text,
                    'is_active' => 1
                ];

                $feedbackResult = $this->insert($data) !== false;
            }

            // If feedback was successfully saved/updated, update abstract status to under_review
            if ($feedbackResult) {
                $abstractModel = new \App\Models\AbstractModel();
                $abstractModel->update($abstract_id, ['status' => 'under_review']);

                log_message('info', 'Abstract status updated to under_review for abstract ID: ' . $abstract_id);
            }

            // Complete transaction
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', 'Transaction failed when submitting feedback for abstract ID: ' . $abstract_id);
                return false;
            }

            return $feedbackResult;
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error submitting feedback for abstract ID ' . $abstract_id . ': ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Get feedback statistics for a reviewer
     * 
     * @param int $reviewer_id
     * @return array
     */    public function getReviewerStats($reviewer_id)
    {
        log_message('info', '=== getReviewerStats START ===');
        log_message('info', 'Reviewer ID: ' . $reviewer_id);
        // Get available abstracts for this reviewer
        // TEMPORARY: Use fixed method for debugging
        $available_abstracts = $this->getFeedbacksByReviewerFixed($reviewer_id, []);
        $total_available = count($available_abstracts);

        log_message('info', 'Total available abstracts: ' . $total_available);

        // Count completed feedbacks
        $completed_feedbacks = array_filter($available_abstracts, function ($abstract) {
            return !empty($abstract->feedback);
        });
        $total_completed = count($completed_feedbacks);

        log_message('info', 'Total completed: ' . $total_completed);

        // Count pending feedbacks
        $total_pending = $total_available - $total_completed;

        log_message('info', 'Total pending: ' . $total_pending);

        $stats = [
            'total_assigned' => $total_available,
            'total_completed' => $total_completed,
            'total_pending' => $total_pending,
            'total_in_review' => 0, // Not applicable in this structure
            'completion_rate' => $total_available > 0 ? round(($total_completed / $total_available) * 100, 2) : 0
        ];

        log_message('info', 'Final stats: ' . json_encode($stats));
        log_message('info', '=== getReviewerStats END ===');

        return $stats;
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
        $builder->join(
            'abstract_reviewer_subthemes ars',
            'ars.abstract_reviewer_id = af.abstract_reviewer_id AND ars.program_subtheme_id = a.program_subtheme_id'
        );

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
        $builder->join(
            'abstract_reviewer_subthemes ars',
            'ars.abstract_reviewer_id = ar.id AND ars.program_subtheme_id = a.program_subtheme_id'
        );
        $builder->join(
            'abstract_feedbacks existing_af',
            'existing_af.abstract_reviewer_id = ar.id',
            'left'
        );
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
    /**
     * Get comprehensive abstract details including all versions, authors, feedbacks, and papers
     * 
     * @param int $abstract_id
     * @param int $reviewer_id
     * @return object|null
     */
    public function getComprehensiveAbstractDetails($abstract_id, $reviewer_id)
    {
        // First, get the basic abstract details (existing method)
        $abstract = $this->getFeedbackDetails($abstract_id, $reviewer_id);

        if (!$abstract) {
            return null;
        }

        // Get only submitted versions for reviewers (not drafts)
        $abstract->versions = $this->getAbstractVersionsForReviewers($abstract_id);

        // Get all authors for this abstract
        $abstract->authors = $this->getAbstractAuthors($abstract_id);

        // Get all feedback history from all reviewers
        $abstract->all_feedbacks = $this->getAllFeedbacksForAbstract($abstract_id);

        // Get associated papers if any
        $abstract->papers = $this->getAbstractPapers($abstract_id);

        return $abstract;
    }
    /**
     * Get all versions of an abstract
     * 
     * @param int $abstract_id
     * @return array
     */    public function getAbstractVersions($abstract_id)
    {
        $builder = $this->db->table('abstract_versions av');
        $builder->select('av.id, av.title, av.content, av.keywords, av.refs, 
                         av.version_number, av.is_active, av.created_at, av.updated_at,
                         av.status, a.active_version_id');
        $builder->join('abstracts a', 'a.id = av.abstract_id');
        $builder->where('av.abstract_id', $abstract_id);
        $builder->where('av.is_deleted', 0);
        $builder->orderBy('av.version_number', 'DESC');

        $versions = $builder->get()->getResult();

        // Add is_current_version flag in PHP
        foreach ($versions as $version) {
            $version->is_current_version = ($version->id == $version->active_version_id) ? 1 : 0;
        }

        return $versions;
    }

    /**
     * Get only submitted versions of an abstract for reviewers
     * 
     * @param int $abstract_id
     * @return array
     */    public function getAbstractVersionsForReviewers($abstract_id)
    {
        $builder = $this->db->table('abstract_versions av');
        $builder->select('av.id, av.title, av.content, av.keywords, av.refs, 
                         av.version_number, av.is_active, av.created_at, av.updated_at,
                         av.status, a.active_version_id');
        $builder->join('abstracts a', 'a.id = av.abstract_id');
        $builder->where('av.abstract_id', $abstract_id);
        $builder->where('av.is_deleted', 0);
        $builder->where('av.status', 'submitted'); // Only show submitted versions to reviewers
        $builder->orderBy('av.version_number', 'DESC');

        $versions = $builder->get()->getResult();

        // Add is_current_version flag in PHP
        foreach ($versions as $version) {
            $version->is_current_version = ($version->id == $version->active_version_id) ? 1 : 0;
        }

        return $versions;
    }

    /**
     * Get all authors for an abstract
     * 
     * @param int $abstract_id
     * @return array
     */    public function getAbstractAuthors($abstract_id)
    {
        $builder = $this->db->table('abstract_authors aa');
        $builder->select('aa.id, aa.full_name, aa.email, aa.institution, 
                         aa.is_participant, aa.participant_id, aa.created_at');
        $builder->where('aa.abstract_id', $abstract_id);
        $builder->where('aa.is_deleted', 0);
        $builder->where('aa.is_active', 1);
        $builder->orderBy('aa.id', 'ASC'); // Order by ID since there's no author_order field

        return $builder->get()->getResult();
    }

    /**
     * Get all feedbacks for an abstract from all reviewers
     * 
     * @param int $abstract_id
     * @return array
     */
    public function getAllFeedbacksForAbstract($abstract_id)
    {
        $builder = $this->db->table('abstract_feedbacks af');
        $builder->select('af.id, af.feedback, af.created_at, af.updated_at,
                         av.id as version_id, av.title as version_title, av.version_number,
                         ar.id as reviewer_id, ar.name as reviewer_name, ar.email as reviewer_email');
        $builder->join('abstract_versions av', 'av.id = af.abstract_version_id');
        $builder->join('abstract_reviewers ar', 'ar.id = af.abstract_reviewer_id');
        $builder->join('abstracts a', 'a.id = av.abstract_id');
        $builder->where('a.id', $abstract_id);
        $builder->where('af.is_deleted', 0);
        $builder->where('ar.is_active', 1);
        $builder->where('ar.is_deleted', 0);
        $builder->where('af.feedback IS NOT NULL');
        $builder->where('af.feedback !=', '');
        $builder->orderBy('af.created_at', 'DESC');

        return $builder->get()->getResult();
    }

    /**
     * Get papers associated with an abstract
     * 
     * @param int $abstract_id
     * @return array
     */    public function getAbstractPapers($abstract_id)
    {
        $builder = $this->db->table('abstract_papers ap');
        $builder->select('ap.id, ap.file_url, ap.notes, ap.status, 
                         ap.created_at, ap.updated_at');
        $builder->where('ap.abstract_id', $abstract_id);
        $builder->where('ap.is_deleted', 0);
        $builder->where('ap.is_active', 1);
        $builder->orderBy('ap.created_at', 'DESC');

        return $builder->get()->getResult();
    }

    /**
     * Simplified method to get abstracts for reviewer debugging
     */
    public function getAbstractsForReviewerSimple($reviewer_id)
    {
        log_message('info', '=== getAbstractsForReviewerSimple START ===');
        log_message('info', 'Reviewer ID: ' . $reviewer_id);

        // Step 1: Get reviewer info
        $reviewer = $this->db->table('abstract_reviewers')->where('id', $reviewer_id)->get()->getRow();
        if (!$reviewer) {
            log_message('error', 'Reviewer not found');
            return [];
        }
        log_message('info', 'Reviewer found: ' . json_encode($reviewer));

        // Step 2: Get reviewer's assigned subthemes
        $assignedSubthemes = $this->db->table('abstract_reviewer_subthemes ars')
            ->select('ars.program_subtheme_id, ps.name as subtheme_name')
            ->join('program_subthemes ps', 'ps.id = ars.program_subtheme_id')
            ->where('ars.abstract_reviewer_id', $reviewer_id)
            ->where('ars.is_active', 1)
            ->where('ars.is_deleted', 0)
            ->get()->getResult();

        log_message('info', 'Assigned subthemes: ' . json_encode($assignedSubthemes));

        if (empty($assignedSubthemes)) {
            log_message('warning', 'No subthemes assigned to reviewer');
            return [];
        }
        $subthemeIds = array_column($assignedSubthemes, 'program_subtheme_id');
        log_message('info', 'Subtheme IDs: ' . implode(', ', $subthemeIds));

        // Step 3: Get abstracts with matching subthemes directly
        $builder = $this->db->table('abstracts a');
        $abstracts = $builder->select('a.id as abstract_id, av.title as abstract_title, a.status as abstract_status,
                                     a.active_version_id, a.created_at as submission_date,
                                     p.full_name as participant_name, prog.name as program_name,
                                     ps.name as subtheme_name, a.program_id')
            ->join('abstract_versions av', 'av.id = a.active_version_id')
            ->join('participants p', 'p.id = a.primary_participant_id')
            ->join('programs prog', 'prog.id = a.program_id')
            ->join('program_subthemes ps', 'ps.id = a.program_subtheme_id')->whereIn('a.program_subtheme_id', $subthemeIds)
            ->where('a.program_id', $reviewer->program_id)
            ->where('a.status !=', 'draft')
            ->where('a.is_deleted', 0)
            ->where('av.is_active', 1)
            ->where('av.is_deleted', 0)
            ->get()->getResult();

        log_message('info', 'Found abstracts: ' . count($abstracts));

        // Step 5: Add feedback information
        foreach ($abstracts as &$abstract) {
            $feedback = $this->db->table('abstract_feedbacks')
                ->where('abstract_version_id', $abstract->active_version_id)
                ->where('abstract_reviewer_id', $reviewer_id)
                ->get()->getRow();

            $abstract->feedback = $feedback ? $feedback->feedback : null;
            $abstract->feedback_id = $feedback ? $feedback->id : null;
            $abstract->feedback_created_at = $feedback ? $feedback->created_at : null;
            $abstract->feedback_updated_at = $feedback ? $feedback->updated_at : null;
        }

        log_message('info', '=== getAbstractsForReviewerSimple END ===');
        return $abstracts;
    }
}
