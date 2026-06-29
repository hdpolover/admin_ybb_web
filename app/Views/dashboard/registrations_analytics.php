<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Registrations Analytics')); ?>

    <!-- apexcharts -->
    <link href="<?= base_url('assets/libs/apexcharts/apexcharts.min.css') ?>" rel="stylesheet" type="text/css" />

    <!-- DataTables -->
    <link href="<?= base_url('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/css/datatables-custom.css') ?>" rel="stylesheet" type="text/css" />

    <style>
        .dt-buttons .btn { margin-right: 5px; margin-bottom: 5px; }
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { margin-top: 10px; }
        .reg-table th, .reg-table td { white-space: nowrap; font-size: 13px; }
    </style>

    <?= $this->include('partials/head-css') ?>
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Analytics', 'title' => 'Registrations Analytics')); ?>

                    <!-- Summary stat cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate card-height-100">
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
                                            <p class="text-muted mb-0">For <?= esc($program->name) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate card-height-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-warning rounded-circle fs-3">
                                                <i class="ri-rocket-line text-warning"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Peak Day</p>
                                            <h4 class="fs-5 flex-grow-1 mb-1"><?= esc($regSummary->peak_date) ?></h4>
                                            <p class="text-muted mb-0">
                                                <span class="badge bg-light text-warning"><?= number_format($regSummary->peak_count) ?> registrations</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate card-height-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded-circle fs-3">
                                                <i class="ri-bar-chart-line text-success"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Avg Per Day</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($regSummary->avg_per_day, 1) ?></h4>
                                            <p class="text-muted mb-0"><?= number_format($regSummary->total_days) ?> active days</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate card-height-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded-circle fs-3">
                                                <i class="ri-calendar-line text-info"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">First Registration</p>
                                            <h4 class="fs-6 flex-grow-1 mb-1"><?= esc($regSummary->first_date) ?></h4>
                                            <p class="text-muted mb-0">Last: <?= esc($regSummary->last_date) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($dailyStats)): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="ri-user-add-line text-muted" style="font-size: 48px;"></i>
                                        <h5 class="mt-3 text-muted">No Registration Data Available</h5>
                                        <p class="text-muted">There is no registration data for the selected program yet.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>

                    <!-- Daily trend + Monthly bar -->
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Daily Registration Trend (Last 90 Active Days)</h4>
                                </div>
                                <div class="card-body">
                                    <div id="reg-area-chart" class="apex-charts" style="height: 380px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Monthly Registrations</h4>
                                </div>
                                <div class="card-body">
                                    <div id="reg-monthly-chart" class="apex-charts" style="height: 380px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registrations by day — exportable DataTable -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Daily Breakdown (Last 90 Active Days)</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="daily-datatable" class="table table-bordered table-striped reg-table dt-responsive w-100">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th class="text-center">Registrations</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_reverse($dailyStats) as $row): ?>
                                                    <tr>
                                                        <td><?= esc($row->label) ?></td>
                                                        <td class="text-center"><?= number_format($row->total) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdowns: Nationality + Knowledge Source -->
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Registrations by Nationality (Top 20)</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($nationalityStats)): ?>
                                        <p class="text-center text-muted py-4">No data available.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table id="nat-datatable" class="table table-bordered table-striped reg-table dt-responsive w-100">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Country</th>
                                                    <th class="text-center">Participants</th>
                                                    <th class="text-center">% of Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($nationalityStats as $row): ?>
                                                    <?php
                                                    $label = empty($row->nationality) ? 'Not Specified' : $row->nationality;
                                                    $pct   = $summary->total_participants > 0
                                                        ? round(($row->total / $summary->total_participants) * 100, 1) : 0;
                                                    ?>
                                                    <tr>
                                                        <td><?= esc($label) ?></td>
                                                        <td class="text-center"><?= number_format($row->total) ?></td>
                                                        <td class="text-center"><?= $pct ?>%</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Registrations by Knowledge Source</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($knowledgeStats)): ?>
                                        <p class="text-center text-muted py-4">No data available.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table id="ks-datatable" class="table table-bordered table-striped reg-table dt-responsive w-100">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Source</th>
                                                    <th class="text-center">Participants</th>
                                                    <th class="text-center">% of Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($knowledgeStats as $row): ?>
                                                    <?php
                                                    $pct = $summary->total_participants > 0
                                                        ? round(($row->total / $summary->total_participants) * 100, 1) : 0;
                                                    ?>
                                                    <tr>
                                                        <td><?= esc($row->source) ?></td>
                                                        <td class="text-center"><?= number_format($row->total) ?></td>
                                                        <td class="text-center"><?= $pct ?>%</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php endif; ?>

                </div>
            </div>

            <?= $this->include('partials/footer') ?>
        </div>
    </div>

    <?= $this->include('partials/vendor-scripts') ?>
    <script src="<?= base_url('assets/libs/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/apexcharts/apexcharts.min.js') ?>"></script>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var dailyData   = <?= $dailyChartData ?>;
            var monthlyData = <?= $monthlyChartData ?>;

            // Area chart — daily trend
            if (document.getElementById('reg-area-chart') && dailyData.labels.length > 0) {
                new ApexCharts(document.querySelector('#reg-area-chart'), {
                    series: [{ name: 'Registrations', data: dailyData.values }],
                    chart: {
                        type: 'area',
                        height: 380,
                        toolbar: { show: false },
                        zoom: { enabled: false }
                    },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2 },
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] }
                    },
                    colors: ['#038edc'],
                    xaxis: {
                        categories: dailyData.labels,
                        tickAmount: Math.min(10, dailyData.labels.length),
                        labels: { rotate: -45, style: { fontSize: '11px' } }
                    },
                    yaxis: { title: { text: 'Registrations' } },
                    tooltip: { x: { format: 'dd MMM yyyy' } },
                    noData: { text: 'No data available', align: 'center', verticalAlign: 'middle' }
                }).render();
            }

            // Bar chart — monthly
            if (document.getElementById('reg-monthly-chart') && monthlyData.labels.length > 0) {
                new ApexCharts(document.querySelector('#reg-monthly-chart'), {
                    series: [{ name: 'Registrations', data: monthlyData.values }],
                    chart: { type: 'bar', height: 380, toolbar: { show: false } },
                    plotOptions: { bar: { borderRadius: 4, distributed: false } },
                    dataLabels: { enabled: false },
                    colors: ['#51d28c'],
                    xaxis: {
                        categories: monthlyData.labels,
                        labels: { rotate: -45, style: { fontSize: '11px' } }
                    },
                    yaxis: { title: { text: 'Registrations' } },
                    noData: { text: 'No data available', align: 'center', verticalAlign: 'middle' }
                }).render();
            }

            // DataTable helper
            function initDT(id) {
                if (!document.getElementById(id)) return;
                try {
                    jQuery('#' + id).DataTable({
                        dom: 'Blfrtip',
                        buttons: [
                            { extend: 'copy',  className: 'btn btn-sm btn-light' },
                            { extend: 'csv',   className: 'btn btn-sm btn-light' },
                            { extend: 'excel', className: 'btn btn-sm btn-light' },
                            { extend: 'pdf',   className: 'btn btn-sm btn-light' },
                            { extend: 'print', className: 'btn btn-sm btn-light' }
                        ],
                        lengthChange: true,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                        pageLength: 25,
                        ordering: true,
                        drawCallback: function () {
                            jQuery('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                        }
                    });
                } catch (e) {
                    console.error('DataTable init failed (' + id + '):', e);
                }
            }

            initDT('daily-datatable');
            initDT('nat-datatable');
            initDT('ks-datatable');
        });
    </script>

    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
