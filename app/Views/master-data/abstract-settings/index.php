<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Abstract Settings')); ?>
    <?= $this->include('partials/head-css') ?>

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script> <!-- Custom styles for this page -->
    <style>
        /* Loading overlay styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s linear;
        }

        .loading-overlay.show {
            visibility: visible;
            opacity: 1;
        }

        .spinner {
            width: 3.5rem;
            height: 3.5rem;
        }

        .info-section {
            margin-bottom: 2rem;
        }

        .info-section h5 {
            font-weight: 600;
            border-bottom: 1px solid #e9e9ef;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            color: #495057;
        }

        .info-label {
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #343a40;
            display: block;
        }

        .info-content {
            font-size: 1rem;
            color: #212529;
            margin-bottom: 1.25rem;
            word-break: break-word;
        }

        .info-card {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            transition: all 0.25s ease;
        }

        .info-card:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            background-color: #fff;
        }

        .badge-large {
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
        }

        /* Tab styles */
        .nav-pills .nav-link.active {
            background-color: #405189;
        }

        .nav-pills .nav-link {
            font-weight: 600;
            font-size: 1.05rem;
        }

        .cursor-on-hover {
            cursor: pointer;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 0.5rem;
            border: 2px dashed #dee2e6;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .empty-state-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 1rem;
        }

        .empty-state-text {
            color: #6c757d;
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .settings-form {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #405189;
            box-shadow: 0 0 0 0.2rem rgba(64, 81, 137, 0.15);
        }

        .form-control[type="date"] {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23999' class='bi bi-calendar3' viewBox='0 0 16 16'%3e%3cpath d='M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857V3.857z'/%3e%3cpath d='M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1rem;
            padding-right: 3rem;
        }

        .form-control[type="url"] {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23999' class='bi bi-link-45deg' viewBox='0 0 16 16'%3e%3cpath d='M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1.002 1.002 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4.018 4.018 0 0 1-.128-1.287z'/%3e%3cpath d='M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243L6.586 4.672z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1rem;
            padding-right: 3rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .word-limit {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .word-limit.warning {
            color: #fd7e14;
        }

        .word-limit.danger {
            color: #dc3545;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body> <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="d-flex flex-column align-items-center">
            <div class="spinner-border text-primary spinner" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-3 text-primary fw-medium">Processing, please wait...</div>
        </div>
    </div>

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
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">Abstract Settings</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">YBB Admin</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                                        <li class="breadcrumb-item active">Abstract Settings</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Abstract Settings</h4>
                                    <?php if ($abstractSettings): ?>
                                        <div class="flex-shrink-0">
                                            <button type="button" class="btn btn-primary btn-sm" onclick="showEditForm()">
                                                <i class="ri-edit-line align-bottom me-1"></i> Edit Settings
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body">
                                    <div class="live-preview">

                                        <?php if (!$abstractSettings): ?>
                                            <!-- No Settings Found -->
                                            <div class="text-center py-4">
                                                <div class="alert alert-warning" role="alert">
                                                    <h4 class="alert-heading">
                                                        <i class="ri-alert-line me-2"></i>Important Configuration Required
                                                    </h4>                                                    <p class="mb-0">
                                                        Abstract settings are not configured for this program yet. These settings are crucial as they control the word limits for abstract submissions including title, content, keywords, references, template files, and submission deadlines.
                                                    </p>
                                                </div>

                                                <div class="mt-4 mb-4">
                                                    <i class="ri-settings-3-line display-4 text-muted"></i>
                                                    <h5 class="mt-3 mb-2">No Abstract Settings Found</h5>
                                                    <p class="text-muted">Create default settings to get started with abstract submission management.</p>
                                                </div>

                                                <button type="button" class="btn btn-success btn-lg" onclick="createDefaultSettings()">
                                                    <i class="ri-add-line me-2"></i>Create Default Settings
                                                </button>
                                            </div>
                                        <?php else: ?>                                            <!-- Settings Display -->
                                            <div class="row">
                                                <div class="col-xxl-6">
                                                    <div class="card border card-border-primary">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0">Word Limits</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row gy-3">
                                                                <div class="col-sm-6">
                                                                    <label class="form-label text-muted">Title Limit</label>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="badge bg-primary-subtle text-primary fs-12">
                                                                            <?= number_format($abstractSettings->title_length) ?> words
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label class="form-label text-muted">Content Limit</label>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="badge bg-info-subtle text-info fs-12">
                                                                            <?= number_format($abstractSettings->content_length) ?> words
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label class="form-label text-muted">Keywords Limit</label>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="badge bg-success-subtle text-success fs-12">
                                                                            <?= number_format($abstractSettings->keywords_length) ?> words
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label class="form-label text-muted">References Limit</label>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="badge bg-warning-subtle text-warning fs-12">
                                                                            <?= number_format($abstractSettings->refs_length) ?> words
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xxl-6">
                                                    <div class="card border card-border-secondary">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0">Settings Information</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row gy-3">
                                                                <div class="col-sm-6">
                                                                    <label class="form-label text-muted">Status</label>
                                                                    <div class="d-flex align-items-center">
                                                                        <?php if ($abstractSettings->is_active): ?>
                                                                            <span class="badge bg-success-subtle text-success fs-12">
                                                                                <i class="ri-check-line me-1"></i>Active
                                                                            </span>
                                                                        <?php else: ?>
                                                                            <span class="badge bg-danger-subtle text-danger fs-12">
                                                                                <i class="ri-close-line me-1"></i>Inactive
                                                                            </span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label class="form-label text-muted">Last Updated</label>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="text-muted fs-12">
                                                                            <?= date('M j, Y g:i A', strtotime($abstractSettings->updated_at)) ?>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-12">
                                                                    <label class="form-label text-muted">Actions</label>
                                                                    <div class="d-flex gap-2 flex-wrap">
                                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetToDefault()">
                                                                            <i class="ri-restart-line me-1"></i>Reset to Default
                                                                        </button>
                                                                        <?php if ($abstractSettings->is_active): ?>
                                                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deactivateSettings()">
                                                                                <i class="ri-pause-line me-1"></i>Deactivate
                                                                            </button>
                                                                        <?php else: ?>
                                                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="showEditForm()">
                                                                                <i class="ri-play-line me-1"></i>Activate
                                                                            </button>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Additional Settings -->
                                            <div class="row mt-4">
                                                <div class="col-lg-12">
                                                    <div class="card border card-border-info">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0">Template & Deadlines</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row gy-3">
                                                                <div class="col-md-4">
                                                                    <label class="form-label text-muted">Paper Template</label>
                                                                    <div class="d-flex align-items-center">
                                                                        <?php if (!empty($abstractSettings->paper_template_url)): ?>
                                                                            <a href="<?= $abstractSettings->paper_template_url ?>" 
                                                                               target="_blank" class="btn btn-outline-primary btn-sm">
                                                                                <i class="ri-download-line me-1"></i>Download Template
                                                                            </a>
                                                                        <?php else: ?>
                                                                            <span class="text-muted fs-12">
                                                                                <i class="ri-file-line me-1"></i>No template uploaded
                                                                            </span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label text-muted">Abstract Deadline</label>
                                                                    <div class="d-flex align-items-center">
                                                                        <?php if (!empty($abstractSettings->abstract_submission_deadline)): ?>
                                                                            <span class="badge bg-warning-subtle text-warning fs-12">
                                                                                <i class="ri-calendar-line me-1"></i>
                                                                                <?= date('M j, Y', strtotime($abstractSettings->abstract_submission_deadline)) ?>
                                                                            </span>
                                                                        <?php else: ?>
                                                                            <span class="text-muted fs-12">
                                                                                <i class="ri-calendar-line me-1"></i>Not set
                                                                            </span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label text-muted">Full Paper Deadline</label>
                                                                    <div class="d-flex align-items-center">
                                                                        <?php if (!empty($abstractSettings->full_paper_submission_deadline)): ?>
                                                                            <span class="badge bg-danger-subtle text-danger fs-12">
                                                                                <i class="ri-calendar-line me-1"></i>
                                                                                <?= date('M j, Y', strtotime($abstractSettings->full_paper_submission_deadline)) ?>
                                                                            </span>
                                                                        <?php else: ?>
                                                                            <span class="text-muted fs-12">
                                                                                <i class="ri-calendar-line me-1"></i>Not set
                                                                            </span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Additional Info -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="card border card-border-light">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0">Default Values Reference</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <div class="text-center p-3 border rounded">
                                                                        <h6 class="mb-1">Title</h6>
                                                                        <span class="text-muted fs-12">15 words</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="text-center p-3 border rounded">
                                                                        <h6 class="mb-1">Content</h6>
                                                                        <span class="text-muted fs-12">500 words</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="text-center p-3 border rounded">
                                                                        <h6 class="mb-1">Keywords</h6>
                                                                        <span class="text-muted fs-12">5 words</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="text-center p-3 border rounded">
                                                                        <h6 class="mb-1">References</h6>
                                                                        <span class="text-muted fs-12">100 words</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

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
    <!-- END layout-wrapper -->    <!-- Edit Settings Modal -->
    <div class="modal fade" id="editSettingsModal" tabindex="-1" aria-labelledby="editSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSettingsModalLabel">Edit Abstract Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editSettingsForm">
                    <div class="modal-body">
                        <!-- Word Limits Section -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">Word Limits Configuration</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title_length" class="form-label">Title Word Limit <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="title_length" name="title_length"
                                            value="<?= $abstractSettings->title_length ?? 15 ?>" min="1" max="200" required>
                                        <div class="form-text">Maximum words allowed for abstract titles</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="content_length" class="form-label">Content Word Limit <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="content_length" name="content_length"
                                            value="<?= $abstractSettings->content_length ?? 500 ?>" min="1" max="2000" required>
                                        <div class="form-text">Maximum words allowed for abstract content</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="keywords_length" class="form-label">Keywords Word Limit <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="keywords_length" name="keywords_length"
                                            value="<?= $abstractSettings->keywords_length ?? 5 ?>" min="1" max="100" required>
                                        <div class="form-text">Maximum words allowed for keywords</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="refs_length" class="form-label">References Word Limit <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="refs_length" name="refs_length"
                                            value="<?= $abstractSettings->refs_length ?? 100 ?>" min="1" max="500" required>
                                        <div class="form-text">Maximum words allowed for references</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Template & Deadlines Section -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">Template & Deadlines Configuration</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="paper_template_url" class="form-label">Paper Template URL</label>
                                        <input type="url" class="form-control" id="paper_template_url" name="paper_template_url"
                                            value="<?= $abstractSettings->paper_template_url ?? '' ?>" placeholder="https://example.com/template.pdf">
                                        <div class="form-text">URL link to the paper template file (PDF, DOC, etc.)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="abstract_submission_deadline" class="form-label">Abstract Submission Deadline</label>
                                        <input type="date" class="form-control" id="abstract_submission_deadline" name="abstract_submission_deadline"
                                            value="<?= !empty($abstractSettings->abstract_submission_deadline) ? date('Y-m-d', strtotime($abstractSettings->abstract_submission_deadline)) : '' ?>">
                                        <div class="form-text">Deadline for abstract submissions</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="full_paper_submission_deadline" class="form-label">Full Paper Submission Deadline</label>
                                        <input type="date" class="form-control" id="full_paper_submission_deadline" name="full_paper_submission_deadline"
                                            value="<?= !empty($abstractSettings->full_paper_submission_deadline) ? date('Y-m-d', strtotime($abstractSettings->full_paper_submission_deadline)) : '' ?>">
                                        <div class="form-text">Deadline for full paper submissions</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Section -->
                        <div class="mb-3">
                            <h6 class="border-bottom pb-2 mb-3">Status Configuration</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                            <?= ($abstractSettings->is_active ?? true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_active">
                                            Active Settings
                                        </label>
                                        <div class="form-text">Enable these settings for abstract submissions</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-2"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?= $this->include('partials/vendor-scripts') ?>

    <script>
        // Show loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }

        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }

        function createDefaultSettings() {
            Swal.fire({
                title: 'Create Default Settings?',
                text: 'This will create default abstract settings with standard word limits.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Create Settings',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#667eea'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    fetch('<?= base_url('master-data/abstract-settings/createDefault') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            hideLoading();
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#667eea'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message,
                                    icon: 'error',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        })
                        .catch(error => {
                            hideLoading();
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while creating settings.',
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        });
                }
            });
        }

        function showEditForm() {
            const modal = new bootstrap.Modal(document.getElementById('editSettingsModal'));
            modal.show();
        }

        function resetToDefault() {
            Swal.fire({
                title: 'Reset to Default?',
                text: 'This will reset all settings to their default values.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Reset Settings',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ffc107'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    fetch('<?= base_url('master-data/abstract-settings/reset') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            hideLoading();
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#667eea'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message,
                                    icon: 'error',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        })
                        .catch(error => {
                            hideLoading();
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while resetting settings.',
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        });
                }
            });
        }

        function deactivateSettings() {
            Swal.fire({
                title: 'Deactivate Settings?',
                text: 'This will disable abstract settings for this program.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Deactivate',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    fetch('<?= base_url('master-data/abstract-settings/deactivate') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            hideLoading();
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#667eea'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message,
                                    icon: 'error',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        })
                        .catch(error => {
                            hideLoading();
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while deactivating settings.',
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        });
                }
            });
        }        // Handle form submission
        document.getElementById('editSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate date logic
            const abstractDeadline = document.getElementById('abstract_submission_deadline').value;
            const fullPaperDeadline = document.getElementById('full_paper_submission_deadline').value;

            if (abstractDeadline && fullPaperDeadline) {
                const abstractDate = new Date(abstractDeadline);
                const fullPaperDate = new Date(fullPaperDeadline);

                if (fullPaperDate <= abstractDate) {
                    Swal.fire({
                        title: 'Invalid Dates!',
                        text: 'Full paper submission deadline must be after abstract submission deadline.',
                        icon: 'warning',
                        confirmButtonColor: '#fd7e14'
                    });
                    return;
                }
            }

            // Show loading overlay
            showLoading();

            const formData = new FormData(this);

            fetch('<?= base_url('master-data/abstract-settings/save') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loading overlay
                    hideLoading();

                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#667eea'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    // Hide loading overlay
                    hideLoading();

                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while saving settings.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                });
        });

        // Add date validation helpers
        document.getElementById('abstract_submission_deadline').addEventListener('change', function() {
            const fullPaperInput = document.getElementById('full_paper_submission_deadline');
            if (this.value) {
                // Set minimum date for full paper to be one day after abstract deadline
                const abstractDate = new Date(this.value);
                abstractDate.setDate(abstractDate.getDate() + 1);
                fullPaperInput.min = abstractDate.toISOString().split('T')[0];
            } else {
                fullPaperInput.min = '';
            }
        });

        // URL validation helper
        document.getElementById('paper_template_url').addEventListener('blur', function() {
            const url = this.value.trim();
            if (url && !isValidUrl(url)) {
                this.classList.add('is-invalid');
                if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('invalid-feedback')) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = 'Please enter a valid URL (e.g., https://example.com/file.pdf)';
                    this.parentNode.appendChild(feedback);
                }
            } else {
                this.classList.remove('is-invalid');
                const feedback = this.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.remove();
                }
            }
        });

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
    </script>

</body>

</html>