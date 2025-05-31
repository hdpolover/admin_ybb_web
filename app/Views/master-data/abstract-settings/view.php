<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title' => 'View Abstract Settings')); ?>

    <?= $this->include('partials/head-css') ?>

    <style>
        .settings-card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .settings-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 1.5rem;
        }
        
        .setting-item {
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 0;
        }
        
        .setting-item:last-child {
            border-bottom: none;
        }
        
        .setting-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .setting-value {
            font-size: 1.1rem;
            color: #212529;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 6px;
            padding: 0.75rem 1.5rem;
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
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">View Abstract Settings</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">YBB Admin</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                                        <li class="breadcrumb-item"><a href="<?= base_url('master-data/abstract-settings') ?>">Abstract Settings</a></li>
                                        <li class="breadcrumb-item active">View</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            
                            <?php if (!$abstractSettings): ?>
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="ri-settings-3-line display-4 text-muted mb-3"></i>
                                        <h5>No Abstract Settings Found</h5>
                                        <p class="text-muted">Abstract settings have not been configured for this program yet.</p>
                                        <a href="<?= base_url('master-data/abstract-settings') ?>" class="btn btn-primary">
                                            <i class="ri-arrow-left-line me-2"></i>Back to Settings
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card settings-card">
                                    <div class="settings-header">
                                        <h4 class="mb-0"><i class="ri-settings-3-line me-2"></i>Abstract Settings Details</h4>
                                        <p class="mb-0 mt-2 opacity-75">Current submission limits for abstracts</p>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="setting-item">
                                            <div class="setting-label">Title Character Limit</div>
                                            <div class="setting-value">
                                                <span class="badge bg-primary-subtle text-primary fs-6">
                                                    <?= number_format($abstractSettings->title_length) ?> characters
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="setting-item">
                                            <div class="setting-label">Content Character Limit</div>
                                            <div class="setting-value">
                                                <span class="badge bg-info-subtle text-info fs-6">
                                                    <?= number_format($abstractSettings->content_length) ?> characters
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="setting-item">
                                            <div class="setting-label">Keywords Character Limit</div>
                                            <div class="setting-value">
                                                <span class="badge bg-success-subtle text-success fs-6">
                                                    <?= number_format($abstractSettings->keywords_length) ?> characters
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="setting-item">
                                            <div class="setting-label">References Character Limit</div>
                                            <div class="setting-value">
                                                <span class="badge bg-warning-subtle text-warning fs-6">
                                                    <?= number_format($abstractSettings->refs_length) ?> characters
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="setting-item">
                                            <div class="setting-label">Status</div>
                                            <div class="setting-value">
                                                <?php if ($abstractSettings->is_active): ?>
                                                    <span class="badge bg-success fs-6">
                                                        <i class="ri-check-line me-1"></i>Active
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger fs-6">
                                                        <i class="ri-close-line me-1"></i>Inactive
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="setting-item">
                                            <div class="setting-label">Created</div>
                                            <div class="setting-value text-muted">
                                                <?= date('F j, Y \a\t g:i A', strtotime($abstractSettings->created_at)) ?>
                                            </div>
                                        </div>
                                        
                                        <div class="setting-item">
                                            <div class="setting-label">Last Updated</div>
                                            <div class="setting-value text-muted">
                                                <?= date('F j, Y \a\t g:i A', strtotime($abstractSettings->updated_at)) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light">
                                        <div class="text-center">
                                            <a href="<?= base_url('master-data/abstract-settings') ?>" class="btn btn-primary">
                                                <i class="ri-arrow-left-line me-2"></i>Back to Settings
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

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

</body>

</html>
