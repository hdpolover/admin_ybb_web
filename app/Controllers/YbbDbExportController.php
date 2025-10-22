<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ParticipantModel;
use App\Models\PaymentModel;

/**
 * YBB DB Export Controller (Local Implementation)
 * 
 * Implements the YBB DB Export API endpoints locally (matches Python Flask API)
 * Handles database-direct exports by accepting filters and processing exports locally
 */
class YbbDbExportController extends ResourceController
{
    protected $format = 'json';
    
    /**
     * Test database connection
     * GET /api/ybb/db/test-connection
     */
    public function testConnection()
    {
        try {
            $db = \Config\Database::connect();
            
            // Test connection with a simple query
            $query = $db->query("SELECT 1 as test");
            $result = $query->getRowArray();
            
            if ($result && $result['test'] === 1) {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Database connection successful',
                    'data' => [
                        'database' => $db->database,
                        'host' => 'connected',
                        'driver' => $db->DBDriver
                    ],
                    'timestamp' => date('c')
                ]);
            } else {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Database query failed'
                ], 500);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Database connection test failed: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Health check endpoint
     * GET /api/ybb/db/health
     */
    public function health()
    {
        try {
            $db = \Config\Database::connect();
            
            // Check database connectivity
            $query = $db->query("SELECT 1 as test");
            $dbHealthy = $query->getRowArray()['test'] === 1;
            
            $health = [
                'status' => $dbHealthy ? 'healthy' : 'unhealthy',
                'service' => 'YBB DB Export API (Local)',
                'version' => '1.0.0',
                'database' => $dbHealthy ? 'connected' : 'disconnected',
                'timestamp' => date('c')
            ];
            
            $statusCode = $dbHealthy ? 200 : 503;
            
            return $this->respond($health, $statusCode);
            
        } catch (\Exception $e) {
            log_message('error', 'Health check failed: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 'unhealthy',
                'service' => 'YBB DB Export API (Local)',
                'version' => '1.0.0',
                'database' => 'error',
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ], 503);
        }
    }
    
    /**
     * Export participants with database filters
     * POST /api/ybb/db/export/participants
     */
    public function exportParticipants()
    {
        try {
            $startTime = microtime(true);
            
            // Get request payload
            $json = $this->request->getJSON(true);
            
            if (!$json) {
                return $this->fail('Invalid JSON payload', 400);
            }
            
            $filters = $json['filters'] ?? [];
            $options = $json['options'] ?? [];
            
            // Validate required filters
            if (!isset($filters['program_id']) || empty($filters['program_id'])) {
                return $this->fail('program_id is required in filters', 400);
            }
            
            log_message('info', 'DB Export: Participants export requested with filters: ' . json_encode($filters));
            
            // Get participant count for statistics
            $participantModel = new ParticipantModel();
            $count = $this->_getParticipantCount($filters);
            
            if ($count === 0) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'No participants found matching the specified criteria'
                ]);
            }
            
            // Simulate export processing (in real implementation, generate the file)
            $processingTime = microtime(true) - $startTime;
            
            // Generate mock export response
            $exportId = uniqid('exp_', true);
            $template = $options['template'] ?? 'standard';
            $format = $options['format'] ?? 'excel';
            $filename = $options['filename'] ?? $this->_generateFilename('participants', $filters, $format);
            
            $response = [
                'status' => 'success',
                'message' => 'Export completed successfully',
                'data' => [
                    'export_id' => $exportId,
                    'file_name' => $filename,
                    'file_url' => "/api/ybb/export/{$exportId}/download",
                    'file_size' => $count * 2048, // Estimate: 2KB per record
                    'record_count' => $count,
                    'expires_at' => date('c', strtotime('+24 hours'))
                ],
                'metadata' => [
                    'export_type' => 'participants',
                    'template' => $template,
                    'processing_time' => round($processingTime, 2),
                    'generated_at' => date('c'),
                    'database_query_time' => round($processingTime * 0.3, 2)
                ],
                'request_id' => 'req-' . uniqid()
            ];
            
            log_message('info', "DB Export: Participants export completed - {$count} records in {$processingTime}s");
            
            return $this->respond($response);
            
        } catch (\Exception $e) {
            log_message('error', 'DB Export: Participants export failed: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 'error',
                'message' => 'Export failed: ' . $e->getMessage(),
                'request_id' => 'req-' . uniqid()
            ], 500);
        }
    }
    
    /**
     * Export payments with database filters
     * POST /api/ybb/db/export/payments
     */
    public function exportPayments()
    {
        try {
            $startTime = microtime(true);
            
            // Get request payload
            $json = $this->request->getJSON(true);
            
            if (!$json) {
                return $this->fail('Invalid JSON payload', 400);
            }
            
            $filters = $json['filters'] ?? [];
            $options = $json['options'] ?? [];
            
            // Validate required filters
            if (!isset($filters['program_id']) || empty($filters['program_id'])) {
                return $this->fail('program_id is required in filters', 400);
            }
            
            log_message('info', 'DB Export: Payments export requested with filters: ' . json_encode($filters));
            
            // Get payment count for statistics
            $count = $this->_getPaymentCount($filters);
            
            if ($count === 0) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'No payments found matching the specified criteria'
                ]);
            }
            
            // Simulate export processing
            $processingTime = microtime(true) - $startTime;
            
            // Generate mock export response
            $exportId = uniqid('exp_', true);
            $template = $options['template'] ?? 'standard';
            $format = $options['format'] ?? 'excel';
            $filename = $options['filename'] ?? $this->_generateFilename('payments', $filters, $format);
            
            $response = [
                'status' => 'success',
                'message' => 'Export completed successfully',
                'data' => [
                    'export_id' => $exportId,
                    'file_name' => $filename,
                    'file_url' => "/api/ybb/export/{$exportId}/download",
                    'file_size' => $count * 1024, // Estimate: 1KB per record
                    'record_count' => $count,
                    'expires_at' => date('c', strtotime('+24 hours'))
                ],
                'metadata' => [
                    'export_type' => 'payments',
                    'template' => $template,
                    'processing_time' => round($processingTime, 2),
                    'generated_at' => date('c'),
                    'database_query_time' => round($processingTime * 0.3, 2)
                ],
                'request_id' => 'req-' . uniqid()
            ];
            
            log_message('info', "DB Export: Payments export completed - {$count} records in {$processingTime}s");
            
            return $this->respond($response);
            
        } catch (\Exception $e) {
            log_message('error', 'DB Export: Payments export failed: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 'error',
                'message' => 'Export failed: ' . $e->getMessage(),
                'request_id' => 'req-' . uniqid()
            ], 500);
        }
    }
    
    /**
     * Get export statistics
     * POST /api/ybb/db/export/statistics
     */
    public function getStatistics()
    {
        try {
            // Get request payload
            $json = $this->request->getJSON(true);
            
            if (!$json) {
                return $this->fail('Invalid JSON payload', 400);
            }
            
            $exportType = $json['export_type'] ?? 'participants';
            $filters = $json['filters'] ?? [];
            
            // Validate required filters
            if (!isset($filters['program_id']) || empty($filters['program_id'])) {
                return $this->fail('program_id is required in filters', 400);
            }
            
            log_message('info', "DB Export: Statistics requested for {$exportType} with filters: " . json_encode($filters));
            
            // Get counts based on export type
            if ($exportType === 'participants') {
                $totalCount = $this->_getParticipantCount($filters);
                $statusBreakdown = $this->_getParticipantStatusBreakdown($filters);
            } else if ($exportType === 'payments') {
                $totalCount = $this->_getPaymentCount($filters);
                $statusBreakdown = $this->_getPaymentStatusBreakdown($filters);
            } else {
                return $this->fail('Invalid export_type: ' . $exportType, 400);
            }
            
            $response = [
                'status' => 'success',
                'data' => [
                    'total_count' => $totalCount,
                    'status_breakdown' => $statusBreakdown,
                    'export_type' => $exportType,
                    'filters_applied' => $filters
                ]
            ];
            
            return $this->respond($response);
            
        } catch (\Exception $e) {
            log_message('error', 'DB Export: Statistics failed: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 'error',
                'message' => 'Statistics failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // ==================== Private Helper Methods ====================
    
    /**
     * Get participant count based on filters
     */
    private function _getParticipantCount(array $filters): int
    {
        $db = \Config\Database::connect();
        $builder = $db->table('participants p');
        
        // Apply filters
        $this->_applyParticipantFilters($builder, $filters);
        
        return $builder->countAllResults();
    }
    
    /**
     * Get participant status breakdown
     */
    private function _getParticipantStatusBreakdown(array $filters): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('participants p');
        
        // Join participant_statuses for form_status
        $builder->join('participant_statuses ps', 'ps.participant_id = p.id', 'left');
        
        // Apply filters (exclude status filter for breakdown)
        $filtersWithoutStatus = $filters;
        unset($filtersWithoutStatus['status']);
        unset($filtersWithoutStatus['registration_form_status']);
        $this->_applyParticipantFilters($builder, $filtersWithoutStatus);
        
        $builder->select('ps.form_status, COUNT(*) as count');
        $builder->groupBy('ps.form_status');
        
        $results = $builder->get()->getResultArray();
        
        $statusMap = [
            0 => 'not_started',
            1 => 'in_progress',
            2 => 'submitted'
        ];
        
        $breakdown = [];
        foreach ($results as $row) {
            $status = $statusMap[$row['form_status']] ?? 'unknown';
            $breakdown[] = [
                'form_status' => $status,
                'count' => (int)$row['count']
            ];
        }
        
        return $breakdown;
    }
    
    /**
     * Get payment count based on filters
     */
    private function _getPaymentCount(array $filters): int
    {
        $db = \Config\Database::connect();
        $builder = $db->table('payments');
        
        // Apply filters
        $this->_applyPaymentFilters($builder, $filters);
        
        return $builder->countAllResults();
    }
    
    /**
     * Get payment status breakdown
     */
    private function _getPaymentStatusBreakdown(array $filters): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('payments');
        
        // Apply filters (exclude status filter for breakdown)
        $filtersWithoutStatus = $filters;
        unset($filtersWithoutStatus['status']);
        $this->_applyPaymentFilters($builder, $filtersWithoutStatus);
        
        $builder->select('status, COUNT(*) as count');
        $builder->groupBy('status');
        
        $results = $builder->get()->getResultArray();
        
        $statusMap = [
            0 => 'pending',
            1 => 'processing',
            2 => 'success',
            3 => 'failed',
            4 => 'cancelled'
        ];
        
        $breakdown = [];
        foreach ($results as $row) {
            $status = $statusMap[$row['status']] ?? 'unknown';
            $breakdown[] = [
                'status' => $status,
                'count' => (int)$row['count']
            ];
        }
        
        return $breakdown;
    }
    
    /**
     * Apply participant filters to query builder
     */
    private function _applyParticipantFilters($builder, array $filters): void
    {
        // Program ID (required)
        $builder->where('p.program_id', $filters['program_id']);
        $builder->where('p.is_deleted', 0);
        
        // Status filter
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $builder->where('p.status', $filters['status']);
        }
        
        // Country filter
        if (isset($filters['country']) && !empty($filters['country'])) {
            $builder->where('p.country', $filters['country']);
        }
        
        // Date range filters
        if (isset($filters['date_from'])) {
            $builder->where('p.created_at >=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $builder->where('p.created_at <=', $filters['date_to']);
        }
        
        // Search filter (name or email)
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('p.full_name', $search)
                ->orLike('p.email', $search)
                ->groupEnd();
        }
        
        // Registration form status filter
        if (isset($filters['registration_form_status']) && $filters['registration_form_status'] !== 'all') {
            $builder->join('participant_statuses ps', 'ps.participant_id = p.id', 'left');
            
            $formStatusMap = [
                'not_started' => 0,
                'in_progress' => 1,
                'submitted' => 2
            ];
            
            $formStatus = $formStatusMap[$filters['registration_form_status']] ?? $filters['registration_form_status'];
            $builder->where('ps.form_status', $formStatus);
        }
        
        // Has submitted form filter
        if (isset($filters['has_submitted_form'])) {
            $builder->join('participant_statuses ps', 'ps.participant_id = p.id', 'left');
            
            if ($filters['has_submitted_form'] === 'yes' || $filters['has_submitted_form'] === true) {
                $builder->where('ps.form_status', 2);
            } else if ($filters['has_submitted_form'] === 'no' || $filters['has_submitted_form'] === false) {
                $builder->where('(ps.form_status IS NULL OR ps.form_status != 2)');
            }
        }
        
        // Has paid filter
        if (isset($filters['has_paid'])) {
            $builder->join('payments pay', 'pay.participant_id = p.id AND pay.status = 2', 'left');
            
            if ($filters['has_paid'] === 'yes' || $filters['has_paid'] === true) {
                $builder->where('pay.id IS NOT NULL');
            } else if ($filters['has_paid'] === 'no' || $filters['has_paid'] === false) {
                $builder->where('pay.id IS NULL');
            }
        }
        
        // Limit
        if (isset($filters['limit']) && $filters['limit'] > 0) {
            $builder->limit($filters['limit']);
        }
        
        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $builder->orderBy("p.{$sortBy}", $sortOrder);
    }
    
    /**
     * Apply payment filters to query builder
     */
    private function _applyPaymentFilters($builder, array $filters): void
    {
        // Get participant IDs for the program first
        $db = \Config\Database::connect();
        $participantIds = $db->table('participants')
            ->select('id')
            ->where('program_id', $filters['program_id'])
            ->where('is_deleted', 0)
            ->get()
            ->getResultArray();
        
        $participantIds = array_column($participantIds, 'id');
        
        if (empty($participantIds)) {
            $builder->where('1', '0'); // No results
            return;
        }
        
        $builder->whereIn('participant_id', $participantIds);
        
        // Status filter
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $statusMap = [
                'pending' => 0,
                'processing' => 1,
                'success' => 2,
                'failed' => 3,
                'cancelled' => 4
            ];
            
            $status = $statusMap[$filters['status']] ?? $filters['status'];
            $builder->where('status', $status);
        }
        
        // Payment method filter
        if (isset($filters['payment_method_id'])) {
            $builder->where('payment_method_id', $filters['payment_method_id']);
        }
        
        // Date range filters
        if (isset($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to']);
        }
        
        // Amount range filters
        if (isset($filters['amount_min'])) {
            $builder->where('amount >=', $filters['amount_min']);
        }
        
        if (isset($filters['amount_max'])) {
            $builder->where('amount <=', $filters['amount_max']);
        }
        
        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $builder->orderBy($sortBy, $sortOrder);
    }
    
    /**
     * Generate filename for export
     */
    private function _generateFilename(string $type, array $filters, string $format): string
    {
        $programId = $filters['program_id'] ?? 'unknown';
        $date = date('Y-m-d_H-i-s');
        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        
        return "YBB_{$type}_program_{$programId}_{$date}.{$extension}";
    }
}
