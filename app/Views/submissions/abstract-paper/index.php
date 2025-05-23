<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title' => 'Abstract Submissions')); ?>

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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Documents', 'title' => 'Abstract Submissions')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Abstract Submissions List</h5>
                                    <div class="flex-shrink-0">
                                        <button class="btn btn-primary waves-effect waves-light me-2" data-bs-toggle="modal" data-bs-target="#add-abstract-modal">
                                            <i class="ri-add-line align-middle me-1"></i> Add Abstract Submission
                                        </button>
                                    </div>
                                </div><div class="card-body">                                    <table id="abstracts-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Participant</th>
                                                <th scope="col">Institution</th>
                                                <th scope="col">Submission Date</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be loaded via AJAX -->
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

            <!-- Add Abstract Modal -->
            <div class="modal fade" id="add-abstract-modal" tabindex="-1" aria-labelledby="addAbstractModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addAbstractModalLabel">Add New Abstract Submission</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>                        <div class="modal-body">
                            <form id="add-abstract-form">                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="participant_id" class="form-label">Participant</label>
                                        <select name="participant_id" id="participant_id" class="form-select" required>
                                            <option value="">Select Participant</option>
                                            <!-- Participants will be loaded via AJAX -->
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Status</label>                                        <select name="status" id="status" class="form-select">
                                            <option value="draft">Draft</option>
                                            <option value="submitted">Submitted</option>
                                            <option value="under_review">Under Review</option>
                                            <option value="accepted">Accepted</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Abstract Version -->
                                <div class="abstract-version-container">
                                    <h5 class="mb-3">Abstract Version</h5>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" class="form-control" id="title" name="title" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="content" class="form-label">Content</label>
                                            <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="keywords" class="form-label">Keywords</label>
                                            <input type="text" class="form-control" id="keywords" name="keywords" placeholder="Comma separated keywords">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Abstract Authors -->
                                <div class="abstract-authors-container">
                                    <h5 class="mb-3">Abstract Authors</h5>
                                    <div class="authors-list">
                                        <div class="author-item mb-3 p-3 border rounded">
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" class="form-control" name="author_name[]" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Institution</label>
                                                    <input type="text" class="form-control" name="author_institution[]">
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" class="form-control" name="author_email[]" required>
                                                </div>
                                                <div class="col-md-6 d-flex align-items-end">
                                                    <div class="form-check pt-2">
                                                        <input class="form-check-input" type="checkbox" name="is_participant[]" value="1">
                                                        <label class="form-check-label">Is Registered Participant</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end mb-3">
                                        <button type="button" class="btn btn-sm btn-secondary add-author-btn">
                                            <i class="ri-add-line align-middle"></i> Add Another Author
                                        </button>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Add Abstract</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View Abstract Modal -->
            <div class="modal fade" id="view-abstract-modal" tabindex="-1" aria-labelledby="viewAbstractModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewAbstractModalLabel">Abstract Submission Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-loading d-none">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="loading-text">Loading...</div>
                                </div>
                            </div>                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="fw-medium">Participant:</label>
                                        <p class="view-abstract-participant"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="fw-medium">Institution:</label>
                                        <p class="view-abstract-institution"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="fw-medium">Submission Date:</label>
                                        <p class="view-abstract-date"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="fw-medium">Status:</label>
                                        <div class="view-abstract-status"></div>
                                    </div>                                </div>
                            </div>
                            
                            <!-- Abstract Version Section -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">Abstract Content</h5>
                                    <div class="mb-3 mt-3">
                                        <label class="fw-medium">Version:</label>
                                        <select class="form-select view-version-select">
                                            <option value="">Loading versions...</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-medium">Title:</label>
                                        <p class="view-abstract-title"></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-medium">Content:</label>
                                        <div class="view-abstract-content p-3 border rounded bg-light"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-medium">Keywords:</label>
                                        <p class="view-abstract-keywords"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Abstract Authors Section -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">Authors</h5>
                                    <div class="view-authors-list mt-3">
                                        <!-- Authors will be loaded dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-success edit-abstract-btn">Edit</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Abstract Modal -->
            <div class="modal fade" id="edit-abstract-modal" tabindex="-1" aria-labelledby="editAbstractModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editAbstractModalLabel">Edit Abstract Submission</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-loading d-none">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="loading-text">Loading...</div>
                                </div>
                            </div>                            <form id="edit-abstract-form">
                                <input type="hidden" id="edit_abstract_id" name="id">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="edit_participant_id" class="form-label">Participant</label>
                                        <select name="participant_id" id="edit_participant_id" class="form-select">
                                            <option value="">Select Participant</option>
                                            <!-- Participants will be loaded via AJAX -->
                                        </select>
                                    </div>
                                </div>                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="edit_status" class="form-label">Status</label>                                        <select name="status" id="edit_status" class="form-select">
                                            <option value="draft">Draft</option>
                                            <option value="submitted">Submitted</option>
                                            <option value="under_review">Under Review</option>
                                            <option value="accepted">Accepted</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Abstract Version -->
                                <div class="abstract-version-container">
                                    <h5 class="mb-3">Abstract Version</h5>
                                    <div class="version-select-container mb-3">
                                        <label for="version_select" class="form-label">Select Version</label>
                                        <select class="form-select" id="version_select">
                                            <option value="">Loading versions...</option>
                                        </select>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="edit_title" class="form-label">Title</label>
                                            <input type="text" class="form-control" id="edit_title" name="title" required>
                                            <input type="hidden" id="edit_version_id" name="version_id">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="edit_content" class="form-label">Content</label>
                                            <textarea class="form-control" id="edit_content" name="content" rows="5" required></textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="edit_keywords" class="form-label">Keywords</label>
                                            <input type="text" class="form-control" id="edit_keywords" name="keywords" placeholder="Comma separated keywords">
                                        </div>
                                    </div>
                                    <div class="text-end mb-3">
                                        <button type="button" class="btn btn-sm btn-primary add-new-version-btn">
                                            <i class="ri-add-line align-middle"></i> Create New Version
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Abstract Authors -->
                                <div class="abstract-authors-container">
                                    <h5 class="mb-3">Abstract Authors</h5>
                                    <div class="edit-authors-list">
                                        <!-- Authors will be loaded dynamically -->
                                    </div>
                                    <div class="text-end mb-3">
                                        <button type="button" class="btn btn-sm btn-secondary edit-add-author-btn">
                                            <i class="ri-add-line align-middle"></i> Add Another Author
                                        </button>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update Abstract</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

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
        $(document).ready(function() {
            // Initialize DataTable
            var abstractsTable = $('#abstracts-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "/submissions/abstracts-papers/getAbstractsByProgram",
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: "participant_name"
                    },
                    {
                        data: "institution"
                    },
                    {
                        data: "created_at",
                        render: function(data) {
                            return data ? new Date(data).toLocaleDateString() : 'N/A';
                        }
                    },
                    {                        data: "status",
                        render: function(data) {
                            let statusText = data ? data.replace('_', ' ') : 'Unknown';
                            let badgeClass = 'bg-secondary';

                            if (data === 'draft') {
                                statusText = 'Draft';
                                badgeClass = 'bg-secondary';
                            } else if (data === 'submitted') {
                                statusText = 'Submitted';
                                badgeClass = 'bg-primary';
                            } else if (data === 'under_review') {
                                statusText = 'Under Review';
                                badgeClass = 'bg-info';
                            } else if (data === 'accepted') {
                                statusText = 'Accepted';
                                badgeClass = 'bg-success';
                            } else if (data === 'rejected') {
                                statusText = 'Rejected';
                                badgeClass = 'bg-danger';
                            }

                            return '<span class="badge ' + badgeClass + '">' + statusText + '</span>';
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return '<div class="d-flex gap-2">' +
                                '<div class="view"><button class="btn btn-sm btn-info view-abstract" data-id="' + data.id + '" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details"><i class="ri-eye-fill"></i></button></div>' +
                                '<div class="edit"><button class="btn btn-sm btn-success edit-abstract" data-id="' + data.id + '" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="ri-pencil-fill"></i></button></div>' +
                                '<div class="delete"><button class="btn btn-sm btn-danger delete-abstract" data-id="' + data.id + '" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"><i class="ri-delete-bin-fill"></i></button></div>' +
                                '</div>';
                        }
                    }
                ],
                responsive: true
            });

            // Load participants for add modal
            $('#add-abstract-modal').on('show.bs.modal', function() {
                $.ajax({
                    url: '/api/participants',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.data) {
                            let participants = response.data;
                            let options = '<option value="">Select Participant</option>';

                            participants.forEach(function(participant) {
                                options += '<option value="' + participant.id + '">' + participant.full_name + ' (' + participant.institution + ')</option>';
                            });

                            $('#participant_id').html(options);
                        }
                    }
                });
            });

            // Load participants for edit modal
            $('#edit-abstract-modal').on('show.bs.modal', function() {
                $.ajax({
                    url: '/api/participants',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.data) {
                            let participants = response.data;
                            let options = '<option value="">Select Participant</option>';

                            participants.forEach(function(participant) {
                                options += '<option value="' + participant.id + '">' + participant.full_name + ' (' + participant.institution + ')</option>';
                            });

                            $('#edit_participant_id').html(options);
                        }
                    }
                });
            });            // View abstract
            $(document).on('click', '.view-abstract', function() {
                let abstractId = $(this).data('id');

                $('#view-abstract-modal').modal('show');
                $('.modal-loading').removeClass('d-none');

                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractData/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('.modal-loading').addClass('d-none');

                        if (response && response.success) {
                            let abstract = response.data;
                            $('.view-abstract-participant').text(abstract.participant_name);
                            $('.view-abstract-institution').text(abstract.institution);
                            $('.view-abstract-date').text(abstract.created_at ? new Date(abstract.created_at).toLocaleDateString() : 'N/A');                            
                            // Set status
                            let statusText = abstract.status ? abstract.status.replace('_', ' ') : 'Unknown';
                            let badgeClass = 'bg-secondary';

                            if (abstract.status === 'draft') {
                                statusText = 'Draft';
                                badgeClass = 'bg-secondary';
                            } else if (abstract.status === 'submitted') {
                                statusText = 'Submitted';
                                badgeClass = 'bg-primary';
                            } else if (abstract.status === 'under_review') {
                                statusText = 'Under Review';
                                badgeClass = 'bg-info';
                            } else if (abstract.status === 'accepted') {
                                statusText = 'Accepted';
                                badgeClass = 'bg-success';
                            } else if (abstract.status === 'rejected') {
                                statusText = 'Rejected';
                                badgeClass = 'bg-danger';
                            }

                            $('.view-abstract-status').html('<span class="badge ' + badgeClass + '">' + statusText + '</span>');

                            // Set edit button data
                            $('.edit-abstract-btn').data('id', abstract.id);
                            
                            // Load abstract versions
                            loadAbstractVersions(abstract.id, 'view');
                            
                            // Load abstract authors
                            loadAbstractAuthors(abstract.id, 'view');
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Failed to load abstract data',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        $('.modal-loading').addClass('d-none');

                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while loading the abstract',
                            icon: 'error'
                        });
                    }
                });
            });

            // Edit button in view modal
            $(document).on('click', '.edit-abstract-btn', function() {
                let abstractId = $(this).data('id');

                $('#view-abstract-modal').modal('hide');
                $('#edit-abstract-modal').modal('show');
                $('.modal-loading').removeClass('d-none');

                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractData/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('.modal-loading').addClass('d-none');

                        if (response && response.success) {
                            let abstract = response.data;
                            $('#edit_abstract_id').val(abstract.id);
                            $('#edit_participant_id').val(abstract.primary_participant_id);
                            $('#edit_status').val(abstract.status);

                            // Update form action
                            $('#edit-abstract-form').attr('action', '/documents/abstracts-papers/update/' + abstract.id);
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Failed to load abstract data',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        $('.modal-loading').addClass('d-none');

                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while loading the abstract',
                            icon: 'error'
                        });
                    }
                });
            });            // Edit abstract
            $(document).on('click', '.edit-abstract', function() {
                let abstractId = $(this).data('id');
                
                $('#edit-abstract-modal').modal('show');
                $('.modal-loading').removeClass('d-none');
                
                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractData/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('.modal-loading').addClass('d-none');
                        
                        if (response && response.success) {
                            let abstract = response.data;
                            $('#edit_abstract_id').val(abstract.id);
                            $('#edit_participant_id').val(abstract.primary_participant_id);
                            $('#edit_status').val(abstract.status);
                            
                            // Load abstract versions
                            loadAbstractVersions(abstract.id, 'edit');
                            
                            // Load abstract authors
                            loadAbstractAuthors(abstract.id, 'edit');
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Failed to load abstract data',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        $('.modal-loading').addClass('d-none');
                        
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while loading the abstract',
                            icon: 'error'
                        });
                    }
                });
            });
              // Edit abstract form submission
            $('#edit-abstract-form').on('submit', function(e) {
                e.preventDefault();
                
                let abstractId = $('#edit_abstract_id').val();
                let formData = $(this).serialize();
                
                $.ajax({
                    url: '/submissions/abstracts-papers/update/' + abstractId,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success',
                                text: response.message,
                                icon: 'success'
                            }).then(() => {
                                $('#edit-abstract-modal').modal('hide');
                                abstractsTable.ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message,
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while updating the abstract',
                            icon: 'error'
                        });
                    }
                });
            });

            // Delete abstract
            $(document).on('click', '.delete-abstract', function() {
                let abstractId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You will not be able to recover this abstract!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!',
                    confirmButtonClass: 'btn btn-danger me-2',
                    cancelButtonClass: 'btn btn-light',
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: '/submissions/abstracts-papers/delete/' + abstractId,
                            type: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonClass: 'btn btn-primary',
                                        buttonsStyling: false
                                    });
                                    
                                    // Reload the table
                                    abstractsTable.ajax.reload();
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.message,
                                        icon: 'error',
                                        confirmButtonClass: 'btn btn-primary',
                                        buttonsStyling: false
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'An error occurred while deleting the abstract',
                                    icon: 'error',
                                    confirmButtonClass: 'btn btn-primary',
                                    buttonsStyling: false
                                });
                            }
                        });
                    }
                });
            });

            // Add abstract form submission
            $('#add-abstract-form').on('submit', function(e) {
                e.preventDefault();
                
                let formData = $(this).serialize();
                
                $.ajax({
                    url: '/submissions/abstracts-papers/store',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success',
                                text: response.message,
                                icon: 'success'
                            }).then(() => {
                                $('#add-abstract-modal').modal('hide');
                                $('#add-abstract-form')[0].reset();
                                abstractsTable.ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message,
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while submitting the form',
                            icon: 'error'
                        });
                    }
                });
            });
            
            // Functions to handle abstract versions
            function loadAbstractVersions(abstractId, mode) {
                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractVersions/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            let versions = response.data;
                            if (versions.length > 0) {
                                let options = '';
                                versions.forEach(function(version) {
                                    options += '<option value="' + version.id + '">Version ' + version.version_number + ' (' + new Date(version.created_at).toLocaleDateString() + ')</option>';
                                });
                                
                                if (mode === 'view') {
                                    $('.view-version-select').html(options);
                                    // Load the first version by default
                                    displayVersionDetails(versions[0], 'view');
                                    
                                    // Handle version change
                                    $('.view-version-select').on('change', function() {
                                        let versionId = $(this).val();
                                        let selectedVersion = versions.find(v => v.id == versionId);
                                        if (selectedVersion) {
                                            displayVersionDetails(selectedVersion, 'view');
                                        }
                                    });
                                } else if (mode === 'edit') {
                                    $('#version_select').html(options);
                                    // Load the first version by default
                                    displayVersionDetails(versions[0], 'edit');
                                    
                                    // Handle version change
                                    $('#version_select').on('change', function() {
                                        let versionId = $(this).val();
                                        let selectedVersion = versions.find(v => v.id == versionId);
                                        if (selectedVersion) {
                                            displayVersionDetails(selectedVersion, 'edit');
                                        }
                                    });
                                }
                            } else {
                                if (mode === 'view') {
                                    $('.view-version-select').html('<option value="">No versions available</option>');
                                } else if (mode === 'edit') {
                                    $('#version_select').html('<option value="">No versions available</option>');
                                }
                            }
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Failed to load abstract versions',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while loading abstract versions',
                            icon: 'error'
                        });
                    }
                });
            }
            
            function displayVersionDetails(version, mode) {
                if (mode === 'view') {
                    $('.view-abstract-title').text(version.title);
                    $('.view-abstract-content').html(version.content);
                    $('.view-abstract-keywords').text(version.keywords || 'None');
                } else if (mode === 'edit') {
                    $('#edit_title').val(version.title);
                    $('#edit_content').val(version.content);
                    $('#edit_keywords').val(version.keywords);
                    $('#edit_version_id').val(version.id);
                }
            }
            
            // Functions to handle abstract authors
            function loadAbstractAuthors(abstractId, mode) {
                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractAuthors/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            let authors = response.data;
                            
                            if (mode === 'view') {
                                let authorsHtml = '';
                                
                                if (authors.length > 0) {
                                    authors.forEach(function(author, index) {
                                        authorsHtml += '<div class="author-item p-3 border rounded mb-3">';
                                        authorsHtml += '<div class="row">';
                                        authorsHtml += '<div class="col-md-6"><strong>Name:</strong> ' + author.full_name + '</div>';
                                        authorsHtml += '<div class="col-md-6"><strong>Institution:</strong> ' + (author.institution || 'N/A') + '</div>';
                                        authorsHtml += '</div>';
                                        authorsHtml += '<div class="row mt-2">';
                                        authorsHtml += '<div class="col-md-6"><strong>Email:</strong> ' + author.email + '</div>';
                                        authorsHtml += '<div class="col-md-6"><strong>Registered Participant:</strong> ' + (author.is_participant ? 'Yes' : 'No') + '</div>';
                                        authorsHtml += '</div>';
                                        authorsHtml += '</div>';
                                    });
                                } else {
                                    authorsHtml = '<p>No authors available for this abstract.</p>';
                                }
                                
                                $('.view-authors-list').html(authorsHtml);
                            } else if (mode === 'edit') {
                                let authorsHtml = '';
                                
                                if (authors.length > 0) {
                                    authors.forEach(function(author, index) {
                                        authorsHtml += '<div class="author-item mb-3 p-3 border rounded">';
                                        authorsHtml += '<input type="hidden" name="author_id[]" value="' + author.id + '">';
                                        authorsHtml += '<div class="row mb-2">';
                                        authorsHtml += '<div class="col-md-6">';
                                        authorsHtml += '<label class="form-label">Name</label>';
                                        authorsHtml += '<input type="text" class="form-control" name="author_name[]" value="' + author.full_name + '" required>';
                                        authorsHtml += '</div>';
                                        authorsHtml += '<div class="col-md-6">';
                                        authorsHtml += '<label class="form-label">Institution</label>';
                                        authorsHtml += '<input type="text" class="form-control" name="author_institution[]" value="' + (author.institution || '') + '">';
                                        authorsHtml += '</div>';
                                        authorsHtml += '</div>';
                                        authorsHtml += '<div class="row mb-2">';
                                        authorsHtml += '<div class="col-md-6">';
                                        authorsHtml += '<label class="form-label">Email</label>';
                                        authorsHtml += '<input type="email" class="form-control" name="author_email[]" value="' + author.email + '" required>';
                                        authorsHtml += '</div>';
                                        authorsHtml += '<div class="col-md-6 d-flex align-items-end">';
                                        authorsHtml += '<div class="form-check pt-2">';
                                        authorsHtml += '<input class="form-check-input" type="checkbox" name="is_participant[]" value="1" ' + (author.is_participant ? 'checked' : '') + '>';
                                        authorsHtml += '<label class="form-check-label">Is Registered Participant</label>';
                                        authorsHtml += '</div>';
                                        
                                        if (index > 0) {
                                            authorsHtml += '<button type="button" class="btn btn-sm btn-danger ms-auto remove-author-btn" data-author-id="' + author.id + '">';
                                            authorsHtml += '<i class="ri-delete-bin-line"></i>';
                                            authorsHtml += '</button>';
                                        }
                                        
                                        authorsHtml += '</div>';
                                        authorsHtml += '</div>';
                                        authorsHtml += '</div>';
                                    });
                                } else {
                                    // Add empty author form if no authors
                                    authorsHtml += getEmptyAuthorTemplate();
                                }
                                
                                $('.edit-authors-list').html(authorsHtml);
                            }
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Failed to load abstract authors',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while loading abstract authors',
                            icon: 'error'
                        });
                    }
                });
            }
            
            // Template for empty author form
            function getEmptyAuthorTemplate() {
                let template = '';
                template += '<div class="author-item mb-3 p-3 border rounded">';
                template += '<div class="row mb-2">';
                template += '<div class="col-md-6">';
                template += '<label class="form-label">Name</label>';
                template += '<input type="text" class="form-control" name="author_name[]" required>';
                template += '</div>';
                template += '<div class="col-md-6">';
                template += '<label class="form-label">Institution</label>';
                template += '<input type="text" class="form-control" name="author_institution[]">';
                template += '</div>';
                template += '</div>';
                template += '<div class="row mb-2">';
                template += '<div class="col-md-6">';
                template += '<label class="form-label">Email</label>';
                template += '<input type="email" class="form-control" name="author_email[]" required>';
                template += '</div>';
                template += '<div class="col-md-6 d-flex align-items-end">';
                template += '<div class="form-check pt-2">';
                template += '<input class="form-check-input" type="checkbox" name="is_participant[]" value="1">';
                template += '<label class="form-check-label">Is Registered Participant</label>';
                template += '</div>';
                template += '</div>';
                template += '</div>';
                template += '</div>';
                return template;
            }
            
            // Add a new author in add modal
            $(document).on('click', '.add-author-btn', function() {
                $('.authors-list').append(getEmptyAuthorTemplate());
            });
            
            // Add a new author in edit modal
            $(document).on('click', '.edit-add-author-btn', function() {
                $('.edit-authors-list').append(getEmptyAuthorTemplate());
            });
            
            // Remove author
            $(document).on('click', '.remove-author-btn', function() {
                let authorId = $(this).data('author-id');
                let authorItem = $(this).closest('.author-item');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You will remove this author from the abstract',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it!',
                    cancelButtonText: 'No, cancel!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (authorId) {
                            $.ajax({
                                url: '/submissions/abstracts-papers/removeAuthor/' + authorId,
                                type: 'POST',
                                dataType: 'json',
                                success: function(response) {
                                    if (response.success) {
                                        authorItem.remove();
                                        Swal.fire('Removed!', 'Author has been removed.', 'success');
                                    } else {
                                        Swal.fire('Error!', response.message || 'Failed to remove author', 'error');
                                    }
                                },
                                error: function() {
                                    Swal.fire('Error!', 'An error occurred while removing the author', 'error');
                                }
                            });
                        } else {
                            authorItem.remove();
                        }
                    }
                });
            });
            
            // Create new version button
            $(document).on('click', '.add-new-version-btn', function() {
                let abstractId = $('#edit_abstract_id').val();
                
                Swal.fire({
                    title: 'Create New Version',
                    text: 'This will create a new version of your abstract. Continue?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, create new version',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create a new version based on current data
                        let title = $('#edit_title').val();
                        let content = $('#edit_content').val();
                        let keywords = $('#edit_keywords').val();
                        
                        $.ajax({
                            url: '/submissions/abstracts-papers/createNewVersion',
                            type: 'POST',
                            data: {
                                abstract_id: abstractId,
                                title: title,
                                content: content,
                                keywords: keywords
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Success!', 'New version created successfully', 'success');
                                    // Reload versions
                                    loadAbstractVersions(abstractId, 'edit');
                                } else {
                                    Swal.fire('Error!', response.message || 'Failed to create new version', 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error!', 'An error occurred while creating new version', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>