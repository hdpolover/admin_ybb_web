<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Payments')); ?>

    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- Date Range Picker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <!-- Enhanced Export SweetAlert Styles -->
    <style>
        /* Enhanced Export SweetAlert Styles */
        .export-success-popup .export-success-details {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .export-success-popup .export-stats .stat-item {
            padding: 10px;
            border-radius: 8px;
            background: linear-gradient(145deg, #f8f9fa, #e9ecef);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .export-success-popup .export-stats .stat-item:hover {
            transform: translateY(-2px);
        }

        /* Enhanced Metrics Styling */
        .export-success-popup .enhanced-metrics .metric-card {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }

        .export-success-popup .enhanced-metrics .metric-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
            border-color: #adb5bd;
        }

        .export-success-popup .enhanced-metrics .metric-value {
            font-weight: 600;
            font-size: 16px;
        }

        .export-success-popup .enhanced-metrics .metric-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .export-success-popup .export-metadata .metadata-item {
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #28a745;
            margin-bottom: 8px;
        }

        .export-completion-popup .completion-stats .stat-badge {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .export-completion-popup .completion-stats .stat-badge:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        /* Enhanced Metrics for Completion Popup */
        .export-completion-popup .metric-badge {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        }

        .export-completion-popup .metric-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.18);
        }

        .export-completion-popup .metric-number {
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .export-completion-popup .completion-metadata .metadata-row {
            padding: 6px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .export-completion-popup .completion-metadata .metadata-row:last-child {
            border-bottom: none;
        }

        .export-completion-popup .completion-icon {
            animation: bounceIn 0.8s ease-out;
        }

        /* Enhanced Metrics for Export Result Tables */
        .export-result .enhanced-metrics-row .metric-card {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .export-result .enhanced-metrics-row .metric-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(0,0,0,0.12);
            border-color: #adb5bd;
        }

        .export-result .enhanced-metrics-row .metric-value {
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .export-result .enhanced-metrics-row .metric-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Loading SweetAlert enhancement */
        .swal2-loading .swal2-progress-bar {
            background: linear-gradient(90deg, #28a745, #20c997, #17a2b8);
            animation: progressShimmer 2s linear infinite;
        }

        @keyframes progressShimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
    </style>

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
                                        <a href="<?= site_url('payments/make') ?>" class="btn btn-primary waves-effect waves-light me-2">
                                            <i class="ri-add-line align-middle me-1"></i> Make Payment
                                        </a>
                                        <button type="button" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#exportModal">
                                            <i class="ri-file-excel-2-line align-middle me-1"></i> Export
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Filter Controls -->
                                    <div class="row mb-4">
                                        <div class="col-md-12 mb-3">
                                            <div class="input-group search-box">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="ri-search-line text-muted"></i>
                                                </span>
                                                <input type="text" id="search-box" class="form-control border-start-0 ps-0"
                                                    placeholder="Search by participant name, email, transaction ID, payment amount..."
                                                    autocomplete="off">
                                                <button class="btn btn-primary" id="search-button" type="button">
                                                    <i class="ri-search-line me-1"></i> Search
                                                </button>
                                            </div>
                                            <div class="form-text text-muted mt-1">
                                                <small>Press Enter or click Search to filter results</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Payment Status</label>
                                            <select id="filter-status" class="form-select">
                                                <option value="">All Statuses</option>
                                                <option value="0">Created</option>
                                                <option value="1">Pending</option>
                                                <option value="2">Success</option>
                                                <option value="3">Cancelled</option>
                                                <option value="4">Rejected</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Program Payment</label>
                                            <select id="filter-program-payment" class="form-select">
                                                <option value="">All Program Payments</option>
                                                <?php
                                                foreach ($programPayments as $payment): ?>
                                                    <option value="<?= $payment->id ?>"><?= $payment->name ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Payment Method</label>
                                            <select id="filter-payment-method" class="form-select">
                                                <option value="">All Payment Methods</option>
                                                <?php
                                                foreach ($paymentMethods as $method): ?>
                                                    <option value="<?= $method->id ?>"><?= $method->name ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end mb-2">
                                            <button id="apply-filters" class="btn btn-primary me-2">Apply Filters</button>
                                            <button id="reset-filters" class="btn btn-light">Reset</button>
                                        </div>
                                    </div>
                                    <table id="payments-datatable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="sorting">Date</th>
                                                <th class="sorting">Transaction Codes</th>
                                                <th class="sorting">Participant</th>
                                                <th class="sorting">Payment Details</th>
                                                <th class="sorting">Status</th>
                                                <th class="sorting_disabled">Actions</th>
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="exportModalLabel">Export Payments Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="exportForm" action="<?= site_url('exports/payments') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="program_id" value="<?= session('current_program') ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="export-template" class="form-label">Export Template</label>
                                <select id="export-template" name="template" class="form-select">
                                    <option value="standard">Standard Export</option>
                                    <option value="detailed">Detailed Export</option>
                                    <option value="summary">Summary Export</option>
                                </select>
                                <div class="form-text text-muted">Choose the level of detail for the export</div>
                            </div>
                            <div class="col-md-6">
                                <label for="export-format" class="form-label">Export Format</label>
                                <select id="export-format" name="format" class="form-select">
                                    <option value="excel">Excel (.xlsx)</option>
                                    <option value="csv">CSV</option>
                                </select>
                                <div class="form-text text-muted">File format for the export</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="dateRange" class="form-label">Date Range</label>
                                <input type="text" class="form-control" name="date_range" id="dateRange" placeholder="Select date range">
                                <div class="form-text text-muted">Filter by payment date range</div>
                            </div>
                            <div class="col-md-6">
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
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="programPaymentFilter" class="form-label">Program Payment</label>
                                <select class="form-select" name="program_payment_id" id="programPaymentFilter">
                                    <option value="">All Program Payments</option>
                                    <?php foreach ($programPayments as $payment): ?>
                                        <option value="<?= $payment->id ?>"><?= esc($payment->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-muted">Filter by specific program payment type</div>
                            </div>
                            <div class="col-md-6">
                                <label for="paymentMethodFilter" class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method_id" id="paymentMethodFilter">
                                    <option value="">All Payment Methods</option>
                                    <?php foreach ($paymentMethods as $method): ?>
                                        <option value="<?= $method->id ?>"><?= esc($method->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-muted">Filter by payment method</div>
                            </div>
                        </div>

                        <div id="exportSummary" class="alert alert-info mt-3">
                            <h6 class="alert-heading">Export Summary</h6>
                            <p id="exportCount" class="mb-0">All payments will be exported using YBB Export API. Use filters above to limit the export.</p>
                        </div>

                        <div class="card border border-info shadow-sm mt-4">
                            <div class="card-header bg-info bg-opacity-10">
                                <h5 class="card-title mb-0">
                                    <i class="ri-cloud-line me-1 fs-18 align-middle text-info"></i>
                                    <span class="align-middle text-dark fw-semibold">YBB Export API Integration</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="ri-server-line fs-24 text-info me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-2">Advanced Export Features:</h6>
                                        <ul class="ps-3 mb-2">
                                            <li>Professional Excel formatting with currency symbols</li>
                                            <li>Automatic data chunking for large payment datasets</li>
                                            <li>Payment status translation and formatting</li>
                                            <li>Real-time export progress tracking</li>
                                            <li>Optimized compression for large files</li>
                                        </ul>
                                        <div class="alert alert-success py-2">
                                            <i class="ri-check-line me-1"></i>
                                            <strong>Ready:</strong> The system will automatically process and optimize your payment export.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="exportButton">
                        <i class="ri-cloud-upload-line align-middle me-1"></i> Start Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Results Container -->
    <div class="container-fluid mt-4">
        <div id="export-results"></div>
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

    <!-- Date Range Picker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script src="/assets/js/pages/datatables.init.js"></script>
    <script src="/assets/js/app.js"></script>
    
    <!-- Enhanced Export Manager with Nuclear Interval Clearing -->
    <script src="/assets/js/enhanced-export-manager.js?v=<?= time() ?>&bust=<?= uniqid() ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            var paymentsTable = $('#payments-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= site_url('payments/getData') ?>',
                    type: 'GET',
                    data: function(d) {
                        // Add filter parameters
                        d.status = $('#filter-status').val();
                        d.program_payment_id = $('#filter-program-payment').val();
                        d.payment_method_id = $('#filter-payment-method').val();
                        d.search.value = $('#search-box').val();

                        console.log("Sending sort data to server:", d.order);
                        return d;
                    },
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
                        name: 'payments.created_at',
                        render: function(data, type, row) {
                            return data || '';
                        }
                    },
                    {
                        data: 'transaction_codes',
                        name: 'payments.id',
                        orderable: true,
                        render: function(data, type, row) {
                            if (type === 'sort' || type === 'filter') {
                                return data?.payment_id || '';
                            }

                            if (!data) return 'N/A';
                            return '<div><strong>Payment ID:</strong> ' + (data.payment_id || 'N/A') + '</div>' +
                                '<div><strong>Transaction Code:</strong> ' + (data.transaction_code || 'N/A') + '</div>' +
                                '<div><strong>Order ID:</strong> ' + (data.order_id || 'N/A') + '</div>';
                        }
                    },
                    {
                        data: 'participant',
                        name: 'participants.full_name',
                        orderable: true,
                        render: function(data, type, row) {
                            if (type === 'sort' || type === 'filter') {
                                return data?.name || '';
                            }

                            if (!data) {
                                return 'N/A';
                            }
                            return '<div class="fw-medium">' + (data.name || 'Unknown') + '</div>' +
                                '<div class="small text-muted">' + (data.email || '') + '</div>' +
                                '<div class="small">' + (data.nationality || 'N/A') + '</div>';
                        }
                    },
                    {
                        data: 'payment_details',
                        name: 'payments.amount',
                        orderable: true,
                        render: function(data, type, row) {
                            if (type === 'sort' || type === 'filter') {
                                return data?.amount_raw || '';
                            }

                            if (!data) return 'N/A';

                            var methodBadge = '<span class="badge bg-primary">' + (data.method || 'N/A') + '</span>';
                            return '<div><strong>' + (data.program_name || 'N/A') + '</strong></div>' +
                                '<div>' + (data.amount || 'N/A') + '</div>' +
                                '<div>' + methodBadge + '</div>';
                        }
                    },
                    {
                        data: 'status',
                        name: 'payments.status',
                        orderable: true
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'desc'] // Default order is first column (date) descending
                ],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                responsive: true,
                orderCellsTop: true,
                stateSave: true,
                ordering: true,
                serverSide: true,
                drawCallback: function(settings) {
                    console.log("DataTable draw complete, data:", settings.json);
                    console.log("Current sort order:", paymentsTable.order());
                }
            });

            // Hide DataTables default search box
            $('.dataTables_filter').hide();

            // Function to perform the search
            function performSearch() {
                var searchTerm = $('#search-box').val();
                console.log("Searching for term:", searchTerm);
                paymentsTable.ajax.reload();
            }

            // Search when Enter is pressed in the search box
            $('#search-box').on('keypress', function(e) {
                if (e.which === 13) { // Enter key pressed
                    e.preventDefault();
                    performSearch();
                }
            });

            // Search when the search button is clicked
            $(document).on('click', '#search-button', function() {
                performSearch();
            });

            // Handle filter buttons with logging
            document.getElementById('apply-filters').addEventListener('click', function() {
                console.log("Applying filters:", {
                    status: $('#filter-status').val(),
                    program_payment_id: $('#filter-program-payment').val(),
                    payment_method_id: $('#filter-payment-method').val(),
                    search: $('#search-box').val()
                });
                paymentsTable.ajax.reload();
            });

            document.getElementById('reset-filters').addEventListener('click', function() {
                console.log("Resetting all filters");
                // Reset all filter select values
                document.getElementById('filter-status').value = '';
                document.getElementById('filter-program-payment').value = '';
                document.getElementById('filter-payment-method').value = '';
                document.getElementById('search-box').value = '';

                // Reload the table with reset filters
                console.log("Table reset - reloading with empty filters");
                paymentsTable.search('').draw();
                paymentsTable.ajax.reload();
            });

            // Initialize Date Range Picker
            $('#dateRange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                updateExportSummary();
            });

            $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                updateExportSummary();
            });

            // Enhanced Export Manager Integration - Convert button to work with EnhancedExportManager
            function initializeEnhancedExport() {
                if (typeof EnhancedExportManager !== 'undefined') {
                    console.log('Enhanced Export Manager class available, initializing...');
                    window.enhancedExportManager = new EnhancedExportManager();
                    console.log('Enhanced Export Manager initialized:', window.enhancedExportManager);
                    
                    // Convert the existing export button to work with EnhancedExportManager
                    const exportBtn = document.getElementById('exportButton');
                    if (exportBtn) {
                        // Add the required classes and data attributes
                        exportBtn.classList.add('export-btn');
                        exportBtn.setAttribute('data-export-type', 'payments');
                        exportBtn.setAttribute('data-url', '/exports/payments');
                        
                        // IMPORTANT: Add data attribute to specify which form to use for CSRF token
                        exportBtn.setAttribute('data-form-selector', '#exportForm');
                        
                        console.log('Payments export button configured for EnhancedExportManager');
                        console.log('Button classes:', exportBtn.className);
                        console.log('Button data attributes:', {
                            exportType: exportBtn.dataset.exportType,
                            url: exportBtn.dataset.url,
                            formSelector: exportBtn.dataset.formSelector
                        });
                    } else {
                        console.error('Export button #exportButton not found!');
                    }
                } else {
                    console.log('Enhanced Export Manager not yet available, retrying in 100ms...');
                    setTimeout(initializeEnhancedExport, 100);
                }
            }
            
            // Start initialization after a short delay to ensure scripts are loaded
            setTimeout(initializeEnhancedExport, 200);

            // Function to check export status using YBB Export API
            function checkExportStatus(exportId, attempt = 1) {
                console.log(`Checking export status for ${exportId}, attempt ${attempt}`);
                
                $.ajax({
                    url: `<?= site_url('exports/status/') ?>${exportId}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Status check response:', response);
                        
                        if (response.success && response.data) {
                            const data = response.data;
                            
                            if (data.status === 'success') {
                                // Export completed successfully
                                handleExportCompleted(exportId, data);
                            } else if (data.status === 'processing') {
                                // Still processing, check again
                                setTimeout(() => {
                                    checkExportStatus(exportId, attempt + 1);
                                }, 2000);
                            } else if (data.status === 'failed') {
                                // Export failed
                                Swal.fire({
                                    title: 'Export Failed',
                                    html: data.message || 'Export processing failed on the server',
                                    icon: 'error',
                                    confirmButtonColor: '#f06548'
                                });
                            } else {
                                // Unknown status, retry
                                if (attempt < 30) { // Max 30 attempts (1 minute)
                                    setTimeout(() => {
                                        checkExportStatus(exportId, attempt + 1);
                                    }, 2000);
                                } else {
                                    Swal.fire({
                                        title: 'Export Timeout',
                                        html: 'Export is taking longer than expected. Please check the export dashboard or try again later.',
                                        icon: 'warning',
                                        confirmButtonColor: '#f06548'
                                    });
                                }
                            }
                        } else {
                            // Failed to get status, retry
                            if (attempt < 30) {
                                setTimeout(() => {
                                    checkExportStatus(exportId, attempt + 1);
                                }, 2000);
                            } else {
                                Swal.fire({
                                    title: 'Status Check Failed',
                                    html: 'Unable to check export status. The export may still be processing.',
                                    icon: 'warning',
                                    confirmButtonColor: '#f06548'
                                });
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Status check error:', error);
                        
                        // Retry on error
                        if (attempt < 30) {
                            setTimeout(() => {
                                checkExportStatus(exportId, attempt + 1);
                            }, 3000);
                        } else {
                            Swal.fire({
                                title: 'Status Check Failed',
                                html: 'Unable to check export status after multiple attempts.',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                        }
                    }
                });
            }

            // Function to handle completed export
            function handleExportCompleted(exportId, exportData) {
                console.log('Export completed:', exportData);
                
                // Check if it's a multi-file export
                if (exportData.export_strategy === 'multi_file' || exportData.total_files > 1) {
                    // Handle multi-file export
                    Swal.fire({
                        title: 'Export Completed!',
                        html: `
                            <div class="text-start">
                                <p><strong>Large payment dataset export completed successfully!</strong></p>
                                <p>Total Records: ${exportData.record_count}</p>
                                <p>Files Generated: ${exportData.total_files}</p>
                                <div class="mt-3">
                                    <button class="btn btn-success btn-sm me-2" onclick="downloadAllFiles('${exportId}')">
                                        <i class="ri-download-2-line"></i> Download ZIP Archive
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="showFileList('${exportId}', ${JSON.stringify(exportData).replace(/"/g, '&quot;')})">
                                        <i class="ri-file-list-line"></i> View Individual Files
                                    </button>
                                </div>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonColor: '#0ab39c',
                        showConfirmButton: false,
                        allowOutsideClick: true
                    });
                } else {
                    // Handle single file export
                    Swal.fire({
                        title: 'Export Completed!',
                        html: `
                            <div class="text-start">
                                <p><strong>Payment export completed successfully!</strong></p>
                                <p>Records: ${exportData.record_count}</p>
                                <p>File Size: ${formatFileSize(exportData.file_size)}</p>
                                <p>Your file will download automatically.</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonColor: '#0ab39c',
                        timer: 3000,
                        timerProgressBar: true
                    });
                    
                    // Trigger download
                    window.location.href = `<?= site_url('exports/download/') ?>${exportId}`;
                }
            }

            // Helper function to download all files as ZIP
            window.downloadAllFiles = function(exportId) {
                window.location.href = `<?= site_url('exports/download/') ?>${exportId}/zip`;
            };

            // Helper function to show individual file list
            window.showFileList = function(exportId, exportDataJson) {
                const exportData = JSON.parse(exportDataJson.replace(/&quot;/g, '"'));
                
                let fileListHtml = '<div class="list-group">';
                if (exportData.individual_files) {
                    exportData.individual_files.forEach((file, index) => {
                        fileListHtml += `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${file.file_name}</strong><br>
                                    <small class="text-muted">Records: ${file.record_count} | Size: ${formatFileSize(file.file_size)}</small>
                                </div>
                                <button class="btn btn-sm btn-outline-success" onclick="downloadBatchFile('${exportId}', ${file.batch_number})">
                                    <i class="ri-download-line"></i> Download
                                </button>
                            </div>
                        `;
                    });
                }
                fileListHtml += '</div>';
                
                Swal.fire({
                    title: 'Individual Export Files',
                    html: fileListHtml,
                    width: '600px',
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#6c757d'
                });
            };

            // Helper function to download batch file
            window.downloadBatchFile = function(exportId, batchNumber) {
                window.location.href = `<?= site_url('exports/download/') ?>${exportId}/batch/${batchNumber}`;
            };

            // Helper function to format file size
            function formatFileSize(bytes) {
                if (!bytes) return 'Unknown';
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(1024));
                return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
            }

            // Setup filter change handlers to update the summary
            $('#dateRange, #statusFilter, #programPaymentFilter, #paymentMethodFilter, #export-template, #export-format').on('change', function() {
                updateExportSummary();
            });

            function updateExportSummary() {
                let summaryText = "All payments will be exported using YBB Export API";

                // Check for date range filter
                const dateRange = $('#dateRange').val();
                if (dateRange) {
                    summaryText += ` (date range: ${dateRange})`;
                }

                // Check for status filter
                const status = $('#statusFilter').val();
                if (status !== '' && status !== null) {
                    const statusNames = {
                        '0': 'Created',
                        '1': 'Pending',
                        '2': 'Success',
                        '3': 'Cancelled',
                        '4': 'Rejected'
                    };
                    summaryText += ` with status: ${statusNames[status]}`;
                }

                // Check for program payment filter
                const programPayment = $('#programPaymentFilter').val();
                if (programPayment) {
                    const programPaymentOption = document.querySelector(`#programPaymentFilter option[value="${programPayment}"]`);
                    const programPaymentText = programPaymentOption ? programPaymentOption.textContent : programPayment;
                    summaryText += `, for ${programPaymentText}`;
                }

                // Check for payment method filter
                const paymentMethod = $('#paymentMethodFilter').val();
                if (paymentMethod) {
                    const paymentMethodOption = document.querySelector(`#paymentMethodFilter option[value="${paymentMethod}"]`);
                    const paymentMethodText = paymentMethodOption ? paymentMethodOption.textContent : paymentMethod;
                    summaryText += `, via ${paymentMethodText}`;
                }

                // Add template and format info
                const template = $('#export-template').val();
                const format = $('#export-format').val();
                summaryText += ` using ${template} template in ${format.toUpperCase()} format`;

                // Update summary text
                $('#exportCount').text(summaryText);
            }

            // Initial call to update export summary
            updateExportSummary();

            // Log any DataTable errors
            $.fn.dataTable.ext.errMode = function(settings, helpPage, message) {
                console.error("DataTable error:", message);
            };
        });
    </script>
</body>

</html>
