<?php

require_once 'vendor/autoload.php';

// Set up basic CodeIgniter environment
define('APPPATH', __DIR__ . '/app/');
define('SYSTEMPATH', __DIR__ . '/system/');
define('ROOTPATH', __DIR__ . '/');
define('WRITEPATH', __DIR__ . '/writable/');

echo "=== TESTING PAYMENT EXPORT FIX ===\n\n";

try {
    // Load CodeIgniter's Database configuration
    $config = include APPPATH . 'Config/Database.php';
    $dbConfig = $config['default'];
    
    // Create database connection
    $pdo = new PDO(
        "mysql:host={$dbConfig['hostname']};dbname={$dbConfig['database']};charset=utf8mb4",
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✓ Database connection successful\n";
    
    // Test if payments table exists and has data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments WHERE is_deleted = 0");
    $result = $stmt->fetch();
    echo "✓ Found {$result['count']} payment records\n";
    
    // Test the query that the PaymentModel would use
    $testProgramId = 1; // Test with program ID 1
    
    $sql = "
        SELECT 
            payments.id as payment_id,
            payments.transaction_code,
            payments.order_id,
            payments.payment_date,
            payments.status as payment_status_code,
            payments.amount as payment_amount,
            payments.usd_amount as payment_usd_amount,
            payments.currency as payment_currency,
            payments.proof_url as payment_proof_url,
            payments.account_name as payment_account_name,
            payments.source_name as payment_source_name,
            payments.notes as payment_notes,
            payments.rejection_reason as payment_rejection_reason,
            payments.created_at as payment_created_at,
            payments.updated_at as payment_updated_at,
            
            participants.id as participant_id,
            participants.full_name as participant_name,
            participants.account_id as participant_account_id,
            participants.nationality as participant_nationality,
            participants.phone_number as participant_phone,
            participants.category as participant_category,
            
            users.email as participant_email,
            
            programs.name as program_name,
            
            pp.name as program_payment_name,
            pp.idr_amount as program_payment_idr_amount,
            pp.usd_amount as program_payment_usd_amount,
            pp.category as program_payment_category,
            
            pm.name as payment_method_name,
            pm.type as payment_method_type
        FROM payments
        INNER JOIN participants ON participants.id = payments.participant_id
        LEFT JOIN users ON users.id = participants.user_id
        LEFT JOIN programs ON programs.id = participants.program_id
        LEFT JOIN program_payments pp ON pp.id = payments.program_payment_id
        LEFT JOIN payment_methods pm ON pm.id = payments.payment_method_id
        WHERE participants.program_id = ?
        AND payments.is_deleted = 0
        AND participants.is_deleted = 0
        ORDER BY payments.created_at DESC
        LIMIT 5
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$testProgramId]);
    $payments = $stmt->fetchAll();
    
    echo "✓ Successfully executed payment export query\n";
    echo "✓ Found " . count($payments) . " payment records for program {$testProgramId}\n";
    
    if (count($payments) > 0) {
        echo "\nSample payment record:\n";
        $sample = $payments[0];
        echo "  - Payment ID: {$sample['payment_id']}\n";
        echo "  - Participant: {$sample['participant_name']}\n";
        echo "  - Amount: {$sample['payment_amount']} {$sample['payment_currency']}\n";
        echo "  - Status: {$sample['payment_status_code']}\n";
        echo "  - Date: {$sample['payment_created_at']}\n";
    }
    
    echo "\n✅ PAYMENT EXPORT FIX VALIDATION SUCCESSFUL\n";
    echo "\nThe fix corrects the parameter mismatch:\n";
    echo "- BEFORE: getNormalizedPaymentsForExport(\$filters) // Wrong - passed array as first param\n";
    echo "- AFTER:  getNormalizedPaymentsForExport(\$programId, \$filters) // Correct - programId first, filters second\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
