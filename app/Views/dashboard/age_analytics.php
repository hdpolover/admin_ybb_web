<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Age Distribution Analytics')); ?>

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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Analytics', 'title' => 'Age Distribution Analytics')); ?>

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
                                                <i class="ri-bar-chart-line text-success"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Dominant Band</p>
                                            <h4 class="fs-5 flex-grow-1 mb-1"><?= esc($dominantBand) ?></h4>
                                            <p class="text-muted mb-0"><?= $dominantPct ?>% of total</p>
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
                                                <i class="ri-group-line text-warning"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Age Groups</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= count(array_filter($ageStats, fn($i) => $i->age_group !== 'Unknown')) ?></h4>
                                            <p class="text-muted mb-0">Active bands with data</p>
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
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Unknown Age</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $unknownPct ?>%</h4>
                                            <p class="text-muted mb-0">Missing birthdate</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($ageStats)): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="ri-bar-chart-2-line text-muted" style="font-size: 48px;"></i>
                                        <h5 class="mt-3 text-muted">No Age Data Available</h5>
                                        <p class="text-muted">There is no age data for the selected program yet.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>

                    <!-- Charts -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Age Band Distribution</h4>
                                </div>
                                <div class="card-body">
                                    <div id="age-bar-chart" class="apex-charts" style="height: 380px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cross-tab: Age × Gender -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Age Band × Gender</h4>
                                    <div class="flex-shrink-0 d-flex align-items-center gap-2">
                                        <label class="mb-0 me-1 text-muted" for="ag-filter">Filter by Band:</label>
                                        <select id="ag-filter" class="form-select form-select-sm" style="min-width: 160px;">
                                            <option value="">All Bands</option>
                                            <?php foreach (array_column($genderMatrixRows, 'label') as $band): ?>
                                                <option value="<?= esc($band) ?>"><?= esc($band) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($genderMatrixRows)): ?>
                                        <p class="text-center text-muted py-4">No data available.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table id="ag-datatable" class="table table-bordered table-striped matrix-table dt-responsive w-100">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Age Band</th>
                                                    <?php foreach ($genderCols as $col): ?>
                                                        <th><?= esc($col) ?></th>
                                                    <?php endforeach; ?>
                                                    <th class="row-total">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($genderMatrixRows as $row): ?>
                                                    <tr>
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

                    <!-- Cross-tab: Age × Nationality -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Age Band × Nationality</h4>
                                    <div class="flex-shrink-0 d-flex align-items-center gap-2">
                                        <label class="mb-0 me-1 text-muted" for="an-filter">Filter by Band:</label>
                                        <select id="an-filter" class="form-select form-select-sm" style="min-width: 160px;">
                                            <option value="">All Bands</option>
                                            <?php foreach (array_column($nationalityMatrixRows, 'label') as $band): ?>
                                                <option value="<?= esc($band) ?>"><?= esc($band) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($nationalityMatrixRows)): ?>
                                        <p class="text-center text-muted py-4">No data available.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table id="an-datatable" class="table table-bordered table-striped matrix-table dt-responsive w-100">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Age Band</th>
                                                    <?php foreach ($nationalityCols as $col): ?>
                                                        <th><?= esc($col) ?></th>
                                                    <?php endforeach; ?>
                                                    <th class="row-total">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($nationalityMatrixRows as $row): ?>
                                                    <tr>
                                                        <td><?= esc($row['label']) ?></td>
                                                        <?php foreach ($nationalityCols as $col): ?>
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
            var ageData = <?= $ageChartData ?>;

            var bandColors = ['#564ab1','#038edc','#51d28c','#f7b84b','#f34e4e','#f1734f','#6c757d'];

            // Vertical bar chart for age bands
            if (document.getElementById('age-bar-chart') && ageData.labels[0] !== 'No Data') {
                new ApexCharts(document.querySelector('#age-bar-chart'), {
                    series: [{ name: 'Participants', data: ageData.values }],
                    chart: { type: 'bar', height: 380, toolbar: { show: false } },
                    plotOptions: { bar: { borderRadius: 4, distributed: true } },
                    dataLabels: { enabled: false },
                    colors: bandColors,
                    xaxis: {
                        categories: ageData.labels,
                        title: { text: 'Age Band' }
                    },
                    yaxis: { title: { text: 'Number of Participants' } },
                    legend: { show: false },
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

            initMatrix('ag-datatable', 'ag-filter');
            initMatrix('an-datatable', 'an-filter');
        });
    </script>

    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
