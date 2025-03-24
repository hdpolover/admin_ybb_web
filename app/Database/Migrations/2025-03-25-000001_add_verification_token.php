<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVerificationToken extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'verification_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'is_verified'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'verification_token');
    }
}