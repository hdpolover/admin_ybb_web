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
                                        <h4 class="card-title mb-0">Create New Role</h4>
                                        <div>
                                            <a href="/roles" class="btn btn-secondary">
                                                <i class="ri-arrow-left-line align-bottom me-1"></i> Back to Roles
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <form id="createRoleForm" method="POST">
                                    <?= csrf_field() ?>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Role Basic Information -->
                                            <div class="col-lg-6">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="name" name="name" 
                                                           placeholder="e.g., content_editor" required>
                                                    <div class="form-text">
                                                        Use lowercase letters, numbers, and underscores only. This will be used internally.
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="display_name" name="display_name" 
                                                           placeholder="e.g., Content Editor" required>
                                                    <div class="form-text">
                                                        Human-readable name that will be displayed in the interface.
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="description" class="form-label">Description</label>
                                                    <textarea class="form-control" id="description" name="description" 
                                                              rows="3" placeholder="Brief description of this role and its responsibilities"></textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="access_level" class="form-label">Access Level <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="access_level" name="access_level" required>
                                                        <option value="">Select access level</option>
                                                        <option value="10">Level 10 - Super Administrator</option>
                                                        <option value="9">Level 9 - Administrator</option>
                                                        <option value="8">Level 8 - Senior Manager</option>
                                                        <option value="7">Level 7 - Manager</option>
                                                        <option value="6">Level 6 - Senior Coordinator</option>
                                                        <option value="5">Level 5 - Coordinator</option>
                                                        <option value="4">Level 4 - Senior Editor</option>
                                                        <option value="3">Level 3 - Editor</option>
                                                        <option value="2">Level 2 - Contributor</option>
                                                        <option value="1">Level 1 - Viewer</option>
                                                    </select>
                                                    <div class="form-text">
                                                        Higher levels have broader access. Level 10 is the highest.
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                                        <label class="form-check-label" for="is_active">
                                                            Active Role
                                                        </label>
                                                        <div class="form-text">
                                                            Only active roles can be assigned to users.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Permission Assignment -->
                                            <div class="col-lg-6">
                                                <h5 class="mb-3">Assign Permissions</h5>
                                                <div class="form-text mb-3">
                                                    Select the permissions this role should have. You can modify these later.
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
                                                                                   data-category="<?= $category ?>">
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
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="/roles" class="btn btn-secondary">
                                                <i class="ri-close-line align-bottom me-1"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="ri-save-line align-bottom me-1"></i> Create Role
                                            </button>
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
            const form = document.getElementById('createRoleForm');
            const submitBtn = document.getElementById('submitBtn');

            // Auto-generate role name from display name
            document.getElementById('display_name').addEventListener('input', function() {
                const displayName = this.value;
                const roleName = displayName.toLowerCase()
                    .replace(/[^a-z0-9\s]/g, '')
                    .replace(/\s+/g, '_')
                    .replace(/_{2,}/g, '_')
                    .replace(/^_|_$/g, '');
                document.getElementById('name').value = roleName;
            });

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
                    const category = this.dataset.category;
                    const categoryCheckboxes = document.querySelectorAll(`input[data-category="${category}"].permission-checkbox`);
                    const categoryToggle = document.querySelector(`input[data-category="${category}"].category-toggle`);
                    
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
                });
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-2-line align-bottom me-1"></i> Creating...';
                
                // Get form data
                const formData = new FormData(form);
                
                // Debug: Log form data
                console.log('Form data being submitted:');
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }
                
                // Submit via fetch
                fetch('/roles/create', {
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
                            text: data.message || 'Role created successfully',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        }).then(() => {
                            window.location.href = '/roles';
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to create role',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                        
                        // Show validation errors if available
                        if (data.errors) {
                            console.log('Validation errors:', data.errors);
                            // You could show specific field errors here
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
                    submitBtn.innerHTML = '<i class="ri-save-line align-bottom me-1"></i> Create Role';
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
    </script>

</body>

</html>