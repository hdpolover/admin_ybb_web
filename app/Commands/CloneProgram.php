<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
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
use App\Models\AnnouncementModel;

/**
 * CLI Command to clone an existing program
 * 
 * Usage:
 *   php spark program:clone
 *   php spark program:clone --source=5 --name="New Program Name"
 */
class CloneProgram extends BaseCommand
{
    protected $group       = 'Program';
    protected $name        = 'program:clone';
    protected $description = 'Clone an existing program with all related data';

    protected $usage     = 'program:clone [options]';
    protected $options   = [
        '--source' => 'Source program ID to clone from',
        '--name'   => 'New program name',
        '--batch'  => 'Batch number for auto-generated name',
    ];

    public function run(array $params)
    {
        CLI::write("🔄 Program Clone Tool", 'green');
        CLI::write("=====================", 'green');
        CLI::newLine();

        $programModel = new ProgramModel();
        
        // Get source program
        $sourceId = $params['source'] ?? null;
        if (!$sourceId) {
            // List available programs
            $programs = $programModel->where('is_deleted', 0)->findAll();
            CLI::write("Available programs:", 'cyan');
            foreach ($programs as $prog) {
                CLI::write("  [{$prog->id}] {$prog->name}", 'white');
            }
            CLI::newLine();
            $sourceId = CLI::prompt('Enter source program ID');
        }

        $sourceProgram = $programModel->find($sourceId);
        if (!$sourceProgram) {
            CLI::write("❌ Source program not found: {$sourceId}", 'red');
            return 1;
        }

        CLI::write("Source: {$sourceProgram->name} (ID: {$sourceId})", 'yellow');

        // Get new program name
        $newName = $params['name'] ?? null;
        if (!$newName) {
            $batch = $params['batch'] ?? '2';
            $suggestedName = preg_replace('/Batch \d+/', "Batch {$batch}", $sourceProgram->name);
            $suggestedName = preg_replace('/\d{4}/', date('Y'), $suggestedName);
            $newName = CLI::prompt('New program name', $suggestedName);
        }

        // Auto-confirm when running non-interactively
        if (empty($params['source']) || empty($params['name'])) {
            if (CLI::prompt("Clone '{$sourceProgram->name}' to '{$newName}'?", ['y', 'n']) !== 'y') {
                CLI::write('❌ Operation cancelled', 'red');
                return;
            }
        } else {
            CLI::write("Auto-cloning to '{$newName}'...", 'cyan');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Clone program
            $newProgramId = $this->cloneProgram($sourceProgram, $newName);
            
            // Clone related data
            $this->clonePayments($sourceId, $newProgramId);
            $this->cloneSchedules($sourceId, $newProgramId);
            $this->cloneEssays($sourceId, $newProgramId);
            $this->cloneSubthemes($sourceId, $newProgramId);
            $this->cloneSpeakers($sourceId, $newProgramId);
            $this->cloneDocuments($sourceId, $newProgramId);
            $this->cloneRundowns($sourceId, $newProgramId);
            $this->cloneAwards($sourceId, $newProgramId);
            $this->cloneFaqs($sourceId, $newProgramId);
            $this->cloneAnnouncements($sourceId, $newProgramId);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            CLI::newLine();
            CLI::write("✅ Program cloned successfully!", 'green');
            CLI::write("New Program ID: {$newProgramId}", 'cyan');
            CLI::write("Name: {$newName}", 'cyan');
            
        } catch (\Exception $e) {
            $db->transRollback();
            CLI::newLine();
            CLI::write("❌ Error: " . $e->getMessage(), 'red');
            return 1;
        }
    }

    private function cloneProgram($source, string $newName): int
    {
        $model = new ProgramModel();
        
        $data = [
            'program_category_id' => $source->program_category_id,
            'name' => $newName,
            'banner_url' => $source->banner_url,
            'description' => $source->description,
            'guideline' => $source->guideline,
            'main_essay_question' => $source->main_essay_question,
            'essay_guideline_url' => $source->essay_guideline_url,
            'twibbon' => $source->twibbon,
            'twibbon_video_url' => $source->twibbon_video_url,
            'start_date' => $source->start_date,
            'end_date' => $source->end_date,
            'registration_video_url' => $source->registration_video_url,
            'tshirt_chart_url' => $source->tshirt_chart_url,
            'theme' => $source->theme,
            'share_desc' => $source->share_desc,
            'confirmation_desc' => $source->confirmation_desc,
            'is_active' => 0, // Start as inactive
            'is_registration_open' => 0, // Start with closed registration
            'is_deleted' => 0,
        ];
        
        $id = $model->insert($data);
        CLI::write("✓ Cloned program record (ID: {$id})", 'cyan');
        return $id;
    }

