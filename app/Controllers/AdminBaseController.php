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
 * Class AdminBaseController
 *
 * AdminBaseController extends BaseController and adds admin-specific functionality
 * like authentication, authorization, menu management, and access control.
 * It inherits all the topbar data loading functionality from BaseController.
 */
abstract class AdminBaseController extends BaseController
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
     * to all other controllers that extend AdminBaseController.
     * Merges with BaseController helpers.
     *
     * @var array
     */
    protected $helpers = ["url", "excel_helper", "date_helper", "cache_helper", "text", "menu"];

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
        // Call parent initController which handles session, models, and topbar data
        parent::initController($request, $response, $logger);
        
        // Add admin-specific initialization
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
        
        // Topbar data is already loaded by BaseController
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
     * Filter programs based on admin access permissions
     * 
     * @param array $allPrograms Array of program categories with programs
     * @return array Filtered programs accessible to current admin
     */
    protected function filterProgramsByAdminAccess($allPrograms)
    {
        if (!$this->currentUser) {
            return [];
        }

        // Super admins have access to all programs
        if ($this->currentUser->role === 'super_admin') {
            return $allPrograms;
        }

        // For other roles, get assigned programs
        $adminModel = new \App\Models\AdminModel();
        $assignedPrograms = $adminModel->getAdminPrograms($this->currentUser->id);
        $assignedProgramIds = array_column($assignedPrograms, 'id');

        // Filter categories to only include programs admin has access to
        $filteredPrograms = [];
        foreach ($allPrograms as $category) {
            if (isset($category->programs) && is_array($category->programs)) {
                $accessiblePrograms = array_filter($category->programs, function ($program) use ($assignedProgramIds) {
                    return in_array($program->id, $assignedProgramIds);
                });

                // Only include category if it has accessible programs
                if (!empty($accessiblePrograms)) {
                    $filteredCategory = clone $category;
                    $filteredCategory->programs = array_values($accessiblePrograms);
                    $filteredPrograms[] = $filteredCategory;
                }
            }
        }

        return $filteredPrograms;
    }

    /**
     * Validate if admin has access to a specific program
     * 
     * @param int $programId
     * @param array $accessiblePrograms Array of accessible program categories
     * @return object|null Program object if accessible, null otherwise
     */
    protected function validateProgramAccess($programId, $accessiblePrograms)
    {
        if (!$programId || !is_numeric($programId)) {
            return null;
        }

        // Search through all accessible programs
        foreach ($accessiblePrograms as $category) {
            if (isset($category->programs) && is_array($category->programs)) {
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
