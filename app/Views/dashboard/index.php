<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Dashboard')); ?>

    <!-- jsvectormap css -->
    <link href="<?= base_url('assets/libs/jsvectormap/jsvectormap.min.css') ?>" rel="stylesheet" type="text/css" />

    <!-- apexcharts -->
    <link href="<?= base_url('assets/libs/apexcharts/apexcharts.min.css') ?>" rel="stylesheet" type="text/css" />

    <!-- DataTables -->
    <link href="<?= base_url('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') ?>" rel="stylesheet" type="text/css" />

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
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            visibility: hidden;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .chart-loading-overlay.active {
            visibility: visible;
            opacity: 1;
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
                            <div class="card">
                                <div class="card-header border-0 align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">
                                        <span id="registration-title">Daily Registration Trend</span>
                                    </h4>
                                    <div>
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
                                        <button type="button" class="btn btn-soft-info btn-sm view-all-btn" data-chart-type="gender">
                                            View All
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="gender-chart" class="apex-charts" style="height: 400px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Age and Nationality -->
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Age Distribution</h4>
                                    <div class="flex-shrink-0">
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

                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Top Nationalities</h4>
                                    <div class="flex-shrink-0">
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
                    </div>

                    <!-- Ambassador Performance -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Top Ambassadors</h4>
                                    <div class="flex-shrink-0">
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
    <!-- END layout-wrapper --> <?= $this->include('partials/vendor-scripts') ?>

    <!-- jQuery (ensure it's loaded) -->
    <script src="<?= base_url('assets/libs/jquery/jquery.min.js') ?>"></script>

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

    <!-- Dashboard charts js -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // For debugging
            console.log('Gender Data:', <?= $genderChartData ?>);
            console.log('Nationality Data:', <?= $nationalityChartData ?>);

            // Testing modal availability
            console.log('jQuery availability:', typeof jQuery !== 'undefined' ? 'Available' : 'Not available');

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
            document.getElementById('registration-title').textContent = defaultButton.getAttribute('data-title');

            // Initialize DataTables objects with enhanced configurations - using window to make them globally accessible
            window.genderTable = $('#gender-datatable').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'copy',
                        className: 'btn btn-sm btn-light'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-sm btn-light',
                        title: 'Gender Distribution - ' + new Date().toLocaleDateString()
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-sm btn-light',
                        title: 'Gender Distribution - ' + new Date().toLocaleDateString()
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-sm btn-light',
                        title: 'Gender Distribution - ' + new Date().toLocaleDateString()
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-sm btn-light'
                    }
                ],
                lengthChange: false,
                pageLength: 10,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    document.querySelector('.dataTables_paginate > .pagination').classList.add('pagination-rounded');
                }
            });

            window.ageTable = $('#age-datatable').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'copy',
                        className: 'btn btn-sm btn-light'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-sm btn-light',
                        title: 'Age Distribution - ' + new Date().toLocaleDateString()
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-sm btn-light',
                        title: 'Age Distribution - ' + new Date().toLocaleDateString()
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-sm btn-light',
                        title: 'Age Distribution - ' + new Date().toLocaleDateString()
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-sm btn-light'
                    }
                ],
                lengthChange: false,
                pageLength: 10,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    document.querySelector('.dataTables_paginate > .pagination').classList.add('pagination-rounded');
                }
            });

            window.nationalityTable = $('#nationality-datatable').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'copy',
                        className: 'btn btn-sm btn-light'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-sm btn-light',
                        title: 'Nationality Distribution - ' + new Date().toLocaleDateString()
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-sm btn-light',
                        title: 'Nationality Distribution - ' + new Date().toLocaleDateString()
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-sm btn-light',
                        title: 'Nationality Distribution - ' + new Date().toLocaleDateString()
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-sm btn-light'
                    }
                ],
                lengthChange: false,
                pageLength: 10,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    document.querySelector('.dataTables_paginate > .pagination').classList.add('pagination-rounded');
                }
            }); // Gender data loading function
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
                        if (window.genderTable) {
                            window.genderTable.clear();

                            var total = data.values.reduce((a, b) => a + b, 0);

                            // Populate the gender table
                            for (var i = 0; i < data.labels.length; i++) {
                                var percentage = ((data.values[i] / total) * 100).toFixed(1) + '%';
                                window.genderTable.row.add([
                                    data.labels[i],
                                    data.values[i].toLocaleString(),
                                    percentage
                                ]);
                            }
                            window.genderTable.draw();
                            console.log('Gender table updated');
                        } else {
                            console.error('Gender table not initialized');
                        }

                        // Remove loading state
                        document.getElementById('gender-loading').classList.remove('active');
                    })
                    .catch(error => {
                        console.error('Error fetching gender data:', error);
                        document.getElementById('gender-loading').classList.remove('active');
                        showError('Failed to load gender statistics');
                    });
            } // Age data loading function
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
                        if (window.ageTable) {
                            window.ageTable.clear();

                            var total = data.values.reduce((a, b) => a + b, 0);

                            // Populate the age table
                            for (var i = 0; i < data.labels.length; i++) {
                                var percentage = ((data.values[i] / total) * 100).toFixed(1) + '%';
                                window.ageTable.row.add([
                                    data.labels[i],
                                    data.values[i].toLocaleString(),
                                    percentage
                                ]);
                            }
                            window.ageTable.draw();
                            console.log('Age table updated');
                        } else {
                            console.error('Age table not initialized');
                        }

                        // Remove loading state
                        document.getElementById('age-loading').classList.remove('active');
                    })
                    .catch(error => {
                        console.error('Error fetching age data:', error);
                        document.getElementById('age-loading').classList.remove('active');
                        showError('Failed to load age statistics');
                    });
            } // Nationality data loading function
            window.loadNationalityData = function() {
                // Show loading indicator
                document.getElementById('nationality-loading').classList.add('active');
                console.log('Loading nationality data...');

                // Fetch fresh nationality data from server
                fetch('<?= site_url('dashboard/ajaxNationalityStats') ?>')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Nationality data received:', data);
                        if (window.nationalityTable) {
                            window.nationalityTable.clear();

                            var total = data.values.reduce((a, b) => a + b, 0);

                            // Populate the nationality table
                            for (var i = 0; i < data.labels.length; i++) {
                                var percentage = ((data.values[i] / total) * 100).toFixed(1) + '%';
                                window.nationalityTable.row.add([
                                    data.labels[i],
                                    data.values[i].toLocaleString(),
                                    percentage
                                ]);
                            }
                            window.nationalityTable.draw();
                            console.log('Nationality table updated');
                        } else {
                            console.error('Nationality table not initialized');
                        }

                        // Remove loading state
                        document.getElementById('nationality-loading').classList.remove('active');
                    })
                    .catch(error => {
                        console.error('Error fetching nationality data:', error);
                        document.getElementById('nationality-loading').classList.remove('active');
                        showError('Failed to load nationality statistics');
                    });
            }

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
        });
    </script>

    <!-- App js -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script> <!-- Modal initialization script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // No need to re-assign these as they're already defined with window scope above
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
                            var modal = new bootstrap.Modal(modalElement);
                            modal.show();
                            // Call the loading function in window scope to ensure it's found
                            setTimeout(function() {
                                window.loadGenderData();
                            }, 500); // Slight delay to let modal open first
                        } else if (chartType === 'age') {
                            console.log('Opening age modal');
                            var modalElement = document.getElementById('ageModal');
                            console.log('Modal element:', modalElement);
                            var modal = new bootstrap.Modal(modalElement);
                            modal.show();
                            setTimeout(function() {
                                window.loadAgeData();
                            }, 500); // Slight delay to let modal open first
                        } else if (chartType === 'nationality') {
                            console.log('Opening nationality modal');
                            var modalElement = document.getElementById('nationalityModal');
                            console.log('Modal element:', modalElement);
                            var modal = new bootstrap.Modal(modalElement);
                            modal.show();
                            setTimeout(function() {
                                window.loadNationalityData();
                            }, 500); // Slight delay to let modal open first
                        }
                    } catch (error) {
                        console.error('Error showing modal:', error);
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
                                <!-- Will be populated dynamically -->
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
                                <!-- Will be populated dynamically -->
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
                    <h5 class="modal-title" id="nationalityModalLabel">Nationality Distribution Details</h5>
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
                        <table id="nationality-datatable" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Nationality</th>
                                    <th>Number of Participants</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>