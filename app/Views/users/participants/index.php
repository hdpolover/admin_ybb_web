<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Participants')); ?>

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
                                                </span>
                                                <input type="text" id="search-box" class="form-control border-start-0 ps-0"
                                                    placeholder="Search by name, email, phone..."
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
                                        <div class="col-md-3 d-flex align-items-end mb-2">
                                            <button id="apply-filters" class="btn btn-primary me-2">Apply Filters</button>
                                            <button id="reset-filters" class="btn btn-light">Reset</button>
                                        </div>
                                    </div>

                                    <table id="participants-datatable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Registration Date</th>
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
                        // d.category = $('#filter-category').val();
                        d.search.value = $('#search-box').val(); // Add search term
                        return d;
                    }
                },
                columns: [{
                        data: 'name',
                        render: function(data, type, row) {
                            if (!data) return 'N/A';
                            return '<div class="d-flex align-items-center">' +
                                '<div class="avatar-xs me-2">' +
                                '<span class="avatar-title rounded-circle bg-soft-primary text-primary">' +
                                data.first_letter +
                                '</span>' +
                                '</div>' +
                                data.full_name +
                                '</div>';
                        }
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'phone'
                    },
                    {
                        data: 'registration_date'
                    },
                    {
                        data: 'category'
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [3, 'desc'] // Order by registration date
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
                document.getElementById('filter-status').value = '';
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