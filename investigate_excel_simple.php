<?php
echo "=== PARTICIPANT DATA INVESTIGATION FOR EXCEL EXPORT ISSUES ===\n\n";

// Simple investigation without full CodeIgniter bootstrap
// First, let's check if we can connect to the database directly

echo "1. ATTEMPTING DATABASE CONNECTION\n";
echo "=" . str_repeat("=", 35) . "\n\n";

// Try to read database config
$configPath = __DIR__ . '/app/Config/Database.php';
if (!file_exists($configPath)) {
    echo "❌ Cannot find database config at: $configPath\n";
    exit(1);
}

// Simple database connection using PDO
try {
    // Database credentials from Config/Database.php
    $host = '194.163.42.101';
    $dbname = 'u1437096_ybb_master_app_db';
    $username = 'u1437096_ybb_master_app_admin_user';
    $password = '7J8*^dFEa&lN';
    
    echo "Attempting to connect to database: $dbname\n";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Database connection successful\n\n";
    
    echo "2. CHECKING DATABASE ENCODING\n";
    echo "=" . str_repeat("=", 30) . "\n\n";
    
    $encodingStmt = $pdo->query("SELECT @@character_set_database, @@collation_database");
    $encoding = $encodingStmt->fetch();
    echo "Database Character Set: " . $encoding['@@character_set_database'] . "\n";
    echo "Database Collation: " . $encoding['@@collation_database'] . "\n\n";
    
    echo "3. CHECKING PARTICIPANT DATA FOR PROBLEMATIC CHARACTERS\n";
    echo "=" . str_repeat("=", 55) . "\n\n";
    
    // Get a sample of participant data
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.full_name,
            p.account_id,
            p.email,
            p.current_address,
            p.origin_address,
            p.instagram_account,
            p.experiences,
            p.achievements,
            p.nationality,
            p.occupation,
            p.institution,
            LENGTH(p.full_name) as name_length,
            LENGTH(p.experiences) as exp_length,
            LENGTH(p.achievements) as ach_length,
            p.program_id
        FROM participants p
        WHERE p.is_deleted = 0
        ORDER BY p.id DESC
        LIMIT 20
    ");
    
    $stmt->execute();
    $participants = $stmt->fetchAll();
    
    echo "Found " . count($participants) . " participants to analyze\n\n";
    
    $totalIssues = 0;
    $issuesFound = [];
    
    foreach ($participants as $participant) {
        $participantIssues = [];
        $id = $participant['id'];
        
        // Check each text field for issues
        $textFields = ['full_name', 'email', 'current_address', 'origin_address', 'instagram_account', 'experiences', 'achievements', 'nationality', 'occupation', 'institution'];
        
        foreach ($textFields as $field) {
            $value = $participant[$field];
            if ($value === null || $value === '') continue;
            
            // Check for null bytes (major Excel killer)
            if (strpos($value, "\0") !== false) {
                $participantIssues[] = "NULL BYTE in $field";
            }
            
            // Check for control characters (except newlines and tabs)
            if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
                $participantIssues[] = "Control characters in $field";
            }
            
            // Check for non-UTF8 characters
            if (!mb_check_encoding($value, 'UTF-8')) {
                $participantIssues[] = "Invalid UTF-8 encoding in $field";
            }
            
            // Check for Excel formula injection characters at start
            if (preg_match('/^[=+\-@]/', $value)) {
                $participantIssues[] = "Potential Excel formula injection in $field (starts with: " . substr($value, 0, 3) . ")";
            }
            
            // Check for very long fields that exceed Excel limits
            if (strlen($value) > 32767) {
                $participantIssues[] = "$field too long (" . strlen($value) . " chars) - Excel limit is 32,767";
            }
            
            // Check for unusual characters that might cause issues
            if (preg_match('/[\x{FEFF}\x{200B}-\x{200D}\x{FFFE}\x{FFFF}]/u', $value)) {
                $participantIssues[] = "Unusual Unicode characters in $field (BOM, zero-width, etc.)";
            }
        }
        
        // Email specific validation
        if ($participant['email'] && !filter_var($participant['email'], FILTER_VALIDATE_EMAIL)) {
            $participantIssues[] = "Invalid email format";
        }
        
        if (!empty($participantIssues)) {
            $totalIssues += count($participantIssues);
            $issuesFound[$id] = [
                'name' => $participant['full_name'],
                'account_id' => $participant['account_id'],
                'program_id' => $participant['program_id'],
                'issues' => $participantIssues
            ];
        }
    }
    
    if (empty($issuesFound)) {
        echo "✅ No obvious data issues found in participant records\n\n";
    } else {
        echo "⚠️  FOUND $totalIssues POTENTIAL ISSUES IN " . count($issuesFound) . " PARTICIPANTS:\n\n";
        foreach ($issuesFound as $id => $info) {
            echo "Participant ID: $id (Program: {$info['program_id']})\n";
            echo "Name: " . ($info['name'] ?: 'N/A') . "\n";
            echo "Account: " . ($info['account_id'] ?: 'N/A') . "\n";
            echo "Issues:\n";
            foreach ($info['issues'] as $issue) {
                echo "  - $issue\n";
            }
            echo "\n";
        }
    }
    
    echo "4. CHECKING ESSAY DATA\n";
    echo "=" . str_repeat("=", 20) . "\n\n";
    
    // Check essays for issues (these often contain the most problematic data)
    $essayStmt = $pdo->prepare("
        SELECT 
            pae.id,
            pae.participant_id,
            pae.answer,
            LENGTH(pae.answer) as answer_length,
            pe.question,
            p.program_id
        FROM participant_essays pae
        JOIN program_essays pe ON pe.id = pae.program_essay_id
        JOIN participants p ON p.id = pae.participant_id
        WHERE pae.is_deleted = 0
        AND pae.answer IS NOT NULL
        AND pae.answer != ''
        ORDER BY LENGTH(pae.answer) DESC
        LIMIT 15
    ");
    
    $essayStmt->execute();
    $essays = $essayStmt->fetchAll();
    
    echo "Checking " . count($essays) . " essay answers\n\n";
    
    $essayIssues = [];
    
    foreach ($essays as $essay) {
        $issues = [];
        $answer = $essay['answer'];
        
        // Check for null bytes
        if (strpos($answer, "\0") !== false) {
            $issues[] = "Contains NULL bytes";
        }
        
        // Check for very long answers
        if ($essay['answer_length'] > 32767) {
            $issues[] = "Answer too long (" . $essay['answer_length'] . " chars) - Excel limit exceeded";
        }
        
        // Check for control characters
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $answer)) {
            $issues[] = "Contains control characters";
        }
        
        // Check for non-UTF8
        if (!mb_check_encoding($answer, 'UTF-8')) {
            $issues[] = "Invalid UTF-8 encoding";
        }
        
        // Check for potential Excel issues
        if (preg_match('/^[=+\-@]/', $answer)) {
            $issues[] = "Starts with Excel formula character";
        }
        
        // Check for unusual Unicode
        if (preg_match('/[\x{FEFF}\x{200B}-\x{200D}\x{FFFE}\x{FFFF}]/u', $answer)) {
            $issues[] = "Contains unusual Unicode characters";
        }
        
        if (!empty($issues)) {
            $essayIssues[] = [
                'essay_id' => $essay['id'],
                'participant_id' => $essay['participant_id'],
                'program_id' => $essay['program_id'],
                'question' => substr($essay['question'], 0, 50) . '...',
                'answer_length' => $essay['answer_length'],
                'issues' => $issues,
                'answer_preview' => substr($answer, 0, 100) . '...'
            ];
        }
    }
    
    if (empty($essayIssues)) {
        echo "✅ No obvious issues found in essay answers\n\n";
    } else {
        echo "⚠️  FOUND ESSAY ISSUES:\n\n";
        foreach ($essayIssues as $issue) {
            echo "Essay ID: {$issue['essay_id']} (Participant: {$issue['participant_id']}, Program: {$issue['program_id']})\n";
            echo "Question: {$issue['question']}\n";
            echo "Answer Length: {$issue['answer_length']} chars\n";
            echo "Issues:\n";
            foreach ($issue['issues'] as $problemDesc) {
                echo "  - $problemDesc\n";
            }
            echo "Answer Preview: {$issue['answer_preview']}\n\n";
        }
    }
    
    echo "5. TESTING SPECIFIC PROBLEMATIC CHARACTERS\n";
    echo "=" . str_repeat("=", 45) . "\n\n";
    
    // Look for specific characters that commonly cause Excel issues
    $problemChars = [
        '\0' => 'NULL byte',
        '\x1A' => 'Substitute character (Ctrl+Z)',
        '\x00' => 'NULL character',
        '\x08' => 'Backspace',
        '\x0B' => 'Vertical tab',
        '\x0C' => 'Form feed',
        '\x7F' => 'DEL character'
    ];
    
    echo "Searching for specific problematic characters in all text fields...\n\n";
    
    foreach ($problemChars as $char => $description) {
        $searchStmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM participants 
            WHERE is_deleted = 0 
            AND (
                full_name LIKE ? OR
                email LIKE ? OR
                experiences LIKE ? OR
                achievements LIKE ?
            )
        ");
        $pattern = "%$char%";
        $searchStmt->execute([$pattern, $pattern, $pattern, $pattern]);
        $result = $searchStmt->fetch();
        
        if ($result['count'] > 0) {
            echo "⚠️  Found {$result['count']} records with $description ($char)\n";
        }
    }
    
    echo "\n6. SUMMARY AND RECOMMENDATIONS\n";
    echo "=" . str_repeat("=", 35) . "\n\n";
    
    $totalProblematicRecords = count($issuesFound) + count($essayIssues);
    
    if ($totalProblematicRecords > 0) {
        echo "❌ FOUND DATA ISSUES THAT COULD CAUSE EXCEL CORRUPTION\n\n";
        echo "Issues Summary:\n";
        echo "- Participant records with issues: " . count($issuesFound) . "\n";
        echo "- Essay records with issues: " . count($essayIssues) . "\n";
        echo "- Total problematic records: $totalProblematicRecords\n\n";
        
        echo "IMMEDIATE ACTION REQUIRED:\n";
        echo "1. Implement data cleaning in YbbExportController before sending to export API\n";
        echo "2. Focus on essays (they typically have the most issues)\n";
        echo "3. Clean null bytes, control characters, and overly long text\n";
        echo "4. Test export with a small clean dataset first\n\n";
    } else {
        echo "✅ No obvious data corruption issues found in sample data\n\n";
        echo "If you're still getting Excel corruption, check:\n";
        echo "1. Export API response format\n";
        echo "2. Network transmission issues\n";
        echo "3. File encoding during download\n";
        echo "4. Export service configuration\n\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "\nPlease update the database credentials in this script:\n";
    echo "- Host: $host\n";
    echo "- Database: $dbname\n";
    echo "- Username: $username\n";
    echo "- Password: [hidden]\n\n";
    echo "Check your app/Config/Database.php for the correct credentials.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== END INVESTIGATION ===\n";
?>
