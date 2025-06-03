<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Abstract Details')); ?>
    <?= $this->include('partials/head-css') ?>

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <style>
        /* Card hover effects */
        .card {
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        /* Author cards */
        .author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.75rem;
            flex-shrink: 0;
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
            background: #e5e7eb;
        }

        .version-item {
            position: relative;
            margin-bottom: 1.5rem;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-left: 1rem;
        }

        .version-item::before {
            content: '';
            position: absolute;
            left: -1.65rem;
            top: 1rem;
            width: 10px;
            height: 10px;
            background: #667eea;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #e5e7eb;
        }

        .version-item.latest::before {
            background: #10b981;
        }

        /* Comparison modal */
        .comparison-container {
            display: flex;
            gap: 1rem;
            height: 500px;
        }

        .comparison-panel {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .comparison-header {
            background: #f9fafb;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
        }

        .comparison-content {
            padding: 1rem;
            height: calc(100% - 60px);
            overflow-y: auto;
        }

        .field-comparison {
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            overflow: hidden;
        }

        .field-label {
            background: #f9fafb;
            padding: 0.5rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .field-content {
            padding: 1rem;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .changed-field {
            border-left: 4px solid #f59e0b;
        }

        .changed-field .field-label {
            background: #fef3c7;
            color: #92400e;
        }

        /* Content truncation */
        .text-truncate-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Modal loading */
        .loading-spinner {
            text-align: center;
            padding: 2rem;
        }

        .loading-spinner .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        /* Version selector styling */
        .version-selector {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .version-selector:hover,
        .version-selector.selected {
            border-color: #007bff;
            background: #f0f8ff;
        }

        /* Keywords styling */
        .keywords-tag {
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            margin: 0.25rem;
            display: inline-block;
        }

        /* Statistics grid */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #007bff;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Feedback Section Styles */
        .feedback-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: white;
            transition: all 0.3s ease;
        }

        .feedback-item:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-color: #dee2e6;
        }

        .feedback-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .feedback-reviewer {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .reviewer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .feedback-content {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1rem;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .feedback-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            color: #6c757d;
        }

        .feedback-rating {
            display: flex;
            align-items: center;
        }

        .rating-stars {
            color: #ffc107;
            margin-right: 0.5rem;
        }

        .feedback-actions {
            display: flex;
            gap: 0.5rem;
        }

        .feedback-status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .feedback-version-info {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 4px;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: #004085;
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <?php echo view('partials/page-title', array('pagetitle' => 'Submissions', 'title' => 'Abstract Details')); ?>

                    <!-- Navigation Bar -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="<?= base_url('submissions/abstracts-papers') ?>" class="btn btn-light btn-sm me-2">
                                        <i class="ri-arrow-left-line me-1"></i>Back to List
                                    </a>
                                </div>
                                <div class="btn-group" role="group"> <button type="button" class="btn btn-outline-primary btn-sm" onclick="openVersionComparison()" data-bs-toggle="tooltip" title="Compare Versions">
                                        <i class="ri-git-branch-line me-1"></i>Compare Versions
                                    </button>
                                    <?php if (!session('adminId')): ?>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="openEditModal()" data-bs-toggle="tooltip" title="Edit Abstract">
                                            <i class="ri-pencil-line me-1"></i>Edit
                                        </button>
                                    <?php endif; ?>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-settings-3-line me-1"></i>Actions
                                        </button>
                                        <ul class="dropdown-menu">
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

                    <!-- Abstract Header Card -->
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
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-<?= $abstract->status === 'submitted' ? 'primary' : ($abstract->status === 'accepted' ? 'success' : ($abstract->status === 'rejected' ? 'danger' : 'warning')) ?> fs-6">
                                            <?= ucfirst(str_replace('_', ' ', $abstract->status ?? 'submitted')) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted">Submitted</small>
                                            <p class="mb-0 fw-medium"><?= date('F j, Y', strtotime($abstract->created_at)) ?></p>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Last Updated</small>
                                            <p class="mb-0 fw-medium"><?= $version_stats['last_update'] ? date('F j, Y', strtotime($version_stats['last_update'])) : 'N/A' ?></p>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Institution</small>
                                            <p class="mb-0 fw-medium"><?= esc($participant->institution ?? 'N/A') ?></p>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Email</small>
                                            <p class="mb-0 fw-medium"><?= esc($participant->email ?? 'N/A') ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card border-0 bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3 class="card-title text-white mb-1"><?= $version_stats['total_versions'] ?></h3>
                                    <p class="card-text opacity-75 mb-0">Total Versions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card border-0 bg-success text-white">
                                <div class="card-body text-center">
                                    <h3 class="card-title text-white mb-1"><?= $version_stats['latest_version'] ?></h3>
                                    <p class="card-text opacity-75 mb-0">Latest Version</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card border-0 bg-info text-white">
                                <div class="card-body text-center">
                                    <h3 class="card-title text-white mb-1"><?= count($authors) ?></h3>
                                    <p class="card-text opacity-75 mb-0">Authors</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card border-0 bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3 class="card-title text-white mb-1">
                                        <?= $version_stats['last_update'] ? date('M j', strtotime($version_stats['last_update'])) : 'N/A' ?>
                                    </h3>
                                    <p class="card-text opacity-75 mb-0">Last Update</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Main Content -->
                        <div class="col-lg-8">
                            <!-- Current Version Display -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-file-text-line me-2"></i>
                                        Current Version (<?= $versions[0]->version_number ?? 'N/A' ?>)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="abstract-content mb-3">
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
                                        <div class="keywords-section">
                                            <h6 class="mb-2">Keywords:</h6>
                                            <?php foreach (explode(',', $versions[0]->keywords) as $keyword): ?>
                                                <span class="keywords-tag"><?= trim(esc($keyword)) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Version History -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-history-line me-2"></i>
                                        Version History
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="version-timeline">
                                        <?php foreach ($versions as $index => $version): ?>
                                            <div class="version-item <?= $index === 0 ? 'latest' : '' ?>">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mb-1">Version <?= $version->version_number ?></h6>
                                                        <p class="text-muted mb-1"><?= $version->title ?></p>
                                                        <small class="text-muted">
                                                            <?= date('F j, Y \a\t g:i A', strtotime($version->created_at)) ?>
                                                        </small>
                                                    </div>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="viewFullVersion(<?= $version->id ?>)">
                                                            <i class="ri-eye-line"></i>
                                                        </button>
                                                        <?php if ($index > 0): ?>
                                                            <button class="btn btn-outline-info" onclick="compareWithCurrent(<?= $version->id ?>)">
                                                                <i class="ri-git-branch-line"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="version-content text-truncate-3">
                                                    <?php
                                                    $versionContent = substr($version->content, 0, 150);
                                                    if (strip_tags($versionContent) !== $versionContent) {
                                                        echo $versionContent . (strlen($version->content) > 150 ? '...' : '');
                                                    } else {
                                                        echo nl2br(esc($versionContent)) . (strlen($version->content) > 150 ? '...' : '');
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <!-- Statistics Card -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-bar-chart-line me-2"></i>
                                        Statistics
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="stats-grid">
                                        <div class="stat-item">
                                            <div class="stat-value"><?= count($versions) ?></div>
                                            <div class="stat-label">Total Versions</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value"><?= count($authors) ?></div>
                                            <div class="stat-label">Authors</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value"><?= str_word_count($versions[0]->content ?? '') ?></div>
                                            <div class="stat-label">Words</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value"><?= date('M j', strtotime($abstract->created_at)) ?></div>
                                            <div class="stat-label">Submitted</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Authors Card -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-team-line me-2"></i>
                                        Authors (<?= count($authors) ?>)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($authors)): ?>
                                        <?php foreach ($authors as $author): ?>
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="author-avatar">
                                                    <?= strtoupper(substr($author->full_name, 0, 1)) ?>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-medium"><?= htmlspecialchars($author->full_name) ?></div>
                                                    <?php if (!empty($author->institution)): ?>
                                                        <div class="small text-muted"><?= htmlspecialchars($author->institution) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($author->email)): ?>
                                                        <div class="small text-muted"><?= htmlspecialchars($author->email) ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($author->is_participant): ?>
                                                        <span class="badge bg-info badge-sm">Participant</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <i class="ri-user-line text-muted" style="font-size: 2rem;"></i>
                                            <p class="text-muted mt-2 mb-0">No authors listed</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feedback Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title mb-0">
                                            <i class="ri-message-3-line me-2"></i>
                                            Reviewer Feedback
                                        </h5>
                                        <p class="text-muted mb-0 mt-1">Comments and suggestions from reviewers</p>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="openAddFeedbackModal()" data-bs-toggle="tooltip" title="Add New Feedback">
                                            <i class="ri-add-line me-1"></i>Add Feedback
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Feedback List -->
                                <div id="feedbackList">
                                    <!-- Feedback items will be loaded here -->
                                    <div class="text-center py-4" id="noFeedbackMessage">
                                        <i class="ri-message-3-line text-muted" style="font-size: 3rem;"></i>
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
                                        <label for="feedbackVersion" class="form-label">Abstract Version <span class="text-danger">*</span></label>
                                        <select class="form-select" id="feedbackVersion" name="abstract_version_id" required>
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
        </div>

        <!-- Version Comparison Modal -->
        <div class="modal fade comparison-modal" id="comparisonModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="ri-git-branch-line me-2"></i>
                            Version Comparison
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Version Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Select First Version:</h6>
                                <div id="version1-selector" class="version-selectors">
                                    <!-- Version options will be loaded here -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Select Second Version:</h6>
                                <div id="version2-selector" class="version-selectors">
                                    <!-- Version options will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <!-- Comparison Results -->
                        <div id="comparisonResults" class="d-none">
                            <div class="comparison-panel">
                                <h6>Comparison Summary</h6>
                                <div id="comparisonSummary"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="comparison-panel">
                                        <h6 id="version1Title">Version 1</h6>
                                        <div id="version1Content"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="comparison-panel">
                                        <h6 id="version2Title">Version 2</h6>
                                        <div id="version2Content"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="compareBtn" onclick="performComparison()">
                            <i class="ri-git-branch-line me-1"></i> Compare Selected Versions
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Full Version Modal -->
        <div class="modal fade" id="fullVersionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ri-file-text-line me-2"></i>
                            <span id="fullVersionTitle">Version Details</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="fullVersionContent"></div>
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
                let version1Html = '';
                let version2Html = '';

                console.log("Loading version selectors with versions:", versions);

                if (!versions || versions.length === 0) {
                    console.error("No versions available");
                    return;
                }

                versions.forEach(function(version) {
                    let versionHtml = `
                        <div class="version-selector mb-2" data-version-id="${version.id}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Version ${version.version_number}</strong>
                                    <div class="small text-muted">${escapeHtml(version.title)}</div>
                                </div>
                                <div>
                                    <span class="badge ${version.status === 'submitted' ? 'bg-primary' : 'bg-secondary'}">${escapeHtml(version.status)}</span>
                                    <div class="small text-muted">${new Date(version.created_at).toLocaleDateString()}</div>
                                </div>
                            </div>
                        </div>
                    `;

                    version1Html += versionHtml;
                    version2Html += versionHtml;
                });

                $('#version1-selector').html(version1Html);
                $('#version2-selector').html(version2Html);

                // Add click event handlers after adding elements to DOM
                $('#version1-selector .version-selector').on('click', function() {
                    const versionId = $(this).data('version-id');
                    selectVersion(versionId, this, true);
                });

                $('#version2-selector .version-selector').on('click', function() {
                    const versionId = $(this).data('version-id');
                    selectVersion(versionId, this, false);
                });

                console.log("Version selectors populated with event handlers attached");
            }

            function selectVersion(versionId, element) {
                console.log("Selecting version:", versionId, element);

                let container = $(element).parent();
                let isVersion1 = container.attr('id') === 'version1-selector';

                console.log("Container ID:", container.attr('id'), "isVersion1:", isVersion1);

                // Remove previous selection in this container
                container.find('.version-selector').removeClass('selected');

                // Add selection to clicked element
                $(element).addClass('selected');
                if (isVersion1) {
                    selectedVersion1 = versionId;
                    console.log("Set selectedVersion1 to", selectedVersion1);
                } else {
                    selectedVersion2 = versionId;
                    console.log("Set selectedVersion2 to", selectedVersion2);
                }

                // Enable compare button if both versions selected
                if (selectedVersion1 && selectedVersion2 && selectedVersion1 !== selectedVersion2) {
                    $('#compareBtn').prop('disabled', false);
                } else {
                    $('#compareBtn').prop('disabled', true);
                }
            }

            function openVersionComparison() {
                $('#comparisonModal').modal('show');
                $('#comparisonResults').addClass('d-none');
                selectedVersion1 = null;
                selectedVersion2 = null;
                $('#compareBtn').prop('disabled', true);
                $('.version-selector').removeClass('selected');
            }

            function performComparison() {
                if (!selectedVersion1 || !selectedVersion2) {
                    Swal.fire('Error', 'Please select two different versions to compare', 'error');
                    return;
                }

                if (selectedVersion1 === selectedVersion2) {
                    Swal.fire('Error', 'Please select two different versions', 'error');
                    return;
                }

                // Show loading
                $('#comparisonResults').removeClass('d-none');
                $('#comparisonSummary').html('<div class="loading-spinner"><div class="spinner-border" role="status"></div></div>');
                $('#version1Content').html('<div class="loading-spinner"><div class="spinner-border" role="status"></div></div>');
                $('#version2Content').html('<div class="loading-spinner"><div class="spinner-border" role="status"></div></div>');

                // Get version data
                let version1 = versions.find(v => v.id == selectedVersion1);
                let version2 = versions.find(v => v.id == selectedVersion2);

                // Update titles
                $('#version1Title').text(`Version ${version1.version_number} - ${version1.title}`);
                $('#version2Title').text(`Version ${version2.version_number} - ${version2.title}`);

                // Simple comparison display
                setTimeout(() => {
                    $('#comparisonSummary').html(`
                        <div class="alert alert-info">
                            <strong>Comparison between Version ${version1.version_number} and Version ${version2.version_number}</strong><br>
                            <small>Created: ${new Date(version1.created_at).toLocaleDateString()} vs ${new Date(version2.created_at).toLocaleDateString()}</small>
                        </div>
                    `);
                    $('#version1Content').html(`
                        <div class="field-comparison">
                            <div class="field-label">Title</div>
                            <div class="field-content">${escapeHtml(version1.title)}</div>
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
                    `);

                    $('#version2Content').html(`
                        <div class="field-comparison">
                            <div class="field-label">Title</div>
                            <div class="field-content">${escapeHtml(version2.title)}</div>
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
                    `);
                }, 1000);
            }

            function viewFullVersion(versionId) {
                let version = versions.find(v => v.id == versionId);
                if (!version) {
                    Swal.fire('Error', 'Version not found', 'error');
                    return;
                }
                $('#fullVersionTitle').text(`Version ${version.version_number} - ${version.title}`);

                let contentHtml = `
                    <div class="mb-3">
                        <strong>Title:</strong>
                        <div class="mt-1">${escapeHtml(version.title)}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge ${version.status === 'submitted' ? 'bg-primary' : 'bg-secondary'}">${escapeHtml(version.status)}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Content:</strong>
                        <div class="mt-1 p-3 bg-light rounded abstract-content">
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
                    <div class="mb-3">
                        <strong>Version Info:</strong>
                        <div class="small text-muted">
                            Created: ${new Date(version.created_at).toLocaleString()}<br>
                            ${version.updated_at !== version.created_at ? `Updated: ${new Date(version.updated_at).toLocaleString()}` : ''}
                        </div>
                    </div>
                `;

                $('#fullVersionContent').html(contentHtml);
                $('#fullVersionModal').modal('show');
            }

            function compareWithCurrent(versionId) {
                console.log("compareWithCurrent called with versionId:", versionId);
                console.log("Current versions array:", versions);

                if (!versions || versions.length === 0) {
                    console.error("No versions available");
                    return;
                }

                selectedVersion1 = versions[0].id; // Current version (first in array)
                selectedVersion2 = versionId;

                console.log("Set selectedVersion1 to", selectedVersion1);
                console.log("Set selectedVersion2 to", selectedVersion2);

                openVersionComparison();

                // Pre-select the versions
                setTimeout(() => {
                    console.log("Selecting version elements in DOM");
                    console.log("Selector 1:", `#version1-selector .version-selector[data-version-id="${selectedVersion1}"]`);
                    console.log("Selector 2:", `#version2-selector .version-selector[data-version-id="${selectedVersion2}"]`);

                    const version1Element = $(`#version1-selector .version-selector[data-version-id="${selectedVersion1}"]`);
                    const version2Element = $(`#version2-selector .version-selector[data-version-id="${selectedVersion2}"]`);

                    console.log("Version1 element found:", version1Element.length);
                    console.log("Version2 element found:", version2Element.length);

                    version1Element.addClass('selected');
                    version2Element.addClass('selected');
                    $('#compareBtn').prop('disabled', false);

                    // Auto-perform comparison
                    performComparison();
                }, 100);
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

                    html += `
                        <div class="feedback-item" data-feedback-id="${feedback.id}">
                            <div class="feedback-header">
                                <div class="flex-grow-1">
                                    <div class="feedback-reviewer">
                                        <div class="reviewer-avatar">
                                            ${reviewer ? reviewer.name.charAt(0).toUpperCase() : 'R'}
                                        </div>
                                        <div>
                                            <h6 class="mb-1">${reviewer ? reviewer.name : 'Unknown Reviewer'}</h6>
                                            <small class="text-muted">${reviewer ? reviewer.email : ''}</small>
                                        </div>
                                    </div>
                                    ${version ? `
                                        <div class="feedback-version-info">
                                            <i class="ri-file-text-line me-1"></i>
                                            Version ${version.version_number} - ${new Date(version.created_at).toLocaleDateString()}
                                        </div>
                                    ` : ''}
                                </div>
                                <div class="feedback-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewFeedback(${feedback.id})">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="editFeedback(${feedback.id})">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteFeedback(${feedback.id})">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="feedback-content">
                                ${feedback.feedback ? feedback.feedback.substring(0, 300) + (feedback.feedback.length > 300 ? '...' : '') : 'No feedback content'}
                            </div>
                            
                            <div class="feedback-meta">
                                <div class="d-flex align-items-center">
                                    ${feedback.rating ? `
                                        <div class="feedback-rating">
                                            <div class="rating-stars">
                                                ${'★'.repeat(parseInt(feedback.rating))}${'☆'.repeat(5 - parseInt(feedback.rating))}
                                            </div>
                                            <span>${feedback.rating}/5</span>
                                        </div>
                                    ` : ''}
                                    <span class="feedback-status-badge badge ${feedback.status === 'submitted' ? 'bg-success' : 'bg-warning'}">
                                        ${feedback.status ? feedback.status.charAt(0).toUpperCase() + feedback.status.slice(1) : 'Draft'}
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted">
                                        ${new Date(feedback.created_at).toLocaleDateString()} • 
                                        ${feedback.updated_at ? 'Updated ' + new Date(feedback.updated_at).toLocaleDateString() : ''}
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