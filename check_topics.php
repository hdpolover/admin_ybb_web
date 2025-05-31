<?php

// Database connection details from app/Config/Database.php
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'u1437096_ybb_master_app_db';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Program ID from command line argument
$program_id = isset($argv[1]) ? intval($argv[1]) : null;

// Build query
$sql = "SELECT * FROM abstract_topics";
if ($program_id) {
    $sql .= " WHERE program_id = $program_id";
}

$result = $conn->query($sql);

// Display topics
echo "Abstract Topics:\n";
echo "---------------\n";
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_object()) {
        echo "ID: " . $row->id . "\n";
        echo "Program ID: " . $row->program_id . "\n";
        echo "Name: " . $row->name . "\n";
        echo "Description: " . ($row->description ?? 'N/A') . "\n";
        echo "Status: " . ($row->is_active == 1 ? 'Active' : 'Inactive') . "\n";
        echo "---------------\n";
    }
} else {
    echo "No topics found" . ($program_id ? " for program ID $program_id" : "") . ".\n";
}

$conn->close();
