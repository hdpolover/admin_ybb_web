<?php

namespace App\Helpers;

/**
 * Export Filename Helper
 * 
 * Provides methods to generate descriptive filenames for exports
 * based on program information, export type, and applied filters.
 */
class ExportFilenameHelper
{
    /**
     * Generate descriptive filename for exports
     * 
     * @param object|array $program Program data
     * @param string $type Export type (participants, payments, ambassadors)
     * @param array $filters Applied filters
     * @return string Generated filename
     */
    public static function generateDescriptiveFilename($program, string $type, array $filters = []): string
    {
        // Handle program data (object or array)
        $programName = is_object($program) ? $program->name : ($program['name'] ?? 'Unknown_Program');
        
        // Sanitize program name for filename
        $cleanProgramName = self::sanitizeForFilename($programName);
        
        // Capitalize export type
        $exportType = ucfirst($type);
        
        // Add filter descriptions
        $filterDesc = self::generateFilterDescription($filters, $type);
        
        // Current date
        $date = date('d-m-Y');
        
        // Construct filename
        $filename = "{$cleanProgramName}_{$exportType}_{$filterDesc}_{$date}.xlsx";
        
        // Ensure filename doesn't exceed limits
        return self::truncateFilename($filename, 200);
    }
    
    /**
     * Generate Excel sheet name
     * 
     * @param object|array $program Program data
     * @param string $type Export type
     * @return string Sheet name (max 31 characters)
     */
    public static function generateSheetName($program, string $type): string
    {
        $typeName = ucfirst($type);
        $monthYear = date('M Y');
        
        // Handle program data (object or array)
        $programName = is_object($program) ? $program->name : ($program['name'] ?? 'Program');
        
        // Try to include program name if it fits
        $shortProgramName = self::abbreviateProgramName($programName);
        
        if (strlen($shortProgramName) > 0) {
            $sheetName = "{$shortProgramName} {$typeName} {$monthYear}";
        } else {
            $sheetName = "{$typeName} Data {$monthYear}";
        }
        
        // Truncate to Excel limit (31 characters)
        return substr($sheetName, 0, 31);
    }
    
    /**
     * Generate filter description for filename
     * 
     * @param array $filters Applied filters
     * @param string $type Export type
     * @return string Filter description
     */
    private static function generateFilterDescription(array $filters, string $type): string
    {
        $descriptions = [];
        
        // Add category filter description
        if (isset($filters['category']) && $filters['category'] !== 'all') {
            $categoryMap = [
                'fully_funded' => 'Fully_Funded',
                'self_funded' => 'Self_Funded'
            ];
            $descriptions[] = $categoryMap[$filters['category']] ?? ucfirst($filters['category']);
        }
        
        // Add status filter descriptions based on export type
        if ($type === 'participants') {
            if (isset($filters['form_status']) && $filters['form_status'] !== 'all') {
                $statusMap = [
                    '0' => 'Draft_Forms',
                    '1' => 'Submitted_Forms', 
                    '2' => 'Approved_Forms'
                ];
                $descriptions[] = $statusMap[$filters['form_status']] ?? 'Status_' . $filters['form_status'];
            }
            
            if (isset($filters['general_status']) && $filters['general_status'] !== 'all') {
                $generalStatusMap = [
                    '0' => 'Pending_Review',
                    '1' => 'Under_Review',
                    '2' => 'Approved',
                    '3' => 'Rejected'
                ];
                $descriptions[] = $generalStatusMap[$filters['general_status']] ?? 'General_Status_' . $filters['general_status'];
            }
        }
        
        if ($type === 'payments') {
            if (isset($filters['payment_status']) && $filters['payment_status'] !== 'all') {
                $paymentStatusMap = [
                    '0' => 'Created',
                    '1' => 'Pending',
                    '2' => 'Successful',
                    '3' => 'Cancelled',
                    '4' => 'Rejected'
                ];
                $descriptions[] = $paymentStatusMap[$filters['payment_status']] ?? 'Status_' . $filters['payment_status'];
            }
            
            if (isset($filters['currency']) && $filters['currency'] !== 'all') {
                $descriptions[] = strtoupper($filters['currency']);
            }
        }
        
        // Add date range if specified
        if (isset($filters['date_from']) || isset($filters['date_to'])) {
            if (isset($filters['date_from']) && isset($filters['date_to'])) {
                $fromDate = date('d-m', strtotime($filters['date_from']));
                $toDate = date('d-m', strtotime($filters['date_to']));
                $descriptions[] = "({$fromDate}_to_{$toDate})";
            } elseif (isset($filters['date_from'])) {
                $fromDate = date('d-m-Y', strtotime($filters['date_from']));
                $descriptions[] = "(from_{$fromDate})";
            } elseif (isset($filters['date_to'])) {
                $toDate = date('d-m-Y', strtotime($filters['date_to']));
                $descriptions[] = "(until_{$toDate})";
            }
        }
        
        // Base descriptor based on export type
        $baseDescriptor = match($type) {
            'participants' => 'Participants',
            'payments' => 'Payments',
            'ambassadors' => 'Ambassadors',
            default => ucfirst($type)
        };
        
        // If we have specific filters, add them
        if (!empty($descriptions)) {
            $fullDescriptor = $baseDescriptor . '_' . implode('_', $descriptions);
            // Limit total length to prevent overly long filenames
            if (strlen($fullDescriptor) > 60) {
                $fullDescriptor = $baseDescriptor . '_Filtered_Data';
            }
            return $fullDescriptor;
        }
        
        // Default comprehensive export descriptions
        return match($type) {
            'participants' => $baseDescriptor . '_Complete_Registration_Data',
            'payments' => $baseDescriptor . '_Complete_Transaction_Report',
            'ambassadors' => $baseDescriptor . '_List',
            default => $baseDescriptor . '_Export'
        };
    }
    
