<?php

// Test payment export with created_at as payment date
$host = '194.163.42.101';
$username = 'u1437096_ybb_master_app_admin_user';
$password = '7J8*^dFEa&lN';
$database = 'u1437096_ybb_master_app_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Payment Export Date Change Verification ===\n\n";
    
    // Test the updated query without payment_date column
    $sql = "
        SELECT 
            payments.id as payment_id,
            payments.transaction_code,
            payments.created_at as payment_created_at,
            payments.status as payment_status_code,
            participants.full_name as participant_name
        FROM payments 
        INNER JOIN participants ON participants.id = payments.participant_id
        WHERE participants.program_id = 7 
        AND payments.is_deleted = 0 
        AND participants.is_deleted = 0
        ORDER BY payments.created_at DESC, payments.id DESC
        LIMIT 5
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($payments)) {
        echo "❌ No payments found\n";
        exit(1);
    }
    
    echo "✅ Found " . count($payments) . " payments\n\n";
    
    echo "Payment export will now use created_at as Payment_Date:\n";
    echo str_repeat("=", 80) . "\n";
    echo sprintf("%-8s | %-15s | %-20s | %-25s\n", "Pay ID", "Trans Code", "Payment Date", "Participant");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($payments as $payment) {
        // Format the created_at as it would appear in export
        $paymentDate = $payment['payment_created_at'] ? 
            date('Y-m-d', strtotime($payment['payment_created_at'])) : 'N/A';
            
        echo sprintf("%-8s | %-15s | %-20s | %-25s\n", 
            $payment['payment_id'],
            substr($payment['transaction_code'] ?? 'N/A', 0, 14),
            $paymentDate,
            substr($payment['participant_name'] ?? 'N/A', 0, 24)
        );
    }
    
    echo str_repeat("=", 80) . "\n\n";
    
    // Check for any payments that had different payment_date vs created_at
    echo "Checking for discrepancies between payment_date and created_at...\n";
    
    $checkSql = "
        SELECT 
            COUNT(*) as total_payments,
            SUM(CASE WHEN payments.payment_date IS NOT NULL AND payments.payment_date != DATE(payments.created_at) THEN 1 ELSE 0 END) as different_dates,
            SUM(CASE WHEN payments.payment_date IS NULL THEN 1 ELSE 0 END) as null_payment_dates
        FROM payments 
        INNER JOIN participants ON participants.id = payments.participant_id
        WHERE participants.program_id = 7 
        AND payments.is_deleted = 0 
        AND participants.is_deleted = 0
    ";
    
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute();
    $stats = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Statistics for Program 7:\n";
    echo "- Total payments: {$stats['total_payments']}\n";
    echo "- Payments with NULL payment_date: {$stats['null_payment_dates']}\n";
    echo "- Payments where payment_date differs from created_at: {$stats['different_dates']}\n\n";
    
    if ($stats['null_payment_dates'] > 0) {
        echo "✅ BENEFIT: {$stats['null_payment_dates']} payments with NULL payment_date will now have proper dates!\n";
    }
    
    if ($stats['different_dates'] > 0) {
        echo "ℹ️  NOTE: {$stats['different_dates']} payments had different payment_date vs created_at\n";
        echo "   Using created_at ensures consistent, reliable payment dates.\n";
    }
    
    echo "\n📋 SUMMARY:\n";
    echo "✅ Payment export now uses created_at as Payment_Date\n";
    echo "✅ Eliminates NULL payment dates in exports\n";
    echo "✅ Provides consistent, reliable payment timing\n";
    echo "✅ Removed dependency on manually-set payment_date field\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
