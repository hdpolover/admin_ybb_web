<?php
/**
 * Simple test for Excel export using PhpSpreadsheet directly
 */

// Define paths
define('BASEPATH', dirname(__FILE__));
define('WRITEPATH', BASEPATH . '/writable/');

// Include composer autoloader to load PhpSpreadsheet
require_once BASEPATH . '/vendor/autoload.php';

// Import PhpSpreadsheet classes
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Simple log function
function debug_log($message, $data = null) {
    $logFile = WRITEPATH . 'logs/simple_excel_test.log';
    $timestamp = date('Y-m-d H:i:s');
    $content = "[$timestamp] $message";
    
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $content .= "\n" . print_r($data, true);
        } else {
            $content .= " $data";
        }
    }
    
    $content .= "\n" . str_repeat('-', 50) . "\n";
    file_put_contents($logFile, $content, FILE_APPEND);
    
    // Also echo to stdout
    echo "$message\n";
}

// Set error handlers
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    debug_log("ERROR [$errno]: $errstr in $errfile on line $errline");
    return true;
});

set_exception_handler(function($exception) {
    debug_log("EXCEPTION: " . $exception->getMessage() . "\n" . $exception->getTraceAsString());
});

try {
    debug_log("Starting simple Excel test");
    
    // Create a new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Test Sheet');
    
    // Define headers
    $headers = ['No', 'Full Name'];
    
    // Add headers to the first row
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->getFont()->setBold(true);
        $col++;
    }
    
    // Add some test data
    $data = [
        [1, 'John Doe'],
        [2, 'Jane Smith'],
        [3, 'Bob Johnson']
    ];
    
    // Add data rows
    $row = 2;
    foreach ($data as $rowData) {
        $col = 'A';
        foreach ($rowData as $cellValue) {
            $sheet->setCellValue($col . $row, $cellValue);
            $col++;
        }
        $row++;
    }
    
    // Set auto width for columns
    foreach (range('A', 'B') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Create temp directory if it doesn't exist
    $tempDir = WRITEPATH . 'temp/';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
        debug_log("Created temp directory: $tempDir");
    }
    
    // Save the spreadsheet to a file
    $filePath = $tempDir . 'simple_test.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);
    
    debug_log("Excel file saved successfully to: $filePath");
    debug_log("File size: " . filesize($filePath) . " bytes");
    
    echo "Test completed successfully. File saved to: $filePath\n";
    
} catch (Throwable $e) {
    debug_log("FATAL ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    echo "Test failed. See log for details.\n";
}
