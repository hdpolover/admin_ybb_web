<?= $this->include('partials/main') ?>

<head> <?php echo view('partials/title-meta', array('title' => 'Starter')); ?> <!-- Sweet Alert css-->
    <link href="/assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet">

    <style>
        .table td {
            white-space: nowrap;
        }

        .table td.description-cell {
            max-width: 200px;
            white-space: normal;
            word-wrap: break-word;
        }

        .dtr-details {
            width: 100%;
        }

        @media (max-width: 768px) {
            .table-responsive {
                border: none;
            }
        }
    </style>

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
                <div class="container-fluid"> <?php echo view('partials/page-title', array('pagetitle' => 'Documents', 'title' => 'Program Documents')); ?>

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
                                        <table id="program-documents-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col" style="width: 50px;">#</th>
                                                    <th scope="col">Document Name</th>
                                                    <th scope="col">Type</th>
                                                    <th scope="col">Date Added</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col" style="width: 150px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($programDocuments)): ?>
                                                    <?php foreach ($programDocuments as $index => $doc): ?>
                                                        <tr>
                                                            <td><?= $index + 1 ?></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-grow-1">
                                                                        <?php if (!empty($doc->file_url) || !empty($doc->drive_url)): ?>
                                                                            <a href="<?= !empty($doc->file_url) ? $doc->file_url : $doc->drive_url ?>" target="_blank" class="text-primary fw-medium" data-bs-toggle="tooltip" title="<?= htmlspecialchars($doc->name) ?>">
                                                                                <?= mb_strimwidth($doc->name, 0, 40, "...") ?>
                                                                            </a>
                                                                        <?php else: ?>
                                                                            <span class="fw-medium" data-bs-toggle="tooltip" title="<?= htmlspecialchars($doc->name) ?>">
                                                                                <?= mb_strimwidth($doc->name, 0, 40, "...") ?>
                                                                            </span>
                                                                        <?php endif; ?>
                                                                        <?php if ($doc->is_generated == 1): ?>
                                                                            <small class="badge bg-info-subtle text-info ms-1">Generated</small>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
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
                                                                        <a href="/documents/program-documents/view/<?= $doc->id ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
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
                                                    <?php endforeach; ?> <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No documents found</td>
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


    <!-- Add Document Modal -->
    <div class="modal fade" id="add-document-modal" tabindex="-1" aria-labelledby="add-document-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-document-modal-label">Add New Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/documents/program-documents/create" method="post" id="add-document-form">
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
                        </div> <!-- Document Type Info -->
                        <div class="alert alert-info" id="document-type-info" style="display: none;">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="ri-information-line fs-16"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div id="loa-info" style="display: none;">
                                        <h6 class="alert-heading fs-14">System Generated Document</h6>
                                        <p class="mb-2">LOA documents are automatically generated by the system based on participant data.</p>
                                        <p class="mb-0"><strong>Configuration Required:</strong></p>
                                        <ul class="mb-0">
                                            <li>LOA template must be configured after creating this document</li>
                                            <li>Template settings will be available in the document details page</li>
                                        </ul>
                                    </div>
                                    <div id="agreement-info" style="display: none;">
                                        <h6 class="alert-heading fs-14">Agreement Template Upload</h6>
                                        <p class="mb-0">Please provide the Google Drive link to the agreement letter template that participants will need to sign.</p>
                                    </div>
                                    <div id="complement-info" style="display: none;">
                                        <h6 class="alert-heading fs-14">External Document Link</h6>
                                        <p class="mb-0">Please provide the Google Drive link to the complementary document.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Drive URL Section -->
                        <div class="row" id="drive-url-section" style="display: none;">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="drive-url" class="form-label" id="drive-url-label">Document Link*</label>
                                    <input type="url" class="form-control" id="drive-url" name="drive_url" placeholder="https://drive.google.com/file/...">
                                    <small class="text-muted" id="drive-url-help">Enter Google Drive or other external document link</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="document-visibility" class="form-label">Document Visibility</label>
                                    <select class="form-select" id="document-visibility" name="visibility">
                                        <option value="1" selected>Public</option>
                                        <option value="0">Private</option>
                                    </select>
                                    <small class="text-muted">Public documents are visible to all users</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="document-status" class="form-label">Status</label>
                                    <select class="form-select" id="document-status" name="is_active">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div> <input type="hidden" name="program_id" value="<?= session('current_program') ?>">
                        <input type="hidden" id="is-upload" name="is_upload" value="0">
                        <input type="hidden" id="is-generated" name="is_generated" value="0">
                        <input type="hidden" name="is_deleted" value="0">
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
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

    <!-- Edit Document Modal -->
    <div class="modal fade" id="edit-document-modal" tabindex="-1" aria-labelledby="edit-document-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edit-document-modal-label">Edit Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/documents/program-documents/update/" method="post" id="edit-document-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-document-name" class="form-label">Document Name*</label>
                                    <input type="text" class="form-control" id="edit-document-name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-document-type" class="form-label">Document Type*</label>
                                    <select class="form-select" id="edit-document-type" name="type" required>
                                        <option value="" disabled>Select Type</option>
                                        <option value="loa">Letter of Acceptance (LOA)</option>
                                        <option value="agreement">Agreement Letter</option>
                                        <option value="complement">Complementary Document</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit-document-desc" class="form-label">Description</label>
                            <textarea class="form-control" id="edit-document-desc" name="desc" rows="3"></textarea>
                        </div> <!-- Document Type Info -->
                        <div class="alert alert-info" id="edit-document-type-info" style="display: none;">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="ri-information-line fs-16"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div id="edit-loa-info" style="display: none;">
                                        <h6 class="alert-heading fs-14">System Generated Document</h6>
                                        <p class="mb-2">LOA documents are automatically generated by the system based on participant data.</p>
                                        <p class="mb-0"><strong>Configuration Required:</strong></p>
                                        <ul class="mb-0">
                                            <li>LOA template must be configured after creating this document</li>
                                            <li>Template settings will be available in the document details page</li>
                                        </ul>
                                    </div>
                                    <div id="edit-agreement-info" style="display: none;">
                                        <h6 class="alert-heading fs-14">Agreement Template Upload</h6>
                                        <p class="mb-0">Please provide the Google Drive link to the agreement letter template that participants will need to sign.</p>
                                    </div>
                                    <div id="edit-complement-info" style="display: none;">
                                        <h6 class="alert-heading fs-14">External Document Link</h6>
                                        <p class="mb-0">Please provide the Google Drive link to the complementary document.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Drive URL Section -->
                        <div class="row" id="edit-drive-url-section" style="display: none;">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="edit-drive-url" class="form-label" id="edit-drive-url-label">Document Link*</label>
                                    <input type="url" class="form-control" id="edit-drive-url" name="drive_url" placeholder="https://drive.google.com/file/...">
                                    <small class="text-muted" id="edit-drive-url-help">Enter Google Drive or other external document link</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-document-visibility" class="form-label">Document Visibility</label>
                                    <select class="form-select" id="edit-document-visibility" name="visibility">
                                        <option value="1">Public</option>
                                        <option value="0">Private</option>
                                    </select>
                                    <small class="text-muted">Public documents are visible to all users</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-document-status" class="form-label">Status</label>
                                    <select class="form-select" id="edit-document-status" name="is_active">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div> <input type="hidden" id="edit-is-upload" name="is_upload" value="0">
                        <input type="hidden" id="edit-is-generated" name="is_generated" value="0">
                        <input type="hidden" id="edit-document-id" name="document_id" value="">
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="edit-document-form" class="btn btn-primary">Update Document</button>
                </div>
            </div>
        </div>
    </div>



    <?= $this->include('partials/vendor-scripts') ?>

    <!-- Sweet Alert js-->
    <script src="/assets/libs/sweetalert2/sweetalert2.min.js"></script>

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

    <script src="/assets/js/pages/datatables.init.js"></script> <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {
            console.log('jQuery loaded successfully');
            console.log('Document ready fired'); // Test basic element selection
            console.log('Document type dropdown found:', $('#document-type').length);
            console.log('Drive URL section found:', $('#drive-url-section').length);
            console.log('Type info section found:', $('#document-type-info').length);

            // Test click event
            $('#document-type').click(function() {
                console.log('Document type dropdown was clicked!');
            });

            // Simple direct event binding for add modal
            $('#document-type').change(function() {
                var selectedType = $(this).val();
                console.log('Document type selected:', selectedType);

                // Reset everything first
                $('#drive-url-section').hide();
                $('#document-type-info').hide();
                $('#loa-info, #agreement-info, #complement-info').hide();
                $('#drive-url').prop('required', false);

                if (selectedType === 'loa') {
                    console.log('Showing LOA configuration');
                    $('#document-type-info').show();
                    $('#loa-info').show();
                    $('#is-upload').val('0');
                    $('#is-generated').val('1');

                } else if (selectedType === 'agreement') {
                    console.log('Showing Agreement configuration');
                    $('#drive-url-section').show();
                    $('#document-type-info').show();
                    $('#agreement-info').show();
                    $('#drive-url-label').text('Agreement Template Link*');
                    $('#drive-url-help').text('Enter Google Drive link to the agreement letter template');
                    $('#drive-url').prop('required', true);
                    $('#is-upload').val('1');
                    $('#is-generated').val('0');

                } else if (selectedType === 'complement') {
                    console.log('Showing Complement configuration');
                    $('#drive-url-section').show();
                    $('#document-type-info').show();
                    $('#complement-info').show();
                    $('#drive-url-label').text('Document Link*');
                    $('#drive-url-help').text('Enter Google Drive link to the complementary document');
                    $('#drive-url').prop('required', true);
                    $('#is-upload').val('1');
                    $('#is-generated').val('0');
                }
            });

            // Similar binding for edit modal
            $('#edit-document-type').change(function() {
                var selectedType = $(this).val();
                console.log('Edit document type selected:', selectedType);

                // Reset everything first
                $('#edit-drive-url-section').hide();
                $('#edit-document-type-info').hide();
                $('#edit-loa-info, #edit-agreement-info, #edit-complement-info').hide();
                $('#edit-drive-url').prop('required', false);

                if (selectedType === 'loa') {
                    $('#edit-document-type-info').show();
                    $('#edit-loa-info').show();
                    $('#edit-is-upload').val('0');
                    $('#edit-is-generated').val('1');

                } else if (selectedType === 'agreement') {
                    $('#edit-drive-url-section').show();
                    $('#edit-document-type-info').show();
                    $('#edit-agreement-info').show();
                    $('#edit-drive-url-label').text('Agreement Template Link*');
                    $('#edit-drive-url-help').text('Enter Google Drive link to the agreement letter template');
                    $('#edit-drive-url').prop('required', true);
                    $('#edit-is-upload').val('1');
                    $('#edit-is-generated').val('0');

                } else if (selectedType === 'complement') {
                    $('#edit-drive-url-section').show();
                    $('#edit-document-type-info').show();
                    $('#edit-complement-info').show();
                    $('#edit-drive-url-label').text('Document Link*');
                    $('#edit-drive-url-help').text('Enter Google Drive link to the complementary document');
                    $('#edit-drive-url').prop('required', true);
                    $('#edit-is-upload').val('1');
                    $('#edit-is-generated').val('0');
                }
            });

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

            // Document type change handlers
            function handleDocumentTypeChange(typeSelect, urlSection, typeInfo, urlInput, isUploadInput, isGeneratedInput, urlLabel, urlHelp) {
                typeSelect.on('change', function() {
                    var typeValue = $(this).val();
                    console.log('Document type changed to:', typeValue);

                    // Hide all info sections first
                    $('#loa-info, #agreement-info, #complement-info, #edit-loa-info, #edit-agreement-info, #edit-complement-info').hide();

                    if (typeValue === 'loa') {
                        // LOA: System generated, no URL needed
                        console.log('Showing LOA info');
                        urlSection.hide();
                        typeInfo.show();

                        // Show appropriate info section
                        if (typeInfo.attr('id') === 'document-type-info') {
                            $('#loa-info').show();
                        } else {
                            $('#edit-loa-info').show();
                        }

                        isUploadInput.val('0');
                        isGeneratedInput.val('1');
                        urlInput.prop('required', false).val('');
                    } else if (typeValue === 'agreement') {
                        // Agreement: Upload type, needs template URL
                        console.log('Showing Agreement info');
                        urlSection.show();
                        typeInfo.show();

                        // Show appropriate info section
                        if (typeInfo.attr('id') === 'document-type-info') {
                            $('#agreement-info').show();
                        } else {
                            $('#edit-agreement-info').show();
                        }

                        urlLabel.text('Agreement Template Link*');
                        urlHelp.text('Enter Google Drive link to the agreement letter template');
                        isUploadInput.val('1');
                        isGeneratedInput.val('0');
                        urlInput.prop('required', true);
                    } else if (typeValue === 'complement') {
                        // Complement: External link type
                        console.log('Showing Complement info');
                        urlSection.show();
                        typeInfo.show();

                        // Show appropriate info section
                        if (typeInfo.attr('id') === 'document-type-info') {
                            $('#complement-info').show();
                        } else {
                            $('#edit-complement-info').show();
                        }

                        urlLabel.text('Document Link*');
                        urlHelp.text('Enter Google Drive link to the complementary document');
                        isUploadInput.val('1');
                        isGeneratedInput.val('0');
                        urlInput.prop('required', true);
                    } else {
                        // No type selected
                        console.log('No type selected');
                        urlSection.hide();
                        typeInfo.hide();
                        isUploadInput.val('0');
                        isGeneratedInput.val('0');
                        urlInput.prop('required', false);
                    }
                });
            } // Initialize handlers for add modal
            console.log('Initializing document type handlers...');

            // Test direct binding
            $('#document-type').on('change', function() {
                console.log('DIRECT: Document type changed to:', $(this).val());
                var typeValue = $(this).val();

                // Hide all sections first
                $('#drive-url-section').hide();
                $('#document-type-info').hide();
                $('#loa-info, #agreement-info, #complement-info').hide();

                if (typeValue === 'loa') {
                    console.log('Processing LOA');
                    $('#document-type-info').show();
                    $('#loa-info').show();
                    $('#is-upload').val('0');
                    $('#is-generated').val('1');
                } else if (typeValue === 'agreement') {
                    console.log('Processing Agreement');
                    $('#drive-url-section').show();
                    $('#document-type-info').show();
                    $('#agreement-info').show();
                    $('#drive-url-label').text('Agreement Template Link*');
                    $('#is-upload').val('1');
                    $('#is-generated').val('0');
                } else if (typeValue === 'complement') {
                    console.log('Processing Complement');
                    $('#drive-url-section').show();
                    $('#document-type-info').show();
                    $('#complement-info').show();
                    $('#drive-url-label').text('Document Link*');
                    $('#is-upload').val('1');
                    $('#is-generated').val('0');
                }
            });

            // Test similar for edit modal
            $('#edit-document-type').on('change', function() {
                console.log('EDIT: Document type changed to:', $(this).val());
                var typeValue = $(this).val();

                // Hide all sections first
                $('#edit-drive-url-section').hide();
                $('#edit-document-type-info').hide();
                $('#edit-loa-info, #edit-agreement-info, #edit-complement-info').hide();

                if (typeValue === 'loa') {
                    $('#edit-document-type-info').show();
                    $('#edit-loa-info').show();
                    $('#edit-is-upload').val('0');
                    $('#edit-is-generated').val('1');
                } else if (typeValue === 'agreement') {
                    $('#edit-drive-url-section').show();
                    $('#edit-document-type-info').show();
                    $('#edit-agreement-info').show();
                    $('#edit-drive-url-label').text('Agreement Template Link*');
                    $('#edit-is-upload').val('1');
                    $('#edit-is-generated').val('0');
                } else if (typeValue === 'complement') {
                    $('#edit-drive-url-section').show();
                    $('#edit-document-type-info').show();
                    $('#edit-complement-info').show();
                    $('#edit-drive-url-label').text('Document Link*');
                    $('#edit-is-upload').val('1');
                    $('#edit-is-generated').val('0');
                }
            });

            handleDocumentTypeChange(
                $('#document-type'),
                $('#drive-url-section'),
                $('#document-type-info'),
                $('#drive-url'),
                $('#is-upload'),
                $('#is-generated'),
                $('#drive-url-label'),
                $('#drive-url-help')
            );

            // Initialize handlers for edit modal
            handleDocumentTypeChange(
                $('#edit-document-type'),
                $('#edit-drive-url-section'),
                $('#edit-document-type-info'),
                $('#edit-drive-url'),
                $('#edit-is-upload'),
                $('#edit-is-generated'),
                $('#edit-drive-url-label'),
                $('#edit-drive-url-help')
            );

            // Test if elements exist
            console.log('Document type element:', $('#document-type').length);
            console.log('Drive URL section:', $('#drive-url-section').length);
            console.log('Type info section:', $('#document-type-info').length); // Initialize DataTable
            var documentTable = $('#program-documents-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true,
                autoWidth: false,
                scrollX: false,
                columnDefs: [{
                        orderable: false,
                        targets: [5] // Actions column
                    },
                    {
                        responsivePriority: 1,
                        targets: 0 // # column
                    },
                    {
                        responsivePriority: 2,
                        targets: 1 // Document Name column
                    },
                    {
                        responsivePriority: 3,
                        targets: -1 // Actions column (last)
                    }
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

                    // Debug: Check if buttons exist after DataTable draw
                    console.log('DataTable drawn. Edit buttons found:', $('.edit-document').length);
                    console.log('DataTable drawn. Delete buttons found:', $('.delete-document').length);
                }
            });

            console.log('DataTable initialized successfully'); // Form submission success handling for add document
            $('#add-document-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var formData = new FormData(this);

                // Show loading state
                var submitBtn = form.find('button[type="submit"]');
                var originalText = submitBtn.text();
                submitBtn.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#add-document-modal').modal('hide');
                        Swal.fire({
                            title: 'Success!',
                            text: 'Document has been added successfully',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        }).then(function() {
                            window.location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = 'An error occurred while saving the document';
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    },
                    complete: function() {
                        // Reset button state
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });

            // Edit form submission handling
            $('#edit-document-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var formData = new FormData(this);

                // Show loading state
                var submitBtn = form.find('button[type="submit"]');
                var originalText = submitBtn.text();
                submitBtn.prop('disabled', true).text('Updating...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#edit-document-modal').modal('hide');
                        Swal.fire({
                            title: 'Success!',
                            text: 'Document has been updated successfully',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        }).then(function() {
                            window.location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = 'An error occurred while updating the document';
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    },
                    complete: function() {
                        // Reset button state
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            }); // Enhanced Edit document button handler
            $(document).on('click', '.edit-document', function() {
                console.log('Edit button clicked!');
                var documentId = $(this).data('id');
                console.log('Document ID:', documentId);

                // Show loading in button
                var btn = $(this);
                var originalHtml = btn.html();
                btn.prop('disabled', true).html('<i class="ri-loader-4-line"></i>');

                // Fetch document data and populate the edit modal
                $.ajax({
                    url: '/documents/program-documents/get-document/' + documentId,
                    type: 'GET',
                    success: function(response) {
                        console.log('Edit response:', response);
                        if (response.success) {
                            // Populate the edit modal with document data
                            var doc = response.data;
                            $('#edit-document-name').val(doc.name);
                            $('#edit-document-type').val(doc.type);
                            $('#edit-document-desc').val(doc.desc);
                            $('#edit-drive-url').val(doc.drive_url || '');
                            $('#edit-document-visibility').val(doc.visibility);
                            $('#edit-document-status').val(doc.is_active);
                            $('#edit-document-id').val(doc.id);

                            // Set hidden fields based on document type and existing values
                            $('#edit-is-upload').val(doc.is_upload || 0);
                            $('#edit-is-generated').val(doc.is_generated || 0);

                            // Trigger change event to show/hide appropriate sections
                            $('#edit-document-type').trigger('change');

                            // Update form action
                            $('#edit-document-form').attr('action', '/documents/program-documents/update/' + doc.id);

                            // Show modal
                            $('#edit-document-modal').modal('show');
                        } else {
                            alert('Error fetching document data: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Server error');
                    },
                    complete: function() {
                        // Reset button
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            }); // Enhanced Delete confirmation with loading
            $(document).on('click', '.delete-document', function() {
                console.log('Delete button clicked!');
                var documentId = $(this).data('id');
                console.log('Document ID for deletion:', documentId);

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This document will be permanently deleted. You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                    cancelButtonClass: 'btn btn-danger w-xs mt-2',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!',
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function(result) {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait while we delete the document',
                            icon: 'info',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            willOpen: function() {
                                Swal.showLoading();
                            }
                        });

                        // Create a temporary form to submit POST request
                        var form = $('<form>', {
                            'method': 'POST',
                            'action': '/documents/program-documents/delete/' + documentId
                        });
                        // Add CSRF token if available
                        var csrfToken = $('input[name="<?= csrf_token() ?>"]').val();
                        if (csrfToken) {
                            form.append($('<input>', {
                                'type': 'hidden',
                                'name': '<?= csrf_token() ?>',
                                'value': csrfToken
                            }));
                        }

                        // Append form to body and submit
                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>

</body>

</html>