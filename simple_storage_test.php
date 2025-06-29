<?php
// Simple storage connection test without CodeIgniter dependencies

echo "Testing External Storage Connection...\n";
echo "=================================\n\n";

// Storage configuration (hardcoded for testing)
$ftpHost = 'ftp.ybbfoundation.com';
$ftpUsername = 'storage_user@ybbfoundation.com'; 
$ftpPassword = 'Ns@%L(y_iSU9';
$ftpPort = 21;
$storageUrl = 'https://storage.ybbfoundation.com';

// Test FTP connection
echo "Testing FTP Connection:\n";
echo "Host: " . $ftpHost . "\n";
echo "Port: " . $ftpPort . "\n";
echo "Username: " . $ftpUsername . "\n\n";

try {
    // Check if FTP extension is available
    if (!extension_loaded('ftp')) {
        echo "❌ FTP extension is not loaded\n";
        exit;
    }
    
    echo "✅ FTP extension is loaded\n";
    
    // Connect to FTP server
    echo "Attempting to connect to FTP server...\n";
    $conn = ftp_connect($ftpHost, $ftpPort, 15); // 15 second timeout
    
    if (!$conn) {
        echo "❌ Failed to connect to FTP server\n";
        echo "Possible issues:\n";
        echo "- Server is down\n";
        echo "- Firewall blocking connection\n";
        echo "- DNS resolution issues\n";
        exit;
    }
    
    echo "✅ Connected to FTP server successfully\n";
    
    // Try to login
    echo "Attempting to login...\n";
    if (!@ftp_login($conn, $ftpUsername, $ftpPassword)) {
        echo "❌ FTP authentication failed\n";
        echo "- Check username and password\n";
        ftp_close($conn);
        exit;
    }
    
    echo "✅ FTP authentication successful\n";
    
    // Set passive mode
    ftp_pasv($conn, true);
    echo "✅ Passive mode enabled\n";
    
    // Test directory listing
    echo "Testing directory listing...\n";
    $files = ftp_nlist($conn, '.');
    if ($files === false) {
        echo "❌ Failed to list directory contents\n";
    } else {
        echo "✅ Directory listing successful\n";
        echo "Files found: " . count($files) . "\n";
        if (count($files) < 10) {
            echo "Directory contents: " . implode(', ', $files) . "\n";
        }
    }
    
    // Test creating a directory
    echo "Testing directory creation...\n";
    $testDir = 'test_connection_' . time();
    if (@ftp_mkdir($conn, $testDir)) {
        echo "✅ Test directory created successfully\n";
        
        // Clean up - delete the test directory
        if (@ftp_rmdir($conn, $testDir)) {
            echo "✅ Test directory deleted successfully\n";
        }
    } else {
        echo "⚠️  Could not create test directory\n";
        echo "- Check write permissions\n";
        echo "- Server might be read-only\n";
    }
    
    // Test creating certificates directory if it doesn't exist
    echo "Testing certificates directory access...\n";
    if (@ftp_chdir($conn, '/certificates')) {
        echo "✅ Certificates directory exists and accessible\n";
    } else {
        echo "⚠️  Certificates directory not found, trying to create...\n";
        if (@ftp_mkdir($conn, '/certificates')) {
            echo "✅ Certificates directory created successfully\n";
        } else {
            echo "❌ Failed to create certificates directory\n";
        }
    }
    
    ftp_close($conn);
    echo "\n🎉 FTP connection test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "\n";
}

// Test HTTP endpoint
echo "\n\nTesting HTTP Endpoint:\n";
echo "Storage URL: " . $storageUrl . "\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $storageUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'Storage Connection Test',
    CURLOPT_VERBOSE => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$info = curl_getinfo($ch);

if ($error) {
    echo "❌ cURL Error: " . $error . "\n";
    echo "Connection details:\n";
    echo "- Connect time: " . $info['connect_time'] . "s\n";
    echo "- Total time: " . $info['total_time'] . "s\n";
} else {
    echo "✅ HTTP request successful\n";
    echo "Response Code: " . $httpCode . "\n";
    echo "Connect time: " . $info['connect_time'] . "s\n";
    echo "Total time: " . $info['total_time'] . "s\n";
    
    if ($httpCode == 200) {
        echo "✅ Storage server is reachable\n";
    } elseif ($httpCode >= 400) {
        echo "⚠️  HTTP error: " . $httpCode . "\n";
    } else {
        echo "⚠️  Unexpected response code: " . $httpCode . "\n";
    }
}

curl_close($ch);

echo "\n=================================\n";
echo "Connection test completed.\n";
echo "\nIf FTP connection works but HTTP fails, the issue might be:\n";
echo "1. Network connectivity issues\n";
echo "2. Server configuration problems\n"; 
echo "3. CodeIgniter session/timeout issues\n";
echo "4. Large file upload timeouts\n";
