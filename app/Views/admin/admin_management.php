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
                                    <table id="admin-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Role</th>
                                                <th scope="col">Program</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Last Login</th>
                                                <th scope="col">Actions</th>
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
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'role' },
                    { data: 'program_name' },
                    { data: 'is_active' },
                    { data: 'last_login' },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            let actions = '<div class="d-flex gap-2">';
                            actions += '<button class="btn btn-sm btn-info view-admin" data-id="' + row.id + '"><i class="ri-eye-line"></i></button>';
                            if (row.can_edit) {
                                actions += '<button class="btn btn-sm btn-success edit-admin" data-id="' + row.id + '"><i class="ri-pencil-line"></i></button>';
                            }
                            if (row.can_delete) {
                                actions += '<button class="btn btn-sm btn-danger delete-admin" data-id="' + row.id + '"><i class="ri-delete-bin-line"></i></button>';
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
                        populateFormOptions(response.data);
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
            
            $.ajax({
                url: '/settings/admin-management/store',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#admin-modal').modal('hide');
                        adminTable.ajax.reload();
                        Swal.fire('Success', response.message, 'success');
                    } else {
                        showValidationErrors(response.errors);
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to create admin', 'error');
                }
            });
        }

        function updateAdmin() {
            const adminId = $('#admin-id').val();
            const formData = new FormData($('#admin-form')[0]);
            
            $.ajax({
                url: `/settings/admin-management/update/${adminId}`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#admin-modal').modal('hide');
                        adminTable.ajax.reload();
                        Swal.fire('Success', response.message, 'success');
                    } else {
                        showValidationErrors(response.errors);
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to update admin', 'error');
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
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/settings/admin-management/delete/${adminId}`,
                        method: 'GET',
                        success: function(response) {
                            if (response.success) {
                                adminTable.ajax.reload();
                                Swal.fire('Deleted!', response.message, 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Failed to delete admin', 'error');
                        }
                    });
                }
            });
        }

        function populateFormOptions(data) {
            // Populate roles dropdown
            const roleSelect = $('#admin-role');
            roleSelect.empty();
            data.roles.forEach(role => {
                roleSelect.append(`<option value="${role}">${role.replace('_', ' ').toUpperCase()}</option>`);
            });

            // Populate programs dropdown
            const programSelect = $('#admin-programs');
            programSelect.empty();
            data.programs.forEach(program => {
                programSelect.append(`<option value="${program.id}">${program.name}</option>`);
            });
        }

        function populateAdminForm(admin) {
            $('#admin-name').val(admin.name);
            $('#admin-email').val(admin.email);
            $('#admin-role').val(admin.role);
            $('#admin-is-active').prop('checked', admin.is_active == 1);
            
            // Set selected programs
            const programIds = admin.programs.map(p => p.program_id.toString());
            $('#admin-programs').val(programIds);
        }

        function showValidationErrors(errors) {
            let errorMessage = 'Please fix the following errors:\n';
            for (const field in errors) {
                errorMessage += `- ${errors[field]}\n`;
            }
            Swal.fire('Validation Error', errorMessage, 'error');
        }

        function showAdminDetails(admin) {
            let programsList = 'All Programs';
            if (admin.programs && admin.programs.length > 0) {
                programsList = admin.programs.map(p => p.program_name).join(', ');
            }

            Swal.fire({
                title: admin.name,
                html: `
                    <div class="text-start">
                        <p><strong>Email:</strong> ${admin.email}</p>
                        <p><strong>Role:</strong> ${admin.role.replace('_', ' ').toUpperCase()}</p>
                        <p><strong>Status:</strong> ${admin.is_active ? 'Active' : 'Inactive'}</p>
                        <p><strong>Programs:</strong> ${programsList}</p>
                        <p><strong>Last Login:</strong> ${admin.last_login || 'Never'}</p>
                        <p><strong>Created:</strong> ${new Date(admin.created_at).toLocaleDateString()}</p>
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                width: '500px'
            });
        }
    </script>

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
                        <label for="admin-programs" class="form-label">Assign to Programs</label>
                        <select class="form-select" id="admin-programs" name="program_ids[]" multiple>
                            <option value="">Loading...</option>
                        </select>
                        <small class="text-muted">Leave empty to assign to all programs. Hold Ctrl/Cmd to select multiple programs.</small>
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