<?= $this->extend('layouts/reviewer') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title mb-0 flex-grow-1">View Abstract</h4>
                    <div class="flex-shrink-0">                        <a href="/reviewers/abstracts-papers" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back to List
                        </a> 
                        
                        <?php if (isset($abstract->abstract_status) && $abstract->abstract_status === 'accepted'): ?>
                            <!-- Show accepted status badge instead of action buttons -->
                            <span class="badge bg-success ms-2 px-3 py-2">
                                <i class="ri-check-double-line me-1"></i>Abstract Accepted
                            </span>
                        <?php elseif (empty($abstract->feedback)): ?>
                            <a href="/reviewers/abstracts-papers/review/<?= $abstract->abstract_id ?>" class="btn btn-primary ms-1">
                                <i class="ri-edit-line me-1"></i> Review
                            </a>
                        <?php else: ?>
                            <!-- Show edit feedback option for non-accepted abstracts -->
                            <a href="/reviewers/abstracts-papers/review/<?= $abstract->abstract_id ?>" class="btn btn-outline-primary ms-1">
                                <i class="ri-edit-line me-1"></i> Edit Feedback
                            </a>
                        <?php endif; ?>
                    </div>
                </div>            </div><!-- end card header -->

            <?php if (isset($abstract->abstract_status) && $abstract->abstract_status === 'accepted'): ?>
                <!-- Accepted Status Banner -->
                <div class="alert alert-success border-success mb-0" style="border-radius: 0;">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="alert-heading mb-1">
                                <i class="ri-check-double-line me-2"></i>Abstract Accepted
                            </h6>
                            <p class="mb-0 small">
                                This abstract has been accepted and is ready for publication. No further revisions are required.
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                <i class="ri-check-double-line me-1"></i>ACCEPTED
                            </span>
                        </div>
                    </div>
                </div>
            <?php elseif (isset($abstract->abstract_status) && $abstract->abstract_status === 'under_review'): ?>
                <!-- Under Review Status Banner -->
                <div class="alert alert-info border-info mb-0" style="border-radius: 0;">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="alert-heading mb-1">
                                <i class="ri-eye-line me-2"></i>Under Review
                            </h6>
                            <p class="mb-0 small">
                                This abstract is currently being reviewed by multiple reviewers.
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="badge bg-info-subtle text-info px-3 py-2">
                                <i class="ri-eye-line me-1"></i>UNDER REVIEW
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card-body">
                <div class="row">
                    <div class="col-xxl-3">
                        <div class="card border">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Review Information</h5>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-medium text-muted">Review Status:</span>
                                        <div>
                                            <?php if (!empty($abstract->feedback)): ?>
                                                <span class="badge bg-success-subtle text-success px-3 py-2">
                                                    <i class="ri-check-line me-1"></i>Completed
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                                    <i class="ri-time-line me-1"></i>Pending Review
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div> <?php if (!empty($abstract->feedback)): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="fw-medium text-muted">Feedback Submitted:</span>
                                            <div class="text-end">
                                                <?php
                                                $displayDate = get_feedback_display_date($abstract->feedback_created_at, $abstract->feedback_updated_at);
                                                ?>
                                                <div class="fw-medium"><?= date('F j, Y', strtotime($displayDate)) ?></div>
                                                <small class="text-muted"><?= date('g:i A', strtotime($displayDate)) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?><div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="fw-medium text-muted">Submission Date:</span>
                                        <div class="text-end">
                                            <div class="fw-medium"><?= date('F j, Y', strtotime($abstract->submission_date)) ?></div>
                                            <small class="text-muted"><?= date('g:i A', strtotime($abstract->submission_date)) ?></small>
                                        </div>
                                    </div>
                                </div> <?php if (!empty($abstract->subtheme_name)): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="fw-medium text-muted">Subtheme:</span>
                                            <div class="text-end" style="max-width: 200px;">
                                                <span class="badge bg-primary-subtle text-primary px-2 py-1 text-wrap">
                                                    <?= esc($abstract->subtheme_name) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="fw-medium text-muted">Abstract Status:</span>
                                        <div class="text-end">
                                            <?php
                                            $statusClass = 'secondary';
                                            $statusIcon = 'ri-file-text-line';
                                            $statusText = ucfirst($abstract->abstract_status ?? 'submitted');

                                            switch ($abstract->abstract_status ?? 'submitted') {
                                                case 'accepted':
                                                    $statusClass = 'success';
                                                    $statusIcon = 'ri-check-double-line';
                                                    break;
                                                case 'under_review':
                                                    $statusClass = 'warning';
                                                    $statusIcon = 'ri-eye-line';
                                                    break;
                                                case 'rejected':
                                                    $statusClass = 'danger';
                                                    $statusIcon = 'ri-close-circle-line';
                                                    break;
                                                case 'revision_required':
                                                    $statusClass = 'info';
                                                    $statusIcon = 'ri-edit-line';
                                                    break;
                                                default:
                                                    $statusClass = 'secondary';
                                                    $statusIcon = 'ri-file-text-line';
                                            }
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?> px-2 py-1">
                                                <i class="<?= $statusIcon ?> me-1"></i><?= $statusText ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- Authors Section -->
                        <?php if (!empty($abstract->authors)): ?>
                            <div class="card border">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">
                                        <i class="ri-team-line me-2"></i>Authors
                                        <span class="badge bg-secondary-subtle text-secondary ms-2"><?= count($abstract->authors) ?></span>
                                    </h5>

                                    <div class="authors-list">
                                        <?php foreach ($abstract->authors as $index => $author): ?>
                                            <div class="author-card mb-3 p-3 border rounded-3 <?= $author->is_participant ? 'border-primary bg-primary-subtle' : 'border-light bg-light' ?>">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <span class="author-number badge bg-secondary me-2"><?= $index + 1 ?></span>
                                                            <h6 class="mb-0 fw-semibold text-dark"><?= esc($author->full_name) ?></h6>
                                                            <?php if ($author->is_participant): ?>
                                                                <span class="badge bg-primary ms-2">
                                                                    <i class="ri-star-line me-1"></i>Primary Author
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary-subtle text-secondary ms-2">Co-author</span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="author-details">
                                                            <div class="mb-1">
                                                                <i class="ri-mail-line text-muted me-1"></i>
                                                                <span class="text-muted small"><?= esc($author->email) ?></span>
                                                            </div>
                                                            <?php if (!empty($author->institution)): ?>
                                                                <div class="mb-0">
                                                                    <i class="ri-building-line text-muted me-1"></i>
                                                                    <span class="text-muted small"><?= esc($author->institution) ?></span>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-xxl-9"> <!-- Abstract Versions -->
                        <?php if (!empty($abstract->versions) && count($abstract->versions) > 1): ?>
                            <div class="card border mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        Submitted Versions
                                        <small class="text-muted">(Draft versions are not shown)</small>
                                    </h5>

                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Version</th>
                                                    <th>Title</th>
                                                    <th>Status</th>
                                                    <th>Submitted</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($abstract->versions as $version): ?>
                                                    <?php if ($version->status === 'submitted'): ?>
                                                        <tr class="<?= $version->is_current_version ? 'table-success' : '' ?>">
                                                            <td>
                                                                v<?= $version->version_number ?>
                                                                <?php if ($version->is_current_version): ?>
                                                                    <span class="badge bg-success ms-1">Current</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= esc($version->title) ?></td>
                                                            <td>
                                                                <span class="badge bg-success">Submitted</span>
                                                            </td>
                                                            <td><?= date('M d, Y H:i', strtotime($version->created_at)) ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-primary" onclick="viewVersion(<?= $version->id ?>)">
                                                                    <i class="ri-eye-line"></i> View
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?> <!-- Main Abstract Content -->
                        <div class="card border">
                            <div class="card-body">
                                <h5 class="card-title">
                                    Abstract Content
                                    <?php if (!empty($abstract->versions)): ?>
                                        <small class="text-muted">(Current Submitted Version: v<?= $abstract->versions[0]->version_number ?? 1 ?>)</small>
                                    <?php endif; ?>
                                </h5>

                                <div class="mb-4">
                                    <h6 class="fw-semibold text-primary">Title</h6>
                                    <p class="fs-14"><?= esc($abstract->abstract_title) ?></p>
                                </div>

                                <div class="mb-4">
                                    <h6 class="fw-semibold text-primary">Abstract</h6>
                                    <div class="fs-14 text-muted">
                                        <?= nl2br(esc($abstract->abstract_content)) ?>
                                    </div>
                                </div>

                                <?php if (!empty($abstract->keywords)): ?>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold text-primary">Keywords</h6>
                                        <div>
                                            <?php foreach (explode(',', $abstract->keywords) as $keyword): ?>
                                                <span class="badge bg-light text-dark me-1"><?= esc(trim($keyword)) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?> <?php if (!empty($abstract->refs)): ?>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold text-primary">References</h6>
                                        <div class="fs-14 text-muted">
                                            <?= nl2br(esc($abstract->refs)) ?>
                                        </div>
                                    </div> <?php endif; ?>
                            </div>
                        </div><!-- Associated Papers -->
                        <?php if (!empty($abstract->papers)): ?>
                            <div class="card border">
                                <div class="card-body">
                                    <h5 class="card-title">Associated Papers</h5>

                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Notes</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($abstract->papers as $paper): ?>
                                                    <tr>
                                                        <td><?= esc($paper->notes ?: 'No notes provided') ?></td>
                                                        <td>
                                                            <span class="badge bg-<?= $paper->status === 'submitted' ? 'success' : ($paper->status === 'under_review' ? 'warning' : ($paper->status === 'accepted' ? 'primary' : 'secondary')) ?>">
                                                                <?= ucfirst(str_replace('_', ' ', $paper->status)) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= date('M d, Y', strtotime($paper->created_at)) ?></td>
                                                        <td>
                                                            <?php if (!empty($paper->file_url)): ?>
                                                                <a href="<?= esc($paper->file_url) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                    <i class="ri-download-line"></i> Download
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">No file</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?> <!-- Your Feedback -->
                        <?php if (!empty($abstract->feedback)): ?>
                            <div class="alert alert-success">
                                <h6 class="fw-semibold text-success mb-2">
                                    <i class="ri-message-2-line me-1"></i> Your Review Feedback
                                </h6>
                                <div class="fs-14">
                                    <?= nl2br(esc($abstract->feedback)) ?>
                                </div> <?php
                                        $displayDate = get_feedback_display_date($abstract->feedback_created_at, $abstract->feedback_updated_at);
                                        ?>
                                <small class="text-muted">Submitted on: <?= date('F d, Y H:i', strtotime($displayDate)) ?></small>

                                <!-- Abstract Status Action -->
                                <div class="mt-3 pt-2 border-top">
                                    <?php if (isset($abstract->abstract_status) && $abstract->abstract_status === 'accepted'): ?>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success me-2">
                                                <i class="ri-check-double-line me-1"></i>Abstract Accepted
                                            </span>
                                            <small class="text-muted">This abstract has been accepted and requires no further changes.</small>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-muted small">Abstract Status:
                                                <span class="badge bg-<?= isset($abstract->abstract_status) && $abstract->abstract_status === 'under_review' ? 'warning' : 'secondary' ?>">
                                                    <?= ucfirst($abstract->abstract_status ?? 'under_review') ?>
                                                </span>
                                            </span>
                                            <button class="btn btn-success btn-sm" onclick="acceptAbstract(<?= $abstract->abstract_id ?>)" id="acceptBtn">
                                                <i class="ri-check-double-line me-1"></i>Accept Abstract
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            Click "Accept Abstract" if you believe this abstract is ready for publication and requires no further revisions.
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- All Feedback History -->
                        <?php if (!empty($abstract->all_feedbacks)): ?>
                            <div class="card border">
                                <div class="card-body">
                                    <h5 class="card-title">Feedback History from All Reviewers</h5>

                                    <?php foreach ($abstract->all_feedbacks as $feedback): ?>
                                        <div class="border rounded p-3 mb-3 <?= $feedback->reviewer_id == session()->get('reviewerId') ? 'bg-light' : '' ?>">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <?= esc($feedback->reviewer_name) ?>
                                                        <?php if ($feedback->reviewer_id == session()->get('reviewerId')): ?>
                                                            <span class="badge bg-primary ms-1">You</span>
                                                        <?php endif; ?>
                                                    </h6> <small class="text-muted">
                                                        Version: v<?= $feedback->version_number ?> |
                                                        Submitted: <?= date('M d, Y H:i', strtotime($feedback->created_at)) ?>
                                                        <?php if (is_valid_timestamp($feedback->updated_at) && $feedback->created_at != $feedback->updated_at): ?>
                                                            | Updated: <?= date('M d, Y H:i', strtotime($feedback->updated_at)) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="fs-14">
                                                <?= nl2br(esc($feedback->feedback)) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Version View Modal -->
