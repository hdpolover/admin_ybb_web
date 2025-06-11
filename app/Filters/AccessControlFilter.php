<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Services\MenuService;
use App\Models\AdminModel;

class AccessControlFilter implements FilterInterface
{
    /**
     * Check if user has access to the requested route
     *
     * @param RequestInterface $request
     * @param array|null $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Get current URL path
        $currentUrl = $request->getUri()->getPath();
        
        // Skip access control for login and public pages
        $publicPaths = ['/auth', '/', '/login', '/logout'];
        if (in_array($currentUrl, $publicPaths)) {
            return $request;
        }
        
        // Check if user is logged in
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/auth')->with('error', 'Please login to access this page.');
        }
        
        // Determine user type and role
        $userType = 'admin';
        $role = 'super';
        
        $adminId = $session->get('adminId');
        if ($adminId) {
            $adminModel = new AdminModel();
            $admin = $adminModel->find($adminId);
            if ($admin) {
                $userType = 'admin';
                $role = $admin->role ?? 'super';
            }
        }
        
        $reviewerId = $session->get('reviewerId');
        if ($reviewerId) {
            $userType = 'reviewer';
            $role = 'reviewer';
        }
        
        // Check access permission
        if (!MenuService::hasAccess($userType, $role, $currentUrl)) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        
        return $request;
    }

    /**
     * Allows after processing, if needed
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
