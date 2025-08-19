<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Advanced Optimized Participant Export Model
 * 
 * Ultra-high-performance export with Python/Excel compatibility
 * Features:
 * - Streaming data processing (constant memory usage)
 * - Python-ready data normalization
 * - Excel-optimized formatting
 * - Smart caching strategies
 * - 99% performance improvement target
 */
class AdvancedOptimizedParticipantExportModel extends Model
{
    protected $table = 'participants';
    
    // Cache for frequently accessed data
    private static $userCache = [];
    private static $programCache = [];
    private static $statusMappingCache = [];
    
    /**
     * 🚀 STREAMING EXPORT - Constant Memory Usage
     * 
     * This method uses generators to process data in chunks,
     * maintaining constant memory usage regardless of dataset size.
     * Perfect for datasets of ANY SIZE!
     */
    public function getStreamingOptimizedParticipantsForExport(array $filters): \Generator
    {
        try {
            if (!isset($filters['program_id']) || empty($filters['program_id'])) {
                throw new \RuntimeException('Program ID filter is required for participant export');
            }

            $programId = $filters['program_id'];
            $chunkSize = $filters['chunk_size'] ?? 1000; // Smaller chunks for streaming
            
            log_message('info', "Starting STREAMING optimized export for program $programId with chunk size $chunkSize");
            
            $startTime = microtime(true);
            $db = \Config\Database::connect();
            
            // Pre-load and cache frequently used data
            $this->preloadCacheData($programId, $db);
            
            // Get total count for progress tracking
            $totalCount = $this->getTotalParticipantCount($programId, $filters, $db);
            $processedCount = 0;
            
            // Stream data in chunks using LIMIT/OFFSET with generator
            $offset = 0;
            
            while ($processedCount < $totalCount) {
                $chunkData = $this->getParticipantChunk($programId, $filters, $offset, $chunkSize, $db);
                
                if (empty($chunkData)) {
                    break;
                }
                
                // Process and normalize chunk for Python/Excel compatibility
                $processedChunk = $this->processChunkForPythonExcel($chunkData, $programId);
                
                // Yield processed chunk (streaming!)
                yield [
                    'data' => $processedChunk,
                    'metadata' => [
                        'chunk_number' => intval($offset / $chunkSize) + 1,
                        'chunk_size' => count($processedChunk),
                        'total_processed' => $processedCount + count($processedChunk),
                        'total_count' => $totalCount,
                        'progress_percentage' => min(100, (($processedCount + count($processedChunk)) / $totalCount) * 100),
                        'processing_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
                    ]
                ];
                
                $processedCount += count($chunkData);
                $offset += $chunkSize;
                
                // Memory cleanup after each chunk
                if ($processedCount % (5 * $chunkSize) === 0) {
                    gc_collect_cycles();
                }
                
                // Micro-pause to prevent database overload
                usleep(10000); // 10ms
            }
            
            $totalTime = microtime(true) - $startTime;
            log_message('info', "STREAMING export completed in " . round($totalTime * 1000, 2) . "ms for $processedCount participants");
            
        } catch (\Exception $e) {
            log_message('error', 'Error in streaming export: ' . $e->getMessage());
            throw new \RuntimeException('Failed to stream participants data: ' . $e->getMessage());
        }
    }
    
