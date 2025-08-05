<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Scoring Fully Funded')); ?>

    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!-- Added missing link -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <!-- Added missing link -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- Date Range Picker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Scorings', 'title' => 'Fully Funded')); ?>

                    <!-- Participant Stats -->
                    <!-- <div class="row">
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
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total
                                                Participants</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= $stats->total ?? 0 ?></h4>
                                            <p class="text-muted mb-0">
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ri-arrow-up-line align-bottom"></i>
                                                    <?= $stats->recent ?? 0 ?>
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
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Fully
                                                Funded</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1">
                                                <?= $stats->category_counts['fully_funded'] ?? 0 ?></h4>
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
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Self
                                                Funded</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1">
                                                <?= $stats->category_counts['self_funded'] ?? 0 ?></h4>
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

                    </div> -->

                    <!-- Participants Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">All Participants</h5>
                                    <div class="flex-shrink-0">
                                        <button type="button" class="btn btn-success waves-effect waves-light me-2"
                                            data-bs-toggle="modal" data-bs-target="#exportModal">
                                            <i class="ri-file-excel-2-line align-middle me-1"></i> Export Data
                                        </button>
                                        <a href="<?= site_url('participants/new') ?>"
                                            class="btn btn-primary waves-effect waves-light">
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
                                                </span> <input type="text" id="search-box"
                                                    class="form-control border-start-0 ps-0"
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
                                        <!-- <div class="col-md-3 mb-2">
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
                                            <button id="apply-filters" class="btn btn-primary me-2">Apply
                                                Filters</button>
                                            <button id="reset-filters" class="btn btn-light">Reset</button>
                                        </div> -->
                                    </div>
                                    <table id="participants-datatable"
                                        class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>

    <!-- Date Range Picker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script src="/assets/js/pages/datatables.init.js"></script> <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <!-- Excel Export Helper -->
    <script src="/assets/js/excel-export.js"></script>

    <!-- Override Excel Export Helper for this page -->
    <script>
    // Override the setupExcelExport function from excel-export.js for this page
    // This ensures our custom implementation takes precedence
    function setupExcelExport() {
        console.log('Using custom export implementation for participants');
        // Implementation is in the page-specific script below
        return false;
    }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        var participantsTable = $('#participants-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= site_url('scorings/fully_funded/getData') ?>',
                type: 'GET',
                data: function(d) {
                    // Add filter parameters
                    d.category = $('#filter-category').val();
                    d.form_status = $('#filter-form-status').val();
                    d.search.value = $('#search-box').val(); // Add search term
                    return d;
                }
            },
            columns: [{
                    data: 'order_number',
                    width: "5%"
                }, {
                    data: 'participant_details',
                    width: "35%",
                    render: function(data, type, row) {
                        if (!data) return 'N/A';
                        let html = '<div class="d-flex align-items-center">';

                        // Avatar display - either picture or placeholder
                        html += '<div class="avatar-xs me-2">';
                        if (data.picture_url && data.picture_url !== '' && data.picture_url !==
                            'null') {
                            html += '<img src="' + data.picture_url + '" alt="' + data
                                .full_name + '" class="avatar-xs rounded-circle" />';
                        } else {
                            html +=
                                '<span class="avatar-title rounded-circle bg-soft-primary text-primary">' +
                                (data.full_name ? data.full_name.charAt(0).toUpperCase() :
                                    '?') + '</span>';
                        }
                        html += '</div>';

                        // Participant info
                        html += '<div>';
                        html += '<h5 class="fs-14 mb-1">' + data.full_name + '</h5>';
                        html += '<p class="text-muted mb-0">' + data.email + '</p>';
                        if (data.nationality && data.nationality !== 'N/A') {
                            html += '<span class="badge bg-light text-dark">' + data
                                .nationality + '</span>';
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
            participantsTable.search('').draw(); // Clear the search
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
                            alert('Failed to delete participant: ' + (response.message ||
                                'Unknown error'));
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
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                'YYYY-MM-DD'));
            updateExportSummary();
        });

        $('#export-date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            updateExportSummary();
        }); // Handle export button click
        $('#btn-do-export').off('click').on('click', function() {
            // Show loading alert
            Swal.fire({
                title: 'Preparing Export',
                html: 'Please wait while we analyze the data...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            }); // First make a regular AJAX call to check if batching is needed
            console.log('Starting export request with form data:', $('#exportForm').serialize());
            $.ajax({
                url: $('#exportForm').attr('action'),
                method: 'POST', // Use POST to handle larger form data
                data: $('#exportForm').serialize() +
                    '&check_batch=1', // Add parameter to indicate we're just checking
                dataType: 'json',
                success: function(response) {
                    // Check if we received batch information
                    if (response && response.success && response.batches) {
                        // Multiple batches needed
                        handleBatchedExport(response);
                    } else {
                        // Small dataset, handle as a single file
                        downloadSingleExport();
                    }
                },
                error: function(xhr, status, error) {
                    // Show error message
                    let errorMessage = 'There was an error processing your export';
                    let errorDetail = '';

                    try {
                        // Try to parse the response as JSON
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }

                        // If there's additional error detail, capture it
                        if (response.detail || response.error) {
                            errorDetail = response.detail || response.error;
                        }
                    } catch (e) {
                        // If not valid JSON, use the raw response text if available
                        if (xhr.responseText && xhr.responseText.length > 0) {
                            errorDetail = xhr.responseText.substring(0, 500);
                        } else {
                            errorDetail = 'Status: ' + status + ', Error: ' + error;
                        }
                    }

                    // Log the full error for debugging
                    console.error('Export error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        response: xhr.responseText,
                        error: error
                    });

                    // Show a nice error message to the user
                    Swal.fire({
                        title: 'Export Failed',
                        html: errorMessage +
                            (errorDetail ?
                                '<br><br><div class="text-start"><strong>Technical details:</strong>' +
                                '<div class="alert alert-danger mt-2 small" style="max-height:200px;overflow-y:auto">' +
                                errorDetail + '</div></div>' : ''),
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                }
            });
        }); // Function to handle single file export                  
        function downloadSingleExport() {
            // Show processing message
            Swal.update({
                title: 'Processing Export',
                html: 'Generating your Excel file, please wait...',
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Create an iframe for download to avoid page navigation
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.name = 'download_iframe';
            document.body.appendChild(iframe);

            // Create a form for traditional form submission (avoids AJAX JSON parsing issues)
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = $('#exportForm').attr('action');
            form.target = 'download_iframe'; // Target the iframe instead of _blank
            form.style.display = 'none';

            // Copy all form fields
            const formData = new FormData(document.getElementById('exportForm'));
            for (const [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }

            // Add direct download parameter
            const directDownload = document.createElement('input');
            directDownload.type = 'hidden';
            directDownload.name = 'direct_download';
            directDownload.value = '1';
            form.appendChild(directDownload);

            // Add to document and submit
            document.body.appendChild(form);

            // Set a timeout to detect if the download fails or takes too long
            let downloadSuccess = false;
            // Handle iframe load events
            iframe.onload = function() {
                // Check if the iframe content contains an error message
                try {
                    const iframeContent = iframe.contentDocument || iframe.contentWindow.document;
                    const responseText = iframeContent.body.textContent || '';

                    // Try to parse response as JSON first                         
                    try {
                        // Check for binary content first - Excel files start with PK signature bytes
                        // There are multiple ways to detect Excel content showing up as text
                        const isProbablyExcel = (
                            // Check for ZIP file signature (Excel files are ZIP containers)
                            responseText.startsWith('PK') ||
                            // Check for XML markers common in Excel files
                            responseText.includes('Content_Types') ||
                            responseText.includes('workbook.xml') ||
                            responseText.includes('xl/worksheets') ||
                            // Or if it's long binary content without HTML markers
                            (responseText.length > 1000 &&
                                !responseText.includes('<!DOCTYPE') &&
                                !responseText.includes('<html'))
                        );

                        if (isProbablyExcel) {
                            console.log('Detected Excel binary data in iframe - download successful');
                            downloadSuccess = true;

                            // Close the modal
                            $('#exportModal').modal('hide');

                            // Show success message
                            Swal.fire({
                                title: 'Export Successful',
                                text: 'Your Excel file has been downloaded successfully.',
                                icon: 'success',
                                confirmButtonColor: '#0ab39c'
                            });

                            // Clean up resources
                            cleanupResources();
                            return;
                        }

                        const jsonResponse = JSON.parse(responseText);
                        if (!jsonResponse.success) {
                            // JSON error response
                            console.error('Export error detected (JSON):', jsonResponse.message ||
                                'Unknown error');
                            Swal.fire({
                                title: 'Export Failed',
                                html: 'There was an error processing your export:<br>' +
                                    (jsonResponse.message ||
                                        'Unknown error. Please try again or contact support.'),
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                            // Clean up but don't mark as success
                            cleanupResources();
                            return;
                        }
                    } catch (jsonError) {
                        // Not JSON or invalid JSON, check for error strings
                        if ((responseText.includes('error') ||
                                responseText.includes('Error') ||
                                responseText.includes('failed') ||
                                responseText.includes('Exception') ||
                                responseText.includes('Fatal')) &&
                            !responseText.startsWith('PK')) { // Skip if it's binary Excel data

                            console.error('Export error detected (text):', responseText);
                            Swal.fire({
                                title: 'Export Failed',
                                html: 'There was an error processing your export.<br>Error details:<br>' +
                                    '<div class="alert alert-danger mt-2">' +
                                    responseText.substring(0, 300) +
                                    (responseText.length > 300 ? '...' : '') + '</div>',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                            // Clean up but don't mark as success
                            cleanupResources();
                            return;
                        } else if (responseText && !responseText.includes('<!DOCTYPE')) {
                            // This is likely not HTML, and possibly XLSX data viewed as text
                            console.log(
                                'Iframe contains non-HTML content, likely Excel data - download successful'
                            );
                            downloadSuccess = true;
                        }
                    }
                } catch (e) {
                    // If we can't access iframe content, it's probably a successful download
                    // due to cross-origin restrictions after the browser initiates a download
                    console.log('Download appears successful (access blocked due to cross-origin policy)');
                }

                downloadSuccess = true;

                // Close the modal
                $('#exportModal').modal('hide');

                Swal.fire({
                    title: 'Export Started',
                    text: 'Your file download has started. Please check your downloads folder.',
                    icon: 'success',
                    confirmButtonColor: '#0ab39c'
                });

                // Clean up
                cleanupResources();
            };

            // Submit the form
            form.submit(); // Set a timeout to check if download succeeded
            setTimeout(() => {
                // Only show message if we haven't marked success yet and not already processed
                if (!downloadSuccess && !isProcessed) {
                    console.log('Export timeout reached without success');
                    isProcessed = true;

                    Swal.fire({
                        title: 'Export Taking Longer Than Expected',
                        html: `
                                <p>The export is taking longer than expected.</p>
                                <p>This might be due to:</p>
                                <ul class="text-start">
                                    <li>Large amount of data being processed</li>
                                    <li>Server processing delay</li>
                                </ul>
                                <p>Please wait a bit longer for your download to start. If it doesn't appear within a minute, 
                                try again with a smaller date range or using batch exports.</p>
                            `,
                        icon: 'info',
                        confirmButtonColor: '#3085d6'
                    });

                    // Only clean up resources if we haven't already
                    cleanupResources();
                }
            }, 15000);

            function cleanupResources() {
                // Clean up the form and iframe
                if (document.body.contains(form)) {
                    document.body.removeChild(form);
                }
                if (document.body.contains(iframe)) {
                    document.body.removeChild(iframe);
                }
            }
        }

        // Function to handle batched export
        function handleBatchedExport(response) {
            const batches = response.batches;
            const totalBatches = batches.length;
            const totalRecords = response.total_records;
            const batchSize = response.batch_size;
            const downloadedFiles = [];

            // Get form data to include filters in each batch
            const formData = new FormData(document.getElementById('exportForm'));
            const formParams = {};
            for (const [key, value] of formData.entries()) {
                formParams[key] = value;
            }

            // Prepare filters as base64 encoded JSON
            const filters = {};
            // Add all form parameters to the filters object
            for (const key in formParams) {
                if (formParams.hasOwnProperty(key) && formParams[key] !== '') {
                    filters[key] = formParams[key];
                }
            }

            // Make sure program_id is always included
            if (!filters.program_id) {
                // Try to get it from the current URL or page context
                const currentProgramId = $('#current-program-id').val() ||
                    $('input[name="program_id"]').val() ||
                    $('.program-selector.active').data('program-id');

                if (currentProgramId) {
                    filters.program_id = currentProgramId;
                    console.log('Added program_id from page context:', currentProgramId);
                }
            }
            // Ensure program_id is in the URL to avoid any issues with base64 encoding
            const currentProgramId = filters.program_id;

            // Add form data to each batch
            for (let i = 0; i < batches.length; i++) {
                try {
                    // Create clean filtered parameters with only non-empty values
                    const cleanFilters = {};
                    for (const key in filters) {
                        if (filters[key] !== '' && filters[key] !== null && filters[key] !== undefined) {
                            cleanFilters[key] = filters[key];
                        }
                    }

                    // Encode filters as base64
                    const encodedFilters = btoa(JSON.stringify(cleanFilters));

                    // Create parameters for the batch
                    batches[i].params = {
                        batch: i + 1,
                        total_batches: totalBatches,
                        batch_size: batchSize,
                        program_id: currentProgramId, // Ensure program_id is always included directly
                        filters: encodedFilters,
                        direct_download: 1
                    };

                    // Log the batch params for debugging
                    console.log(`Batch ${i+1} parameters:`, batches[i].params);
                    console.log(`Encoded filters for batch ${i+1}:`, encodedFilters);
                } catch (err) {
                    console.error(`Error preparing batch ${i+1}:`, err);
                }
            }

            // Close the modal now that we're handling with SweetAlert
            $('#exportModal').modal('hide');

            // Create a progress tracking dialog
            Swal.fire({
                title: 'Processing Large Export',
                html: `
                        <div class="text-start mb-3">
                            <p>Exporting ${totalRecords} records in ${totalBatches} files (${batchSize} records per file).</p>
                            <p>Files will be downloaded one by one. Please wait until all downloads are complete.</p>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" 
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                            <p class="mt-2 mb-0"><small>File <span id="current-batch">0</span> of ${totalBatches}</small></p>
                            <p class="mt-1" id="current-file-name">Preparing...</p>
                        </div>
                    `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // Process each batch sequentially
            downloadBatch(batches, 0, totalBatches, downloadedFiles);
        }

        // Function to download each batch sequentially using AJAX
        function downloadBatch(batches, currentIndex, totalBatches, downloadedFiles) {
            if (currentIndex >= batches.length) {
                // All batches are completed
                Swal.fire({
                    title: 'Export Completed!',
                    html: `
                            <div class="text-start">
                                <p>All ${totalBatches} files have been exported successfully.</p>
                                <p><strong>Downloaded files:</strong></p>
                                <ul class="text-start">
                                    ${downloadedFiles.map(file => `<li>${file}</li>`).join('')}
                                </ul>
                            </div>
                        `,
                    icon: 'success',
                    confirmButtonColor: '#0ab39c'
                });
                return;
            }
            const batch = batches[currentIndex];
            const progress = Math.round(((currentIndex + 1) / totalBatches) * 100);
            const fileNumber = currentIndex + 1;
            const filename = batch.filename || `batch_${fileNumber}_of_${totalBatches}.xlsx`;

            // Update the progress
            $('.progress-bar').css('width', progress + '%');
            $('.progress-bar').attr('aria-valuenow', progress);
            $('.progress-bar').text(progress + '%');
            $('#current-batch').text(fileNumber);

            // Display status message and record the filename
            $('#current-file-name').text('Downloading: ' + filename);

            console.log(`Processing batch ${fileNumber}/${totalBatches}: ${filename}`);
            downloadedFiles.push(filename); // Create a hidden iframe for downloading
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.name = 'download_iframe_' + currentIndex;
            document.body.appendChild(iframe); // Create and submit a form targeting the iframe
            const batchForm = document.createElement('form');
            batchForm.method = 'POST'; // Use POST instead of GET for larger data

            // Use site_url to ensure we have a full URL with correct domain
            const baseUrl = '<?= site_url('users/participants/export_batch') ?>';
            batchForm.action = baseUrl;
            batchForm.target = iframe.name;
            batchForm.style.display = 'none';
            batchForm.enctype = 'application/x-www-form-urlencoded';

            console.log(`Creating form for batch ${fileNumber}, posting to: ${baseUrl}`);

            // Add all the necessary parameters from the batch object
            for (const key in batch.params) {
                if (batch.params.hasOwnProperty(key)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = batch.params[key];
                    batchForm.appendChild(input);
                    console.log(
                        `Added form parameter: ${key}=${batch.params[key].toString().substring(0, 50)}${batch.params[key].toString().length > 50 ? '...' : ''}`
                    );
                }
            }

            // Add CSRF token if needed
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '<?= csrf_token() ?>';
            tokenInput.value = '<?= csrf_hash() ?>';
            batchForm.appendChild(tokenInput);

            // Append form to body and submit
            document.body.appendChild(batchForm);
            batchForm
                .submit(); // Handle iframe load event to proceed to next batch                iframe.onload = function() {
            console.log(`Batch ${fileNumber}/${totalBatches} loaded`);

            // Set a flag to track if we've processed this batch already
            let processed = false;

            // Check if there's an error message in the iframe
            try {
                // Access might fail due to cross-origin policy if download started
                const iframeContent = iframe.contentDocument || iframe.contentWindow.document;
                const bodyText = iframeContent.body.textContent || '';
                console.log(
                    `Batch ${fileNumber} content length: ${bodyText.length} chars`
                ); // Check for binary content (Excel file)
                const isProbablyExcel = (
                    // Check for ZIP file signature (Excel files are ZIP containers)
                    bodyText.startsWith('PK') ||
                    // Check for XML markers common in Excel files
                    bodyText.includes('Content_Types') ||
                    bodyText.includes('workbook.xml') ||
                    bodyText.includes('xl/worksheets') ||
                    // Or if it's long binary content without HTML markers
                    (bodyText.length > 1000 &&
                        !bodyText.includes('<!DOCTYPE') &&
                        !bodyText.includes('<html'))
                );

                if (isProbablyExcel) {
                    console.log(`Batch ${fileNumber}: Binary content detected - Excel download successful`);
                    // Mark this batch as processed
                    processed = true;

                    // Add the file to the list in the UI
                    const listItems = document.querySelectorAll('ul.text-start');
                    if (listItems && listItems.length > 0) {
                        const li = document.createElement('li');
                        li.textContent = filename;
                        listItems[0].appendChild(li);
                    }

                    // Proceed to next batch after a short delay to let the download initialize
                    setTimeout(() => {
                        downloadBatch(batches, currentIndex + 1, totalBatches, downloadedFiles);
                    }, 800);
                    return;
                }

                // Check if the content is JSON
                try {
                    const jsonResponse = JSON.parse(bodyText);
                    if (!jsonResponse.success) {
                        throw new Error(jsonResponse.message || "Unknown error in JSON response");
                    }
                } catch (jsonError) {
                    // Not valid JSON or JSON with error, check for error text
                    if (bodyText.includes('Error:') ||
                        bodyText.includes('error') ||
                        bodyText.includes('failed') ||
                        bodyText.includes('Exception') ||
                        bodyText.includes('Fatal')) {

                        console.error(`Batch ${fileNumber} failed:`, bodyText);
                        Swal.fire({
                            title: 'Batch Export Error',
                            html: `There was an error processing batch ${fileNumber}. <br>The system will continue with the next batch.<br><strong>Error details:</strong> ${bodyText.substring(0, 200)}...`,
                            icon: 'warning',
                            confirmButtonColor: '#f06548'
                        });
                        return; // Return early to indicate error
                    }
                } // If we got here without errors, log success
                console.log(`Batch ${fileNumber} appears to be successful`);
                processed = true;
            } catch (e) {
                // If we can't access the iframe content due to cross-origin policy,
                // it's likely a download has started (which is good)
                console.log(`Access to iframe content blocked (likely successful download): ${e.message}`);
                processed = true;
            }

            if (processed) {
                // Wait a bit to ensure the browser starts the download
                setTimeout(function() {
                    // Clean up resources
                    if (document.body.contains(batchForm)) {
                        document.body.removeChild(batchForm);
                    }

                    // Clean up iframe
                    if (document.body.contains(iframe)) {
                        document.body.removeChild(iframe);
                    }

                    // Proceed to next batch
                    downloadBatch(batches, currentIndex + 1, totalBatches, downloadedFiles);
                }, 1500);
            }
        };

        // Add a timeout in case the iframe load event doesn't fire or gets stuck
        const timeoutId = setTimeout(function() {
            // Check if the iframe still exists and if the processed flag hasn't been set
            if (document.body.contains(iframe) && !processed) {
                console.log(
                    `Timeout reached for batch ${fileNumber}/${totalBatches}, moving to next batch`);

                // Prevent the original onload from doing anything if it fires later
                iframe.onload = function() {
                    console.log(`Ignored late onload for batch ${fileNumber}`);
                };

                // Mark as processed to prevent double-processing
                processed = true;

                // Clean up resources
                if (document.body.contains(batchForm)) {
                    document.body.removeChild(batchForm);
                }

                if (document.body.contains(iframe)) {
                    document.body.removeChild(iframe);
                }

                // Log a warning about the timeout
                console.warn(`Batch ${fileNumber} timed out, but still moving to next batch`);

                // Move to next batch
                downloadBatch(batches, currentIndex + 1, totalBatches, downloadedFiles);
            }
        }, 15000); // Give even more time for very large batches

        // Setup filter change handlers to update the summary
        $('#export-limit, #export-category, #export-form-status, #export-payment-status, #export-program-payment')
            .on('change', function() {
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

            summaryText += "will be exported";

            // Add info about batch processing for large exports
            if (!limit || parseInt(limit) > 1000) {
                summaryText += " (large exports will be processed automatically)";
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
            } // Check for program payment filter
            const programPayment = $('#export-program-payment').val();
            if (programPayment) {
                const programPaymentOption = document.querySelector(
                    `#export-program-payment option[value="${programPayment}"]`);
                const programPaymentText = programPaymentOption ? programPaymentOption.textContent :
                    programPayment;
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

    });
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
                        <input type="hidden" name="program_id" id="export-program-id"
                            value="<?= session('current_program') ?>">

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
                            </div>
                            <div class="col-md-6">
                                <label for="export-date-range" class="form-label">Registration Date Range</label>
                                <input type="text" id="export-date-range" name="date_range" class="form-control"
                                    placeholder="Select date range">
                                <div class="form-text text-muted">Filter by registration date range</div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="export-payment-status" class="form-label">Payment Status</label>
                                <select id="export-payment-status" name="payment_status" class="form-select">
                                    <option value="">All Participants</option>
                                    <option value="success">Only Paid Participants</option>
                                </select>
                                <div class="form-text text-muted">Filter by payment status</div>
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
                            <h6 class="alert-heading">Export Summary</h6>
                            <p id="exportCount" class="mb-0">All participants will be exported. Use filters above to
                                limit the export.</p>
                        </div>

                        <div class="card border border-warning shadow-sm mt-4">
                            <div class="card-header bg-warning bg-opacity-10">
                                <h5 class="card-title mb-0">
                                    <i class="ri-information-line me-1 fs-18 align-middle text-warning"></i>
                                    <span class="align-middle text-dark fw-semibold">Important! Export Process
                                        Information</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="ri-file-excel-2-line fs-24 text-success me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-2">Processing Steps:</h6>
                                        <ol class="ps-3 mb-2">
                                            <li>Click "Export Data" to start the process</li>
                                            <li>Wait while data is being processed - <strong>do not close this
                                                    window</strong></li>
                                            <li>For large datasets (>1000 records), files will be split automatically
                                            </li>
                                            <li>Progress will be shown with status updates via notifications</li>
                                        </ol>
                                        <div class="alert alert-warning py-2">
                                            <i class="ri-alert-line me-1"></i>
                                            <strong>Important:</strong> The system will handle the download process.
                                            Please do not navigate away from the page until all files are downloaded.
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
                        <i class="ri-file-excel-2-line align-middle me-1"></i> Export Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>