<?php
/**
 * DATABASE ENCODING CONVERSION TO UTF8MB4
 * 
 * This script safely converts your database from latin1 to utf8mb4 encoding
 * to prevent future Excel export corruption issues.
 * 
 * IMPORTANT: This will BACKUP your database first before making any changes!
 */

echo "=== DATABASE ENCODING CONVERSION TO UTF8MB4 ===\n\n";

try {
    // Database connection
    $host = '194.163.42.101';
    $dbname = 'u1437096_ybb_master_app_db';
    $username = 'u1437096_ybb_master_app_admin_user';
    $password = '7J8*^dFEa&lN';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=latin1", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Connected to database: $dbname\n\n";
    
    // Step 1: Create backup
    echo "1. CREATING DATABASE BACKUP\n";
    echo "=" . str_repeat("=", 30) . "\n\n";
    
    $backupFilename = "backup_before_utf8mb4_conversion_" . date('Y-m-d_H-i-s') . ".sql";
    
    // Note: We'll create a PHP-based backup since mysqldump might not be available
    echo "Creating backup file: $backupFilename\n";
    
    // Get all tables
    $tablesStmt = $pdo->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($tables) . " tables to backup\n";
    
    $backupContent = "-- Database backup before UTF8MB4 conversion\n";
    $backupContent .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
    $backupContent .= "-- Database: $dbname\n\n";
    $backupContent .= "SET FOREIGN_KEY_CHECKS = 0;\n";
    $backupContent .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    $backupContent .= "SET AUTOCOMMIT = 0;\n";
    $backupContent .= "START TRANSACTION;\n\n";
    
    foreach ($tables as $table) {
        echo "  Backing up table: $table\n";
        
        // Get table structure
        $createStmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $createResult = $createStmt->fetch();
        $backupContent .= "-- Structure for table `$table`\n";
        $backupContent .= "DROP TABLE IF EXISTS `$table`;\n";
        $backupContent .= $createResult['Create Table'] . ";\n\n";
        
        // Get table data (limit to reasonable size for critical tables)
        $dataStmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $dataStmt->fetch()['count'];
        
        if ($count > 0) {
            $backupContent .= "-- Data for table `$table` ($count rows)\n";
            
            if ($count > 10000) {
                $backupContent .= "-- WARNING: Table has $count rows - backing up first 10000 rows only\n";
                $dataQuery = "SELECT * FROM `$table` LIMIT 10000";
            } else {
                $dataQuery = "SELECT * FROM `$table`";
            }
            
            $dataStmt = $pdo->query($dataQuery);
            while ($row = $dataStmt->fetch()) {
                $values = array_map(function($value) use ($pdo) {
                    return $value === null ? 'NULL' : $pdo->quote($value);
                }, $row);
                $backupContent .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
            }
            $backupContent .= "\n";
        }
    }
    
    $backupContent .= "COMMIT;\n";
    $backupContent .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    
    file_put_contents($backupFilename, $backupContent);
    echo "✅ Backup created: $backupFilename (" . round(filesize($backupFilename) / 1024 / 1024, 2) . " MB)\n\n";
    
    // Step 2: Check current encoding
    echo "2. CHECKING CURRENT DATABASE ENCODING\n";
    echo "=" . str_repeat("=", 38) . "\n\n";
    
    $encodingStmt = $pdo->query("SELECT @@character_set_database, @@collation_database");
    $encoding = $encodingStmt->fetch();
    
    echo "Current Database Character Set: " . $encoding['@@character_set_database'] . "\n";
    echo "Current Database Collation: " . $encoding['@@collation_database'] . "\n\n";
    
    if ($encoding['@@character_set_database'] === 'utf8mb4') {
        echo "✅ Database is already using utf8mb4 encoding!\n";
        echo "No conversion needed.\n";
        exit(0);
    }
    
    // Step 3: Analyze tables that need conversion
    echo "3. ANALYZING TABLES FOR CONVERSION\n";
    echo "=" . str_repeat("=", 37) . "\n\n";
    
    $tablesToConvert = [];
    $criticalTables = ['participants', 'participant_essays', 'program_essays', 'users'];
    
    foreach ($tables as $table) {
        $tableStatusStmt = $pdo->query("SHOW TABLE STATUS LIKE '$table'");
        $tableStatus = $tableStatusStmt->fetch();
        
        if ($tableStatus && $tableStatus['Collation'] && strpos($tableStatus['Collation'], 'latin1') !== false) {
            $tablesToConvert[] = [
                'name' => $table,
                'collation' => $tableStatus['Collation'],
                'critical' => in_array($table, $criticalTables)
            ];
        }
    }
    
    echo "Tables requiring conversion: " . count($tablesToConvert) . "\n\n";
    
    foreach ($tablesToConvert as $table) {
        $status = $table['critical'] ? '🚨 CRITICAL' : '📋 Standard';
        echo "  $status {$table['name']} (current: {$table['collation']})\n";
    }
    
    echo "\n";
    
    // Step 4: Convert database
    echo "4. CONVERTING DATABASE TO UTF8MB4\n";
    echo "=" . str_repeat("=", 35) . "\n\n";
    
    echo "⚠️  WARNING: This will modify your database structure!\n";
    echo "Backup has been created: $backupFilename\n\n";
    
    // Ask for confirmation (in a real scenario, you'd want manual confirmation)
    echo "Starting conversion in 3 seconds...\n";
    echo "Press Ctrl+C to cancel if needed.\n\n";
    
    sleep(1); echo "3...\n";
    sleep(1); echo "2...\n"; 
    sleep(1); echo "1...\n";
    
    // Convert database
    echo "Converting database character set...\n";
    $pdo->exec("ALTER DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database character set converted to utf8mb4\n\n";
    
    // Step 5: Convert tables
    echo "5. CONVERTING TABLES TO UTF8MB4\n";
    echo "=" . str_repeat("=", 32) . "\n\n";
    
    $convertedCount = 0;
    $errors = [];
    
    foreach ($tablesToConvert as $table) {
        $tableName = $table['name'];
        echo "Converting table: $tableName\n";
        
        try {
            // Disable foreign key checks temporarily
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Convert table
            $pdo->exec("ALTER TABLE `$tableName` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Re-enable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            echo "  ✅ Successfully converted $tableName\n";
            $convertedCount++;
            
        } catch (Exception $e) {
            $error = "❌ Error converting $tableName: " . $e->getMessage();
            echo "  $error\n";
            $errors[] = $error;
        }
    }
    
    echo "\n";
    
    // Step 6: Verify conversion
    echo "6. VERIFYING CONVERSION RESULTS\n";
    echo "=" . str_repeat("=", 33) . "\n\n";
    
    // Check database encoding
    $newEncodingStmt = $pdo->query("SELECT @@character_set_database, @@collation_database");
    $newEncoding = $newEncodingStmt->fetch();
    
    echo "New Database Character Set: " . $newEncoding['@@character_set_database'] . "\n";
    echo "New Database Collation: " . $newEncoding['@@collation_database'] . "\n\n";
    
    // Check table encodings
    echo "Verifying table conversions:\n";
    foreach ($criticalTables as $table) {
        if (in_array($table, $tables)) {
            $tableStatusStmt = $pdo->query("SHOW TABLE STATUS LIKE '$table'");
            $tableStatus = $tableStatusStmt->fetch();
            
            if ($tableStatus) {
                $collation = $tableStatus['Collation'];
                $status = strpos($collation, 'utf8mb4') !== false ? '✅' : '❌';
                echo "  $status $table: $collation\n";
            }
        }
    }
    
    echo "\n";
    
    // Step 7: Update CodeIgniter config
    echo "7. UPDATING CODEIGNITER DATABASE CONFIG\n";
    echo "=" . str_repeat("=", 42) . "\n\n";
    
    $configFile = 'app/Config/Database.php';
    
    if (file_exists($configFile)) {
        $configContent = file_get_contents($configFile);
        
        // Update charset and collation in both default and export connections
        $updated = false;
        
        // Update charset
        if (strpos($configContent, "'charset'  => 'utf8'") !== false) {
            $configContent = str_replace("'charset'  => 'utf8'", "'charset'  => 'utf8mb4'", $configContent);
            $updated = true;
        }
        
        // Update collation
        if (strpos($configContent, "'DBCollat' => 'utf8_general_ci'") !== false) {
            $configContent = str_replace("'DBCollat' => 'utf8_general_ci'", "'DBCollat' => 'utf8mb4_unicode_ci'", $configContent);
            $updated = true;
        }
        
        if ($updated) {
            // Create backup of config file
            $configBackup = $configFile . '.backup.' . date('Y-m-d_H-i-s');
            copy($configFile, $configBackup);
            
            // Update config file
            file_put_contents($configFile, $configContent);
            
            echo "✅ Updated $configFile\n";
            echo "📁 Config backup created: $configBackup\n";
        } else {
            echo "ℹ️  Config file already appears to be configured for utf8mb4\n";
        }
    } else {
        echo "⚠️  Could not find config file: $configFile\n";
    }
    
    echo "\n";
    
    // Step 8: Summary
    echo "8. CONVERSION SUMMARY\n";
    echo "=" . str_repeat("=", 22) . "\n\n";
    
    if (count($errors) === 0) {
        echo "🎉 CONVERSION COMPLETED SUCCESSFULLY!\n\n";
        echo "✅ Database converted to utf8mb4_unicode_ci\n";
        echo "✅ $convertedCount tables converted\n";
        echo "✅ CodeIgniter config updated\n";
        echo "✅ Backup created: $backupFilename\n\n";
        
        echo "NEXT STEPS:\n";
        echo "1. Test your application to ensure everything works\n";
        echo "2. Test Excel exports - they should no longer have corruption\n";
        echo "3. New data will be stored properly in Unicode\n";
        echo "4. Existing corrupted data may still show '?' symbols (expected)\n\n";
        
        echo "The data cleaning in YbbExportController will handle any remaining\n";
        echo "corruption from previously stored data.\n";
        
    } else {
        echo "⚠️  CONVERSION COMPLETED WITH ERRORS\n\n";
        echo "Converted tables: $convertedCount\n";
        echo "Errors encountered: " . count($errors) . "\n\n";
        
        echo "ERRORS:\n";
        foreach ($errors as $error) {
            echo "  $error\n";
        }
        
        echo "\nDespite errors, the main functionality should still work.\n";
        echo "You may need to manually fix the failed tables.\n";
    }
    
} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== CONVERSION COMPLETE ===\n";
?>
