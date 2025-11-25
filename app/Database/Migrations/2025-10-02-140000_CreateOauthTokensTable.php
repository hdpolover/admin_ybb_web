<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOauthTokensTable extends Migration
{
    public function up()
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

    public function down()
    {
        $this->forge->dropTable('oauth_tokens');
    }
}