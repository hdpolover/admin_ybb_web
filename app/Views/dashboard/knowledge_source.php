<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Knowledge Source Analytics')); ?>

    <!-- apexcharts -->
    <link href="<?= base_url('assets/libs/apexcharts/apexcharts.min.css') ?>" rel="stylesheet" type="text/css" />

    <!-- DataTables -->
    <link href="<?= base_url('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') ?>" rel="stylesheet" type="text/css" />
    <!-- Custom DataTables styling -->
    <link href="<?= base_url('assets/css/datatables-custom.css') ?>" rel="stylesheet" type="text/css" />

    <style>
        .dt-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 10px;
        }
        .matrix-table th,
        .matrix-table td {
            white-space: nowrap;
            font-size: 13px;
        }
        .matrix-table thead th {
            text-align: center;
            vertical-align: middle;
        }
        .matrix-table td.row-total {
            font-weight: 600;
        }
        .hidden-row {
            display: none;
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Analytics', 'title' => 'Knowledge Source Analytics')); ?>

                    <!-- Summary stat cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate card-height-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-primary rounded-circle fs-3">
                                                <i class="ri-broadcast-line text-primary"></i>
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
                                                <i class="ri-checkbox-circle-line text-success"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">With Known Source</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($totalWithSource) ?></h4>
                                            <p class="text-muted mb-0">
                                                <span class="badge bg-light text-success">
                                                    <?= $grandTotal > 0 ? round(($totalWithSource / $grandTotal) * 100, 1) : 0 ?>% of total
                                                </span>
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
                                            <span class="avatar-title bg-soft-warning rounded-circle fs-3">
                                                <i class="ri-bar-chart-line text-warning"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Top Source</p>
                                            <h4 class="fs-5 flex-grow-1 mb-1"><?= esc($topSource) ?></h4>
                                            <p class="text-muted mb-0"><?= number_format($grandTotal > 0 && $topSource !== 'N/A' ? (array_reduce($knowledgeSourceStats, fn($carry, $item) => $item->source === $topSource ? (int)$item->total : $carry, 0) / $grandTotal * 100) : 0, 1) ?>% of total</p>
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

                    <?php if (empty($knowledgeSourceStats)): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="ri-bar-chart-2-line text-muted" style="font-size: 48px;"></i>
                                        <h5 class="mt-3 text-muted">No Knowledge Source Data Available</h5>
                                        <p class="text-muted">There is no knowledge source data for the selected program yet.</p>
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
                                    <h4 class="card-title mb-0 flex-grow-1">Source Distribution</h4>
                                </div>
                                <div class="card-body">
                                    <div id="ks-donut-chart" class="apex-charts" style="height: 380px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-7">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Source Breakdown (Horizontal Bar)</h4>
                                </div>
                                <div class="card-body">
                                    <div id="ks-bar-chart" class="apex-charts" style="height: 380px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cross-tab Matrix -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Knowledge Source by Country</h4>
                                    <div class="flex-shrink-0 d-flex align-items-center gap-2">
                                        <label class="mb-0 me-1 text-muted" for="country-filter">Filter by Country:</label>
                                        <select id="country-filter" class="form-select form-select-sm" style="min-width: 200px;">
                                            <option value="">All Countries</option>
                                            <?php foreach ($countries as $country): ?>
                                                <option value="<?= esc($country) ?>"><?= esc($country) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($matrixRows)): ?>
                                        <p class="text-center text-muted py-4">No country cross-tab data available.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table id="matrix-datatable" class="table table-bordered table-striped matrix-table dt-responsive w-100">
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
                                                <?php foreach ($matrixRows as $row): ?>
                                                    <tr data-country="<?= esc($row['nationality']) ?>">
                                                        <td><?= esc($row['nationality']) ?></td>
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

                    <?php endif; ?>

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

    <!-- jQuery -->
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ksData = <?= $knowledgeSourceChartData ?>;

            // Donut chart
            if (document.getElementById('ks-donut-chart') && ksData.labels[0] !== 'No Data') {
                var donutOptions = {
                    chart: {
                        height: 380,
                        type: 'pie',
                    },
                    series: ksData.values,
                    labels: ksData.labels,
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
                        text: 'No data available',
                        align: 'center',
                        verticalAlign: 'middle',
                        style: { color: '#6c757d', fontSize: '16px', fontFamily: 'Poppins' }
                    }
                };
                new ApexCharts(document.querySelector('#ks-donut-chart'), donutOptions).render();
            }

            // Horizontal bar chart
            if (document.getElementById('ks-bar-chart') && ksData.labels[0] !== 'No Data') {
                var barOptions = {
                    series: [{ name: 'Participants', data: ksData.values }],
                    chart: {
                        type: 'bar',
                        height: 380,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true,
                            distributed: true
                        }
                    },
                    dataLabels: { enabled: false },
                    colors: ['#038edc', '#f7b84b', '#51d28c', '#f34e4e', '#564ab1', '#f1734f', '#45cb85'],
                    xaxis: {
                        categories: ksData.labels,
                        title: { text: 'Number of Participants' }
                    },
                    yaxis: { title: { text: '' } },
                    noData: {
                        text: 'No data available',
                        align: 'center',
                        verticalAlign: 'middle',
                        style: { color: '#6c757d', fontSize: '16px', fontFamily: 'Poppins' }
                    }
                };
                new ApexCharts(document.querySelector('#ks-bar-chart'), barOptions).render();
            }

            // Initialize matrix DataTable
            if (document.getElementById('matrix-datatable')) {
                try {
                    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.DataTable !== 'undefined') {
                        var matrixTable = jQuery('#matrix-datatable').DataTable({
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
                            pageLength: 25,
                            searching: true,
                            ordering: true,
                            language: {
                                search: 'Search countries:',
                                lengthMenu: 'Show _MENU_ countries'
                            },
                            drawCallback: function() {
                                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                            }
                        });

                        // Country filter dropdown (client-side, works on top of DataTable search)
                        document.getElementById('country-filter').addEventListener('change', function() {
                            var val = this.value;
                            matrixTable.search(val).draw();
                        });
                    }
                } catch (err) {
                    console.error('Failed to initialize matrix DataTable:', err);
                }
            }
        });
    </script>

    <!-- App js -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
