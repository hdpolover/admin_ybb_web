<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProgramPaymentPeriodsTable extends Migration
{
    public function up()
    {
        // Create the program_payment_periods table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'payment_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => false,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'comment'    => 'Period name for admin identification (e.g., Main Registration, Extension, Final Extension)',
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Optional description for the period',
            ],
            'start_date' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => 'Period start date and time',
            ],
            'end_date' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => 'Period end date and time',
            ],
            'order_number' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
                'comment'    => 'Display order for periods (1, 2, 3, etc.)',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => 'Whether this period is active (1) or inactive (0)',
            ],
            'is_deleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Soft delete flag (0 = active, 1 = deleted)',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);

        // Set primary key
        $this->forge->addPrimaryKey('id');

        // Add foreign key constraint to program_payments
        $this->forge->addForeignKey('payment_id', 'program_payments', 'id', 'CASCADE', 'CASCADE');

        // Add indexes for performance
        $this->forge->addKey('payment_id');
        $this->forge->addKey(['payment_id', 'is_active', 'is_deleted']);
        $this->forge->addKey(['start_date', 'end_date']);
        $this->forge->addKey('order_number');

        // Create the table
        $this->forge->createTable('program_payment_periods');

        // Migrate existing data from program_payments table
        $this->migrateExistingPaymentData();
    }

    public function down()
    {
        // Drop the table
        $this->forge->dropTable('program_payment_periods');
    }

    /**
     * Migrate existing payment data to the new periods structure
     */
    private function migrateExistingPaymentData()
    {
        $db = \Config\Database::connect();
        
        // Get all existing payments with their date ranges
        $query = $db->query("
            SELECT id, name, start_date, end_date 
            FROM program_payments 
            WHERE is_deleted = 0 
            AND start_date IS NOT NULL 
            AND end_date IS NOT NULL
        ");
        
        $payments = $query->getResultArray();
        
        foreach ($payments as $payment) {
            // Create the first period for each existing payment
            $periodData = [
                'payment_id'   => $payment['id'],
                'name'         => 'Main Period', // Default name for migrated data
                'description'  => 'Migrated from original payment date range',
                'start_date'   => $payment['start_date'],
                'end_date'     => $payment['end_date'],
                'order_number' => 1,
                'is_active'    => 1,
                'is_deleted'   => 0,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ];
            
            $db->table('program_payment_periods')->insert($periodData);
        }
        
        log_message('info', "Migrated " . count($payments) . " payment records to periods structure");
    }
}