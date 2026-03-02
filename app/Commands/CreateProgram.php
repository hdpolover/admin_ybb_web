<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ProgramTypeModel;
use App\Models\ProgramCategoryModel;
use App\Models\ProgramModel;
use App\Models\ProgramPaymentModel;
use App\Models\ProgramPaymentPeriodModel;
use App\Models\ProgramScheduleModel;
use App\Models\ProgramEssayModel;
use App\Models\ProgramSubthemeModel;
use App\Models\ProgramSpeakerModel;
use App\Models\ProgramDocumentModel;
use App\Models\ProgramRundownModel;
use App\Models\ProgramAwardModel;
use App\Models\FaqModel;
use App\Models\WebSettingModel;

/**
 * CLI Command to create a new program with all related records
 * 
 * Usage:
 *   php spark program:create
 *   php spark program:create --type="Summit" --name="Japan Youth Summit" --year=2026 --batch=1
 *   php spark program:create --category-id=4 --name="Korea Youth Summit 2026 Batch 3"
 */
class CreateProgram extends BaseCommand
{
    protected $group       = 'Program';
    protected $name        = 'program:create';
    protected $description = 'Create a new program with all related database records';

    protected $usage     = 'program:create [options]';
    protected $options   = [
        '--name'        => 'Program name (e.g., "Japan Youth Summit 2026")',
        '--clone-from'  => 'Source program ID to clone data from (e.g., 9 for KYS 2026)',
        '--type'        => 'Program type - Summit, Conference, Forum (default: Summit)',
        '--category'    => 'Program category name (default: same as program name)',
        '--category-id' => 'Use existing program category ID instead of creating new',
        '--year'        => 'Program year (default: current year)',
        '--batch'       => 'Batch number (optional, e.g., 1, 2, 3)',
        '--start-date'  => 'Program start date (YYYY-MM-DD)',
        '--end-date'    => 'Program end date (YYYY-MM-DD)',
        '--location'    => 'Program location (default: Jakarta, Indonesia)',
        '--self-funded' => 'Self funded amount in IDR (default: 15000000)',
        '--fully-funded'=> 'Fully funded processing fee in IDR (default: 500000)',
        '--web-url'     => 'Custom web URL slug',
        '--instagram'   => 'Instagram handle (e.g., @youthsummit)',
        '--interactive' => 'Force interactive mode even with params',
        '--skip-payments' => 'Skip creating payment records',
        '--template'    => 'Use template: korea, japan, istanbul, default',
    ];

    // Predefined templates for common programs
    protected $templates = [
        'korea' => [
            'location' => 'Seoul, South Korea',
            'self_funded' => 15000000,
            'fully_funded' => 500000,
            'instagram' => '@koreayouthsummit',
            'email' => 'info@koreayouthsummit.org',
        ],
        'japan' => [
            'location' => 'Tokyo, Japan',
            'self_funded' => 18000000,
            'fully_funded' => 750000,
            'instagram' => '@japanyouthsummitofficial',
            'email' => 'info@japanyouthsummit.org',
        ],
        'istanbul' => [
            'location' => 'Istanbul, Turkey',
            'self_funded' => 14000000,
            'fully_funded' => 500000,
            'instagram' => '@istanbulyouthsummit',
            'email' => 'info@istanbulyouthsummit.org',
        ],
        'default' => [
            'location' => 'Jakarta, Indonesia',
            'self_funded' => 12000000,
            'fully_funded' => 500000,
            'instagram' => '@ybbfoundation',
            'email' => 'info@ybbfoundation.com',
        ],
    ];

