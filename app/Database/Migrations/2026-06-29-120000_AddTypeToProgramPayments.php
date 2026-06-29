<?php
// app/Database/Migrations/2026-06-29-120000_AddTypeToProgramPayments.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds the `type` funding-category column to `program_payments`.
 *
 * The application already reads/writes this column (CreateProgram seeds
 * self_funded/fully_funded rows, ProgramPaymentModel allows it, and the
 * participant Payments controller filters visibility by it) but no migration
 * ever created it. On a fresh or restored DB the column is absent, so the
 * runtime fallback `$payment['type'] ?? 'all'` makes every payment visible to
 * every participant regardless of funding category.
 *
 * Idempotent: only adds the column when it does not already exist, so it is
 * safe to run against a production DB where the column was added manually.
 */
class AddTypeToProgramPayments extends Migration
{
    public function up(): void
    {
        if ($this->columnExists('type')) {
            log_message('info', 'Migration: program_payments.type already exists, skipping add.');
            return;
        }

        $this->db->query("
            ALTER TABLE program_payments
            ADD COLUMN type ENUM('all','self_funded','fully_funded') NOT NULL DEFAULT 'all' AFTER category
        ");

        // Backfill legacy rows to 'all' so existing payments stay visible to
        // every participant, matching the prior runtime fallback behaviour.
        $this->db->query("
            UPDATE program_payments
            SET type = 'all'
            WHERE type IS NULL OR type = ''
        ");

        log_message('info', 'Migration: program_payments.type column added and backfilled to "all".');
    }

    public function down(): void
    {
        if (!$this->columnExists('type')) {
            return;
        }

        $this->db->query("
            ALTER TABLE program_payments
            DROP COLUMN type
        ");
    }

    private function columnExists(string $column): bool
    {
        $database = $this->db->getDatabase();

        $row = $this->db->query(
            "SELECT COUNT(*) AS c
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = 'program_payments'
               AND COLUMN_NAME = ?",
            [$database, $column]
        )->getRow();

        return $row !== null && (int) $row->c > 0;
    }
}
