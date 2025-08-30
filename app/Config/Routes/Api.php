<?php

namespace Config\Routes;

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // Auth routes - organized by functionality
    $routes->group('auth', function ($routes) {
        // JWT Authentication - Participants
        $routes->post('participant/sign-in', 'Auth\ParticipantAuthController::signIn');
        $routes->post('participant/sign-up', 'Auth\ParticipantAuthController::signUp');
        
        // JWT Authentication - Ambassadors
        $routes->post('ambassador/sign-in', 'Auth\AmbassadorAuthController::signIn');
        
        // JWT Authentication - Admins (if needed)
        $routes->post('admin/sign-in', 'Auth\AdminAuthController::signIn');
        
        // Generic sign-in (for backward compatibility)
        $routes->post('sign-in', 'AuthApiController::signIn');
        $routes->get('profile', 'AuthApiController::profile', ['filter' => 'jwt']);
        $routes->post('refresh', 'AuthApiController::refreshToken');

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

    // Payment routes - publicly accessible (webhooks, redirects, config) - SPECIFIC PATHS FIRST
    $routes->get('payments/config', 'PaymentsApiController::getConfig');
    $routes->post('payments/webhook', 'PaymentsApiController::webhook'); 
    $routes->get('payments/finish', 'PaymentsApiController::finishRedirect');
    $routes->get('payments/unfinish', 'PaymentsApiController::unfinishRedirect');  
    $routes->get('payments/error', 'PaymentsApiController::errorRedirect');

    // Payment routes with explicit JWT filter (using same pattern as auth/profile)
    $routes->post('payments/create', 'PaymentsApiController::createTransaction', ['filter' => 'jwt']);
    $routes->post('payments/upload-proof', 'PaymentsApiController::uploadPaymentProof', ['filter' => 'jwt']);
    $routes->get('payments/get/(:num)', 'PaymentsApiController::getPayment/$1', ['filter' => 'jwt']);
    $routes->get('payments/status/(:segment)', 'PaymentsApiController::getStatus/$1', ['filter' => 'jwt']);
    $routes->get('payments/participant/(:num)', 'PaymentsApiController::getPaymentsByParticipantId/$1', ['filter' => 'jwt']);
    $routes->get('payments/program-payment/(:num)/participant/(:num)', 'PaymentsApiController::getPaymentsByProgramPaymentIdAndParticipantId/$1/$2', ['filter' => 'jwt']);

    // Other protected routes
    $routes->get('my-programs', 'ProgramsApiController::getUserPrograms', ['filter' => 'jwt']);

    // Profile picture routes - publicly accessible
    $routes->post('participants/(:num)/upload-picture', 'ProfileApiController::uploadParticipantProfilePicture/$1');
    
    // users
    $routes->get('users', 'UsersApiController::index');
    $routes->get('users/(:num)', 'UsersApiController::show/$1');
    // check users by params
    $routes->get('users/check', 'UsersApiController::checkUserByParams');

    // Maintenance check endpoint - publicly accessible
    $routes->get('maintenance/check', 'Api\MaintenanceApiController::check');

    // Program Speakers API routes
    $routes->group('program-speakers', function ($routes) {
        // Basic CRUD
        $routes->get('/', 'ProgramSpeakersApiController::index');
        $routes->get('(:num)', 'ProgramSpeakersApiController::show/$1');
        $routes->post('/', 'ProgramSpeakersApiController::create');
        $routes->put('(:num)', 'ProgramSpeakersApiController::update/$1');
        $routes->delete('(:num)', 'ProgramSpeakersApiController::delete/$1');
        
        // Program-specific routes
        $routes->get('program/(:num)', 'ProgramSpeakersApiController::getByProgramId/$1');
        $routes->get('program/(:num)/keynote', 'ProgramSpeakersApiController::getKeynoteSpeakers/$1');
        $routes->get('program/(:num)/regular', 'ProgramSpeakersApiController::getRegularSpeakers/$1');
        $routes->get('program/(:num)/stats', 'ProgramSpeakersApiController::getSpeakerStats/$1');
        $routes->get('program/(:num)/export', 'ProgramSpeakersApiController::exportSpeakers/$1');
        
        // Special operations
        $routes->post('reorder', 'ProgramSpeakersApiController::reorder');
        $routes->post('search', 'ProgramSpeakersApiController::search');
    });

    // Ambassador Dashboard API routes - JWT protected
    $routes->get('ambassador/dashboard/overview', 'AmbassadorsApiController::getDashboardOverview', ['filter' => 'jwt']);
    $routes->get('ambassador/dashboard/profile', 'AmbassadorsApiController::getDashboardProfile', ['filter' => 'jwt']);
    $routes->get('ambassador/dashboard/participants', 'AmbassadorsApiController::getDashboardParticipants', ['filter' => 'jwt']);
    $routes->get('ambassador/dashboard/payments', 'AmbassadorsApiController::getDashboardPayments', ['filter' => 'jwt']);
    $routes->get('ambassador/dashboard/performance', 'AmbassadorsApiController::getDashboardPerformance', ['filter' => 'jwt']);
    $routes->get('ambassador/dashboard/participant-payment/(:num)', 'AmbassadorsApiController::getParticipantPaymentSummary/$1', ['filter' => 'jwt']);
    $routes->get('ambassador/dashboard/participants-payment', 'AmbassadorsApiController::getParticipantPaymentSummary', ['filter' => 'jwt']);
});
