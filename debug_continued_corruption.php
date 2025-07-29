<?php
echo "=== DEBUGGING CONTINUED EXCEL CORRUPTION ISSUES ===\n\n";

try {
    $host = '194.163.42.101';
    $dbname = 'u1437096_ybb_master_app_db';
    $username = 'u1437096_ybb_master_app_admin_user';
    $password = '7J8*^dFEa&lN';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Connected to UTF8MB4 database\n\n";
    
    echo "1. CHECKING RECENT EXPORT LOGS\n";
    echo "=" . str_repeat("=", 32) . "\n\n";
    
    // Check if there are recent log files
    $logDir = 'writable/logs/';
    $todayLog = $logDir . 'log-' . date('Y-m-d') . '.php';
    
    if (file_exists($todayLog)) {
        echo "Found today's log file: $todayLog\n";
        
        // Look for recent export and cleaning messages
        $logContent = file_get_contents($todayLog);
        
        // Extract export-related messages from the last few hours
        $lines = explode("\n", $logContent);
        $recentExportLogs = [];
        $currentTime = time();
        
        foreach ($lines as $line) {
            // Look for export and cleaning messages
            if (strpos($line, 'export') !== false || 
                strpos($line, 'Excel') !== false || 
                strpos($line, 'clean') !== false) {
                $recentExportLogs[] = $line;
            }
        }
        
        if (!empty($recentExportLogs)) {
            echo "Recent export-related log entries:\n";
            foreach (array_slice($recentExportLogs, -10) as $logEntry) {
                echo "  $logEntry\n";
            }
        } else {
            echo "No recent export-related log entries found\n";
        }
    } else {
        echo "Today's log file not found: $todayLog\n";
        echo "Available log files:\n";
        $logFiles = glob($logDir . 'log-*.php');
        foreach (array_slice($logFiles, -3) as $file) {
            echo "  " . basename($file) . "\n";
        }
    }
    
    echo "\n2. ANALYZING SPECIFIC CORRUPTION PATTERNS\n";
    echo "=" . str_repeat("=", 43) . "\n\n";
    
    // Get a sample of recent participants that might be causing issues
    $recentParticipants = $pdo->query("
        SELECT 
            id, full_name, account_id, experiences, achievements, program_id,
            LENGTH(full_name) as name_len,
            LENGTH(experiences) as exp_len, 
            LENGTH(achievements) as ach_len
        FROM participants 
        WHERE is_deleted = 0 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY id DESC 
        LIMIT 10
    ")->fetchAll();
    
    echo "Analyzing " . count($recentParticipants) . " recent participants:\n\n";
    
    $severeIssues = [];
    
    foreach ($recentParticipants as $p) {
        $issues = [];
        
        // Check for severe corruption patterns
        foreach (['full_name', 'experiences', 'achievements'] as $field) {
            $value = $p[$field];
            if (!$value) continue;
            
            // Check for NULL bytes (critical)
            if (strpos($value, "\0") !== false) {
                $issues[] = "❌ NULL BYTES in $field";
            }
            
            // Check for binary data
            if (!ctype_print(str_replace(["\n", "\r", "\t"], '', $value))) {
                $issues[] = "❌ NON-PRINTABLE chars in $field";
            }
            
            // Check for specific corruption markers
            $corruptMarkers = [
                '�' => 'Unicode replacement char',
                "\xEF\xBF\xBD" => 'UTF-8 replacement char',
                "\x00" => 'NULL byte',
                "\x1A" => 'EOF character',
                "\x08" => 'Backspace',
                "\x0C" => 'Form feed'
            ];
            
            foreach ($corruptMarkers as $marker => $desc) {
                if (strpos($value, $marker) !== false) {
                    $issues[] = "⚠️  $desc in $field";
                }
            }
            
            // Check for very long fields
            if (strlen($value) > 32000) {
                $issues[] = "📏 $field too long (" . strlen($value) . " chars)";
            }
        }
        
        if (!empty($issues)) {
            $severeIssues[$p['id']] = [
                'name' => $p['full_name'],
                'account' => $p['account_id'],
                'program' => $p['program_id'],
                'issues' => $issues
            ];
        }
    }
    
    if (empty($severeIssues)) {
        echo "✅ No severe corruption found in recent participants\n";
    } else {
        echo "🚨 FOUND SEVERE CORRUPTION IN " . count($severeIssues) . " RECENT PARTICIPANTS:\n\n";
        foreach ($severeIssues as $id => $info) {
            echo "Participant ID: $id (Program: {$info['program']})\n";
            echo "Name: {$info['name']}\n";
            echo "Account: {$info['account']}\n";
            foreach ($info['issues'] as $issue) {
                echo "  $issue\n";
            }
            echo "\n";
        }
    }
    
    echo "3. TESTING DATA CLEANING FUNCTION DIRECTLY\n";
    echo "=" . str_repeat("=", 45) . "\n\n";
    
    // Create test data with known corruption issues
    $testData = [
        [
            'id' => 1,
            'name' => "Test User",
            'text_with_nulls' => "Hello\0World\0Test",
            'text_with_control' => "Line1\x08\x0CLine2\x1AEnd",
            'text_with_unicode' => "Café � Test",
            'very_long_text' => str_repeat('A', 35000),
            'formula_injection' => "=SUM(A1:A10)",
            'normal_text' => "This is normal text"
        ]
    ];
    
    echo "Testing data cleaning function with problematic data...\n\n";
    
    // Simulate the cleaning function
    function testCleanDataForExcel($data) {
        $cleanedCount = 0;
        $issuesFound = [];
        
        foreach ($data as &$row) {
            $rowCleaned = false;
            
            foreach ($row as $field => &$value) {
                if (!is_string($value) || $value === '' || $value === null) {
                    continue;
                }
                
                $originalValue = $value;
                
                // Remove NULL bytes
                if (strpos($value, "\0") !== false) {
                    $value = str_replace("\0", '', $value);
                    $issuesFound[] = "Removed NULL bytes from field: $field";
                    $rowCleaned = true;
                }
                
                // Remove control characters
                $beforeControlClean = $value;
                $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
                if ($beforeControlClean !== $value) {
                    $issuesFound[] = "Removed control characters from field: $field";
                    $rowCleaned = true;
                }
                
                // Handle corrupted Unicode
                $beforeUnicodeClean = $value;
                $value = preg_replace('/�+/', '?', $value);
                
                if (!mb_check_encoding($value, 'UTF-8')) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    $issuesFound[] = "Fixed UTF-8 encoding for field: $field";
                    $rowCleaned = true;
                }
                
                if ($beforeUnicodeClean !== $value) {
                    $issuesFound[] = "Cleaned corrupted Unicode characters from field: $field";
                    $rowCleaned = true;
                }
                
                // Truncate long fields
                if (strlen($value) > 32767) {
                    $value = substr($value, 0, 32764) . '...';
                    $issuesFound[] = "Truncated long text in field: $field";
                    $rowCleaned = true;
                }
                
                // Prevent formula injection
                if (preg_match('/^[=+\-@]/', $value)) {
                    $value = "'" . $value;
                    $issuesFound[] = "Prevented formula injection in field: $field";
                    $rowCleaned = true;
                }
            }
            
            if ($rowCleaned) {
                $cleanedCount++;
            }
        }
        
        return [$data, $issuesFound, $cleanedCount];
    }
    
    list($cleanedData, $issues, $cleanedCount) = testCleanDataForExcel($testData);
    
    echo "Cleaning test results:\n";
    echo "Records cleaned: $cleanedCount\n";
    echo "Issues found and fixed: " . count($issues) . "\n\n";
    
    foreach ($issues as $issue) {
        echo "✅ $issue\n";
    }
    
    echo "\nCleaned data sample:\n";
    foreach ($cleanedData[0] as $field => $value) {
        $display = is_string($value) ? substr($value, 0, 50) : $value;
        if (strlen($value) > 50) $display .= '...';
        echo "  $field: $display\n";
    }
    
    echo "\n4. TESTING ACTUAL EXPORT API COMMUNICATION\n";
    echo "=" . str_repeat("=", 44) . "\n\n";
    
    // Test what's actually being sent to the export API
    echo "Creating a minimal test export request...\n";
    
    $testExportData = [
        'export_type' => 'participants',
        'data' => [
            [
                'id' => 1,
                'full_name' => 'Test User',
                'account_id' => 'TEST123',
                'email' => 'test@example.com',
                'program_name' => 'Test Program'
            ]
        ],
        'filename' => 'test_export_debug.xlsx'
    ];
    
    $jsonData = json_encode($testExportData);
    echo "JSON payload size: " . strlen($jsonData) . " bytes\n";
    echo "JSON payload valid: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "\n";
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON Error: " . json_last_error_msg() . "\n";
    }
    
    // Check if JSON contains any problematic characters
    $problematicChars = [
        "\0" => 'NULL byte',
        "\x08" => 'Backspace',
        "\x0C" => 'Form feed',
        "\x1A" => 'EOF character'
    ];
    
    $jsonIssues = [];
    foreach ($problematicChars as $char => $desc) {
        if (strpos($jsonData, $char) !== false) {
            $jsonIssues[] = $desc;
        }
    }
    
    if (empty($jsonIssues)) {
        echo "✅ JSON payload appears clean\n";
    } else {
        echo "❌ JSON payload contains problematic characters:\n";
        foreach ($jsonIssues as $issue) {
            echo "  - $issue\n";
        }
    }
    
    echo "\n5. CHECKING YBB EXPORT API CONFIGURATION\n";
    echo "=" . str_repeat("=", 43) . "\n\n";
    
    // Check the YbbExport library configuration
    $ybbExportFile = 'app/Libraries/YbbExport.php';
    if (file_exists($ybbExportFile)) {
        echo "Checking YBB Export Library configuration...\n";
        
        $exportContent = file_get_contents($ybbExportFile);
        
        // Look for API URL configuration
        if (preg_match('/export_api_url[\'"]?\s*=>\s*[\'"]([^\'"]+)/', $exportContent, $matches)) {
            echo "Export API URL: {$matches[1]}\n";
        }
        
        // Check for timeout settings
        if (preg_match('/timeout[\'"]?\s*=>\s*(\d+)/', $exportContent, $matches)) {
            echo "Timeout setting: {$matches[1]} seconds\n";
        }
        
        // Check for encoding settings
        if (strpos($exportContent, 'utf-8') !== false || strpos($exportContent, 'UTF-8') !== false) {
            echo "✅ UTF-8 encoding found in export library\n";
        } else {
            echo "⚠️  No explicit UTF-8 encoding found in export library\n";
        }
        
    } else {
        echo "❌ YBB Export Library not found at: $ybbExportFile\n";
    }
    
    echo "\n6. RECOMMENDATIONS FOR NEXT STEPS\n";
    echo "=" . str_repeat("=", 35) . "\n\n";
    
    if (!empty($severeIssues)) {
        echo "🚨 IMMEDIATE ACTION REQUIRED:\n";
        echo "1. Found severe corruption in recent data\n";
        echo "2. The data cleaning may not be handling all cases\n";
        echo "3. Need to strengthen the cleaning function\n\n";
    }
    
    echo "DEBUGGING STEPS TO TRY:\n\n";
    
    echo "A. TEST WITH SINGLE CLEAN RECORD:\n";
    echo "   - Export just 1 participant with simple ASCII data\n";
    echo "   - See if the basic export mechanism works\n\n";
    
    echo "B. CHECK EXPORT API LOGS:\n";
    echo "   - Look at the Python Flask export service logs\n";
    echo "   - Check if it's receiving corrupted data\n\n";
    
    echo "C. EXAMINE THE ACTUAL EXCEL FILE:\n";
    echo "   - Open the corrupted file in a hex editor\n";
    echo "   - Look for specific corruption patterns\n";
    echo "   - Check if it's a valid ZIP/Excel structure\n\n";
    
    echo "D. TEST DIFFERENT EXPORT SIZES:\n";
    echo "   - Try limit=1, limit=10, limit=100\n";
    echo "   - See if corruption is size-related\n\n";
    
    echo "E. STRENGTHEN DATA CLEANING:\n";
    echo "   - Add more aggressive cleaning rules\n";
    echo "   - Handle edge cases found in analysis\n\n";
    
    echo "Would you like me to create an enhanced cleaning function\n";
    echo "based on the issues found in this analysis?\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "=== DEBUGGING COMPLETE ===\n";
?>
