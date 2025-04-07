<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramDocumentModel extends Model
{
    //`id`, `program_id`, `name`, `file_url`, `drive_url`, `desc`, `is_upload`, `is_generated`, `visibility`, `is_active`, `is_deleted`, `created_at`, `updated_at

    protected $table = 'program_documents';
    protected $primaryKey = 'id';
    protected $allowedFields = ['program_id', 'name', 'file_url', 'drive_url', 'desc', 'is_upload', 'is_generated', 'visibility', 'is_active', 'is_deleted', 'created_at', 'updated_at'];
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $validationRules = [
        'program_id' => 'required|numeric',
        'name' => 'required|string|max_length[255]',
        'file_url' => 'permit_empty|string|max_length[255]',
        'drive_url' => 'permit_empty|string|max_length[255]',
        'desc' => 'permit_empty|string',
        'is_upload' => 'permit_empty|in_list[0,1]',
        'is_generated' => 'permit_empty|in_list[0,1]',
        'visibility' => 'permit_empty|in_list[0,1]',
        'is_active' => 'permit_empty|in_list[0,1]',
        'is_deleted' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'program_id' => [
            'required' => 'Program ID is required',
            'numeric' => 'Program ID must be a number'
        ],
        'name' => [
            'required' => 'Document name is required',
            'string' => 'Document name must be a string',
            'max_length' => 'Document name cannot exceed 255 characters'
        ],
        // Add other validation messages as needed
    ];

    // get program documents by program id
    public function getProgramDocumentsByProgramId($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->findAll();
    }
    

    public function getProgramDocumentById($id)
    {
        return $this->where('id', $id)
                    ->where('is_deleted', 0)
                    ->first();
    }
}
