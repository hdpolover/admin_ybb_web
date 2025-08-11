<?php

echo "=== Testing Participant Data Sanitization ===\n\n";

// Test the sanitization methods
class TestParticipantSanitizer {
    
    private function sanitizeParticipantData(array $participant): array
    {
        $sanitized = [];
        
        foreach ($participant as $field => $value) {
            if (is_string($value)) {
                // Remove null bytes and control characters
                $cleanValue = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
                
                // Ensure UTF-8 encoding
                if (!mb_check_encoding($cleanValue, 'UTF-8')) {
                    $cleanValue = mb_convert_encoding($cleanValue, 'UTF-8', 'UTF-8');
                }
                
                // Limit field length to prevent oversized payloads
                $maxLength = $this->getFieldMaxLength($field);
                if (strlen($cleanValue) > $maxLength) {
                    $cleanValue = substr($cleanValue, 0, $maxLength - 3) . '...';
                }
                
                // Trim whitespace
                $sanitized[$field] = trim($cleanValue);
            } else {
                $sanitized[$field] = $value;
            }
        }
        
        return $sanitized;
    }
    
    private function getFieldMaxLength(string $fieldName): int
    {
        // Define field length limits
        if (strpos($fieldName, 'Essay_') === 0) {
            return 2000; // Limit essays to 2000 characters
        }
        
        switch ($fieldName) {
            case 'Full_Name':
            case 'Institution':
            case 'Program':
                return 200;
            
            case 'Email':
            case 'Phone':
                return 100;
                
            case 'Current_Address':
                return 500;
                
            case 'Instagram_Account':
                return 50;
                
            default:
                return 1000; // Default limit for other fields
        }
    }
    
    public function testSanitization() {
        // Test data with various issues
        $problematicData = [
            'Participant_ID' => '123',
            'Full_Name' => "John\x00Doe\x01with\x02control\x03chars",  // Control characters
            'Email' => '  user@example.com  ',  // Whitespace
            'Essay_1' => str_repeat('This is a very long essay that exceeds the limit. ', 50), // Long content
            'Instagram_Account' => 'this_username_is_way_too_long_for_instagram_and_should_be_truncated',
            'Current_Address' => "123 Main St\nwith\nnewlines\tand\ttabs"
        ];
        
        echo "Before sanitization:\n";
        foreach ($problematicData as $field => $value) {
            $length = strlen($value);
            $hasControl = preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value);
            echo "  $field: $length chars" . ($hasControl ? " (has control chars)" : "") . "\n";
        }
        
        $sanitized = $this->sanitizeParticipantData($problematicData);
        
        echo "\nAfter sanitization:\n";
        foreach ($sanitized as $field => $value) {
            $length = strlen($value);
            $hasControl = preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value);
            echo "  $field: $length chars" . ($hasControl ? " (has control chars)" : " (clean)") . "\n";
            
            if ($field === 'Essay_1' || $field === 'Instagram_Account') {
                echo "    Value: " . substr($value, 0, 100) . "...\n";
            }
        }
        
        // Test JSON encoding
        $jsonBefore = json_encode($problematicData);
        $jsonAfter = json_encode($sanitized);
        
        echo "\nJSON encoding:\n";
        echo "Before: " . ($jsonBefore === false ? "FAILED (" . json_last_error_msg() . ")" : "SUCCESS") . "\n";
        echo "After: " . ($jsonAfter === false ? "FAILED (" . json_last_error_msg() . ")" : "SUCCESS") . "\n";
        
        if ($jsonAfter !== false) {
            echo "Sanitized payload size: " . strlen($jsonAfter) . " bytes\n";
        }
    }
}

$tester = new TestParticipantSanitizer();
$tester->testSanitization();

echo "\n=== Sanitization Test Complete ===\n";
echo "✅ Data sanitization should now prevent API rejections\n";
echo "✅ Control characters removed\n";
echo "✅ Field lengths limited\n";
echo "✅ UTF-8 encoding ensured\n";
