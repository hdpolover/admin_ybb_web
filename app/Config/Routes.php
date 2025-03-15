<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
// $routes->get('/login', 'Auth::index', ['filter' => 'noauth']);
// $routes->post('/login', 'Auth::login');

// user routes with name space App\Controllers\Users
$routes->group('', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'Auth::index');
    $routes->post('login', 'Auth::login');
});

// these routes can be accessed only by admin after auth
$routes->group('', ['namespace' => 'App\Controllers', 'filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    // welcome
    $routes->get('welcome', 'Welcome::index');
    $routes->get('welcome/set_program/(:num)', 'Welcome::set_program/$1');
    $routes->get('logout', 'Auth::logout');

    // participants
    $routes->get('users/participants', 'Participants::index');
});

// Protected routes that require program selection
$routes->group('', ['filter' => 'program_selection'], function($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    // Add other routes that require program selection here
    
    // Payment routes
    $routes->get('payments', 'Payments::index');
    $routes->get('payments/getData', 'Payments::getData');
    $routes->get('payments/view/(:num)', 'Payments::view/$1');
    $routes->post('payments/export', 'Payments::export');
});

// api routes
$routes->group('api/', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // users
    $routes->get('users', 'Users::index');
    $routes->get('users/(:num)', 'Users::show/$1');

    // participants
    $routes->get('participants', 'Participants::index');
    $routes->get('participants/(:num)', 'Participants::show/$1');
    // $routes->get('participants/program/(:num)', 'Participants::getParticipantsByProgramId/$1');

    // ambassodors
    $routes->get('ambassadors', 'Ambassadors::index');
    $routes->get('ambassadors/(:num)', 'Ambassadors::show/$1');
    $routes->get('ambassadors/(:any)/participants', 'Ambassadors::getParticipantsbyRefCode/$1');

    // program categories
    $routes->get('program-categories', 'ProgramCategories::index');
    $routes->get('program-categories/(:num)', 'ProgramCategories::show/$1');
    $routes->get('program-categories/(:num)/programs', 'ProgramCategories::getProgramsbyCatId/$1');
});

// web routes
// excel
$routes->get('excel', 'Excel::index');

// ambassadors
$routes->get('ambassadors', 'Ambassadors::index');

// Participant routes
$routes->get('participants', 'Participants::index');
$routes->get('participants/view/(:num)', 'Participants::view/$1');
$routes->get('participants/edit/(:num)', 'Participants::edit/$1');
$routes->post('participants/delete/(:num)', 'Participants::delete/$1');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
