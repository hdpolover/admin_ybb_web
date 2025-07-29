<?php

// Simple cURL test to verify YBB Export API connection directly
$apiUrl = 'https://ybb-data-management-service-production.up.railway.app';

echo "Testing YBB Export API Connection...\n";
echo "=====================================\n\n";
echo "API URL: $apiUrl\n\n";

// Test health endpoint
$healthUrl = $apiUrl . '/health';
echo "Testing health endpoint: $healthUrl\n";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $healthUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);

curl_close($curl);

if ($response === false || !empty($error)) {
    echo "❌ CONNECTION FAILED!\n";
    echo "cURL Error: $error\n";
} elseif ($httpCode >= 200 && $httpCode < 300) {
    echo "✅ CONNECTION SUCCESSFUL!\n";
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
    
    $data = json_decode($response, true);
    if ($data) {
        echo "\nParsed Response:\n";
        echo "Status: " . ($data['status'] ?? 'Unknown') . "\n";
        echo "Timestamp: " . ($data['timestamp'] ?? 'Unknown') . "\n";
        if (isset($data['services'])) {
            echo "Services:\n";
            foreach ($data['services'] as $service => $status) {
                echo "  - $service: $status\n";
            }
        }
    }
} else {
    echo "❌ HTTP ERROR!\n";
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
}

echo "\n=====================================\n";

// Test storage info endpoint
echo "\nTesting storage info endpoint...\n";
$storageUrl = $apiUrl . '/api/ybb/storage/info';
echo "URL: $storageUrl\n";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $storageUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);

curl_close($curl);

if ($response === false || !empty($error)) {
    echo "❌ STORAGE INFO FAILED!\n";
    echo "cURL Error: $error\n";
} elseif ($httpCode >= 200 && $httpCode < 300) {
    echo "✅ STORAGE INFO SUCCESS!\n";
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
} else {
    echo "❌ STORAGE INFO HTTP ERROR!\n";
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
}

echo "\n=====================================\n";
echo "Connection test completed.\n";
