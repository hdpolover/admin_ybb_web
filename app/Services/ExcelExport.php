<?php

namespace App\Services;

/**
 * ExcelExport Service
 *
 * A service for exporting data to Excel files
 */
class ExcelExport
{
    /**
     * Clean HTML and Quill editor tags from text
     * 
     * @param string $text Text that may contain HTML/Quill tags
     * @return string Cleaned text
     */
    private function cleanHtmlTags($text)
    {
        if (empty($text)) {
            return '';
        }

        // Handle non-string inputs by converting them to string safely
        if (!is_string($text)) {
            log_message('debug', 'Converting non-string value to string for cleaning: ' . gettype($text));
            // Safely convert to string or return empty if not convertible
            try {
                if (is_object($text) || is_array($text)) {
                    $text = json_encode($text);
                } else {
                    $text = (string)$text;
                }
            } catch (\Exception $e) {
                log_message('error', 'Failed to convert value to string: ' . $e->getMessage());
                return '';
            }
        }

        // Log the start of cleaning for potentially problematic content
        if (strlen($text) > 1000) {
            log_message('debug', 'Cleaning large text content (' . strlen($text) . ' chars)');
        }

        // First check if the content is JSON (for Quill editor or other rich text formats)
        $firstChar = substr(trim($text), 0, 1);
        if ($firstChar === '{' || $firstChar === '[') {
            try {
                log_message('debug', 'Detected possible JSON content, attempting to extract plain text');
                $jsonObj = json_decode($text, true);

                // If valid JSON, try to extract meaningful content
                if (json_last_error() === JSON_ERROR_NONE) {
                    log_message('debug', 'Successfully decoded JSON content');

                    // Handle Quill format: {"ops":[{"insert":"text"}]}
                    if (isset($jsonObj['ops']) && is_array($jsonObj['ops'])) {
                        $plainText = '';
                        foreach ($jsonObj['ops'] as $op) {
                            if (isset($op['insert'])) {
                                $plainText .= $op['insert'];
                            }
                        }
                        $text = $plainText;
                        log_message('debug', 'Extracted ' . strlen($plainText) . ' chars from Quill JSON');
                    }
                    // Handle other common formats or just use the raw JSON string
                    else {
                        $text = json_encode($jsonObj, JSON_UNESCAPED_UNICODE);
                        log_message('debug', 'Using JSON string representation for non-Quill content');
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error processing JSON content: ' . $e->getMessage());
                // Continue with original text if processing fails
            }
        }

        // Remove any HTML comments
        $text = preg_replace('/<!--.*?-->/', '', $text);

        // Strip HTML tags
        $text = strip_tags($text);

        // Convert HTML entities to their corresponding characters
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Replace common problematic characters
        $text = str_replace(["\r", "\n", "\t", "\0", "\x0B"], ' ', $text);

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove any non-printable characters
        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);

        // Trim the result
        $cleaned = trim($text);

        if (strlen($text) > 1000 && strlen($cleaned) < strlen($text)) {
            log_message('debug', 'Cleaned text reduced from ' . strlen($text) . ' to ' . strlen($cleaned) . ' chars');
        }

        return $cleaned;
    }

    /**
     * Export participants data to Excel
     *
     * @param array $participants Array of participants data
     * @param string $filename Filename (without extension)
     * @param bool $download Whether to download the file or save it locally
     * @return string|bool File path if saved, true if downloaded, false on failure
     */    /**
     * Validate and clean participant data to prevent Excel export issues
     * 
     * @param array &$participants Array of participant objects
     * @return void
     */
    private function validateAndCleanParticipantData(&$participants)
    {
        log_message('debug', 'Starting data validation and cleaning for ' . count($participants) . ' participants');
        
        $problematicFields = [
            'bio', 'notes', 'achievements', 'experience', 'address', 'education', 
            'awards', 'skills', 'organization', 'description', 'about', 'summary'
        ];
        
        $count = 0;
        foreach ($participants as &$participant) {
            $participantId = $participant->id ?? 'unknown';
            
            // Log every 50 participants to avoid overwhelming logs
            $count++;
            $logThisOne = ($count === 1 || $count % 50 === 0 || $count === count($participants));
            
            if ($logThisOne) {
                log_message('debug', "Validating participant {$count} of " . count($participants) . ", ID: {$participantId}");
            }
            
            // Check if the participant is a valid object
            if (!is_object($participant)) {
                log_message('error', 'Invalid participant data type: ' . gettype($participant) . ' at index ' . ($count-1));
                continue;
            }
            
            // Clean commonly problematic fields
            foreach ($problematicFields as $field) {
                if (isset($participant->$field)) {
                    $original = $participant->$field;
                    $participant->$field = $this->cleanHtmlTags($original);
                    
                    // Log significant changes
                    if (is_string($original) && is_string($participant->$field)) {
                        $originalLen = strlen($original);
                        $cleanedLen = strlen($participant->$field);
                        if ($originalLen !== $cleanedLen && abs($originalLen - $cleanedLen) > 10) {
                            log_message('debug', "Cleaned field '{$field}' for participant {$participantId}: {$originalLen} -> {$cleanedLen} chars");
                        }
                    }
                }
            }
            
            // Check for any large property values that might cause issues
            foreach ($participant as $key => $value) {
                if (is_string($value) && strlen($value) > 10000) {
                    log_message('warning', "Participant {$participantId} has very large '{$key}' field: " . strlen($value) . " chars");
                    // Truncate extremely large fields
                    $participant->$key = substr($value, 0, 10000) . "... [truncated]";
                    log_message('debug', "Truncated '{$key}' field to 10000 chars");
                }
                else if (is_resource($value)) {
                    // Handle resource types that cannot be serialized
                    $type = get_resource_type($value);
                    log_message('warning', "Participant {$participantId} has resource property '{$key}' of type '{$type}'");
                    $participant->$key = "[Resource: {$type}]";
                }
                else if (is_array($value) || is_object($value)) {
                    // Handle complex property types to prevent serialization issues
                    if (is_array($value)) {
                        $type = 'array with ' . count($value) . ' elements';
                        
                        // For arrays, check if they contain objects that might cause issues
                        if (!empty($value)) {
                            $sample = reset($value);
                            if (is_object($sample)) {
                                $objClass = get_class($sample);
                                log_message('debug', "Participant {$participantId} '{$key}' contains {$objClass} objects");
                                
                                // If this is an essay array or other complex data, simplify it
                                if ($key === 'essays' || count($value) > 10) {
                                    // Replace with a simple indication to avoid serialization issues
                                    $participant->$key = "[Contains " . count($value) . " items]";
                                    log_message('debug', "Simplified complex array property '{$key}' to prevent export issues");
                                }
                            }
                        }
                    } 
                    else if (is_object($value)) {
                        $type = 'object of class ' . get_class($value);
                        
                        // For non-stdClass objects, convert to array or replace with string representation
                        if (get_class($value) !== 'stdClass') {
                            if (method_exists($value, 'toArray')) {
                                $participant->$key = $value->toArray();
                                log_message('debug', "Converted {$type} to array for property '{$key}'");
                            } else {
                                // Replace with simple indication for safety
                                $participant->$key = "[Object: " . get_class($value) . "]";
                                log_message('debug', "Replaced {$type} with string representation for '{$key}'");
                            }
                        }
                    }
                }
                else if (!is_scalar($value) && !is_null($value)) {
                    // Catch any other non-serializable types
                    $type = gettype($value);
                    log_message('warning', "Participant {$participantId} has unexpected property '{$key}' of type '{$type}'");
                    $participant->$key = "[Unhandled type: {$type}]";
                }
                    
                if ($logThisOne && (is_array($value) || is_object($value))) {
                    $type = is_array($value) ? 'array with ' . count($value) . ' elements' : 'object of class ' . get_class($value);
                    log_message('debug', "Participant {$participantId} has complex '{$key}' property: {$type}");
                }
            }
        }
        
        log_message('debug', 'Completed participant data validation and cleaning');
    }
    
    /**
     * Export participants data to Excel
     *
     * @param array $participants Array of participants data
     * @param string $filename Filename (without extension)
     * @param bool $download Whether to download the file or save it locally
     * @return string|bool File path if saved, true if downloaded, false on failure
     */    public function exportParticipants(array $participants, string $filename = 'participants', bool $download = true)
    {        // Clear any output buffers before starting
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            // Disable automatic output compression that might interfere with Excel download
            ini_set('zlib.output_compression', 'Off');
            
            log_message('debug', 'Starting Excel export for ' . count($participants) . ' participants');
            
            // SAFETY: Instead of trying to clean complex objects (which may cause JSON issues),
            // we'll extract only the specific fields we need directly into a simple array format
            // This completely avoids any serialization or complicated object handling

            // Define headers with English names - REMOVED essay columns to avoid export issues
            $headers = [
                'No',
                'Full Name',
                'Email',
                'Phone',
                'Address',
                'Registration Date',
                'Category',
                'Nationality',
                'Form Status'
            ];log_message('debug', 'Excel headers defined with ' . count($headers) . ' columns');
        // Set column widths
        $columnWidths = [5, 25, 25, 15, 30, 15, 15, 15, 15];
        log_message('debug', 'Column widths configured');

        // Format data for Excel - using simple array structure that avoids complex objects
        $data = [];
        $no = 1;
        log_message('debug', 'Starting participant data processing for Excel export - simplified approach');
        
        // Create a direct data extraction function for safe value retrieval
        $safeGetValue = function($participant, $field, $default = '') {
            // Make sure we avoid any complex objects or arrays that could cause serialization issues
            if (!isset($participant->$field)) {
                return $default;
            }
            
            $value = $participant->$field;
            
            // Handle simple scalar types directly
            if (is_string($value) || is_int($value) || is_float($value) || is_bool($value)) {
                return is_string($value) ? $this->cleanHtmlTags($value) : $value;
            }
            
            // For complex types, just return a placeholder
            if (is_object($value) || is_array($value)) {
                $type = is_object($value) ? get_class($value) : 'array';
                log_message('debug', "Simplified complex {$type} in '{$field}' field");
                return "[Complex data]";
            }
            
            return $default;
        };
        
        foreach ($participants as $index => $participant) {
            try {
                $participantId = $safeGetValue($participant, 'id', 'unknown-'.$index);
                log_message('debug', "Processing participant {$index}: ID {$participantId}");
                
                // Map form status to text - using direct access to avoid complex object issues
                $formStatus = isset($participant->form_status) ? (int)$participant->form_status : null;
                $formStatusText = 'Unknown';
                
                if ($formStatus !== null) {
                    switch($formStatus) {
                        case 0: $formStatusText = 'Not Started'; break;
                        case 1: $formStatusText = 'On Progress'; break;
                        case 2: $formStatusText = 'Submitted'; break;
                        default: $formStatusText = 'Unknown';
                    }
                }
                
                // Map category to English - using direct access to avoid complex object issues
                $category = $safeGetValue($participant, 'category');
                $categoryText = 'Unknown';
                
                if (!empty($category)) {
                    $categoryLower = strtolower($category);
                    if ($categoryLower === 'fully_funded') {
                        $categoryText = 'Fully Funded';
                    } elseif ($categoryLower === 'self_funded') {
                        $categoryText = 'Self Funded';
                    } else {
                        $categoryText = $category;
                    }
                }

                // Map form status to text
                $formStatusText = 'Unknown';
                if (isset($participant->form_status)) {
                    switch ($participant->form_status) {
                        case 0:
                            $formStatusText = 'Not Started';
                            break;
                        case 1:
                            $formStatusText = 'On Progress';
                            break;
                        case 2:
                            $formStatusText = 'Submitted';
                            break;
                        default:
                            $formStatusText = 'Unknown';
                    }
                    log_message('debug', 'Participant form status mapped: ' . $participant->form_status . ' -> ' . $formStatusText);
                }

                // Map category to English
                $categoryText = 'Unknown';
                if (isset($participant->category)) {
                    if (strtolower($participant->category) == 'fully_funded') {
                        $categoryText = 'Fully Funded';
                    } elseif (strtolower($participant->category) == 'self_funded') {
                        $categoryText = 'Self Funded';
                    } else {
                        $categoryText = $participant->category;
                    }
                }                  // Prepare row data and sanitize text for Excel - clean HTML tags first
                log_message('debug', 'Cleaning participant data for Excel export');
                $rowData = [
                    $no++,
                    clean_for_excel($this->cleanHtmlTags($participant->full_name ?? '')),
                    clean_for_excel($participant->email ?? ''),
                    clean_for_excel($this->cleanHtmlTags($participant->phone_number ?? $participant->phone ?? '')),
                    clean_for_excel($this->cleanHtmlTags($participant->address ?? '')),
                    isset($participant->created_at) ? format_excel_date($participant->created_at) : '',
                    $categoryText,
                    clean_for_excel($this->cleanHtmlTags($participant->nationality ?? '')),
                    $formStatusText                ];
                
                // Verify that all data is clean before adding to the array
                foreach ($rowData as $key => $value) {
                    if (!is_scalar($value) && !is_null($value)) {
                        log_message('error', "Non-scalar value detected in row data at position {$key} for participant {$participantId}");
                        // Replace with a safe string value
                        $rowData[$key] = "[Invalid data type: " . gettype($value) . "]";
                    }
                }
                
                $data[] = $rowData;
                log_message('debug', 'Added row data for participant ID: ' . $participantId);
            } catch (\Exception $e) {
                log_message('error', 'Error processing participant at index ' . $index . ': ' . $e->getMessage());
                log_message('error', 'Exception stack trace: ' . $e->getTraceAsString());
                continue; // Skip to next participant on error
            }
        }
        
        log_message('debug', 'Completed processing all ' . count($data) . ' participant rows for Excel export');
        
        // Final safety check - If no data was processed successfully, don't try to create an Excel file
        if (empty($data)) {
            log_message('error', 'No participant data was successfully processed for export');
            throw new \Exception('No data available for export');
        }
          // Clean up output buffers to ensure clean download
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        log_message('debug', 'Starting direct Excel file generation to browser');
        try {
            // Create a new spreadsheet directly here to avoid nested function issues
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Participants');
            
            // Add headers
            $col = 'A';
            foreach ($headers as $index => $header) {
                $sheet->setCellValue($col . '1', $header);
                if (isset($columnWidths[$index])) {
                    $sheet->getColumnDimension($col)->setWidth($columnWidths[$index]);
                } else {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                $col++;
            }
            
            // Style headers
            $lastCol = chr(64 + count($headers)); // ASCII 65 is 'A'
            $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']]
            ]);
            
            // Add data rows
            $row = 2;
            foreach ($data as $dataRow) {
                $col = 'A';
                foreach ($dataRow as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }
            
            // Final download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            
            log_message('debug', 'Excel export completed successfully with direct output');
            exit; // Stop execution after download
        } catch (\Throwable $e) {
            log_message('error', 'Excel export failed at final stage: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    } catch (\Throwable $e) {
        log_message('error', 'Participant export failed with exception: ' . $e->getMessage());
        log_message('error', 'Exception trace: ' . $e->getTraceAsString());
        throw $e;
    }
    }

    /**
     * Export payments data to Excel
     *
     * @param array $payments Array of payments data
     * @param string $filename Filename (without extension)
     * @param bool $download Whether to download the file or save it locally
     * @return string|bool File path if saved, true if downloaded, false on failure
     */
    public function exportPayments(array $payments, string $filename = 'payments', bool $download = true)
    {
        // Define headers
        $headers = [
            'No',
            'ID Pembayaran',
            'Nama',
            'Email',
            'Jumlah',
            'Status',
            'Metode Pembayaran',
            'Tanggal Pembayaran'
        ];

        // Set column widths
        $columnWidths = [5, 20, 25, 25, 15, 15, 20, 15];

        // Format data for Excel
        $data = [];
        $no = 1;

        foreach ($payments as $payment) {
            $data[] = [
                $no++,
                $payment['payment_id'] ?? '',
                $payment['name'] ?? '',
                $payment['email'] ?? '',
                isset($payment['amount']) ? format_excel_currency($payment['amount']) : '',
                $payment['status'] ?? '',
                $payment['payment_method'] ?? '',
                isset($payment['payment_date']) ? format_excel_date($payment['payment_date']) : ''
            ];
        }

        // Use the helper function to generate the Excel file
        return export_to_excel(
            $filename,
            $headers,
            $data,
            'Payments',
            $columnWidths,
            true,
            $download
        );
    }

    /**
     * Export custom data to Excel
     *
     * @param array $headers Column headers
     * @param array $data Data to export
     * @param string $filename Filename (without extension)
     * @param string $sheetTitle Sheet title
     * @param array $columnWidths Column widths (optional)
     * @param bool $download Whether to download the file or save it locally
     * @return string|bool File path if saved, true if downloaded, false on failure
     */
    public function exportCustomData(
        array $headers,
        array $data,
        string $filename,
        string $sheetTitle,
        array $columnWidths = [],
        bool $download = true
    ) {
        return export_to_excel(
            $filename,
            $headers,
            $data,
            $sheetTitle,
            $columnWidths,
            true,
            $download
        );
    }
}
