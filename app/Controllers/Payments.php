<?php

namespace App\Controllers;

class Payments extends BaseController
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
     */
    public function getData()
    {
        // Process DataTables server-side request
        $request = $this->request->getGet();

        $draw = $request['draw'] ?? 1;
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;
        $search = $request['search']['value'] ?? '';
        $order = isset($request['order'][0]) ? [
            'column' => $request['order'][0]['column'],
            'dir' => $request['order'][0]['dir']
        ] : ['column' => 0, 'dir' => 'desc'];

        // Column names', 
        $columns = [
            'payment_date',
            'transaction_codes',
            'participant',
            'payment_details',
            'status'
        ];

        $orderColumn = $columns[$order['column']] ?? 'payment_date';

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
                ],
                'payment_details' => [
                    'program_name' => $programPaymentName,
                    'amount' => $this->formatCurrency($row->amount, $row->currency ?? 'IDR'),
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
     */
    public function view($id)
    {
        $payment = $this->paymentModel->getPaymentById($id);

        // Check if payment exists and belongs to the current program
        if (!$payment || $payment->program_id != session('current_program')) {
            return redirect()->to('payments')->with('error', 'Payment not found');
        }

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
     * Export payments data
     */
    public function export()
    {
        $programId = $this->request->getPost('program_id');
        if (!$programId) {
            $programId = session('current_program');
            $programId = session('current_program');
        }

        // Get export parameters
        $exportType = $this->request->getPost('export_type') ?? 'excel';
        $dateRange = $this->request->getPost('date_range');
        $status = $this->request->getPost('status');

        // Build query
        $db = \Config\Database::connect();
        $builder = $db->table('payments')
            ->select('
                payments.id,
                payments.id,
                payments.amount,
                payments.payment_method_id,
                payments.created_at,
                payments.status,
                participants.full_name as participant_name,
                users.email as participant_email,

            ')
            ->join('participants', 'participants.id = payments.participant_id')
            ->join('users', 'users.id = participants.user_id')
            ->where('participants.program_id', $programId);

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

        if ($status !== '') {
            $builder->where('payments.status', $status);
        }

        // Get data// Get data
        $payments = $builder->orderBy('payments.created_at', 'DESC')->get()->getResult();

        // Set the headers for download// Set the headers for download
        $filename = 'payments_export_' . date('Ymd_His');

        // Process based on export typed on export type
        switch ($exportType) {
            case 'csv':
                return $this->exportCSV($payments, $filename);
            case 'pdf':
                return $this->exportPDF($payments, $filename);
            default:
                return $this->exportExcel($payments, $filename);
        }
    }

    /**
     * Export data to CSV
     */
    private function exportCSV($data, $filename)
    {
        // Set headers
        header('Content-Type: text/csv');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        // Open output stream// Open output stream
        $output = fopen('php://output', 'w');

        // Add headers// Add headers
        fputcsv($output, ['Transaction ID', 'Participant', 'Email', 'Amount', 'Payment Method', 'Date', 'Status']);

        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, [
                $row->transaction_id,
                $row->participant_name,
                $row->participant_email,
                $row->amount,
                $row->payment_method,
                date('Y-m-d H:i:s', strtotime($row->payment_date)),
                $row->status
            ]);
        }

        // Close and returnose and return
        fclose($output);
        exit;
    }

    /**
     * Placeholder method for Excel exportPlaceholder method for Excel export
     * You'll need a library like PhpSpreadsheet for proper Excel exportor proper Excel export
     */
    private function exportExcel($data, $filename)
    {
        // This is a simplified example that outputs CSV but with Excel extension// This is a simplified example that outputs CSV but with Excel extension
        // For a complete implementation, you would use a library like PhpSpreadsheetete implementation, you would use a library like PhpSpreadsheet

        // Set headers
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');

        // Open output stream// Open output stream
        $output = fopen('php://output', 'w');

        // Add headers// Add headers
        fputcsv($output, ['Transaction ID', 'Participant', 'Email', 'Amount', 'Payment Method', 'Date', 'Status']);

        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, [
                $row->transaction_id,
                $row->participant_name,
                $row->participant_email,
                $row->amount,
                $row->payment_method,
                date('Y-m-d H:i:s', strtotime($row->payment_date)),
                date('Y-m-d H:i:s', strtotime($row->payment_date)),
                $row->status
            ]);
        }

        // Close and returnose and return
        fclose($output);
        exit;
    }

    /**
     * Placeholder method for PDF exportPlaceholder method for PDF export
     * You'll need a library like TCPDF or MPDF for proper PDF exportfor proper PDF export
     */
    function exportPDF($data, $filename)
    {
        // This example simply redirects with a message   // This example simply redirects with a message
        return redirect()->to('payments')->with('error', 'PDF export requires additional libraries. Please use Excel or CSV export.');
        return redirect()->to('payments')->with('error', 'PDF export requires additional libraries. Please use Excel or CSV export.');
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
     * @param int $id Payment ID
     */
    public function updateStatus($id)
    {
        // Validate required fields
        $rules = [
            'status' => 'required|integer|in_list[0,1,2,3,4]',
            'notes' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->with('error', 'Invalid input: ' . implode(', ', $this->validator->getErrors()));
        }

        // Get payment data
        $payment = $this->paymentModel->getPaymentById($id);

        // Check if payment exists and belongs to the current program
        if (!$payment || $payment->program_id != session('current_program')) {
            return redirect()->to('payments')->with('error', 'Payment not found');
        }

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
        } else {
            $statusUpdateNote .= ". Additional notes: {$notes}";
        }

        // Update payment status using model method which handles notes concatenation
        $updated = $this->paymentModel->updatePaymentStatus($id, $status, $statusUpdateNote, $rejectionReason);

        if ($updated) {
            // If payment was successful, you might want to trigger some additional actions
            if ($status == 2) {
                // E.g., Update participant status, send confirmation email, etc.
                $this->handleSuccessfulPaymentActions($payment);
            }

            return redirect()->to('payments/view/' . $id)
                ->with('success', "Payment status updated to '{$statusName}' successfully");
        } else {
            return redirect()->back()
                ->with('error', 'Failed to update payment status');
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
