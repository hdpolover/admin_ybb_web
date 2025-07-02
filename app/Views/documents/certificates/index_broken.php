<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Certificate Management')); ?>
    <?= $this->include('partials/head-css') ?>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Documents', 'title' => 'Certificate Management')); ?>

                    <!-- Statistics Cards -->
                    <div class="row" id="stats-row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Awards</p>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="counter-value" data-target="0" id="total-awards"><?= count($awards ?? []) ?></span></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-primary rounded fs-3">
                                                    <i class="ri-award-line"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Recipients</p>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="counter-value" data-target="0" id="total-recipients"><?= array_sum(array_column($awards ?? [], 'participants_count')) ?></span></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-success rounded fs-3">
                                                    <i class="ri-group-line"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Certificates Issued</p>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="counter-value" data-target="0" id="total-issued"><?= array_sum(array_column($awards ?? [], 'certificates_issued')) ?></span></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-info rounded fs-3">
                                                    <i class="ri-file-text-line"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Completion Rate</p>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <?php 
                                                    $totalRecipients = array_sum(array_column($awards ?? [], 'participants_count'));
                                                    $totalIssued = array_sum(array_column($awards ?? [], 'certificates_issued'));
                                                    $completionRate = $totalRecipients > 0 ? round(($totalIssued / $totalRecipients) * 100) : 0;
                                                    ?>
                                                    <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="counter-value" data-target="0" id="completion-rate"><?= $completionRate ?></span>%</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-warning rounded fs-3">
                                                    <i class="ri-percent-line"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Awards & Certificate Management</h4>
                                    <div class="flex-shrink-0">
                                        <button type="button" class="btn btn-primary" onclick="refreshData()">
                                            <i class="ri-refresh-line align-bottom me-1"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="certificates-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Award</th>
                                                    <th>Type</th>
                                                    <th>Description</th>
                                                    <th>Recipients</th>
                                                    <th>Progress</th>
                                                    <th>Certificate Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($awards)): ?>
                                                    <?php foreach ($awards as $award): ?>
                                                        <?php
                                                        $progressPercent = $award->participants_count > 0 
                                                            ? round(($award->certificates_issued / $award->participants_count) * 100, 1) 
                                                            : 0;
                                                        $progressText = $award->participants_count > 0 
                                                            ? "{$award->certificates_issued} / {$award->participants_count}" 
                                                            : "0 / 0";
                                                        $certificateStatus = $award->has_certificate_template 
                                                            ? '<span class="badge bg-success">Available</span>' 
                                                            : '<span class="badge bg-warning">No Template</span>';
                                                        ?>
                                                        <tr>
                                                            <td><?= esc($award->title) ?></td>
                                                            <td><?= ucfirst(str_replace('_', ' ', $award->award_type)) ?></td>
                                                            <td><?= esc($award->description) ?></td>
                                                            <td class="text-center"><?= $award->participants_count ?></td>
                                                            <td>
                                                                <div class="progress" style="height: 20px;">
                                                                    <div class="progress-bar" role="progressbar" style="width: <?= $progressPercent ?>%;" aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100">
                                                                        <?= $progressText ?>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-center"><?= $certificateStatus ?></td>
                                                            <td class="text-center">
                                                                <div class="btn-group" role="group">
                                                                    <button type="button" class="btn btn-primary btn-sm" onclick="viewAwardDetails(<?= $award->id ?>)" title="View Details">
                                                                        <i class="ri-eye-line"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-success btn-sm" onclick="manageParticipants(<?= $award->id ?>)" title="Manage Participants">
                                                                        <i class="ri-group-line"></i>
                                                                    </button>
                                                                    <?php if ($award->has_certificate_template): ?>
                                                                        <button type="button" class="btn btn-info btn-sm" onclick="issueCertificates(<?= $award->id ?>)" title="Issue Certificates">
                                                                            <i class="ri-award-line"></i>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
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

    <!-- Award Details Modal -->
    <div class="modal fade" id="awardDetailsModal" tabindex="-1" aria-labelledby="awardDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="awardDetailsModalLabel">Award Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="award-details-content">
                        <!-- Content loaded via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Participants Modal -->
    <div class="modal fade" id="manageParticipantsModal" tabindex="-1" aria-labelledby="manageParticipantsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageParticipantsModalLabel">Manage Participants</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="manage-participants-content">
                        <!-- Content loaded via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="assign-participants-btn">Assign Selected</button>
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
                        <!-- Content loaded via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="issue-certificates-btn">Issue to Selected</button>
                    <button type="button" class="btn btn-primary" id="issue-all-certificates-btn">Issue to All</button>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>

    <!-- DataTables JS -->
    <script src="/assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <script>
        let certificatesTable;
        let currentAwardId = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            initializeDataTable();
            initializeEventHandlers();
        });

        function initializeDataTable() {
            // Initialize simple DataTable (like program payments)
            certificatesTable = $('#certificates-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [4, 5, 6] }, // Progress, Status, Actions columns
                    { searchable: false, targets: [6] }, // Actions column
                    { className: 'text-center', targets: [3, 5, 6] } // Center align specific columns
                ],
                language: {
                    emptyTable: "No awards found for this program"
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-squared justify-content-end mb-0");
                }
            });
        }
                            $('#certificates-table tbody').append(row);
                        });
                        
                        // Now initialize DataTable on the populated table
                        console.log('� Initializing DataTable on populated table...');
                        
                        // Destroy any existing DataTable
                        if (certificatesTable) {
                            certificatesTable.destroy();
                        }
                        
                        certificatesTable = $('#certificates-table').DataTable({
                            order: [[0, 'asc']],
                            responsive: true,
                            pageLength: 10,
                            language: {
                                emptyTable: "No awards found for this program"
                            },
                            columnDefs: [
                                { orderable: false, targets: [4, 5, 6] }, // Progress, Status, Actions columns
                                { searchable: false, targets: [6] }, // Actions column
                                { className: 'text-center', targets: [3, 5, 6] } // Center align specific columns
                            ],
                            drawCallback: function(settings) {
                                console.log('� DataTable draw callback - rows displayed:', settings.fnRecordsDisplay());
                            }
                        });
                        
                        console.log('✅ DataTable initialized successfully');
                        updateStatistics(response.data);
                        
                    } else {
                        console.warn('⚠️ No data found, initializing empty DataTable');
                        
                        // Initialize empty DataTable
                        certificatesTable = $('#certificates-table').DataTable({
                            order: [[0, 'asc']],
                            responsive: true,
                            pageLength: 10,
                            language: {
                                emptyTable: "No awards found for this program"
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Failed to load initial data:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error,
                        responseText: xhr.responseText
                    });
                    
                    // Initialize empty DataTable even on error
                    certificatesTable = $('#certificates-table').DataTable({
                        order: [[0, 'asc']],
                        responsive: true,
                        pageLength: 10,
                        language: {
                            emptyTable: "Failed to load awards data"
                        }
                    });
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Load Error',
                        text: 'Failed to load certificate data: ' + error
                    });
                }
            });
        }

        function initializeEventHandlers() {
            // Assign participants button
            $('#assign-participants-btn').on('click', function() {
                assignParticipants();
            });

            // Issue certificates buttons
            $('#issue-certificates-btn').on('click', function() {
                issueCertificatesSelected();
            });

            $('#issue-all-certificates-btn').on('click', function() {
                issueCertificatesAll();
            });
        }

        function loadStatistics() {
            // This will be populated when the DataTable loads
            certificatesTable.on('xhr.dt', function(e, settings, json) {
                if (json.data) {
                    updateStatistics(json.data);
                }
            });
        }

        function updateStatistics(data) {
            const totalAwards = data.length;
            const totalRecipients = data.reduce((sum, item) => sum + parseInt(item.participants_count), 0);
            const totalIssued = data.reduce((sum, item) => sum + parseInt(item.certificates_issued), 0);
            const completionRate = totalRecipients > 0 ? Math.round((totalIssued / totalRecipients) * 100) : 0;

            document.getElementById('total-awards').textContent = totalAwards;
            document.getElementById('total-recipients').textContent = totalRecipients;
            document.getElementById('total-issued').textContent = totalIssued;
            document.getElementById('completion-rate').textContent = completionRate;
        }

        function refreshData() {
            certificatesTable.ajax.reload();
            Swal.fire({
                icon: 'success',
                title: 'Refreshed!',
                text: 'Data has been refreshed',
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }

        function viewAwardDetails(awardId) {
            currentAwardId = awardId;
            
            // Show loading
            $('#award-details-content').html('<div class="text-center"><i class="ri-loader-2-line fs-1 text-muted"></i><p>Loading award details...</p></div>');
            $('#awardDetailsModal').modal('show');

            // Load award details
            fetch(`/documents/certificates/getAwardDetails/${awardId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    renderAwardDetails(data);
                })
                .catch(error => {
                    $('#award-details-content').html('<div class="alert alert-danger">Error loading award details: ' + error.message + '</div>');
                });
        }

        function renderAwardDetails(data) {
            const { award, participants, certificates } = data;
            
            let html = `
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="fw-bold">Award Information</h6>
                        <table class="table table-borderless">
                            <tr><td class="fw-medium">Title:</td><td>${award.title}</td></tr>
                            <tr><td class="fw-medium">Type:</td><td>${award.award_type.replace('_', ' ').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')}</td></tr>
                            <tr><td class="fw-medium">Description:</td><td>${award.description || 'No description'}</td></tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Certificate Templates (${certificates.length})</h6>
                        ${certificates.length > 0 ? `
                            <div class="list-group">
                                ${certificates.map(cert => `
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">${cert.template_type.toUpperCase()} Template</h6>
                                            <span class="badge bg-${cert.is_active ? 'success' : 'secondary'}">${cert.is_active ? 'Active' : 'Inactive'}</span>
                                        </div>
                                        <small>Created: ${new Date(cert.created_at).toLocaleDateString()}</small>
                                    </div>
                                `).join('')}
                            </div>
                        ` : '<p class="text-muted">No certificate templates found</p>'}
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="fw-bold">Assigned Participants (${participants.length})</h6>
                        ${participants.length > 0 ? `
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Account ID</th>
                                            <th>Certificate</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${participants.map(p => `
                                            <tr>
                                                <td>${p.full_name}</td>
                                                <td>${p.account_id}</td>
                                                <td>
                                                    ${p.certificate_id ? 
                                                        '<span class="badge bg-success">Issued</span>' : 
                                                        '<span class="badge bg-warning">Pending</span>'
                                                    }
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="removeParticipantFromAward(${p.id})" title="Remove">
                                                        <i class="ri-close-line"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        ` : '<p class="text-muted">No participants assigned</p>'}
                    </div>
                </div>
            `;

            $('#award-details-content').html(html);
        }

        function manageParticipants(awardId) {
            currentAwardId = awardId;
            
            // Show loading
            $('#manage-participants-content').html('<div class="text-center"><i class="ri-loader-2-line fs-1 text-muted"></i><p>Loading participants...</p></div>');
            $('#manageParticipantsModal').modal('show');

            // Load available participants
            fetch(`/documents/certificates/getAvailableParticipants/${awardId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    renderParticipantManagement(data.participants);
                })
                .catch(error => {
                    $('#manage-participants-content').html('<div class="alert alert-danger">Error loading participants: ' + error.message + '</div>');
                });
        }

        function renderParticipantManagement(participants) {
            let html = `
                <div class="mb-3">
                    <label for="participant-search" class="form-label">Search Participants</label>
                    <input type="text" class="form-control" id="participant-search" placeholder="Search by name or account ID...">
                </div>
                
                <div class="mb-3">
                    <label for="assignment-notes" class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" id="assignment-notes" rows="2" placeholder="Add notes for this assignment..."></textarea>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="select-all-participants">
                    <label class="form-check-label" for="select-all-participants">
                        Select All Available Participants
                    </label>
                </div>

                <div style="max-height: 300px; overflow-y: auto;">
                    ${participants.length > 0 ? `
                        <div class="row" id="participants-list">
                            ${participants.map(p => `
                                <div class="col-md-6 mb-2 participant-item" data-name="${p.full_name.toLowerCase()}" data-account="${p.account_id.toLowerCase()}">
                                    <div class="form-check">
                                        <input class="form-check-input participant-checkbox" type="checkbox" value="${p.id}" id="participant-${p.id}">
                                        <label class="form-check-label" for="participant-${p.id}">
                                            <strong>${p.full_name}</strong><br>
                                            <small class="text-muted">${p.account_id} • ${p.email}</small>
                                        </label>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    ` : '<p class="text-muted">No available participants found</p>'}
                </div>
            `;

            $('#manage-participants-content').html(html);

            // Initialize search functionality
            $('#participant-search').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('.participant-item').each(function() {
                    const name = $(this).data('name');
                    const account = $(this).data('account');
                    if (name.includes(searchTerm) || account.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Initialize select all functionality
            $('#select-all-participants').on('change', function() {
                $('.participant-checkbox:visible').prop('checked', $(this).is(':checked'));
            });
        }

        function assignParticipants() {
            const selectedParticipants = $('.participant-checkbox:checked').map(function() {
                return parseInt($(this).val());
            }).get();

            if (selectedParticipants.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one participant to assign'
                });
                return;
            }

            const notes = $('#assignment-notes').val();

            Swal.fire({
                title: 'Confirm Assignment',
                text: `Assign ${selectedParticipants.length} participant(s) to this award?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, assign them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Assigning Participants...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('/documents/certificates/assignParticipants', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            award_id: currentAwardId,
                            participant_ids: selectedParticipants,
                            notes: notes
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message
                            });
                            $('#manageParticipantsModal').modal('hide');
                            certificatesTable.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.error || 'Failed to assign participants'
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

        function issueCertificates(awardId) {
            currentAwardId = awardId;
            
            // Show loading
            $('#issue-certificates-content').html('<div class="text-center"><i class="ri-loader-2-line fs-1 text-muted"></i><p>Loading award participants...</p></div>');
            $('#issueCertificatesModal').modal('show');

            // Load award details
            fetch(`/documents/certificates/getAwardDetails/${awardId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    renderCertificateIssuance(data);
                })
                .catch(error => {
                    $('#issue-certificates-content').html('<div class="alert alert-danger">Error loading participants: ' + error.message + '</div>');
                });
        }

        function renderCertificateIssuance(data) {
            const { award, participants, certificates } = data;
            
            if (certificates.length === 0) {
                $('#issue-certificates-content').html(`
                    <div class="alert alert-warning">
                        <h6>No Certificate Template</h6>
                        <p>This award does not have a certificate template. Please create one in the Master Data section before issuing certificates.</p>
                    </div>
                `);
                $('#issue-certificates-btn, #issue-all-certificates-btn').prop('disabled', true);
                return;
            }

            const eligibleParticipants = participants.filter(p => !p.certificate_id);
            const issuedParticipants = participants.filter(p => p.certificate_id);

            let html = `
                <div class="alert alert-info">
                    <h6>${award.title}</h6>
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
                                <div>
                                    <small class="text-muted">Issued: ${new Date(p.generated_at).toLocaleDateString()}</small>
                                    <button class="btn btn-sm btn-outline-danger ms-2" onclick="revokeCertificate(${p.certificate_id})" title="Revoke Certificate">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                ` : ''}
            `;

            $('#issue-certificates-content').html(html);

            // Enable/disable buttons based on available participants
            $('#issue-certificates-btn').prop('disabled', eligibleParticipants.length === 0);
            $('#issue-all-certificates-btn').prop('disabled', eligibleParticipants.length === 0);

            // Initialize select all functionality
            $('#select-all-eligible').on('change', function() {
                $('.eligible-checkbox').prop('checked', $(this).is(':checked'));
            });
        }

        function issueCertificatesSelected() {
            const selectedParticipants = $('.eligible-checkbox:checked').map(function() {
                return parseInt($(this).val());
            }).get();

            if (selectedParticipants.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one participant'
                });
                return;
            }

            issueCertificatesRequest(selectedParticipants);
        }

        function issueCertificatesAll() {
            const allEligible = $('.eligible-checkbox').map(function() {
                return parseInt($(this).val());
            }).get();

            if (allEligible.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Participants',
                    text: 'All participants already have certificates'
                });
                return;
            }

            issueCertificatesRequest([]);
        }

        function issueCertificatesRequest(participantIds = []) {
            const isAll = participantIds.length === 0;
            const countText = isAll ? 'all eligible participants' : `${participantIds.length} selected participant(s)`;

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
                            award_id: currentAwardId,
                            participant_ids: participantIds
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message
                            });
                            $('#issueCertificatesModal').modal('hide');
                            certificatesTable.ajax.reload();
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
                            });
                            viewAwardDetails(currentAwardId); // Refresh the modal
                            certificatesTable.ajax.reload();
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

        function revokeCertificate(participantCertificateId) {
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
                            participant_certificate_id: participantCertificateId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Revoked!',
                                text: data.message
                            });
                            issueCertificates(currentAwardId); // Refresh the modal
                            certificatesTable.ajax.reload();
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

        // Test functions for debugging
        function testAjaxConnection() {
            console.log('🔧 Testing AJAX connection...');
            
            $.ajax({
                url: '<?= base_url('documents/certificates/getData') ?>',
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    console.log('📤 Sending test request to:', '<?= base_url('documents/certificates/getData') ?>');
                },
                success: function(response) {
            // Use DataTable's built-in AJAX for loading data, like master data program payments
            certificatesTable = $('#certificates-table').DataTable({
                ajax: {
                    url: '<?= base_url('documents/certificates/getData') ?>',
                    dataSrc: 'data',
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Load Error',
                            text: 'Failed to load certificate data: ' + error
                        });
                    }
                },
                order: [[0, 'asc']],
                responsive: true,
                pageLength: 10,
                language: {
                    emptyTable: "No awards found for this program"
                },
                columnDefs: [
                    { orderable: false, targets: [4, 5, 6] },
                    { searchable: false, targets: [6] },
                    { className: 'text-center', targets: [3, 5, 6] }
                ],
                drawCallback: function(settings) {
                    // Optionally, re-initialize tooltips or other UI here
                }
            });
            // Update statistics after table loads
            certificatesTable.on('xhr.dt', function(e, settings, json) {
                if (json && json.data) {
                    updateStatistics(json.data);
                }
            });
        }

        function refreshData() {
            console.log('🔄 Refreshing data...');
            
            // Load fresh data from server
            $.ajax({
                url: '<?= base_url('documents/certificates/getData') ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('🔄 Refresh data success:', response);
                    
                    if (response.data && response.data.length > 0) {
                        // Destroy existing DataTable
                        if (certificatesTable) {
                            certificatesTable.destroy();
                        }
                        
                        // Clear and repopulate table
                        $('#certificates-table tbody').empty();
                        
                        response.data.forEach(award => {
                            const row = `
                                <tr>
                                    <td>${award.title || 'N/A'}</td>
                                    <td>${award.award_type || 'N/A'}</td>
                                    <td>${award.description || 'No description'}</td>
                                    <td class="text-center">${award.participants_count || '0'}</td>
                                    <td>${award.progress || 'N/A'}</td>
                                    <td class="text-center">${award.certificate_status || 'N/A'}</td>
                                    <td class="text-center">${award.actions || ''}</td>
                                </tr>
                            `;
                            $('#certificates-table tbody').append(row);
                        });
                        
                        // Reinitialize DataTable
                        certificatesTable = $('#certificates-table').DataTable({
                            order: [[0, 'asc']],
                            responsive: true,
                            pageLength: 10,
                            language: {
                                emptyTable: "No awards found for this program"
                            },
                            columnDefs: [
                                { orderable: false, targets: [4, 5, 6] },
                                { searchable: false, targets: [6] },
                                { className: 'text-center', targets: [3, 5, 6] }
                            ]
                        });
                        
                        updateStatistics(response.data);
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Refreshed!',
                            text: 'Data has been refreshed',
                            timer: 1500,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    } else {
                        console.warn('⚠️ No data in refresh response');
                        Swal.fire({
                            icon: 'info',
                            title: 'No Data',
                            text: 'No awards found for this program',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Refresh failed:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Refresh Failed',
                        text: 'Failed to refresh data: ' + error,
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            });
        }

        // Handle flash messages with SweetAlert
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