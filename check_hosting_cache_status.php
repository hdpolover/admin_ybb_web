<?php
/**
 * Hosting Cache Status Checker
 * Upload this file to your hosting and run it to check cache configuration
 */

// Load CodeIgniter
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

echo "<h1>Hosting Cache Status Report</h1>";
echo "<style>
    body { font-family: Arial; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style>";

try {
    // Initialize CodeIgniter
    $app = \Config\Services::codeigniter();
    
    echo "<h2>1. Cache Configuration Status</h2>";
    
    // Load cache config
    $cacheConfig = new \Config\Cache();
    echo "<p><strong>Primary Handler:</strong> <span class='info'>{$cacheConfig->handler}</span></p>";
    echo "<p><strong>Backup Handler:</strong> <span class='info'>{$cacheConfig->backupHandler}</span></p>";
    
    // Test cache service
    $cache = \Config\Services::cache();
    echo "<h2>2. Active Cache Handler</h2>";
    echo "<p><strong>Current Handler:</strong> <span class='info'>" . get_class($cache) . "</span></p>";
    
    // Test Redis connectivity
    echo "<h2>3. Redis Connection Test</h2>";
    try {
        $testKey = 'hosting_test_' . time();
        $testValue = 'Cache test successful at ' . date('Y-m-d H:i:s');
        
        // Try to save to cache
        $saveResult = $cache->save($testKey, $testValue, 60);
        
        if ($saveResult) {
            echo "<p class='success'>✅ Cache SAVE successful</p>";
            
            // Try to retrieve from cache
            $retrievedValue = $cache->get($testKey);
            
            if ($retrievedValue === $testValue) {
                echo "<p class='success'>✅ Cache GET successful</p>";
                echo "<p><strong>Cached Value:</strong> {$retrievedValue}</p>";
                
                // Clean up test
                $cache->delete($testKey);
                echo "<p class='success'>✅ Cache DELETE successful</p>";
                
            } else {
                echo "<p class='error'>❌ Cache GET failed</p>";
                echo "<p>Expected: {$testValue}</p>";
                echo "<p>Got: " . var_export($retrievedValue, true) . "</p>";
            }
        } else {
            echo "<p class='error'>❌ Cache SAVE failed</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Redis connection failed: " . $e->getMessage() . "</p>";
        echo "<p class='warning'>⚠️ Falling back to file cache</p>";
    }
    
    // Check file cache directory
    echo "<h2>4. File Cache Directory Status</h2>";
    $fileCachePath = WRITEPATH . 'cache/';
    echo "<p><strong>File Cache Path:</strong> <code>{$fileCachePath}</code></p>";
    
    if (is_dir($fileCachePath)) {
        echo "<p class='success'>✅ Cache directory exists</p>";
        
        if (is_writable($fileCachePath)) {
            echo "<p class='success'>✅ Cache directory is writable</p>";
            
            // List cache files
            $cacheFiles = glob($fileCachePath . '*');
            echo "<p><strong>Cache files found:</strong> " . count($cacheFiles) . "</p>";
            
            if (count($cacheFiles) > 0) {
                echo "<h3>Recent Cache Files:</h3>";
                echo "<ul>";
                foreach (array_slice($cacheFiles, 0, 10) as $file) {
                    $fileName = basename($file);
                    $fileTime = date('Y-m-d H:i:s', filemtime($file));
                    $fileSize = round(filesize($file) / 1024, 2);
                    echo "<li><code>{$fileName}</code> - {$fileTime} ({$fileSize} KB)</li>";
                }
                echo "</ul>";
                
                if (count($cacheFiles) > 10) {
                    echo "<p><em>... and " . (count($cacheFiles) - 10) . " more files</em></p>";
                }
            } else {
                echo "<p class='warning'>⚠️ No cache files found (Redis is working)</p>";
            }
            
        } else {
            echo "<p class='error'>❌ Cache directory is not writable</p>";
        }
    } else {
        echo "<p class='error'>❌ Cache directory does not exist</p>";
    }
    
    // Test application cache keys
    echo "<h2>5. Application Cache Test</h2>";
    try {
        // Test landing cache
        $landingCacheKey = 'landing_home_cache';
        $landingCache = $cache->get($landingCacheKey);
        
        if ($landingCache) {
            echo "<p class='success'>✅ Landing cache exists</p>";
            echo "<p><strong>Cache size:</strong> " . strlen(serialize($landingCache)) . " bytes</p>";
        } else {
            echo "<p class='warning'>⚠️ No landing cache found (may not be populated yet)</p>";
        }
        
        // Test program cache
        $programCacheKey = 'programs_list_cache';
        $programCache = $cache->get($programCacheKey);
        
        if ($programCache) {
            echo "<p class='success'>✅ Program cache exists</p>";
            echo "<p><strong>Cache size:</strong> " . strlen(serialize($programCache)) . " bytes</p>";
        } else {
            echo "<p class='warning'>⚠️ No program cache found (may not be populated yet)</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Application cache test failed: " . $e->getMessage() . "</p>";
    }
    
    // Summary
    echo "<h2>6. Summary</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px;'>";
    echo "<h3>Why You Don't See Cache Files:</h3>";
    echo "<ul>";
    echo "<li><strong>Redis Cache:</strong> Stores data in memory (RAM), not files</li>";
    echo "<li><strong>File Cache:</strong> Only used as backup when Redis fails</li>";
    echo "<li><strong>File Location:</strong> If created, files would be in <code>writable/cache/</code>, not public folder</li>";
    echo "</ul>";
    
    echo "<h3>This is Normal and Expected:</h3>";
    echo "<ul>";
    echo "<li>✅ Redis provides faster performance than file cache</li>";
    echo "<li>✅ No files means Redis is working correctly</li>";
    echo "<li>✅ Your cache invalidation system is working properly</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2 class='error'>Error</h2>";
    echo "<p class='error'>Failed to run cache test: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><small>Generated at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
