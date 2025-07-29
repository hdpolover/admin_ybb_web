<?php

require_once 'vendor/autoload.php';

// Set up basic CodeIgniter environment for testing
define('APPPATH', __DIR__ . '/app/');
define('SYSTEMPATH', __DIR__ . '/system/');
define('ROOTPATH', __DIR__ . '/');
define('WRITEPATH', __DIR__ . '/writable/');

// Simple test to simulate what happens in the export controller
try {
    echo "Testing YBB Export Controller simulation...\n";
    
    // Simulate session data
    $_SESSION['current_program'] = 1;
    $_POST['template'] = 'standard';
    
    echo "Session program ID: " . (isset($_SESSION['current_program']) ? $_SESSION['current_program'] : 'NOT SET') . "\n";
    echo "POST data: " . print_r($_POST, true) . "\n";
    
    // Test database connection with actual config
    $host = '194.163.42.101';
    $database = 'u1437096_ybb_master_app_db';
    $username = 'u1437096_ybb_master_app_admin_user';
    $password = '7J8*^dFEa&lN';

    echo "Testing database connection to: $host\n";
    
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    echo "Database connection: SUCCESS\n";
    
    // Test a simple query
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM participants WHERE program_id = ?");
    $stmt->execute([1]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Participants found for program 1: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
