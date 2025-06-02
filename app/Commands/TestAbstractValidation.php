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
    protected $description = 'Test abstract validation with word limits';

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
        CLI::write('Testing Abstract Word Limit Validation', 'green');
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
        
        // Create test data that exceeds word limits
        $longTitle = str_repeat('Word ', $abstractSettings->title_length + 10);
        $longContent = str_repeat('Content word ', $abstractSettings->content_length + 50);
        $longKeywords = str_repeat('Keyword ', $abstractSettings->keywords_length + 5);
        
        $errors = [];
        
        // Validate title word count
        if ($this->countWords($longTitle) > $abstractSettings->title_length) {
            $errors['title'] = "Title exceeds maximum word limit of {$abstractSettings->title_length} words.";
        }
        
        // Validate content word count
        if ($this->countWords($longContent) > $abstractSettings->content_length) {
            $errors['content'] = "Content exceeds maximum word limit of {$abstractSettings->content_length} words.";
        }
        
        // Validate keywords word count
        if ($this->countWords($longKeywords) > $abstractSettings->keywords_length) {
            $errors['keywords'] = "Keywords exceed maximum word limit of {$abstractSettings->keywords_length} words.";
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
        
        // Validate title word count
        if ($this->countWords($validTitle) > $abstractSettings->title_length) {
            $errors['title'] = "Title exceeds maximum word limit of {$abstractSettings->title_length} words.";
        }
        
        // Validate content word count
        if ($this->countWords($validContent) > $abstractSettings->content_length) {
            $errors['content'] = "Content exceeds maximum word limit of {$abstractSettings->content_length} words.";
        }
        
        // Validate keywords word count
        if ($this->countWords($validKeywords) > $abstractSettings->keywords_length) {
            $errors['keywords'] = "Keywords exceed maximum word limit of {$abstractSettings->keywords_length} words.";
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

    /**
     * Count words in a text string
     * 
     * @param string $text
     * @return int
     */
    private function countWords($text)
    {
        // Remove HTML tags if present
        $text = strip_tags($text);
        
        // Remove extra whitespace and trim
        $text = preg_replace('/\s+/', ' ', trim($text));
        
        // If empty after cleaning, return 0
        if (empty($text)) {
            return 0;
        }
        
        // Split by spaces and count
        return count(explode(' ', $text));
    }
}
