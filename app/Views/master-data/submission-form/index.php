<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title' => 'Starter')); ?>

    <?= $this->include('partials/head-css') ?>

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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Submission Form')); ?>

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><?= $currentProgram->name ?> Submission Form</h4>
                        </div>
                        <div class="card-body cursor-default-hover">
                            <!-- Nav tabs -->
                            <ul class="nav nav-pills nav-justified mb-3" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link cursor-on-hover active" data-bs-toggle="tab" href="#participation-category" role="tab" aria-selected="true" tabindex="-1">
                                        Participation Category
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link cursor-on-hover" data-bs-toggle="tab" href="#sub-themes" role="tab" aria-selected="false" tabindex="-1">
                                        Sub themes
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link cursor-on-hover" data-bs-toggle="tab" href="#essays" role="tab" aria-selected="false" tabindex="-1">
                                        Essays
                                    </a>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content text-muted">
                                <div class="tab-pane active" id="participation-category" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6>Participation Category</h6>
                                            <p class="mb-0">
                                                Configure participation categories for this submission form.
                                            </p>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-primary" id="add-category-btn">
                                                <i class="ri-add-line align-bottom"></i> Add Category
                                            </button>
                                        </div>
                                    </div>
                                    <div class="table-responsive mt-4">
                                        <table class="table table-bordered dt-responsive nowrap table-striped align-middle">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Category Name</th>
                                                    <th>Description</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $i = 1; ?>
                                                <?php foreach ($competitionCategories as $category) : ?>
                                                    <tr>
                                                        <td><?= $i++ ?></td>
                                                        <td><?= $category->category ?? '-' ?></td>
                                                        <td><?= $category->desc ?? '-' ?></td>
                                                        <td>
                                                            <?php if ($category->is_active) : ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php else : ?>
                                                                <span class="badge bg-danger">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="dropdown d-inline-block">
                                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="ri-more-fill align-middle"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li><a href="#" class="dropdown-item edit-category" data-id="<?= $category->id ?>"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a></li>
                                                                    <li>
                                                                        <a href="#" class="dropdown-item remove-category" data-id="<?= $category->id ?>">
                                                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($competitionCategories)) : ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No categories found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane" id="sub-themes" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6>Sub Themes</h6>
                                            <p class="mb-0">
                                                Manage the sub themes available for participants to select.
                                            </p>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-primary" id="add-subtheme-btn">
                                                <i class="ri-add-line align-bottom"></i> Add Sub Theme
                                            </button>
                                        </div>
                                    </div>
                                    <div class="table-responsive mt-4">
                                        <table class="table table-bordered dt-responsive nowrap table-striped align-middle">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Sub Theme</th>
                                                    <th>Description</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $i = 1; ?>
                                                <?php foreach ($programSubThemes as $subTheme) : ?>
                                                    <tr>
                                                        <td><?= $i++ ?></td>
                                                        <td><?= $subTheme->name ?? '-' ?></td>
                                                        <td><?= $subTheme->desc ?? '-' ?></td>
                                                        <td>
                                                            <?php if ($subTheme->is_active) : ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php else : ?>
                                                                <span class="badge bg-danger">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="dropdown d-inline-block">
                                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="ri-more-fill align-middle"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li><a href="#" class="dropdown-item edit-subtheme" data-id="<?= $subTheme->id ?>"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a></li>
                                                                    <li>
                                                                        <a href="#" class="dropdown-item remove-subtheme" data-id="<?= $subTheme->id ?>">
                                                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($programSubThemes)) : ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No sub themes found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                                <div class="tab-pane" id="essays" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6>Essays</h6>
                                            <p class="mb-0">
                                                Configure essay requirements and guidelines for participants.
                                            </p>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-primary" id="add-essay-btn">
                                                <i class="ri-add-line align-bottom"></i> Add Essay
                                            </button>
                                        </div>
                                    </div>
                                    <div class="table-responsive mt-4">
                                        <table class="table table-bordered dt-responsive nowrap table-striped align-middle">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Question</th>
                                                    <th>Word Limit</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $i = 1; ?>
                                                <?php foreach ($programEssays as $essay) : ?>
                                                    <tr>
                                                        <td><?= $i++ ?></td>
                                                        <td><?= $essay->questions ?? '-' ?></td>
                                                        <td><?= $essay->max_word_count ?? 'N/A' ?></td>
                                                        <td>
                                                            <?php if ($essay->is_active) : ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php else : ?>
                                                                <span class="badge bg-danger">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="dropdown d-inline-block">
                                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="ri-more-fill align-middle"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li><a href="#" class="dropdown-item edit-essay" data-id="<?= $essay->id ?>"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a></li>
                                                                    <li>
                                                                        <a href="#" class="dropdown-item remove-essay" data-id="<?= $essay->id ?>">
                                                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($programEssays)) : ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No essays found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->    <?= $this->include('partials/vendor-scripts') ?>

    <!-- Include Modal Components -->
    <?= $this->include('master-data/submission-form/category_modal') ?>
    <?= $this->include('master-data/submission-form/subtheme_modal') ?>
    <?= $this->include('master-data/submission-form/essay_modal') ?>

    <!-- Sweet Alert js -->
    <script src="/assets/libs/sweetalert2/sweetalert2.min.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Category Management
            document.getElementById('add-category-btn').addEventListener('click', function() {
                document.getElementById('addCategoryForm').reset();
                var modal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
                modal.show();
            });

            document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const isActive = document.getElementById('categoryIsActive').checked ? '1' : '0';
                formData.set('is_active', isActive);

                fetch('<?= site_url('master-data/submission-form/add-category') ?>', {
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
                            text: data.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message || 'Failed to add category', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An unexpected error occurred', 'error');
                });
            });

            // Category Edit
            document.querySelectorAll('.edit-category').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    
                    fetch(`<?= site_url('master-data/submission-form/get-category-by-id/') ?>${id}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const category = data.data;
                            document.getElementById('editCategoryId').value = category.id;
                            document.getElementById('editCategoryName').value = category.category;
                            document.getElementById('editCategoryDescription').value = category.desc;
                            document.getElementById('editCategoryIsActive').checked = category.is_active == 1;
                            
                            var modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
                            modal.show();
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to fetch category details', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'An unexpected error occurred', 'error');
                    });
                });
            });

            document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const id = document.getElementById('editCategoryId').value;
                const isActive = document.getElementById('editCategoryIsActive').checked ? '1' : '0';
                formData.set('is_active', isActive);

                fetch(`<?= site_url('master-data/submission-form/update-category/') ?>${id}`, {
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
                            text: data.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message || 'Failed to update category', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An unexpected error occurred', 'error');
                });
            });

            // Category Delete
            document.querySelectorAll('.remove-category').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    
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
                            fetch(`<?= site_url('master-data/submission-form/delete-category/') ?>${id}`, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: data.message,
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', data.message || 'Failed to delete category', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error!', 'An unexpected error occurred', 'error');
                            });
                        }
                    });
                });
            });

            // Sub-theme Management
            document.getElementById('add-subtheme-btn').addEventListener('click', function() {
                document.getElementById('addSubthemeForm').reset();
                var modal = new bootstrap.Modal(document.getElementById('addSubthemeModal'));
                modal.show();
            });

            document.getElementById('addSubthemeForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const isActive = document.getElementById('subthemeIsActive').checked ? '1' : '0';
                formData.set('is_active', isActive);                fetch('<?= site_url('master-data/submission-form/add-sub-theme') ?>', {
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
                            text: data.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message || 'Failed to add sub theme', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An unexpected error occurred', 'error');
                });
            });

            // Sub-theme Edit
            document.querySelectorAll('.edit-subtheme').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    
                    fetch(`<?= site_url('master-data/submission-form/get-sub-theme-by-id/') ?>${id}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const subtheme = data.data;
                            document.getElementById('editSubthemeId').value = subtheme.id;
                            document.getElementById('editSubthemeName').value = subtheme.name;
                            document.getElementById('editSubthemeDescription').value = subtheme.desc;
                            document.getElementById('editSubthemeIsActive').checked = subtheme.is_active == 1;
                            
                            var modal = new bootstrap.Modal(document.getElementById('editSubthemeModal'));
                            modal.show();
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to fetch sub theme details', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'An unexpected error occurred', 'error');
                    });
                });
            });

            document.getElementById('editSubthemeForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const id = document.getElementById('editSubthemeId').value;
                const isActive = document.getElementById('editSubthemeIsActive').checked ? '1' : '0';
                formData.set('is_active', isActive);

                fetch(`<?= site_url('master-data/submission-form/update-sub-theme/') ?>${id}`, {
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
                            text: data.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message || 'Failed to update sub theme', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An unexpected error occurred', 'error');
                });
            });

            // Sub-theme Delete
            document.querySelectorAll('.remove-subtheme').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    
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
                            fetch(`<?= site_url('master-data/submission-form/delete-sub-theme/') ?>${id}`, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: data.message,
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', data.message || 'Failed to delete sub theme', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error!', 'An unexpected error occurred', 'error');
                            });
                        }
                    });
                });
            });
            
            // Essay Management
            document.getElementById('add-essay-btn').addEventListener('click', function() {
                document.getElementById('addEssayForm').reset();
                var modal = new bootstrap.Modal(document.getElementById('addEssayModal'));
                modal.show();
            });

            document.getElementById('addEssayForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const isActive = document.getElementById('essayIsActive').checked ? '1' : '0';
                formData.set('is_active', isActive);

                fetch('<?= site_url('master-data/submission-form/add-essay') ?>', {
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
                            text: data.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message || 'Failed to add essay', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An unexpected error occurred', 'error');
                });
            });

            // Essay Edit
            document.querySelectorAll('.edit-essay').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    
                    fetch(`<?= site_url('master-data/submission-form/get-essay-by-id/') ?>${id}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const essay = data.data;
                            document.getElementById('editEssayId').value = essay.id;
                            document.getElementById('editEssayQuestion').value = essay.questions;
                            document.getElementById('editEssayWordLimit').value = essay.max_word_count || '';
                            document.getElementById('editEssayIsActive').checked = essay.is_active == 1;
                            
                            var modal = new bootstrap.Modal(document.getElementById('editEssayModal'));
                            modal.show();
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to fetch essay details', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'An unexpected error occurred', 'error');
                    });
                });
            });

            document.getElementById('editEssayForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const id = document.getElementById('editEssayId').value;
                const isActive = document.getElementById('editEssayIsActive').checked ? '1' : '0';
                formData.set('is_active', isActive);

                fetch(`<?= site_url('master-data/submission-form/update-essay/') ?>${id}`, {
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
                            text: data.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message || 'Failed to update essay', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An unexpected error occurred', 'error');
                });
            });

            // Essay Delete
            document.querySelectorAll('.remove-essay').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    
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
                            fetch(`<?= site_url('master-data/submission-form/delete-essay/') ?>${id}`, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: data.message,
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', data.message || 'Failed to delete essay', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error!', 'An unexpected error occurred', 'error');
                            });
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>