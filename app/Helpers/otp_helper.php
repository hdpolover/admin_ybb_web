<?php

if (!function_exists('generate_otp')) {
    /**
     * Generates a random 6-digit OTP (One Time Password)
     *
     * @return string The generated 6-digit OTP
     */
    function generate_otp() {
        // Generate a random number between 100000 and 999999
        $otp = mt_rand(100000, 999999);
        
        // Return as string
        return (string) $otp;
    }
}

if (!function_exists('generate_token')) {
    /**
     * Generates a random token
     *
     * @param int $length Length of the token (default 32)
     * @param bool $alphanumeric Whether to use alphanumeric characters (default true)
     * @return string The generated token
     */
    function generate_token($length = 32, $alphanumeric = true) {
        if ($alphanumeric) {
            // For alphanumeric tokens (more secure)
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } else {
            // For numeric-only tokens
            $characters = '0123456789';
        }
        
        $token = '';
        
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $token;
    }
}