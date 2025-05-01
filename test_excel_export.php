<?php

/**
 * Simple test script for Excel export functionality
 * This standalone script tests the Excel export functionality without any dependencies on the application
 */

// Define base path and writable path
define('BASEPATH', dirname(__FILE__));
define('WRITEPATH', BASEPATH . '/writable/');

// Load the CodeIgniter framework
require BASEPATH . '/app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '/ ') . '/bootstrap.php';

// Include helpers
require_once 'app/Helpers/ExcelHelper.php'; 

// Simple function to write to debug log
function debug_log($message, $data = null) {
    $logFile = WRITEPATH . 'logs/excel_test.log';
    $timestamp = date('Y-m-d H:i:s');
    $content = "[$timestamp] $message";
    
    if ($data !== null) {
        $content .= "\n" . print_r($data, true);
    }
    
    $content .= "\n" . str_repeat('-', 50) . "\n";
    file_put_contents($logFile, $content, FILE_APPEND);
}

// Clear previous log
file_put_contents(WRITEPATH . 'logs/excel_test.log', "=== Excel Export Test Log ===\n\n");

// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    debug_log("ERROR [$errno]: $errstr in $errfile on line $errline");
    return true;
});

// Set exception handler
set_exception_handler(function($exception) {
    debug_log("EXCEPTION: " . $exception->getMessage() . "\n" . $exception->getTraceAsString());
});

try {
    debug_log("Starting Excel export test");
    
    // Create simple test data - just names
    $headers = ['No', 'Full Name'];
    $data = [
        [1, 'John Doe'],
        [2, 'Jane Smith'],
        [3, 'Bob Johnson']
    ];
    
    debug_log("Test data created", $data);
    
    // Test the export function
    debug_log("Calling export_to_excel");
    
    // Try using a simple absolute path for saving
    $savePath = WRITEPATH . 'temp/';
    
    // Make sure directory exists
    if (!is_dir($savePath)) {
        mkdir($savePath, 0755, true);
        debug_log("Created directory: $savePath");
    }
    
    // Export without downloading (save to disk)
    $result = export_to_excel(
        'test_export',
        $headers,
        $data,
        'Test Sheet',
        [5, 30],
        $savePath,
        false
    );
    
    debug_log("Export result", $result);
    
    echo "Test completed. Check log file: " . WRITEPATH . "logs/excel_test.log";
    
} catch (Throwable $e) {
    debug_log("FATAL ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    echo "Test failed. Check log file: " . WRITEPATH . "logs/excel_test.log";
}
