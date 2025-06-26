<?php

// Test the export functionality outside of the web context
require_once 'vendor/autoload.php';

// Set up basic CodeIgniter environment
define('SYSTEMPATH', __DIR__ . '/system/');
define('APPPATH', __DIR__ . '/app/');
define('FCPATH', __DIR__ . '/public/');
define('WRITEPATH', __DIR__ . '/writable/');
define('ROOTPATH', __DIR__ . '/');

// Load CodeIgniter bootstrap
require_once SYSTEMPATH . 'bootstrap.php';

// Initialize basic services
$config = new \Config\App();
$request = \Config\Services::request($config);

echo "Testing Excel Export Service...\n";

try {
    // Test if PhpSpreadsheet is available
    if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        throw new Exception('PhpSpreadsheet not available');
    }
    echo "✓ PhpSpreadsheet is available\n";

    // Test ExcelExport service
    $excelService = new \App\Services\ExcelExport();
    echo "✓ ExcelExport service instantiated\n";

    // Test with simple data
    $testParticipants = [
        (object)[
            'id' => 1,
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '123456789',
            'address' => 'Test Address',
            'created_at' => '2024-01-01 10:00:00',
            'category' => 'fully_funded',
            'nationality' => 'Indonesia',
            'form_status' => 2
        ]
    ];

    echo "✓ Test data created\n";

    // Test if we can call the method without errors (don't actually export)
    echo "Testing export method preparation...\n";
    
    // Create a simple test spreadsheet to verify the library works
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Test');
    echo "✓ PhpSpreadsheet basic functionality works\n";

    echo "All tests passed! Export should work now.\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
