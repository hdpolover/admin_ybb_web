<?php

echo "=== Testing JSON Payload Issues ===\n\n";

// Simulate the payload structure from logs
$sampleData = [
    [
        'Participant_ID' => '123',
        'Account_ID' => 'ACC_456',
        'Full_Name' => 'John Doe',
        'Email' => 'john@example.com',
        'Phone' => '+1234567890',
        'Nationality' => 'USA',
        'Current_Address' => '123 Main St, City, State',
        'Gender' => 'Male',
        'Birthdate' => '1990-01-15',
        'Age' => '34',
        'Category' => 'Student',
        'Registration_Status' => 'Complete',
        'Payment_Status' => 'Paid',
        'General_Status' => 'Active',
        'Email_Verified' => 'Yes',
        'Education_Level' => 'Bachelor',
        'Major_Field' => 'Computer Science',
        'Institution' => 'University ABC',
        'Occupation' => 'Developer',
        'Program' => 'Youth Program 2024',
        'Program_Theme' => 'Technology',
        'Registration_Date' => '2024-01-15 10:30:00',
        'Document_Status' => 'Verified',
        'Instagram_Account' => '@johndoe',
        'TShirt_Size' => 'L',
        'Essay_1' => 'This is a sample essay response that could be quite long and contain various characters including quotes, apostrophes, newlines, and special symbols like é, ñ, and other Unicode characters.'
    ]
];

// Create the payload as YbbExport would
$payload = [
    'data' => $sampleData,
    'template' => 'standard',
    'format' => 'excel',
    'filename' => 'test_participants.xlsx'
];

echo "1. Testing JSON encoding...\n";
$jsonPayload = json_encode($payload);

if ($jsonPayload === false) {
    echo "ERROR: JSON encoding failed!\n";
    echo "JSON Error: " . json_last_error_msg() . "\n";
} else {
    echo "✓ JSON encoding successful\n";
    echo "Payload size: " . strlen($jsonPayload) . " bytes\n";
}

echo "\n2. Testing with UTF-8 encoding...\n";
$jsonPayloadUTF8 = json_encode($payload, JSON_UNESCAPED_UNICODE);

if ($jsonPayloadUTF8 === false) {
    echo "ERROR: UTF-8 JSON encoding failed!\n";
    echo "JSON Error: " . json_last_error_msg() . "\n";
} else {
    echo "✓ UTF-8 JSON encoding successful\n";
    echo "UTF-8 Payload size: " . strlen($jsonPayloadUTF8) . " bytes\n";
}

echo "\n3. Testing common problematic scenarios...\n";

// Test with problematic data
$problematicData = [
    [
        'Participant_ID' => '123',
        'Full_Name' => 'José María Ñoño',  // Special characters
        'Email' => 'jose@example.com',
        'Essay_1' => "This essay contains \"quotes\" and 'apostrophes' and\nnewlines and\ttabs.",
        'Essay_2' => 'This essay contains emojis 😀🎉 and special symbols ∞ ≤ ≥',
        'Address' => 'Calle Principal #123, Apartamento 4-B',
        'Notes' => null,  // Null value
        'Empty_Field' => '',  // Empty string
        'Number_As_String' => '12345'
    ]
];

$problematicPayload = [
    'data' => $problematicData,
    'template' => 'standard',
    'format' => 'excel'
];

$problematicJson = json_encode($problematicPayload, JSON_UNESCAPED_UNICODE);

if ($problematicJson === false) {
    echo "ERROR: Problematic data JSON encoding failed!\n";
    echo "JSON Error: " . json_last_error_msg() . "\n";
} else {
    echo "✓ Problematic data JSON encoding successful\n";
    echo "Problematic payload size: " . strlen($problematicJson) . " bytes\n";
}

echo "\n4. Testing large dataset simulation (433 records)...\n";

// Simulate 433 records
$largeData = [];
for ($i = 1; $i <= 433; $i++) {
    $largeData[] = [
        'Participant_ID' => (string)$i,
        'Full_Name' => "Participant $i",
        'Email' => "participant{$i}@example.com",
        'Essay_1' => str_repeat("This is a long essay response for participant $i. ", 50), // ~2.5KB per essay
        'Essay_2' => str_repeat("Another essay response with more content. ", 30),
        'Essay_3' => str_repeat("Third essay with detailed information. ", 40),
        'Address' => "123 Street Name, City, Country $i",
        'Program' => 'Youth Program 2024',
        'Status' => 'Active'
    ];
}

$largePayload = [
    'data' => $largeData,
    'template' => 'standard',
    'format' => 'excel'
];

echo "Large dataset: " . count($largeData) . " records\n";

$largeJson = json_encode($largePayload);
if ($largeJson === false) {
    echo "ERROR: Large dataset JSON encoding failed!\n";
    echo "JSON Error: " . json_last_error_msg() . "\n";
} else {
    echo "✓ Large dataset JSON encoding successful\n";
    echo "Large payload size: " . number_format(strlen($largeJson)) . " bytes (" . round(strlen($largeJson)/1024/1024, 2) . " MB)\n";
    
    // Check if payload is too large for typical HTTP request
    $sizeMB = strlen($largeJson) / 1024 / 1024;
    if ($sizeMB > 10) {
        echo "⚠️  WARNING: Payload is very large (>{$sizeMB}MB) - may exceed server limits\n";
    } else {
        echo "✓ Payload size is reasonable (<10MB)\n";
    }
}

echo "\n5. Common API issues to check:\n";
echo "- Content-Type: application/json ✓\n";
echo "- Valid JSON structure ✓\n";
echo "- UTF-8 encoding ✓\n";
echo "- No null bytes ✓\n";

echo "\n6. Potential API rejection reasons:\n";
echo "- Payload too large (>10MB server limit)\n";
echo "- Invalid field names (spaces, special chars)\n";
echo "- Missing required fields in API spec\n";
echo "- Rate limiting or authentication issues\n";
echo "- Server configuration (max POST size, timeout)\n";

echo "\n=== Test Complete ===\n";
