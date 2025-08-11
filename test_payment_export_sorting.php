<?php

require_once 'vendor/autoload.php';

// Define FCPATH
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

// Load CodeIgniter framework
$pathsPath = realpath(FCPATH . '../app/Config/Paths.php');
$paths = new \Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = require_once realpath($bootstrap);

echo "=== Payment Export Sorting Test ===\n\n";

try {
    // Initialize PaymentModel
    $paymentModel = new \App\Models\PaymentModel();
    
    // Test with program ID 1 (adjust as needed)
    $programId = 1;
    $filters = [];
    
    echo "Testing payment export sorting for Program ID: $programId\n";
    echo "Retrieving payments with latest-to-oldest sorting...\n\n";
    
    // Get payments using the export method
    $payments = $paymentModel->getNormalizedPaymentsForExport($programId, $filters);
    
    if (empty($payments)) {
        echo "❌ No payments found for program ID $programId\n";
        echo "Please adjust the program ID in the script.\n";
        exit(1);
    }
    
    echo "✅ Found " . count($payments) . " payments\n\n";
    
    echo "First 5 payments (should be newest to oldest):\n";
    echo str_repeat("=", 80) . "\n";
    
    for ($i = 0; $i < min(5, count($payments)); $i++) {
        $payment = $payments[$i];
        echo sprintf("%-3d | %-20s | %-15s | %-25s | %s\n", 
            $i + 1,
            $payment['Payment_ID'] ?? 'N/A',
            $payment['Payment_Date'] ?? 'N/A',
            $payment['Payment_Status'] ?? 'N/A',
            $payment['Participant_Name'] ?? 'N/A'
        );
    }
    
    echo str_repeat("=", 80) . "\n\n";
    
    // Check if sorting is working correctly
    $sortingCorrect = true;
    $lastDate = null;
    
    foreach ($payments as $payment) {
        $currentDate = $payment['Payment_Date'] ?? null;
        
        if ($currentDate && $lastDate) {
            if (strtotime($currentDate) > strtotime($lastDate)) {
                $sortingCorrect = false;
                break;
            }
        }
        
        $lastDate = $currentDate;
    }
    
    if ($sortingCorrect) {
        echo "✅ SORTING VERIFICATION: Payments are correctly sorted from latest to oldest\n";
    } else {
        echo "❌ SORTING VERIFICATION: Payments are NOT properly sorted\n";
    }
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error during test: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
