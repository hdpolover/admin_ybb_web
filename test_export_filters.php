<?php
// Test specific export filters
echo "=== Export Filters Test ===\n\n";

// Test the actual filter query logic
echo "1. Testing Category Filter (fully_funded):\n";
echo "   Query: SELECT * FROM participants WHERE category = 'fully_funded' AND program_id = 2\n";

echo "\n2. Testing Form Status Filter (form_status = 2):\n";
echo "   Query: SELECT participants.* FROM participants \n";
echo "          LEFT JOIN participant_statuses ON participant_statuses.participant_id = participants.id\n";
echo "          WHERE participant_statuses.form_status = '2' AND participants.program_id = 2\n";

echo "\n3. Testing Date Range Filter (2024-01-01 to 2024-12-31):\n";
echo "   Query: WHERE DATE(participants.created_at) >= '2024-01-01' AND DATE(participants.created_at) <= '2024-12-31'\n";

echo "\n4. Testing Payment Status Filter (success):\n";
echo "   Query: WHERE participants.id IN (SELECT participant_id FROM payments WHERE status = 2 AND is_deleted = 0)\n";

echo "\n5. Testing Program Payment Filter (program_payment_id = 1):\n";
echo "   Query: WHERE participants.id IN (SELECT participant_id FROM payments WHERE program_payment_id = 1 AND status = 2 AND is_deleted = 0)\n";

echo "\n6. Testing Limit Filter (100):\n";
echo "   Query: LIMIT 100\n";

echo "\n=== Potential Issues Analysis ===\n";

$issues = [
    "1. JOIN with participant_statuses table - Check if all participants have corresponding records",
    "2. Date range parsing - JavaScript daterangepicker format vs PHP parsing",
    "3. Payment table relationships - Check if payments table exists and has correct structure",
    "4. Form data collection - Check if JavaScript is collecting all form fields correctly",
    "5. CSRF token handling - Make sure form submission includes proper CSRF token",
    "6. Request method handling - Verify both GET and POST parameter handling"
];

foreach ($issues as $issue) {
    echo "   ⚠ {$issue}\n";
}

echo "\n=== Debugging Steps ===\n";

$steps = [
    "1. Add console.log debugging to enhanced-export-manager.js to see collected form data",
    "2. Add log_message debugging to Participants.php getExportFilters() method",
    "3. Add log_message debugging to Participants.php applyExportFilters() method", 
    "4. Test individual filters one by one to isolate which ones aren't working",
    "5. Check browser network tab to see actual request data being sent",
    "6. Check CodeIgniter logs for any SQL errors or warnings"
];

foreach ($steps as $step) {
    echo "   📋 {$step}\n";
}

echo "\n=== Quick Database Checks ===\n";

// These would need to be run manually in the database
$checks = [
    "SELECT COUNT(*) FROM participants WHERE category = 'fully_funded' AND program_id = 2;",
    "SELECT COUNT(*) FROM participants p LEFT JOIN participant_statuses ps ON ps.participant_id = p.id WHERE ps.form_status = 2 AND p.program_id = 2;",
    "SELECT COUNT(*) FROM participants WHERE DATE(created_at) >= '2024-01-01' AND DATE(created_at) <= '2024-12-31' AND program_id = 2;",
    "SELECT COUNT(*) FROM payments WHERE status = 2 AND is_deleted = 0;",
    "SELECT * FROM payments LIMIT 5;"
];

foreach ($checks as $i => $check) {
    echo "   " . ($i + 1) . ". {$check}\n";
}

echo "\n=== End Test ===\n";
?>
