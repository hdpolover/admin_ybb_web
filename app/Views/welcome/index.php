<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title' => 'Welcome')); ?>

    <!-- jsvectormap css -->
    <link href="/assets/libs/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="/assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css" />

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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Welcome', 'title' => 'Program Selection')); ?>

                    <?php foreach ($programs as $categoryProgram): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="bg-primary p-3 rounded">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h2 class="text-white mb-0"><?= $categoryProgram->name ?? 'Uncategorized' ?></h2>
                                        <?php if (!empty($categoryProgram->web_url)): ?>
                                            <a href="<?= $categoryProgram->web_url ?>" class="btn btn-light" target="_blank">
                                                <i class="ri-external-link-line me-1"></i> Visit Website
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <?php if (!empty($categoryProgram->programs)): ?>
                                <?php foreach ($categoryProgram->programs as $program): ?>
                                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                                        <?= view('welcome/components/program-card', ['program' => $program]) ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="ri-information-line me-2"></i>
                                        No programs available in this category yet.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <?= $this->include('partials/customizer') ?>

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- apexcharts -->
    <script src="/assets/libs/apexcharts/apexcharts.min.js"></script>

    <!-- Vector map-->
    <script src="/assets/libs/jsvectormap/jsvectormap.min.js"></script>
    <script src="/assets/libs/jsvectormap/maps/world-merc.js"></script>

    <!--Swiper slider js-->
    <script src="/assets/libs/swiper/swiper-bundle.min.js"></script>

    <!-- Dashboard init -->
    <script src="/assets/js/pages/dashboard-ecommerce.init.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <?php if (session()->has('error_message')): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?= session('error_message') ?>',
            confirmButtonText: 'OK'
        });
    });
    </script>
    <?php endif; ?>
</body>

</html>