<?php

require_once __DIR__ . '/vendor/autoload.php';

try {
    $db = \Config\Database::connect();
    
    echo "=== Program Essays Structure ===\n";
    $query = $db->query("DESCRIBE program_essays");
    foreach ($query->getResultArray() as $row) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
    echo "\n=== Participant Essays Structure ===\n";
    $query = $db->query("DESCRIBE participant_essays");
    foreach ($query->getResultArray() as $row) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
    echo "\n=== Sample Program Essay Count ===\n";
    $query = $db->query("SELECT program_id, COUNT(*) as essay_count FROM program_essays WHERE is_deleted = 0 GROUP BY program_id ORDER BY program_id DESC LIMIT 5");
    foreach ($query->getResultArray() as $row) {
        echo "Program {$row['program_id']}: {$row['essay_count']} essays\n";
    }
    
    echo "\n=== Sample Program Essays ===\n";
    $query = $db->query("SELECT id, program_id, question, order_number FROM program_essays WHERE is_deleted = 0 ORDER BY program_id DESC, order_number ASC LIMIT 10");
    foreach ($query->getResultArray() as $row) {
        echo "Program {$row['program_id']}, Order {$row['order_number']}: " . substr($row['question'], 0, 50) . "...\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
