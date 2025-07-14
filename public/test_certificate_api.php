<?php
/**
 * Certificate API Test Script
 * 
 * This script tests the Certificate API endpoints
 * Usage: Place this file in the public directory and access via browser
 * URL: http://your-domain.com/test_certificate_api.php
 */

// Test configuration
$base_url = 'http://localhost/admin_ybb_web/public'; // Update this to your actual domain
$api_base = $base_url . '/api/certificates';

// Test data
$test_participant_id = 1; // Update with a valid participant ID
$test_program_id = 1;     // Update with a valid program ID
$test_award_id = 1;       // Update with a valid award ID

echo "<h1>Certificate API Test Results</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    .endpoint { font-weight: bold; color: #007bff; }
</style>";

/**
 * Helper function to make API requests
 */
function makeApiRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $http_code,
        'error' => $error
    ];
}

/**
 * Display test result
 */
function displayResult($title, $url, $result) {
    $class = ($result['http_code'] >= 200 && $result['http_code'] < 300) ? 'success' : 'error';
    
    echo "<div class='test $class'>";
    echo "<h3>$title</h3>";
    echo "<p class='endpoint'>$url</p>";
    echo "<p><strong>HTTP Code:</strong> {$result['http_code']}</p>";
    
    if ($result['error']) {
        echo "<p><strong>cURL Error:</strong> {$result['error']}</p>";
    }
    
    if ($result['response']) {
        $json = json_decode($result['response'], true);
        if ($json) {
            echo "<p><strong>Response:</strong></p>";
            echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<p><strong>Raw Response:</strong></p>";
            echo "<pre>" . htmlspecialchars($result['response']) . "</pre>";
        }
    }
    
    echo "</div>";
}

// Test 1: Get Participant Certificates
echo "<div class='test info'><h2>Running Certificate API Tests</h2></div>";

$url = "$api_base/participant/$test_participant_id";
$result = makeApiRequest($url);
displayResult("Test 1: Get Participant Certificates", $url, $result);

// Test 2: Get Program Certificates
$url = "$api_base/program/$test_program_id";
$result = makeApiRequest($url);
displayResult("Test 2: Get Program Certificates", $url, $result);

// Test 3: Get Certificate Stats
$url = "$api_base/stats/$test_participant_id";
$result = makeApiRequest($url);
displayResult("Test 3: Get Certificate Statistics", $url, $result);

// Test 4: Generate Certificate (uncomment if you want to test generation)
/*
$url = "$api_base/generate";
$data = [
    'participant_id' => $test_participant_id,
    'award_id' => $test_award_id
];
$result = makeApiRequest($url, 'POST', $data);
displayResult("Test 4: Generate Certificate", $url, $result);
*/

echo "<div class='test info'>";
echo "<h3>Test Configuration</h3>";
echo "<p><strong>Base URL:</strong> $base_url</p>";
echo "<p><strong>API Base:</strong> $api_base</p>";
echo "<p><strong>Test Participant ID:</strong> $test_participant_id</p>";
echo "<p><strong>Test Program ID:</strong> $test_program_id</p>";
echo "<p><strong>Test Award ID:</strong> $test_award_id</p>";
echo "<p><em>Update the test configuration at the top of this file with valid IDs from your database.</em></p>";
echo "</div>";

echo "<div class='test info'>";
echo "<h3>Available Endpoints</h3>";
echo "<ul>";
echo "<li><strong>GET</strong> /api/certificates/participant/{id} - Get participant certificates</li>";
echo "<li><strong>GET</strong> /api/certificates/program/{id} - Get program certificates</li>";
echo "<li><strong>GET</strong> /api/certificates/{id} - Get certificate details</li>";
echo "<li><strong>POST</strong> /api/certificates/generate - Generate certificate</li>";
echo "<li><strong>DELETE</strong> /api/certificates/{id} - Revoke certificate</li>";
echo "<li><strong>GET</strong> /api/certificates/stats/{id} - Get certificate stats</li>";
echo "<li><strong>POST</strong> /api/certificates/{id}/regenerate - Regenerate certificate</li>";
echo "</ul>";
echo "</div>";
?>
