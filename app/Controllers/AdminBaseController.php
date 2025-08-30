<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Services\MenuService;
use App\Models\AdminModel;

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
abstract class AdminBaseController extends Controller
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
    protected $helpers = ['menu'];

    /**
     * Current user data
     *
     * @var object|null
     */
    protected $currentUser;

    /**
     * Current user type (admin/reviewer)
     *
     * @var string
     */
    protected $userType = 'admin';    /**
     * Current user role
     *
     * @var string
     */
    protected $userRole = 'super_admin';

    /**
     * Menu items for current user
     *
     * @var array
     */
    protected $menuItems = [];

    /**
     * Session instance
     *
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $this->session = session();
        
        // Load current user and menu
        $this->loadCurrentUser();
        $this->loadUserMenu();
    }

    /**
     * Load current user data
     */
    protected function loadCurrentUser()
    {
        if (!$this->session->get('isLoggedIn')) {
            return;
        }

        $adminId = $this->session->get('adminId');
        if ($adminId) {
            $adminModel = new AdminModel();
            $this->currentUser = $adminModel->find($adminId);
            if ($this->currentUser) {
                $this->userType = 'admin';
                $this->userRole = $this->currentUser->role ?? 'super_admin';
                
                // Store role in session for consistency
                $this->session->set('userRole', $this->userRole);
                
                // Debug logging for role
                log_message('debug', 'Admin role loaded: ' . $this->userRole . ' for admin ID: ' . $adminId);
            }
        }

        // Handle reviewer users if you implement reviewer authentication
        $reviewerId = $this->session->get('reviewerId');
        if ($reviewerId) {
            // Load reviewer model and data
            $this->userType = 'reviewer';
            $this->userRole = 'reviewer';
        }
    }

    /**
     * Load menu items for current user
     */
    protected function loadUserMenu()
    {
        if (!$this->currentUser) {
            return;
        }

        $currentUrl = $this->request->getUri()->getPath();
        $this->menuItems = MenuService::getMenuWithActiveStates($this->userType, $this->userRole, $currentUrl);
        
        // Load topbar data for program selection
        $this->loadTopbarData();
    }
    
    /**
     * Load topbar data for program selection
     */
    protected function loadTopbarData()
    {
        // Check if topbar data is already cached in session
        $topbarData = $this->session->get('topbar_data');
        $lastUpdated = $this->session->get('topbar_data_updated');
        
        // Refresh topbar data if not cached or older than 1 hour
        if (!$topbarData || !$lastUpdated || (time() - $lastUpdated) > 3600) {
            $topbarData = $this->prepareTopbarData();
            $this->session->set('topbar_data', $topbarData);
            $this->session->set('topbar_data_updated', time());
        }
    }
    
    /**
     * Prepare topbar data for program selection
     */
    protected function prepareTopbarData()
    {
        $programCategoryModel = new \App\Models\ProgramCategoryModel();
        $adminModel = new \App\Models\AdminModel();
        
        // Get all available programs
        $allPrograms = $programCategoryModel->getAllCategoriesWithPrograms();
        
        // Filter programs based on admin access
        $accessiblePrograms = $this->filterProgramsByAdminAccess($allPrograms, $adminModel);
        
        // Sort programs by category name
        usort($accessiblePrograms, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });

        // Group by active and inactive
        $activePrograms = array_filter($accessiblePrograms, function ($program) {
            return $program->is_active == 1;
        });

        $inactivePrograms = array_filter($accessiblePrograms, function ($program) {
            return $program->is_active == 0;
        });

        $currentProgramId = $this->session->get('current_program');
        
        // Check if a program is already selected and if admin has access to it
        $selectedProgram = null;
        if ($currentProgramId) {
            $selectedProgram = $this->validateProgramAccess($currentProgramId, $accessiblePrograms);
        }

        return [
            'selectedProgram' => $selectedProgram,
            'activePrograms' => $activePrograms,
            'inactivePrograms' => $inactivePrograms,
            'currentUser' => $this->currentUser,
        ];
    }
    
    /**
     * Filter programs based on admin access
     */
    protected function filterProgramsByAdminAccess($allPrograms, $adminModel)
    {
        // Super admin has access to all programs
        if ($this->userRole === 'super_admin') {
            return $allPrograms;
        }
        
        // Get admin's assigned programs
        $adminPrograms = $adminModel->getAdminPrograms($this->currentUser->id);
        $adminProgramIds = array_column($adminPrograms, 'id');
        
        // If admin has no program assignments, return empty array
        if (empty($adminProgramIds)) {
            return [];
        }
        
        // Filter categories and programs
        $filteredPrograms = [];
        foreach ($allPrograms as $category) {
            $filteredCategory = clone $category;
            $filteredCategory->programs = [];
            
            if (!empty($category->programs)) {
                foreach ($category->programs as $program) {
                    if (in_array($program->id, $adminProgramIds)) {
                        $filteredCategory->programs[] = $program;
                    }
                }
            }
            
            // Only include category if it has accessible programs
            if (!empty($filteredCategory->programs)) {
                $filteredPrograms[] = $filteredCategory;
            }
        }
        
        return $filteredPrograms;
    }
    
    /**
     * Validate if admin has access to the selected program
     */
    protected function validateProgramAccess($programId, $accessiblePrograms)
    {
        foreach ($accessiblePrograms as $category) {
            if (!empty($category->programs)) {
                foreach ($category->programs as $program) {
                    if ($program->id == $programId) {
                        return $program;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Check if current user has access to a specific URL
     *
     * @param string $url
     * @return bool
     */
    protected function hasAccess($url)
    {
        return MenuService::hasAccess($this->userType, $this->userRole, $url);
    }

    /**
     * Get breadcrumb for current page
     *
     * @return array
     */
    protected function getBreadcrumb()
    {
        $currentUrl = $this->request->getUri()->getPath();
        return MenuService::getBreadcrumb($this->userType, $this->userRole, $currentUrl);
    }

    /**
     * Require authentication
     */
    protected function requireAuth()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/auth')->with('error', 'Please login to access this page.');
        }
    }

    /**
     * Require specific role
     *
     * @param array|string $allowedRoles
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    protected function requireRole($allowedRoles)
    {
        $this->requireAuth();

        if (is_string($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }

        if (!in_array($this->userRole, $allowedRoles)) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }

        return null;
    }

    /**
     * Prepare common view data
     *
     * @param array $data
     * @return array
     */
    protected function prepareViewData($data = [])
    {
        $currentUrl = $this->request->getUri()->getPath();
        
        return array_merge($data, [
            'currentUser' => $this->currentUser,
            'userType' => $this->userType,
            'userRole' => $this->userRole,
            'menuItems' => $this->menuItems,
            'breadcrumb' => $this->getBreadcrumb(),
            'currentUrl' => $currentUrl,
            'pageTitle' => $data['pageTitle'] ?? 'Admin Panel'
        ]);
    }

    /**
     * Render view with common data
     *
     * @param string $view
     * @param array $data
     * @param array $options
     * @return string
     */
    protected function renderView($view, $data = [], $options = [])
    {
        $viewData = $this->prepareViewData($data);
        return view($view, $viewData, $options);
    }
    
    /**
     * Debug method to check current admin state
     */
    protected function debugAdminState()
    {
        if (ENVIRONMENT === 'development') {
            log_message('debug', 'Admin State Debug: ' . json_encode([
                'adminId' => $this->session->get('adminId'),
                'userRole' => $this->userRole,
                'userType' => $this->userType,
                'currentProgram' => $this->session->get('current_program'),
                'isLoggedIn' => $this->session->get('isLoggedIn'),
                'sessionData' => $this->session->get()
            ]));
        }
    }
}
