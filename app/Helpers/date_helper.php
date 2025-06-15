<?php
if (!function_exists('format_date')) {
    /**
     * Format a date string or timestamp into a specific format
     *
     * @param mixed $date Date string or timestamp
     * @param string $format Date format (default: 'Y-m-d H:i:s')
     * @return string Formatted date
     */
    function format_date($date, $format = 'Y-m-d H:i:s')
    {
        if (empty($date)) {
            return null;
        }

        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date($format, $timestamp);
    }
}
if (!function_exists('format_time')) {
    /**
     * Format a time string or timestamp into a specific format
     *
     * @param mixed $time Time string or timestamp
     * @param string $format Time format (default: 'H:i:s')
     * @return string Formatted time
     */
    function format_time($time, $format = 'H:i:s')
    {
        if (empty($time)) {
            return null;
        }

        $timestamp = is_numeric($time) ? $time : strtotime($time);
        return date($format, $timestamp);
    }
}
if (!function_exists('format_date_time')) {
    /**
     * Format a date and time string or timestamp into a specific format
     *
     * @param mixed $date_time Date and time string or timestamp
     * @param string $format Date and time format (default: 'Y-m-d H:i:s')
     * @return string Formatted date and time
     */
    function format_date_time($date_time, $format = 'Y-m-d H:i:s')
    {
        if (empty($date_time)) {
            return null;
        }

        $timestamp = is_numeric($date_time) ? $date_time : strtotime($date_time);
        return date($format, $timestamp);
    }
}
if (!function_exists('format_date_diff')) {
    /**
     * Calculate the difference between two dates and return it in a human-readable format
     *
     * @param mixed $start_date Start date string or timestamp
     * @param mixed $end_date End date string or timestamp
     * @return string Human-readable date difference
     */
    function format_date_diff($start_date, $end_date)
    {
        if (empty($start_date) || empty($end_date)) {
            return null;
        }

        $start_timestamp = is_numeric($start_date) ? $start_date : strtotime($start_date);
        $end_timestamp = is_numeric($end_date) ? $end_date : strtotime($end_date);

        $diff = abs($end_timestamp - $start_timestamp);
        $days = floor($diff / (60 * 60 * 24));
        return "$days days";
    }
}
if (!function_exists('format_pretty_date')) {
    /**
     * Format a date in a pretty format (e.g., January 20, 2025)
     *
     * @param mixed $date Date string or timestamp
     * @return string Formatted pretty date
     */
    function format_pretty_date($date)
    {
        if (empty($date)) {
            return null;
        }

        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date('F j, Y', $timestamp);
    }
}

if (!function_exists('format_date_range')) {
    /**
     * Format a date range
     *
     * @param mixed $start_date Start date string or timestamp
     * @param mixed $end_date End date string or timestamp
     * @param string $format Format for individual dates (default: pretty format)
     * @return string Formatted date range
     */
    function format_date_range($start_date, $end_date, $format = 'pretty')
    {
        if (empty($start_date) || empty($end_date)) {
            return null;
        }

        if ($format === 'pretty') {
            $start_timestamp = is_numeric($start_date) ? $start_date : strtotime($start_date);
            $end_timestamp = is_numeric($end_date) ? $end_date : strtotime($end_date);

            // If same year, don't repeat the year in first date
            if (date('Y', $start_timestamp) === date('Y', $end_timestamp)) {
                // If same month, don't repeat the month
                if (date('F', $start_timestamp) === date('F', $end_timestamp)) {
                    return date('F j', $start_timestamp) . ' - ' . date('j, Y', $end_timestamp);
                }
                return date('F j', $start_timestamp) . ' - ' . date('F j, Y', $end_timestamp);
            }

            return date('F j, Y', $start_timestamp) . ' - ' . date('F j, Y', $end_timestamp);
        } else {
            return format_date($start_date, $format) . ' - ' . format_date($end_date, $format);
        }
    }
}

if (!function_exists('get_feedback_display_date')) {
    /**
     * Get the appropriate date to display for feedback
     * Returns updated_at if it's valid and different from created_at, otherwise returns created_at
     *
     * @param string $created_at The created_at timestamp
     * @param string $updated_at The updated_at timestamp
     * @return string The appropriate timestamp to display
     */
    function get_feedback_display_date($created_at, $updated_at)
    {
        // If updated_at is empty, null, or invalid date, use created_at
        if (empty($updated_at) || $updated_at === '0000-00-00 00:00:00' || $updated_at === '1970-01-01 00:00:00') {
            return $created_at;
        }
        
        // If updated_at is the same as created_at, use created_at (no actual update occurred)
        if ($updated_at === $created_at) {
            return $created_at;
        }
        
        // Otherwise, use updated_at
        return $updated_at;
    }
}

if (!function_exists('is_valid_timestamp')) {
    /**
     * Check if a timestamp is valid (not null, empty, or zero date)
     *
     * @param string $timestamp The timestamp to check
     * @return bool True if valid, false otherwise
     */
    function is_valid_timestamp($timestamp)
    {
        if (empty($timestamp)) {
            return false;
        }
        
        if (in_array($timestamp, ['0000-00-00 00:00:00', '1970-01-01 00:00:00', '0000-00-00', '1970-01-01'])) {
            return false;
        }
        
        return strtotime($timestamp) !== false;
    }
}
