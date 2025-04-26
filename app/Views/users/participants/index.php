<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Participants')); ?>

    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" /> <!-- Added missing link -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" /> <!-- Added missing link -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- Custom CSS for participant table -->
    <style>
        .payment-status-container,
        .submission-status-container {
            max-width: 200px;
            font-size: 0.85rem;
        }

        .payment-status-container .badge,
        .submission-status-container .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        #participants-datatable td {
            vertical-align: middle;
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Users', 'title' => 'Participants')); ?>

                    <!-- Participant Stats -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-primary rounded-circle fs-3">
                                                <i class="ri-user-line text-primary"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total Participants</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $stats->total ?? 0 ?></h4>
                                            <p class="text-muted mb-0">
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ri-arrow-up-line align-bottom"></i> <?= $stats->recent ?? 0 ?>
                                                </span> new in last 30 days
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
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Fully Funded</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $stats->category_counts['fully_funded'] ?? 0 ?></h4>
                                            <p class="text-muted mb-0">
                                                <?= $stats->total > 0 ?
                                                    number_format(($stats->category_counts['fully_funded'] / $stats->total) * 100, 1) . '%'
                                                    : '0%' ?>
                                                of participants
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
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Self Funded</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $stats->category_counts['self_funded'] ?? 0 ?></h4>
                                            <p class="text-muted mb-0">
                                                <?= $stats->total > 0 ?
                                                    number_format(($stats->category_counts['self_funded'] / $stats->total) * 100, 1) . '%'
                                                    : '0%' ?>
                                                of participants
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Participants Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">All Participants</h5>
                                    <div class="flex-shrink-0">
                                        <a href="<?= site_url('participants/new') ?>" class="btn btn-primary waves-effect waves-light">
                                            <i class="ri-add-line align-middle me-1"></i> Add New Participant
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
                                                </span> <input type="text" id="search-box" class="form-control border-start-0 ps-0"
                                                    placeholder="Search by name, email, account ID, nationality..."
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
                                            <label class="form-label">Category</label>
                                            <select id="filter-category" class="form-select">
                                                <option value="">All Categories</option>
                                                <option value="fully_funded">Fully Funded</option>
                                                <option value="self_funded">Self Funded</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Form Status</label>
                                            <select id="filter-form-status" class="form-select">
                                                <option value="">All Statuses</option>
                                                <option value="0">Not Started</option>
                                                <option value="1">On Progress</option>
                                                <option value="2">Submitted</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end mb-2">
                                            <button id="apply-filters" class="btn btn-primary me-2">Apply Filters</button>
                                            <button id="reset-filters" class="btn btn-light">Reset</button>
                                        </div>
                                    </div>
                                    <table id="participants-datatable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Account ID</th>
                                                <th>Participant Details</th>
                                                <th>Submission Status</th>
                                                <th>Registered On</th>
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
            var participantsTable = $('#participants-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= site_url('users/participants/getData') ?>',
                    type: 'GET',
                    data: function(d) {
                        // Add filter parameters
                        d.category = $('#filter-category').val();
                        d.form_status = $('#filter-form-status').val();
                        d.search.value = $('#search-box').val(); // Add search term
                        return d;
                    }
                },
                columns: [{
                        data: 'order_number',
                        width: "5%"
                    }, {
                        data: 'account_id',
                        width: "10%",
                        render: function(data, type, row) {
                            if (!data || type === 'sort' || type === 'type') return data;
                            return '<div class="text-truncate" style="max-width: 120px;" title="' + data + '">' + data + '</div>';
                        }
                    }, {
                        data: 'participant_details',
                        width: "35%",
                        render: function(data, type, row) {
                            if (!data) return 'N/A';
                            let html = '<div class="d-flex align-items-center">';

                            // Avatar display - either picture or placeholder
                            html += '<div class="avatar-xs me-2">';
                            if (data.picture_url && data.picture_url !== '' && data.picture_url !== 'null') {
                                html += '<img src="' + data.picture_url + '" alt="' + data.full_name + '" class="avatar-xs rounded-circle" />';
                            } else {
                                html += '<span class="avatar-title rounded-circle bg-soft-primary text-primary">' +
                                    (data.full_name ? data.full_name.charAt(0).toUpperCase() : '?') + '</span>';
                            }
                            html += '</div>';

                            // Participant info
                            html += '<div>';
                            html += '<h5 class="fs-14 mb-1">' + data.full_name + '</h5>';
                            html += '<p class="text-muted mb-0">' + data.email + '</p>';
                            if (data.nationality && data.nationality !== 'N/A') {
                                html += '<span class="badge bg-light text-dark">' + data.nationality + '</span>';
                            }
                            html += '</div>';
                            html += '</div>';
                            return html;
                        }
                    },
                    {
                        data: 'submission_status',
                        width: "20%"
                    },
                    {
                        data: 'registered_on',
                        width: "15%"
                    },
                    {
                        data: 'actions',
                        width: "15%",
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [4, 'desc'] // Order by registration date (descending)
                ],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                responsive: true
            });

            // Hide DataTables default search box
            $('.dataTables_filter').hide();

            // Function to perform the search
            function performSearch() {
                var searchTerm = $('#search-box').val();
                participantsTable.ajax.reload();
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
                participantsTable.ajax.reload();
            });
            
            document.getElementById('reset-filters').addEventListener('click', function() {
                // Reset all filter select values
                document.getElementById('filter-category').value = '';
                document.getElementById('filter-form-status').value = '';
                document.getElementById('search-box').value = '';

                // Reload the table with reset filters
                participantsTable.search('').draw(); // Clear the search
                participantsTable.ajax.reload();
            });

            // Handle delete participant
            $(document).on('click', '.delete-participant', function() {
                var participantId = $(this).data('id');

                if (confirm('Are you sure you want to delete this participant?')) {
                    $.ajax({
                        url: '<?= base_url('participants/delete/') ?>' + participantId,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('Participant deleted successfully');
                                participantsTable.ajax.reload();
                            } else {
                                alert('Failed to delete participant: ' + (response.message || 'Unknown error'));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            alert('An error occurred while trying to delete the participant');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>