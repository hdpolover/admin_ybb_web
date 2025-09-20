<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => $pageTitle)); ?>

    <?= $this->include('partials/head-css') ?>

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <style>
        .permission-group {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 1rem;
            background: #f8f9fa;
        }
        
        .permission-group-header {
            background: #e9ecef;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #dee2e6;
            border-radius: 7px 7px 0 0;
            font-weight: 600;
        }
        
        .permission-group-body {
            padding: 1rem;
        }
        
        .permission-item {
            margin: 0.5rem 0;
            padding: 0.5rem;
            border-radius: 4px;
            background: white;
            border: 1px solid #e9ecef;
        }
        
        .permission-item:hover {
            background: #f8f9fa;
        }
        
        .form-check-label {
            font-weight: 500;
        }
        
        .permission-description {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .role-info-badge {
            margin: 2px;
            font-size: 0.75rem;
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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Role Management', 'title' => $pageTitle)); ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h4 class="card-title mb-0">Edit Role: <?= esc($role->display_name ?? 'Unknown') ?></h4>
                                            <div class="mt-2">
                                                <span class="badge bg-primary role-info-badge">ID: <?= $role->id ?? 'N/A' ?></span>
                                                <span class="badge bg-info role-info-badge">Access Level: <?= $role->access_level ?? 'N/A' ?></span>
                                                <span class="badge bg-<?= ($role->is_active ?? 0) ? 'success' : 'danger' ?> role-info-badge">
                                                    <?= ($role->is_active ?? 0) ? 'Active' : 'Inactive' ?>
                                                </span>
                                                <?php if (isset($role->permissions) && is_array($role->permissions)): ?>
                                                    <span class="badge bg-secondary role-info-badge"><?= count($role->permissions) ?> Permissions</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="/roles" class="btn btn-secondary">
                                                <i class="ri-arrow-left-line align-bottom me-1"></i> Back to Roles
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <form id="editRoleForm" method="POST">
                                    <?= csrf_field() ?>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Role Basic Information -->
                                            <div class="col-lg-6">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="name" name="name" 
                                                           value="<?= esc($role->name ?? '') ?>" readonly>
                                                    <div class="form-text">
                                                        Role name cannot be changed after creation.
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="display_name" name="display_name" 
                                                           value="<?= esc($role->display_name ?? '') ?>" required>
                                                    <div class="form-text">
                                                        Human-readable name that will be displayed in the interface.
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="description" class="form-label">Description</label>
                                                    <textarea class="form-control" id="description" name="description" 
                                                              rows="3"><?= esc($role->description ?? '') ?></textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="access_level" class="form-label">Access Level <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="access_level" name="access_level" required>
                                                        <option value="">Select access level</option>
                                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                                            <option value="<?= $i ?>" <?= ($role->access_level ?? 0) == $i ? 'selected' : '' ?>>
                                                                Level <?= $i ?> - 
                                                                <?php
                                                                $levels = [
                                                                    10 => 'Super Administrator',
                                                                    9 => 'Administrator', 
                                                                    8 => 'Senior Manager',
                                                                    7 => 'Manager',
                                                                    6 => 'Senior Coordinator',
                                                                    5 => 'Coordinator',
                                                                    4 => 'Senior Editor',
                                                                    3 => 'Editor',
                                                                    2 => 'Contributor',
                                                                    1 => 'Viewer'
                                                                ];
                                                                echo $levels[$i] ?? 'Custom Level';
                                                                ?>
                                                            </option>
                                                        <?php endfor; ?>
                                                    </select>
                                                    <div class="form-text">
                                                        Higher levels have broader access. Level 10 is the highest.
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                                               <?= ($role->is_active ?? 0) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="is_active">
                                                            Active Role
                                                        </label>
                                                        <div class="form-text">
                                                            Only active roles can be assigned to users.
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Role Statistics -->
                                                <?php if (isset($role->created_at)): ?>
                                                    <div class="mb-3">
                                                        <div class="card border-light">
                                                            <div class="card-body p-3">
                                                                <h6 class="card-title">Role Information</h6>
                                                                <small class="text-muted">
                                                                    <strong>Created:</strong> <?= date('M d, Y H:i', strtotime($role->created_at)) ?><br>
                                                                    <?php if (isset($role->updated_at) && $role->updated_at): ?>
                                                                        <strong>Last Updated:</strong> <?= date('M d, Y H:i', strtotime($role->updated_at)) ?><br>
                                                                    <?php endif; ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Permission Assignment -->
                                            <div class="col-lg-6">
                                                <h5 class="mb-3">Assign Permissions</h5>
                                                <div class="form-text mb-3">
                                                    Select the permissions this role should have.
                                                </div>
                                                
                                                <?php if (isset($permissions) && is_array($permissions)): ?>
                                                    <?php foreach ($permissions as $category => $perms): ?>
                                                        <div class="permission-group">
                                                            <div class="permission-group-header">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <span><?= ucwords(str_replace('_', ' ', $category)) ?></span>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input category-toggle" type="checkbox" 
                                                                               id="category_<?= $category ?>" data-category="<?= $category ?>">
                                                                        <label class="form-check-label" for="category_<?= $category ?>">
                                                                            Select All
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="permission-group-body">
                                                                <?php foreach ($perms as $permission): ?>
                                                                    <div class="permission-item">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input permission-checkbox" 
                                                                                   type="checkbox" 
                                                                                   id="perm_<?= $permission->id ?>" 
                                                                                   name="permissions[]" 
                                                                                   value="<?= $permission->id ?>"
                                                                                   data-category="<?= $category ?>"
                                                                                   <?= (isset($rolePermissions) && in_array($permission->id, $rolePermissions)) ? 'checked' : '' ?>>
                                                                            <label class="form-check-label" for="perm_<?= $permission->id ?>">
                                                                                <?= esc($permission->display_name ?? '') ?>
                                                                            </label>
                                                                            <?php if (!empty($permission->description)): ?>
                                                                                <div class="permission-description">
                                                                                    <?= esc($permission->description) ?>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="alert alert-info">
                                                        <i class="ri-information-line me-2"></i>
                                                        No permissions available. Please create permissions first.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <?php if (isset($role->id)): ?>
                                                    <button type="button" class="btn btn-danger" onclick="deleteRole(<?= $role->id ?>)">
                                                        <i class="ri-delete-bin-line align-bottom me-1"></i> Delete Role
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="/roles" class="btn btn-secondary">
                                                    <i class="ri-close-line align-bottom me-1"></i> Cancel
                                                </a>
                                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                                    <i class="ri-save-line align-bottom me-1"></i> Update Role
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
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

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('editRoleForm');
            const submitBtn = document.getElementById('submitBtn');

            // Category toggle functionality
            document.querySelectorAll('.category-toggle').forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    const category = this.dataset.category;
                    const checkboxes = document.querySelectorAll(`input[data-category="${category}"].permission-checkbox`);
                    
                    checkboxes.forEach(function(checkbox) {
                        checkbox.checked = toggle.checked;
                    });
                });
            });

            // Update category toggle when individual permissions change
            document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    updateCategoryToggle(this.dataset.category);
                });
            });

            // Initialize category toggles based on current permissions
            const categories = [...new Set(Array.from(document.querySelectorAll('.permission-checkbox')).map(cb => cb.dataset.category))];
            categories.forEach(category => {
                updateCategoryToggle(category);
            });

            function updateCategoryToggle(category) {
                const categoryCheckboxes = document.querySelectorAll(`input[data-category="${category}"].permission-checkbox`);
                const categoryToggle = document.querySelector(`input[data-category="${category}"].category-toggle`);
                
                if (!categoryToggle) return;
                
                const checkedCount = Array.from(categoryCheckboxes).filter(cb => cb.checked).length;
                const totalCount = categoryCheckboxes.length;
                
                if (checkedCount === 0) {
                    categoryToggle.checked = false;
                    categoryToggle.indeterminate = false;
                } else if (checkedCount === totalCount) {
                    categoryToggle.checked = true;
                    categoryToggle.indeterminate = false;
                } else {
                    categoryToggle.checked = false;
                    categoryToggle.indeterminate = true;
                }
            }

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-2-line align-bottom me-1"></i> Updating...';
                
                // Get form data
                const formData = new FormData(form);
                
                // Submit via fetch
                fetch('/roles/edit/<?= $role->id ?? 0 ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message || 'Role updated successfully',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        }).then(() => {
                            window.location.href = '/roles';
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to update role',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                        
                        // Show validation errors if available
                        if (data.errors) {
                            console.log('Validation errors:', data.errors);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                })
                .finally(() => {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="ri-save-line align-bottom me-1"></i> Update Role';
                });
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

        function deleteRole(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the role and cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f06548',
                cancelButtonColor: '#74788d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send delete request
                    fetch(`/roles/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message || 'Role has been deleted successfully',
                                icon: 'success',
                                confirmButtonColor: '#0ab39c'
                            }).then(() => {
                                window.location.href = '/roles';
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to delete role',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An unexpected error occurred',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    });
                }
            });
        }
    </script>

</body>

</html>