<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Frequently Asked Questions')); ?>

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
        .description-cell {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .modal-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }

        /* Improve table responsiveness */
        .table-responsive.table-card {
            border-radius: 0.25rem;
            box-shadow: 0 1px 2px rgba(56, 65, 74, 0.15);
        }

        #faqs-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Frequently Asked Questions')); ?> <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Frequently Asked Questions</h4>
                                    <div class="flex-shrink-0">
                                        <?php if (session('current_program')): ?>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-faq-modal">
                                                <i class="ri-add-line align-bottom me-1"></i> Add FAQ
                                            </button>
                                        <?php else: ?>
                                            <div class="alert alert-warning mb-0">
                                                <i class="ri-error-warning-line me-1 align-middle"></i>
                                                Please select a program first
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div>
                                                <label for="category-filter" class="form-label">Filter by Category</label>
                                                <select class="form-select" id="category-filter">
                                                    <option value="">All Categories</option>
                                                    <?php foreach ($faqCategories as $value => $label) : ?>
                                                        <option value="<?= $value ?>"><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <table id="faqs-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Question</th>
                                                <th scope="col">Answer</th>
                                                <th scope="col">Category</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($faqs)) : ?>
                                                <?php foreach ($faqs as $index => $faq) : ?>
                                                    <tr data-category="<?= $faq->faq_category ?>">
                                                        <td><?= $index + 1 ?></td>
                                                        <td class="description-cell" data-bs-toggle="tooltip" title="<?= htmlspecialchars($faq->question) ?>">
                                                            <?= mb_strimwidth(strip_tags($faq->question), 0, 50, "...") ?>
                                                        </td>
                                                        <td class="description-cell" data-bs-toggle="tooltip" title="<?= htmlspecialchars($faq->answer) ?>">
                                                            <?= mb_strimwidth(strip_tags($faq->answer), 0, 40, "...") ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary-subtle text-primary">
                                                                <?= isset($faqCategories[$faq->faq_category]) ? $faqCategories[$faq->faq_category] : 'General' ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if ($faq->is_active == 1): ?>
                                                                <span class="badge bg-success-subtle text-success">Active</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <div class="view">
                                                                    <button type="button" class="btn btn-sm btn-info view-faq" data-id="<?= $faq->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                                                        <i class="ri-eye-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="edit">
                                                                    <button type="button" class="btn btn-sm btn-success edit-faq" data-id="<?= $faq->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                                        <i class="ri-pencil-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="remove">
                                                                    <button type="button" class="btn btn-sm btn-danger remove-faq" data-id="<?= $faq->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                                        <i class="ri-delete-bin-fill"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No FAQs found</td>
                                                </tr>
                                            <?php endif; ?>
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

    <!-- Add FAQ Modal -->
    <div class="modal fade" id="add-faq-modal" tabindex="-1" aria-labelledby="add-faq-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-faq-modal-label">Add New FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/faqs/create" method="post" id="add-faq-form" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <!-- Add hidden program_id field -->
                        <input type="hidden" name="program_id" value="<?= session('current_program') ?>">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="question" class="form-label">Question*</label>
                                    <input type="text" class="form-control" id="question" name="question" required>
                                    <div class="invalid-feedback">Please enter a question.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="answer" class="form-label">Answer*</label>
                            <textarea class="form-control" id="answer" name="answer" rows="3" required></textarea>
                            <div class="invalid-feedback">Please provide an answer.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="faq_category" class="form-label">Category*</label>
                                    <select class="form-select" id="faq_category" name="faq_category" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($faqCategories as $value => $label) : ?>
                                            <option value="<?= $value ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a category.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status*</label>
                                    <select class="form-select" id="is_active" name="is_active" required>
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a status.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit FAQ Modal -->
    <div class="modal fade" id="edit-faq-modal" tabindex="-1" aria-labelledby="edit-faq-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-loading" id="edit-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="edit-faq-modal-label">Edit FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/faqs/update/" method="post" id="edit-faq-form" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <input type="hidden" id="edit_faq_id" name="id">
                        <!-- Add hidden program_id field -->
                        <input type="hidden" name="program_id" value="<?= session('current_program') ?>">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="edit_question" class="form-label">Question*</label>
                                    <input type="text" class="form-control" id="edit_question" name="question" required>
                                    <div class="invalid-feedback">Please enter a question.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_answer" class="form-label">Answer*</label>
                            <textarea class="form-control" id="edit_answer" name="answer" rows="5" required></textarea>
                            <div class="invalid-feedback">Please provide an answer.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_faq_category" class="form-label">Category*</label>
                                    <select class="form-select" id="edit_faq_category" name="faq_category" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($faqCategories as $value => $label) : ?>
                                            <option value="<?= $value ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a category.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_is_active" class="form-label">Status*</label>
                                    <select class="form-select" id="edit_is_active" name="is_active" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a status.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- View FAQ Modal -->
    <div class="modal fade" id="view-faq-modal" tabindex="-1" aria-labelledby="view-faq-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-loading" id="view-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="view-faq-modal-label">FAQ Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <h6 class="fw-semibold">Question</h6>
                                <p id="view_question">Loading...</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-semibold">Category</h6>
                                <p id="view_category">Loading...</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-semibold">Status</h6>
                                <p id="view_status">Loading...</p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-semibold">Answer</h6>
                        <p id="view_answer">Loading...</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-semibold">Last Updated</h6>
                        <p id="view_updated">Loading...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success view-edit-btn" data-id="">Edit</button>
                </div>
            </div>
        </div>
    </div> 
    
    <!-- Delete FAQ Modal -->
    <div class="modal fade" id="delete-faq-modal" tabindex="-1" aria-labelledby="delete-faq-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-faq-modal-label">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this FAQ? This action cannot be undone.</p>
                </div>                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <form id="delete-faq-form" action="/master-data/faqs/delete" method="post" style="display: inline;">
                        <input type="hidden" id="delete_faq_id" name="id">
                        <button type="submit" class="btn btn-danger" id="confirm-delete-btn">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <script src="/assets/js/pages/datatables.init.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script> 
    
    <!-- Custom JavaScript -->
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM loaded");

            // Check for flash messages
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

            // Ensure jQuery is loaded
            if (typeof jQuery !== 'undefined') {
                console.log("jQuery is loaded");
                initializeFaqFunctions();
            } else {
                console.error("jQuery is not loaded!");
            }
        });

        function initializeFaqFunctions() {
            // Initialize DataTable with improved configuration
            var faqsTable = $('#faqs-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true,
                columnDefs: [{
                    orderable: false,
                    targets: [5] // Action column is not sortable
                }],
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-squared justify-content-end mb-0");
                    // Initialize tooltips
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });
                }
            });

            // Category filter functionality
            $('#category-filter').on('change', function() {
                var category = $(this).val();
                if (category === '') {
                    faqsTable.column(3).search('').draw();
                } else {
                    faqsTable.column(3).search(category).draw();
                }
            });

            // Use event delegation for view button
            $(document).on('click', '.view-faq', function(e) {
                e.preventDefault();

                var faqId = $(this).data('id');
                console.log("View button clicked for ID:", faqId);

                // Show modal first
                $('#view-faq-modal').modal('show');
                $('#view-loading').show();

                // Get FAQ details
                $.ajax({
                    url: '/master-data/faqs/get/' + faqId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("View Ajax response:", response);
                        $('#view-loading').hide();

                        if (response && response.success) {
                            var faq = response.data;

                            // Populate modal
                            $('#view_question').text(faq.question || 'N/A');

                            // Get category name from the categories dropdown
                            var categoryName = "General";
                            if (faq.faq_category) {
                                var categoryOption = $('#faq_category option[value="' + faq.faq_category + '"]');
                                if (categoryOption.length) {
                                    categoryName = categoryOption.text();
                                }
                            }
                            $('#view_category').text(categoryName);

                            $('#view_answer').html(faq.answer || 'No answer provided');

                            // Format status with badge
                            var statusBadge = faq.is_active == 1 ?
                                '<span class="badge bg-success-subtle text-success">Active</span>' :
                                '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
                            $('#view_status').html(statusBadge);
                            $('#view_updated').text(formatDate(faq.updated_at || 'N/A'));

                            // Set FAQ ID for the edit button in view modal
                            $('.view-edit-btn').data('id', faq.id);
                        } else {
                            console.error("Invalid response:", response);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load FAQ details',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("View Ajax error:", xhr.responseText);
                        $('#view-loading').hide();
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while fetching FAQ details',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                });
            });

            // Handle edit button click
            $(document).on('click', '.edit-faq', function(e) {
                e.preventDefault();

                var faqId = $(this).data('id');
                console.log("Edit button clicked for ID:", faqId);

                // Show modal first
                $('#edit-faq-modal').modal('show');
                $('#edit-loading').show();

                // Get FAQ details
                $.ajax({
                    url: '/master-data/faqs/get/' + faqId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#edit-loading').hide();

                        if (response && response.success) {
                            var faq = response.data;                            // Set form action URL (without FAQ ID in the path)
                            $('#edit-faq-form').attr('action', '/master-data/faqs/update');

                            // Populate form
                            $('#edit_faq_id').val(faq.id);
                            $('#edit_question').val(faq.question);
                            $('#edit_answer').val(faq.answer);
                            $('#edit_faq_category').val(faq.faq_category);
                            $('#edit_is_active').val(faq.is_active);
                        } else {
                            console.error("Invalid response:", response);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load FAQ details for editing',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                            $('#edit-faq-modal').modal('hide');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#edit-loading').hide();
                        console.error("Edit Ajax error:", xhr.responseText);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while fetching FAQ details',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                        $('#edit-faq-modal').modal('hide');
                    }
                });
            });            // Handle delete button click
            $(document).on('click', '.remove-faq', function(e) {
                e.preventDefault();

                var faqId = $(this).data('id');
                console.log("Delete button clicked for ID:", faqId);

                // Set the FAQ ID in the hidden form field and show confirmation modal
                $('#delete_faq_id').val(faqId);
                $('#delete-faq-modal').modal('show');
            });

            // Handle click on edit button in view modal
            $(document).on('click', '.view-edit-btn', function() {
                var faqId = $(this).data('id');
                $('#view-faq-modal').modal('hide');

                // Wait for view modal to close before opening edit modal
                setTimeout(function() {
                    $('.edit-faq[data-id="' + faqId + '"]').trigger('click');
                }, 500);
            });

            // Form validation for add FAQ form
            $('#add-faq-form').on('submit', function(e) {
                if ($(this)[0].checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Show SweetAlert for validation errors
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                }
                $(this).addClass('was-validated');
            });

            // Form validation for edit FAQ form
            $('#edit-faq-form').on('submit', function(e) {
                if ($(this)[0].checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Show SweetAlert for validation errors
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                }
                $(this).addClass('was-validated');
            });

            // Helper function to format date
            function formatDate(dateString) {
                if (!dateString || dateString === 'N/A') return 'N/A';

                var date = new Date(dateString);
                if (isNaN(date)) return 'N/A';

                return date.toLocaleDateString('en-US', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
            }
        }
    </script>
</body>

</html>