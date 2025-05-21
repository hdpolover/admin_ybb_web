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

    // Excel Export routes
    $routes->group('exports', function ($routes) {
        $routes->get('/', 'ExportController::index');
        $routes->get('participants', 'ExportController::exportParticipants');
        $routes->post('participants/filtered', 'ExportController::exportFilteredParticipants');
        $routes->get('participants/filtered', 'ExportController::exportFilteredParticipants');
        $routes->post('participants/by-payment', 'ExportController::exportParticipantsByPaymentStatus');
        $routes->get('payments', 'ExportController::exportPayments');
        $routes->get('custom', 'ExportController::exportCustomData');
    });

    // Explicitly add POST routes outside the group to ensure they're registered
    $routes->post('exports/participants/filtered', 'ExportController::exportFilteredParticipants');
    $routes->post('exports/participants/by-payment', 'ExportController::exportParticipantsByPaymentStatus');    // Users group    

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
        $routes->get('ambassadors/getAmbassadorData/(:num)', 'Ambassadors::getAmbassadorData/$1');
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
    });    // document routes
    $routes->group('documents', ['filter' => 'program_selection'], function ($routes) {        // Program Documents routes
        $routes->group('program-documents', ['filter' => 'auth'], function ($routes) {
            $routes->get('/', 'ProgramDocuments::index');
            $routes->get('view/(:num)', 'ProgramDocuments::view/$1');
            $routes->get('edit/(:num)', 'ProgramDocuments::edit/$1');
            $routes->post('create', 'ProgramDocuments::create');
            $routes->post('update/(:num)', 'ProgramDocuments::update/$1');
            $routes->get('delete/(:num)', 'ProgramDocuments::delete/$1');
            $routes->get('get-document/(:num)', 'ProgramDocuments::getDocument/$1');

            // LOA Template routes
            $routes->get('loa-settings/(:num)', 'ProgramDocuments::loaSettings/$1');
            $routes->post('save-loa-template/(:num)', 'ProgramDocuments::saveLoaTemplate/$1');
            $routes->get('generate-loa/(:num)/(:num)', 'ProgramDocuments::generateLoa/$1/$2');
        });

        // Abstract Papers routes
        $routes->group('abstracts-papers', function ($routes) {
            $routes->get('/', 'AbstractPapers::index');
            $routes->get('view/(:num)', 'AbstractPapers::view/$1');
            $routes->get('edit/(:num)', 'AbstractPapers::edit/$1');
            $routes->post('store', 'AbstractPapers::store');
            $routes->post('update/(:num)', 'AbstractPapers::update/$1');
            $routes->get('delete/(:num)', 'AbstractPapers::delete/$1');
            $routes->get('getAbstractData/(:num)', 'AbstractPapers::getAbstractData/$1');
            $routes->get('getAbstractsByProgram', 'AbstractPapers::getAbstractsByProgram');
            $routes->get('getAbstractsByProgram/(:num)', 'AbstractPapers::getAbstractsByProgram/$1');
        });

        // certificates
        $routes->get('certificates', 'Certificates::index');
        $routes->get('certificates/view/(:num)', 'Certificates::view/$1');
        $routes->get('certificates/getData', 'Certificates::getData');

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
            $routes->post('add-sub-theme', 'SubmissionForm::addSubTheme');
            $routes->post('update-sub-theme/(:num)', 'SubmissionForm::updateSubTheme/$1');
            $routes->post('delete-sub-theme/(:num)', 'SubmissionForm::deleteSubTheme/$1');
            $routes->get('get-sub-theme-by-id/(:num)', 'SubmissionForm::getSubThemeById/$1');

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
});

// Remove excel route
// $routes->get('excel', 'Excel::index');
