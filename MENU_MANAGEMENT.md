# Menu Management System Documentation

This documentation explains how to use the menu management system in your CodeIgniter application.

## Overview

The menu management system provides role-based access control for your application with the following features:

- **Role-based menu generation**: Different menus for different user roles
- **Access control**: Automatic permission checking for routes
- **Breadcrumb generation**: Automatic breadcrumb trails
- **Active state management**: Automatic highlighting of active menu items
- **Hierarchical menus**: Support for nested menu structures

## User Types and Roles

### Admin Users
- **super**: Full access to everything
- **program_admin**: Access to participant management and program settings
- **editor**: Access to content management (announcements, FAQs, etc.)
- **moderator**: Access to participant and abstract management

### Reviewer Users
- **reviewer**: Access to abstract/paper review functionality

## Files Structure

```
app/
├── Services/
│   └── MenuService.php          # Core menu management logic
├── Helpers/
│   └── menu_helper.php          # Helper functions for views
├── Filters/
│   └── AccessControlFilter.php  # Access control middleware
├── Controllers/
│   ├── AdminBaseController.php  # Base controller with menu functionality
│   └── MenuTestController.php   # Example controller
└── Views/
    ├── layouts/
    │   └── admin.php            # Admin layout with menu
    └── admin/
        ├── menu_test.php        # Test page
        └── user_info.php        # User information page
```

## Usage

### 1. Creating a Controller

Extend `AdminBaseController` to get automatic menu functionality:

```php
<?php

namespace App\Controllers;

class YourController extends AdminBaseController
{
    public function index()
    {
        // Require authentication
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        // Require specific role (optional)
        $redirect = $this->requireRole(['super', 'program_admin']);
        if ($redirect) return $redirect;

        $data = [
            'pageTitle' => 'Your Page Title',
            'content' => 'Your page content'
        ];

        return $this->renderView('your_view', $data);
    }
}
```

### 2. Using in Views

The base controller automatically provides these variables to your views:

- `$currentUser`: Current user object
- `$userType`: 'admin' or 'reviewer'
- `$userRole`: User's specific role
- `$menuItems`: Menu items for current user
- `$breadcrumb`: Breadcrumb trail
- `$currentUrl`: Current URL path

### 3. Helper Functions

Use these helper functions in your views:

```php
// Get menu for current user
$menu = get_user_menu($currentUrl);

// Get breadcrumb
$breadcrumb = get_user_breadcrumb($currentUrl);

// Check access permission
if (user_has_access('/some-url')) {
    // User has access
}

// Render complete menu
echo render_menu($currentUrl);

// Render single menu item
echo render_menu_item($menuItem);
```

### 4. Customizing Menus

Edit `app/Services/MenuService.php` to modify the menu structure:

```php
private static $menuStructure = [
    'admin' => [
        'your_role' => [
            [
                'label' => 'Menu Item',
                'url' => '/your-url',
                'icon' => 'fas fa-icon',
                'active_patterns' => ['/your-url', '/your-url/*']
            ],
            [
                'label' => 'Parent Menu',
                'icon' => 'fas fa-folder',
                'children' => [
                    [
                        'label' => 'Child Item',
                        'url' => '/child-url',
                        'icon' => 'fas fa-child-icon'
                    ]
                ]
            ]
        ]
    ]
];
```

### 5. Adding Access Control

Add the access control filter to your routes:

```php
$routes->group('admin', ['filter' => 'access_control'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    // ... other routes
});
```

### 6. Layout Usage

Use the admin layout in your views:

```php
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<!-- Your content here -->
<?= $this->endSection() ?>
```

## Menu Item Properties

Each menu item can have these properties:

- `label`: Display text for the menu item
- `url`: URL to navigate to (optional for parent items)
- `icon`: CSS class for the icon (e.g., 'fas fa-dashboard')
- `active_patterns`: Array of URL patterns that should make this item active
- `children`: Array of child menu items (for hierarchical menus)

## Access Control

The system automatically:

1. Checks if user is logged in
2. Determines user type and role from session
3. Compares requested URL against user's allowed menu items
4. Redirects to login or shows error if access denied

## Testing the System

Visit these URLs to test different access levels:

- `/menu-test` - General access test
- `/menu-test/super-only` - Super admin only
- `/menu-test/program-admin` - Program admin or higher
- `/menu-test/editor` - Editor or higher
- `/menu-test/user-info` - View user and menu information

## Role Hierarchy

The system doesn't enforce a strict hierarchy, but the default setup suggests:

1. **super** - Highest level, access to everything
2. **program_admin** - Program management
3. **moderator** - Content moderation
4. **editor** - Content editing

## Adding New Roles

1. Add the role to `$menuStructure` in `MenuService.php`
2. Define the menu items for that role
3. Update your authentication system to assign the new role
4. Test access controls

## Security Notes

- Always use the access control filter on protected routes
- Menu visibility doesn't guarantee access - the filter enforces permissions
- Test all role combinations thoroughly
- Keep menu structures and actual permissions in sync

## Troubleshooting

### Menu not showing
- Check if user is logged in
- Verify user role is defined in MenuService
- Ensure helper is loaded

### Access denied errors
- Check route is defined in user's menu structure
- Verify active_patterns include the requested URL
- Test with higher privilege role

### Breadcrumb issues
- Ensure menu items have proper URL structure
- Check active_patterns are correctly defined
- Verify hierarchical structure is correct
