<?php

namespace Config\Routes;

// Reviewer routes - separate from admin routes
$routes->group('reviewers', ['namespace' => 'App\Controllers\Reviewers', 'filter' => 'reviewer_auth'], function ($routes) {    // Dashboard
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('dashboard/ajaxAbstractStats', 'Dashboard::ajaxAbstractStats');
    $routes->get('dashboard/ajaxReviewStats', 'Dashboard::ajaxReviewStats');
    $routes->get('dashboard/getReviewerSubthemes', 'Dashboard::getReviewerSubthemes');
    $routes->get('dashboard/debug', 'Dashboard::debugReviewerData'); // Debug route
    $routes->get('dashboard/test', 'Dashboard::testDataRetrieval'); // Test route    // Abstracts and Papers
    $routes->get('abstracts-papers', 'AbstractsPapers::index');
    $routes->get('abstracts-papers/view/(:num)', 'AbstractsPapers::view/$1');
    $routes->get('abstracts-papers/version/(:num)', 'AbstractsPapers::version/$1');
    $routes->get('abstracts-papers/review/(:num)', 'AbstractsPapers::review/$1');
    $routes->post('abstracts-papers/submit-review/(:num)', 'AbstractsPapers::submitReview/$1');
    $routes->post('abstracts-papers/accept/(:num)', 'AbstractsPapers::accept/$1');
    $routes->post('abstracts-papers/getData', 'AbstractsPapers::getData'); // Make sure this is POST
    $routes->get('abstracts-papers/getStats', 'AbstractsPapers::getStats');
    $routes->get('abstracts-papers/debug', 'AbstractsPapers::debugReviewerAccess'); // Debug route
    $routes->get('abstracts-papers/test-subthemes', 'AbstractsPapers::testSubthemes'); // Test route    $routes->get('abstracts-papers/test-data', 'AbstractsPapers::testData'); // Test data route
    
    // My Info (Profile Management)
    $routes->get('my-info', 'MyInfo::index');
    $routes->post('my-info/update', 'MyInfo::update');
    $routes->post('my-info/change-password', 'MyInfo::changePassword');
    
    // Sign out
    $routes->get('sign-out', 'Auth::signOut');
});

// Reviewer authentication (shared sign-in page with admins but different handling)
$routes->group('', ['namespace' => 'App\Controllers\Reviewers'], function ($routes) {
    // This will handle reviewer authentication from the same sign-in page
    $routes->post('reviewer-sign-in', 'Auth::signIn');
});
