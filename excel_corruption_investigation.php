<?php
echo "=== EXCEL EXPORT DATA CORRUPTION INVESTIGATION ===\n\n";

try {
    $host = '194.163.42.101';
    $dbname = 'u1437096_ybb_master_app_db';
    $username = 'u1437096_ybb_master_app_admin_user';
    $password = '7J8*^dFEa&lN';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Database connection successful\n\n";
    
    echo "⚠️  CRITICAL FINDING: Database Character Set is 'latin1'\n";
    echo "This is likely the ROOT CAUSE of your Excel corruption issues!\n\n";
    
    echo "1. DATABASE ENCODING ISSUES\n";
    echo "=" . str_repeat("=", 30) . "\n\n";
    
    $encodingStmt = $pdo->query("SELECT @@character_set_database, @@collation_database");
    $encoding = $encodingStmt->fetch();
    echo "❌ Database Character Set: " . $encoding['@@character_set_database'] . "\n";
    echo "❌ Database Collation: " . $encoding['@@collation_database'] . "\n\n";
    
    echo "This latin1 encoding cannot properly handle:\n";
    echo "- Unicode characters (emojis, special symbols)\n";
    echo "- Non-Latin scripts (Arabic, Chinese, etc.)\n";
    echo "- Extended ASCII characters\n\n";
    
    echo "2. CHECKING FOR PROBLEMATIC CHARACTERS IN PARTICIPANT DATA\n";
    echo "=" . str_repeat("=", 58) . "\n\n";
    
    // Get participants with potential issues
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.full_name,
            p.account_id,
            p.current_address,
            p.origin_address,
            p.instagram_account,
            p.source_account_name,
            p.experiences,
            p.achievements,
            p.nationality,
            p.occupation,
            p.institution,
            p.organizations,
            p.disease_history,
            p.phone_number,
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
    
    echo "Analyzing " . count($participants) . " participants for corruption issues...\n\n";
    
    $totalIssues = 0;
    $issuesFound = [];
    
    foreach ($participants as $participant) {
        $participantIssues = [];
        $id = $participant['id'];
        
        // Text fields to check
        $textFields = [
            'full_name', 'current_address', 'origin_address', 'instagram_account', 
            'source_account_name', 'experiences', 'achievements', 'nationality', 
            'occupation', 'institution', 'organizations', 'disease_history', 'phone_number'
        ];
        
        foreach ($textFields as $field) {
            $value = $participant[$field];
            if ($value === null || $value === '') continue;
            
            // 1. Check for null bytes (MAJOR Excel killer)
            if (strpos($value, "\0") !== false) {
                $participantIssues[] = "🚨 NULL BYTE in $field - Will corrupt Excel file!";
            }
            
            // 2. Check for control characters (except newlines/tabs)
            if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
                $participantIssues[] = "⚠️  Control characters in $field";
            }
            
            // 3. Check for non-ASCII characters that might be corrupted by latin1
            if (preg_match('/[^\x00-\x7F]/', $value)) {
                $participantIssues[] = "🌐 Non-ASCII characters in $field (may be corrupted by latin1 encoding)";
                
                // Show some examples of the problematic characters
                preg_match_all('/[^\x00-\x7F]/', $value, $matches);
                if (!empty($matches[0])) {
                    $uniqueChars = array_unique($matches[0]);
                    $charList = implode(', ', array_slice($uniqueChars, 0, 5));
                    $participantIssues[] = "   → Found characters: $charList";
                }
            }
            
            // 4. Check for Excel formula injection
            if (preg_match('/^[=+\-@]/', $value)) {
                $participantIssues[] = "📋 Excel formula injection risk in $field (starts with: " . substr($value, 0, 3) . ")";
            }
            
            // 5. Check for very long fields
            if (strlen($value) > 32767) {
                $participantIssues[] = "📏 $field too long (" . strlen($value) . " chars) - Excel limit exceeded";
            }
            
            // 6. Check for Unicode BOM and other problematic Unicode
            if (preg_match('/[\x{FEFF}\x{200B}-\x{200D}\x{FFFE}\x{FFFF}]/u', $value)) {
                $participantIssues[] = "🔤 Problematic Unicode characters in $field (BOM, zero-width, etc.)";
            }
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
        echo "✅ No character-level issues found in participant records\n\n";
    } else {
        echo "🚨 FOUND $totalIssues CHARACTER ISSUES IN " . count($issuesFound) . " PARTICIPANTS:\n\n";
        foreach ($issuesFound as $id => $info) {
            echo "Participant ID: $id (Program: {$info['program_id']})\n";
            echo "Name: " . ($info['name'] ?: 'N/A') . "\n";
            echo "Account: " . ($info['account_id'] ?: 'N/A') . "\n";
            echo "Issues:\n";
            foreach ($info['issues'] as $issue) {
                echo "  $issue\n";
            }
            echo "\n";
        }
    }
    
    echo "3. CHECKING ESSAY DATA FOR CORRUPTION ISSUES\n";
    echo "=" . str_repeat("=", 45) . "\n\n";
    
    // Check essays (most likely to have problematic characters)
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
    
    echo "Analyzing " . count($essays) . " essay answers for corruption issues...\n\n";
    
    $essayIssues = [];
    $totalEssayIssues = 0;
    
    foreach ($essays as $essay) {
        $issues = [];
        $answer = $essay['answer'];
        
        // Check for null bytes (critical)
        if (strpos($answer, "\0") !== false) {
            $issues[] = "🚨 Contains NULL bytes - WILL CORRUPT EXCEL!";
        }
        
        // Check for very long answers
        if ($essay['answer_length'] > 32767) {
            $issues[] = "📏 Answer too long (" . $essay['answer_length'] . " chars) - Excel limit exceeded";
        }
        
        // Check for control characters
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $answer)) {
            $issues[] = "⚠️  Contains control characters";
        }
        
        // Check for non-ASCII (most critical for latin1 database)
        if (preg_match('/[^\x00-\x7F]/', $answer)) {
            $issues[] = "🌐 Contains non-ASCII characters (corrupted by latin1 database)";
            
            // Count how many non-ASCII characters
            preg_match_all('/[^\x00-\x7F]/', $answer, $matches);
            $nonAsciiCount = count($matches[0]);
            $issues[] = "   → $nonAsciiCount non-ASCII characters found";
        }
        
        // Check for Excel formula injection
        if (preg_match('/^[=+\-@]/', $answer)) {
            $issues[] = "📋 Starts with Excel formula character";
        }
        
        // Check for problematic Unicode
        if (preg_match('/[\x{FEFF}\x{200B}-\x{200D}\x{FFFE}\x{FFFF}]/u', $answer)) {
            $issues[] = "🔤 Contains problematic Unicode characters";
        }
        
        if (!empty($issues)) {
            $totalEssayIssues += count($issues);
            $essayIssues[] = [
                'essay_id' => $essay['id'],
                'participant_id' => $essay['participant_id'],
                'program_id' => $essay['program_id'],
                'question' => substr($essay['question'], 0, 50) . '...',
                'answer_length' => $essay['answer_length'],
                'issues' => $issues,
                'answer_preview' => substr($answer, 0, 200) . '...'
            ];
        }
    }
    
    if (empty($essayIssues)) {
        echo "✅ No obvious corruption issues in essay answers\n\n";
    } else {
        echo "🚨 FOUND $totalEssayIssues ISSUES IN " . count($essayIssues) . " ESSAYS:\n\n";
        foreach ($essayIssues as $issue) {
            echo "Essay ID: {$issue['essay_id']} (Participant: {$issue['participant_id']}, Program: {$issue['program_id']})\n";
            echo "Question: {$issue['question']}\n";
            echo "Answer Length: {$issue['answer_length']} chars\n";
            echo "Issues:\n";
            foreach ($issue['issues'] as $problemDesc) {
                echo "  $problemDesc\n";
            }
            echo "Answer Preview: {$issue['answer_preview']}\n\n";
        }
    }
    
    echo "4. SAMPLE PROBLEM DETECTION\n";
    echo "=" . str_repeat("=", 30) . "\n\n";
    
    // Look for specific characters that commonly cause Excel corruption
    echo "Searching for common Excel corruption characters...\n\n";
    
    // Check for null bytes in any text field
    $nullByteQuery = "
        SELECT COUNT(*) as count
        FROM participants 
        WHERE is_deleted = 0 
        AND (
            full_name LIKE '%\0%' OR
            experiences LIKE '%\0%' OR
            achievements LIKE '%\0%' OR
            current_address LIKE '%\0%' OR
            origin_address LIKE '%\0%'
        )
    ";
    
    $nullResult = $pdo->query($nullByteQuery)->fetch();
    if ($nullResult['count'] > 0) {
        echo "🚨 CRITICAL: Found {$nullResult['count']} records with NULL BYTES!\n";
        echo "   This WILL cause Excel corruption!\n\n";
    }
    
    // Check essays for null bytes
    $essayNullQuery = "
        SELECT COUNT(*) as count
        FROM participant_essays 
        WHERE is_deleted = 0 
        AND answer LIKE '%\0%'
    ";
    
    $essayNullResult = $pdo->query($essayNullQuery)->fetch();
    if ($essayNullResult['count'] > 0) {
        echo "🚨 CRITICAL: Found {$essayNullResult['count']} essay records with NULL BYTES!\n";
        echo "   This WILL cause Excel corruption!\n\n";
    }
    
    echo "5. ROOT CAUSE ANALYSIS & SOLUTION\n";
    echo "=" . str_repeat("=", 35) . "\n\n";
    
    $totalProblematicRecords = count($issuesFound) + count($essayIssues);
    
    echo "🔍 PRIMARY SUSPECTS FOR EXCEL CORRUPTION:\n\n";
    
    echo "1. 🚨 LATIN1 DATABASE ENCODING (CRITICAL)\n";
    echo "   - Your database uses latin1 instead of utf8mb4\n";
    echo "   - This corrupts any Unicode characters during storage\n";
    echo "   - Corrupted data then breaks Excel files\n\n";
    
    if ($totalProblematicRecords > 0) {
        echo "2. 📊 DATA CORRUPTION FOUND\n";
        echo "   - Participant records with issues: " . count($issuesFound) . "\n";
        echo "   - Essay records with issues: " . count($essayIssues) . "\n";
        echo "   - Total problematic records: $totalProblematicRecords\n\n";
    }
    
    echo "🛠️  IMMEDIATE FIXES REQUIRED:\n\n";
    
    echo "A. DATABASE ENCODING FIX (CRITICAL):\n";
    echo "   1. Backup your database first!\n";
    echo "   2. Convert database to utf8mb4:\n";
    echo "      ALTER DATABASE u1437096_ybb_master_app_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
    echo "   3. Convert tables to utf8mb4:\n";
    echo "      ALTER TABLE participants CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
    echo "      ALTER TABLE participant_essays CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
    echo "   4. Update Database.php config to use utf8mb4\n\n";
    
    echo "B. DATA CLEANING IN EXPORT CONTROLLER:\n";
    echo "   Add this cleaning function to YbbExportController:\n\n";
    
    echo "```php\n";
    echo "private function cleanDataForExcel(\$data) {\n";
    echo "    foreach (\$data as &\$row) {\n";
    echo "        foreach (\$row as &\$value) {\n";
    echo "            if (is_string(\$value)) {\n";
    echo "                // Remove null bytes (critical!)\n";
    echo "                \$value = str_replace(\"\\0\", '', \$value);\n";
    echo "                \n";  
    echo "                // Remove control characters except newlines/tabs\n";
    echo "                \$value = preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/', '', \$value);\n";
    echo "                \n";
    echo "                // Ensure proper UTF-8 encoding\n";
    echo "                \$value = mb_convert_encoding(\$value, 'UTF-8', 'UTF-8');\n";
    echo "                \n";
    echo "                // Truncate very long fields\n";
    echo "                if (strlen(\$value) > 32767) {\n";
    echo "                    \$value = substr(\$value, 0, 32764) . '...';\n";
    echo "                }\n";
    echo "                \n";
    echo "                // Prevent formula injection\n";
    echo "                if (preg_match('/^[=+\\-@]/', \$value)) {\n";
    echo "                    \$value = \"'\" . \$value;\n";
    echo "                }\n";
    echo "            }\n";
    echo "        }\n";
    echo "    }\n";
    echo "    return \$data;\n";
    echo "}\n```\n\n";
    
    echo "C. APPLY CLEANING IN _getParticipantsData():\n";
    echo "   Call \$data = \$this->cleanDataForExcel(\$data); before returning\n\n";
    
    echo "6. PRIORITY ACTION PLAN:\n";
    echo "=" . str_repeat("=", 25) . "\n\n";
    
    echo "IMMEDIATE (Today):\n";
    echo "1. ✅ Add data cleaning function to YbbExportController\n";
    echo "2. ✅ Test export with cleaned data\n\n";
    
    echo "URGENT (This Week):\n"; 
    echo "1. 🔄 Convert database to utf8mb4 encoding\n";
    echo "2. 🔄 Update Database.php config\n";
    echo "3. 🔄 Test all exports after encoding change\n\n";
    
    echo "FOLLOW-UP:\n";
    echo "1. 📝 Add input validation to prevent future corruption\n";
    echo "2. 🧪 Regular data integrity checks\n";
    echo "3. 📊 Monitor export success rates\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "=== END INVESTIGATION ===\n";
?>
