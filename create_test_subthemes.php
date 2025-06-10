<?php
require 'vendor/autoload.php';
require 'spark';

$db = \Config\Database::connect();

// Insert test subthemes for program 4 (Youth Academic Forum)
$subthemes = [
    ['program_id' => 4, 'name' => 'Academic Research', 'desc' => 'Academic research methodologies and findings'],
    ['program_id' => 4, 'name' => 'Innovation & Technology', 'desc' => 'Technology innovations and digital transformation'],
    ['program_id' => 4, 'name' => 'Social Impact', 'desc' => 'Social impact and community development'],
    ['program_id' => 4, 'name' => 'Sustainability', 'desc' => 'Environmental sustainability and green initiatives']
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
?>
