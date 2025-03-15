<?php

namespace App\Controllers;

class Payments extends BaseController
{
    protected $paymentModel;
    protected $programModel;
    protected $participantModel;

    public function __construct()
    {
        $this->paymentModel = new \App\Models\PaymentModel();
        $this->programModel = new \App\Models\ProgramModel();
        $this->participantModel = new \App\Models\ParticipantModel();
    }

    public function index()
    {
        $programId = session('current_program');
        $program = $this->programModel->find($programId);
        
        // Get payment statistics
        $stats = $this->paymentModel->getPaymentStats($programId);
        $currency_stats = $this->paymentModel->getPaymentStatsByCurrency($programId);
        
        $data = [
            'program' => $program,
            'stats' => $stats,
            'currency_stats' => $currency_stats
        ];
        
        return view('payments/index', $data);
    }
    
    /**
     * Get payments data for DataTables
     */
    public function getData()
    {
        $programId = session('current_program');
        
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
            'created_at', 'participant_name', 'amount', 'payment_method', 
            'transaction_id', 'status'
        ];
        
        $orderColumn = $columns[$order['column']] ?? 'payment_date';
        
        // Get data from database
        $builder = $this->paymentModel->select('
                payments.*, 
                participants.full_name as participant_name, 
                users.email as participant_email,
                participants.program_id
            ')
            ->join('participants', 'participants.id = payments.participant_id')
            ->join('users', 'users.id = participants.user_id')
            ->where('participants.program_id', $programId);
            
        // Apply search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('participants.full_name', $search)
                ->orLike('participants.email', $search)
                ->orLike('payments.transaction_id', $search)
                ->orLike('payments.payment_method', $search)
                ->groupEnd();// Get total count
        }
        
        // Get total count// Order and limit
        $totalRecords = $builder->countAllResults(false);
        
        // Order and limit
        $result = $builder->orderBy($orderColumn, $order['dir'])
            ->limit($length, $start)// Format data for DataTables
            ->get()->getResult();
      
        // Format data for DataTablesetStatusBadge($row->status);
        $data = [];
        foreach ($result as $row) {// Get payment method name
            $statusBadge = $this->getStatusBadge($row->status);
            $paymentMethod = $this->getPaymentMethodName($row->payment_method_id);
            
            $data[] = [
                'id' => $row->id,
                'payment_date' => format_date($row->created_at, 'M j, Y H:i'),
                'participant' => [
                    'name' => $row->participant_name, 
                    'email' => $row->participant_email
                ],
                'amount' => $this->formatCurrency($row->amount, $row->currency ?? 'IDR'),
                'payment_method' => $paymentMethod,
                'transaction_id' => $row->id,
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
            'payment' => $payment,  'payment' => $payment,
            'program' => $this->programModel->find(session('current_program'))  
        ];
           
        return view('payments/view', $data);    return view('payments/view', $data);
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
           
        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';    return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
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
           
        return $statuses[$statusCode] ?? 'Unknown';    return $statuses[$statusCode] ?? 'Unknown';
    }

    /**
     * Get payment method name
     */
    private function getPaymentMethodName($methodId)
    {
        $methods = [
            1 => 'Credit Card',
            2 => 'Bank Transfer',
            3 => 'PayPal',
            4 => 'Cash', 
            5 => 'Other'    
        ];
           
        return $methods[$methodId] ?? 'Unknown';    return $methods[$methodId] ?? 'Unknown';
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
        
        return $symbol . number_format($amount, 2, ',', '.');    return $symbol . number_format($amount, 2, ',', '.');
    }
    
    /**
     * Export payments data
     */
    public function export()
    {
        $programId = $this->request->getPost('program_id');
        if (!$programId) {
            $programId = session('current_program');    $programId = session('current_program');
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
        header('Content-Type: text/csv');header('Content-Type: text/csv');
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
                date('Y-m-d H:i:s', strtotime($row->payment_date)), date('Y-m-d H:i:s', strtotime($row->payment_date)),
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
        return redirect()->to('payments')->with('error', 'PDF export requires additional libraries. Please use Excel or CSV export.');       return redirect()->to('payments')->with('error', 'PDF export requires additional libraries. Please use Excel or CSV export.');
    }

}
