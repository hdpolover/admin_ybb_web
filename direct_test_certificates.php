<?php
// Direct test of the certificate getData endpoint

require_once __DIR__ . '/vendor/autoload.php';

// Start CI framework
$app = \Config\Services::codeigniter();
$app->initialize();

// Set up session data
session_start();
$_SESSION['current_program'] = 8; // Use the program ID that has data
$_SESSION['user_id'] = 1;

echo "<h2>Direct Certificate getData Test</h2>";
echo "<p>Testing URL: /documents/certificates/getData</p>";
echo "<p>Session Program ID: " . ($_SESSION['current_program'] ?? 'Not Set') . "</p>";
echo "<hr>";

try {
    // Create controller instance
    $certificates = new \App\Controllers\Certificates();
    
    // Call getData method
    $response = $certificates->getData();
    
    // Get response as string
    $responseBody = $response->getBody();
    
    echo "<h3>📄 Raw Response:</h3>";
    echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd; max-height:300px; overflow:auto;'>";
    echo htmlspecialchars($responseBody);
    echo "</pre>";
    
    // Parse JSON
    $jsonData = json_decode($responseBody, true);
    
    if ($jsonData === null) {
        echo "<h3 style='color:red;'>❌ JSON Parse Error:</h3>";
        echo "<p>Error: " . json_last_error_msg() . "</p>";
    } else {
        echo "<h3 style='color:green;'>✅ JSON Parsed Successfully</h3>";
        
        if (isset($jsonData['data'])) {
            echo "<p><strong>Data found:</strong> " . count($jsonData['data']) . " records</p>";
            
            if (!empty($jsonData['data'])) {
                echo "<h4>📊 First Record Sample:</h4>";
                echo "<pre style='background:#e8f5e8; padding:10px; border:1px solid #4caf50;'>";
                print_r($jsonData['data'][0]);
                echo "</pre>";
                
                // Check DataTable required columns
                echo "<h4>🔍 Column Validation:</h4>";
                $required = ['title', 'award_type', 'description', 'participants_count', 'progress', 'certificate_status', 'actions'];
                $first = $jsonData['data'][0];
                
                echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
                echo "<tr><th>Column</th><th>Status</th><th>Value Preview</th></tr>";
                
                foreach ($required as $col) {
                    $status = isset($first[$col]) ? "✅ Present" : "❌ Missing";
                    $preview = isset($first[$col]) ? htmlspecialchars(substr($first[$col], 0, 100)) : "N/A";
                    $color = isset($first[$col]) ? "#d4edda" : "#f8d7da";
                    echo "<tr style='background-color:$color;'>";
                    echo "<td><strong>$col</strong></td>";
                    echo "<td>$status</td>";
                    echo "<td>$preview</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p style='color:red;'><strong>❌ No 'data' key found in response</strong></p>";
            echo "<p>Available keys: " . implode(', ', array_keys($jsonData)) . "</p>";
        }
        
        if (isset($jsonData['error'])) {
            echo "<p style='color:red;'><strong>❌ Error in response:</strong> " . $jsonData['error'] . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<h3 style='color:red;'>💥 Exception Caught:</h3>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<h4>Stack Trace:</h4>";
    echo "<pre style='background:#ffe6e6; padding:10px; border:1px solid #ff0000; max-height:200px; overflow:auto;'>";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?>
