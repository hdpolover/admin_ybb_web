<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Ambassadors')); ?>

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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Ambassadors', 'title' => 'Ambassador Management')); ?>

                    <!-- Ambassador Stats -->
                    <div class="row">
                        <div class="col-xl-4 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-primary rounded-circle fs-3">
                                                <i class="ri-user-star-line text-primary"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total Ambassadors</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $stats['total_ambassadors'] ?? 0 ?></h4>
                                            <p class="text-muted mb-0">
                                                For current program
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded-circle fs-3">
                                                <i class="ri-check-double-line text-success"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Active Ambassadors</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $stats['active_ambassadors'] ?? 0 ?></h4>
                                            <p class="text-muted mb-0">
                                                <?= $stats['total_ambassadors'] > 0 ?
                                                    number_format(($stats['active_ambassadors'] / $stats['total_ambassadors']) * 100, 1) . '%'
                                                    : '0%' ?>
                                                of total ambassadors
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded-circle fs-3">
                                                <i class="ri-group-line text-info"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total Referrals</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $stats['total_referrals'] ?? 0 ?></h4>
                                            <p class="text-muted mb-0">
                                                From all ambassadors
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card" id="ambassadorsList">
                                <div class="card-header border-0">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title mb-0 flex-grow-1">All Ambassadors</h5>
                                        <div class="flex-shrink-0">
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-soft-primary" id="refresh-btn">
                                                    <i class="ri-refresh-line align-bottom"></i> Refresh
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn btn-soft-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ri-filter-3-line align-bottom"></i> Filter
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item status-filter" href="#" data-status="">All Ambassadors</a></li>
                                                        <li><a class="dropdown-item status-filter" href="#" data-status="1">Active</a></li>
                                                        <li><a class="dropdown-item status-filter" href="#" data-status="0">Inactive</a></li>
                                                        <li><a class="dropdown-item status-filter" href="#" data-status="2">Suspended</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div>
                                        <div class="table-responsive table-card mb-3">
                                            <table id="ambassadors-datatable" class="table table-nowrap align-middle mb-0">
                                                <thead class="table-light text-muted">
                                                    <tr>
                                                        <th scope="col">Registered Date</th>
                                                        <th scope="col">Name</th>
                                                        <th scope="col">Contact Info</th>
                                                        <th scope="col">Referral Code</th>
                                                        <th scope="col">Referrals</th>
                                                        <th scope="col">Status</th>
                                                        <th scope="col">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->
                    </div><!--end row-->
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
    <script src="/assets/js/app.js"></script>    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize DataTable
            var dataTable = $('#ambassadors-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= base_url('ambassadors/getData') ?>',
                    type: 'GET',
                    data: function(d) {
                        // Get status filter value if it exists
                        var statusFilter = $('.status-filter.active').data('status');
                        if (statusFilter !== undefined) {
                            d.status = statusFilter;
                        }
                    },
                    error: function(xhr, error, thrown) {
                        console.log('DataTables error: ' + error + ' - ' + thrown);
                    }
                },
                columns: [{
                        data: 'created_date',
                        name: 'created_at'
                    },
                    {
                        data: null,
                        name: 'full_name',
                        render: function(data, type, row) {
                            return '<h5 class="fs-14 mb-1">' + data.ambassador.name + '</h5>';
                        }
                    },
                    {
                        data: null,
                        name: 'email',
                        render: function(data, type, row) {
                            return '<div>' +
                                '<p class="mb-0">Email: ' + data.ambassador.email + '</p>' +
                                '<p class="mb-0">Phone: ' + data.ambassador.phone + '</p>' +
                                '</div>';
                        }
                    },
                    {
                        data: 'ref_code',
                        name: 'ref_code'
                    },
                    {
                        data: 'referral_count',
                        name: 'referral_count'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                language: {
                    emptyTable: 'No ambassadors found'
                },
                drawCallback: function() {
                    // Initialize any JS components after table is drawn
                }
            });

            // Handle refresh button click
            $('#refresh-btn').on('click', function() {
                dataTable.ajax.reload(null, false);
            });

            // Handle status filter
            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                var status = $(this).data('status');

                // Update ajax request with status filter
                dataTable.ajax.url('<?= base_url('ambassadors/getData') ?>?status=' + status).load();
            });
        });
    </script>

    <!-- App js -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>

</html>