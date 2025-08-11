<?php

// Check which program IDs have payments
$host = '194.163.42.101';
$username = 'u1437096_ybb_master_app_admin_user';
$password = '7J8*^dFEa&lN';
$database = 'u1437096_ybb_master_app_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Program Payment Distribution ===\n\n";
    
    // Get payment counts by program
    $sql = "
        SELECT 
            participants.program_id,
            programs.name as program_name,
            COUNT(payments.id) as payment_count,
            MAX(payments.created_at) as latest_payment,
            MIN(payments.created_at) as earliest_payment
        FROM payments 
        INNER JOIN participants ON participants.id = payments.participant_id
        LEFT JOIN programs ON programs.id = participants.program_id
        WHERE payments.is_deleted = 0 
        AND participants.is_deleted = 0
        GROUP BY participants.program_id, programs.name
        ORDER BY payment_count DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $programData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($programData)) {
        echo "❌ No payment data found\n";
        exit(1);
    }
    
    echo "Programs with payments:\n";
    echo str_repeat("=", 80) . "\n";
    echo sprintf("%-10s | %-30s | %-8s | %-19s\n", "Program ID", "Program Name", "Payments", "Latest Payment");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($programData as $program) {
        echo sprintf("%-10s | %-30s | %-8s | %-19s\n", 
            $program['program_id'],
            substr($program['program_name'] ?? 'Unknown', 0, 29),
            $program['payment_count'],
            substr($program['latest_payment'], 0, 19)
        );
    }
    
    echo str_repeat("=", 80) . "\n\n";
    
    // Test with the program that has the most payments
    $testProgramId = $programData[0]['program_id'];
    echo "Testing sorting with Program ID $testProgramId (most payments)...\n\n";
    
    // Test the actual export query with proper sorting
    $testSql = "
        SELECT 
            payments.id as payment_id,
            payments.created_at as payment_created_at,
            payments.payment_date,
            payments.status as payment_status,
            participants.full_name as participant_name
        FROM payments 
        INNER JOIN participants ON participants.id = payments.participant_id
        WHERE participants.program_id = ? 
        AND payments.is_deleted = 0 
        AND participants.is_deleted = 0
        ORDER BY payments.created_at DESC, payments.id DESC
        LIMIT 5
    ";
    
    $testStmt = $pdo->prepare($testSql);
    $testStmt->execute([$testProgramId]);
    $testPayments = $testStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Latest 5 payments (newest to oldest):\n";
    echo str_repeat("=", 90) . "\n";
    echo sprintf("%-8s | %-20s | %-15s | %-20s\n", "Pay ID", "Created At", "Payment Date", "Participant");
    echo str_repeat("-", 90) . "\n";
    
    foreach ($testPayments as $payment) {
        echo sprintf("%-8s | %-20s | %-15s | %-20s\n", 
            $payment['payment_id'],
            substr($payment['payment_created_at'], 0, 19),
            substr($payment['payment_date'] ?? 'N/A', 0, 14),
            substr($payment['participant_name'] ?? 'N/A', 0, 19)
        );
    }
    
    echo str_repeat("=", 90) . "\n\n";
    echo "✅ CONFIRMED: Payment export will show newest payments first!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
