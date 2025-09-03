<?php

namespace App\Controllers;

use App\Controllers\AdminBaseController;
use App\Models\MenuItemModel;
use App\Models\RoleModel;
use App\Models\PermissionModel;

class MenuSetup extends AdminBaseController
{
    protected $menuItemModel;
    protected $roleModel;
    protected $permissionModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->menuItemModel = new MenuItemModel();
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
    }

    public function index()
    {
        echo "<h1>Menu System Setup</h1>";
        echo "<p><a href='" . base_url('menu-setup/check') . "'>Check Current Status</a></p>";
        echo "<p><a href='" . base_url('menu-setup/init') . "'>Initialize Menu System</a></p>";
        echo "<p><a href='" . base_url('menu-setup/populate') . "'>Populate Default Menu Items</a></p>";
    }

    public function check()
    {
        echo "<h1>Menu System Status</h1>";
        
        $db = \Config\Database::connect();
        $tables = $db->listTables();
        
        $requiredTables = ['menu_items', 'roles', 'permissions', 'role_permissions'];
        
        echo "<h2>Database Tables:</h2>";
        foreach ($requiredTables as $table) {
            if (in_array($table, $tables)) {
                $count = $db->table($table)->countAllResults();
                echo "✓ Table '{$table}' exists with {$count} records<br>";
                
                if ($table === 'menu_items' && $count > 0) {
                    $items = $db->table($table)->orderBy('sort_order')->get()->getResult();
                    echo "<ul>";
                    foreach ($items as $item) {
                        echo "<li>{$item->label} - {$item->url} (Permission: {$item->required_permission})</li>";
                    }
                    echo "</ul>";
                }
            } else {
                echo "✗ Table '{$table}' NOT found<br>";
            }
        }

        echo "<br><a href='" . base_url('menu-setup') . "'>Back</a>";
    }

    public function init()
    {
        echo "<h1>Initialize Menu System</h1>";
        
        $db = \Config\Database::connect();
        
        // Create menu_items table if not exists
        $sql = "
        CREATE TABLE IF NOT EXISTS menu_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            label VARCHAR(100) NOT NULL,
            icon VARCHAR(100),
            url VARCHAR(255),
            route_name VARCHAR(100),
            parent_id INT NULL,
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            required_permission VARCHAR(100),
            badge_text VARCHAR(50),
            badge_color VARCHAR(20),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE
        )";
        
        if ($db->query($sql)) {
            echo "✓ menu_items table created/verified<br>";
        } else {
            echo "✗ Failed to create menu_items table<br>";
        }

        echo "<br><a href='" . base_url('menu-setup') . "'>Back</a>";
    }

    public function populate()
    {
        echo "<h1>Populate Default Menu Items</h1>";
        
        // Check if menu items already exist
        $existingCount = $this->menuItemModel->countAllResults();
        if ($existingCount > 0) {
            echo "<p>Menu items already exist ({$existingCount} items). <a href='" . base_url('menu-setup/clear') . "'>Clear first?</a></p>";
            echo "<a href='" . base_url('menu-setup') . "'>Back</a>";
            return;
        }

        // Default menu structure
        $menuItems = [
            [
                'name' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'ri-dashboard-line',
                'url' => '/dashboard',
                'parent_id' => null,
                'sort_order' => 1,
                'required_permission' => 'view_dashboard'
            ],
            [
                'name' => 'participants',
                'label' => 'Participants',
                'icon' => 'ri-user-line',
                'url' => '/participants',
                'parent_id' => null,
                'sort_order' => 2,
                'required_permission' => 'view_participants'
            ],
            [
                'name' => 'settings',
                'label' => 'Settings',
                'icon' => 'ri-tools-fill',
                'url' => '#',
                'parent_id' => null,
                'sort_order' => 10,
                'required_permission' => 'system_settings'
            ]
        ];

        $insertedItems = [];
        
        // Insert parent items first
        foreach ($menuItems as $item) {
            $id = $this->menuItemModel->insert($item);
            if ($id) {
                $insertedItems[$item['name']] = $id;
                echo "✓ Added: {$item['label']}<br>";
            } else {
                echo "✗ Failed to add: {$item['label']}<br>";
            }
        }

        // Add settings sub-items
        if (isset($insertedItems['settings'])) {
            $settingsItems = [
                [
                    'name' => 'main_config',
                    'label' => 'Main Configuration',
                    'icon' => 'ri-settings-4-line',
                    'url' => '/settings/main-config',
                    'parent_id' => $insertedItems['settings'],
                    'sort_order' => 1,
                    'required_permission' => 'system_settings'
                ],
                [
                    'name' => 'admin_management',
                    'label' => 'Admin Management',
                    'icon' => 'ri-admin-line',
                    'url' => '/settings/admin-management',
                    'parent_id' => $insertedItems['settings'],
                    'sort_order' => 2,
                    'required_permission' => 'manage_admins'
                ],
                [
                    'name' => 'role_management',
                    'label' => 'Roles & Permissions',
                    'icon' => 'ri-user-settings-line',
                    'url' => '/settings/roles',
                    'parent_id' => $insertedItems['settings'],
                    'sort_order' => 3,
                    'required_permission' => 'manage_roles'
                ]
            ];

            foreach ($settingsItems as $item) {
                $id = $this->menuItemModel->insert($item);
                if ($id) {
                    echo "✓ Added sub-item: {$item['label']}<br>";
                } else {
                    echo "✗ Failed to add sub-item: {$item['label']}<br>";
                }
            }
        }

        echo "<br><p>Menu items populated successfully!</p>";
        echo "<a href='" . base_url('menu-setup/check') . "'>Check Status</a> | ";
        echo "<a href='" . base_url('menu-setup') . "'>Back</a>";
    }

    public function clear()
    {
        echo "<h1>Clear Menu Items</h1>";
        
        $count = $this->menuItemModel->countAllResults();
        $this->menuItemModel->emptyTable();
        
        echo "<p>Cleared {$count} menu items.</p>";
        echo "<a href='" . base_url('menu-setup') . "'>Back</a>";
    }
}