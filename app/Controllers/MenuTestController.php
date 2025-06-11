<?php

namespace App\Controllers;

class MenuTestController extends AdminBaseController
{
    /**
     * Display menu test page
     */
    public function index()
    {
        // Require authentication
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        $data = [
            'pageTitle' => 'Menu Test Page',
            'content' => 'This is a test page to demonstrate the menu system.'
        ];

        return $this->renderView('admin/menu_test', $data);
    }

    /**
     * Super admin only page
     */
    public function superOnly()
    {
        // Require super admin role
        $redirect = $this->requireRole(['super']);
        if ($redirect) return $redirect;

        $data = [
            'pageTitle' => 'Super Admin Only',
            'content' => 'This page is only accessible by super admins.'
        ];

        return $this->renderView('admin/menu_test', $data);
    }

    /**
     * Program admin or higher
     */
    public function programAdminAccess()
    {
        // Require program admin or super admin role
        $redirect = $this->requireRole(['super', 'program_admin']);
        if ($redirect) return $redirect;

        $data = [
            'pageTitle' => 'Program Admin Access',
            'content' => 'This page is accessible by program admins and super admins.'
        ];

        return $this->renderView('admin/menu_test', $data);
    }

    /**
     * Editor access
     */
    public function editorAccess()
    {
        // Require editor, moderator, program_admin, or super admin role
        $redirect = $this->requireRole(['super', 'program_admin', 'moderator', 'editor']);
        if ($redirect) return $redirect;

        $data = [
            'pageTitle' => 'Editor Access',
            'content' => 'This page is accessible by editors and higher roles.'
        ];

        return $this->renderView('admin/menu_test', $data);
    }

    /**
     * Show current user info and menu structure
     */
    public function userInfo()
    {
        // Require authentication
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        $data = [
            'pageTitle' => 'User Information',
            'availableRoles' => get_available_roles('admin'),
            'reviewerRoles' => get_available_roles('reviewer')
        ];

        return $this->renderView('admin/user_info', $data);
    }
}
