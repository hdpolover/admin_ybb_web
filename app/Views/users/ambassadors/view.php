<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Ambassador Details')); ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />

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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Ambassadors', 'title' => 'Ambassador Details')); ?>

                    <div class="row">
                        <!-- Ambassador Profile -->
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title flex-grow-1 mb-0">Ambassador Profile</h5>
                                        <div class="flex-shrink-0">
                                            <a href="<?= site_url('ambassadors/edit/' . $ambassador->id) ?>" class="btn btn-soft-primary btn-sm">
                                                <i class="ri-edit-box-line align-middle me-1"></i> Edit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-4">
                                        <div class="avatar-lg mx-auto mb-4">
                                            <div class="avatar-title bg-primary-subtle text-primary display-5 rounded-circle">
                                                <?= strtoupper(substr($ambassador->name ?? '', 0, 1)) ?>
                                            </div>
                                        </div>
                                        <h5 class="fs-16 mb-1"><?= esc($ambassador->name) ?></h5>
                                        <p class="text-muted mb-0">Ambassador</p>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-borderless mb-0">
                                            <tbody>
                                                <tr>
                                                    <th class="ps-0" scope="row">Email:</th>
                                                    <td class="text-muted"><?= esc($ambassador->email) ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Phone:</th>
                                                    <td class="text-muted"><?= esc($ambassador->phone ?? 'N/A') ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Institution:</th>
                                                    <td class="text-muted"><?= esc($ambassador->institution ?? 'N/A') ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Referral Code:</th>
                                                    <td>
                                                        <span class="badge bg-info-subtle text-info fs-12"><?= esc($ambassador->ref_code) ?></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Status:</th>
                                                    <td>
                                                        <?php
                                                        $statusClass = [
                                                            '1' => 'bg-success-subtle text-success',
                                                            '0' => 'bg-danger-subtle text-danger',
                                                            '2' => 'bg-warning-subtle text-warning'
                                                        ];
                                                        $statusText = [
                                                            '1' => 'Active',
                                                            '0' => 'Inactive',
                                                            '2' => 'Suspended'
                                                        ];
                                                        $status = $ambassador->is_active ?? '0';
                                                        ?>
                                                        <span class="badge <?= $statusClass[$status] ?? 'bg-secondary-subtle text-secondary' ?>">
                                                            <?= $statusText[$status] ?? 'Unknown' ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Joined Date:</th>
                                                    <td class="text-muted"><?= date('d M Y', strtotime($ambassador->created_at)) ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Ambassador Profile -->

                        <!-- Referral Stats -->
                        <div class="col-xl-8">
                            <!-- Ambassador Referral Link -->
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title flex-grow-1 mb-0">Ambassador Referral Link</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Shareable Registration Link</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="referral-link" value="<?= $generated_url ?>" readonly>
                                            <button class="btn btn-primary" type="button" id="copy-link"><i class="ri-file-copy-line me-1"></i> Copy</button>
                                        </div>
                                        <div class="form-text text-muted mt-2">Share this link with potential participants to track referrals.</div>
                                    </div>

                                    <div class="mt-3">
                                        <h6>Referral QR Code</h6>
                                        <p class="text-muted">Scan this QR code to visit the registration page with this ambassador's referral code.</p>
                                        <div class="text-center">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?data=<?= urlencode($generated_url) ?>&amp;size=150x150" class="img-fluid border p-2 rounded" alt="QR Code">
                                        </div>
                                        <div class="text-center mt-2">
                                            <a href="https://api.qrserver.com/v1/create-qr-code/?data=<?= urlencode($generated_url) ?>&amp;size=300x300" download="ambassador-<?= $ambassador->id ?>-qrcode.png" class="btn btn-sm btn-soft-info">
                                                <i class="ri-download-2-line align-middle me-1"></i> Download QR Code
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Referral Stats -->
                    </div>

                    <div class="row">
                        <!-- Referral Stats -->
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title flex-grow-1 mb-0">Referral Statistics</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <h4 class="fs-22 fw-semibold"><?= $referralCounts['total'] ?></h4>
                                                <p class="text-muted mb-0">Total Referrals</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <h4 class="fs-22 fw-semibold"><?= $referralCounts['new'] ?></h4>
                                                <p class="text-muted mb-0">New System Referrals</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <h4 class="fs-22 fw-semibold"><?= $referralCounts['legacy'] ?></h4>
                                                <p class="text-muted mb-0">Legacy Referrals</p>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($referralCounts['total'] > 0): ?>
                                        <!-- Progress Bar Visualization -->
                                        <div class="mt-4">
                                            <h6 class="text-muted text-uppercase fw-semibold mb-3">Referral Sources</h6>
                                            <div class="mb-3">
                                                <div class="d-flex mb-2">
                                                    <div class="flex-grow-1">
                                                        <p class="text-muted mb-0">New System</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <p class="mb-0"><?= number_format(($referralCounts['new'] / $referralCounts['total']) * 100, 1) ?>%</p>
                                                    </div>
                                                </div>
                                                <div class="progress progress-sm" style="height: 8px;">
                                                    <div class="progress-bar bg-primary" role="progressbar"
                                                        style="width: <?= ($referralCounts['new'] / $referralCounts['total']) * 100 ?>%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="d-flex mb-2">
                                                    <div class="flex-grow-1">
                                                        <p class="text-muted mb-0">Legacy System</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <p class="mb-0"><?= number_format(($referralCounts['legacy'] / $referralCounts['total']) * 100, 1) ?>%</p>
                                                    </div>
                                                </div>
                                                <div class="progress progress-sm" style="height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: <?= ($referralCounts['legacy'] / $referralCounts['total']) * 100 ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Participant Referrals List -->
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title flex-grow-1 mb-0">Referred Participants</h5>
                                        <div class="flex-shrink-0">
                                            <button type="button" class="btn btn-sm btn-soft-secondary" id="refresh-referrals">
                                                <i class="ri-refresh-line align-middle"></i> Refresh
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($referrals)): ?>
                                        <div class="text-center py-4">
                                            <div class="avatar-md mx-auto mb-4">
                                                <div class="avatar-title bg-light rounded-circle text-primary display-6">
                                                    <i class="ri-user-search-line"></i>
                                                </div>
                                            </div>
                                            <h5>No referrals found</h5>
                                            <p class="text-muted">This ambassador has not referred any participants yet.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table id="referrals-datatable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th scope="col">Participant</th>
                                                        <th scope="col">Registration Date</th>
                                                        <th scope="col">Payment Status</th>
                                                        <th scope="col">Referral Type</th>
                                                        <th scope="col">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($referrals as $referral): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar-xs me-2">
                                                                        <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                                            <?= strtoupper(substr($referral->full_name ?? '', 0, 1)) ?>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <h5 class="fs-14 mb-0"><?= esc($referral->full_name) ?></h5>
                                                                        <p class="text-muted mb-0"><?= esc($referral->email ?? '') ?></p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><?= date('d M Y', strtotime($referral->created_at)) ?></td>
                                                            <td>
                                                                <?php
                                                                $paymentStatus = isset($referral->payment_status) ? $referral->payment_status : null;
                                                                if ($paymentStatus === '2') { // Success
                                                                    echo '<span class="badge bg-success">Paid</span>';
                                                                } elseif ($paymentStatus === '1') { // Pending
                                                                    echo '<span class="badge bg-warning">Pending Payment</span>';
                                                                } else {
                                                                    echo '<span class="badge bg-danger">Unpaid</span>';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php if (isset($referral->referral_type) && $referral->referral_type === 'legacy'): ?>
                                                                    <span class="badge bg-success-subtle text-success">Legacy</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-primary-subtle text-primary">New</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <div class="dropdown d-inline-block">
                                                                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <i class="ri-more-fill align-middle"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-end">                                                                        <li>
                                                                            <a href="<?= base_url('users/participants/view/' . $referral->participant_id) ?>" class="dropdown-item">
                                                                                <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View Details
                                                                            </a>
                                                                        </li>
                                                                        <?php if (isset($referral->payment_id)): ?>
                                                                            <li>
                                                                                <a href="<?= base_url('payments/view/' . $referral->payment_id) ?>" class="dropdown-item">
                                                                                    <i class="ri-money-dollar-circle-line align-bottom me-2 text-muted"></i> View Payment
                                                                                </a>
                                                                            </li>
                                                                        <?php endif; ?>
                                                                    </ul>
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
                        <!-- End Referral Stats -->
                    </div>
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper --> <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable for referrals
            if (document.getElementById('referrals-datatable')) {
                var referralsTable = $('#referrals-datatable').DataTable({
                    responsive: true,
                    searching: true,
                    paging: true,
                    info: true,
                    language: {
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        },
                        info: "Showing referrals _START_ to _END_ of _TOTAL_",
                        search: "Search:"
                    },
                    drawCallback: function() {
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    }
                });

                // Refresh button
                document.getElementById('refresh-referrals').addEventListener('click', function() {
                    window.location.reload();
                });
            }

            // Copy referral link functionality
            document.getElementById('copy-link').addEventListener('click', function() {
                var referralLink = document.getElementById('referral-link');
                referralLink.select();
                referralLink.setSelectionRange(0, 99999); // For mobile devices

                try {
                    var successful = document.execCommand('copy');
                    if (successful) {
                        // Show success message
                        this.innerHTML = '<i class="ri-check-line me-1"></i> Copied!';
                        this.classList.remove('btn-primary');
                        this.classList.add('btn-success');

                        // Reset after 2 seconds
                        setTimeout(() => {
                            this.innerHTML = '<i class="ri-file-copy-line me-1"></i> Copy';
                            this.classList.remove('btn-success');
                            this.classList.add('btn-primary');
                        }, 2000);
                    } else {
                        alert('Failed to copy link');
                    }
                } catch (err) {
                    console.error('Failed to copy: ', err);
                    alert('Failed to copy link: ' + err);
                }
            });
        });
    </script>
</body>

</html>