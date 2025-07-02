<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Certificate Management')); ?>
    <?= $this->include('partials/head-css') ?>
    
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

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
                                        <button type="button" class="btn btn-secondary btn-sm me-2" onclick="toggleDebug()">
                                            <i class="ri-bug-line align-bottom me-1"></i> Debug
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="refreshData()">
                                            <i class="ri-refresh-line align-bottom me-1"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Debug Information -->
                                    <div class="alert alert-info d-none" id="debug-info">
                                        <strong>Debug Info:</strong><br>
                                        Program ID: <?= $programId ?? 'Not set' ?><br>
                                        Awards Count: <?= count($awards ?? []) ?><br>
                                        Awards Data: <pre><?= isset($awards) ? print_r(array_slice($awards, 0, 2), true) : 'No awards data' ?></pre>
                                    </div>
                                    
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
                                                                    <div class="progress-bar bg-<?= $progressPercent == 100 ? 'success' : ($progressPercent > 0 ? 'warning' : 'secondary') ?>" 
                                                                         role="progressbar" 
                                                                         style="width: <?= $progressPercent ?>%" 
                                                                         aria-valuenow="<?= $progressPercent ?>" 
                                                                         aria-valuemin="0" 
                                                                         aria-valuemax="100">
                                                                        <?= $progressText ?>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-center"><?= $certificateStatus ?></td>
                                                            <td class="text-center">
                                                                <div class="d-flex gap-2 justify-content-center">
                                                                    <div class="view">
                                                                        <a href="/documents/certificates/view/<?= $award->id ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                                                            <i class="ri-eye-fill"></i>
                                                                        </a>
                                                                    </div>
                                                                    <div class="manage">
                                                                        <a href="/documents/certificates/view/<?= $award->id ?>" class="btn btn-sm btn-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Manage Participants">
                                                                            <i class="ri-group-line"></i>
                                                                        </a>
                                                                    </div>
                                                                    <?php if ($award->has_certificate_template): ?>
                                                                        <div class="issue">
                                                                            <a href="/documents/certificates/view/<?= $award->id ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Issue Certificates">
                                                                                <i class="ri-award-line"></i>
                                                                            </a>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center py-4">
                                                            <div class="d-flex flex-column align-items-center">
                                                                <i class="ri-award-line display-1 text-muted mb-2"></i>
                                                                <h5 class="text-muted">No Awards Found</h5>
                                                                <p class="text-muted mb-0">No awards have been configured for this program yet.</p>
                                                            </div>
                                                        </td>
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

    <?= $this->include('partials/customizer') ?>
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
            console.log("DOM loaded for certificates");

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
                console.log("jQuery is loaded, version:", jQuery.fn.jquery);
                initializeCertificatesFunctions();
            } else {
                console.error("jQuery is not loaded!");
            }
        });

        function initializeCertificatesFunctions() {
            console.log("Initializing certificates DataTable...");
            
            // Check if table exists
            var tableElement = $('#certificates-table');
            if (tableElement.length === 0) {
                console.error("Table #certificates-table not found!");
                return;
            }
            
            console.log("Table found, rows count:", tableElement.find('tbody tr').length);

            // Initialize DataTable exactly like program payments
            try {
                var certificatesTable = $('#certificates-table').DataTable({
                    responsive: true,
                    lengthChange: false,
                    pageLength: 10,
                    searching: true,
                    ordering: true,
                    columnDefs: [{
                        orderable: false,
                        targets: [4, 5, 6] // Progress, Status, Actions columns are not sortable
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
                
                console.log("DataTable initialized successfully");
            } catch (error) {
                console.error("Error initializing DataTable:", error);
            }
        }

        function refreshData() {
            console.log("Refreshing data...");
            location.reload();
        }

        function toggleDebug() {
            var debugInfo = document.getElementById('debug-info');
            if (debugInfo.classList.contains('d-none')) {
                debugInfo.classList.remove('d-none');
            } else {
                debugInfo.classList.add('d-none');
            }
        }

        function viewAwardDetails(awardId) {
            console.log("Viewing award details for ID:", awardId);
            window.location.href = `/documents/certificates/view/${awardId}`;
        }

        function manageParticipants(awardId) {
            console.log("Managing participants for award ID:", awardId);
            window.location.href = `/documents/certificates/view/${awardId}`;
        }

        function issueCertificates(awardId) {
            console.log("Issuing certificates for award ID:", awardId);
            window.location.href = `/documents/certificates/view/${awardId}`;
        }
    </script>
    </script>
</body>
</html>
