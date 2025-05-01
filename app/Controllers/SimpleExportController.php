<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use App\Models\ProgramModel;
use App\Services\ExcelExport;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SimpleExportController extends BaseController
{
    protected $participantModel;
    protected $programModel;
    
    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
        $this->programModel = new ProgramModel();
    }
    
    /**
     * Show the simple export test page
     */
    public function index()
    {
        // Get all programs for the dropdown
        $programs = $this->programModel->findAll();
        
        return view('exports/simple_test', [
            'programs' => $programs
        ]);
    }
    
    /**
     * Simple export to Base64
     * This approach avoids the issues with direct Excel downloads
     */
    public function exportSimple()
    {
        // Get program ID
        $programId = $this->request->getGet('program_id') ?? session('current_program');
        
        if (!$programId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No program selected'
            ]);
        }
        
        try {
            // Get participants (simplified query)
            $participants = $this->participantModel
                ->select('participants.id, participants.full_name')
                ->where('program_id', $programId)
                ->findAll();
                
            // Create the Excel file in memory
            $spreadsheet = new Spreadsheet();
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
            
            // Generate Excel file to memory
            $writer = new Xlsx($spreadsheet);
            
            // Save to memory
            ob_start();
            $writer->save('php://output');
            $excelData = ob_get_contents();
            ob_end_clean();
            
            // Get program name for filename
            $program = $this->programModel->find($programId);
            $programName = $program ? url_title($program->name, '-', true) : 'program';
            $filename = 'participants_' . $programName . '_' . date('Ymd_His') . '.xlsx';
            
            // Return base64 encoded Excel data and filename
            return $this->response->setJSON([
                'success' => true,
                'filename' => $filename,
                'data' => base64_encode($excelData),
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Excel export error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ]);
        }
    }
}
