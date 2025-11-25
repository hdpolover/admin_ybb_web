<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => $title)); ?>
    <?= $this->include('partials/head-css') ?>

    <style>
        .essay-content {
            background-color: #f8f9fa;
            border-radius: 8px;
            position: relative;
        }

        .essay-text {
            font-size: 0.95rem;
            line-height: 1.6;
            text-align: justify;
        }

        .essay-collection .card {
            transition: all 0.3s ease;
            border-left: 4px solid #0ab39c !important;
        }

        .essay-collection .card:hover {
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .avatar-title {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .participant-header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-card {
            border-left: 4px solid #0ab39c;
        }
    </style>
</head>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- Page title -->
                    <?php $pageTitle = 'Essay Details'; 
                    $breadcrumbItems = [
                        ['text' => 'Submissions', 'link' => '#'],
                        ['text' => 'Essays', 'link' => base_url('submissions/essays')],
                        ['text' => 'View', 'link' => '#']
                    ]; ?>
                    <?= view('partials/page-title', ['pagetitle' => 'YBB Admin', 'title' => $pageTitle, 'breadcrumb' => $breadcrumbItems]) ?>

                    <!-- Back button -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <a href="<?= base_url('submissions/essays') ?>" class="btn btn-light">
                                <i class="ri-arrow-left-line me-1"></i>Back to Essays List
                            </a>
                        </div>
                    </div>

                    <!-- Participant Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card participant-header-card border-0">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h4 class="text-white mb-2">
                                                <i class="ri-user-line me-2"></i><?= esc($participant->full_name ?? 'N/A') ?>
                                            </h4>
                                            <div class="d-flex gap-3 text-white-50">
                                                <span><i class="ri-mail-line me-1"></i><?= esc(isset($participant->user) ? $participant->user->email : 'N/A') ?></span>
                                                <span><i class="ri-id-card-line me-1"></i>Participant ID: <?= $participant->id ?></span>
                                                <span>
                                                    <i class="ri-bookmark-line me-1"></i>
                                                    <?= $participant->category === 'fully_funded' ? 'Fully Funded' : 'Self Funded' ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <a href="<?= base_url('users/participants/view/' . $participant->id) ?>" 
                                               class="btn btn-light">
                                                <i class="ri-user-line me-1"></i>View Full Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Essay Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-primary-subtle text-primary rounded">
                                                    <i class="ri-file-list-3-line fs-20"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-1">Total Essays</p>
                                            <h4 class="mb-0"><?= count($essays) ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-success-subtle text-success rounded">
                                                    <i class="ri-check-line fs-20"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-1">Answered</p>
                                            <h4 class="mb-0">
                                                <?php 
                                                $answeredCount = 0;
                                                foreach ($essays as $essay) {
                                                    if (!empty($essay['answer'])) $answeredCount++;
                                                }
                                                echo $answeredCount;
                                                ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-info-subtle text-info rounded">
                                                    <i class="ri-text fs-20"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-1">Total Words</p>
                                            <h4 class="mb-0">
                                                <?php 
                                                $totalWords = 0;
                                                foreach ($essays as $essay) {
                                                    if (!empty($essay['answer'])) {
                                                        $totalWords += str_word_count($essay['answer']);
                                                    }
                                                }
                                                echo number_format($totalWords);
                                                ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Essays Collection -->
                    <div class="row">
                        <div class="col-12">
                            <?php if (!empty($essays)): ?>
                                <div class="essay-collection">
                                    <?php $essayCount = 1; ?>
                                    <?php foreach ($essays as $essay): ?>
                                        <div class="card border mb-4">
                                            <div class="card-header bg-light">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar-sm">
                                                            <div class="avatar-title rounded-circle bg-primary text-white">
                                                                <?= $essayCount ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="card-title mb-0">
                                                            <?= isset($essay['question']) ? nl2br(esc($essay['question'])) : 'No question available' ?>
                                                        </h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <?php if (isset($essay['answer']) && !empty($essay['answer'])): ?>
                                                    <div class="essay-content p-3">
                                                        <div class="position-relative">
                                                            <div class="essay-icon position-absolute" 
                                                                 style="top: 0; left: -10px; opacity: 0.1">
                                                                <i class="ri-double-quotes-l fs-35 text-primary"></i>
                                                            </div>
                                                            <div class="ps-4 pt-2 pb-2 essay-text">
                                                                <?= nl2br(esc($essay['answer'])) ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Word count and metadata -->
                                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                                        <div>
                                                            <span class="badge bg-soft-secondary text-dark me-2">
                                                                <i class="ri-text me-1"></i>
                                                                <?= str_word_count($essay['answer']) ?> words
                                                            </span>
                                                            <span class="badge bg-soft-secondary text-dark">
                                                                <i class="ri-character-recognition-line me-1"></i>
                                                                <?= strlen($essay['answer']) ?> characters
                                                            </span>
                                                        </div>
                                                        <?php if (!empty($essay['created_at'])): ?>
                                                            <small class="text-muted">
                                                                <i class="ri-time-line me-1"></i>
                                                                Submitted: <?= date('M d, Y H:i', strtotime($essay['created_at'])) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-warning mb-0">
                                                        <i class="ri-error-warning-line me-2"></i>No answer provided
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php $essayCount++; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <div class="avatar-lg mx-auto mb-4">
                                            <div class="avatar-title bg-soft-warning text-warning rounded-circle">
                                                <i class="ri-file-warning-line fs-36"></i>
                                            </div>
                                        </div>
                                        <h5 class="mb-1">No Essays Found</h5>
                                        <p class="text-muted">This participant has not submitted any essays yet.</p>
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

    <!-- App js -->
    <script src="<?= base_url() ?>/assets/js/app.js"></script>
</body>

</html>
