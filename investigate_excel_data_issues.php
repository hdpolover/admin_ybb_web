<?php
echo "=== PARTICIPANT DATA INVESTIGATION FOR EXCEL EXPORT ISSUES ===\n\n";

// Initialize CodeIgniter
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require_once 'vendor/autoload.php';

try {
    // Simple bootstrap
    $app = require_once 'system/bootstrap.php';
    $app->initialize();
    
    $db = \Config\Database::connect();
    
    echo "1. CHECKING FOR PROBLEMATIC CHARACTERS IN PARTICIPANT DATA\n";
    echo "=" . str_repeat("=", 60) . "\n\n";
    
    // Get current program from session or use a test program
    $programId = 7; // Adjust this to your current program
    
    // Sample query to get participants with potential issues
    $query = "
        SELECT 
            p.id,
            p.full_name,
            p.account_id,
            p.email,
            u.email as user_email,
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
            LENGTH(p.full_name) as name_length,
            LENGTH(p.experiences) as exp_length,
            LENGTH(p.achievements) as ach_length
        FROM participants p
        LEFT JOIN users u ON u.id = p.user_id
        WHERE p.program_id = ? 
        AND p.is_deleted = 0
        LIMIT 10
    ";
    
    $participants = $db->query($query, [$programId])->getResultArray();
    
    echo "Found " . count($participants) . " participants to analyze\n\n";
    
    $issues = [];
    
    foreach ($participants as $participant) {
        $participantIssues = [];
        $id = $participant['id'];
        
        // Check for null bytes (major Excel killer)
        foreach ($participant as $field => $value) {
            if ($value && strpos($value, "\0") !== false) {
                $participantIssues[] = "NULL BYTE in $field";
            }
        }
        
        // Check for very long text fields
        if ($participant['exp_length'] > 32767) {
            $participantIssues[] = "Experiences field too long (" . $participant['exp_length'] . " chars) - Excel limit is 32,767";
        }
        
        if ($participant['ach_length'] > 32767) {
            $participantIssues[] = "Achievements field too long (" . $participant['ach_length'] . " chars) - Excel limit is 32,767";
        }
        
        // Check for problematic characters
        $textFields = ['full_name', 'current_address', 'origin_address', 'experiences', 'achievements', 'occupation', 'institution'];
        foreach ($textFields as $field) {
            $value = $participant[$field];
            if ($value) {
                // Check for control characters (except newlines and tabs)
                if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
                    $participantIssues[] = "Control characters in $field";
                }
                
                // Check for non-UTF8 characters
                if (!mb_check_encoding($value, 'UTF-8')) {
                    $participantIssues[] = "Invalid UTF-8 encoding in $field";
                }
                
                // Check for Excel formula injection characters
                if (preg_match('/^[=+\-@]/', $value)) {
                    $participantIssues[] = "Potential Excel formula injection in $field (starts with =, +, -, or @)";
                }
            }
        }
        
        // Check email formats
        if ($participant['user_email'] && !filter_var($participant['user_email'], FILTER_VALIDATE_EMAIL)) {
            $participantIssues[] = "Invalid email format in user_email";
        }
        
        if (!empty($participantIssues)) {
            $issues[$id] = [
                'name' => $participant['full_name'],
                'account_id' => $participant['account_id'],
                'issues' => $participantIssues
            ];
        }
    }
    
    if (empty($issues)) {
        echo "✅ No obvious data issues found in sample participants\n\n";
    } else {
        echo "⚠️  FOUND POTENTIAL ISSUES:\n\n";
        foreach ($issues as $id => $info) {
            echo "Participant ID: $id\n";
            echo "Name: " . ($info['name'] ?: 'N/A') . "\n";
            echo "Account: " . ($info['account_id'] ?: 'N/A') . "\n";
            echo "Issues:\n";
            foreach ($info['issues'] as $issue) {
                echo "  - $issue\n";
            }
            echo "\n";
        }
    }
    
    echo "2. CHECKING PARTICIPANT ESSAYS FOR ISSUES\n";
    echo "=" . str_repeat("=", 50) . "\n\n";
    
    // Check essay data
    $essayQuery = "
        SELECT 
            pae.id,
            pae.participant_id,
            pae.answer,
            LENGTH(pae.answer) as answer_length,
            pe.question
        FROM participant_essays pae
        JOIN program_essays pe ON pe.id = pae.program_essay_id
        JOIN participants p ON p.id = pae.participant_id
        WHERE p.program_id = ? 
        AND pae.is_deleted = 0
        AND pae.answer IS NOT NULL
        AND pae.answer != ''
        ORDER BY LENGTH(pae.answer) DESC
        LIMIT 10
    ";
    
    $essays = $db->query($essayQuery, [$programId])->getResultArray();
    
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
        
        if (!empty($issues)) {
            $essayIssues[] = [
                'essay_id' => $essay['id'],
                'participant_id' => $essay['participant_id'],
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
            echo "Essay ID: {$issue['essay_id']} (Participant: {$issue['participant_id']})\n";
            echo "Question: {$issue['question']}\n";
            echo "Answer Length: {$issue['answer_length']} chars\n";
            echo "Issues:\n";
            foreach ($issue['issues'] as $problemDesc) {
                echo "  - $problemDesc\n";
            }
            echo "Answer Preview: {$issue['answer_preview']}\n\n";
        }
    }
    
    echo "3. CHECKING DATABASE ENCODING AND COLLATION\n";
    echo "=" . str_repeat("=", 45) . "\n\n";
    
    // Check database encoding
    $encodingQuery = "SELECT @@character_set_database, @@collation_database";
    $encoding = $db->query($encodingQuery)->getRowArray();
    echo "Database Character Set: " . $encoding['@@character_set_database'] . "\n";
    echo "Database Collation: " . $encoding['@@collation_database'] . "\n\n";
    
    // Check table encoding
    $tableQuery = "SHOW TABLE STATUS LIKE 'participants'";
    $tableInfo = $db->query($tableQuery)->getRowArray();
    echo "Participants Table Collation: " . ($tableInfo['Collation'] ?? 'Unknown') . "\n\n";
    
    echo "4. SAMPLE DATA CLEANING FUNCTIONS\n";
    echo "=" . str_repeat("=", 35) . "\n\n";
    
    echo "If issues are found, here are some cleaning functions you can use:\n\n";
    
    echo "```php\n";
    echo "// Clean data before sending to export API\n";
    echo "function cleanDataForExcel(\$data) {\n";
    echo "    foreach (\$data as &\$row) {\n";
    echo "        foreach (\$row as &\$value) {\n";
    echo "            if (is_string(\$value)) {\n";
    echo "                // Remove null bytes\n";
    echo "                \$value = str_replace(\"\\0\", '', \$value);\n";
    echo "                \n";
    echo "                // Remove other control characters except newlines/tabs\n";
    echo "                \$value = preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/', '', \$value);\n";
    echo "                \n";
    echo "                // Ensure UTF-8 encoding\n";
    echo "                \$value = mb_convert_encoding(\$value, 'UTF-8', 'UTF-8');\n";
    echo "                \n";
    echo "                // Truncate very long fields\n";
    echo "                if (strlen(\$value) > 32767) {\n";
    echo "                    \$value = substr(\$value, 0, 32764) . '...';\n";
    echo "                }\n";
    echo "                \n";
    echo "                // Prevent formula injection\n";
    echo "                if (preg_match('/^[=+\\-@]/', \$value)) {\n";
    echo "                    \$value = \"'\" . \$value; // Prefix with apostrophe\n";
    echo "                }\n";
    echo "            }\n";
    echo "        }\n";
    echo "    }\n";
    echo "    return \$data;\n";
    echo "}\n";
    echo "```\n\n";
    
    echo "5. RECOMMENDATIONS\n";
    echo "=" . str_repeat("=", 20) . "\n\n";
    
    echo "Based on this analysis:\n\n";
    
    if (!empty($issues) || !empty($essayIssues)) {
        echo "❌ DATA ISSUES FOUND - Implement data cleaning before export\n";
        echo "1. Add data cleaning function to YbbExportController\n";
        echo "2. Clean data in _getParticipantsData() before returning\n";
        echo "3. Pay special attention to essay answers (they tend to have the most issues)\n";
        echo "4. Consider adding validation to prevent problematic data entry\n\n";
    } else {
        echo "✅ No obvious data issues in sample - check other potential causes:\n";
        echo "1. Network issues during API communication\n";
        echo "2. Export service configuration\n";
        echo "3. File permission issues\n";
        echo "4. Memory limits during large exports\n\n";
    }
    
    echo "Next steps:\n";
    echo "1. Run this script with different program IDs\n";
    echo "2. Check the actual export API request/response\n";
    echo "3. Test with a small subset of clean data\n";
    echo "4. Implement data cleaning if issues are found\n\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "=== END INVESTIGATION ===\n";
?>
