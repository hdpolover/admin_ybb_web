<?php

namespace App\Controllers;

use App\Services\ExcelExport;

class ExportController extends BaseController
{
    protected $participantModel;
    protected $participantEssayModel;
    protected $paymentModel;
    protected $programModel;
    protected $participantStatusModel;
    protected $request;

    /**
     * Constructor to initialize models
     */
    public function __construct()
    {
        $this->participantModel = new \App\Models\ParticipantModel();
        $this->participantEssayModel = new \App\Models\ParticipantEssayModel();
        $this->paymentModel = new \App\Models\PaymentModel();
        $this->programModel = new \App\Models\ProgramModel();
        $this->participantStatusModel = new \App\Models\ParticipantStatusModel();
        
        // Initialize request
        $this->request = \Config\Services::request();
    }

    /**
     * Show export options page
     */
    public function index()
    {
        // Get programs for dropdown
        $programs = $this->programModel->findAll();
        
        // Get payment categories from program payments
        $programPaymentModel = new \App\Models\ProgramPaymentModel();
        $paymentCategories = $programPaymentModel->distinct()
                                          ->select('category')
                                          ->findAll();
        
        return view('export/index', [
            'programs' => $programs,
            'paymentCategories' => $paymentCategories
        ]);
    }

    /**
     * Export participants to Excel
     */
    public function exportParticipants()
    {
        // Get current program ID from session or request
        $programId = $this->request->getGet('program_id') ?? session('current_program');
        
        if (!$programId) {
            return redirect()->to('exports')->with('error', 'No program selected.');
        }
        
        try {
            // Get participants with essays data
            $participants = $this->getParticipantsWithEssays($programId);
            
            // Generate filename with date and program
            $filename = 'participants_export_' . date('Ymd_His');
            
            // Create the spreadsheet directly with PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Participants');
            
            // Set headers
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Full Name');
            $sheet->getStyle('A1:B1')->getFont()->setBold(true);
            
            // Add data
            $row = 2;
            foreach ($participants as $i => $participant) {
                $sheet->setCellValue('A' . $row, ($i + 1));
                $sheet->setCellValue('B' . $row, $participant->full_name ?? 'No Name');
                $row++;
            }
            
            // Auto-size columns
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            
            // Create writer
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Clear any output buffering
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            header('Expires: 0');
            header('Pragma: public');
            
            // Save directly to output
            $writer->save('php://output');
            exit;
            
        } catch (\Throwable $e) {
            log_message('error', 'Export error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Export payments to Excel
     */
    public function exportPayments()
    {
        // Get current program ID from session or request
        $programId = $this->request->getGet('program_id') ?? session('current_program');
        
        if (!$programId) {
            return redirect()->to('exports')->with('error', 'No program selected.');
        }
        
        // Get payments with participant details
        $payments = $this->paymentModel->getPaymentsWithDetails($programId);
        
        if (empty($payments)) {
            return redirect()->to('exports')->with('error', 'No payment data found for this program.');
        }
        
        // Create Excel exporter
        $excelExport = new ExcelExport();
        
        // Get program info for filename
        $program = $this->programModel->find($programId);
        $programName = $program ? url_title($program->name, '-', true) : 'all-programs';
        
        // Generate filename with date and program
        $filename = 'payments_' . $programName . '_' . date('Ymd_His');
        
        // Export to Excel and download
        return $excelExport->exportPayments($payments, $filename);
    }
    
    /**
     * Export filtered participants to Excel
     */
    public function exportFilteredParticipants()
    {
        // Get filter parameters from request (supporting both POST and GET)
        $programId = $this->request->getPost('program_id') ?? $this->request->getGet('program_id') ?? session('current_program');
        $startDate = $this->request->getPost('start_date') ?? $this->request->getGet('start_date');
        $endDate = $this->request->getPost('end_date') ?? $this->request->getGet('end_date');
        $status = $this->request->getPost('status') ?? $this->request->getGet('status');
        $paymentStatus = $this->request->getPost('payment_status') ?? $this->request->getGet('payment_status');
        
        if (!$programId) {
            return redirect()->to('exports')->with('error', 'No program selected.');
        }
        
        // Build base query
        $builder = $this->participantModel->builder();
        $builder->select('participants.*, users.email')
                ->join('users', 'users.id = participants.user_id', 'left')
                ->where('participants.program_id', $programId)
                ->where('participants.is_deleted', 0);
        
        // Apply additional filters
        if (!empty($startDate)) {
            $builder->where('participants.created_at >=', $startDate . ' 00:00:00');
        }
        
        if (!empty($endDate)) {
            $builder->where('participants.created_at <=', $endDate . ' 23:59:59');
        }
        
        if (!empty($status)) {
            $builder->where('participants.status', $status);
        }
        
        // Apply payment status filter if selected
        if (!empty($paymentStatus)) {
            $builder->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
                   ->where('participant_statuses.payment_status', $paymentStatus);
        }
        
        // Get filtered participants data
        $participantsData = $builder->get()->getResultArray();
        
        if (empty($participantsData)) {
            return redirect()->to('exports')->with('error', 'No participants match the selected filters.');
        }
        
        // Get essays for these participants
        $participants = [];
        foreach ($participantsData as $participant) {
            // Convert to object for consistency
            $p = (object)$participant;
            
            // Add essays
            $p->essays = $this->participantEssayModel->getParticipantEssayByParticipantId($p->id);
            
            // Add payments
            $p->payments = $this->paymentModel->getPaymentsByParticipantId($p->id);
            
            // Add status
            $p->status = $this->participantStatusModel->getParticipantStatusById($p->id);
            
            $participants[] = $p;
        }
        
        // Create Excel exporter
        $excelExport = new ExcelExport();
        
        // Get program info for filename
        $program = $this->programModel->find($programId);
        $programName = $program ? url_title($program->name, '-', true) : 'filtered';
        
        // Generate filename with filter info
        $filename = 'participants_' . $programName . '_filtered_' . date('Ymd_His');
        
        // Export to Excel and download
        return $excelExport->exportParticipants($participants, $filename);
    }
    
    /**
     * Export participants by payment status
     * 
     * This method exports participants based on their payment status (registration, program_fee_1, etc.)
     */
    public function exportParticipantsByPaymentStatus()
    {
        // Get filter parameters from request (supporting both POST and GET)
        $programId = $this->request->getPost('program_id') ?? $this->request->getGet('program_id') ?? session('current_program');
        $paymentCategory = $this->request->getPost('payment_category') ?? $this->request->getGet('payment_category');
        
        if (!$programId) {
            return redirect()->to('exports')->with('error', 'No program selected.');
        }
        
        if (!$paymentCategory) {
            return redirect()->to('exports')->with('error', 'No payment category selected.');
        }
        
        // Get program payment to check if it exists
        $programPaymentModel = new \App\Models\ProgramPaymentModel();
        $programPayment = $programPaymentModel->where('program_id', $programId)
                                             ->where('category', $paymentCategory)
                                             ->first();
        
        if (!$programPayment) {
            return redirect()->to('exports')->with('error', 'Invalid payment category.');
        }
        
        // Find participants who have paid this specific payment
        $builder = $this->participantModel->builder();
        $builder->select('participants.*, users.email')
                ->join('users', 'users.id = participants.user_id', 'left')
                ->join('payments', 'payments.participant_id = participants.id', 'left')
                ->join('program_payments', 'program_payments.id = payments.program_payment_id', 'left')
                ->where('participants.program_id', $programId)
                ->where('participants.is_deleted', 0)
                ->where('program_payments.category', $paymentCategory)
                ->where('payments.status', 2) // Status 2 is success
                ->groupBy('participants.id');
        
        $participantsData = $builder->get()->getResultArray();
        
        if (empty($participantsData)) {
            return redirect()->to('exports')->with('error', 'No participants found with successful payment for ' . $paymentCategory);
        }
        
        // Get essays and additional data for these participants
        $participants = [];
        foreach ($participantsData as $participant) {
            // Convert to object for consistency
            $p = (object)$participant;
            
            // Add essays
            $p->essays = $this->participantEssayModel->getParticipantEssayByParticipantId($p->id);
            
            // Add payments
            $p->payments = $this->paymentModel->getPaymentsByParticipantId($p->id);
            
            // Add status
            $p->status = $this->participantStatusModel->getParticipantStatusById($p->id);
            
            $participants[] = $p;
        }
        
        // Create Excel exporter
        $excelExport = new ExcelExport();
        
        // Get program info for filename
        $program = $this->programModel->find($programId);
        $programName = $program ? url_title($program->name, '-', true) : 'all-programs';
        
        // Generate filename with payment category
        $filename = 'participants_' . $programName . '_' . $paymentCategory . '_' . date('Ymd_His');
        
        // Export to Excel and download
        return $excelExport->exportParticipants($participants, $filename);
    }

    /**
     * Export participants to PDF
     * This allows admin to export participant data in PDF format
     */
    public function exportParticipantsPDF()
    {
        // Track start time for performance monitoring
        $startTime = microtime(true);
        
        // Get current program ID from session or request
        $programId = $this->request->getGet('program_id') ?? session('current_program');
        
        if (!$programId) {
            return redirect()->to('exports')->with('error', 'No program selected.');
        }
        
        try {
            // Log the export attempt
            log_message('info', "PDF Export started for program ID: {$programId}");
            
            // Use the cached method to get participants data
            $participants = $this->getParticipantsWithEssays($programId);
            
            // Track data retrieval time
            $dataRetrievalTime = microtime(true) - $startTime;
            log_message('info', "PDF Export data retrieval completed in " . round($dataRetrievalTime, 2) . " seconds");
            
            if (empty($participants)) {
                return redirect()->to('exports')->with('error', 'No participants found for this program.');
            }
            
            // Get program info for filename
            $program = $this->programModel->find($programId);
            $programName = $program ? url_title($program->name, '-', true) : 'all-programs';
            
            // Generate filename with date and program
            $filename = 'participants_' . $programName . '_' . date('Ymd_His') . '.pdf';
            
            // Generate HTML for PDF
            $html = '<html><head>';
            $html .= '<style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th { background-color: #f2f2f2; text-align: left; padding: 8px; }
                td { border: 1px solid #ddd; padding: 8px; }
                h1 { text-align: center; }
                .page-break { page-break-after: always; }
                .center { text-align: center; }
                .header { margin-bottom: 20px; }
            </style>';
            $html .= '</head><body>';
            $html .= '<div class="header">';
            $html .= '<h1>Participants Export</h1>';
            $html .= '<p class="center">Program: ' . ($program ? $program->name : 'All Programs') . '</p>';
            $html .= '<p class="center">Generated on: ' . date('Y-m-d H:i:s') . '</p>';
            $html .= '</div>';
            
            // Main participants table
            $html .= '<table>';
            $html .= '<tr><th>No</th><th>Full Name</th><th>Email</th><th>Country</th><th>Status</th></tr>';
            
            foreach ($participants as $i => $participant) {
                $status = isset($participant->status) ? $participant->status->status_name : 'Unknown';
                
                $html .= '<tr>';
                $html .= '<td>' . ($i + 1) . '</td>';
                $html .= '<td>' . ($participant->full_name ?? 'No Name') . '</td>';
                $html .= '<td>' . ($participant->email ?? 'No Email') . '</td>';
                $html .= '<td>' . ($participant->country ?? 'Unknown') . '</td>';
                $html .= '<td>' . $status . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</table>';
            
            // Add performance metrics if in development environment
            if (ENVIRONMENT === 'development') {
                $html .= '<div style="font-size: 10px; margin-top: 20px; color: #666;">';
                $html .= '<p>Data retrieval time: ' . round($dataRetrievalTime, 2) . ' seconds</p>';
                $html .= '<p>Total participants: ' . count($participants) . '</p>';
                $html .= '<p>Cache status: ' . ($dataRetrievalTime < 0.1 ? 'Cache hit (fast retrieval)' : 'Cache miss or slow retrieval') . '</p>';
                $html .= '</div>';
            }
            
            // End the HTML content
            $html .= '</body></html>';
            
            // Track HTML generation time
            $htmlGenTime = microtime(true) - $startTime - $dataRetrievalTime;
            log_message('info', "PDF Export HTML generation completed in " . round($htmlGenTime, 2) . " seconds");
            
            // Create PDF using Dompdf
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            
            // Track PDF rendering time
            $pdfRenderTime = microtime(true) - $startTime - $dataRetrievalTime - $htmlGenTime;
            log_message('info', "PDF rendering completed in " . round($pdfRenderTime, 2) . " seconds");
            
            // Track total export time
            $totalTime = microtime(true) - $startTime;
            log_message('info', "Total PDF export time: " . round($totalTime, 2) . " seconds for " . count($participants) . " participants");
            
            // Output the PDF
            $dompdf->stream($filename, ['Attachment' => true]);
            exit();
            
        } catch (\Throwable $e) {
            log_message('error', 'PDF Export error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'PDF Export failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export participants in bulk (for administrators)
     * This allows admin to export large numbers of participants with caching
     */
    public function exportParticipantsBulk()
    {
        // Track start time for performance monitoring
        $startTime = microtime(true);
        
        // Check if user is admin
        if (session()->get('userType') !== 'admin') {
            return redirect()->to('exports')->with('error', 'Only administrators can perform bulk exports.');
        }
        
        // Get parameters
        $programId = $this->request->getGet('program_id') ?? session('current_program');
        $format = $this->request->getGet('format') ?? 'excel'; // Default to Excel
        $chunkSize = (int)$this->request->getGet('chunk_size') ?? 1000;
        
        if (!$programId) {
            return redirect()->to('exports')->with('error', 'No program selected.');
        }
        
        try {
            // Log the export attempt
            log_message('info', "Bulk Export started for program ID: {$programId}, format: {$format}, chunk size: {$chunkSize}");
            
            // Use the cached method to get participants data
            $participants = $this->getParticipantsWithEssays($programId);
            
            // Track data retrieval time
            $dataRetrievalTime = microtime(true) - $startTime;
            log_message('info', "Bulk Export data retrieval completed in " . round($dataRetrievalTime, 2) . " seconds");
            
            if (empty($participants)) {
                return redirect()->to('exports')->with('error', 'No participants found for this program.');
            }
            
            // Get program info for filename
            $program = $this->programModel->find($programId);
            $programName = $program ? url_title($program->name, '-', true) : 'all-programs';
            $filename = 'participants_' . $programName . '_bulk_' . date('Ymd_His');
            
            // Export based on requested format
            if ($format === 'pdf') {
                return $this->generateBulkPDF($participants, $program, $filename, $chunkSize, $startTime, $dataRetrievalTime);
            } else {
                return $this->generateBulkExcel($participants, $filename, $startTime, $dataRetrievalTime);
            }
            
        } catch (\Throwable $e) {
            // Track error time
            $errorTime = microtime(true) - $startTime;
            log_message('error', "Bulk Export error after " . round($errorTime, 2) . " seconds: " . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Bulk Export failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Generate bulk PDF export with enhanced data
     * 
     * @param array $participants Array of participant objects
     * @param object $program Program object
     * @param string $filename Base filename without extension
     * @param int $chunkSize Number of participants per PDF file
     * @param float $startTime Start time for performance tracking
     * @param float $dataRetrievalTime Time taken to retrieve participant data
     * @return Response
     */
    private function generateBulkPDF($participants, $program, $filename, $chunkSize = 1000, $startTime = null, $dataRetrievalTime = null)
    {
        // Generate more comprehensive HTML for PDF
        $html = '<html><head>';
        $html .= '<style>
            body { font-family: Arial, sans-serif; font-size: 10px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th { background-color: #f2f2f2; text-align: left; padding: 6px; font-size: 11px; }
            td { border: 1px solid #ddd; padding: 6px; }
            h1 { text-align: center; font-size: 16px; }
            h2 { font-size: 14px; margin-top: 20px; }
            .page-break { page-break-after: always; }
            .center { text-align: center; }
            .header { margin-bottom: 20px; }
        </style>';
        $html .= '</head><body>';
        $html .= '<div class="header">';
        $html .= '<h1>Bulk Participants Export</h1>';
        $html .= '<p class="center">Program: ' . ($program ? $program->name : 'All Programs') . '</p>';
        $html .= '<p class="center">Generated on: ' . date('Y-m-d H:i:s') . '</p>';
        $html .= '<p class="center">Total Participants: ' . count($participants) . '</p>';
        $html .= '</div>';
        
        // Main participants table with expanded columns
        $html .= '<table>';
        $html .= '<tr>
                    <th>No</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Institution</th>
                    <th>Status</th>
                    <th>Registration Date</th>
                    <th>Payment Status</th>
                </tr>';
        
        foreach ($participants as $i => $participant) {
            $status = isset($participant->status) ? $participant->status->status_name : 'Unknown';
            $paymentStatus = isset($participant->status) ? $participant->status->payment_status : 'Unknown';
            
            $html .= '<tr>';
            $html .= '<td>' . ($i + 1) . '</td>';
            $html .= '<td>' . ($participant->full_name ?? 'No Name') . '</td>';
            $html .= '<td>' . ($participant->email ?? 'No Email') . '</td>';
            $html .= '<td>' . ($participant->country ?? 'Unknown') . '</td>';
            $html .= '<td>' . ($participant->institution ?? 'Unknown') . '</td>';
            $html .= '<td>' . $status . '</td>';
            $html .= '<td>' . date('Y-m-d', strtotime($participant->created_at ?? 'now')) . '</td>';
            $html .= '<td>' . $paymentStatus . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        // End the HTML content
        $html .= '</body></html>';
        
        // Track HTML generation time if we have start time
        if ($startTime) {
            $htmlGenTime = microtime(true) - $startTime - ($dataRetrievalTime ?? 0);
            log_message('info', "PDF HTML generation completed in " . round($htmlGenTime, 2) . " seconds");
        }
        
        // Create PDF using Dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        // Track PDF rendering time if we have start time
        if ($startTime) {
            $pdfRenderTime = microtime(true) - $startTime - ($dataRetrievalTime ?? 0) - ($htmlGenTime ?? 0);
            log_message('info', "PDF rendering completed in " . round($pdfRenderTime, 2) . " seconds");
            
            // Track total time
            $totalTime = microtime(true) - $startTime;
            log_message('info', "Total PDF export time: " . round($totalTime, 2) . 
                      " seconds for " . count($participants) . " participants");
        }
        
        // Output the PDF
        $dompdf->stream($filename . '.pdf', ['Attachment' => true]);
        exit();
    }
    
    /**
     * Generate bulk Excel export with enhanced data
     * 
     * @param array $participants Array of participant objects
     * @param string $filename Base filename without extension
     * @param float $startTime Start time for performance tracking
     * @param float $dataRetrievalTime Time taken to retrieve participant data
     * @return Response
     */
    private function generateBulkExcel($participants, $filename, $startTime = null, $dataRetrievalTime = null)
    {
        // Create the spreadsheet directly with PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Participants');
        
        // Set headers with expanded columns
        $headers = [
            'No', 'Full Name', 'Email', 'Country', 'Institution', 
            'Phone', 'Status', 'Registration Date', 'Payment Status'
        ];
        
        // Set header style
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ]
        ];
        
        // Apply headers
        foreach ($headers as $index => $header) {
            $column = chr(65 + $index); // A, B, C, etc.
            $sheet->setCellValue($column . '1', $header);
        }
        $sheet->getStyle('A1:' . chr(65 + count($headers) - 1) . '1')->applyFromArray($headerStyle);
        
        // Add data
        $row = 2;
        foreach ($participants as $i => $participant) {
            $status = isset($participant->status) ? $participant->status->status_name : 'Unknown';
            $paymentStatus = isset($participant->status) ? $participant->status->payment_status : 'Unknown';
            
            $sheet->setCellValue('A' . $row, ($i + 1));
            $sheet->setCellValue('B' . $row, $participant->full_name ?? 'No Name');
            $sheet->setCellValue('C' . $row, $participant->email ?? 'No Email');
            $sheet->setCellValue('D' . $row, $participant->country ?? 'Unknown');
            $sheet->setCellValue('E' . $row, $participant->institution ?? 'Unknown');
            $sheet->setCellValue('F' . $row, $participant->phone ?? 'Unknown');
            $sheet->setCellValue('G' . $row, $status);
            $sheet->setCellValue('H' . $row, date('Y-m-d', strtotime($participant->created_at ?? 'now')));
            $sheet->setCellValue('I' . $row, $paymentStatus);
            
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', chr(65 + count($headers) - 1)) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Create writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Track Excel generation time
        if ($startTime) {
            $excelGenTime = microtime(true) - $startTime - ($dataRetrievalTime ?? 0);
            log_message('info', "Excel generation completed in " . round($excelGenTime, 2) . " seconds");
            
            // Track total export time
            $totalTime = microtime(true) - $startTime;
            log_message('info', "Total Excel export time: " . round($totalTime, 2) . 
                      " seconds for " . count($participants) . " participants");
        }
        
        // Clear any output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: 0');
        header('Pragma: public');
        
        // Save directly to output
        $writer->save('php://output');
        exit;
    }

    /**
     * Helper method to get participants with their essays
     * 
     * @param int $programId Program ID
     * @return array Participants with essays data
     */
    private function getParticipantsWithEssays($programId)
    {
        // Create a cache key for this export data
        $cacheKey = "participants_export_{$programId}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $participants = $cache->get($cacheKey);
        
        if ($participants !== null) {
            log_message('info', "ExportController::getParticipantsWithEssays - Returning cached participant data for program ID: {$programId}");
            return $participants;
        }
        
        // Cache miss - get from database
        log_message('info', "ExportController::getParticipantsWithEssays - Cache miss, fetching participant data for program ID: {$programId}");
        
        // Get participants from the specified program
        $builder = $this->participantModel->builder();
        $builder->select('participants.*, users.email')
                ->join('users', 'users.id = participants.user_id', 'left')
                ->where('participants.program_id', $programId)
                ->where('participants.is_deleted', 0);
                
        $participantsData = $builder->get()->getResultArray();
        
        if (empty($participantsData)) {
            return [];
        }
        
        // Get essays for each participant
        $participants = [];
        foreach ($participantsData as $participant) {
            // Convert to object for consistency
            $p = (object)$participant;
            
            // Add essays
            $p->essays = $this->participantEssayModel->getParticipantEssayByParticipantId($p->id);
            
            // Add payments
            $p->payments = $this->paymentModel->getPaymentsByParticipantId($p->id);
            
            // Add status
            $p->status = $this->participantStatusModel->getParticipantStatusById($p->id);
            
            $participants[] = $p;
        }
        
        // Cache the result for 30 minutes (1800 seconds)
        // Using a moderate TTL since export data might change, but we want to avoid redundant heavy queries
        $cache->save($cacheKey, $participants, 1800);
        
        return $participants;
    }
    
    /**
     * Example of custom data export to Excel
     */
    public function exportCustomData()
    {
        // Create some example data
        $headers = ['No', 'Name', 'Value', 'Date'];
        $data = [
            [1, 'Item 1', 100, '2025-04-26'],
            [2, 'Item 2', 200, '2025-04-25'],
            [3, 'Item 3', 300, '2025-04-24'],
        ];
        
        // Set column widths
        $columnWidths = [5, 20, 15, 15];
        
        // Create Excel exporter
        $excelExport = new ExcelExport();
        
        // Export to Excel and download
        return $excelExport->exportCustomData(
            $headers,
            $data,
            'custom_export_' . date('Ymd_His'),
            'Custom Data',
            $columnWidths
        );
    }
}
