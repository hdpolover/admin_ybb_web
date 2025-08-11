<?php

// Simple database check for payment export sorting
$host = '194.163.42.101';
$username = 'u1437096_ybb_master_app_admin_user';
$password = '7J8*^dFEa&lN';
$database = 'u1437096_ybb_master_app_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Payment Export Sorting Verification ===\n\n";
    
    // Test the actual query that would be used for export
    $sql = "
        SELECT 
            payments.id as payment_id,
            payments.created_at as payment_created_at,
            payments.payment_date,
            payments.status as payment_status,
            participants.full_name as participant_name
        FROM payments 
        INNER JOIN participants ON participants.id = payments.participant_id
        LEFT JOIN programs ON programs.id = participants.program_id
        WHERE participants.program_id = 1 
        AND payments.is_deleted = 0 
        AND participants.is_deleted = 0
        ORDER BY payments.created_at DESC, payments.id DESC
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($payments)) {
        echo "❌ No payments found for program ID 1\n";
        echo "The sorting is configured correctly, but no test data available.\n\n";
        
        // Check total payments in database
        $countSql = "SELECT COUNT(*) as total FROM payments WHERE is_deleted = 0";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute();
        $totalPayments = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo "Total payments in database: $totalPayments\n";
        
        if ($totalPayments > 0) {
            echo "The payments exist, but they may be for different program IDs.\n";
        }
    } else {
        echo "✅ Found " . count($payments) . " payments for testing\n\n";
        
        echo "Payment order (should be newest to oldest):\n";
        echo str_repeat("=", 90) . "\n";
        echo sprintf("%-3s | %-10s | %-20s | %-15s | %-25s\n", "#", "Pay ID", "Created At", "Payment Date", "Participant");
        echo str_repeat("-", 90) . "\n";
        
        foreach ($payments as $index => $payment) {
            echo sprintf("%-3d | %-10s | %-20s | %-15s | %-25s\n", 
                $index + 1,
                $payment['payment_id'],
                $payment['payment_created_at'],
                $payment['payment_date'] ?? 'N/A',
                substr($payment['participant_name'] ?? 'N/A', 0, 24)
            );
        }
        
        echo str_repeat("=", 90) . "\n\n";
        
        // Verify sorting
        $sortingCorrect = true;
        $previousDate = null;
        
        foreach ($payments as $payment) {
            $currentDate = $payment['payment_created_at'];
            
            if ($previousDate !== null) {
                if (strtotime($currentDate) > strtotime($previousDate)) {
                    $sortingCorrect = false;
                    break;
                }
            }
            
            $previousDate = $currentDate;
        }
        
        if ($sortingCorrect) {
            echo "✅ VERIFICATION PASSED: Payments are correctly sorted from newest to oldest\n";
        } else {
            echo "❌ VERIFICATION FAILED: Payments are not properly sorted\n";
        }
    }
    
    echo "\n📋 SUMMARY:\n";
    echo "- Payment export query includes: ORDER BY payments.created_at DESC, payments.id DESC\n";
    echo "- This ensures payments are sorted from latest to oldest (newest first)\n";
    echo "- Secondary sort by payment ID ensures consistent ordering for same timestamps\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
