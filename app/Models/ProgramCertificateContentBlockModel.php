<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramCertificateContentBlockModel extends Model
{
    protected $table = 'program_certificate_content_blocks';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'certificate_id',
        'type',
        'value',
        'x',
        'y',
        'font_size',
        'font_family',
        'font_weight',
        'text_align',
        'color',
        'is_active',
        'is_deleted'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'certificate_id' => 'required|integer',
        'type' => 'required|in_list[text,participant_name,award_title,program_name,date]',
        'value' => 'required|string',
        'x' => 'required|integer',
        'y' => 'required|integer',
        'font_size' => 'permit_empty|integer|greater_than[0]',
        'font_family' => 'permit_empty|string|max_length[100]',
        'font_weight' => 'permit_empty|in_list[normal,bold]',
        'text_align' => 'permit_empty|in_list[left,center,right]',
        'color' => 'permit_empty|string|max_length[10]',
        'is_active' => 'permit_empty|in_list[0,1]',
        'is_deleted' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'certificate_id' => [
            'required' => 'Certificate ID is required',
            'integer' => 'Certificate ID must be a valid integer'
        ],
        'type' => [
            'required' => 'Content block type is required',
            'in_list' => 'Content block type must be text, participant_name, award_title, program_name, or date'
        ],
        'value' => [
            'required' => 'Content value is required'
        ],
        'x' => [
            'required' => 'X coordinate is required',
            'integer' => 'X coordinate must be a valid integer'
        ],
        'y' => [
            'required' => 'Y coordinate is required',
            'integer' => 'Y coordinate must be a valid integer'
        ],
        'font_size' => [
            'integer' => 'Font size must be a valid integer',
            'greater_than' => 'Font size must be greater than 0'
        ],
        'font_family' => [
            'max_length' => 'Font family cannot exceed 100 characters'
        ],
        'font_weight' => [
            'in_list' => 'Font weight must be either normal or bold'
        ],
        'text_align' => [
            'in_list' => 'Text align must be left, center, or right'
        ],
        'color' => [
            'max_length' => 'Color value cannot exceed 10 characters'
        ]
    ];

    /**
     * Get content blocks for a specific certificate
     */
    public function getContentBlocksByCertificate($certificateId)
    {
        return $this->where('certificate_id', $certificateId)
                   ->where('is_active', 1)
                   ->where('is_deleted', 0)
                   ->orderBy('y', 'ASC')
                   ->orderBy('x', 'ASC')
                   ->findAll();
    }

    /**
     * Get content blocks by type
     */
    public function getContentBlocksByType($certificateId, $type)
    {
        return $this->where('certificate_id', $certificateId)
                   ->where('type', $type)
                   ->where('is_active', 1)
                   ->where('is_deleted', 0)
                   ->findAll();
    }

    /**
     * Update content block position
     */
    public function updatePosition($id, $x, $y)
    {
        return $this->update($id, [
            'x' => $x,
            'y' => $y,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Bulk delete content blocks for a certificate
     */
    public function deleteBlocksByCertificate($certificateId)
    {
        return $this->where('certificate_id', $certificateId)
                   ->set('is_deleted', 1)
                   ->set('is_active', 0)
                   ->set('updated_at', date('Y-m-d H:i:s'))
                   ->update();
    }

    /**
     * Soft delete content block
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
