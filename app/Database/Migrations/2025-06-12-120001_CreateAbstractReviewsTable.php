<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAbstractFeedbacksTable extends Migration
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
            'abstract_version_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'abstract_reviewer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'feedback' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'is_deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
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
        $this->forge->addKey(['abstract_version_id', 'abstract_reviewer_id']);
        $this->forge->addKey('abstract_reviewer_id');
        
        // Add foreign key constraints (if the related tables exist)
        // $this->forge->addForeignKey('abstract_version_id', 'abstract_versions', 'id', 'CASCADE', 'CASCADE');
        // $this->forge->addForeignKey('abstract_reviewer_id', 'abstract_reviewers', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('abstract_feedbacks');
    }

    public function down()
    {
        $this->forge->dropTable('abstract_feedbacks');
    }
}
