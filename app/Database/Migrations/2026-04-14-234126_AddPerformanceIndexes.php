<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        // users.email — full table scan on every login (230k rows, no index)
        $exists = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name   = 'users'
               AND index_name   = 'idx_users_email'"
        )->getRow();

        if ((int)$exists->cnt === 0) {
            $this->db->query('ALTER TABLE users ADD INDEX idx_users_email (email)');
        }

        // participants — add is_active to the existing common query pattern
        // (program_id + is_deleted already covered, but is_active is often added to WHERE)
        $exists2 = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name   = 'participants'
               AND index_name   = 'idx_participants_program_active_status'"
        )->getRow();

        if ((int)$exists2->cnt === 0) {
            $this->db->query(
                'ALTER TABLE participants
                 ADD INDEX idx_participants_program_active_status (program_id, is_deleted, is_active)'
            );
        }
    }

    public function down()
    {
        $this->db->query('ALTER TABLE users DROP INDEX IF EXISTS idx_users_email');
        $this->db->query('ALTER TABLE participants DROP INDEX IF EXISTS idx_participants_program_active_status');
    }
}
