<?php

if (!function_exists('get_user_menu')) {
    /**
     * Get menu for the current user
     * 
     * @param string|null $currentUrl
     * @return array
     */
    function get_user_menu($currentUrl = null)
    {
        $session = session();
        
        // Determine user type and role from session
        $userType = 'admin'; // Default
        $role = 'super'; // Default
        
        if ($session->get('isLoggedIn')) {
            // For admin users
            $adminId = $session->get('adminId');
            if ($adminId) {
                $adminModel = new \App\Models\AdminModel();
                $admin = $adminModel->find($adminId);
                if ($admin) {
                    $userType = 'admin';
                    $role = $admin->role ?? 'super';
                }
            }
            
            // For reviewer users (if you have a separate reviewer session)
            $reviewerId = $session->get('reviewerId');
            if ($reviewerId) {
                $userType = 'reviewer';
                $role = 'reviewer';
            }
        }
        
        if ($currentUrl) {
            return \App\Services\MenuService::getMenuWithActiveStates($userType, $role, $currentUrl);
        }
        
        return \App\Services\MenuService::getMenuForRole($userType, $role);
    }
}

if (!function_exists('get_user_breadcrumb')) {
    /**
     * Get breadcrumb for current page
     * 
     * @param string $currentUrl
     * @return array
     */
    function get_user_breadcrumb($currentUrl)
    {
        $session = session();
        
        // Determine user type and role from session
        $userType = 'admin';
        $role = 'super';
        
        if ($session->get('isLoggedIn')) {
            $adminId = $session->get('adminId');
            if ($adminId) {
                $adminModel = new \App\Models\AdminModel();
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
        }
        
        return \App\Services\MenuService::getBreadcrumb($userType, $role, $currentUrl);
    }
}

if (!function_exists('user_has_access')) {
    /**
     * Check if current user has access to a URL
     * 
     * @param string $url
     * @return bool
     */
    function user_has_access($url)
    {
        $session = session();
        
        // Determine user type and role from session
        $userType = 'admin';
        $role = 'super';
        
        if ($session->get('isLoggedIn')) {
            $adminId = $session->get('adminId');
            if ($adminId) {
                $adminModel = new \App\Models\AdminModel();
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
        }
        
        return \App\Services\MenuService::hasAccess($userType, $role, $url);
    }
}

if (!function_exists('get_available_roles')) {
    /**
     * Get all available roles for a user type
     * 
     * @param string $userType
     * @return array
     */
    function get_available_roles($userType = 'admin')
    {
        return \App\Services\MenuService::getAvailableRoles($userType);
    }
}

if (!function_exists('render_menu_item')) {
    /**
     * Render a single menu item
     * 
     * @param array $item
     * @param int $level
     * @return string
     */
    function render_menu_item($item, $level = 0)
    {
        $html = '';
        $hasChildren = isset($item['children']) && !empty($item['children']);
        $isActive = $item['is_active'] ?? false;
        $hasActiveChild = $item['has_active_child'] ?? false;
        
        $liClass = '';
        if ($isActive) {
            $liClass .= ' active';
        }
        if ($hasActiveChild) {
            $liClass .= ' menu-open';
        }
        
        $html .= '<li class="nav-item' . ($hasChildren ? ' has-treeview' : '') . $liClass . '">';
        
        if ($hasChildren) {
            // Parent menu item
            $html .= '<a href="#" class="nav-link' . ($isActive || $hasActiveChild ? ' active' : '') . '">';
            if (isset($item['icon'])) {
                $html .= '<i class="nav-icon ' . $item['icon'] . '"></i>';
            }
            $html .= '<p>' . $item['label'] . '<i class="right fas fa-angle-left"></i></p>';
            $html .= '</a>';
            
            // Children
            $html .= '<ul class="nav nav-treeview">';
            foreach ($item['children'] as $child) {
                $html .= render_menu_item($child, $level + 1);
            }
            $html .= '</ul>';
        } else {
            // Single menu item
            $html .= '<a href="' . ($item['url'] ?? '#') . '" class="nav-link' . ($isActive ? ' active' : '') . '">';
            if (isset($item['icon'])) {
                $html .= '<i class="nav-icon ' . $item['icon'] . '"></i>';
            }
            $html .= '<p>' . $item['label'] . '</p>';
            $html .= '</a>';
        }
        
        $html .= '</li>';
        
        return $html;
    }
}

if (!function_exists('render_menu')) {
    /**
     * Render complete menu
     * 
     * @param string|null $currentUrl
     * @return string
     */
    function render_menu($currentUrl = null)
    {
        if (!$currentUrl) {
            $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
        }
        
        $menuItems = get_user_menu($currentUrl);
        $html = '';
        
        foreach ($menuItems as $item) {
            $html .= render_menu_item($item);
        }
        
        return $html;
    }
}
