<?php
/**
 * Certificate API Usage Examples
 * 
 * This file demonstrates how to use the Certificate API endpoints
 * These examples can be integrated into your frontend application
 */

class CertificateApiClient 
{
    private $baseUrl;
    
    public function __construct($baseUrl) 
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    /**
     * Get certificates for a specific participant
     */
    public function getParticipantCertificates($participantId) 
    {
        $url = $this->baseUrl . "/api/certificates/participant/" . $participantId;
        return $this->makeRequest($url);
    }
    
    /**
     * Get certificate templates for a program
     */
    public function getProgramCertificates($programId) 
    {
        $url = $this->baseUrl . "/api/certificates/program/" . $programId;
        return $this->makeRequest($url);
    }
    
    /**
     * Get details of a specific certificate
     */
    public function getCertificateDetails($certificateId) 
    {
        $url = $this->baseUrl . "/api/certificates/" . $certificateId;
        return $this->makeRequest($url);
    }
    
    /**
     * Generate a new certificate
     */
    public function generateCertificate($participantId, $awardId) 
    {
        $url = $this->baseUrl . "/api/certificates/generate";
        $data = [
            'participant_id' => $participantId,
            'award_id' => $awardId
        ];
        return $this->makeRequest($url, 'POST', $data);
    }
    
    /**
     * Revoke a certificate
     */
    public function revokeCertificate($certificateId) 
    {
        $url = $this->baseUrl . "/api/certificates/" . $certificateId;
        return $this->makeRequest($url, 'DELETE');
    }
    
    /**
     * Get certificate statistics for a participant
     */
    public function getCertificateStats($participantId) 
    {
        $url = $this->baseUrl . "/api/certificates/stats/" . $participantId;
        return $this->makeRequest($url);
    }
    
    /**
     * Regenerate an existing certificate
     */
    public function regenerateCertificate($certificateId) 
    {
        $url = $this->baseUrl . "/api/certificates/" . $certificateId . "/regenerate";
        return $this->makeRequest($url, 'POST');
    }
    
    /**
     * Helper method to make HTTP requests
     */
    private function makeRequest($url, $method = 'GET', $data = null) 
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode
            ];
        }
        
        $decodedResponse = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => $decodedResponse,
            'http_code' => $httpCode
        ];
    }
}

// Usage Examples
try {
    // Initialize the API client
    $certificateApi = new CertificateApiClient('http://localhost/admin_ybb_web/public');
    
    echo "<h1>Certificate API Usage Examples</h1>\n";
    echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .example { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; } .success { background-color: #d4edda; } .error { background-color: #f8d7da; } pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }</style>\n";
    
    // Example 1: Get participant certificates
    echo "<div class='example'>\n";
    echo "<h2>Example 1: Get Participant Certificates</h2>\n";
    echo "<pre>// PHP Code:\n";
    echo "\$result = \$certificateApi->getParticipantCertificates(1);</pre>\n";
    
    $result = $certificateApi->getParticipantCertificates(1);
    $class = $result['success'] ? 'success' : 'error';
    echo "<div class='$class'>\n";
    echo "<h4>Result:</h4>\n";
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>\n";
    echo "</div></div>\n";
    
    // Example 2: Get program certificates
    echo "<div class='example'>\n";
    echo "<h2>Example 2: Get Program Certificates</h2>\n";
    echo "<pre>// PHP Code:\n";
    echo "\$result = \$certificateApi->getProgramCertificates(1);</pre>\n";
    
    $result = $certificateApi->getProgramCertificates(1);
    $class = $result['success'] ? 'success' : 'error';
    echo "<div class='$class'>\n";
    echo "<h4>Result:</h4>\n";
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>\n";
    echo "</div></div>\n";
    
    // Example 3: Get certificate stats
    echo "<div class='example'>\n";
    echo "<h2>Example 3: Get Certificate Statistics</h2>\n";
    echo "<pre>// PHP Code:\n";
    echo "\$result = \$certificateApi->getCertificateStats(1);</pre>\n";
    
    $result = $certificateApi->getCertificateStats(1);
    $class = $result['success'] ? 'success' : 'error';
    echo "<div class='$class'>\n";
    echo "<h4>Result:</h4>\n";
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>\n";
    echo "</div></div>\n";
    
    // JavaScript Examples
    echo "<div class='example'>\n";
    echo "<h2>JavaScript/Frontend Examples</h2>\n";
    echo "<pre>// Get participant certificates\n";
    echo "fetch('/api/certificates/participant/1')\n";
    echo "  .then(response => response.json())\n";
    echo "  .then(data => {\n";
    echo "    if (data.status) {\n";
    echo "      console.log('Certificates:', data.data.certificates);\n";
    echo "    }\n";
    echo "  });\n\n";
    echo "// Generate certificate\n";
    echo "fetch('/api/certificates/generate', {\n";
    echo "  method: 'POST',\n";
    echo "  headers: {\n";
    echo "    'Content-Type': 'application/json'\n";
    echo "  },\n";
    echo "  body: JSON.stringify({\n";
    echo "    participant_id: 1,\n";
    echo "    award_id: 1\n";
    echo "  })\n";
    echo "})\n";
    echo ".then(response => response.json())\n";
    echo ".then(data => {\n";
    echo "  if (data.status) {\n";
    echo "    // data.data.file_data contains base64 PDF\n";
    echo "    const link = document.createElement('a');\n";
    echo "    link.href = 'data:application/pdf;base64,' + data.data.file_data;\n";
    echo "    link.download = data.data.file_name;\n";
    echo "    link.click();\n";
    echo "  }\n";
    echo "});</pre>\n";
    echo "</div>\n";
    
    // Integration Tips
    echo "<div class='example'>\n";
    echo "<h2>Integration Tips</h2>\n";
    echo "<ul>\n";
    echo "<li><strong>Error Handling:</strong> Always check the 'status' field in the response</li>\n";
    echo "<li><strong>PDF Downloads:</strong> Use the base64 data to create downloadable links</li>\n";
    echo "<li><strong>Caching:</strong> Consider caching certificate lists to improve performance</li>\n";
    echo "<li><strong>Authentication:</strong> Add JWT or other authentication as needed</li>\n";
    echo "<li><strong>Rate Limiting:</strong> Implement rate limiting for certificate generation</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='example error'>\n";
    echo "<h2>Error</h2>\n";
    echo "<p>An error occurred while running the examples: " . $e->getMessage() . "</p>\n";
    echo "</div>\n";
}
?>
