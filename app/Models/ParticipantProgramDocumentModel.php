<?php
namespace App\Models;

use CodeIgniter\Model;

class ParticipantProgramDocumentModel extends Model
{
    protected $table = 'participant_program_documents';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'object';

    protected $allowedFields = [
        'participant_id',
        'program_document_id',
        'file_url',
        'status',
        'notes',
        'created_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;


    /**
     * Get all FAQs by program ID
     *
     * @param int $programId The program ID
     * @return array The FAQs
     */    public function getAllDocsByProgramId($programId)
    {
        // return $this->where('program_document_id', '18')
        //     ->where('is_deleted', 0)
        //     ->where('is_active', 1)
        //     ->orderBy('created_at', 'DESC')
        //     ->findAll();
        return $this->select('participant_program_documents.*, participants.full_name, program_documents.name as document_name')
            ->join('participants', 'participants.id = participant_program_documents.participant_id')
            ->join('program_documents', 'program_documents.id = participant_program_documents.program_document_id')
            ->where('participant_program_documents.program_document_id', $programId)
            ->where('participant_program_documents.is_deleted', 0)
            ->where('participant_program_documents.is_active', 1)
            ->orderBy('participant_program_documents.created_at', 'DESC')
            ->findAll();
    }
}

?>