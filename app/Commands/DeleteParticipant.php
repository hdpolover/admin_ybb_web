<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DeleteParticipant extends BaseCommand
{
    protected $group       = 'YBB';
    protected $name        = 'participant:delete';
    protected $description = 'Soft deletes a participant by ID.';
    protected $usage       = 'participant:delete [id]';
    protected $arguments   = ['id' => 'The Participant ID to delete'];

    public function run(array $params)
    {
        $id = $params[0] ?? null;
        
        if (!$id) {
            CLI::error("Please provide a Participant ID.");
            return;
        }

        $db = \Config\Database::connect();
        $builder = $db->table('participants');
        
        $participant = $builder->where('id', $id)->get()->getRow();
        
        if (!$participant) {
            CLI::error("Participant {$id} not found.");
            return;
        }
        
        if ($participant->is_deleted == 1) {
            CLI::error("Participant {$id} is already deleted.");
            return;
        }
        
        CLI::write("Deleting Participant ID: {$id} (User ID: {$participant->user_id}, Program ID: {$participant->program_id})", 'yellow');
        
        $db->table('participants')
           ->where('id', $id)
           ->update(['is_deleted' => 1]);
           
        CLI::write("Participant {$id} soft deleted successfully.", 'green');
    }
}
