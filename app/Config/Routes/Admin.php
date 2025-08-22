<?php

namespace Config\Routes;

$routes->group('', ['namespace' => 'App\Controllers', 'filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('dashboard/ajaxRegistrationStats', 'Dashboard::ajaxRegistrationStats');
    $routes->get('dashboard/ajaxGenderStats', 'Dashboard::ajaxGenderStats');
    $routes->get('dashboard/ajaxAgeStats', 'Dashboard::ajaxAgeStats');
    $routes->get('dashboard/ajaxNationalityStats', 'Dashboard::ajaxNationalityStats');
    // welcome
    $routes->get('welcome', 'Welcome::index');
    $routes->get('welcome/set-program/(:num)', 'Welcome::setProgram/$1');
    $routes->get('sign-out', 'Auth::signOut');

    // YBB Export API routes (Python Flask service integration)
    $routes->group('exports', function ($routes) {
        $routes->get('/', 'YbbExportController::index');
        $routes->post('participants', 'YbbExportController::exportParticipants');
        $routes->post('payments', 'YbbExportController::exportPayments');
        $routes->post('ambassadors', 'YbbExportController::exportAmbassadors');
        $routes->get('status/(:any)', 'YbbExportController::getExportStatus/$1');
        $routes->get('download/(:any)', 'YbbExportController::downloadExport/$1');
        $routes->get('download/(:any)/zip', 'YbbExportController::downloadExportZip/$1');
        $routes->get('download/(:any)/batch/(:num)', 'YbbExportController::downloadBatchFile/$1/$2');
        $routes->get('templates', 'YbbExportController::getTemplates');
        $routes->get('templates/(:any)', 'YbbExportController::getTemplates/$1');
        $routes->post('estimate', 'YbbExportController::estimateExport');
        $routes->get('test-connection', 'YbbExportController::testConnection');
        $routes->get('storage-info', 'YbbExportController::getStorageInfo');
    });    // Users group    

    $routes->group('users', ['filter' => 'program_selection'], function ($routes) {
        // participants
        $routes->get('participants', 'Participants::index');
        $routes->get('participants/view/(:num)', 'Participants::view/$1');
        $routes->get('participants/edit/(:num)', 'Participants::edit/$1');
        $routes->get('participants/getData', 'Participants::getData');
        $routes->post('participants/export', 'Participants::export');
        $routes->get('participants/export/(:num)', 'Participants::export/$1');
        $routes->get('participants/export_batch', 'Participants::export_batch');

        // ambassadors
        $routes->get('ambassadors', 'Ambassadors::index');
        $routes->post('ambassadors/create', 'Ambassadors::create');
        $routes->post('ambassadors/update', 'Ambassadors::update');
        $routes->post('ambassadors/delete/(:num)', 'Ambassadors::delete/$1');
        $routes->get('ambassadors/view/(:num)', 'Ambassadors::view/$1');
        $routes->get('ambassadors/edit/(:num)', 'Ambassadors::edit/$1');
        $routes->get('ambassadors/getData', 'Ambassadors::getData');
        $routes->get('ambassadors/debugSession', 'Ambassadors::debugSession');
        $routes->get('ambassadors/getAmbassadorData/(:num)', 'Ambassadors::getAmbassadorData/$1');
    });

    //Scoring
    $routes->group('scorings', ['filter' => 'program_selection'], function ($routes) {
        $routes->get('fully_funded', 'Scorings::index');
        $routes->post('fully_funded/saveScore', 'Scorings::saveScore');
        $routes->get('fully_funded/view/(:num)', 'Scorings::view/$1');
        $routes->get('fully_funded/edit/(:num)', 'Scorings::edit/$1');
        $routes->get('fully_funded/getData', 'Scorings::getData');
        $routes->post('fully_funded/export', 'Scorings::export');
        $routes->get('fully_funded/export/(:num)', 'Scorings::export/$1');
        $routes->get('fully_funded/export_batch', 'Scorings::export_batch');
        $routes->get('interview', 'Scorings::interview');
        $routes->get('interview/getData', 'Scorings::getData2');
    });

    // announcements group
    $routes->group('announcements', ['filter' => 'program_selection'], function ($routes) {
        $routes->get('', 'Announcements::index');
        $routes->get('view/(:num)', 'Announcements::view/$1');
        $routes->get('add', 'Announcements::add');
        $routes->get('edit/(:num)', 'Announcements::edit/$1');
        $routes->post('create', 'Announcements::create');
        $routes->post('update/(:num)', 'Announcements::update/$1');
        $routes->post('delete/(:num)', 'Announcements::delete/$1');
        // Keep GET method for backward compatibility but will update the app to use POST
        $routes->get('delete/(:num)', 'Announcements::delete/$1');
    });

    // Payment routes
    $routes->group('payments', ['filter' => 'program_selection'], function ($routes) {
        $routes->get('', 'Payments::index');
        $routes->get('getData', 'Payments::getData');
        $routes->get('view/(:num)', 'Payments::view/$1');
        $routes->post('update-status/(:num)', 'Payments::updateStatus/$1');
        $routes->post('export', 'Payments::export');
        $routes->get('make', 'Payments::makePayment');
    });        // submissions group
    $routes->group('submissions', ['filter' => 'program_selection'], function ($routes) {
        // Abstract Papers routes
        $routes->group('abstracts-papers', function ($routes) {
            $routes->get('/', 'AbstractPapers::index');
            $routes->get('view/(:num)', 'AbstractPapers::view/$1');
            $routes->get('details/(:num)', 'AbstractPapers::details/$1');
            $routes->get('edit/(:num)', 'AbstractPapers::edit/$1');
            $routes->post('store', 'AbstractPapers::store');
            $routes->post('update/(:num)', 'AbstractPapers::update/$1');
            $routes->post('delete/(:num)', 'AbstractPapers::delete/$1');
            $routes->get('getAbstractsByProgram/(:num)', 'AbstractPapers::getAbstractsByProgram/$1');
            $routes->get('getAbstractsByProgram', 'AbstractPapers::getAbstractsByProgram');
            $routes->get('getAbstractData/(:num)', 'AbstractPapers::getAbstractData/$1');            $routes->get('getAbstractVersions/(:num)', 'AbstractPapers::getAbstractVersions/$1');
            $routes->get('getAbstractAuthors/(:num)', 'AbstractPapers::getAbstractAuthors/$1');
            $routes->get('getAbstractVersion/(:num)', 'AbstractPapers::getAbstractVersion/$1');
            $routes->get('getVersionFeedback/(:num)', 'AbstractPapers::getVersionFeedback/$1');
            $routes->post('submitFeedback', 'AbstractPapers::submitFeedback');            $routes->post('quickAction', 'AbstractPapers::quickAction');
            $routes->get('pdf/(:num)', 'AbstractPapers::pdf/$1');
            $routes->get('export/(:num)', 'AbstractPapers::export/$1');
            $routes->post('createNewVersion', 'AbstractPapers::createNewVersion');
            $routes->post('removeAuthor/(:num)', 'AbstractPapers::removeAuthor/$1');            $routes->get('getTopicsByProgram/(:num)', 'AbstractPapers::getTopicsByProgram/$1');
            $routes->get('getTopicsByProgram', 'AbstractPapers::getTopicsByProgram');
            $routes->get('getReviewerSubthemes', 'AbstractPapers::getReviewerSubthemes');
        });
        $routes->group('agreements', function ($routes) {
            $routes->get('/', 'Submissions\Agreements::index');
            $routes->post('update-status', 'Submissions\Agreements::updateStatus');
        });
        
    });
    
       
    // document routes
    $routes->group('documents', ['filter' => 'program_selection'], function ($routes) {        // Program Documents routes
        $routes->group('program-documents', ['filter' => 'auth'], function ($routes) {
            $routes->get('/', 'ProgramDocuments::index');
            $routes->get('view/(:num)', 'ProgramDocuments::view/$1');
            $routes->get('edit/(:num)', 'ProgramDocuments::edit/$1');
            $routes->post('create', 'ProgramDocuments::create');
            $routes->post('update/(:num)', 'ProgramDocuments::update/$1');
            $routes->post('delete/(:num)', 'ProgramDocuments::delete/$1');
            $routes->get('get-document/(:num)', 'ProgramDocuments::getDocument/$1');

            // LOA Template routes
            $routes->get('loa-settings/(:num)', 'ProgramDocuments::loaSettings/$1');
            $routes->post('save-loa-template/(:num)', 'ProgramDocuments::saveLoaTemplate/$1');
            $routes->get('generate-loa/(:num)/(:num)', 'ProgramDocuments::generateLoa/$1/$2');
        });

        // certificates
        $routes->get('certificates', 'Certificates::index');
        $routes->get('certificates/test', 'Certificates::testIndex');
        $routes->get('certificates/debug', 'Certificates::debugData');
        $routes->get('certificates/testParticipants/(:num)', 'Certificates::testParticipantsData/$1');
        $routes->get('certificates/view/(:num)', 'Certificates::view/$1');
        $routes->get('certificates/getData', 'Certificates::getData');
        $routes->get('certificates/getAwardDetails/(:num)', 'Certificates::getAwardDetails/$1');
        $routes->get('certificates/getAvailableParticipants/(:num)', 'Certificates::getAvailableParticipants/$1');
        $routes->get('certificates/getAvailableParticipantsData/(:num)', 'Certificates::getAvailableParticipantsData/$1');
        $routes->post('certificates/getAvailableParticipantsData/(:num)', 'Certificates::getAvailableParticipantsData/$1');
        $routes->get('certificates/getAssignedParticipantsData/(:num)', 'Certificates::getAssignedParticipantsData/$1');
        $routes->post('certificates/getAssignedParticipantsData/(:num)', 'Certificates::getAssignedParticipantsData/$1');
        $routes->post('certificates/assignParticipants', 'Certificates::assignParticipants');
        $routes->post('certificates/removeParticipant', 'Certificates::removeParticipant');
        $routes->post('certificates/issueCertificates', 'Certificates::issueCertificates');
        $routes->post('certificates/revokeCertificate', 'Certificates::revokeCertificate');

        // configuration
        $routes->get('configuration', 'Configuration::index');
        $routes->post('configuration/update', 'Configuration::update');
        $routes->get('configuration/getData', 'Configuration::getData');
    });

    // Master Data routes
    $routes->group('master-data', ['filter' => 'program_selection'], function ($routes) {        // program payment group
        $routes->group('program-payments', function ($routes) {
            $routes->get('/', 'ProgramPayments::index');
            $routes->get('view/(:num)', 'ProgramPayments::view/$1');
            $routes->get('getData', 'ProgramPayments::getData');
            $routes->get('getPaymentOption/(:num)', 'ProgramPayments::getPaymentOption/$1');
            $routes->post('create', 'ProgramPayments::create');
            $routes->post('update/(:num)', 'ProgramPayments::update/$1');
            $routes->get('delete/(:num)', 'ProgramPayments::delete/$1');
        });

        // program schedule/timeline group
        $routes->group('timelines', function ($routes) {
            $routes->get('/', 'ProgramSchedules::index');
            $routes->get('view/(:num)', 'ProgramSchedules::view/$1');
            $routes->get('getData', 'ProgramSchedules::getData');
            $routes->get('getSchedule/(:num)', 'ProgramSchedules::getSchedule/$1');
            $routes->post('create', 'ProgramSchedules::create');
            $routes->post('update/(:num)', 'ProgramSchedules::update/$1');
            $routes->get('delete/(:num)', 'ProgramSchedules::delete/$1');
        });

        // FAQs management
        $routes->group('faqs', function ($routes) {
            $routes->get('/', 'Faqs::index');
            $routes->get('get/(:num)', 'Faqs::get/$1');
            $routes->post('create', 'Faqs::create');
            $routes->post('update', 'Faqs::update');
            $routes->post('delete', 'Faqs::delete');
        });

        // program details group
        $routes->group('program-details', function ($routes) {
            $routes->get('/', 'ProgramDetails::index');
            $routes->get('view/(:num)', 'ProgramDetails::view/$1');
            $routes->get('getData', 'ProgramDetails::getData');
            $routes->post('create', 'ProgramDetails::create');
            $routes->post('category/(:num)/update', 'ProgramDetails::updateCategoryDetails/$1');
            $routes->get('delete/(:num)', 'ProgramDetails::delete/$1');
            $routes->post('program/(:num)/update', 'ProgramDetails::updateProgramDetails/$1');
        });

        // submission form group
        $routes->group('submission-form', function ($routes) {
            $routes->get('/', 'SubmissionForm::index');
            $routes->get('view/(:num)', 'SubmissionForm::view/$1');
            $routes->get('getData', 'SubmissionForm::getData');
            $routes->post('create', 'SubmissionForm::create');
            $routes->post('update/(:num)', 'SubmissionForm::update/$1');
            $routes->get('delete/(:num)', 'SubmissionForm::delete/$1');
            // Category Management Routes
            $routes->post('add-category', 'SubmissionForm::addCategory');
            $routes->post('update-category/(:num)', 'SubmissionForm::updateCategory/$1');
            $routes->post('delete-category/(:num)', 'SubmissionForm::deleteCategory/$1');
            $routes->get('get-category-by-id/(:num)', 'SubmissionForm::getCategoryById/$1');

            // SubTheme Management Routes
            $routes->post('add-subtheme', 'SubmissionForm::addSubTheme');
            $routes->post('update-subtheme/(:num)', 'SubmissionForm::updateSubTheme/$1');
            $routes->post('delete-subtheme/(:num)', 'SubmissionForm::deleteSubTheme/$1');
            $routes->get('get-subtheme-by-id/(:num)', 'SubmissionForm::getSubThemeById/$1');

            // Essay Management Routes
            $routes->post('add-essay', 'SubmissionForm::addEssay');
            $routes->post('update-essay/(:num)', 'SubmissionForm::updateEssay/$1');
            $routes->post('delete-essay/(:num)', 'SubmissionForm::deleteEssay/$1');
            $routes->get('get-essay-by-id/(:num)', 'SubmissionForm::getEssayById/$1');
        });

        // payment methods
        $routes->group('payment-methods', function ($routes) {
            $routes->get('/', 'PaymentMethods::index');
            $routes->get('view/(:num)', 'PaymentMethods::view/$1');
            $routes->get('getData', 'PaymentMethods::getData');
            $routes->get('getPaymentMethod/(:num)', 'PaymentMethods::getPaymentMethod/$1');
            $routes->post('create', 'PaymentMethods::create');
            $routes->post('update/(:num)', 'PaymentMethods::update/$1');
            $routes->get('delete/(:num)', 'PaymentMethods::delete/$1');
        });

        // program photos
        $routes->group('program-photos', function ($routes) {
            $routes->get('/', 'ProgramPhotos::index');
            $routes->get('view/(:num)', 'ProgramPhotos::view/$1');
            $routes->get('getData', 'ProgramPhotos::getData');
            // Regular form submit routes (kept for backward compatibility)
            $routes->post('create', 'ProgramPhotos::create');
            $routes->post('update/(:num)', 'ProgramPhotos::update/$1');
            $routes->get('delete/(:num)', 'ProgramPhotos::delete/$1');
            // AJAX routes
            $routes->post('ajax-create', 'ProgramPhotos::ajaxCreate');
            $routes->post('ajax-update/(:num)', 'ProgramPhotos::ajaxUpdate/$1');
            $routes->post('ajax-delete/(:num)', 'ProgramPhotos::ajaxDelete/$1');
        });

        // program testimonies
        $routes->group('program-testimonies', function ($routes) {
            $routes->get('/', 'ProgramTestimonies::index');
            $routes->get('view/(:num)', 'ProgramTestimonies::view/$1');
            $routes->get('getData', 'ProgramTestimonies::getData');
            $routes->post('create', 'ProgramTestimonies::create');
            $routes->post('update/(:num)', 'ProgramTestimonies::update/$1');
            $routes->get('delete/(:num)', 'ProgramTestimonies::delete/$1');
        });        
        
        // program rundowns        
        $routes->group('program-rundowns', function ($routes) {
            $routes->get('/', 'ProgramRundowns::index');
            $routes->get('view/(:num)', 'ProgramRundowns::view/$1');
            $routes->get('getData', 'ProgramRundowns::getData');
            $routes->post('create', 'ProgramRundowns::create');
            $routes->post('update/(:num)', 'ProgramRundowns::update/$1');
            $routes->get('delete/(:num)', 'ProgramRundowns::delete/$1');
            $routes->get('getRundown/(:num)', 'ProgramRundowns::getRundown/$1');
        });        
          // abstract topics
        $routes->group('abstract-topics', function ($routes) {
            $routes->get('/', 'AbstractTopics::index');
            $routes->get('view/(:num)', 'AbstractTopics::view/$1');
            $routes->get('getAbstractTopic/(:num)', 'AbstractTopics::getAbstractTopic/$1');
            $routes->post('create', 'AbstractTopics::create');
            $routes->post('update/(:num)', 'AbstractTopics::update/$1');
            $routes->get('delete/(:num)', 'AbstractTopics::delete/$1');  // Keep for backward compatibility
            $routes->post('delete/(:num)', 'AbstractTopics::delete/$1');  // Add POST endpoint for AJAX
        });

        // abstract reviewers
        $routes->group('abstract-reviewers', function ($routes) {
            $routes->get('/', 'AbstractReviewers::index');
            $routes->get('getData', 'AbstractReviewers::getData');
            $routes->get('edit/(:num)', 'AbstractReviewers::edit/$1');
            $routes->get('getSubthemes', 'AbstractReviewers::getSubthemes');
            $routes->post('create', 'AbstractReviewers::create');
            $routes->post('update/(:num)', 'AbstractReviewers::update/$1');
            $routes->post('delete/(:num)', 'AbstractReviewers::delete/$1');
            $routes->post('toggleStatus/(:num)', 'AbstractReviewers::toggleStatus/$1');
        });

        // abstract settings
        $routes->group('abstract-settings', function ($routes) {
            $routes->get('/', 'AbstractSettings::index');
            $routes->get('view', 'AbstractSettings::view');
            $routes->get('getSettings', 'AbstractSettings::getSettings');
            $routes->post('save', 'AbstractSettings::save');
            $routes->post('createDefault', 'AbstractSettings::createDefault');
            $routes->post('reset', 'AbstractSettings::reset');
            $routes->post('deactivate', 'AbstractSettings::deactivate');
        });

        // program awards
        $routes->group('program-awards', function ($routes) {
            $routes->get('/', 'ProgramAwards::index');
            $routes->get('view/(:num)', 'ProgramAwards::view/$1');
            $routes->get('getData', 'ProgramAwards::getData');
            $routes->get('getAward/(:num)', 'ProgramAwards::getAward/$1');
            $routes->post('create', 'ProgramAwards::create');
            $routes->post('update/(:num)', 'ProgramAwards::update/$1');
            $routes->get('delete/(:num)', 'ProgramAwards::delete/$1');
            $routes->post('delete/(:num)', 'ProgramAwards::delete/$1');
        });

        // program certificates
        $routes->group('program-certificates', function ($routes) {
            $routes->get('/', 'ProgramCertificates::index');
            $routes->get('add', 'ProgramCertificates::add');
            $routes->get('edit/(:num)', 'ProgramCertificates::edit/$1');
            $routes->get('view/(:num)', 'ProgramCertificates::view/$1');
            $routes->get('getData', 'ProgramCertificates::getData');
            $routes->get('getCertificate/(:num)', 'ProgramCertificates::getCertificate/$1');
            $routes->post('create', 'ProgramCertificates::create');
            $routes->post('update/(:num)', 'ProgramCertificates::update/$1');
            $routes->get('delete/(:num)', 'ProgramCertificates::delete/$1');
            $routes->post('delete/(:num)', 'ProgramCertificates::delete/$1');
            $routes->post('publish/(:num)', 'ProgramCertificates::publish/$1');
            $routes->get('content-blocks/(:num)', 'ProgramCertificates::contentBlocks/$1');
            $routes->post('content-blocks/save', 'ProgramCertificates::saveContentBlocks');
        });
    });

    // settings group

    $routes->group('settings', function ($routes) {
        // main config group
        $routes->group('main-config', function ($routes) {
            $routes->get('/', 'MainConfig::index');
            $routes->get('view/(:num)', 'MainConfig::view/$1');
            $routes->get('edit/(:num)', 'MainConfig::edit/$1');
            $routes->post('create', 'MainConfig::create');
            $routes->post('update/(:num)', 'MainConfig::update/$1');
            $routes->get('delete/(:num)', 'MainConfig::delete/$1');
        });

        // admin group
        $routes->group('admin', function ($routes) {
            $routes->get('/', 'Admin::index');
            $routes->get('view/(:num)', 'Admin::view/$1');
            $routes->get('edit/(:num)', 'Admin::edit/$1');
            $routes->post('create', 'Admin::create');
            $routes->post('update/(:num)', 'Admin::update/$1');
            $routes->get('delete/(:num)', 'Admin::delete/$1');
        });
    });

    // Cache management routes
    $routes->group('cache', function ($routes) {
        $routes->get('clear/programs', 'CacheManager::clearProgramCaches');
        $routes->get('clear/program/(:num)', 'CacheManager::clearProgramCache/$1');
        $routes->get('clear/landing', 'CacheManager::clearLandingCache');
        $routes->get('stats', 'CacheManager::stats');
    });
});

// Temporary debug routes (remove in production)
$routes->group('debug', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('certificates-data', 'Certificates::testGetData');
    $routes->get('certificates-simple', 'Certificates::simpleTest');
    $routes->get('certificates-direct', 'Certificates::testDataDirect');
    $routes->get('certificates-view/(:num)', 'Certificates::debugView/$1');
});

// Session test routes
$routes->group('session-test', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('set-program', 'SessionTest::setProgram');
    $routes->get('select-program/(:num)', 'SessionTest::selectProgram/$1');
});

// Remove excel route
// $routes->get('excel', 'Excel::index');