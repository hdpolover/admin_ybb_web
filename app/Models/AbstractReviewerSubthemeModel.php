<?php

namespace App\Models;

use CodeIgniter\Model;

class AbstractReviewerSubthemeModel extends Model
{
    // `id`, `abstract_reviewer_id`, `program_subtheme_id`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table = 'abstract_reviewer_subthemes';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $fillable = [
        'id',
        'abstract_reviewer_id',
        'program_subtheme_id',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];
    protected $allowedFields = [
        'abstract_reviewer_id',
        'program_subtheme_id',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];    // Validation rules
    protected $validationRules = [
        'abstract_reviewer_id' => 'required|integer',
        'program_subtheme_id' => 'required|integer',
        'is_active' => 'in_list[0,1]',
        'is_deleted' => 'in_list[0,1]',
    ];

    // Get reviewer subtheme by abstract reviewer ID and program subtheme ID
    public function getByAbstractReviewerAndSubtheme($abstract_reviewer_id, $program_subtheme_id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('abstract_reviewer_id', $abstract_reviewer_id)
            ->where('program_subtheme_id', $program_subtheme_id);
        return $builder->get()->getRow();
    }    // Get all subthemes for a specific abstract reviewer
    public function getSubthemesByReviewerId($abstract_reviewer_id)
    {
        $builder = $this->builder();
        $builder->select('abstract_reviewer_subthemes.*, program_subthemes.name as subtheme_name, program_subthemes.desc as subtheme_description')
            ->join('program_subthemes', 'program_subthemes.id = abstract_reviewer_subthemes.program_subtheme_id', 'left')
            ->where('abstract_reviewer_subthemes.abstract_reviewer_id', $abstract_reviewer_id)
            ->where('abstract_reviewer_subthemes.is_active', 1)
            ->where('abstract_reviewer_subthemes.is_deleted', 0);
        return $builder->get()->getResult();
    }

    // Get all subthemes for a specific program subtheme
    public function getSubthemesByProgramSubthemeId($program_subtheme_id)
    {
        $builder = $this->builder();
        $builder->select('*')
            ->where('program_subtheme_id', $program_subtheme_id)
            ->where('is_active', 1)
            ->where('is_deleted', 0);
        return $builder->get()->getResult();
    }
    /**
     * Assign subthemes to a reviewer (update existing assignments)
     *
     * @param int $reviewerId
     * @param array $subthemeIds
     * @return bool
     */
    public function assignSubthemes($reviewerId, $subthemeIds)
    {
        log_message('debug', 'AssignSubthemes called with reviewer: ' . $reviewerId . ', subthemes: ' . json_encode($subthemeIds));

        if (!is_array($subthemeIds)) {
            log_message('error', 'Invalid subtheme IDs format');
            return false;
        }

        // Start a transaction
        $this->db->transStart();

        try {
            // Get existing assignments
            $existingAssignments = $this->where('abstract_reviewer_id', $reviewerId)
                ->findAll();

            $existingIds = array_map(function ($assignment) {
                return $assignment->program_subtheme_id;
            }, $existingAssignments);

            // Calculate which assignments to add and which to update
            $toAdd = array_diff($subthemeIds, $existingIds);
            $toUpdate = array_intersect($subthemeIds, $existingIds);
            $toDeactivate = array_diff($existingIds, $subthemeIds);

            // Add new assignments
            foreach ($toAdd as $subthemeId) {
                if (!empty($subthemeId) && is_numeric($subthemeId)) {
                    $data = [
                        'abstract_reviewer_id' => (int)$reviewerId,
                        'program_subtheme_id' => (int)$subthemeId,
                        'is_active' => 1,
                        'is_deleted' => 0
                    ];
                    $this->insert($data);
                    log_message('debug', 'Added new assignment: ' . json_encode($data));
                }
            }

            // Reactivate existing assignments that should be kept
            if (!empty($toUpdate)) {
                $this->where('abstract_reviewer_id', $reviewerId)
                    ->whereIn('program_subtheme_id', $toUpdate)
                    ->set(['is_active' => 1, 'is_deleted' => 0])
                    ->update();
                log_message('debug', 'Updated existing assignments: ' . json_encode($toUpdate));
            }

            // Deactivate assignments that are not in the new list
            if (!empty($toDeactivate)) {
                $this->where('abstract_reviewer_id', $reviewerId)
                    ->whereIn('program_subtheme_id', $toDeactivate)
                    ->set(['is_active' => 0, 'is_deleted' => 1])
                    ->update();
                log_message('debug', 'Deactivated assignments: ' . json_encode($toDeactivate));
            }

            $this->db->transComplete();
            return $this->db->transStatus();
        } catch (\Exception $e) {
            log_message('error', 'Exception in assignSubthemes: ' . $e->getMessage());
            $this->db->transRollback();
            return false;
        }
    }
    /**
     * Remove all subtheme assignments for a reviewer
     *
     * @param int $reviewerId
     * @return bool
     */
    public function removeAllAssignments($reviewerId)
    {
        log_message('debug', 'Removing all assignments for reviewer: ' . $reviewerId);

        try {
            $result = $this->where('abstract_reviewer_id', $reviewerId)
                ->set(['is_deleted' => 1, 'is_active' => 0])
                ->update();

            log_message('debug', 'Remove assignments result: ' . ($result ? 'success' : 'failed'));
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Exception removing assignments: ' . $e->getMessage());
            return false;
        }
    }
}
