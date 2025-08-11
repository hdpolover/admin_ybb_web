<?php

// Add missing columns to export_requests table
$host = '194.163.42.101';
$username = 'u1437096_ybb_master_app_admin_user';
$password = '7J8*^dFEa&lN';
$database = 'u1437096_ybb_master_app_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n\n";
    
    // Check current columns
    $stmt = $pdo->query("DESCRIBE export_requests");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $existingColumns = array_column($columns, 'Field');
    
    $columnsToAdd = [];
    
    if (!in_array('batch_processing', $existingColumns)) {
        $columnsToAdd[] = [
            'name' => 'batch_processing',
            'sql' => 'ALTER TABLE export_requests ADD COLUMN batch_processing TINYINT(1) DEFAULT 0'
        ];
    }
    
    if (!in_array('batch_count', $existingColumns)) {
        $columnsToAdd[] = [
            'name' => 'batch_count', 
            'sql' => 'ALTER TABLE export_requests ADD COLUMN batch_count INT DEFAULT NULL'
        ];
    }
    
    if (empty($columnsToAdd)) {
        echo "✅ All required columns already exist!\n";
        exit(0);
    }
    
    echo "Adding missing columns...\n\n";
    
    foreach ($columnsToAdd as $column) {
        echo "Adding {$column['name']} column...\n";
        echo "SQL: {$column['sql']}\n";
        
        try {
            $pdo->exec($column['sql']);
            echo "✅ Successfully added {$column['name']} column\n\n";
        } catch (PDOException $e) {
            echo "❌ Failed to add {$column['name']} column: " . $e->getMessage() . "\n\n";
        }
    }
    
    // Verify the columns were added
    echo "Verifying columns were added...\n";
    $stmt = $pdo->query("DESCRIBE export_requests");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $newColumns = array_column($columns, 'Field');
    
    $success = true;
    if (!in_array('batch_processing', $newColumns)) {
        echo "❌ batch_processing column still missing\n";
        $success = false;
    } else {
        echo "✅ batch_processing column exists\n";
    }
    
    if (!in_array('batch_count', $newColumns)) {
        echo "❌ batch_count column still missing\n";
        $success = false;
    } else {
        echo "✅ batch_count column exists\n";
    }
    
    if ($success) {
        echo "\n🎉 All columns added successfully!\n";
        echo "You can now run the export again.\n";
    } else {
        echo "\n❌ Some columns failed to add. Please check manually.\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
