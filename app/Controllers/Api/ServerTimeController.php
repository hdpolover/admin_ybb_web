<?php

namespace App\Controllers\Api;

class ServerTimeController extends ApiBaseController
{
    /**
     * Get server time information
     * GET /api/server-time
     * 
     * Returns comprehensive server time information including:
     * - Unix timestamp
     * - ISO 8601 format
     * - 24-hour and 12-hour time formats
     * - Date in various formats
     * - Timezone information
     * - Day of week and week of year
     */
    public function getServerTime()
    {
        try {
            // Get application timezone from config
            $appTimezone = config('App')->appTimezone ?? 'UTC';
            
            // Create DateTime object with application timezone
            $now = new \DateTime('now', new \DateTimeZone($appTimezone));
            
            // Get timezone information
            $timezone = $now->getTimezone();
            $timezoneOffset = $now->getOffset();
            
            // Format timezone offset as string (e.g., +07:00)
            $offsetHours = intval($timezoneOffset / 3600);
            $offsetMinutes = abs(intval(($timezoneOffset % 3600) / 60));
            $offsetString = sprintf('%+03d:%02d', $offsetHours, $offsetMinutes);
            
            // Prepare server time data
            $serverTimeData = [
                'timestamp' => $now->getTimestamp(),
                'iso' => $now->format('c'), // ISO 8601 format (2025-09-30T14:35:23+07:00)
                'time_24' => $now->format('H:i:s'), // 24-hour format
                'time_12' => $now->format('g:i:s A'), // 12-hour format with AM/PM
                'date' => $now->format('Y-m-d'), // Date in Y-m-d format
                'date_formatted' => $now->format('D, M j, Y'), // Formatted date (Mon, Sep 30, 2025)
                'timezone_name' => $timezone->getName(), // e.g., Asia/Jakarta (top-level for easy access)
                'timezone' => [
                    'name' => $timezone->getName(), // e.g., Asia/Jakarta
                    'abbreviation' => $now->format('T'), // e.g., WIB
                    'offset' => $offsetString, // e.g., +07:00
                    'offset_seconds' => $timezoneOffset // Offset in seconds
                ],
                'day_of_week' => $now->format('l'), // Full day name (Monday)
                'week_of_year' => $now->format('W') // Week number of the year
            ];
            
            // Return custom response format to match expected structure
            return $this->respond([
                'success' => true,
                'server_time' => $serverTimeData
            ], self::HTTP_OK);
            
        } catch (\Exception $e) {
            // Log the error for debugging
            log_message('error', 'ServerTimeController::getServerTime() - Error: ' . $e->getMessage());
            
            return $this->respondError('Failed to retrieve server time', self::HTTP_INTERNAL_ERROR);
        }
    }
}