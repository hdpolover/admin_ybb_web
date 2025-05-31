<?php

// Load the CodeIgniter bootstrap
require_once 'system/bootstrap.php';

// Create DB connection
$db = \Config\Database::connect();

// Check if program_id is provided
$program_id = isset($argv[1]) ? $argv[1] : null;

// Build query
$builder = $db->table('abstract_topics');
if ($program_id) {
    $builder->where('program_id', $program_id);
}
$topics = $builder->get()->getResult();

// Display topics
echo "Abstract Topics:\n";
echo "---------------\n";
if (count($topics) > 0) {
    foreach ($topics as $topic) {
        echo "ID: " . $topic->id . "\n";
        echo "Program ID: " . $topic->program_id . "\n";
        echo "Name: " . $topic->name . "\n";
        echo "Description: " . ($topic->description ?? 'N/A') . "\n";
        echo "Status: " . ($topic->is_active == 1 ? 'Active' : 'Inactive') . "\n";
        echo "---------------\n";
    }
} else {
    echo "No topics found" . ($program_id ? " for program ID $program_id" : "") . ".\n";
}
