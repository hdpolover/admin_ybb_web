<?php

require_once 'vendor/autoload.php';

// Define FCPATH
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

// Load CodeIgniter framework
$pathsPath = realpath(FCPATH . '../app/Config/Paths.php');
$paths = new \Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = require_once realpath($bootstrap);

try {
    $db = \Config\Database::connect();
    $query = $db->query('DESCRIBE export_requests');
    $columns = $query->getResultArray();
    
    echo "Current columns in export_requests table:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    echo "\nChecking if batch_processing and batch_count columns exist...\n";
    
    $hasColumns = false;
    foreach ($columns as $column) {
        if (in_array($column['Field'], ['batch_processing', 'batch_count'])) {
            echo "✓ Found: {$column['Field']}\n";
            $hasColumns = true;
        }
    }
    
    if (!$hasColumns) {
        echo "❌ batch_processing and batch_count columns are missing!\n";
        echo "\nSQL to add missing columns:\n";
        echo "ALTER TABLE export_requests ADD COLUMN batch_processing TINYINT(1) DEFAULT 0;\n";
        echo "ALTER TABLE export_requests ADD COLUMN batch_count INT DEFAULT NULL;\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
