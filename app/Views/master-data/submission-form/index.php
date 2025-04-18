<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title' => 'Starter')); ?>

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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Submission Form')); ?>

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><?= $currentProgram->name ?> Submission Form</h4>
                        </div>
                        <div class="card-body cursor-default-hover">
                            <!-- Nav tabs -->
                            <ul class="nav nav-pills nav-justified mb-3" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link cursor-on-hover active" data-bs-toggle="tab" href="#participation-category" role="tab" aria-selected="true" tabindex="-1">
                                        Participation Category
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link cursor-on-hover" data-bs-toggle="tab" href="#sub-themes" role="tab" aria-selected="false" tabindex="-1">
                                        Sub themes
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link cursor-on-hover" data-bs-toggle="tab" href="#miscellaneous" role="tab" aria-selected="false" tabindex="-1">
                                        Miscellaneous
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link cursor-on-hover" data-bs-toggle="tab" href="#essays" role="tab" aria-selected="false" tabindex="-1">
                                        Essays
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link cursor-on-hover " data-bs-toggle="tab" href="#preview" role="tab" aria-selected="false" tabindex="-1">
                                        Preview
                                    </a>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content text-muted">
                                <div class="tab-pane active" id="participation-category" role="tabpanel">
                                    <h6>Participation Category</h6>
                                    <p class="mb-0">
                                        Configure participation categories for this submission form.
                                    </p>
                                </div>
                                <div class="tab-pane" id="sub-themes" role="tabpanel">
                                    <h6>Sub Themes</h6>
                                    <p class="mb-0">
                                        Manage the sub themes available for participants to select.
                                    </p>
                                </div>
                                <div class="tab-pane" id="miscellaneous" role="tabpanel">
                                    <h6>Miscellaneous</h6>
                                    <p class="mb-0">
                                        Additional configuration options and settings for the submission form.
                                    </p>
                                </div>
                                <div class="tab-pane" id="essays" role="tabpanel">
                                    <h6>Essays</h6>
                                    <p class="mb-0">
                                        Configure essay requirements and guidelines for participants.
                                    </p>
                                </div>
                                <div class="tab-pane" id="preview" role="tabpanel">
                                    <h6>Preview</h6>
                                    <p class="mb-0">
                                        Preview how the submission form will appear to participants.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!-- end card body -->
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
    <script src="/assets/js/app.js"></script>
</body>

</html>