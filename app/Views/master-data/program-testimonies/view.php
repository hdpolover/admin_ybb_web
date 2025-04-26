<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title'=>'View Testimony')); ?>
    <?= $this->include('partials/head-css') ?>
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
                    <?php echo view('partials/page-title', array('pagetitle'=>'Testimonies', 'title'=>'View Testimony')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Testimony Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-4">
                                            <?php if (!empty($testimony->img_url)) : ?>
                                                <img src="<?= $testimony->img_url ?>" alt="<?= $testimony->person_name ?>" class="img-fluid rounded">
                                            <?php else : ?>
                                                <div class="text-center p-4 bg-light rounded">
                                                    <i class="ri-user-3-line ri-4x text-muted"></i>
                                                    <p class="mt-2 text-muted">No image available</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-8">
                                            <h4><?= $testimony->person_name ?></h4>
                                            <?php if (!empty($testimony->occupation)) : ?>
                                                <p class="text-muted mb-1"><?= $testimony->occupation ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($testimony->institution)) : ?>
                                                <p class="text-muted"><?= $testimony->institution ?></p>
                                            <?php endif; ?>
                                            
                                            <div class="mt-4">
                                                <h5>Testimony</h5>
                                                <div class="p-3 bg-light rounded">
                                                    <?= nl2br($testimony->testimony) ?>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-4">
                                                <span class="badge <?= $testimony->is_active ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $testimony->is_active ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </div>
                                            
                                            <div class="mt-4">
                                                <a href="<?= base_url('master-data/program-testimonies') ?>" class="btn btn-secondary">Back to List</a>
                                                <a href="#" class="btn btn-primary edit-testimony" 
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#editTestimonyModal"
                                                   data-id="<?= $testimony->id ?>"
                                                   data-person_name="<?= $testimony->person_name ?>"
                                                   data-testimony="<?= htmlspecialchars($testimony->testimony) ?>"
                                                   data-occupation="<?= $testimony->occupation ?>"
                                                   data-institution="<?= $testimony->institution ?>"
                                                   data-img_url="<?= $testimony->img_url ?>"
                                                   data-is_active="<?= $testimony->is_active ?>">
                                                    Edit Testimony
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?= $this->include('partials/footer') ?>
        </div>
    </div>

    <?= $this->include('partials/customizer') ?>

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
