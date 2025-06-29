<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Program Awards')); ?>
    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <style>
        .modal-loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 0.3rem;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .loading-text {
            margin-top: 1rem;
            color: #495057;
        }

        .award-type-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .table-loading {
            position: relative;
        }

        .table-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
            border-radius: 0.375rem;
        }

        .dataTables_processing {
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            width: auto !important;
            height: auto !important;
            margin: 0 !important;
            border: 0 !important;
            color: #0ab39c !important;
            font-size: 1rem !important;
            background: transparent !important;
        }
    </style>
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Program Awards')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Program Awards List</h5>
                                    <div class="flex-shrink-0">
                                        <button class="btn btn-primary waves-effect waves-light me-2" data-bs-toggle="modal" data-bs-target="#add-award-modal">
                                            <i class="ri-add-line align-middle me-1"></i> Add Award
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="program-awards-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Award Title</th>
                                                <th scope="col">Type</th>
                                                <th scope="col">Order</th>
                                                <th scope="col">Description</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be loaded via AJAX -->
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
    </div>

    <!-- Add Award Modal -->
    <div class="modal fade" id="add-award-modal" tabindex="-1" aria-labelledby="add-award-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-award-modal-label">Add New Award</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/program-awards/create" method="post" id="add-award-form">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Award Title*</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                    <div class="invalid-feedback">Please enter an award title.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="award_type" class="form-label">Award Type*</label>
                                    <select class="form-select" id="award_type" name="award_type" required>
                                        <option value="">Select Type</option>
                                        <option value="winner">Winner</option>
                                        <option value="runner_up">Runner Up</option>
                                        <option value="mention">Honorable Mention</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div class="invalid-feedback">Please select an award type.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description*</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            <div class="invalid-feedback">Please provide a description.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order_number" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="order_number" name="order_number" min="1" placeholder="1">
                                    <small class="text-muted">Order in which awards will be displayed</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status*</label>
                                    <select class="form-select" id="is_active" name="is_active" required>
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a status.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Award</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Award Modal -->
    <div class="modal fade" id="edit-award-modal" tabindex="-1" aria-labelledby="edit-award-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-loading" id="edit-loading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="loading-text">Loading award details...</div>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="edit-award-modal-label">Edit Award</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/program-awards/update/" method="post" id="edit-award-form">
                    <input type="hidden" id="edit_award_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="edit_title" class="form-label">Award Title*</label>
                                    <input type="text" class="form-control" id="edit_title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_award_type" class="form-label">Award Type*</label>
                                    <select class="form-select" id="edit_award_type" name="award_type" required>
                                        <option value="winner">Winner</option>
                                        <option value="runner_up">Runner Up</option>
                                        <option value="mention">Honorable Mention</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description*</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_order_number" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="edit_order_number" name="order_number" min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_is_active" class="form-label">Status*</label>
                                    <select class="form-select" id="edit_is_active" name="is_active" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Award</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Award Modal -->
    <div class="modal fade" id="view-award-modal" tabindex="-1" aria-labelledby="view-award-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-loading" id="view-loading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="loading-text">Loading award details...</div>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="view-award-modal-label">Award Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Award Title</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_title"></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Award Type</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_award_type"></p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h5 class="text-muted fw-normal">Description</h5>
                        <p class="text-dark fw-medium fs-15 mb-3" id="view_description"></p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Display Order</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_order_number"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Status</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_status"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary view-edit-btn">Edit</button>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="/assets/js/app.js"></script>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Check for flash messages
            <?php if (session()->has('success')): ?>
                Swal.fire({
                    title: 'Success!',
                    text: '<?= session('success') ?>',
                    icon: 'success',
                    confirmButtonColor: '#0ab39c',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => {
                    // Refresh the table if it exists
                    if (window.refreshAwardTable) {
                        window.refreshAwardTable();
                    }
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

            <?php if (session()->has('warning')): ?>
                Swal.fire({
                    title: 'Warning!',
                    text: '<?= session('warning') ?>',
                    icon: 'warning',
                    confirmButtonColor: '#f7b84b'
                });
            <?php endif; ?>

            if (typeof jQuery !== 'undefined') {
                initializeAwardFunctions();
            }
        });

        function initializeAwardFunctions() {
            // Initialize DataTable
            var awardTable = $('#program-awards-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true,
                processing: true,
                language: {
                    processing: '<i class="ri-loader-4-line fs-2x text-primary"></i><br><span class="text-muted">Loading awards...</span>',
                    emptyTable: "No awards found for this program",
                    zeroRecords: "No matching awards found"
                },
                ajax: {
                    url: '/master-data/program-awards/getData',
                    type: 'GET',
                    dataSrc: function(json) {
                        if (json.success) {
                            return json.data;
                        } else {
                            console.error('Error loading data:', json.message);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load award data: ' + (json.message || 'Unknown error'),
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                            return [];
                        }
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTable AJAX error:', error);
                        Swal.fire({
                            title: 'Connection Error!',
                            text: 'Failed to connect to server. Please check your connection and try again.',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                },
                columns: [
                    { 
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { data: 'title' },
                    { 
                        data: 'award_type',
                        render: function(data, type, row) {
                            let typeText = data || 'Other';
                            let badgeClass = 'bg-secondary';
                            
                            switch(data) {
                                case 'winner':
                                    typeText = 'Winner';
                                    badgeClass = 'bg-success';
                                    break;
                                case 'runner_up':
                                    typeText = 'Runner Up';
                                    badgeClass = 'bg-warning';
                                    break;
                                case 'mention':
                                    typeText = 'Honorable Mention';
                                    badgeClass = 'bg-info';
                                    break;
                                case 'other':
                                    typeText = 'Other';
                                    badgeClass = 'bg-secondary';
                                    break;
                            }
                            
                            return `<span class="badge ${badgeClass} award-type-badge">${typeText}</span>`;
                        }
                    },
                    { 
                        data: 'order_number',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    { 
                        data: 'description',
                        render: function(data, type, row) {
                            if (data && data.length > 50) {
                                return data.substring(0, 50) + '...';
                            }
                            return data || '-';
                        }
                    },
                    {
                        data: 'is_active',
                        render: function(data, type, row) {
                            let badgeClass = data == 1 ? 'bg-success' : 'bg-secondary';
                            let statusText = data == 1 ? 'Active' : 'Inactive';
                            return `<span class="badge ${badgeClass}">${statusText}</span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-info view-award" data-id="${row.id}" data-bs-toggle="tooltip" title="View Details">
                                        <i class="ri-eye-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success edit-award" data-id="${row.id}" data-bs-toggle="tooltip" title="Edit">
                                        <i class="ri-pencil-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-award" data-id="${row.id}" data-bs-toggle="tooltip" title="Delete">
                                        <i class="ri-delete-bin-fill"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [6]
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

            // Make table globally accessible for refresh
            window.awardTable = awardTable;

            // Function to refresh the DataTable
            window.refreshAwardTable = function() {
                if (window.awardTable) {
                    window.awardTable.ajax.reload(null, false); // false = keep current page
                }
            };

            // Function to reset and close modals
            window.resetAwardModals = function() {
                $('#add-award-modal').modal('hide');
                $('#edit-award-modal').modal('hide');
                $('#add-award-form')[0].reset();
                $('#edit-award-form')[0].reset();
                $('.was-validated').removeClass('was-validated');
            };

            // Close modals when success flash message is shown
            <?php if (session()->has('success')): ?>
                setTimeout(() => {
                    window.resetAwardModals();
                }, 100);
            <?php endif; ?>

            // View award
            $(document).on('click', '.view-award', function(e) {
                e.preventDefault();
                var awardId = $(this).data('id');
                
                $('#view-award-modal').modal('show');
                $('#view-loading').show();

                $.ajax({
                    url: '/master-data/program-awards/getAward/' + awardId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            var award = response.data;
                            $('#view_title').text(award.title || 'N/A');
                            
                            // Format award type
                            var typeDisplay = award.award_type || 'Other';
                            switch(award.award_type) {
                                case 'winner': typeDisplay = 'Winner'; break;
                                case 'runner_up': typeDisplay = 'Runner Up'; break;
                                case 'mention': typeDisplay = 'Honorable Mention'; break;
                                case 'other': typeDisplay = 'Other'; break;
                            }
                            $('#view_award_type').text(typeDisplay);
                            
                            $('#view_description').text(award.description || 'No description provided');
                            $('#view_order_number').text(award.order_number || 'Not set');
                            
                            var statusBadge = award.is_active == 1 ?
                                '<span class="badge bg-success">Active</span>' :
                                '<span class="badge bg-secondary">Inactive</span>';
                            $('#view_status').html(statusBadge);
                            
                            $('.view-edit-btn').data('id', award.id);
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load award details',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                            $('#view-award-modal').modal('hide');
                        }
                        $('#view-loading').hide();
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while fetching award details',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                        $('#view-award-modal').modal('hide');
                        $('#view-loading').hide();
                    }
                });
            });

            // Edit award
            $(document).on('click', '.edit-award', function(e) {
                e.preventDefault();
                var awardId = $(this).data('id');
                
                $('#edit-award-modal').modal('show');
                $('#edit-loading').show();

                $.ajax({
                    url: '/master-data/program-awards/getAward/' + awardId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            var award = response.data;
                            $('#edit-award-form').attr('action', '/master-data/program-awards/update/' + award.id);
                            $('#edit_award_id').val(award.id);
                            $('#edit_title').val(award.title);
                            $('#edit_award_type').val(award.award_type);
                            $('#edit_description').val(award.description);
                            $('#edit_order_number').val(award.order_number);
                            $('#edit_is_active').val(award.is_active);
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load award details',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                            $('#edit-award-modal').modal('hide');
                        }
                        $('#edit-loading').hide();
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while fetching award details',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                        $('#edit-award-modal').modal('hide');
                        $('#edit-loading').hide();
                    }
                });
            });

            // Delete award with confirmation
            $(document).on('click', '.delete-award', function(e) {
                e.preventDefault();
                var awardId = $(this).data('id');
                var awardTitle = $(this).closest('tr').find('td:nth-child(2)').text();
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete "${awardTitle}". This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f06548',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Deleting Award...',
                            text: 'Please wait while we delete the award.',
                            icon: 'info',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Redirect to delete URL
                        window.location.href = '/master-data/program-awards/delete/' + awardId;
                    }
                });
            });

            // Add toast notification function
            window.showToast = function(message, type = 'success') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: type,
                    title: message
                });
            };

            // Edit from view modal
            $(document).on('click', '.view-edit-btn', function() {
                var awardId = $(this).data('id');
                $('#view-award-modal').modal('hide');
                setTimeout(function() {
                    $('.edit-award[data-id="' + awardId + '"]').trigger('click');
                }, 500);
            });

            // Form validation and submission with loading
            $('#add-award-form').on('submit', function(e) {
                if ($(this)[0].checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    return;
                }
                
                // Show loading
                Swal.fire({
                    title: 'Creating Award...',
                    text: 'Please wait while we create the award.',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $(this).addClass('was-validated');
            });

            $('#edit-award-form').on('submit', function(e) {
                if ($(this)[0].checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    return;
                }
                
                // Show loading
                Swal.fire({
                    title: 'Updating Award...',
                    text: 'Please wait while we update the award.',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $(this).addClass('was-validated');
            });
        }
    </script>
</body>
</html>
