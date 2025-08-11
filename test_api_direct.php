<?php

echo "=== Testing Minimal Participant Export ===\n\n";

// Test with minimal data structure (no underscores, basic fields only)
$minimalData = [
    [
        'ID' => '1',
        'Name' => 'Test User',
        'Email' => 'test@example.com',
        'Status' => 'Active'
    ]
];

$minimalPayload = [
    'data' => $minimalData,
    'template' => 'standard',
    'format' => 'excel'
];

echo "1. Testing minimal payload structure...\n";
$url = 'https://ybb-data-management-service-production.up.railway.app/api/ybb/export/participants';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($minimalPayload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status Code: $httpCode\n";
if ($error) {
    echo "cURL Error: $error\n";
}
echo "Response: $response\n\n";

if ($httpCode === 400) {
    echo "❌ 400 error even with minimal data - API issue\n";
} elseif ($httpCode === 200 || $httpCode === 201) {
    echo "✅ Minimal data accepted - field structure issue\n";
} else {
    echo "🔍 Other status code - check API requirements\n";
}

echo "\n2. Testing with original-style column names (spaces)...\n";
$originalStyleData = [
    [
        'Participant ID' => '1',
        'Full Name' => 'Test User',
        'Email Address' => 'test@example.com',
        'Registration Status' => 'Active',
        'Program Name' => 'Test Program'
    ]
];

$originalStylePayload = [
    'data' => $originalStyleData,
    'template' => 'standard',
    'format' => 'excel'
];

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $url);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($originalStylePayload));
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
if ($error2) {
    echo "cURL Error: $error2\n";
}
echo "Response: $response2\n\n";

if ($httpCode2 === 400) {
    echo "❌ 400 error with original-style names too\n";
} elseif ($httpCode2 === 200 || $httpCode2 === 201) {
    echo "✅ Original-style names accepted\n";
} else {
    echo "🔍 Other status code with original-style names\n";
}

echo "\n3. Testing empty data array...\n";
$emptyPayload = [
    'data' => [],
    'template' => 'standard',
    'format' => 'excel'
];

$ch3 = curl_init();
curl_setopt($ch3, CURLOPT_URL, $url);
curl_setopt($ch3, CURLOPT_POST, true);
curl_setopt($ch3, CURLOPT_POSTFIELDS, json_encode($emptyPayload));
curl_setopt($ch3, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_TIMEOUT, 30);

$response3 = curl_exec($ch3);
$httpCode3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
$error3 = curl_error($ch3);
curl_close($ch3);

echo "HTTP Status Code: $httpCode3\n";
if ($error3) {
    echo "cURL Error: $error3\n";
}
echo "Response: $response3\n\n";

echo "=== Test Results Summary ===\n";
echo "Minimal data: HTTP $httpCode\n";
echo "Original style: HTTP $httpCode2\n";
echo "Empty data: HTTP $httpCode3\n";

if ($httpCode === 400 && $httpCode2 === 400 && $httpCode3 === 400) {
    echo "\n🔍 Conclusion: API is rejecting ALL requests - likely server-side issue\n";
} elseif ($httpCode !== 400) {
    echo "\n✅ Conclusion: Minimal data works - check field naming/structure\n";
} else {
    echo "\n🤔 Conclusion: Mixed results - investigate further\n";
}

echo "\n=== Test Complete ===\n";
