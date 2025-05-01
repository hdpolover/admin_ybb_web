<?php
// Direct Excel Generator - Simplest possible implementation
// Place this in the public folder for easy access

// Include autoloader for PhpSpreadsheet
require dirname(__DIR__) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Simple Test');
    
    // Add some test data
    $sheet->setCellValue('A1', 'Name');
    $sheet->setCellValue('B1', 'Age');
    $sheet->getStyle('A1:B1')->getFont()->setBold(true);
    
    $sheet->setCellValue('A2', 'John Doe');
    $sheet->setCellValue('B2', '25');
    
    $sheet->setCellValue('A3', 'Jane Smith');
    $sheet->setCellValue('B3', '30');
    
    // Auto-size columns
    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->getColumnDimension('B')->setAutoSize(true);
    
    // Create Xlsx writer
    $writer = new Xlsx($spreadsheet);
    
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Generate a unique filename with timestamp
    $filename = 'simple_excel_' . date('YmdHis') . '.xlsx';
    
    // Set headers for direct download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Save directly to output
    $writer->save('php://output');
    exit;
    
} catch (Exception $e) {
    echo '<h1>Error creating Excel file</h1>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
