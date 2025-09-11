<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => $pageTitle)); ?>

    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <style>
        .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }
        
        .stats-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .stats-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 24px;
        }
        
        .admin-details {
            line-height: 1.4;
        }
        
        .admin-details .badge {
            font-size: 0.7rem;
        }
        
        #admin-table {
            font-size: 0.9rem;
        }
        
        #admin-table td {
            vertical-align: middle;
            padding: 0.75rem 0.5rem;
        }
        
        .text-wrap {
            word-break: break-word;
            white-space: normal !important;
        }
        
        /* Enhanced checkbox styling */
        .program-checkbox-container {
            transition: background-color 0.2s ease;
            border-radius: 6px;
            padding: 8px 12px;
            margin: 2px 0;
        }
        
        .program-checkbox-container:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        .program-checkbox-container .form-check-input:checked + .form-check-label {
            color: #0d6efd;
            font-weight: 500;
        }
        
        
        @media (max-width: 768px) {
            #admin-table {
                font-size: 0.8rem;
            }
            
            .admin-details {
                font-size: 0.85rem;
            }
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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Admin Panel', 'title' => $pageTitle)); ?>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card card-hover">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-primary-subtle text-primary me-3">
                                            <i class="ri-admin-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0">Total Admins</p>
                                            <h4 class="mb-0"><?= $statistics['total_admins'] ?? 0 ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card card-hover">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-success-subtle text-success me-3">
                                            <i class="ri-user-check-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0">Active Admins</p>
                                            <h4 class="mb-0"><?= $statistics['active_admins'] ?? 0 ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card card-hover">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-warning-subtle text-warning me-3">
                                            <i class="ri-shield-user-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0">Super Admins</p>
                                            <h4 class="mb-0"><?= $statistics['super_admins'] ?? 0 ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card card-hover">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-info-subtle text-info me-3">
                                            <i class="ri-time-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0">Recent Logins</p>
                                            <h4 class="mb-0"><?= $statistics['recent_logins'] ?? 0 ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Management Table -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h4 class="card-title mb-0">Administrator Management</h4>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#admin-modal">
                                                <i class="ri-add-line align-bottom me-1"></i> Add Administrator
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <!-- Filters -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <select class="form-select" id="role-filter">
                                                <option value="">All Roles</option>
                                                <?php foreach ($manageableRoles as $role): ?>
                                                    <option value="<?= $role ?>"><?= ucwords(str_replace('_', ' ', $role)) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select" id="program-filter">
                                                <option value="">All Programs</option>
                                                <?php foreach ($programs as $program): ?>
                                                    <option value="<?= $program->id ?>"><?= esc($program->name) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select" id="status-filter">
                                                <option value="">All Status</option>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Data Table -->
                                    <table id="admin-table" class="table table-bordered dt-responsive table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 5%">#</th>
                                                <th scope="col" style="width: 30%">Admin Details</th>
                                                <th scope="col" style="width: 25%">Programs</th>
                                                <th scope="col" style="width: 10%">Status</th>
                                                <th scope="col" style="width: 15%">Last Login</th>
                                                <th scope="col" style="width: 15%">Actions</th>
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
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <?= $this->include('partials/vendor-scripts') ?>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            const adminTable = $('#admin-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/settings/admin-management/getAdminsData',
                    type: 'GET',
                    data: function(d) {
                        d.role = $('#role-filter').val();
                        d.program_id = $('#program-filter').val();
                        d.is_active = $('#status-filter').val();
                    }
                },
                columns: [
                    { data: null, render: function(data, type, row, meta) { return meta.row + 1; } },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <div class="admin-details">
                                    <div class="fw-bold text-primary">${row.name}</div>
                                    <div class="text-muted small">${row.email}</div>
                                    <span class="badge bg-secondary-subtle text-secondary mt-1">${row.role}</span>
                                </div>
                            `;
                        }
                    },
                    { 
                        data: 'program_name',
                        render: function(data, type, row) {
                            return '<div class="text-wrap" style="max-width: 200px; word-wrap: break-word;">' + data + '</div>';
                        }
                    },
                    { data: 'is_active' },
                    { data: 'last_login' },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            let actions = '<div class="d-flex gap-1 flex-wrap">';
                            actions += '<button class="btn btn-sm btn-info view-admin" data-id="' + row.id + '" title="View Details"><i class="ri-eye-line"></i></button>';
                            if (row.can_edit) {
                                actions += '<button class="btn btn-sm btn-success edit-admin" data-id="' + row.id + '" title="Edit"><i class="ri-pencil-line"></i></button>';
                            }
                            if (row.can_delete) {
                                actions += '<button class="btn btn-sm btn-danger delete-admin" data-id="' + row.id + '" title="Delete"><i class="ri-delete-bin-line"></i></button>';
                            }
                            actions += '</div>';
                            return actions;
                        }
                    }
                ],
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true
            });

            // Filter events
            $('#role-filter, #program-filter, #status-filter').on('change', function() {
                adminTable.draw();
            });

            // Flash messages
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

            // CRUD functionality
            $(document).on('click', '.view-admin', function() {
                const adminId = $(this).data('id');
                viewAdmin(adminId);
            });

            $(document).on('click', '.edit-admin', function() {
                const adminId = $(this).data('id');
                editAdmin(adminId);
            });

            $(document).on('click', '.delete-admin', function() {
                const adminId = $(this).data('id');
                deleteAdmin(adminId);
            });

            // Add admin button click (for the button in the card header)
            $(document).on('click', '[data-bs-target="#admin-modal"]', function() {
                showAddAdminModal();
            });

            $('#add-admin-btn').click(function() {
                showAddAdminModal();
            });

            $('#save-admin-btn').click(function() {
                saveAdmin();
            });

            $('#update-admin-btn').click(function() {
                updateAdmin();
            });
        });

        // CRUD Functions
        function showAddAdminModal() {
            $.get('/settings/admin-management/create')
                .done(function(response) {
                    if (response.success) {
                        populateFormOptions(response.data);
                        $('#admin-modal-title').text('Add New Admin');
                        $('#admin-form')[0].reset();
                        $('#admin-id').val('');
                        
                        // Reset all checkboxes
                        $('.program-checkbox').prop('checked', false);
                        $('#select-all-programs').prop('checked', false).prop('indeterminate', false);
                        
                        $('#save-admin-btn').show();
                        $('#update-admin-btn').hide();
                        $('#password-group').show();
                        $('#admin-modal').modal('show');
                    }
                })
                .fail(function() {
                    Swal.fire('Error', 'Failed to load form data', 'error');
                });
        }

        function viewAdmin(adminId) {
            $.get(`/settings/admin-management/view/${adminId}`)
                .done(function(response) {
                    if (response.success) {
                        showAdminDetails(response.admin);
                    }
                })
                .fail(function() {
                    Swal.fire('Error', 'Failed to load admin details', 'error');
                });
        }

        function editAdmin(adminId) {
            $.get(`/settings/admin-management/edit/${adminId}`)
                .done(function(response) {
                    if (response.success) {
                        populateFormOptions(response.data, response.data.admin);
                        populateAdminForm(response.data.admin);
                        $('#admin-modal-title').text('Edit Admin');
                        $('#admin-id').val(adminId);
                        $('#save-admin-btn').hide();
                        $('#update-admin-btn').show();
                        $('#password-group').hide();
                        $('#admin-modal').modal('show');
                    }
                })
                .fail(function() {
                    Swal.fire('Error', 'Failed to load admin data', 'error');
                });
        }

        function saveAdmin() {
            const formData = new FormData($('#admin-form')[0]);
            
            // Show loading state
            const saveBtn = $('#save-admin-btn');
            const originalText = saveBtn.html();
            saveBtn.html('<i class="ri-loader-2-line"></i> Creating...').prop('disabled', true);
            
            $.ajax({
                url: '/settings/admin-management/store',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#admin-modal').modal('hide');
                        
                        // Show success notification with page reload
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || 'Admin created successfully',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Reload the entire page to show fresh data
                                window.location.reload();
                            }
                        });
                    } else {
                        showValidationErrors(response.errors);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to create admin. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                },
                complete: function() {
                    // Reset button state
                    saveBtn.html(originalText).prop('disabled', false);
                }
            });
        }

        function updateAdmin() {
            const adminId = $('#admin-id').val();
            const formData = new FormData($('#admin-form')[0]);
            
            // Show loading state
            const updateBtn = $('#update-admin-btn');
            const originalText = updateBtn.html();
            updateBtn.html('<i class="ri-loader-2-line"></i> Updating...').prop('disabled', true);
            
            $.ajax({
                url: `/settings/admin-management/update/${adminId}`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#admin-modal').modal('hide');
                        
                        // Show success notification with page reload
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || 'Admin updated successfully',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Reload the entire page to show fresh data
                                window.location.reload();
                            }
                        });
                    } else {
                        showValidationErrors(response.errors);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to update admin. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                },
                complete: function() {
                    // Reset button state
                    updateBtn.html(originalText).prop('disabled', false);
                }
            });
        }

        function deleteAdmin(adminId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This admin will be deleted permanently!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: `/settings/admin-management/delete/${adminId}`,
                        method: 'GET'
                    }).then(response => {
                        if (!response.success) {
                            throw new Error(response.message || 'Failed to delete admin');
                        }
                        return response;
                    }).catch(error => {
                        Swal.showValidationMessage('Request failed: ' + error.message);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    // Show success notification with page reload
                    Swal.fire({
                        title: 'Deleted!',
                        text: result.value.message || 'Admin deleted successfully',
                        icon: 'success',
                        confirmButtonColor: '#0ab39c',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((deleteResult) => {
                        if (deleteResult.isConfirmed) {
                            // Reload the entire page to show fresh data
                            window.location.reload();
                        }
                    });
                }
            });
        }

        function populateFormOptions(data, admin = null) {
            // Populate roles dropdown
            const roleSelect = $('#admin-role');
            roleSelect.empty();
            roleSelect.append('<option value="">Select Role</option>');
            data.roles.forEach(role => {
                // Handle both old format (string) and new format (object)
                if (typeof role === 'string') {
                    const roleName = role.replace(/_/g, ' ').toUpperCase();
                    roleSelect.append(`<option value="${role}">${roleName}</option>`);
                } else {
                    // New format with display_name
                    roleSelect.append(`<option value="${role.name}">${role.display_name}</option>`);
                }
            });

            // Populate programs checkboxes
            const programsContainer = $('#admin-programs-checkboxes');
            programsContainer.empty();
            
            // Create a map of all programs to show (active programs + assigned inactive programs)
            const allProgramsToShow = new Map();
            
            // Add all active programs from data.programs
            data.programs.forEach(program => {
                allProgramsToShow.set(program.id, {
                    ...program,
                    is_active: 1,
                    is_assigned: false
                });
            });
            
            // Add assigned programs (including inactive ones) if this is for editing
            if (admin && admin.programs) {
                admin.programs.forEach(program => {
                    if (!allProgramsToShow.has(program.id)) {
                        // This is an inactive assigned program, add it
                        allProgramsToShow.set(program.id, {
                            ...program,
                            is_assigned: true
                        });
                    } else {
                        // Mark as assigned
                        allProgramsToShow.get(program.id).is_assigned = true;
                    }
                });
            }
            
            // Sort programs by name
            const sortedPrograms = Array.from(allProgramsToShow.values()).sort((a, b) => a.name.localeCompare(b.name));
            
            sortedPrograms.forEach(program => {
                const isInactive = program.is_active != 1;
                const inactiveClass = isInactive ? ' text-muted' : '';
                const inactiveLabel = isInactive ? ' (Inactive)' : '';
                const disabledAttr = isInactive && !program.is_assigned ? ' disabled' : '';
                
                programsContainer.append(`
                    <div class="form-check program-checkbox-container">
                        <input class="form-check-input program-checkbox" type="checkbox" 
                               name="program_ids[]" value="${program.id}" id="program-${program.id}"${disabledAttr}>
                        <label class="form-check-label${inactiveClass}" for="program-${program.id}">
                            ${program.name}${inactiveLabel}
                        </label>
                    </div>
                `);
            });

            // Add select all functionality
            $('#select-all-programs').off('change').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.program-checkbox').prop('checked', isChecked);
                updateSelectAllState();
            });

            // Update select all when individual checkboxes change
            $(document).off('change', '.program-checkbox').on('change', '.program-checkbox', function() {
                updateSelectAllState();
            });
        }

        function updateSelectAllState() {
            const totalCheckboxes = $('.program-checkbox').length;
            const checkedCheckboxes = $('.program-checkbox:checked').length;
            
            if (checkedCheckboxes === 0) {
                $('#select-all-programs').prop('checked', false).prop('indeterminate', false);
            } else if (checkedCheckboxes === totalCheckboxes) {
                $('#select-all-programs').prop('checked', true).prop('indeterminate', false);
            } else {
                $('#select-all-programs').prop('checked', false).prop('indeterminate', true);
            }
        }

        function populateAdminForm(admin) {
            $('#admin-name').val(admin.name);
            $('#admin-email').val(admin.email);
            $('#admin-role').val(admin.role);
            $('#admin-is-active').prop('checked', admin.is_active == 1);
            
            // Clear all program checkboxes first
            $('.program-checkbox').prop('checked', false);
            $('#select-all-programs').prop('checked', false).prop('indeterminate', false);
            
            // Set selected programs
            if (admin.programs && admin.programs.length > 0) {
                const programIds = admin.programs.map(p => p.id || p.program_id).filter(id => id).map(id => id.toString());
                programIds.forEach(programId => {
                    $(`#program-${programId}`).prop('checked', true);
                });
                
                // Update select all state
                updateSelectAllState();
            }
        }

        function showValidationErrors(errors) {
            let errorMessage = 'Please fix the following errors:\n';
            for (const field in errors) {
                errorMessage += `• ${errors[field]}\n`;
            }
            Swal.fire({
                title: 'Validation Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#f06548',
                confirmButtonText: 'OK'
            });
        }

        function showAdminDetails(admin) {
            // Populate modal with admin details
            $('#view-admin-name').text(admin.name);
            $('#view-admin-email').text(admin.email);
            $('#view-admin-role').text(admin.role.replace(/_/g, ' ').toUpperCase());
            $('#view-admin-status').html(admin.is_active ? 
                '<span class="badge bg-success">Active</span>' : 
                '<span class="badge bg-danger">Inactive</span>'
            );
            $('#view-admin-last-login').text(admin.last_login || 'Never');
            $('#view-admin-created').text(new Date(admin.created_at).toLocaleDateString());
            
            // Handle programs list
            const programsContainer = $('#view-admin-programs');
            programsContainer.empty();
            
            if (admin.programs && admin.programs.length > 0) {
                admin.programs.forEach(program => {
                    programsContainer.append(`
                        <span class="badge bg-primary me-1 mb-1">${program.name}</span>
                    `);
                });
            } else {
                programsContainer.html('<span class="text-muted">No programs assigned</span>');
            }
            
            // Show modal
            $('#admin-view-modal').modal('show');
        }
    </script>

