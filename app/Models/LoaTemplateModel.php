<?php

namespace App\Models;

use CodeIgniter\Model;

class LoaTemplateModel extends Model
{
    protected $table = 'loa_templates';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $protectFields = true;
    protected $useTimestamps = true; // Enable timestamps
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime'; // Set date format to datetime
    
    protected $allowedFields = [
        'program_document_id',
        'letter_type',
        'body',
        'is_active',
        'created_at',
        'updated_at'
    ];

    protected $validationRules = [
        'program_document_id' => 'required|numeric',
        'letter_type' => 'required|in_list[regular,journal]',
        'body' => 'required'
    ];

    protected $validationMessages = [
        'program_document_id' => [
            'required' => 'Program Document ID is required',
            'numeric' => 'Program Document ID must be a number'
        ],
        'letter_type' => [
            'required' => 'Letter type is required',
            'in_list' => 'Letter type must be either regular or journal'
        ],
        'body' => [
            'required' => 'Body content is required'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // get LOA template by program document id
    public function getLoaTemplateByProgramDocumentId($programDocumentId)
    {
        return $this->where('program_document_id', $programDocumentId)
            ->where('is_deleted', 0)
            ->first();
    }

    // get LOA template by id
    public function getLoaTemplateById($id)
    {
        return $this->where('id', $id)
            ->where('is_deleted', 0)
            ->first();
    }
}
