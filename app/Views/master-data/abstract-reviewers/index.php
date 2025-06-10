<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Abstract Reviewers')); ?>

    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        /* Modal loading overlay */
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

        /* Badge styling */
        .status-badge {
            display: inline-flex;
            align-items: center;
        }        /* Subthemes styling */
        .subthemes-list {
            max-width: 200px;
            word-wrap: break-word;
            white-space: normal;
        }
        
        /* Table cell wrapping */
        #reviewers-table td {
            white-space: normal;
            max-width: 200px;
        }
        
        #reviewers-table td.subthemes-cell {
            min-width: 150px;
            max-width: 250px;
        }

        /* Form styling */
        .select2-container--bootstrap-5 .select2-selection {
            min-height: calc(1.5em + 0.75rem + 2px);
        }

        /* Improve Select2 selected items visibility */
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #0d6efd !important;
            border: 1px solid #0d6efd !important;
            color: #fff !important;
            padding: 2px 8px !important;
            margin: 2px !important;
            border-radius: 4px !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff !important;
            margin-right: 5px !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #ffcccc !important;
        }

        /* Role badge styling */
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Abstract Reviewers')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">
                                        <i class="ri-user-star-line me-2"></i>Abstract Reviewers Management
                                    </h5>
                                    <div class="flex-shrink-0">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReviewerModal">
                                            <i class="ri-add-line me-1"></i>Add Reviewer
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="reviewers-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Institution</th>
                                                <th>Role</th>
                                                <th>Subthemes</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be loaded via DataTables -->
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
    <!-- END layout-wrapper -->

    <!-- Add Reviewer Modal -->
    <div class="modal fade" id="addReviewerModal" tabindex="-1" aria-labelledby="addReviewerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addReviewerModalLabel">
                        <i class="ri-user-add-line me-2"></i>Add New Reviewer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addReviewerForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reviewerName" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="reviewerName" name="name" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reviewerEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="reviewerEmail" name="email" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reviewerInstitution" class="form-label">Institution <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="reviewerInstitution" name="institution" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reviewerRole" class="form-label">Reviewer Role <span class="text-danger">*</span></label>
                                    <select class="form-select" id="reviewerRole" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="super">Super Reviewer</option>
                                        <option value="internal">Internal Reviewer</option>
                                        <option value="external">External Reviewer</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reviewerPassword" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="reviewerPassword" name="password" required>
                                    <div class="invalid-feedback"></div>
                                    <div class="form-text">Minimum 6 characters</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="reviewerSubthemes" class="form-label">Assigned Subthemes</label>
                                    <select class="form-select" id="reviewerSubthemes" name="subthemes[]" multiple>
                                        <?php if (!empty($programSubthemes)): ?>
                                            <?php foreach ($programSubthemes as $subtheme): ?>
                                                <option value="<?= $subtheme->id ?>"><?= esc($subtheme->name) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text">Select the subthemes this reviewer can review</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="addSpinner"></span>
                            <i class="ri-save-line me-1"></i>Save Reviewer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Reviewer Modal -->
    <div class="modal fade" id="editReviewerModal" tabindex="-1" aria-labelledby="editReviewerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-loading d-none" id="editLoading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="loading-text">Loading reviewer data...</div>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="editReviewerModalLabel">
                        <i class="ri-edit-line me-2"></i>Edit Reviewer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editReviewerForm">
                    <input type="hidden" id="editReviewerId" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editReviewerName" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editReviewerName" name="name" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editReviewerEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="editReviewerEmail" name="email" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editReviewerInstitution" class="form-label">Institution <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editReviewerInstitution" name="institution" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editReviewerRole" class="form-label">Reviewer Role <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editReviewerRole" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="super">Super Reviewer</option>
                                        <option value="internal">Internal Reviewer</option>
                                        <option value="external">External Reviewer</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editReviewerPassword" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="editReviewerPassword" name="password">
                                    <div class="invalid-feedback"></div>
                                    <div class="form-text">Leave blank to keep current password</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="editReviewerSubthemes" class="form-label">Assigned Subthemes</label>
                                    <select class="form-select" id="editReviewerSubthemes" name="subthemes[]" multiple>
                                        <?php if (!empty($programSubthemes)): ?>
                                            <?php foreach ($programSubthemes as $subtheme): ?>
                                                <option value="<?= $subtheme->id ?>"><?= esc($subtheme->name) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text">Select the subthemes this reviewer can review</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="editSpinner"></span>
                            <i class="ri-save-line me-1"></i>Update Reviewer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Reviewer Modal -->
    <div class="modal fade" id="viewReviewerModal" tabindex="-1" aria-labelledby="viewReviewerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-loading d-none" id="viewLoading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="loading-text">Loading reviewer details...</div>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReviewerModalLabel">
                        <i class="ri-eye-line me-2"></i>Reviewer Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Full Name</label>
                                <p class="fw-semibold" id="viewReviewerName">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Email Address</label>
                                <p class="fw-semibold" id="viewReviewerEmail">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Institution</label>
                                <p class="fw-semibold" id="viewReviewerInstitution">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Reviewer Role</label>
                                <p id="viewReviewerRole">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Status</label>
                                <p id="viewReviewerStatus">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label text-muted">Assigned Subthemes</label>
                                <div id="viewReviewerSubthemes">
                                    <span class="text-muted">No subthemes assigned</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Created Date</label>
                                <p id="viewReviewerCreated">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Last Updated</label>
                                <p id="viewReviewerUpdated">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                    <button type="button" class="btn btn-primary" id="editFromViewBtn">
                        <i class="ri-edit-line me-1"></i>Edit Reviewer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable            
            let reviewersTable = $('#reviewers-table').DataTable({
                processing: true,
                serverSide: false,
                scrollX: true,
                ajax: {
                    url: '/master-data/abstract-reviewers/getData',
                    type: 'GET'
                },
                columnDefs: [
                    {
                        targets: 'subthemes-cell',
                        width: '250px'
                    }
                ],
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                        orderable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'institution'
                    },
                    {
                        data: 'role',
                        render: function(data) {
                            let badgeClass = 'bg-secondary';
                            let displayText = 'External';

                            if (data === 'super') {
                                badgeClass = 'bg-danger';
                                displayText = 'Super';
                            } else if (data === 'internal') {
                                badgeClass = 'bg-primary';
                                displayText = 'Internal';
                            } else if (data === 'external') {
                                badgeClass = 'bg-info';
                                displayText = 'External';
                            }

                            return `<span class="badge ${badgeClass} role-badge">${displayText}</span>`;
                        }
                    },                    {
                        data: 'subthemes',
                        className: 'subthemes-cell',
                        render: function(data) {
                            if (!data || data.trim() === '') {
                                return '<span class="text-muted">No subthemes</span>';
                            }
                            return '<div class="subthemes-list">' + data + '</div>';
                        }
                    },
                    {
                        data: 'is_active',
                        render: function(data) {
                            return data == 1 ?
                                '<span class="badge bg-success">Active</span>' :
                                '<span class="badge bg-danger">Inactive</span>';
                        }
                    },
                    {
                        data: 'created_at',
                        render: function(data) {
                            return data ? new Date(data).toLocaleDateString() : '-';
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return `
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-info view-reviewer" data-id="${data.id}" title="View Details">
                                        <i class="ri-eye-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning edit-reviewer" data-id="${data.id}" title="Edit">
                                        <i class="ri-edit-fill"></i>
                                    </button>
                                    <button class="btn btn-sm ${data.is_active == 1 ? 'btn-secondary' : 'btn-success'} toggle-status" data-id="${data.id}" title="${data.is_active == 1 ? 'Deactivate' : 'Activate'}">
                                        <i class="ri-${data.is_active == 1 ? 'pause' : 'play'}-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-reviewer" data-id="${data.id}" title="Delete">
                                        <i class="ri-delete-bin-fill"></i>
                                    </button>
                                </div>
                            `;
                        },
                        orderable: false
                    }
                ],
                responsive: true,
                pageLength: 25,
                order: [
                    [1, 'asc']
                ],
                drawCallback: function() {
                    $('[title]').tooltip();
                }
            });

            // Initialize Select2
            $('#reviewerSubthemes, #editReviewerSubthemes').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select subthemes...',
                allowClear: true
            });

            // Add Reviewer Form
            $('#addReviewerForm').on('submit', function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const spinner = $('#addSpinner');

                // Show loading state
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');

                // Clear previous validation errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').empty();

                $.ajax({
                    url: '/master-data/abstract-reviewers/create',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#addReviewerModal').modal('hide');
                            $('#addReviewerForm')[0].reset();
                            $('#reviewerSubthemes').val(null).trigger('change');
                            reviewersTable.ajax.reload();

                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            if (response.errors) {
                                // Show validation errors
                                Object.keys(response.errors).forEach(function(field) {
                                    const input = $(`[name="${field}"]`);
                                    input.addClass('is-invalid');
                                    input.siblings('.invalid-feedback').text(response.errors[field]);
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message
                                });
                            }
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while creating the reviewer'
                        });
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                        spinner.addClass('d-none');
                    }
                });
            });

            // View Reviewer
            $(document).on('click', '.view-reviewer', function() {
                const reviewerId = $(this).data('id');
                $('#viewLoading').removeClass('d-none');
                $('#viewReviewerModal').modal('show');

                $.ajax({
                    url: `/master-data/abstract-reviewers/edit/${reviewerId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const reviewer = response.data;

                            $('#viewReviewerName').text(reviewer.name);
                            $('#viewReviewerEmail').text(reviewer.email);
                            $('#viewReviewerInstitution').text(reviewer.institution);

                            // Display role with proper styling
                            let roleBadgeClass = 'bg-secondary';
                            let roleDisplayText = 'External';

                            if (reviewer.role === 'super') {
                                roleBadgeClass = 'bg-danger';
                                roleDisplayText = 'Super';
                            } else if (reviewer.role === 'internal') {
                                roleBadgeClass = 'bg-primary';
                                roleDisplayText = 'Internal';
                            } else if (reviewer.role === 'external') {
                                roleBadgeClass = 'bg-info';
                                roleDisplayText = 'External';
                            }

                            $('#viewReviewerRole').html(`<span class="badge ${roleBadgeClass} role-badge">${roleDisplayText}</span>`);

                            $('#viewReviewerStatus').html(reviewer.is_active == 1 ?
                                '<span class="badge bg-success">Active</span>' :
                                '<span class="badge bg-danger">Inactive</span>'
                            );
                            $('#viewReviewerCreated').text(new Date(reviewer.created_at).toLocaleDateString());
                            $('#viewReviewerUpdated').text(new Date(reviewer.updated_at).toLocaleDateString());

                            // Show assigned subthemes
                            if (reviewer.assigned_subthemes && reviewer.assigned_subthemes.length > 0) {
                                let subthemesHtml = '';
                                reviewer.assigned_subthemes.forEach(function(subthemeId) {
                                    const subtheme = <?= json_encode($programSubthemes) ?>.find(s => s.id == subthemeId);
                                    if (subtheme) {
                                        subthemesHtml += `<span class="badge bg-primary me-1 mb-1">${subtheme.name}</span>`;
                                    }
                                });
                                $('#viewReviewerSubthemes').html(subthemesHtml);
                            } else {
                                $('#viewReviewerSubthemes').html('<span class="text-muted">No subthemes assigned</span>');
                            }

                            // Store reviewer ID for edit button
                            $('#editFromViewBtn').data('id', reviewerId);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while loading reviewer details'
                        });
                    },
                    complete: function() {
                        $('#viewLoading').addClass('d-none');
                    }
                });
            });

            // Edit Reviewer
            $(document).on('click', '.edit-reviewer, #editFromViewBtn', function() {
                const reviewerId = $(this).data('id');
                $('#editLoading').removeClass('d-none');
                $('#editReviewerModal').modal('show');

                // Hide view modal if coming from view
                if ($(this).attr('id') === 'editFromViewBtn') {
                    $('#viewReviewerModal').modal('hide');
                }

                $.ajax({
                    url: `/master-data/abstract-reviewers/edit/${reviewerId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const reviewer = response.data;
                            $('#editReviewerId').val(reviewer.id);
                            $('#editReviewerName').val(reviewer.name);
                            $('#editReviewerEmail').val(reviewer.email);
                            $('#editReviewerInstitution').val(reviewer.institution);
                            $('#editReviewerRole').val(reviewer.role || 'external');
                            $('#editReviewerPassword').val('');

                            // Set selected subthemes
                            $('#editReviewerSubthemes').val(reviewer.assigned_subthemes).trigger('change');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while loading reviewer data'
                        });
                    },
                    complete: function() {
                        $('#editLoading').addClass('d-none');
                    }
                });
            });

            // Update Reviewer Form
            $('#editReviewerForm').on('submit', function(e) {
                e.preventDefault();

                const reviewerId = $('#editReviewerId').val();
                const submitBtn = $(this).find('button[type="submit"]');
                const spinner = $('#editSpinner');

                // Show loading state
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');

                // Clear previous validation errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').empty();

                $.ajax({
                    url: `/master-data/abstract-reviewers/update/${reviewerId}`,
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#editReviewerModal').modal('hide');
                            reviewersTable.ajax.reload();

                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            if (response.errors) {
                                // Show validation errors
                                Object.keys(response.errors).forEach(function(field) {
                                    const input = $(`#editReviewer${field.charAt(0).toUpperCase() + field.slice(1)}`);
                                    input.addClass('is-invalid');
                                    input.siblings('.invalid-feedback').text(response.errors[field]);
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message
                                });
                            }
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while updating the reviewer'
                        });
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                        spinner.addClass('d-none');
                    }
                });
            });

            // Toggle Status
            $(document).on('click', '.toggle-status', function() {
                const reviewerId = $(this).data('id');
                const button = $(this);

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to change the reviewer status?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, change it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/master-data/abstract-reviewers/toggleStatus/${reviewerId}`,
                            type: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    reviewersTable.ajax.reload();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: response.message
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'An error occurred while changing the status'
                                });
                            }
                        });
                    }
                });
            });

            // Delete Reviewer
            $(document).on('click', '.delete-reviewer', function() {
                const reviewerId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This action will permanently delete the reviewer and all associated data!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/master-data/abstract-reviewers/delete/${reviewerId}`,
                            type: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    reviewersTable.ajax.reload();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: response.message
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'An error occurred while deleting the reviewer'
                                });
                            }
                        });
                    }
                });
            }); // Reset modals when closed
            $('#addReviewerModal').on('hidden.bs.modal', function() {
                $('#addReviewerForm')[0].reset();
                $('#reviewerSubthemes').val(null).trigger('change');
                $('#reviewerRole').val('').trigger('change');
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').empty();
            });

            $('#editReviewerModal').on('hidden.bs.modal', function() {
                $('#editReviewerForm')[0].reset();
                $('#editReviewerSubthemes').val(null).trigger('change');
                $('#editReviewerRole').val('').trigger('change');
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').empty();
            });
        });
    </script>
</body>

</html>