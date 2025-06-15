<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ["url", "excel_helper", "date_helper"];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */    protected $session;
    protected $programModel;
    protected $programCategoryModel;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
        $this->session = \Config\Services::session();

        // Initialize program models for topbar data
        $this->programModel = new \App\Models\ProgramModel();
        $this->programCategoryModel = new \App\Models\ProgramCategoryModel();

        // Get program data for the topbar
        $this->loadTopbarData();
    }    /**
     * Load program data for topbar
     * This ensures program data is available across all views
     */
    protected function loadTopbarData()
    {
        // Skip program loading for reviewers since they're tied to a specific program
        if (session()->get('userType') === 'reviewer') {
            $this->loadReviewerTopbarData();
            return;
        }

        // Get program category with categoryWithPrograms
        $categoryWithPrograms = $this->programCategoryModel->getAllCategoriesWithPrograms();

        // Sort categoryWithPrograms by category name
        usort($categoryWithPrograms, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });       
        
        // Group by active and inactive
        $activePrograms = [];
        $inactivePrograms = [];        foreach ($categoryWithPrograms as $category) {
            $logoUrl = $category->logo_url ?? null;

            // Get categoryWithPrograms from category
            $currentCategoryPrograms = $category->programs ?? [];            
            // Filter active and inactive programs and add them to the respective arrays
            foreach ($currentCategoryPrograms as $program) {
                if ($program->is_active == 1) {
                    // Add logo URL to the program object
                    $program->logo_url = $logoUrl;
                    $activePrograms[] = $program;
                } else {
                    // Add logo URL to the program object
                    $program->logo_url = $logoUrl;
                    $inactivePrograms[] = $program;
                }
            }
        }

        $currentProgramId = session('current_program');
        $selectedProgram = null;

        // Check if a program is already selected
        if ($currentProgramId) {
            // Find the selected program in the list
            foreach ($categoryWithPrograms as $category) {
                foreach ($category->programs as $program) {
                    if ($program->id == $currentProgramId) {
                        $selectedProgram = $program;
                        break 2; // Break out of both loops
                    }
                }
            }

            // If the selected program is not found, unset the session variable
            if (!$selectedProgram) {
                session()->remove('current_program');
            }
        }        
          // Check if selected program is of type journal (id: 3)
        $isJournalType = false;
        if ($selectedProgram) {
            // Get directly from database to ensure fresh data
            $category = $this->programCategoryModel->find($selectedProgram->program_category_id);
            if ($category) {
                $isJournalType = ((int)$category->program_type_id === 3);
                log_message('debug', 'BaseController - Category ID: ' . $category->id);
                log_message('debug', 'BaseController - Program Type ID: ' . $category->program_type_id);
                log_message('debug', 'BaseController - Is Journal Type: ' . ($isJournalType ? 'true' : 'false'));
            }
        }

        // Share this data with all views
        $this->session->set('topbar_data', [
            'selectedProgram' => $selectedProgram,
            'activePrograms' => $activePrograms,
            'inactivePrograms' => $inactivePrograms,
            'categoryWithPrograms' => $categoryWithPrograms,
            'isJournalType' => $isJournalType,
        ]);
    }    /**
     * Load program data for reviewers
     * Reviewers are tied to a specific program, so we just load their program data
     */
    protected function loadReviewerTopbarData()
    {
        $reviewerId = session()->get('reviewerId');
        $reviewerProgramId = session()->get('reviewerProgramId');
        
        // Set the current_program session for reviewers to prevent any conflicts
        if ($reviewerProgramId && !session()->has('current_program')) {
            session()->set('current_program', $reviewerProgramId);
        }
        
        // Set cookie to indicate program is "selected" for reviewers (if not already set)
        if (!isset($_COOKIE['has_program_selected']) || $_COOKIE['has_program_selected'] !== 'true') {
            $cookie = [
                'name' => 'has_program_selected',
                'value' => 'true',
                'expire' => time() + (24 * 60 * 60), // 24 hours
                'path' => '/',
                'secure' => false,
                'httponly' => false
            ];
            
            // Use CodeIgniter's response service to set cookie
            $response = service('response');
            $response->setCookie($cookie);
        }
        
        // Set a simplified topbar data for reviewers
        $selectedProgram = null;
        
        if ($reviewerProgramId) {
            // Get the reviewer's assigned program
            $selectedProgram = $this->programModel->find($reviewerProgramId);
        }
        
        // Share simplified data for reviewers
        $this->session->set('topbar_data', [
            'selectedProgram' => $selectedProgram,
            'activePrograms' => [], // Reviewers don't need to see other programs
            'inactivePrograms' => [],
            'categoryWithPrograms' => [],
            'isJournalType' => false, // We can add this logic later if needed for reviewers
        ]);
    }
}
