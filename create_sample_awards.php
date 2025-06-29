<?php
echo "Creating sample awards for testing...\n";

// Database connection (from app config)
$host = '194.163.42.101';
$username = 'u1437096_ybb_master_app_admin_user'; 
$password = '7J8*^dFEa&lN';
$database = 'u1437096_ybb_master_app_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if we have a current program - get the latest active one
    $stmt = $pdo->query("SELECT id, name FROM programs WHERE is_active = 1 AND is_deleted = 0 ORDER BY id DESC LIMIT 1");
    $program = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$program) {
        echo "❌ No active program found. Please create a program first.\n";
        exit;
    }
    
    echo "✅ Using program: {$program->name} (ID: {$program->id})\n";
    
    // Check if awards already exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM program_awards WHERE program_id = ? AND is_deleted = 0");
    $stmt->execute([$program->id]);
    $existingAwards = $stmt->fetchColumn();
    
    if ($existingAwards > 0) {
        echo "✅ Found {$existingAwards} existing awards for this program.\n";
    } else {
        echo "Creating sample awards...\n";
        
        // Create sample awards
        $awards = [
            [
                'program_id' => $program->id,
                'title' => 'Best Paper Award',
                'description' => 'Awarded to the best research paper submitted',
                'award_type' => 'winner',
                'order_number' => 1
            ],
            [
                'program_id' => $program->id,
                'title' => 'Outstanding Presentation',
                'description' => 'Awarded for exceptional presentation skills',
                'award_type' => 'runner_up',
                'order_number' => 2
            ],
            [
                'program_id' => $program->id,
                'title' => 'Innovation Award',
                'description' => 'Awarded for innovative ideas and solutions',
                'award_type' => 'mention',
                'order_number' => 3
            ],
            [
                'program_id' => $program->id,
                'title' => 'Participation Certificate',
                'description' => 'Certificate of participation for all participants',
                'award_type' => 'other',
                'order_number' => 4
            ]
        ];
        
        $insertStmt = $pdo->prepare("
            INSERT INTO program_awards (program_id, title, description, award_type, order_number, is_active, is_deleted, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, 1, 0, NOW(), NOW())
        ");
        
        foreach ($awards as $award) {
            $insertStmt->execute([
                $award['program_id'],
                $award['title'],
                $award['description'],
                $award['award_type'],
                $award['order_number']
            ]);
            echo "  ✅ Created: {$award['title']}\n";
        }
    }
    
    // Check participants
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE program_id = ? AND is_deleted = 0");
    $stmt->execute([$program->id]);
    $participantCount = $stmt->fetchColumn();
    
    echo "✅ Found {$participantCount} participants in this program.\n";
    
    if ($participantCount > 0) {
        $stmt = $pdo->prepare("SELECT id, full_name, account_id FROM participants WHERE program_id = ? AND is_deleted = 0 LIMIT 3");
        $stmt->execute([$program->id]);
        $participants = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        echo "Sample participants:\n";
        foreach ($participants as $participant) {
            echo "  - {$participant->full_name} ({$participant->account_id})\n";
        }
    }
    
    echo "\n🎉 Test data setup complete!\n";
    echo "You can now visit: http://localhost:8080/documents/certificates\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
