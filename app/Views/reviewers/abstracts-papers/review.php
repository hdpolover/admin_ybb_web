<?= $this->extend('layouts/reviewer') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title mb-0 flex-grow-1">Review Abstract</h4>
                    <div class="flex-shrink-0">
                        <a href="/reviewers/abstracts-papers" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back to List
                        </a>
                        <a href="/reviewers/abstracts-papers/view/<?= $abstract->abstract_id ?>" class="btn btn-info ms-1">
                            <i class="ri-eye-line me-1"></i> View Only
                        </a>
                    </div>
                </div>
            </div><!-- end card header -->

            <div class="card-body">
                <form id="review-form" action="/reviewers/abstracts-papers/submit-review/<?= $abstract->abstract_id ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-xxl-8">
                            <div class="card border">
                                <div class="card-body">
                                    <h5 class="card-title">Abstract Content</h5>                                    <div class="mb-4">
                                        <h6 class="fw-semibold text-primary">
                                            Title 
                                            <small class="text-muted fw-normal">
                                                (<?= str_word_count($abstract->abstract_title) ?> words)
                                            </small>
                                        </h6>
                                        <p class="fs-14"><?= esc($abstract->abstract_title) ?></p>
                                    </div>

                                    <div class="mb-4">
                                        <h6 class="fw-semibold text-primary">
                                            Abstract 
                                            <small class="text-muted fw-normal">
                                                (<?= str_word_count(strip_tags($abstract->abstract_content)) ?> words)
                                            </small>
                                        </h6>                                        <div class="fs-14 text-muted bg-light p-3 rounded abstract-content">
                                            <?= nl2br(esc($abstract->abstract_content)) ?>
                                        </div>
                                    </div>                                    <?php if (!empty($abstract->keywords)): ?>
                                        <div class="mb-4">
                                            <h6 class="fw-semibold text-primary">
                                                Keywords 
                                                <small class="text-muted fw-normal">
                                                    (<?= str_word_count($abstract->keywords) ?> words)
                                                </small>
                                            </h6>
                                            <div>
                                                <?php foreach (explode(',', $abstract->keywords) as $keyword): ?>
                                                    <span class="badge bg-light text-dark me-1"><?= esc(trim($keyword)) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?> <?php if (!empty($abstract->refs)): ?>
                                        <div class="mb-4">
                                            <h6 class="fw-semibold text-primary">
                                                References 
                                                <small class="text-muted fw-normal">
                                                    (<?= str_word_count(strip_tags($abstract->refs)) ?> words)
                                                </small>
                                            </h6>
                                            <div class="fs-14 text-muted bg-light p-3 rounded">
                                                <?= nl2br(esc($abstract->refs)) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>                                    <div class="mb-4">
                                        <h6 class="fw-semibold text-primary">Participant Information</h6>
                                        <div class="bg-light p-3 rounded review-info-section">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Name:</strong> <?= esc($abstract->participant_name) ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Program:</strong> <?= esc($abstract->program_name) ?>
                                                </div>
                                                <?php if (!empty($abstract->subtheme_name)): ?>
                                                <div class="col-md-12 mt-2">
                                                    <strong>Subtheme:</strong> 
                                                    <span class="badge bg-primary-subtle text-primary"><?= esc($abstract->subtheme_name) ?></span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <h6 class="fw-semibold text-primary">Submission Details</h6>
                                        <div class="bg-light p-3 rounded">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Submission Date:</strong><br>
                                                    <small class="text-muted"><?= date('F j, Y g:i A', strtotime($abstract->submission_date)) ?></small>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Status:</strong><br>
                                                    <span class="badge bg-<?= $abstract->abstract_status === 'submitted' ? 'success' : ($abstract->abstract_status === 'under_review' ? 'warning' : 'secondary') ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $abstract->abstract_status)) ?>
                                                    </span>
                                                </div>
                                                <?php if (!empty($abstract->version_number)): ?>
                                                <div class="col-md-6 mt-2">
                                                    <strong>Version:</strong> v<?= $abstract->version_number ?>
                                                </div>
                                                <?php endif; ?>
                                                <div class="col-md-6 mt-2">
                                                    <strong>Abstract ID:</strong> #<?= $abstract->abstract_id ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Authors Information -->
                                    <?php if (!empty($abstract->authors)): ?>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold text-primary">Authors</h6>
                                        <div class="bg-light p-3 rounded">
                                            <?php foreach ($abstract->authors as $index => $author): ?>
                                            <div class="d-flex align-items-center mb-2 <?= $index > 0 ? 'border-top pt-2' : '' ?>">
                                                <span class="badge bg-secondary me-2"><?= $index + 1 ?></span>
                                                <div class="flex-grow-1">
                                                    <strong><?= esc($author->full_name) ?></strong>
                                                    <?php if ($author->is_participant): ?>
                                                        <span class="badge bg-primary-subtle text-primary ms-1">Primary</span>
                                                    <?php endif; ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?= esc($author->email) ?>
                                                        <?php if (!empty($author->institution)): ?>
                                                            | <?= esc($author->institution) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-4">
                            <div class="card border">
                                <div class="card-body">
                                    <h5 class="card-title">Feedback Form</h5>                                    <div class="mb-3">
                                        <label for="feedback" class="form-label">Feedback <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="feedback" name="feedback" rows="10" placeholder="Provide detailed feedback for the author..." required><?= esc($abstract->feedback ?? '') ?></textarea>
                                        <div class="form-text">Provide constructive feedback to help improve the abstract</div>
                                    </div>

                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">Review Guidelines</h6>
                                        <ul class="mb-0">
                                            <li>Evaluate clarity and significance</li>
                                            <li>Check research approach appropriateness</li>
                                            <li>Consider novelty and contribution</li>
                                            <li>Provide constructive feedback</li>
                                        </ul>
                                    </div>                                    <div class="d-grid gap-2">
                                        <?php if (!empty($abstract->feedback)): ?>
                                            <button type="submit" class="btn btn-success" id="submit-feedback-btn">
                                                <i class="ri-refresh-line me-1"></i> Update Feedback
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-primary" id="submit-feedback-btn">
                                                <i class="ri-send-plane-line me-1"></i> Submit Feedback
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.bg-light {
    background-color: #f8f9fa !important;
}

