<?php

if (!function_exists('format_date_range')) {
    /**
     * Format a date range
     *
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return string
     */
    function format_date_range($start_date, $end_date)
    {
        if (empty($start_date) && empty($end_date)) {
            return 'No dates available';
        }
        
        if (empty($start_date)) {
            return 'Until ' . date('M j, Y', strtotime($end_date));
        }
        
        if (empty($end_date)) {
            return 'From ' . date('M j, Y', strtotime($start_date));
        }
        
        return date('M j, Y', strtotime($start_date)) . ' - ' . date('M j, Y', strtotime($end_date));
    }
}

if (!function_exists('time_ago')) {
    /**
     * Returns a string representing time elapsed since the given date
     * 
     * @param string $datetime Date/time string
     * @return string
     */
    function time_ago($datetime)
    {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $mins = round($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = round($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $days = round($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 31536000) {
            $months = round($diff / 2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        } else {
            $years = round($diff / 31536000);
            return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
        }
    }
}

if (!function_exists('format_date')) {
    /**
     * Format a date with a given format
     * 
     * @param string $date The date to format
     * @param string $format The format to use (default: 'M j, Y')
     * @return string Formatted date or empty string if invalid
     */
    function format_date($date, $format = 'M j, Y')
    {
        if (empty($date) || $date == '0000-00-00') {
            return '';
        }
        
        return date($format, strtotime($date));
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format a number as currency
     * 
     * @param float $amount The amount to format
     * @param string $currency Currency symbol
     * @param int $decimals Number of decimal places
     * @return string Formatted currency
     */
    function format_currency($amount, $currency = '$', $decimals = 2)
    {
        return $currency . number_format($amount, $decimals);
    }
}

if (!function_exists('ellipsize_string')) {
    /**
     * Truncate a string to a certain length and add ellipsis
     * 
     * @param string $str The string to truncate
     * @param int $max_length Maximum length of the string
     * @param string $position Where to truncate (beginning, middle, end)
     * @return string Truncated string
     */
    function ellipsize_string($str, $max_length = 50, $position = 'end')
    {
        if (strlen($str) <= $max_length) {
            return $str;
        }
        
        switch ($position) {
            case 'beginning':
                return '...' . substr($str, -$max_length);
            case 'middle':
                $start = ceil($max_length / 2);
                return substr($str, 0, $start) . '...' . substr($str, -($max_length - $start));
            case 'end':
            default:
                return substr($str, 0, $max_length) . '...';
        }
    }
}

if (!function_exists('is_date_range_active')) {
    /**
     * Check if the current date is within a given date range
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return bool True if current date is within range
     */
    function is_date_range_active($start_date, $end_date)
    {
        $today = strtotime(date('Y-m-d'));
        $start = $start_date ? strtotime($start_date) : null;
        $end = $end_date ? strtotime($end_date) : null;
        
        if ($start && $end) {
            return ($today >= $start && $today <= $end);
        } elseif ($start) {
            return ($today >= $start);
        } elseif ($end) {
            return ($today <= $end);
        }
        
        return false;
    }
}
