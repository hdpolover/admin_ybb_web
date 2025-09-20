<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => $pageTitle)); ?>

    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <style>
        .permission-badge {
            margin: 2px;
            font-size: 0.75rem;
        }
        
        .stats-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
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

        /* Table row hover effects */
        #role-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        #role-table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1) !important;
        }
        
        .table-hover-row {
            background-color: rgba(0, 123, 255, 0.05) !important;
        }

        /* Modal improvements */
        .modal-lg {
            max-width: 900px;
        }
        
        .permission-badge {
            margin: 2px;
            font-size: 0.75rem;
        }
        
        .list-group-item {
            border: 1px solid rgba(0,0,0,.125);
            padding: 0.75rem 1rem;
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

                    <!-- Permission Statistics -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-primary-subtle text-primary me-3">
                                            <i class="ri-shield-user-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0">Total Roles</p>
                                            <h4 class="mb-0"><?= count($roles ?? []) ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-success-subtle text-success me-3">
                                            <i class="ri-key-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0">Total Permissions</p>
                                            <h4 class="mb-0"><?= $permissionStats['total'] ?? 0 ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-info-subtle text-info me-3">
                                            <i class="ri-settings-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0">Active Roles</p>
                                            <h4 class="mb-0"><?= $permissionStats['active_roles'] ?? 0 ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-warning-subtle text-warning me-3">
                                            <i class="ri-group-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0">Permission Categories</p>
                                            <h4 class="mb-0"><?= count($permissions ?? []) ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Management -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h4 class="card-title mb-0">Roles & Permissions</h4>
                                        <div>
                                            <a href="/roles/create" class="btn btn-primary">
                                                <i class="ri-add-line align-bottom me-1"></i> Create Role
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                                        <i class="ri-information-line me-2"></i>
                                        <strong>Tip:</strong> Click on any role row to view detailed information, or use the action buttons for specific operations.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="role-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Role Name</th>
                                                    <th scope="col">Display Name</th>
                                                    <th scope="col">Permissions</th>
                                                    <th scope="col">Users</th>
                                                    <th scope="col">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (isset($roles) && is_array($roles)): ?>
                                                    <?php foreach ($roles as $index => $role): ?>
                                                        <tr>
                                                            <td><?= $index + 1 ?></td>
                                                            <td><strong><?= esc($role->name ?? '') ?></strong></td>
                                                            <td><?= esc($role->display_name ?? '') ?></td>
                                                            <td>
                                                                <span class="badge bg-info permission-badge">
                                                                    <?= $role->permission_count ?? 0 ?> permissions
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-secondary">
                                                                    <?= $role->user_count ?? 0 ?> users
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex gap-2">
                                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewRole(<?= $role->id ?? 0 ?>)" data-bs-toggle="tooltip" title="View">
                                                                        <i class="ri-eye-line"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-success" onclick="editRole(<?= $role->id ?? 0 ?>)" data-bs-toggle="tooltip" title="Edit">
                                                                        <i class="ri-pencil-line"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteRole(<?= $role->id ?? 0 ?>)" data-bs-toggle="tooltip" title="Delete">
                                                                        <i class="ri-delete-bin-line"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No roles found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Permission Categories -->
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Permission Categories</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($permissions) && is_array($permissions)): ?>
                                        <?php foreach ($permissions as $category => $perms): ?>
                                            <div class="mb-4">
                                                <h6 class="text-primary fw-semibold"><?= ucwords(str_replace('_', ' ', $category)) ?></h6>
                                                <div class="d-flex flex-wrap">
                                                    <?php foreach ($perms as $permission): ?>
                                                        <span class="badge bg-light text-dark permission-badge">
                                                            <?= esc($permission->name ?? '') ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">No permissions found</p>
                                    <?php endif; ?>
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

    <!-- Role Details Modal -->
    <div class="modal fade" id="roleDetailsModal" tabindex="-1" aria-labelledby="roleDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleDetailsModalLabel">Role Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Role Name:</label>
                                <p id="role-name" class="mb-0 text-muted">-</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Display Name:</label>
                                <p id="role-display-name" class="mb-0 text-muted">-</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Description:</label>
                                <p id="role-description" class="mb-0 text-muted">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Access Level:</label>
                                <p id="role-access-level" class="mb-0 text-muted">-</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p id="role-status" class="mb-0">-</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Total Users:</label>
                                <p id="role-user-count" class="mb-0 text-muted">-</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Permissions (<span id="permission-count">0</span>):</label>
                                <div id="role-permissions" class="d-flex flex-wrap gap-1">
                                    <!-- Permissions will be dynamically loaded -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Users with this role:</label>
                                <div id="role-users" class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                                    <!-- Users will be dynamically loaded -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Created:</label>
                                <p id="role-created-at" class="mb-0 text-muted">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Last Updated:</label>
                                <p id="role-updated-at" class="mb-0 text-muted">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="editRoleBtn">
                        <i class="ri-pencil-line"></i> Edit Role
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/vendor-scripts') ?>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            const table = $('#role-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true
            });

            // Add click event to table rows (except action buttons)
            $('#role-table tbody').on('click', 'tr', function(e) {
                // Don't trigger row click if clicking on action buttons
                if ($(e.target).closest('.btn').length > 0) {
                    return;
                }
                
                // Get role ID from the view button data attribute
                const viewBtn = $(this).find('.btn-info[onclick*="viewRole"]');
                if (viewBtn.length > 0) {
                    const onclickAttr = viewBtn.attr('onclick');
                    const roleId = onclickAttr.match(/viewRole\((\d+)\)/);
                    if (roleId && roleId[1]) {
                        viewRole(parseInt(roleId[1]));
                    }
                }
            });

            // Add hover effect to table rows
            $('#role-table tbody').on('mouseenter', 'tr', function() {
                $(this).addClass('table-hover-row');
            }).on('mouseleave', 'tr', function() {
                $(this).removeClass('table-hover-row');
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
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
        });

        function viewRole(id) {
            // Show loading state
            Swal.fire({
                title: 'Loading...',
                text: 'Fetching role details',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Fetch role details
            fetch(`/settings/roles/view/${id}`)
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    
                    if (data.success) {
                        populateRoleModal(data.data);
                        $('#roleDetailsModal').modal('show');
                    } else {
                        Swal.fire('Error', data.message || 'Failed to load role details', 'error');
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to load role details', 'error');
                });
        }

        function populateRoleModal(role) {
            // Basic role information
            document.getElementById('role-name').textContent = role.name || '-';
            document.getElementById('role-display-name').textContent = role.display_name || '-';
            document.getElementById('role-description').textContent = role.description || 'No description available';
            document.getElementById('role-access-level').textContent = role.access_level || '-';
            
            // Status badge
            const statusElement = document.getElementById('role-status');
            if (role.is_active == 1) {
                statusElement.innerHTML = '<span class="badge bg-success">Active</span>';
            } else {
                statusElement.innerHTML = '<span class="badge bg-danger">Inactive</span>';
            }
            
            // User count
            document.getElementById('role-user-count').textContent = role.admin_count || 0;
            
            // Permission count
            document.getElementById('permission-count').textContent = role.permission_count || 0;
            
            // Permissions
            const permissionsContainer = document.getElementById('role-permissions');
            permissionsContainer.innerHTML = '';
            if (role.permissions && role.permissions.length > 0) {
                role.permissions.forEach(permission => {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-info permission-badge';
                    badge.textContent = permission.name;
                    permissionsContainer.appendChild(badge);
                });
            } else {
                permissionsContainer.innerHTML = '<span class="text-muted">No permissions assigned</span>';
            }
            
            // Users
            const usersContainer = document.getElementById('role-users');
            usersContainer.innerHTML = '';
            if (role.admins && role.admins.length > 0) {
                role.admins.forEach(admin => {
                    const userItem = document.createElement('div');
                    userItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                    
                    const userInfo = document.createElement('div');
                    userInfo.innerHTML = `
                        <strong>${admin.name}</strong>
                        <br><small class="text-muted">${admin.email}</small>
                    `;
                    
                    const statusBadge = document.createElement('span');
                    statusBadge.className = admin.is_active == 1 ? 'badge bg-success' : 'badge bg-danger';
                    statusBadge.textContent = admin.is_active == 1 ? 'Active' : 'Inactive';
                    
                    userItem.appendChild(userInfo);
                    userItem.appendChild(statusBadge);
                    usersContainer.appendChild(userItem);
                });
            } else {
                usersContainer.innerHTML = '<div class="text-muted text-center p-3">No users assigned to this role</div>';
            }
            
            // Timestamps
            document.getElementById('role-created-at').textContent = role.created_at ? 
                new Date(role.created_at).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : '-';
                
            document.getElementById('role-updated-at').textContent = role.updated_at ? 
                new Date(role.updated_at).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : '-';
            
            // Set up edit button
            document.getElementById('editRoleBtn').onclick = function() {
                $('#roleDetailsModal').modal('hide');
                editRole(role.id);
            };
        }

        function editRole(id) {
            window.location.href = '/roles/edit/' + id;
        }

        function deleteRole(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f06548',
                cancelButtonColor: '#74788d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Implement delete functionality
                    Swal.fire('Info', 'Delete role functionality will be implemented soon', 'info');
                }
            });
        }
    </script>

</body>

</html>