<?= $this->include('partials/main') ?>

<head> <?php echo view('partials/title-meta', array('title' => 'Payment Methods')); ?>

    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>


    <style>
        /* Modal loading overlay */
        .modal-loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 0.3rem;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .loading-text {
            margin-top: 1rem;
            color: #495057;
        }

        /* Quill editor styles */
        .ql-container {
            min-height: 150px;
            border-bottom-left-radius: 0.25rem;
            border-bottom-right-radius: 0.25rem;
        }

        .ql-toolbar {
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
        }

        /* File upload styles */
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 5px;
        }

        .custom-file-upload {
            border: 1px solid #ccc;
            display: inline-block;
            padding: 6px 12px;
            cursor: pointer;
            margin-top: 5px;
            border-radius: 0.25rem;
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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Payment Methods')); ?> <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Payment Methods List</h5>
                                    <div class="flex-shrink-0">
                                        <button class="btn btn-primary waves-effect waves-light me-2" data-bs-toggle="modal" data-bs-target="#add-method-modal">
                                            <i class="ri-add-line align-middle me-1"></i> Add Payment Method
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="payment-methods-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Type</th>
                                                <th scope="col">Image</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($paymentMethods ?? [])): ?>
                                                <?php foreach ($paymentMethods as $index => $method): ?>
                                                    <?php
                                                    // Normalize method method name based on category
                                                    $displayName = $method->name ?? 'N/A';

                                                    $type = $method->type ?? 'N/A';
                                                    $typeName = $type === 'manual' ? 'Manual' : ($type === 'gateway' ? 'Gateway' : 'N/A');

                                                    $status = $method->is_active == 1 ? 'Active' : 'Inactive';
                                                    $statusClass = $method->is_active == 1 ? 'badge bg-success' : 'badge bg-secondary';

                                                    $imgUrl = $method->img_url ?? null;
                                                    $imgHtml = $imgUrl ? '<img src="' . htmlspecialchars($imgUrl) . '" class="img-fluid rounded" style="max-height: 50px;" alt="Payment Method Image">' : '<div class="alert alert-light">No image available</div>';
                                                    ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td><?= htmlspecialchars($displayName) ?></td>
                                                        <td><?= htmlspecialchars($typeName) ?></td>
                                                        <td><?= $imgHtml ?></td>
                                                        <td><span class="<?= $statusClass ?>"><?= htmlspecialchars($status) ?></span></td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <div class="view">
                                                                    <button class="btn btn-sm btn-info view-payment-method" data-id="<?= $method->id ?? 0 ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                                                        <i class="ri-eye-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="edit">
                                                                    <button class="btn btn-sm btn-success edit-payment-method" data-id="<?= $method->id ?? 0 ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                                        <i class="ri-pencil-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="remove">
                                                                    <button class="btn btn-sm btn-danger delete-payment-method" data-id="<?= $method->id ?? 0 ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                                        <i class="ri-delete-bin-fill"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">No payment methods found</td>
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

    </div> <!-- END layout-wrapper -->

    <!-- Add Payment Modal -->
    <div class="modal fade" id="add-method-modal" tabindex="-1" aria-labelledby="add-method-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-method-modal-label">Add New Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/payment-methods/create" method="post" id="add-method-form" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Method Name*</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <div class="invalid-feedback">Please enter a method name.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Payment Type*</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="manual">Manual</option>
                                        <option value="gateway">Gateway</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a method type.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="gateway_provider_group" style="display:none;">
                            <label for="gateway_provider" class="form-label">Gateway Provider*</label>
                            <select class="form-select" id="gateway_provider" name="gateway_provider">
                                <option value="">— select provider —</option>
                                <option value="midtrans">Midtrans</option>
                                <option value="xendit">Xendit</option>
                            </select>
                            <small class="text-muted">API credentials for the selected provider must be set in the server's .env file.</small>
                        </div>

                        <div class="mb-3">
                            <label for="payment_image" class="form-label">Payment Method Image</label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="payment_image" name="payment_image" accept="image/*">
                                <label class="input-group-text" for="payment_image">Upload</label>
                            </div>
                            <small class="text-muted">Upload an image for the payment method (recommended size: 200x100px)</small>
                            <div class="mt-2" id="image_preview_container"></div>
                            <!-- Hidden field to store image URL from previous uploads -->
                            <input type="hidden" id="img_url" name="img_url">
                        </div>
                        <div class="mb-3">
                            <label for="description_container" class="form-label">Description*</label>
                            <div id="description_editor" style="height: 200px;"></div>
                            <input type="hidden" id="description" name="description" required>
                            <div class="invalid-feedback">Please provide a description.</div>
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status*</label>
                            <select class="form-select" id="is_active" name="is_active" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Payment Method</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Payment Modal -->
    <div class="modal fade" id="edit-method-modal" tabindex="-1" aria-labelledby="edit-method-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-loading" id="edit-loading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="loading-text">Loading method method details...</div>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="edit-method-modal-label">Edit Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/payment-methods/update/" method="post" id="edit-method-form" enctype="multipart/form-data">
                    <input type="hidden" id="edit_payment_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Method Name*</label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_type" class="form-label">Payment Type*</label>
                                    <select class="form-select" id="edit_type" name="type">
                                        <option value="manual">Manual</option>
                                        <option value="gateway">Gateway</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="edit_gateway_provider_group" style="display:none;">
                            <label for="edit_gateway_provider" class="form-label">Gateway Provider*</label>
                            <select class="form-select" id="edit_gateway_provider" name="gateway_provider">
                                <option value="">— select provider —</option>
                                <option value="midtrans">Midtrans</option>
                                <option value="xendit">Xendit</option>
                            </select>
                            <small class="text-muted">API credentials for the selected provider must be set in the server's .env file.</small>
                        </div>

                        <div class="mb-3">
                            <label for="edit_payment_image" class="form-label">Payment Method Image</label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="edit_payment_image" name="payment_image" accept="image/*">
                                <label class="input-group-text" for="edit_payment_image">Upload</label>
                            </div>
                            <small class="text-muted">Upload an image for the payment method (recommended size: 200x100px)</small>
                            <div class="mt-2" id="edit_image_preview_container"></div>
                            <!-- Hidden field to store image URL from previous uploads -->
                            <input type="hidden" id="edit_img_url" name="img_url">
                        </div>

                        <div class="mb-3">
                            <label for="edit_description_container" class="form-label">Description</label>
                            <div id="edit_description_editor" style="height: 200px;"></div>
                            <input type="hidden" id="edit_description" name="description">
                        </div>

                        <div class="mb-3">
                            <label for="edit_is_active" class="form-label">Status</label>
                            <select class="form-select" id="edit_is_active" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Payment Method</button>
                    </div>
                </form>
            </div>
        </div>
    </div> <!-- Delete Payment Modal -->
    <div class="modal fade" id="delete-method-modal" tabindex="-1" aria-labelledby="delete-method-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-method-modal-label">Delete Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this method method?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirm-delete-btn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div> <!-- View Payment Modal -->
    <div class="modal fade" id="view-method-modal" tabindex="-1" aria-labelledby="view-method-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-loading" id="view-loading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="loading-text">Loading payment method details...</div>
                    </div>
                </div>
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="view-method-modal-label">
                        <i class="ri-bank-card-line me-1"></i> Payment Method Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Payment method image and key details -->
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <div class="card shadow-sm border">
                                <div class="card-body p-3 d-flex align-items-center justify-content-center" style="height: 160px;">
                                    <div id="view_img_container">
                                        <!-- Image will be displayed here if available -->
                                        <div class="placeholder-glow">
                                            <span class="placeholder col-12" style="height: 120px;"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-light py-2 text-center">
                                    <span class="badge" id="view_type_badge"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-light py-2">
                                    <h5 class="card-title mb-0" id="view_name_header"></h5>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <p class="text-muted mb-1 small">Method Name</p>
                                                <h6 class="text-dark mb-0 fw-semibold" id="view_name"></h6>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <p class="text-muted mb-1 small">Payment Type</p>
                                                <h6 class="text-dark mb-0 fw-semibold" id="view_type"></h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <p class="text-muted mb-1 small">Status</p>
                                                <div id="view_status"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-0">
                                                <p class="text-muted mb-1 small">Image URL</p>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" class="form-control form-control-sm" id="view_img_url_input" readonly>
                                                    <button class="btn btn-sm btn-outline-secondary copy-url-btn" type="button" title="Copy URL">
                                                        <i class="ri-file-copy-line"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description section -->
                    <div class="card shadow-sm mb-0">
                        <div class="card-header bg-light py-2">
                            <h5 class="card-title mb-0">
                                <i class="ri-file-text-line me-1"></i> Description
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="description-content border rounded p-3 bg-light-subtle" id="view_description"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary view-edit-btn">
                        <i class="ri-pencil-line me-1"></i> Edit
                    </button>
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
        // Global variables for Quill editors
        var quillEditor, editQuillEditor;

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

            // Initialize Quill editors
            initializeQuillEditors();

            // Ensure jQuery is loaded
            if (typeof jQuery !== 'undefined') {
                console.log("jQuery is loaded");
                initializePaymentFunctions();
            } else {
                console.error("jQuery is not loaded!");
            }
        });

        function initializeQuillEditors() {
            // Initialize Quill for add form
            quillEditor = new Quill('#description_editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        ['link'],
                        ['clean']
                    ]
                },
                placeholder: 'Enter payment method description...'
            });

            // Initialize Quill for edit form
            editQuillEditor = new Quill('#edit_description_editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        ['link'],
                        ['clean']
                    ]
                },
                placeholder: 'Enter payment method description...'
            });

            // Update hidden input when Quill content changes for add form
            quillEditor.on('text-change', function() {
                document.getElementById('description').value = quillEditor.root.innerHTML;
            });

            // Update hidden input when Quill content changes for edit form
            editQuillEditor.on('text-change', function() {
                document.getElementById('edit_description').value = editQuillEditor.root.innerHTML;
            });
        }

        function initializePaymentFunctions() {
            // Initialize DataTable
            var paymentTable = $('#payment-methods-table').DataTable({
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

            // Debug click events
            console.log("Setting up click handlers");

            // Log all view buttons for debugging
            var viewButtons = document.querySelectorAll('.view-payment-method');
            console.log("View buttons found:", viewButtons.length);

            // Test direct DOM event listener for view buttons
            document.querySelectorAll('.view-payment-method').forEach(function(button) {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    console.log("View button clicked via DOM listener, ID:", id);
                });
            }); // Use simplified event delegation for view button
            $(document).on('click', '.view-payment-method', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var paymentId = $(this).data('id');
                console.log("View button clicked for ID:", paymentId);

                // Show modal first
                $('#view-method-modal').modal('show');
                $('#view-loading').show();

                // Get payment method details                
                $.ajax({
                    url: '/master-data/payment-methods/getPaymentMethod/' + paymentId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("View Ajax response:", response);
                        if (response && response.success) {
                            var method = response.data;

                            // Set method name in multiple places
                            $('#view_name').text(method.name || 'N/A');
                            $('#view_name_header').text(method.name || 'N/A');

                            // Format method type for display
                            var typeDisplay = method.type || 'N/A';
                            var typeBadgeClass = '';

                            if (typeDisplay === 'manual') {
                                typeDisplay = 'Manual';
                                typeBadgeClass = 'bg-info';
                            } else if (typeDisplay === 'gateway') {
                                typeDisplay = 'Gateway';
                                typeBadgeClass = 'bg-primary';
                            }

                            $('#view_type').text(typeDisplay);
                            $('#view_type_badge').text(typeDisplay).addClass(typeBadgeClass);

                            // Display image URL in input field for easy copying
                            $('#view_img_url_input').val(method.img_url || '');

                            // Show the actual image if URL is provided
                            if (method.img_url) {
                                $('#view_img_container').html('<img src="' + method.img_url + '" class="img-fluid rounded" style="max-height: 150px; max-width: 100%;" alt="Payment Method Image">');
                            } else {
                                $('#view_img_container').html('<div class="d-flex align-items-center justify-content-center h-100"><div class="text-center text-muted"><i class="ri-image-line display-6"></i><p class="mt-2 mb-0">No image available</p></div></div>');
                            }

                            // Set description as HTML content to preserve formatting
                            $('#view_description').html(method.description || '<p class="text-muted">No description provided</p>');

                            // Format status with badge
                            var statusBadge = method.is_active == 1 ?
                                '<span class="badge bg-success fs-6"><i class="ri-checkbox-circle-line me-1"></i>Active</span>' :
                                '<span class="badge bg-secondary fs-6"><i class="ri-indeterminate-circle-line me-1"></i>Inactive</span>';
                            $('#view_status').html(statusBadge);

                            // Set method ID for the edit button in view modal
                            $('.view-edit-btn').data('id', method.id);
                        } else {
                            console.error("Invalid response:", response);
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to load payment method details',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                        }

                        // Hide loading spinner
                        $('#view-loading').hide();
                    },
                    error: function(xhr, status, error) {
                        console.error("View Ajax error:", xhr.responseText);
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while fetching payment method details',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                        $('#view-loading').hide();
                    }
                });
            });

            $(document).on('click', '.edit-payment-method', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var paymentId = $(this).data('id');
                console.log("Edit button clicked for ID:", paymentId);

                // Show modal first
                $('#edit-method-modal').modal('show');
                $('#edit-loading').show();

                // Get method method details                
                $.ajax({
                    url: '/master-data/payment-methods/getPaymentMethod/' + paymentId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Edit Ajax response:", response);

                        if (response && response.success) {
                            var method = response.data; // Set form action                            
                            $('#edit-method-form').attr('action', '/master-data/payment-methods/update/' + method.id); // Populate form
                            $('#edit_payment_id').val(method.id);
                            $('#edit_name').val(method.name);
                            $('#edit_type').val(method.type || 'manual');
                            // Show/populate gateway provider if applicable
                            if (method.type === 'gateway') {
                                $('#edit_gateway_provider_group').show();
                                $('#edit_gateway_provider').val(method.gateway_provider || '');
                            } else {
                                $('#edit_gateway_provider_group').hide();
                                $('#edit_gateway_provider').val('');
                            }

                            // Handle image preview for existing image
                            if (method.img_url) {
                                $('#edit_img_url').val(method.img_url);
                                $('#edit_image_preview_container').html('<img src="' + method.img_url + '" class="image-preview" alt="Payment Method Image">');
                            } else {
                                $('#edit_image_preview_container').empty();
                                $('#edit_img_url').val('');
                            }

                            // Set Quill editor content
                            editQuillEditor.root.innerHTML = method.description || '';
                            $('#edit_description').val(method.description || '');

                            $('#edit_is_active').val(method.is_active);
                        } else {
                            console.error("Invalid response:", response);
                            alert('Failed to load method option details');
                        }

                        // Hide loading spinner
                        $('#edit-loading').hide();
                    },
                    error: function(xhr, status, error) {
                        console.error("Edit Ajax error:", xhr.responseText);
                        alert('An error occurred while fetching method option details');
                        $('#edit-loading').hide();
                    }
                });
            }); 
            
            // Handle delete button click with event delegation
            $(document).on('click', '.delete-payment-method', function(e) {
                e.preventDefault();
                var paymentId = $(this).data('id');
                console.log("Delete button clicked for ID:", paymentId);

                // Set delete URL                
                $('#confirm-delete-btn').attr('href', '/master-data/payment-methods/delete/' + paymentId);
                $('#confirm-delete-btn').data('id', paymentId);

                // Show modal
                $('#delete-method-modal').modal('show');
            });

            // Handle confirm delete button click
            $(document).on('click', '#confirm-delete-btn', function(e) {
                e.preventDefault();
                var paymentId = $(this).data('id');
                var deleteUrl = $(this).attr('href');

                // Hide the modal
                $('#delete-method-modal').modal('hide');

                // Show loading SweetAlert
                Swal.fire({
                    title: 'Processing',
                    text: 'Deleting payment method...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send delete request
                $.ajax({
                    url: deleteUrl,
                    type: 'GET',
                    success: function(response) {
                        // Show success message
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Payment method has been deleted successfully.',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        }).then((result) => {
                            // Reload page
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        // Show error message
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to delete payment method. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                });
            });

            // Handle click on edit button in view modal
            $(document).on('click', '.view-edit-btn', function() {
                var paymentId = $(this).data('id');
                console.log("Edit button clicked from view modal for ID:", paymentId);

                // Close view modal
                $('#view-method-modal').modal('hide');

                // Trigger edit click after a small delay to let the first modal close
                setTimeout(function() {
                    $('.edit-payment-method[data-id="' + paymentId + '"]').trigger('click');
                }, 500);
            }); // Handle file upload preview for add form
            $('#payment_image').on('change', function() {
                previewImage(this, '#image_preview_container');
            });

            // Handle file upload preview for edit form
            $('#edit_payment_image').on('change', function() {
                previewImage(this, '#edit_image_preview_container');
            });

            // Function to preview uploaded images
            function previewImage(input, previewContainerId) {
                const container = $(previewContainerId);
                container.empty();

                if (input.files && input.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        $('<img>')
                            .attr('src', e.target.result)
                            .addClass('image-preview')
                            .appendTo(container);
                    };

                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Form validation and submission for add method form
            $('#add-method-form').on('submit', function(e) {
                e.preventDefault();

                // Update hidden input with Quill content before validation
                document.getElementById('description').value = quillEditor.root.innerHTML;

                if ($(this)[0].checkValidity() === false) {
                    e.stopPropagation();

                    // Show SweetAlert for validation errors
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    $(this).addClass('was-validated');
                    return false;
                }

                // Create FormData object for file upload
                var formData = new FormData(this);

                // Show loading spinner
                Swal.fire({
                    title: 'Processing',
                    text: 'Saving payment method...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.close();
                        // Show success message
                        Swal.fire({
                            title: 'Success!',
                            text: 'Payment method has been added successfully.',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        }).then((result) => {
                            // Reload page to show new payment method
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to save payment method. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                });
            });

            // Form validation and submission for edit method form
            $('#edit-method-form').on('submit', function(e) {
                e.preventDefault();

                // Update hidden input with Quill content before validation
                document.getElementById('edit_description').value = editQuillEditor.root.innerHTML;
                if ($(this)[0].checkValidity() === false) {
                    e.stopPropagation();

                    // Show SweetAlert for validation errors
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    $(this).addClass('was-validated');
                    return false;
                }

                // Create FormData object for file upload
                var formData = new FormData(this);

                // Show loading spinner
                Swal.fire({
                    title: 'Processing',
                    text: 'Updating payment method...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.close();
                        // Show success message
                        Swal.fire({
                            title: 'Success!',
                            text: 'Payment method has been updated successfully.',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        }).then((result) => {
                            // Reload page to show updated payment method
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to update payment method. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                });
                $(this).addClass('was-validated');
            });
        }

        // Show/hide gateway_provider field based on type selection (Add modal)
        $('#type').on('change', function () {
            if ($(this).val() === 'gateway') {
                $('#gateway_provider_group').show();
            } else {
                $('#gateway_provider_group').hide();
                $('#gateway_provider').val('');
            }
        });

        // Show/hide gateway_provider field based on type selection (Edit modal)
        $('#edit_type').on('change', function () {
            if ($(this).val() === 'gateway') {
                $('#edit_gateway_provider_group').show();
            } else {
                $('#edit_gateway_provider_group').hide();
                $('#edit_gateway_provider').val('');
            }
        });
    </script>
</body>

</html>