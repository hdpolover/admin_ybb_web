<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTemplateTypeToProgamCertificates extends Migration
{
    public function up()
    {
        $this->forge->addColumn('program_certificates', [
            'template_type' => [
                'type' => 'ENUM',
                'constraint' => ['image', 'pdf'],
                'default' => 'image',
                'null' => false,
                'after' => 'template_url'
            ],
            'preview_url' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'template_type',
                'comment' => 'Preview image URL for PDF templates'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('program_certificates', ['template_type', 'preview_url']);
    }
}
