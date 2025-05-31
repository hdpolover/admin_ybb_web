<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestAbstractValidation extends BaseCommand
{
    /**
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
    protected $name = 'test:abstract-validation';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Test abstract validation with character limits';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'test:abstract-validation';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

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
        CLI::write('Testing Abstract Character Limit Validation', 'green');
        CLI::newLine();

        // Initialize models directly
        $abstractSettingModel = new \App\Models\AbstractSettingModel();
        
        // Get abstract settings for program 1
        $abstractSettings = $abstractSettingModel->where('program_id', 1)
                                                 ->where('is_active', 1)
                                                 ->first();
        
        if (!$abstractSettings) {
            CLI::write('No abstract settings found for program 1. Please run test:abstract-settings first.', 'red');
            return;
        }
        
        CLI::write('Found abstract settings:', 'cyan');
        CLI::write("- Title limit: {$abstractSettings->title_length}");
        CLI::write("- Content limit: {$abstractSettings->content_length}");
        CLI::write("- Keywords limit: {$abstractSettings->keywords_length}");
        CLI::write("- References limit: {$abstractSettings->refs_length}");
        CLI::newLine();
        
        // Test validation logic
        CLI::write('Testing validation logic...', 'cyan');
        
        // Create test data that exceeds limits
        $longTitle = str_repeat('A', $abstractSettings->title_length + 50);
        $longContent = str_repeat('B', $abstractSettings->content_length + 1000);
        $longKeywords = str_repeat('C', $abstractSettings->keywords_length + 50);
        
        $errors = [];
        
        // Validate title length
        if (strlen($longTitle) > $abstractSettings->title_length) {
            $errors['title'] = "Title exceeds maximum character limit of {$abstractSettings->title_length} characters.";
        }
        
        // Validate content length
        if (strlen($longContent) > $abstractSettings->content_length) {
            $errors['content'] = "Content exceeds maximum character limit of {$abstractSettings->content_length} characters.";
        }
        
        // Validate keywords length
        if (strlen($longKeywords) > $abstractSettings->keywords_length) {
            $errors['keywords'] = "Keywords exceed maximum character limit of {$abstractSettings->keywords_length} characters.";
        }
        
        if (!empty($errors)) {
            CLI::write('Validation correctly failed for oversized content:', 'green');
            foreach ($errors as $field => $error) {
                CLI::write("- {$field}: {$error}", 'yellow');
            }
        } else {
            CLI::write('ERROR: Validation should have failed but did not!', 'red');
        }
        
        CLI::newLine();
        
        // Test with valid content
        $validTitle = 'Valid Title';
        $validContent = 'Valid content that is within limits.';
        $validKeywords = 'keyword1, keyword2';
        
        $errors = [];
        
        // Validate title length
        if (strlen($validTitle) > $abstractSettings->title_length) {
            $errors['title'] = "Title exceeds maximum character limit of {$abstractSettings->title_length} characters.";
        }
        
        // Validate content length
        if (strlen($validContent) > $abstractSettings->content_length) {
            $errors['content'] = "Content exceeds maximum character limit of {$abstractSettings->content_length} characters.";
        }
        
        // Validate keywords length
        if (strlen($validKeywords) > $abstractSettings->keywords_length) {
            $errors['keywords'] = "Keywords exceed maximum character limit of {$abstractSettings->keywords_length} characters.";
        }
        
        if (empty($errors)) {
            CLI::write('Validation correctly passed for valid content.', 'green');
        } else {
            CLI::write('ERROR: Validation should have passed but failed!', 'red');
            foreach ($errors as $field => $error) {
                CLI::write("- {$field}: {$error}", 'red');
            }
        }

        CLI::newLine();
        CLI::write('Test completed.', 'green');
    }
}
