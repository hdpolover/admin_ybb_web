<?php
echo "=== CHECKING PARTICIPANTS TABLE STRUCTURE ===\n\n";

try {
    $host = '194.163.42.101';
    $dbname = 'u1437096_ybb_master_app_db';
    $username = 'u1437096_ybb_master_app_admin_user';
    $password = '7J8*^dFEa&lN';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Database connection successful\n\n";
    
    // Check participants table structure
    echo "PARTICIPANTS TABLE STRUCTURE:\n";
    echo "=" . str_repeat("=", 35) . "\n";
    
    $stmt = $pdo->query("DESCRIBE participants");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) " . 
             ($column['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . 
             ($column['Key'] ? " [{$column['Key']}]" : '') . "\n";
    }
    
    echo "\n\nPARTICIPANT_ESSAYS TABLE STRUCTURE:\n";
    echo "=" . str_repeat("=", 38) . "\n";
    
    $stmt = $pdo->query("DESCRIBE participant_essays");
    $essayColumns = $stmt->fetchAll();
    
    foreach ($essayColumns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) " . 
             ($column['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . 
             ($column['Key'] ? " [{$column['Key']}]" : '') . "\n";
    }
    
    echo "\n\nSAMPLE PARTICIPANT DATA:\n";
    echo "=" . str_repeat("=", 25) . "\n";
    
    // Get sample data with existing columns
    $stmt = $pdo->query("SELECT * FROM participants WHERE is_deleted = 0 LIMIT 2");
    $samples = $stmt->fetchAll();
    
    if (!empty($samples)) {
        foreach ($samples as $index => $sample) {
            echo "Sample " . ($index + 1) . ":\n";
            foreach ($sample as $field => $value) {
                $displayValue = $value;
                if (strlen($displayValue) > 50) {
                    $displayValue = substr($displayValue, 0, 47) . "...";
                }
                echo "  $field: " . ($displayValue ?: '[NULL/Empty]') . "\n";
            }
            echo "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
