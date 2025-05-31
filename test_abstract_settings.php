<?php

// Define ENVIRONMENT
defined('ENVIRONMENT') || define('ENVIRONMENT', 'development');

// Bootstrap CodeIgniter
require_once __DIR__ . '/system/bootstrap.php';

use App\Models\AbstractSettingModel;
use App\Models\ProgramModel;

// Initialize models
$abstractSettingModel = new AbstractSettingModel();
$programModel = new ProgramModel();

// Get first program for testing
$program = $programModel->first();

if (!$program) {
    echo "No programs found. Please create a program first.\n";
    exit(1);
}

echo "Found program: {$program->title} (ID: {$program->id})\n";

// Check if abstract settings already exist
$existingSettings = $abstractSettingModel->where('program_id', $program->id)->first();

if ($existingSettings) {
    echo "Abstract settings already exist for this program:\n";
    echo "- Title limit: {$existingSettings->title_limit}\n";
    echo "- Content limit: {$existingSettings->content_limit}\n";
    echo "- Keywords limit: {$existingSettings->keywords_limit}\n";
    echo "- References limit: {$existingSettings->references_limit}\n";
    echo "- Active: " . ($existingSettings->is_active ? 'Yes' : 'No') . "\n";
} else {
    echo "Creating default abstract settings for program {$program->id}...\n";
    
    // Create default settings
    $defaultSettings = [
        'program_id' => $program->id,
        'title_limit' => 250,
        'content_limit' => 5000,
        'keywords_limit' => 200,
        'references_limit' => 1000,
        'is_active' => 1
    ];
    
    $result = $abstractSettingModel->insert($defaultSettings);
    
    if ($result) {
        echo "Default abstract settings created successfully!\n";
        echo "- Title limit: 250 characters\n";
        echo "- Content limit: 5000 characters\n";
        echo "- Keywords limit: 200 characters\n";
        echo "- References limit: 1000 characters\n";
    } else {
        echo "Error creating abstract settings.\n";
    }
}

echo "\nTest completed.\n";
