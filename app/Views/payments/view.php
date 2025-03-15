<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Payment Details')); ?>
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Payments', 'title' => 'Payment Details')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Transaction #<?= esc($payment->id) ?></h5>
                                    <div>
                                        <a href="<?= site_url('payments') ?>" class="btn btn-soft-secondary btn-sm">
                                            <i class="ri-arrow-left-line align-middle me-1"></i> Back to Payments
                                        </a>
                                        <a href="<?= site_url('payments/edit/' . $payment->id) ?>" class="btn btn-primary btn-sm ms-1">
                                            <i class="ri-edit-line align-middle me-1"></i> Edit
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-4">
                                                <h5 class="text-muted mb-3">Payment Details</h5>
                                                <div class="table-responsive">
                                                    <table class="table table-borderless mb-0">
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row" style="width: 200px;">Transaction ID</th>
                                                                <td><?= esc($payment->id) ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Amount</th>
                                                                <td>
                                                                    <?php
                                                                    $currencySymbol = $payment->currency === 'USD' ? '$' : 'Rp';
                                                                    $decimals = $payment->currency === 'USD' ? 2 : 0;
                                                                    $thousandSep = $payment->currency === 'USD' ? ',' : '.';
                                                                    $decimalSep = $payment->currency === 'USD' ? '.' : ',';
                                                                    echo $currencySymbol . ' ' . number_format($payment->amount, $decimals, $decimalSep, $thousandSep);
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Currency</th>
                                                                <td><?= $payment->currency === 'USD' ? 'US Dollar (USD)' : 'Indonesian Rupiah (IDR)' ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Status</th>
                                                                <td>
                                                                    <?php 
                                                                    $statusClasses = [
                                                                        0 => 'secondary',
                                                                        1 => 'warning',
                                                                        2 => 'success',
                                                                        3 => 'danger',
                                                                        4 => 'danger'
                                                                    ];
                                                                    $statusLabels = [
                                                                        0 => 'Created',
                                                                        1 => 'Pending',
                                                                        2 => 'Success',
                                                                        3 => 'Cancelled',
                                                                        4 => 'Rejected'
                                                                    ];
                                                                    $statusClass = $statusClasses[$payment->status] ?? 'secondary';
                                                                    $statusLabel = $statusLabels[$payment->status] ?? 'Unknown';
                                                                    ?>
                                                                    <span class="badge bg-<?= $statusClass ?>"><?= $statusLabel ?></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Payment Method</th>
                                                                <td><?= esc($payment->payment_method_id) ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Payment Date</th>
                                                                <td><?= format_date($payment->created_at, 'F j, Y H:i:s') ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-4">
                                                <h5 class="text-muted mb-3">Participant Information</h5>
                                                <div class="table-responsive">
                                                    <table class="table table-borderless mb-0">
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row" style="width: 200px;">Name</th>
                                                                <td><?= esc($payment->participant_name) ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Email</th>
                                                                <td><?= esc($payment->participant_email) ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Participant ID</th>
                                                                <td>
                                                                    <a href="<?= site_url('participants/view/' . $payment->participant_id) ?>">
                                                                        #<?= esc($payment->participant_id) ?>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($payment->notes)): ?>
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <h5 class="text-muted mb-3">Notes</h5>
                                            <div class="bg-light p-3 rounded">
                                                <?= nl2br(esc($payment->notes)) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
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

    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>

    <!-- App js -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>

</html>
