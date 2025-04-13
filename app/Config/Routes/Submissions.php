<?php

namespace Config\Routes;

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // submission
    $routes->get('submissions/participants/(:num)', 'SubmissionApiController::index/$1');
    $routes->get('submissions/participants', 'SubmissionApiController::index');
    // get submission data form
    $routes->get('submissions/program/(:num)/form', 'SubmissionApiController::getSubmissionFormData/$1');
    // update participant submission
    $routes->post('submissions/participants/(:num)/update', 'SubmissionApiController::updateSubmission/$1');
    // dedicated endpoint for profile picture uploads
    $routes->post('submissions/participants/(:num)/upload-picture', 'SubmissionApiController::uploadProfilePicture/$1');
    // submit form
    $routes->post('submissions/participants/(:num)/submit', 'SubmissionApiController::submitForm/$1');
    
    // web settings
    $routes->get('web-settings', 'WebSettingApiController::index');
});
