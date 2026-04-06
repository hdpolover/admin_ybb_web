<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGatewayProviderToPaymentMethods extends Migration
{
    public function up(): void
    {
        $this->db->query("
            ALTER TABLE payment_methods
            ADD COLUMN gateway_provider VARCHAR(50) NULL DEFAULT NULL AFTER type
        ");

        // Backfill: all existing 'gateway' type records are Midtrans
        $this->db->query("
            UPDATE payment_methods
            SET gateway_provider = 'midtrans'
            WHERE type = 'gateway' AND gateway_provider IS NULL
        ");

        log_message('info', 'Migration: gateway_provider column added and backfilled for Midtrans records.');
    }

    public function down(): void
    {
        $this->db->query("
            ALTER TABLE payment_methods
            DROP COLUMN gateway_provider
        ");
    }
}
