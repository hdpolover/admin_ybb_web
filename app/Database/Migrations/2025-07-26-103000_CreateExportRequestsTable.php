<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExportRequestsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'program_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'export_type' => [
                'type' => 'ENUM',
                'constraint' => ['participants', 'payments', 'ambassadors'],
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'filters' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'custom_filename' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'record_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'success', 'error'],
                'default' => 'pending',
            ],
            'export_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'file_size' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'processing_time' => [
                'type' => 'DECIMAL',
                'constraint' => '10,3',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['program_id', 'user_id']);
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');

        $this->forge->createTable('export_requests');
    }

    public function down()
    {
        $this->forge->dropTable('export_requests');
    }
}
