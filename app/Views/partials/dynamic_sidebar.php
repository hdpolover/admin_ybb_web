<?php
/**
 * Dynamic Sidebar Component
 * 
 * This component integrates with MenuService to render role-based menus
 * It replaces the static sidebar.php with dynamic menu generation
 */

use App\Services\MenuService;

// Get current user info from session
$currentUser = session('topbar_data')['currentUser'] ?? null;
$userType = session('topbar_data')['userType'] ?? 'admin';
$userRole = session('topbar_data')['userRole'] ?? 'moderator';

// Get current URL for active state detection
$currentUrl = uri_string();

// Get menu items based on user role
$menuItems = [];
if ($currentUser) {
    $menuItems = MenuService::getMenuWithActiveStates($userType, $userRole, $currentUrl);
}

/**
 * Render menu item recursively
 */
function renderMenuItem($item, $level = 0) {
    $hasChildren = isset($item['children']) && !empty($item['children']);
    $isActive = $item['is_active'] ?? false;
    $hasActiveChild = $item['has_active_child'] ?? false;
    
    $html = '';
    
    if ($hasChildren) {
        // Parent menu with children
        $collapseId = 'sidebar' . str_replace([' ', '-'], '', $item['label']);
        $expanded = $isActive || $hasActiveChild ? 'true' : 'false';
        $show = $isActive || $hasActiveChild ? 'show' : '';
        $activeClass = $isActive || $hasActiveChild ? 'active' : '';
        
        $html .= '<li class="nav-item">';
        $html .= '<a class="nav-link menu-link ' . $activeClass . '" href="#' . $collapseId . '" data-bs-toggle="collapse" role="button" aria-expanded="' . $expanded . '" aria-controls="' . $collapseId . '">';
        $html .= '<i class="' . $item['icon'] . '"></i> <span>' . esc($item['label']) . '</span>';
        $html .= '</a>';
        $html .= '<div class="collapse menu-dropdown ' . $show . '" id="' . $collapseId . '">';
        $html .= '<ul class="nav nav-sm flex-column">';
        
        foreach ($item['children'] as $child) {
            $html .= renderMenuItem($child, $level + 1);
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</li>';
        
    } else {
        // Single menu item
        $activeClass = $isActive ? 'active' : '';
        $url = $item['url'] ?? '#';
        
        if ($level > 0) {
            // Child menu item
            $html .= '<li class="nav-item">';
            $html .= '<a href="' . base_url($url) . '" class="nav-link ' . $activeClass . '">';
            $html .= '<i class="' . $item['icon'] . '"></i> ' . esc($item['label']);
            $html .= '</a>';
            $html .= '</li>';
        } else {
            // Top-level menu item
            $html .= '<li class="nav-item">';
            $html .= '<a class="nav-link menu-link ' . $activeClass . '" href="' . base_url($url) . '">';
            $html .= '<i class="' . $item['icon'] . '"></i> <span>' . esc($item['label']) . '</span>';
            $html .= '</a>';
            $html .= '</li>';
        }
    }
    
    return $html;
}
?>

<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="/" class="logo logo-dark">
            <span class="logo-sm">
                <img src="/assets/ybb/ybb_white.png" alt="YBB Logo" height="60">
            </span>
            <span class="logo-lg">
                <img src="/assets/ybb/ybb_white.png" alt="YBB Logo" height="60">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="/" class="logo logo-light">
            <span class="logo-sm">
                <img src="/assets/ybb/ybb_white.png" alt="YBB Logo" height="60">
            </span>
            <span class="logo-lg">
                <img src="/assets/ybb/ybb_white.png" alt="YBB Logo" height="60">
            </span>
        </a>
        <!-- Mobile Menu Toggle Button -->
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            
            <?php if ($currentUser): ?>
            <!-- Dynamic Menu Based on User Role -->
            <ul class="navbar-nav" id="navbar-nav">
                <?php foreach ($menuItems as $menuItem): ?>
                    <?= renderMenuItem($menuItem) ?>
                <?php endforeach; ?>
                
                <!-- User Info Section -->
                <li class="menu-title"><i class="ri-account-circle-line"></i> <span>Account</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="<?= base_url('profile') ?>">
                        <i class="ri-user-settings-line"></i> <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="<?= base_url('logout') ?>">
                        <i class="ri-logout-box-line"></i> <span>Logout</span>
                    </a>
                </li>
            </ul>
            
            <?php else: ?>
            <!-- Fallback for non-authenticated users -->
            <ul class="navbar-nav" id="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link menu-link" href="<?= base_url('login') ?>">
                        <i class="ri-login-box-line"></i> <span>Login</span>
                    </a>
                </li>
            </ul>
            <?php endif; ?>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>

<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>