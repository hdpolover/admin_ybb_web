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
    $routes->post('sign-in', 'Auth::signIn');   
});

// these routes can be accessed only by admin after auth
$routes->group('', ['namespace' => 'App\Controllers', 'filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    // welcome
    $routes->get('welcome', 'Welcome::index');
    $routes->get('welcome/set-program/(:num)', 'Welcome::setProgram/$1');
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
    $routes->get('payments/make', 'Payments::makePayment');
});

// api routes
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // Auth routes - organized by functionality
    $routes->group('auth', function($routes) {
        // JWT Authentication
        $routes->post('sign-in', 'AuthApiController::signIn');
        $routes->get('profile', 'AuthApiController::profile', ['filter' => 'jwt']);
        $routes->post('refresh', 'AuthApiController::refreshToken');
        
        // User registration
        $routes->post('participant/sign-up', 'AuthApiController::participantSignUp');
        
        // Password Recovery
        $routes->post('forgot-password', 'AuthApiController::forgotPassword');
        $routes->get('verify-token', 'AuthApiController::verifyToken');
        $routes->post('reset-password', 'AuthApiController::resetPassword');
        
        // Email Verification
        $routes->get('verify-email', 'AuthApiController::verifyEmail');
        $routes->post('resend-verification', 'AuthApiController::resendVerification');
        $routes->get('test-email', 'AuthApiController::testEmail');
        
        // For backward compatibility - can be removed after updating client apps
        $routes->post('sign-in-jwt', 'AuthApiController::signIn');
    });
    
    // Midtrans Payment Integration
    $routes->group('payments', function($routes) {
        $routes->get('config', 'Api\PaymentsApiController::getConfig');
        $routes->post('create', 'Api\PaymentsApiController::createTransaction');
        $routes->post('webhook', 'Api\PaymentsApiController::webhook');
        $routes->get('status/(:num)', 'Api\PaymentsApiController::getStatus/$1');
        $routes->post('upload-proof', 'Api\PaymentsApiController::uploadPaymentProof');
    });
    
    // Protected routes with JWT authentication
    $routes->group('', ['filter' => 'jwt'], function ($routes) {
        $routes->get('my-programs', 'ProgramsApiController::getUserPrograms');
        // Add more protected endpoints here
    });
    
    // users
    $routes->get('users', 'UsersApiController::index');
    $routes->get('users/(:num)', 'UsersApiController::show/$1');
    // check users by params
    $routes->get('users/check', 'UsersApiController::checkUserByParams');

    // participants 
    $routes->get('participants', 'ParticipantsApiController::index');
    $routes->get('participants/(:num)', 'ParticipantsApiController::show/$1');
    $routes->get('participants/current-program', 'ParticipantsApiController::getCurrentProgramParticipants');
    //get participants by user id
    $routes->get('participants/user/(:num)', 'ParticipantsApiController::getByUserId/$1');
    // get random participant photos by program id
    $routes->get('participants/program/(:num)/photos', 'ParticipantsApiController::getProgramParticipantsPhotos/$1');

    // ambassadors
    $routes->get('ambassadors', 'AmbassadorsApiController::index');
    $routes->get('ambassadors/(:num)', 'AmbassadorsApiController::show/$1');
    $routes->get('ambassadors/(:any)/participants', 'AmbassadorsApiController::getParticipantsbyRefCode/$1');
    $routes->get('ambassadors/(:num)/generate-link', 'AmbassadorsApiController::generateLink/$1');
    // check query
    $routes->post('ambassadors/check-query', 'AmbassadorsApiController::checkEncryptedQuery');

    // program categories
    $routes->get('program-categories', 'ProgramCategoriesApiController::index');
    $routes->get('program-categories/(:num)', 'ProgramCategoriesApiController::show/$1');
    $routes->get('program-categories/(:num)/programs', 'ProgramCategoriesApiController::getProgramsbyCatId/$1');

    // photos
    $routes->get('program-photos', 'ProgramPhotosApiController::index');
    $routes->get('program-photos/(:num)', 'ProgramPhotosApiController::show/$1');
    $routes->get('program-photos/category/(:num)', 'ProgramPhotosApiController::getByCategory/$1');

    // programs
    $routes->get('programs', 'ProgramsApiController::index');
    // get by slug (accepts string parameter)
    $routes->get('programs/slug/(:any)', 'ProgramsApiController::getBySlug/$1');
    // get by category id
    $routes->get('programs/category/(:num)', 'ProgramsApiController::getByCategory/$1');
    // get programs not in program category id
    $routes->get('programs/not-in-category/(:num)', 'ProgramsApiController::getNotInCategory/$1');
    
    // program schedules
    $routes->get('program-schedules', 'ProgramSchedulesApiController::index');
    $routes->get('program-schedules/(:num)', 'ProgramSchedulesApiController::show/$1');
    $routes->get('program-schedules/program/(:num)', 'ProgramSchedulesApiController::getByProgramId/$1');

    // program subthemes
    $routes->get('program-subthemes', 'ProgramSubthemeApiController::index');
    $routes->get('program-subthemes/(:num)', 'ProgramSubthemeApiController::show/$1');
    $routes->get('program-subthemes/program/(:num)', 'ProgramSubthemeApiController::getByProgramId/$1');

    // program essays
    $routes->get('program-essays', 'ProgramEssayApiController::index');
    $routes->get('program-essays/(:num)', 'ProgramEssayApiController::show/$1');
    $routes->get('program-essays/program/(:num)', 'ProgramEssayApiController::getByProgramId/$1');

    // web settings
    $routes->get('web-settings', 'WebSettingApiController::index');

    // Maintenance check endpoint - publicly accessible
    $routes->get('maintenance/check', 'Api\MaintenanceApiController::check');
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

// Landing API Routes
$routes->group('api/landing', ['namespace' => 'App\Controllers\Api'], function($routes) {
    $routes->get('home', 'LandingApiController::home');
    $routes->get('programs', 'LandingApiController::programs');
    $routes->get('programs/(:num)', 'LandingApiController::programDetail/$1');
    // insights
    $routes->get('insights', 'LandingApiController::insights');
    $routes->get('insights/(:num)', 'LandingApiController::insightDetail/$1');
    // partners and sponsors
    $routes->get('partners-sponsors', 'LandingApiController::partnersAndSponsors');
    $routes->get('partners-sponsors/(:num)', 'LandingApiController::partnerSponsorDetail/$1');
    // help & news
    $routes->get('help-news', 'LandingApiController::helpAndNews');
    $routes->get('help-news/(:num)', 'LandingApiController::helpAndNewsDetail/$1');
 });

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
