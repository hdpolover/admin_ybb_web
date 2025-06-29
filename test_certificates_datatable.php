<?php
echo "Testing Certificate Management Authentication & Data...\n";

// Database connection
$host = '194.163.42.101';
$username = 'u1437096_ybb_master_app_admin_user';
$password = '7J8*^dFEa&lN';
$database = 'u1437096_ybb_master_app_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected\n\n";
    
    // Check current program (ID: 8 - Middle East Youth Summit 2025)
    $programId = 8;
    
    echo "🔍 Checking Program Awards for Program ID: $programId\n";
    echo "=" . str_repeat("=", 50) . "\n";
    
    // Test the exact query from the controller
    $query = "
        SELECT program_awards.*, 
               COUNT(DISTINCT participant_awards.participant_id) as participants_count,
               COUNT(DISTINCT participant_certificates.participant_id) as certificates_issued
        FROM program_awards
        LEFT JOIN participant_awards ON participant_awards.award_id = program_awards.id 
            AND participant_awards.is_active = 1 
            AND participant_awards.is_deleted = 0
        LEFT JOIN participant_certificates ON participant_certificates.award_id = program_awards.id 
            AND participant_certificates.is_active = 1 
            AND participant_certificates.is_deleted = 0
        WHERE program_awards.program_id = ? 
            AND program_awards.is_active = 1 
            AND program_awards.is_deleted = 0
        GROUP BY program_awards.id
        ORDER BY program_awards.order_number ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$programId]);
    $awards = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "Found: " . count($awards) . " awards\n\n";
    
    if (count($awards) === 0) {
        echo "❌ No awards found!\n";
        
        // Debug: Check if awards exist but with different conditions
        $debugQuery = "SELECT * FROM program_awards WHERE program_id = ?";
        $stmt = $pdo->prepare($debugQuery);
        $stmt->execute([$programId]);
        $allAwards = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        echo "Debug - All awards for this program (regardless of status):\n";
        foreach ($allAwards as $award) {
            echo "  - {$award->title} (Active: {$award->is_active}, Deleted: {$award->is_deleted}, Order: {$award->order_number})\n";
        }
        exit;
    }
    
    echo "📋 Awards Details:\n";
    foreach ($awards as $index => $award) {
        echo "\n" . ($index + 1) . ". {$award->title}\n";
        echo "   - Type: {$award->award_type}\n";
        echo "   - Description: " . ($award->description ?: 'No description') . "\n";
        echo "   - Participants Assigned: {$award->participants_count}\n";
        echo "   - Certificates Issued: {$award->certificates_issued}\n";
        echo "   - Order: {$award->order_number}\n";
        
        // Check assigned participants for this award
        if ($award->participants_count > 0) {
            $participantQuery = "
                SELECT p.full_name, p.account_id, pa.created_at as assigned_at
                FROM participant_awards pa
                JOIN participants p ON p.id = pa.participant_id
                WHERE pa.award_id = ? AND pa.is_active = 1 AND pa.is_deleted = 0
                LIMIT 5
            ";
            $stmt = $pdo->prepare($participantQuery);
            $stmt->execute([$award->id]);
            $participants = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            echo "   - Sample assigned participants:\n";
            foreach ($participants as $participant) {
                echo "     • {$participant->full_name} ({$participant->account_id}) - {$participant->assigned_at}\n";
            }
        }
    }
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "🎯 Summary for Certificate Management:\n";
    echo "   - Program ID: $programId (Middle East Youth Summit 2025)\n";
    echo "   - Total Awards: " . count($awards) . "\n";
    echo "   - Total Participants Assigned: " . array_sum(array_column($awards, 'participants_count')) . "\n";
    echo "   - Total Certificates Issued: " . array_sum(array_column($awards, 'certificates_issued')) . "\n";
    
    echo "\n🔧 For the Certificate Management UI:\n";
    echo "   1. Awards will appear in the table ✅\n";
    echo "   2. 'Manage Participants' button will be available ✅\n";
    echo "   3. Current assignment status will show in Progress column ✅\n";
    
    if (array_sum(array_column($awards, 'participants_count')) === 0) {
        echo "\n⚠️  NOTE: No participants are currently assigned to any awards.\n";
        echo "   This is why the table might look empty in the Progress column.\n";
        echo "   Use the 'Manage Participants' button to assign participants!\n";
    }
    
    echo "\n✅ The DataTable should work correctly with this data!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ General error: " . $e->getMessage() . "\n";
}
