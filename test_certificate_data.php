<?php
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap CodeIgniter
$pathsConfig = __DIR__ . '/app/Config/Paths.php';
require_once $pathsConfig;
$paths = new \Config\Paths();

// Load CodeIgniter
$bootstrap = \CodeIgniter\Config\Services::codeigniter();

// Load models
use App\Models\ProgramAwardModel;
use App\Models\ParticipantModel;
use App\Models\ProgramModel;

$programModel = new \App\Models\ProgramModel();
$awardModel = new ProgramAwardModel();
$participantModel = new ParticipantModel();

echo "Certificate Management Test Data Check\n";
echo "=====================================\n\n";

// Get all programs
$programs = $programModel->where('is_active', 1)->where('is_deleted', 0)->findAll();
echo "Active Programs: " . count($programs) . "\n";

foreach ($programs as $program) {
    echo "\nProgram: {$program->name} (ID: {$program->id})\n";
    echo str_repeat("-", 50) . "\n";
    
    // Get awards for this program
    $awards = $awardModel->where('program_id', $program->id)
                        ->where('is_active', 1)
                        ->where('is_deleted', 0)
                        ->findAll();
    echo "Awards: " . count($awards) . "\n";
    
    foreach ($awards as $award) {
        echo "  - {$award->title} ({$award->award_type})\n";
    }
    
    // Get participants for this program
    $participants = $participantModel->where('program_id', $program->id)
                                   ->where('is_active', 1)
                                   ->where('is_deleted', 0)
                                   ->limit(5)
                                   ->findAll();
    echo "Participants: " . count($participants) . " (showing first 5)\n";
    
    foreach ($participants as $participant) {
        echo "  - {$participant->full_name} ({$participant->account_id})\n";
    }
}

if (count($programs) === 0) {
    echo "❌ No active programs found!\n";
    echo "Please create a program first in the master data section.\n";
}

echo "\n";
?>
