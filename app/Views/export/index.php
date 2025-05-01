<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title'=>'Data Export')); ?>

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

                    <?php echo view('partials/page-title', array('pagetitle'=>'Exports', 'title'=>'Data Export Options')); ?>
                    
                    <div class="card">
                        <div class="card-body">
                <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h5>Participants Export</h5>
                            </div>
                            <div class="card-body">
                                <p>Export all participants data including essays to Excel format.</p>                                <a href="<?= base_url('exports/participants') ?>" class="btn btn-primary">
                                    <i class="ri-file-excel-2-line"></i> Export All Participants
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h5>Payments Export</h5>
                            </div>
                            <div class="card-body">
                                <p>Export all payments data to Excel format.</p>                                <a href="<?= base_url('exports/payments') ?>" class="btn btn-success">
                                    <i class="ri-file-excel-2-line"></i> Export All Payments
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5>Filtered Participants Export</h5>
                            </div>
                            <div class="card-body">
                                <form action="<?= base_url('exports/participants/filtered') ?>" method="post">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="program_id">Program:</label>
                                                <select name="program_id" id="program_id" class="form-select">
                                                    <option value="">Select Program</option>
                                                    <?php foreach ($programs as $program): ?>
                                                    <option value="<?= $program->id ?>"><?= $program->name ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="start_date">Start Date:</label>
                                                <input type="date" name="start_date" id="start_date" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="end_date">End Date:</label>
                                                <input type="date" name="end_date" id="end_date" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="status">Status:</label>
                                                <select name="status" id="status" class="form-select">
                                                    <option value="">All Statuses</option>
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="payment_status">Payment Status:</label>
                                                <select name="payment_status" id="payment_status" class="form-select">
                                                    <option value="">All Payment Statuses</option>
                                                    <option value="0">No Payment</option>
                                                    <option value="1">Partial Payment</option>
                                                    <option value="2">Full Payment</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">                                        <button type="submit" class="btn btn-info">
                                            <i class="ri-file-excel-2-line"></i> Export Filtered Participants
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5>Participants by Payment Status</h5>
                            </div>
                            <div class="card-body">
                                <p>Export participants who have successfully made specific payments (registration, program fees, etc.)</p>
                                <form action="<?= base_url('exports/participants/by-payment') ?>" method="post">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="program_id_payment">Program:</label>
                                                <select name="program_id" id="program_id_payment" class="form-select">
                                                    <option value="">Select Program</option>
                                                    <?php foreach ($programs as $program): ?>
                                                    <option value="<?= $program->id ?>"><?= $program->name ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="payment_category">Payment Category:</label>
                                                <select name="payment_category" id="payment_category" class="form-select">
                                                    <option value="">Select Payment Category</option>
                                                    <option value="registration">Registration Fee</option>
                                                    <option value="program_fee_1">Program Fee 1</option>
                                                    <option value="program_fee_2">Program Fee 2</option>
                                                    <option value="program_fee_3">Program Fee 3</option>
                                                    <option value="program_deposit">Program Deposit</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">                                        <button type="submit" class="btn btn-warning">
                                            <i class="ri-file-excel-2-line"></i> Export Participants by Payment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>                </div>
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

    <?= $this->include('partials/customizer') ?>

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>
</body>

</html>
