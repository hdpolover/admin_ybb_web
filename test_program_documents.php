<?php

// Simple test script to debug program documents creation
echo "Starting test...\n";

// Test to see the table structure directly with mysqli
try {
    echo "Testing database connection...\n";
    
    // Database connection details - you may need to adjust these
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'ybb_master_app_db';
    
    $mysqli = new mysqli($host, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "Connected successfully!\n";
    
    // Check if table exists and get structure
    $result = $mysqli->query("DESCRIBE program_documents");
    
    if ($result) {
        echo "\nTable structure:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  " . $row['Field'] . " (" . $row['Type'] . ") - " . 
                 ($row['Null'] === 'YES' ? 'nullable' : 'not null') . 
                 ($row['Key'] ? " [" . $row['Key'] . "]" : "") . "\n";
        }
    } else {
        echo "Error describing table: " . $mysqli->error . "\n";
    }
    
    // Check if we have any existing program_documents
    $result = $mysqli->query("SELECT COUNT(*) as count FROM program_documents WHERE is_deleted = 0");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "\nExisting active documents: " . $row['count'] . "\n";
    }
    
    // Check for valid program IDs
    $result = $mysqli->query("SELECT id, name FROM programs LIMIT 5");
    if ($result) {
        echo "\nAvailable programs:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  ID: " . $row['id'] . " - Name: " . $row['name'] . "\n";
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";

?>
