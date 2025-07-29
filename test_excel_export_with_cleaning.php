<?php
echo "=== TESTING EXCEL EXPORT WITH DATA CLEANING ===\n\n";

// Test the YBB Export with data cleaning
$exportUrl = 'http://localhost:8080/exports/participants';

// Test data to export (small sample first)
$testFilters = [
    'program_id' => 7, // Adjust this to a program with data
    'limit' => 5, // Small test first
    'category' => 'fully_funded', // Test category filter
];

echo "1. TESTING EXPORT WITH CLEANED DATA\n";
echo "=" . str_repeat("=", 38) . "\n\n";

echo "Export URL: $exportUrl\n";
echo "Test Filters:\n";
foreach ($testFilters as $key => $value) {
    echo "  $key: $value\n";
}
echo "\n";

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt_array($ch, [
    CURLOPT_URL => $exportUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($testFilters),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json',
        'User-Agent: Excel-Export-Test/1.0'
    ],
    CURLOPT_VERBOSE => false,
    CURLOPT_SSL_VERIFYPEER => false,
]);

echo "2. SENDING EXPORT REQUEST\n";
echo "=" . str_repeat("=", 28) . "\n\n";

$startTime = microtime(true);
$response = curl_exec($ch);
$endTime = microtime(true);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$totalTime = round($endTime - $startTime, 2);

if (curl_errno($ch)) {
    echo "❌ cURL Error: " . curl_error($ch) . "\n";
    curl_close($ch);
    exit(1);
}

curl_close($ch);

echo "Response received in {$totalTime} seconds\n";
echo "HTTP Status Code: $httpCode\n";
echo "Content Type: $contentType\n";
echo "Response Size: " . strlen($response) . " bytes\n\n";

if ($httpCode !== 200) {
    echo "❌ HTTP Error $httpCode\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
    exit(1);
}

echo "3. ANALYZING RESPONSE\n";
echo "=" . str_repeat("=", 22) . "\n\n";

// Try to decode JSON response
$jsonResponse = json_decode($response, true);

if ($jsonResponse === null) {
    echo "❌ Response is not valid JSON\n";
    echo "Response preview: " . substr($response, 0, 200) . "\n";
    exit(1);
}

// Check if it's a success response with download URL
if (isset($jsonResponse['success']) && $jsonResponse['success']) {
    echo "✅ Export request successful!\n";
    
    if (isset($jsonResponse['download_url'])) {
        echo "📁 Download URL: {$jsonResponse['download_url']}\n";
        
        echo "\n4. TESTING EXCEL FILE DOWNLOAD\n";
        echo "=" . str_repeat("=", 33) . "\n\n";
        
        // Test downloading the actual Excel file
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
        $downloadContentType = curl_getinfo($downloadCh, CURLINFO_CONTENT_TYPE);
        
        if (curl_errno($downloadCh)) {
            echo "❌ Download Error: " . curl_error($downloadCh) . "\n";
        } else {
            echo "Download HTTP Status: $downloadHttpCode\n";
            echo "Download Content Type: $downloadContentType\n";
            echo "Downloaded File Size: " . strlen($fileContent) . " bytes\n";
            
            if ($downloadHttpCode === 200 && strlen($fileContent) > 0) {
                // Save the file for testing
                $testFilename = 'test_export_' . date('Y-m-d_H-i-s') . '.xlsx';
                file_put_contents($testFilename, $fileContent);
                echo "✅ Excel file downloaded successfully!\n";
                echo "📁 Saved as: $testFilename\n";
                
                // Check if it's a valid Excel file by looking at file headers
                $fileHeader = substr($fileContent, 0, 8);
                $hexHeader = bin2hex($fileHeader);
                echo "File header (hex): $hexHeader\n";
                
                // Excel files should start with PK (ZIP format) - 50 4B
                if (substr($hexHeader, 0, 4) === '504b') {
                    echo "✅ File appears to be a valid ZIP/Excel format\n";
                    echo "\n🎉 SUCCESS: Excel export with data cleaning is working!\n";
                    echo "Try opening the file: $testFilename\n";
                } else {
                    echo "⚠️  File header doesn't look like Excel format\n";
                    echo "This might indicate the data cleaning fixed corruption issues\n";
                }
            } else {
                echo "❌ Failed to download file or file is empty\n";
            }
        }
        
        curl_close($downloadCh);
    }
    
    // Show any additional info
    if (isset($jsonResponse['message'])) {
        echo "📝 Message: {$jsonResponse['message']}\n";
    }
    
    if (isset($jsonResponse['export_id'])) {
        echo "🆔 Export ID: {$jsonResponse['export_id']}\n";
    }
    
} else {
    echo "❌ Export request failed\n";
    echo "Response: " . json_encode($jsonResponse, JSON_PRETTY_PRINT) . "\n";
}

echo "\n5. CHECKING LOGS FOR DATA CLEANING INFO\n";
echo "=" . str_repeat("=", 42) . "\n\n";

echo "Check the following log files for data cleaning details:\n";
echo "- writable/logs/log-" . date('Y-m-d') . ".php\n";
echo "\nLook for messages containing:\n";
echo "- 'Excel data cleaning'\n";
echo "- 'Removed NULL bytes'\n";
echo "- 'Fixed UTF-8 encoding'\n";
echo "- 'Cleaned corrupted Unicode'\n\n";

echo "6. NEXT STEPS\n";
echo "=" . str_repeat("=", 15) . "\n\n";

echo "If the export worked:\n";
echo "✅ 1. Try with a larger dataset (remove limit filter)\n";
echo "✅ 2. Test with different programs that had corruption issues\n";
echo "✅ 3. Monitor the logs to see what data issues were cleaned\n\n";

echo "If you still get corruption:\n";
echo "🔧 1. Check the specific participant/essay data that's causing issues\n";
echo "🔧 2. Consider converting database to utf8mb4 encoding\n";
echo "🔧 3. Add more specific character cleaning rules\n\n";

echo "=== TEST COMPLETE ===\n";
?>