    /**
     * 🎯 PYTHON-OPTIMIZED BULK EXPORT
     * 
     * Optimized for direct consumption by Python pandas/services
     * with proper data types and formatting
     */
    public function getPythonOptimizedParticipantsForExport(array $filters): array
    {
        try {
            $programId = $filters['program_id'];
            log_message('info', "Starting PYTHON-optimized export for program $programId");
            
            $startTime = microtime(true);
            $db = \Config\Database::connect();
            
            // Enable MySQL optimizations for large datasets
            $this->optimizeDatabaseSession($db);
            
            // Pre-load cache data
            $this->preloadCacheData($programId, $db);
            
            // Get all data using the most optimized query
            $participants = $this->getOptimizedParticipantData($programId, $filters, $db);
            
            if (empty($participants)) {
                return ['data' => [], 'metadata' => $this->generateMetadata([], $startTime)];
            }
            
            // Process for Python/Excel compatibility
            $pythonReadyData = [];
            foreach ($participants as $participant) {
                $pythonReadyData[] = $this->normalizePythonDataTypes($participant);
            }
            
            // Generate comprehensive metadata for Python service
            $metadata = $this->generateMetadata($pythonReadyData, $startTime);
            
            $totalTime = microtime(true) - $startTime;
            log_message('info', "PYTHON export completed in " . round($totalTime * 1000, 2) . "ms for " . count($pythonReadyData) . " participants");
            
            return [
                'data' => $pythonReadyData,
                'metadata' => $metadata
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Error in Python-optimized export: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve Python-optimized data: ' . $e->getMessage());
        }
    }
    
    /**
     * Pre-load frequently used data into cache
     */
    private function preloadCacheData(int $programId, $db): void
    {
        $startTime = microtime(true);
        
        // Cache program data
        if (!isset(self::$programCache[$programId])) {
            $program = $db->query("SELECT id, name, start_date, end_date, theme FROM programs WHERE id = ?", [$programId])->getRowArray();
            self::$programCache[$programId] = $program ?? [];
        }
        
        // Cache status mappings (they don't change often)
        if (empty(self::$statusMappingCache)) {
            self::$statusMappingCache = [
                'form_status' => [0 => 'Draft', 1 => 'Submitted', 2 => 'Approved'],
                'payment_status' => [0 => 'Not Required', 1 => 'Pending', 2 => 'Paid', 3 => 'Failed'],
                'general_status' => [0 => 'Pending Review', 1 => 'Under Review', 2 => 'Approved', 3 => 'Rejected'],
                'document_status' => [0 => 'Not Required', 1 => 'Pending', 2 => 'Submitted', 3 => 'Approved', 4 => 'Rejected']
            ];
        }
        
        $cacheTime = microtime(true) - $startTime;
        log_message('info', "Cache preloading completed in " . round($cacheTime * 1000, 2) . "ms");
    }
    
    /**
     * Get total participant count for progress tracking
     */
    private function getTotalParticipantCount(int $programId, array $filters, $db): int
    {
        $countQuery = "SELECT COUNT(*) as total FROM participants WHERE program_id = ? AND is_deleted = 0";
        $params = [$programId];
        
        // Apply filters to count query
        if (isset($filters['category']) && $filters['category'] !== 'all') {
            $countQuery .= " AND category = ?";
            $params[] = $filters['category'];
        }
        
        if (isset($filters['date_from'])) {
            $countQuery .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $countQuery .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        return $db->query($countQuery, $params)->getRowArray()['total'] ?? 0;
    }
    
    /**
     * Get a chunk of participant data
     */
    private function getParticipantChunk(int $programId, array $filters, int $offset, int $limit, $db): array
    {
        // Ultra-optimized query with minimal data transfer
        $query = "
            SELECT 
                p.id as participant_id,
                p.account_id,
                p.full_name,
                p.gender,
                p.birthdate,
                p.nationality,
                p.nationality_code,
                p.phone_number,
                p.country_code,
                p.category,
                p.occupation,
                p.education_level,
                p.major,
                p.institution,
                p.current_address,
                p.instagram_account,
                p.tshirt_size,
                p.created_at,
                p.user_id,
                u.email,
                u.is_verified,
                ps.form_status,
                ps.payment_status,
                ps.general_status,
                ps.document_status
            FROM participants p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN participant_statuses ps ON p.id = ps.participant_id
            WHERE p.program_id = ? AND p.is_deleted = 0
        ";
        
        $params = [$programId];
        
        // Apply filters
        if (isset($filters['category']) && $filters['category'] !== 'all') {
            $query .= " AND p.category = ?";
            $params[] = $filters['category'];
        }
        
        if (isset($filters['date_from'])) {
            $query .= " AND p.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $query .= " AND p.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Add ordering and pagination
        $query .= " ORDER BY p.id LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $db->query($query, $params)->getResultArray();
    }
    
    /**
     * Process chunk data for Python/Excel compatibility
     */
    private function processChunkForPythonExcel(array $chunkData, int $programId): array
    {
        $processed = [];
        $program = self::$programCache[$programId] ?? [];
        
        foreach ($chunkData as $participant) {
            $processed[] = $this->normalizePythonDataTypes([
                // Core participant data
                'participant_id' => intval($participant['participant_id']),
                'account_id' => $participant['account_id'] ?: null,
                'full_name' => $this->sanitizeForExcel($participant['full_name']),
                'email' => $participant['email'] ?: null,
                'phone' => $this->formatPhone($participant['phone_number'], $participant['country_code']),
                'nationality' => $participant['nationality'] ?: null,
                'nationality_code' => $participant['nationality_code'] ?: null,
                'gender' => $this->normalizeGender($participant['gender']),
                'birthdate' => $this->normalizeDateForPython($participant['birthdate']),
                'category' => $this->normalizeCategory($participant['category']),
                'education_level' => $this->normalizeEducationLevel($participant['education_level']),
                'major' => $participant['major'] ?: null,
                'institution' => $participant['institution'] ?: null,
                'occupation' => $participant['occupation'] ?: null,
                'current_address' => $this->sanitizeForExcel($participant['current_address']),
                'instagram_account' => $participant['instagram_account'] ?: null,
                'tshirt_size' => $participant['tshirt_size'] ?: null,
                'registration_date' => $this->normalizeDateForPython($participant['created_at']),
                'user_is_verified' => $participant['is_verified'] ? true : false,
                
                // Status data (human-readable for Excel)
                'form_status' => self::$statusMappingCache['form_status'][$participant['form_status']] ?? 'Unknown',
                'payment_status' => self::$statusMappingCache['payment_status'][$participant['payment_status']] ?? 'Unknown',
                'general_status' => self::$statusMappingCache['general_status'][$participant['general_status']] ?? 'Unknown',
                'document_status' => self::$statusMappingCache['document_status'][$participant['document_status']] ?? 'Unknown',
                
                // Program data
                'program_name' => $program['name'] ?? 'Unknown Program',
                'program_start_date' => $this->normalizeDateForPython($program['start_date'] ?? null),
                'program_end_date' => $this->normalizeDateForPython($program['end_date'] ?? null),
                'program_theme' => $program['theme'] ?: null,
            ]);
        }
        
        return $processed;
    }
    
    /**
     * 🐍 NORMALIZE DATA TYPES FOR PYTHON/PANDAS
     * 
     * Ensures data types are optimal for Python processing:
     * - int64 for integers
     * - bool for booleans (not 0/1)
     * - None for nulls (not empty strings)
     * - ISO 8601 dates
     * - UTF-8 normalized strings
     */
    private function normalizePythonDataTypes(array $data): array
    {
        $normalized = [];
        
        foreach ($data as $key => $value) {
            $normalized[$key] = match($key) {
                // Integer fields (pandas int64 compatible)
                'participant_id' => is_numeric($value) ? intval($value) : null,
                
                // Boolean fields (Python bool, not 0/1)
                'user_is_verified' => is_null($value) ? null : boolval($value),
                
                // Date fields (ISO 8601 format for Python datetime)
                'birthdate', 'registration_date', 'program_start_date', 'program_end_date' => 
                    $this->normalizeDateForPython($value),
                
                // String fields (UTF-8 normalized, null if empty)
                default => is_string($value) ? 
                    (trim($value) === '' ? null : $this->normalizeStringForPython($value)) : 
                    $value
            };
        }
        
        return $normalized;
    }
    
    /**
     * Normalize dates for Python datetime compatibility
     */
    private function normalizeDateForPython(?string $date): ?string
    {
        if (!$date || trim($date) === '') {
            return null;
        }
        
        try {
            return date('Y-m-d H:i:s', strtotime($date));
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Normalize strings for Python/Excel compatibility
     */
    private function normalizeStringForPython(?string $str): ?string
    {
        if (!$str || trim($str) === '') {
            return null;
        }
        
        // UTF-8 normalize and clean for Excel
        $cleaned = $this->sanitizeForExcel($str);
        
        // Decode HTML entities (common in database data)
        $cleaned = html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Normalize Unicode (for international characters)
        if (class_exists('Normalizer')) {
            $cleaned = \Normalizer::normalize($cleaned, \Normalizer::FORM_C);
        }
        
        return trim($cleaned) ?: null;
    }
    
    /**
     * 📊 EXCEL-SAFE DATA SANITIZATION
     * 
     * Prevents Excel corruption and injection attacks
     */
    private function sanitizeForExcel(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        
        // Remove dangerous characters that cause Excel corruption
        $value = str_replace(["\0", "\r"], '', $value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        // Prevent Excel formula injection
        if (strlen($value) > 0 && in_array($value[0], ['=', '+', '-', '@', '\t', '\r'])) {
            $value = "'" . $value; // Prefix with single quote to treat as text
        }
        
        // Truncate if too long (Excel 32,767 character limit)
        if (strlen($value) > 32000) {
            $value = substr($value, 0, 32000) . '...';
        }
        
        return trim($value) ?: null;
    }
    
    /**
     * Format phone number with country code
     */
    private function formatPhone(?string $phone, ?string $countryCode): ?string
    {
        if (!$phone) {
            return null;
        }
        
        $formattedPhone = trim($phone);
        
        if ($countryCode && !str_starts_with($formattedPhone, '+')) {
            $formattedPhone = "+{$countryCode} {$formattedPhone}";
        }
        
        return $formattedPhone;
    }
    
    /**
     * Normalize gender values
     */
    private function normalizeGender(?string $gender): ?string
    {
        if (!$gender) {
            return null;
        }
        
        return match(strtolower(trim($gender))) {
            'm', 'male', '1' => 'Male',
            'f', 'female', '2' => 'Female',
            'o', 'other' => 'Other',
            default => 'Not Specified'
        };
    }
    
    /**
     * Normalize category values
     */
    private function normalizeCategory(?string $category): ?string
    {
        if (!$category) {
            return null;
        }
        
        return match(strtolower(str_replace([' ', '_'], '', trim($category)))) {
            'fullyfunded' => 'Fully Funded',
            'selffunded' => 'Self Funded',
            default => ucwords(str_replace('_', ' ', $category))
        };
    }
    
    /**
     * Normalize education level
     */
    private function normalizeEducationLevel(?string $level): ?string
    {
        if (!$level) {
            return null;
        }
        
        return match(strtolower(str_replace([' ', '_', "'"], '', trim($level)))) {
            'highschool' => 'High School',
            'bachelor', 'bachelors' => 'Bachelor\'s Degree', 
            'master', 'masters' => 'Master\'s Degree',
            'doctorate', 'phd', 'ph.d' => 'Doctorate',
            default => ucwords(str_replace(['_', '-'], ' ', $level))
        };
    }
    
    /**
     * Generate comprehensive metadata for Python service
     */
    private function generateMetadata(array $data, float $startTime): array
    {
        $endTime = microtime(true);
        $sampleData = !empty($data) ? array_slice($data, 0, 3) : [];
        
        // Analyze data types
        $columnTypes = [];
        if (!empty($data)) {
            foreach ($data[0] as $key => $value) {
                $columnTypes[$key] = $this->detectPythonDataType($value);
            }
        }
        
        return [
            'export_info' => [
                'total_records' => count($data),
                'columns_count' => !empty($data) ? count($data[0]) : 0,
                'processing_time_ms' => round(($endTime - $startTime) * 1000, 2),
                'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'export_timestamp' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'data_format' => 'python_optimized'
            ],
            'data_types' => $columnTypes,
            'excel_hints' => [
                'auto_width' => true,
                'freeze_header' => true,
                'apply_filters' => true,
                'format_dates' => true,
                'format_numbers' => true,
                'text_wrap' => false
            ],
            'quality_metrics' => [
                'null_percentage' => $this->calculateNullPercentage($data),
                'data_completeness' => $this->calculateDataCompleteness($data),
                'encoding' => 'UTF-8',
                'sanitization_applied' => true
            ],
            'sample_data' => $sampleData
        ];
    }
    
    /**
     * Detect Python-compatible data type
     */
    private function detectPythonDataType($value): string
    {
        if (is_null($value)) {
            return 'object';
        }
        
        if (is_int($value)) {
            return 'int64';
        }
        
        if (is_float($value)) {
            return 'float64';
        }
        
        if (is_bool($value)) {
            return 'bool';
        }
        
        if (is_string($value)) {
            // Check if it's a date
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                return 'datetime64';
            }
            
            // Check if it's numeric
            if (is_numeric($value)) {
                return strpos($value, '.') !== false ? 'float64' : 'int64';
            }
            
            return 'string';
        }
        
        return 'object';
    }
    
    /**
     * Calculate null percentage for quality metrics
     */
    private function calculateNullPercentage(array $data): float
    {
        if (empty($data)) {
            return 0.0;
        }
        
        $totalCells = count($data) * count($data[0]);
        $nullCells = 0;
        
        foreach ($data as $row) {
            foreach ($row as $value) {
                if (is_null($value)) {
                    $nullCells++;
                }
            }
        }
        
        return round(($nullCells / $totalCells) * 100, 2);
    }
    
    /**
     * Calculate data completeness score
     */
    private function calculateDataCompleteness(array $data): float
    {
        if (empty($data)) {
            return 0.0;
        }
        
        $completenessScore = 100.0 - $this->calculateNullPercentage($data);
        return round($completenessScore, 2);
    }
    
    /**
     * Optimize database session for large exports
     */
    private function optimizeDatabaseSession($db): void
    {
        try {
            $db->query("SET SESSION sql_big_selects=1");
            $db->query("SET SESSION max_heap_table_size=536870912"); // 512MB
            $db->query("SET SESSION tmp_table_size=536870912"); // 512MB
            $db->query("SET SESSION read_buffer_size=2097152"); // 2MB
            $db->query("SET SESSION sort_buffer_size=4194304"); // 4MB
            $db->query("SET SESSION join_buffer_size=4194304"); // 4MB
        } catch (\Exception $e) {
            log_message('warning', 'Could not optimize database session: ' . $e->getMessage());
        }
    }
    
    /**
     * Get optimized participant data with improved query
     */
    private function getOptimizedParticipantData(int $programId, array $filters, $db): array
    {
        // Use the most optimized query with proper indexing
        $query = "
            SELECT 
                p.id as participant_id,
                p.account_id,
                p.full_name,
                p.gender,
                p.birthdate,
                p.nationality,
                p.nationality_code,
                p.phone_number,
                p.country_code,
                p.category,
                p.occupation,
                p.education_level,
                p.major,
                p.institution,
                p.current_address,
                p.instagram_account,
                p.tshirt_size,
                p.created_at,
                p.user_id,
                u.email,
                u.is_verified,
                ps.form_status,
                ps.payment_status,
                ps.general_status,
                ps.document_status
            FROM participants p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN participant_statuses ps ON p.id = ps.participant_id
            WHERE p.program_id = ? AND p.is_deleted = 0
        ";
        
        $params = [$programId];
        
        // Apply filters efficiently
        if (isset($filters['category']) && $filters['category'] !== 'all') {
            $query .= " AND p.category = ?";
            $params[] = $filters['category'];
        }
        
        if (isset($filters['date_from'])) {
            $query .= " AND p.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $query .= " AND p.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Apply status filters
        if (isset($filters['form_status']) && $filters['form_status'] !== 'all') {
            $query .= " AND ps.form_status = ?";
            $params[] = $filters['form_status'];
        }
        
        if (isset($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $query .= " AND ps.payment_status = ?";
            $params[] = $filters['payment_status'];
        }
        
        if (isset($filters['general_status']) && $filters['general_status'] !== 'all') {
            $query .= " AND ps.general_status = ?";
            $params[] = $filters['general_status'];
        }
        
        $query .= " ORDER BY p.id";
        
        return $db->query($query, $params)->getResultArray();
    }
}
