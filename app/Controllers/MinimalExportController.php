<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use App\Models\ProgramModel;

class MinimalExportController extends BaseController
{
    protected $participantModel;
    protected $programModel;
    
    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
        $this->programModel = new ProgramModel();
    }
    
    /**
     * Show the minimal export test page
     */
    public function index()
    {
        // Get all programs for the dropdown
        $programs = $this->programModel->findAll();
        
        return view('exports/minimal_test', [
            'programs' => $programs
        ]);
    }
    
    /**
     * Export only participant names to Excel
     */
    public function exportNames()
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
            // Get only participant names
            $participants = $this->participantModel
                ->select('participants.id, participants.full_name')
                ->where('program_id', $programId)
                ->findAll();
                
            // Generate filename
            $filename = 'participant_names_' . date('Ymd_His');
            
            // Create Excel file
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Names Only');
            
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
            
            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            
            // Save directly to output
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            log_message('error', 'Export error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ]);
        }
    }
}
