<?php

require_once __DIR__ . '/vendor/autoload.php';

// Initialize CodeIgniter for testing
$app = require_once FCPATH . '../app/Config/Paths.php';
$app = new \CodeIgniter\CodeIgniter($app);
$app->initialize();

use App\Models\ParticipantModel;
use App\Libraries\YbbExport;

echo "=== Testing Participant Export Payload Structure ===\n\n";

try {
    $participantModel = new ParticipantModel();
    
    // Get a small sample of participants (just 5 records)
    echo "1. Getting sample participant data...\n";
    $participants = $participantModel->getNormalizedParticipantsForExport([1], [], 5);
    
    echo "Found " . count($participants) . " participants\n\n";
    
    if (!empty($participants)) {
        echo "2. Sample participant record structure:\n";
        $sampleParticipant = $participants[0];
        
        echo "Keys in normalized participant:\n";
        foreach (array_keys($sampleParticipant) as $key) {
            echo "  - " . $key . "\n";
        }
        
        echo "\n3. Sample values:\n";
        echo "Participant_ID: " . ($sampleParticipant['Participant_ID'] ?? 'N/A') . "\n";
        echo "Full_Name: " . ($sampleParticipant['Full_Name'] ?? 'N/A') . "\n";
        echo "Email: " . ($sampleParticipant['Email'] ?? 'N/A') . "\n";
        
        echo "\n4. Testing JSON encoding...\n";
        $jsonPayload = json_encode($participants);
        
        if ($jsonPayload === false) {
            echo "ERROR: JSON encoding failed!\n";
            echo "JSON Error: " . json_last_error_msg() . "\n";
        } else {
            echo "JSON encoding successful\n";
            echo "Payload size: " . strlen($jsonPayload) . " bytes\n";
            
            // Check for potential problematic characters
            echo "\n5. Checking for potential issues:\n";
            
            // Check for null bytes
            if (strpos($jsonPayload, "\0") !== false) {
                echo "WARNING: Null bytes found in payload\n";
            } else {
                echo "✓ No null bytes found\n";
            }
            
            // Check for very long strings
            $decoded = json_decode($jsonPayload, true);
            $maxLength = 0;
            $longestField = '';
            
            foreach ($decoded as $record) {
                foreach ($record as $field => $value) {
                    if (is_string($value) && strlen($value) > $maxLength) {
                        $maxLength = strlen($value);
                        $longestField = $field;
                    }
                }
            }
            
            echo "Longest field: $longestField ($maxLength characters)\n";
            
            if ($maxLength > 10000) {
                echo "WARNING: Very long field detected (>10KB)\n";
            } else {
                echo "✓ No extremely long fields\n";
            }
            
            // Check for special characters
            $hasSpecialChars = false;
            foreach ($decoded as $record) {
                foreach ($record as $field => $value) {
                    if (is_string($value) && preg_match('/[^\x20-\x7E\x0A\x0D]/', $value)) {
                        $hasSpecialChars = true;
                        break 2;
                    }
                }
            }
            
            if ($hasSpecialChars) {
                echo "INFO: Special/Unicode characters found (may be normal)\n";
            } else {
                echo "✓ Only standard ASCII characters found\n";
            }
        }
        
        echo "\n6. Testing YBB Export Library format...\n";
        
        // Create the same payload structure as YbbExport
        $payload = [
            'data' => $participants,
            'template' => 'standard',
            'format' => 'excel',
            'filename' => 'test_participants.xlsx'
        ];
        
        $finalJson = json_encode($payload);
        if ($finalJson === false) {
            echo "ERROR: Final payload JSON encoding failed!\n";
            echo "JSON Error: " . json_last_error_msg() . "\n";
        } else {
            echo "✓ Final payload JSON encoding successful\n";
            echo "Final payload size: " . strlen($finalJson) . " bytes\n";
            
            // Save sample to file for inspection
            file_put_contents('sample_payload.json', $finalJson);
            echo "✓ Sample payload saved to sample_payload.json\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
