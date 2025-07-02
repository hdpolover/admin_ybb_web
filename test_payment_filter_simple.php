<?php
// Simple test to verify payment filtering implementation
require_once 'preload.php';

// Set up CodeIgniter environment
$app = \Config\Services::codeigniter();
$app->initialize();

// Test different payment filter values
$testFilters = ['all', 'any_payment', 'registration', 'program_fee_1', 'program_fee_2'];

echo "Testing Payment Filter Implementation\n";
echo "====================================\n\n";

foreach ($testFilters as $filter) {
    echo "Testing filter: {$filter}\n";
    
    // Simulate request data
    $_GET = [
        'draw' => 1,
        'start' => 0,
        'length' => 5,
        'payment_filter' => $filter
    ];
    
    try {
        // Create controller instance
        $certificates = new \App\Controllers\Certificates();
        
        // Call the method (certificate_id parameter is required)
        $response = $certificates->getAvailableParticipantsData(1);
        
        if ($response) {
            $data = json_decode($response->getBody(), true);
            if ($data && isset($data['recordsTotal'])) {
                echo "  ✓ Filter '{$filter}' returned {$data['recordsTotal']} total records\n";
                echo "  ✓ Filter '{$filter}' returned {$data['recordsFiltered']} filtered records\n";
                echo "  ✓ Data count: " . count($data['data']) . " records\n";
            } else {
                echo "  ✗ Invalid response format for filter '{$filter}'\n";
            }
        } else {
            echo "  ✗ No response for filter '{$filter}'\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Error with filter '{$filter}': " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "Test completed.\n";
?>
