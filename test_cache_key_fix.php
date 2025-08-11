<?php

/**
 * Test script to verify cache key sanitization fix
 * Tests the RedisCacheService key generation with reserved characters
 */

// Initialize CodeIgniter
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require_once FCPATH . 'system/bootstrap.php';

$app = \Config\Services::codeigniter();
$app->initialize();
$app->setContext('web');

use App\Services\RedisCacheService;

echo "Testing Cache Key Sanitization Fix\n";
echo "=================================\n\n";

try {
    $cacheService = new RedisCacheService();
    
    // Test scenarios that were causing errors
    $testCases = [
        [
            'endpoint' => 'api/web-settings',
            'parameters' => ['web_url' => 'koreayouthsummit.com'],
            'description' => 'Web settings endpoint with web_url parameter'
        ],
        [
            'endpoint' => 'api/programs/category',
            'parameters' => ['category_id' => '4'],
            'description' => 'Programs category endpoint'
        ],
        [
            'endpoint' => 'api/landing/announcements',
            'parameters' => ['web_url' => 'koreayouthsummit.com'],
            'description' => 'Landing announcements endpoint'
        ],
        [
            'endpoint' => 'api/landing/home',
            'parameters' => ['web_url' => 'koreayouthsummit.com'],
            'description' => 'Landing home endpoint'
        ]
    ];
    
    echo "Test Results:\n";
    echo "-------------\n";
    
    foreach ($testCases as $i => $testCase) {
        echo "\nTest " . ($i + 1) . ": " . $testCase['description'] . "\n";
        
        $key = $cacheService->generateKey($testCase['endpoint'], $testCase['parameters']);
        
        echo "Generated Key: {$key}\n";
        
        // Check for reserved characters
        $reservedChars = ['{', '}', '(', ')', '/', '\\', '@', ':'];
        $hasReservedChars = false;
        
        foreach ($reservedChars as $char) {
            if (strpos($key, $char) !== false) {
                $hasReservedChars = true;
                echo "❌ FOUND RESERVED CHARACTER: {$char}\n";
            }
        }
        
        if (!$hasReservedChars) {
            echo "✅ Key is clean (no reserved characters)\n";
        }
        
        echo str_repeat('-', 50) . "\n";
    }
    
    // Test TTL calculation
    echo "\nTTL Test:\n";
    echo "---------\n";
    
    foreach ($testCases as $testCase) {
        $ttl = $cacheService->getTtl($testCase['endpoint']);
        echo "Endpoint: {$testCase['endpoint']} -> TTL: {$ttl}s\n";
    }
    
    echo "\n✅ Cache Key Sanitization Test Complete!\n";
    echo "\nSummary:\n";
    echo "- All cache keys should now be free of reserved characters\n";
    echo "- Port numbers are removed from domain parts\n";
    echo "- Colons, slashes, and other reserved chars are replaced with underscores\n";
    echo "- Cache operations should work without errors\n";
    
} catch (\Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
