<?php
/**
 * Cache Diagnostics Test
 * Upload this file to your public folder and access it via browser
 * URL: https://yourdomain.com/test_cache_diagnostics.php
 */

echo "<h1>Cache Diagnostics for YBB App</h1>";

// Test 1: Check Redis availability
echo "<h2>1. Testing Redis Connection</h2>";
try {
    if (class_exists('Redis')) {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->set('test_key', 'test_value_' . time());
        $value = $redis->get('test_key');
        echo "✅ Redis is working! Test value: " . $value . "<br>";
        $redis->del('test_key');
    } else {
        echo "❌ Redis class not available<br>";
    }
} catch (Exception $e) {
    echo "❌ Redis error: " . $e->getMessage() . "<br>";
}

// Test 2: Check CodeIgniter Cache Service
echo "<h2>2. Testing CodeIgniter Cache Service</h2>";
try {
    // Include CodeIgniter's autoloader if available
    if (file_exists('../vendor/autoload.php')) {
        require_once '../vendor/autoload.php';
    } elseif (file_exists('../system/bootstrap.php')) {
        require_once '../system/bootstrap.php';
    } else {
        echo "❌ Cannot load CodeIgniter framework<br>";
    }
    
    // Try to use CodeIgniter's cache service
    if (class_exists('Config\Services')) {
        $cache = \Config\Services::cache();
        $testKey = 'diagnostic_test_' . time();
        $testValue = 'Cache test value: ' . date('Y-m-d H:i:s');
        
        $cache->save($testKey, $testValue, 60);
        $retrieved = $cache->get($testKey);
        
        if ($retrieved === $testValue) {
            echo "✅ CodeIgniter cache is working! Stored and retrieved: " . $retrieved . "<br>";
        } else {
            echo "❌ CodeIgniter cache failed. Stored: " . $testValue . ", Retrieved: " . ($retrieved ?: 'null') . "<br>";
        }
        
        $cache->delete($testKey);
    } else {
        echo "❌ CodeIgniter Services not available<br>";
    }
} catch (Exception $e) {
    echo "❌ CodeIgniter cache error: " . $e->getMessage() . "<br>";
}

// Test 3: Check file system permissions
echo "<h2>3. Testing File System Permissions</h2>";
$writablePath = '../writable/cache/';
if (is_dir($writablePath)) {
    echo "✅ Cache directory exists: " . realpath($writablePath) . "<br>";
    
    if (is_writable($writablePath)) {
        echo "✅ Cache directory is writable<br>";
        
        // Try to create a test file
        $testFile = $writablePath . 'test_file_' . time() . '.txt';
        if (file_put_contents($testFile, 'test content')) {
            echo "✅ Can create files in cache directory<br>";
            unlink($testFile); // Clean up
        } else {
            echo "❌ Cannot create files in cache directory<br>";
        }
    } else {
        echo "❌ Cache directory is not writable<br>";
    }
} else {
    echo "❌ Cache directory does not exist: " . $writablePath . "<br>";
}

// Test 4: Check PHP extensions
echo "<h2>4. Checking PHP Extensions</h2>";
$extensions = ['redis', 'memcached', 'apcu'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext extension is loaded<br>";
    } else {
        echo "❌ $ext extension is not loaded<br>";
    }
}

// Test 5: Environment information
echo "<h2>5. Environment Information</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";

// Test 6: List cache files (if using file cache)
echo "<h2>6. Current Cache Files</h2>";
if (is_dir($writablePath)) {
    $files = glob($writablePath . '*');
    if (empty($files)) {
        echo "No cache files found (this is normal if using Redis)<br>";
    } else {
        echo "Found " . count($files) . " cache files:<br>";
        foreach (array_slice($files, 0, 10) as $file) { // Show only first 10
            echo "- " . basename($file) . " (" . date('Y-m-d H:i:s', filemtime($file)) . ")<br>";
        }
        if (count($files) > 10) {
            echo "... and " . (count($files) - 10) . " more files<br>";
        }
    }
}

echo "<hr>";
echo "<h2>Recommendations:</h2>";
echo "<ul>";
echo "<li>If Redis errors appear above, contact your hosting provider to enable Redis</li>";
echo "<li>If Redis is not available, change cache handler to 'file' in app/Config/Cache.php</li>";
echo "<li>Ensure writable/cache/ directory has 755 or 777 permissions</li>";
echo "<li>Delete this file after testing for security</li>";
echo "</ul>";
?>
