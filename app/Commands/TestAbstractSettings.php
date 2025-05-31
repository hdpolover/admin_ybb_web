<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestAbstractSettings extends BaseCommand
{    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Testing';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'test:abstract-settings';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Test abstract settings functionality by creating default settings';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'test:abstract-settings [program_id]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'program_id' => 'Program ID to create settings for (optional, uses first program if not specified)'
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write('Testing Abstract Settings Functionality', 'green');
        CLI::newLine();

        // Initialize models
        $abstractSettingModel = new \App\Models\AbstractSettingModel();
        $programModel = new \App\Models\ProgramModel();

        // Get program ID from arguments or use first program
        $program_id = $params[0] ?? null;
        
        if ($program_id) {
            $program = $programModel->find($program_id);
            if (!$program) {
                CLI::write("Program with ID {$program_id} not found.", 'red');
                return;
            }
        } else {
            $program = $programModel->first();
            if (!$program) {
                CLI::write('No programs found. Please create a program first.', 'red');
                return;
            }
        }

        CLI::write("Found program: {$program->name} (ID: {$program->id})", 'cyan');

        // Check if abstract settings already exist
        $existingSettings = $abstractSettingModel->where('program_id', $program->id)->first();

        if ($existingSettings) {
            CLI::write('Abstract settings already exist for this program:', 'yellow');            CLI::write("- Title limit: {$existingSettings->title_length}");
            CLI::write("- Content limit: {$existingSettings->content_length}");
            CLI::write("- Keywords limit: {$existingSettings->keywords_length}");
            CLI::write("- References limit: {$existingSettings->refs_length}");
            CLI::write("- Active: " . ($existingSettings->is_active ? 'Yes' : 'No'));
        } else {
            CLI::write("Creating default abstract settings for program {$program->id}...", 'green');
              // Create default settings
            $defaultSettings = [
                'program_id' => $program->id,
                'title_length' => 250,
                'content_length' => 5000,
                'keywords_length' => 200,
                'refs_length' => 1000,
                'is_active' => 1,
                'is_deleted' => 0
            ];
              $result = $abstractSettingModel->insert($defaultSettings);
            
            if ($result) {
                CLI::write('Default abstract settings created successfully!', 'green');
                CLI::write('- Title limit: 250 characters');
                CLI::write('- Content limit: 5000 characters');
                CLI::write('- Keywords limit: 200 characters');
                CLI::write('- References limit: 1000 characters');
            } else {
                CLI::write('Error creating abstract settings.', 'red');
                // Show validation errors if any
                $errors = $abstractSettingModel->errors();
                if (!empty($errors)) {
                    CLI::write('Validation errors:', 'red');
                    foreach ($errors as $error) {
                        CLI::write("- {$error}", 'red');
                    }
                }
            }
        }

        CLI::newLine();
        CLI::write('Test completed.', 'green');
    }
}
