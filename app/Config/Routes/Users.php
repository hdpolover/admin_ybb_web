<?php

namespace Config\Routes;

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // participants 
    $routes->get('participants', 'ParticipantsApiController::index');
    //  POST /api/participants/users/{userId}/create
    $routes->post('participants/users/(:num)/create', 'ParticipantsApiController::createFromUserId/$1');
    $routes->post('participants', 'ParticipantsApiController::create');
    $routes->get('participants/(:num)', 'ParticipantsApiController::show/$1');
    $routes->get('participants/current-program', 'ParticipantsApiController::getCurrentProgramParticipants');
    //get participants by user id
    $routes->get('participants/user/(:num)', 'ParticipantsApiController::getByUserId/$1');
    // get random participant photos by program id
    $routes->get('participants/program/(:num)/photos', 'ParticipantsApiController::getProgramParticipantsPhotos/$1');
    // get participants essays
    $routes->get('participants/(:num)/essays', 'ParticipantsApiController::getParticipantEssays/$1');
    // get participants subthemes
    $routes->get('participants/(:num)/subthemes', 'ParticipantsApiController::getParticipantSubthemes/$1');
    // get participant statuses
    $routes->get('participants/(:num)/status', 'ParticipantsApiController::getParticipantStatuses/$1');
    // get referral by participant id
    $routes->get('participants/(:num)/referrals', 'ParticipantsApiController::getParticipantReferrals/$1');

    // ambassadors
    $routes->get('ambassadors', 'AmbassadorsApiController::index');
    $routes->get('ambassadors/(:num)', 'AmbassadorsApiController::show/$1');
    $routes->get('ambassadors/(:any)/referrals', 'AmbassadorsApiController::getAmbassadorReferrals/$1');
    $routes->get('ambassadors/(:num)/generate-link', 'AmbassadorsApiController::generateLink/$1');
    // check query
    $routes->post('ambassadors/check-query', 'AmbassadorsApiController::checkEncryptedQuery');
    // get ambassador by ref code and program id
    $routes->get('ambassadors/programs/(:num)/ref-code/(:any)', 'AmbassadorsApiController::getAmbassadorByRefAndProgram/$1/$2');
});
