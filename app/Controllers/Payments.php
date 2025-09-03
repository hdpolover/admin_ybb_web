<?php

namespace App\Controllers;

class Payments extends AdminBaseController
{
    protected $paymentModel;
    protected $programModel;
    protected $participantModel;
    protected $programPaymentModel;
    protected $paymentMethodModel;

    protected $paymentMethods = [];
    protected $programPayments = [];

    // program id
    protected $programId = null;
    protected $program = null;

    public function __construct()
    {
        $this->paymentModel = new \App\Models\PaymentModel();
        $this->programModel = new \App\Models\ProgramModel();
        $this->participantModel = new \App\Models\ParticipantModel();
        $this->programPaymentModel = new \App\Models\ProgramPaymentModel();
        $this->paymentMethodModel = new \App\Models\PaymentMethodModel();

        // set data
        $this->initData();
    }

    // init data
    public function initData()
    {
        $programId = session('current_program');
        $this->programId = $programId;
        $this->program = $this->programModel->find($programId);
        $this->paymentMethods = $this->paymentMethodModel->getByProgramId($programId);
        $this->programPayments = $this->programPaymentModel->getByProgramId($programId);
    }

    public function index()
    {
        $stats = $this->paymentModel->getPaymentStats($this->programId);
        $currency_stats = $this->paymentModel->getPaymentStatsByCurrency($this->programId);

        $data = [
            'program' => $this->program,
            'stats' => $stats,
            'currency_stats' => $currency_stats,
            'paymentMethods' => $this->paymentMethods,
            'programPayments' => $this->programPayments
        ];

        return view('payments/index', $data);
    }

