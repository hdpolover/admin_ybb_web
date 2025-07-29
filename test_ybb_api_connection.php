<?php

// Simple test to check if YBB Export API is reachable
$apiUrl = 'https://ybb-data-management-service-production.up.railway.app';

echo "Testing YBB Export API connection...\n";
echo "API URL: $apiUrl\n\n";

// Test basic connectivity
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl . '/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Health Check Response:\n";
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
if ($error) {
    echo "cURL Error: $error\n";
}

echo "\n" . str_repeat("-", 50) . "\n";

// Test templates endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl . '/api/templates');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Templates Endpoint Response:\n";
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
if ($error) {
    echo "cURL Error: $error\n";
}

echo "\nTest completed.\n";
