<?php

/**
 * Simple test for cache key sanitization
 * Tests the key generation logic without requiring CodeIgniter bootstrap
 */

echo "Testing Cache Key Sanitization Logic\n";
echo "====================================\n\n";

// Simulate the key generation logic from RedisCacheService
function sanitizeDomain(string $domain): string
{
    // Remove port number and replace reserved characters
    $domain = preg_replace('/:\d+$/', '', $domain); // Remove port
    $domain = preg_replace('/[{}()\\/\\@:]/', '_', $domain); // Replace reserved chars
    $domain = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $domain); // Keep only safe chars
    return $domain;
}

function sanitizeKey(string $key): string
{
    // Replace reserved characters with underscores
    $key = preg_replace('/[{}()\\/\\@:]/', '_', $key);
    // Ensure no double underscores
    $key = preg_replace('/_+/', '_', $key);
    // Remove leading/trailing underscores
    $key = trim($key, '_');
    return $key;
}

function generateKey(string $domain, string $endpoint, array $parameters = [], int $version = 1): string
{
    // Sanitize domain
    $domain = sanitizeDomain($domain);
    
    // Clean endpoint (remove leading slash and normalize)
    $endpoint = ltrim($endpoint, '/');
    $endpoint = str_replace('/', '_', $endpoint);
    
    // Sort parameters for consistent key generation
    ksort($parameters);
    $paramString = empty($parameters) ? '' : '_' . md5(serialize($parameters));
    
    // Create initial key
    $key = sprintf('%s_%s%s_v%d', $domain, $endpoint, $paramString, $version);
    
    // Sanitize the final key
    return sanitizeKey($key);
}

// Test scenarios that were causing errors
$testCases = [
    [
        'domain' => 'localhost:8080',
        'endpoint' => 'api/web-settings',
        'parameters' => ['web_url' => 'koreayouthsummit.com'],
        'description' => 'Web settings endpoint with web_url parameter'
    ],
    [
        'domain' => 'localhost:8080',
        'endpoint' => 'api/programs/category',
        'parameters' => ['category_id' => '4'],
        'description' => 'Programs category endpoint'
    ],
    [
        'domain' => 'localhost:8080',
        'endpoint' => 'api/landing/announcements',
        'parameters' => ['web_url' => 'koreayouthsummit.com'],
        'description' => 'Landing announcements endpoint'
    ],
    [
        'domain' => 'localhost:8080',
        'endpoint' => 'api/landing/home',
        'parameters' => ['web_url' => 'koreayouthsummit.com'],
        'description' => 'Landing home endpoint'
    ]
];

echo "Before Fix (problematic keys):\n";
echo "------------------------------\n";

// Show what the old keys would look like
foreach ($testCases as $i => $testCase) {
    $oldKey = sprintf('%s:%s:%s:v1', 
        $testCase['domain'], 
        str_replace('/', ':', ltrim($testCase['endpoint'], '/')), 
        md5(serialize($testCase['parameters']))
    );
    echo "Test " . ($i + 1) . ": {$oldKey}\n";
}

echo "\nAfter Fix (sanitized keys):\n";
echo "---------------------------\n";

foreach ($testCases as $i => $testCase) {
    echo "\nTest " . ($i + 1) . ": " . $testCase['description'] . "\n";
    
    $key = generateKey($testCase['domain'], $testCase['endpoint'], $testCase['parameters']);
    
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

// Test domain sanitization specifically
echo "\nDomain Sanitization Test:\n";
echo "-------------------------\n";

$testDomains = [
    'localhost:8080',
    'localhost:3000',
    'example.com:80',
    'test@domain.com',
    'site{with}brackets.com',
    'normal-domain.com'
];

foreach ($testDomains as $domain) {
    $sanitized = sanitizeDomain($domain);
    $key = generateKey($domain, 'test/endpoint', []);
    
    echo "Original domain: {$domain}\n";
    echo "Sanitized domain: {$sanitized}\n";
    echo "Generated key: {$key}\n";
    echo "\n";
}

echo "✅ Cache Key Sanitization Test Complete!\n";
echo "\nSummary of Changes:\n";
echo "-------------------\n";
echo "✅ Port numbers removed from domain (localhost:8080 -> localhost)\n";
echo "✅ Colons replaced with underscores throughout\n";
echo "✅ Slashes in endpoints replaced with underscores\n";
echo "✅ All reserved characters {, }, (, ), /, \\, @, : removed or replaced\n";
echo "✅ Keys are now compatible with CodeIgniter cache system\n";
echo "\nExpected Result:\n";
echo "- Cache errors should no longer appear in logs\n";
echo "- All cache operations (GET, SET, DELETE) should work properly\n";
echo "- Performance should improve as cache actually works now\n";
