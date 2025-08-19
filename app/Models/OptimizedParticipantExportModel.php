<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Optimized Participant Export Model
 * 
 * This model provides high-performance export methods for large datasets
 * with significant query optimizations and memory management.
 */
class OptimizedParticipantExportModel extends Model
{
    protected $table = 'participants';
    
    /**
     * Optimized participant export with performance improvements:
     * 1. Minimal JOINs
     * 2. Indexed query paths
     * 3. Chunked processing
     * 4. Separate essay retrieval
     * 5. Memory-efficient processing
     */
    public function getOptimizedParticipantsForExport(array $filters): array
    {
        try {
            // CRITICAL: Program ID is required
            if (!isset($filters['program_id']) || empty($filters['program_id'])) {
                throw new \RuntimeException('Program ID filter is required for participant export');
            }

            $programId = $filters['program_id'];
            log_message('info', "Starting OPTIMIZED participant export for program $programId");

            $startTime = microtime(true);
            
            // Use optimized database connection with performance settings
            $db = \Config\Database::connect();
            
            // Enable query result buffering for better memory management
            $db->query("SET SESSION sql_big_selects=1");
            $db->query("SET SESSION max_heap_table_size=268435456"); // 256MB
            $db->query("SET SESSION tmp_table_size=268435456"); // 256MB
            
            // Step 1: Get core participant data with minimal JOINs (FASTEST)
            $coreQuery = "
                SELECT 
                    p.id as participant_id,
                    p.account_id as participant_account_id,
                    p.full_name as participant_full_name,
                    p.gender as participant_gender,
                    p.birthdate as participant_birthdate,
                    p.nationality as participant_nationality,
                    p.nationality_code as participant_nationality_code,
                    p.phone_number as participant_phone,
                    p.country_code as participant_country_code,
                    p.category as participant_category,
                    p.occupation as participant_occupation,
                    p.education_level as participant_education_level,
                    p.major as participant_major,
                    p.institution as participant_institution,
                    p.current_address as participant_current_address,
                    p.instagram_account as participant_instagram,
                    p.tshirt_size as participant_tshirt_size,
                    p.created_at as participant_registered_at,
                    p.user_id
                FROM participants p
                WHERE p.program_id = ?
                  AND p.is_deleted = 0
            ";
            
            // Apply core filters to reduce dataset early
            $params = [$programId];
            if (isset($filters['category']) && $filters['category'] !== 'all') {
                $coreQuery .= " AND p.category = ?";
                $params[] = $filters['category'];
            }
            
            if (isset($filters['date_from'])) {
                $coreQuery .= " AND p.created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (isset($filters['date_to'])) {
                $coreQuery .= " AND p.created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $coreQuery .= " ORDER BY p.id"; // Use primary key for consistent ordering
            
            log_message('info', "Executing optimized core query for program $programId");
            $coreResult = $db->query($coreQuery, $params)->getResultArray();
            $participantIds = array_column($coreResult, 'participant_id');
            $userIds = array_unique(array_column($coreResult, 'user_id'));
            
            $coreTime = microtime(true) - $startTime;
            log_message('info', "Core query completed in " . round($coreTime * 1000, 2) . "ms, found " . count($coreResult) . " participants");
            
            if (empty($coreResult)) {
                return [];
            }
            
            // Filter out null user IDs before processing
            $userIds = array_filter(array_unique(array_column($coreResult, 'user_id')), function($id) {
                return !is_null($id) && $id !== '';
            });
            
            // Step 2: Get user data in batch (FAST - using IN clause with IDs)
            $userData = [];
            if (!empty($userIds)) {
                $userPlaceholders = str_repeat('?,', count($userIds) - 1) . '?';
                $userQuery = "
                    SELECT id, email, is_verified
                    FROM users 
                    WHERE id IN ($userPlaceholders)
                ";
                $userResult = $db->query($userQuery, $userIds)->getResultArray();
                foreach ($userResult as $user) {
                    $userData[$user['id']] = $user;
                }
                log_message('info', "User data retrieved for " . count($userData) . " users");
            }
            
            // Step 3: Get program data once (VERY FAST - single record)
            $programData = $db->query("
                SELECT name, start_date, end_date, theme
                FROM programs 
                WHERE id = ?
            ", [$programId])->getRowArray();
            
            // Step 4: Get participant statuses in batch (FAST - using IN clause)
            $statusData = [];
            if (!empty($participantIds)) {
                $statusPlaceholders = str_repeat('?,', count($participantIds) - 1) . '?';
                $statusQuery = "
                    SELECT participant_id, form_status, payment_status, general_status, document_status
                    FROM participant_statuses 
                    WHERE participant_id IN ($statusPlaceholders)
                ";
                $statusResult = $db->query($statusQuery, $participantIds)->getResultArray();
                foreach ($statusResult as $status) {
                    $statusData[$status['participant_id']] = $status;
                }
                log_message('info', "Status data retrieved for " . count($statusData) . " participants");
            }
            
            // Step 5: Get essays separately if needed (OPTIMIZED - avoid complex JOINs)
            $essayData = [];
            $essayCount = 0;
            
            // Check if program has essays
            $programEssayModel = new \App\Models\ProgramEssayModel();
            $programEssays = $programEssayModel->getActiveEssays($programId);
            $essayCount = count($programEssays);
            
            if ($essayCount > 0 && !empty($participantIds)) {
                log_message('info', "Retrieving essay data for $essayCount essays");
                
                // Get all essays in one optimized query
                $essayPlaceholders = str_repeat('?,', count($participantIds) - 1) . '?';
                $essayQuery = "
                    SELECT 
                        pae.participant_id,
                        pae.answer,
                        pe.questions,
                        ROW_NUMBER() OVER (PARTITION BY pae.participant_id ORDER BY pe.id) as essay_order
                    FROM participant_essays pae
                    INNER JOIN program_essays pe ON pe.id = pae.program_essay_id
                    WHERE pae.participant_id IN ($essayPlaceholders)
                      AND pe.program_id = ?
                      AND pe.is_deleted = 0 
                      AND pe.is_active = 1
                      AND pae.is_deleted = 0
                    ORDER BY pae.participant_id, pe.id
                ";
                
                $essayParams = array_merge($participantIds, [$programId]);
                $essayResult = $db->query($essayQuery, $essayParams)->getResultArray();
                
                // Group essays by participant
                foreach ($essayResult as $essay) {
                    $participantId = $essay['participant_id'];
                    if (!isset($essayData[$participantId])) {
                        $essayData[$participantId] = [];
                    }
                    $essayData[$participantId][$essay['essay_order']] = [
                        'answer' => $essay['answer'],
                        'question' => $essay['questions']
                    ];
                }
                
                log_message('info', "Essay data retrieved for " . count($essayData) . " participants");
            }
            
            // Step 6: Combine all data efficiently (FAST - in-memory operations)
            $combinedResult = [];
            
            foreach ($coreResult as $participant) {
                $participantId = $participant['participant_id'];
                $userId = $participant['user_id'] ?? null;
                
                // Merge user data (safely handle missing user IDs)
                if ($userId && isset($userData[$userId])) {
                    $user = $userData[$userId];
                    $participant['participant_email'] = $user['email'] ?? 'No Email';
                    $participant['user_is_verified'] = $user['is_verified'] ?? 0;
                } else {
                    $participant['participant_email'] = 'No Email';
                    $participant['user_is_verified'] = 0;
                    if ($userId) {
                        log_message('warning', "User ID $userId not found for participant $participantId");
                    }
                }
                
                // Merge program data
                $participant['program_name'] = $programData['name'] ?? 'Unknown Program';
                $participant['program_start_date'] = $programData['start_date'] ?? null;
                $participant['program_end_date'] = $programData['end_date'] ?? null;
                $participant['program_theme'] = $programData['theme'] ?? null;
                
                // Merge status data (safely handle missing status records)
                if (isset($statusData[$participantId])) {
                    $status = $statusData[$participantId];
                    $participant['form_status_code'] = $status['form_status'] ?? null;
                    $participant['payment_status_code'] = $status['payment_status'] ?? null;
                    $participant['general_status_code'] = $status['general_status'] ?? null;
                    $participant['document_status_code'] = $status['document_status'] ?? null;
                } else {
                    $participant['form_status_code'] = null;
                    $participant['payment_status_code'] = null;
                    $participant['general_status_code'] = null;
                    $participant['document_status_code'] = null;
                }
                
                // Merge essay data
                if ($essayCount > 0) {
                    $participantEssays = $essayData[$participantId] ?? [];
                    for ($i = 1; $i <= $essayCount; $i++) {
                        $participant["essay_$i"] = isset($participantEssays[$i]) ? $participantEssays[$i]['answer'] : null;
                        $participant["essay_{$i}_question"] = isset($participantEssays[$i]) ? $participantEssays[$i]['question'] : null;
                    }
                }
                
                // Remove user_id as it's not needed in export
                unset($participant['user_id']);
                
                $combinedResult[] = $participant;
            }
            
            $totalTime = microtime(true) - $startTime;
            log_message('info', "OPTIMIZED export completed in " . round($totalTime * 1000, 2) . "ms for " . count($combinedResult) . " participants");
            
            // Step 7: Apply status filters AFTER data retrieval (more efficient than JOIN filtering)
            if (isset($filters['form_status']) || isset($filters['payment_status']) || isset($filters['general_status'])) {
                $filteredResult = [];
                foreach ($combinedResult as $participant) {
                    $include = true;
                    
                    if (isset($filters['form_status']) && $filters['form_status'] !== 'all') {
                        if ($participant['form_status_code'] != $filters['form_status']) {
                            $include = false;
                        }
                    }
                    
                    if (isset($filters['payment_status']) && $filters['payment_status'] !== 'all') {
                        if ($participant['payment_status_code'] != $filters['payment_status']) {
                            $include = false;
                        }
                    }
                    
                    if (isset($filters['general_status']) && $filters['general_status'] !== 'all') {
                        if ($participant['general_status_code'] != $filters['general_status']) {
                            $include = false;
                        }
                    }
                    
                    if ($include) {
                        $filteredResult[] = $participant;
                    }
                }
                $combinedResult = $filteredResult;
                log_message('info', "Applied status filters, final count: " . count($combinedResult) . " participants");
            }
            
            // Step 8: Normalize the data efficiently
            $normalizedResult = [];
            foreach ($combinedResult as $participant) {
                $normalizedParticipant = $this->normalizeParticipantForExport($participant, $essayCount);
                $sanitizedParticipant = $this->sanitizeParticipantData($normalizedParticipant);
                $normalizedResult[] = $sanitizedParticipant;
            }
            
            $finalTime = microtime(true) - $startTime;
            log_message('info', "FINAL optimized export completed in " . round($finalTime * 1000, 2) . "ms for " . count($normalizedResult) . " participants");
            
            return $normalizedResult;

        } catch (\Exception $e) {
            log_message('error', 'Error in getOptimizedParticipantsForExport: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve optimized participants data: ' . $e->getMessage());
        }
    }
    
    /**
     * Chunked processing version for extremely large datasets (100k+ records)
     */
    public function getChunkedOptimizedParticipantsForExport(array $filters, int $chunkSize = 5000): array
    {
        try {
            $programId = $filters['program_id'];
            log_message('info', "Starting CHUNKED optimized export for program $programId with chunk size $chunkSize");
            
            $db = \Config\Database::connect();
            $allResults = [];
            $offset = 0;
            $totalProcessed = 0;
            
            // Get total count first
            $countQuery = "SELECT COUNT(*) as total FROM participants WHERE program_id = ? AND is_deleted = 0";
            $countParams = [$programId];
            
            if (isset($filters['category']) && $filters['category'] !== 'all') {
                $countQuery .= " AND category = ?";
                $countParams[] = $filters['category'];
            }
            
            $totalCount = $db->query($countQuery, $countParams)->getRowArray()['total'];
            log_message('info', "Total participants to process: $totalCount");
            
            while ($totalProcessed < $totalCount) {
                log_message('info', "Processing chunk: offset $offset, size $chunkSize");
                
                // Create chunked filters
                $chunkFilters = $filters;
                $chunkFilters['limit'] = $chunkSize;
                $chunkFilters['offset'] = $offset;
                
                // Get chunk data
                $chunkData = $this->getOptimizedParticipantsChunk($chunkFilters);
                
                if (empty($chunkData)) {
                    break;
                }
                
                $allResults = array_merge($allResults, $chunkData);
                $totalProcessed += count($chunkData);
                $offset += $chunkSize;
                
                log_message('info', "Processed $totalProcessed of $totalCount participants");
                
                // Memory management
                if ($totalProcessed % (5 * $chunkSize) === 0) {
                    gc_collect_cycles();
                }
                
                // Brief pause to prevent database overload
                usleep(50000); // 50ms
            }
            
            log_message('info', "Chunked optimized export completed: $totalProcessed participants processed");
            return $allResults;
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getChunkedOptimizedParticipantsForExport: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve chunked optimized participants data: ' . $e->getMessage());
        }
    }
    
    /**
     * Get a single chunk of participants with optimized queries
     */
    private function getOptimizedParticipantsChunk(array $filters): array
    {
        $programId = $filters['program_id'];
        $limit = $filters['limit'] ?? 5000;
        $offset = $filters['offset'] ?? 0;
        
        $db = \Config\Database::connect();
        
        // Core query with LIMIT and OFFSET
        $coreQuery = "
            SELECT 
                p.id as participant_id,
                p.account_id as participant_account_id,
                p.full_name as participant_full_name,
                p.gender as participant_gender,
                p.birthdate as participant_birthdate,
                p.nationality as participant_nationality,
                p.phone_number as participant_phone,
                p.category as participant_category,
                p.occupation as participant_occupation,
                p.education_level as participant_education_level,
                p.major as participant_major,
                p.institution as participant_institution,
                p.current_address as participant_current_address,
                p.created_at as participant_registered_at,
                p.user_id
            FROM participants p
            WHERE p.program_id = ?
              AND p.is_deleted = 0
        ";
        
        $params = [$programId];
        if (isset($filters['category']) && $filters['category'] !== 'all') {
            $coreQuery .= " AND p.category = ?";
            $params[] = $filters['category'];
        }
        
        $coreQuery .= " ORDER BY p.id LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $coreResult = $db->query($coreQuery, $params)->getResultArray();
        
        if (empty($coreResult)) {
            return [];
        }
        
        // Process this chunk using the same optimized approach as the main method
        // (Implementation would be similar to the main method but for this chunk only)
        
        return $this->processChunkData($coreResult, $programId);
    }
    
    /**
     * Process chunk data with optimized lookups
     */
    private function processChunkData(array $coreResult, int $programId): array
    {
        $db = \Config\Database::connect();
        
        $participantIds = array_column($coreResult, 'participant_id');
        
        // Filter out null user IDs before processing
        $userIds = array_filter(array_unique(array_column($coreResult, 'user_id')), function($id) {
            return !is_null($id) && $id !== '';
        });
        
        // Get related data for this chunk
        $userData = $this->getBatchUserData($userIds);
        $statusData = $this->getBatchStatusData($participantIds);
        $programData = $this->getProgramData($programId);
        
        log_message('info', "Chunk processing: " . count($participantIds) . " participants, " . count($userIds) . " users, " . count($userData) . " user records retrieved");
        
        // Combine and normalize
        $normalizedResult = [];
        foreach ($coreResult as $participant) {
            $participantId = $participant['participant_id'];
            $userId = $participant['user_id'] ?? null;
            
            // Debug logging for the problematic case
            if ($userId == 428 || $participantId == 428) {
                log_message('info', "Processing participant ID: $participantId, user ID: $userId, user data exists: " . (isset($userData[$userId]) ? 'yes' : 'no'));
            }
            
            // Merge all data with safe array access
            if ($userId && isset($userData[$userId])) {
                $participant['participant_email'] = $userData[$userId]['email'] ?? 'No Email';
            } else {
                $participant['participant_email'] = 'No Email';
                if ($userId) {
                    log_message('warning', "User ID $userId not found for participant $participantId in chunk processing");
                }
            }
            
            $participant['program_name'] = $programData['name'] ?? 'Unknown Program';
            
            $status = $statusData[$participantId] ?? [];
            $participant['form_status_code'] = $status['form_status'] ?? null;
            $participant['payment_status_code'] = $status['payment_status'] ?? null;
            $participant['general_status_code'] = $status['general_status'] ?? null;
            
            unset($participant['user_id']);
            
            // Normalize and add to result
            $normalizedParticipant = $this->normalizeParticipantForExport($participant, 0);
            $normalizedResult[] = $this->sanitizeParticipantData($normalizedParticipant);
        }
        
        return $normalizedResult;
    }
    
    /**
     * Get user data in batch
     */
    private function getBatchUserData(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }
        
        $db = \Config\Database::connect();
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        $query = "SELECT id, email, is_verified FROM users WHERE id IN ($placeholders)";
        $result = $db->query($query, $userIds)->getResultArray();
        
        $userData = [];
        foreach ($result as $user) {
            $userData[$user['id']] = $user;
        }
        
        return $userData;
    }
    
    /**
     * Get status data in batch
     */
    private function getBatchStatusData(array $participantIds): array
    {
        if (empty($participantIds)) {
            return [];
        }
        
        $db = \Config\Database::connect();
        $placeholders = str_repeat('?,', count($participantIds) - 1) . '?';
        $query = "SELECT participant_id, form_status, payment_status, general_status, document_status FROM participant_statuses WHERE participant_id IN ($placeholders)";
        $result = $db->query($query, $participantIds)->getResultArray();
        
        $statusData = [];
        foreach ($result as $status) {
            $statusData[$status['participant_id']] = $status;
        }
        
        return $statusData;
    }
    
    /**
     * Get program data (cached for chunk processing)
     */
    private static $programDataCache = [];
    
    private function getProgramData(int $programId): array
    {
        if (isset(self::$programDataCache[$programId])) {
            return self::$programDataCache[$programId];
        }
        
        $db = \Config\Database::connect();
        $programData = $db->query("SELECT name, start_date, end_date, theme FROM programs WHERE id = ?", [$programId])->getRowArray();
        
        self::$programDataCache[$programId] = $programData ?? [];
        return self::$programDataCache[$programId];
    }
    
    /**
     * Normalize participant data for export (optimized version)
     */
    public function normalizeParticipantForExport(array $participant, int $essayCount): array
    {
        // Same normalization logic but optimized field access
        return [
            'Participant_ID' => $participant['participant_id'] ?? 'N/A',
            'Account_ID' => $participant['participant_account_id'] ?? 'N/A',
            'Full_Name' => $participant['participant_full_name'] ?? 'Unknown',
            'Email' => $participant['participant_email'] ?? 'No Email',
            'Phone' => $participant['participant_phone'] ?? 'Not Provided',
            'Nationality' => $participant['participant_nationality'] ?? 'Not Specified',
            'Gender' => $this->formatGender($participant['participant_gender'] ?? ''),
            'Birthdate' => $this->formatDate($participant['participant_birthdate']),
            'Category' => $this->formatCategory($participant['participant_category'] ?? ''),
            'Education_Level' => $this->formatEducationLevel($participant['participant_education_level'] ?? ''),
            'Major_Field' => $participant['participant_major'] ?? 'Not Specified',
            'Institution' => $participant['participant_institution'] ?? 'Not Specified',
            'Occupation' => $participant['participant_occupation'] ?? 'Not Specified',
            'Current_Address' => $participant['participant_current_address'] ?? 'Not Provided',
            'Registration_Date' => $participant['participant_registered_at'] ?? 'Unknown',
            'Form_Status' => $this->formatFormStatus($participant['form_status_code'] ?? null),
            'Payment_Status' => $this->formatPaymentStatus($participant['payment_status_code'] ?? null),
            'General_Status' => $this->formatGeneralStatus($participant['general_status_code'] ?? null),
            'Program_Name' => $participant['program_name'] ?? 'Unknown Program',
        ];
    }
    
    /**
     * Sanitize participant data (optimized)
     */
    public function sanitizeParticipantData(array $participant): array
    {
        foreach ($participant as $key => &$value) {
            if (is_string($value)) {
                $value = str_replace(["\0", "\r"], ['', ''], $value);
                $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
            }
        }
        return $participant;
    }
    
    // Formatting methods (same as original but optimized)
    private function formatGender(?string $gender): string
    {
        return match(strtolower($gender ?? '')) {
            'm', 'male', '1' => 'Male',
            'f', 'female', '2' => 'Female',
            default => 'Not Specified'
        };
    }
    
    private function formatDate(?string $date): string
    {
        if (!$date) return 'Not Provided';
        try {
            return date('Y-m-d', strtotime($date));
        } catch (\Exception $e) {
            return 'Invalid Date';
        }
    }
    
    private function formatCategory(?string $category): string
    {
        return match(strtolower($category ?? '')) {
            'fully_funded', 'fully funded' => 'Fully Funded',
            'self_funded', 'self funded' => 'Self Funded',
            default => $category ?: 'Not Specified'
        };
    }
    
    private function formatEducationLevel(?string $level): string
    {
        return match(strtolower($level ?? '')) {
            'high_school', 'high school' => 'High School',
            'bachelor', 'bachelors', 'bachelor\'s' => 'Bachelor\'s Degree',
            'master', 'masters', 'master\'s' => 'Master\'s Degree',
            'doctorate', 'phd', 'ph.d.' => 'Doctorate',
            default => $level ?: 'Not Specified'
        };
    }
    
    private function formatFormStatus(?int $status): string
    {
        return match($status) {
            0 => 'Draft',
            1 => 'Submitted',
            2 => 'Approved',
            default => 'Unknown'
        };
    }
    
    private function formatPaymentStatus(?int $status): string
    {
        return match($status) {
            0 => 'Not Required',
            1 => 'Pending',
            2 => 'Paid',
            3 => 'Failed',
            default => 'Unknown'
        };
    }
    
    private function formatGeneralStatus(?int $status): string
    {
        return match($status) {
            0 => 'Pending Review',
            1 => 'Under Review', 
            2 => 'Approved',
            3 => 'Rejected',
            default => 'Unknown'
        };
    }
}