.review-info-section {
    background-color: #f8f9fa;
    border-left: 4px solid #0d6efd;
}

.author-item {
    transition: background-color 0.2s ease;
}

.author-item:hover {
    background-color: #e9ecef;
}

.word-count {
    font-size: 0.8rem;
    color: #6c757d;
}

.status-badge {
    font-size: 0.85rem;
}

.swal2-popup {
    font-size: 0.95rem;
}

.abstract-content {
    line-height: 1.6;
    text-align: justify;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Form validation and SweetAlert confirmation
    document.getElementById('review-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        const feedback = document.getElementById('feedback').value;
        const isUpdate = <?= !empty($abstract->feedback) ? 'true' : 'false' ?>;

        if (!feedback.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Feedback Required',
                text: 'Please provide feedback before submitting.',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }

        // Show confirmation dialog
        Swal.fire({
            title: isUpdate ? 'Update Feedback?' : 'Submit Feedback?',
            html: `
                <div class="text-start">
                    <p class="mb-2"><strong>Abstract:</strong> <?= esc($abstract->abstract_title) ?></p>
                    <p class="mb-2"><strong>Participant:</strong> <?= esc($abstract->participant_name) ?></p>
                    <p class="mb-3"><strong>Your Feedback:</strong></p>
                    <div class="bg-light p-2 rounded" style="max-height: 120px; overflow-y: auto;">
                        <small>${feedback.substring(0, 200)}${feedback.length > 200 ? '...' : ''}</small>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: isUpdate ? '#198754' : '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: isUpdate ? '<i class="ri-refresh-line me-1"></i> Update Feedback' : '<i class="ri-send-plane-line me-1"></i> Submit Feedback',
            cancelButtonText: '<i class="ri-close-line me-1"></i> Cancel',
            reverseButtons: true,
            customClass: {
                popup: 'text-start'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: isUpdate ? 'Updating...' : 'Submitting...',
                    text: 'Please wait while we process your feedback.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit the form
                this.submit();
            }
        });
    });
</script>
<?= $this->endSection() ?>