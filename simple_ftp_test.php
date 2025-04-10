<?php

// Simple FTP connection test
echo "Starting FTP connection test...\n";

// FTP connection settings from your configuration
$ftpHost = 'ftp.ybbfoundation.com';
$ftpUsername = 'storage_user@ybbfoundation.com';
$ftpPassword = 'Ns@%L(y_iSU9';
$ftpPort = 21;
$ftpPassive = true;

// Try to connect
echo "Connecting to {$ftpHost}:{$ftpPort}...\n";
$conn = @ftp_connect($ftpHost, $ftpPort, 10); // 10 second timeout

if (!$conn) {
    echo "ERROR: Could not connect to FTP server.\n";
    exit(1);
}

echo "SUCCESS: Connected to FTP server.\n";

// Try to login
echo "Logging in as {$ftpUsername}...\n";
$login = @ftp_login($conn, $ftpUsername, $ftpPassword);

if (!$login) {
    echo "ERROR: Login failed with the provided credentials.\n";
    ftp_close($conn);
    exit(1);
}

echo "SUCCESS: Logged in successfully.\n";

// Set passive mode
if ($ftpPassive) {
    echo "Setting passive mode...\n";
    ftp_pasv($conn, true);
}

// Try to list root directory
echo "Listing root directory contents...\n";
$list = @ftp_nlist($conn, '/');

if ($list === false) {
    echo "ERROR: Could not list directory contents.\n";
    ftp_close($conn);
    exit(1);
}

echo "SUCCESS: Listed " . count($list) . " items in root directory.\n";

// Try to create a test file
echo "Creating test file...\n";
$testContent = "Test file created at " . date('Y-m-d H:i:s');
$tempFile = tempnam(sys_get_temp_dir(), 'ftp_test');
file_put_contents($tempFile, $testContent);

// Upload the test file
$remotePath = '/test_' . time() . '.txt';
echo "Uploading test file to {$remotePath}...\n";

if (!@ftp_put($conn, $remotePath, $tempFile, FTP_ASCII)) {
    echo "ERROR: Failed to upload test file.\n";
    unlink($tempFile);
    ftp_close($conn);
    exit(1);
}

echo "SUCCESS: Test file uploaded successfully.\n";

// Clean up
echo "Deleting test file...\n";
if (@ftp_delete($conn, $remotePath)) {
    echo "SUCCESS: Test file deleted.\n";
} else {
    echo "WARNING: Could not delete test file.\n";
}

unlink($tempFile);
ftp_close($conn);

echo "FTP test completed successfully!\n";
echo "Your FTP configuration is working correctly.\n";
