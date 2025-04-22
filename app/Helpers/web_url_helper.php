<?php

if (!function_exists('normalize_web_url')) {
    function normalize_web_url(string $url): string
    {
        // Extract host
        $host = parse_url($url, PHP_URL_HOST) ?? $url;

        // Fallback for plain domains without protocol
        if (!$host && !str_starts_with($url, 'http')) {
            $host = $url;
        }

        // Remove www
        $normalized = preg_replace('/^www\./', '', $host);

        return $normalized;
    }
}
