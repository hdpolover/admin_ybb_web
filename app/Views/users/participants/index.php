<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Participants')); ?>

    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- Date Range Picker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <!-- Custom CSS for participant table -->
    <style>
        .payment-status-container,
        .submission-status-container {
            max-width: 200px;
            font-size: 0.85rem;
        }

        .payment-status-container .badge,
        .submission-status-container .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        #participants-datatable td {
            vertical-align: middle;
        }

        /* Enhanced Export SweetAlert Styles */
        .export-success-popup .export-success-details {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .export-success-popup .export-stats .stat-item {
            padding: 10px;
            border-radius: 8px;
            background: linear-gradient(145deg, #f8f9fa, #e9ecef);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .export-success-popup .export-stats .stat-item:hover {
            transform: translateY(-2px);
        }

        /* Enhanced Metrics Styling */
        .export-success-popup .enhanced-metrics .metric-card {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }

        .export-success-popup .enhanced-metrics .metric-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
            border-color: #adb5bd;
        }

        .export-success-popup .enhanced-metrics .metric-value {
            font-weight: 600;
            font-size: 16px;
        }

        .export-success-popup .enhanced-metrics .metric-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .export-success-popup .export-metadata .metadata-item {
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #28a745;
            margin-bottom: 8px;
        }

        .export-completion-popup .completion-stats .stat-badge {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .export-completion-popup .completion-stats .stat-badge:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        /* Enhanced Metrics for Completion Popup */
        .export-completion-popup .metric-badge {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        }

        .export-completion-popup .metric-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.18);
        }

        .export-completion-popup .metric-number {
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .export-completion-popup .completion-metadata .metadata-row {
            padding: 6px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .export-completion-popup .completion-metadata .metadata-row:last-child {
            border-bottom: none;
        }

        .export-completion-popup .completion-icon {
            animation: bounceIn 0.8s ease-out;
        }

        /* Enhanced Metrics for Export Result Tables */
        .export-result .enhanced-metrics-row .metric-card {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .export-result .enhanced-metrics-row .metric-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(0,0,0,0.12);
            border-color: #adb5bd;
        }

        .export-result .enhanced-metrics-row .metric-value {
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .export-result .enhanced-metrics-row .metric-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Loading SweetAlert enhancement */
        .swal2-loading .swal2-progress-bar {
            background: linear-gradient(90deg, #28a745, #20c997, #17a2b8);
            animation: progressShimmer 2s linear infinite;
        }

        @keyframes progressShimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Users', 'title' => 'Participants')); ?>

                    <!-- Participant Stats -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-primary rounded-circle fs-3">
                                                <i class="ri-user-line text-primary"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total Participants</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $stats->total ?? 0 ?></h4>
                                            <p class="text-muted mb-0">
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ri-arrow-up-line align-bottom"></i> <?= $stats->recent ?? 0 ?>
                                                </span> new in last 30 days
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded-circle fs-3">
                                                <i class="ri-check-double-line text-success"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Fully Funded</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $stats->category_counts['fully_funded'] ?? 0 ?></h4>
                                            <p class="text-muted mb-0">
                                                <?= $stats->total > 0 ?
                                                    number_format(($stats->category_counts['fully_funded'] / $stats->total) * 100, 1) . '%'
                                                    : '0%' ?>
                                                of participants
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-warning rounded-circle fs-3">
                                                <i class="ri-time-line text-warning"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Self Funded</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $stats->category_counts['self_funded'] ?? 0 ?></h4>
                                            <p class="text-muted mb-0">
                                                <?= $stats->total > 0 ?
                                                    number_format(($stats->category_counts['self_funded'] / $stats->total) * 100, 1) . '%'
                                                    : '0%' ?>
                                                of participants
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Participants Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">All Participants</h5>
                                    <div class="flex-shrink-0">
                                        <button type="button" class="btn btn-success waves-effect waves-light me-2" data-bs-toggle="modal" data-bs-target="#exportModal">
                                            <i class="ri-file-excel-2-line align-middle me-1"></i> Export Data
                                        </button>
                                        <button type="button" class="btn btn-outline-info waves-effect waves-light me-2" onclick="showExportHistory()" title="View Export History">
                                            <i class="ri-history-line align-middle me-1"></i> Export History
                                        </button>
                                        <a href="<?= site_url('participants/new') ?>" class="btn btn-primary waves-effect waves-light">
                                            <i class="ri-add-line align-middle me-1"></i> Add New Participant
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Filter Controls -->
                                    <div class="row mb-4">
                                        <div class="col-md-12 mb-3">
                                            <div class="input-group search-box">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="ri-search-line text-muted"></i>
                                                </span>
                                                <input type="text" id="search-box" class="form-control border-start-0 ps-0"
                                                    placeholder="Search by name, email, account ID, nationality..."
                                                    autocomplete="off">
                                                <button class="btn btn-primary" id="search-button" type="button">
                                                    <i class="ri-search-line me-1"></i> Search
                                                </button>
                                            </div>
                                            <div class="form-text text-muted mt-1">
                                                <small>Press Enter or click Search to filter results</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Category</label>
                                            <select id="filter-category" class="form-select">
                                                <option value="">All Categories</option>
                                                <option value="fully_funded">Fully Funded</option>
                                                <option value="self_funded">Self Funded</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Form Status</label>
                                            <select id="filter-form-status" class="form-select">
                                                <option value="">All Statuses</option>
                                                <option value="0">Not Started</option>
                                                <option value="1">On Progress</option>
                                                <option value="2">Submitted</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end mb-2">
                                            <button id="apply-filters" class="btn btn-primary me-2">Apply Filters</button>
                                            <button id="reset-filters" class="btn btn-light">Reset</button>
                                        </div>
                                    </div>
                                    <table id="participants-datatable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Account ID</th>
                                                <th>Participant Details</th>
                                                <th>Submission Status</th>
                                                <th>Registered On</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- DataTable will populate this -->
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>

    <!-- Date Range Picker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script src="/assets/js/pages/datatables.init.js"></script>
    <script src="/assets/js/app.js"></script>
    
    <!-- Enhanced Export Manager with Nuclear Interval Clearing -->
    <script src="/assets/js/enhanced-export-manager.js?v=<?= time() ?>&bust=<?= uniqid() ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            var participantsTable = $('#participants-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= site_url('users/participants/getData') ?>',
                    type: 'GET',
                    data: function(d) {
                        // Add current program ID - CRITICAL for proper filtering
                        d.program_id = '<?= session('current_program') ?>';
                        
                        // Debug logging
                        console.log('DataTable AJAX request data:', {
                            program_id: d.program_id,
                            category: $('#filter-category').val(),
                            form_status: $('#filter-form-status').val(),
                            search: $('#search-box').val()
                        });
                        
                        // Add filter parameters
                        d.category = $('#filter-category').val();
                        d.form_status = $('#filter-form-status').val();
                        d.search.value = $('#search-box').val();
                        return d;
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTable AJAX Error:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            error: error,
                            code: code,
                            response: xhr.responseText
                        });
                    }
                },
                columns: [{
                        data: 'order_number',
                        width: "5%"
                    }, {
                        data: 'account_id',
                        width: "10%",
                        render: function(data, type, row) {
                            if (!data || type === 'sort' || type === 'type') return data;
                            return '<div class="text-truncate" style="max-width: 120px;" title="' + data + '">' + data + '</div>';
                        }
                    }, {
                        data: 'participant_details',
                        width: "35%",
                        render: function(data, type, row) {
                            if (!data) return 'N/A';
                            let html = '<div class="d-flex align-items-center">';

                            // Avatar display - either picture or placeholder
                            html += '<div class="avatar-xs me-2">';
                            if (data.picture_url && data.picture_url !== '' && data.picture_url !== 'null') {
                                html += '<img src="' + data.picture_url + '" alt="' + data.full_name + '" class="avatar-xs rounded-circle" />';
                            } else {
                                html += '<span class="avatar-title rounded-circle bg-soft-primary text-primary">' +
                                    (data.full_name ? data.full_name.charAt(0).toUpperCase() : '?') + '</span>';
                            }
                            html += '</div>';

                            // Participant info
                            html += '<div>';
                            html += '<h5 class="fs-14 mb-1">' + data.full_name + '</h5>';
                            html += '<p class="text-muted mb-0">' + data.email + '</p>';
                            if (data.nationality && data.nationality !== 'N/A') {
                                html += '<span class="badge bg-light text-dark">' + data.nationality + '</span>';
                            }
                            html += '</div>';
                            html += '</div>';
                            return html;
                        }
                    },
                    {
                        data: 'submission_status',
                        width: "20%"
                    },
                    {
                        data: 'registered_on',
                        width: "15%"
                    },
                    {
                        data: 'actions',
                        width: "15%",
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [4, 'desc'] // Order by registration date (descending)
                ],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                responsive: true
            });

            // Hide DataTables default search box
            $('.dataTables_filter').hide();

            // Function to perform the search
            function performSearch() {
                var searchTerm = $('#search-box').val();
                participantsTable.ajax.reload();
            }

            // Search when Enter is pressed in the search box
            $('#search-box').on('keypress', function(e) {
                if (e.which === 13) { // Enter key pressed
                    e.preventDefault();
                    performSearch();
                }
            });

            // Search when the search button is clicked
            $(document).on('click', '#search-button', function() {
                performSearch();
            });

            // Handle filter buttons
            document.getElementById('apply-filters').addEventListener('click', function() {
                participantsTable.ajax.reload();
            });

            document.getElementById('reset-filters').addEventListener('click', function() {
                // Reset all filter select values
                document.getElementById('filter-category').value = '';
                document.getElementById('filter-form-status').value = '';
                document.getElementById('search-box').value = '';

                // Reload the table with reset filters
                participantsTable.search('').draw();
                participantsTable.ajax.reload();
            });

            // Handle delete participant
            $(document).on('click', '.delete-participant', function() {
                var participantId = $(this).data('id');

                if (confirm('Are you sure you want to delete this participant?')) {
                    $.ajax({
                        url: '<?= base_url('participants/delete/') ?>' + participantId,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('Participant deleted successfully');
                                participantsTable.ajax.reload();
                            } else {
                                alert('Failed to delete participant: ' + (response.message || 'Unknown error'));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            alert('An error occurred while trying to delete the participant');
                        }
                    });
                }
            });

            // Initialize Date Range Picker
            $('#export-date-range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            $('#export-date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                updateExportSummary();
            });

            $('#export-date-range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                updateExportSummary();
            });

            // Enhanced Export Manager Integration - Convert button to work with EnhancedExportManager
            function initializeEnhancedExport() {
                if (typeof EnhancedExportManager !== 'undefined') {
                    console.log('Enhanced Export Manager class available, initializing...');
                    
                    // Initialize the export manager
                    window.enhancedExportManager = new EnhancedExportManager();
                    window.exportManager = window.enhancedExportManager; // Add alias for compatibility
                    console.log('Enhanced Export Manager initialized:', window.enhancedExportManager);
                    
                    // Setup export button with retry logic
                    setTimeout(() => {
                        setupExportButton();
                    }, 100); // Give DOM time to settle
                    
                } else {
                    console.log('Enhanced Export Manager not yet available, retrying in 100ms...');
                    setTimeout(initializeEnhancedExport, 100);
                }
            }
            
            function setupExportButton() {
                console.log('🔧 Setting up export button...');
                const exportBtn = document.getElementById('btn-do-export');
                
                if (exportBtn) {
                    console.log('✅ Found export button:', exportBtn);
                    
                    // Clear any existing classes and re-add them
                    exportBtn.classList.remove('export-btn');
                    exportBtn.classList.add('export-btn');
                    exportBtn.setAttribute('data-export-type', 'participants');
                    exportBtn.setAttribute('data-url', '/users/participants/export');
                    exportBtn.setAttribute('data-form-selector', '#exportForm');
                    
                    console.log('🔧 Button classes after setup:', exportBtn.className);
                    console.log('🔧 Button data attributes:', {
                        exportType: exportBtn.dataset.exportType,
                        url: exportBtn.dataset.url,
                        formSelector: exportBtn.dataset.formSelector
                    });
                    
                    // Force re-attach handlers in the export manager
                    if (window.enhancedExportManager && typeof window.enhancedExportManager.attachExportHandlers === 'function') {
                        console.log('🔗 Re-attaching export handlers...');
                        window.enhancedExportManager.attachExportHandlers();
                    }
                    
                    // Add a direct click handler as backup that will definitely work
                    exportBtn.addEventListener('click', function(e) {
                        console.log('🖱️ Export button clicked! (Direct handler)');
                        e.preventDefault();
                        e.stopPropagation();
                        
                        if (!window.enhancedExportManager) {
                            console.error('❌ Enhanced Export Manager not available');
                            alert('Export system not ready. Please refresh the page.');
                            return;
                        }
                        
                        // Check if the button has the right class
                        if (!exportBtn.classList.contains('export-btn')) {
                            console.warn('⚠️ Button missing export-btn class, adding it...');
                            exportBtn.classList.add('export-btn');
                        }
                        
                        console.log('� Triggering export via handleExportRequest...');
                        try {
                            window.enhancedExportManager.handleExportRequest($(exportBtn));
                        } catch (error) {
                            console.error('❌ Error during export:', error);
                            alert('Export error: ' + error.message);
                        }
                    });
                    
                    console.log('✅ Export button setup complete with direct handler');
                    
                    // Test jQuery selection
                    const $exportBtns = $('.export-btn');
                    console.log(`🔍 Found ${$exportBtns.length} buttons with export-btn class`);
                    
                } else {
                    console.error('❌ Export button #btn-do-export not found!');
                    // Retry after a short delay
                    setTimeout(setupExportButton, 1000);
                }
            }
            
            // Start initialization after a short delay to ensure scripts are loaded
            setTimeout(initializeEnhancedExport, 200);

            // Setup filter change handlers to update the summary
            $('#export-limit, #export-category, #export-form-status, #export-payment-status, #export-program-payment').on('change', function() {
                updateExportSummary();
            });

            function updateExportSummary() {
                let summaryText = "";

                // Check for limit filter
                const limit = $('#export-limit').val();
                if (limit) {
                    summaryText += `A maximum of ${limit} participants `;
                } else {
                    summaryText += "All participants ";
                }

                summaryText += "will be exported using YBB Export API";

                // Add info about automatic processing for large exports
                if (!limit || parseInt(limit) > 5000) {
                    summaryText += " (large exports will be automatically processed and optimized)";
                }

                // Check for category filter
                const category = $('#export-category').val();
                if (category) {
                    const categoryText = category === 'fully_funded' ? 'Fully Funded' : 'Self Funded';
                    summaryText += ` (${categoryText} only)`;
                }

                // Check for form status filter
                const formStatus = $('#export-form-status').val();
                if (formStatus !== null && formStatus !== '') {
                    let statusText = '';
                    switch (formStatus) {
                        case '0':
                            statusText = 'Not Started';
                            break;
                        case '1':
                            statusText = 'On Progress';
                            break;
                        case '2':
                            statusText = 'Submitted';
                            break;
                    }
                    summaryText += `, with form status: ${statusText}`;
                }

                // Check for payment status filter
                const paymentStatus = $('#export-payment-status').val();
                if (paymentStatus === 'success') {
                    summaryText += `, who have made successful payments`;
                }

                // Check for program payment filter
                const programPayment = $('#export-program-payment').val();
                if (programPayment) {
                    const programPaymentOption = document.querySelector(`#export-program-payment option[value="${programPayment}"]`);
                    const programPaymentText = programPaymentOption ? programPaymentOption.textContent : programPayment;
                    summaryText += `, who paid for ${programPaymentText}`;
                }

                // Check for date range filter
                const dateRange = $('#export-date-range').val();
                if (dateRange) {
                    summaryText += `, registered between ${dateRange}`;
                }

                // Update summary text
                $('#exportCount').text(summaryText);
            }

            // Initial call to update export summary
            updateExportSummary();

            // Listen for program changes and reload table
            // This handles when users switch programs via the topbar
            window.addEventListener('storage', function(e) {
                if (e.key === 'current_program_changed') {
                    console.log('Program changed detected, reloading participants table...');
                    participantsTable.ajax.reload();
                }
            });
            
            // Also listen for cookie changes (alternative method)
            let currentProgram = '<?= session('current_program') ?>';
            setInterval(function() {
                // Check if program has changed via session or other means
                // This is a fallback method in case the storage event doesn't fire
                const newProgram = getCookie('current_program');
                if (newProgram && newProgram !== currentProgram) {
                    console.log('Program change detected via cookie, reloading table...');
                    currentProgram = newProgram;
                    participantsTable.ajax.reload();
                }
            }, 5000); // Check every 5 seconds

        });
        
        // Helper function to get cookie value
        function getCookie(name) {
            let value = "; " + document.cookie;
            let parts = value.split("; " + name + "=");
            if (parts.length == 2) return parts.pop().split(";").shift();
        }
    </script>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="exportModalLabel">Export Participants Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="exportForm" action="<?= site_url('users/participants/export') ?>" method="post">
                        <?= csrf_field() ?>
                        <!-- Hidden field for program_id -->
                        <input type="hidden" name="program_id" id="export-program-id" value="<?= session('current_program') ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="export-template" class="form-label">Export Template</label>
                                <select id="export-template" name="template" class="form-select">
                                    <option value="standard">Standard (10 columns)</option>
                                    <option value="detailed">Detailed (20 columns)</option>
                                    <option value="summary">Summary (5 columns)</option>
                                    <option value="complete">Complete (39 columns)</option>
                                </select>
                                <div class="form-text text-muted">Summary: Basic info | Standard: Core data | Detailed: Extended profile | Complete: All fields</div>
                            </div>
                            <div class="col-md-6">
                                <label for="export-format" class="form-label">Export Format</label>
                                <select id="export-format" name="format" class="form-select">
                                    <option value="excel">Excel (.xlsx)</option>
                                    <option value="csv">CSV</option>
                                </select>
                                <div class="form-text text-muted">File format for the export</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="export-limit" class="form-label">Limit Export Records</label>
                                <select id="export-limit" name="limit" class="form-select">
                                    <option value="">All Records</option>
                                    <option value="100">100 Records</option>
                                    <option value="500">500 Records</option>
                                    <option value="1000">1000 Records</option>
                                    <option value="5000">5000 Records</option>
                                </select>
                                <div class="form-text text-muted">Maximum number of participants to export</div>
                            </div>
                            <div class="col-md-6">
                                <label for="export-category" class="form-label">Participant Category</label>
                                <select id="export-category" name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <option value="fully_funded">Fully Funded</option>
                                    <option value="self_funded">Self Funded</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="export-form-status" class="form-label">Form Status</label>
                                <select id="export-form-status" name="form_status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="0">Not Started</option>
                                    <option value="1">On Progress</option>
                                    <option value="2">Submitted</option>
                                </select>
                                <div class="form-text text-muted">Filter by registration form completion status</div>
                            </div>
                            <div class="col-md-6">
                                <label for="export-has-submitted-form" class="form-label">Registration Completed</label>
                                <select id="export-has-submitted-form" name="has_submitted_form" class="form-select">
                                    <option value="">All Participants</option>
                                    <option value="yes">Only Completed Registration</option>
                                    <option value="no">Not Completed</option>
                                </select>
                                <div class="form-text text-muted">Filter by registration form submission</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="export-has-paid" class="form-label">Payment Status</label>
                                <select id="export-has-paid" name="has_paid" class="form-select">
                                    <option value="">All Participants</option>
                                    <option value="yes">Only Paid Participants</option>
                                    <option value="no">Unpaid Participants</option>
                                </select>
                                <div class="form-text text-muted">Filter by successful payment completion</div>
                            </div>
                            <div class="col-md-6">
                                <label for="export-date-range" class="form-label">Registration Date Range</label>
                                <input type="text" id="export-date-range" name="date_range" class="form-control" placeholder="Select date range">
                                <div class="form-text text-muted">Filter by registration date range</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="export-search" class="form-label">Search</label>
                                <input type="text" id="export-search" name="search" class="form-control" placeholder="Search by name or email">
                                <div class="form-text text-muted">Filter by participant name or email</div>
                            </div>
                            <div class="col-md-6">
                                <label for="export-country" class="form-label">Country</label>
                                <input type="text" id="export-country" name="country" class="form-control" placeholder="e.g., USA, Indonesia">
                                <div class="form-text text-muted">Filter by country</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="export-payment-status" class="form-label">Legacy Payment Filter</label>
                                <select id="export-payment-status" name="payment_status" class="form-select">
                                    <option value="">All Participants</option>
                                    <option value="success">Only Paid Participants</option>
                                </select>
                                <div class="form-text text-muted">Legacy payment status filter (use "Payment Status" above instead)</div>
                            </div>
                            <div class="col-md-6">
                                <label for="export-program-payment" class="form-label">Program Payment</label>
                                <select id="export-program-payment" name="program_payment_id" class="form-select">
                                    <option value="">All Payment Types</option>
                                    <?php
                                    // Get program ID from session
                                    $programId = session('current_program');
                                    // Load program payment model
                                    $programPaymentModel = new \App\Models\ProgramPaymentModel();
                                    // Get available program payments
                                    $programPayments = $programPaymentModel->getByProgramId($programId);
                                    // Display options
                                    foreach ($programPayments as $payment) {
                                        echo '<option value="' . $payment->id . '">' . esc($payment->name) . '</option>';
                                    }
                                    ?>
                                </select>
                                <div class="form-text text-muted">Filter by specific program payment type</div>
                            </div>
                        </div>

                        <div id="exportSummary" class="alert alert-info mt-3">
                            <h6 class="alert-heading"><i class="ri-information-line me-1"></i> Export Information</h6>
                            <p id="exportCount" class="mb-2">Using YBB DB Export API (database-direct mode) for optimal performance.</p>
                            <p class="mb-0 small"><strong>Enhanced Filters Available:</strong> Registration status, payment status, date ranges, search, country, and more. Use filters above to customize your export.</p>
                        </div>

                        <div class="card border border-success shadow-sm mt-4">
                            <div class="card-header bg-success bg-opacity-10">
                                <h5 class="card-title mb-0">
                                    <i class="ri-database-2-line me-1 fs-18 align-middle text-success"></i>
                                    <span class="align-middle text-dark fw-semibold">Database-Direct Export (Recommended)</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="ri-flashlight-line fs-24 text-success me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-2">Enhanced Export Features:</h6>
                                        <ul class="ps-3 mb-2">
                                            <li><strong>Reduced Payload:</strong> Sends filters instead of full data arrays</li>
                                            <li><strong>Better Performance:</strong> Optimized database queries on server-side</li>
                                            <li><strong>Enhanced Security:</strong> Data never leaves the database server</li>
                                            <li><strong>Advanced Filtering:</strong> Registration form status, payment status, search, date ranges</li>
                                            <li><strong>Real-time Progress:</strong> Status updates and download links provided automatically</li>
                                        </ul>
                                        <div class="alert alert-success py-2 mb-0">
                                            <i class="ri-check-line me-1"></i>
                                            <strong>Ready:</strong> The system handles all processing server-side and provides download options when complete.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="btn-do-export">
                        <i class="ri-cloud-upload-line align-middle me-1"></i> Start Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Results Container -->
    <div class="container-fluid mt-4">
        <div id="export-results"></div>
    </div>

    <!-- Debug Panel for SweetAlert Testing -->
    <?php if (ENVIRONMENT === 'development'): ?>
    <div class="container-fluid mt-3">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">🔧 Debug Panel (Development Mode Only)</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-sm btn-outline-info me-2" onclick="testSweetAlert()">
                            Test SweetAlert2
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="testExportSuccess()">
                            Test Export Success
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="checkExportManager()">
                            Check Export Manager
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="console.clear()">
                            Clear Console
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="forceExportTest()">
                            Force Export Test
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        // Debug functions for testing
        function testSweetAlert() {
            if (window.enhancedExportManager) {
                window.enhancedExportManager.testSweetAlert();
            } else {
                console.error('Enhanced Export Manager not available');
            }
        }
        
        function showExportHistory() {
            if (window.exportManager && typeof window.exportManager.showExportHistory === 'function') {
                window.exportManager.showExportHistory();
            } else if (window.enhancedExportManager && typeof window.enhancedExportManager.showExportHistory === 'function') {
                window.enhancedExportManager.showExportHistory();
            } else {
                console.error('Export Manager or history feature not available');
                // Fallback notification
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Feature Not Available',
                        text: 'Export history feature is not yet loaded. Please try again in a moment.',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Export history feature is not yet loaded. Please try again in a moment.');
                }
            }
        }
        
        function testExportSuccess() {
            if (window.enhancedExportManager) {
                window.enhancedExportManager.testExportSuccessNotification();
            } else {
                console.error('Enhanced Export Manager not available');
            }
        }
        
        function checkExportManager() {
            console.log('=== EXPORT MANAGER DEBUG ===');
            console.log('window.enhancedExportManager:', window.enhancedExportManager);
            console.log('window.exportManager:', window.exportManager);
            console.log('typeof Swal:', typeof Swal);
            console.log('window.Swal:', window.Swal);
            console.log('EnhancedExportManager class:', typeof EnhancedExportManager);
            
            const exportBtn = document.getElementById('btn-do-export');
            console.log('Export button element:', exportBtn);
            if (exportBtn) {
                console.log('Button classes:', exportBtn.className);
                console.log('Button dataset:', exportBtn.dataset);
                console.log('Has export-btn class:', exportBtn.classList.contains('export-btn'));
            }
            
            // Test jQuery selection
            const $exportBtn = $('.export-btn');
            console.log('jQuery export buttons found:', $exportBtn.length);
            
            if (window.enhancedExportManager) {
                console.log('✅ Export Manager is available');
                console.log('Export Manager instance:', window.enhancedExportManager);
                console.log('Export Manager methods:', Object.getOwnPropertyNames(Object.getPrototypeOf(window.enhancedExportManager)));
                
                // Test manual export
                if (exportBtn && exportBtn.classList.contains('export-btn')) {
                    console.log('🧪 Testing manual export...');
                    window.enhancedExportManager.handleExportRequest($(exportBtn));
                }
            } else {
                console.error('❌ Export Manager is not available');
                console.log('Available global objects:', Object.keys(window).filter(key => key.toLowerCase().includes('export')));
            }
        }
        
        function forceExportTest() {
            const exportBtn = document.getElementById('btn-do-export');
            if (exportBtn && window.enhancedExportManager) {
                console.log('🚀 Force testing export...');
                $(exportBtn).addClass('export-btn');
                $(exportBtn).attr('data-export-type', 'participants');
                $(exportBtn).attr('data-url', '/users/participants/export');
                $(exportBtn).attr('data-form-selector', '#exportForm');
                window.enhancedExportManager.handleExportRequest($(exportBtn));
            } else {
                console.error('Cannot test export - missing button or manager');
            }
        }
    </script>
</body>

</html>
