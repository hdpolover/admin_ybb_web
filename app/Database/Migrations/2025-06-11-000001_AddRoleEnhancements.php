<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleEnhancements extends Migration
{    public function up()
    {
        $db = \Config\Database::connect();
        
        // Check if admins table exists and add role enhancements if needed
        if ($db->tableExists('admins')) {
            $fields = $db->getFieldData('admins');
            $hasRole = false;
            
            foreach ($fields as $field) {
                if ($field->name === 'role') {
                    $hasRole = true;
                    break;
                }
            }
            
            if (!$hasRole) {
                $this->forge->addColumn('admins', [
                    'role' => [
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                        'default' => 'super',
                        'after' => 'password'
                    ]
                ]);
            }
        }

        // Create reviewers table for reviewer users
        if (!$db->tableExists('reviewers')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'unique' => true,
                ],
                'password' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'role' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'reviewer',
                ],
                'specialization' => [
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
                    'type' => 'TIMESTAMP',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'TIMESTAMP',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('email');
            $this->forge->createTable('reviewers');
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        
        // Remove role column if it was added
        if ($db->tableExists('admins')) {
            $fields = $db->getFieldData('admins');
            foreach ($fields as $field) {
                if ($field->name === 'role') {
                    $this->forge->dropColumn('admins', 'role');
                    break;
                }
            }
        }

        // Drop reviewers table
        if ($db->tableExists('reviewers')) {
            $this->forge->dropTable('reviewers');
        }
    }
}
