<?php

try {
    $pdo = new PDO('mysql:host=194.163.42.101;dbname=u1437096_ybb_master_app_db;charset=utf8mb4', 
                   'u1437096_ybb_master_app_admin_user', '7J8*^dFEa&lN');
    
    echo "Checking participants table columns for potential GROUP BY issues:\n\n";
    
    $stmt = $pdo->query('SHOW COLUMNS FROM participants');
    $columns = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }
    
    // List columns that might be missing from GROUP BY
    $expectedColumns = [
        'phone_flag', 'emergency_phone_flag', 'emergency_country_code', 
        'emergency_account', 'contact_relation'
    ];
    
    echo "Checking for expected columns:\n";
    foreach ($expectedColumns as $col) {
        if (in_array($col, $columns)) {
            echo "✓ $col - EXISTS\n";
        } else {
            echo "✗ $col - MISSING\n";
        }
    }
    
    echo "\nAll participants columns:\n";
    foreach ($columns as $col) {
        echo "- $col\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
