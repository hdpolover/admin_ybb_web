<?php

namespace App\Controllers;

use App\Models\AdminModel;

class Admin extends AdminBaseController
{
    protected $adminModel;

    public function __construct()
    {
        parent::__construct();
        $this->adminModel = new AdminModel();
    }

    public function index()
    {
        // Require authentication and admin permissions
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        // Check if user can manage admins
        if (!$this->adminModel->hasPermission($this->currentUser, 'manage_admins')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. You do not have permission to access admin settings.');
        }

        $data = $this->prepareViewData([
            'pageTitle' => 'Admin Settings',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => base_url('dashboard')],
                ['label' => 'Settings', 'url' => base_url('settings')],
                ['label' => 'Admin Settings', 'url' => '', 'active' => true]
            ]
        ]);

        return $this->renderView('settings/admin/index', $data);
    }

    public function view($id)
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        $admin = $this->adminModel->find($id);
        if (!$admin) {
            return redirect()->to('/settings/admin')->with('error', 'Admin not found.');
        }

        $data = $this->prepareViewData([
            'pageTitle' => 'View Admin',
            'admin' => $admin,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => base_url('dashboard')],
                ['label' => 'Settings', 'url' => base_url('settings')],
                ['label' => 'Admin Settings', 'url' => base_url('settings/admin')],
                ['label' => 'View Admin', 'url' => '', 'active' => true]
            ]
        ]);

        return $this->renderView('settings/admin/view', $data);
    }

    public function edit($id)
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        $admin = $this->adminModel->find($id);
        if (!$admin) {
            return redirect()->to('/settings/admin')->with('error', 'Admin not found.');
        }

        $data = $this->prepareViewData([
            'pageTitle' => 'Edit Admin',
            'admin' => $admin,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => base_url('dashboard')],
                ['label' => 'Settings', 'url' => base_url('settings')],
                ['label' => 'Admin Settings', 'url' => base_url('settings/admin')],
                ['label' => 'Edit Admin', 'url' => '', 'active' => true]
            ]
        ]);

        return $this->renderView('settings/admin/edit', $data);
    }

    public function create()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        if ($this->request->getMethod() === 'POST') {
            // Handle form submission
            $validationRules = [
                'name' => 'required|min_length[2]|max_length[255]',
                'email' => 'required|valid_email|is_unique[admins.email]',
                'password' => 'required|min_length[8]',
                'role' => 'required|in_list[super_admin,tnd,reviewer,ambassador_coordinator,news_writer]'
            ];

            if (!$this->validate($validationRules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $data = [
                'name' => $this->request->getPost('name'),
                'email' => $this->request->getPost('email'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'role' => $this->request->getPost('role'),
                'is_active' => 1,
                'is_deleted' => 0
            ];

            if ($this->adminModel->insert($data)) {
                return redirect()->to('/settings/admin')->with('success', 'Admin created successfully.');
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to create admin.');
            }
        }

        $data = $this->prepareViewData([
            'pageTitle' => 'Create Admin',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => base_url('dashboard')],
                ['label' => 'Settings', 'url' => base_url('settings')],
                ['label' => 'Admin Settings', 'url' => base_url('settings/admin')],
                ['label' => 'Create Admin', 'url' => '', 'active' => true]
            ]
        ]);

        return $this->renderView('settings/admin/create', $data);
    }

    public function update($id)
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        $admin = $this->adminModel->find($id);
        if (!$admin) {
            return redirect()->to('/settings/admin')->with('error', 'Admin not found.');
        }

        $validationRules = [
            'name' => 'required|min_length[2]|max_length[255]',
            'email' => "required|valid_email|is_unique[admins.email,id,{$id}]",
            'role' => 'required|in_list[super_admin,tnd,reviewer,ambassador_coordinator,news_writer]'
        ];

        if ($this->request->getPost('password')) {
            $validationRules['password'] = 'min_length[8]';
        }

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role' => $this->request->getPost('role')
        ];

        if ($this->request->getPost('password')) {
            $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        if ($this->adminModel->update($id, $data)) {
            return redirect()->to('/settings/admin')->with('success', 'Admin updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update admin.');
        }
    }

    public function delete($id)
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        $admin = $this->adminModel->find($id);
        if (!$admin) {
            return redirect()->to('/settings/admin')->with('error', 'Admin not found.');
        }

        // Soft delete
        if ($this->adminModel->update($id, ['is_deleted' => 1])) {
            return redirect()->to('/settings/admin')->with('success', 'Admin deleted successfully.');
        } else {
            return redirect()->to('/settings/admin')->with('error', 'Failed to delete admin.');
        }
    }
}
