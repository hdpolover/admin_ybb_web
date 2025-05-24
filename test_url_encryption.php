<?php

/**
 * Test file for URL encryption/decryption functions
 * 
 * This file will test various encryption/decryption scenarios to ensure
 * that our functions work properly in an online environment with URL-safe characters.
 */

// Mock the log_message function if it doesn't exist
if (!function_exists('log_message')) {
    function log_message($level, $message) {
        if ($level === 'error') {
            echo "LOG [{$level}]: {$message}\n";
        }
    }
}

// Load necessary helper files
require_once 'app/Helpers/url_encryption_helper.php';

// Function to run a test case and display results
function runTest($name, $data) {
    echo "=== Test: $name ===\n";
    
    // Encrypt the data
    echo "Original data: " . (is_array($data) ? json_encode($data) : $data) . "\n";
    $encrypted = url_encrypt($data);
    
    if ($encrypted === false) {
        echo "FAIL: Encryption failed\n";
        return;
    }
    
    echo "Encrypted: $encrypted\n";
    
    // Test URL encoding/decoding
    $urlEncoded = urlencode($encrypted);
    echo "URL encoded: $urlEncoded\n";
    $urlDecoded = urldecode($urlEncoded);
    echo "URL decoded: $urlDecoded\n";
    
    // Test decryption (as string)
    $decrypted1 = url_decrypt($urlDecoded, false);
    if ($decrypted1 === false) {
        echo "FAIL: Decryption failed (as string)\n";
    } else {
        echo "Decrypted (as string): $decrypted1\n";
        if (is_array($data) || strpos($data, '=') !== false) {
            echo "Note: Original was array or query string, comparing with decoded JSON may not match exactly\n";
        } else {
            echo "Match with original: " . ($decrypted1 === $data ? "YES" : "NO") . "\n";
        }
    }
    
    // Test decryption (as array)
    $decrypted2 = url_decrypt($urlDecoded, true);
    if ($decrypted2 === false) {
        echo "FAIL: Decryption failed (as array)\n";
    } else {
        echo "Decrypted (as array): " . json_encode($decrypted2) . "\n";
    }
    
    echo "\n";
}

// Test cases
$tests = [
    "Simple string" => "ABC123",
    "String with special chars" => "Hello, World! @#$%^&*()_+",
    "Alphanumeric with dashes" => "YBB-REF-CODE-12345",
    "JSON data" => json_encode(["id" => 123, "name" => "Test User"]),
    "Query string" => "id=123&name=Test+User",
    "Array data" => ["id" => 123, "name" => "Test User"],
    "Long string" => str_repeat("abcdefghijklmnopqrstuvwxyz", 10)
];

// Run all tests
foreach ($tests as $name => $data) {
    runTest($name, $data);
}

echo "All tests completed!\n";
