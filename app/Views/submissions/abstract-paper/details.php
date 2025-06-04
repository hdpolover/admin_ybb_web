<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Abstract Details')); ?>
    <?= $this->include('partials/head-css') ?>

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <style>
        /* Card styling */
        .card {
            border-radius: 0.25rem;
            border: 1px solid rgba(0, 0, 0, 0.125);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 0.75rem 1.25rem;
        }

        .card-header h5 {
            margin-bottom: 0;
            font-weight: 500;
        }

        .card-body {
            padding: 1.25rem;
        }

        /* Version selector styles */
        .version-selector {
            border: 1px solid #dee2e6;
            padding: 10px;
            border-radius: 0.25rem;
            margin-bottom: 10px;
            cursor: pointer;
            background-color: #f8f9fa;
        }

        .version-selector:hover {
            background-color: #e9ecef;
        }

        .version-selector.selected {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }

        .version-selector.loading-selection {
            border-color: #6c757d;
            background-color: #f0f0f0;
            position: relative;
        }

        .version-selector.loading-selection::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(1px);
        }

        /* Stats cards */
        .stats-card {
            padding: 1rem;
            text-align: center;
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 0.25rem;
        }

        /* Loading spinner */
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            text-align: center;
            min-height: 100px;
        }

        .loading-spinner .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .stats-card .stats-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 0.75rem;
            font-size: 1.25rem;
        }

        .stats-card .stats-value {
            font-size: 1.75rem;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 0.25rem;
        }

        .stats-card .stats-label {
            font-size: 0.8125rem;
            color: #6c757d;
            text-transform: uppercase;
        }

        /* Author cards */
        .author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .author-card {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: #f8f9fa;
            margin-bottom: 0.75rem;
            border: 1px solid #e9ecef;
            border-radius: 0.25rem;
        }

        /* Version timeline */
        .version-timeline {
            position: relative;
            padding-left: 1.5rem;
        }

        .version-timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .version-item {
            position: relative;
            margin-bottom: 1.25rem;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-left: 1rem;
        }

        .version-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 1.25rem;
            width: 10px;
            height: 10px;
            background: #6c757d;
            border-radius: 50%;
            border: 2px solid white;
        }

        .version-item.latest::before {
            background: #28a745;
        }

        /* Keywords styling */
        .keywords-tag {
            background: #f1f8ff;
            color: #0d6efd;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.8125rem;
            margin: 0.25rem;
            display: inline-block;
        }

        /* Abstract content styling */
        .abstract-content {
            line-height: 1.6;
            color: #495057;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
            border: 1px solid #e9ecef;
        }

        /* Navigation & Action buttons */
        .action-btn {
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        /* Status badges */
        .status-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
            font-weight: 500;
        }

        /* Content truncation */
        .text-truncate-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Additional utility classes */
        .text-purple {
            color: #6f42c1 !important;
        }

        .bg-purple-subtle {
            background-color: rgba(111, 66, 193, 0.1) !important;
        }

        .bg-primary-subtle {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }

        .bg-success-subtle {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }

        .bg-info-subtle {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        .bg-warning-subtle {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .bg-danger-subtle {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        .btn-soft-primary {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            border: 1px solid rgba(13, 110, 253, 0.1);
        }

        .btn-soft-info {
            background-color: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
            border: 1px solid rgba(13, 202, 240, 0.1);
        }

        .btn-danger-subtle {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.1);
        }

        /* Badge styling */
        .badge {
            font-weight: 500;
            letter-spacing: 0.3px;
        }
    </style>
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <?php echo view('partials/page-title', array('pagetitle' => 'Submissions', 'title' => 'Abstract Details')); ?> <!-- Navigation Bar -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <a href="<?= base_url('submissions/abstracts-papers') ?>" class="btn btn-light action-btn">
                                                <i class="ri-arrow-left-line me-1"></i> Back to List
                                            </a>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-primary action-btn" onclick="openVersionComparison()">
                                                <i class="ri-git-branch-line me-1"></i> Compare Versions
                                            </button>
                                            <?php if (!session('adminId')): ?>
                                                <button type="button" class="btn btn-success action-btn" onclick="openEditModal()">
                                                    <i class="ri-pencil-line me-1"></i> Edit
                                                </button>
                                            <?php endif; ?>
                                            <div class="dropdown">
                                                <button class="btn btn-light action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-settings-3-line me-1"></i> Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#" onclick="quickAction('accept')"><i class="ri-check-line me-2 text-success"></i>Accept</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="quickAction('reject')"><i class="ri-close-line me-2 text-danger"></i>Reject</a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><a class="dropdown-item" href="#" onclick="downloadPDF()"><i class="ri-download-line me-2"></i>Download PDF</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="exportData()"><i class="ri-file-export-line me-2"></i>Export Data</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Abstract Header Card -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="card-title mb-1"><?= esc($abstract->title ?? 'Untitled Abstract') ?></h4>
                                        <p class="text-muted mb-0">
                                            <i class="ri-user-line me-1"></i><?= esc($participant->first_name ?? '') ?> <?= esc($participant->last_name ?? '') ?>
                                            <?php if (!empty($topic)): ?>
                                                | <i class="ri-bookmark-line me-1"></i><?= esc($topic->name) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <span class="badge bg-<?= $abstract->status === 'submitted' ? 'primary' : ($abstract->status === 'accepted' ? 'success' : ($abstract->status === 'rejected' ? 'danger' : 'warning')) ?> fs-6">
                                        <?= ucfirst(str_replace('_', ' ', $abstract->status ?? 'submitted')) ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div style="width: 36px; height: 36px; background-color: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                                    <i class="ri-calendar-line text-primary" style="font-size: 1.15rem;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Submitted</small>
                                                    <p class="mb-0 fw-medium"><?= date('F j, Y', strtotime($abstract->created_at)) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div style="width: 36px; height: 36px; background-color: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                                    <i class="ri-time-line text-success" style="font-size: 1.15rem;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Last Updated</small>
                                                    <p class="mb-0 fw-medium"><?= $version_stats['last_update'] ? date('F j, Y', strtotime($version_stats['last_update'])) : 'N/A' ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div style="width: 36px; height: 36px; background-color: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                                    <i class="ri-building-line text-info" style="font-size: 1.15rem;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Institution</small>
                                                    <p class="mb-0 fw-medium"><?= esc($participant->institution ?? 'N/A') ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div style="width: 36px; height: 36px; background-color: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                                    <i class="ri-mail-line text-warning" style="font-size: 1.15rem;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Email</small>
                                                    <p class="mb-0 fw-medium"><?= esc($participant->email ?? 'N/A') ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <i class="ri-file-list-3-line text-primary fs-4"></i>
                                    </div>
                                    <h4 class="card-title mb-1"><?= $version_stats['total_versions'] ?></h4>
                                    <p class="text-muted mb-0">Total Versions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <i class="ri-file-paper-2-line text-success fs-4"></i>
                                    </div>
                                    <h4 class="card-title mb-1"><?= $version_stats['latest_version'] ?></h4>
                                    <p class="text-muted mb-0">Latest Version</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <i class="ri-user-star-line text-info fs-4"></i>
                                    </div>
                                    <h4 class="card-title mb-1"><?= count($authors) ?></h4>
                                    <p class="text-muted mb-0">Authors</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <i class="ri-history-line text-warning fs-4"></i>
                                    </div>
                                    <h4 class="card-title mb-1">
                                        <?= $version_stats['last_update'] ? date('M j', strtotime($version_stats['last_update'])) : 'N/A' ?>
                                    </h4>
                                    <p class="text-muted mb-0">Last Update</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Main Content -->
                        <div class="col-lg-8"> <!-- Current Version Display -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-file-text-line me-2"></i>
                                        Current Version <span class="badge bg-primary"><?= $versions[0]->version_number ?? 'N/A' ?></span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="abstract-content mb-4">
                                        <?php
                                        $content = $versions[0]->content ?? 'No content available';
                                        // Check if content contains HTML tags (likely from Quill editor)
                                        if (strip_tags($content) !== $content) {
                                            // Content has HTML tags, display as HTML (sanitized)
                                            echo $content;
                                        } else {
                                            // Plain text content, escape and add line breaks
                                            echo nl2br(esc($content));
                                        }
                                        ?>
                                    </div>

                                    <?php if (!empty($versions[0]->keywords)): ?>
                                        <div class="keywords-section mt-4 pt-3 border-top">
                                            <h6 class="mb-3"><i class="ri-price-tag-3-line me-2"></i>Keywords:</h6>
                                            <div>
                                                <?php foreach (explode(',', $versions[0]->keywords) as $keyword): ?>
                                                    <span class="keywords-tag"><?= trim(esc($keyword)) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div> <!-- Version History -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-history-line me-2"></i>
                                        Version History
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="version-timeline">
                                        <?php foreach ($versions as $index => $version): ?> <div class="version-item <?= $index === 0 ? 'latest' : '' ?>">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h6 class="mb-1">Version <?= $version->version_number ?> <?= $index === 0 ? '<span class="badge bg-success">Latest</span>' : '' ?></h6>
                                                        <p class="text-muted mb-1"><?= $version->title ?></p>
                                                        <small class="text-muted">
                                                            <i class="ri-time-line me-1"></i><?= date('F j, Y \a\t g:i A', strtotime($version->created_at)) ?>
                                                        </small>
                                                    </div>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="viewFullVersion(<?= $version->id ?>)">
                                                            <i class="ri-eye-line me-1"></i> View
                                                        </button>
                                                        <?php if ($index > 0): ?>
                                                            <button class="btn btn-outline-info" onclick="compareWithCurrent(<?= $version->id ?>)">
                                                                <i class="ri-git-branch-line me-1"></i> Compare
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="version-content text-truncate-3 bg-light p-3 border rounded">
                                                    <?php
                                                    $versionContent = substr($version->content, 0, 150);
                                                    if (strip_tags($versionContent) !== $versionContent) {
                                                        echo $versionContent . (strlen($version->content) > 150 ? '...' : '');
                                                    } else {
                                                        echo nl2br(esc($versionContent)) . (strlen($version->content) > 150 ? '...' : '');
                                                    }
                                                    ?>
                                                </div>

                                                <?php if (!empty($version->keywords)): ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted"><strong>Keywords:</strong></small>
                                                        <div>
                                                            <?php foreach (explode(',', $version->keywords) as $keyword): ?>
                                                                <span class="keywords-tag"><?= trim(esc($keyword)) ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (!empty($version->references)): ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted"><strong>References:</strong> Available</small>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <strong>Updated:</strong> <?= date('F j, Y \a\t g:i A', strtotime($version->updated_at)) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- Sidebar -->
                        <div class="col-lg-4">
                            <!-- Quick Actions Card -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-settings-3-line me-2"></i>
                                        Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary" onclick="openVersionComparison()">
                                            <i class="ri-git-branch-line me-1"></i> Compare Versions
                                        </button>

                                        <?php if (!session('adminId')): ?>
                                            <button type="button" class="btn btn-outline-success" onclick="openEditModal()">
                                                <i class="ri-pencil-line me-1"></i> Edit Abstract
                                            </button>
                                        <?php endif; ?>

                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-2-line me-1"></i> More Actions
                                            </button>
                                            <ul class="dropdown-menu w-100">
                                                <li><a class="dropdown-item" href="#" onclick="quickAction('accept')"><i class="ri-check-line me-2 text-success"></i>Accept</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="quickAction('reject')"><i class="ri-close-line me-2 text-danger"></i>Reject</a></li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li><a class="dropdown-item" href="#" onclick="downloadPDF()"><i class="ri-download-line me-2"></i>Download PDF</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="exportData()"><i class="ri-file-export-line me-2"></i>Export Data</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistics Card -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-bar-chart-line me-2"></i>
                                        Statistics
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="p-3 border rounded bg-light text-center">
                                                <div class="mb-2"><i class="ri-file-list-3-line text-primary"></i></div>
                                                <div class="fs-4 fw-bold"><?= count($versions) ?></div>
                                                <div class="small text-muted">Versions</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-3 border rounded bg-light text-center">
                                                <div class="mb-2"><i class="ri-team-line text-success"></i></div>
                                                <div class="fs-4 fw-bold"><?= count($authors) ?></div>
                                                <div class="small text-muted">Authors</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-3 border rounded bg-light text-center">
                                                <div class="mb-2"><i class="ri-text text-info"></i></div>
                                                <div class="fs-4 fw-bold"><?= str_word_count($versions[0]->content ?? '') ?></div>
                                                <div class="small text-muted">Words</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-3 border rounded bg-light text-center">
                                                <div class="mb-2"><i class="ri-calendar-check-line text-warning"></i></div>
                                                <div class="fs-4 fw-bold"><?= date('M j', strtotime($abstract->created_at)) ?></div>
                                                <div class="small text-muted">Submitted</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Authors Card -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-team-line me-2"></i>
                                        Authors <span class="badge bg-secondary rounded-pill"><?= count($authors) ?></span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($authors)): ?>
                                        <div class="authors-list">
                                            <?php foreach ($authors as $author): ?>
                                                <div class="author-card">
                                                    <div class="author-avatar">
                                                        <?= strtoupper(substr($author->full_name, 0, 1)) ?>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-medium mb-1"><?= htmlspecialchars($author->full_name) ?></div>
                                                        <?php if (!empty($author->institution)): ?>
                                                            <div class="small text-muted mb-1"><i class="ri-building-line me-1"></i><?= htmlspecialchars($author->institution) ?></div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($author->email)): ?>
                                                            <div class="small text-muted"><i class="ri-mail-line me-1"></i><?= htmlspecialchars($author->email) ?></div>
                                                        <?php endif; ?>
                                                        <?php if ($author->is_participant): ?>
                                                            <span class="badge bg-info mt-2">Participant</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <div class="text-muted mb-2">
                                                <i class="ri-user-line" style="font-size: 2rem;"></i>
                                            </div>
                                            <p class="text-muted">No authors listed</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- Feedback Section -->
                <div class="container-fluid">
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-message-3-line me-2"></i>
                                        Reviewer Feedback
                                    </h5>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openAddFeedbackModal()">
                                        <i class="ri-add-line me-1"></i>Add Feedback
                                    </button>
                                </div>
                                <div class="card-body">
                                    <!-- Feedback List -->
                                    <div id="feedbackList">
                                        <!-- Feedback items will be loaded here -->
                                        <div class="text-center py-4" id="noFeedbackMessage">
                                            <i class="ri-message-3-line text-muted" style="font-size: 2rem;"></i>
                                            <h6 class="text-muted mt-2">No feedback available</h6>
                                            <p class="text-muted mb-0">Reviewer feedback will appear here once submitted.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Feedback Modal -->
        <div class="modal fade" id="addFeedbackModal" tabindex="-1" aria-labelledby="addFeedbackModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addFeedbackModalLabel">
                            <i class="ri-add-line me-2"></i>Add Reviewer Feedback
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addFeedbackForm">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="feedbackReviewer" class="form-label">Reviewer <span class="text-danger">*</span></label>
                                        <select class="form-select" id="feedbackReviewer" name="reviewer_id" required>
                                            <option value="">Select Reviewer</option>
                                            <!-- Reviewers will be loaded dynamically -->
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="feedbackVersion" class="form-label">Abstract Version <span class="text-danger">*</span></label> <select class="form-select" id="feedbackVersion" name="abstract_version_id" required>
                                            <option value="">Select Version</option>
                                            <?php if (!empty($versions)): ?>
                                                <?php foreach ($versions as $version): ?>
                                                    <option value="<?= $version->id ?>">
                                                        Version <?= $version->version_number ?> - <?= date('M j, Y \a\t g:i A', strtotime($version->created_at)) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="feedbackContent" class="form-label">Feedback Content <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="feedbackContent" name="feedback" rows="8" placeholder="Enter detailed feedback, comments, and suggestions..." required></textarea>
                                <div class="form-text">
                                    <i class="ri-information-line me-1"></i>
                                    Provide constructive feedback to help improve the abstract quality.
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="feedbackRating" class="form-label">Overall Rating</label>
                                        <select class="form-select" id="feedbackRating" name="rating">
                                            <option value="">No Rating</option>
                                            <option value="5">5 - Excellent</option>
                                            <option value="4">4 - Good</option>
                                            <option value="3">3 - Average</option>
                                            <option value="2">2 - Needs Improvement</option>
                                            <option value="1">1 - Poor</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="feedbackStatus" class="form-label">Status</label>
                                        <select class="form-select" id="feedbackStatus" name="status">
                                            <option value="draft">Draft</option>
                                            <option value="submitted" selected>Submitted</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="ri-close-line me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i>Save Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Feedback Modal -->
        <div class="modal fade" id="editFeedbackModal" tabindex="-1" aria-labelledby="editFeedbackModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFeedbackModalLabel">
                            <i class="ri-edit-line me-2"></i>Edit Reviewer Feedback
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editFeedbackForm">
                        <input type="hidden" id="editFeedbackId" name="feedback_id">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editFeedbackReviewer" class="form-label">Reviewer <span class="text-danger">*</span></label>
                                        <select class="form-select" id="editFeedbackReviewer" name="reviewer_id" required>
                                            <option value="">Select Reviewer</option>
                                            <!-- Reviewers will be loaded dynamically -->
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editFeedbackVersion" class="form-label">Abstract Version <span class="text-danger">*</span></label>
                                        <select class="form-select" id="editFeedbackVersion" name="abstract_version_id" required>
                                            <option value="">Select Version</option>
                                            <?php if (!empty($versions)): ?>
                                                <?php foreach ($versions as $version): ?>
                                                    <option value="<?= $version->id ?>">
                                                        Version <?= $version->version_number ?> - <?= date('M j, Y', strtotime($version->created_at)) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="editFeedbackContent" class="form-label">Feedback Content <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="editFeedbackContent" name="feedback" rows="8" placeholder="Enter detailed feedback, comments, and suggestions..." required></textarea>
                                <div class="form-text">
                                    <i class="ri-information-line me-1"></i>
                                    Provide constructive feedback to help improve the abstract quality.
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editFeedbackRating" class="form-label">Overall Rating</label>
                                        <select class="form-select" id="editFeedbackRating" name="rating">
                                            <option value="">No Rating</option>
                                            <option value="5">5 - Excellent</option>
                                            <option value="4">4 - Good</option>
                                            <option value="3">3 - Average</option>
                                            <option value="2">2 - Needs Improvement</option>
                                            <option value="1">1 - Poor</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editFeedbackStatus" class="form-label">Status</label>
                                        <select class="form-select" id="editFeedbackStatus" name="status">
                                            <option value="draft">Draft</option>
                                            <option value="submitted">Submitted</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="ri-close-line me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i>Update Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Feedback Modal -->
        <div class="modal fade" id="viewFeedbackModal" tabindex="-1" aria-labelledby="viewFeedbackModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewFeedbackModalLabel">
                            <i class="ri-eye-line me-2"></i>Feedback Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Reviewer</h6>
                                <p id="viewFeedbackReviewer" class="mb-0"></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Abstract Version</h6>
                                <p id="viewFeedbackVersion" class="mb-0"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Rating</h6>
                                <div id="viewFeedbackRating"></div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Status</h6>
                                <span id="viewFeedbackStatus" class="badge"></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-muted">Feedback Content</h6>
                            <div id="viewFeedbackContent" class="border rounded p-3 bg-light"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Created At</h6>
                                <p id="viewFeedbackCreated" class="mb-0"></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Last Updated</h6>
                                <p id="viewFeedbackUpdated" class="mb-0"></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                        <button type="button" class="btn btn-primary" onclick="editFeedbackFromView()">
                            <i class="ri-edit-line me-1"></i>Edit Feedback
                        </button>
                    </div>
                </div>
            </div>
        </div> <!-- Version Comparison Modal -->
        <div class="modal fade comparison-modal" id="comparisonModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mb-0">
                            <i class="ri-git-branch-line me-2"></i>
                            Version Comparison
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Version Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="mb-3">Select First Version:</h6>
                                <div id="version1-selector" class="version-selectors">
                                    <!-- Version options will be loaded here -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3">Select Second Version:</h6>
                                <div id="version2-selector" class="version-selectors">
                                    <!-- Version options will be loaded here -->
                                </div>
                            </div>
                        </div> <!-- Comparison Results -->
                        <div id="comparisonResults" class="d-none">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-git-branch-line me-2"></i>
                                        Comparison Results
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div id="comparisonSummary" class="p-3 bg-light rounded border mb-4"></div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="mb-3" id="version1Title">Version 1</h6>
                                            <div id="version1Content" class="p-3 bg-light rounded border"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="mb-3" id="version2Title">Version 2</h6>
                                            <div id="version2Content" class="p-3 bg-light rounded border"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="compareBtn" onclick="performComparison()" disabled>
                            <span class="d-flex align-items-center">
                                <i class="ri-git-branch-line me-1"></i>
                                <span id="compareBtnText">Compare Selected Versions</span>
                                <span id="compareBtnSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div> <!-- View Full Version Modal -->
        <div class="modal fade" id="fullVersionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mb-0" id="fullVersionTitle">
                            <i class="ri-file-text-line me-2"></i>
                            Version Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="fullVersionContent" class="abstract-content bg-light p-3 rounded border"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <?= $this->include('partials/vendor-scripts') ?>

        <script>
            // Helper functions for HTML content handling
            function escapeHtml(unsafe) {
                if (!unsafe) return '';
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            function isHtml(str) {
                if (!str) return false;
                // Simple check for HTML content
                return /<[a-z][\s\S]*>/i.test(str);
            }
            let abstractId = <?= $abstract->id ?>;
            let versions = <?= json_encode($versions) ?>;
            let selectedVersion1 = null;
            let selectedVersion2 = null;
            let baseUrl = '<?= base_url() ?>';

            $(document).ready(function() {
                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                loadVersionSelectors();
            });

            // Quick action functions
            function quickAction(action) {
                Swal.fire({
                    title: `${action.charAt(0).toUpperCase() + action.slice(1)} Abstract?`,
                    text: `Are you sure you want to ${action} this abstract?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: action === 'accept' ? '#28a745' : '#dc3545',
                    confirmButtonText: `Yes, ${action} it!`
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `${baseUrl}/submissions/abstracts-papers/quick-action`,
                            method: 'POST',
                            data: {
                                abstract_id: abstractId,
                                action: action,
                                csrf_token_name: '<?= csrf_token() ?>',
                                csrf_hash: '<?= csrf_hash() ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: `Abstract has been ${action}ed successfully.`,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload(); // Reload to show updated status
                                    });
                                } else {
                                    Swal.fire('Error', response.message || 'An error occurred', 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to perform action. Please try again.', 'error');
                            }
                        });
                    }
                });
            }

            function downloadPDF() {
                // Show loading
                Swal.fire({
                    title: 'Generating PDF...',
                    text: 'Please wait while we generate your PDF.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Simulate PDF generation (replace with actual endpoint)
                setTimeout(() => {
                    Swal.close();
                    window.open(`${baseUrl}/submissions/abstracts-papers/pdf/${abstractId}`, '_blank');
                }, 2000);
            }

            function exportData() {
                Swal.fire({
                    title: 'Export Data',
                    text: 'Choose export format:',
                    showCancelButton: true,
                    showCloseButton: true,
                    confirmButtonText: 'Excel',
                    cancelButtonText: 'CSV',
                    showDenyButton: true,
                    denyButtonText: 'JSON'
                }).then((result) => {
                    let format = 'excel';
                    if (result.isDenied) format = 'json';
                    else if (result.dismiss === Swal.DismissReason.cancel) format = 'csv';
                    else if (!result.isConfirmed) return;

                    window.location.href = `${baseUrl}/submissions/abstracts-papers/export/${abstractId}?format=${format}`;
                });
            }

            function loadVersionSelectors() {
                // Clear previous selectors
                $('#version1-selector, #version2-selector').empty();

                // Create selectors for each version
                versions.forEach(function(version) {
                    const $version1Item = $(`
                        <div class="version-selector mb-2" data-version-id="${version.id}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <strong>Version ${version.version_number}</strong>
                                    <div class="small text-muted">${escapeHtml(version.title)}</div>
                                </div>
                                <div>
                                    <span class="badge ${version.status === 'submitted' ? 'bg-primary' : (version.status === 'accepted' ? 'bg-success' : (version.status === 'rejected' ? 'bg-danger' : 'bg-warning'))}">${escapeHtml(version.status)}</span>
                                    <div class="small text-muted">${new Date(version.created_at).toLocaleString()}</div>
                                </div>
                            </div>
                            ${version.keywords ? `
                                <div class="small text-muted mt-1 mb-1">
                                    <strong>Keywords:</strong> ${escapeHtml(version.keywords)}
                                </div>
                            ` : ''}
                            ${version.refs ? `
                                <div class="small text-muted">
                                    <strong>Has References:</strong> Yes
                                </div>
                            ` : ''}
                        </div>
                    `);

                    const $version2Item = $version1Item.clone(); // Add click handlers
                    $version1Item.on('click', function() {
                        console.log('Version 1 selector clicked:', version.id);

                        // Show visual feedback
                        $(this).addClass('loading-selection');

                        // Use setTimeout to allow the UI to update
                        setTimeout(() => {
                            $('#version1-selector .version-selector').removeClass('selected loading-selection');
                            $(this).addClass('selected');
                            selectedVersion1 = version.id;
                            updateCompareButtonState();
                            console.log('Version 1 selected:', selectedVersion1);
                        }, 100);
                    });

                    $version2Item.on('click', function() {
                        console.log('Version 2 selector clicked:', version.id);

                        // Show visual feedback
                        $(this).addClass('loading-selection');

                        // Use setTimeout to allow the UI to update
                        setTimeout(() => {
                            $('#version2-selector .version-selector').removeClass('selected loading-selection');
                            $(this).addClass('selected');
                            selectedVersion2 = version.id;
                            updateCompareButtonState();
                            console.log('Version 2 selected:', selectedVersion2);
                        }, 100);
                    });

                    // Append to respective containers                    
                    $('#version1-selector').append($version1Item);
                    $('#version2-selector').append($version2Item);
                });
            }

            function updateCompareButtonState() {
                console.log('updateCompareButtonState called', {
                    version1: selectedVersion1,
                    version2: selectedVersion2
                });

                // Enable compare button only if two different versions are selected
                if (selectedVersion1 && selectedVersion2 && selectedVersion1 !== selectedVersion2) {
                    $('#compareBtn').prop('disabled', false);
                    console.log('Compare button enabled');
                } else {
                    $('#compareBtn').prop('disabled', true);
                    console.log('Compare button disabled');
                }
            }

            function openVersionComparison() {
                console.log('openVersionComparison called');

                // Show loading using SweetAlert
                Swal.fire({
                    title: 'Opening Version Comparison',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    timer: 1000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Reset state
                $('#comparisonResults').addClass('d-none');
                selectedVersion1 = null;
                selectedVersion2 = null;

                console.log('Resetting comparison state');

                // Show modal
                $('#comparisonModal').modal('show');

                // Reset selections
                $('#version1-selector .version-selector, #version2-selector .version-selector').removeClass('selected');

                // Disable compare button
                $('#compareBtn').prop('disabled', true);
                console.log('Version comparison modal opened');
            }

            function performComparison() {
                console.log('performComparison called');

                if (!selectedVersion1 || !selectedVersion2) {
                    console.log('Error: Missing versions', {
                        selectedVersion1,
                        selectedVersion2
                    });
                    Swal.fire('Error', 'Please select two different versions to compare', 'error');
                    return;
                }

                if (selectedVersion1 === selectedVersion2) {
                    console.log('Error: Same versions selected', {
                        selectedVersion1,
                        selectedVersion2
                    });
                    Swal.fire('Error', 'Please select two different versions', 'error');
                    return;
                }

                // Show loading using SweetAlert
                Swal.fire({
                    title: 'Comparing Versions',
                    text: 'Please wait while we prepare the comparison...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                console.log('Starting comparison between versions', {
                    v1: selectedVersion1,
                    v2: selectedVersion2
                });

                // Get version data
                let version1 = versions.find(v => v.id == selectedVersion1);
                let version2 = versions.find(v => v.id == selectedVersion2);

                if (!version1 || !version2) {
                    console.log('Error: Versions not found', {
                        version1,
                        version2
                    });
                    Swal.close();
                    Swal.fire('Error', 'Selected version not found', 'error');
                    return;
                }

                // Show comparison results section - make sure this is visible
                $('#comparisonResults').removeClass('d-none').show();

                // Show loading indicators
                $('#comparisonSummary').html('<div class="loading-spinner"><div class="spinner-border text-primary" role="status"></div></div>');
                $('#version1Content').html('<div class="loading-spinner"><div class="spinner-border text-primary" role="status"></div></div>');
                $('#version2Content').html('<div class="loading-spinner"><div class="spinner-border text-primary" role="status"></div></div>');

                console.log('Found versions to compare', {
                    v1: {
                        id: version1.id,
                        number: version1.version_number
                    },
                    v2: {
                        id: version2.id,
                        number: version2.version_number
                    }
                }); // Update titles
                $('#version1Title').text(`Version ${version1.version_number} - ${version1.title}`);
                $('#version2Title').text(`Version ${version2.version_number} - ${version2.title}`);

                // Simple comparison display - simulate loading delay
                setTimeout(() => {
                    console.log('Building comparison content');

                    $('#comparisonSummary').html(`
                        <div class="alert alert-info mb-0">
                            <strong>Comparison between Version ${version1.version_number} and Version ${version2.version_number}</strong><br>
                            <small>Created: ${new Date(version1.created_at).toLocaleString()} vs ${new Date(version2.created_at).toLocaleString()}</small>
                        </div>
                    `);

                    $('#version1Content').html(`
                        <div class="field-comparison">
                            <div class="field-label">Title</div>
                            <div class="field-content">${escapeHtml(version1.title)}</div>
                        </div>
                        <div class="field-comparison">
                            <div class="field-label">Status</div>
                            <div class="field-content">
                                <span class="badge ${version1.status === 'submitted' ? 'bg-primary' : (version1.status === 'accepted' ? 'bg-success' : (version1.status === 'rejected' ? 'bg-danger' : 'bg-warning'))}">
                                    ${version1.status ? version1.status.charAt(0).toUpperCase() + version1.status.slice(1) : 'Draft'}
                                </span>
                            </div>
                        </div>
                        <div class="field-comparison">
                            <div class="field-label">Content</div>
                            <div class="field-content">${isHtml(version1.content) ? version1.content : version1.content.replace(/\n/g, '<br>')}</div>
                        </div>
                        ${version1.keywords ? `
                            <div class="field-comparison">
                                <div class="field-label">Keywords</div>
                                <div class="field-content">${escapeHtml(version1.keywords)}</div>
                            </div>
                        ` : ''}
                        ${version1.refs ? `
                            <div class="field-comparison">
                                <div class="field-label">References</div>
                                <div class="field-content">${isHtml(version1.refs) ? version1.refs : version1.refs.replace(/\n/g, '<br>')}</div>
                            </div>
                        ` : ''}
                        <div class="field-comparison">
                            <div class="field-label">Created At</div>
                            <div class="field-content">${new Date(version1.created_at).toLocaleString()}</div>
                        </div>
                        ${version1.updated_at && version1.updated_at !== version1.created_at ? `
                            <div class="field-comparison">
                                <div class="field-label">Updated At</div>
                                <div class="field-content">${new Date(version1.updated_at).toLocaleString()}</div>
                            </div>
                        ` : ''}
                    `);

                    $('#version2Content').html(`
                        <div class="field-comparison">
                            <div class="field-label">Title</div>
                            <div class="field-content">${escapeHtml(version2.title)}</div>
                        </div>
                        <div class="field-comparison">
                            <div class="field-label">Status</div>
                            <div class="field-content">
                                <span class="badge ${version2.status === 'submitted' ? 'bg-primary' : (version2.status === 'accepted' ? 'bg-success' : (version2.status === 'rejected' ? 'bg-danger' : 'bg-warning'))}">
                                    ${version2.status ? version2.status.charAt(0).toUpperCase() + version2.status.slice(1) : 'Draft'}
                                </span>
                            </div>
                        </div>
                        <div class="field-comparison">
                            <div class="field-label">Content</div>
                            <div class="field-content">${isHtml(version2.content) ? version2.content : version2.content.replace(/\n/g, '<br>')}</div>
                        </div>
                        ${version2.keywords ? `
                            <div class="field-comparison">
                                <div class="field-label">Keywords</div>
                                <div class="field-content">${escapeHtml(version2.keywords)}</div>
                            </div>
                        ` : ''}
                        ${version2.refs ? `
                            <div class="field-comparison">
                                <div class="field-label">References</div>
                                <div class="field-content">${isHtml(version2.refs) ? version2.refs : version2.refs.replace(/\n/g, '<br>')}</div>
                            </div>
                        ` : ''}
                        <div class="field-comparison">
                            <div class="field-label">Created At</div>
                            <div class="field-content">${new Date(version2.created_at).toLocaleString()}</div>
                        </div>
                        ${version2.updated_at && version2.updated_at !== version2.created_at ? `
                            <div class="field-comparison">
                                <div class="field-label">Updated At</div>
                                <div class="field-content">${new Date(version2.updated_at).toLocaleString()}</div>
                            </div>
                        ` : ''}
                    `);
                    console.log('Comparison complete');

                    // Close the loading dialog
                    Swal.close();
                }, 800);
            }

            function viewFullVersion(versionId) {
                // Show loading indicator in a toast
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });

                Toast.fire({
                    icon: 'info',
                    title: 'Loading version details...'
                });

                let version = versions.find(v => v.id == versionId);
                if (!version) {
                    Swal.fire('Error', 'Version not found', 'error');
                    return;
                }

                // Show loading indicator in the modal content
                $('#fullVersionTitle').text(`Version ${version.version_number} - ${version.title}`);
                $('#fullVersionContent').html('<div class="loading-spinner py-5"><div class="spinner-border text-primary" role="status"></div></div>');
                $('#fullVersionModal').modal('show');

                // Simulate loading delay for better UX
                setTimeout(() => {
                    let contentHtml = `
                        <div class="mb-3">
                            <strong>Title:</strong>
                            <div class="mt-1">${escapeHtml(version.title)}</div>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong>
                            <span class="badge ${version.status === 'submitted' ? 'bg-primary' : (version.status === 'accepted' ? 'bg-success' : (version.status === 'rejected' ? 'bg-danger' : 'bg-warning'))}">
                                ${version.status ? version.status.charAt(0).toUpperCase() + version.status.slice(1) : 'Draft'}
                            </span>
                        </div>
                        <div class="mb-3">
                            <strong>Content:</strong>
                            <div class="mt-1 p-3 bg-light rounded border abstract-content">
                                ${isHtml(version.content) ? version.content : version.content.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                        ${version.keywords ? `
                            <div class="mb-3">
                                <strong>Keywords:</strong>
                                <div class="mt-1">
                                    ${version.keywords.split(',').map(k => `<span class="keywords-tag">${escapeHtml(k.trim())}</span>`).join('')}
                                </div>
                            </div>
                        ` : ''}
                        ${version.refs ? `
                            <div class="mb-3">
                                <strong>References:</strong>
                                <div class="mt-1 p-3 bg-light rounded border">
                                    ${isHtml(version.refs) ? version.refs : version.refs.replace(/\n/g, '<br>')}
                                </div>
                            </div>
                        ` : ''}
                        <div class="mb-3">
                            <strong>Version Info:</strong>
                            <div class="small text-muted">
                                Created: ${new Date(version.created_at).toLocaleString()}<br>
                                ${version.updated_at !== version.created_at ? `Updated: ${new Date(version.updated_at).toLocaleString()}` : ''}
                            </div>
                        </div>                    `;

                    $('#fullVersionContent').html(contentHtml);
                    console.log('Version content rendered');

                    // Close the loading dialog
                    Swal.close();
                }, 300);
            }

            function compareWithCurrent(versionId) {
                console.log('compareWithCurrent called with versionId:', versionId);

                // Show loading dialog using SweetAlert
                Swal.fire({
                    title: 'Preparing Comparison',
                    text: 'Loading version data...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // First reset everything
                $('#comparisonResults').addClass('d-none');
                selectedVersion1 = null;
                selectedVersion2 = null;

                console.log('Opening comparison modal');

                // Open modal first
                $('#comparisonModal').modal('show');

                // Set version selections
                if (versions && versions.length > 0) {
                    selectedVersion1 = versions[0].id; // Current version (first in array)
                    selectedVersion2 = versionId;

                    console.log('Setting versions for comparison', {
                        current: selectedVersion1,
                        selected: selectedVersion2
                    });

                    // Apply selection visually after modal has shown
                    $('#comparisonModal').on('shown.bs.modal', function() {
                        console.log('Modal shown, applying version selections');

                        // Select first version (current version)
                        $('#version1-selector .version-selector').each(function() {
                            if ($(this).data('version-id') == selectedVersion1) {
                                $(this).addClass('selected');
                                console.log('Selected version 1:', selectedVersion1);
                            }
                        });

                        // Select second version (comparison version)
                        $('#version2-selector .version-selector').each(function() {
                            if ($(this).data('version-id') == selectedVersion2) {
                                $(this).addClass('selected');
                                console.log('Selected version 2:', selectedVersion2);
                            }
                        });

                        // Enable compare button
                        $('#compareBtn').prop('disabled', false);

                        // Close the initial loading dialog
                        Swal.close(); // Auto-perform comparison after a small delay
                        setTimeout(() => {
                            console.log('Auto-performing comparison');
                            performComparison();
                        }, 300);

                        // Remove event listener to prevent multiple bindings
                        $('#comparisonModal').off('shown.bs.modal');
                    });
                } else {
                    console.log('Error: No versions available');
                    Swal.fire('Error', 'No versions available for comparison', 'error');
                }
            }

            function openEditModal() {
                // This would open an edit modal or redirect to edit page
                window.location.href = `${baseUrl}/submissions/abstracts-papers/edit/${abstractId}`;
            }

            // ============= FEEDBACK FUNCTIONS =============

            // Global variables for feedback
            let feedbackData = [];
            let reviewers = [];
            let currentFeedbackId = null;

            // Load feedback data on page load
            $(document).ready(function() {
                loadFeedbackData();
                loadReviewers();
            });

            // Load feedback data from server
            function loadFeedbackData() {
                $.ajax({
                    url: `${baseUrl}/api/abstracts/${abstractId}/feedback`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            feedbackData = response.data;
                            renderFeedbackList();
                        } else {
                            console.error('Failed to load feedback:', response.message);
                        }
                    },
                    error: function() {
                        console.error('Failed to load feedback data');
                    }
                });
            }

            // Load reviewers for dropdowns
            function loadReviewers() {
                $.ajax({
                    url: `${baseUrl}/api/reviewers`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            reviewers = response.data;
                            populateReviewerDropdowns();
                        } else {
                            console.error('Failed to load reviewers:', response.message);
                        }
                    },
                    error: function() {
                        console.error('Failed to load reviewers');
                    }
                });
            }

            // Populate reviewer dropdowns
            function populateReviewerDropdowns() {
                const dropdowns = ['#feedbackReviewer', '#editFeedbackReviewer'];

                dropdowns.forEach(selector => {
                    const dropdown = $(selector);
                    dropdown.find('option:not(:first)').remove();

                    reviewers.forEach(reviewer => {
                        dropdown.append(`
                            <option value="${reviewer.id}">
                                ${reviewer.name} (${reviewer.email})
                            </option>
                        `);
                    });
                });
            }

            // Render feedback list
            function renderFeedbackList() {
                const container = $('#feedbackList');
                const noFeedbackMessage = $('#noFeedbackMessage');

                if (!feedbackData || feedbackData.length === 0) {
                    noFeedbackMessage.show();
                    return;
                }

                noFeedbackMessage.hide();

                let html = '';
                feedbackData.forEach(feedback => {
                    const reviewer = reviewers.find(r => r.id == feedback.reviewer_id);
                    const version = versions.find(v => v.id == feedback.abstract_version_id);

                    // Generate star rating
                    const ratingStars = feedback.rating ?
                        `<div class="me-2">
                            <span class="text-warning">${'★'.repeat(parseInt(feedback.rating))}</span><span class="text-muted">${'☆'.repeat(5 - parseInt(feedback.rating))}</span>
                        </div>
                        <span class="fw-medium">${feedback.rating}/5</span>` :
                        '<span class="text-muted">No rating</span>';

                    html += `
                        <div class="card mb-3 border" data-feedback-id="${feedback.id}">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="author-avatar me-2">
                                            ${reviewer ? reviewer.name.charAt(0).toUpperCase() : 'R'}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">${reviewer ? reviewer.name : 'Unknown Reviewer'}</h6>
                                            <small class="text-muted">${reviewer ? reviewer.email : ''}</small>
                                        </div>
                                    </div>
                                    <span class="badge ${feedback.status === 'submitted' ? 'bg-success' : 'bg-warning'}">
                                        ${feedback.status ? feedback.status.charAt(0).toUpperCase() + feedback.status.slice(1) : 'Draft'}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 bg-light p-3 rounded">
                                    ${feedback.feedback ? feedback.feedback.substring(0, 300) + (feedback.feedback.length > 300 ? '...' : '') : 'No feedback content'}
                                </div>
                                
                                ${version ? `
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="ri-file-text-line me-1"></i>
                                            Version ${version.version_number} - ${new Date(version.created_at).toLocaleDateString()}
                                        </small>
                                    </div>
                                ` : ''}
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        ${ratingStars}
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewFeedback(${feedback.id})">
                                            <i class="ri-eye-line me-1"></i> View
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="editFeedback(${feedback.id})">
                                            <i class="ri-edit-line me-1"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteFeedback(${feedback.id})">
                                            <i class="ri-delete-bin-line me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mt-3 pt-2 border-top">
                                    <small class="text-muted">
                                        <i class="ri-time-line me-1"></i> Created: ${new Date(feedback.created_at).toLocaleDateString()} 
                                        ${feedback.updated_at ? ' • Updated: ' + new Date(feedback.updated_at).toLocaleDateString() : ''}
                                    </small>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.html(html);
            }

            // Open add feedback modal
            function openAddFeedbackModal() {
                $('#addFeedbackForm')[0].reset();
                $('#addFeedbackModal').modal('show');
            }

            // Handle add feedback form submission
            $('#addFeedbackForm').on('submit', function(e) {
                e.preventDefault();

                const formData = {
                    abstract_id: abstractId,
                    reviewer_id: $('#feedbackReviewer').val(),
                    abstract_version_id: $('#feedbackVersion').val(),
                    feedback: $('#feedbackContent').val(),
                    rating: $('#feedbackRating').val(),
                    status: $('#feedbackStatus').val(),
                    csrf_token_name: '<?= csrf_token() ?>',
                    csrf_hash: '<?= csrf_hash() ?>'
                };

                $.ajax({
                    url: `${baseUrl}/api/abstracts/${abstractId}/feedback`,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#addFeedbackModal').modal('hide');
                            Swal.fire('Success!', 'Feedback added successfully.', 'success');
                            loadFeedbackData(); // Reload feedback data
                        } else {
                            Swal.fire('Error', response.message || 'Failed to add feedback', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to add feedback. Please try again.', 'error');
                    }
                });
            });

            // View feedback details
            function viewFeedback(feedbackId) {
                const feedback = feedbackData.find(f => f.id == feedbackId);
                const reviewer = reviewers.find(r => r.id == feedback.reviewer_id);
                const version = versions.find(v => v.id == feedback.abstract_version_id);

                if (!feedback) return;

                $('#viewFeedbackReviewer').text(reviewer ? `${reviewer.name} (${reviewer.email})` : 'Unknown Reviewer');
                $('#viewFeedbackVersion').text(version ? `Version ${version.version_number} - ${new Date(version.created_at).toLocaleDateString()}` : 'Unknown Version');
                $('#viewFeedbackContent').text(feedback.feedback || 'No feedback content');
                $('#viewFeedbackCreated').text(new Date(feedback.created_at).toLocaleDateString());
                $('#viewFeedbackUpdated').text(feedback.updated_at ? new Date(feedback.updated_at).toLocaleDateString() : 'Never');

                // Set rating
                if (feedback.rating) {
                    $('#viewFeedbackRating').html(`
                        <div class="rating-stars">
                            ${'★'.repeat(parseInt(feedback.rating))}${'☆'.repeat(5 - parseInt(feedback.rating))}
                        </div>
                        <span>${feedback.rating}/5</span>
                    `);
                } else {
                    $('#viewFeedbackRating').text('No rating');
                }

                // Set status
                const statusBadge = $('#viewFeedbackStatus');
                statusBadge.removeClass('bg-success bg-warning bg-secondary');
                statusBadge.addClass(feedback.status === 'submitted' ? 'bg-success' : 'bg-warning');
                statusBadge.text(feedback.status ? feedback.status.charAt(0).toUpperCase() + feedback.status.slice(1) : 'Draft');

                currentFeedbackId = feedbackId;
                $('#viewFeedbackModal').modal('show');
            }

            // Edit feedback
            function editFeedback(feedbackId) {
                const feedback = feedbackData.find(f => f.id == feedbackId);
                if (!feedback) return;

                $('#editFeedbackId').val(feedback.id);
                $('#editFeedbackReviewer').val(feedback.reviewer_id);
                $('#editFeedbackVersion').val(feedback.abstract_version_id);
                $('#editFeedbackContent').val(feedback.feedback);
                $('#editFeedbackRating').val(feedback.rating);
                $('#editFeedbackStatus').val(feedback.status);

                $('#editFeedbackModal').modal('show');
            }

            // Edit feedback from view modal
            function editFeedbackFromView() {
                $('#viewFeedbackModal').modal('hide');
                setTimeout(() => {
                    editFeedback(currentFeedbackId);
                }, 300);
            }

            // Handle edit feedback form submission
            $('#editFeedbackForm').on('submit', function(e) {
                e.preventDefault();

                const feedbackId = $('#editFeedbackId').val();
                const formData = {
                    reviewer_id: $('#editFeedbackReviewer').val(),
                    abstract_version_id: $('#editFeedbackVersion').val(),
                    feedback: $('#editFeedbackContent').val(),
                    rating: $('#editFeedbackRating').val(),
                    status: $('#editFeedbackStatus').val(),
                    csrf_token_name: '<?= csrf_token() ?>',
                    csrf_hash: '<?= csrf_hash() ?>'
                };

                $.ajax({
                    url: `${baseUrl}/api/abstracts/${abstractId}/feedback/${feedbackId}`,
                    method: 'PUT',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#editFeedbackModal').modal('hide');
                            Swal.fire('Success!', 'Feedback updated successfully.', 'success');
                            loadFeedbackData(); // Reload feedback data
                        } else {
                            Swal.fire('Error', response.message || 'Failed to update feedback', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to update feedback. Please try again.', 'error');
                    }
                });
            });

            // Delete feedback
            function deleteFeedback(feedbackId) {
                Swal.fire({
                    title: 'Delete Feedback?',
                    text: 'Are you sure you want to delete this feedback? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `${baseUrl}/api/abstracts/${abstractId}/feedback/${feedbackId}`,
                            method: 'DELETE',
                            data: {
                                csrf_token_name: '<?= csrf_token() ?>',
                                csrf_hash: '<?= csrf_hash() ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Deleted!', 'Feedback has been deleted.', 'success');
                                    loadFeedbackData(); // Reload feedback data
                                } else {
                                    Swal.fire('Error', response.message || 'Failed to delete feedback', 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to delete feedback. Please try again.', 'error');
                            }
                        });
                    }
                });
            }
        </script>

        <?= $this->include('partials/footer') ?>
    </div>
</body>

</html>