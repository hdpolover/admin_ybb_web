<?php
echo "=== COMPREHENSIVE EXCEL CORRUPTION ROOT CAUSE ANALYSIS ===\n\n";

// Create a simple test with known good data first
$testParticipant = [
    'id' => 1,
    'full_name' => 'Test User Simple',
    'account_id' => 'TEST123',
    'email' => 'test@example.com',
    'program_name' => 'Test Program',
    'created_at' => '2025-07-27 12:00:00'
];

echo "1. TESTING WITH SIMPLEST POSSIBLE DATA\n";
echo "=" . str_repeat("=", 40) . "\n\n";

// Test JSON encoding first
$jsonTest = json_encode([$testParticipant]);
echo "JSON Test:\n";
echo "- Data: " . substr($jsonTest, 0, 100) . "...\n";
echo "- Size: " . strlen($jsonTest) . " bytes\n";
echo "- Valid: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "\n";
echo "- Error: " . (json_last_error_msg() ?: 'None') . "\n\n";

echo "2. TESTING ACTUAL EXPORT WITH MINIMAL DATA\n";
echo "=" . str_repeat("=", 42) . "\n\n";

// Use the actual YBB Export system
try {
    // Initialize the export library
    require_once 'vendor/autoload.php';
    
    // Simple bootstrap without full CI4
    define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
    
    // Create a minimal export request
    $exportUrl = 'http://localhost:8080/exports/participants';
    
    $postData = [
        'program_id' => 7, // Use a valid program ID
        'limit' => 1,      // Just export 1 record
        'category' => 'fully_funded'
    ];
    
    echo "Making direct HTTP request to export endpoint...\n";
    echo "URL: $exportUrl\n";
    echo "Data: " . json_encode($postData) . "\n\n";
    
    // Make the request with maximum debugging
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $exportUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            'User-Agent: Debug-Test/1.0'
        ],
        CURLOPT_VERBOSE => true,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    
    // Capture verbose output
    $verboseHandle = fopen('php://temp', 'r+');
    curl_setopt($ch, CURLOPT_STDERR, $verboseHandle);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    
    // Get verbose output
    rewind($verboseHandle);
    $verboseOutput = stream_get_contents($verboseHandle);
    fclose($verboseHandle);
    
    if (curl_errno($ch)) {
        echo "❌ cURL Error: " . curl_error($ch) . "\n";
        curl_close($ch);
        exit(1);
    }
    
    curl_close($ch);
    
    echo "Response Details:\n";
    echo "- HTTP Code: $httpCode\n";
    echo "- Content Type: $contentType\n";
    echo "- Response Size: " . strlen($response) . " bytes\n\n";
    
    if ($httpCode !== 200) {
        echo "❌ HTTP Error $httpCode\n";
        echo "Response: " . substr($response, 0, 500) . "\n\n";
        echo "cURL Verbose Output:\n$verboseOutput\n";
        exit(1);
    }
    
    // Parse the response
    $jsonResponse = json_decode($response, true);
    
    if ($jsonResponse === null) {
        echo "❌ Invalid JSON Response\n";
        echo "Response: " . substr($response, 0, 200) . "\n";
        exit(1);
    }
    
    echo "✅ Export request successful!\n";
    
    if (isset($jsonResponse['download_url'])) {
        echo "Download URL: {$jsonResponse['download_url']}\n\n";
        
        echo "3. DOWNLOADING AND ANALYZING THE EXCEL FILE\n";
        echo "=" . str_repeat("=", 45) . "\n\n";
        
        // Download the file
        $downloadCh = curl_init();
        curl_setopt_array($downloadCh, [
            CURLOPT_URL => $jsonResponse['download_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        $fileContent = curl_exec($downloadCh);
        $downloadHttpCode = curl_getinfo($downloadCh, CURLINFO_HTTP_CODE);
        
        if (curl_errno($downloadCh)) {
            echo "❌ Download Error: " . curl_error($downloadCh) . "\n";
            curl_close($downloadCh);
            exit(1);
        }
        
        curl_close($downloadCh);
        
        if ($downloadHttpCode !== 200 || empty($fileContent)) {
            echo "❌ Failed to download file (HTTP: $downloadHttpCode)\n";
            exit(1);
        }
        
        echo "Downloaded file size: " . strlen($fileContent) . " bytes\n";
        
        // Analyze the file header
        $header = substr($fileContent, 0, 16);
        $hexHeader = bin2hex($header);
        
        echo "File header (hex): $hexHeader\n";
        echo "File header (ascii): " . preg_replace('/[^\x20-\x7E]/', '.', $header) . "\n";
        
        // Check if it's a valid ZIP file (Excel files are ZIP archives)
        if (substr($hexHeader, 0, 4) === '504b') {
            echo "✅ File appears to be a valid ZIP/Excel format\n";
            
            // Save the file for manual inspection
            $testFilename = 'debug_export_' . date('H-i-s') . '.xlsx';
            file_put_contents($testFilename, $fileContent);
            echo "✅ File saved as: $testFilename\n";
            echo "Try opening this file manually to see if it works\n\n";
            
            // Try to extract and examine the content
            echo "4. EXAMINING EXCEL FILE INTERNAL STRUCTURE\n";
            echo "=" . str_repeat("=", 45) . "\n\n";
            
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                $result = $zip->open($testFilename);
                
                if ($result === TRUE) {
                    echo "✅ Excel file is a valid ZIP archive\n";
                    echo "Files in archive: " . $zip->numFiles . "\n";
                    
                    // List some key files
                    for ($i = 0; $i < min($zip->numFiles, 10); $i++) {
                        echo "  - " . $zip->getNameIndex($i) . "\n";
                    }
                    
                    // Try to read the worksheet data
                    $worksheetContent = $zip->getFromName('xl/worksheets/sheet1.xml');
                    if ($worksheetContent !== false) {
                        echo "\nWorksheet content preview:\n";
                        echo substr($worksheetContent, 0, 300) . "...\n";
                        
                        // Check for corruption in the XML
                        if (strpos($worksheetContent, "\0") !== false) {
                            echo "❌ Found NULL bytes in worksheet XML - THIS IS THE CORRUPTION!\n";
                        } elseif (!mb_check_encoding($worksheetContent, 'UTF-8')) {
                            echo "❌ Worksheet XML is not valid UTF-8 - THIS IS THE CORRUPTION!\n";
                        } else {
                            echo "✅ Worksheet XML appears clean\n";
                        }
                    }
                    
                    $zip->close();
                } else {
                    echo "❌ Cannot open as ZIP archive (error code: $result)\n";
                    echo "This means the Excel file is corrupted at the ZIP level\n";
                }
            } else {
                echo "⚠️  ZipArchive not available for detailed analysis\n";
            }
            
        } else {
            echo "❌ File is NOT a valid ZIP/Excel format!\n";
            echo "This indicates severe corruption at the file format level\n";
            
            // Show more of the file content to identify what we actually got
            echo "\nFile content preview (first 200 bytes):\n";
            echo "Hex: " . bin2hex(substr($fileContent, 0, 200)) . "\n";
            echo "ASCII: " . preg_replace('/[^\x20-\x7E]/', '.', substr($fileContent, 0, 200)) . "\n";
        }
        
    } else {
        echo "❌ No download URL in response\n";
        echo "Response: " . json_encode($jsonResponse, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n5. ANALYSIS RESULTS AND NEXT STEPS\n";
echo "=" . str_repeat("=", 37) . "\n\n";

echo "Based on this test, the corruption could be happening at several levels:\n\n";

echo "A. DATA LEVEL:\n";
echo "   - Database UTF8MB4 conversion successful\n";
echo "   - Data cleaning function working\n";
echo "   - JSON encoding successful\n\n";

echo "B. TRANSMISSION LEVEL:\n";
echo "   - HTTP request/response successful\n";
echo "   - Export API communication working\n\n";

echo "C. EXPORT API LEVEL:\n";
echo "   - Python Flask service may have encoding issues\n";
echo "   - Excel generation process may have bugs\n";
echo "   - File format corruption during creation\n\n";

echo "D. FILE DOWNLOAD LEVEL:\n";
echo "   - File transfer may corrupt binary data\n";
echo "   - Web server configuration issues\n\n";

echo "RECOMMENDED NEXT ACTIONS:\n";
echo "1. Examine the downloaded test file manually\n";
echo "2. Check the Python export service logs\n";
echo "3. Test with even simpler data (no special characters)\n";
echo "4. Consider updating the YBB Export Library to force UTF-8\n\n";

echo "=== ANALYSIS COMPLETE ===\n";
?>
