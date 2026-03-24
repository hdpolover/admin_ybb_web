<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExchangeRateToPrograms extends Migration
{
    public function up()
    {
        // Add usd_in_idr column to programs table so each program/batch can have its own exchange rate
        $this->forge->addColumn('programs', [
            'usd_in_idr' => [
                'type'       => 'DOUBLE',
                'null'       => true,
                'default'    => null,
                'after'      => 'is_registration_open',
                'comment'    => 'Per-program USD to IDR exchange rate. Falls back to web_settings if NULL.',
            ],
        ]);

        // Add exchange_rate column to payments table to snapshot the rate used at transaction time
        $this->forge->addColumn('payments', [
            'exchange_rate' => [
                'type'       => 'DOUBLE',
                'null'       => true,
                'default'    => null,
                'after'      => 'usd_amount',
                'comment'    => 'USD to IDR exchange rate used at the time of this payment.',
            ],
        ]);

        log_message('info', 'Migration: Added usd_in_idr to programs table and exchange_rate to payments table');
    }

    public function down()
    {
        try {
            $this->forge->dropColumn('programs', 'usd_in_idr');
        } catch (\Exception $e) {
            log_message('warning', 'Migration rollback: Could not drop usd_in_idr from programs: ' . $e->getMessage());
        }

        try {
            $this->forge->dropColumn('payments', 'exchange_rate');
        } catch (\Exception $e) {
            log_message('warning', 'Migration rollback: Could not drop exchange_rate from payments: ' . $e->getMessage());
        }

        log_message('info', 'Migration: Rolled back AddExchangeRateToPrograms');
    }
}
