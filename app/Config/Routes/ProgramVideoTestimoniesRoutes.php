<?php

// Program Video Testimonies Routes
$routes->group('master-data/program-video-testimonies', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'ProgramVideoTestimonies::index');
    $routes->post('create', 'ProgramVideoTestimonies::create');
    $routes->get('view/(:num)', 'ProgramVideoTestimonies::view/$1');
    $routes->post('update/(:num)', 'ProgramVideoTestimonies::update/$1');
    $routes->get('delete/(:num)', 'ProgramVideoTestimonies::delete/$1');
    $routes->post('updateOrder', 'ProgramVideoTestimonies::updateOrder');
    $routes->get('data', 'ProgramVideoTestimonies::getData');
});