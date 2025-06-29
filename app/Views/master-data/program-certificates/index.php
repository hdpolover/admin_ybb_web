<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Program Certificates')); ?>
    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <style>
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

        .certificate-template-preview {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            background-color: #f8f9fa;
            margin-top: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
        }

        .template-info {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .table-loading {
            position: relative;
        }

        .table-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
            border-radius: 0.375rem;
        }

        .dataTables_processing {
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            width: auto !important;
            height: auto !important;
            margin: 0 !important;
            border: 0 !important;
            color: #0ab39c !important;
            font-size: 1rem !important;
            background: transparent !important;
        }
    </style>
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Program Certificates')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Program Certificates List</h5>
                                    <div class="flex-shrink-0">
                                        <a href="/master-data/program-certificates/add" class="btn btn-primary waves-effect waves-light me-2">
                                            <i class="ri-add-line align-middle me-1"></i> Add Certificate
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="program-certificates-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Award</th>
                                                <th scope="col">Template Type</th>
                                                <th scope="col">Issue Date</th>
                                                <th scope="col">Published</th>
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
            </div>

            <?= $this->include('partials/footer') ?>
        </div>
    </div>

    <!-- View Certificate Modal -->
    <div class="modal fade" id="view-certificate-modal" tabindex="-1" aria-labelledby="view-certificate-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-loading" id="view-loading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="loading-text">Loading certificate details...</div>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="view-certificate-modal-label">Certificate Template Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Award</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_award_title"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Template Type</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_template_type"></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Issue Date</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_issue_date"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Published Date</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_published_at"></p>
                            </div>
                        </div>
                    </div>
                        </div>
                    </div>

                    <div class="mb-3" id="css_styles_section" style="display: none;">
                        <h5 class="text-muted fw-normal">CSS Styles</h5>
                        <div class="certificate-template-preview">
                            <pre id="view_css_styles" style="white-space: pre-wrap; font-size: 0.875rem;"></pre>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary view-edit-btn">Edit</a>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="/assets/js/app.js"></script>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Check for flash messages
            <?php if (session()->has('success')): ?>
                Swal.fire({
                    title: 'Success!',
                    text: '<?= session('success') ?>',
                    icon: 'success',
                    confirmButtonColor: '#0ab39c',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => {
                    // Refresh the table if it exists
                    if (window.refreshCertificateTable) {
                        window.refreshCertificateTable();
                    }
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

            <?php if (session()->has('warning')): ?>
                Swal.fire({
                    title: 'Warning!',
                    text: '<?= session('warning') ?>',
                    icon: 'warning',
                    confirmButtonColor: '#f7b84b'
                });
            <?php endif; ?>

            if (typeof jQuery !== 'undefined') {
                initializeCertificateFunctions();
            }
        });

        function initializeCertificateFunctions() {
            // Initialize DataTable
            var certificateTable = $('#program-certificates-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true,
                processing: true,
                language: {
                    processing: '<i class="ri-loader-4-line fs-2x text-primary"></i><br><span class="text-muted">Loading certificates...</span>',
                    emptyTable: "No certificate templates found for this program",
                    zeroRecords: "No matching certificate templates found"
                },
                ajax: {
                    url: '/master-data/program-certificates/getData',
                    type: 'GET',
                    dataSrc: function(json) {
                        if (json.success) {
                            return json.data;
                        } else {
                            console.error('Error loading data:', json.message);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load certificate data: ' + (json.message || 'Unknown error'),
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                            return [];
                        }
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTable AJAX error:', error);
                        Swal.fire({
                            title: 'Connection Error!',
                            text: 'Failed to connect to server. Please check your connection and try again.',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                },
                columns: [
                    { 
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { 
                        data: 'award_title',
                        render: function(data, type, row) {
                            return data || 'No Award';
                        }
                    },
                    { 
                        data: 'template_type',
                        render: function(data, type, row) {
                            if (data === 'pdf') {
                                return '<span class="badge bg-danger"><i class="ri-file-pdf-line"></i> PDF</span>';
                            } else if (data === 'image') {
                                return '<span class="badge bg-info"><i class="ri-image-line"></i> Image</span>';
                            }
                            return '<span class="badge bg-secondary">Unknown</span>';
                        }
                    },
                    { 
                        data: 'issue_date',
                        render: function(data, type, row) {
                            return data ? new Date(data).toLocaleDateString() : '-';
                        }
                    },
                    { 
                        data: 'published_at',
                        render: function(data, type, row) {
                            if (data) {
                                return '<span class="badge bg-success">Published</span><br><small class="text-muted">' + new Date(data).toLocaleDateString() + '</small>';
                            }
                            return '<span class="badge bg-warning">Draft</span>';
                        }
                    },
                    {
                        data: 'is_active',
                        render: function(data, type, row) {
                            let badgeClass = data == 1 ? 'bg-success' : 'bg-secondary';
                            let statusText = data == 1 ? 'Active' : 'Inactive';
                            return `<span class="badge ${badgeClass}">${statusText}</span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-info view-certificate" data-id="${row.id}" data-bs-toggle="tooltip" title="View Details">
                                        <i class="ri-eye-fill"></i>
                                    </button>
                                    <a href="/master-data/program-certificates/edit/${row.id}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Edit">
                                        <i class="ri-pencil-fill"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-certificate" data-id="${row.id}" data-bs-toggle="tooltip" title="Delete">
                                        <i class="ri-delete-bin-fill"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [6]
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

            // Make table globally accessible for refresh
            window.certificateTable = certificateTable;

            // Function to refresh the DataTable
            window.refreshCertificateTable = function() {
                if (window.certificateTable) {
                    window.certificateTable.ajax.reload(null, false); // false = keep current page
                }
            };

            // Function to reset and close modals
            window.resetCertificateModals = function() {
                $('#view-certificate-modal').modal('hide');
            };

            // Close modals when success flash message is shown
            <?php if (session()->has('success')): ?>
                setTimeout(() => {
                    window.resetCertificateModals();
                }, 100);
            <?php endif; ?>

            // View certificate
            $(document).on('click', '.view-certificate', function(e) {
                e.preventDefault();
                var certificateId = $(this).data('id');
                
                $('#view-certificate-modal').modal('show');
                $('#view-loading').show();

                $.ajax({
                    url: '/master-data/program-certificates/getCertificate/' + certificateId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            var certificate = response.data;
                            $('#view_award_title').text(certificate.award_title || 'No Award');
                            $('#view_template_type').text(certificate.template_type || 'Unknown');
                            $('#view_issue_date').text(certificate.issue_date ? new Date(certificate.issue_date).toLocaleDateString() : 'Not set');
                            $('#view_published_at').text(certificate.published_at ? new Date(certificate.published_at).toLocaleString() : 'Not published');
                            
                            var statusBadge = certificate.is_active == 1 ?
                                '<span class="badge bg-success">Active</span>' :
                                '<span class="badge bg-secondary">Inactive</span>';
                            $('#view_status').html(statusBadge);

                            if (certificate.css_styles && certificate.css_styles.trim()) {
                                $('#view_css_styles').text(certificate.css_styles);
                                $('#css_styles_section').show();
                            } else {
                                $('#css_styles_section').hide();
                            }
                            
                            $('.view-edit-btn').attr('href', '/master-data/program-certificates/edit/' + certificate.id);
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load certificate details',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                            $('#view-certificate-modal').modal('hide');
                        }
                        $('#view-loading').hide();
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while fetching certificate details',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                        $('#view-certificate-modal').modal('hide');
                        $('#view-loading').hide();
                    }
                });
            });

            // Delete certificate with confirmation
            $(document).on('click', '.delete-certificate', function(e) {
                e.preventDefault();
                var certificateId = $(this).data('id');
                var certificateName = $(this).closest('tr').find('td:nth-child(2)').text();
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete "${certificateName}". This action cannot be undone and may affect participant certificates!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f06548',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Deleting Certificate...',
                            text: 'Please wait while we delete the certificate template.',
                            icon: 'info',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Redirect to delete URL
                        window.location.href = '/master-data/program-certificates/delete/' + certificateId;
                    }
                });
            });

            // Add toast notification function
            window.showToast = function(message, type = 'success') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: type,
                    title: message
                });
            };
        }
    </script>
</body>
</html>
