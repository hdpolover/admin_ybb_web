<?php
/**
 * Test script to verify export endpoints are accessible
 */

require_once __DIR__ . '/vendor/autoload.php';

$config = \Config\Services::codeigniter();

// Test URLs
$baseUrl = 'http://localhost:8080'; // Adjust if your server runs on different port
$testUrls = [
    '/admin/exports/',
    '/admin/exports/test-connection',
    '/admin/exports/storage-info'
];

echo "Testing Export Endpoints:\n";
echo "========================\n\n";

foreach ($testUrls as $url) {
    $fullUrl = $baseUrl . $url;
    echo "Testing: $fullUrl\n";
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $fullUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'User-Agent: Export Test Script'
        ]
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    
    if ($error) {
        echo "  ❌ CURL Error: $error\n";
    } else {
        echo "  📊 HTTP Status: $httpCode\n";
        if ($httpCode == 200) {
            echo "  ✅ Endpoint accessible\n";
        } elseif ($httpCode == 302) {
            echo "  🔄 Redirected (probably auth required)\n";
        } else {
            echo "  ⚠️  Unexpected status code\n";
        }
    }
    echo "\n";
}

echo "Test completed.\n";
?>
