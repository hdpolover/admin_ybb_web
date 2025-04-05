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
        // Convert query string to array if string is provided
        if (is_string($data) && strpos($data, '=') !== false) {
            parse_str($data, $params);
            $data = $params;
        }
        
        // Convert data to JSON if it's an array
        if (is_array($data)) {
            $data = json_encode($data);
        }
        
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        
        $encrypted = openssl_encrypt($data, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($encrypted);
        
        // Make URL safe by replacing + and / with - and _
        $output = strtr($output, '+/', '-_');
        
        return $output;
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
            // URL-safe base64 decode (convert - and _ back to + and /)
            $encrypted_data = strtr($encrypted_data, '-_', '+/');
            
            $encrypt_method = "AES-256-CBC";
            $key = hash('sha256', $secret_key);
            $iv = substr(hash('sha256', $secret_iv), 0, 16);
            
            $decrypted = openssl_decrypt(base64_decode($encrypted_data), $encrypt_method, $key, 0, $iv);
            
            if ($decrypted === false) {
                return false;
            }
            
            // return as string
            if (!$as_array) {
                return $decrypted;
            }

            if ($as_array) {
                return json_decode($decrypted, true);
            }

            return $decrypted;
        } catch (\Exception $e) {
            log_message('error', 'URL Decryption failed: ' . $e->getMessage());
            return false;
        }
    }
}