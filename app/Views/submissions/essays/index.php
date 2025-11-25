<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => $title)); ?>
    <?= $this->include('partials/head-css') ?>
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />

    <style>
        .essay-count-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
        }
        
        .participant-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .participant-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .category-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .progress-wrapper {
            min-width: 120px;
        }
        
        .progress {
            height: 20px;
        }
        
        .progress-text {
            font-size: 0.75rem;
            font-weight: 600;
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
                    <!-- start page title -->
                    <?php $pageTitle = 'Participant Essays'; 
                    $breadcrumbItems = [
                        ['text' => 'Submissions', 'link' => '#'],
                        ['text' => 'Essays', 'link' => '#']
                    ]; ?>
                    <?= view('partials/page-title', ['pagetitle' => 'YBB Admin', 'title' => $pageTitle, 'breadcrumb' => $breadcrumbItems]) ?>
                    <!-- end page title -->

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ri-check-line me-2"></i><?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-2"></i><?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Info Card -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-primary-subtle text-primary rounded">
                                                    <i class="ri-article-line fs-20"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="card-title mb-1">Essay Submissions Overview</h5>
                                            <p class="text-muted mb-0">View and manage all participant essay submissions for the current program. Track completion status and review responses.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Essays Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title mb-0 flex-grow-1">
                                            <i class="ri-file-list-3-line me-2"></i>All Essay Submissions
                                        </h5>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <!-- Filters -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Category</label>
                                            <select id="filter-category" class="form-select">
                                                <option value="">All Categories</option>
                                                <option value="fully_funded">Fully Funded</option>
                                                <option value="self_funded">Self Funded</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button id="apply-filters" class="btn btn-primary me-2">
                                                <i class="ri-filter-3-line me-1"></i>Apply Filters
                                            </button>
                                            <button id="reset-filters" class="btn btn-light">
                                                <i class="ri-refresh-line me-1"></i>Reset
                                            </button>
                                        </div>
                                    </div>

                                    <!-- DataTable -->
                                    <div class="table-responsive">
                                        <table id="essays-datatable" class="table table-bordered table-hover dt-responsive nowrap w-100">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 60px;">#</th>
                                                    <th>Participant</th>
                                                    <th style="width: 120px;">Category</th>
                                                    <th style="width: 150px;">Essay Progress</th>
                                                    <th style="width: 130px;">Submitted On</th>
                                                    <th style="width: 100px;">Actions</th>
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

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            const baseUrl = '<?= base_url() ?>';
            
            // Initialize DataTable
            const table = $('#essays-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: baseUrl + '/submissions/essays/getData',
                    type: 'POST',
                    data: function(d) {
                        d.category = $('#filter-category').val();
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable error:', error, thrown);
                    }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            const initials = row.full_name.split(' ')
                                .map(n => n[0])
                                .join('')
                                .substring(0, 2)
                                .toUpperCase();
                            
                            return `
                                <div class="participant-info">
                                    <div class="participant-avatar">${initials}</div>
                                    <div>
                                        <div class="fw-semibold">${row.full_name}</div>
                                        <div class="text-muted small">${row.email}</div>
                                        <div class="text-muted small">ID: ${row.id}</div>
                                    </div>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'category',
                        render: function(data) {
                            const badgeClass = data === 'fully_funded' ? 'bg-success' : 'bg-info';
                            const displayText = data === 'fully_funded' ? 'Fully Funded' : 'Self Funded';
                            return `<span class="badge ${badgeClass} category-badge">${displayText}</span>`;
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            const percentage = row.essay_count > 0 
                                ? Math.round((row.answered_count / row.essay_count) * 100) 
                                : 0;
                            
                            let progressClass = 'bg-danger';
                            if (percentage >= 100) progressClass = 'bg-success';
                            else if (percentage >= 50) progressClass = 'bg-warning';
                            
                            return `
                                <div class="progress-wrapper">
                                    <div class="progress">
                                        <div class="progress-bar ${progressClass}" role="progressbar" 
                                             style="width: ${percentage}%">
                                            <span class="progress-text">${row.answered_count}/${row.essay_count}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'created_at',
                        render: function(data) {
                            if (!data) return 'N/A';
                            const date = new Date(data);
                            return date.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group" role="group">
                                    <a href="${baseUrl}/submissions/essays/view/${row.id}" 
                                       class="btn btn-sm btn-primary" 
                                       title="View Essays">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="${baseUrl}/users/participants/view/${row.id}" 
                                       class="btn btn-sm btn-info" 
                                       title="View Participant">
                                        <i class="ri-user-line"></i>
                                    </a>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[4, 'desc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search participants...",
                    emptyTable: "No essay submissions found",
                    zeroRecords: "No matching essay submissions found"
                }
            });

            // Apply filters
            $('#apply-filters').click(function() {
                table.ajax.reload();
            });

            // Reset filters
            $('#reset-filters').click(function() {
                $('#filter-category').val('');
                table.ajax.reload();
            });
        });
    </script>

    <!-- App js -->
    <script src="<?= base_url() ?>/assets/js/app.js"></script>
</body>

</html>
