<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ReviewerSeeder extends Seeder
{    public function run()
    {
        $data = [
            [
                'program_id' => 1, // Assuming program ID 1 exists
                'name' => 'Dr. Jane Smith',
                'email' => 'reviewer1@example.com',
                'password' => password_hash('reviewer123', PASSWORD_DEFAULT),
                'role' => 'reviewer',
                'institution' => 'Environmental Science University, Department of Climate Change Studies',
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_id' => 1,
                'name' => 'Prof. Michael Johnson',
                'email' => 'reviewer2@example.com',
                'password' => password_hash('reviewer123', PASSWORD_DEFAULT),
                'role' => 'reviewer',
                'institution' => 'Technology Institute, Innovation and Digital Transformation Center',
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_id' => 1,
                'name' => 'Dr. Sarah Wilson',
                'email' => 'reviewer3@example.com',
                'password' => password_hash('reviewer123', PASSWORD_DEFAULT),
                'role' => 'reviewer',
                'institution' => 'Social Sciences College, Department of Psychology and Human Behavior',
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_id' => 1,
                'name' => 'Prof. David Brown',
                'email' => 'reviewer4@example.com',
                'password' => password_hash('reviewer123', PASSWORD_DEFAULT),
                'role' => 'reviewer',
                'institution' => 'Business School, Department of Economics and Entrepreneurship',
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_id' => 1,
                'name' => 'Dr. Lisa Chen',
                'email' => 'reviewer5@example.com',
                'password' => password_hash('reviewer123', PASSWORD_DEFAULT),
                'role' => 'reviewer',
                'institution' => 'Medical University, School of Public Health Sciences',
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Using Query Builder
        $this->db->table('abstract_reviewers')->insertBatch($data);
    }
}