    private function clonePayments(int $sourceId, int $newProgramId): void
    {
        $paymentModel = new ProgramPaymentModel();
        $periodModel = new ProgramPaymentPeriodModel();
        
        $payments = $paymentModel->where('program_id', $sourceId)
                                  ->where('is_deleted', 0)
                                  ->findAll();
        
        $count = 0;
        foreach ($payments as $payment) {
            $newPaymentData = [
                'program_id' => $newProgramId,
                'name' => $payment->name,
                'description' => $payment->description,
                'order_number' => $payment->order_number,
                'idr_amount' => $payment->idr_amount,
                'usd_amount' => $payment->usd_amount,
                'category' => $payment->category,
                'type' => $payment->type,
                'is_active' => 0, // Start inactive
                'is_deleted' => 0,
            ];
            $newPaymentId = $paymentModel->insert($newPaymentData);
            
            // Clone periods for this payment
            $periods = $periodModel->where('payment_id', $payment->id)
                                   ->where('is_deleted', 0)
                                   ->findAll();
            
            foreach ($periods as $period) {
                $periodModel->insert([
                    'payment_id' => $newPaymentId,
                    'parent_period_id' => null, // Reset parent relationship
                    'extension_type' => $period->extension_type,
                    'name' => $period->name,
                    'description' => $period->description,
                    'start_date' => $period->start_date,
                    'end_date' => $period->end_date,
                    'order_number' => $period->order_number,
                    'is_active' => 0, // Start inactive
                    'is_deleted' => 0,
                ]);
            }
            $count++;
        }
        
        CLI::write("✓ Cloned {$count} payment records", 'cyan');
    }

    private function cloneSchedules(int $sourceId, int $newProgramId): void
    {
        $model = new ProgramScheduleModel();
        $items = $model->where('program_id', $sourceId)
                       ->where('is_deleted', 0)
                       ->findAll();
        
        $count = 0;
        foreach ($items as $item) {
            $model->insert([
                'program_id' => $newProgramId,
                'name' => $item->name,
                'description' => $item->description,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'order_number' => $item->order_number,
                'is_active' => $item->is_active,
                'is_deleted' => 0,
            ]);
            $count++;
        }
        
        CLI::write("✓ Cloned {$count} schedule records", 'cyan');
    }

    private function cloneEssays(int $sourceId, int $newProgramId): void
    {
        $model = new ProgramEssayModel();
        $items = $model->where('program_id', $sourceId)
                       ->where('is_deleted', 0)
                       ->findAll();
        
        $count = 0;
        foreach ($items as $item) {
            $model->insert([
                'program_id' => $newProgramId,
                'questions' => $item->questions,
                'max_word_count' => $item->max_word_count,
                'is_active' => $item->is_active,
                'is_deleted' => 0,
            ]);
            $count++;
        }
        
        CLI::write("✓ Cloned {$count} essay questions", 'cyan');
    }

    private function cloneSubthemes(int $sourceId, int $newProgramId): void
    {
        $model = new ProgramSubthemeModel();
        $items = $model->where('program_id', $sourceId)
                       ->where('is_deleted', 0)
                       ->findAll();
        
        $count = 0;
        foreach ($items as $item) {
            $model->insert([
                'program_id' => $newProgramId,
                'name' => $item->name,
                'desc' => $item->desc,
                'is_active' => $item->is_active,
                'is_deleted' => 0,
            ]);
            $count++;
        }
        
        CLI::write("✓ Cloned {$count} subthemes", 'cyan');
    }

