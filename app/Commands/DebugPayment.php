<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DebugPayment extends BaseCommand
{
    protected $group       = 'YBB';
    protected $name        = 'debug:payment';
    protected $description = 'Inspects payment details for a specific user email.';

    public function run(array $params)
    {
        $email = '07samha@gmail.com';
        CLI::write("Inspecting for user: {$email}", 'yellow');

        $db = \Config\Database::connect();
        
        // 1. Find User
        $user = $db->table('users')->where('email', $email)->get()->getRow();
        
        if (!$user) {
            CLI::error("User not found.");
            return;
        }
        
        CLI::write("User Found: ID {$user->id}, Name: {$user->full_name}", 'green');
        
        // 2. Find Participants
        $participants = $db->table('participants')
                           ->select('participants.*, programs.name as program_name')
                           ->join('programs', 'programs.id = participants.program_id')
                           ->where('user_id', $user->id)
                           ->where('participants.is_deleted', 0)
                           ->get()
                           ->getResult();
                           
        if (empty($participants)) {
            CLI::error("No participants found for this user.");
            return;
        }
        
        foreach ($participants as $p) {
            CLI::newLine();
            CLI::write("Participant ID: {$p->id} - Program: {$p->program_name} (ID: {$p->program_id}) - Created: {$p->created_at}", 'cyan');
            
            // Limit to Middle East Youth Summit 2026 (ID 12) if that's the only one we care about
            if ($p->program_id != 12) continue;

            // 3. Check Participant Status
            $status = $db->table('participant_statuses')->where('participant_id', $p->id)->get()->getRow();
            if ($status) {
                CLI::write("  [Status Table]: Payment Status = {$status->payment_status}, General Status = {$status->general_status}", 'white');
            } else {
                CLI::error("  [Status Table]: Record MISSING");
            }
            
            // 4. Check Payments with Category
            $payments = $db->table('payments')
                           ->select('payments.*, program_payments.category as payment_category, program_payments.name as payment_name')
                           ->join('program_payments', 'program_payments.id = payments.program_payment_id', 'left')
                           ->where('participant_id', $p->id)
                           ->where('payments.is_deleted', 0)
                           ->get()
                           ->getResult();
                           
            if (empty($payments)) {
                CLI::write("  [Payments Table]: No records found.", 'red');
            } else {
                foreach ($payments as $pmt) {
                    $statusStr = match((int)$pmt->status) {
                        1 => 'PENDING',
                        2 => 'SUCCESS',
                        3 => 'FAILED',
                        default => 'UNKNOWN (' . $pmt->status . ')'
                    };
                    $color = ($pmt->status == 2) ? 'green' : 'white';
                    CLI::write("  [Payments Table] ID: {$pmt->id} - Amount: {$pmt->amount} - Status: {$statusStr} - Category: {$pmt->payment_category}", $color);
                }
            }
        }
        
        CLI::newLine();
    }
}
