<?php

// Simple database check without CodeIgniter framework
$host = '194.163.42.101';
$username = 'u1437096_ybb_master_app_admin_user';
$password = '7J8*^dFEa&lN';
$database = 'u1437096_ybb_master_app_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n\n";
    
    // Check if export_requests table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'export_requests'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "❌ export_requests table does not exist!\n";
        echo "The table needs to be created first.\n";
        exit(1);
    }
    
    echo "✓ export_requests table exists\n\n";
    
    // Get table structure
    $stmt = $pdo->query("DESCRIBE export_requests");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current columns in export_requests table:\n";
    $hasColumns = false;
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
        if (in_array($column['Field'], ['batch_processing', 'batch_count'])) {
            $hasColumns = true;
        }
    }
    
    echo "\nChecking for missing columns...\n";
    
    $missingColumns = [];
    $existingColumns = array_column($columns, 'Field');
    
    if (!in_array('batch_processing', $existingColumns)) {
        $missingColumns[] = 'batch_processing';
    }
    
    if (!in_array('batch_count', $existingColumns)) {
        $missingColumns[] = 'batch_count';
    }
    
    if (!empty($missingColumns)) {
        echo "❌ Missing columns: " . implode(', ', $missingColumns) . "\n\n";
        echo "SQL to add missing columns:\n";
        
        if (in_array('batch_processing', $missingColumns)) {
            echo "ALTER TABLE export_requests ADD COLUMN batch_processing TINYINT(1) DEFAULT 0;\n";
        }
        
        if (in_array('batch_count', $missingColumns)) {
            echo "ALTER TABLE export_requests ADD COLUMN batch_count INT DEFAULT NULL;\n";
        }
        
        echo "\nDo you want to add these columns? (y/N): ";
        $response = trim(fgets(STDIN));
        
        if (strtolower($response) === 'y') {
            if (in_array('batch_processing', $missingColumns)) {
                $pdo->exec("ALTER TABLE export_requests ADD COLUMN batch_processing TINYINT(1) DEFAULT 0");
                echo "✓ Added batch_processing column\n";
            }
            
            if (in_array('batch_count', $missingColumns)) {
                $pdo->exec("ALTER TABLE export_requests ADD COLUMN batch_count INT DEFAULT NULL");
                echo "✓ Added batch_count column\n";
            }
            
            echo "\n✅ All missing columns added successfully!\n";
        } else {
            echo "\nColumns not added. You'll need to add them manually.\n";
        }
    } else {
        echo "✅ All required columns exist!\n";
    }
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    echo "\nIf using different database credentials, please update the script.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
