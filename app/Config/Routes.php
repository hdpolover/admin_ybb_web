<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Basic routes
$routes->group('', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'Auth::index');
    $routes->post('sign-in', 'Auth::signIn');
      // Simple Excel export routes
    $routes->get('simpleexport', 'SimpleExportController::index');
    $routes->get('simpleexport/exportSimple', 'SimpleExportController::exportSimple');
});

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
// $routes->get('/login', 'Auth::index', ['filter' => 'noauth']);
// $routes->post('/login', 'Auth::login');

// Payment webhook route - no authentication required for external service callbacks
$routes->post('api/payment/notification/midtrans', 'Api\Payment\NotificationController::handleMidtransNotification');
$routes->get('api/payment/finish/midtrans', 'Api\Payment\NotificationController::handleMidtransFinish');
$routes->get('api/payment/unfinish/midtrans', 'Api\Payment\NotificationController::handleMidtransUnfinish');
$routes->get('api/payment/error/midtrans', 'Api\Payment\NotificationController::handleMidtransError');

// api routes
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

    // Midtrans Payment Integration
    $routes->group('payments', function ($routes) {
        // Payment routes - organized by functionality
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

    // routes for program documents
    $routes->get('program-documents', 'ProgramDocumentsApiController::index');
    $routes->get('program-documents/(:num)', 'ProgramDocumentsApiController::show/$1');
    $routes->get('program-documents/program/(:num)', 'ProgramDocumentsApiController::getByProgram/$1');
    // generate loa /api/program-documents/{documentId}/participant/{participantId}/generate
    $routes->get('program-documents/(:num)/participants/(:num)/generate', 'ProgramDocumentsApiController::generateLoA/$1/$2');

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
    $routes->get('ambassadors/(:num)', 'AmbassadorsApiController::getAmbassador/$1');
    $routes->get('ambassadors/(:any)/referrals', 'AmbassadorsApiController::getAmbassadorReferrals/$1');
    $routes->get('ambassadors/(:num)/generate-link', 'AmbassadorsApiController::generateLink/$1');
    // check query
    $routes->get('ambassadors/check-query', 'AmbassadorsApiController::checkEncryptedQuery');
    // get ambassador by ref code and program id
    $routes->get('ambassadors/programs/(:num)/ref-code/(:any)', 'AmbassadorsApiController::getAmbassadorByRefAndProgram/$1/$2');

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

    // prorgam payments
    $routes->get('program-payments', 'ProgramPaymentsApiController::index');
    $routes->get('program-payments/(:num)', 'ProgramPaymentsApiController::show/$1');
    $routes->get('program-payments/program/(:num)', 'ProgramPaymentsApiController::getByProgramId/$1');

    // payment methods
    $routes->get('payment-methods', 'PaymentMethodsApiController::index');
    $routes->get('payment-methods/(:num)', 'PaymentMethodsApiController::show/$1');
    $routes->get('payment-methods/(:num)/update', 'PaymentMethodsApiController::update/$1');
    $routes->post('payment-methods', 'PaymentMethodsApiController::create');
    $routes->post('payment-methods/(:num)', 'PaymentMethodsApiController::update/$1');
    $routes->delete('payment-methods/(:num)', 'PaymentMethodsApiController::delete/$1');
    $routes->get('payment-methods/program/(:num)', 'PaymentMethodsApiController::getByProgramId/$1');
    // web settings
    $routes->get('web-settings', 'WebSettingApiController::index');

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
    
    // Maintenance check endpoint - publicly accessible
    $routes->get('maintenance/check', 'Api\MaintenanceApiController::check');

    // loa templates
    $routes->get('loa-templates', 'LoaTemplatesApiController::index');
    $routes->get('loa-templates/(:num)', 'LoaTemplatesApiController::show/$1');
    $routes->get('loa-templates/program-documents/(:num)', 'LoaTemplatesApiController::getByProgramDocumentId/$1');
    $routes->get('loa-templates/(:num)/program-documents/(:num)', 'LoaTemplatesApiController::getByProgramDocumentIdAndTemplateId/$1/$2');

    // notifications
   $routes->get('notifications', 'NotificationsApiController::index');
   $routes->get('notifications/(:num)', 'NotificationsApiController::show/$1');
   $routes->get('notifications/random-registration', 'NotificationsApiController::generateRandomRegistrationNotifications');

   $routes->put('notifications/(:num)', 'NotificationsApiController::update/$1');
   $routes->delete('notifications/(:num)', 'NotificationsApiController::delete/$1');

   // program faqs
    $routes->get('program-faqs', 'ProgramFaqsApiController::index');
    $routes->get('program-faqs/(:num)', 'ProgramFaqsApiController::show/$1');
    $routes->get('program-faqs/program/(:num)', 'ProgramFaqsApiController::getByProgram/$1');

    // program announcements
    $routes->get('program-announcements', 'ProgramAnnouncementsApiController::index');
    $routes->get('program-announcements/(:any)', 'ProgramAnnouncementsApiController::show/$1');
    $routes->get('program-announcements/program/(:num)', 'ProgramAnnouncementsApiController::getByProgram/$1');    // program rundowns
    $routes->get('program-rundowns', 'ProgramRundownsApiController::index');
    $routes->get('program-rundowns/(:num)', 'ProgramRundownsApiController::show/$1');
    $routes->get('program-rundowns/program/(:num)', 'ProgramRundownsApiController::getByProgramId/$1');

    // abstracts routes
    $routes->group('abstracts', function ($routes) {
        // Get all abstracts with pagination and filtering
        $routes->get('', 'AbstractsApiController::getAllAbstracts');
        
        // Create a new abstract
        $routes->post('', 'AbstractsApiController::createAbstract');
        
        // Get, update, delete abstract by id
        $routes->get('(:num)', 'AbstractsApiController::getAbstractById/$1');
        $routes->put('(:num)', 'AbstractsApiController::updateAbstract/$1');
        $routes->delete('(:num)', 'AbstractsApiController::deleteAbstract/$1');
        
        // Get abstracts by program or participant
        $routes->get('program/(:num)', 'AbstractsApiController::getAllAbstractsByProgramId/$1');
        $routes->get('participant/(:num)', 'AbstractsApiController::getAllAbstractsByParticipantId/$1');
        $routes->get('participant/(:num)/details', 'AbstractsApiController::getAbstractDetailsByParticipantId/$1');
        
        // Abstract versions
        $routes->get('version/(:num)', 'AbstractsApiController::getAbstractVersionById/$1');
        $routes->get('(:num)/versions', 'AbstractsApiController::getAllAbstractVersionsByAbstractId/$1');
        $routes->post('(:num)/versions', 'AbstractsApiController::createAbstractVersion/$1');
        
        // Abstract authors
        $routes->get('(:num)/authors', 'AbstractsApiController::getAllAbstractAuthorsByAbstractId/$1');
        $routes->post('(:num)/authors', 'AbstractsApiController::addAbstractAuthor/$1');
        
        // Author operations
        $routes->put('authors/(:num)', 'AbstractsApiController::updateAbstractAuthor/$1');
        $routes->delete('authors/(:num)', 'AbstractsApiController::deleteAbstractAuthor/$1');
    });    
    
    // Abstract topics group
    $routes->group('abstract-topics', function ($routes) {
        $routes->get('program/(:num)', 'AbstractTopicsApiController::getAbstractTopicsByProgramId/$1');
    });
});

// Landing API Routes
$routes->group('api/landing', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->get('home', 'LandingApiController::home');
    $routes->get('programs', 'LandingApiController::programs');
    $routes->get('programs/(:num)', 'LandingApiController::programDetail/$1');

    // galllery
    $routes->get('gallery', 'LandingApiController::gallery');
    // insights
    $routes->get('insights', 'LandingApiController::insights');
    $routes->get('insights/(:num)', 'LandingApiController::insightDetail/$1');
    // partners and sponsors
    $routes->get('partners-sponsors', 'LandingApiController::partnersSponsors');
    $routes->get('partners-sponsors/(:num)', 'LandingApiController::partnerSponsorDetail/$1');
    // announcements
    $routes->get('announcements', 'LandingApiController::announcements');
    $routes->get('announcements/(:num)', 'LandingApiController::announcementDetail/$1');
});

// Include modular route files
require_once APPPATH . 'Config/Routes/Admin.php';

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}