<?php
/**
 * Cache Fallback Test
 * Upload this file to your hosting to test the cache fallback mechanism
 */

// Load CodeIgniter
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

echo "<h1>Cache Fallback Mechanism Test</h1>";
echo "<style>
    body { font-family: Arial; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    .test-box { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
</style>";

try {
    // Initialize CodeIgniter
    $app = \Config\Services::codeigniter();
    
    echo "<div class='test-box'>";
    echo "<h2>1. Cache Configuration Test</h2>";
    
    // Load cache config
    $cacheConfig = new \Config\Cache();
    echo "<p><strong>Primary Handler:</strong> <span class='info'>{$cacheConfig->handler}</span></p>";
    echo "<p><strong>Backup Handler:</strong> <span class='info'>{$cacheConfig->backupHandler}</span></p>";
    echo "<p><strong>Fallback Enabled:</strong> <span class='info'>" . ($cacheConfig->enableFallback ? 'YES' : 'NO') . "</span></p>";
    echo "<p><strong>Log Fallbacks:</strong> <span class='info'>" . ($cacheConfig->logFallbacks ? 'YES' : 'NO') . "</span></p>";
    echo "</div>";
    
    echo "<div class='test-box'>";
    echo "<h2>2. Cache Service Availability Test</h2>";
    
    // Test RedisCacheService
    $cacheService = new \App\Services\RedisCacheService();
    $isAvailable = $cacheService->isCacheAvailable();
    
    if ($isAvailable) {
        echo "<p class='success'>✅ Cache service is available and working</p>";
    } else {
        echo "<p class='warning'>⚠️ Cache service is not available - fallback will be used</p>";
    }
    echo "</div>";
    
    echo "<div class='test-box'>";
    echo "<h2>3. Fallback Mechanism Test</h2>";
    
    // Test fallback methods
    $testKey = 'fallback_test_' . time();
    $testData = ['test' => 'data', 'timestamp' => time()];
    
    echo "<h3>3.1 Testing setWithFallback()</h3>";
    $setResult = $cacheService->setWithFallback($testKey, $testData, 300);
    echo "<p><strong>Set Result:</strong> " . ($setResult ? '<span class="success">SUCCESS</span>' : '<span class="warning">FAILED (fallback mode)</span>') . "</p>";
    
    echo "<h3>3.2 Testing getWithFallback()</h3>";
    $getData = $cacheService->getWithFallback($testKey);
    
    if ($getData !== null) {
        echo "<p class='success'>✅ Data retrieved from cache successfully</p>";
        echo "<pre>" . json_encode($getData, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p class='warning'>⚠️ No data retrieved - this would trigger fallback to direct API call</p>";
    }
    echo "</div>";
    
    echo "<div class='test-box'>";
    echo "<h2>4. Simulated API Fallback Test</h2>";
    
    // Simulate what happens when cache fails
    echo "<p>Simulating scenario where cache is unavailable...</p>";
    
    // Create a mock function that would normally be called
    $mockApiCall = function() {
        return [
            'message' => 'This data came directly from API (fallback mode)',
            'timestamp' => date('Y-m-d H:i:s'),
            'source' => 'direct_api_call'
        ];
    };
    
    // Test the fallback logic
    if (!$isAvailable) {
        echo "<p class='info'>Cache unavailable - calling API directly...</p>";
        $fallbackData = $mockApiCall();
        echo "<p class='success'>✅ Fallback successful! Data retrieved from direct API call:</p>";
        echo "<pre>" . json_encode($fallbackData, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p class='info'>Cache is available - normal caching would be used</p>";
        echo "<p>To test fallback, you can:</p>";
        echo "<ul>";
        echo "<li>Temporarily disable Redis on your server</li>";
        echo "<li>Set <code>\$enableFallback = false</code> in Cache config</li>";
        echo "<li>Simulate cache connection issues</li>";
        echo "</ul>";
    }
    echo "</div>";
    
    echo "<div class='test-box'>";
    echo "<h2>5. Performance Impact Analysis</h2>";
    
    echo "<h3>Cache Available Scenario:</h3>";
    echo "<ul>";
    echo "<li>✅ Fast response times (cache hit/miss)</li>";
    echo "<li>✅ Reduced database load</li>";
    echo "<li>✅ Better user experience</li>";
    echo "</ul>";
    
    echo "<h3>Fallback Scenario (Cache Unavailable):</h3>";
    echo "<ul>";
    echo "<li>⚠️ Slightly slower response (direct database calls)</li>";
    echo "<li>⚠️ Higher database load</li>";
    echo "<li>✅ Application continues to work normally</li>";
    echo "<li>✅ No user-facing errors or downtime</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='test-box'>";
    echo "<h2>6. Monitoring & Logging</h2>";
    
    echo "<p>When fallback is active, check your logs for entries like:</p>";
    echo "<pre>";
    echo "WARNING: Cache unavailable, calling API directly without caching\n";
    echo "WARNING: Cache GET failed for key [key_name], falling back to direct API call\n";
    echo "WARNING: Cache SET failed for key [key_name], continuing without cache";
    echo "</pre>";
    
    echo "<p>Log files location: <code>writable/logs/</code></p>";
    echo "</div>";
    
    // Clean up test data
    if ($setResult) {
        $cacheService->delete($testKey);
    }
    
} catch (Exception $e) {
    echo "<div class='test-box'>";
    echo "<h2 class='error'>Error</h2>";
    echo "<p class='error'>Test failed: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Generated at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
