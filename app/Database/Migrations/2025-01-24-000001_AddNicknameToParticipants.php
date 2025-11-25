<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNicknameToParticipants extends Migration
{
    public function up()
    {
        // Add nickname field to participants table
        $fields = [
            'nickname' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
                'after' => 'full_name'
            ]
        ];

        $this->forge->addColumn('participants', $fields);
    }

    public function down()
    {
        // Remove nickname field from participants table
        $this->forge->dropColumn('participants', 'nickname');
    }
}
