<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ReviewerSubthemeSeeder extends Seeder
{
    public function run()
    {
        // Sample data for reviewer subtheme assignments
        // Assumes we have reviewers with IDs 1-5 and some program subthemes exist
        $data = [
            // Reviewer 1 - Environmental themes
            [
                'abstract_reviewer_id' => 1,
                'program_subtheme_id' => 1, // Assuming subtheme ID 1 exists
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'abstract_reviewer_id' => 1,
                'program_subtheme_id' => 2, // Assuming subtheme ID 2 exists
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Reviewer 2 - Technology themes
            [
                'abstract_reviewer_id' => 2,
                'program_subtheme_id' => 3, // Assuming subtheme ID 3 exists
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'abstract_reviewer_id' => 2,
                'program_subtheme_id' => 4, // Assuming subtheme ID 4 exists
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Reviewer 3 - Social themes
            [
                'abstract_reviewer_id' => 3,
                'program_subtheme_id' => 1, // Can be assigned to multiple reviewers
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'abstract_reviewer_id' => 3,
                'program_subtheme_id' => 5, // Assuming subtheme ID 5 exists
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Reviewer 4 - Business themes
            [
                'abstract_reviewer_id' => 4,
                'program_subtheme_id' => 2,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'abstract_reviewer_id' => 4,
                'program_subtheme_id' => 6, // Assuming subtheme ID 6 exists
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Reviewer 5 - Health themes
            [
                'abstract_reviewer_id' => 5,
                'program_subtheme_id' => 3,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'abstract_reviewer_id' => 5,
                'program_subtheme_id' => 7, // Assuming subtheme ID 7 exists
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Using Query Builder
        $this->db->table('abstract_reviewer_subthemes')->insertBatch($data);
    }
}
