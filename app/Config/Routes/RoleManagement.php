<?php

// Alternative shorter routes for easier access
$routes->group('roles', ['namespace' => 'App\Controllers', 'filter' => 'auth'], function ($routes) {
    $routes->get('/', 'RoleManagement::index');
    $routes->get('create', 'RoleManagement::showCreateForm');
    $routes->post('create', 'RoleManagement::storeRole');
    $routes->get('view/(:num)', 'RoleManagement::view/$1');
    $routes->get('edit/(:num)', 'RoleManagement::showEditForm/$1');
    $routes->post('edit/(:num)', 'RoleManagement::updateRole/$1');
    $routes->delete('(:num)', 'RoleManagement::deleteRole/$1');
});