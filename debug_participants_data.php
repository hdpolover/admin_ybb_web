<?php
// Direct test of the getAvailableParticipantsData method
echo "Testing getAvailableParticipantsData method directly...\n";

// Simulate DataTable request parameters
$_GET = [
    'draw' => 1,
    'start' => 0,
    'length' => 25,
    'search' => ['value' => ''],
    'payment_filter' => 'any_payment'
];

$_SERVER['REQUEST_METHOD'] = 'GET';

try {
    // Include the necessary files
    require_once 'app/Config/Autoload.php';
    require_once 'vendor/autoload.php';
    
    // Initialize CodeIgniter
    $app = \Config\Services::codeigniter();
    $app->initialize();
    
    // Create controller instance
    $controller = new \App\Controllers\Certificates();
    
    // Test with certificate ID 1 
    echo "Calling getAvailableParticipantsData(1)...\n";
    $response = $controller->getAvailableParticipantsData(1);
    
    if ($response) {
        echo "Response type: " . get_class($response) . "\n";
        $body = $response->getBody();
        echo "Response body: " . $body . "\n";
        
        $data = json_decode($body, true);
        if ($data) {
            echo "JSON decoded successfully\n";
            echo "Keys: " . implode(', ', array_keys($data)) . "\n";
            if (isset($data['recordsTotal'])) {
                echo "Total records: " . $data['recordsTotal'] . "\n";
            }
            if (isset($data['error'])) {
                echo "Error: " . $data['error'] . "\n";
            }
        } else {
            echo "Failed to decode JSON\n";
        }
    } else {
        echo "No response returned\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
?>