<!-- Admin View Modal -->
<div class="modal fade" id="admin-view-modal" tabindex="-1" aria-labelledby="admin-view-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="admin-view-modal-title">
                    <i class="ri-user-line me-2"></i>Administrator Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-header bg-transparent border-0">
                                <h6 class="mb-0"><i class="ri-information-line me-1"></i>Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Full Name</label>
                                    <p class="mb-0" id="view-admin-name">-</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email Address</label>
                                    <p class="mb-0" id="view-admin-email">-</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Role</label>
                                    <p class="mb-0" id="view-admin-role">-</p>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-bold">Status</label>
                                    <div id="view-admin-status">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-header bg-transparent border-0">
                                <h6 class="mb-0"><i class="ri-folder-line me-1"></i>Program Assignments</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Assigned Programs</label>
                                    <div id="view-admin-programs">-</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Last Login</label>
                                    <p class="mb-0" id="view-admin-last-login">-</p>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-bold">Created Date</label>
                                    <p class="mb-0" id="view-admin-created">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Admin Management Modal -->
<div class="modal fade" id="admin-modal" tabindex="-1" aria-labelledby="admin-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="admin-modal-title">Add New Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="admin-form">
                    <input type="hidden" id="admin-id" name="admin_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin-name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="admin-name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin-email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="admin-email" name="email" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3" id="password-group">
                                <label for="admin-password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="admin-password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin-role" class="form-label">Role</label>
                                <select class="form-select" id="admin-role" name="role" required>
                                    <option value="">Select Role</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assign to Programs</label>
                        <div class="border rounded p-3" style="background-color: #f8f9fa;">
                            <div class="mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select-all-programs">
                                    <label class="form-check-label fw-bold text-primary" for="select-all-programs">
                                        Select All Programs
                                    </label>
                                </div>
                                <hr class="my-2">
                            </div>
                            <div id="admin-programs-checkboxes">
                                <div class="text-muted">Loading programs...</div>
                            </div>
                        </div>
                        <small class="text-muted">Select the programs this administrator should have access to. Leave all unchecked to assign to all programs.</small>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="admin-is-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="admin-is-active">
                            Active Status
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-admin-btn">Save Admin</button>
                <button type="button" class="btn btn-primary" id="update-admin-btn" style="display: none;">Update Admin</button>
            </div>
        </div>
    </div>
</div>

</body>

</html>