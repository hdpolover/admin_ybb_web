<?php
// Check database structure for participant_statuses table
require_once 'vendor/autoload.php';

// Load CodeIgniter framework
$pathsPath = realpath(FCPATH . '../app/Config/Paths.php');
$paths = new \Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = require_once realpath($bootstrap);

$db = \Config\Database::connect();

echo "=== Checking participant_statuses table ===\n\n";

// Check if table exists
$tables = $db->listTables();
if (in_array('participant_statuses', $tables)) {
    echo "✓ participant_statuses table exists\n\n";
    
    // Get table structure
    $fields = $db->getFieldData('participant_statuses');
    echo "Table structure:\n";
    foreach ($fields as $field) {
        echo "  - {$field->name} ({$field->type}";
        if ($field->max_length) {
            echo ", max_length: {$field->max_length}";
        }
        echo ")\n";
    }
    
    // Check sample data
    $query = $db->query("SELECT * FROM participant_statuses LIMIT 5");
    $results = $query->getResult();
    
    echo "\nSample data (first 5 records):\n";
    if (empty($results)) {
        echo "  No data found in participant_statuses table\n";
    } else {
        foreach ($results as $row) {
            echo "  ID: {$row->id}, participant_id: {$row->participant_id}";
            if (isset($row->form_status)) {
                echo ", form_status: {$row->form_status}";
            }
            if (isset($row->general_status)) {
                echo ", general_status: {$row->general_status}";
            }
            if (isset($row->document_status)) {
                echo ", document_status: {$row->document_status}";
            }
            echo "\n";
        }
    }
    
    // Check count
    $count = $db->query("SELECT COUNT(*) as count FROM participant_statuses")->getRow()->count;
    echo "\nTotal records: {$count}\n";
    
} else {
    echo "✗ participant_statuses table does NOT exist\n";
    echo "Available tables:\n";
    foreach ($tables as $table) {
        if (strpos($table, 'participant') !== false) {
            echo "  - {$table}\n";
        }
    }
}

echo "\n=== Checking participants table structure ===\n";
$fields = $db->getFieldData('participants');
echo "Participants table structure:\n";
foreach ($fields as $field) {
    echo "  - {$field->name} ({$field->type}";
    if ($field->max_length) {
        echo ", max_length: {$field->max_length}";
    }
    echo ")\n";
}

// Check if participants table has status fields directly
echo "\nChecking for status fields in participants table:\n";
foreach ($fields as $field) {
    if (strpos($field->name, 'status') !== false) {
        echo "  ✓ {$field->name}\n";
    }
}

echo "\n=== End Check ===\n";
?>
