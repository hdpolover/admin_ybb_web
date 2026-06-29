<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Dashboard')); ?>

    <!-- jsvectormap css -->
    <link href="<?= base_url('assets/libs/jsvectormap/jsvectormap.min.css') ?>" rel="stylesheet" type="text/css" />

    <!-- apexcharts -->
    <link href="<?= base_url('assets/libs/apexcharts/apexcharts.min.css') ?>" rel="stylesheet" type="text/css" /> <!-- DataTables -->
    <link href="<?= base_url('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') ?>" rel="stylesheet" type="text/css" />
    <!-- Custom DataTables styling -->
    <link href="<?= base_url('assets/css/datatables-custom.css') ?>" rel="stylesheet" type="text/css" />

    <style>
        .opacity-50 {
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }

        /* Loading spinner styles */
        .chart-loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .chart-loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .chart-container {
            position: relative;
        }

        /* DataTable button styling */
        .dt-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }

        /* Modal improvements */
        .modal-lg {
            max-width: 900px;
        }

        @media (max-width: 992px) {
            .modal-lg {
                max-width: 95%;
                margin-left: auto;
                margin-right: auto;
            }
        }

        /* Make sure modal body can scroll on small screens */
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        /* Improved DataTable styling for modals */
        .modal .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px;
            text-align: right;
        }

        .modal .dataTables_wrapper .dataTables_filter input {
            margin-left: 5px;
            padding: 5px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            width: 220px;
        }

        .modal .dataTables_wrapper .dataTables_length {
            margin-bottom: 15px;
        }

        .modal .dataTables_wrapper .dataTables_length select {
            padding: 5px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        /* Ensure pagination is visible and properly styled */
        .modal .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 5px 10px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>

    <?= $this->include('partials/head-css') ?>
</head>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Dashboard', 'title' => esc($program->name) . ' Dashboard')); ?>

                    <!-- Summary Widgets -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-primary rounded-circle fs-3">
                                                <i class="ri-user-3-line text-primary"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total Participants</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($summary->total_participants) ?></h4>
                                            <p class="text-muted mb-0">
                                                <span class="badge bg-light text-success">
                                                    <i class="ri-user-add-line align-middle"></i> <?= number_format($summary->participants_today) ?> today
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-warning rounded-circle fs-3">
                                                <i class="ri-team-line text-warning"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Ambassadors</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($summary->total_ambassadors) ?></h4>
                                            <p class="text-muted mb-0">Active ambassadors</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded-circle fs-3">
                                                <i class="ri-link text-success"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Referred Participants</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($summary->total_referred) ?></h4>
                                            <p class="text-muted mb-0">
                                                <span class="badge bg-light text-success">
                                                    <?= $summary->referral_percentage ?>% of total
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded-circle fs-3">
                                                <i class="ri-calendar-event-line text-info"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Program Status</p>
                                            <?php if ($program->is_active == 1): ?>
                                                <h4 class="fs-5 flex-grow-1 mb-1 text-success">Active</h4>
                                                <p class="text-muted mb-0">
                                                    <?= $program->start_date ?>
                                                </p>
                                            <?php else: ?>
                                                <h4 class="fs-5 flex-grow-1 mb-1 text-danger">Inactive</h4>
                                                <p class="text-muted mb-0">Program not active</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Chart -->
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="card card-height-100">
                                <div class="card-header border-0 align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">
                                        <span id="registration-title">Daily Registration Trend</span>
                                    </h4>
                                    <div class="flex-shrink-0 d-flex align-items-center gap-1">
                                        <a href="<?= site_url('dashboard/registrations-analytics') ?>" class="btn btn-soft-primary btn-sm me-1">
                                            Analytics
                                        </a>
                                        <button type="button" class="btn btn-soft-secondary btn-sm" data-period="day" data-title="Daily Registration Trend">
                                            Daily
                                        </button>
                                        <button type="button" class="btn btn-soft-secondary btn-sm" data-period="week" data-title="Weekly Registration Trend">
                                            Weekly
                                        </button>
                                        <button type="button" class="btn btn-soft-secondary btn-sm" data-period="month" data-title="Monthly Registration Trend">
                                            Monthly
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="p-3 chart-container">
                                        <div id="registration-chart" class="apex-charts" dir="ltr" style="height: 360px;"></div>
                                        <div id="chart-loading-overlay" class="chart-loading-overlay">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Gender Distribution</h4>
                                    <div class="flex-shrink-0">
                                        <a href="<?= site_url('dashboard/gender-analytics') ?>" class="btn btn-soft-primary btn-sm me-1">
                                            Analytics
                                        </a>
                                        <button type="button" class="btn btn-soft-info btn-sm view-all-btn" data-chart-type="gender">
                                            View All
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="gender-chart" class="apex-charts" style="height: 360px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Age, Nationality and Knowledge Sources -->
                    <div class="row">
                        <div class="col-xl-4 col-md-6">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Age Distribution</h4>
                                    <div class="flex-shrink-0">
                                        <a href="<?= site_url('dashboard/age-analytics') ?>" class="btn btn-soft-primary btn-sm me-1">
                                            Analytics
                                        </a>
                                        <button type="button" class="btn btn-soft-info btn-sm view-all-btn" data-chart-type="age">
                                            View All
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="age-chart" class="apex-charts" style="height: 350px;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Top Nationalities</h4>
                                    <div class="flex-shrink-0">
                                        <a href="<?= site_url('dashboard/nationality-analytics') ?>" class="btn btn-soft-primary btn-sm me-1">
                                            Analytics
                                        </a>
                                        <button type="button" class="btn btn-soft-info btn-sm view-all-btn" data-chart-type="nationality">
                                            View All
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="nationality-chart" class="apex-charts" style="height: 350px;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Top Knowledge Sources</h4>
                                    <div class="flex-shrink-0">
                                        <a href="<?= site_url('dashboard/knowledge-source') ?>" class="btn btn-soft-primary btn-sm me-1">
                                            Analytics
                                        </a>
                                        <button type="button" class="btn btn-soft-info btn-sm view-all-btn" data-chart-type="knowledgeSource">
                                            View All
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="knowledge-source-chart" class="apex-charts" style="height: 350px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ambassador Performance -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Top Ambassadors</h4>
                                    <div class="flex-shrink-0">
                                        <a href="<?= site_url('dashboard/ambassadors-analytics') ?>" class="btn btn-soft-primary btn-sm me-1">
                                            Analytics
                                        </a>
                                        <a href="<?= site_url('ambassadors') ?>" class="btn btn-soft-info btn-sm">
                                            View All Ambassadors
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Ambassador</th>
                                                    <th class="text-end">Participants Referred</th>
                                                    <th class="text-end">Performance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ambassadorStats as $ambassador): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-xs me-2">
                                                                    <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                        <?= strtoupper(substr($ambassador->ambassador_name, 0, 1)) ?>
                                                                    </span>
                                                                </div>
                                                                <span><?= esc($ambassador->ambassador_name) ?></span>
                                                            </div>
                                                        </td>
                                                        <td class="text-end"><?= number_format($ambassador->total_referrals) ?></td>
                                                        <td class="text-end">
                                                            <?php
                                                            $percentage = 0;
                                                            if ($summary->total_participants > 0) {
                                                                $percentage = ($ambassador->total_referrals / $summary->total_participants) * 100;
                                                            }
                                                            ?>
                                                            <div class="progress" style="height: 8px;">
                                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percentage ?>%"></div>
                                                            </div>
                                                            <span class="small"><?= number_format($percentage, 1) ?>% of total</span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($ambassadorStats)): ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">No ambassador data available</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->
    </div>

    <!-- END layout-wrapper -->
    <?= $this->include('partials/vendor-scripts') ?>

    <!-- jQuery (ensure it's loaded) -->
    <script src="<?= base_url('assets/libs/jquery/jquery.min.js') ?>"></script>
    <script>
        // Ensure jQuery is properly loaded and not in conflict
        if (typeof jQuery !== 'undefined') {
            console.log('jQuery version:', jQuery.fn.jquery);

            // Save jQuery reference to ensure no conflicts
            window.$YBB = jQuery;

            // Check if DataTables is available
            if (typeof jQuery.fn.DataTable === 'undefined') {
                console.warn('DataTables not detected! Attempting to fix...');

                // Try to reload DataTables core
                var dtScript = document.createElement('script');
                dtScript.src = '<?= base_url('assets/libs/datatables.net/js/jquery.dataTables.min.js') ?>?v=' + new Date().getTime();
                dtScript.onload = function() {
                    console.log('DataTables core reloaded successfully');

                    // Reload buttons extension
                    var btnScript = document.createElement('script');
                    btnScript.src = '<?= base_url('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') ?>?v=' + new Date().getTime();
                    btnScript.onload = function() {
                        console.log('DataTables buttons reloaded successfully');
                        // Reload other extensions
                        var extensions = ['html5', 'print', 'colVis'];
                        for (var i = 0; i < extensions.length; i++) {
                            var extScript = document.createElement('script');
                            extScript.src = '<?= base_url('assets/libs/datatables.net-buttons/js/buttons.') ?>' + extensions[i] + '.min.js?v=' + new Date().getTime();
                            document.head.appendChild(extScript);
                        }
                    };
                    document.head.appendChild(btnScript);
                };
                document.head.appendChild(dtScript);
            }

            // Ensure jQuery is globally available
            window.$ = window.$YBB;
            window.jQuery = window.$YBB;

            console.log('jQuery configured for YBB dashboard');
        } else {
            console.error('jQuery not loaded properly!');

            // Critical failure - try to load jQuery
            var script = document.createElement('script');
            script.src = '<?= base_url('assets/libs/jquery/jquery.min.js') ?>?v=' + new Date().getTime();
            script.onload = function() {
                console.log('jQuery loaded dynamically');
                window.location.reload(); // Reload the page to initialize everything properly
            };
            document.head.appendChild(script);
        }
    </script>

    <!-- apexcharts -->
    <script src="<?= base_url('assets/libs/apexcharts/apexcharts.min.js') ?>"></script>

    <!-- DataTables js -->
    <script src="<?= base_url('assets/libs/datatables.net/js/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/datatables.net-buttons/js/buttons.print.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/jszip/jszip.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/pdfmake/build/pdfmake.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/pdfmake/build/vfs_fonts.js') ?>"></script>

    <!-- Verify DataTables loading -->
    <script>
        (function() {
            // Check if all required DataTables libraries are loaded
            console.group('DataTables Loading Check');

            var requiredLibraries = [{
                    name: 'jQuery',
                    check: function() {
                        return typeof jQuery !== 'undefined';
                    }
                },
                {
                    name: 'DataTables Core',
                    check: function() {
                        return typeof jQuery !== 'undefined' && typeof jQuery.fn.dataTable !== 'undefined';
                    }
                },
                {
                    name: 'DataTables Buttons',
                    check: function() {
                        return typeof jQuery !== 'undefined' && typeof jQuery.fn.dataTable !== 'undefined' && typeof jQuery.fn.dataTable.Buttons !== 'undefined';
                    }
                },
                {
                    name: 'JSZip',
                    check: function() {
                        return typeof JSZip !== 'undefined';
                    }
                },
                {
                    name: 'pdfmake',
                    check: function() {
                        return typeof pdfMake !== 'undefined';
                    }
                }
            ];

            var allLoaded = true;
            requiredLibraries.forEach(function(lib) {
                var isLoaded = lib.check();
                console.log(lib.name + ':', isLoaded ? 'Loaded' : 'NOT LOADED');
                if (!isLoaded) {
                    allLoaded = false;
                }
            });

            if (!allLoaded) {
                console.error('Some DataTables dependencies are missing!');
                // Fix DataTables if jQuery is available but DataTables isn't
                if (typeof jQuery !== 'undefined' && (typeof jQuery.fn.dataTable === 'undefined' || typeof jQuery.fn.DataTable === 'undefined')) {
                    console.warn('Attempting to reload DataTables');
                    var script = document.createElement('script');
                    script.src = '<?= base_url('assets/libs/datatables.net/js/jquery.dataTables.min.js') ?>?v=' + new Date().getTime();
                    script.onload = function() {
                        console.log('DataTables core reloaded');
                        // Now reload buttons
                        var buttonsScript = document.createElement('script');
                        buttonsScript.src = '<?= base_url('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') ?>?v=' + new Date().getTime();
                        document.head.appendChild(buttonsScript);
                    };
                    document.head.appendChild(script);
                }
            } else {
                console.log('All DataTables dependencies successfully loaded!');
            }

            console.groupEnd();
        })();
    </script> <!-- Custom CSS for chart loading overlay -->
    <style>
        .chart-loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .chart-loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Make buttons more visible */
        .dt-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }

        /* Fix for DataTables header alignment */
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 10px;
        }

        /* Improved DataTable styling for modals */
        .modal .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px;
            text-align: right;
        }

        .modal .dataTables_wrapper .dataTables_filter input {
            margin-left: 5px;
            padding: 5px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            width: 220px;
        }

        .modal .dataTables_wrapper .dataTables_length {
            margin-bottom: 15px;
        }

        .modal .dataTables_wrapper .dataTables_length select {
            padding: 5px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        /* Add more space between buttons and filter */
        .modal .dataTables_wrapper .dt-buttons {
            margin-right: 15px;
        }

        /* Ensure pagination is visible and properly styled */
        .modal .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 5px 10px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #556ee6;
            color: white !important;
            border-color: #556ee6;
        }
    </style>

    <!-- Dashboard charts js -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // For debugging
            console.log('Gender Data:', <?= $genderChartData ?>);
            console.log('Nationality Data:', <?= $nationalityChartData ?>);

            // Testing modal availability
            console.log('jQuery availability:', typeof jQuery !== 'undefined' ? 'Available' : 'Not available');

            // Force check DataTables availability
            if (typeof jQuery !== 'undefined') {
                console.log('DataTables Core available:', typeof jQuery.fn.dataTable !== 'undefined' ? 'YES' : 'NO');
                console.log('DataTables Shorthand available:', typeof jQuery.fn.DataTable !== 'undefined' ? 'YES' : 'NO');

                // Attempt to ensure DataTables is available
                if (typeof jQuery.fn.dataTable === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                    console.warn('DataTables not available. Attempting to reload.');
                    var script = document.createElement('script');
                    script.src = '<?= base_url('assets/libs/datatables.net/js/jquery.dataTables.min.js') ?>?v=' + new Date().getTime();
                    document.head.appendChild(script);
                }
            }

            // Add event listeners for View All buttons
            document.querySelectorAll('.view-all-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    var chartType = this.getAttribute('data-chart-type');
                    console.log('View All clicked for:', chartType);                    if (chartType === 'nationality') {
                        // Show the nationality modal with proper backdrop options
                        var modalElement = document.getElementById('nationalityModal');
                        var nationalityModal = new bootstrap.Modal(modalElement, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                        nationalityModal.show();

                        // Load or reload nationality data
                        window.loadNationalityData();
                        
                        // Ensure cleanup when modal is hidden
                        $(modalElement).on('hidden.bs.modal', function () {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                            $('body').css('padding-right', '');
                        });
                    }
                    // Other chart types can be handled similarly
                });
            });

            // Registration Chart        
            var registrationOptions = {
                chart: {
                    height: 360,
                    type: 'area',
                    toolbar: {
                        show: false,
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2,
                },
                series: [{
                    name: 'Registrations',
                    data: <?= $registrationChartData ?>.values
                }],
                xaxis: {
                    categories: <?= $registrationChartData ?>.labels,
                    title: {
                        text: 'Date'
                    },
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Number of Registrations'
                    },
                    forceNiceScale: true,
                    labels: {
                        formatter: function(val) {
                            return Math.floor(val) === val ? val : '';
                        }
                    }
                },
                colors: ['#1c84ee'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        inverseColors: false,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [20, 100, 100, 100]
                    },
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " participants"
                        }
                    }
                },
                noData: {
                    text: 'Loading data...',
                    align: 'center',
                    verticalAlign: 'middle',
                    style: {
                        color: "#6c757d",
                        fontSize: '16px',
                        fontFamily: "Poppins"
                    }
                }
            };
            var registrationChart = new ApexCharts(
                document.querySelector("#registration-chart"),
                registrationOptions
            );
            registrationChart.render();

            // Gender Chart
            var genderData = <?= $genderChartData ?>;
            var genderOptions = {
                chart: {
                    height: 370,
                    type: 'pie',
                },
                series: genderData.values,
                labels: genderData.labels,
                colors: ["#038edc", "#f7b84b", "#51d28c", "#f34e4e", "#564ab1"],
                legend: {
                    position: 'bottom'
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                noData: {
                    text: 'No gender data available',
                    align: 'center',
                    verticalAlign: 'middle',
                    offsetX: 0,
                    offsetY: 0,
                    style: {
                        color: "#6c757d",
                        fontSize: '16px',
                        fontFamily: "Poppins"
                    }
                }
            };
            var genderChart = new ApexCharts(
                document.querySelector("#gender-chart"),
                genderOptions
            );
            genderChart.render();

            // Age Chart
            var ageOptions = {
                chart: {
                    height: 350,
                    type: 'bar',
                    toolbar: {
                        show: false,
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                    }
                },
                dataLabels: {
                    enabled: false
                },
                series: [{
                    name: 'Participants',
                    data: <?= $ageChartData ?>.values
                }],
                xaxis: {
                    categories: <?= $ageChartData ?>.labels,
                    title: {
                        text: 'Number of Participants'
                    }
                },
                colors: ['#564ab1']
            };
            var ageChart = new ApexCharts(
                document.querySelector("#age-chart"),
                ageOptions
            );
            ageChart.render();

            // Nationality Chart
            var nationalityData = <?= $nationalityChartData ?>;
            var nationalityOptions = {
                series: [{
                    name: 'Participants',
                    data: nationalityData.values
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: false,
                        distributed: true
                    }
                },
                dataLabels: {
                    enabled: false
                },
                colors: ['#51d28c', '#f1734f', '#038edc', '#564ab1', '#f7b84b', '#51d28c', '#f34e4e'],
                xaxis: {
                    categories: nationalityData.labels,
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Number of Participants'
                    }
                },
                noData: {
                    text: 'No nationality data available',
                    align: 'center',
                    verticalAlign: 'middle',
                    offsetX: 0,
                    offsetY: 0,
                    style: {
                        color: "#6c757d",
                        fontSize: '16px',
                        fontFamily: "Poppins"
                    }
                }
            };
            var nationalityChart = new ApexCharts(
                document.querySelector("#nationality-chart"),
                nationalityOptions
            );
            nationalityChart.render();

            // Knowledge Source Chart
            var knowledgeSourceData = <?= $knowledgeSourceChartData ?>;
            var knowledgeSourceOptions = {
                chart: {
                    height: 350,
                    type: 'pie',
                },
                series: knowledgeSourceData.values,
                labels: knowledgeSourceData.labels,
                colors: ['#038edc', '#f7b84b', '#51d28c', '#f34e4e', '#564ab1', '#f1734f', '#45cb85'],
                legend: {
                    position: 'bottom'
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                noData: {
                    text: 'No knowledge source data available',
                    align: 'center',
                    verticalAlign: 'middle',
                    offsetX: 0,
                    offsetY: 0,
                    style: {
                        color: "#6c757d",
                        fontSize: '16px',
                        fontFamily: "Poppins"
                    }
                }
            };
            var knowledgeSourceChart = new ApexCharts(
                document.querySelector("#knowledge-source-chart"),
                knowledgeSourceOptions
            );
            knowledgeSourceChart.render();

            // Period filter buttons        
            document.querySelectorAll('button[data-period]').forEach(function(button) {
                button.addEventListener('click', function() {
                    var period = this.getAttribute('data-period');
                    var title = this.getAttribute('data-title');

                    // If already active, don't reload
                    if (this.classList.contains('active')) {
                        return;
                    }

                    // Update chart title
                    document.getElementById('registration-title').textContent = title;

                    // Set active class
                    document.querySelectorAll('button[data-period]').forEach(function(btn) {
                        btn.classList.remove('active', 'btn-secondary');
                        btn.classList.add('btn-soft-secondary');
                    });
                    this.classList.remove('btn-soft-secondary');
                    this.classList.add('active', 'btn-secondary'); // Show loading state
                    document.getElementById('registration-chart').classList.add('opacity-50');
                    document.getElementById('chart-loading-overlay').classList.add('active');

                    // Disable buttons during loading
                    document.querySelectorAll('button[data-period]').forEach(function(btn) {
                        btn.disabled = true;
                    });

                    // Fetch data for selected period
                    fetch('<?= site_url('dashboard/ajaxRegistrationStats') ?>?period=' + period)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Period data:', data);

                            if (!data.labels || !data.values || data.labels.length === 0) {
                                console.warn('No data returned for period:', period);
                                data = {
                                    labels: ['No data available'],
                                    values: [0]
                                };
                            }

                            // Update chart with new data
                            registrationChart.updateSeries([{
                                name: 'Registrations',
                                data: data.values
                            }]);

                            registrationChart.updateOptions({
                                xaxis: {
                                    categories: data.labels
                                }
                            });

                            // Remove loading state
                            document.getElementById('registration-chart').classList.remove('opacity-50');
                            document.getElementById('chart-loading-overlay').classList.remove('active');

                            // Enable buttons again
                            document.querySelectorAll('button[data-period]').forEach(function(btn) {
                                btn.disabled = false;
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching registration data:', error);
                            // Show error message and re-enable buttons
                            document.getElementById('registration-chart').classList.remove('opacity-50');
                            document.getElementById('chart-loading-overlay').classList.remove('active');
                            document.querySelectorAll('button[data-period]').forEach(function(btn) {
                                btn.disabled = false;
                            });

                            // Display error using Swal if available, otherwise alert
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Data Load Error',
                                    text: 'Failed to load registration statistics',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                alert('Failed to load registration statistics');
                            }
                        });
                });
            }); // Set first button as active
            var defaultButton = document.querySelector('button[data-period="day"]');
            defaultButton.classList.add('active', 'btn-secondary');
            defaultButton.classList.remove('btn-soft-secondary');
            document.getElementById('registration-title').textContent = defaultButton.getAttribute('data-title'); // Just initialize the dataTable selectors without actual initialization
            // Tables will be properly initialized when data is loaded
            window.genderTable = null;
            window.ageTable = null;
            window.nationalityTable = null;
            // Pre-configure the tables with empty data to ensure they're visible
            // We'll reinitialize them with real data in the load functions
            // Make sure the functions are correctly defined in window scope
            console.log("Initializing data tables and global functions"); // Gender data loading function
            window.loadGenderData = function() {
                // Show loading indicator
                document.getElementById('gender-loading').classList.add('active');
                console.log('Loading gender data...');

                // Fetch fresh gender data from server
                fetch('<?= site_url('dashboard/ajaxGenderStats') ?>')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Gender data received:', data);

                        // Verify data structure
                        if (!data || !data.labels || !data.values) {
                            console.error('Invalid gender data structure:', data);
                            showError('Invalid data received from server');
                            document.getElementById('gender-loading').classList.remove('active');
                            return;
                        }

                        // Calculate total for percentages
                        var total = data.values.reduce((a, b) => a + b, 0);

                        // Clear existing table body directly
                        $('#gender-datatable tbody').empty();

                        // Manually add rows to the table body
                        var tableBody = '';
                        for (var i = 0; i < data.labels.length; i++) {
                            var percentage = ((data.values[i] / total) * 100).toFixed(1) + '%';
                            tableBody += '<tr>' +
                                '<td>' + data.labels[i] + '</td>' +
                                '<td>' + data.values[i].toLocaleString() + '</td>' +
                                '<td>' + percentage + '</td>' +
                                '</tr>';
                        }
                        $('#gender-datatable tbody').html(tableBody); // Initialize or redraw the table
                        try {
                            console.log('Initializing gender DataTable');
                            // First check if table exists and if DataTables is available
                            if (!$.fn.dataTable || !$.fn.DataTable) {
                                throw new Error('DataTables plugin not available');
                            }

                            // Safe destroy if already initialized
                            if ($.fn.DataTable.isDataTable('#gender-datatable')) {
                                console.log('Destroying existing gender DataTable');
                                $('#gender-datatable').DataTable().destroy();
                            }

                            console.log('Creating new gender DataTable');
                            window.genderTable = $('#gender-datatable').DataTable({
                                dom: 'Bfrtip',
                                buttons: [{
                                        extend: 'copy',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'csv',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'excel',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'pdf',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'print',
                                        className: 'btn btn-sm btn-light'
                                    }
                                ],
                                lengthChange: false,
                                pageLength: 10,
                                drawCallback: function() {
                                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                                }
                            });
                            console.log('Gender DataTable successfully initialized');
                        } catch (err) {
                            console.error('Failed to initialize gender DataTable:', err);
                            alert('Error initializing data table: ' + err.message);
                        }
                        console.log('Gender table initialized or updated');

                        // Remove loading state
                        document.getElementById('gender-loading').classList.remove('active');
                    })
                    .catch(error => {
                        console.error('Error fetching gender data:', error);
                        document.getElementById('gender-loading').classList.remove('active');
                        showError('Failed to load gender statistics');
                    });
            }; // Age data loading function
            window.loadAgeData = function() {
                // Show loading indicator
                document.getElementById('age-loading').classList.add('active');
                console.log('Loading age data...');

                // Fetch fresh age data from server
                fetch('<?= site_url('dashboard/ajaxAgeStats') ?>')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Age data received:', data);

                        // Verify data structure
                        if (!data || !data.labels || !data.values) {
                            console.error('Invalid age data structure:', data);
                            showError('Invalid data received from server');
                            document.getElementById('age-loading').classList.remove('active');
                            return;
                        }

                        // Calculate total for percentages
                        var total = data.values.reduce((a, b) => a + b, 0);

                        // Clear existing table body directly
                        $('#age-datatable tbody').empty();

                        // Manually add rows to the table body
                        var tableBody = '';
                        for (var i = 0; i < data.labels.length; i++) {
                            var percentage = ((data.values[i] / total) * 100).toFixed(1) + '%';
                            tableBody += '<tr>' +
                                '<td>' + data.labels[i] + '</td>' +
                                '<td>' + data.values[i].toLocaleString() + '</td>' +
                                '<td>' + percentage + '</td>' +
                                '</tr>';
                        }
                        $('#age-datatable tbody').html(tableBody); // Initialize or redraw the table
                        try {
                            console.log('Initializing age DataTable');
                            // First check if table exists and if DataTables is available
                            if (!$.fn.dataTable || !$.fn.DataTable) {
                                throw new Error('DataTables plugin not available');
                            }

                            // Safe destroy if already initialized
                            if ($.fn.DataTable.isDataTable('#age-datatable')) {
                                console.log('Destroying existing age DataTable');
                                $('#age-datatable').DataTable().destroy();
                            }

                            console.log('Creating new age DataTable');
                            window.ageTable = $('#age-datatable').DataTable({
                                dom: 'Bfrtip',
                                buttons: [{
                                        extend: 'copy',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'csv',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'excel',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'pdf',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'print',
                                        className: 'btn btn-sm btn-light'
                                    }
                                ],
                                lengthChange: false,
                                pageLength: 10,
                                drawCallback: function() {
                                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                                }
                            });
                            console.log('Age DataTable successfully initialized');
                        } catch (err) {
                            console.error('Failed to initialize age DataTable:', err);
                            alert('Error initializing data table: ' + err.message);
                        }
                        console.log('Age table initialized or updated');

                        // Remove loading state
                        document.getElementById('age-loading').classList.remove('active');
                    })
                    .catch(error => {
                        console.error('Error fetching age data:', error);
                        document.getElementById('age-loading').classList.remove('active');
                        showError('Failed to load age statistics');
                    });
            }; // Nationality data loading function            
            window.loadNationalityData = function() {
                // Show loading indicator
                document.getElementById('nationality-loading').classList.add('active');
                console.log('Loading nationality data...');

                // Fetch fresh nationality data from server
                // Use fullData=1 to get ALL nationalities, not just the top ones
                fetch('<?= site_url('dashboard/ajaxNationalityStats') ?>?fullData=1')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Nationality data received:', data);

                        // Verify data structure
                        if (!data || !data.labels || !data.values) {
                            console.error('Invalid nationality data structure:', data);
                            showError('Invalid data received from server');
                            document.getElementById('nationality-loading').classList.remove('active');
                            return;
                        }

                        // Calculate total for percentages
                        var total = data.values.reduce((a, b) => a + b, 0);

                        // Clear existing table body directly
                        $('#nationality-datatable tbody').empty();

                        // Manually add rows to the table body
                        var tableBody = '';
                        for (var i = 0; i < data.labels.length; i++) {
                            var percentage = ((data.values[i] / total) * 100).toFixed(1) + '%';
                            tableBody += '<tr>' +
                                '<td>' + data.labels[i] + '</td>' +
                                '<td>' + data.values[i].toLocaleString() + '</td>' +
                                '<td>' + percentage + '</td>' +
                                '</tr>';
                        }
                        $('#nationality-datatable tbody').html(tableBody); // Initialize or redraw the table
                        try {
                            console.log('Initializing nationality DataTable');
                            // First check if table exists and if DataTables is available
                            if (!$.fn.dataTable || !$.fn.DataTable) {
                                throw new Error('DataTables plugin not available');
                            }

                            // Safe destroy if already initialized
                            if ($.fn.DataTable.isDataTable('#nationality-datatable')) {
                                console.log('Destroying existing nationality DataTable');
                                $('#nationality-datatable').DataTable().destroy();
                            }
                            console.log('Creating new nationality DataTable');
                            window.nationalityTable = $('#nationality-datatable').DataTable({
                                dom: 'Blfrtip', // Added 'l' for length changing input and 'f' for filtering input
                                buttons: [{
                                        extend: 'copy',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'csv',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'excel',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'pdf',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'print',
                                        className: 'btn btn-sm btn-light'
                                    }
                                ],
                                lengthChange: true, // Enable length changing
                                lengthMenu: [
                                    [10, 25, 50, -1],
                                    [10, 25, 50, "All"]
                                ], // Add length options
                                pageLength: 10,
                                searching: true, // Enable search
                                ordering: true, // Enable sorting
                                language: {
                                    search: "Search nationalities:",
                                    lengthMenu: "Show _MENU_ nationalities"
                                },
                                lengthChange: true, // Allow changing the number of records shown
                                pageLength: 10,
                                searching: true, // Enable search
                                ordering: true, // Enable column sorting
                                drawCallback: function() {
                                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                                }
                            });
                            console.log('Nationality DataTable successfully initialized');
                        } catch (err) {
                            console.error('Failed to initialize nationality DataTable:', err);
                            alert('Error initializing data table: ' + err.message);
                        }
                        console.log('Nationality table initialized or updated');

                        // Remove loading state
                        document.getElementById('nationality-loading').classList.remove('active');
                    })
                    .catch(error => {
                        console.error('Error fetching nationality data:', error);
                        document.getElementById('nationality-loading').classList.remove('active');
                        showError('Failed to load nationality statistics');
                    });
            };

            // Helper function to show errors
            function showError(message) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Load Error',
                        text: message,
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(message);
                }
            }

            // Debug helper function
            function debugDataTableState(tableId, message) {
                console.group('DataTable Debug: ' + message);
                console.log('Table Element Exists:', document.getElementById(tableId) !== null);
                console.log('Table jQuery Object:', $('#' + tableId).length);
                console.log('Table HTML:', $('#' + tableId).parent().html());
                console.log('DataTables Initialized:', $.fn.dataTable.isDataTable('#' + tableId));
                console.groupEnd();
            }

            // Check if tables are initialized on page load
            debugDataTableState('nationality-datatable', 'Initial State');
        });
    </script>

    <!-- App js -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script> <!-- Modals for chart details -->

    <!-- Modal initialization script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Define data loading functions early to ensure availability
            window.loadGenderData = function() {
                console.log('Loading gender data...');
                var loadingElement = document.getElementById('gender-loading');
                if (loadingElement) loadingElement.classList.add('active');

                fetch('<?= site_url('dashboard/ajaxGenderStats') ?>')
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(function(data) {
                        console.log('Gender data received:', data);

                        if (!data || !data.labels || !data.values) {
                            throw new Error('Invalid data structure received from server');
                        }

                        // Calculate total for percentages
                        var total = data.values.reduce(function(a, b) {
                            return a + b;
                        }, 0);

                        // Clear existing table data
                        var tableBody = document.querySelector('#gender-datatable tbody');
                        if (!tableBody) {
                            throw new Error('Table body element not found');
                        }

                        // Create new table content
                        var html = '';
                        for (var i = 0; i < data.labels.length; i++) {
                            var percentage = total > 0 ? ((data.values[i] / total) * 100).toFixed(1) + '%' : '0%';
                            html += '<tr>' +
                                '<td>' + data.labels[i] + '</td>' +
                                '<td>' + data.values[i] + '</td>' +
                                '<td>' + percentage + '</td>' +
                                '</tr>';
                        }
                        tableBody.innerHTML = html;

                        // Initialize DataTable with a safety check
                        setTimeout(function() {
                            try {
                                if (typeof jQuery === 'undefined') {
                                    throw new Error('jQuery not available');
                                }

                                if (typeof jQuery.fn.DataTable === 'undefined') {
                                    throw new Error('DataTables plugin not available');
                                }

                                // Destroy existing table if already initialized
                                if (jQuery.fn.DataTable.isDataTable('#gender-datatable')) {
                                    jQuery('#gender-datatable').DataTable().destroy();
                                }

                                // Initialize new DataTable
                                window.genderTable = jQuery('#gender-datatable').DataTable({
                                    dom: 'Bfrtip',
                                    buttons: [{
                                            extend: 'copy',
                                            className: 'btn btn-sm btn-light'
                                        },
                                        {
                                            extend: 'csv',
                                            className: 'btn btn-sm btn-light'
                                        },
                                        {
                                            extend: 'excel',
                                            className: 'btn btn-sm btn-light'
                                        },
                                        {
                                            extend: 'pdf',
                                            className: 'btn btn-sm btn-light'
                                        },
                                        {
                                            extend: 'print',
                                            className: 'btn btn-sm btn-light'
                                        }
                                    ],
                                    lengthChange: false,
                                    pageLength: 10
                                });

                                console.log('Gender DataTable initialized successfully');
                            } catch (err) {
                                console.error('Failed to initialize gender DataTable:', err);
                                // Fallback to basic table if DataTables fails
                                console.log('Using fallback table display without DataTables');
                            } finally {
                                // Hide loading indicator in all cases
                                if (loadingElement) loadingElement.classList.remove('active');
                            }
                        }, 300); // Short delay to ensure DOM is ready
                    })
                    .catch(function(error) {
                        console.error('Error loading gender data:', error);
                        alert('Failed to load gender data: ' + error.message);
                        if (loadingElement) loadingElement.classList.remove('active');
                    });
            };

            window.loadAgeData = function() {
                console.log('Loading age data...');
                var loadingElement = document.getElementById('age-loading');
                if (loadingElement) loadingElement.classList.add('active');

                fetch('<?= site_url('dashboard/ajaxAgeStats') ?>')
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(function(data) {
                        console.log('Age data received:', data);

                        if (!data || !data.labels || !data.values) {
                            throw new Error('Invalid data structure received from server');
                        }

                        // Calculate total for percentages
                        var total = data.values.reduce(function(a, b) {
                            return a + b;
                        }, 0);

                        // Clear existing table data
                        var tableBody = document.querySelector('#age-datatable tbody');
                        if (!tableBody) {
                            throw new Error('Table body element not found');
                        }

                        // Create new table content
                        var html = '';
                        for (var i = 0; i < data.labels.length; i++) {
                            var percentage = total > 0 ? ((data.values[i] / total) * 100).toFixed(1) + '%' : '0%';
                            html += '<tr>' +
                                '<td>' + data.labels[i] + '</td>' +
                                '<td>' + data.values[i] + '</td>' +
                                '<td>' + percentage + '</td>' +
                                '</tr>';
                        }
                        tableBody.innerHTML = html;

                        // Initialize DataTable with a safety check
                        setTimeout(function() {
                            try {
                                if (typeof jQuery === 'undefined') {
                                    throw new Error('jQuery not available');
                                }

                                if (typeof jQuery.fn.DataTable === 'undefined') {
                                    throw new Error('DataTables plugin not available');
                                }

                                // Destroy existing table if already initialized
                                if (jQuery.fn.DataTable.isDataTable('#age-datatable')) {
                                    jQuery('#age-datatable').DataTable().destroy();
                                }

                                // Initialize new DataTable
                                window.ageTable = jQuery('#age-datatable').DataTable({
                                    dom: 'Bfrtip',
                                    buttons: [{
                                            extend: 'copy',
                                            className: 'btn btn-sm btn-light'
                                        },
                                        {
                                            extend: 'csv',
                                            className: 'btn btn-sm btn-light'
                                        },
                                        {
                                            extend: 'excel',
                                            className: 'btn btn-sm btn-light'
                                        },
                                        {
                                            extend: 'pdf',
                                            className: 'btn btn-sm btn-light'
                                        },
                                        {
                                            extend: 'print',
                                            className: 'btn btn-sm btn-light'
                                        }
                                    ],
                                    lengthChange: false,
                                    pageLength: 10
                                });

                                console.log('Age DataTable initialized successfully');
                            } catch (err) {
                                console.error('Failed to initialize age DataTable:', err);
                                // Fallback to basic table if DataTables fails
                                console.log('Using fallback table display without DataTables');
                            } finally {
                                // Hide loading indicator in all cases
                                if (loadingElement) loadingElement.classList.remove('active');
                            }
                        }, 300); // Short delay to ensure DOM is ready
                    })
                    .catch(function(error) {
                        console.error('Error loading age data:', error);
                        alert('Failed to load age data: ' + error.message);
                        if (loadingElement) loadingElement.classList.remove('active');
                    });
            };
            window.loadNationalityData = function() {
                console.log('Loading nationality data...');
                var loadingElement = document.getElementById('nationality-loading');
                if (loadingElement) loadingElement.classList.add('active');

                fetch('<?= site_url('dashboard/ajaxNationalityStats') ?>?fullData=1')
                    .then(function(response) {
                        if (response.ok) {
                            return response.json();
                        } else {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                    })
                    .then(function(data) {
                        console.log('Nationality data received:', data);

                        if (!data || !data.labels || !data.values) {
                            throw new Error('Invalid data structure received from server');
                        }

                        // Calculate total for percentages
                        var total = data.values.reduce(function(a, b) {
                            return a + b;
                        }, 0);

                        // Clear existing table data
                        var tableBody = document.querySelector('#nationality-datatable tbody');
                        if (!tableBody) {
                            throw new Error('Table body element not found');
                        }

                        // Create new table content
                        var html = '';
                        for (var i = 0; i < data.labels.length; i++) {
                            var percentage = total > 0 ? ((data.values[i] / total) * 100).toFixed(1) + '%' : '0%';
                            html += '<tr>' +
                                '<td>' + data.labels[i] + '</td>' +
                                '<td>' + data.values[i] + '</td>' +
                                '<td>' + percentage + '</td>' +
                                '</tr>';
                        }
                        tableBody.innerHTML = html;

                        // Initialize DataTable with a safety check
                        setTimeout(function() {
                            try {
                                if (typeof jQuery === 'undefined') {
                                    throw new Error('jQuery not available');
                                }

                                if (typeof jQuery.fn.DataTable === 'undefined') {
                                    throw new Error('DataTables plugin not available');
                                }

                                // Destroy existing table if already initialized
                                if (jQuery.fn.DataTable.isDataTable('#nationality-datatable')) {
                                    jQuery('#nationality-datatable').DataTable().destroy();
                                }

                                // Initialize new DataTable                                
                                window.nationalityTable = jQuery('#nationality-datatable').DataTable({
                                    dom: 'Blfrtip', // Added length menu and filter input
                                    buttons: [{
                                            extend: 'copy',
                                            className: 'btn btn-sm btn-light'
                                        },
                                        {
                                            extend: 'csv',
                                            className: 'btn btn-sm btn-light'
                                        },
                                        {
                                            extend: 'excel',
                                            className: 'btn btn-sm btn-light'
                                        },
                                        {
                                            extend: 'pdf',
                                            className: 'btn btn-sm btn-light'
                                        },
                                        {
                                            extend: 'print',
                                            className: 'btn btn-sm btn-light'
                                        }
                                    ],
                                    lengthChange: true, // Enable length changing
                                    lengthMenu: [
                                        [10, 25, 50, -1],
                                        [10, 25, 50, "All"]
                                    ], // Add length options
                                    pageLength: 10,
                                    searching: true, // Enable search box
                                    ordering: true, // Enable column sorting
                                    language: {
                                        search: "Search nationalities:",
                                        lengthMenu: "Show _MENU_ nationalities"
                                    }
                                });

                                console.log('Nationality DataTable initialized successfully');
                            } catch (err) {
                                console.error('Failed to initialize nationality DataTable:', err);
                                // Fallback to basic table if DataTables fails
                                console.log('Using fallback table display without DataTables');
                            } finally {
                                // Hide loading indicator in all cases
                                if (loadingElement) loadingElement.classList.remove('active');
                            }
                        }, 300); // Short delay to ensure DOM is ready
                    })
                    .catch(function(error) {
                        console.error('Error loading nationality data:', error);
                        alert('Failed to load nationality data: ' + error.message);
                        if (loadingElement) loadingElement.classList.remove('active');
                    });
            };

            window.loadKnowledgeSourceData = function() {
                console.log('Loading knowledge source data...');
                var loadingElement = document.getElementById('knowledge-source-loading');
                if (loadingElement) loadingElement.classList.add('active');

                fetch('<?= site_url('dashboard/ajaxKnowledgeSourceStats') ?>?fullData=1')
                    .then(function(response) {
                        if (response.ok) {
                            return response.json();
                        } else {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                    })
                    .then(function(data) {
                        console.log('Knowledge source data received:', data);

                        if (!data || !data.labels || !data.values) {
                            throw new Error('Invalid data structure received from server');
                        }

                        // Calculate total for percentages
                        var total = data.values.reduce(function(a, b) { return a + b; }, 0);

                        // Clear existing table data
                        var tableBody = document.querySelector('#knowledge-source-datatable tbody');
                        if (!tableBody) {
                            throw new Error('Table body element not found');
                        }

                        // Create new table content
                        var html = '';
                        for (var i = 0; i < data.labels.length; i++) {
                            var percentage = total > 0 ? ((data.values[i] / total) * 100).toFixed(1) + '%' : '0%';
                            html += '<tr>' +
                                '<td>' + data.labels[i] + '</td>' +
                                '<td>' + data.values[i] + '</td>' +
                                '<td>' + percentage + '</td>' +
                                '</tr>';
                        }
                        tableBody.innerHTML = html;

                        // Initialize DataTable with a safety check
                        setTimeout(function() {
                            try {
                                if (typeof jQuery === 'undefined') {
                                    throw new Error('jQuery not available');
                                }
                                if (typeof jQuery.fn.DataTable === 'undefined') {
                                    throw new Error('DataTables plugin not available');
                                }

                                // Destroy existing table if already initialized
                                if (jQuery.fn.DataTable.isDataTable('#knowledge-source-datatable')) {
                                    jQuery('#knowledge-source-datatable').DataTable().destroy();
                                }

                                window.knowledgeSourceTable = jQuery('#knowledge-source-datatable').DataTable({
                                    dom: 'Blfrtip',
                                    buttons: [
                                        { extend: 'copy', className: 'btn btn-sm btn-light' },
                                        { extend: 'csv', className: 'btn btn-sm btn-light' },
                                        { extend: 'excel', className: 'btn btn-sm btn-light' },
                                        { extend: 'pdf', className: 'btn btn-sm btn-light' },
                                        { extend: 'print', className: 'btn btn-sm btn-light' }
                                    ],
                                    lengthChange: true,
                                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                                    pageLength: 10,
                                    searching: true,
                                    ordering: true,
                                    language: {
                                        search: 'Search sources:',
                                        lengthMenu: 'Show _MENU_ sources'
                                    }
                                });

                                console.log('Knowledge Source DataTable initialized successfully');
                            } catch (err) {
                                console.error('Failed to initialize knowledge source DataTable:', err);
                                console.log('Using fallback table display without DataTables');
                            } finally {
                                if (loadingElement) loadingElement.classList.remove('active');
                            }
                        }, 300);
                    })
                    .catch(function(error) {
                        console.error('Error loading knowledge source data:', error);
                        alert('Failed to load knowledge source data: ' + error.message);
                        if (loadingElement) loadingElement.classList.remove('active');
                    });
            };

            // Initialize modal triggers using vanilla JS
            document.querySelectorAll('.view-all-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    var chartType = this.getAttribute('data-chart-type');
                    console.log('View All button clicked for', chartType);

                    try {
                        if (chartType === 'gender') {
                            console.log('Opening gender modal');
                            var modalElement = document.getElementById('genderModal');
                            console.log('Modal element:', modalElement);

                            if (!modalElement) {
                                throw new Error('Gender modal element not found');
                            }

                            // Create a new modal instance to ensure it's fresh
                            var modal = new bootstrap.Modal(modalElement);
                            modal.show();

                            // Load data once modal is shown using the event
                            modalElement.addEventListener('shown.bs.modal', function onceShown() {
                                console.log('Gender modal shown, loading data');
                                window.loadGenderData();
                                // Remove event listener to prevent multiple calls
                                modalElement.removeEventListener('shown.bs.modal', onceShown);
                            }, {
                                once: true
                            });

                        } else if (chartType === 'age') {
                            console.log('Opening age modal');
                            var modalElement = document.getElementById('ageModal');
                            console.log('Modal element:', modalElement);

                            if (!modalElement) {
                                throw new Error('Age modal element not found');
                            }

                            // Create a new modal instance to ensure it's fresh
                            var modal = new bootstrap.Modal(modalElement);
                            modal.show();

                            // Load data once modal is shown using the event
                            modalElement.addEventListener('shown.bs.modal', function onceShown() {
                                console.log('Age modal shown, loading data');
                                window.loadAgeData();
                                // Remove event listener to prevent multiple calls
                                modalElement.removeEventListener('shown.bs.modal', onceShown);
                            }, {
                                once: true
                            });

                        } else if (chartType === 'nationality') {
                            console.log('Opening nationality modal');
                            var modalElement = document.getElementById('nationalityModal');
                            console.log('Modal element:', modalElement);

                            if (!modalElement) {
                                throw new Error('Nationality modal element not found');
                            }

                            // Create a new modal instance to ensure it's fresh
                            var modal = new bootstrap.Modal(modalElement);
                            modal.show();

                            // Load data once modal is shown using the event
                            modalElement.addEventListener('shown.bs.modal', function onceShown() {
                                console.log('Nationality modal shown, loading data');
                                window.loadNationalityData();
                                // Remove event listener to prevent multiple calls
                                modalElement.removeEventListener('shown.bs.modal', onceShown);
                            }, {
                                once: true
                            });

                        } else if (chartType === 'knowledgeSource') {
                            console.log('Opening knowledge source modal');
                            var modalElement = document.getElementById('knowledgeSourceModal');

                            if (!modalElement) {
                                throw new Error('Knowledge source modal element not found');
                            }

                            var modal = new bootstrap.Modal(modalElement);
                            modal.show();

                            modalElement.addEventListener('shown.bs.modal', function onceShown() {
                                console.log('Knowledge source modal shown, loading data');
                                window.loadKnowledgeSourceData();
                                modalElement.removeEventListener('shown.bs.modal', onceShown);
                            }, {
                                once: true
                            });
                        }
                    } catch (error) {
                        console.error('Error showing modal:', error);
                        alert('Error showing data: ' + error.message);
                    }
                });
            });

            // Log modal elements for debugging
            console.log('Gender modal element:', document.getElementById('genderModal'));
            console.log('Age modal element:', document.getElementById('ageModal'));
            console.log('Nationality modal element:', document.getElementById('nationalityModal'));

            // Check if DataTables are properly initialized
            console.log('Gender DataTable initialized:', genderTable instanceof $.fn.dataTable.Api);
            console.log('Age DataTable initialized:', ageTable instanceof $.fn.dataTable.Api);
            console.log('Nationality DataTable initialized:', nationalityTable instanceof $.fn.dataTable.Api);
        });
    </script>

    <!-- Chart Details Modals --><!-- Gender Distribution Modal -->
    <div class="modal fade" id="genderModal" tabindex="-1" aria-labelledby="genderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="genderModalLabel">Gender Distribution Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body position-relative">
                    <!-- Loading spinner for this modal -->
                    <div id="gender-loading" class="chart-loading-overlay">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="gender-datatable" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Gender</th>
                                    <th>Number of Participants</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="3" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> <!-- Age Distribution Modal -->
    <div class="modal fade" id="ageModal" tabindex="-1" aria-labelledby="ageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ageModalLabel">Age Distribution Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body position-relative">
                    <!-- Loading spinner for this modal -->
                    <div id="age-loading" class="chart-loading-overlay">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="age-datatable" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Age Range</th>
                                    <th>Number of Participants</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="3" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> <!-- Nationality Distribution Modal -->
    <div class="modal fade" id="nationalityModal" tabindex="-1" aria-labelledby="nationalityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nationalityModalLabel">All Nationality Distribution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body position-relative">
                    <!-- Loading spinner for this modal -->
                    <div id="nationality-loading" class="chart-loading-overlay">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="nationality-datatable" class="table table-bordered table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Nationality</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Knowledge Source Distribution Modal -->
    <div class="modal fade" id="knowledgeSourceModal" tabindex="-1" aria-labelledby="knowledgeSourceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="knowledgeSourceModalLabel">All Knowledge Source Distribution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body position-relative">
                    <!-- Loading spinner for this modal -->
                    <div id="knowledge-source-loading" class="chart-loading-overlay">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="knowledge-source-datatable" class="table table-bordered table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Source</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Final script fix for modals -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Global fix for modal backdrop issues
            $(document).on('hidden.bs.modal', '.modal', function () {
                console.log('Modal hidden event triggered (global handler)');
                
                // Check if any other modals are still open
                if ($('.modal.show').length === 0) {
                    // No other modals are open, remove all backdrops and modal classes
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('padding-right', '');
                    console.log('Modal backdrops and classes cleaned up (global handler)');
                }
            });
        });
    </script>
