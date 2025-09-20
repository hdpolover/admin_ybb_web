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

        #menuTable tbody tr {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        #menuTable tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1) !important;
        }
        
        .permission-badge {
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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Settings', 'title' => $pageTitle)); ?>

                    <!-- Menu Statistics -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-primary-subtle text-primary me-3">
                                            <i class="ri-menu-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0">Total Menu Items</p>
                                            <h4 class="mb-0"><?= count($menuItems ?? []) ?></h4>
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
                                            <i class="ri-check-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0">Active Items</p>
                                            <h4 class="mb-0"><?= count(array_filter($menuItems ?? [], function($item) { return $item->is_active; })) ?></h4>
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
                                            <i class="ri-shield-check-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0">Protected Items</p>
                                            <h4 class="mb-0"><?= count(array_filter($menuItems ?? [], function($item) { return !empty($item->required_permission); })) ?></h4>
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
                                            <i class="ri-key-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0">Permissions</p>
                                            <h4 class="mb-0"><?= count($permissions ?? []) ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Management Table -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h4 class="card-title mb-0">System Menu Items</h4>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#menuModal">
                                                <i class="ri-add-line align-bottom me-1"></i> Add Menu Item
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="menuTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Sort Order</th>
                                                    <th>Menu Item</th>
                                                    <th>URL</th>
                                                    <th>Required Permission</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($menuItems as $item): ?>
                                                <tr data-id="<?= $item->id ?>">
                                                    <td>
                                                        <span class="badge bg-secondary"><?= $item->sort_order ?></span>
                                                    </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($item->parent_id): ?>
                                                            <span class="text-muted me-2">└─</span>
                                                        <?php endif; ?>
                                                        <?php if ($item->icon): ?>
                                                            <i class="<?= $item->icon ?> me-2"></i>
                                                        <?php endif; ?>
                                                        <div>
                                                            <h6 class="mb-0"><?= $item->label ?></h6>
                                                            <small class="text-muted"><?= $item->name ?></small>
                                                            <?php if ($item->parent_id): ?>
                                                                <small class="text-info d-block">Sub-menu item</small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($item->badge_text): ?>
                                                            <span class="badge bg-<?= $item->badge_color ?: 'primary' ?> ms-2"><?= $item->badge_text ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code><?= $item->url ?></code>
                                                </td>
                                                <td>
                                                    <?php if ($item->required_permission): ?>
                                                        <span class="badge bg-info permission-badge"><?= $item->permission_display_name ?: $item->required_permission ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">No permission required</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($item->is_active): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            <i class="ri-more-fill"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a class="dropdown-item edit-menu" href="#" 
                                                                   data-id="<?= $item->id ?>"
                                                                   data-name="<?= $item->name ?>"
                                                                   data-label="<?= $item->label ?>"
                                                                   data-icon="<?= $item->icon ?>"
                                                                   data-url="<?= $item->url ?>"
                                                                   data-permission="<?= $item->required_permission ?>"
                                                                   data-parent="<?= $item->parent_id ?>"
                                                                   data-sort="<?= $item->sort_order ?>"
                                                                   data-badge-text="<?= $item->badge_text ?>"
                                                                   data-badge-color="<?= $item->badge_color ?>"
                                                                   data-active="<?= $item->is_active ?>">
                                                                    <i class="ri-edit-line align-bottom me-2 text-muted"></i> Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item toggle-status" href="#" data-id="<?= $item->id ?>">
                                                                    <i class="ri-refresh-line align-bottom me-2 text-muted"></i> 
                                                                    <?= $item->is_active ? 'Deactivate' : 'Activate' ?>
                                                                </a>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <a class="dropdown-item delete-menu text-danger" href="#" data-id="<?= $item->id ?>">
                                                                    <i class="ri-delete-bin-line align-bottom me-2"></i> Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
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
<!-- END layout-wrapper --><!-- Menu Modal -->
<div class="modal fade" id="menuModal" tabindex="-1" aria-labelledby="menuModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menuModalLabel">Add Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="menuForm">
                <div class="modal-body">
                    <input type="hidden" id="menuId" name="menu_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="menuName" class="form-label">Menu Name</label>
                                <input type="text" class="form-control" id="menuName" name="name" required>
                                <div class="form-text">Internal name (lowercase, underscore separated)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="menuLabel" class="form-label">Menu Label</label>
                                <input type="text" class="form-control" id="menuLabel" name="label" required>
                                <div class="form-text">Display name shown in the menu</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="menuIcon" class="form-label">Icon Class</label>
                                <input type="text" class="form-control" id="menuIcon" name="icon">
                                <div class="form-text">RemixIcon class (e.g., ri-dashboard-line)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="menuUrl" class="form-label">URL</label>
                                <input type="text" class="form-control" id="menuUrl" name="url" required>
                                <div class="form-text">Menu URL path (e.g., /dashboard)</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="menuPermission" class="form-label">Required Permission</label>
                                <select class="form-select" id="menuPermission" name="required_permission">
                                    <option value="">No permission required</option>
                                    <?php foreach ($permissions as $permission): ?>
                                    <option value="<?= $permission->name ?>"><?= $permission->display_name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="menuParent" class="form-label">Parent Menu</label>
                                <select class="form-select" id="menuParent" name="parent_id">
                                    <option value="">Top Level Menu</option>
                                    <?php foreach ($menuItems as $item): ?>
                                    <option value="<?= $item->id ?>"><?= $item->label ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Select a parent menu for sub-menu items</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="menuSort" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="menuSort" name="sort_order" value="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="badgeText" class="form-label">Badge Text</label>
                                <input type="text" class="form-control" id="badgeText" name="badge_text">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="badgeColor" class="form-label">Badge Color</label>
                                <select class="form-select" id="badgeColor" name="badge_color">
                                    <option value="">Default</option>
                                    <option value="primary">Primary</option>
                                    <option value="secondary">Secondary</option>
                                    <option value="success">Success</option>
                                    <option value="danger">Danger</option>
                                    <option value="warning">Warning</option>
                                    <option value="info">Info</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="menuActive" name="is_active" checked>
                                    <label class="form-check-label" for="menuActive">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Menu Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->include('partials/vendor-scripts') ?>

<!-- Required datatable js -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>

<!-- App js -->
<?= $this->include('partials/vendor-scripts') ?>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#menuTable').DataTable({
        order: [[0, 'asc']], // Sort by sort_order
        pageLength: 25,
        responsive: true
    });

    // Add/Edit menu form submission
    $('#menuForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const menuId = $('#menuId').val();
        const url = menuId ? `/settings/menu-management/update/${menuId}` : '/settings/menu-management/create';
        
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#menuModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 2000);
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
                    text: 'An error occurred while saving the menu item.'
                });
            }
        });
    });

    // Edit menu button
    $(document).on('click', '.edit-menu', function() {
        const data = $(this).data();
        
        $('#menuModalLabel').text('Edit Menu Item');
        $('#menuId').val(data.id);
        $('#menuName').val(data.name);
        $('#menuLabel').val(data.label);
        $('#menuIcon').val(data.icon);
        $('#menuUrl').val(data.url);
        $('#menuPermission').val(data.permission);
        $('#menuParent').val(data.parent);
        $('#menuSort').val(data.sort);
        $('#badgeText').val(data.badgeText);
        $('#badgeColor').val(data.badgeColor);
        $('#menuActive').prop('checked', data.active == 1);
        
        $('#menuModal').modal('show');
    });

    // Reset modal when adding new
    $('#menuModal').on('hidden.bs.modal', function() {
        $('#menuModalLabel').text('Add Menu Item');
        $('#menuForm')[0].reset();
        $('#menuId').val('');
    });

    // Toggle status
    $(document).on('click', '.toggle-status', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: `/settings/menu-management/toggle-status/${id}`,
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            }
        });
    });

    // Delete menu
    $(document).on('click', '.delete-menu', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/settings/menu-management/delete/${id}`,
                    method: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message
                            });
                        }
                    }
                });
            }
        });
    });
});
</script>

</body>

</html>