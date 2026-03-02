<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ProgramCategoryModel;
use App\Models\ProgramModel;
use App\Models\ProgramPaymentModel;

/**
 * CLI Command to list existing programs and categories
 * 
 * Usage:
 *   php spark program:list
 *   php spark program:list --category=5
 */
class ListPrograms extends BaseCommand
{
    protected $group       = 'Program';
    protected $name        = 'program:list';
    protected $description = 'List all programs and categories';

    protected $usage     = 'program:list [options]';
    protected $options   = [
        '--category' => 'Filter by category ID',
        '--payments' => 'Show payment details',
    ];

    public function run(array $params)
    {
        $categoryId = $params['category'] ?? null;
        $showPayments = isset($params['payments']);

        CLI::write("📋 Program List", 'green');
        CLI::write("===============", 'green');
        CLI::newLine();

        $categoryModel = new ProgramCategoryModel();
        $programModel = new ProgramModel();
        $paymentModel = new ProgramPaymentModel();

        if ($categoryId) {
            // Show specific category
            $category = $categoryModel->find($categoryId);
            if (!$category) {
                CLI::write("❌ Category not found: {$categoryId}", 'red');
                return 1;
            }
            $this->showCategory($category, $programModel, $paymentModel, $showPayments);
        } else {
            // Show all categories
            $categories = $categoryModel->where('is_deleted', 0)->findAll();
            
            if (empty($categories)) {
                CLI::write("No categories found.", 'yellow');
                return;
            }

            foreach ($categories as $category) {
                $this->showCategory($category, $programModel, $paymentModel, $showPayments);
                CLI::newLine();
            }
        }

        // Summary
        $totalCategories = $categoryModel->where('is_deleted', 0)->countAllResults();
        $totalPrograms = $programModel->where('is_deleted', 0)->countAllResults();
        
        CLI::write("Summary:", 'green');
        CLI::write("  Total Categories: {$totalCategories}", 'cyan');
        CLI::write("  Total Programs: {$totalPrograms}", 'cyan');
    }

    private function showCategory($category, $programModel, $paymentModel, $showPayments)
    {
        $status = $category->is_active ? '🟢' : '🔴';
        CLI::write("{$status} Category: {$category->name}", 'yellow');
        CLI::write("   ID: {$category->id} | URL: {$category->web_url}", 'white');
        
        if ($category->instagram) {
            CLI::write("   Instagram: {$category->instagram}", 'white');
        }

        // Get programs in this category
        $programs = $programModel->where('program_category_id', $category->id)
                                 ->where('is_deleted', 0)
                                 ->findAll();
        
        if (!empty($programs)) {
            CLI::write("   Programs:", 'cyan');
            foreach ($programs as $program) {
                $progStatus = $program->is_active ? '🟢' : '🔴';
                $regStatus = $program->is_registration_open ? '(Registration Open)' : '(Registration Closed)';
                CLI::write("     {$progStatus} {$program->name} (ID: {$program->id}) {$regStatus}", 'white');
                CLI::write("        Dates: {$program->start_date} to {$program->end_date}", 'white');
                
                if ($showPayments) {
                    $this->showProgramPayments($program->id, $paymentModel);
                }
            }
        } else {
            CLI::write("   Programs: None", 'cyan');
        }
    }

    private function showProgramPayments(int $programId, $paymentModel)
    {
        $payments = $paymentModel->getByProgramId($programId, false, false);
        
        if (!empty($payments)) {
            foreach ($payments as $payment) {
                $idr = number_format($payment->idr_amount, 0, ',', '.');
                $usd = number_format($payment->usd_amount, 2);
                CLI::write("        💰 {$payment->name}: IDR {$idr} / USD {$usd}", 'white');
            }
        }
    }
}