</body>

<!-- Final script fix to ensure window functions are available -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pre-initialize DataTables to ensure they're set up correctly
        // This step helps prevent "DataTable is not a function" errors
        function initializeDatatables() {
            console.log('Running fallback DataTables initialization');

            try {
                // Check if DataTables is available
                if (typeof $ === 'undefined' || typeof $.fn === 'undefined' || typeof $.fn.DataTable === 'undefined') {
                    console.error('DataTables not available in fallback initialization');

                    // Try to reload the DataTables library
                    var script = document.createElement('script');
                    script.src = '<?= base_url('assets/libs/datatables.net/js/jquery.dataTables.min.js') ?>?nocache=' + new Date().getTime();
                    script.onload = function() {
                        console.log('DataTables reloaded successfully');
                        setTimeout(initializeDatatables, 500); // Try again after script loads
                    };
                    document.head.appendChild(script);
                    return;
                }

                // Fix for modal backdrop issue - add event listeners to ensure backdrop is removed on modal close
                $('#nationalityModal').on('hidden.bs.modal', function () {
                    console.log('Nationality modal hidden event triggered');
                    
                    // Remove any leftover backdrop
                    $('.modal-backdrop').remove();
                    
                    // Ensure body doesn't retain modal classes
                    $('body').removeClass('modal-open');
                    $('body').css('padding-right', '');
                    
                    console.log('Modal backdrop and classes cleaned up');
                });
                
                // Do the same for other modals to ensure consistency
                $('#genderModal, #ageModal, #knowledgeSourceModal').on('hidden.bs.modal', function () {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('padding-right', '');
                });

                // Initialize gender table
                if (!$.fn.DataTable.isDataTable('#gender-datatable')) {
                    console.log('Initializing gender table from fallback');
                    window.genderTable = $('#gender-datatable').DataTable({
                        dom: 'Bfrtip',
                        buttons: [{
                                extend: 'copy',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'csv',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'excel',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'pdf',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'print',
                                className: 'btn btn-sm btn-light'
                            }
                        ],
                        lengthChange: false,
                        pageLength: 10
                    });
                    console.log('Gender DataTable initialized from fallback');
                }

                // Initialize age table
                if (!$.fn.DataTable.isDataTable('#age-datatable')) {
                    console.log('Initializing age table from fallback');
                    window.ageTable = $('#age-datatable').DataTable({
                        dom: 'Bfrtip',
                        buttons: [{
                                extend: 'copy',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'csv',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'excel',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'pdf',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'print',
                                className: 'btn btn-sm btn-light'
                            }
                        ],
                        lengthChange: false,
                        pageLength: 10
                    });
                    console.log('Age DataTable initialized from fallback');
                }

                // Initialize nationality table
                if (!$.fn.DataTable.isDataTable('#nationality-datatable')) {
                    console.log('Initializing nationality table from fallback');
                    window.nationalityTable = $('#nationality-datatable').DataTable({
                        dom: 'Blfrtip', // Added length menu and filter input
                        buttons: [{
                                extend: 'copy',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'csv',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'excel',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'pdf',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'print',
                                className: 'btn btn-sm btn-light'
                            }
                        ],
                        lengthChange: true, // Enable length changing
                        lengthMenu: [
                            [10, 25, 50, -1],
                            [10, 25, 50, "All"]
                        ], // Add length options
                        pageLength: 10,
                        searching: true, // Enable search
                        ordering: true // Enable sorting
                    });
                    console.log('Nationality DataTable initialized from fallback');
                }

                console.log('DataTables fallback initialization complete');
            } catch (error) {
                console.error('Error during DataTables fallback initialization:', error);
            }
        }

        // Call the initialization function with a slight delay to ensure all scripts are loaded
        setTimeout(initializeDatatables, 1000);
    });

    // Ensure the window functions are properly defined
    if (typeof window.loadGenderData !== 'function') {
        console.log('Redefining loadGenderData function');
        window.loadGenderData = function() {
            console.log('Gender data loading function called');
            document.getElementById('gender-loading').classList.add('active');

            fetch('<?= site_url('dashboard/ajaxGenderStats') ?>')
                .then(response => response.json())
                .then(data => {
                    console.log('Gender data received from fallback:', data);
                    var tableBody = '';
                    var total = data.values.reduce((a, b) => a + b, 0);

                    for (var i = 0; i < data.labels.length; i++) {
                        var percentage = ((data.values[i] / total) * 100).toFixed(1) + '%';
                        tableBody += '<tr><td>' + data.labels[i] + '</td><td>' + data.values[i] + '</td><td>' + percentage + '</td></tr>';
                    }

                    document.querySelector('#gender-datatable tbody').innerHTML = tableBody;

                    // Always destroy and recreate the table to ensure it works
                    if ($.fn.DataTable.isDataTable('#gender-datatable')) {
                        $('#gender-datatable').DataTable().destroy();
                    }

                    window.genderTable = $('#gender-datatable').DataTable({
                        dom: 'Bfrtip',
                        buttons: [{
                                extend: 'copy',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'csv',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'excel',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'pdf',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'print',
                                className: 'btn btn-sm btn-light'
                            }
                        ],
                        lengthChange: false,
                        pageLength: 10
                    });

                    document.getElementById('gender-loading').classList.remove('active');
                })
                .catch(error => {
                    console.error('Error in fallback gender function:', error);
                    document.getElementById('gender-loading').classList.remove('active');
                    alert('Failed to load gender data: ' + error.message);
                });
        }
    }

    if (typeof window.loadAgeData !== 'function') {
        console.log('Redefining loadAgeData function');
        window.loadAgeData = function() {
            console.log('Age data loading function called');
            document.getElementById('age-loading').classList.add('active');

            fetch('<?= site_url('dashboard/ajaxAgeStats') ?>')
                .then(response => response.json())
                .then(data => {
                    console.log('Age data received from fallback:', data);
                    var tableBody = '';
                    var total = data.values.reduce((a, b) => a + b, 0);

                    for (var i = 0; i < data.labels.length; i++) {
                        var percentage = ((data.values[i] / total) * 100).toFixed(1) + '%';
                        tableBody += '<tr><td>' + data.labels[i] + '</td><td>' + data.values[i] + '</td><td>' + percentage + '</td></tr>';
                    }

                    document.querySelector('#age-datatable tbody').innerHTML = tableBody;

                    // Always destroy and recreate the table to ensure it works
                    if ($.fn.DataTable.isDataTable('#age-datatable')) {
                        $('#age-datatable').DataTable().destroy();
                    }

                    window.ageTable = $('#age-datatable').DataTable({
                        dom: 'Bfrtip',
                        buttons: [{
                                extend: 'copy',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'csv',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'excel',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'pdf',
                                className: 'btn btn-sm btn-light'
                            },
                            {
                                extend: 'print',
                                className: 'btn btn-sm btn-light'
                            }
                        ],
                        lengthChange: false,
                        pageLength: 10
                    });

                    document.getElementById('age-loading').classList.remove('active');
                })
                .catch(error => {
                    console.error('Error in fallback age function:', error);
                    document.getElementById('age-loading').classList.remove('active');
                    alert('Failed to load age data: ' + error.message);
                });
        }
    }
    if (typeof window.loadNationalityData !== 'function') {
        console.log('Redefining loadNationalityData function');
        window.loadNationalityData = function() {
            console.log('Nationality data loading function called');
            document.getElementById('nationality-loading').classList.add('active');

            fetch('<?= site_url('dashboard/ajaxNationalityStats') ?>?fullData=1')
                .then(function(response) {
                    if (response.ok) {
                        return response.json();
                    } else {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                })
                .then(function(data) {
                    console.log('Nationality data received:', data);

                    if (!data || !data.labels || !data.values) {
                        throw new Error('Invalid data structure received from server');
                    }

                    // Calculate total for percentages
                    var total = data.values.reduce(function(a, b) {
                        return a + b;
                    }, 0);

                    // Clear existing table data
                    var tableBody = document.querySelector('#nationality-datatable tbody');
                    if (!tableBody) {
                        throw new Error('Table body element not found');
                    }

                    // Create new table content
                    var html = '';
                    for (var i = 0; i < data.labels.length; i++) {
                        var percentage = total > 0 ? ((data.values[i] / total) * 100).toFixed(1) + '%' : '0%';
                        html += '<tr>' +
                            '<td>' + data.labels[i] + '</td>' +
                            '<td>' + data.values[i] + '</td>' +
                            '<td>' + percentage + '</td>' +
                            '</tr>';
                    }
                    tableBody.innerHTML = html;

                    // Initialize DataTable with a safety check
                    setTimeout(function() {
                        try {
                            if (typeof jQuery === 'undefined') {
                                throw new Error('jQuery not available');
                            }

                            if (typeof jQuery.fn.DataTable === 'undefined') {
                                throw new Error('DataTables plugin not available');
                            }

                            // Destroy existing table if already initialized
                            if (jQuery.fn.DataTable.isDataTable('#nationality-datatable')) {
                                jQuery('#nationality-datatable').DataTable().destroy();
                            }

                            // Initialize new DataTable                                
                            window.nationalityTable = jQuery('#nationality-datatable').DataTable({
                                dom: 'Blfrtip', // Added length menu and filter input
                                buttons: [{
                                        extend: 'copy',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'csv',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'excel',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'pdf',
                                        className: 'btn btn-sm btn-light'
                                    },
                                    {
                                        extend: 'print',
                                        className: 'btn btn-sm btn-light'
                                    }
                                ],
                                lengthChange: true, // Enable length changing
                                lengthMenu: [
                                    [10, 25, 50, -1],
                                    [10, 25, 50, "All"]
                                ], // Add length options
                                pageLength: 10,
                                searching: true, // Enable search box
                                ordering: true, // Enable column sorting
                                language: {
                                    search: "Search nationalities:",
                                    lengthMenu: "Show _MENU_ nationalities"
                                }
                            });

                            document.getElementById('nationality-loading').classList.remove('active');
                            console.log('Nationality DataTable initialized successfully');
                        } catch (err) {
                            console.error('Failed to initialize nationality DataTable:', err);
                            // Fallback to basic table if DataTables fails
                            console.log('Using fallback table display without DataTables');
                        } finally {
                            // Hide loading indicator in all cases
                            if (loadingElement) loadingElement.classList.remove('active');
                        }
                    }, 300); // Short delay to ensure DOM is ready
                })
                .catch(function(error) {
                    console.error('Error loading nationality data:', error);
                    alert('Failed to load nationality data: ' + error.message);
                    if (loadingElement) loadingElement.classList.remove('active');
                });
        }

        // Console log to verify functions are available
        console.log('Gender function available:', typeof window.loadGenderData === 'function');
        console.log('Age function available:', typeof window.loadAgeData === 'function');
        console.log('Nationality function available:', typeof window.loadNationalityData === 'function');

    }
</script>
</body>