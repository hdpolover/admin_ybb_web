<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Program Rundowns')); ?>

    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>    <style>
        .description-cell {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .modal-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }

        /* Improve table responsiveness */
        .table-responsive.table-card {
            border-radius: 0.25rem;
            box-shadow: 0 1px 2px rgba(56, 65, 74, 0.15);
        }

        #program-rundowns-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }
        
        /* SweetAlert customizations */
        .swal2-popup {
            font-size: 0.875rem;
        }
        
        .swal2-actions {
            margin-top: 1.5rem;
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Program Rundowns')); ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">                                    <h4 class="card-title mb-0 flex-grow-1">Program Rundowns</h4>
                                    <div class="flex-shrink-0">
                                        <?php if ($program): ?>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-rundown-modal">
                                                <i class="ri-add-line align-bottom me-1"></i> Add Rundown
                                            </button>
                                        <?php else: ?>
                                            <div class="alert alert-warning mb-0">
                                                <i class="ri-error-warning-line me-1 align-middle"></i>
                                                Please select a program first
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card-body">                                    <table id="program-rundowns-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Title</th>
                                                <th scope="col">Start Time</th>
                                                <th scope="col">End Time</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (isset($rundowns) && is_array($rundowns)) : ?>
                                                <?php foreach ($rundowns as $index => $rundown) : ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td><?= esc($rundown->title) ?></td>
                                                        <td>
                                                            <?php if ($rundown->start_date): ?>
                                                                <?= date('M d, Y - H:i', strtotime($rundown->start_date)) ?>
                                                            <?php else: ?>
                                                                N/A
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($rundown->end_date): ?>
                                                                <?= date('M d, Y - H:i', strtotime($rundown->end_date)) ?>
                                                            <?php else: ?>
                                                                N/A
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($rundown->is_active == 1): ?>
                                                                <span class="badge bg-success-subtle text-success">Active</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <div class="view">
                                                                    <button type="button" class="btn btn-sm btn-info view-rundown" data-id="<?= $rundown->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                                                        <i class="ri-eye-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="edit">
                                                                    <button type="button" class="btn btn-sm btn-success edit-rundown" data-id="<?= $rundown->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                                        <i class="ri-pencil-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="remove">
                                                                    <button type="button" class="btn btn-sm btn-danger delete-rundown" data-id="<?= $rundown->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                                        <i class="ri-delete-bin-fill"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->    <!-- View Rundown Modal -->
    <div class="modal fade" id="view-rundown-modal" tabindex="-1" aria-labelledby="view-rundown-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-loading" id="view-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="view-rundown-modal-label">Rundown Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-semibold">Title</h6>
                                <p id="view_title">Loading...</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-semibold">Status</h6>
                                <p id="view_status">Loading...</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-semibold">Start Time</h6>
                                <p id="view_start_date">Loading...</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-semibold">End Time</h6>
                                <p id="view_end_date">Loading...</p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-semibold">Description</h6>
                        <p id="view_description">Loading...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success view-edit-btn" data-id="">Edit</button>
                </div>
            </div>
        </div>
    </div>    <!-- Add Rundown Modal -->
    <div class="modal fade" id="add-rundown-modal" tabindex="-1" aria-labelledby="add-rundown-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-rundown-modal-label">Add New Rundown</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/program-rundowns/create" method="post" id="add-rundown-form">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Rundown Title*</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                    <div class="invalid-feedback">Please enter a rundown title.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order_number" class="form-label">Order Number*</label>
                                    <input type="number" class="form-control" id="order_number" name="order_number" min="0" value="0" required>
                                    <div class="invalid-feedback">Please enter an order number.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Time*</label>
                                    <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                                    <div class="invalid-feedback">Please select a start time.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Time*</label>
                                    <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                                    <div class="invalid-feedback">Please select an end time.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description*</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            <div class="invalid-feedback">Please provide a description.</div>
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status*</label>
                            <select class="form-select" id="is_active" name="is_active" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Rundown</button>
                    </div>
                </form>
            </div>
        </div>
    </div>    <!-- Edit Rundown Modal -->
    <div class="modal fade" id="edit-rundown-modal" tabindex="-1" aria-labelledby="edit-rundown-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-loading" id="edit-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="edit-rundown-modal-label">Edit Rundown</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/program-rundowns/update/" method="post" id="edit-rundown-form">
                    <div class="modal-body">
                        <input type="hidden" id="edit_rundown_id" name="id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_title" class="form-label">Rundown Title*</label>
                                    <input type="text" class="form-control" id="edit_title" name="title" required>
                                    <div class="invalid-feedback">Please enter a rundown title.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_order_number" class="form-label">Order Number*</label>
                                    <input type="number" class="form-control" id="edit_order_number" name="order_number" min="0" required>
                                    <div class="invalid-feedback">Please enter an order number.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_start_date" class="form-label">Start Time*</label>
                                    <input type="datetime-local" class="form-control" id="edit_start_date" name="start_date" required>
                                    <div class="invalid-feedback">Please select a start time.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_end_date" class="form-label">End Time*</label>
                                    <input type="datetime-local" class="form-control" id="edit_end_date" name="end_date" required>
                                    <div class="invalid-feedback">Please select an end time.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description*</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                            <div class="invalid-feedback">Please provide a description.</div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_is_active" class="form-label">Status*</label>
                            <select class="form-select" id="edit_is_active" name="is_active" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update Rundown</button>
                    </div>
                </form>
            </div>
        </div>
    </div>    <!-- Delete Rundown Modal -->
    <div class="modal fade" id="delete-rundown-modal" tabindex="-1" aria-labelledby="delete-rundown-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-rundown-modal-label">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this rundown? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" class="btn btn-danger" id="confirm-delete-btn">Delete</a>
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
    <script src="/assets/js/app.js"></script>    <!-- Custom JavaScript -->
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM loaded");

            // Check for flash messages
            <?php if (session()->has('success')): ?>
                Swal.fire({
                    title: 'Success!',
                    text: '<?= session('success') ?>',
                    icon: 'success',
                    confirmButtonColor: '#0ab39c'
                });
            <?php endif; ?>

            <?php if (session()->has('error')): ?>
                Swal.fire({
                    title: 'Error!',
                    text: '<?= session('error') ?>',
                    icon: 'error',
                    confirmButtonColor: '#f06548'
                });
            <?php endif; ?>

            // Ensure jQuery is loaded
            if (typeof jQuery !== 'undefined') {
                console.log("jQuery is loaded");
                initializeRundownFunctions();
            } else {
                console.error("jQuery is not loaded!");
            }
        });

        function initializeRundownFunctions() {
            // Initialize DataTable with improved configuration
            var rundownTable = $('#program-rundowns-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true,
                columnDefs: [{
                    orderable: false,
                    targets: [5] // Action column is not sortable
                }],
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-squared justify-content-end mb-0");
                    // Initialize tooltips
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });
                }
            });

            // Use event delegation for view button
            $(document).on('click', '.view-rundown', function(e) {
                e.preventDefault();

                var rundownId = $(this).data('id');
                console.log("View button clicked for ID:", rundownId);

                // Show modal first
                $('#view-rundown-modal').modal('show');
                $('#view-loading').show();

                // Get rundown details
                $.ajax({
                    url: '/master-data/program-rundowns/getRundown/' + rundownId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("View Ajax response:", response);
                        $('#view-loading').hide();

                        if (response && response.data) {
                            var rundown = response.data;

                            // Populate modal
                            $('#view_title').text(rundown.title || 'N/A');

                            // Format dates with time
                            var startDate = rundown.start_date ?
                                new Date(rundown.start_date).toLocaleString('en-US', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) : 'N/A';
                            var endDate = rundown.end_date ?
                                new Date(rundown.end_date).toLocaleString('en-US', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) : 'N/A';

                            $('#view_start_date').text(startDate);
                            $('#view_end_date').text(endDate);
                            $('#view_description').text(rundown.description || 'No description provided');

                            // Format status with badge
                            var statusBadge = rundown.is_active == 1 ?
                                '<span class="badge bg-success-subtle text-success">Active</span>' :
                                '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
                            $('#view_status').html(statusBadge);

                            // Set rundown ID for the edit button in view modal
                            $('.view-edit-btn').data('id', rundown.id);
                        } else {
                            console.error("Invalid response:", response);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load rundown details',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("View Ajax error:", xhr.responseText);
                        $('#view-loading').hide();
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while fetching rundown details',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                });
            });

            // Handle edit button click
            $(document).on('click', '.edit-rundown', function(e) {
                e.preventDefault();

                var rundownId = $(this).data('id');
                console.log("Edit button clicked for ID:", rundownId);

                // Show modal first
                $('#edit-rundown-modal').modal('show');
                $('#edit-loading').show();                // Get rundown details
                $.ajax({
                    url: '/master-data/program-rundowns/getRundown/' + rundownId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#edit-loading').hide();
                        console.log("Edit Ajax response:", response);

                        if (response && response.data) {
                            var rundown = response.data;
                            console.log("Rundown data:", rundown);
                            console.log("Start date formatted:", rundown.start_date_formatted);
                            console.log("End date formatted:", rundown.end_date_formatted);

                            // Set form action URL with rundown ID
                            $('#edit-rundown-form').attr('action', '/master-data/program-rundowns/update/' + rundown.id);

                            // Populate form
                            $('#edit_rundown_id').val(rundown.id);
                            $('#edit_title').val(rundown.title);
                            $('#edit_order_number').val(rundown.order_number);                            // Use pre-formatted dates from the server if available
                            if (rundown.start_date_formatted) {
                                console.log('Using server formatted start date:', rundown.start_date_formatted);
                                $('#edit_start_date').val(rundown.start_date_formatted);
                            } else if (rundown.start_date) {
                                console.log('Using client-side formatting for start date:', rundown.start_date);
                                // Fallback to client-side formatting
                                try {
                                    // Replace any potential timezone offset issues
                                    var startDateStr = rundown.start_date.replace(' ', 'T');
                                    var startDate = new Date(startDateStr);
                                    
                                    if (isNaN(startDate.getTime())) {
                                        // Try another format
                                        var parts = rundown.start_date.split(/[- :]/);
                                        // parts[0] = year, parts[1] = month, parts[2] = day, parts[3] = hour, parts[4] = minute, parts[5] = second
                                        startDate = new Date(parts[0], parts[1]-1, parts[2], parts[3], parts[4], parts[5]);
                                    }
                                    
                                    if (!isNaN(startDate.getTime())) {
                                        // Format as YYYY-MM-DDTHH:MM
                                        var formattedStartDate = startDate.getFullYear() + '-' +
                                            String(startDate.getMonth() + 1).padStart(2, '0') + '-' +
                                            String(startDate.getDate()).padStart(2, '0') + 'T' +
                                            String(startDate.getHours()).padStart(2, '0') + ':' +
                                            String(startDate.getMinutes()).padStart(2, '0');
                                            
                                        console.log('Client formatted start date:', formattedStartDate);
                                        $('#edit_start_date').val(formattedStartDate);
                                    } else {
                                        console.error('Invalid date format for start_date:', rundown.start_date);
                                    }
                                } catch (e) {
                                    console.error('Error formatting start_date:', e);
                                }
                            }

                            if (rundown.end_date_formatted) {
                                console.log('Using server formatted end date:', rundown.end_date_formatted);
                                $('#edit_end_date').val(rundown.end_date_formatted);
                            } else if (rundown.end_date) {
                                console.log('Using client-side formatting for end date:', rundown.end_date);
                                // Fallback to client-side formatting
                                try {
                                    // Replace any potential timezone offset issues
                                    var endDateStr = rundown.end_date.replace(' ', 'T');
                                    var endDate = new Date(endDateStr);
                                    
                                    if (isNaN(endDate.getTime())) {
                                        // Try another format
                                        var parts = rundown.end_date.split(/[- :]/);
                                        // parts[0] = year, parts[1] = month, parts[2] = day, parts[3] = hour, parts[4] = minute, parts[5] = second
                                        endDate = new Date(parts[0], parts[1]-1, parts[2], parts[3], parts[4], parts[5]);
                                    }
                                    
                                    if (!isNaN(endDate.getTime())) {
                                        // Format as YYYY-MM-DDTHH:MM
                                        var formattedEndDate = endDate.getFullYear() + '-' +
                                            String(endDate.getMonth() + 1).padStart(2, '0') + '-' +
                                            String(endDate.getDate()).padStart(2, '0') + 'T' +
                                            String(endDate.getHours()).padStart(2, '0') + ':' +
                                            String(endDate.getMinutes()).padStart(2, '0');
                                            
                                        console.log('Client formatted end date:', formattedEndDate);
                                        $('#edit_end_date').val(formattedEndDate);
                                    } else {
                                        console.error('Invalid date format for end_date:', rundown.end_date);
                                    }
                                } catch (e) {
                                    console.error('Error formatting end_date:', e);
                                }
                            }

                            $('#edit_description').val(rundown.description);
                            $('#edit_is_active').val(rundown.is_active);
                        } else {
                            console.error("Invalid response:", response);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load rundown details for editing',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                            $('#edit-rundown-modal').modal('hide');
                        }
                    },                    error: function(xhr, status, error) {
                        $('#edit-loading').hide();
                        console.error("Edit Ajax error. Status:", status);
                        console.error("Error:", error);
                        console.error("Response text:", xhr.responseText);
                        
                        let errorMsg = 'An error occurred while fetching rundown details';
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse && errorResponse.message) {
                                errorMsg = errorResponse.message;
                            }
                        } catch (e) {
                            console.error("Error parsing error response:", e);
                        }
                        
                        Swal.fire({
                            title: 'Error!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                        $('#edit-rundown-modal').modal('hide');
                    }
                });
            });

            // Handle delete button click
            $(document).on('click', '.delete-rundown', function(e) {
                e.preventDefault();

                var rundownId = $(this).data('id');
                console.log("Delete button clicked for ID:", rundownId);

                // Show SweetAlert confirmation instead of modal
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this deletion!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!',
                    confirmButtonColor: '#f06548',
                    cancelButtonColor: '#74788d',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait while we delete the rundown.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                                // Send AJAX delete request
                                $.ajax({
                                    url: '/master-data/program-rundowns/delete/' + rundownId,
                                    type: 'GET',
                                    success: function(response) {
                                        // Show success message
                                        Swal.fire({
                                            title: 'Deleted!',
                                            text: 'The rundown has been deleted successfully.',
                                            icon: 'success',
                                            confirmButtonColor: '#0ab39c'
                                        }).then(() => {
                                            // Reload the page to refresh the table
                                            location.reload();
                                        });
                                    },
                                    error: function(xhr, status, error) {
                                        console.error("Delete Ajax error:", xhr.responseText);
                                        Swal.fire({
                                            title: 'Error!',
                                            text: 'Failed to delete the rundown. Please try again.',
                                            icon: 'error',
                                            confirmButtonColor: '#f06548'
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            });

            // Handle click on edit button in view modal
            $(document).on('click', '.view-edit-btn', function() {
                var rundownId = $(this).data('id');
                $('#view-rundown-modal').modal('hide');

                // Wait for view modal to close before opening edit modal
                setTimeout(function() {
                    $('.edit-rundown[data-id="' + rundownId + '"]').trigger('click');
                }, 500);
            });            // Form validation and submission with SweetAlert for add rundown form
            $('#add-rundown-form').on('submit', function(e) {
                e.preventDefault();
                
                if ($(this)[0].checkValidity() === false) {
                    e.stopPropagation();
                    $(this).addClass('was-validated');
                    
                    // Show SweetAlert for validation errors
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    return;
                }
                
                // Additional date validation
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                
                if (!startDate || !endDate) {
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Both start and end dates are required.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    return;
                }
                
                // Check if end date is after start date
                if (new Date(endDate) <= new Date(startDate)) {
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'End date must be after start date.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    return;
                }
                
                $(this).addClass('was-validated');
                
                // Show loading state
                Swal.fire({
                    title: 'Creating Rundown...',
                    text: 'Please wait while we process your request.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                        
                        // Get form data
                        var formData = $(this).serialize();
                        
                        // Send AJAX request
                        $.ajax({
                            url: $(this).attr('action'),
                            type: 'POST',
                            data: formData,
                            dataType: 'json',
                            success: function(response) {
                                $('#add-rundown-modal').modal('hide');
                                
                                if (response && response.success) {
                                    // Reset form
                                    $('#add-rundown-form')[0].reset();
                                    $('#add-rundown-form').removeClass('was-validated');
                                    
                                    // Show success message
                                    Swal.fire({
                                        title: 'Success!',
                                        text: response.message || 'Rundown created successfully.',
                                        icon: 'success',
                                        confirmButtonColor: '#0ab39c'
                                    }).then(() => {
                                        // Reload the page to refresh the table
                                        location.reload();
                                    });
                                } else {
                                    // Show error message
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.message || 'Failed to create the rundown.',
                                        icon: 'error',
                                        confirmButtonColor: '#f06548'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Create Ajax error:", xhr.responseText);
                                let errorMessage = 'Failed to create the rundown. Please try again.';
                                  try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response && response.message) {
                                        errorMessage = response.message;
                                    }
                                    
                                    // Check if we have specific field errors
                                    if (response && response.errors) {
                                        let errorDetails = [];
                                        for (const field in response.errors) {
                                            errorDetails.push(response.errors[field]);
                                        }
                                        if (errorDetails.length > 0) {
                                            errorMessage = errorDetails.join('<br>');
                                        }
                                    }
                                } catch (e) {
                                    // If validation errors were returned, display them
                                    if (xhr.responseText.includes('The') && xhr.responseText.includes('field is required')) {
                                        errorMessage = xhr.responseText;
                                    }
                                }
                                
                                Swal.fire({
                                    title: 'Error!',
                                    text: errorMessage,
                                    icon: 'error',
                                    confirmButtonColor: '#f06548'
                                });
                            }
                        });
                    }
                });
            });            // Form validation and submission with SweetAlert for edit rundown form
            $('#edit-rundown-form').on('submit', function(e) {
                e.preventDefault();
                
                if ($(this)[0].checkValidity() === false) {
                    e.stopPropagation();
                    $(this).addClass('was-validated');
                    
                    // Show SweetAlert for validation errors
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    return;
                }
                
                // Additional date validation
                const startDate = $('#edit_start_date').val();
                const endDate = $('#edit_end_date').val();
                
                if (!startDate || !endDate) {
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Both start and end dates are required.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    return;
                }
                
                // Check if end date is after start date
                if (new Date(endDate) <= new Date(startDate)) {
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'End date must be after start date.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    return;
                }
                
                $(this).addClass('was-validated');
                
                // Show loading state
                Swal.fire({
                    title: 'Updating Rundown...',
                    text: 'Please wait while we process your request.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                        
                        // Get form data
                        var formData = $(this).serialize();
                        
                        // Send AJAX request
                        $.ajax({
                            url: $(this).attr('action'),
                            type: 'POST',
                            data: formData,
                            dataType: 'json',
                            success: function(response) {
                                $('#edit-rundown-modal').modal('hide');
                                
                                if (response && response.success) {
                                    // Show success message
                                    Swal.fire({
                                        title: 'Success!',
                                        text: response.message || 'Rundown updated successfully.',
                                        icon: 'success',
                                        confirmButtonColor: '#0ab39c'
                                    }).then(() => {
                                        // Reload the page to refresh the table
                                        location.reload();
                                    });
                                } else {
                                    // Show error message
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.message || 'Failed to update the rundown.',
                                        icon: 'error',
                                        confirmButtonColor: '#f06548'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Update Ajax error:", xhr.responseText);
                                let errorMessage = 'Failed to update the rundown. Please try again.';
                                  try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response && response.message) {
                                        errorMessage = response.message;
                                    }
                                    
                                    // Check if we have specific field errors
                                    if (response && response.errors) {
                                        let errorDetails = [];
                                        for (const field in response.errors) {
                                            errorDetails.push(response.errors[field]);
                                        }
                                        if (errorDetails.length > 0) {
                                            errorMessage = errorDetails.join('<br>');
                                        }
                                    }
                                } catch (e) {
                                    console.error("Error parsing response:", e);
                                    // If the response is plain text, show it
                                    if (xhr.responseText) {
                                        errorMessage = xhr.responseText;
                                    }
                                }
                                
                                Swal.fire({
                                    title: 'Error!',
                                    html: errorMessage, // Using html instead of text to support HTML formatting
                                    icon: 'error',
                                    confirmButtonColor: '#f06548'
                                });
                            }
                        });
                    }
                });
            });
            
            // Initialize table searching, filtering, and sorting enhancement
            if ($.fn.DataTable.isDataTable('#program-rundowns-table')) {
                rundownTable.on('draw', function() {
                    // Reinitialize tooltips after table redraw
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                });
            }
        }
    </script>
</body>

</html>