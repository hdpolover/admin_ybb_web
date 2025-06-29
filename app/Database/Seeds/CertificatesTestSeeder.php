<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CertificatesTestSeeder extends Seeder
{
    public function run()
    {
        // Sample awards for testing
        $awards = [
            [
                'program_id' => 5, // Update this to match your current program
                'title' => 'Best Paper Award',
                'description' => 'Awarded to the best research paper submitted',
                'award_type' => 'winner',
                'order_number' => 1,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'program_id' => 5,
                'title' => 'Outstanding Presentation',
                'description' => 'Awarded for exceptional presentation skills',
                'award_type' => 'runner_up',
                'order_number' => 2,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'program_id' => 5,
                'title' => 'Innovation Award',
                'description' => 'Awarded for innovative ideas and solutions',
                'award_type' => 'mention',
                'order_number' => 3,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'program_id' => 5,
                'title' => 'Participation Certificate',
                'description' => 'Certificate of participation for all participants',
                'award_type' => 'other',
                'order_number' => 4,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert awards
        $this->db->table('program_awards')->insertBatch($awards);
        
        echo "Sample awards created successfully!\n";
    }
}
