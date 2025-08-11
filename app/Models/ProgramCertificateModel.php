<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramCertificateModel extends Model
{
    protected $table = 'program_certificates';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = [
        'program_id',
        'award_id',
        'template_url',
        'template_type',
        'preview_url',
        'issue_date',
        'published_at',
        'is_active',
        'is_deleted'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'program_id' => 'required|integer',
        'award_id' => 'required|integer',
        'template_url' => 'permit_empty|string|max_length[512]',
        'template_type' => 'permit_empty|in_list[image,pdf]',
        'preview_url' => 'permit_empty|string|max_length[512]',
        'issue_date' => 'permit_empty|valid_date',
        'published_at' => 'permit_empty|valid_date',
        'is_active' => 'permit_empty|in_list[0,1]',
        'is_deleted' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'program_id' => [
            'required' => 'Program ID is required',
            'integer' => 'Program ID must be a valid integer'
        ],
        'award_id' => [
            'required' => 'Award ID is required',
            'integer' => 'Award ID must be a valid integer'
        ],
        'template_url' => [
            'max_length' => 'Template URL cannot exceed 512 characters'
        ],
        'template_type' => [
            'in_list' => 'Template type must be either image or pdf'
        ],
        'preview_url' => [
            'max_length' => 'Preview URL cannot exceed 512 characters'
        ],
        'issue_date' => [
            'valid_date' => 'Issue date must be a valid date'
        ],
        'published_at' => [
            'valid_date' => 'Published date must be a valid date'
        ]
    ];

    /**
     * Get certificates for a specific program
     */
    public function getCertificatesByProgram($programId)
    {
        return $this->select('program_certificates.*, program_awards.title as award_title, programs.name as program_name')
                   ->join('program_awards', 'program_awards.id = program_certificates.award_id', 'left')
                   ->join('programs', 'programs.id = program_certificates.program_id', 'left')
                   ->where('program_certificates.program_id', $programId)
                   ->where('program_certificates.is_active', 1)
                   ->where('program_certificates.is_deleted', 0)
                   ->findAll();
    }

    /**
     * Get certificate with related data
     */
    public function getCertificateWithDetails($id)
    {
        return $this->select('program_certificates.*, program_awards.title as award_title, programs.name as program_name')
                   ->join('program_awards', 'program_awards.id = program_certificates.award_id', 'left')
                   ->join('programs', 'programs.id = program_certificates.program_id', 'left')
                   ->where('program_certificates.id', $id)
                   ->where('program_certificates.is_deleted', 0)
                   ->first();
    }

    /**
     * Get published certificates
     */
    public function getPublishedCertificates($programId = null)
    {
        $query = $this->where('is_active', 1)
                     ->where('is_deleted', 0)
                     ->where('published_at IS NOT NULL');
        
        if ($programId) {
            $query->where('program_id', $programId);
        }
        
        return $query->findAll();
    }

    /**
     * Soft delete certificate
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
