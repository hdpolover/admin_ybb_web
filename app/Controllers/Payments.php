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
    /**
     * Export payments data using YBB DB Export API (Database-Direct Mode)
     * This is the recommended approach per YBB_DB_EXPORT_API_INTEGRATION_GUIDE.md
     */
    public function export()
    {
        try {
            // Always use DB-direct mode for payments export
            log_message('debug', 'Using DB-direct payment export mode (recommended)');
            return $this->exportUsingDbFilters();

        } catch (\Exception $e) {
            log_message('error', 'Failed to export payments: ' . $e->getMessage());

            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)
                    ->setJSON(['success' => false, 'message' => 'Failed to export payments: ' . $e->getMessage()]);
            }
            return redirect()->to('/payments')->with('error', 'Failed to export payments: ' . $e->getMessage());
        }
    }

    /**
     * Get export filters from request for payments
     * Only includes filters documented in YBB_DB_EXPORT_API_INTEGRATION_GUIDE.md
     * Handles both JSON body (from frontend) and direct POST/GET parameters
     */
    private function getPaymentExportFilters()
    {
        // Try to get JSON body first (from AJAX requests)
        $jsonBody = $this->request->getJSON(true); // true = return as array
        $filters = $jsonBody['filters'] ?? [];
        
        // If no JSON body, fall back to traditional POST/GET parameters
        if (empty($filters)) {
            $filters = [
                'status' => $this->request->getGet('status') ?: $this->request->getPost('status'),
                'payment_method_id' => $this->request->getGet('payment_method_id') ?: $this->request->getPost('payment_method_id'),
                'program_payment_id' => $this->request->getGet('program_payment_id') ?: $this->request->getPost('program_payment_id'),
                'date_range' => $this->request->getGet('date_range') ?: $this->request->getPost('date_range'),
                'amount_min' => $this->request->getGet('amount_min') ?: $this->request->getPost('amount_min'),
                'amount_max' => $this->request->getGet('amount_max') ?: $this->request->getPost('amount_max'),
                'limit' => $this->request->getGet('limit') ?: $this->request->getPost('limit'),
                'sort_by' => $this->request->getGet('sort_by') ?: $this->request->getPost('sort_by'),
                'sort_order' => $this->request->getGet('sort_order') ?: $this->request->getPost('sort_order')
            ];
        }
        
        return $filters;
    }

    /**
     * Export payments using DB-direct filters (recommended approach)
     * This method sends filters to the YBB DB Export API instead of fetching data first
     * Following YBB_DB_EXPORT_API_INTEGRATION_GUIDE.md documentation
     * 
     * @param array $additionalFilters Optional additional filters to merge with request filters
     */
    private function exportUsingDbFilters(array $additionalFilters = [])
    {
        try {
            $programId = session('current_program');
            if (!$programId) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No program selected'
                    ]);
                }
                return redirect()->to('/payments')->with('error', 'No program selected');
            }

            log_message('info', 'Starting DB-direct payment export for program: ' . $programId);

            // Get export template and format from request
            // Try JSON body first (from AJAX requests with nested structure)
            $jsonBody = $this->request->getJSON(true);
            $options = $jsonBody['options'] ?? [];
            
            $template = $options['template'] ?? ($this->request->getPost('template') ?: $this->request->getGet('template') ?: 'standard');
            $format = $options['format'] ?? ($this->request->getPost('format') ?: $this->request->getGet('format') ?: 'excel');

            // Build filters for DB-direct export according to YBB DB API documentation
            $filters = [
                'program_id' => (int)$programId,
            ];

            // Get filters from request
            $requestFilters = $this->getPaymentExportFilters();
            
            // Merge with any additional filters passed directly
            $requestFilters = array_merge($requestFilters, $additionalFilters);
            
            // Map all documented filters from YBB_DB_EXPORT_API_INTEGRATION_GUIDE.md
            
            // Status filter (0=pending, 1=processing, 2=success, 3=failed, 4=cancelled)
            // Empty string or "all" returns all payment statuses (no filter applied)
            if (isset($requestFilters['status']) && $requestFilters['status'] !== '' && $requestFilters['status'] !== null && $requestFilters['status'] !== 'all') {
                // Ensure status is numeric (convert if string is provided)
                if (!is_numeric($requestFilters['status'])) {
                    $statusMap = [
                        'pending' => 0,
                        'processing' => 1,
                        'success' => 2,
                        'failed' => 3,
                        'cancelled' => 4
                    ];
                    $filters['status'] = $statusMap[strtolower($requestFilters['status'])] ?? $requestFilters['status'];
                } else {
                    $filters['status'] = (int)$requestFilters['status'];
                }
            }
            
            // Payment method filter
            if (!empty($requestFilters['payment_method_id'])) {
                $filters['payment_method_id'] = (int)$requestFilters['payment_method_id'];
            }
            
            // Program payment filter  
            if (!empty($requestFilters['program_payment_id'])) {
                $filters['program_payment_id'] = (int)$requestFilters['program_payment_id'];
            }
            
            // Date range filter - converts to date_from and date_to
            if (!empty($requestFilters['date_range'])) {
                $dates = explode(' - ', $requestFilters['date_range']);
                if (count($dates) == 2) {
                    $filters['date_from'] = date('Y-m-d', strtotime($dates[0]));
                    $filters['date_to'] = date('Y-m-d', strtotime($dates[1]));
                }
            }
            
            // Amount range filters
            if (!empty($requestFilters['amount_min']) && is_numeric($requestFilters['amount_min'])) {
                $filters['amount_min'] = (float)$requestFilters['amount_min'];
            }
            
            if (!empty($requestFilters['amount_max']) && is_numeric($requestFilters['amount_max'])) {
                $filters['amount_max'] = (float)$requestFilters['amount_max'];
            }
            
            // Limit filter (maximum records to export)
            if (!empty($requestFilters['limit']) && is_numeric($requestFilters['limit'])) {
                $filters['limit'] = (int)$requestFilters['limit'];
            }
            
            // Sort options (defaults to payment_date desc if not provided)
            $filters['sort_by'] = !empty($requestFilters['sort_by']) ? $requestFilters['sort_by'] : 'payment_date';
            $filters['sort_order'] = !empty($requestFilters['sort_order']) ? $requestFilters['sort_order'] : 'desc';

            log_message('debug', 'Payment DB Export filters: ' . json_encode($filters));

            // Prepare export options according to YBB DB API documentation
            $exportOptions = [
                'template' => $template, // Valid options: standard, detailed
                'format' => $format,
                'filename' => $this->generateExportFilename('payments', $programId),
                'sheet_name' => 'YBB_Payments_' . date('Y'),
                'include_related' => true // Include related data (participant info, payment method info, etc.)
            ];

            log_message('debug', 'Payment DB Export options: ' . json_encode($exportOptions));

            // Use DB-direct export method from YbbExport library
            $ybbExport = new \App\Libraries\YbbExport();
            $result = $ybbExport->exportPaymentsFromDB($filters, $exportOptions);

            if ($result['success']) {
                log_message('info', 'Payment DB Export initiated successfully');
                
                $exportData = $result['data'];
                $metadata = $result['metadata'] ?? [];
                
                log_message('info', 'Payment Export data keys: ' . json_encode(array_keys($exportData)));
                log_message('info', 'Payment Export data values: ' . json_encode($exportData));
                
                // Build full download URL if relative path provided
                $downloadUrl = $exportData['download_url'] ?? null;
                if ($downloadUrl && !str_starts_with($downloadUrl, 'http')) {
                    // Get API base URL from environment
                    $apiBaseUrl = getenv('YBB_EXPORT_API_URL') ?: 'http://127.0.0.1:5000';
                    $downloadUrl = rtrim($apiBaseUrl, '/') . $downloadUrl;
                }
                
                $response = [
                    'success' => true,
                    'exportId' => $exportData['export_id'],
                    'export_id' => $exportData['export_id'],
                    'message' => 'Payment export initiated successfully',
                    'recordCount' => $exportData['record_count'] ?? 0,
                    'record_count' => $exportData['record_count'] ?? 0,
                    
                    // File information from API response
                    'fileName' => $exportData['file_name'] ?? null,
                    'file_name' => $exportData['file_name'] ?? null,
                    'fileSize' => $exportData['file_size'] ?? null,
                    'file_size' => $exportData['file_size'] ?? null,
                    'fileSizeMB' => $exportData['file_size_mb'] ?? null,
                    'file_size_mb' => $exportData['file_size_mb'] ?? null,
                    'downloadUrl' => $downloadUrl,
                    'download_url' => $downloadUrl,
                    'expiresAt' => $exportData['expires_at'] ?? null,
                    'expires_at' => $exportData['expires_at'] ?? null,
                    
                    // Metadata
                    'metadata' => $metadata,
                    'data' => $exportData,
                    
                    // Additional info for frontend
                    'exportType' => 'payments',
                    'exportMode' => 'db_direct',
                    'filtersApplied' => $filters
                ];
                
                log_message('info', 'Payment export response prepared: ' . json_encode($response));
                
                return $this->response->setJSON($response);
            } else {
                log_message('error', 'Payment DB Export failed: ' . ($result['message'] ?? 'Unknown error'));
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'] ?? 'Export failed',
                    'error_code' => $result['error_code'] ?? 'EXPORT_FAILED'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Exception in payment export: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to export payments: ' . $e->getMessage(),
                'error_code' => 'EXCEPTION'
            ]);
        }
    }
    
    /**
     * Get payment export statistics (preview before export)
     * Following YBB_DB_EXPORT_API_INTEGRATION_GUIDE.md documentation
     */
    public function export_statistics()
    {
        try {
            $programId = session('current_program');
            if (!$programId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No program selected'
                ]);
            }

            // Build filters same as export
            $filters = ['program_id' => (int)$programId];
            $requestFilters = $this->getPaymentExportFilters();
            
            // Apply same filter mapping as export
            if (isset($requestFilters['status']) && $requestFilters['status'] !== '' && $requestFilters['status'] !== null) {
                $filters['status'] = $requestFilters['status'];
            }
            if (!empty($requestFilters['payment_method_id'])) {
                $filters['payment_method_id'] = (int)$requestFilters['payment_method_id'];
            }
            if (!empty($requestFilters['program_payment_id'])) {
                $filters['program_payment_id'] = (int)$requestFilters['program_payment_id'];
            }
            if (!empty($requestFilters['date_range'])) {
                $dates = explode(' - ', $requestFilters['date_range']);
                if (count($dates) == 2) {
                    $filters['date_from'] = date('Y-m-d', strtotime($dates[0]));
                    $filters['date_to'] = date('Y-m-d', strtotime($dates[1]));
                }
            }
            if (!empty($requestFilters['amount_min']) && is_numeric($requestFilters['amount_min'])) {
                $filters['amount_min'] = (float)$requestFilters['amount_min'];
            }
            if (!empty($requestFilters['amount_max']) && is_numeric($requestFilters['amount_max'])) {
                $filters['amount_max'] = (float)$requestFilters['amount_max'];
            }
            if (!empty($requestFilters['limit']) && is_numeric($requestFilters['limit'])) {
                $filters['limit'] = (int)$requestFilters['limit'];
            }

            // Call YBB Export API statistics endpoint
            $ybbExport = new \App\Libraries\YbbExport();
            $result = $ybbExport->getExportStatistics('payments', $filters);

            if ($result['success']) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => $result['data'],
                    'total_count' => $result['total_count'] ?? 0,
                    'status_breakdown' => $result['status_breakdown'] ?? []
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to get export statistics'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Exception in payment export statistics: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check payment export status
     * Following YBB_DB_EXPORT_API_INTEGRATION_GUIDE.md documentation
     */
    public function export_status($exportId = null)
    {
        try {
            if (!$exportId) {
                $exportId = $this->request->getGet('export_id') ?: $this->request->getPost('export_id');
            }

            if (!$exportId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Export ID is required'
                ]);
            }

            // Call YBB Export API status endpoint
            $ybbExport = new \App\Libraries\YbbExport();
            $result = $ybbExport->getExportStatus($exportId);

            if ($result['success']) {
                // Build full download URL if relative path provided
                $downloadUrl = $result['data']['download_url'] ?? $result['download_url'] ?? null;
                
                if ($downloadUrl && !str_starts_with($downloadUrl, 'http')) {
                    $apiBaseUrl = getenv('YBB_EXPORT_API_URL') ?: 'http://127.0.0.1:5000';
                    $downloadUrl = rtrim($apiBaseUrl, '/') . $downloadUrl;
                }
                
                // Normalize response format
                $response = [
                    'success' => true,
                    'export_id' => $exportId,
                    'status' => $result['data']['status'] ?? $result['status'] ?? 'completed',
                    'download_url' => $downloadUrl,
                    'file_name' => $result['data']['file_name'] ?? $result['file_name'] ?? null,
                    'file_size' => $result['data']['file_size'] ?? $result['file_size'] ?? null,
                    'record_count' => $result['data']['record_count'] ?? $result['record_count'] ?? 0,
                    'created_at' => $result['data']['created_at'] ?? $result['created_at'] ?? null,
                    'expires_at' => $result['data']['expires_at'] ?? $result['expires_at'] ?? null
                ];
                
                return $this->response->setJSON($response);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'] ?? 'Export not found',
                    'error_code' => $result['error_code'] ?? 'EXPORT_NOT_FOUND',
                    'export_id' => $exportId,
                    'suggestion' => $result['suggestion'] ?? 'Please create a new export'
                ])->setStatusCode(404);
            }

        } catch (\Exception $e) {
            log_message('error', 'Exception in payment export status check: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to check export status: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Generate export filename for payments
     */
    private function generateExportFilename($type, $programId)
    {
        return sprintf(
            '%s_program_%d_%s.xlsx',
            $type,
            $programId,
            date('Y-m-d_His')
        );
    }
    
    /**
     * Build user-friendly export message for payments
     */
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
