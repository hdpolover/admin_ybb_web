<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveStartEndDateFromProgramPayments extends Migration
{
    public function up()
    {
        try {
            // Drop start_date column if it exists
            $this->forge->dropColumn('program_payments', 'start_date');
            log_message('info', 'Migration: Removed start_date column from program_payments table');
        } catch (\Exception $e) {
            log_message('warning', 'Migration: start_date column may not exist or already removed: ' . $e->getMessage());
        }
        
        try {
            // Drop end_date column if it exists
            $this->forge->dropColumn('program_payments', 'end_date');
            log_message('info', 'Migration: Removed end_date column from program_payments table');
        } catch (\Exception $e) {
            log_message('warning', 'Migration: end_date column may not exist or already removed: ' . $e->getMessage());
        }
        
        log_message('info', 'Migration: RemoveStartEndDateFromProgramPayments completed - program payments now use periods table for date management');
    }

    public function down()
    {
        // Add the columns back in case we need to rollback
        $fields = [
            'start_date' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'Legacy start date - moved to program_payment_periods table'
            ],
            'end_date' => [
                'type'       => 'DATETIME', 
                'null'       => true,
                'comment'    => 'Legacy end date - moved to program_payment_periods table'
            ]
        ];
        
        $this->forge->addColumn('program_payments', $fields);
        log_message('info', 'Migration: Rollback - Re-added start_date and end_date columns to program_payments table');
    }
}
