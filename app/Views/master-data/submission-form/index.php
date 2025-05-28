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
                                        Participation Categories
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
                                    </div>                                </div>
                                
                                <div class="tab-pane" id="sub-themes" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6>Sub Themes</h6>
                                            <p class="mb-0">
                                                Configure sub themes for this submission form.
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
                                                <?php $i = 1; ?>                                                <?php foreach ($programSubThemes as $subtheme) : ?>
                                                    <tr>
                                                        <td><?= $i++ ?></td>
                                                        <td><?= $subtheme->name ?? '-' ?></td>
                                                        <td><?= $subtheme->desc ?? '-' ?></td>
                                                        <td>
                                                            <?php if ($subtheme->is_active) : ?>
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
                                                                    <li><a href="#" class="dropdown-item edit-subtheme" data-id="<?= $subtheme->id ?>"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a></li>
                                                                    <li>
                                                                        <a href="#" class="dropdown-item remove-subtheme" data-id="<?= $subtheme->id ?>">
                                                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>                                                <?php if (empty($programSubThemes)) : ?>
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
                                                Configure essays for this submission form.
                                            </p>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-primary" id="add-essay-btn">
                                                <i class="ri-add-line align-bottom"></i> Add Essay
                                            </button>
                                        </div>
                                    </div>
                                    <div class="table-responsive mt-4">
                                        <table class="table table-bordered dt-responsive nowrap table-striped align-middle">                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Question</th>
                                                    <th>Word Limit</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $i = 1; ?>                                                <?php foreach ($programEssays as $essay) : ?>
                                                    <tr>
                                                        <td><?= $i++ ?></td>
                                                        <td><?= $essay->questions ?? '-' ?></td>
                                                        <td><?= $essay->max_word_count ? "Max {$essay->max_word_count} words" : '-' ?></td>
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
                                                        <td colspan="5" class="text-center">No essays found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Add Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCategoryForm">
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categoryName" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
                            <div class="invalid-feedback" id="description-error"></div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="categoryIsActive" name="is_active" checked>
                            <label class="form-check-label" for="categoryIsActive">Active</label>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="addCategorySubmitBtn">
                                <span class="d-none spinner-border spinner-border-sm me-1" id="addCategorySpinner"></span>
                                Add
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Edit Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm">
                        <input type="hidden" id="editCategoryId">
                        <div class="mb-3">
                            <label for="editCategoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editCategoryName" name="name" required>
                            <div class="invalid-feedback" id="edit-name-error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="editCategoryDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editCategoryDescription" name="description" rows="3"></textarea>
                            <div class="invalid-feedback" id="edit-description-error"></div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="editCategoryIsActive" name="is_active">
                            <label class="form-check-label" for="editCategoryIsActive">Active</label>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="editCategorySubmitBtn">
                                <span class="d-none spinner-border spinner-border-sm me-1" id="editCategorySpinner"></span>
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>    </div>

    <!-- Subtheme Add Modal -->
    <div class="modal fade" id="addSubthemeModal" tabindex="-1" aria-labelledby="addSubthemeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSubthemeModalLabel">Add Sub Theme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>                <div class="modal-body">
                    <form id="addSubthemeForm">
                        <div class="mb-3">
                            <label for="subthemeName" class="form-label">Sub Theme <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subthemeName" name="name" required>
                            <div class="invalid-feedback" id="name-error-subtheme"></div>
                        </div>
                        <div class="mb-3">
                            <label for="subthemeDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="subthemeDescription" name="description" rows="3"></textarea>
                            <div class="invalid-feedback" id="description-error-subtheme"></div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="subthemeIsActive" name="is_active" checked>
                            <label class="form-check-label" for="subthemeIsActive">Active</label>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="addSubthemeSubmitBtn">
                                <span class="d-none spinner-border spinner-border-sm me-1" id="addSubthemeSpinner"></span>
                                Add
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Subtheme Edit Modal -->
    <div class="modal fade" id="editSubthemeModal" tabindex="-1" aria-labelledby="editSubthemeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSubthemeModalLabel">Edit Sub Theme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSubthemeForm">
                        <input type="hidden" id="editSubthemeId">
                        <div class="mb-3">
                            <label for="editSubthemeName" class="form-label">Sub Theme <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editSubthemeName" name="name" required>
                            <div class="invalid-feedback" id="edit-name-error-subtheme"></div>
                        </div>
                        <div class="mb-3">
                            <label for="editSubthemeDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editSubthemeDescription" name="description" rows="3"></textarea>
                            <div class="invalid-feedback" id="edit-description-error-subtheme"></div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="editSubthemeIsActive" name="is_active">
                            <label class="form-check-label" for="editSubthemeIsActive">Active</label>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="editSubthemeSubmitBtn">
                                <span class="d-none spinner-border spinner-border-sm me-1" id="editSubthemeSpinner"></span>
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Essay Add Modal -->
    <div class="modal fade" id="addEssayModal" tabindex="-1" aria-labelledby="addEssayModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEssayModalLabel">Add Essay</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>                <div class="modal-body">
                    <form id="addEssayForm">
                        <div class="mb-3">
                            <label for="essayQuestions" class="form-label">Essay Question <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="essayQuestions" name="questions" rows="3" required></textarea>
                            <div class="invalid-feedback" id="questions-error-essay"></div>
                        </div>
                        <div class="mb-3">
                            <label for="essayMaxWordCount" class="form-label">Maximum Word Count</label>
                            <input type="number" class="form-control" id="essayMaxWordCount" name="max_word_count">
                            <div class="invalid-feedback" id="max_word_count-error-essay"></div>
                            <small class="text-muted">Leave empty for no word limit</small>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="essayIsActive" name="is_active" checked>
                            <label class="form-check-label" for="essayIsActive">Active</label>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="addEssaySubmitBtn">
                                <span class="d-none spinner-border spinner-border-sm me-1" id="addEssaySpinner"></span>
                                Add
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Essay Edit Modal -->
    <div class="modal fade" id="editEssayModal" tabindex="-1" aria-labelledby="editEssayModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEssayModalLabel">Edit Essay</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>                <div class="modal-body">
                    <form id="editEssayForm">
                        <input type="hidden" id="editEssayId">
                        <div class="mb-3">
                            <label for="editEssayQuestions" class="form-label">Essay Question <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="editEssayQuestions" name="questions" rows="3" required></textarea>
                            <div class="invalid-feedback" id="edit-questions-error-essay"></div>
                        </div>
                        <div class="mb-3">
                            <label for="editEssayMaxWordCount" class="form-label">Maximum Word Count</label>
                            <input type="number" class="form-control" id="editEssayMaxWordCount" name="max_word_count">
                            <div class="invalid-feedback" id="edit-max_word_count-error-essay"></div>
                            <small class="text-muted">Leave empty for no word limit</small>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="editEssayIsActive" name="is_active">
                            <label class="form-check-label" for="editEssayIsActive">Active</label>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="editEssaySubmitBtn">
                                <span class="d-none spinner-border spinner-border-sm me-1" id="editEssaySpinner"></span>
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Your other existing modals and content here -->

    <?= $this->include('partials/vendor-scripts') ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Reset form errors
            function resetFormErrors(formId) {
                const form = document.getElementById(formId);
                const inputs = form.querySelectorAll('.form-control');
                inputs.forEach(input => {
                    input.classList.remove('is-invalid');
                });
                const errorElements = form.querySelectorAll('.invalid-feedback');
                errorElements.forEach(element => {
                    element.textContent = '';
                });
            }

            // Display form errors
            function displayFormErrors(formId, errors) {
                for (const field in errors) {
                    const errorElement = document.getElementById(`${formId === 'editCategoryForm' ? 'edit-' : ''}${field}-error`);
                    const inputElement = document.getElementById(`${formId === 'editCategoryForm' ? 'edit' : ''}Category${field.charAt(0).toUpperCase() + field.slice(1)}`);
                    
                    if (errorElement && inputElement) {
                        errorElement.textContent = errors[field];
                        inputElement.classList.add('is-invalid');
                    }
                }
            }

            // Category Management
            document.getElementById('add-category-btn').addEventListener('click', function() {
                resetFormErrors('addCategoryForm');
                document.getElementById('addCategoryForm').reset();
                document.getElementById('categoryIsActive').checked = true;
                var modal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
                modal.show();
            });

            document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                resetFormErrors('addCategoryForm');

                // Show loading spinner
                const submitBtn = document.getElementById('addCategorySubmitBtn');
                const spinner = document.getElementById('addCategorySpinner');
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');

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
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');

                    if (data.success) {
                        // Close the modal
                        var modal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
                        modal.hide();

                        // Show success message
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
                        // Show error message
                        if (data.errors) {
                            displayFormErrors('addCategoryForm', data.errors);
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to add category', 'error');
                        }
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An unexpected error occurred', 'error');
                });
            });

            // Category Edit
            document.querySelectorAll('.edit-category').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    resetFormErrors('editCategoryForm');
                    const id = this.getAttribute('data-id');
                    
                    // Show loading indicator
                    Swal.fire({
                        title: 'Loading...',
                        html: 'Fetching category data',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch(`<?= site_url('master-data/submission-form/get-category-by-id/') ?>${id}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();
                        
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
                        Swal.close();
                        console.error('Error:', error);
                        Swal.fire('Error!', 'An unexpected error occurred', 'error');
                    });
                });
            });

            document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                resetFormErrors('editCategoryForm');
                
                // Show loading spinner
                const submitBtn = document.getElementById('editCategorySubmitBtn');
                const spinner = document.getElementById('editCategorySpinner');
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                
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
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    
                    if (data.success) {
                        // Close the modal
                        var modal = bootstrap.Modal.getInstance(document.getElementById('editCategoryModal'));
                        modal.hide();
                        
                        // Show success message
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
                        // Show error message
                        if (data.errors) {
                            displayFormErrors('editCategoryForm', data.errors);
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to update category', 'error');
                        }
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    
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
                        text: "This will deactivate the category. You can reactivate it later.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, deactivate it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading indicator
                            Swal.fire({
                                title: 'Deleting...',
                                html: 'Processing your request',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            fetch(`<?= site_url('master-data/submission-form/delete-category/') ?>${id}`, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                Swal.close();
                                
                                if (data.success) {                                    Swal.fire({
                                        title: 'Deactivated!',
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
                                Swal.close();
                                console.error('Error:', error);
                                Swal.fire('Error!', 'An unexpected error occurred', 'error');
                            });
                        }
                    });
                });            });

            // Subtheme Management
            document.getElementById('add-subtheme-btn')?.addEventListener('click', function() {
                resetFormErrors('addSubthemeForm');
                document.getElementById('addSubthemeForm').reset();
                document.getElementById('subthemeIsActive').checked = true;
                var modal = new bootstrap.Modal(document.getElementById('addSubthemeModal'));
                modal.show();
            });

            document.getElementById('addSubthemeForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                resetFormErrors('addSubthemeForm');

                // Show loading spinner
                const submitBtn = document.getElementById('addSubthemeSubmitBtn');
                const spinner = document.getElementById('addSubthemeSpinner');
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');

                const formData = new FormData(this);
                const isActive = document.getElementById('subthemeIsActive').checked ? '1' : '0';
                formData.set('is_active', isActive);

                fetch('<?= site_url('master-data/submission-form/add-subtheme') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');

                    if (data.success) {
                        // Close the modal
                        var modal = bootstrap.Modal.getInstance(document.getElementById('addSubthemeModal'));
                        modal.hide();

                        // Show success message
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
                        // Show error message
                        if (data.errors) {
                            for (const field in data.errors) {
                                const errorElement = document.getElementById(`${field}-error-subtheme`);
                                const inputElement = document.getElementById(`subtheme${field.charAt(0).toUpperCase() + field.slice(1)}`);
                                
                                if (errorElement && inputElement) {
                                    errorElement.textContent = data.errors[field];
                                    inputElement.classList.add('is-invalid');
                                }
                            }
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to add subtheme', 'error');
                        }
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An unexpected error occurred', 'error');
                });
            });

            // Subtheme Edit
            document.querySelectorAll('.edit-subtheme')?.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    resetFormErrors('editSubthemeForm');
                    const id = this.getAttribute('data-id');
                    
                    // Show loading indicator
                    Swal.fire({
                        title: 'Loading...',
                        html: 'Fetching subtheme data',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch(`<?= site_url('master-data/submission-form/get-subtheme-by-id/') ?>${id}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();
                          if (data.success) {
                            const subtheme = data.data;
                            document.getElementById('editSubthemeId').value = subtheme.id;
                            document.getElementById('editSubthemeName').value = subtheme.name;
                            document.getElementById('editSubthemeDescription').value = subtheme.desc;
                            document.getElementById('editSubthemeIsActive').checked = subtheme.is_active == 1;
                            
                            var modal = new bootstrap.Modal(document.getElementById('editSubthemeModal'));
                            modal.show();
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to fetch subtheme details', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        console.error('Error:', error);
                        Swal.fire('Error!', 'An unexpected error occurred', 'error');
                    });
                });
            });

            document.getElementById('editSubthemeForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                resetFormErrors('editSubthemeForm');
                
                // Show loading spinner
                const submitBtn = document.getElementById('editSubthemeSubmitBtn');
                const spinner = document.getElementById('editSubthemeSpinner');
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                
                const formData = new FormData(this);
                const id = document.getElementById('editSubthemeId').value;
                const isActive = document.getElementById('editSubthemeIsActive').checked ? '1' : '0';
                formData.set('is_active', isActive);

                fetch(`<?= site_url('master-data/submission-form/update-subtheme/') ?>${id}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    
                    if (data.success) {
                        // Close the modal
                        var modal = bootstrap.Modal.getInstance(document.getElementById('editSubthemeModal'));
                        modal.hide();
                        
                        // Show success message
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
                        // Show error message
                        if (data.errors) {
                            for (const field in data.errors) {
                                const errorElement = document.getElementById(`edit-${field}-error-subtheme`);
                                const inputElement = document.getElementById(`editSubtheme${field.charAt(0).toUpperCase() + field.slice(1)}`);
                                
                                if (errorElement && inputElement) {
                                    errorElement.textContent = data.errors[field];
                                    inputElement.classList.add('is-invalid');
                                }
                            }
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to update subtheme', 'error');
                        }
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An unexpected error occurred', 'error');
                });
            });

            // Subtheme Delete
            document.querySelectorAll('.remove-subtheme')?.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will deactivate the subtheme. You can reactivate it later.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, deactivate it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading indicator
                            Swal.fire({
                                title: 'Deleting...',
                                html: 'Processing your request',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            fetch(`<?= site_url('master-data/submission-form/delete-subtheme/') ?>${id}`, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                Swal.close();
                                
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Deactivated!',
                                        text: data.message,
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', data.message || 'Failed to delete subtheme', 'error');
                                }
                            })
                            .catch(error => {
                                Swal.close();
                                console.error('Error:', error);
                                Swal.fire('Error!', 'An unexpected error occurred', 'error');
                            });
                        }
                    });
                });
            });

            // Essay Management
            document.getElementById('add-essay-btn')?.addEventListener('click', function() {
                resetFormErrors('addEssayForm');
                document.getElementById('addEssayForm').reset();
                document.getElementById('essayIsActive').checked = true;
                var modal = new bootstrap.Modal(document.getElementById('addEssayModal'));
                modal.show();
            });

            document.getElementById('addEssayForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                resetFormErrors('addEssayForm');

                // Show loading spinner
                const submitBtn = document.getElementById('addEssaySubmitBtn');
                const spinner = document.getElementById('addEssaySpinner');
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');

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
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');

                    if (data.success) {
                        // Close the modal
                        var modal = bootstrap.Modal.getInstance(document.getElementById('addEssayModal'));
                        modal.hide();

                        // Show success message
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
                        // Show error message
                        if (data.errors) {
                            for (const field in data.errors) {
                                const errorElement = document.getElementById(`${field}-error-essay`);
                                const inputElement = document.getElementById(`essay${field.charAt(0).toUpperCase() + field.slice(1)}`);
                                
                                if (errorElement && inputElement) {
                                    errorElement.textContent = data.errors[field];
                                    inputElement.classList.add('is-invalid');
                                }
                            }
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to add essay', 'error');
                        }
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An unexpected error occurred', 'error');
                });
            });

            // Essay Edit
            document.querySelectorAll('.edit-essay')?.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    resetFormErrors('editEssayForm');
                    const id = this.getAttribute('data-id');
                    
                    // Show loading indicator
                    Swal.fire({
                        title: 'Loading...',
                        html: 'Fetching essay data',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch(`<?= site_url('master-data/submission-form/get-essay-by-id/') ?>${id}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();
                          if (data.success) {
                            const essay = data.data;
                            document.getElementById('editEssayId').value = essay.id;
                            document.getElementById('editEssayQuestions').value = essay.questions;
                            document.getElementById('editEssayMaxWordCount').value = essay.max_word_count || '';
                            document.getElementById('editEssayIsActive').checked = essay.is_active == 1;
                            
                            var modal = new bootstrap.Modal(document.getElementById('editEssayModal'));
                            modal.show();
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to fetch essay details', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        console.error('Error:', error);
                        Swal.fire('Error!', 'An unexpected error occurred', 'error');
                    });
                });
            });

            document.getElementById('editEssayForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                resetFormErrors('editEssayForm');
                
                // Show loading spinner
                const submitBtn = document.getElementById('editEssaySubmitBtn');
                const spinner = document.getElementById('editEssaySpinner');
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                
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
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    
                    if (data.success) {
                        // Close the modal
                        var modal = bootstrap.Modal.getInstance(document.getElementById('editEssayModal'));
                        modal.hide();
                        
                        // Show success message
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
                        // Show error message
                        if (data.errors) {
                            for (const field in data.errors) {
                                const errorElement = document.getElementById(`edit-${field}-error-essay`);
                                const inputElement = document.getElementById(`editEssay${field.charAt(0).toUpperCase() + field.slice(1)}`);
                                
                                if (errorElement && inputElement) {
                                    errorElement.textContent = data.errors[field];
                                    inputElement.classList.add('is-invalid');
                                }
                            }
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to update essay', 'error');
                        }
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An unexpected error occurred', 'error');
                });
            });

            // Essay Delete
            document.querySelectorAll('.remove-essay')?.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will deactivate the essay. You can reactivate it later.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, deactivate it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading indicator
                            Swal.fire({
                                title: 'Deleting...',
                                html: 'Processing your request',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            fetch(`<?= site_url('master-data/submission-form/delete-essay/') ?>${id}`, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                Swal.close();
                                
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Deactivated!',
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
                                Swal.close();
                                console.error('Error:', error);
                                Swal.fire('Error!', 'An unexpected error occurred', 'error');
                            });
                        }
                    });
                });
            });

            // Other tabs JavaScript would go here
        });
    </script>
</body>

</html>
