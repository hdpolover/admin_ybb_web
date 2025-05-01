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
    }    /**
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
     */    public function exportFilteredParticipants()
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
     */    public function exportParticipantsByPaymentStatus()
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
     * Helper method to get participants with their essays
     * 
     * @param int $programId Program ID
     * @return array Participants with essays data
     */
    private function getParticipantsWithEssays($programId)
    {
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