    public function run(array $params)
    {
        CLI::write("🚀 Dynamic Program Creation Tool", 'green');
        CLI::write("=================================", 'green');
        CLI::newLine();

        // Check if we should use a template
        $template = $params['template'] ?? null;
        $templateData = $template ? ($this->templates[$template] ?? $this->templates['default']) : [];

        // Determine if we should run in interactive mode
        $interactive = isset($params['interactive']) || (empty($params['name']) && empty($params['category-id']));

        // Get inputs (from params or prompt)
        $inputs = $this->gatherInputs($params, $templateData, $interactive);

        // Display summary
        $this->showSummary($inputs);

        // Confirm if interactive
        if ($interactive) {
            if (CLI::prompt('Continue with these settings?', ['y', 'n']) !== 'y') {
                CLI::write('❌ Operation cancelled', 'red');
                return;
            }
        } else {
            CLI::write('Creating program...', 'cyan');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Check if we're cloning from an existing program
            $sourceProgramId = $params['clone-from'] ?? null;
            
            // Step 1: Get or create Program Type
            $programTypeId = $this->getOrCreateProgramType($inputs['type']);
            
            // Step 2: Get or create Program Category
            $categoryId = $inputs['category_id'] ?? null;
            if (!$categoryId) {
                $categoryId = $this->getOrCreateProgramCategory($programTypeId, $inputs);
            }
            
            // Step 3: Create Program
            $programId = $this->createProgram($categoryId, $inputs);
            
            // Step 4: Create Web Settings
            $this->createWebSettings($categoryId);
            
            // Step 5: Create related data (clone or defaults)
            if ($sourceProgramId) {
                CLI::write("🔄 Cloning data from program ID: {$sourceProgramId}...", 'cyan');
                $this->clonePayments($sourceProgramId, $programId);
                $this->cloneSchedules($sourceProgramId, $programId, $inputs);
                $this->cloneEssays($sourceProgramId, $programId);
                $this->cloneSubthemes($sourceProgramId, $programId);
                $this->cloneDocuments($sourceProgramId, $programId);
                $this->cloneAwards($sourceProgramId, $programId);
                $this->cloneFaqs($sourceProgramId, $programId);
                $this->cloneSpeakers($sourceProgramId, $programId);
                $this->cloneRundowns($sourceProgramId, $programId, $inputs);
            } else {
                // Create default data
                if (!isset($params['skip-payments'])) {
                    $this->createProgramPayments($programId, $inputs);
                }
                $this->createDefaultSchedules($programId, $inputs);
                $this->createDefaultEssays($programId, $inputs);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            CLI::newLine();
            CLI::write("✅ Program created successfully!", 'green');
            CLI::write("Program ID: {$programId}", 'cyan');
            CLI::write("Category ID: {$categoryId}", 'cyan');
            CLI::write("Program Name: {$inputs['full_name']}", 'cyan');
            CLI::newLine();
            CLI::write("Next steps:", 'yellow');
            CLI::write("1. Update program details in admin panel", 'white');
            CLI::write("2. Upload program banner and assets", 'white');
            CLI::write("3. Configure payment amounts if needed", 'white');
            CLI::write("4. Add speakers, FAQ, and other content", 'white');
            
        } catch (\Exception $e) {
            $db->transRollback();
            CLI::newLine();
            CLI::write("❌ Error: " . $e->getMessage(), 'red');
            return 1;
        }
    }

    /**
     * Gather all inputs from params or interactive prompts
     */
    private function gatherInputs(array $params, array $templateData, bool $interactive): array
    {
        $currentYear = date('Y');
        
        $inputs = [];

        // Program Type
        if ($interactive) {
            CLI::write("Available program types:", 'cyan');
            $types = ['Summit', 'Conference', 'Forum', 'Festival', 'Workshop', 'Other'];
            foreach ($types as $i => $type) {
                CLI::write("  [{$i}] {$type}");
            }
            $typeIndex = CLI::prompt('Select program type', '0');
            $inputs['type'] = $types[$typeIndex] ?? 'Summit';
        } else {
            $inputs['type'] = $params['type'] ?? 'Summit';
        }

        // Program Name
        if ($interactive || empty($params['name'])) {
            $inputs['program_name'] = CLI::prompt('Program name (e.g., "Japan Youth Summit 2026")');
        } else {
            $inputs['program_name'] = $params['name'];
        }

        // Year
        $inputs['year'] = $params['year'] ?? $currentYear;
        if ($interactive && empty($params['year'])) {
            $inputs['year'] = CLI::prompt('Program year', $currentYear);
        }

        // Batch (optional)
        $inputs['batch'] = $params['batch'] ?? null;
        if ($interactive && !isset($params['batch'])) {
            $batchInput = CLI::prompt('Batch number (optional, press Enter to skip)');
            $inputs['batch'] = $batchInput ?: null;
        }

        // Full program name with batch
        if ($inputs['batch']) {
            $inputs['full_name'] = "{$inputs['program_name']} Batch {$inputs['batch']}";
        } else {
            $inputs['full_name'] = $inputs['program_name'];
        }

        // Category name
        $inputs['category_name'] = $params['category'] ?? $inputs['program_name'];
        if ($interactive && empty($params['category'])) {
            $inputs['category_name'] = CLI::prompt('Category name', $inputs['program_name']);
        }

        // Category ID (if using existing)
        $inputs['category_id'] = $params['category-id'] ?? null;

        // Location
        $inputs['location'] = $params['location'] ?? $templateData['location'] ?? 'Jakarta, Indonesia';
        if ($interactive && empty($params['location']) && empty($templateData)) {
            $inputs['location'] = CLI::prompt('Program location', 'Jakarta, Indonesia');
        }

        // Web URL
        if (!empty($params['web-url'])) {
            $inputs['web_url'] = $params['web-url'];
        } else {
            $baseUrl = strtolower(str_replace(' ', '-', $inputs['program_name']));
            $inputs['web_url'] = $baseUrl;
            if ($inputs['batch']) {
                $inputs['web_url'] .= "-batch-{$inputs['batch']}";
            }
        }

        // Dates
        $inputs['start_date'] = $params['start-date'] ?? null;
        $inputs['end_date'] = $params['end-date'] ?? null;
        
        if (!$inputs['start_date']) {
            $inputs['start_date'] = "{$inputs['year']}-06-01";
        }
        if (!$inputs['end_date']) {
            $inputs['end_date'] = "{$inputs['year']}-06-07";
        }

        if ($interactive && (empty($params['start-date']) || empty($params['end-date']))) {
            $inputs['start_date'] = CLI::prompt('Program start date (YYYY-MM-DD)', $inputs['start_date']);
            $inputs['end_date'] = CLI::prompt('Program end date (YYYY-MM-DD)', $inputs['end_date']);
        }

        // Payment amounts
        $inputs['self_funded'] = $params['self-funded'] ?? $templateData['self_funded'] ?? 15000000;
        $inputs['fully_funded'] = $params['fully-funded'] ?? $templateData['fully_funded'] ?? 500000;

        if ($interactive && empty($params['self-funded'])) {
            $inputs['self_funded'] = CLI::prompt('Self funded amount (IDR)', '15000000');
            $inputs['fully_funded'] = CLI::prompt('Fully funded processing fee (IDR)', '500000');
        }

        // Social media & contact
        $inputs['instagram'] = $params['instagram'] ?? $templateData['instagram'] ?? '@ybbfoundation';
        $inputs['email'] = $templateData['email'] ?? 'info@ybbfoundation.com';

        return $inputs;
    }

    /**
     * Show summary of inputs
     */
    private function showSummary(array $inputs): void
    {
        CLI::newLine();
        CLI::write("📋 Program Configuration:", 'yellow');
        CLI::write("-------------------------", 'yellow');
        CLI::write("Name:         {$inputs['full_name']}");
        CLI::write("Type:         {$inputs['type']}");
        CLI::write("Category:     {$inputs['category_name']}");
        CLI::write("Location:     {$inputs['location']}");
        CLI::write("Dates:        {$inputs['start_date']} to {$inputs['end_date']}");
        CLI::write("Web URL:      {$inputs['web_url']}");
        CLI::write("Self Funded:  IDR " . number_format($inputs['self_funded']));
        CLI::write("Fully Funded: IDR " . number_format($inputs['fully_funded']));
        CLI::write("Instagram:    {$inputs['instagram']}");
        CLI::newLine();
    }

    /**
     * Get or create Program Type
     */
    private function getOrCreateProgramType(string $typeName): int
    {
        $model = new ProgramTypeModel();
        
        $existing = $model->where('name', $typeName)
                         ->where('is_deleted', 0)
                         ->first();
        
        if ($existing) {
            CLI::write("✓ Using existing Program Type: {$typeName} (ID: {$existing->id})", 'cyan');
            return $existing->id;
        }

        $data = [
            'name' => $typeName,
            'description' => "{$typeName} Programs",
            'is_active' => 1,
            'is_deleted' => 0,
        ];
        
        $id = $model->insert($data);
        CLI::write("✓ Created Program Type: {$typeName} (ID: {$id})", 'cyan');
        return $id;
    }

    /**
     * Get or create Program Category
     */
    private function getOrCreateProgramCategory(int $programTypeId, array $inputs): int
    {
        $model = new ProgramCategoryModel();
        
        // Check if category already exists
        $existing = $model->where('name', $inputs['category_name'])
                         ->where('web_url', $inputs['web_url'])
                         ->where('is_deleted', 0)
                         ->first();
        
        if ($existing) {
            CLI::write("✓ Using existing Program Category: {$inputs['category_name']} (ID: {$existing->id})", 'cyan');
            return $existing->id;
        }

        $programBase = preg_replace('/\s+\d{4}$/', '', $inputs['program_name']);

        $data = [
            'name' => $inputs['category_name'],
            'description' => "{$programBase} Program {$inputs['year']} - Connecting young leaders from around the world",
            'program_type_id' => $programTypeId,
            'web_url' => $inputs['web_url'],
            'logo_url' => 'default-logo.png',
            'about' => "<p>The {$programBase} is an international program designed to empower young leaders...</p>",
            'core_values' => json_encode([
                'Leadership Development',
                'Cultural Exchange',
                'Global Networking',
                'Innovation & Entrepreneurship'
            ]),
            'objectives' => json_encode([
                'Develop leadership skills among youth',
                'Foster international collaboration',
                'Promote cultural understanding',
                'Create lasting global networks'
            ]),
            'benefits' => json_encode([
                'International certificate',
                'Networking opportunities',
                'Cultural immersion',
                'Leadership workshops'
            ]),
            'tagline' => 'Empowering Future Global Leaders',
            'contact' => '+62 812-3456-7890',
            'location' => $inputs['location'],
            'email' => $inputs['email'],
            'instagram' => $inputs['instagram'],
            'tiktok' => null,
            'youtube' => null,
            'telegram' => null,
            'verification_required' => 1,
            'is_active' => 1,
            'is_deleted' => 0,
        ];
        
        $id = $model->insert($data);
        CLI::write("✓ Created Program Category: {$inputs['category_name']} (ID: {$id})", 'cyan');
        return $id;
    }

    /**
     * Create the main Program record
     */
    private function createProgram(int $categoryId, array $inputs): int
    {
        $model = new ProgramModel();
        
        $programBase = preg_replace('/\s+\d{4}$/', '', $inputs['program_name']);

        $data = [
            'program_category_id' => $categoryId,
            'name' => $inputs['full_name'],
            'banner_url' => 'default-banner.jpg',
            'description' => "<p>Welcome to {$inputs['full_name']}...</p>",
            'guideline' => '<h3>Program Guidelines</h3><p>Please read carefully...</p>',
            'main_essay_question' => "Why do you want to join {$programBase} and what do you hope to achieve?",
            'essay_guideline_url' => null,
            'twibbon' => null,
            'twibbon_video_url' => null,
            'start_date' => $inputs['start_date'],
            'end_date' => $inputs['end_date'],
            'registration_video_url' => null,
            'tshirt_chart_url' => null,
            'theme' => 'Empowering Future Global Leaders',
            'share_desc' => "I just registered for {$inputs['program_name']}! Join me in this amazing journey.",
            'confirmation_desc' => '<p>Thank you for registering! Please complete your payment to secure your spot.</p>',
            'is_active' => 0,
            'is_registration_open' => 0,
            'is_deleted' => 0,
        ];
        
        $id = $model->insert($data);
        CLI::write("✓ Created Program: {$inputs['full_name']} (ID: {$id})", 'cyan');
        return $id;
    }

    /**
     * Create Web Settings for the category
     */
    private function createWebSettings(int $categoryId): void
    {
        $model = new WebSettingModel();
        
        $existing = $model->where('program_category_id', $categoryId)->first();
        if ($existing) {
            CLI::write("✓ Web Settings already exist for category {$categoryId}", 'cyan');
            return;
        }
        
        $data = [
            'program_category_id' => $categoryId,
            'is_maintenance_mode' => 0,
            'usd_in_idr' => 16000,
            'is_verification_required' => 1,
        ];
        
        $id = $model->insert($data);
        CLI::write("✓ Created Web Settings (ID: {$id})", 'cyan');
    }

    /**
     * Create Program Payments with periods
     */
    private function createProgramPayments(int $programId, array $inputs): void
    {
        $paymentModel = new ProgramPaymentModel();
        $periodModel = new ProgramPaymentPeriodModel();
        
        $year = $inputs['year'];
        
        // Self Funded Payment
        $selfFundedData = [
            'program_id' => $programId,
            'name' => 'Self Funded Registration',
            'description' => 'Registration fee for self-funded participants',
            'order_number' => 1,
            'idr_amount' => $inputs['self_funded'],
            'usd_amount' => round($inputs['self_funded'] / 16000),
            'category' => 'registration',
            'type' => 'self_funded',
            'is_active' => 0,
            'is_deleted' => 0,
        ];
        $selfFundedId = $paymentModel->insert($selfFundedData);
        
        $periodModel->insert([
            'payment_id' => $selfFundedId,
            'parent_period_id' => null,
            'extension_type' => 'continuation',
            'name' => 'Early Bird',
            'description' => 'Early bird registration period',
            'start_date' => "{$year}-01-01 00:00:00",
            'end_date' => "{$year}-03-31 23:59:59",
            'order_number' => 1,
            'is_active' => 1,
            'is_deleted' => 0,
        ]);
        
        // Fully Funded Payment
        $fullyFundedData = [
            'program_id' => $programId,
            'name' => 'Fully Funded Registration',
            'description' => 'Registration fee for fully-funded participants (processing fee only)',
            'order_number' => 2,
            'idr_amount' => $inputs['fully_funded'],
            'usd_amount' => round($inputs['fully_funded'] / 16000),
            'category' => 'registration',
            'type' => 'fully_funded',
            'is_active' => 0,
            'is_deleted' => 0,
        ];
        $fullyFundedId = $paymentModel->insert($fullyFundedData);
        
        $periodModel->insert([
            'payment_id' => $fullyFundedId,
            'parent_period_id' => null,
            'extension_type' => 'continuation',
            'name' => 'Application Period',
            'description' => 'Fully funded application period',
            'start_date' => "{$year}-01-01 00:00:00",
            'end_date' => "{$year}-04-30 23:59:59",
            'order_number' => 1,
            'is_active' => 1,
            'is_deleted' => 0,
        ]);
        
        CLI::write("✓ Created Program Payments", 'cyan');
    }

    /**
     * Create default program schedules
     */
    private function createDefaultSchedules(int $programId, array $inputs): void
    {
        $model = new ProgramScheduleModel();
        $year = $inputs['year'];
        
        $schedules = [
            [
                'program_id' => $programId,
                'name' => 'Registration Period',
                'description' => 'Online registration and document submission',
                'start_date' => "{$year}-01-01",
                'end_date' => "{$year}-04-30",
                'order_number' => 1,
                'is_active' => 1,
                'is_deleted' => 0,
            ],
            [
                'program_id' => $programId,
                'name' => 'Selection Process',
                'description' => 'Application review and participant selection',
                'start_date' => "{$year}-05-01",
                'end_date' => "{$year}-05-15",
                'order_number' => 2,
                'is_active' => 1,
                'is_deleted' => 0,
            ],
            [
                'program_id' => $programId,
                'name' => 'Program Execution',
                'description' => 'Main program activities in ' . $inputs['location'],
                'start_date' => $inputs['start_date'],
                'end_date' => $inputs['end_date'],
                'order_number' => 3,
                'is_active' => 1,
                'is_deleted' => 0,
            ],
            [
                'program_id' => $programId,
                'name' => 'Post-Program',
                'description' => 'Certificate distribution and alumni network activation',
                'start_date' => date('Y-m-d', strtotime($inputs['end_date'] . ' +1 day')),
                'end_date' => "{$year}-06-30",
                'order_number' => 4,
                'is_active' => 1,
                'is_deleted' => 0,
            ],
        ];
        
        foreach ($schedules as $schedule) {
            $model->insert($schedule);
        }
        
        CLI::write("✓ Created Default Schedules (" . count($schedules) . " items)", 'cyan');
    }

    /**
     * Create default essay questions
     */
    private function createDefaultEssays(int $programId, array $inputs): void
    {
        $model = new ProgramEssayModel();
        $programBase = preg_replace('/\s+\d{4}$/', '', $inputs['program_name']);
        
        $essays = [
            [
                'program_id' => $programId,
                'questions' => 'Tell us about yourself, your background, and your achievements.',
                'max_word_count' => 500,
                'is_active' => 1,
                'is_deleted' => 0,
            ],
            [
                'program_id' => $programId,
                'questions' => "Why do you want to join {$programBase} and what do you hope to achieve?",
                'max_word_count' => 500,
                'is_active' => 1,
                'is_deleted' => 0,
            ],
            [
                'program_id' => $programId,
                'questions' => 'Describe a challenge you have faced and how you overcame it.',
                'max_word_count' => 400,
                'is_active' => 1,
                'is_deleted' => 0,
            ],
            [
                'program_id' => $programId,
                'questions' => 'How do you plan to contribute to your community after attending this program?',
                'max_word_count' => 400,
                'is_active' => 1,
                'is_deleted' => 0,
            ],
        ];
        
        foreach ($essays as $essay) {
            $model->insert($essay);
        }
        
        CLI::write("✓ Created Default Essay Questions (" . count($essays) . " items)", 'cyan');
    }

    // =================================================================
    // CLONE METHODS - Copy data from existing program
    // =================================================================

    private function clonePayments(int $sourceId, int $newProgramId): void
    {
        $paymentModel = new ProgramPaymentModel();
        $periodModel = new ProgramPaymentPeriodModel();
        
        $payments = $paymentModel->where('program_id', $sourceId)
                                  ->where('is_deleted', 0)
                                  ->findAll();
        
        foreach ($payments as $payment) {
            $newPaymentId = $paymentModel->insert([
                'program_id' => $newProgramId,
                'name' => $payment->name,
                'description' => $payment->description,
                'order_number' => $payment->order_number,
                'idr_amount' => $payment->idr_amount,
                'usd_amount' => $payment->usd_amount,
                'category' => $payment->category,
                'type' => $payment->type,
                'is_active' => 0,
                'is_deleted' => 0,
            ]);

            // Clone periods for this payment
            $periods = $periodModel->where('payment_id', $payment->id)
                                   ->where('is_deleted', 0)
                                   ->findAll();
            
            foreach ($periods as $period) {
                $periodModel->insert([
                    'payment_id' => $newPaymentId,
                    'parent_period_id' => null,
                    'extension_type' => $period->extension_type,
                    'name' => $period->name,
                    'description' => $period->description,
                    'start_date' => $period->start_date,
                    'end_date' => $period->end_date,
                    'order_number' => $period->order_number,
                    'is_active' => 0,
                    'is_deleted' => 0,
                ]);
            }
        }
        
        CLI::write("✓ Cloned " . count($payments) . " payment(s)", 'cyan');
    }

    private function cloneSchedules(int $sourceId, int $newProgramId, array $inputs): void
    {
        $model = new ProgramScheduleModel();
        $schedules = $model->where('program_id', $sourceId)
                           ->where('is_deleted', 0)
                           ->findAll();
        
        foreach ($schedules as $schedule) {
            $model->insert([
                'program_id' => $newProgramId,
                'name' => $schedule->name,
                'description' => $schedule->description,
                'start_date' => $this->shiftDate($schedule->start_date, $inputs['year']),
                'end_date' => $this->shiftDate($schedule->end_date, $inputs['year']),
                'order_number' => $schedule->order_number,
                'is_active' => $schedule->is_active,
                'is_deleted' => 0,
            ]);
        }
        
        CLI::write("✓ Cloned " . count($schedules) . " schedule(s)", 'cyan');
    }

    private function cloneEssays(int $sourceId, int $newProgramId): void
    {
        $model = new ProgramEssayModel();
        $essays = $model->where('program_id', $sourceId)
                        ->where('is_deleted', 0)
                        ->findAll();
        
        foreach ($essays as $essay) {
            $model->insert([
                'program_id' => $newProgramId,
                'questions' => $essay->questions,
                'max_word_count' => $essay->max_word_count,
                'is_active' => $essay->is_active,
                'is_deleted' => 0,
            ]);
        }
        
        CLI::write("✓ Cloned " . count($essays) . " essay question(s)", 'cyan');
    }

    private function cloneSubthemes(int $sourceId, int $newProgramId): void
    {
        $model = new ProgramSubthemeModel();
        $subthemes = $model->where('program_id', $sourceId)
                           ->where('is_deleted', 0)
                           ->findAll();
        
        foreach ($subthemes as $subtheme) {
            $model->insert([
                'program_id' => $newProgramId,
                'name' => $subtheme->name,
                'desc' => $subtheme->desc,
                'is_active' => $subtheme->is_active,
                'is_deleted' => 0,
            ]);
        }
        
        CLI::write("✓ Cloned " . count($subthemes) . " subtheme(s)", 'cyan');
    }

    private function cloneDocuments(int $sourceId, int $newProgramId): void
    {
        $model = new ProgramDocumentModel();
        $documents = $model->where('program_id', $sourceId)
                           ->where('is_deleted', 0)
                           ->findAll();
        
        foreach ($documents as $doc) {
            $model->insert([
                'program_id' => $newProgramId,
                'type' => $doc->type,
                'name' => $doc->name,
                'file_url' => $doc->file_url,
                'drive_url' => $doc->drive_url,
                'desc' => $doc->desc,
                'is_upload' => $doc->is_upload,
                'is_generated' => $doc->is_generated,
                'visibility' => $doc->visibility,
                'is_active' => $doc->is_active,
                'is_deleted' => 0,
            ]);
        }
        
        CLI::write("✓ Cloned " . count($documents) . " document(s)", 'cyan');
    }

    private function cloneAwards(int $sourceId, int $newProgramId): void
    {
        $model = new ProgramAwardModel();
        $awards = $model->where('program_id', $sourceId)
                        ->where('is_deleted', 0)
                        ->findAll();
        
        foreach ($awards as $award) {
            $model->insert([
                'program_id' => $newProgramId,
                'title' => $award->title,
                'description' => $award->description,
                'award_type' => $award->award_type,
                'order_number' => $award->order_number,
                'is_active' => $award->is_active,
                'is_deleted' => 0,
            ]);
        }
        
        CLI::write("✓ Cloned " . count($awards) . " award(s)", 'cyan');
    }

    private function cloneFaqs(int $sourceId, int $newProgramId): void
    {
        $model = new FaqModel();
        $faqs = $model->where('program_id', $sourceId)
                      ->where('is_deleted', 0)
                      ->findAll();
        
        foreach ($faqs as $faq) {
            $model->insert([
                'program_id' => $newProgramId,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'faq_category' => $faq->faq_category,
                'order_number' => $faq->order_number,
                'is_active' => $faq->is_active,
                'is_deleted' => 0,
            ]);
        }
        
        CLI::write("✓ Cloned " . count($faqs) . " FAQ(s)", 'cyan');
    }

    private function cloneSpeakers(int $sourceId, int $newProgramId): void
    {
        $model = new ProgramSpeakerModel();
        $speakers = $model->where('program_id', $sourceId)
                          ->where('is_deleted', 0)
                          ->findAll();
        
        foreach ($speakers as $speaker) {
            $model->insert([
                'program_id' => $newProgramId,
                'name' => $speaker->name,
                'title' => $speaker->title,
                'bio' => $speaker->bio,
                'photo_url' => $speaker->photo_url,
                'linkedin_url' => $speaker->linkedin_url,
                'instagram_url' => $speaker->instagram_url,
                'email' => $speaker->email,
                'organization' => $speaker->organization,
                'expertise_areas' => $speaker->expertise_areas,
                'is_keynote' => $speaker->is_keynote,
                'session_title' => $speaker->session_title,
                'session_description' => $speaker->session_description,
                'session_time' => $speaker->session_time,
                'order_number' => $speaker->order_number,
                'is_active' => $speaker->is_active,
                'is_deleted' => 0,
            ]);
        }
        
        CLI::write("✓ Cloned " . count($speakers) . " speaker(s)", 'cyan');
    }

    private function cloneRundowns(int $sourceId, int $newProgramId, array $inputs): void
    {
        $model = new ProgramRundownModel();
        $rundowns = $model->where('program_id', $sourceId)
                          ->where('is_deleted', 0)
                          ->findAll();
        
        foreach ($rundowns as $rundown) {
            $model->insert([
                'program_id' => $newProgramId,
                'start_date' => $this->shiftDate($rundown->start_date, $inputs['year']),
                'end_date' => $this->shiftDate($rundown->end_date, $inputs['year']),
                'title' => $rundown->title,
                'description' => $rundown->description,
                'order_number' => $rundown->order_number,
                'is_active' => $rundown->is_active,
                'is_deleted' => 0,
            ]);
        }
        
        CLI::write("✓ Cloned " . count($rundowns) . " rundown item(s)", 'cyan');
    }

    /**
     * Helper to shift dates to new year
     */
    private function shiftDate(?string $date, string $targetYear): ?string
    {
        if (!$date) return null;
        $originalYear = date('Y', strtotime($date));
        return str_replace($originalYear, $targetYear, $date);
    }
}
