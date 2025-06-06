<?php
namespace App\Models;

use CodeIgniter\Model;

class ParticipantProgramDocumentModel extends Model
{
    protected $table = 'participant_program_documents';
    protected $allowedFields = [
        'participant_id',
        'program_document_id',
        'file_url',
        'status',
        'notes',
        'created_at',
    ];
    public $timestamps = false;
}

?>