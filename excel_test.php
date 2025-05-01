<?php
// Simplified test script
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    // Create a new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Test Sheet");
    
    // Add some data
    $sheet->setCellValue("A1", "No");
    $sheet->setCellValue("B1", "Name");
    $sheet->setCellValue("A2", "1");
    $sheet->setCellValue("B2", "John Doe");
    
    // Create writer
    $writer = new Xlsx($spreadsheet);
    
    // Save to file
    $filePath = __DIR__ . "/writable/test_excel.xlsx";
    $writer->save($filePath);
    
    echo "Excel file created successfully at: " . $filePath;
    echo PHP_EOL . "File size: " . filesize($filePath) . " bytes";
} catch (Exception $e) {
    echo "Error creating Excel file: " . $e->getMessage();
}
