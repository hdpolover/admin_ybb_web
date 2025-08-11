<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Ambassadors')); ?>

    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

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
                                        <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addAmbassadorModal">
                                            <i class="ri-add-line align-middle me-1"></i> Add New Ambassador
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
                                                    placeholder="Search by name, email, phone, institution, referral code..."
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
                                                <option value="name-asc">Name (A-Z)</option>
                                                <option value="name-desc">Name (Z-A)</option>
                                                <option value="referral_count-desc" selected>Most Referrals</option>
                                                <option value="referral_count-asc">Fewest Referrals</option>
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

    <!-- New Ambassador Modal -->
    <div class="modal fade" id="addAmbassadorModal" tabindex="-1" aria-labelledby="addAmbassadorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAmbassadorModalLabel">Add New Ambassador</h5>
                    <button type="button" class="btn-close btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <i class="ri-information-line me-2"></i>
                        <strong>Note:</strong> Referral code will be generated automatically. You don't need to input it manually.
                    </div>
                    <form id="addAmbassadorForm" method="post" action="/users/ambassadors/create">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-4">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number">
                            </div>
                            <div class="col-md-4">
                                <label for="institution" class="form-label">Institution/University</label>
                                <input type="text" class="form-control" id="institution" name="institution">
                            </div>
                            <div class="col-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addAmbassadorForm" class="btn btn-primary">Create Ambassador</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Ambassador Modal -->
    <div class="modal fade" id="editAmbassadorModal" tabindex="-1" aria-labelledby="editAmbassadorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAmbassadorModalLabel">Edit Ambassador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAmbassadorForm" method="post">
                        <input type="hidden" id="edit_ambassador_id" name="id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_gender" class="form-label">Gender</label>
                                <select class="form-select" id="edit_gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_phone_number" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="edit_phone_number" name="phone_number">
                            </div>
                            <div class="col-md-4">
                                <label for="edit_institution" class="form-label">Institution/Company</label>
                                <input type="text" class="form-control" id="edit_institution" name="institution">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_ref_code" class="form-label">Referral Code</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="edit_ref_code" name="ref_code" readonly>
                                </div>
                                <div class="form-text text-muted mt-1">
                                    <i class="ri-information-line me-1"></i> Referral code cannot be changed
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                    <option value="2">Suspended</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="edit_notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editAmbassadorForm" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener to the "Add New Ambassador" button to open the modal
            document.querySelectorAll('[href="<?= site_url('ambassadors/new') ?>"]').forEach(function(element) {
                element.addEventListener('click', function(e) {
                    e.preventDefault();
                    var modal = new bootstrap.Modal(document.getElementById('addAmbassadorModal'));
                    modal.show();
                });
            });

            // Form submission handling
            document.getElementById('addAmbassadorForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch(this.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Ambassador created successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reload the table
                                $('#ambassadors-datatable').DataTable().ajax.reload();
                                // Close the modal
                                bootstrap.Modal.getInstance(document.getElementById('addAmbassadorModal')).hide();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to create ambassador',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while processing your request',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
            });
        });
    </script>

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
                },
                columns: [{
                        data: null,
                        name: 'row_number',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            // Using DataTables' row counter for sequential numbering
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'details',
                        name: 'name',
                        orderable: true,
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
                        name: 'ref_code',
                        orderable: true,
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
                    }, 
                    {
                        data: 'referral_count',
                        name: 'referral_count',
                        orderable: true,
                        render: function(data, type, row) {
                            // For sorting, just return the number
                            if (type === 'sort' || type === 'type') {
                                return data || 0;
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
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [3, 'desc'] // Sort by referral count (column 3) in descending order by default
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
                    case 'created_at':
                        return 0; // Maps to # column (row number, but we can use ID for sorting)
                    case 'full_name':
                    case 'name':
                        return 1; // Maps to Details column
                    case 'ref_code':
                        return 2; // Maps to Referral Code column
                    case 'referral_count':
                        return 3; // Maps to Referrals column
                    default:
                        return 0; // Default to first column
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

            // Optional: Auto-search with debounce for better UX
            let searchTimeout;
            $('#search-box').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    performSearch();
                }, 500); // 500ms debounce
            });

            // Handle filter change events
            $('#filter-status').on('change', function() {
                ambassadorsTable.ajax.reload();
            });

            $('#sort-by').on('change', function() {
                ambassadorsTable.ajax.reload();
            });

            // Handle filter buttons
            document.getElementById('apply-filters').addEventListener('click', function() {
                ambassadorsTable.ajax.reload();
            });

            document.getElementById('reset-filters').addEventListener('click', function() {
                // Reset all filter select values
                document.getElementById('filter-status').value = '';
                document.getElementById('search-box').value = '';
                document.getElementById('sort-by').value = 'referral_count-desc';

                // Reload the table with reset filters
                ambassadorsTable.search('').draw(); // Clear the search
                ambassadorsTable.ajax.reload();
            }); // Handle delete ambassador
            $(document).on('click', '.delete-ambassador', function() {
                var ambassadorId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This ambassador will be deactivated and removed from the system. You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                    cancelButtonClass: 'btn btn-danger w-xs mt-2',
                    confirmButtonText: 'Yes, deactivate it!',
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '<?= base_url('users/ambassadors/delete/') ?>' + ambassadorId,
                            type: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Deactivated!',
                                        text: 'Ambassador has been deactivated successfully.',
                                        icon: 'success',
                                        customClass: {
                                            confirmButton: 'btn btn-primary w-xs mt-2',
                                        },
                                        buttonsStyling: false
                                    }).then(function() {
                                        // Check if page refresh is requested
                                        if (response.refresh_page) {
                                            window.location.reload();
                                        } else {
                                            ambassadorsTable.ajax.reload();
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.message || 'Failed to deactivate ambassador',
                                        icon: 'error',
                                        customClass: {
                                            confirmButton: 'btn btn-primary w-xs mt-2',
                                        },
                                        buttonsStyling: false
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'An error occurred while trying to deactivate the ambassador',
                                    icon: 'error',
                                    customClass: {
                                        confirmButton: 'btn btn-primary w-xs mt-2',
                                    },
                                    buttonsStyling: false
                                });
                            }
                        });
                    }
                });
            });

            // Handle editing ambassador (initialization from row)
            $(document).on('click', '.btn-soft-warning', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                var ambassadorId = url.substring(url.lastIndexOf('/') + 1);

                // Fetch ambassador data via AJAX
                $.ajax({
                    url: '<?= site_url('users/ambassadors/getAmbassadorData') ?>/' + ambassadorId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var ambassador = response.data; // Populate the form fields
                            $('#edit_ambassador_id').val(ambassador.id);
                            $('#edit_name').val(ambassador.name);
                            $('#edit_email').val(ambassador.email);
                            $('#edit_phone_number').val(ambassador.phone_number || '');
                            $('#edit_institution').val(ambassador.institution || '');
                            $('#edit_ref_code').val(ambassador.ref_code);
                            $('#edit_status').val(ambassador.is_active);
                            $('#edit_gender').val(ambassador.gender || '');
                            $('#edit_notes').val(ambassador.notes || '');

                            // Show the modal
                            var editModal = new bootstrap.Modal(document.getElementById('editAmbassadorModal'));
                            editModal.show();
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'Failed to retrieve ambassador data',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while retrieving ambassador data',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            // Handle edit from view modal
            $('.edit-from-view').on('click', function() {
                var ambassadorId = $(this).attr('data-id');

                // Close view modal
                bootstrap.Modal.getInstance(document.getElementById('viewAmbassadorModal')).hide();

                // Trigger edit action with the same ID
                $.ajax({
                    url: '<?= site_url('ambassadors/getAmbassadorData') ?>/' + ambassadorId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var ambassador = response.data;

                            // Populate the form fields
                            $('#edit_ambassador_id').val(ambassador.id);
                            $('#edit_name').val(ambassador.name);
                            $('#edit_email').val(ambassador.email);
                            $('#edit_phone_number').val(ambassador.phone_number || '');
                            $('#edit_institution').val(ambassador.institution || '');
                            $('#edit_ref_code').val(ambassador.ref_code);
                            $('#edit_status').val(ambassador.is_active);
                            $('#edit_notes').val(ambassador.notes || '');

                            // make the referral code field readonly
                            $('#edit_ref_code').attr('readonly', true);

                            // Show the edit modal
                            var editModal = new bootstrap.Modal(document.getElementById('editAmbassadorModal'));
                            editModal.show();
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'Failed to retrieve ambassador data',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    }
                });
            });

            // Handle copy referral code
            $(document).on('click', '.copy-code', function() {
                var code = $(this).data('code');
                navigator.clipboard.writeText(code).then(function() {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Referral code copied to clipboard',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }, function() {
                    // Fallback for older browsers
                    var textarea = document.createElement('textarea');
                    textarea.value = code;
                    textarea.setAttribute('readonly', '');
                    textarea.style.position = 'absolute';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);

                    Swal.fire({
                        title: 'Success!',
                        text: 'Referral code copied to clipboard',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                });
            }); // Handle edit ambassador form submission
            $('#editAmbassadorForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                $.ajax({
                    url: '<?= site_url('users/ambassadors/update') ?>',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Ambassador updated successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Refresh the table
                                $('#ambassadors-datatable').DataTable().ajax.reload();
                                // Close the modal
                                bootstrap.Modal.getInstance(document.getElementById('editAmbassadorModal')).hide();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'Failed to update ambassador',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while processing your request',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>

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
</body>

</html>