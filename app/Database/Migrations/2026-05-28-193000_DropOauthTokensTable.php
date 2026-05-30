<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Drops the oauth_tokens table introduced by 2025-10-02-140000_CreateOauthTokensTable.
 *
 * The table backed the Gmail-API-via-OAuth email path that we removed when we
 * switched outbound mail to Resend. No application code reads or writes the
 * table anymore. Apply this migration once the Resend deployment has been
 * stable in production.
 */
class DropOauthTokensTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('oauth_tokens')) {
            $this->forge->dropTable('oauth_tokens');
        }
    }

    public function down()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'access_token' => [
                'type' => 'TEXT',
            ],
            'refresh_token' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'token_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'Bearer',
            ],
            'scope' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('email');
        $this->forge->createTable('oauth_tokens');
    }
}
