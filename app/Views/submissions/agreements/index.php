<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Agreement Letters')); ?>

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
    .description-cell {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .modal-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }

    /* Improve table responsiveness */
    .table-responsive.table-card {
        border-radius: 0.25rem;
        box-shadow: 0 1px 2px rgba(56, 65, 74, 0.15);
    }

    #faqs-table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }

    /* Custom modal styles for split layout */
    .modal-xl .modal-body {
        padding: 0;
    }

    .document-viewer {
        background: #f8f9fa;
        position: relative;
    }

    .document-viewer embed {
        border: none;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin: 10px;
        border-radius: 4px;
    }

    /* Right column should fit content naturally */
    .modal-xl .col-md-4 {
        display: flex;
        flex-direction: column;
    }

    .modal-xl .col-md-4 > div:last-child {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    /* Form styling improvements */
    .modal-xl .form-label {
        font-weight: 600;
        color: #495057;
    }

    .modal-xl .form-control:focus {
        border-color: #0ab39c;
        box-shadow: 0 0 0 0.2rem rgba(10, 179, 156, 0.25);
    }

    .modal-xl .btn-primary {
        background-color: #0ab39c;
        border-color: #0ab39c;
    }

    .modal-xl .btn-primary:hover {
        background-color: #089c87;
        border-color: #089c87;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .modal-xl .row.g-0 {
            flex-direction: column;
        }
        
        .modal-xl .col-md-8,
        .modal-xl .col-md-4 {
            max-width: 100%;
        }
        
        .document-viewer {
            height: 400px !important;
        }
    }

    /* Loading animation */
    .rotating {
        animation: rotating 1s linear infinite;
    }

    @keyframes rotating {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
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
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Submissions', 'title' => 'Agreement Letters')); ?>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Agreement Letters</h4>

                                </div>

                                <div class="card-body">

                                    <table id="faqs-table"
                                        class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                        style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Participant Details</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Create At</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($docs)) : ?>
                                            <?php foreach ($docs as $index => $doc) : ?>
                                            <tr data-category="123">
                                                <td><?= $index + 1 ?></td>
                                                <td class="description-cell" data-bs-toggle="tooltip" title="123">
                                                    <?=$doc->full_name?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = '';
                                                    $statusText = '';
                                                    switch(strtolower($doc->status)) {
                                                        case 'accepted':
                                                            $statusClass = 'bg-success-subtle text-success';
                                                            $statusText = 'Accepted';
                                                            break;
                                                        case 'rejected':
                                                            $statusClass = 'bg-danger-subtle text-danger';
                                                            $statusText = 'Rejected';
                                                            break;
                                                        case 'under_review':
                                                            $statusClass = 'bg-warning-subtle text-warning';
                                                            $statusText = 'Under Review';
                                                            break;
                                                        case 'pending':
                                                            $statusClass = 'bg-secondary-subtle text-secondary';
                                                            $statusText = 'Pending';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-primary-subtle text-primary';
                                                            $statusText = ucwords(str_replace('_', ' ', $doc->status));
                                                    }
                                                    ?>
                                                    <span class="badge <?=$statusClass?>">
                                                        <?=$statusText?>
                                                    </span>
                                                </td>
                                                <td> <?= date('M d, Y', strtotime($doc->created_at))?></td>
                                                <!-- <td>
                                                    <?php if ($doc->is_active == 1): ?>
                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                    <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td> -->
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <!-- <div class="view">
                                                            <button type="button" class="btn btn-sm btn-info view-faq"
                                                                data-id="<?= $doc->id ?>" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="View Details">
                                                                <i class="ri-eye-fill"></i>
                                                            </button>
                                                        </div> -->
                                                        <div class="edit">
                                                            <button type="button" class="btn btn-sm btn-info edit-faq"
                                                                data-bs-target="#edit-<?=$doc->id?>"
                                                                data-bs-toggle="modal" data-bs-placement="top"
                                                                title="Review Agreement">
                                                                <i class="ri-file-text-line"></i>
                                                                Review
                                                            </button>
                                                        </div>
                                                        <!-- <div class="remove">
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger remove-faq"
                                                                data-id="<?= $doc->id ?>" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Delete">
                                                                <i class="ri-delete-bin-fill"></i>
                                                            </button>
                                                        </div> -->
                                                        <div class="modal fade" id="edit-<?=$doc->id?>" tabindex="-1"
                                                            role="dialog" aria-labelledby="edit-<?=$doc->id?>"
                                                            aria-hidden="true">
                                                            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title"
                                                                            id="exampleModalScrollableTitle">
                                                                            Agreement Letter - <?=$doc->full_name?>
                                                                        </h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal" aria-label="Close">
                                                                        </button>
                                                                    </div>

                                                                    <div class="modal-body p-0">
                                                                        <div class="row g-0" style="min-height: 70vh;">
                                                                            <!-- Left side - Document viewer -->
                                                                            <div class="col-md-8 border-end">
                                                                                <div class="p-3 bg-light border-bottom">
                                                                                    <h6 class="mb-0">Document Preview</h6>
                                                                                </div>
                                                                                <div class="document-viewer" style="height: 70vh; overflow-y: auto;">
                                                                                    <embed type="application/pdf"
                                                                                        src="<?=$doc->file_url?>" 
                                                                                        width="100%"
                                                                                        height="100%"
                                                                                        style="min-height: 600px;">
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <!-- Right side - Form -->
                                                                            <div class="col-md-4">
                                                                                <div class="p-3 bg-light border-bottom">
                                                                                    <h6 class="mb-0">Review & Update Status</h6>
                                                                                </div>
                                                                                <div class="p-4">
                                                                                    <!-- Participant Info -->
                                                                                    <div class="mb-4">
                                                                                        <h6 class="text-muted mb-2">Participant Information</h6>
                                                                                        <p class="mb-1"><strong>Name:</strong> <?=$doc->full_name?></p>
                                                                                        <p class="mb-1"><strong>Submitted:</strong> <?= date('M d, Y H:i', strtotime($doc->created_at))?></p>
                                                                                        <p class="mb-1"><strong>Current Status:</strong> 
                                                                                            <?php
                                                                                            $statusClass = '';
                                                                                            $statusText = '';
                                                                                            switch(strtolower($doc->status)) {
                                                                                                case 'accepted':
                                                                                                    $statusClass = 'bg-success-subtle text-success';
                                                                                                    $statusText = 'Accepted';
                                                                                                    break;
                                                                                                case 'rejected':
                                                                                                    $statusClass = 'bg-danger-subtle text-danger';
                                                                                                    $statusText = 'Rejected';
                                                                                                    break;
                                                                                                case 'under_review':
                                                                                                    $statusClass = 'bg-warning-subtle text-warning';
                                                                                                    $statusText = 'Under Review';
                                                                                                    break;
                                                                                                case 'pending':
                                                                                                    $statusClass = 'bg-secondary-subtle text-secondary';
                                                                                                    $statusText = 'Pending';
                                                                                                    break;
                                                                                                default:
                                                                                                    $statusClass = 'bg-primary-subtle text-primary';
                                                                                                    $statusText = ucwords(str_replace('_', ' ', $doc->status));
                                                                                            }
                                                                                            ?>
                                                                                            <span class="badge <?=$statusClass?>"><?=$statusText?></span>
                                                                                        </p>
                                                                                    </div>

                                                                                    <hr>

                                                                                    <!-- Status Update Form -->
                                                                                    <form action="<?= base_url('submissions/agreements/update-status') ?>" method="post">
                                                                                        <input type="hidden" value="<?=$doc->id?>" name="id_doc">
                                                                                        
                                                                                        <div class="mb-3">
                                                                                            <label for="status_doc_<?=$doc->id?>" class="form-label">
                                                                                                <i class="ri-checkbox-circle-line me-1"></i>Update Status
                                                                                            </label>
                                                                                            <select class="form-select" name="status_doc" id="status_doc_<?=$doc->id?>">
                                                                                                <option value="<?=$doc->status?>" selected>
                                                                                                    <?php
                                                                                                    switch(strtolower($doc->status)) {
                                                                                                        case 'accepted': echo 'Accepted'; break;
                                                                                                        case 'rejected': echo 'Rejected'; break;
                                                                                                        case 'under_review': echo 'Under Review'; break;
                                                                                                        case 'pending': echo 'Pending'; break;
                                                                                                        default: echo ucwords(str_replace('_', ' ', $doc->status));
                                                                                                    }
                                                                                                    ?>
                                                                                                </option>
                                                                                                <?php if($doc->status != 'pending'): ?>
                                                                                                <option value="pending">Pending</option>
                                                                                                <?php endif; ?>
                                                                                                <?php if($doc->status != 'under_review'): ?>
                                                                                                <option value="under_review">Under Review</option>
                                                                                                <?php endif; ?>
                                                                                                <?php if($doc->status != 'accepted'): ?>
                                                                                                <option value="accepted">Accepted</option>
                                                                                                <?php endif; ?>
                                                                                                <?php if($doc->status != 'rejected'): ?>
                                                                                                <option value="rejected">Rejected</option>
                                                                                                <?php endif; ?>
                                                                                            </select>
                                                                                        </div>
                                                                                        
                                                                                        <div class="mb-4">
                                                                                            <label for="notes_<?=$doc->id?>" class="form-label">
                                                                                                <i class="ri-file-text-line me-1"></i>Review Notes
                                                                                            </label>
                                                                                            <textarea class="form-control" 
                                                                                                id="notes_<?=$doc->id?>"
                                                                                                rows="4"
                                                                                                name="notes"
                                                                                                placeholder="Add your review comments here..."><?=$doc->notes?></textarea>
                                                                                            <div class="form-text">
                                                                                                Provide feedback or reasons for your decision.
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- Action buttons -->
                                                                                        <div class="d-grid gap-2">
                                                                                            <button type="submit" class="btn btn-primary">
                                                                                                <i class="ri-save-line me-1"></i>Save Changes
                                                                                            </button>
                                                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                                                                <i class="ri-close-line me-1"></i>Cancel
                                                                                            </button>
                                                                                        </div>
                                                                                    </form>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div><!-- /.modal-content -->
                                                            </div><!-- /.modal-dialog -->
                                                        </div><!-- /.modal -->
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php else : ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No Docs found</td>
                                            </tr>
                                            <?php endif; ?>
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
    <!-- END layout-wrapper -->

    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

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

        // Ensure jQuery is loaded
        if (typeof jQuery !== 'undefined') {
            console.log("jQuery is loaded");
            initializeAgreementFunctions();
        } else {
            console.error("jQuery is not loaded!");
        }
    });

    function initializeAgreementFunctions() {
        // Initialize DataTable with improved configuration
        var agreementsTable = $('#faqs-table').DataTable({
            responsive: true,
            lengthChange: false,
            pageLength: 10,
            searching: true,
            ordering: true,
            columnDefs: [{
                orderable: false,
                targets: [4] // Action column is not sortable (5th column, index 4)
            }],
            drawCallback: function() {
                $(".dataTables_paginate > .pagination").addClass(
                    "pagination-squared justify-content-end mb-0");
                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                    '[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
            }
        });

        // Category filter functionality
        $('#category-filter').on('change', function() {
            var category = $(this).val();
            if (category === '') {
                agreementsTable.column(3).search('').draw();
            } else {
                agreementsTable.column(3).search(category).draw();
            }
        });

        // Handle agreement review modal
        $(document).on('shown.bs.modal', '[id^="edit-"]', function() {
            var modal = $(this);
            var embed = modal.find('embed');
            
            // Show loading indicator while PDF loads
            var loadingDiv = $('<div class="d-flex justify-content-center align-items-center h-100">' +
                              '<div class="spinner-border text-primary" role="status">' +
                              '<span class="visually-hidden">Loading document...</span>' +
                              '</div></div>');
            
            var documentViewer = modal.find('.document-viewer');
            documentViewer.prepend(loadingDiv);
            
            // Hide loading when embed loads (this might not work perfectly with PDFs)
            embed.on('load', function() {
                loadingDiv.remove();
            });
            
            // Remove loading after a timeout as fallback
            setTimeout(function() {
                loadingDiv.remove();
            }, 3000);
        });

        // Form submission confirmation
        $(document).on('submit', 'form[action*="update-status"]', function(e) {
            var form = $(this);
            var status = form.find('select[name="status_doc"]').val();
            var currentStatus = form.find('select[name="status_doc"] option:first').val();
            
            // Check if form is already confirmed to avoid infinite loop
            if (form.data('confirmed')) {
                return true; // Allow submission
            }
            
            if (status !== currentStatus) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Confirm Status Update',
                    text: `Are you sure you want to change the status to "${status.replace('_', ' ')}"?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0ab39c',
                    cancelButtonColor: '#f06548',
                    confirmButtonText: 'Yes, update it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        var submitBtn = form.find('button[type="submit"]');
                        var originalText = submitBtn.html();
                        submitBtn.html('<i class="ri-loader-2-line me-1 rotating"></i>Updating...').prop('disabled', true);
                        
                        // Mark form as confirmed and submit
                        form.data('confirmed', true);
                        form.submit();
                    }
                });
            }
        });

        // Use event delegation for view button (currently not used for agreements)
        // This can be enabled if needed for viewing agreement details

        // Agreement edit functionality is handled via modal forms in the table

        // Helper function to format date
        function formatDate(dateString) {
            if (!dateString || dateString === 'N/A') return 'N/A';

            var date = new Date(dateString);
            if (isNaN(date)) return 'N/A';

            return date.toLocaleDateString('en-US', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }
    }
    </script>
</body>

</html>
