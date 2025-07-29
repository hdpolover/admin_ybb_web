<?php

// Direct test of YBB Export functionality
require_once 'app/Config/Autoload.php';
require_once 'app/Config/Constants.php';

// Set up basic CodeIgniter environment
define('ENVIRONMENT', 'development');
define('WRITEPATH', __DIR__ . '/writable/');
define('APPPATH', __DIR__ . '/app/');

// Create a minimal session for testing
$_SESSION['current_program'] = 1; // Set a test program ID

// Test the YBB Export library directly
try {
    $ybbExport = new \App\Libraries\YbbExport();
    echo "YBB Export library initialized successfully\n";
    
    // Test with minimal participant data
    $testParticipants = [
        [
            'id' => 1,
            'name' => 'Test Participant',
            'email' => 'test@example.com',
            'program_id' => 1
        ]
    ];
    
    $options = [
        'filename' => 'test_export.xlsx',
        'template' => 'standard'
    ];
    
    echo "Testing participant export...\n";
    $result = $ybbExport->exportParticipants($testParticipants, $options);
    
    echo "Export result:\n";
    print_r($result);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
