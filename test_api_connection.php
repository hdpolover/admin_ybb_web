<?php

// Simple test script to verify YBB Export API connection
require_once __DIR__ . '/app/Libraries/YbbExport.php';

use App\Libraries\YbbExport;

try {
    echo "Testing YBB Export API Connection...\n";
    echo "=====================================\n\n";
    
    // Initialize the library
    $ybbExport = new YbbExport();
    
    // Test connection
    $result = $ybbExport->testConnection();
    
    if ($result['success']) {
        echo "✅ CONNECTION SUCCESSFUL!\n";
        echo "Service: " . ($result['service'] ?? 'Unknown') . "\n";
        echo "Status: " . ($result['status'] ?? 'Unknown') . "\n";
        echo "Version: " . ($result['version'] ?? 'Unknown') . "\n";
        echo "Timestamp: " . ($result['timestamp'] ?? 'Unknown') . "\n";
    } else {
        echo "❌ CONNECTION FAILED!\n";
        echo "Error: " . ($result['message'] ?? 'Unknown error') . "\n";
        
        if (isset($result['error_details'])) {
            echo "Details: " . json_encode($result['error_details'], JSON_PRETTY_PRINT) . "\n";
        }
    }
    
    echo "\n=====================================\n";
    
} catch (Exception $e) {
    echo "❌ EXCEPTION OCCURRED!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
