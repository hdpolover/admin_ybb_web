<?= $this->extend('layouts/reviewer') ?>

<?= $this->section('styles') ?>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

<!-- Custom DataTables styling fix -->
<style>
.table th, .table td {
    padding: 0.75rem;
    vertical-align: middle;
    border-top: 1px solid #dee2e6;
}

.table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
}

.dataTables_wrapper .dataTables_processing {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    margin-left: -100px;
    margin-top: -26px;
    text-align: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #ddd;
    border-radius: 4px;
}

.dt-responsive table.dataTable {
    width: 100% !important;
}

/* Action buttons styling */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    line-height: 1.5;
    border-radius: 0.25rem;
}

/* Filter indicator styling */
#filter-indicator {
    font-style: italic;
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.card-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.subtheme-filter-section {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 1rem;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">            <div class="card-header">
                <div>
                    <h4 class="card-title mb-0">Abstracts & Papers</h4>
                    <small class="text-muted d-none" id="filter-indicator"></small>
                </div>
            </div><!-- end card header -->
            <div class="card-body">                <?php if (isset($assignedSubthemes) && !empty($assignedSubthemes)): ?>
                    <div class="alert alert-info mb-3">
                        <strong>Your Assigned Subthemes:</strong>
                        <?php foreach ($assignedSubthemes as $subtheme): ?>
                            <span class="badge bg-primary me-1"><?= esc($subtheme->subtheme_name) ?></span>
                        <?php endforeach; ?>
                        <br><small>You can only review abstracts from participants in these subthemes.</small>
                    </div>
                <?php elseif (isset($assignedSubthemes)): ?>
                    <div class="alert alert-warning mb-3">
                        <strong>No Subthemes Assigned:</strong> You haven't been assigned to any subthemes yet. Please contact the administrator.
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger mb-3">
                        <strong>Error:</strong> Could not load subtheme assignments. Please contact support.
                    </div>
                <?php endif; ?>                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="subtheme-filter" class="form-label">Filter by Subtheme</label>
                            <select class="form-select" id="subtheme-filter">
                                <option value="all">All Assigned Subthemes</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                    </div>                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status-filter" class="form-label">Review Status</label>
                            <select class="form-select" id="status-filter">
                                <option value="all">All Status</option>
                                <option value="submitted">Submitted</option>
                                <option value="under_review">Under Review</option>
                                <option value="accepted">Accepted</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                </div><div id="table-container" class="table-responsive">                    <table id="reviews-table" class="table table-bordered table-striped table-hover align-middle" style="width:100%">                        <thead class="table-light">                            <tr>
                                <th width="50">#</th>
                                <th>Abstract Title</th>
                                <th>Authors</th>
                                <th>Program</th>
                                <th>Submitted</th>
                                <th>Review Status</th>
                                <th>Feedbacks</th>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Force load jQuery immediately -->
<script src="/assets/libs/jquery/jquery.min.js"></script>

<!-- Alternative: Load from CDN as backup -->
<script>
if (typeof jQuery === 'undefined') {
    document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
}
</script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>

<script type="text/javascript">    $(document).ready(function() {
        console.log("jQuery loaded successfully, version:", $.fn.jquery);
        console.log("DataTables available:", typeof $.fn.DataTable);
        
        // Load subthemes first, then initialize table
        loadReviewerSubthemes();
        initializeAbstractsTable();
    });

    function loadReviewerSubthemes() {
        // Populate subtheme filter with assigned subthemes
        const assignedSubthemes = <?= json_encode($assignedSubthemes ?? []) ?>;
        let options = '<option value="all">All Assigned Subthemes</option>';
        
        if (assignedSubthemes && assignedSubthemes.length > 0) {
            assignedSubthemes.forEach(function(subtheme) {
                options += `<option value="${subtheme.program_subtheme_id}">${subtheme.subtheme_name}</option>`;
            });
        } else {
            options += '<option value="" disabled>No subthemes assigned</option>';
        }
        
        $('#subtheme-filter').html(options);
    }

    function initializeAbstractsTable() {
        console.log("Initializing abstracts DataTable...");
        console.log("DataTables available:", typeof $.fn.DataTable);

        // Initialize DataTables with server-side processing
        var table = $('#reviews-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: '<?= base_url("reviewers/abstracts-papers/getData") ?>',
                type: 'POST',                data: function(d) {
                    console.log('Sending DataTables request:', d);
                    d.subtheme_filter = $('#subtheme-filter').val();
                    d.status_filter = $('#status-filter').val();
                    d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
                    return d;
                },
                dataSrc: function(json) {
                    console.log('Received DataTables response:', json);
                    
                    // Handle errors in the response
                    if (json.error) {
                        console.error('Server returned error:', json.error);
                        $('#table-container').html(
                            '<div class="alert alert-danger">' +
                            '<strong>Error:</strong> ' + json.error +
                            '</div>'
                        );
                        return [];
                    }
                    
                    return json.data || [];
                },
                error: function(xhr, status, error) {
                    console.error('DataTables AJAX Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    
                    // Show user-friendly error
                    $('#table-container').html(
                        '<div class="alert alert-danger">' +
                        '<strong>Error loading data:</strong> ' + error + '<br>' +
                        '<button class="btn btn-sm btn-primary mt-2" onclick="location.reload()">Reload Page</button>' +
                        '<details class="mt-2">' +
                        '<summary>Technical Details</summary>' +
                        '<pre>' + xhr.responseText + '</pre>' +
                        '</details>' +
                        '</div>'
                    );
                }
            },            columns: [{
                    data: null,
                    name: 'row_number',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    width: '50px'
                },
                {
                    data: 'abstract_title',
                    name: 'abstract_title',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'authors',
                    name: 'authors',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'program_name',
                    name: 'program_name',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'submission_date',
                    name: 'submission_date',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'abstract_status',
                    name: 'abstract_status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'feedbacks_count',
                    name: 'feedbacks_count',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (data > 0) {
                            return '<span class="badge bg-info">' + data + ' feedback' + (data > 1 ? 's' : '') + '</span>';
                        } else {
                            return '<span class="badge bg-secondary">No feedback</span>';
                        }
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    width: '120px'
                }
            ],
            columnDefs: [{
                orderable: false,
                targets: [0, 5, 6, 7] // Row number, status, feedbacks and action columns are not sortable
            }],            order: [
                [4, 'desc']
            ], // Order by submission date desc (now column 4 instead of 3)
            responsive: true,
            lengthChange: true,
            pageLength: 25,
            searching: true,
            ordering: true,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            language: {
                processing: "Loading abstracts...",
                emptyTable: "No abstracts assigned for review yet",
                zeroRecords: "No matching abstracts found",
                search: "Search abstracts:",
                lengthMenu: "Show _MENU_ abstracts per page",
                info: "Showing _START_ to _END_ of _TOTAL_ abstracts",
                infoEmpty: "Showing 0 to 0 of 0 abstracts",
                infoFiltered: "(filtered from _MAX_ total abstracts)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },            drawCallback: function(settings) {
                console.log('DataTables draw completed');
                // Style pagination like program-payments
                $(".dataTables_paginate > .pagination").addClass("pagination-squared justify-content-end mb-0");
                // Initialize tooltips for action buttons
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            initComplete: function(settings, json) {
                console.log('DataTables initialization complete:', json);
            }
        });        // Filter change events
        $('#subtheme-filter').on('change', function() {
            console.log('Subtheme filter changed to:', $(this).val());
            updateFilterIndicator();
            table.ajax.reload();
        });

        $('#status-filter').on('change', function() {
            console.log('Status filter changed to:', $(this).val());
            updateFilterIndicator();
            table.ajax.reload();
        });

        // Update filter indicator
        function updateFilterIndicator() {
            const subthemeFilter = $('#subtheme-filter').val();
            const statusFilter = $('#status-filter').val();
            const subthemeText = $('#subtheme-filter option:selected').text();
            const statusText = $('#status-filter option:selected').text();
            
            let indicators = [];
            
            if (subthemeFilter && subthemeFilter !== 'all') {
                indicators.push('Subtheme: ' + subthemeText);
            }
            
            if (statusFilter && statusFilter !== 'all') {
                indicators.push('Status: ' + statusText);
            }
            
            if (indicators.length > 0) {
                $('#filter-indicator').html('Filtered by: ' + indicators.join(' | ')).removeClass('d-none');
            } else {
                $('#filter-indicator').addClass('d-none');
            }
        }// Add manual refresh button functionality if needed
        $(document).on('click', '.btn-refresh', function() {
            table.ajax.reload();
        });        return table;
    }
</script>
<?= $this->endSection() ?>