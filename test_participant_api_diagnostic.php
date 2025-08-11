<?php

echo "=== Participant Export Diagnostic Test ===\n\n";

// Test with minimal participant data structure
$minimalParticipant = [
    'Participant_ID' => '1',
    'Full_Name' => 'Test User',
    'Email' => 'test@example.com',
    'Program' => 'Test Program'
];

$testPayload = [
    'data' => [$minimalParticipant],
    'template' => 'standard',
    'format' => 'excel'
];

echo "1. Testing minimal participant structure...\n";
echo "Payload size: " . strlen(json_encode($testPayload)) . " bytes\n";

$url = 'https://ybb-data-management-service-production.up.railway.app/api/ybb/export/participants';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testPayload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status Code: $httpCode\n";
if ($error) {
    echo "cURL Error: $error\n";
}
echo "Response: " . substr($response, 0, 200) . "...\n\n";

if ($httpCode === 200) {
    echo "✅ Minimal participant structure works\n";
} else {
    echo "❌ Even minimal participant structure fails\n";
    echo "This suggests an API-level issue or configuration problem\n";
}

echo "\n2. Testing with problematic field names...\n";

// Test with column names that might cause issues
$problematicParticipant = [
    'Participant_ID' => '1',
    'Full_Name' => 'Test User with "quotes" and special chars éñ',
    'Email' => 'test@example.com',
    'Essay_1' => 'Essay with newlines\nand tabs\tand special characters',
    'Current_Address' => '123 Main St, Apt #4-B, City with "quotes"'
];

$problematicPayload = [
    'data' => [$problematicParticipant],
    'template' => 'standard',
    'format' => 'excel'
];

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $url);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($problematicPayload));
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 30);

$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$error2 = curl_error($ch2);
curl_close($ch2);

echo "HTTP Status Code: $httpCode2\n";
echo "Response: " . substr($response2, 0, 200) . "...\n\n";

if ($httpCode2 === 200) {
    echo "✅ Problematic characters are handled correctly\n";
} else {
    echo "❌ Problematic characters cause API rejection\n";
}

echo "\n3. API Status Summary:\n";
echo "Minimal data: HTTP $httpCode\n";
echo "Complex data: HTTP $httpCode2\n";

if ($httpCode === 200 && $httpCode2 === 200) {
    echo "\n🤔 API works with test data - issue may be with specific database content\n";
} elseif ($httpCode !== 200) {
    echo "\n🚨 API rejects even minimal data - API service issue\n";
} else {
    echo "\n⚠️  API rejects complex data - data content issue\n";
}

echo "\n=== Diagnostic Complete ===\n";
