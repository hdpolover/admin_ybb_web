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
                                            <p class="text-muted mb-0">Categories</p>
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
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-primary" onclick="createRole()">
                                                <i class="ri-add-line align-bottom me-1"></i> Create Role
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
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
            $('#role-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true
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

        function createRole() {
            window.location.href = '/roles/create';
        }

        function viewRole(id) {
            // Implement view role functionality
            Swal.fire('Info', 'View role functionality will be implemented soon', 'info');
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