<?php

// Role Management Routes
$routes->group('settings', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->group('roles', ['filter' => 'auth'], function ($routes) {
        $routes->get('/', 'RoleManagement::index');
        $routes->get('create', 'RoleManagement::create');
        $routes->post('store', 'RoleManagement::store');
        $routes->get('edit/(:num)', 'RoleManagement::edit/$1');
        $routes->post('update/(:num)', 'RoleManagement::update/$1');
        $routes->delete('delete/(:num)', 'RoleManagement::delete/$1');
        
        // Permission management
        $routes->get('permissions', 'RoleManagement::permissions');
        $routes->post('permissions/assign', 'RoleManagement::assignPermissions');
        
        // Menu management
        $routes->get('menu-items', 'RoleManagement::menuItems');
        $routes->post('menu-items/update', 'RoleManagement::updateMenuItems');
        
        // Testing interface
        $routes->get('test', 'RoleManagement::test');
        $routes->post('test/check', 'RoleManagement::checkPermission');
    });
});