    /**
     * Sanitize string for use in filename
     * 
     * @param string $input Input string
     * @return string Sanitized filename component
     */
    private static function sanitizeForFilename(string $input): string
    {
        // Remove or replace problematic characters
        $sanitized = preg_replace('/[<>:"\\/\\|?*]/', '', $input);
        
        // Replace spaces and other characters with underscores
        $sanitized = preg_replace('/[\s\-\.]+/', '_', $sanitized);
        
        // Remove multiple consecutive underscores
        $sanitized = preg_replace('/_+/', '_', $sanitized);
        
        // Trim underscores from start and end
        $sanitized = trim($sanitized, '_');
        
        // Limit length to prevent overly long components
        if (strlen($sanitized) > 30) {
            $sanitized = substr($sanitized, 0, 30);
            // Make sure we don't cut in the middle of a word
            if (strpos($sanitized, '_') !== false) {
                $parts = explode('_', $sanitized);
                array_pop($parts); // Remove potentially incomplete last part
                $sanitized = implode('_', $parts);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Truncate filename while preserving extension
     * 
     * @param string $filename Original filename
     * @param int $maxLength Maximum length (default 200)
     * @return string Truncated filename
     */
    private static function truncateFilename(string $filename, int $maxLength = 200): string
    {
        if (strlen($filename) <= $maxLength) {
            return $filename;
        }
        
        // Extract extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        
        // Calculate available space for name (reserve space for extension + dot)
        $availableLength = $maxLength - strlen($extension) - 1;
        
        // Truncate name
        $truncatedName = substr($nameWithoutExt, 0, $availableLength);
        
        // Try to end at a sensible break point (underscore)
        if (strpos($truncatedName, '_') !== false) {
            $lastUnderscore = strrpos($truncatedName, '_');
            if ($lastUnderscore > ($availableLength * 0.7)) { // Only if we're not losing too much
                $truncatedName = substr($truncatedName, 0, $lastUnderscore);
            }
        }
        
        return $truncatedName . '.' . $extension;
    }
    
    /**
     * Create abbreviated program name for sheet names
     * 
     * @param string $programName Full program name
     * @return string Abbreviated name
     */
    private static function abbreviateProgramName(string $programName): string
    {
        // Common abbreviations for program names
        $abbreviations = [
            'Youth' => 'Y',
            'Summit' => 'S',
            'Conference' => 'Conf',
            'Workshop' => 'WS',
            'Training' => 'Tr',
            'Leadership' => 'Lead',
            'Development' => 'Dev',
            'International' => 'Intl',
            'Program' => 'Prog',
            'Initiative' => 'Init'
        ];
        
        $words = explode(' ', $programName);
        $abbreviated = [];
        
        foreach ($words as $word) {
            if (isset($abbreviations[$word])) {
                $abbreviated[] = $abbreviations[$word];
            } elseif (strlen($word) > 5) {
                // For long words, take first 4 characters
                $abbreviated[] = substr($word, 0, 4);
            } else {
                $abbreviated[] = $word;
            }
        }
        
        $result = implode(' ', $abbreviated);
        
        // If still too long, take initials only
        if (strlen($result) > 15) {
            $initials = [];
            foreach ($words as $word) {
                $initials[] = strtoupper(substr($word, 0, 1));
            }
            $result = implode('', $initials);
        }
        
        return $result;
    }
    
    /**
     * Generate filename for batch exports
     * 
     * @param string $baseFilename Base filename without extension
     * @param int $batchNumber Current batch number
     * @param int $totalBatches Total number of batches
     * @return string Batch filename
     */
    public static function generateBatchFilename(string $baseFilename, int $batchNumber, int $totalBatches): string
    {
        $extension = pathinfo($baseFilename, PATHINFO_EXTENSION);
        $nameWithoutExt = pathinfo($baseFilename, PATHINFO_FILENAME);
        
        return "{$nameWithoutExt}_batch_{$batchNumber}_of_{$totalBatches}.{$extension}";
    }
    
    /**
     * Generate ZIP archive filename
     * 
     * @param string $baseFilename Base filename without extension
     * @return string ZIP filename
     */
    public static function generateZipFilename(string $baseFilename): string
    {
        $nameWithoutExt = pathinfo($baseFilename, PATHINFO_FILENAME);
        return "{$nameWithoutExt}_complete_export.zip";
    }
}