<div class="modal fade" id="versionModal" tabindex="-1" aria-labelledby="versionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="versionModalLabel">Abstract Version Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="versionContent">
                <!-- Version content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .author-card {
        transition: all 0.2s ease;
    }

    .author-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .author-number {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .author-details i {
        width: 14px;
        font-size: 0.8rem;
    }

    .badge.text-wrap {
        white-space: normal;
        text-align: left;
        line-height: 1.3;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function viewVersion(versionId) {
        console.log('Attempting to view version:', versionId); // Show loading state
        const versionContent = document.getElementById('versionContent');
        if (versionContent) {
            versionContent.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        } // Show modal
        const versionModal = document.getElementById('versionModal');
        if (versionModal) {
            const modalInstance = new bootstrap.Modal(versionModal);
            modalInstance.show();
        } else {
            console.error('Modal element not found');
            alert('Error: Modal not found');
            return;
        }

        // Fetch version details
        fetch('/reviewers/abstracts-papers/version/' + versionId, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status + ' - ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    const version = data.version;
                    let content = `
                <div class="mb-3">
                    <h6 class="fw-semibold text-primary">Title</h6>
                    <p>${escapeHtml(version.title)}</p>
                </div>
                <div class="mb-3">
                    <h6 class="fw-semibold text-primary">Content</h6>
                    <div class="text-muted">${escapeHtml(version.content).replace(/\n/g, '<br>')}</div>
                </div>
            `;

                    if (version.keywords) {
                        content += `
                    <div class="mb-3">
                        <h6 class="fw-semibold text-primary">Keywords</h6>
                        <div>
                            ${version.keywords.split(',').map(keyword => 
                                `<span class="badge bg-light text-dark me-1">${escapeHtml(keyword.trim())}</span>`
                            ).join('')}
                        </div>
                    </div>
                `;
                    }

                    if (version.refs) {
                        content += `
                    <div class="mb-3">
                        <h6 class="fw-semibold text-primary">References</h6>
                        <div class="text-muted">${escapeHtml(version.refs).replace(/\n/g, '<br>')}</div>
                    </div>
                `;
                    }

                    content += `
                <div class="mb-3">
                    <h6 class="fw-semibold text-primary">Version Info</h6>
                    <p><strong>Version:</strong> v${version.version_number}<br>
                    <strong>Created:</strong> ${new Date(version.created_at).toLocaleDateString()}<br>
                    <strong>Status:</strong> ${version.is_active ? 'Active' : 'Inactive'}</p>
                </div>
            `;
                    if (versionContent) {
                        versionContent.innerHTML = content;
                    }
                } else {
                    console.error('Server returned error:', data.message);
                    if (versionContent) {
                        versionContent.innerHTML = '<div class="alert alert-danger">Error loading version details: ' + data.message + '</div>';
                    }
                }
            }).catch(error => {
                console.error('Fetch error:', error);
                if (versionContent) {
                    versionContent.innerHTML = '<div class="alert alert-danger">Error loading version details: ' + error.message + '</div>';
                }
            });
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) {
            return map[m];
        });
    }

    function acceptAbstract(abstractId) {
        // Show SweetAlert confirmation
        Swal.fire({
            title: 'Accept Abstract?',
            text: 'This will mark the abstract as ready for publication and indicate that no further revisions are needed.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Accept It!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                processAcceptance(abstractId);
            }
        });
    }

    function processAcceptance(abstractId) {
        console.log('Attempting to accept abstract:', abstractId);

        // Show loading state
        const acceptBtn = document.getElementById('acceptBtn');
        const originalText = acceptBtn.innerHTML;
        acceptBtn.disabled = true;
        acceptBtn.innerHTML = '<i class="ri-loader-2-line me-1"></i>Processing...';

        // Send acceptance request
        fetch('/reviewers/abstracts-papers/accept/' + abstractId, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify({
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status + ' - ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    // Show success message with SweetAlert
                    Swal.fire({
                        title: 'Success!',
                        text: 'Abstract has been successfully accepted!',
                        icon: 'success',
                        confirmButtonColor: '#198754',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    console.error('Server returned error:', data.message);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Error accepting abstract: ' + data.message,
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK'
                    });
                    // Restore button state
                    acceptBtn.disabled = false;
                    acceptBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Error accepting abstract: ' + error.message,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK'
                });
                // Restore button state
                acceptBtn.disabled = false;
                acceptBtn.innerHTML = originalText;
            });
    }
</script>
<?= $this->endSection() ?>