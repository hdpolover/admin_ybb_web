<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TestSubthemesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'program_id' => 4,
                'name' => 'Academic Research',
                'desc' => 'Academic research methodologies and findings',
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'program_id' => 4,
                'name' => 'Innovation & Technology',
                'desc' => 'Technology innovations and digital transformation',
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'program_id' => 4,
                'name' => 'Social Impact',
                'desc' => 'Social impact and community development',
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'program_id' => 4,
                'name' => 'Sustainability',
                'desc' => 'Environmental sustainability and green initiatives',
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('program_subthemes')->insertBatch($data);
    }
}
