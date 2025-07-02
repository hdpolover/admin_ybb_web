<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramAwardModel extends Model
{
    protected $table = 'program_awards';
    protected $primaryKey = 'id';
    protected $returnType = 'object';  // Force return as object
    protected $allowedFields = [
        'program_id',
        'title',
        'description',
        'award_type',
        'order_number',
        'is_active',
        'is_deleted'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'program_id' => 'required|integer',
        'title' => 'required|string|max_length[255]',
        'description' => 'permit_empty|string',
        'award_type' => 'required|in_list[winner,runner_up,mention,other]',
        'order_number' => 'permit_empty|integer',
        'is_active' => 'permit_empty|in_list[0,1]',
        'is_deleted' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'program_id' => [
            'required' => 'Program ID is required',
            'integer' => 'Program ID must be a valid integer'
        ],
        'title' => [
            'required' => 'Award title is required',
            'max_length' => 'Award title cannot exceed 255 characters'
        ],
        'award_type' => [
            'required' => 'Award type is required',
            'in_list' => 'Award type must be one of: winner, runner_up, mention, other'
        ]
    ];

    /**
     * Get active awards for a specific program
     */
    public function getActiveAwardsByProgram($programId)
    {
        return $this->where('program_id', $programId)
                   ->where('is_active', 1)
                   ->where('is_deleted', 0)
                   ->orderBy('order_number', 'ASC')
                   ->findAll();
    }

    /**
     * Get award with program details
     */
    public function getAwardWithProgram($id)
    {
        return $this->select('program_awards.*, programs.name as program_name')
                   ->join('programs', 'programs.id = program_awards.program_id', 'left')
                   ->where('program_awards.id', $id)
                   ->where('program_awards.is_deleted', 0)
                   ->first();
    }

    /**
     * Soft delete award
     */
    public function softDelete($id)
    {
        return $this->update($id, [
            'is_deleted' => 1,
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
