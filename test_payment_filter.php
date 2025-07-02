<?php

require_once 'vendor/autoload.php';
require_once 'app/Config/Paths.php';

// Initialize CodeIgniter
$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = require realpath($bootstrap) ?: $bootstrap;

// Simulate a request with session
$_SESSION['current_program'] = 1; // Set a test program ID
$_GET['payment_filter'] = 'any_payment'; // Test payment filter
$_GET['draw'] = 1;
$_GET['start'] = 0;
$_GET['length'] = 25;

// Initialize the controller
$controller = new \App\Controllers\Certificates();

// Mock the request object to simulate parameters
$request = \Config\Services::request();

echo "<h1>Testing Payment Filter Functionality</h1>";

try {
    // Test with award ID 1 (assuming it exists)
    $awardId = 1;
    
    echo "<h3>Testing getAvailableParticipantsData with payment_filter='any_payment'</h3>";
    
    // Call the method
    $response = $controller->getAvailableParticipantsData($awardId);
    
    echo "<p>Method executed successfully - check logs for details</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}

echo "<h3>Current Log Contents:</h3>";
$logFile = 'writable/logs/log-' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    $logContents = file_get_contents($logFile);
    echo "<pre>" . htmlspecialchars($logContents) . "</pre>";
} else {
    echo "<p>Log file not found: $logFile</p>";
}

?>
