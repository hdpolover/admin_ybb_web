<?php

/**
 * Test file for Ambassador referral URL encryption/decryption
 * 
 * This file simulates the actual ambassador referral link generation and verification
 * that happens in the AmbassadorsApiController.
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

echo "=== Ambassador Referral Link Test ===\n\n";

// Test data
$refCode = "YBB-REF-2025-05";
$webUrl = "https://example.com";

echo "Original ref_code: $refCode\n";

// Step 1: Generate encrypted query (like in generateLink method)
$encryptedQuery = url_encrypt($refCode);
echo "Encrypted query: $encryptedQuery\n";

// Step 2: Create the referral link
$referralLink = $webUrl . '/sign-up?q=' . urlencode($encryptedQuery);
echo "Referral link: $referralLink\n\n";

// Step 3: Simulate what happens when the link is clicked and received by checkEncryptedQuery method
echo "=== Simulating link click and server processing ===\n";

// Extract the query parameter (like what would happen in a browser)
$parts = parse_url($referralLink);
parse_str($parts['query'], $query);
$receivedEncryptedQuery = $query['q'];
echo "Received encrypted query: $receivedEncryptedQuery\n";

// URL decode (happens in the controller)
$receivedEncryptedQuery = urldecode($receivedEncryptedQuery);
echo "After urldecode: $receivedEncryptedQuery\n";

// Try to decrypt
$decryptedQuery = url_decrypt($receivedEncryptedQuery, false);
if ($decryptedQuery === false) {
    echo "FAIL: Decryption failed\n";
} else {
    echo "Decrypted ref_code: $decryptedQuery\n";
    echo "Match with original: " . ($decryptedQuery === $refCode ? "YES" : "NO") . "\n";
}

// Test with potential URL transformations that might happen
echo "\n=== Testing with URL edge cases ===\n";

// Test 1: URL with spaces (might happen if incorrectly copied)
$testUrl = str_replace(urlencode($encryptedQuery), urlencode($encryptedQuery) . " ", $referralLink);
echo "Test URL with space: $testUrl\n";
$parts = parse_url($testUrl);
parse_str($parts['query'], $query);
$receivedEncryptedQuery = $query['q'];
$receivedEncryptedQuery = urldecode($receivedEncryptedQuery);
$receivedEncryptedQuery = trim($receivedEncryptedQuery); // Added in our improved controller
$decryptedQuery = url_decrypt($receivedEncryptedQuery, false);
echo "Decryption result: " . ($decryptedQuery === false ? "FAILED" : $decryptedQuery) . "\n";
echo "Match with original: " . ($decryptedQuery === $refCode ? "YES" : "NO") . "\n\n";

// Test 2: Double-encoded URL (might happen in some frameworks)
$doubleEncoded = urlencode(urlencode($encryptedQuery));
$testUrl = $webUrl . '/sign-up?q=' . $doubleEncoded;
echo "Test with double encoding: $testUrl\n";
$parts = parse_url($testUrl);
parse_str($parts['query'], $query);
$receivedEncryptedQuery = $query['q'];
$receivedEncryptedQuery = urldecode($receivedEncryptedQuery);
$receivedEncryptedQuery = urldecode($receivedEncryptedQuery); // Second decode for double encoding
$decryptedQuery = url_decrypt($receivedEncryptedQuery, false);
echo "Decryption result: " . ($decryptedQuery === false ? "FAILED" : $decryptedQuery) . "\n";
echo "Match with original: " . ($decryptedQuery === $refCode ? "YES" : "NO") . "\n\n";

// Test 3: URL with special characters that might be transformed
$specialChars = "YBB-REF+2025/05";
echo "\nTest with special characters in ref code: $specialChars\n";
$encryptedSpecial = url_encrypt($specialChars);
echo "Encrypted: $encryptedSpecial\n";
$testUrl = $webUrl . '/sign-up?q=' . urlencode($encryptedSpecial);
echo "URL: $testUrl\n";
$parts = parse_url($testUrl);
parse_str($parts['query'], $query);
$receivedEncryptedQuery = $query['q'];
$receivedEncryptedQuery = urldecode($receivedEncryptedQuery);
$decryptedQuery = url_decrypt($receivedEncryptedQuery, false);
echo "Decryption result: " . ($decryptedQuery === false ? "FAILED" : $decryptedQuery) . "\n";
echo "Match with original: " . ($decryptedQuery === $specialChars ? "YES" : "NO") . "\n\n";

// Test 4: Test with multiple parameters in URL
$testUrl = $webUrl . '/sign-up?q=' . urlencode($encryptedQuery) . '&utm_source=test&utm_medium=email';
echo "Test with multiple URL parameters: $testUrl\n";
$parts = parse_url($testUrl);
parse_str($parts['query'], $query);
$receivedEncryptedQuery = $query['q'];
$receivedEncryptedQuery = urldecode($receivedEncryptedQuery);
$decryptedQuery = url_decrypt($receivedEncryptedQuery, false);
echo "Decryption result: " . ($decryptedQuery === false ? "FAILED" : $decryptedQuery) . "\n";
echo "Match with original: " . ($decryptedQuery === $refCode ? "YES" : "NO") . "\n\n";

// Test 5: Test with a fragment
$testUrl = $webUrl . '/sign-up?q=' . urlencode($encryptedQuery) . '#section1';
echo "Test with URL fragment: $testUrl\n";
$parts = parse_url($testUrl);
parse_str($parts['query'], $query);
$receivedEncryptedQuery = $query['q'];
$receivedEncryptedQuery = urldecode($receivedEncryptedQuery);
$decryptedQuery = url_decrypt($receivedEncryptedQuery, false);
echo "Decryption result: " . ($decryptedQuery === false ? "FAILED" : $decryptedQuery) . "\n";
echo "Match with original: " . ($decryptedQuery === $refCode ? "YES" : "NO") . "\n\n";

// Test 6: Very long ref code
$longRefCode = "YBB-REF-" . str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789", 3);
echo "Test with very long ref code: " . substr($longRefCode, 0, 30) . "...\n";
$encryptedLong = url_encrypt($longRefCode);
echo "Encrypted (truncated): " . substr($encryptedLong, 0, 30) . "...\n";
$testUrl = $webUrl . '/sign-up?q=' . urlencode($encryptedLong);
$parts = parse_url($testUrl);
parse_str($parts['query'], $query);
$receivedEncryptedQuery = $query['q'];
$receivedEncryptedQuery = urldecode($receivedEncryptedQuery);
$decryptedQuery = url_decrypt($receivedEncryptedQuery, false);
echo "Decryption success: " . ($decryptedQuery !== false ? "YES" : "NO") . "\n";
if ($decryptedQuery !== false) {
    echo "Length match: " . (strlen($decryptedQuery) === strlen($longRefCode) ? "YES" : "NO") . "\n";
    echo "Content match: " . ($decryptedQuery === $longRefCode ? "YES" : "NO") . "\n\n";
}

echo "Tests completed!\n";
