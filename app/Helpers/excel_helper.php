<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if (!function_exists('exportToExcel')) {
    function exportToExcel($data, $filename = 'report.xlsx')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Apply styles for header row
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']], // Green background
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];

        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];

        // Insert data into the spreadsheet
        $rowNum = 1;
        foreach ($data as $rowIndex => $row) {
            $colNum = 1;
            foreach ($row as $cell) {
                $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colNum) . $rowNum;
                $sheet->setCellValue($cellAddress, $cell);
                $colNum++;
            }

            // Apply header style only for the first row
            if ($rowIndex === 0) {
                $sheet->getStyle("A{$rowNum}:" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colNum - 1) . "{$rowNum}")
                    ->applyFromArray($headerStyle);
            } else {
                $sheet->getStyle("A{$rowNum}:" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colNum - 1) . "{$rowNum}")
                    ->applyFromArray($dataStyle);
            }

            $rowNum++;
        }

        // Auto-adjust column width
        foreach (range('A', \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($data[0]))) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Stream the file instead of saving
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
