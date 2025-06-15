<?php

namespace App\Models;

use CodeIgniter\Model;

class AbstractSettingModel extends Model
{
    // `id`, `program_id`, `title_length`, `content_length`, `keywords_length`, `refs_length`, `abstract_template_url`, `paper_template_url`, `abstract_submission_deadline`, `full_paper_submission_deadline`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table = 'abstract_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $fillable = [
        'id',
        'program_id',
        'title_length',
        'content_length',
        'keywords_length',
        'refs_length',
        'paper_template_url',
        'abstract_template_url',
        'abstract_submission_deadline',
        'full_paper_submission_deadline',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $allowedFields = [
        'program_id',
        'title_length',
        'content_length',
        'keywords_length',
        'refs_length',
        'paper_template_url',
        'abstract_template_url',
        'abstract_submission_deadline',
        'full_paper_submission_deadline',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $validationRules = [
        'program_id' => 'required|integer',
        'title_length' => 'required|integer',
        'content_length' => 'required|integer',
        'keywords_length' => 'required|integer',
        'refs_length' => 'required|integer',
        'paper_template_url' => 'permit_empty|valid_url',
        'abstract_template_url' => 'permit_empty|valid_url',
        'abstract_submission_deadline' => 'permit_empty|valid_date',
        'full_paper_submission_deadline' => 'permit_empty|valid_date',
        'is_active' => 'in_list[0,1]',
        'is_deleted' => 'in_list[0,1]'
    ];

    /**
     * Get abstract settings by program ID
     *
     * @param int $programId The program ID
     * @return object|null
     */
    public function getByProgramId($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->first();
    }

    /**
     * Get abstract settings by program ID including inactive/deleted
     *
     * @param int $programId The program ID
     * @return object|null
     */
    public function getByProgramIdAll($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->first();
    }    /**
     * Create default settings for a program
     *
     * @param int $programId The program ID
     * @return int|false Insert ID on success, false on failure
     */    
    public function createDefaultSettings($programId)
    {        
        $defaultData = [
            'program_id' => $programId,
            'title_length' => 15,
            'content_length' => 500,
            'keywords_length' => 5,
            'refs_length' => 100,
            'paper_template_url' => null,
            'abstract_template_url' => null,
            // Set deadlines to null by default
            'abstract_submission_deadline' => null,
            'full_paper_submission_deadline' => null,
            'is_active' => 1,
            'is_deleted' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($defaultData);
    }
}
