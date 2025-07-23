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

                    <!-- Flash Messages -->
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ri-check-double-line me-2"></i>
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-2"></i>
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('warning')): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="ri-alert-line me-2"></i>
                            <?= session()->getFlashdata('warning') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Transaction #<?= esc($payment->id) ?></h5>
                                    <div>
                                        <a href="<?= site_url('payments') ?>" class="btn btn-soft-secondary btn-sm me-1">
                                            <i class="ri-arrow-left-line align-middle me-1"></i> Back to Payments
                                        </a>
                                        <button type="button" class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                            <i class="ri-refresh-line align-middle me-1"></i> Update Status
                                        </button>
                                        <a href="<?= site_url('payments/edit/' . $payment->id) ?>" class="btn btn-primary btn-sm">
                                            <i class="ri-edit-line align-middle me-1"></i> Edit
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body"> <!-- Payment Summary Section -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card card-animate bg-gradient-primary">
                                                <div class="card-body p-4">
                                                    <div class="row align-items-center">
                                                        <?php
                                                        $statusClasses = [
                                                            0 => 'bg-soft-secondary text-secondary',
                                                            1 => 'bg-soft-warning text-warning',
                                                            2 => 'bg-soft-success text-success',
                                                            3 => 'bg-soft-danger text-danger',
                                                            4 => 'bg-soft-danger text-danger'
                                                        ];
                                                        $statusLabels = [
                                                            0 => 'Created',
                                                            1 => 'Pending',
                                                            2 => 'Success',
                                                            3 => 'Cancelled',
                                                            4 => 'Rejected'
                                                        ];
                                                        $statusClass = $statusClasses[$payment->status] ?? 'bg-soft-secondary text-secondary';
                                                        $statusLabel = $statusLabels[$payment->status] ?? 'Unknown';
                                                        $iconClass = $payment->status == 2 ? 'ri-check-double-line' : ($payment->status == 1 ? 'ri-time-line' : ($payment->status == 0 ? 'ri-file-list-3-line' :
                                                            'ri-close-circle-line'));

                                                        $statusBadgeClass = $payment->status == 2 ? 'bg-success' : ($payment->status == 1 ? 'bg-warning' : ($payment->status == 0 ? 'bg-info' : 'bg-danger'));
                                                        ?>
                                                        <div class="col-lg-6">
                                                            <div class="d-flex">
                                                                <div class="flex-shrink-0">
                                                                    <div class="avatar-md">
                                                                        <div class="avatar-title rounded-circle fs-1 <?= $statusClass ?>">
                                                                            <i class="<?= $iconClass ?>"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <h1 class="mb-0 text-dark">
                                                                            <?php
                                                                            $currencySymbol = $payment->currency === 'USD' ? '$' : 'Rp';
                                                                            $decimals = $payment->currency === 'USD' ? 2 : 0;
                                                                            $thousandSep = $payment->currency === 'USD' ? ',' : '.';
                                                                            $decimalSep = $payment->currency === 'USD' ? '.' : ',';
                                                                            echo $currencySymbol . ' ' . number_format($payment->amount, $decimals, $decimalSep, $thousandSep);
                                                                            ?>
                                                                        </h1>
                                                                        <span class="badge <?= $statusBadgeClass ?> fs-12 ms-3"><?= $statusLabel ?></span>
                                                                    </div>
                                                                    <p class="mb-0 text-dark">
                                                                        Payment for <span class="fw-semibold"><?= esc($payment->program_name ?? 'Program') ?></span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="row text-center mt-4 mt-lg-0">
                                                                <div class="col-4 border-end">
                                                                    <div>
                                                                        <p class="mb-2 fs-13 text-dark-emphasis">Transaction Code</p>
                                                                        <h5 class="mb-0 fs-15 text-dark"><?= esc($payment->transaction_code ?? 'N/A') ?></h5>
                                                                    </div>
                                                                </div>
                                                                <div class="col-4 border-end">
                                                                    <div>
                                                                        <p class="mb-2 fs-13 text-dark-emphasis">Order ID</p>
                                                                        <h5 class="mb-0 fs-15 text-dark"><?= esc($payment->order_id ?? 'N/A') ?></h5>
                                                                    </div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div>
                                                                        <p class="mb-2 fs-13 text-dark-emphasis">Created</p>
                                                                        <h5 class="mb-0 fs-15 text-dark"><?= format_date($payment->created_at, 'j M Y') ?></h5>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Details Section -->
                                    <div class="row"> <!-- Participant Info -->
                                        <div class="col-xxl-4 col-lg-6 mb-4">
                                            <div class="card h-100 border border-primary-subtle shadow-sm">
                                                <div class="card-header bg-primary-subtle border-bottom">
                                                    <div class="d-flex align-items-center">
                                                        <h5 class="card-title mb-0 flex-grow-1 text-primary">
                                                            <i class="ri-user-2-line me-2"></i>Participant
                                                        </h5>
                                                        <div class="flex-shrink-0">
                                                            <a href="<?= site_url('participants/view/' . $payment->participant_id) ?>" class="btn btn-sm btn-primary">
                                                                <i class="ri-user-search-line me-1"></i>See Profile
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="text-center mb-4">
                                                        <?php if (!empty($payment->participant_picture)): ?>
                                                            <div class="position-relative d-inline-block">
                                                                <img src="<?= $payment->participant_picture ?>" alt="<?= esc($payment->participant_name) ?>" class="avatar-xl rounded-circle img-thumbnail border-primary shadow">
                                                                <span class="badge rounded-pill bg-primary position-absolute bottom-0 end-0">
                                                                    <i class="ri-verified-badge-line"></i>
                                                                </span>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="position-relative d-inline-block">
                                                                <div class="avatar-xl mx-auto">
                                                                    <div class="avatar-title bg-soft-primary text-primary display-4 rounded-circle shadow border border-primary">
                                                                        <?= substr($payment->participant_name, 0, 1) ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <h4 class="fs-16 mt-3 mb-1 fw-semibold"><?= esc($payment->participant_name) ?></h4>
                                                        <p class="text-muted mb-3">
                                                            <i class="ri-mail-line me-1 align-middle"></i><?= esc($payment->participant_email) ?>
                                                        </p>

                                                        <div class="d-flex justify-content-center gap-2 mb-3">
                                                            <a href="mailto:<?= esc($payment->participant_email) ?>" class="btn btn-sm btn-soft-primary">
                                                                <i class="ri-mail-send-line me-1"></i>Email
                                                            </a>
                                                            <?php if (!empty($payment->phone_number)): ?>
                                                                <a href="tel:<?= esc($payment->phone_number) ?>" class="btn btn-sm btn-soft-info">
                                                                    <i class="ri-phone-line me-1"></i>Call
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="border-top pt-3">
                                                        <div class="row text-center">
                                                            <div class="col-6 border-end">
                                                                <div class="p-2">
                                                                    <h5 class="fs-15 mb-1">#<?= $payment->participant_id ?></h5>
                                                                    <p class="text-muted mb-0 fs-12">Participant ID</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="p-2">
                                                                    <h5 class="fs-15 mb-1"><?= !empty($payment->participant_phone) ? esc($payment->participant_phone) : 'N/A' ?></h5>
                                                                    <p class="text-muted mb-0 fs-12">Phone</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- Payment Method Info -->
                                        <div class="col-xxl-4 col-lg-6 mb-4">
                                            <div class="card h-100 border border-warning-subtle shadow-sm">
                                                <?php
                                                $paymentMethod = null;
                                                if (!empty($payment->payment_method_id)) {
                                                    $paymentMethodModel = new \App\Models\PaymentMethodModel();
                                                    $paymentMethod = $paymentMethodModel->getPaymentMethodById($payment->payment_method_id);
                                                }
                                                ?>
                                                <div class="card-header bg-warning-subtle border-bottom">
                                                    <h5 class="card-title mb-0 text-warning">
                                                        <i class="ri-bank-card-line me-2"></i>Payment Method
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <?php if ($paymentMethod): ?>
                                                        <div class="text-center mb-4">
                                                            <?php if (!empty($paymentMethod->img_url)): ?>
                                                                <div class="bg-light rounded p-3 d-inline-block mb-3">
                                                                    <img src="<?= $paymentMethod->img_url ?>" alt="<?= esc($paymentMethod->name) ?>" class="img-fluid" style="max-height: 70px; max-width: 200px;">
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="avatar-lg mx-auto mb-3">
                                                                    <div class="avatar-title bg-warning-subtle text-warning display-4 rounded-circle shadow">
                                                                        <i class="ri-bank-card-2-line"></i>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?> <h4 class="fs-16 fw-semibold mt-3 mb-1"><?= esc($paymentMethod->name) ?></h4>
                                                            <span class="badge bg-warning-subtle text-warning fs-12 mb-3"><?= ucfirst($paymentMethod->type ?? 'Standard') ?></span>
                                                        </div>

                                                        <?php if (!empty($paymentMethod->description)): ?>
                                                            <div class="alert bg-light border-warning border-opacity-25 mb-0">
                                                                <i class="ri-information-line me-2 align-middle text-warning"></i>
                                                                <?= esc($paymentMethod->description) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="text-center">
                                                            <div class="avatar-lg mx-auto">
                                                                <div class="avatar-title bg-soft-danger text-danger rounded-circle display-5">
                                                                    <i class="ri-question-mark"></i>
                                                                </div>
                                                            </div>
                                                            <h5 class="mt-3">Payment Method Unavailable</h5>
                                                            <p class="text-muted mb-0">Details for this payment method could not be found.</p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Program Payment Info -->
                                        <div class="col-xxl-4 col-lg-6 mb-4">
                                            <div class="card h-100 border border-success-subtle shadow-sm">
                                                <?php
                                                $programPayment = null;
                                                if (!empty($payment->program_payment_id)) {
                                                    $programPaymentModel = new \App\Models\ProgramPaymentModel();
                                                    $programPayment = $programPaymentModel->find($payment->program_payment_id);
                                                }
                                                ?>
                                                <div class="card-header bg-success-subtle border-bottom">
                                                    <h5 class="card-title mb-0 text-success">
                                                        <i class="ri-price-tag-3-line me-2"></i>Program Payment
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <?php if ($programPayment): ?>
                                                        <div class="text-center mb-4">
                                                            <div class="avatar-lg mx-auto mb-3">
                                                                <div class="avatar-title bg-success-subtle text-success rounded-circle display-4 shadow">
                                                                    <i class="ri-price-tag-3-line"></i>
                                                                </div>
                                                            </div>
                                                            <h4 class="fs-16 fw-semibold mt-2 mb-1"><?= esc($programPayment->name) ?></h4>
                                                            <span class="badge bg-success-subtle text-success fs-12 mb-3"><?= ucfirst($programPayment->category ?? 'Standard') ?></span>
                                                        </div>

                                                        <div class="table-responsive bg-light rounded p-3">
                                                            <table class="table table-sm table-borderless mb-0">
                                                                <tbody>
                                                                    <tr>
                                                                        <th scope="row" class="fs-14" style="width: 40%">
                                                                            <i class="ri-money-rupee-circle-line text-success me-1"></i> IDR Price:
                                                                        </th>
                                                                        <td class="text-end fw-medium">Rp <?= number_format($programPayment->idr_amount ?? 0, 0, ',', '.') ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row" class="fs-14">
                                                                            <i class="ri-money-dollar-circle-line text-success me-1"></i> USD Price:
                                                                        </th>
                                                                        <td class="text-end fw-medium">$ <?= number_format($programPayment->usd_amount ?? 0, 2, '.', ',') ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row" class="fs-14">
                                                                            <i class="ri-calendar-check-line text-success me-1"></i> Valid Period:
                                                                        </th>
                                                                        <td class="text-end fw-medium">
                                                                            <?= !empty($programPayment->start_date) ? format_date($programPayment->start_date, 'j M Y') : 'N/A' ?> -
                                                                            <?= !empty($programPayment->end_date) ? format_date($programPayment->end_date, 'j M Y') : 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <?php if (!empty($programPayment->description)): ?>
                                                            <div class="mt-3 border-top pt-3">
                                                                <h6 class="fs-14 mb-2 text-success">Payment Description:</h6>
                                                                <p class="text-muted fs-13 mb-0">
                                                                    <?= esc($programPayment->description) ?>
                                                                </p>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="text-center">
                                                            <div class="avatar-lg mx-auto">
                                                                <div class="avatar-title bg-soft-danger text-danger rounded-circle display-5">
                                                                    <i class="ri-question-mark"></i>
                                                                </div>
                                                            </div>
                                                            <h5 class="mt-3">Program Payment Unavailable</h5>
                                                            <p class="text-muted mb-0">Details for this program payment could not be found.</p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Proof and Notes Section -->
                                    <div class="row">
                                        <!-- Payment Proof -->
                                        <?php if (!empty($payment->proof_url) || !empty($payment->payment_proof)): ?>
                                            <div class="col-lg-6 mb-4">
                                                <div class="card border border-info-subtle shadow-sm h-100">
                                                    <div class="card-header bg-info-subtle border-bottom">
                                                        <h5 class="card-title mb-0 text-info">
                                                            <i class="ri-file-list-3-line me-2"></i>Payment Proof
                                                        </h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php
                                                        $proofUrl = !empty($payment->proof_url) ? $payment->proof_url : (!empty($payment->payment_proof) ? base_url('writable/uploads/payment_proofs/' . $payment->payment_proof) : '');

                                                        if (!empty($proofUrl)) {
                                                            $fileExt = pathinfo($proofUrl, PATHINFO_EXTENSION);
                                                            if (in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif'])):
                                                        ?>
                                                                <div class="text-center mb-4">
                                                                    <div class="position-relative">
                                                                        <a href="<?= $proofUrl ?>" class="image-popup d-block" title="Payment Proof">
                                                                            <img src="<?= $proofUrl ?>" alt="Payment Proof" class="img-fluid rounded shadow" style="max-height: 250px;">
                                                                            <div class="position-absolute top-50 start-50 translate-middle bg-dark bg-opacity-50 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; opacity: 0; transition: all 0.3s ease;">
                                                                                <i class="ri-zoom-in-line text-white fs-24"></i>
                                                                            </div>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex justify-content-center gap-2">
                                                                    <a href="<?= $proofUrl ?>" class="btn btn-info" target="_blank">
                                                                        <i class="ri-eye-line align-middle me-1"></i> View Full Image
                                                                    </a>
                                                                    <a href="<?= $proofUrl ?>" class="btn btn-soft-success" download>
                                                                        <i class="ri-download-2-line align-middle me-1"></i> Download
                                                                    </a>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="text-center mb-4">
                                                                    <div class="avatar-lg mx-auto mb-3">
                                                                        <div class="avatar-title bg-info-subtle text-info display-4 rounded shadow">
                                                                            <i class="ri-file-text-line"></i>
                                                                        </div>
                                                                    </div>
                                                                    <h4 class="fs-16 fw-semibold">Payment Proof Document</h4>
                                                                    <p class="text-muted mb-4">File format: <span class="badge bg-info-subtle text-info">.<?= strtoupper($fileExt) ?></span></p>
                                                                    <div class="hstack gap-3 justify-content-center">
                                                                        <a href="<?= $proofUrl ?>" class="btn btn-info" target="_blank">
                                                                            <i class="ri-eye-line align-middle me-1"></i> View Document
                                                                        </a>
                                                                        <a href="<?= $proofUrl ?>" class="btn btn-soft-success" download>
                                                                            <i class="ri-download-2-line align-middle me-1"></i> Download
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php } else { ?>
                                                            <div class="text-center">
                                                                <div class="avatar-lg mx-auto">
                                                                    <div class="avatar-title bg-soft-danger text-danger display-5 rounded-circle">
                                                                        <i class="ri-file-damage-line"></i>
                                                                    </div>
                                                                </div>
                                                                <h5 class="mt-3">No Payment Proof</h5>
                                                                <p class="text-muted mb-0">No payment proof has been uploaded for this transaction.</p>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Payment Notes -->
                                        <div class="<?= (!empty($payment->proof_url) || !empty($payment->payment_proof)) ? 'col-lg-6' : 'col-lg-12' ?> mb-4">
                                            <div class="card border border-purple-subtle shadow-sm h-100">
                                                <div class="card-header bg-purple-subtle border-bottom">
                                                    <h5 class="card-title mb-0 text-purple">
                                                        <i class="ri-sticky-note-line me-2"></i>Notes & Status History
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <?php if (!empty($payment->notes)): ?>
                                                        <div class="mb-4">
                                                            <h6 class="fw-semibold mb-3 text-purple">
                                                                <i class="ri-message-3-line me-1"></i> Transaction Notes:
                                                            </h6>
                                                            <div class="bg-light p-3 rounded-3 shadow-sm border-start border-purple border-3">
                                                                <div class="fs-14 fst-italic text-body-emphasis">
                                                                    <?= nl2br(esc($payment->notes)) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-light border-purple-subtle mb-4">
                                                            <i class="ri-information-line me-2 align-middle text-purple"></i>
                                                            No additional notes for this payment.
                                                        </div>
                                                    <?php endif; ?>

                                                    <div>
                                                        <h6 class="fw-semibold mb-3 text-purple">
                                                            <i class="ri-history-line me-1"></i> Status Timeline:
                                                        </h6>
                                                        <div class="profile-timeline">
                                                            <div class="accordion custom-accordionwithicon accordion-flush" id="accordionFlushExample">
                                                                <div class="accordion-item border-0">
                                                                    <div class="accordion-header" id="headingOne">
                                                                        <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#collapseOne" aria-expanded="true">
                                                                            <div class="d-flex align-items-center">
                                                                                <div class="avatar-sm">
                                                                                    <div class="avatar-title rounded-circle bg-purple-subtle text-purple">
                                                                                        <i class="ri-shopping-bag-line"></i>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="ms-3">
                                                                                    <h6 class="fs-14 mb-1 fw-semibold">Payment Created</h6>
                                                                                    <p class="text-muted fs-12 mb-0">
                                                                                        <i class="ri-calendar-event-line me-1"></i>
                                                                                        <?= format_date($payment->created_at, 'j M Y - H:i') ?>
                                                                                    </p>
                                                                                </div>
                                                                            </div>
                                                                        </a>
                                                                    </div>
                                                                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                                        <div class="accordion-body ms-2 ps-5 pt-0">
                                                                            <div class="bg-light p-2 rounded-3">
                                                                                <p class="text-dark mb-0 fs-13">
                                                                                    <i class="ri-information-line text-primary me-1"></i>
                                                                                    Payment record was created in the system.
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php if ($payment->status > 0): ?>
                                                                    <div class="accordion-item border-0">
                                                                        <div class="accordion-header" id="headingTwo">
                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#collapseTwo" aria-expanded="true">
                                                                                <div class="d-flex align-items-center">
                                                                                    <div class="avatar-sm">
                                                                                        <div class="avatar-title rounded-circle bg-soft-warning text-warning">
                                                                                            <i class="ri-time-line"></i>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="ms-3">
                                                                                        <h6 class="fs-14 mb-1 fw-semibold">Payment Status Updated</h6>
                                                                                        <p class="text-muted fs-12 mb-0">
                                                                                            <i class="ri-calendar-event-line me-1"></i>
                                                                                            <?= format_date($payment->updated_at, 'j M Y - H:i') ?>
                                                                                        </p>
                                                                                    </div>
                                                                                </div>
                                                                            </a>
                                                                        </div>
                                                                        <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                                                            <div class="accordion-body ms-2 ps-5 pt-0">
                                                                                <div class="bg-light p-2 rounded-3">
                                                                                    <p class="text-dark mb-0 fs-13">
                                                                                        <i class="ri-refresh-line text-warning me-1"></i>
                                                                                        Payment status updated to:
                                                                                        <span class="badge bg-<?= $payment->status == 2 ? 'success' : ($payment->status == 1 ? 'warning' : 'danger') ?> ms-1">
                                                                                            <?= $statusLabels[$payment->status] ?>
                                                                                        </span>
                                                                                    </p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div><!--end accordion-->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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

    <!-- Status Update Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">Update Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= site_url('payments/update-status/' . $payment->id) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="paymentStatus" class="form-label">Status</label>
                            <select class="form-select" id="paymentStatus" name="status" required>
                                <option value="0" <?= $payment->status == 0 ? 'selected' : '' ?>>Created</option>
                                <option value="1" <?= $payment->status == 1 ? 'selected' : '' ?>>Pending</option>
                                <option value="2" <?= $payment->status == 2 ? 'selected' : '' ?>>Success</option>
                                <option value="3" <?= $payment->status == 3 ? 'selected' : '' ?>>Cancelled</option>
                                <option value="4" <?= $payment->status == 4 ? 'selected' : '' ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="statusNotes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="statusNotes" name="notes" rows="3" placeholder="Add notes about this status change (optional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- App js -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
    
    <script>
    // Handle rejection reason requirement
    document.getElementById('paymentStatus').addEventListener('change', function() {
        const notesField = document.getElementById('statusNotes');
        const notesLabel = document.querySelector('label[for="statusNotes"]');
        
        if (this.value == '4') { // Rejected status
            notesField.setAttribute('required', 'required');
            notesLabel.innerHTML = 'Rejection Reason <span class="text-danger">*</span>';
            notesField.placeholder = 'Please provide a reason for rejecting this payment';
        } else {
            notesField.removeAttribute('required');
            notesLabel.innerHTML = 'Additional Notes';
            notesField.placeholder = 'Add notes about this status change (optional)';
        }
    });
    
    // Trigger change event on load to set initial state
    document.getElementById('paymentStatus').dispatchEvent(new Event('change'));
    </script>
</body>

</html>