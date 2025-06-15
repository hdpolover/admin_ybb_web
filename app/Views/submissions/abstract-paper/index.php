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

        /* Enhanced table styling */
        .table th {
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .table td {
            vertical-align: middle;
        }

        /* Action buttons styling */
        .btn-group>.btn {
            margin-right: 2px;
        }

        /* Feedback item styling */
        .feedback-item {
            background-color: #f8f9fa;
        }

        .feedback-item:hover {
            background-color: #e9ecef;
        }

        /* Author item styling */
        .author-item {
            background-color: #f8f9fa;
        }

        /* Version accordion styling */
        .accordion-button:not(.collapsed) {
            background-color: #e7f3ff;
            color: #0c63e4;
        }

        /* Badge styling */
        .badge-sm {
            font-size: 0.75em;
        }

        /* Content area styling */
        .feedback-abstract-content,
        .view-abstract-content {
            max-height: 300px;
            overflow-y: auto;
            line-height: 1.6;
        }

        /* Modal size adjustments */
        .modal-xl {
            max-width: 1200px;
        }

        /* Status badge positioning */
        .status-badge {
            display: inline-flex;
            align-items: center;
        }

        /* Responsive table improvements */
        @media (max-width: 768px) {
            .d-flex.gap-2 {
                flex-direction: column;
                gap: 0.5rem !important;
            }

            .btn-group {
                width: 100%;
            }

            .btn-group>.btn {
                flex: 1;
            }
        }

        /* Tooltip improvements */
        .tooltip-inner {
            max-width: 300px;
        }

        /* Form validation styling */
        .is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: block;
        }

        /* Enhanced feedback section */
        .feedback-section {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
            margin-top: 1rem;
        }

        /* Version content styling */
        .version-content {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
            margin: 0.5rem 0;
        }

        /* Quick action buttons */
        .quick-action-btn {
            min-width: 80px;
        }

        /* Abstract title in feedback modal */
        .feedback-abstract-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #495057;
        }

        /* Loading states */
        .btn.loading {
            position: relative;
            color: transparent;
        }

        .btn.loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: button-loading-spinner 1s ease infinite;
        }

        /* Subtheme filter styling */
        .subtheme-filter-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        #subthemeFilter {
            min-width: 200px;
            font-size: 0.875rem;
        }

        .subtheme-filter-container .form-label {
            white-space: nowrap;
            margin-bottom: 0;
            font-weight: 500;
            color: #6c757d;
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
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Abstract Submissions List (Reviewer View)</h5>
                                    <div class="flex-shrink-0">
                                        <div class="subtheme-filter-container">
                                            <label for="subthemeFilter" class="form-label">Filter by Subtheme:</label>
                                            <select id="subthemeFilter" class="form-select">
                                                <option value="">All Assigned Subthemes</option>
                                                <!-- Options will be loaded via AJAX -->
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="abstracts-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Title</th>
                                                <th scope="col">Topic</th>
                                                <th scope="col">Authors</th>
                                                <th scope="col">Submission Date</th>
                                                <th scope="col">Last Updated</th>
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
            <!-- End Page-content --> <!-- View Abstract Modal -->
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
                                        <label class="fw-medium">Topic:</label>
                                        <p class="view-abstract-topic"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="fw-medium">Submission Date:</label>
                                        <p class="view-abstract-date"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="fw-medium">Status:</label>
                                        <div class="view-abstract-status"></div>
                                    </div>
                                </div>
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
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviewer Feedback Modal -->
            <div class="modal fade" id="feedback-modal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="feedbackModalLabel">Review Abstract</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-loading d-none">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="loading-text">Loading...</div>
                                </div>
                            </div>

                            <!-- Abstract Details Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Abstract Information</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-medium">Participant:</label>
                                                <p class="feedback-abstract-participant"></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-medium">Topic:</label>
                                                <p class="feedback-abstract-topic"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-medium">Status:</label>
                                                <div class="feedback-abstract-status"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-medium">Submission Date:</label>
                                                <p class="feedback-abstract-date"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Version Selection -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Select Version to Review</h5>
                                    <div class="mb-3">
                                        <label for="feedback-version-select" class="form-label">Version:</label>
                                        <select class="form-select" id="feedback-version-select">
                                            <option value="">Loading versions...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Abstract Version Content -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Abstract Content</h5>
                                    <div class="mb-3">
                                        <label class="fw-medium">Title:</label>
                                        <p class="feedback-abstract-title bg-light p-3 rounded"></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-medium">Content:</label>
                                        <div class="feedback-abstract-content bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-medium">Keywords:</label>
                                        <p class="feedback-abstract-keywords bg-light p-3 rounded"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Authors -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Authors</h5>
                                    <div class="feedback-authors-list">
                                        <!-- Authors will be loaded dynamically -->
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Feedback -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Previous Feedback</h5>
                                    <div class="existing-feedback-list">
                                        <!-- Existing feedback will be loaded dynamically -->
                                    </div>
                                </div>
                            </div>

                            <!-- Add New Feedback -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Add Your Feedback</h5>
                                    <form id="feedback-form">
                                        <input type="hidden" id="feedback_abstract_id" name="abstract_id">
                                        <input type="hidden" id="feedback_version_id" name="abstract_version_id">
                                        <div class="mb-3">
                                            <label for="feedback_text" class="form-label">Your Feedback:</label>
                                            <textarea class="form-control" id="feedback_text" name="feedback" rows="5" placeholder="Enter your detailed feedback here..." required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="feedback_recommendation" class="form-label">Recommendation:</label>
                                            <select class="form-select" id="feedback_recommendation" name="recommendation" required>
                                                <option value="">Select Recommendation</option>
                                                <option value="accept">Accept</option>
                                                <option value="minor_revision">Minor Revision Required</option>
                                                <option value="major_revision">Major Revision Required</option>
                                                <option value="reject">Reject</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="submit-feedback-btn">Submit Feedback</button>
                            <div class="dropdown">
                                <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Quick Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item quick-action" href="#" data-action="accept">Accept Abstract</a></li>
                                    <li><a class="dropdown-item quick-action" href="#" data-action="reject">Reject Abstract</a></li>
                                    <li><a class="dropdown-item quick-action" href="#" data-action="request_revision">Request Revision</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Abstract Details Modal (Enhanced) -->
            <div class="modal fade" id="abstract-details-modal" tabindex="-1" aria-labelledby="abstractDetailsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="abstractDetailsModalLabel">Abstract Details & History</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-loading d-none">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="loading-text">Loading...</div>
                                </div>
                            </div>

                            <!-- Abstract Basic Info -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-medium">Participant:</label>
                                                <p class="details-abstract-participant"></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-medium">Institution:</label>
                                                <p class="details-abstract-institution"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-medium">Topic:</label>
                                                <p class="details-abstract-topic"></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-medium">Current Status:</label>
                                                <div class="details-abstract-status"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-medium">Submission Date:</label>
                                                <p class="details-abstract-date"></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-medium">Last Updated:</label>
                                                <p class="details-abstract-updated"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Abstract Versions -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Abstract Versions</h5>
                                    <div class="versions-accordion" id="versionsAccordion">
                                        <!-- Versions will be loaded dynamically -->
                                    </div>
                                </div>
                            </div>

                            <!-- Authors -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Authors</h5>
                                    <div class="details-authors-list">
                                        <!-- Authors will be loaded dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-info open-feedback-btn">Review Abstract</button>
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
            // Enable debugging for DataTables
            if (typeof console !== 'undefined') {
                console.log('Initializing DataTable for abstracts...');
            }

            // Load reviewer's assigned subthemes for filtering
            loadReviewerSubthemes();

            // Initialize DataTable
            var abstractsTable = $('#abstracts-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "/submissions/abstracts-papers/getAbstractsByProgram",
                    dataSrc: function(json) {
                        // Filter out abstracts with 'draft' status
                        return json.data.filter(function(item) {
                            return item.status !== 'draft';
                        });
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTable Ajax Error:', error, code, xhr.responseText);
                        Swal.fire({
                            title: 'Error Loading Data',
                            text: 'Failed to load abstracts. Please check the console for details.',
                            icon: 'error'
                        });
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: "title",
                        render: function(data, type, row) {
                            return data ? data : 'No title available';
                        }
                    },
                    {
                        data: "topic_name",
                        render: function(data, type, row) {
                            return data ? data : 'No topic selected';
                        }
                    },
                    {
                        data: "authors_list",
                        render: function(data, type, row) {
                            let authorsText = '';
                            if (data && data.length > 0) {
                                let displayAuthors = data.slice(0, 2);
                                authorsText = displayAuthors.join(', ');
                                if (data.length > 2) {
                                    authorsText += ` and ${data.length - 2} more`;
                                }
                            } else {
                                authorsText = 'No authors';
                            }
                            return authorsText;
                        }
                    }, {
                        data: "created_at",
                        render: function(data) {
                            return data ? new Date(data).toLocaleString() : 'N/A';
                        }
                    },
                    {
                        data: "updated_at",
                        render: function(data) {
                            return data ? new Date(data).toLocaleString() : 'N/A';
                        }
                    },
                    {
                        data: "status",
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
                    }, {
                        data: null,
                        render: function(data) {
                            let actions = '<div class="d-flex gap-2 flex-wrap">';

                            // View Details
                            actions += '<button class="btn btn-sm btn-info view-details" data-id="' + data.id + '" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details"><i class="ri-eye-fill"></i></button>';

                            // Review/Feedback
                            actions += '<button class="btn btn-sm btn-warning review-abstract" data-id="' + data.id + '" data-bs-toggle="tooltip" data-bs-placement="top" title="Review & Give Feedback"><i class="ri-feedback-fill"></i></button>';

                            // Quick Actions based on status for reviewers (status updates only)
                            if (data.status === 'submitted' || data.status === 'under_review') {
                                actions += '<div class="btn-group">';
                                actions += '<button class="btn btn-sm btn-outline-success quick-accept" data-id="' + data.id + '" data-bs-toggle="tooltip" title="Accept"><i class="ri-check-fill"></i></button>';
                                actions += '<button class="btn btn-sm btn-outline-danger quick-reject" data-id="' + data.id + '" data-bs-toggle="tooltip" title="Reject"><i class="ri-close-fill"></i></button>';
                                actions += '</div>';
                            }

                            actions += '</div>';
                            return actions;
                        }
                    }
                ],
                responsive: true
            }); // Load reviewer's assigned subthemes for filtering
            function loadReviewerSubthemes() {
                // Show loading state
                $('#subthemeFilter').html('<option value="">Loading subthemes...</option>').prop('disabled', true);

                $.ajax({
                    url: '/submissions/abstracts-papers/getReviewerSubthemes',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            let options = '<option value="">All Assigned Subthemes</option>';
                            if (response.data.length > 0) {
                                response.data.forEach(function(subtheme) {
                                    options += `<option value="${subtheme.program_subtheme_id}">${subtheme.subtheme_name}</option>`;
                                });
                            } else {
                                options += '<option value="" disabled>No subthemes assigned</option>';
                            }
                            $('#subthemeFilter').html(options).prop('disabled', false);
                        } else {
                            console.error('Failed to load reviewer subthemes:', response.message);
                            $('#subthemeFilter').html('<option value="" disabled>No subthemes assigned</option>').prop('disabled', true);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading reviewer subthemes:', error, xhr.responseText);
                        $('#subthemeFilter').html('<option value="" disabled>Error loading subthemes</option>').prop('disabled', true);
                        Swal.fire({
                            title: 'Warning',
                            text: 'Could not load subthemes filter. You can still view all abstracts.',
                            icon: 'warning'
                        });
                    }
                });
            }

            // Handle subtheme filter change
            $('#subthemeFilter').on('change', function() {
                const selectedSubtheme = $(this).val();
                const selectedText = $(this).find('option:selected').text();

                // Update the DataTable ajax configuration with subtheme filter
                let newAjaxConfig = {
                    url: "/submissions/abstracts-papers/getAbstractsByProgram",
                    dataSrc: function(json) {
                        // Filter out abstracts with 'draft' status
                        return json.data.filter(function(item) {
                            return item.status !== 'draft';
                        });
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTable Ajax Error:', error, code, xhr.responseText);
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to load data. Please try again.',
                            icon: 'error'
                        });
                    }
                };

                // Add subtheme filter if selected
                if (selectedSubtheme) {
                    newAjaxConfig.data = function(d) {
                        d.subtheme_id = selectedSubtheme;
                        return d;
                    };
                }

                // Update DataTable ajax configuration and reload
                abstractsTable.ajax.url(newAjaxConfig.url);
                if (newAjaxConfig.data) {
                    abstractsTable.settings()[0].ajax.data = newAjaxConfig.data;
                } else {
                    delete abstractsTable.settings()[0].ajax.data;
                }
                abstractsTable.ajax.reload();

                // Update card title to show active filter
                const cardTitle = $('.card-title');
                if (selectedSubtheme) {
                    cardTitle.html('Abstract Submissions List (Reviewer View) <small class="text-primary">- Filtered by: ' + selectedText + '</small>');
                } else {
                    cardTitle.text('Abstract Submissions List (Reviewer View)');
                }
            });

            // View abstract (legacy support)
            $(document).on('click', '.view-abstract', function() {
                let abstractId = $(this).data('id');
                loadAbstractDetails(abstractId);
            }); // Reset modal content when closed
            $('#view-abstract-modal').on('hidden.bs.modal', function() {
                $('.view-abstract-participant').text('');
                $('.view-abstract-institution').text('');
                $('.view-abstract-topic').text('');
                $('.view-abstract-date').text('');
                $('.view-abstract-status').html('');
                $('.view-version-select').html('<option value="">Loading versions...</option>');
                $('.view-abstract-title').text('');
                $('.view-abstract-content').html('');
                $('.view-abstract-keywords').text('');
                $('.view-authors-list').html('');
                $('.view-version-select').off('change');
            });
            // Functions to handle abstract versions for view modal
            function loadAbstractVersionsForView(abstractId) {
                // Clear previous version select event handlers
                $('.view-version-select').off('change');

                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractVersions/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            let versions = response.data;
                            if (versions.length > 0) {
                                let options = '';
                                versions.forEach(function(version) {
                                    options += '<option value="' + version.id + '">Version ' + version.version_number + ' (' + new Date(version.created_at).toLocaleString() + ')</option>';
                                });

                                $('.view-version-select').html(options);
                                // Load the first version by default
                                displayVersionDetailsForView(versions[0]);

                                // Handle version change
                                $('.view-version-select').on('change', function() {
                                    let versionId = $(this).val();
                                    let selectedVersion = versions.find(v => v.id == versionId);
                                    if (selectedVersion) {
                                        displayVersionDetailsForView(selectedVersion);
                                    }
                                });
                            } else {
                                $('.view-version-select').html('<option value="">No versions available</option>');
                                $('.view-abstract-title').text('No title available');
                                $('.view-abstract-content').html('No content available');
                                $('.view-abstract-keywords').text('None');
                            }
                        } else {
                            console.error('Failed to load abstract versions:', response.message);
                            $('.view-version-select').html('<option value="">Error loading versions</option>');
                        }
                    },
                    error: function() {
                        console.error('Error occurred while loading abstract versions');
                        $('.view-version-select').html('<option value="">Error loading versions</option>');
                    }
                });
            }

            function displayVersionDetailsForView(version) {
                if (!version) {
                    $('.view-abstract-title').text('No title available');
                    $('.view-abstract-content').html('No content available');
                    $('.view-abstract-keywords').text('None');
                    return;
                }

                $('.view-abstract-title').text(version.title || 'No title available');
                $('.view-abstract-content').html(version.content ? version.content.replace(/\n/g, '<br>') : 'No content available');
                $('.view-abstract-keywords').text(version.keywords || 'None');
            }
            // Functions to handle abstract authors for view modal
            function loadAbstractAuthorsForView(abstractId) {
                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractAuthors/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            let authors = response.data;
                            let authorsHtml = '';

                            if (authors.length > 0) {
                                authors.forEach(function(author, index) {
                                    authorsHtml += '<div class="author-item p-3 border rounded mb-3">';
                                    authorsHtml += '<div class="row">';
                                    authorsHtml += '<div class="col-md-6"><strong>Name:</strong> ' + (author.full_name || 'N/A') + '</div>';
                                    authorsHtml += '<div class="col-md-6"><strong>Institution:</strong> ' + (author.institution || 'N/A') + '</div>';
                                    authorsHtml += '</div>';
                                    authorsHtml += '<div class="row mt-2">';
                                    authorsHtml += '<div class="col-md-6"><strong>Email:</strong> ' + (author.email || 'N/A') + '</div>';
                                    authorsHtml += '<div class="col-md-6"><strong>Registered Participant:</strong> ' + (author.is_participant == 1 ? 'Yes' : 'No') + '</div>';
                                    authorsHtml += '</div>';
                                    authorsHtml += '</div>';
                                });
                            } else {
                                authorsHtml = '<div class="alert alert-info">No authors available for this abstract.</div>';
                            }

                            $('.view-authors-list').html(authorsHtml);
                        } else {
                            console.error('Failed to load abstract authors:', response.message || 'Unknown error');
                            $('.view-authors-list').html('<div class="alert alert-danger">Error loading authors: ' + (response.message || 'Unknown error') + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred while loading abstract authors:', error);
                        $('.view-authors-list').html('<div class="alert alert-danger">Error loading authors. Please try again.</div>');
                    }
                });
            }

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
                                    options += '<option value="' + version.id + '">Version ' + version.version_number + ' (' + new Date(version.created_at).toLocaleString() + ')</option>';
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

            // View Details Button
            $(document).on('click', '.view-details', function() {
                let abstractId = $(this).data('id');
                loadAbstractDetails(abstractId);
            });

            // Review Abstract Button
            $(document).on('click', '.review-abstract', function() {
                let abstractId = $(this).data('id');
                loadAbstractForReview(abstractId);
            });

            // Quick Accept Button
            $(document).on('click', '.quick-accept', function() {
                let abstractId = $(this).data('id');
                quickAction(abstractId, 'accept');
            });

            // Quick Reject Button
            $(document).on('click', '.quick-reject', function() {
                let abstractId = $(this).data('id');
                quickAction(abstractId, 'reject');
            });

            // Quick Actions from dropdown
            $(document).on('click', '.quick-action', function(e) {
                e.preventDefault();
                let action = $(this).data('action');
                let abstractId = $('#feedback_abstract_id').val();
                quickAction(abstractId, action);
            });

            // Submit Feedback
            $(document).on('click', '#submit-feedback-btn', function() {
                submitFeedback();
            }); // Open Feedback Modal from Details Modal
            $(document).on('click', '.open-feedback-btn', function() {
                let abstractId = $(this).data('id') || $('#view_abstract_id').val();
                $('#abstract-details-modal').modal('hide');
                loadAbstractForReview(abstractId);
            });

            // Feedback Version Selection
            $(document).on('change', '#feedback-version-select', function() {
                let versionId = $(this).val();
                if (versionId) {
                    loadVersionForFeedback(versionId);
                    loadVersionFeedback(versionId);
                }
            });

            // Function to load abstract details
            function loadAbstractDetails(abstractId) {
                // Redirect to the new enhanced details page
                window.location.href = '/submissions/abstracts-papers/details/' + abstractId;
            }

            // Function to load abstract for review
            function loadAbstractForReview(abstractId) {
                $('#feedback-modal').modal('show');

                $('.modal-loading').removeClass('d-none');

                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractData/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('.modal-loading').addClass('d-none');

                        if (response && response.success) {
                            let abstract = response.data;

                            // Set form data
                            $('#feedback_abstract_id').val(abstract.id); // Basic info
                            $('.feedback-abstract-participant').text(abstract.participant_name);
                            $('.feedback-abstract-topic').text(abstract.topic_name || 'No topic');
                            $('.feedback-abstract-date').text(abstract.created_at ? new Date(abstract.created_at).toLocaleString() : 'N/A');

                            // Status
                            let statusText = abstract.status ? abstract.status.replace('_', ' ') : 'Unknown';
                            let badgeClass = getStatusBadgeClass(abstract.status);
                            $('.feedback-abstract-status').html('<span class="badge ' + badgeClass + '">' + statusText + '</span>');

                            // Load versions for selection
                            loadVersionsForFeedback(abstract.id);

                            // Load authors
                            loadAbstractAuthorsForFeedback(abstract.id);
                        } else {
                            Swal.fire('Error', response.message || 'Failed to load abstract data', 'error');
                        }
                    },
                    error: function() {
                        $('.modal-loading').addClass('d-none');
                        Swal.fire('Error', 'An error occurred while loading the abstract', 'error');
                    }
                });
            }

            // Load versions for feedback selection
            function loadVersionsForFeedback(abstractId) {
                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractVersions/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            let versions = response.data;
                            let options = '<option value="">Select a version to review</option>';
                            versions.forEach(function(version) {
                                options += '<option value="' + version.id + '">Version ' + version.version_number + ' (' + new Date(version.created_at).toLocaleString() + ')</option>';
                            });

                            $('#feedback-version-select').html(options);

                            // Auto-select latest version
                            if (versions.length > 0) {
                                let latestVersion = versions[versions.length - 1];
                                $('#feedback-version-select').val(latestVersion.id);
                                loadVersionForFeedback(latestVersion.id);
                                loadVersionFeedback(latestVersion.id);
                            }
                        }
                    }
                });
            }

            // Load specific version content for feedback
            function loadVersionForFeedback(versionId) {
                $('#feedback_version_id').val(versionId);

                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractVersion/' + versionId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            let version = response.data;
                            $('.feedback-abstract-title').text(version.title || 'No title');
                            $('.feedback-abstract-content').html(version.content ? version.content.replace(/\n/g, '<br>') : 'No content');
                            $('.feedback-abstract-keywords').text(version.keywords || 'None');
                        }
                    }
                });
            }

            // Load existing feedback for version
            function loadVersionFeedback(versionId) {
                $.ajax({
                    url: '/submissions/abstracts-papers/getVersionFeedback/' + versionId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            let feedbacks = response.data;
                            let feedbackHtml = '';

                            if (feedbacks.length > 0) {
                                feedbacks.forEach(function(feedback) {
                                    feedbackHtml += '<div class="feedback-item border rounded p-3 mb-3">';
                                    feedbackHtml += '<div class="d-flex justify-content-between mb-2">';
                                    feedbackHtml += '<strong>' + (feedback.reviewer_name || 'Anonymous Reviewer') + '</strong>';
                                    feedbackHtml += '<small class="text-muted">' + new Date(feedback.created_at).toLocaleString() + '</small>';
                                    feedbackHtml += '</div>';
                                    if (feedback.recommendation) {
                                        feedbackHtml += '<div class="mb-2"><span class="badge bg-info">' + feedback.recommendation.replace('_', ' ') + '</span></div>';
                                    }
                                    feedbackHtml += '<p>' + feedback.feedback + '</p>';
                                    feedbackHtml += '</div>';
                                });
                            } else {
                                feedbackHtml = '<div class="alert alert-info">No feedback available for this version yet.</div>';
                            }

                            $('.existing-feedback-list').html(feedbackHtml);
                        }
                    }
                });
            }

            // Load authors for feedback modal
            function loadAbstractAuthorsForFeedback(abstractId) {
                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractAuthors/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            let authors = response.data;
                            let authorsHtml = '';

                            if (authors.length > 0) {
                                authors.forEach(function(author) {
                                    authorsHtml += '<div class="author-item p-2 border rounded mb-2">';
                                    authorsHtml += '<div class="row">';
                                    authorsHtml += '<div class="col-md-6"><strong>' + (author.full_name || 'N/A') + '</strong></div>';
                                    authorsHtml += '<div class="col-md-6">' + (author.institution || 'N/A') + '</div>';
                                    authorsHtml += '</div>';
                                    authorsHtml += '<div class="row">';
                                    authorsHtml += '<div class="col-md-6">' + (author.email || 'N/A') + '</div>';
                                    authorsHtml += '<div class="col-md-6">' + (author.is_participant == 1 ? 'Registered Participant' : 'External Author') + '</div>';
                                    authorsHtml += '</div>';
                                    authorsHtml += '</div>';
                                });
                            } else {
                                authorsHtml = '<div class="alert alert-info">No authors information available.</div>';
                            }

                            $('.feedback-authors-list').html(authorsHtml);
                        }
                    }
                });
            }

            // Load authors for details modal
            function loadAbstractAuthorsForDetails(abstractId) {
                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractAuthors/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            let authors = response.data;
                            let authorsHtml = '';

                            if (authors.length > 0) {
                                authors.forEach(function(author, index) {
                                    authorsHtml += '<div class="author-item p-3 border rounded mb-3">';
                                    authorsHtml += '<div class="row">';
                                    authorsHtml += '<div class="col-md-6"><strong>Name:</strong> ' + (author.full_name || 'N/A') + '</div>';
                                    authorsHtml += '<div class="col-md-6"><strong>Institution:</strong> ' + (author.institution || 'N/A') + '</div>';
                                    authorsHtml += '</div>';
                                    authorsHtml += '<div class="row mt-2">';
                                    authorsHtml += '<div class="col-md-6"><strong>Email:</strong> ' + (author.email || 'N/A') + '</div>';
                                    authorsHtml += '<div class="col-md-6"><strong>Type:</strong> ' + (author.is_participant == 1 ? 'Registered Participant' : 'External Author') + '</div>';
                                    authorsHtml += '</div>';
                                    authorsHtml += '</div>';
                                });
                            } else {
                                authorsHtml = '<div class="alert alert-info">No authors available for this abstract.</div>';
                            }

                            $('.details-authors-list').html(authorsHtml);
                        }
                    }
                });
            }

            // Load abstract versions with feedback for details modal
            function loadAbstractVersionsWithFeedback(abstractId) {
                $.ajax({
                    url: '/submissions/abstracts-papers/getAbstractVersions/' + abstractId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            let versions = response.data;
                            let accordionHtml = '';

                            if (versions.length > 0) {
                                versions.forEach(function(version, index) {
                                    let versionId = 'version-' + version.id;
                                    let isActive = index === versions.length - 1; // Latest version expanded by default

                                    accordionHtml += '<div class="accordion-item">';
                                    accordionHtml += '<h2 class="accordion-header" id="heading-' + versionId + '">';
                                    accordionHtml += '<button class="accordion-button ' + (isActive ? '' : 'collapsed') + '" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-' + versionId + '" aria-expanded="' + isActive + '">';
                                    accordionHtml += 'Version ' + version.version_number + ' (' + new Date(version.created_at).toLocaleString() + ')';
                                    accordionHtml += '</button>';
                                    accordionHtml += '</h2>';
                                    accordionHtml += '<div id="collapse-' + versionId + '" class="accordion-collapse collapse ' + (isActive ? 'show' : '') + '" data-bs-parent="#versionsAccordion">';
                                    accordionHtml += '<div class="accordion-body">';

                                    // Version content
                                    accordionHtml += '<div class="mb-3"><strong>Title:</strong> ' + (version.title || 'No title') + '</div>';
                                    accordionHtml += '<div class="mb-3"><strong>Content:</strong><div class="p-2 bg-light rounded">' + (version.content ? version.content.replace(/\n/g, '<br>') : 'No content') + '</div></div>';
                                    accordionHtml += '<div class="mb-3"><strong>Keywords:</strong> ' + (version.keywords || 'None') + '</div>';

                                    // Feedback section placeholder
                                    accordionHtml += '<div class="feedback-section" data-version-id="' + version.id + '">';
                                    accordionHtml += '<h6 class="border-bottom pb-2">Feedback for this version</h6>';
                                    accordionHtml += '<div class="feedback-list">Loading feedback...</div>';
                                    accordionHtml += '</div>';

                                    accordionHtml += '</div>';
                                    accordionHtml += '</div>';
                                    accordionHtml += '</div>';
                                });
                            } else {
                                accordionHtml = '<div class="alert alert-info">No versions available for this abstract.</div>';
                            }

                            $('.versions-accordion').html(accordionHtml);

                            // Load feedback for each version
                            versions.forEach(function(version) {
                                loadFeedbackForVersion(version.id);
                            });
                        }
                    }
                });
            }

            // Load feedback for specific version in details modal
            function loadFeedbackForVersion(versionId) {
                $.ajax({
                    url: '/submissions/abstracts-papers/getVersionFeedback/' + versionId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        let feedbackContainer = $('.feedback-section[data-version-id="' + versionId + '"] .feedback-list');

                        if (response.success && response.data) {
                            let feedbacks = response.data;
                            let feedbackHtml = '';

                            if (feedbacks.length > 0) {
                                feedbacks.forEach(function(feedback) {
                                    feedbackHtml += '<div class="feedback-item border rounded p-2 mb-2">';
                                    feedbackHtml += '<div class="d-flex justify-content-between mb-1">';
                                    feedbackHtml += '<small><strong>' + (feedback.reviewer_name || 'Anonymous Reviewer') + '</strong></small>';
                                    feedbackHtml += '<small class="text-muted">' + new Date(feedback.created_at).toLocaleString() + '</small>';
                                    feedbackHtml += '</div>';
                                    if (feedback.recommendation) {
                                        feedbackHtml += '<div class="mb-1"><span class="badge bg-info badge-sm">' + feedback.recommendation.replace('_', ' ') + '</span></div>';
                                    }
                                    feedbackHtml += '<small>' + feedback.feedback + '</small>';
                                    feedbackHtml += '</div>';
                                });
                            } else {
                                feedbackHtml = '<small class="text-muted">No feedback available for this version.</small>';
                            }

                            feedbackContainer.html(feedbackHtml);
                        } else {
                            feedbackContainer.html('<small class="text-muted">Error loading feedback.</small>');
                        }
                    }
                });
            }

            // Submit feedback
            function submitFeedback() {
                let formData = $('#feedback-form').serialize();

                if (!$('#feedback_version_id').val()) {
                    Swal.fire('Error', 'Please select a version to review', 'error');
                    return;
                }

                if (!$('#feedback_text').val().trim()) {
                    Swal.fire('Error', 'Please enter your feedback', 'error');
                    return;
                }

                $.ajax({
                    url: '/submissions/abstracts-papers/submitFeedback',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', 'Feedback submitted successfully', 'success').then(() => {
                                $('#feedback-form')[0].reset();
                                let versionId = $('#feedback_version_id').val();
                                loadVersionFeedback(versionId);
                                abstractsTable.ajax.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Failed to submit feedback', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while submitting feedback', 'error');
                    }
                });
            }

            // Quick actions (accept, reject, request revision)
            function quickAction(abstractId, action) {
                let actionText = action.replace('_', ' ');
                let confirmText = 'Are you sure you want to ' + actionText + ' this abstract?';

                Swal.fire({
                    title: 'Confirm Action',
                    text: confirmText,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, ' + actionText,
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/submissions/abstracts-papers/quickAction',
                            type: 'POST',
                            data: {
                                abstract_id: abstractId,
                                action: action
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Success', response.message, 'success').then(() => {
                                        abstractsTable.ajax.reload();
                                        if ($('#feedback-modal').hasClass('show')) {
                                            $('#feedback-modal').modal('hide');
                                        }
                                    });
                                } else {
                                    Swal.fire('Error', response.message || 'Action failed', 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'An error occurred while performing the action', 'error');
                            }
                        });
                    }
                });
            }

            // Helper function to get status badge class
            function getStatusBadgeClass(status) {
                switch (status) {
                    case 'draft':
                        return 'bg-secondary';
                    case 'submitted':
                        return 'bg-primary';
                    case 'under_review':
                        return 'bg-info';
                    case 'accepted':
                        return 'bg-success';
                    case 'rejected':
                        return 'bg-danger';
                    default:
                        return 'bg-secondary';
                }
            }

            // Reset modals when closed
            $('#feedback-modal').on('hidden.bs.modal', function() {
                $('#feedback-form')[0].reset();
                $('.feedback-abstract-title').text('');
                $('.feedback-abstract-content').html('');
                $('.feedback-abstract-keywords').text('');
                $('.existing-feedback-list').html('');
                $('#feedback-version-select').html('<option value="">Loading versions...</option>');
            });

            $('#abstract-details-modal').on('hidden.bs.modal', function() {
                $('.details-authors-list').html('');
                $('.versions-accordion').html('');
            });
        });
    </script>
</body>

</html>