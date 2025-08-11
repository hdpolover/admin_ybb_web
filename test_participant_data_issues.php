<?php

echo "=== Testing Participant Export with Enhanced Debugging ===\n\n";

// Simulate a very basic export without database connection
// to test our export controller logic

function testParticipantData($data, $label) {
    echo "Testing: $label\n";
    echo "Records: " . count($data) . "\n";
    echo "Payload size: " . strlen(json_encode(['data' => $data])) . " bytes\n";
    
    // Check for potentially problematic content
    $issues = [];
    
    foreach ($data as $i => $record) {
        foreach ($record as $field => $value) {
            if (is_string($value)) {
                // Check for null bytes
                if (strpos($value, "\0") !== false) {
                    $issues[] = "Record $i, field '$field': Contains null bytes";
                }
                
                // Check for very long strings
                if (strlen($value) > 5000) {
                    $issues[] = "Record $i, field '$field': Very long string (" . strlen($value) . " chars)";
                }
                
                // Check for control characters
                if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
                    $issues[] = "Record $i, field '$field': Contains control characters";
                }
                
                // Check for unusual encodings
                if (!mb_check_encoding($value, 'UTF-8')) {
                    $issues[] = "Record $i, field '$field': Invalid UTF-8 encoding";
                }
            }
        }
    }
    
    if (!empty($issues)) {
        echo "⚠️  Potential issues found:\n";
        foreach (array_slice($issues, 0, 5) as $issue) {
            echo "  - $issue\n";
        }
        if (count($issues) > 5) {
            echo "  ... and " . (count($issues) - 5) . " more issues\n";
        }
    } else {
        echo "✅ No obvious data issues detected\n";
    }
    
    // Test JSON encoding
    $jsonData = json_encode(['data' => $data]);
    if ($jsonData === false) {
        echo "❌ JSON encoding failed: " . json_last_error_msg() . "\n";
    } else {
        echo "✅ JSON encoding successful\n";
    }
    
    echo "\n";
}

// Test 1: Clean minimal data
$cleanData = [
    [
        'Participant_ID' => '1',
        'Full_Name' => 'John Doe',
        'Email' => 'john@example.com',
        'Program' => 'Test Program'
    ]
];

testParticipantData($cleanData, "Clean minimal data");

// Test 2: Data with potential issues
$problematicData = [
    [
        'Participant_ID' => '1',
        'Full_Name' => 'José María Ñoño',
        'Email' => 'jose@example.com',
        'Essay_1' => str_repeat('This is a very long essay. ', 200), // Long content
        'Essay_2' => "Essay with\nnewlines\tand\ttabs",
        'Current_Address' => '123 "Main" Street, Apt #4-B',
        'Instagram_Account' => '@user_with_émojis_🎉',
        'Notes' => 'Some notes with special chars: áéíóú àèìòù âêîôû'
    ]
];

testParticipantData($problematicData, "Data with special characters and long content");

// Test 3: Data with control characters (simulating database corruption)
$corruptedData = [
    [
        'Participant_ID' => '1',
        'Full_Name' => "Name\x00with\x01null\x02bytes", // Simulated corruption
        'Email' => 'test@example.com',
        'Essay_1' => "Essay\rwith\ncontrol\tchars"
    ]
];

testParticipantData($corruptedData, "Data with control characters (simulated corruption)");

echo "=== Recommendations Based on Results ===\n";
echo "1. If clean data works but problematic data fails:\n";
echo "   - Add data sanitization before API calls\n";
echo "   - Strip control characters and null bytes\n";
echo "   - Limit field lengths\n\n";

echo "2. If all data fails:\n";
echo "   - Check API endpoint configuration\n";
echo "   - Verify authentication/headers\n";
echo "   - Check for network/proxy issues\n\n";

echo "3. Next steps:\n";
echo "   - Test with actual database sample\n";
echo "   - Add data cleaning functions\n";
echo "   - Implement progressive debugging\n";

echo "\n=== Test Complete ===\n";
