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
    
    // Debug authentication routes
    $routes->get('debug-auth', 'DebugAuth::index');
    $routes->post('debug-auth/sign-in', 'DebugAuth::signIn');
    $routes->get('debug-menu', 'DebugMenu::index');
    $routes->get('debug-session', 'DebugSession::index');
    $routes->get('fix-menu', 'FixMenuSystem::index');
    $routes->get('test-routes', 'TestRoutes::index');
    
    // Simple Excel export routes
    $routes->get('simpleexport', 'SimpleExportController::index');
    $routes->get('simpleexport/exportSimple', 'SimpleExportController::exportSimple');
    
    // Test routes for certificate debugging
    $routes->get('test-certificates', 'CertificateTest::testPage');
    $routes->get('test-certificates/data', 'CertificateTest::testData');
    
    // Certificate service test routes
    $routes->group('test-certificate-service', function ($routes) {
        $routes->get('connection', 'CertificateTestController::testConnection');
        $routes->get('placeholders', 'CertificateTestController::testPlaceholders');
        $routes->get('generation-data', 'CertificateTestController::testGeneration');
    });
    
    // Certificate debug routes
    $routes->group('debug-certificate', function ($routes) {
        $routes->get('/', 'CertificateDebugController::index');
        $routes->get('step1', 'CertificateDebugController::testStep1');
        $routes->get('step2', 'CertificateDebugController::testStep2');
        $routes->get('step3', 'CertificateDebugController::testStep3');
        $routes->get('step4', 'CertificateDebugController::testStep4');
        $routes->get('step5', 'CertificateDebugController::testStep5');
    });
    
    // YBB Export Test Routes
    $routes->group('test-export', function ($routes) {
        $routes->get('/', 'TestExportController::index');
        $routes->get('test-connection', 'TestExportController::testConnection');
        $routes->post('test-participant-export', 'TestExportController::testParticipantExport');
        $routes->post('test-payment-export', 'TestExportController::testPaymentExport');
        $routes->get('get-templates', 'TestExportController::getTemplates');
        $routes->get('get-templates/(:any)', 'TestExportController::getTemplates/$1');
        $routes->get('status/(:any)', 'TestExportController::getExportStatus/$1');
        $routes->get('download/(:any)', 'TestExportController::downloadExport/$1');
    });
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

    // routes for program documents
    $routes->post('program-documents/upload', 'ProgramDocumentsApiController::addDocument');
    $routes->post('program-documents/participant-file', 'ProgramDocumentsApiController::showfile');

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
    // Search participants by custom parameters
    $routes->get('participants/search', 'ParticipantsApiController::search');
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
    // get participant documents
    $routes->get('participants/(:num)/documents', 'ParticipantsApiController::getParticipantDocuments/$1');
    // get referral by participant id
    $routes->get('participants/(:num)/referrals', 'ParticipantsApiController::getParticipantReferrals/$1');
    // check participant category switch eligibility
    $routes->get('participants/(:num)/switch-category/check', 'ParticipantsApiController::checkSwitchCategoryEligibility/$1');
    // switch participant category between fully_funded and self_funded
    $routes->post('participants/(:num)/switch-category', 'ParticipantsApiController::switchCategory/$1');

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
    
    // payments - actual payment transactions
    $routes->get('payments/(:num)', 'PaymentsApiController::getPayment/$1');
    $routes->get('payments/participants/(:num)', 'PaymentsApiController::getPaymentsByParticipantId/$1');
    $routes->get('payments/program-payments/(:num)/participants/(:num)', 'PaymentsApiController::getPaymentsByProgramPaymentIdAndParticipantId/$1/$2');
    $routes->post('payments/create', 'PaymentsApiController::createTransaction');
    $routes->post('payments/upload-proof', 'PaymentsApiController::uploadPaymentProof');
    $routes->get('payments/status/(:num)', 'PaymentsApiController::getStatus/$1');
    $routes->get('payments/config', 'PaymentsApiController::getConfig');
    $routes->post('payments/webhook', 'PaymentsApiController::webhook');
    $routes->get('payments/finish', 'PaymentsApiController::finishRedirect');
    $routes->get('payments/unfinish', 'PaymentsApiController::unfinishRedirect');
    $routes->get('payments/error', 'PaymentsApiController::errorRedirect');
    
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
    
    // Test endpoint
    $routes->get('test', 'TestApiController::index');
    
    // Simple test endpoint
    $routes->get('simple-test', 'SimpleTestApiController::index');

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
    $routes->get('program-rundowns/program/(:num)', 'ProgramRundownsApiController::getByProgramId/$1');    // abstracts routes
    $routes->group('abstracts', function ($routes) {
        // Get all abstracts with pagination and filtering
        $routes->get('', 'AbstractsApiController::getAllAbstracts');

        // Create a new abstract
        $routes->post('', 'AbstractsApiController::createAbstract');

        // Get, update, delete abstract by id
        $routes->get('(:num)', 'AbstractsApiController::getAbstractById/$1');
        $routes->post('(:num)/update', 'AbstractsApiController::updateAbstract/$1');
        $routes->delete('(:num)', 'AbstractsApiController::deleteAbstract/$1');
        // Get abstracts by program or participant
        $routes->get('programs/(:num)/limits', 'AbstractsApiController::getAbstractLimits/$1');
        $routes->get('program/(:num)', 'AbstractsApiController::getAllAbstractsByProgramId/$1');
        $routes->get('participant/(:num)', 'AbstractsApiController::getAllAbstractsByParticipantId/$1');
        $routes->get('participant/(:num)/details', 'AbstractsApiController::getAbstractDetailsByParticipantId/$1');
        // Abstract versions
        $routes->get('version/(:num)', 'AbstractsApiController::getAbstractVersionById/$1');
        $routes->get('(:num)/versions', 'AbstractsApiController::getAllAbstractVersionsByAbstractId/$1');
        //$routes->post('(:num)/versions', 'AbstractsApiController::createAbstractVersion/$1');
        $routes->post('version/(:num)/update', 'AbstractsApiController::updateAbstractVersion/$1');
        $routes->post('(:num)/save-version', 'AbstractsApiController::saveAbstractVersion/$1');
        // Abstract authors
        $routes->get('(:num)/authors', 'AbstractsApiController::getAllAbstractAuthorsByAbstractId/$1');
        $routes->post('(:num)/authors/validate', 'AbstractsApiController::validateAuthorForAbstract/$1');
        $routes->post('(:num)/authors', 'AbstractsApiController::addAbstractAuthor/$1');

        // Author operations
        $routes->post('authors/(:num)/update', 'AbstractsApiController::updateAbstractAuthor/$1');
        $routes->delete('authors/(:num)', 'AbstractsApiController::deleteAbstractAuthor/$1');
    });

    // Abstract topics group
    $routes->group('abstract-topics', function ($routes) {
        $routes->get('program/(:num)', 'AbstractTopicsApiController::getAbstractTopicsByProgramId/$1');
    });
    
    // Abstract versions group
    $routes->group('abstract-versions', function ($routes) {
        $routes->get('(:num)', 'AbstractVersionsApiController::getAbstractVersionById/$1');
        $routes->get('compare/(:num)/(:num)', 'AbstractVersionsApiController::compareVersions/$1/$2');
    });

    // abstract settings
    $routes->group('abstract-settings', function ($routes) {
        $routes->get('program/(:num)', 'AbstractSettingsApiController::getAbstractSettingsByProgramId/$1');
    });

    // Certificate API routes
    $routes->group('certificates', function ($routes) {
        // Get participant certificates (based on participant_awards)
        $routes->get('participant/(:num)', 'CertificatesApiController::getParticipantCertificates/$1');
        
        // Get participants assigned to an award (eligible for certificates)
        $routes->get('award/(:num)/participants', 'CertificatesApiController::getCertificateParticipants/$1');
        
        // Get program certificates (awards available for certificates)
        $routes->get('program/(:num)', 'CertificatesApiController::getProgramCertificates/$1');
        
        // Get single certificate details
        $routes->get('(:num)', 'CertificatesApiController::getCertificateDetails/$1');
        
        // Generate new certificate using Python service
        $routes->post('generate', 'CertificatesApiController::generateCertificate');
        
        // Revoke certificate (soft delete)
        $routes->delete('(:num)', 'CertificatesApiController::revokeCertificate/$1');
        
        // Get certificate statistics for participant
        $routes->get('stats/(:num)', 'CertificatesApiController::getCertificateStats/$1');
        
        // Regenerate existing certificate using Python service
        $routes->post('(:num)/regenerate', 'CertificatesApiController::regenerateCertificate/$1');
        
        // Health check for certificate service
        $routes->get('health', 'CertificatesApiController::health');
        
        // Get available placeholders
        $routes->get('placeholders', 'CertificatesApiController::getPlaceholders');
    });
});



// Include modular route files
require_once APPPATH . 'Config/Routes/Reviewers.php';
require_once APPPATH . 'Config/Routes/Admin.php';
require_once APPPATH . 'Config/Routes/Api.php';
require_once APPPATH . 'Config/Routes/Landing.php';
require_once APPPATH . 'Config/Routes/RoleManagement.php';

// Menu management test routes
$routes->group('menu-test', ['namespace' => 'App\Controllers', 'filter' => 'access_control'], function ($routes) {
    $routes->get('/', 'MenuTestController::index');
    $routes->get('super-only', 'MenuTestController::superOnly');
    $routes->get('program-admin', 'MenuTestController::programAdminAccess');
    $routes->get('editor', 'MenuTestController::editorAccess');
    $routes->get('user-info', 'MenuTestController::userInfo');
});

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