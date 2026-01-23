<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckDuplicateParticipants extends BaseCommand
{
    protected $group       = 'YBB';
    protected $name        = 'audit:duplicates';
    protected $description = 'Finds users with duplicate participant profiles for the same program.';

    public function run(array $params)
    {
        CLI::write('Scanning for duplicate participant profiles...', 'yellow');

        $db = \Config\Database::connect();
        
        // Find user_id + program_id groupings with count > 1
        $builder = $db->table('participants');
        $builder->select('user_id, program_id, COUNT(*) as count');
        $builder->where('is_deleted', 0);
        $builder->groupBy('user_id, program_id');
        $builder->having('count >', 1);
        $query = $builder->get();
        $duplicates = $query->getResult();
        
        $totalDupes = count($duplicates);
        
        if ($totalDupes === 0) {
            CLI::write('No duplicate profiles found.', 'green');
            return;
        }

        CLI::write("Found {$totalDupes} users with duplicate profiles.", 'cyan');
        CLI::newLine();

        foreach ($duplicates as $dupe) {
            // Get user info
            $user = $db->table('users')->select('email, full_name')->where('id', $dupe->user_id)->get()->getRow();
            $email = $user ? $user->email : 'Unknown Email';
            
            CLI::write("User ID: {$dupe->user_id} ({$email}) - Program ID: {$dupe->program_id} - Count: {$dupe->count}", 'yellow');
            
            // Get profiles
            $profiles = $db->table('participants')
                           ->where('user_id', $dupe->user_id)
                           ->where('program_id', $dupe->program_id)
                           ->where('is_deleted', 0)
                           ->orderBy('created_at', 'ASC')
                           ->get()
                           ->getResult();
                           
            foreach ($profiles as $p) {
                // Check payments
                $payments = $db->table('payments')
                               ->where('participant_id', $p->id)
                               ->where('is_deleted', 0)
                               ->get()
                               ->getResult();
                               
                $paymentInfo = empty($payments) ? "No payments" : count($payments) . " payment(s)";
                
                $statusColor = 'white';
                if (!empty($payments)) {
                     $statuses = [];
                     foreach ($payments as $pmt) {
                         // Status 2 is Success/Settlement
                         $statusStr = $pmt->status;
                         if ($pmt->status == 2) {
                             $statusColor = 'green';
                             $statusStr = 'SUCCESS';
                         } elseif ($pmt->status == 1) {
                             $statusStr = 'PENDING';
                         }
                         $statuses[] = "ID:{$pmt->id} [$statusStr] Amount:{$pmt->amount}";
                     }
                     $paymentInfo .= " - " . implode(', ', $statuses);
                } else {
                    $statusColor = 'red'; // Potential to be deleted profile
                }
                
                CLI::write("  - Participant ID: {$p->id} (Created: {$p->created_at}) : {$paymentInfo}", $statusColor);
            }
            CLI::newLine();
        }
        
        CLI::write('Audit Done.', 'cyan');
    }
}
