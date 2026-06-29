<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Ambassadors Analytics')); ?>

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
        .amb-table th, .amb-table td { white-space: nowrap; font-size: 13px; }
    </style>

    <?= $this->include('partials/head-css') ?>
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Analytics', 'title' => 'Ambassadors Analytics')); ?>

                    <!-- Summary stat cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate card-height-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-primary rounded-circle fs-3">
                                                <i class="ri-team-line text-primary"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total Ambassadors</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($totalAmbassadors) ?></h4>
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
                                                <i class="ri-user-add-line text-success"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total Referred</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($totalReferred) ?></h4>
                                            <p class="text-muted mb-0">
                                                <span class="badge bg-light text-success">
                                                    <?= $summary->total_participants > 0 ? round(($totalReferred / $summary->total_participants) * 100, 1) : 0 ?>% of total participants
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
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Avg Referrals</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($avgReferrals, 1) ?></h4>
                                            <p class="text-muted mb-0">Per ambassador</p>
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
                                                <i class="ri-trophy-line text-danger"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Top Ambassador</p>
                                            <h4 class="fs-6 flex-grow-1 mb-1"><?= esc($topAmbassadorName) ?></h4>
                                            <p class="text-muted mb-0"><?= number_format($topReferrals) ?> referrals</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($ambassadors)): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="ri-team-line text-muted" style="font-size: 48px;"></i>
                                        <h5 class="mt-3 text-muted">No Ambassador Data Available</h5>
                                        <p class="text-muted">There are no ambassadors for the selected program yet.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>

                    <!-- Top 15 Horizontal Bar Chart -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Top 15 Ambassadors by Referrals</h4>
                                </div>
                                <div class="card-body">
                                    <div id="amb-bar-chart" class="apex-charts" style="height: 420px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Full Leaderboard DataTable -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Full Ambassador Leaderboard</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="amb-datatable" class="table table-bordered table-striped amb-table dt-responsive w-100">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center">#</th>
                                                    <th>Ambassador</th>
                                                    <th class="text-center">Referrals</th>
                                                    <th class="text-center">% of Referred Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ambassadors as $rank => $amb): ?>
                                                    <?php
                                                    $pct = $totalReferred > 0
                                                        ? round(((int)$amb->total_referrals / $totalReferred) * 100, 1) : 0;
                                                    ?>
                                                    <tr>
                                                        <td class="text-center"><?= $rank + 1 ?></td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-xs me-2">
                                                                    <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                        <?= strtoupper(substr($amb->ambassador_name, 0, 1)) ?>
                                                                    </span>
                                                                </div>
                                                                <?= esc($amb->ambassador_name) ?>
                                                            </div>
                                                        </td>
                                                        <td class="text-center"><?= number_format($amb->total_referrals) ?></td>
                                                        <td class="text-center"><?= $pct ?>%</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
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
            var ambData = <?= $ambassadorChartData ?>;

            // Horizontal bar chart — top 15
            if (document.getElementById('amb-bar-chart') && ambData.labels.length > 0) {
                new ApexCharts(document.querySelector('#amb-bar-chart'), {
                    series: [{ name: 'Referrals', data: ambData.values }],
                    chart: {
                        type: 'bar',
                        height: 420,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true,
                            distributed: true,
                            barHeight: '60%'
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '11px' }
                    },
                    colors: ['#038edc','#f7b84b','#51d28c','#f34e4e','#564ab1','#f1734f','#45cb85',
                             '#038edc','#f7b84b','#51d28c','#f34e4e','#564ab1','#f1734f','#45cb85','#6c757d'],
                    xaxis: {
                        categories: ambData.labels,
                        title: { text: 'Number of Referrals' }
                    },
                    yaxis: { title: { text: '' } },
                    legend: { show: false },
                    noData: {
                        text: 'No data available',
                        align: 'center',
                        verticalAlign: 'middle',
                        style: { color: '#6c757d', fontSize: '16px' }
                    }
                }).render();
            }

            // Leaderboard DataTable
            if (document.getElementById('amb-datatable')) {
                try {
                    jQuery('#amb-datatable').DataTable({
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
                        order: [[2, 'desc']],
                        language: {
                            search: 'Search ambassadors:',
                            lengthMenu: 'Show _MENU_ ambassadors'
                        },
                        drawCallback: function () {
                            jQuery('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                        }
                    });
                } catch (e) {
                    console.error('DataTable init failed (amb-datatable):', e);
                }
            }
        });
    </script>

    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
