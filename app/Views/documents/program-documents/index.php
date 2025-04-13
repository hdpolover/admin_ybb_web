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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Documents', 'title' => 'Program Documents')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Program Documents List</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center mb-4">
                                        <div class="col-md-6">
                                            <div class="d-flex flex-wrap gap-2">
                                                <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" data-bs-target="#add-document-modal">
                                                    <i class="ri-add-line align-bottom me-1"></i> Add Document
                                                </button>
                                                <button type="button" class="btn btn-primary visibility-btn" data-bs-toggle="modal" data-bs-target="#visibility-config-modal">
                                                    <i class="ri-eye-line align-bottom me-1"></i> Visibility Settings
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-md-end">
                                                <div class="search-box ms-2">
                                                    <input type="text" class="form-control search" placeholder="Search...">
                                                    <i class="ri-search-line search-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="program-documents-table" class="table align-middle table-nowrap table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col" style="width: 50px;">#</th>
                                                    <th scope="col">Document Name</th>
                                                    <th scope="col">Type</th>
                                                    <th scope="col">Date Added</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($programDocuments)): ?>
                                                    <?php foreach ($programDocuments as $index => $doc): ?>
                                                        <tr>
                                                            <td><?= $index + 1 ?></td>
                                                            <td>
                                                                <?php if (!empty($doc->file_url) || !empty($doc->drive_url)): ?>
                                                                    <a href="<?= !empty($doc->file_url) ? $doc->file_url : $doc->drive_url ?>" target="_blank" class="text-primary">
                                                                        <?= $doc->name ?>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <?= $doc->name ?>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $badge_class = "badge bg-info";
                                                                if ($doc->type == 'loa') {
                                                                    $badge_class = "badge bg-primary";
                                                                } elseif ($doc->type == 'agreement') {
                                                                    $badge_class = "badge bg-success";
                                                                } elseif ($doc->type == 'complement') {
                                                                    $badge_class = "badge bg-warning";
                                                                }
                                                                ?>
                                                                <span class="<?= $badge_class ?>"><?= ucfirst($doc->type ?? 'Document') ?></span>
                                                            </td>
                                                            <td><?= date('d M Y', strtotime($doc->created_at)) ?></td>
                                                            <td>
                                                                <?php if ($doc->is_active == 1): ?>
                                                                    <span class="badge bg-success">Active</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger">Inactive</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex gap-2">
                                                                    <div class="view">
                                                                        <a href="program-documents/view/<?= $doc->id ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                                                            <i class="ri-eye-fill"></i>
                                                                        </a>
                                                                    </div>
                                                                    <?php if (!empty($doc->file_url) || !empty($doc->drive_url)): ?>
                                                                        <div class="download">
                                                                            <a href="<?= !empty($doc->file_url) ? $doc->file_url : $doc->drive_url ?>" target="_blank" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Download">
                                                                                <i class="ri-download-2-line"></i>
                                                                            </a>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <div class="edit">
                                                                        <button class="btn btn-sm btn-success edit-document" data-id="<?= $doc->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                                            <i class="ri-pencil-fill"></i>
                                                                        </button>
                                                                    </div>
                                                                    <div class="remove">
                                                                        <button class="btn btn-sm btn-danger delete-document" data-id="<?= $doc->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                                            <i class="ri-delete-bin-fill"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center">No documents found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <div class="pagination-wrap">
                                            <nav aria-label="Page navigation example">
                                                <ul class="pagination">
                                                    <li class="page-item disabled">
                                                        <a class="page-link" href="#" aria-label="Previous">
                                                            <i class="mdi mdi-chevron-left"></i>
                                                        </a>
                                                    </li>
                                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link" href="#" aria-label="Next">
                                                            <i class="mdi mdi-chevron-right"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
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

    <?= $this->include('partials/vendor-scripts') ?> <!-- Add Document Modal -->
    <div class="modal fade" id="add-document-modal" tabindex="-1" aria-labelledby="add-document-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-document-modal-label">Add New Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/program-documents/create" method="post" enctype="multipart/form-data" id="add-document-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="document-name" class="form-label">Document Name*</label>
                                    <input type="text" class="form-control" id="document-name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="document-type" class="form-label">Document Type*</label>
                                    <select class="form-select" id="document-type" name="type" required>
                                        <option value="" selected disabled>Select Type</option>
                                        <option value="loa">Letter of Acceptance (LOA)</option>
                                        <option value="agreement">Agreement Letter</option>
                                        <option value="complement">Complementary Document</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="document-desc" class="form-label">Description</label>
                            <textarea class="form-control" id="document-desc" name="desc" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="upload-method-switch" checked>
                                        <label class="form-check-label" for="upload-method-switch">Upload File / External Link</label>
                                    </div>
                                </div>

                                <div id="upload-file-section" class="mb-3">
                                    <label for="document-file" class="form-label">Upload Document</label>
                                    <input type="file" class="form-control" id="document-file" name="document_file">
                                    <small class="text-muted">Supported formats: PDF, DOC, DOCX, XLS, XLSX (Max size: 10MB)</small>
                                    <input type="hidden" name="is_upload" id="is-upload" value="1">
                                </div>

                                <div id="drive-url-section" class="mb-3 d-none">
                                    <label for="drive-url" class="form-label">External Document Link</label>
                                    <input type="url" class="form-control" id="drive-url" name="drive_url" placeholder="https://drive.google.com/file/...">
                                    <small class="text-muted">Enter Google Drive or other external document link</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="document-visibility" class="form-label">Document Visibility</label>
                                    <select class="form-select" id="document-visibility" name="visibility">
                                        <option value="1" selected>Public</option>
                                        <option value="0">Private</option>
                                    </select>
                                    <small class="text-muted">Public documents are visible to all users</small>
                                </div>

                                <div class="mb-3">
                                    <label for="document-status" class="form-label">Status</label>
                                    <select class="form-select" id="document-status" name="is_active">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="program_id" value="<?= session('current_program') ?>">
                        <input type="hidden" name="is_generated" value="0">
                        <input type="hidden" name="is_deleted" value="0">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="add-document-form" class="btn btn-primary">Save Document</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Document View Modal -->
    <div class="modal fade" id="view-document-modal" tabindex="-1" aria-labelledby="view-document-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="view-document-modal-label">Document Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="view-document-content">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="view-document-download" class="btn btn-primary" target="_blank">Download</a>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTables js -->
    <script src="/assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/libs/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="/assets/libs/datatables/dataTables.responsive.min.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var documentTable = $('#program-documents-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true,
                columnDefs: [{
                        orderable: false,
                        targets: [6]
                    } // Only make the actions column non-sortable
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    // Initialize tooltips
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });
                }
            });

            // Edit document button handler
            $(document).on('click', '.edit-document', function() {
                var documentId = $(this).data('id');
                // Fetch document data and populate the edit modal
                $.ajax({
                    url: '/program-documents/get-document/' + documentId,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            // Populate the edit modal with document data
                            // This will be implemented when the edit modal is created
                        } else {
                            alert('Error fetching document data');
                        }
                    },
                    error: function() {
                        alert('Server error');
                    }
                });
            });

            // Delete document button handler
            $(document).on('click', '.delete-document', function() {
                if (confirm('Are you sure you want to delete this document?')) {
                    var documentId = $(this).data('id');
                    $.ajax({
                        url: '/program-documents/delete/' + documentId,
                        type: 'POST',
                        success: function(response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                alert('Error deleting document');
                            }
                        },
                        error: function() {
                            alert('Server error');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>