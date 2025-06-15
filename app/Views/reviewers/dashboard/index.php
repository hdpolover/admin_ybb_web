<?= $this->extend('layouts/reviewer') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col">

        <div class="h-100">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1">Good Morning, <?= $currentUser->name ?>!</h4>
                            <p class="text-muted mb-0">Here's your review summary today.</p>
                        </div>
                    </div><!-- end card header -->
                </div>
                <!--end col-->
            </div> <!--end row--> <!-- Priority Stats Row - Most Important Metrics -->
            <div class="row mb-3">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card card-animate h-100">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between flex-grow-1">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-2 fs-12">Pending Reviews</p>
                                    <h3 class="fs-24 fw-bold ff-secondary mb-2">
                                        <span class="counter-value" data-target="<?= $stats['total_pending'] ?>">0</span>
                                    </h3>
                                    <span class="badge bg-warning-subtle text-warning fs-11">
                                        <i class="ri-time-line align-middle"></i> Awaiting Action
                                    </span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-warning-subtle rounded fs-3">
                                        <i class="bx bx-time text-warning"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card card-animate h-100">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between flex-grow-1">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-2 fs-12">Completed Reviews</p>
                                    <h3 class="fs-24 fw-bold ff-secondary mb-2">
                                        <span class="counter-value" data-target="<?= $stats['total_completed'] ?>">0</span>
                                    </h3>
                                    <span class="badge bg-success-subtle text-success fs-11">
                                        <i class="ri-check-line align-middle"></i> Feedbacks Given
                                    </span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-success-subtle rounded fs-3">
                                        <i class="bx bx-check-circle text-success"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card card-animate h-100">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between flex-grow-1">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-2 fs-12">Completion Rate</p>
                                    <h3 class="fs-24 fw-bold ff-secondary mb-2">
                                        <span class="counter-value" data-target="<?= $stats['completion_rate'] ?>">0</span>%
                                    </h3>
                                    <span class="badge bg-info-subtle text-info fs-11">
                                        <i class="ri-percent-line align-middle"></i> Overall Progress
                                    </span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-info-subtle rounded fs-3">
                                        <i class="bx bx-percentage text-info"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card card-animate h-100">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between flex-grow-1">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-2 fs-12">Total Available</p>
                                    <h3 class="fs-24 fw-bold ff-secondary mb-2">
                                        <span class="counter-value" data-target="<?= $enhancedStats['total_abstracts_in_subthemes'] ?>">0</span>
                                    </h3>
                                    <span class="badge bg-primary-subtle text-primary fs-11">
                                        <i class="ri-file-list-3-line align-middle"></i> In Your Subthemes
                                    </span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-primary-subtle rounded fs-3">
                                        <i class="bx bx-file text-primary"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- Quick Actions & Secondary Stats Row -->
            <div class="row mb-3">
                <div class="col-xl-8 mb-3">
                    <div class="card h-100">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="card-title mb-0">Quick Actions</h6>
                                <small class="text-muted">Jump to important tasks</small>
                            </div>
                            <div class="d-flex gap-2 flex-wrap flex-grow-1 align-items-center">
                                <a href="/reviewers/abstracts-papers" class="btn btn-primary">
                                    <i class="ri-file-list-3-line me-1"></i> View All Abstracts (<?= $enhancedStats['total_abstracts_in_subthemes'] ?>)
                                </a>
                                <?php if ($stats['total_pending'] > 0): ?>
                                    <a href="/reviewers/abstracts-papers?filter=pending" class="btn btn-warning">
                                        <i class="ri-time-line me-1"></i> Pending Reviews (<?= $stats['total_pending'] ?>)
                                    </a>
                                <?php endif; ?>
                                <?php if ($stats['total_completed'] > 0): ?>
                                    <a href="/reviewers/abstracts-papers?status=completed" class="btn btn-success">
                                        <i class="ri-check-line me-1"></i> Completed Reviews (<?= $stats['total_completed'] ?>)
                                    </a>
                                <?php endif; ?>
                                <a href="/reviewers/my-info" class="btn btn-outline-secondary">
                                    <i class="ri-user-line me-1"></i> My Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="row h-100">
                        <div class="col-md-6 col-xl-12 mb-2">
                            <div class="card h-100">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1 fs-13">This Week Reviews</p>
                                        <h5 class="mb-0">
                                            <span class="counter-value" data-target="<?= isset($enhancedStats['reviews_this_week']) ? $enhancedStats['reviews_this_week'] : 0 ?>">0</span>
                                            <small class="fs-14 text-muted ms-1">completed</small>
                                        </h5>
                                    </div>
                                    <div class="avatar-xs flex-shrink-0">
                                        <span class="avatar-title bg-secondary-subtle rounded">
                                            <i class="bx bx-calendar text-secondary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-12">
                            <div class="card h-100">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1 fs-13">Avg Review Time</p>
                                        <h5 class="mb-0">
                                            <span class="counter-value" data-target="<?= isset($enhancedStats['avg_review_time_days']) ? $enhancedStats['avg_review_time_days'] : 0 ?>">0</span>
                                            <small class="fs-14 text-muted ms-1">days</small>
                                        </h5>
                                    </div>
                                    <div class="avatar-xs flex-shrink-0">
                                        <span class="avatar-title bg-dark-subtle rounded">
                                            <i class="bx bx-timer text-dark"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- Main Content Row - Subtheme Progress & Guidelines -->
            <div class="row mb-3">
                <div class="col-xl-8 mb-3">
                    <div class="card h-100">
                        <div class="card-header border-0 pb-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Subtheme Progress</h5>
                                <small class="text-muted">Review distribution by subthemes</small>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <?php if (empty($subthemeStats)): ?>
                                <div class="text-center py-4">
                                    <i class="ri-folder-line display-4 text-muted"></i>
                                    <h6 class="mt-3">No Subthemes Assigned</h6>
                                    <p class="text-muted">You haven't been assigned to any subthemes yet. Please contact the administrator.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-borderless align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Subtheme</th>
                                                <th class="text-center">Total</th>
                                                <th class="text-center">Completed</th>
                                                <th class="text-center">Pending</th>
                                                <th>Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($subthemeStats as $subtheme): ?>
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <h6 class="mb-1"><?= esc($subtheme['name']) ?></h6>
                                                            <?php if (!empty($subtheme['description'])): ?>
                                                                <small class="text-muted"><?= esc(substr($subtheme['description'], 0, 60)) ?><?= strlen($subtheme['description']) > 60 ? '...' : '' ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary-subtle text-primary"><?= $subtheme['total_abstracts'] ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success-subtle text-success"><?= $subtheme['completed_reviews'] ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-warning-subtle text-warning"><?= $subtheme['pending_reviews'] ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-grow-1 me-2">
                                                                <div class="progress" style="height: 6px;">
                                                                    <div class="progress-bar bg-success" role="progressbar"
                                                                        style="width: <?= $subtheme['completion_percentage'] ?>%"
                                                                        aria-valuenow="<?= $subtheme['completion_percentage'] ?>"
                                                                        aria-valuemin="0" aria-valuemax="100">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <span class="text-muted fs-12"><?= $subtheme['completion_percentage'] ?>%</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header border-0 pb-2">
                            <h5 class="card-title mb-0">Performance Summary</h5>
                        </div>
                        <div class="card-body pt-0 d-flex flex-column">
                            <!-- Performance Metrics -->
                            <div class="card bg-light border-0 mb-3 flex-grow-1">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted fs-13">Productivity Score:</span>
                                        <span class="fw-semibold fs-16 text-success"><?= isset($enhancedStats['productivity_score']) ? $enhancedStats['productivity_score'] : 0 ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted fs-13">Reviews This Week:</span>
                                        <span class="fw-semibold fs-16"><?= isset($enhancedStats['reviews_this_week']) ? $enhancedStats['reviews_this_week'] : 0 ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted fs-13">Avg. Review Time:</span>
                                        <span class="fw-semibold fs-16"><?= isset($enhancedStats['avg_review_time_days']) ? $enhancedStats['avg_review_time_days'] : 0 ?> days</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Review Guidelines -->
                            <div class="alert alert-info mb-0">
                                <h6 class="alert-heading mb-2">Review Guidelines</h6>
                                <ul class="mb-2 fs-13">
                                    <li>Evaluate clarity, methodology, and significance</li>
                                    <li>Provide constructive and specific feedback</li>
                                    <li>Consider the abstract's contribution to the field</li>
                                    <li>Complete reviews within assigned timeframes</li>
                                </ul>
                                <small class="text-muted">You can only review abstracts in your assigned subthemes.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- Recent Reviews Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0 pb-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="card-title mb-0">Recent Reviews</h4>
                                <div class="d-flex gap-2">
                                    <?php if ($stats['total_pending'] > 0): ?>
                                        <a href="/reviewers/abstracts-papers?status=pending" class="btn btn-warning btn-sm">
                                            <i class="ri-time-line me-1"></i> View Pending (<?= $stats['total_pending'] ?>)
                                        </a>
                                    <?php endif; ?>
                                    <a href="/reviewers/abstracts-papers" class="btn btn-primary btn-sm">
                                        <i class="ri-arrow-right-line me-1"></i> View All
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <?php if (empty($recentReviews)): ?>
                                <div class="text-center py-5">
                                    <i class="ri-file-list-3-line display-4 text-muted"></i>
                                    <h5 class="mt-3">No Reviews Yet</h5>
                                    <p class="text-muted mb-3">You haven't been assigned any reviews yet.</p>
                                    <a href="/reviewers/abstracts-papers" class="btn btn-primary">
                                        <i class="ri-refresh-line me-1"></i> Check for New Assignments
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-nowrap align-middle table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Abstract Details</th>
                                                <th scope="col">Participant</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Last Activity</th>
                                                <th scope="col" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentReviews as $review): ?>
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <h6 class="fs-14 mb-1"><?= esc(substr($review->abstract_title, 0, 50)) ?><?= strlen($review->abstract_title) > 50 ? '...' : '' ?></h6>
                                                            <div class="d-flex flex-wrap gap-1 mb-1">
                                                                <span class="badge bg-light text-dark fs-11"><?= esc($review->program_name) ?></span>
                                                                <?php if (!empty($review->subtheme_name)): ?>
                                                                    <span class="badge bg-primary-subtle text-primary fs-11"><?= esc($review->subtheme_name) ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <small class="text-muted">Submitted: <?= date('M d, Y', strtotime($review->submission_date)) ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <h6 class="fs-13 mb-0"><?= esc($review->participant_name) ?></h6>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($review->feedback)): ?>
                                                            <span class="badge bg-success-subtle text-success">
                                                                <i class="ri-check-line me-1"></i>Review Complete
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning-subtle text-warning">
                                                                <i class="ri-time-line me-1"></i>Pending Review
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $displayDate = $review->submission_date;
                                                        $activityType = 'Submitted';

                                                        if (!empty($review->feedback)) {
                                                            $activityType = 'Reviewed';
                                                            $displayDate = get_feedback_display_date($review->feedback_created_at, $review->feedback_updated_at);
                                                        }
                                                        ?>
                                                        <div>
                                                            <small class="text-muted"><?= $activityType ?></small>
                                                            <br>
                                                            <small class="text-muted"><?= date('M d, Y', strtotime($displayDate)) ?></small>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex gap-1 justify-content-center">
                                                            <a href="/reviewers/abstracts-papers/view/<?= $review->abstract_id ?>"
                                                                class="btn btn-sm btn-outline-info" title="View Details">
                                                                <i class="ri-eye-line"></i>
                                                            </a>
                                                            <?php if (empty($review->feedback)): ?>
                                                                <a href="/reviewers/abstracts-papers/review/<?= $review->abstract_id ?>"
                                                                    class="btn btn-sm btn-outline-primary" title="Add Review">
                                                                    <i class="ri-edit-line"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="/reviewers/abstracts-papers/review/<?= $review->abstract_id ?>"
                                                                    class="btn btn-sm btn-outline-warning" title="Edit Review">
                                                                    <i class="ri-edit-2-line"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- end .h-100-->

    </div> <!-- end col -->
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Counter animation
    document.addEventListener('DOMContentLoaded', function() {
        const counters = document.querySelectorAll('.counter-value');

        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            let current = 0;
            const increment = target / 50;

            const updateCounter = () => {
                if (current < target) {
                    current += increment;
                    counter.textContent = Math.ceil(current);
                    setTimeout(updateCounter, 30);
                } else {
                    counter.textContent = target;
                }
            };

            updateCounter();
        });

        // Load assigned subthemes
        loadAssignedSubthemes();
    });

    function loadAssignedSubthemes() {
        fetch('/reviewers/dashboard/getReviewerSubthemes')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('assigned-subthemes');

                if (data.success && data.data.length > 0) {
                    let html = '';
                    data.data.forEach(subtheme => {
                        html += `
                            <div class="mb-2 d-flex align-items-center">
                                <div class="avatar-xs flex-shrink-0 me-2">
                                    <span class="avatar-title bg-primary-subtle rounded fs-12">
                                        <i class="ri-folder-line text-primary"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fs-13">${subtheme.subtheme_name}</h6>
                                    <p class="text-muted mb-0 fs-12">${subtheme.theme_name}</p>
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `
                        <div class="text-center">
                            <i class="ri-folder-open-line text-muted fs-2"></i>
                            <p class="text-muted mt-2 mb-0">No subthemes assigned</p>
                            <small class="text-muted">Contact administrator for subtheme assignments</small>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading subthemes:', error);
                document.getElementById('assigned-subthemes').innerHTML = `
                    <div class="text-center">
                        <i class="ri-error-warning-line text-danger fs-2"></i>
                        <p class="text-danger mt-2 mb-0">Error loading subthemes</p>
                    </div>
                `;
            });
    }
</script>
<?= $this->endSection() ?>

<!-- DEBUG INFORMATION (Remove in production) -->
<?php if (ENVIRONMENT === 'development'): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning-subtle">
                    <h6 class="mb-0">DEBUG: Data Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small><strong>Basic Stats:</strong></small>
                            <pre class="small"><?= print_r($stats, true) ?></pre>
                        </div>
                        <div class="col-md-3">
                            <small><strong>Enhanced Stats:</strong></small>
                            <pre class="small"><?= print_r($enhancedStats, true) ?></pre>
                            <?php
                            // Check current week calculation
                            $weekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
                            echo "<small><strong>Week Ago:</strong> {$weekAgo}</small><br>";
                            echo "<small><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</small>";
                            ?>
                        </div>
                        <div class="col-md-3">
                            <small><strong>Subtheme Stats:</strong></small>
                            <pre class="small"><?= count($subthemeStats) ?> subthemes found</pre>
                        </div>
                        <div class="col-md-3">
                            <small><strong>Recent Reviews:</strong></small>
                            <pre class="small"><?= count($recentReviews) ?> recent reviews</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">