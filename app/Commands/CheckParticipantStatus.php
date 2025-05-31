<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ParticipantStatusModel;

class CheckParticipantStatus extends BaseCommand
{
    protected $group = 'Custom';
    protected $name = 'check:participant';
    protected $description = 'Check participant status by ID';

    public function run(array $params)
    {
        $participantId = $params[0] ?? CLI::prompt('Participant ID');
        
        $model = new ParticipantStatusModel();
        $status = $model->getParticipantStatusById($participantId);
        
        if ($status) {
            CLI::write('Participant Status Found:');
            CLI::write("ID: {$status->id}");
            CLI::write("Participant ID: {$status->participant_id}");
            CLI::write("Form Status: {$status->form_status}");
            CLI::write("General Status: {$status->general_status}");
            CLI::write("Document Status: {$status->document_status}");
            CLI::write("Payment Status: {$status->payment_status}");
            CLI::write("Is Active: {$status->is_active}");
            CLI::write("Is Deleted: {$status->is_deleted}");
        } else {
            CLI::error('No status found for this participant');
        }
    }
}
