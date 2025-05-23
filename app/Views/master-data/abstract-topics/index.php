<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title' => 'Abstract Topics')); ?>

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

        /* Table responsive card view for mobile */
        @media (max-width: 767px) {
            .table-responsive-card tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #e9e9ef;
                border-radius: 0.25rem;
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            }

            .table-responsive-card tbody td {
                display: block;
                border: none;
                padding: 0.5rem 1rem;
                position: relative;
                text-align: right;
            }

            .table-responsive-card tbody td:before {
                content: attr(data-label);
                float: left;
                font-weight: bold;
            }

            .table-responsive-card tbody td:not(:last-child) {
                border-bottom: 1px solid #e9e9ef;
            }

            .table-responsive-card thead {
                display: none;
            }
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
                <div class="container-fluid"> <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">Abstract Topics</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                                        <li class="breadcrumb-item active">Abstract Topics</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <!-- Flash messages -->
                    <?php if (session()->getFlashdata('success')) : ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong> <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Abstract Topics List</h5>
                                    <div class="flex-shrink-0">
                                        <button class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addTopicModal">
                                            <i class="ri-add-line align-middle me-1"></i> Add New Topic
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="abstract-topics-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Created At</th>
                                                <th>Updated At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody> <?php foreach ($abstractTopics as $topic) : ?>
                                                <?php if ($topic->is_deleted == 0) : ?>
                                                    <tr>
                                                        <td data-label="ID"><?= $topic->id ?></td>
                                                        <td data-label="Name"><?= $topic->name ?></td>
                                                        <td data-label="Description"><?= $topic->description ?></td>
                                                        <td data-label="Status">
                                                            <?php if ($topic->is_active == 1) : ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php else : ?>
                                                                <span class="badge bg-danger">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td data-label="Created At"><?= date('d M Y H:i', strtotime($topic->created_at)) ?></td>
                                                        <td data-label="Updated At"><?= date('d M Y H:i', strtotime($topic->updated_at)) ?></td>
                                                        <td data-label="Actions">
                                                            <div class="d-flex gap-2">
                                                                <div class="view">
                                                                    <button class="btn btn-sm btn-info view-topic-btn" data-id="<?= $topic->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                                                        <i class="ri-eye-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="edit">
                                                                    <button class="btn btn-sm btn-success edit-topic-btn" data-id="<?= $topic->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                                        <i class="ri-pencil-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="remove">
                                                                    <button class="btn btn-sm btn-danger remove-topic-btn" data-id="<?= $topic->id ?>" data-name="<?= $topic->name ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                                        <i class="ri-delete-bin-fill"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
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
    <!-- END layout-wrapper --> <!-- Add Abstract Topic Modal -->
    <div class="modal fade" id="addTopicModal" tabindex="-1" aria-labelledby="addTopicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTopicModalLabel">Add Abstract Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/abstract-topics/create" method="post" id="add-topic-form">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">Please enter a topic name.</div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select" id="is_active" name="is_active" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Topic</button>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- Edit Abstract Topic Modal -->
    <div class="modal fade" id="editTopicModal" tabindex="-1" aria-labelledby="editTopicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-loading" id="edit-loading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="loading-text">Loading topic details...</div>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="editTopicModalLabel">Edit Abstract Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/abstract-topics/update/" method="post" id="edit-topic-form">
                    <input type="hidden" id="edit_topic_id" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
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
                        <button type="submit" class="btn btn-primary">Update Topic</button>
                    </div>
                </form>
            </div>
        </div>
    </div> 
    
    <!-- Delete Abstract Topic Modal -->
    <div class="modal fade" id="deleteTopicModal" tabindex="-1" aria-labelledby="deleteTopicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="deleteTopicModalLabel">Delete Abstract Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto">
                            <div class="avatar-title bg-light text-danger rounded-circle fs-1">
                                <i class="ri-delete-bin-2-line"></i>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <h4 class="mb-3">Are you sure you want to delete this topic?</h4>
                        <p class="text-muted mb-4" id="delete-topic-name">This action cannot be undone.</p>

                        <div class="hstack gap-2 justify-content-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="confirm-delete-btn" class="btn btn-danger">
                                <i class="ri-delete-bin-line align-bottom"></i> Delete Topic
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Abstract Topic Modal -->
    <div class="modal fade" id="viewTopicModal" tabindex="-1" aria-labelledby="viewTopicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-loading" id="view-loading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="loading-text">Loading topic details...</div>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="viewTopicModalLabel">Abstract Topic Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Topic Name</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_name"></p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h5 class="text-muted fw-normal">Description</h5>
                        <p class="text-dark fw-medium fs-15 mb-3" id="view_description"></p>
                    </div>
                    <div class="mb-3">
                        <h5 class="text-muted fw-normal">Status</h5>
                        <p class="text-dark fw-medium fs-15 mb-3" id="view_status"></p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Created At</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_created_at"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Updated At</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_updated_at"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary view-edit-btn">Edit</button>
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

   
   <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM loaded for Abstract Topics");

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

            // Initialize DataTable
            var topicTable = $('#abstract-topics-table').DataTable({
                responsive: true,
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                order: [
                    [0, 'desc']
                ],
                dom: '<"row"<"col-sm-6"B><"col-sm-6"f>>rtip',
                stateSave: true,
                stateDuration: 60 * 60 * 24, // Save state for 1 day
                buttons: [{
                        extend: 'copy',
                        text: '<i class="ri-file-copy-line"></i> Copy',
                        className: 'btn btn-sm btn-soft-secondary'
                    },
                    {
                        extend: 'csv',
                        text: '<i class="ri-file-text-line"></i> CSV',
                        className: 'btn btn-sm btn-soft-secondary'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="ri-file-excel-line"></i> Excel',
                        className: 'btn btn-sm btn-soft-secondary'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="ri-file-pdf-line"></i> PDF',
                        className: 'btn btn-sm btn-soft-secondary'
                    },
                    {
                        extend: 'print',
                        text: '<i class="ri-printer-line"></i> Print',
                        className: 'btn btn-sm btn-soft-secondary'
                    }
                ],
                columnDefs: [{
                        responsivePriority: 1,
                        targets: 0
                    }, // ID column
                    {
                        responsivePriority: 2,
                        targets: 1
                    }, // Name column
                    {
                        responsivePriority: 3,
                        targets: 3
                    }, // Status column
                    {
                        responsivePriority: 4,
                        targets: 6
                    }, // Actions column
                    {
                        targets: 6,
                        orderable: false,
                        searchable: false
                    }
                ],                language: {
                    emptyTable: "No abstract topics found in <?= $program->name ?>. Click 'Add New' to create one.",
                    zeroRecords: "No matching abstract topics found in <?= $program->name ?>",
                    info: "Showing _START_ to _END_ of _TOTAL_ abstract topics for <?= $program->name ?>",
                    infoEmpty: "No abstract topics available in <?= $program->name ?>",
                    infoFiltered: "(filtered from _MAX_ total abstract topics in <?= $program->name ?>)",
                    search: "<i class='ri-search-line'></i>",
                    paginate: {
                        first: "<i class='ri-arrow-left-s-line'></i><i class='ri-arrow-left-s-line'></i>",
                        last: "<i class='ri-arrow-right-s-line'></i><i class='ri-arrow-right-s-line'></i>",
                        next: "<i class='ri-arrow-right-s-line'></i>",
                        previous: "<i class='ri-arrow-left-s-line'></i>"
                    }
                },
                drawCallback: function() {
                    // Initialize tooltips after each draw
                    $('[data-bs-toggle="tooltip"]').tooltip();

                    // Apply table-responsive-card custom styling on mobile
                    if (window.innerWidth < 768) {
                        $('#abstract-topics-table').addClass('table-responsive-card');
                    } else {
                        $('#abstract-topics-table').removeClass('table-responsive-card');
                    }
                }
            });

            // Ensure jQuery is loaded
            if (typeof jQuery !== 'undefined') {
                console.log("jQuery is loaded for Abstract Topics");
                initializeTopicsFunctions();
            } else {
                console.error("jQuery is not loaded!");
            }
        });

        function initializeTopicsFunctions() {
            // Handle delete button click with event delegation
            $(document).on('click', '.remove-topic-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var topicId = $(this).data('id');
                var topicName = $(this).data('name');
                console.log("Delete button clicked for ID:", topicId);

                // Set the topic name in the confirmation dialog
                $('#delete-topic-name').html('You are about to delete the topic: <strong>' + topicName + '</strong>. This action cannot be undone.');

                // Set delete URL
                $('#confirm-delete-btn').data('id', topicId);

                // Show the modal
                $('#deleteTopicModal').modal('show');
            });

            // Handle delete confirmation with AJAX
            $('#confirm-delete-btn').on('click', function(e) {
                e.preventDefault();
                var topicId = $(this).data('id');
                var deleteUrl = '/master-data/abstract-topics/delete/' + topicId;

                // Disable the button and show loading state
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');

                $.ajax({
                    url: deleteUrl,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $('#deleteTopicModal').modal('hide');

                        if (response.success) {
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message || 'Abstract topic deleted successfully'
                            }).then(function() {
                                // Refresh the table
                                refreshAbstractTopicsTable();
                            });
                        } else {
                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to delete abstract topic'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#deleteTopicModal').modal('hide');

                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete abstract topic. Please try again.'
                        });
                    },
                    complete: function() {
                        // Reset button state
                        $('#confirm-delete-btn').prop('disabled', false).html('<i class="ri-delete-bin-line align-bottom"></i> Delete Topic');
                    }
                });
            });
            // Handle edit button click - delegate event for dynamic content
            $(document).on('click', '.edit-topic-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var topicId = $(this).data('id');
                console.log("Edit button clicked for ID:", topicId);

                // Show modal first
                $('#editTopicModal').modal('show');
                $('#edit-loading').show();

                // Fetch the abstract topic data
                $.ajax({
                    url: '/master-data/abstract-topics/getAbstractTopic/' + topicId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Edit Ajax response:", response);

                        if (response && response.success) {
                            var topic = response.data;

                            // Populate form
                            $('#edit_topic_id').val(topic.id);
                            $('#edit_name').val(topic.name);
                            $('#edit_description').val(topic.description);
                            $('#edit_is_active').val(topic.is_active);

                            // Set form action URL
                            $('#edit-topic-form').attr('action', '/master-data/abstract-topics/update/' + topic.id);

                            // Hide loading spinner
                            $('#edit-loading').hide();
                        } else {
                            // Hide loading spinner
                            $('#edit-loading').hide();

                            // Show error message
                            $('#editTopicModal').modal('hide');
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to fetch abstract topic data'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Hide loading spinner
                        $('#edit-loading').hide();

                        // Show error message
                        $('#editTopicModal').modal('hide');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to fetch abstract topic data. Please try again.'
                        });
                    }
                });
            }); // Function to refresh the table without page reload
            function refreshAbstractTopicsTable() {
                // Show loading indicator
                Swal.fire({
                    title: 'Loading...',
                    html: 'Refreshing abstract topics data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Reload the page with a small delay to show the loading indicator
                setTimeout(function() {
                    location.reload();
                }, 1000);
            }
            // Form validation for add topic form
            $('#add-topic-form').on('submit', function(e) {
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
                    $(this).addClass('was-validated');
                    return false;
                }

                e.preventDefault();
                var name = $('#name').val().trim();

                if (!name) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Topic name is required'
                    });
                    return false;
                }

                // Get form data
                var formData = $(this).serialize();

                // Show loading indicator
                var submitBtn = $(this).find('button[type="submit"]');
                var originalBtnText = submitBtn.html();
                submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
                submitBtn.prop('disabled', true);

                // Submit form via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        // Reset button state
                        submitBtn.html(originalBtnText);
                        submitBtn.prop('disabled', false);

                        if (response.success) {
                            // Close modal
                            $('#addTopicModal').modal('hide');

                            // Clear form
                            $('#add-topic-form')[0].reset();

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message || 'Abstract topic created successfully'
                            }).then(function() {
                                // Refresh the table
                                refreshAbstractTopicsTable();
                            });
                        } else {
                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to create abstract topic'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Reset button state
                        submitBtn.html(originalBtnText);
                        submitBtn.prop('disabled', false);

                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to create abstract topic. Please try again.'
                        });
                    }
                });
            });

            // Edit form validation with AJAX
            $('#edit-topic-form').on('submit', function(e) {
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
                    $(this).addClass('was-validated');
                    return false;
                }

                e.preventDefault();
                var name = $('#edit_name').val().trim();

                if (!name) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Topic name is required'
                    });
                    return false;
                }

                // Get form data
                var formData = $(this).serialize();

                // Show loading indicator
                var submitBtn = $(this).find('button[type="submit"]');
                var originalBtnText = submitBtn.html();
                submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
                submitBtn.prop('disabled', true);

                // Submit form via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        // Reset button state
                        submitBtn.html(originalBtnText);
                        submitBtn.prop('disabled', false);

                        if (response.success) {
                            // Close modal
                            $('#editTopicModal').modal('hide');

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message || 'Abstract topic updated successfully'
                            }).then(function() {
                                // Refresh the table
                                refreshAbstractTopicsTable();
                            });
                        } else {
                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to update abstract topic'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Reset button state
                        submitBtn.html(originalBtnText);
                        submitBtn.prop('disabled', false);

                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to update abstract topic. Please try again.'
                        });
                    }
                });
            });

            // Handle view button click with event delegation
            $(document).on('click', '.view-topic-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var topicId = $(this).data('id');
                console.log("View button clicked for ID:", topicId);

                // Show modal first
                $('#viewTopicModal').modal('show');
                $('#view-loading').show();

                // Get topic details
                $.ajax({
                    url: '/master-data/abstract-topics/getAbstractTopic/' + topicId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("View Ajax response:", response);
                        if (response && response.success) {
                            var topic = response.data;

                            // Populate modal
                            $('#view_name').text(topic.name || 'N/A');
                            $('#view_description').text(topic.description || 'No description provided');

                            // Format status with badge
                            var statusBadge = topic.is_active == 1 ?
                                '<span class="badge bg-success">Active</span>' :
                                '<span class="badge bg-danger">Inactive</span>';
                            $('#view_status').html(statusBadge);

                            // Format dates
                            var createdAt = topic.created_at ?
                                new Date(topic.created_at).toLocaleDateString('en-US', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) : 'N/A';

                            var updatedAt = topic.updated_at ?
                                new Date(topic.updated_at).toLocaleDateString('en-US', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) : 'N/A';

                            $('#view_created_at').text(createdAt);
                            $('#view_updated_at').text(updatedAt);

                            // Set topic ID for the edit button in view modal
                            $('.view-edit-btn').data('id', topic.id);
                        } else {
                            console.error("Invalid response:", response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load topic details'
                            });
                        }

                        // Hide loading spinner
                        $('#view-loading').hide();
                    },
                    error: function(xhr, status, error) {
                        console.error("View Ajax error:", xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while fetching topic details'
                        });
                        $('#view-loading').hide();
                    }
                });
            });

            // Handle click on edit button in view modal
            $(document).on('click', '.view-edit-btn', function() {
                var topicId = $(this).data('id');
                console.log("Edit button clicked from view modal for ID:", topicId);

                // Close view modal
                $('#viewTopicModal').modal('hide');

                // Trigger edit click after a small delay to let the first modal close
                setTimeout(function() {
                    $('.edit-topic-btn[data-id="' + topicId + '"]').trigger('click');
                }, 500);
            });
        } // End of initializeTopicsFunctions
    </script>

</body>

</html>