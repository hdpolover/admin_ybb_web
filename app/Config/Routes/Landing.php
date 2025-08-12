<?php

namespace Config\Routes;

$routes->group('api/landing', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->get('home', 'LandingApiController::home');
    $routes->get('programs', 'LandingApiController::programs');
    $routes->get('programs/(:num)', 'LandingApiController::programDetail/$1');
    
    // Program by slug
    $routes->get('program-by-slug/(:any)', 'LandingApiController::programBySlug/$1');
    
    // Gallery
    $routes->get('gallery', 'LandingApiController::gallery');
    
    // Insights
    $routes->get('insights', 'LandingApiController::insights');
    $routes->get('insights/(:num)', 'LandingApiController::insightDetail/$1');
    $routes->get('insights/debug', 'LandingApiController::insightsDebug');
    
    // Partners and sponsors
    $routes->get('partners-sponsors', 'LandingApiController::partnersSponsors');
    $routes->get('partners-sponsors/(:num)', 'LandingApiController::partnerSponsorDetail/$1');
    
    // Help & news
    $routes->get('help-news', 'LandingApiController::helpAndNews');
    $routes->get('help-news/(:num)', 'LandingApiController::helpAndNewsDetail/$1');
    
    // Announcements
    $routes->get('announcements', 'LandingApiController::announcements');
    $routes->get('announcements/(:num)', 'LandingApiController::announcementDetail/$1');
    $routes->get('announcement-by-slug/(:any)', 'LandingApiController::announcementBySlug/$1');
    
    // Development/Testing - Clear cache and test endpoints
    $routes->get('clear-cache', 'LandingApiController::clearCache');
    $routes->get('test-payment-flags', 'LandingApiController::testPaymentFlags');
});
