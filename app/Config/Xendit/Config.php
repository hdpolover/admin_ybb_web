<?php

namespace App\Config\Xendit;

/**
 * Xendit Configuration Class
 *
 * Mirrors the structure of App\Config\Midtrans\Config.
 * Never cached — always loads fresh from environment variables.
 */
class Config
{
    public string $secretKey    = '';
    public string $publicKey    = '';
    public string $callbackToken = '';
    public bool   $isProduction  = false;

    public function __construct()
    {
        $this->secretKey     = $this->env('XENDIT_SECRET_KEY');
        $this->publicKey     = $this->env('XENDIT_PUBLIC_KEY');
        $this->callbackToken = $this->env('XENDIT_CALLBACK_TOKEN');

        $prod = $this->env('XENDIT_IS_PRODUCTION', 'false');
        $this->isProduction = in_array($prod, ['true', '1'], true);
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getCallbackToken(): string
    {
        return $this->callbackToken;
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    public function getBaseUrl(): string
    {
        // Xendit uses the same production API for both live and test;
        // sandbox mode is controlled solely by the key used.
        return 'https://api.xendit.co';
    }

    private function env(string $key, string $default = ''): string
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        return $default;
    }
}
