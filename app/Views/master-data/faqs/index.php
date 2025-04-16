<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Frequently Asked Questions')); ?>
    <?= $this->include('partials/head-css') ?>
    <!-- DataTables css -->
    <link href="/assets/libs/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/libs/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/libs/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Frequently Asked Questions')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title mb-0 flex-grow-1">FAQs List</h5>
                                        <div class="flex-shrink-0">
                                            <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" data-bs-target="#add-faq-modal">
                                                <i class="ri-add-line align-bottom me-1"></i> Add FAQ
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Category Filter Dropdown -->
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="category-filter" class="form-label">Filter by Category</label>
                                                <select class="form-select" id="category-filter">
                                                    <option value="">All Categories</option>
                                                    <?php foreach ($faqCategories as $value => $label) : ?>
                                                        <option value="<?= $value ?>"><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Search Box -->
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="searchFaqs" class="form-label">Search</label>
                                                <input type="text" id="searchFaqs" class="form-control" placeholder="Search...">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive table-card">
                                        <table id="faqs-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col" style="width: 50px;">#</th>
                                                    <th scope="col">Question</th>
                                                    <th scope="col">Answer</th>
                                                    <th scope="col">Category</th>
                                                    <th scope="col" style="width: 120px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($faqs)) : ?>
                                                    <?php foreach ($faqs as $index => $faq) : ?>
                                                        <tr data-category="<?= $faq->faq_category ?>">
                                                            <td><?= $index + 1 ?></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-grow-1">
                                                                        <span class="text-truncate d-inline-block" style="max-width: 260px" data-bs-toggle="tooltip" title="<?= htmlspecialchars($faq->question) ?>">
                                                                            <?= mb_strimwidth(strip_tags($faq->question), 0, 50, "...") ?>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-truncate d-inline-block" style="max-width: 200px" data-bs-toggle="tooltip" title="<?= htmlspecialchars($faq->answer) ?>">
                                                                    <?= mb_strimwidth(strip_tags($faq->answer), 0, 40, "...") ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-soft-primary text-primary">
                                                                    <?= isset($faqCategories[$faq->faq_category]) ? $faqCategories[$faq->faq_category] : 'General' ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="hstack gap-2">
                                                                    <button class="btn btn-sm btn-soft-info view-faq"
                                                                        data-id="<?= $faq->id ?>"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#view-faq-modal">
                                                                        <i class="ri-eye-fill align-bottom"></i>
                                                                    </button>
                                                                    <button class="btn btn-sm btn-soft-success edit-faq"
                                                                        data-id="<?= $faq->id ?>"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#edit-faq-modal">
                                                                        <i class="ri-pencil-fill align-bottom"></i>
                                                                    </button>
                                                                    <button class="btn btn-sm btn-soft-danger remove-faq"
                                                                        data-id="<?= $faq->id ?>">
                                                                        <i class="ri-delete-bin-fill align-bottom"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else : ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No FAQs found</td>
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
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- Add FAQ Modal -->
    <div class="modal fade" id="add-faq-modal" tabindex="-1" aria-labelledby="add-faq-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="add-faq-modal-label">Add New FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add-faq-form" method="post" action="<?= base_url('master-data/faqs/create') ?>" class="needs-validation" novalidate>
                        <!-- Add hidden program_id field -->
                        <input type="hidden" name="program_id" value="<?= session('current_program') ?>">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="question" class="form-label">Question <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="question" name="question" required>
                                <div class="invalid-feedback">Please enter a question</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="answer" class="form-label">Answer <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="answer" name="answer" rows="5" required></textarea>
                                <div class="invalid-feedback">Please enter an answer</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="faq_category" class="form-label">Category</label>
                                <select class="form-select" id="faq_category" name="faq_category">
                                    <option value="">Select Category</option>
                                    <?php foreach ($faqCategories as $value => $label) : ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-select" id="is_active" name="is_active">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="add-faq-form" class="btn btn-primary">Add FAQ</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit FAQ Modal -->
    <div class="modal fade" id="edit-faq-modal" tabindex="-1" aria-labelledby="edit-faq-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="edit-faq-modal-label">Edit FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-faq-form" method="post" action="<?= base_url('master-data/faqs/update') ?>" class="needs-validation" novalidate>
                        <input type="hidden" id="edit_faq_id" name="id">
                        <!-- Add hidden program_id field -->
                        <input type="hidden" name="program_id" value="<?= session('current_program') ?>">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_question" class="form-label">Question <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_question" name="question" required>
                                <div class="invalid-feedback">Please enter a question</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_answer" class="form-label">Answer <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit_answer" name="answer" rows="5" required></textarea>
                                <div class="invalid-feedback">Please enter an answer</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_faq_category" class="form-label">Category</label>
                                <select class="form-select" id="edit_faq_category" name="faq_category">
                                    <option value="">Select Category</option>
                                    <?php foreach ($faqCategories as $value => $label) : ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_is_active" class="form-label">Status</label>
                                <select class="form-select" id="edit_is_active" name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="edit-faq-form" class="btn btn-primary">Update FAQ</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View FAQ Modal -->
    <div class="modal fade" id="view-faq-modal" tabindex="-1" aria-labelledby="view-faq-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="view-faq-modal-label">FAQ Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="border-bottom pb-3 mb-3">
                        <h5 class="fw-semibold">Q: <span id="view_question"></span></h5>
                    </div>
                    <div class="border-bottom pb-3 mb-3">
                        <h6 class="fw-semibold text-muted mb-2">Answer:</h6>
                        <div id="view_answer" class="text-muted"></div>
                    </div>
                    <div class="row border-bottom pb-3 mb-3">
                        <div class="col-md-6">
                            <h6 class="fw-semibold text-muted mb-2">Category:</h6>
                            <div><span id="view_category" class="badge bg-soft-primary text-primary"></span></div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold text-muted mb-2">Status:</h6>
                            <div id="view_status"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold text-muted mb-2">Last Updated:</h6>
                            <div id="view_updated" class="text-muted"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary edit-from-view" data-bs-dismiss="modal">Edit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div class="modal fade" id="delete-faq-modal" tabindex="-1" aria-labelledby="delete-faq-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="delete-faq-modal-label">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this FAQ? This action cannot be undone.</p>
                    <form id="delete-faq-form" method="post" action="<?= base_url('master-data/faqs/delete') ?>">
                        <input type="hidden" id="delete_faq_id" name="id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="delete-faq-form" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTables js -->
    <script src="/assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/libs/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="/assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="/assets/libs/datatables/responsive.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Initialize DataTable
            var faqsTable = $('#faqs-table').DataTable({
                dom: 'Bfrtip',
                responsive: true,
                lengthChange: true,
                pageLength: 10,
                searching: true,
                ordering: true,
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [4] // Actions column not sortable
                }],
                language: {
                    emptyTable: "No FAQs found",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    lengthMenu: "Show _MENU_ entries",
                    loadingRecords: "Loading...",
                    processing: "Processing...",
                    search: "Search:",
                    zeroRecords: "No matching records found"
                },
                buttons: []
            });

            // Search functionality
            $('#searchFaqs').on('keyup', function() {
                faqsTable.search(this.value).draw();
            });

            // Category filter dropdown
            $('#category-filter').on('change', function() {
                var category = $(this).val();

                if (category === '') {
                    // Show all categories
                    faqsTable.column(3).search('').draw();
                } else {
                    // Filter by category value
                    faqsTable.column(3).search(category).draw();
                }
            });

            // Form validation
            (function() {
                'use strict'

                // Fetch all forms we want to apply validation styles to
                var forms = document.querySelectorAll('.needs-validation')

                // Loop over them and prevent submission
                Array.prototype.slice.call(forms)
                    .forEach(function(form) {
                        form.addEventListener('submit', function(event) {
                            if (!form.checkValidity()) {
                                event.preventDefault()
                                event.stopPropagation()
                            }

                            form.classList.add('was-validated')
                        }, false)
                    })
            })();

            // Edit FAQ button handler
            $(document).on('click', '.edit-faq', function() {
                var faqId = $(this).data('id');
                console.log("Edit FAQ clicked for ID:", faqId);

                // Clear previous form data and validation
                $('#edit-faq-form').removeClass('was-validated');

                // Fetch FAQ data and populate the edit modal
                $.ajax({
                    url: '<?= base_url('master-data/faqs/get') ?>/' + faqId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("API Response:", response);
                        if (response.success) {
                            var faq = response.data;
                            $('#edit_faq_id').val(faq.id);
                            $('#edit_question').val(faq.question);
                            $('#edit_answer').val(faq.answer);
                            $('#edit_faq_category').val(faq.faq_category);
                            $('#edit_is_active').val(faq.is_active);
                        } else {
                            alert(response.message || 'Error fetching FAQ data');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr, status, error);
                        alert('Failed to fetch FAQ data. Please try again. Error: ' + error);
                    }
                });
            });

            // View FAQ button handler
            $(document).on('click', '.view-faq', function() {
                var faqId = $(this).data('id');
                console.log("View FAQ clicked for ID:", faqId);

                // Fetch FAQ data and populate the view modal
                $.ajax({
                    url: '<?= base_url('master-data/faqs/get') ?>/' + faqId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("View API Response:", response);
                        if (response.success) {
                            var faq = response.data;
                            $('#view_question').text(faq.question);
                            $('#view_answer').html(faq.answer);

                            // Get category name from the categories dropdown
                            var categoryName = "General";
                            if (faq.faq_category) {
                                var categoryOption = $('#faq_category option[value="' + faq.faq_category + '"]');
                                if (categoryOption.length) {
                                    categoryName = categoryOption.text();
                                }
                            }
                            $('#view_category').text(categoryName);

                            // Set status badge
                            if (faq.is_active == 1) {
                                $('#view_status').html('<span class="badge bg-soft-success text-success">Active</span>');
                            } else {
                                $('#view_status').html('<span class="badge bg-soft-danger text-danger">Inactive</span>');
                            }

                            $('#view_updated').text(formatDate(faq.updated_at));

                            // Set edit from view button data
                            $('.edit-from-view').data('id', faq.id);
                        } else {
                            alert(response.message || 'Error fetching FAQ data');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr, status, error);
                        alert('Failed to fetch FAQ data. Please try again. Error: ' + error);
                    }
                });
            });

            // Edit from view button handler
            $('.edit-from-view').on('click', function() {
                var faqId = $(this).data('id');
                console.log("Edit from view clicked for ID:", faqId);
                setTimeout(function() {
                    $('.edit-faq[data-id="' + faqId + '"]').click();
                }, 300); // Small delay to ensure modal transition completes
            });

            // Delete FAQ button handler
            $(document).on('click', '.remove-faq', function() {
                var faqId = $(this).data('id');
                console.log("Remove FAQ clicked for ID:", faqId);
                $('#delete_faq_id').val(faqId);
                $('#delete-faq-modal').modal('show');
            });

            // Helper function to format date
            function formatDate(dateString) {
                if (!dateString) return 'N/A';

                var date = new Date(dateString);
                var options = {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                return date.toLocaleDateString('en-US', options);
            }
        });
    </script>
</body>

</html>