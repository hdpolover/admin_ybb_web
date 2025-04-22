<?php

/**
 * URL Encryption Helper
 * 
 * This helper provides functions to encrypt and decrypt URL query parameters
 * so that they cannot be read or manipulated by users.
 */

if (!function_exists('url_encrypt')) {
    /**
     * Encrypt URL query string or parameters
     * 
     * @param string|array $data Query string or associative array to encrypt
     * @param string $secret_key Secret key for encryption (optional)
     * @param string $secret_iv Initialization vector for encryption (optional)
     * @return string Encrypted string safe for URL
     */
    function url_encrypt($data, $secret_key = 'ybb_program', $secret_iv = 'ybb_iv')
    {
        try {
            // Log incoming data type
            log_message('debug', 'URL Encrypt - Input type: ' . gettype($data));

            // Convert query string to array if string is provided
            if (is_string($data) && strpos($data, '=') !== false) {
                parse_str($data, $params);
                $data = $params;
                log_message('debug', 'URL Encrypt - Parsed query string to array');
            }

            // Convert data to JSON if it's an array
            if (is_array($data)) {
                $data = json_encode($data);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    log_message('error', 'URL Encrypt - JSON encode error: ' . json_last_error_msg());
                    return false;
                }
                log_message('debug', 'URL Encrypt - Converted array to JSON');
            }

            // Check if we have valid data to encrypt
            if (empty($data)) {
                log_message('error', 'URL Encrypt - Empty input data');
                return false;
            }

            $encrypt_method = "AES-256-CBC";
            $key = hash('sha256', $secret_key);
            $iv = substr(hash('sha256', $secret_iv), 0, 16);

            $encrypted = openssl_encrypt($data, $encrypt_method, $key, 0, $iv);

            if ($encrypted === false) {
                $error = openssl_error_string();
                log_message('error', 'URL Encrypt - OpenSSL error: ' . ($error ?: 'Unknown error'));
                return false;
            }

            $output = base64_encode($encrypted);

            // Make URL safe by replacing + and / with - and _
            $output = strtr($output, '+/', '-_');

            log_message('debug', 'URL Encrypt - Successfully encrypted data to: ' . substr($output, 0, 30) . '...');

            return $output;
        } catch (\Exception $e) {
            log_message('error', 'URL Encryption failed: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('url_decrypt')) {
    /**
     * Decrypt an encrypted URL query string
     * 
     * @param string $encrypted_data The encrypted data string
     * @param bool $as_array Return as array (true) or query string (false)
     * @param string $secret_key Secret key for decryption (optional)
     * @param string $secret_iv Initialization vector for decryption (optional)
     * @return array|string|bool Decrypted data or false if decryption fails
     */
    function url_decrypt($encrypted_data, $as_array = true, $secret_key = 'ybb_program', $secret_iv = 'ybb_iv')
    {
        try {
            // Log incoming data
            log_message('debug', 'URL Decrypt - Input: ' . $encrypted_data);

            // Make sure we have valid data
            if (empty($encrypted_data)) {
                log_message('error', 'URL Decrypt - Empty input data');
                return false;
            }

            // URL-safe base64 decode (convert - and _ back to + and /)
            $encrypted_data = strtr($encrypted_data, '-_', '+/');

            // Check for base64 padding - add if needed
            $mod4 = strlen($encrypted_data) % 4;
            if ($mod4) {
                $encrypted_data .= substr('====', $mod4);
            }

            $encrypt_method = "AES-256-CBC";
            $key = hash('sha256', $secret_key);
            $iv = substr(hash('sha256', $secret_iv), 0, 16);

            // Decode base64 first with error checking
            $base64_decoded = base64_decode($encrypted_data, true);
            if ($base64_decoded === false) {
                log_message('error', 'URL Decrypt - Invalid base64 encoding');
                return false;
            }

            // Try decryption
            $decrypted = openssl_decrypt($base64_decoded, $encrypt_method, $key, 0, $iv);

            if ($decrypted === false) {
                $error = openssl_error_string();
                log_message('error', 'URL Decrypt - OpenSSL error: ' . ($error ?: 'Unknown error'));
                return false;
            }

            log_message('debug', 'URL Decrypt - Successfully decrypted: ' . substr($decrypted, 0, 30));

            // Return as requested format
            if (!$as_array) {
                return $decrypted;
            }

            // Try to decode JSON for array return
            $json_decoded = json_decode($decrypted, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'URL Decrypt - JSON decode error: ' . json_last_error_msg());
                // If JSON decode fails but we have a string, return it as a single-item array
                if (!empty($decrypted)) {
                    return ['value' => $decrypted];
                }
                return false;
            }

            return $json_decoded;
        } catch (\Exception $e) {
            log_message('error', 'URL Decryption failed: ' . $e->getMessage());
            return false;
        }
    }
}
