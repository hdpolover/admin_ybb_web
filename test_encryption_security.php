<?php

/**
 * Test file for URL encryption/decryption security features
 * 
 * This file tests the security enhancements we made to the url_encrypt and url_decrypt functions.
 */

// Mock the log_message function if it doesn't exist
if (!function_exists('log_message')) {
    function log_message($level, $message) {
        echo "LOG [{$level}]: {$message}\n";
    }
}

// Load necessary helper files
require_once 'app/Helpers/url_encryption_helper.php';

echo "=== URL Encryption/Decryption Security Tests ===\n\n";

// Test 1: Maximum input length
echo "Test 1: Maximum input length\n";
$longData = str_repeat('abcdefghijklmnopqrstuvwxyz', 200); // 5200 characters
echo "Input length: " . strlen($longData) . " chars\n";
$encrypted = url_encrypt($longData);
echo "Encryption result: " . ($encrypted === false ? "Failed (expected)" : "Succeeded (unexpected)") . "\n\n";

// Test 2: Invalid base64 input for decryption
echo "Test 2: Invalid base64 input for decryption\n";
$invalidBase64 = "This is not a valid base64 string!@#$%^&*()";
echo "Input: $invalidBase64\n";
$decrypted = url_decrypt($invalidBase64);
echo "Decryption result: " . ($decrypted === false ? "Failed (expected)" : "Succeeded (unexpected)") . "\n\n";

// Test 3: Tampered encrypted data
echo "Test 3: Tampered encrypted data\n";
$originalData = "Original secure data";
echo "Original data: $originalData\n";
$encrypted = url_encrypt($originalData);
echo "Encrypted: $encrypted\n";

// Tamper with the encrypted data
$tampered = substr($encrypted, 0, -5) . 'XXXXX';
echo "Tampered: $tampered\n";
$decrypted = url_decrypt($tampered);
echo "Decryption result: " . ($decrypted === false ? "Failed (expected)" : "Succeeded (unexpected)") . "\n\n";

// Test 4: Check length limits on decryption
echo "Test 4: Custom length limits\n";
$data = "Test data for custom length limits";
$encrypted = url_encrypt($data);

// Try decryption with very small max output length
$decrypted = url_decrypt($encrypted, true, 'ybb_program', 'ybb_iv', 10);
echo "Decryption with 10-char limit: " . ($decrypted === false ? "Failed (expected)" : "Succeeded (unexpected)") . "\n";

// Try with appropriate length
$decrypted = url_decrypt($encrypted, true, 'ybb_program', 'ybb_iv', 100);
echo "Decryption with 100-char limit: " . ($decrypted === false ? "Failed (unexpected)" : "Succeeded (expected)") . "\n\n";

echo "Security tests completed!\n";
