<?php

namespace Config\Routes;

// Payment webhook routes - no authentication required for external service callbacks

// Midtrans (unchanged - existing prod URLs must not move)
$routes->post('api/payment/notification/midtrans', 'Api\Payment\NotificationController::handleMidtransNotification');
$routes->get('api/payment/finish/midtrans', 'Api\Payment\NotificationController::handleMidtransFinish');
$routes->get('api/payment/unfinish/midtrans', 'Api\Payment\NotificationController::handleMidtransUnfinish');
$routes->get('api/payment/error/midtrans', 'Api\Payment\NotificationController::handleMidtransError');

// Xendit - validated via x-callback-token header inside XenditGateway::parseWebhookPayload()
$routes->post('api/payment/notification/xendit', 'Api\Payment\NotificationController::handleXenditNotification');

// Midtrans Payment Integration API routes
$routes->group('api/payments', function ($routes) {
    // get payment by id
    $routes->get('(:num)', 'PaymentsApiController::getPayment/$1');

    // Configuration and initialization
    $routes->get('config', 'PaymentsApiController::getConfig');
    
    // Transaction management
    $routes->post('create', 'PaymentsApiController::createTransaction');
    $routes->get('status/(:num)', 'PaymentsApiController::getStatus/$1');
    $routes->get('participants/(:num)', 'PaymentsApiController::getPaymentsByParticipantId/$1');
    
    // Manual payment handling
    $routes->post('upload-proof', 'PaymentsApiController::uploadPaymentProof');

    // Midtrans notifications and callbacks
    $routes->post('webhook', 'PaymentsApiController::webhook');
    $routes->get('finish', 'PaymentsApiController::finishRedirect');
    $routes->get('unfinish', 'PaymentsApiController::unfinishRedirect');
    $routes->get('error', 'PaymentsApiController::errorRedirect');

    $routes->get('program-payment/(:num)/participant/(:num)', 'PaymentsApiController::getPaymentsByProgramPaymentIdAndParticipantId/$1/$2');
});
