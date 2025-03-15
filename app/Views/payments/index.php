<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Payments')); ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Payments', 'title' => 'Payment Management')); ?>

                    <!-- Payment Stats -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded-circle fs-3">
                                                <i class="ri-money-dollar-circle-line text-success"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total Payments</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1">IDR <?= number_format($stats->total_amount, 0, ',', '.') ?></h4>
                                            <p class="text-muted mb-0">
                                                From <?= array_sum($stats->status_counts) ?> transactions
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
                                            <span class="avatar-title bg-soft-success rounded-circle fs-3">
                                                <i class="ri-check-double-line text-success"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Successful</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $stats->status_counts[2] ?></h4>
                                            <p class="text-muted mb-0">
                                                <?= array_sum($stats->status_counts) > 0 ?
                                                    number_format($stats->status_counts[2] / array_sum($stats->status_counts) * 100, 1) . '%'
                                                    : '0%' ?>
                                                of transactions
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
                                                <i class="ri-time-line text-warning"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Pending</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $stats->status_counts[1] ?></h4>
                                            <p class="text-muted mb-0">
                                                <?= array_sum($stats->status_counts) > 0 ?
                                                    number_format($stats->status_counts[1] / array_sum($stats->status_counts) * 100, 1) . '%'
                                                    : '0%' ?>
                                                of transactions
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
                                            <span class="avatar-title bg-soft-danger rounded-circle fs-3">
                                                <i class="ri-close-circle-line text-danger"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Cancelled/Rejected</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1">
                                                <?= ($stats->status_counts[3] + $stats->status_counts[4]) ?>
                                            </h4>
                                            <p class="text-muted mb-0">
                                                <?= array_sum($stats->status_counts) > 0 ?
                                                    number_format(($stats->status_counts[3] + $stats->status_counts[4]) / array_sum($stats->status_counts) * 100, 1) . '%'
                                                    : '0%' ?>
                                                of transactions
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Currency Stats -->
                    <div class="row">
                        <div class="col-xl-6 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-primary rounded-circle fs-3">
                                                <i class="ri-money-rupee-circle-line text-primary"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total in IDR</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1">Rp <?= number_format($currency_stats->total_idr, 0, ',', '.') ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded-circle fs-3">
                                                <i class="ri-money-dollar-circle-line text-info"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total in USD</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1">$ <?= number_format($currency_stats->total_usd, 2) ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payments Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">All Payments</h5>
                                    <div class="flex-shrink-0">
                                        <button type="button" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#exportModal">
                                            <i class="ri-file-excel-2-line align-middle me-1"></i> Export
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="payments-datatable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Participant</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Transaction ID</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- DataTable will populate this -->
                                        </tbody>
                                    </table>
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

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Export Payments Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="<?= site_url('payments/export') ?>" method="post">
                        <input type="hidden" name="program_id" value="<?= session('current_program') ?>">
                        <div class="mb-3">
                            <label for="exportType" class="form-label">Export Format</label>
                            <select class="form-select" name="export_type" id="exportType">
                                <option value="excel">Excel (.xlsx)</option>
                                <option value="csv">CSV</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="dateRange" class="form-label">Date Range</label>
                            <input type="text" class="form-control" name="date_range" id="dateRange" placeholder="Select date range">
                        </div>
                        <div class="mb-3">
                            <label for="statusFilter" class="form-label">Payment Status</label>
                            <select class="form-select" name="status" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="0">Created</option>
                                <option value="1">Pending</option>
                                <option value="2">Success</option>
                                <option value="3">Cancelled</option>
                                <option value="4">Rejected</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="exportButton">Export</button>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <script src="/assets/js/pages/datatables.init.js"></script>
    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            var paymentsTable = $('#payments-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= site_url('payments/getData') ?>',
                    type: 'GET',
                    dataSrc: function(json) {
                        console.log("Server response:", json);
                        return json.data || [];
                    },
                    error: function(xhr, error, thrown) {
                        console.error("DataTable AJAX error:", error, thrown, xhr);
                    }
                },
                columns: [{
                        data: 'payment_date',
                        render: function(data, type, row) {
                            console.log("Row data:", row);
                            return data || '';
                        }
                    },
                    {
                        data: 'participant',
                        render: function(data, type, row) {
                            if (!data) {
                                console.warn("Missing participant data for row:", row);
                                return 'N/A';
                            }
                            return '<div>' + (data.name || 'Unknown') + '</div>' +
                                '<div class="small text-muted">' + (data.email || '') + '</div>';
                        }
                    },
                    {
                        data: 'amount'
                    },
                    {
                        data: 'payment_method'
                    },
                    {
                        data: 'transaction_id'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                responsive: true,
                drawCallback: function(settings) {
                    console.log("DataTable draw complete, data:", settings.json);
                }
            });

            // Handle export button click
            document.getElementById('exportButton').addEventListener('click', function() {
                document.querySelector('#exportModal form').submit();
            });

            // Log any DataTable errors
            $.fn.dataTable.ext.errMode = function(settings, helpPage, message) {
                console.error("DataTable error:", message);
            };
        });
    </script>
</body>

</html>