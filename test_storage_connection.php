<?php
// Load CodeIgniter
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap CodeIgniter
$pathsConfig = 'app/Config/Paths.php';
require_once $pathsConfig;
$paths = new \Config\Paths();

// Initialize the application
$bootstrap = \CodeIgniter\Config\Services::codeigniter();

use Config\Storage as StorageConfig;

// Load storage configuration
$storageConfig = new StorageConfig();

echo "Testing External Storage Connection...\n";
echo "=================================\n\n";

// Test FTP connection
echo "Testing FTP Connection:\n";
echo "Host: " . $storageConfig->ftpHost . "\n";
echo "Port: " . $storageConfig->ftpPort . "\n";
echo "Username: " . $storageConfig->ftpUsername . "\n";

try {
    // Check if FTP extension is available
    if (!extension_loaded('ftp')) {
        echo "❌ FTP extension is not loaded\n";
        exit;
    }
    
    echo "✅ FTP extension is loaded\n";
    
    // Connect to FTP server
    echo "Attempting to connect to FTP server...\n";
    $conn = ftp_connect($storageConfig->ftpHost, $storageConfig->ftpPort, 10); // 10 second timeout
    
    if (!$conn) {
        echo "❌ Failed to connect to FTP server\n";
        exit;
    }
    
    echo "✅ Connected to FTP server successfully\n";
    
    // Try to login
    echo "Attempting to login...\n";
    if (!@ftp_login($conn, $storageConfig->ftpUsername, $storageConfig->ftpPassword)) {
        echo "❌ FTP authentication failed\n";
        ftp_close($conn);
        exit;
    }
    
    echo "✅ FTP authentication successful\n";
    
    // Set passive mode
    if ($storageConfig->ftpPassive) {
        ftp_pasv($conn, true);
        echo "✅ Passive mode enabled\n";
    }
    
    // Test directory listing
    echo "Testing directory listing...\n";
    $files = ftp_nlist($conn, '.');
    if ($files === false) {
        echo "❌ Failed to list directory contents\n";
    } else {
        echo "✅ Directory listing successful\n";
        echo "Files found: " . count($files) . "\n";
    }
    
    // Test creating a directory
    echo "Testing directory creation...\n";
    $testDir = '/test_connection_' . time();
    if (@ftp_mkdir($conn, $testDir)) {
        echo "✅ Test directory created successfully\n";
        
        // Clean up - delete the test directory
        if (@ftp_rmdir($conn, $testDir)) {
            echo "✅ Test directory deleted successfully\n";
        }
    } else {
        echo "⚠️  Could not create test directory (might be permissions issue)\n";
    }
    
    ftp_close($conn);
    echo "\n🎉 FTP connection test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "\n";
}

// Test HTTP endpoint
echo "\n\nTesting HTTP Endpoint:\n";
echo "Storage URL: " . $storageConfig->storageUrl . "\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $storageConfig->storageUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'Storage Connection Test'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

if ($error) {
    echo "❌ cURL Error: " . $error . "\n";
} else {
    echo "✅ HTTP request successful\n";
    echo "Response Code: " . $httpCode . "\n";
    if ($httpCode == 200) {
        echo "✅ Storage server is reachable\n";
    } else {
        echo "⚠️  Unexpected response code\n";
    }
}

curl_close($ch);

echo "\n=================================\n";
echo "Connection test completed.\n";
