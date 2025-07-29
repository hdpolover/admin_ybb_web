<?php
// Simple database check script
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

require_once 'vendor/autoload.php';

// Load app config
$app = \Config\Services::codeigniter();
$app->initialize();

echo "=== Database Structure Check ===\n\n";

try {
    $db = \Config\Database::connect();
    
    // Check tables
    $tables = $db->listTables();
    echo "Available tables:\n";
    foreach ($tables as $table) {
        if (strpos($table, 'participant') !== false) {
            echo "  ✓ {$table}\n";
        }
    }
    
    echo "\n=== Checking participants table ===\n";
    $fields = $db->getFieldData('participants');
    foreach ($fields as $field) {
        if (strpos($field->name, 'status') !== false || strpos($field->name, 'category') !== false) {
            echo "  - {$field->name} ({$field->type})\n";
        }
    }
    
    // Check if participant_statuses table exists
    if (in_array('participant_statuses', $tables)) {
        echo "\n=== participant_statuses table structure ===\n";
        $fields = $db->getFieldData('participant_statuses');
        foreach ($fields as $field) {
            echo "  - {$field->name} ({$field->type})\n";
        }
        
        $count = $db->query("SELECT COUNT(*) as count FROM participant_statuses")->getRow()->count;
        echo "Records count: {$count}\n";
    } else {
        echo "\n✗ participant_statuses table does NOT exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End Check ===\n";
?>
