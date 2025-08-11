<?php

require_once __DIR__ . '/vendor/autoload.php';

// Simple database connection test
try {
    $config = [
        'hostname' => 'localhost',
        'username' => 'root',  
        'password' => '',
        'database' => 'admin_ybb_web',
        'charset'  => 'utf8mb4'
    ];
    
    $dsn = "mysql:host={$config['hostname']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Testing Real Participant Data Export ===\n\n";
    
    // Get just 3 participants to test
    $sql = "SELECT p.id as participant_id, p.account_id as participant_account_id, 
                   p.full_name as participant_full_name, p.email as participant_email,
                   p.phone as participant_phone, p.nationality as participant_nationality,
                   p.current_address as participant_current_address, p.gender as participant_gender,
                   p.birthdate as participant_birthdate, p.category as participant_category,
                   p.form_status_code, p.payment_status_code, p.general_status_code,
                   u.is_verified as user_is_verified, p.education_level as participant_education_level,
                   p.major as participant_major, p.institution as participant_institution,
                   p.occupation as participant_occupation, pr.name as program_name,
                   pr.theme as program_theme, p.registered_at as participant_registered_at,
                   p.document_status_code, p.instagram as participant_instagram,
                   p.tshirt_size as participant_tshirt_size
            FROM participants p
            LEFT JOIN users u ON u.id = p.user_id  
            LEFT JOIN programs pr ON pr.id = p.program_id
            WHERE p.is_active = 1 AND p.is_deleted = 0
            LIMIT 3";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($participants) . " participants\n\n";
    
    if (!empty($participants)) {
        // Normalize the data similar to our current method
        $normalizedData = [];
        
        foreach ($participants as $participant) {
            $normalized = [
                'Participant_ID' => $participant['participant_id'] ?? 'N/A',
                'Account_ID' => $participant['participant_account_id'] ?? 'N/A', 
                'Full_Name' => $participant['participant_full_name'] ?? 'Unknown',
                'Email' => $participant['participant_email'] ?? 'No Email',
                'Phone' => $participant['participant_phone'] ?? 'Not Provided',
                'Nationality' => $participant['participant_nationality'] ?? 'Not Specified',
                'Current_Address' => $participant['participant_current_address'] ?? '',
                'Gender' => $participant['participant_gender'] ?? '',
                'Birthdate' => $participant['participant_birthdate'] ?? '',
                'Category' => $participant['participant_category'] ?? '',
                'Registration_Status' => 'Active', // Simplified
                'Payment_Status' => 'Paid',       // Simplified  
                'General_Status' => 'Active',     // Simplified
                'Email_Verified' => $participant['user_is_verified'] ? 'Yes' : 'No',
                'Program' => $participant['program_name'] ?? 'Unknown Program'
            ];
            
            $normalizedData[] = $normalized;
        }
        
        // Test with our normalized structure
        $payload = [
            'data' => $normalizedData,
            'template' => 'standard',
            'format' => 'excel'
        ];
        
        echo "1. Testing with our normalized field structure...\n";
        
        $url = 'https://ybb-data-management-service-production.up.railway.app/api/ybb/export/participants';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
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
            echo "✅ Our normalized structure works with small dataset\n";
            
            // Now test with progressively larger datasets
            echo "\n2. Testing with 10 records...\n";
            
            $sql10 = str_replace('LIMIT 3', 'LIMIT 10', $sql);
            $stmt10 = $pdo->prepare($sql10);
            $stmt10->execute();
            $participants10 = $stmt10->fetchAll(PDO::FETCH_ASSOC);
            
            $normalizedData10 = [];
            foreach ($participants10 as $participant) {
                $normalized = [
                    'Participant_ID' => $participant['participant_id'] ?? 'N/A',
                    'Full_Name' => $participant['participant_full_name'] ?? 'Unknown',
                    'Email' => $participant['participant_email'] ?? 'No Email',
                    'Program' => $participant['program_name'] ?? 'Unknown Program'
                ];
                $normalizedData10[] = $normalized;
            }
            
            $payload10 = [
                'data' => $normalizedData10,
                'template' => 'standard', 
                'format' => 'excel'
            ];
            
            $ch10 = curl_init();
            curl_setopt($ch10, CURLOPT_URL, $url);
            curl_setopt($ch10, CURLOPT_POST, true);
            curl_setopt($ch10, CURLOPT_POSTFIELDS, json_encode($payload10));
            curl_setopt($ch10, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch10, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch10, CURLOPT_TIMEOUT, 30);
            
            $response10 = curl_exec($ch10);
            $httpCode10 = curl_getinfo($ch10, CURLINFO_HTTP_CODE);
            curl_close($ch10);
            
            echo "10 records - HTTP Status: $httpCode10\n";
            
            if ($httpCode10 === 200) {
                echo "✅ 10 records work\n";
                
                // Test with even more
                echo "\n3. Testing with 50 records (simplified)...\n";
                
                $sql50 = str_replace('LIMIT 3', 'LIMIT 50', $sql);
                $stmt50 = $pdo->prepare($sql50);
                $stmt50->execute();
                $participants50 = $stmt50->fetchAll(PDO::FETCH_ASSOC);
                
                $normalizedData50 = [];
                foreach ($participants50 as $participant) {
                    $normalizedData50[] = [
                        'ID' => $participant['participant_id'],
                        'Name' => $participant['participant_full_name'],
                        'Email' => $participant['participant_email']
                    ];
                }
                
                $payload50 = [
                    'data' => $normalizedData50,
                    'template' => 'standard',
                    'format' => 'excel'
                ];
                
                $ch50 = curl_init();
                curl_setopt($ch50, CURLOPT_URL, $url);
                curl_setopt($ch50, CURLOPT_POST, true);
                curl_setopt($ch50, CURLOPT_POSTFIELDS, json_encode($payload50));
                curl_setopt($ch50, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]);
                curl_setopt($ch50, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch50, CURLOPT_TIMEOUT, 60);
                
                $response50 = curl_exec($ch50);
                $httpCode50 = curl_getinfo($ch50, CURLINFO_HTTP_CODE);
                curl_close($ch50);
                
                echo "50 records - HTTP Status: $httpCode50\n";
                echo "50 records response: " . substr($response50, 0, 100) . "...\n";
                
            } else {
                echo "❌ 10 records failed: $response10\n";
            }
            
        } else {
            echo "❌ Our normalized structure failed with small dataset\n";
            echo "This suggests the field structure itself is the problem\n";
        }
        
    } else {
        echo "No participants found in database\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
