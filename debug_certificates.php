<?php
echo "Testing certificate management system...\n";

// Direct database connection for testing
$host = '194.163.42.101';
$username = 'u1437096_ybb_master_app_admin_user';
$password = '7J8*^dFEa&lN';
$database = 'u1437096_ybb_master_app_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected\n";
    
    // Check programs
    $stmt = $pdo->query("SELECT id, name, is_active FROM programs WHERE is_deleted = 0 ORDER BY id DESC LIMIT 5");
    $programs = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "Programs found: " . count($programs) . "\n";
    foreach ($programs as $program) {
        $status = $program->is_active ? 'Active' : 'Inactive';
        echo "  - {$program->name} (ID: {$program->id}) - {$status}\n";
    }
    
    // Use the first active program
    $activeProgram = null;
    foreach ($programs as $program) {
        if ($program->is_active) {
            $activeProgram = $program;
            break;
        }
    }
    
    if (!$activeProgram) {
        echo "❌ No active program found\n";
        exit;
    }
    
    echo "\n✅ Using active program: {$activeProgram->name} (ID: {$activeProgram->id})\n";
    
    // Check awards
    $stmt = $pdo->prepare("SELECT id, title, award_type, description FROM program_awards WHERE program_id = ? AND is_active = 1 AND is_deleted = 0 ORDER BY order_number");
    $stmt->execute([$activeProgram->id]);
    $awards = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "Awards found: " . count($awards) . "\n";
    foreach ($awards as $award) {
        echo "  - {$award->title} ({$award->award_type})\n";
    }
    
    // Check participants
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM participants WHERE program_id = ? AND is_deleted = 0");
    $stmt->execute([$activeProgram->id]);
    $participantCount = $stmt->fetch(PDO::FETCH_OBJ)->count;
    
    echo "Participants: {$participantCount}\n";
    
    // Check participant awards
    $stmt = $pdo->prepare("
        SELECT pa.id, pa.award_id, pa.participant_id, pgm_a.title as award_title, p.full_name
        FROM participant_awards pa
        JOIN program_awards pgm_a ON pgm_a.id = pa.award_id
        JOIN participants p ON p.id = pa.participant_id
        WHERE pgm_a.program_id = ? AND pa.is_active = 1 AND pa.is_deleted = 0
        LIMIT 5
    ");
    $stmt->execute([$activeProgram->id]);
    $participantAwards = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "Participant awards assigned: " . count($participantAwards) . "\n";
    foreach ($participantAwards as $pa) {
        echo "  - {$pa->full_name} -> {$pa->award_title}\n";
    }
    
    // Test the actual data structure that would be returned
    echo "\n🔍 Testing data structure for DataTables...\n";
    
    foreach ($awards as $award) {
        // Count participants for this award
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT pa.participant_id) as participants_count,
                   COUNT(DISTINCT pc.participant_id) as certificates_issued
            FROM program_awards pgm_a
            LEFT JOIN participant_awards pa ON pa.award_id = pgm_a.id AND pa.is_active = 1 AND pa.is_deleted = 0
            LEFT JOIN participant_certificates pc ON pc.award_id = pgm_a.id AND pc.is_active = 1 AND pc.is_deleted = 0
            WHERE pgm_a.id = ?
        ");
        $stmt->execute([$award->id]);
        $counts = $stmt->fetch(PDO::FETCH_OBJ);
        
        $progress = $counts->participants_count > 0 
            ? round(($counts->certificates_issued / $counts->participants_count) * 100, 1) 
            : 0;
            
        echo "Award: {$award->title}\n";
        echo "  Participants: {$counts->participants_count}\n";
        echo "  Certificates: {$counts->certificates_issued}\n";
        echo "  Progress: {$progress}%\n\n";
    }
    
    echo "✅ All tests completed successfully!\n";
    echo "\nNext step: Check browser console for JavaScript errors or authentication issues.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
