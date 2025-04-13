<?php

namespace Config\Routes;

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
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
    $routes->get('programs/(:num)', 'ProgramsApiController::show/$1');
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

    // program payments
    $routes->get('program-payments', 'ProgramPaymentsApiController::index');
    $routes->get('program-payments/(:num)', 'ProgramPaymentsApiController::show/$1');
    $routes->get('program-payments/program/(:num)', 'ProgramPaymentsApiController::getByProgramId/$1');
    
    // program documents
    $routes->get('program-documents', 'ProgramDocumentsApiController::index');
    $routes->get('program-documents/(:num)', 'ProgramDocumentsApiController::show/$1');
    $routes->get('program-documents/program/(:num)', 'ProgramDocumentsApiController::getByProgram/$1');

    // payment methods
    $routes->get('payment-methods', 'PaymentMethodsApiController::index');
    $routes->get('payment-methods/(:num)', 'PaymentMethodsApiController::show/$1');
    $routes->get('payment-methods/(:num)/update', 'PaymentMethodsApiController::update/$1');
    $routes->post('payment-methods', 'PaymentMethodsApiController::create');
    $routes->post('payment-methods/(:num)', 'PaymentMethodsApiController::update/$1');
    $routes->delete('payment-methods/(:num)', 'PaymentMethodsApiController::delete/$1');
    $routes->get('payment-methods/program/(:num)', 'PaymentMethodsApiController::getByProgramId/$1');
});