    private function cloneSpeakers(int $sourceId, int $newProgramId): void
    {
        $model = new ProgramSpeakerModel();
        $items = $model->where('program_id', $sourceId)
                       ->where('is_deleted', 0)
                       ->findAll();
        
        $count = 0;
        foreach ($items as $item) {
            $model->insert([
                'program_id' => $newProgramId,
                'name' => $item->name,
                'title' => $item->title,
                'bio' => $item->bio,
                'photo_url' => $item->photo_url,
                'linkedin_url' => $item->linkedin_url,
                'instagram_url' => $item->instagram_url,
                'email' => $item->email,
                'organization' => $item->organization,
                'expertise_areas' => $item->expertise_areas,
                'is_keynote' => $item->is_keynote,
                'session_title' => $item->session_title,
                'session_description' => $item->session_description,
                'session_time' => $item->session_time,
                'order_number' => $item->order_number,
                'is_active' => $item->is_active,
                'is_deleted' => 0,
            ]);
            $count++;
        }
        
        CLI::write("✓ Cloned {$count} speakers", 'cyan');
    }

    private function cloneDocuments(int $sourceId, int $newProgramId): void
    {
        $model = new ProgramDocumentModel();
        $items = $model->where('program_id', $sourceId)
                       ->where('is_deleted', 0)
                       ->findAll();
        
        $count = 0;
        foreach ($items as $item) {
            $model->insert([
                'program_id' => $newProgramId,
                'type' => $item->type,
                'name' => $item->name,
                'file_url' => $item->file_url,
                'drive_url' => $item->drive_url,
                'desc' => $item->desc,
                'is_upload' => $item->is_upload,
                'is_generated' => $item->is_generated,
                'visibility' => $item->visibility,
                'is_active' => $item->is_active,
                'is_deleted' => 0,
            ]);
            $count++;
        }
        
        CLI::write("✓ Cloned {$count} documents", 'cyan');
    }

    private function cloneRundowns(int $sourceId, int $newProgramId): void
    {
        $model = new ProgramRundownModel();
        $items = $model->where('program_id', $sourceId)
                       ->where('is_deleted', 0)
                       ->findAll();
        
        $count = 0;
        foreach ($items as $item) {
            $model->insert([
                'program_id' => $newProgramId,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'title' => $item->title,
                'description' => $item->description,
                'order_number' => $item->order_number,
                'is_active' => $item->is_active,
                'is_deleted' => 0,
            ]);
            $count++;
        }
        
        CLI::write("✓ Cloned {$count} rundown items", 'cyan');
    }

    private function cloneAwards(int $sourceId, int $newProgramId): void
    {
        $model = new ProgramAwardModel();
        $items = $model->where('program_id', $sourceId)
                       ->where('is_deleted', 0)
                       ->findAll();
        
        $count = 0;
        foreach ($items as $item) {
            $model->insert([
                'program_id' => $newProgramId,
                'title' => $item->title,
                'description' => $item->description,
                'award_type' => $item->award_type,
                'order_number' => $item->order_number,
                'is_active' => $item->is_active,
                'is_deleted' => 0,
            ]);
            $count++;
        }
        
        CLI::write("✓ Cloned {$count} awards", 'cyan');
    }

    private function cloneFaqs(int $sourceId, int $newProgramId): void
    {
        $model = new FaqModel();
        $items = $model->where('program_id', $sourceId)
                       ->where('is_deleted', 0)
                       ->findAll();
        
        $count = 0;
        foreach ($items as $item) {
            $model->insert([
                'program_id' => $newProgramId,
                'question' => $item->question,
                'answer' => $item->answer,
                'faq_category' => $item->faq_category,
                'order_number' => $item->order_number,
                'is_active' => $item->is_active,
                'is_deleted' => 0,
            ]);
            $count++;
        }
        
        CLI::write("✓ Cloned {$count} FAQs", 'cyan');
    }

    private function cloneAnnouncements(int $sourceId, int $newProgramId): void
    {
        $model = new AnnouncementModel();
        $items = $model->where('program_id', $sourceId)
                       ->where('is_deleted', 0)
                       ->findAll();
        
        $count = 0;
        foreach ($items as $item) {
            $model->insert([
                'program_id' => $newProgramId,
                'title' => $item->title,
                'content' => $item->content,
                'img_url' => $item->img_url,
                'visible_to' => $item->visible_to,
                'is_active' => 0, // Start inactive
                'is_deleted' => 0,
                'slug' => $item->slug . '-' . time(), // Ensure unique slug
                'meta_title' => $item->meta_title,
                'meta_description' => $item->meta_description,
                'tags' => $item->tags,
            ]);
            $count++;
        }
        
        CLI::write("✓ Cloned {$count} announcements (as inactive)", 'cyan');
    }
}
