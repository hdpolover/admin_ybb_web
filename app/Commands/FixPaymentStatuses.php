<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\PaymentModel;
use App\Models\ParticipantStatusModel;
use App\Models\ProgramPaymentModel;

class FixPaymentStatuses extends BaseCommand
{
    protected $group       = 'YBB';
    protected $name        = 'fix:payment-statuses';
    protected $description = 'Synchronizes participant_statuses based on successful payments history.';

    public function run(array $params)
    {
        CLI::write('Starting payment status synchronization...', 'yellow');

        $paymentModel = new PaymentModel();
        $statusModel = new ParticipantStatusModel();
        $programPaymentModel = new ProgramPaymentModel();

        // 1. Get all successful payments
        // We only care about payments that are "Success" (2) and not deleted
        $successfulPayments = $paymentModel->select('payments.*, program_payments.category')
            ->join('program_payments', 'program_payments.id = payments.program_payment_id')
            ->where('payments.status', 2)
            ->where('payments.is_deleted', 0)
            ->findAll();

        $total = count($successfulPayments);
        CLI::write("Found {$total} successful payments.", 'cyan');

        $updatedCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        foreach ($successfulPayments as $index => $payment) {
             // Show progress every 10 items
             if (($index + 1) % 10 === 0) {
                 CLI::write("Processing {$index} / {$total}...", 'white');
             }

             if (empty($payment->participant_id)) {
                 $skippedCount++;
                 continue;
             }

             // Determine the target status based on category
             $targetPaymentStatus = null;
             $targetGeneralStatus = null;
             
             // Normalize category
             $category = strtolower($payment->category ?? '');

             if ($category === 'registration') {
                 $targetPaymentStatus = 1; 
                 // We don't force update general_status for registration as it tracks form progress
             } 
             elseif ($category === 'batch_1' || $category === 'program_fee_1') {
                 $targetPaymentStatus = 2;
                 $targetGeneralStatus = 2;
             }
             elseif ($category === 'batch_2' || $category === 'program_fee_2') {
                 $targetPaymentStatus = 3;
                 $targetGeneralStatus = 3;
             }

             if ($targetPaymentStatus === null) {
                 // Unknown category
                 $skippedCount++;
                 continue;
             }

             // Get current status
             $statusRecord = $statusModel->getParticipantStatusById($payment->participant_id);

             if (!$statusRecord) {
                 // If status record missing, create it? 
                 // Usually participants MUST have a status record. If missing, it's a data integrity issue.
                 // We'll calculate creating one.
                 try {
                     $statusModel->addParticipantStatus($payment->participant_id);
                     $statusRecord = $statusModel->getParticipantStatusById($payment->participant_id);
                     CLI::write("Created missing status record for participant {$payment->participant_id}", 'yellow');
                 } catch (\Exception $e) {
                     CLI::error("Failed to create status for participant {$payment->participant_id}: " . $e->getMessage());
                     $errorCount++;
                     continue;
                 }
             }

             // Check if update is needed
             // logic: If current status is LOWER than target, update it.
             // We generally assume payments push you strictly forward.
             
             $needsUpdate = false;
             $updates = [];

             // Check Payment Status
             if ((int)$statusRecord->payment_status < $targetPaymentStatus) {
                 $updates['payment_status'] = $targetPaymentStatus;
                 $needsUpdate = true;
             } elseif ((int)$statusRecord->payment_status !== $targetPaymentStatus && $targetPaymentStatus > 1) {
                 // If status is mismatch but not necessarily lower (e.g. batch 2 paid but status is batch 1),
                 // we usually want the HIGHEST status.
                 // Here we just ensure we upgrade.
                 
                 // If current is 3 (batch 2) and we process a batch 1 payment (target 2), we DO NOT downgrade.
                 // So the '<' check handles it correctly.
             }

             // Check General Status (Only for Program Fees)
             if ($targetGeneralStatus !== null) {
                 if ((int)$statusRecord->general_status < $targetGeneralStatus) {
                     $updates['general_status'] = $targetGeneralStatus;
                     $needsUpdate = true;
                 }
             }

             if ($needsUpdate && !empty($updates)) {
                 try {
                     $statusModel->update($statusRecord->id, $updates);
                     $updatedCount++;
                     
                     CLI::write(
                         "Updated Participant {$payment->participant_id}: " . 
                         "PaymentStatus {$statusRecord->payment_status}->{$targetPaymentStatus} " .
                         ($targetGeneralStatus ? "GeneralStatus {$statusRecord->general_status}->{$targetGeneralStatus}" : ""), 
                         'green'
                     );
                 } catch (\Exception $e) {
                     CLI::error("Error updating participant {$payment->participant_id}: " . $e->getMessage());
                     $errorCount++;
                 }
             } else {
                 $skippedCount++;
             }
        }

        CLI::write("--------------------------------", 'white');
        CLI::write("Synchronization Complete.", 'green');
        CLI::write("Total Processed: {$total}", 'white');
        CLI::write("Updated: {$updatedCount}", 'green');
        CLI::write("Skipped (Already Correct/Unknown Category): {$skippedCount}", 'yellow');
        CLI::write("Errors: {$errorCount}", 'red');
    }
}
