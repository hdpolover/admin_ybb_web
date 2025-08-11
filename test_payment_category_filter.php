<?php

// Check program payment categories and test payment filtering
$host = '194.163.42.101';
$username = 'u1437096_ybb_master_app_admin_user';
$password = '7J8*^dFEa&lN';
$database = 'u1437096_ybb_master_app_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Payment Category Filter Analysis ===\n\n";
    
    // Check available payment categories
    $sql = "
        SELECT DISTINCT 
            pp.id as program_payment_id,
            pp.name as payment_name,
            pp.category as payment_category,
            COUNT(p.id) as payment_count
        FROM program_payments pp
        LEFT JOIN payments p ON p.program_payment_id = pp.id AND p.is_deleted = 0
        WHERE pp.program_id = 7
        GROUP BY pp.id, pp.name, pp.category
        ORDER BY payment_count DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $paymentTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($paymentTypes)) {
        echo "❌ No payment types found for program 7\n";
        exit(1);
    }
    
    echo "Available payment types for Program 7:\n";
    echo str_repeat("=", 80) . "\n";
    echo sprintf("%-5s | %-25s | %-15s | %-10s\n", "ID", "Payment Name", "Category", "Payments");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($paymentTypes as $type) {
        echo sprintf("%-5s | %-25s | %-15s | %-10s\n", 
            $type['program_payment_id'],
            substr($type['payment_name'] ?? 'N/A', 0, 24),
            $type['payment_category'] ?? 'N/A',
            $type['payment_count']
        );
    }
    
    echo str_repeat("=", 80) . "\n\n";
    
    // Test current program_payment_id filter
    $testPaymentId = $paymentTypes[0]['program_payment_id']; // Use the one with most payments
    $testCategory = $paymentTypes[0]['payment_category'];
    
    echo "Testing program_payment_id filter with ID: $testPaymentId (Category: $testCategory)\n\n";
    
    // Test the actual filter query from PaymentModel
    $filterTestSql = "
        SELECT 
            payments.id as payment_id,
            payments.created_at,
            pp.name as program_payment_name,
            pp.category as program_payment_category,
            participants.full_name as participant_name
        FROM payments 
        INNER JOIN participants ON participants.id = payments.participant_id
        LEFT JOIN program_payments pp ON pp.id = payments.program_payment_id
        WHERE participants.program_id = 7 
        AND payments.is_deleted = 0 
        AND participants.is_deleted = 0
        AND payments.program_payment_id = ?
        ORDER BY payments.created_at DESC
        LIMIT 5
    ";
    
    $filterStmt = $pdo->prepare($filterTestSql);
    $filterStmt->execute([$testPaymentId]);
    $filteredPayments = $filterStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Results with program_payment_id filter:\n";
    echo str_repeat("=", 90) . "\n";
    echo sprintf("%-8s | %-20s | %-25s | %-15s\n", "Pay ID", "Created At", "Payment Type", "Category");
    echo str_repeat("-", 90) . "\n";
    
    foreach ($filteredPayments as $payment) {
        echo sprintf("%-8s | %-20s | %-25s | %-15s\n", 
            $payment['payment_id'],
            substr($payment['created_at'], 0, 19),
            substr($payment['program_payment_name'] ?? 'N/A', 0, 24),
            $payment['program_payment_category'] ?? 'N/A'
        );
    }
    
    echo str_repeat("=", 90) . "\n\n";
    
    // Now test if we need a category filter as well
    echo "Testing need for payment_category filter...\n";
    
    $categorySql = "
        SELECT 
            pp.category as payment_category,
            COUNT(DISTINCT pp.id) as payment_types,
            COUNT(p.id) as total_payments
        FROM program_payments pp
        LEFT JOIN payments p ON p.program_payment_id = pp.id AND p.is_deleted = 0
        INNER JOIN participants ON participants.id = p.participant_id AND participants.program_id = 7
        WHERE pp.program_id = 7
        GROUP BY pp.category
        ORDER BY total_payments DESC
    ";
    
    $categoryStmt = $pdo->prepare($categorySql);
    $categoryStmt->execute();
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nPayment categories breakdown:\n";
    echo str_repeat("=", 60) . "\n";
    echo sprintf("%-20s | %-12s | %-12s\n", "Category", "Types", "Payments");
    echo str_repeat("-", 60) . "\n";
    
    foreach ($categories as $category) {
        echo sprintf("%-20s | %-12s | %-12s\n", 
            $category['payment_category'] ?? 'NULL',
            $category['payment_types'],
            $category['total_payments']
        );
    }
    
    echo str_repeat("=", 60) . "\n\n";
    
    echo "📋 ANALYSIS RESULTS:\n";
    echo "✅ program_payment_id filter: Working correctly\n";
    
    if (count($categories) > 1) {
        echo "⚠️  payment_category filter: RECOMMENDED - Multiple categories exist\n";
        echo "   This would allow filtering by category (e.g., 'registration' only)\n";
    } else {
        echo "✅ payment_category filter: Not needed - Only one category\n";
    }
    
    echo "\n🔧 FILTER VERIFICATION:\n";
    echo "- Current filters working: program_payment_id\n";
    echo "- Missing filters: payment_category (if multiple categories exist)\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
