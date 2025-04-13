<?php

namespace Config\Routes;

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // Auth routes - organized by functionality
    $routes->group('auth', function ($routes) {
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

    // Protected routes with JWT authentication
    $routes->group('', ['filter' => 'jwt'], function ($routes) {
        $routes->get('my-programs', 'ProgramsApiController::getUserPrograms');
        // Add more protected endpoints here
    });

    // Profile picture routes - publicly accessible
    $routes->post('participants/(:num)/upload-picture', 'ProfileApiController::uploadParticipantProfilePicture/$1');

    // users
    $routes->get('users', 'UsersApiController::index');
    $routes->get('users/(:num)', 'UsersApiController::show/$1');
    // check users by params
    $routes->get('users/check', 'UsersApiController::checkUserByParams');

    // Maintenance check endpoint - publicly accessible
    $routes->get('maintenance/check', 'Api\MaintenanceApiController::check');
});
