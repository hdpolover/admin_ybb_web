<?php
require 'preload.php';

$db = \Config\Database::connect();

// Check successful payments
$query = $db->query('SELECT COUNT(*) as total FROM payments WHERE status = 2');
$result = $query->getRow();
echo "Total successful payments: " . $result->total . "\n";

// Check payments by type
$query = $db->query('
    SELECT pp.name as payment_type, COUNT(*) as count 
    FROM payments p 
    JOIN program_payments pp ON p.program_payment_id = pp.id 
    WHERE p.status = 2 
    GROUP BY pp.name
');
$results = $query->getResult();

echo "Successful payments by type:\n";
foreach($results as $row) {
    echo "- {$row->payment_type}: {$row->count}\n";
}

// Check participants with payments for program 1
$query = $db->query('
    SELECT COUNT(DISTINCT p.participant_id) as count 
    FROM payments p 
    JOIN program_payments pp ON p.program_payment_id = pp.id 
    WHERE p.status = 2 AND pp.program_id = 1
');
$result = $query->getRow();
echo "Participants with successful payments in program 1: " . $result->count . "\n";
?>
