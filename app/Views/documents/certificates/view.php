<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Award Details - ' . ($award->title ?? 'Unknown Award'))); ?>
    <?= $this->include('partials/head-css') ?>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <style>
        .award-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .participant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .bulk-actions-bar {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: none;
        }
        
        .bulk-actions-bar.show {
            display: block;
        }

        .certificate-status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .funding-type-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }

        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background: white;
            border-bottom: 2px solid #dee2e6;
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
                    <!-- Page Title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">Award Details</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="/documents/certificates">Certificates</a></li>
                                        <li class="breadcrumb-item active">Award Details</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Award Information Card -->
                    <div class="row">
                        <div class="col-12">
                            <div class="award-info-card">
                                <div class="row align-items-center">
                                    <div class="col-lg-8">
                                        <h2 class="text-white mb-3"><?= esc($award->title ?? 'Unknown Award') ?></h2>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-2"><i class="ri-award-line me-2"></i><strong>Type:</strong> <?= ucfirst(str_replace('_', ' ', $award->award_type ?? 'unknown')) ?></p>
                                                <p class="mb-2"><i class="ri-file-text-line me-2"></i><strong>Description:</strong> <?= esc($award->description ?? 'No description') ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-2"><i class="ri-calendar-line me-2"></i><strong>Created:</strong> <?= date('M d, Y', strtotime($award->created_at ?? 'now')) ?></p>
                                                <p class="mb-2"><i class="ri-shield-check-line me-2"></i><strong>Certificate Template:</strong> 
                                                    <?php if (isset($award->has_certificate_template) && $award->has_certificate_template): ?>
                                                        <span class="badge bg-success">Available</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Not Available</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 text-lg-end">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <h3 class="text-white mb-0"><?= $award->participants_count ?? 0 ?></h3>
                                                <p class="text-white-50 mb-0">Participants</p>
                                            </div>
                                            <div class="col-6">
                                                <h3 class="text-white mb-0"><?= $award->certificates_issued ?? 0 ?></h3>
                                                <p class="text-white-50 mb-0">Certificates Issued</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header sticky-header">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h4 class="card-title mb-0">Participant Management</h4>
                                            <small class="text-muted">Manage participants for this award based on program: <strong><?= esc($program->name ?? 'Unknown Program') ?></strong></small>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <button type="button" class="btn btn-primary" id="assign-selected-btn" disabled>
                                                <i class="ri-user-add-line me-1"></i> Assign Selected
                                            </button>
                                            <button type="button" class="btn btn-success" onclick="issueCertificatesView()">
                                                <i class="ri-file-text-line me-1"></i> Issue Certificates
                                            </button>
                                            <a href="/documents/certificates" class="btn btn-secondary">
                                                <i class="ri-arrow-left-line me-1"></i> Back to List
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bulk Actions Bar -->
                                <div class="bulk-actions-bar" id="bulk-actions-bar">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <span id="selected-count">0</span> participants selected
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="assignSelectedParticipants()">
                                                <i class="ri-user-add-line me-1"></i> Assign Selected
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSelectedParticipants()">
                                                <i class="ri-user-unfollow-line me-1"></i> Remove Selected
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light" onclick="clearSelection()">
                                                <i class="ri-close-line me-1"></i> Clear Selection
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <!-- Filter Tabs -->
                                    <ul class="nav nav-tabs nav-justified" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#available-participants" role="tab">
                                                <i class="ri-user-line me-1"></i> Available Participants
                                                <span class="badge bg-primary ms-1" id="available-count"><?= $availableCount ?? 0 ?></span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#assigned-participants" role="tab">
                                                <i class="ri-user-star-line me-1"></i> Assigned Participants
                                                <span class="badge bg-success ms-1" id="assigned-count"><?= $assignedCount ?? 0 ?></span>
                                            </a>
                                        </li>
                                    </ul>

                                    <!-- Tab Content -->
                                    <div class="tab-content pt-3">
                                        <!-- Available Participants Tab -->
                                        <div class="tab-pane fade show active" id="available-participants" role="tabpanel">
                                            <!-- Payment Filter -->
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <label for="payment-filter" class="form-label">
                                                        <i class="ri-money-dollar-circle-line me-1"></i>Filter by Payment Status
                                                    </label>
                                                    <select class="form-select" id="payment-filter">
                                                        <option value="any_payment" selected>Any Payment Type</option>
                                                        <option value="registration">Registration Fee Paid</option>
                                                        <option value="program_fee_1">Program Fee 1 Paid</option>
                                                        <option value="program_fee_2">Program Fee 2 Paid</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="d-flex align-items-end h-100">
                                                        <small class="text-muted">
                                                            <i class="ri-information-line me-1"></i>
                                                            Only participants who have made payments will be shown.
                                                            Select a specific payment type to further filter the list.
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="table-responsive">
                                                <table id="available-participants-table" class="table table-bordered table-striped align-middle" style="width:100%">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="40">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" id="select-all-available">
                                                                </div>
                                                            </th>
                                                            <th width="60">Avatar</th>
                                                            <th>Name</th>
                                                            <th>Account ID</th>
                                                            <th>Email</th>
                                                            <th>Country</th>
                                                            <th>Funding</th>
                                                            <th>Registration Date</th>
                                                            <th width="100">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Data will be loaded via AJAX -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Assigned Participants Tab -->
                                        <div class="tab-pane fade" id="assigned-participants" role="tabpanel">
                                            <div class="table-responsive">
                                                <table id="assigned-participants-table" class="table table-bordered table-striped align-middle" style="width:100%">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="40">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" id="select-all-assigned">
                                                                </div>
                                                            </th>
                                                            <th width="60">Avatar</th>
                                                            <th>Name</th>
                                                            <th>Account ID</th>
                                                            <th>Email</th>
                                                            <th>Country</th>
                                                            <th>Certificate Status</th>
                                                            <th>Assigned Date</th>
                                                            <th width="120">Actions</th>
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

    <!-- Assignment Notes Modal -->
    <div class="modal fade" id="assignmentNotesModal" tabindex="-1" aria-labelledby="assignmentNotesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignmentNotesModalLabel">Add Assignment Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="assignment-notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="assignment-notes" rows="3" placeholder="Add any notes for this assignment..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Selected Participants:</label>
                        <div id="selected-participants-list" class="border rounded p-2 bg-light">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="confirmAssignment()">Assign Participants</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Issue Certificates Modal -->
    <div class="modal fade" id="issueCertificatesModal" tabindex="-1" aria-labelledby="issueCertificatesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="issueCertificatesModalLabel">Issue Certificates</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="issue-certificates-content">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="issue-selected-certificates-btn">Issue to Selected</button>
                    <button type="button" class="btn btn-primary" id="issue-all-certificates-btn">Issue to All Eligible</button>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>

    <!-- Add jQuery and DataTables via CDN (like payments module) -->
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom CSS for simplified styling -->
    <style>
        /* Bulk actions bar styling */
        .bulk-actions-bar {
            background: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
            display: none;
            transition: all 0.3s ease;
        }
        
        .bulk-actions-bar.show {
            display: block;
        }
        
        /* Payment filter styling */
        .form-select:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        /* Award info card */
        .award-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            color: white;
        }
        
        /* Sticky header */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: white;
            border-bottom: 1px solid #dee2e6;
        }
        
        /* Participant avatar */
        .participant-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        /* Funding type badges */
        .funding-type-badge {
            font-size: 0.75em;
        }
    </style>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <script>
        let availableTable, assignedTable;
        let selectedParticipants = [];
        let selectedAssigned = [];
        const awardId = <?= $award->id ?? 0 ?>;

        // Use jQuery ready instead of DOMContentLoaded 
        $(function() {
            console.log("=== CERTIFICATE VIEW DEBUG START ===");
            console.log("Award ID:", awardId);
            console.log("Current URL:", window.location.href);
            console.log("jQuery loaded:", typeof $ !== 'undefined');
            console.log("DataTable available:", typeof $.fn.DataTable !== 'undefined');
            console.log("Document ready, initializing DataTables...");
            
            // Check if required elements exist
            console.log("Available table element exists:", $('#available-participants-table').length > 0);
            console.log("Assigned table element exists:", $('#assigned-participants-table').length > 0);
            console.log("Payment filter element exists:", $('#payment-filter').length > 0);
            console.log("Current payment filter value:", $('#payment-filter').val());
            
            // Check if we have the required data
            if (!awardId || awardId === 0) {
                console.error("Invalid award ID:", awardId);
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Award',
                    text: 'No valid award ID found. Please go back and select an award.'
                });
                return;
            }
            
            // Debug logging only - no visual indicator
            console.log("Debug: JavaScript is running with Award ID:", awardId);
            
            if (typeof $.fn.DataTable !== 'undefined') {
                try {
                    initializeDataTables();
                    initializeEventHandlers();
                    console.log("DataTables initialization completed successfully");
                } catch (error) {
                    console.error("Error during DataTable initialization:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Initialization Error',
                        text: 'Error during DataTable initialization: ' + error.message
                    });
                }
            } else {
                console.error("DataTables not loaded!");
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Library',
                    text: 'DataTables library not loaded. Check console for errors.'
                });
            }
            console.log("=== CERTIFICATE VIEW DEBUG END ===");
        });

        function initializeDataTables() {
            console.log("=== INITIALIZING DATATABLES ===");
            console.log("Award ID for DataTables:", awardId);
            
            // Available Participants Table - Server Side
            console.log("Creating available participants table...");
            availableTable = $('#available-participants-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= site_url('documents/certificates/getAvailableParticipantsData/' . $award->id) ?>',
                    type: 'GET',
                    beforeSend: function(xhr, settings) {
                        console.log("=== AVAILABLE PARTICIPANTS AJAX START ===");
                        console.log("URL:", settings.url);
                        console.log("Request type:", settings.type);
                        console.log("Headers:", xhr.getAllResponseHeaders());
                    },
                    data: function(d) {
                        console.log("DataTable parameters being sent:", d);
                        // Add payment filter parameter - ensure proper format
                        const filterValue = $('#payment-filter').val();
                        if (filterValue) {
                            d.payment_filter = filterValue;
                            console.log("Payment filter set to:", filterValue);
                        } else {
                            d.payment_filter = 'any_payment';
                            console.log("Payment filter defaulted to: any_payment");
                        }
                        console.log("Payment filter being applied:", d.payment_filter);
                        console.log("Final request data:", d);
                        return d;
                    },
                    dataSrc: function(json) {
                        console.log("=== AVAILABLE PARTICIPANTS RESPONSE ===");
                        console.log("Response type:", typeof json);
                        console.log("Full response:", json);
                        
                        if (typeof json === 'string') {
                            console.error("Response is string, not JSON. Raw response:", json.substring(0, 500));
                            try {
                                json = JSON.parse(json);
                            } catch (e) {
                                console.error("Failed to parse JSON:", e);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Invalid Response',
                                    text: 'Server returned invalid JSON response'
                                });
                                return [];
                            }
                        }
                        
                        if (json.error) {
                            console.error("Server error:", json.error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Server Error',
                                text: json.error
                            });
                            return [];
                        }
                        
                        if (!json.data || !Array.isArray(json.data)) {
                            console.error("Invalid data structure. Expected array, got:", typeof json.data);
                            return [];
                        }
                        
                        console.log("Data array length:", json.data.length);
                        console.log("Records total:", json.recordsTotal);
                        console.log("Records filtered:", json.recordsFiltered);
                        return json.data;
                    },
                    error: function(xhr, error, thrown) {
                        console.error('=== AVAILABLE PARTICIPANTS AJAX ERROR ===');
                        console.error('Status:', xhr.status);
                        console.error('Error:', error);
                        console.error('Thrown:', thrown);
                        console.error('Response Text:', xhr.responseText);
                        console.error('Ready State:', xhr.readyState);
                        
                        var errorMsg = 'Failed to load available participants data';
                        var detailMsg = '';
                        
                        if (xhr.status === 0) {
                            errorMsg = 'Network error or server not responding';
                            detailMsg = 'Please check if the server is running and accessible.';
                        } else if (xhr.status === 403) {
                            errorMsg = 'Access denied';
                            detailMsg = 'Please ensure you have selected a program and have proper permissions.';
                        } else if (xhr.status === 404) {
                            errorMsg = 'Endpoint not found';
                            detailMsg = 'The requested URL was not found on the server.';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Internal server error';
                            detailMsg = 'Please check the server logs for more details.';
                        } else if (xhr.status >= 400) {
                            errorMsg = 'Client error';
                            detailMsg = `HTTP ${xhr.status}: ${xhr.statusText}`;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: errorMsg,
                            text: detailMsg,
                            footer: `Status: ${xhr.status} ${xhr.statusText}`
                        });
                    }
                },
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false },
                    { data: 'avatar', orderable: false, searchable: false },
                    { data: 'name', name: 'participants.full_name' },
                    { data: 'account_id', name: 'participants.account_id' },
                    { data: 'email', name: 'users.email' },
                    { data: 'country', name: 'participants.nationality' },
                    { data: 'funding', orderable: false, searchable: false },
                    { data: 'registration_date', name: 'participants.created_at' },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                responsive: true,
                pageLength: 25,
                order: [[2, 'asc']],
                language: {
                    processing: '<div class="d-flex align-items-center justify-content-center p-3"><i class="ri-loader-2-line spinner-border spinner-border-sm text-primary me-2"></i> Loading participants...</div>',
                    emptyTable: "No available participants found for this program",
                    search: "Search participants:",
                    lengthMenu: "Show _MENU_ participants per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ participants",
                    paginate: {
                        previous: "Previous",
                        next: "Next"
                    },
                    loadingRecords: "Loading...",
                    zeroRecords: "No matching participants found"
                },
                initComplete: function() {
                    console.log("=== AVAILABLE PARTICIPANTS TABLE INITIALIZED ===");
                    console.log("Table settings:", this.api().settings()[0]);
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="ri-file-excel-line"></i> Export Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="ri-file-pdf-line"></i> Export PDF',
                        className: 'btn btn-danger btn-sm'
                    }
                ],
                drawCallback: function() {
                    console.log("Available participants table draw completed");
                    updateSelectionControls();
                    // Update available count badge
                    const info = this.api().page.info();
                    $('#available-count').text(info.recordsTotal);
                    console.log("Available participants count:", info.recordsTotal);
                }
            });

            // Assigned Participants Table - Server Side
            assignedTable = $('#assigned-participants-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= site_url('documents/certificates/getAssignedParticipantsData/' . $award->id) ?>',
                    type: 'GET',
                    beforeSend: function() {
                        console.log("=== ASSIGNED PARTICIPANTS AJAX START ===");
                        console.log("URL:", '<?= site_url('documents/certificates/getAssignedParticipantsData/' . $award->id) ?>');
                    },
                    data: function(d) {
                        console.log("Assigned DataTable parameters being sent:", d);
                        return d;
                    },
                    dataSrc: function(json) {
                        console.log("=== ASSIGNED PARTICIPANTS RESPONSE ===");
                        console.log("Full response:", json);
                        if (json.error) {
                            console.error("Server error:", json.error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Server Error',
                                text: json.error
                            });
                            return [];
                        }
                        console.log("Data array length:", json.data ? json.data.length : 0);
                        return json.data || [];
                    },
                    error: function(xhr, error, thrown) {
                        console.error('=== ASSIGNED PARTICIPANTS AJAX ERROR ===');
                        console.error('Status:', xhr.status);
                        console.error('Error:', error);
                        console.error('Thrown:', thrown);
                        console.error('Response Text:', xhr.responseText);
                        
                        var errorMsg = 'Failed to load assigned participants data';
                        if (xhr.status === 403) {
                            errorMsg = 'Access denied. Please ensure you have selected a program.';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Server error occurred. Please check logs.';
                        } else if (xhr.status === 404) {
                            errorMsg = 'Endpoint not found. Check routing configuration.';
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error loading participants',
                            text: errorMsg + ' (Status: ' + xhr.status + ')'
                        });
                    }
                },
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false },
                    { data: 'avatar', orderable: false, searchable: false },
                    { data: 'name', name: 'participants.full_name' },
                    { data: 'account_id', name: 'participants.account_id' },
                    { data: 'email', name: 'users.email' },
                    { data: 'country', name: 'participants.nationality' },
                    { data: 'certificate_status', orderable: false, searchable: false },
                    { data: 'assigned_date', name: 'participant_awards.assigned_at' },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                responsive: true,
                pageLength: 25,
                order: [[7, 'desc']],
                language: {
                    processing: '<i class="ri-loader-2-line spinner-border spinner-border-sm text-primary"></i> Loading assigned participants...',
                    emptyTable: "No participants assigned to this award yet",
                    search: "Search assigned participants:",
                    lengthMenu: "Show _MENU_ participants per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ participants"
                },
                initComplete: function() {
                    console.log("=== ASSIGNED PARTICIPANTS TABLE INITIALIZED ===");
                    console.log("Table settings:", this.api().settings()[0]);
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="ri-file-excel-line"></i> Export Excel',
                        className: 'btn btn-success btn-sm'
                    }
                ],
                drawCallback: function() {
                    console.log("Assigned participants table draw completed");
                    updateSelectionControls();
                    // Update assigned count badge
                    const info = this.api().page.info();
                    $('#assigned-count').text(info.recordsTotal);
                    console.log("Assigned participants count:", info.recordsTotal);
                }
            });
            
            console.log("=== DATATABLES INITIALIZATION COMPLETE ===");
        }

        function initializeEventHandlers() {
            // Select all checkboxes
            $('#select-all-available').on('change', function() {
                $('.participant-checkbox').prop('checked', $(this).is(':checked'));
                updateSelectionControls();
            });

            $('#select-all-assigned').on('change', function() {
                $('.assigned-checkbox').prop('checked', $(this).is(':checked'));
                updateSelectionControls();
            });

            // Individual checkboxes
            $(document).on('change', '.participant-checkbox, .assigned-checkbox', function() {
                updateSelectionControls();
            });

            // Payment filter handler
            $('#payment-filter').on('change', function() {
                const newFilterValue = $(this).val();
                console.log("Payment filter changed to:", newFilterValue);
                console.log("Reloading table with payment filter:", newFilterValue);
                if (availableTable) {
                    availableTable.ajax.reload();
                }
            });

            // Initialize payment filter to its default value - always ensure we filter for paid participants
            const defaultPaymentFilter = 'any_payment';
            if ($('#payment-filter').val() !== defaultPaymentFilter) {
                $('#payment-filter').val(defaultPaymentFilter);
                console.log("Payment filter was set to default:", defaultPaymentFilter);
            }
            console.log("Payment filter initialized to:", $('#payment-filter').val());

            // Assign selected button
            $('#assign-selected-btn').on('click', function() {
                if (selectedParticipants.length > 0) {
                    showAssignmentNotesModal();
                }
            });
        }

        function updateSelectionControls() {
            // Update selected participants count
            selectedParticipants = $('.participant-checkbox:checked').map(function() {
                return {
                    id: parseInt($(this).val()),
                    name: $(this).data('name')
                };
            }).get();

            selectedAssigned = $('.assigned-checkbox:checked').map(function() {
                return {
                    id: parseInt($(this).val()),
                    name: $(this).data('name')
                };
            }).get();

            const totalSelected = selectedParticipants.length + selectedAssigned.length;
            
            // Update counter
            $('#selected-count').text(totalSelected);
            
            // Show/hide bulk actions bar
            if (totalSelected > 0) {
                $('#bulk-actions-bar').addClass('show');
            } else {
                $('#bulk-actions-bar').removeClass('show');
            }

            // Enable/disable assign button
            $('#assign-selected-btn').prop('disabled', selectedParticipants.length === 0);

            // Update select all checkboxes
            const totalAvailable = $('.participant-checkbox').length;
            const selectedAvailable = $('.participant-checkbox:checked').length;
            const totalAssigned = $('.assigned-checkbox').length;
            const selectedAssignedCount = $('.assigned-checkbox:checked').length;

            $('#select-all-available').prop('checked', totalAvailable > 0 && selectedAvailable === totalAvailable);
            $('#select-all-assigned').prop('checked', totalAssigned > 0 && selectedAssignedCount === totalAssigned);
        }

        function showAssignmentNotesModal() {
            if (selectedParticipants.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one participant to assign'
                });
                return;
            }

            // Populate selected participants list
            let participantsList = selectedParticipants.map(p => 
                `<span class="badge bg-primary me-1 mb-1">${p.name}</span>`
            ).join('');
            
            $('#selected-participants-list').html(participantsList);
            $('#assignmentNotesModal').modal('show');
        }

        function confirmAssignment() {
            const notes = $('#assignment-notes').val();
            const participantIds = selectedParticipants.map(p => p.id);

            Swal.fire({
                title: 'Confirm Assignment',
                text: `Assign ${participantIds.length} participant(s) to this award?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, assign them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    assignParticipants(participantIds, notes);
                }
            });
        }

        function assignParticipants(participantIds, notes = '') {
            Swal.fire({
                title: 'Assigning Participants...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            console.log('Assigning participants:', participantIds, 'to award:', awardId);
            
            // Log the request details for debugging
            console.log('Assignment request payload:', {
                award_id: awardId,
                participant_ids: participantIds,
                notes: notes
            });
            
            fetch('/documents/certificates/assignParticipants', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    award_id: awardId,
                    participant_ids: participantIds,
                    notes: notes
                })
            })
            .then(response => {
                console.log('Assignment response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Assignment response data:', data);
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message
                    }).then(() => {
                        $('#assignmentNotesModal').modal('hide');
                        // Reload both tables
                        availableTable.ajax.reload();
                        assignedTable.ajax.reload();
                        clearSelection();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'Failed to assign participants'
                    });
                }
            })
            .catch(error => {
                console.error('Assignment error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Network error occurred: ' + error.message
                });
            });
        }

        function assignSingleParticipant(participantId) {
            console.log('assignSingleParticipant called with ID:', participantId);
            // Convert to integer to ensure proper data type
            const numericId = parseInt(participantId, 10);
            console.log('Converted to numeric ID:', numericId);
            if (isNaN(numericId)) {
                console.error('Invalid participant ID:', participantId);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Invalid participant ID'
                });
                return;
            }
            
            // Confirm assignment before proceeding
            Swal.fire({
                title: 'Confirm Assignment',
                text: 'Assign this participant to the award?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, assign!'
            }).then((result) => {
                if (result.isConfirmed) {
                    assignParticipants([numericId]);
                }
            });
        }

        function assignSelectedParticipants() {
            if (selectedParticipants.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select participants to assign'
                });
                return;
            }
            showAssignmentNotesModal();
        }

        function removeSelectedParticipants() {
            if (selectedAssigned.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select assigned participants to remove'
                });
                return;
            }

            Swal.fire({
                title: 'Remove Participants?',
                text: `Remove ${selectedAssigned.length} participant(s) from this award?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Process removals
                    const removePromises = selectedAssigned.map(p => 
                        fetch('/documents/certificates/removeParticipant', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                participant_award_id: p.id
                            })
                        })
                    );

                    Promise.all(removePromises)
                        .then(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed!',
                                text: 'Selected participants have been removed'
                            }).then(() => {
                                location.reload();
                            });
                        })
                        .catch(() => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Some participants could not be removed'
                            });
                        });
                }
            });
        }

        function removeParticipantFromAward(participantAwardId) {
            Swal.fire({
                title: 'Remove Participant?',
                text: 'This will remove the participant from the award and revoke any issued certificates.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/documents/certificates/removeParticipant', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            participant_award_id: participantAwardId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed!',
                                text: data.message
                            }).then(() => {
                                // Reload both tables
                                availableTable.ajax.reload();
                                assignedTable.ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.error || 'Failed to remove participant'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Network error occurred'
                        });
                    });
                }
            });
        }

        function clearSelection() {
            $('.participant-checkbox, .assigned-checkbox').prop('checked', false);
            $('#select-all-available, #select-all-assigned').prop('checked', false);
            updateSelectionControls();
        }

        function issueCertificatesView() {
            // Show loading
            $('#issue-certificates-content').html('<div class="text-center"><i class="ri-loader-2-line fs-1 text-muted"></i><p>Loading certificate details...</p></div>');
            $('#issueCertificatesModal').modal('show');

            // Load assigned participants who need certificates
            fetch(`/documents/certificates/getAwardDetails/${awardId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    renderCertificateIssuance(data);
                })
                .catch(error => {
                    $('#issue-certificates-content').html('<div class="alert alert-danger">Error loading certificate details: ' + error.message + '</div>');
                });
        }

        function renderCertificateIssuance(data) {
            const { award, participants, certificates } = data;
            
            if (certificates.length === 0) {
                $('#issue-certificates-content').html(`
                    <div class="alert alert-warning">
                        <h6><i class="ri-error-warning-line me-2"></i>No Certificate Template</h6>
                        <p>This award does not have a certificate template. Please create one in the Master Data section before issuing certificates.</p>
                    </div>
                `);
                $('#issue-selected-certificates-btn, #issue-all-certificates-btn').prop('disabled', true);
                return;
            }

            const eligibleParticipants = participants.filter(p => !p.certificate_id);
            const issuedParticipants = participants.filter(p => p.certificate_id);

            let html = `
                <div class="alert alert-info">
                    <h6><i class="ri-information-line me-2"></i>${award.title}</h6>
                    <p class="mb-0">Certificate template available: <strong>${certificates[0].template_type.toUpperCase()}</strong></p>
                </div>

                ${eligibleParticipants.length > 0 ? `
                    <h6>Participants Eligible for Certificates (${eligibleParticipants.length})</h6>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="select-all-eligible">
                        <label class="form-check-label" for="select-all-eligible">
                            Select All Eligible Participants
                        </label>
                    </div>
                    
                    <div style="max-height: 200px; overflow-y: auto;" class="mb-3">
                        ${eligibleParticipants.map(p => `
                            <div class="form-check">
                                <input class="form-check-input eligible-checkbox" type="checkbox" value="${p.participant_id}" id="eligible-${p.participant_id}">
                                <label class="form-check-label" for="eligible-${p.participant_id}">
                                    ${p.full_name} (${p.account_id})
                                </label>
                            </div>
                        `).join('')}
                    </div>
                ` : '<p class="text-muted">All eligible participants already have certificates.</p>'}

                ${issuedParticipants.length > 0 ? `
                    <h6 class="mt-4">Already Issued Certificates (${issuedParticipants.length})</h6>
                    <div class="list-group" style="max-height: 150px; overflow-y: auto;">
                        ${issuedParticipants.map(p => `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${p.full_name} (${p.account_id})</span>
                                <small class="text-muted">Issued: ${new Date(p.generated_at).toLocaleDateString()}</small>
                            </div>
                        `).join('')}
                    </div>
                ` : ''}
            `;

            $('#issue-certificates-content').html(html);

            // Enable/disable buttons based on available participants
            $('#issue-selected-certificates-btn').prop('disabled', eligibleParticipants.length === 0);
            $('#issue-all-certificates-btn').prop('disabled', eligibleParticipants.length === 0);

            // Initialize select all functionality
            $('#select-all-eligible').on('change', function() {
                $('.eligible-checkbox').prop('checked', $(this).is(':checked'));
            });
        }

        function issueSingleCertificate(participantId) {
            issueCertificatesRequest([participantId]);
        }

        function revokeCertificate(certificateId) {
            Swal.fire({
                title: 'Revoke Certificate?',
                text: 'This will revoke the issued certificate for this participant.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, revoke!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/documents/certificates/revokeCertificate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            participant_certificate_id: certificateId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Revoked!',
                                text: data.message
                            }).then(() => {
                                // Reload assigned table
                                assignedTable.ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.error || 'Failed to revoke certificate'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Network error occurred'
                        });
                    });
                }
            });
        }

        function issueCertificatesRequest(participantIds = []) {
            const isAll = participantIds.length === 0;
            let selectedIds = participantIds;
            
            if (isAll) {
                selectedIds = $('.eligible-checkbox').map(function() {
                    return parseInt($(this).val());
                }).get();
            }

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Participants',
                    text: 'No eligible participants selected'
                });
                return;
            }

            const countText = isAll ? 'all eligible participants' : `${selectedIds.length} selected participant(s)`;

            Swal.fire({
                title: 'Confirm Certificate Issuance',
                text: `Issue certificates to ${countText}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, issue certificates!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Issuing Certificates...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('/documents/certificates/issueCertificates', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            award_id: awardId,
                            participant_ids: selectedIds
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message
                            }).then(() => {
                                $('#issueCertificatesModal').modal('hide');
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.error || 'Failed to issue certificates'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Network error occurred'
                        });
                    });
                }
            });
        }

        // Certificate issuance button handlers
        $(document).on('click', '#issue-selected-certificates-btn', function() {
            const selectedIds = $('.eligible-checkbox:checked').map(function() {
                return parseInt($(this).val());
            }).get();

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one participant'
                });
                return;
            }

            issueCertificatesRequest(selectedIds);
        });

        $(document).on('click', '#issue-all-certificates-btn', function() {
            issueCertificatesRequest([]);
        });

        // Handle flash messages
        <?php if (session()->getFlashdata('success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= session()->getFlashdata('success') ?>',
                timer: 3000,
                showConfirmButton: false
            });
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?= session()->getFlashdata('error') ?>',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>
    </script>
</body>
</html>
