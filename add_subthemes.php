<?php

require 'vendor/autoload.php';
require 'spark';

try {
    $db = \Config\Database::connect();
    
    $subthemes = [
        ['program_id' => 4, 'name' => 'Academic Research', 'desc' => 'Academic research and methodology'],
        ['program_id' => 4, 'name' => 'Innovation & Technology', 'desc' => 'Technology and innovation topics'],
        ['program_id' => 4, 'name' => 'Social Impact', 'desc' => 'Social impact and community development']
    ];
    
    foreach ($subthemes as $subtheme) {
        $data = array_merge($subtheme, [
            'is_active' => 1,
            'is_deleted' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $db->table('program_subthemes')->insert($data);
        echo "Inserted subtheme: " . $subtheme['name'] . "\n";
    }
    
    echo "Test subthemes created for Youth Academic Forum (Program 4)\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