    /**
     * Get payments data for DataTables
     * 
     * Payment list data is never cached to ensure real-time status updates
     */
    public function getData()
    {
        // Set cache-prevention headers for DataTables API
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        // Process DataTables server-side request
        $request = $this->request->getGet();

        $draw = $request['draw'] ?? 1;
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;
        $search = $request['search']['value'] ?? '';
        $order = isset($request['order'][0]) ? [
            'column' => $request['order'][0]['column'],
            'dir' => $request['order'][0]['dir']
        ] : ['column' => 0, 'dir' => 'desc'];        // Column names and their corresponding database fields for sorting
        $columns = [
            'payments.created_at', // payment_date comes from created_at
            'payments.id', // transaction_codes sorting using payment ID
            'participants.full_name', // participant column
            'payments.amount', // payment_details sorted by amount
            'payments.status' // status
        ];

        $orderColumn = $columns[$order['column']] ?? 'payments.created_at';

        // Get data from database
        $builder = $this->paymentModel->select('
                payments.*, 
                participants.full_name as participant_name, 
                users.email as participant_email,
                participants.nationality as participant_nationality,
                participants.program_id
            ')
            ->join('participants', 'participants.id = payments.participant_id')
            ->join('users', 'users.id = participants.user_id')
            ->where('participants.program_id', $this->programId);

        // Apply search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('participants.full_name', $search)
                ->orLike('users.email', $search)
                ->orLike('participants.nationality', $search)
                ->orLike('payments.id', $search)
                ->orLike('payments.transaction_code', $search)
                ->orLike('payments.order_id', $search)
                ->orLike('payments.amount', $search)
                ->orLike('payments.notes', $search)
                ->groupEnd();
        }

        // Apply filters
        $status = $this->request->getGet('status');
        if ($status !== '' && $status !== null) {
            $builder->where('payments.status', $status);
        }

        $programPaymentId = $this->request->getGet('program_payment_id');
        if ($programPaymentId !== '' && $programPaymentId !== null) {
            $builder->where('payments.program_payment_id', $programPaymentId);
        }

        $paymentMethodId = $this->request->getGet('payment_method_id');
        if ($paymentMethodId !== '' && $paymentMethodId !== null) {
            $builder->where('payments.payment_method_id', $paymentMethodId);
        }

        // Get total count
        $totalRecords = $builder->countAllResults(false);

        // Order and limit
        $result = $builder->orderBy($orderColumn, $order['dir'])
            ->limit($length, $start) // Format data for DataTables
            ->get()->getResult();

        // Format data for DataTable
        $data = [];
        foreach ($result as $row) {
            // Get payment method name
            $statusBadge = $this->getStatusBadge($row->status);
            $paymentMethod = $this->getPaymentMethodName($row->payment_method_id);

            // Get program payment name
            $programPayment = $this->getProgramPayment($row->program_payment_id ?? 0);
            $programPaymentName = $programPayment ? $programPayment->name : 'General Payment';

            $data[] = [
                'id' => $row->id,
                'payment_date' => format_date($row->created_at, 'M j, Y H:i'),
                'transaction_codes' => [
                    'payment_id' => $row->id,
                    'transaction_code' => $row->transaction_code ?? 'N/A',
                    'order_id' => $row->order_id ?? 'N/A'
                ],
                'participant' => [
                    'name' => $row->participant_name,
                    'email' => $row->participant_email,
                    'nationality' => $row->participant_nationality ?? 'N/A',
                ],                'payment_details' => [
                    'program_name' => $programPaymentName,
                    'amount' => $this->formatCurrency($row->amount, $row->currency ?? 'IDR'),
                    'amount_raw' => (float) $row->amount, // Add raw amount for sorting
                    'method' => $paymentMethod
                ],
                'status' => $statusBadge,
                'actions' => '<a href="' . base_url('payments/view/' . $row->id) . '" class="btn btn-sm btn-primary">View</a>'
            ];
        }

        // Response for DataTables
        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    /**
     * View payment details
     * 
     * Payment details are never cached to ensure real-time data display
     */
    public function view($id)
    {
        // Set cache-prevention headers for admin interface
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        // Always fetch fresh payment data - no caching
        $payment = $this->paymentModel->getPaymentById($id);

        // Check if payment exists and belongs to the current program
        if (!$payment || $payment->program_id != session('current_program')) {
            return redirect()->to('payments')->with('error', 'Payment not found');
        }

        // Log admin access to payment details
        log_message('info', "Admin payment view accessed - Payment ID: {$id}, Status: {$payment->status}, Admin: " . session('user_id'));

        $data = [
            'payment' => $payment,
            'program' => $this->programModel->find(session('current_program'))
        ];

        return view('payments/view', $data);
    }

    /**
     * Get HTML for status badge
     */
    private function getStatusBadge($status)
    {
        $badges = [
            0 => '<span class="badge bg-secondary">Created</span>',
            1 => '<span class="badge bg-warning">Pending</span>',
            2 => '<span class="badge bg-success">Success</span>',
            3 => '<span class="badge bg-danger">Cancelled</span>',
            4 => '<span class="badge bg-danger">Rejected</span>'
        ];

        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get program payment by ID
     */
    private function getProgramPayment($id)
    {
        // Loop through program payments to find the payment name
        foreach ($this->programPayments as $payment) {
            if ($payment->id == $id) {
                return $payment;
            }
        }

        // If not found, return null
        return null;
    }

    /**
     * Get status name from code
     */
    private function getStatusName($statusCode)
    {
        $statuses = [
            0 => 'Created',
            1 => 'Pending',
            2 => 'Success',
            3 => 'Cancelled',
            4 => 'Rejected'
        ];

        return $statuses[$statusCode] ?? 'Unknown';
    }

    /**
     * Get payment method name
     */
    private function getPaymentMethodName($methodId)
    {
        // loop thorugh payment methods to find the method name
        foreach ($this->paymentMethods as $method) {
            if ($method->id == $methodId) {
                return $method->name;
            }
        }

        // If not found, return 'Unknown'
        return 'Unknown';
    }

    /**
     * Format currency
     */
    private function formatCurrency($amount, $currency)
    {
        $currencies = [
            'IDR' => 'Rp',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥'
        ];

        $symbol = $currencies[$currency] ?? '';

        return $symbol . number_format($amount, 2, ',', '.');
        return $symbol . number_format($amount, 2, ',', '.');
    }

    /**
     * Export payments data using enhanced YBB Export system with performance tracking
     */
    public function export()
    {
        try {
            $startTime = microtime(true);
            
            $programId = $this->request->getPost('program_id');
            if (!$programId) {
                $programId = session('current_program');
            }

            if (!$programId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No program selected'
                ]);
            }

            // Get export parameters and filters
            $dateRange = $this->request->getPost('date_range');
            $status = $this->request->getPost('status');
            $trackPerformance = $this->request->getPost('track_performance') === 'true';

            // Build optimized query using database builder
            $dataStartTime = microtime(true);
            $db = \Config\Database::connect();
            $builder = $db->table('payments')
                ->select('
                    payments.id,
                    payments.amount,
                    payments.currency,
                    payments.payment_method_id,
                    payments.program_payment_id,
                    payments.created_at,
                    payments.updated_at,
                    payments.status,
                    payments.transaction_code,
                    payments.order_id,
                    payments.transaction_id,
                    payments.notes,
                    participants.full_name as participant_name,
                    users.email as participant_email,
                    participants.nationality as participant_nationality,
                    program_payments.name as payment_type_name
                ')
                ->join('participants', 'participants.id = payments.participant_id')
                ->join('users', 'users.id = participants.user_id')
                ->join('program_payments', 'program_payments.id = payments.program_payment_id', 'left')
                ->where('participants.program_id', $programId)
                ->where('payments.is_deleted', 0);

            // Apply filters if provided
            if ($dateRange) {
                $dates = explode(' - ', $dateRange);
                if (count($dates) == 2) {
                    $startDate = date('Y-m-d', strtotime($dates[0]));
                    $endDate = date('Y-m-d', strtotime($dates[1]));
                    $builder->where('DATE(payments.created_at) >=', $startDate)
                        ->where('DATE(payments.created_at) <=', $endDate);
                }
            }

            if ($status !== '' && $status !== null) {
                $builder->where('payments.status', $status);
            }

            // Get data with performance monitoring
            $payments = $builder->orderBy('payments.created_at', 'DESC')->get()->getResultArray();
            $dataFetchTime = microtime(true) - $dataStartTime;

            if (empty($payments)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No payments found for export'
                ]);
            }

            $recordCount = count($payments);
            
            // Determine optimal export strategy for payments (8K threshold)
            $exportStrategy = $recordCount > 8000 ? 'chunked' : 'single_file';
            
            log_message('info', "Payments export initiated: {$recordCount} records, strategy: {$exportStrategy}, performance tracking: " . ($trackPerformance ? 'enabled' : 'disabled'));

            // Enhanced export options with performance tracking
            $options = [
                'template' => 'payments',
                'format' => 'excel',
                'program_id' => $programId,
                'export_strategy' => $exportStrategy,
                'chunk_size' => $exportStrategy === 'chunked' ? 8000 : null,
                'track_performance' => $trackPerformance,
                'filters' => [
                    'date_range' => $dateRange,
                    'status' => $status
                ],
                'filename' => 'payments_export_prog' . $programId . '_' . date('Y-m-d_H-i-s')
            ];

            // Add performance context
            if ($trackPerformance) {
                $options['performance_context'] = [
                    'data_fetch_time' => $dataFetchTime,
                    'record_count' => $recordCount,
                    'memory_before_export' => memory_get_usage(true),
                    'export_start_time' => microtime(true)
                ];
            }

            // Create export using enhanced YBB Export API
            $exportStartTime = microtime(true);
            $ybbExport = new \App\Libraries\YbbExport();
            $result = $ybbExport->exportPayments($payments, $options);
            $exportTime = microtime(true) - $exportStartTime;

            if ($result['success']) {
                $totalTime = microtime(true) - $startTime;
                
                // Enhanced logging with performance data
                $logData = [
                    'export_initiated' => true,
                    'record_count' => $recordCount,
                    'export_strategy' => $exportStrategy,
                    'processing_time' => $totalTime,
                    'data_fetch_time' => $dataFetchTime,
                    'export_time' => $exportTime
                ];
                
                if ($trackPerformance && isset($result['performanceStats'])) {
                    $logData['performance_stats'] = $result['performanceStats'];
                }
                
                log_message('info', 'Payments export completed: ' . json_encode($logData));
                
                // Build comprehensive response
                $response = [
                    'success' => true,
                    'exportId' => $result['exportId'],
                    'message' => $this->_buildPaymentExportMessage($result, $recordCount),
                    'recordCount' => $recordCount,
                    'exportStrategy' => $exportStrategy,
                    'fileType' => $result['fileType'] ?? 'single',
                    'totalFiles' => $result['totalFiles'] ?? 1,
                    'processingTime' => number_format($totalTime, 2) . 's',
                    'status' => 'completed'
                ];
                
                // Add performance stats if available
                if (isset($result['performanceStats'])) {
                    $response['performanceStats'] = $result['performanceStats'];
                }
                
                // Add chunking info if applicable
                if ($exportStrategy === 'chunked') {
                    $response['chunkCount'] = $result['chunkCount'] ?? null;
                    $response['compressedSize'] = $result['compressedSize'] ?? null;
                    $response['compressionRatio'] = $result['compressionRatio'] ?? null;
                }
                
                return $this->response->setJSON($response);
                
            } else {
                log_message('error', "Payments export failed: {$result['message']} (Records: {$recordCount}, Strategy: {$exportStrategy})");
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'],
                    'recordCount' => $recordCount,
                    'exportStrategy' => $exportStrategy
                ]);
            }

        } catch (\Exception $e) {
            $processingTime = microtime(true) - ($startTime ?? microtime(true));
            
            log_message('error', "Exception in payments export: {$e->getMessage()} (Processing time: {$processingTime}s)");
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to export payments: ' . $e->getMessage(),
                'processingTime' => number_format($processingTime, 2) . 's'
            ]);
        }
    }
    
    /**
     * Build user-friendly export message for payments
     */
    private function _buildPaymentExportMessage(array $result, int $recordCount): string
    {
        $isChunked = isset($result['fileType']) && $result['fileType'] === 'chunked';
        
        if ($isChunked) {
            $fileCount = $result['totalFiles'] ?? 1;
            $message = "Payment export completed: " . number_format($recordCount) . " records exported in $fileCount optimized files";
            
            if (isset($result['compressionRatio'])) {
                $message .= " (Compressed to {$result['compressionRatio']})";
            }
        } else {
            $message = "Payment export completed successfully with " . number_format($recordCount) . " payment records";
            
            if (isset($result['performanceStats']['processingTime'])) {
                $time = $result['performanceStats']['processingTime']['formatted'] ?? '';
                if ($time) {
                    $message .= " in $time";
                }
            }
        }
        
        return $message;
    }

    /**
     * Show the payment form
     */
    public function makePayment()
    {
        // Get participants from the current program
        $programId = session('current_program');

        $participantModel = new \App\Models\ParticipantModel();
        $participants = $participantModel->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->findAll();

        $data = [
            'participants' => $participants,
            'program' => $this->programModel->find($programId)
        ];

        return view('payments/make-payment', $data);
    }

    /**
     * Update payment status
     * 
     * This method includes comprehensive cache invalidation to ensure
     * all payment-related data is refreshed after status changes
     * 
     * @param int $id Payment ID
     */
    public function updateStatus($id)
    {
        try {
            // Validate required fields
            $rules = [
                'status' => 'required|integer|in_list[0,1,2,3,4]',
                'notes' => 'permit_empty|string'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->with('error', 'Invalid input: ' . implode(', ', $this->validator->getErrors()));
            }

            // Get payment data BEFORE update for cache invalidation
            $payment = $this->paymentModel->getPaymentById($id);

            // Check if payment exists and belongs to the current program
            if (!$payment || $payment->program_id != session('current_program')) {
                return redirect()->to('payments')->with('error', 'Payment not found');
            }

            $oldStatus = $payment->status; // Store old status for comparison
            $status = $this->request->getPost('status');
            $notes = $this->request->getPost('notes');

            // Get status name for the message
            $statusNames = [
                0 => 'Created',
                1 => 'Pending',
                2 => 'Success',
                3 => 'Cancelled',
                4 => 'Rejected'
            ];

            // Get status name for the message  
            $statusName = $statusNames[$status] ?? 'Unknown';

            // Create notes for the update
            $statusUpdateNote = "Status updated to '{$statusName}'";
            $rejectionReason = null; // Initialize rejection reason

            // if rejected, add rejection reason if provided
            if ($status == 4) {
                $rejectionReason = $notes ?? '';
                if (!empty($notes)) {
                    $statusUpdateNote .= ". Rejection reason: {$notes}";
                }
            } else {
                if (!empty($notes)) {
                    $statusUpdateNote .= ". Additional notes: {$notes}";
                }
            }

            // Update payment status using model method which includes cache invalidation
            $updated = $this->paymentModel->updatePaymentStatus($id, $status, $statusUpdateNote, $rejectionReason);

            if ($updated) {
                // Additional cache invalidation for admin interfaces and API endpoints
                $this->invalidatePaymentCaches($payment, $oldStatus, $status);

                // If payment was successful, trigger additional actions
                if ($status == 2) {
                    $this->handleSuccessfulPaymentActions($payment);
                }

                log_message('info', "Admin payment status update - Payment ID: {$id}, Old Status: {$oldStatus}, New Status: {$status}, Admin: " . session('user_id'));

                return redirect()->to('payments/view/' . $id)
                    ->with('success', "Payment status updated to '{$statusName}' successfully");
            } else {
                return redirect()->back()
                    ->with('error', 'Failed to update payment status');
            }
            
        } catch (\Exception $e) {
            // Log the error for debugging
            log_message('error', 'Payment status update failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            
            // Return user-friendly error message
            return redirect()->back()
                ->with('error', 'An error occurred while updating the payment status. Please try again.');
        }
    }

    /**
     * Invalidate all payment-related caches after status update
     * 
     * @param object $payment Payment data
     * @param int $oldStatus Previous status
     * @param int $newStatus New status
     */
    private function invalidatePaymentCaches($payment, $oldStatus, $newStatus)
    {
        try {
            $cache = \Config\Services::cache();
            
            // Clear payment-specific caches
            $keysToDelete = [
                // Individual payment cache
                "payment_id_{$payment->id}",
                "payment_detail_{$payment->id}",
                
                // Participant payment caches
                "participant_payments_{$payment->participant_id}",
                "payments_participant_{$payment->participant_id}",
                
                // Program payment caches  
                "program_payment_data_{$payment->program_payment_id}_{$payment->participant_id}",
                "payments_program_{$payment->program_payment_id}_participant_{$payment->participant_id}",
                
                // Program statistics caches
                "payment_stats_{$payment->program_id}",
                "payment_stats_currency_{$payment->program_id}",
                "program_payments_{$payment->program_id}",
                
                // Status-specific caches
                "payments_status_{$oldStatus}_program_{$payment->program_id}",
                "payments_status_{$newStatus}_program_{$payment->program_id}",
                
                // Admin interface caches
                "admin_payment_list_{$payment->program_id}",
                "admin_payment_filters_{$payment->program_id}",
                "datatable_payments_{$payment->program_id}",
                
                // Export caches
                "export_payments_{$payment->program_id}",
                "export_request_payments_{$payment->program_id}",
                
                // API response caches
                "api_payments_config",
                "api_payments_participant_{$payment->participant_id}",
                "api_payments_program_{$payment->program_payment_id}",
            ];

            $deletedCount = 0;
            foreach ($keysToDelete as $key) {
                if ($cache->delete($key)) {
                    $deletedCount++;
                }
                
                // Also try with sanitized key (replace special characters with underscores)
                $sanitizedKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
                if ($cache->delete($sanitizedKey)) {
                    $deletedCount++;
                }
            }

            // Clear additional cache keys that might exist
            $additionalKeys = [
                // General payment caches by participant
                "payments_{$payment->participant_id}",
                "participant_{$payment->participant_id}_payments",
                
                // Payment method caches
                "payment_method_{$payment->payment_method_id}",
                
                // Program-wide caches
                "program_{$payment->program_id}_payment_stats",
                "program_{$payment->program_id}_payments",
            ];

            foreach ($additionalKeys as $key) {
                if ($cache->delete($key)) {
                    $deletedCount++;
                }
            }

            log_message('info', "Payment cache invalidation complete - Payment ID: {$payment->id}, Status: {$oldStatus} → {$newStatus}, Keys deleted: {$deletedCount}");
            
        } catch (\Exception $e) {
            log_message('error', 'Error invalidating payment caches: ' . $e->getMessage());
            // Don't throw exception - cache invalidation failure shouldn't break the payment update
        }
    }

    /**
     * Handle additional actions after a payment is marked as successful
     * 
     * @param object $payment Payment data
     */
    private function handleSuccessfulPaymentActions($payment)
    {
        // You can add code here to:
        // 1. Update participant payment status if needed
        // 2. Send confirmation email to participant
        // 3. Generate receipt or invoice
        // 4. Update any other related records

        // For now, we'll just log the action
        log_message('info', "Payment {$payment->id} was manually marked as successful");
    }
}
