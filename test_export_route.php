<?php
/**
 * Quick test to verify export routes are working
 */

// Test if we can access the exports index
$testUrl = 'http://localhost:8080/exports/';
echo "Testing export routes accessibility...\n";
echo "URL: $testUrl\n";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $testUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
        'User-Agent: Route Test'
    ]
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);
$redirectUrl = curl_getinfo($curl, CURLINFO_REDIRECT_URL);
curl_close($curl);

echo "HTTP Status: $httpCode\n";
if ($error) {
    echo "CURL Error: $error\n";
}
if ($redirectUrl) {
    echo "Redirect URL: $redirectUrl\n";
}

// Status interpretations
switch($httpCode) {
    case 200:
        echo "✅ Route accessible\n";
        break;
    case 302:
        echo "🔄 Redirected (likely authentication required)\n";
        break;
    case 404:
        echo "❌ Route not found\n";
        break;
    case 500:
        echo "💥 Server error\n";
        break;
    default:
        echo "⚠️ Unexpected status: $httpCode\n";
}

echo "\nDone.\n";
?>
