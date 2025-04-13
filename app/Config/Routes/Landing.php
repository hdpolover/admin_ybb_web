<?php

namespace Config\Routes;

$routes->group('api/landing', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->get('home', 'LandingApiController::home');
    $routes->get('programs', 'LandingApiController::programs');
    $routes->get('programs/(:num)', 'LandingApiController::programDetail/$1');
    // insights
    $routes->get('insights', 'LandingApiController::insights');
    $routes->get('insights/(:num)', 'LandingApiController::insightDetail/$1');
    // partners and sponsors
    $routes->get('partners-sponsors', 'LandingApiController::partnersSponsors');
    $routes->get('partners-sponsors/(:num)', 'LandingApiController::partnerSponsorDetail/$1');
    // help & news
    $routes->get('help-news', 'LandingApiController::helpAndNews');
    $routes->get('help-news/(:num)', 'LandingApiController::helpAndNewsDetail/$1');
});
