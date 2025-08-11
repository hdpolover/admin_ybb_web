<?php

// Test the participant export with limited records
echo "=== Testing Participant Export with Limited Records ===\n\n";

try {
    // Simple direct test
    $url = "http://localhost/admin_ybb_web/exports/participants";
    
    $postData = json_encode([
        'program_ids' => [1]
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    echo "Sending request to: $url\n";
    echo "POST data: $postData\n\n";
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    echo "HTTP Status Code: $httpCode\n";
    
    if ($error) {
        echo "cURL Error: $error\n";
    }
    
    if ($response) {
        echo "Response: $response\n";
        
        // Try to decode JSON response
        $jsonResponse = json_decode($response, true);
        if ($jsonResponse) {
            echo "\nParsed Response:\n";
            print_r($jsonResponse);
        }
    } else {
        echo "No response received\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
