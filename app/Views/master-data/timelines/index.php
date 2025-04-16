<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Program Timelines')); ?> <!-- DataTables css -->
    <link href="/assets/libs/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/libs/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/libs/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <?= $this->include('partials/head-css') ?>

    <style>
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

        #program-schedules-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Program Timelines')); ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Program Timelines</h4>
                                    <div class="flex-shrink-0">
                                        <?php if ($program): ?>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-schedule-modal">
                                                <i class="ri-add-line align-bottom me-1"></i> Add Timeline
                                            </button>
                                        <?php else: ?>
                                            <div class="alert alert-warning mb-0">
                                                <i class="ri-error-warning-line me-1 align-middle"></i>
                                                Please select a program first
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive table-card">
                                        <table id="program-schedules-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col" style="width: 50px;">#</th>
                                                    <th scope="col">Name</th>
                                                    <th scope="col">Start Date</th>
                                                    <th scope="col">End Date</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (isset($schedules) && is_array($schedules)) : ?>
                                                    <?php foreach ($schedules as $index => $schedule) : ?>
                                                        <tr>
                                                            <td><?= $index + 1 ?></td>
                                                            <td><?= esc($schedule->name) ?></td>
                                                            <td>
                                                                <?php if ($schedule->start_date): ?>
                                                                    <?= date('M d, Y', strtotime($schedule->start_date)) ?>
                                                                <?php else: ?>
                                                                    N/A
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($schedule->end_date): ?>
                                                                    <?= date('M d, Y', strtotime($schedule->end_date)) ?>
                                                                <?php else: ?>
                                                                    N/A
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($schedule->is_active == 1): ?>
                                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                                <?php endif; ?>
                                                            </td>                                                            <td>
                                                                <div class="d-flex gap-2">
                                                                    <div class="view">
                                                                        <button type="button" class="btn btn-sm btn-info view-schedule" data-id="<?= $schedule->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                                                            <i class="ri-eye-fill"></i>
                                                                        </button>
                                                                    </div>
                                                                    <div class="edit">
                                                                        <button type="button" class="btn btn-sm btn-success edit-schedule" data-id="<?= $schedule->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                                            <i class="ri-pencil-fill"></i>
                                                                        </button>
                                                                    </div>
                                                                    <div class="remove">
                                                                        <button type="button" class="btn btn-sm btn-danger delete-schedule" data-id="<?= $schedule->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
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
            </div>

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- jQuery first (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>    <!-- DataTables js -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <!-- View Schedule Modal -->
    <div class="modal fade" id="view-schedule-modal" tabindex="-1" aria-labelledby="view-schedule-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-loading" id="view-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="view-schedule-modal-label">Timeline Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-semibold">Name</h6>
                                <p id="view_name">Loading...</p>
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
                                <h6 class="fw-semibold">Start Date</h6>
                                <p id="view_start_date">Loading...</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-semibold">End Date</h6>
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
    </div>

    <!-- Add Schedule Modal -->
    <div class="modal fade" id="add-schedule-modal" tabindex="-1" aria-labelledby="add-schedule-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-schedule-modal-label">Add New Timeline</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/timelines/create" method="post" id="add-schedule-form">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Timeline Name*</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <div class="invalid-feedback">Please enter a timeline name.</div>
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
                                    <label for="start_date" class="form-label">Start Date*</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                    <div class="invalid-feedback">Please select a start date.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date*</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                    <div class="invalid-feedback">Please select an end date.</div>
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
                        <button type="submit" class="btn btn-primary">Add Timeline</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div class="modal fade" id="edit-schedule-modal" tabindex="-1" aria-labelledby="edit-schedule-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-loading" id="edit-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="edit-schedule-modal-label">Edit Timeline</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/timelines/update/" method="post" id="edit-schedule-form">
                    <div class="modal-body">
                        <input type="hidden" id="edit_schedule_id" name="id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Timeline Name*</label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                    <div class="invalid-feedback">Please enter a timeline name.</div>
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
                                    <label for="edit_start_date" class="form-label">Start Date*</label>
                                    <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                                    <div class="invalid-feedback">Please select a start date.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_end_date" class="form-label">End Date*</label>
                                    <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                                    <div class="invalid-feedback">Please select an end date.</div>
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
                        <button type="submit" class="btn btn-success">Update Timeline</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Schedule Modal -->
    <div class="modal fade" id="delete-schedule-modal" tabindex="-1" aria-labelledby="delete-schedule-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-schedule-modal-label">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this timeline? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" class="btn btn-danger" id="confirm-delete-btn">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom JavaScript -->
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
                initializeScheduleFunctions();
            } else {
                console.error("jQuery is not loaded!");
            }
        });

        function initializeScheduleFunctions() {            // Initialize DataTable with improved configuration
            var scheduleTable = $('#program-schedules-table').DataTable({
                responsive: true,
                lengthChange: true,
                pageLength: 10,
                searching: true,
                ordering: true,
                dom: '<"row mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: false,
                columnDefs: [{
                    orderable: false,
                    targets: [5] // Action column is not sortable (index changed to 5 since we removed description)
                }, {
                    targets: 0, // Row number column
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    width: '50px'
                }],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    emptyTable: "No timeline events found",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    },
                    lengthMenu: "_MENU_ entries per page"
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    // Initialize tooltips
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });
                },
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    emptyTable: "No timeline events found",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        previous: "<i class='ri-arrow-left-s-line'>",
                        next: "<i class='ri-arrow-right-s-line'>"
                    },
                    lengthMenu: "_MENU_ entries per page"
                }
            });

            // Connect custom search box if exists
            $('.search').keyup(function() {
                scheduleTable.search($(this).val()).draw();
            });            // Use event delegation for view button
            $(document).on('click', '.view-schedule', function(e) {
                e.preventDefault();
                
                var scheduleId = $(this).data('id');
                console.log("View button clicked for ID:", scheduleId);

                // Show modal first
                $('#view-schedule-modal').modal('show');
                $('#view-loading').show();

                // Get schedule details
                $.ajax({
                    url: '/master-data/timelines/getSchedule/' + scheduleId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("View Ajax response:", response);
                        $('#view-loading').hide();
                        
                        if (response && response.data) {
                            var schedule = response.data;

                            // Populate modal
                            $('#view_name').text(schedule.name || 'N/A');

                            // Format dates
                            var startDate = schedule.start_date ?
                                new Date(schedule.start_date).toLocaleDateString('en-US', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric'
                                }) : 'N/A';
                            var endDate = schedule.end_date ?
                                new Date(schedule.end_date).toLocaleDateString('en-US', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric'
                                }) : 'N/A';

                            $('#view_start_date').text(startDate);
                            $('#view_end_date').text(endDate);
                            $('#view_description').text(schedule.description || 'No description provided');

                            // Format status with badge
                            var statusBadge = schedule.is_active == 1 ?
                                '<span class="badge bg-success-subtle text-success">Active</span>' :
                                '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
                            $('#view_status').html(statusBadge);

                            // Set schedule ID for the edit button in view modal
                            $('.view-edit-btn').data('id', schedule.id);
                        } else {
                            console.error("Invalid response:", response);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load timeline details',
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
                            text: 'An error occurred while fetching timeline details',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                });
            });            // Handle edit button click
            $(document).on('click', '.edit-schedule', function(e) {
                e.preventDefault();
                
                var scheduleId = $(this).data('id');
                console.log("Edit button clicked for ID:", scheduleId);

                // Show modal first
                $('#edit-schedule-modal').modal('show');
                $('#edit-loading').show();

                // Get schedule details
                $.ajax({
                    url: '/master-data/timelines/getSchedule/' + scheduleId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#edit-loading').hide();

                        if (response && response.data) {
                            var schedule = response.data;

                            // Set form action URL with schedule ID
                            $('#edit-schedule-form').attr('action', '/master-data/timelines/update/' + schedule.id);

                            // Populate form
                            $('#edit_schedule_id').val(schedule.id);
                            $('#edit_name').val(schedule.name);
                            $('#edit_order_number').val(schedule.order_number);

                            // Format dates for date input (yyyy-mm-dd)
                            if (schedule.start_date) {
                                var startDate = new Date(schedule.start_date);
                                var formattedStartDate = startDate.getFullYear() + '-' +
                                    String(startDate.getMonth() + 1).padStart(2, '0') + '-' +
                                    String(startDate.getDate()).padStart(2, '0');
                                $('#edit_start_date').val(formattedStartDate);
                            }

                            if (schedule.end_date) {
                                var endDate = new Date(schedule.end_date);
                                var formattedEndDate = endDate.getFullYear() + '-' +
                                    String(endDate.getMonth() + 1).padStart(2, '0') + '-' +
                                    String(endDate.getDate()).padStart(2, '0');
                                $('#edit_end_date').val(formattedEndDate);
                            }

                            $('#edit_description').val(schedule.description);
                            $('#edit_is_active').val(schedule.is_active);
                        } else {
                            console.error("Invalid response:", response);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load timeline details for editing',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                            $('#edit-schedule-modal').modal('hide');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#edit-loading').hide();
                        console.error("Edit Ajax error:", xhr.responseText);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while fetching timeline details',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                        $('#edit-schedule-modal').modal('hide');
                    }
                });
            });

            // Handle delete button click
            $(document).on('click', '.delete-schedule', function(e) {
                e.preventDefault();
                
                var scheduleId = $(this).data('id');
                console.log("Delete button clicked for ID:", scheduleId);

                // Set delete URL and show confirmation modal
                $('#confirm-delete-btn').attr('href', '/master-data/timelines/delete/' + scheduleId);
                $('#delete-schedule-modal').modal('show');
            });

            // Handle click on edit button in view modal
            $(document).on('click', '.view-edit-btn', function() {
                var scheduleId = $(this).data('id');
                $('#view-schedule-modal').modal('hide');

                // Wait for view modal to close before opening edit modal
                setTimeout(function() {
                    $('.edit-schedule[data-id="' + scheduleId + '"]').trigger('click');
                }, 500);
            });

            // Form validation for add schedule form
            $('#add-schedule-form').on('submit', function(e) {
                if ($(this)[0].checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Show SweetAlert for validation errors
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                }
                $(this).addClass('was-validated');
            });

            // Form validation for edit schedule form
            $('#edit-schedule-form').on('submit', function(e) {
                if ($(this)[0].checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Show SweetAlert for validation errors
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                }
                $(this).addClass('was-validated');
            });
        }
    </script>
</body>

</html>