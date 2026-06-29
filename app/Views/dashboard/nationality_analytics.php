<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Nationality Analytics')); ?>

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
        .matrix-table th, .matrix-table td { white-space: nowrap; font-size: 13px; }
        .matrix-table thead th { text-align: center; vertical-align: middle; }
        .matrix-table td.row-total { font-weight: 600; }
    </style>

    <?= $this->include('partials/head-css') ?>
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Analytics', 'title' => 'Nationality Analytics')); ?>

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
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($grandTotal) ?></h4>
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
                                            <span class="avatar-title bg-soft-success rounded-circle fs-3">
                                                <i class="ri-map-pin-line text-success"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Distinct Countries</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($distinctCountries) ?></h4>
                                            <p class="text-muted mb-0">Excluding Not Specified</p>
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
                                                <i class="ri-trophy-line text-warning"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Top Country</p>
                                            <h4 class="fs-5 flex-grow-1 mb-1"><?= esc($topCountry) ?></h4>
                                            <p class="text-muted mb-0"><?= $topCountryPct ?>% of total</p>
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
                                            <span class="avatar-title bg-soft-danger rounded-circle fs-3">
                                                <i class="ri-question-mark text-danger"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Not Specified</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $notSpecifiedPct ?>%</h4>
                                            <p class="text-muted mb-0">Of all participants</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($nationalityStats)): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="ri-bar-chart-2-line text-muted" style="font-size: 48px;"></i>
                                        <h5 class="mt-3 text-muted">No Nationality Data Available</h5>
                                        <p class="text-muted">There is no nationality data for the selected program yet.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>

                    <!-- Charts -->
                    <div class="row">
                        <div class="col-xl-5">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Country Distribution (Donut)</h4>
                                </div>
                                <div class="card-body">
                                    <div id="nat-donut-chart" class="apex-charts" style="height: 380px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-7">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Top Countries (Horizontal Bar)</h4>
                                </div>
                                <div class="card-body">
                                    <div id="nat-bar-chart" class="apex-charts" style="height: 380px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cross-tab: Country × Source -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Country × Knowledge Source</h4>
                                    <div class="flex-shrink-0 d-flex align-items-center gap-2">
                                        <label class="mb-0 me-1 text-muted" for="source-country-filter">Filter:</label>
                                        <select id="source-country-filter" class="form-select form-select-sm" style="min-width: 200px;">
                                            <option value="">All Countries</option>
                                            <?php foreach ($sourceCountries as $c): ?>
                                                <option value="<?= esc($c) ?>"><?= esc($c) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($sourceMatrixRows)): ?>
                                        <p class="text-center text-muted py-4">No data available.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table id="nat-source-datatable" class="table table-bordered table-striped matrix-table dt-responsive w-100">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Country</th>
                                                    <?php foreach ($sourceCols as $col): ?>
                                                        <th><?= esc($col) ?></th>
                                                    <?php endforeach; ?>
                                                    <th class="row-total">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($sourceMatrixRows as $row): ?>
                                                    <tr data-country="<?= esc($row['label']) ?>">
                                                        <td><?= esc($row['label']) ?></td>
                                                        <?php foreach ($sourceCols as $col): ?>
                                                            <td class="text-center"><?= number_format($row['counts'][$col] ?? 0) ?></td>
                                                        <?php endforeach; ?>
                                                        <td class="text-center row-total"><?= number_format($row['rowTotal']) ?></td>
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

                    <!-- Cross-tab: Country × Gender -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Country × Gender</h4>
                                    <div class="flex-shrink-0 d-flex align-items-center gap-2">
                                        <label class="mb-0 me-1 text-muted" for="gender-country-filter">Filter:</label>
                                        <select id="gender-country-filter" class="form-select form-select-sm" style="min-width: 200px;">
                                            <option value="">All Countries</option>
                                            <?php foreach ($genderCountries as $c): ?>
                                                <option value="<?= esc($c) ?>"><?= esc($c) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($genderMatrixRows)): ?>
                                        <p class="text-center text-muted py-4">No data available.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table id="nat-gender-datatable" class="table table-bordered table-striped matrix-table dt-responsive w-100">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Country</th>
                                                    <?php foreach ($genderCols as $col): ?>
                                                        <th><?= esc($col) ?></th>
                                                    <?php endforeach; ?>
                                                    <th class="row-total">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($genderMatrixRows as $row): ?>
                                                    <tr data-country="<?= esc($row['label']) ?>">
                                                        <td><?= esc($row['label']) ?></td>
                                                        <?php foreach ($genderCols as $col): ?>
                                                            <td class="text-center"><?= number_format($row['counts'][$col] ?? 0) ?></td>
                                                        <?php endforeach; ?>
                                                        <td class="text-center row-total"><?= number_format($row['rowTotal']) ?></td>
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
        document.addEventListener('DOMContentLoaded', function() {
            var natData = <?= $nationalityChartData ?>;

            var chartColors = ['#038edc','#f7b84b','#51d28c','#f34e4e','#564ab1','#f1734f','#45cb85','#e83e8c','#20c997','#fd7e14'];

            // Donut chart (top 10)
            if (document.getElementById('nat-donut-chart') && natData.labels[0] !== 'No Data') {
                var topN     = 10;
                var donutLbls = natData.labels.slice(0, topN);
                var donutVals = natData.values.slice(0, topN);
                new ApexCharts(document.querySelector('#nat-donut-chart'), {
                    chart: { height: 380, type: 'pie' },
                    series: donutVals,
                    labels: donutLbls,
                    colors: chartColors,
                    legend: { position: 'bottom' },
                    noData: { text: 'No data available', align: 'center', verticalAlign: 'middle', style: { color: '#6c757d', fontSize: '16px' } }
                }).render();
            }

            // Horizontal bar chart (top 15)
            if (document.getElementById('nat-bar-chart') && natData.labels[0] !== 'No Data') {
                var topBar = 15;
                new ApexCharts(document.querySelector('#nat-bar-chart'), {
                    series: [{ name: 'Participants', data: natData.values.slice(0, topBar) }],
                    chart: { type: 'bar', height: 380, toolbar: { show: false } },
                    plotOptions: { bar: { borderRadius: 4, horizontal: true, distributed: true } },
                    dataLabels: { enabled: false },
                    colors: chartColors,
                    xaxis: { categories: natData.labels.slice(0, topBar), title: { text: 'Number of Participants' } },
                    noData: { text: 'No data available', align: 'center', verticalAlign: 'middle', style: { color: '#6c757d', fontSize: '16px' } }
                }).render();
            }

            // DataTable helper
            function initMatrix(tableId, filterId) {
                if (!document.getElementById(tableId)) return null;
                var dt = null;
                try {
                    if (jQuery.fn.DataTable.isDataTable('#' + tableId)) {
                        jQuery('#' + tableId).DataTable().destroy();
                    }
                    dt = jQuery('#' + tableId).DataTable({
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
                        searching: true,
                        ordering: true,
                        language: { search: 'Search countries:', lengthMenu: 'Show _MENU_ countries' },
                        drawCallback: function() {
                            jQuery('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                        }
                    });
                } catch (err) {
                    console.error('DataTable init failed (' + tableId + '):', err);
                }

                if (filterId && dt) {
                    document.getElementById(filterId).addEventListener('change', function() {
                        dt.search(this.value).draw();
                    });
                }
                return dt;
            }

            initMatrix('nat-source-datatable', 'source-country-filter');
            initMatrix('nat-gender-datatable', 'gender-country-filter');
        });
    </script>

    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
