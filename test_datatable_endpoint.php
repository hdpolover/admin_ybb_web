<?php
// Simple HTTP test to check the DataTable endpoint
echo "Testing DataTable endpoint...\n";

// Set up a basic test
$awardId = 1; // Test with award ID 1
$url = "http://localhost:8080/documents/certificates/getAvailableParticipantsData/$awardId";

// Create context with GET parameters
$params = http_build_query([
    'draw' => 1,
    'start' => 0,
    'length' => 25,
    'search' => ['value' => ''],
    'payment_filter' => 'any_payment'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'Accept: application/json',
            'Content-Type: application/json'
        ]
    ]
]);

echo "Making request to: $url?$params\n";

try {
    $response = file_get_contents("$url?$params", false, $context);
    
    if ($response === false) {
        echo "Failed to get response\n";
        echo "HTTP response headers:\n";
        print_r($http_response_header ?? []);
    } else {
        echo "Response received:\n";
        echo "Length: " . strlen($response) . " bytes\n";
        
        // Try to decode JSON
        $data = json_decode($response, true);
        if ($data) {
            echo "JSON decoded successfully\n";
            echo "Keys: " . implode(', ', array_keys($data)) . "\n";
            if (isset($data['error'])) {
                echo "Error: " . $data['error'] . "\n";
            }
            if (isset($data['recordsTotal'])) {
                echo "Total records: " . $data['recordsTotal'] . "\n";
            }
        } else {
            echo "Failed to decode JSON. Raw response:\n";
            echo substr($response, 0, 500) . "\n";
        }
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
