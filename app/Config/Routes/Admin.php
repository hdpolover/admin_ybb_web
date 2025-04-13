<?php

namespace Config\Routes;

$routes->group('', ['namespace' => 'App\Controllers', 'filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');

    // welcome
    $routes->get('welcome', 'Welcome::index');
    $routes->get('welcome/set-program/(:num)', 'Welcome::setProgram/$1');
    $routes->get('sign-out', 'Auth::signOut');

    // Users group
    $routes->group('users', ['filter' => 'program_selection'], function ($routes) {
        // participants
        $routes->get('participants', 'Participants::index');
        $routes->get('participants/view/(:num)', 'Participants::view/$1');
        $routes->get('participants/getData', 'Participants::getData');

        // ambassadors
        $routes->get('ambassadors', 'Ambassadors::index');
        $routes->get('ambassadors/view/(:num)', 'Ambassadors::view/$1');
        $routes->get('ambassadors/getData', 'Ambassadors::getData');
    });

    // Payment routes
    $routes->group('payments', ['filter' => 'program_selection'], function ($routes) {
        $routes->get('', 'Payments::index');
        $routes->get('getData', 'Payments::getData');
        $routes->get('view/(:num)', 'Payments::view/$1');
        $routes->post('update-status/(:num)', 'Payments::updateStatus/$1');
        $routes->post('export', 'Payments::export');
        $routes->get('make', 'Payments::makePayment');
    });

    // document routes
    $routes->group('documents', ['filter' => 'program_selection'], function ($routes) {        // Program Documents routes
        $routes->group('program-documents', ['filter' => 'auth'], function ($routes) {
            $routes->get('/', 'ProgramDocuments::index');
            $routes->get('view/(:num)', 'ProgramDocuments::view/$1');
            $routes->get('edit/(:num)', 'ProgramDocuments::edit/$1');
            $routes->post('create', 'ProgramDocuments::create');
            $routes->post('update/(:num)', 'ProgramDocuments::update/$1');
            $routes->get('delete/(:num)', 'ProgramDocuments::delete/$1');
            $routes->get('get-document/(:num)', 'ProgramDocuments::getDocument/$1');
            
            // LOA Template routes
            $routes->get('loa-settings/(:num)', 'ProgramDocuments::loaSettings/$1');
            $routes->post('save-loa-template/(:num)', 'ProgramDocuments::saveLoaTemplate/$1');
            $routes->get('generate-loa/(:num)/(:num)', 'ProgramDocuments::generateLoa/$1/$2');
        });

        // certificates
        $routes->get('certificates', 'Certificates::index');
        $routes->get('certificates/view/(:num)', 'Certificates::view/$1');
        $routes->get('certificates/getData', 'Certificates::getData');

        // configuration
        $routes->get('configuration', 'Configuration::index');
        $routes->post('configuration/update', 'Configuration::update');
        $routes->get('configuration/getData', 'Configuration::getData');
    });
});

// Remove excel route
// $routes->get('excel', 'Excel::index');
