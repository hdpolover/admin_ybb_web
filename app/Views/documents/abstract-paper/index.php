<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title'=>'Abstract Papers')); ?>

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

                    <?php echo view('partials/page-title', array('pagetitle'=>'Documents', 'title'=>'Abstract Papers')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Abstract Papers List</h5>
                                    <div class="flex-shrink-0">
                                        <button class="btn btn-primary waves-effect waves-light me-2" data-bs-toggle="modal" data-bs-target="#add-abstract-modal">
                                            <i class="ri-add-line align-middle me-1"></i> Add Abstract Paper
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="program-select" class="form-label">Select Program</label>
                                                <select class="form-select" id="program-select">
                                                    <option value="">All Programs</option>
                                                    <!-- Programs will be loaded via AJAX -->
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <table id="abstracts-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Title</th>
                                                <th scope="col">Participant</th>
                                                <th scope="col">Institution</th>
                                                <th scope="col">Submitted Date</th>
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
                            <h5 class="modal-title" id="addAbstractModalLabel">Add New Abstract Paper</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="add-abstract-form" action="/documents/abstracts-papers/store" method="post">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="participant_id" class="form-label">Participant</label>
                                        <select name="participant_id" id="participant_id" class="form-select" required>
                                            <option value="">Select Participant</option>
                                            <!-- Participants will be loaded via AJAX -->
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="title" class="form-label">Abstract Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="content" class="form-label">Abstract Content</label>
                                        <textarea class="form-control" id="content" name="content" rows="6" required></textarea>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Status</label>
                                        <select name="status" id="status" class="form-select">
                                            <option value="0">Pending</option>
                                            <option value="1">Under Review</option>
                                            <option value="2">Accepted</option>
                                            <option value="3">Rejected</option>
                                        </select>
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
                            <h5 class="modal-title" id="viewAbstractModalLabel">Abstract Paper Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-loading d-none">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="loading-text">Loading...</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <h5 class="view-abstract-title"></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
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
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="fw-medium">Abstract Content:</label>
                                        <div class="view-abstract-content border rounded p-3 bg-light"></div>
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
                            <h5 class="modal-title" id="editAbstractModalLabel">Edit Abstract Paper</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-loading d-none">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="loading-text">Loading...</div>
                                </div>
                            </div>
                            <form id="edit-abstract-form" action="/documents/abstracts-papers/update" method="post">
                                <input type="hidden" name="abstract_id" id="edit_abstract_id">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="edit_title" class="form-label">Abstract Title</label>
                                        <input type="text" class="form-control" id="edit_title" name="title" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="edit_content" class="form-label">Abstract Content</label>
                                        <textarea class="form-control" id="edit_content" name="content" rows="6" required></textarea>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="edit_status" class="form-label">Status</label>
                                        <select name="status" id="edit_status" class="form-select">
                                            <option value="0">Pending</option>
                                            <option value="1">Under Review</option>
                                            <option value="2">Accepted</option>
                                            <option value="3">Rejected</option>
                                        </select>
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

    <!-- Required datatable js -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Responsive examples -->
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap.min.js"></script>
    <!-- Buttons examples -->
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <script>
        $(document).ready(function () {
            // Initialize DataTable
            var abstractsTable = $('#abstracts-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "/documents/abstracts-papers/getAbstractsByProgram",
                    dataSrc: function (json) {
                        return json.data;
                    }
                },
                columns: [
                    { 
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { data: "title" },
                    { data: "participant_name" },
                    { data: "institution" },
                    { 
                        data: "submitted_at",
                        render: function (data) {
                            return data ? new Date(data).toLocaleDateString() : 'N/A';
                        }
                    },
                    { 
                        data: "status",
                        render: function (data) {
                            let statusText = 'Pending';
                            let badgeClass = 'bg-warning';
                            
                            if (data == 1) {
                                statusText = 'Under Review';
                                badgeClass = 'bg-info';
                            } else if (data == 2) {
                                statusText = 'Accepted';
                                badgeClass = 'bg-success';
                            } else if (data == 3) {
                                statusText = 'Rejected';
                                badgeClass = 'bg-danger';
                            }
                            
                            return '<span class="badge ' + badgeClass + '">' + statusText + '</span>';
                        }
                    },
                    { 
                        data: null,
                        render: function (data) {
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
            
            // Load programs for filter
            $.ajax({
                url: '/api/programs',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.data) {
                        let programs = response.data;
                        let options = '<option value="">All Programs</option>';
                        
                        programs.forEach(function(program) {
                            options += '<option value="' + program.id + '">' + program.name + '</option>';
                        });
                        
                        $('#program-select').html(options);
                    }
                }
            });
            
            // Program filter change
            $('#program-select').on('change', function() {
                let programId = $(this).val();
                let url = '/documents/abstracts-papers/getAbstractsByProgram';
                
                if (programId) {
                    url += '/' + programId;
                }
                
                abstractsTable.ajax.url(url).load();
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
            
            // View abstract
            $(document).on('click', '.view-abstract', function() {
                let abstractId = $(this).data('id');
                
                $('#view-abstract-modal').modal('show');
                $('.modal-loading').removeClass('d-none');
                
                $.ajax({
                    url: '/documents/abstracts-papers/getAbstractData/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('.modal-loading').addClass('d-none');
                        
                        if (response && response.success) {
                            let abstract = response.data;
                            $('.view-abstract-title').text(abstract.title);
                            $('.view-abstract-participant').text(abstract.participant_name);
                            $('.view-abstract-institution').text(abstract.institution);
                            $('.view-abstract-date').text(abstract.submitted_at ? new Date(abstract.submitted_at).toLocaleDateString() : 'N/A');
                            
                            // Set status
                            let statusText = 'Pending';
                            let badgeClass = 'bg-warning';
                            
                            if (abstract.status == 1) {
                                statusText = 'Under Review';
                                badgeClass = 'bg-info';
                            } else if (abstract.status == 2) {
                                statusText = 'Accepted';
                                badgeClass = 'bg-success';
                            } else if (abstract.status == 3) {
                                statusText = 'Rejected';
                                badgeClass = 'bg-danger';
                            }
                            
                            $('.view-abstract-status').html('<span class="badge ' + badgeClass + '">' + statusText + '</span>');
                            $('.view-abstract-content').html(abstract.content);
                            
                            // Set edit button data
                            $('.edit-abstract-btn').data('id', abstract.id);
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
                    url: '/documents/abstracts-papers/getAbstractData/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('.modal-loading').addClass('d-none');
                        
                        if (response && response.success) {
                            let abstract = response.data;
                            $('#edit_abstract_id').val(abstract.id);
                            $('#edit_title').val(abstract.title);
                            $('#edit_content').val(abstract.content);
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
            });
            
            // Edit abstract directly
            $(document).on('click', '.edit-abstract', function() {
                let abstractId = $(this).data('id');
                
                $('#edit-abstract-modal').modal('show');
                $('.modal-loading').removeClass('d-none');
                
                $.ajax({
                    url: '/documents/abstracts-papers/getAbstractData/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('.modal-loading').addClass('d-none');
                        
                        if (response && response.success) {
                            let abstract = response.data;
                            $('#edit_abstract_id').val(abstract.id);
                            $('#edit_title').val(abstract.title);
                            $('#edit_content').val(abstract.content);
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
            });
            
            // Delete abstract
            $(document).on('click', '.delete-abstract', function() {
                let abstractId = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You are about to delete this abstract paper. This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '/documents/abstracts-papers/delete/' + abstractId;
                    }
                });
            });
        });
    </script>
</body>

</html>