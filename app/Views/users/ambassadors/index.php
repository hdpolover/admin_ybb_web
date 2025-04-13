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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Users', 'title' => 'Ambassador Management')); ?>

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

                    <!-- Ambassadors Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">All Ambassadors</h5>
                                    <div class="flex-shrink-0">
                                        <a href="<?= site_url('ambassadors/new') ?>" class="btn btn-primary waves-effect waves-light">
                                            <i class="ri-add-line align-middle me-1"></i> Add New Ambassador
                                        </a>
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
                                                    placeholder="Search by name, email, phone, referral code..."
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
                                            <label class="form-label">Status</label>
                                            <select id="filter-status" class="form-select">
                                                <option value="">All Statuses</option>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                                <option value="2">Suspended</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Sort By</label>
                                            <select id="sort-by" class="form-select">
                                                <option value="created_at-desc">Registration Date (Newest)</option>
                                                <option value="created_at-asc">Registration Date (Oldest)</option>
                                                <option value="full_name-asc">Name (A-Z)</option>
                                                <option value="full_name-desc">Name (Z-A)</option>
                                                <option value="referral_count-desc">Most Referrals</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end mb-2">
                                            <button id="apply-filters" class="btn btn-primary me-2">Apply Filters</button>
                                            <button id="reset-filters" class="btn btn-light">Reset</button>
                                        </div>
                                    </div>

                                    <table id="ambassadors-datatable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Details</th>
                                                <th>Referral Code</th>
                                                <th>Referrals</th>
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

    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>

    <script src="/assets/js/pages/datatables.init.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            var ambassadorsTable = $('#ambassadors-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= site_url('users/ambassadors/getData') ?>',
                    type: 'GET',
                    data: function(d) {
                        // Add filter parameters
                        d.status = $('#filter-status').val();
                        d.search.value = $('#search-box').val(); // Add search term

                        // Get sort info from the dropdown
                        var sortInfo = $('#sort-by').val().split('-');
                        if (sortInfo.length === 2) {
                            d.order = [{
                                column: getColumnIndex(sortInfo[0]),
                                dir: sortInfo[1]
                            }];
                        }

                        return d;
                    }
                },                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            // Using DataTables' row counter for sequential numbering
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'details',
                        render: function(data, type, row) {
                            if (!data) return '<div class="text-muted">N/A</div>';

                            // Get user status class
                            let statusClass = 'bg-success';
                            let statusText = 'Active';

                            if (data.status == 0) {
                                statusClass = 'bg-danger';
                                statusText = 'Inactive';
                            } else if (data.status == 2) {
                                statusClass = 'bg-warning';
                                statusText = 'Suspended';
                            }

                            return '<div class="d-flex gap-2">' +
                                '<div class="flex-shrink-0">' +
                                '<div class="avatar-sm rounded-circle bg-soft-primary text-center d-flex align-items-center justify-content-center">' +
                                '<span class="fs-5 text-primary">' + (data.first_letter || '?') + '</span>' +
                                '</div>' +
                                '</div>' +
                                '<div class="flex-grow-1">' +
                                '<h5 class="fs-14 mb-1">' + data.name + ' <span class="badge ' + statusClass + ' badge-soft-' + statusClass + ' fs-11">' + statusText + '</span></h5>' +
                                '<div class="d-flex flex-column gap-1 text-muted fs-12">' +
                                '<div><i class="ri-mail-line me-1"></i>' + data.email + '</div>' +
                                '<div><i class="ri-building-2-line me-1"></i>' + (data.institution || 'No Institution') + '</div>' +
                                '<div><i class="ri-calendar-line me-1"></i>Joined: ' + data.created_at + '</div>' +
                                '</div>' +
                                '</div>' +
                                '</div>';
                        }
                    },
                    {
                        data: 'ref_code',
                        render: function(data, type, row) {
                            return '<div class="d-flex align-items-center">' +
                                   '<span class="badge bg-info-subtle text-info fs-6 p-2 user-select-all" ' +
                                   'style="font-weight: 600; letter-spacing: 1px;">' + 
                                   data + '</span>' +
                                   '<button class="ms-2 btn btn-sm btn-soft-secondary copy-code" ' +
                                   'data-code="' + data + '" title="Copy code">' +
                                   '<i class="ri-file-copy-line"></i></button>' +
                                   '</div>';
                        }
                    },                    {
                        data: 'referral_count',
                        render: function(data, type, row) {
                            // For sorting, just return the number
                            if (type === 'sort' || type === 'type') {
                                return data;
                            }
                            
                            // For display, create a visual representation
                            if (data > 0) {
                                // Create different badge colors based on referral count
                                let badgeClass = 'bg-info';
                                if (data >= 10) {
                                    badgeClass = 'bg-success';
                                } else if (data >= 5) {
                                    badgeClass = 'bg-primary';
                                }
                                
                                // Progress bar representation for visual impact
                                let maxReferrals = 20; // Consider 20+ referrals as max for visual purposes
                                let percentage = Math.min(data / maxReferrals * 100, 100);
                                
                                return '<div class="d-flex align-items-center">' +
                                    '<span class="badge rounded-pill ' + badgeClass + ' fs-6 me-2">' + data + '</span>' +
                                    '<div class="progress flex-grow-1" style="height: 6px;">' +
                                    '<div class="progress-bar ' + badgeClass + '" role="progressbar" style="width: ' + percentage + '%;" ' +
                                    'aria-valuenow="' + data + '" aria-valuemin="0" aria-valuemax="' + maxReferrals + '"></div>' +
                                    '</div>' +
                                    '</div>';
                            }
                            return '<span class="text-muted">0</span>';
                        }
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],                order: [
                    [3, 'desc'] // Sort by referral count (column 3) in descending order
                ],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                responsive: true
            });

            // Helper function to map column names to DataTable column indices
            function getColumnIndex(columnName) {
                switch (columnName) {
                    case '#':
                        return 0; // This would map to the 'details' column which likely contains date info
                    case 'details':
                        return 1; // Maps to the 'details' column which contains name
                    case 'ref_code':
                        return 2; // Maps to the referral code column
                    case 'referral_count':
                        return 3; // Maps to the referrals count column
                    default:
                        return 0; // Default to 0 for unknown columns
                }
            }

            // Hide DataTables default search box
            $('.dataTables_filter').hide();

            // Function to perform the search
            function performSearch() {
                var searchTerm = $('#search-box').val();
                ambassadorsTable.ajax.reload();
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

            // Handle filter buttons
            document.getElementById('apply-filters').addEventListener('click', function() {
                ambassadorsTable.ajax.reload();
            });

            document.getElementById('reset-filters').addEventListener('click', function() {
                // Reset all filter select values
                document.getElementById('filter-status').value = '';
                document.getElementById('search-box').value = '';
                document.getElementById('sort-by').value = 'created_at-desc';

                // Reload the table with reset filters
                ambassadorsTable.search('').draw(); // Clear the search
                ambassadorsTable.ajax.reload();
            });

            // Handle delete ambassador
            $(document).on('click', '.delete-ambassador', function() {
                var ambassadorId = $(this).data('id');

                if (confirm('Are you sure you want to delete this ambassador?')) {
                    $.ajax({
                        url: '<?= base_url('ambassadors/delete/') ?>' + ambassadorId,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('Ambassador deleted successfully');
                                ambassadorsTable.ajax.reload();
                            } else {
                                alert('Failed to delete ambassador: ' + (response.message || 'Unknown error'));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            alert('An error occurred while trying to delete the ambassador');
                        }
                    });
                }
            });
        });
    </script>

    <!-- App js -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>

</html>