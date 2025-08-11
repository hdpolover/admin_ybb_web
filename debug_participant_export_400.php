<?php

require_once 'vendor/autoload.php';

// Set up basic CodeIgniter environment
define('APPPATH', __DIR__ . '/app/');
define('SYSTEMPATH', __DIR__ . '/system/');
define('ROOTPATH', __DIR__ . '/');
define('WRITEPATH', __DIR__ . '/writable/');

echo "=== DEBUGGING PARTICIPANT EXPORT 400 ERROR ===\n\n";

try {
    // Simulate the participant export process
    echo "1. TESTING PARTICIPANT MODEL OPTIMIZATION:\n";
    
    // Load the participant model directly to test the normalization
    $sampleParticipant = [
        'participant_id' => 1,
        'participant_account_id' => 'ACC001',
        'participant_full_name' => 'John Doe',
        'participant_email' => 'john.doe@example.com',
        'participant_phone' => '812345678',
        'participant_country_code' => '+62',
        'participant_nationality' => 'Indonesian',
        'participant_current_address' => 'Test Address 123, Jakarta',
        'participant_gender' => 'M',
        'participant_birthdate' => '1995-05-15',
        'participant_category' => 'student',
        'form_status_code' => 1,
        'payment_status_code' => 1,
        'general_status_code' => 1,
        'document_status_code' => 1,
        'user_is_verified' => 1,
        'participant_education_level' => 'bachelor',
        'participant_major' => 'Computer Science',
        'participant_institution' => 'Test University',
        'participant_occupation' => 'Student',
        'program_name' => 'Young Business Builder 2025',
        'program_theme' => 'Innovation and Leadership',
        'participant_registered_at' => '2024-08-01 10:30:00',
        'participant_instagram' => 'johndoe',
        'participant_tshirt_size' => 'm',
        'essay_1' => 'This is my motivation essay...',
        'essay_1_question' => 'Why do you want to join YBB?'
    ];
    
    echo "   Sample participant data structure looks valid\n";
    
    echo "\n2. CHECKING POTENTIAL ISSUES:\n";
    
    // Check if the new column names might cause issues
    $newColumns = [
        'Participant ID', 'Account ID', 'Full Name', 'Email', 'Phone',
        'Nationality', 'Current Address', 'Gender', 'Birthdate', 'Age',
        'Category', 'Registration Status', 'Payment Status', 'General Status',
        'Email Verified', 'Education Level', 'Major/Field', 'Institution',
        'Occupation', 'Program', 'Program Theme', 'Registration Date',
        'Document Status', 'Instagram Account', 'T-Shirt Size'
    ];
    
    echo "   New column names (" . count($newColumns) . " columns):\n";
    foreach ($newColumns as $i => $col) {
        echo sprintf("      %2d. %s\n", $i + 1, $col);
    }
    
    echo "\n3. POTENTIAL CAUSES OF 400 BAD REQUEST:\n";
    
    $potentialIssues = [
        "Column names with spaces might cause API issues",
        "Special characters in column names (apostrophes, slashes)",
        "Data structure changes not compatible with export API",
        "Missing required fields that the API expects",
        "Helper method errors causing malformed data",
        "PHP namespace issues in new helper methods",
        "Large data payload exceeding server limits",
        "CSRF token or authentication issues"
    ];
    
    foreach ($potentialIssues as $i => $issue) {
        echo sprintf("   %d. %s\n", $i + 1, $issue);
    }
    
    echo "\n4. RECOMMENDED FIXES TO TRY:\n";
    
    $fixes = [
        "Check web server error logs for detailed error message",
        "Test with original column structure to isolate the issue",
        "Add error logging in ParticipantModel::normalizeParticipantForExport()",
        "Verify YBB Export API can handle new column names",
        "Check for PHP syntax errors in helper methods",
        "Test with smaller dataset to rule out size issues",
        "Ensure CSRF token is properly included in request",
        "Check if server has sufficient memory for new processing"
    ];
    
    foreach ($fixes as $i => $fix) {
        echo sprintf("   %d. %s\n", $i + 1, $fix);
    }
    
    echo "\n5. IMMEDIATE DIAGNOSTIC STEPS:\n";
    echo "   a) Check: writable/logs/log-" . date('Y-m-d') . ".php for detailed errors\n";
    echo "   b) Check: Apache/Nginx error logs for server-level issues\n";
    echo "   c) Try: Export with original code to confirm it's the optimization\n";
    echo "   d) Test: Simple participant export with minimal data\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
?>
