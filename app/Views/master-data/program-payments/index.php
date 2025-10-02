<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title' => 'Program Payments')); ?>

    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <style>
        /* Modal loading overlay */
        .modal-loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 0.3rem;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .loading-text {
            margin-top: 1rem;
            color: #495057;
        }
    </style>

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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Program Payments')); ?> <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Payment Options List</h5>
                                    <div class="flex-shrink-0">
                                        <button class="btn btn-primary waves-effect waves-light me-2" data-bs-toggle="modal" data-bs-target="#add-payment-modal">
                                            <i class="ri-add-line align-middle me-1"></i> Add Payment Option
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="program-payments-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Payment Option</th>
                                                <th scope="col">Amount <button type="button" class="btn btn-sm btn-link text-info p-0 ms-1" data-bs-toggle="modal" data-bs-target="#amount-info-modal"><i class="ri-information-line"></i></button></th>
                                                <th scope="col">Current Active Period</th>
                                                <th scope="col">Last Active Period</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($programPayments ?? [])): ?>
                                                <?php foreach ($programPayments as $index => $payment): ?>
                                                    <?php
                                                    // Normalize payment option name based on category
                                                    $displayName = $payment->name ?? 'N/A';
                                                    $category = $payment->category ?? '';

                                                    $categoryName = strtolower($category);

                                                    if (strtolower($category) === 'registration') {
                                                        $categoryName = 'Registration Fee';
                                                    } elseif (in_array(strtolower($category), ['program_fee_1', 'program_fee_2'])) {
                                                        $categoryName = 'Program Fee';
                                                    }

                                                    $rate = $webSettings->usd_in_idr;
                                                    $amountInIdr = isset($payment->usd_amount) ? $payment->usd_amount * $rate : 'N/A';

                                                    $usdAmount = isset($payment->usd_amount) ? number_format($payment->usd_amount, 2, '.', ',') : 'N/A';

                                                    // categroy badge
                                                    $categoryBadge = '<span class="badge bg-primary">' . htmlspecialchars($categoryName) . '</span>';
                                                    ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td>
                                                            <?= $displayName ?><br>
                                                            <?= $categoryBadge ?>
                                                        </td>
                                                        <td>
                                                            <div class="fw-medium"><?= $usdAmount ? '$' . $usdAmount : 'N/A' ?></div>
                                                            <small class="text-muted d-block mt-1">
                                                                <span class="badge bg-light text-dark border">
                                                                    <i class="ri-exchange-line me-1"></i>Approx. Rp <?= $amountInIdr ? number_format($amountInIdr, 0, ',', '.') : 'N/A' ?>
                                                                </span>
                                                            </small>
                                                        </td>
                                                        <!-- Current Active Period Column -->
                                                        <td>
                                                            <?php
                                                            // Get current active period info
                                                            $currentTime = new DateTime();
                                                            $hasCurrentPeriod = false;
                                                            $currentPeriodData = null;
                                                            
                                                            if (isset($payment->start_date) && isset($payment->end_date)) {
                                                                $startDateTime = new DateTime($payment->start_date);
                                                                $endDateTime = new DateTime($payment->end_date);
                                                                $isCurrentlyActive = $currentTime >= $startDateTime && $currentTime <= $endDateTime;
                                                                
                                                                if ($isCurrentlyActive) {
                                                                    $hasCurrentPeriod = true;
                                                                    $currentPeriodData = [
                                                                        'name' => $payment->current_period_name ?? 'Unknown Period',
                                                                        'start_date' => $payment->start_date,
                                                                        'end_date' => $payment->end_date
                                                                    ];
                                                                }
                                                            }
                                                            ?>
                                                            
                                                            <?php if ($hasCurrentPeriod): ?>
                                                                <div class="fw-medium mb-1 text-success">
                                                                    <i class="ri-play-circle-line me-1"></i><?= htmlspecialchars($currentPeriodData['name']) ?>
                                                                </div>
                                                                <small class="text-muted d-block">
                                                                    <?php
                                                                    $startDateTime = new DateTime($currentPeriodData['start_date']);
                                                                    $endDateTime = new DateTime($currentPeriodData['end_date']);
                                                                    $isStartMidnight = $startDateTime->format('H:i:s') === '00:00:00';
                                                                    $isEndMidnight = $endDateTime->format('H:i:s') === '00:00:00';
                                                                    $isEndEndOfDay = $endDateTime->format('H:i:s') === '23:59:59';
                                                                    ?>
                                                                    <?= $isStartMidnight ? date('d M Y', strtotime($currentPeriodData['start_date'])) : date('d M Y g:i A', strtotime($currentPeriodData['start_date'])) ?>
                                                                    -
                                                                    <?= ($isEndMidnight || $isEndEndOfDay) ? date('d M Y', strtotime($currentPeriodData['end_date'])) : date('d M Y g:i A', strtotime($currentPeriodData['end_date'])) ?>
                                                                </small>
                                                                <span class="badge bg-success-subtle text-success mt-1">
                                                                    <i class="ri-time-line me-1"></i>Active Now
                                                                </span>
                                                            <?php else: ?>
                                                                <div class="text-center text-muted">
                                                                    <i class="ri-pause-circle-line fs-4 d-block mb-1"></i>
                                                                    <small>No Active Period</small>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>
                                                        
                                                        <!-- Last Active Period Column -->
                                                        <td>
                                                            <?php
                                                            // For now, we'll show the most recent period info or upcoming period
                                                            $hasLastPeriod = false;
                                                            $lastPeriodData = null;
                                                            $showUpcoming = false;
                                                            
                                                            if (isset($payment->start_date) && isset($payment->end_date)) {
                                                                $startDateTime = new DateTime($payment->start_date);
                                                                $endDateTime = new DateTime($payment->end_date);
                                                                $isCurrentlyActive = $currentTime >= $startDateTime && $currentTime <= $endDateTime;
                                                                $isUpcoming = $currentTime < $startDateTime;
                                                                $isEnded = $currentTime > $endDateTime;
                                                                
                                                                if (!$isCurrentlyActive) {
                                                                    $hasLastPeriod = true;
                                                                    $lastPeriodData = [
                                                                        'name' => $payment->current_period_name ?? 'Unknown Period',
                                                                        'start_date' => $payment->start_date,
                                                                        'end_date' => $payment->end_date,
                                                                        'is_upcoming' => $isUpcoming,
                                                                        'is_ended' => $isEnded
                                                                    ];
                                                                    if ($isUpcoming) {
                                                                        $showUpcoming = true;
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                            
                                                            <?php if ($hasLastPeriod): ?>
                                                                <div class="fw-medium mb-1 <?= $showUpcoming ? 'text-warning' : 'text-secondary' ?>">
                                                                    <i class="<?= $showUpcoming ? 'ri-calendar-schedule-line' : 'ri-stop-circle-line' ?> me-1"></i><?= htmlspecialchars($lastPeriodData['name']) ?>
                                                                </div>
                                                                <small class="text-muted d-block">
                                                                    <?php
                                                                    $startDateTime = new DateTime($lastPeriodData['start_date']);
                                                                    $endDateTime = new DateTime($lastPeriodData['end_date']);
                                                                    $isStartMidnight = $startDateTime->format('H:i:s') === '00:00:00';
                                                                    $isEndMidnight = $endDateTime->format('H:i:s') === '00:00:00';
                                                                    $isEndEndOfDay = $endDateTime->format('H:i:s') === '23:59:59';
                                                                    ?>
                                                                    <?= $isStartMidnight ? date('d M Y', strtotime($lastPeriodData['start_date'])) : date('d M Y g:i A', strtotime($lastPeriodData['start_date'])) ?>
                                                                    -
                                                                    <?= ($isEndMidnight || $isEndEndOfDay) ? date('d M Y', strtotime($lastPeriodData['end_date'])) : date('d M Y g:i A', strtotime($lastPeriodData['end_date'])) ?>
                                                                </small>
                                                                
                                                                <?php if ($showUpcoming): ?>
                                                                    <span class="badge bg-warning-subtle text-warning mt-1">
                                                                        <i class="ri-calendar-schedule-line me-1"></i>Upcoming
                                                                    </span>
                                                                    <?php 
                                                                    $daysUntilStart = $currentTime->diff($startDateTime)->days;
                                                                    if ($daysUntilStart <= 7): ?>
                                                                        <small class="text-muted d-block mt-1">Starts in <?= $daysUntilStart ?> day<?= $daysUntilStart != 1 ? 's' : '' ?></small>
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary-subtle text-secondary mt-1">
                                                                        <i class="ri-stop-circle-line me-1"></i>Ended
                                                                    </span>
                                                                    <?php 
                                                                    $daysSinceEnd = $endDateTime->diff($currentTime)->days;
                                                                    if ($daysSinceEnd <= 30): ?>
                                                                        <small class="text-muted d-block mt-1">Ended <?= $daysSinceEnd ?> day<?= $daysSinceEnd != 1 ? 's' : '' ?> ago</small>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <div class="text-center">
                                                                    <span class="text-muted">No Period Set</span>
                                                                    <br>
                                                                    <small class="text-info mt-1">
                                                                        <i class="ri-calendar-line me-1"></i>
                                                                        <a href="<?= base_url('master-data/program-payments/' . ($payment->id ?? 0) . '/periods') ?>" class="text-decoration-none">
                                                                            Configure Periods
                                                                        </a>
                                                                    </small>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $badge_class = "badge bg-secondary";
                                                            $status_text = "Inactive";

                                                            if (isset($payment->is_active) && $payment->is_active == 1) {
                                                                $badge_class = "badge bg-success";
                                                                $status_text = "Active";
                                                            }
                                                            ?>
                                                            <span class="<?= $badge_class ?>"><?= $status_text ?></span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <div class="view">
                                                                    <button class="btn btn-sm btn-info view-payment" data-id="<?= $payment->id ?? 0 ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                                                        <i class="ri-eye-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="periods">
                                                                    <a href="<?= base_url('master-data/program-payments/' . ($payment->id ?? 0) . '/periods') ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Manage Periods">
                                                                        <i class="ri-calendar-line"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="edit">
                                                                    <button class="btn btn-sm btn-success edit-payment" data-id="<?= $payment->id ?? 0 ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                                        <i class="ri-pencil-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="remove">
                                                                    <button class="btn btn-sm btn-danger delete-payment" data-id="<?= $payment->id ?? 0 ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                                        <i class="ri-delete-bin-fill"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No payments found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
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

    </div> <!-- END layout-wrapper -->

    <!-- Amount Info Modal -->
    <div class="modal fade" id="amount-info-modal" tabindex="-1" aria-labelledby="amount-info-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="amount-info-modal-label">Payment Amount Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <h6><i class="ri-information-line me-2"></i>Currency Conversion</h6>
                        <p class="mb-0">Payment amounts are displayed in both USD and IDR. The IDR amount is calculated based on the current exchange rate.</p>
                    </div>

                    <div class="mb-3">
                        <h6>Current Exchange Rate:</h6>
                        <p>1 USD = Rp <?= number_format($webSettings->usd_in_idr, 0, ',', '.') ?></p>
                    </div>

                    <div class="mb-3">
                        <h6>Important Notes:</h6>
                        <ul>
                            <li>Participants will use the IDR amount displayed here during payment processing.</li>
                            <li>Administrators need to regularly update the exchange rate to ensure accurate conversion.</li>
                            <li>The current exchange rate can be modified in the system settings.</li>
                            <li>All payments processed in IDR will use these fixed displayed amounts.</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal -->
    <div class="modal fade" id="add-payment-modal" tabindex="-1" aria-labelledby="add-payment-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-payment-modal-label">Add New Payment Option</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/program-payments/create" method="post" id="add-payment-form">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Option Name*</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <div class="invalid-feedback">Please enter an option name.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category*</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="registration">Registration Fee</option>
                                        <option value="program_fee_1">Program Fee 1</option>
                                        <option value="program_fee_2">Program Fee 2</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a category.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Funding Type*</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="all">All</option>
                                        <option value="self_funded">Self Funded</option>
                                        <option value="fully_funded">Fully Funded</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a funding type.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="usd_amount" class="form-label">USD Amount*</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="usd_amount" name="usd_amount" min="0" step="0.01" required>
                                        <div class="invalid-feedback">Please enter a valid USD amount.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Note:</strong> After creating this payment option, you can manage its availability periods by clicking the <strong>Manage Periods</strong> button.
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description*</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            <div class="invalid-feedback">Please provide a description.</div>
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status*</label>
                            <select class="form-select" id="is_active" name="is_active" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Payment Option</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Payment Modal -->
    <div class="modal fade" id="edit-payment-modal" tabindex="-1" aria-labelledby="edit-payment-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-loading" id="edit-loading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="loading-text">Loading payment details...</div>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="edit-payment-modal-label">Edit Payment Option</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/program-payments/update/" method="post" id="edit-payment-form">
                    <input type="hidden" id="edit_payment_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Option Name*</label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_category" class="form-label">Category</label>
                                    <select class="form-select" id="edit_category" name="category">
                                        <option value="registration">Registration Fee</option>
                                        <option value="program_fee_1">Program Fee 1</option>
                                        <option value="program_fee_2">Program Fee 2</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_type" class="form-label">Funding Type</label>
                                    <select class="form-select" id="edit_type" name="type">
                                        <option value="all">All</option>
                                        <option value="self_funded">Self Funded</option>
                                        <option value="fully_funded">Fully Funded</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_usd_amount" class="form-label">USD Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="edit_usd_amount" name="usd_amount" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Note:</strong> Availability periods are managed separately. Use the <strong>Manage Periods</strong> button to set when this payment option is available.
                        </div>

                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="edit_is_active" class="form-label">Status</label>
                            <select class="form-select" id="edit_is_active" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Payment Option</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Payment Modal -->
    <div class="modal fade" id="delete-payment-modal" tabindex="-1" aria-labelledby="delete-payment-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-payment-modal-label">Delete Payment Option</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this payment option?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirm-delete-btn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- View Payment Modal -->
    <div class="modal fade" id="view-payment-modal" tabindex="-1" aria-labelledby="view-payment-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-loading" id="view-loading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="loading-text">Loading payment details...</div>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="view-payment-modal-label">Payment Option Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Option Name</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_name"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Category</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_category"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Funding Type</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_type"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">USD Amount</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_usd_amount"></p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h5 class="text-muted fw-normal">Description</h5>
                        <p class="text-dark fw-medium fs-15 mb-3" id="view_description"></p>
                    </div>
                    <div class="mb-3">
                        <h5 class="text-muted fw-normal">Status</h5>
                        <p class="text-dark fw-medium fs-15 mb-3" id="view_status"></p>
                    </div>

                    <!-- Payment Periods Section -->
                    <div class="mb-3" id="view_periods_section" style="display: none;">
                        <hr class="my-4">
                        <h5 class="text-muted fw-normal">Payment Periods</h5>
                        <div id="view_periods_content">
                            <!-- Periods will be populated here via JavaScript -->
                        </div>
                    </div>

                    <!-- Current Period Status -->
                    <div class="mb-3" id="view_current_status_section" style="display: none;">
                        <hr class="my-4">
                        <h5 class="text-muted fw-normal">Current Status</h5>
                        <div id="view_current_status_content">
                            <!-- Current status will be populated here via JavaScript -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-warning view-manage-periods-btn">
                        <i class="ri-calendar-line me-1"></i> Manage Periods
                    </a>
                    <button type="button" class="btn btn-primary view-edit-btn">
                        <i class="ri-pencil-line me-1"></i> Edit
                    </button>
                </div>
            </div>
        </div>
    </div>


    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <script src="/assets/js/pages/datatables.init.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <!-- Custom JavaScript -->
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM loaded");

            // Check for flash messages
            <?php if (session()->has('success')): ?>
                Swal.fire({
                    title: 'Success!',
                    text: '<?= session('success') ?>',
                    icon: 'success',
                    confirmButtonColor: '#0ab39c'
                });
            <?php endif; ?>

            <?php if (session()->has('error')): ?>
                Swal.fire({
                    title: 'Error!',
                    text: '<?= session('error') ?>',
                    icon: 'error',
                    confirmButtonColor: '#f06548'
                });
            <?php endif; ?>

            // Ensure jQuery is loaded
            if (typeof jQuery !== 'undefined') {
                console.log("jQuery is loaded");
                initializePaymentFunctions();
            } else {
                console.error("jQuery is not loaded!");
            }
        });

        // Function to populate payment modal with server time synchronization
        function populatePaymentModal(data, payment, currentTime) {
            // Populate basic payment info
            $('#view_name').text(payment.name || 'N/A');

            // Format category for display
            var categoryDisplay = payment.category || 'N/A';
            if (categoryDisplay === 'registration') {
                categoryDisplay = 'Registration Fee';
            } else if (categoryDisplay === 'program_fee_1') {
                categoryDisplay = 'Program Fee 1';
            } else if (categoryDisplay === 'program_fee_2') {
                categoryDisplay = 'Program Fee 2';
            }
            $('#view_category').text(categoryDisplay);

            // Format funding type for display
            var typeDisplay = payment.type || 'All';
            if (typeDisplay === 'self_funded') {
                typeDisplay = 'Self Funded';
            } else if (typeDisplay === 'fully_funded') {
                typeDisplay = 'Fully Funded';
            }
            $('#view_type').text(typeDisplay);

            // Format currency values
            var usdAmount = payment.usd_amount ?
                '$ ' + Number(payment.usd_amount).toFixed(2) : 'N/A';
            $('#view_usd_amount').text(usdAmount);

            $('#view_description').text(payment.description || 'No description provided');

            // Format status with badge
            var statusBadge = payment.is_active == 1 ?
                '<span class="badge bg-success">Active</span>' :
                '<span class="badge bg-secondary">Inactive</span>';
            $('#view_status').html(statusBadge);

            // Handle periods data if available - using server time for accuracy
            if (data.periods && data.periods.length > 0) {
                $('#view_periods_section').show();
                
                // Sort periods by end date (most recent first) to find the latest period
                var sortedPeriods = data.periods.slice().sort(function(a, b) {
                    return new Date(b.end_date) - new Date(a.end_date);
                });
                
                // Find the latest/most relevant period
                var latestPeriod = sortedPeriods[0];
                
                var periodsHtml = '';
                
                // Show latest period prominently
                if (latestPeriod) {
                    var latestStartDate = new Date(latestPeriod.start_date);
                    var latestEndDate = new Date(latestPeriod.end_date);
                    
                    var latestFormattedStart = latestStartDate.toLocaleDateString('en-US', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
                    });
                    
                    var latestFormattedEnd = latestEndDate.toLocaleDateString('en-US', {
                        day: 'numeric', 
                        month: 'short',
                        year: 'numeric'
                    });
                    
                    // Determine latest period status using server time
                    var latestPeriodStatus = '';
                    var alertClass = 'alert-secondary';
                    if (!latestPeriod.is_active) {
                        latestPeriodStatus = 'Inactive';
                        alertClass = 'alert-secondary';
                    } else if (currentTime >= latestStartDate && currentTime <= latestEndDate) {
                        latestPeriodStatus = 'Currently Active';
                        alertClass = 'alert-success';
                    } else if (currentTime < latestStartDate) {
                        latestPeriodStatus = 'Upcoming';
                        alertClass = 'alert-warning';
                    } else {
                        latestPeriodStatus = 'Ended';
                        alertClass = 'alert-info';
                    }
                    
                    periodsHtml += '<div class="alert ' + alertClass + ' mb-3">';
                    periodsHtml += '<div class="d-flex justify-content-between align-items-start">';
                    periodsHtml += '<div>';
                    periodsHtml += '<h6 class="alert-heading mb-1"><i class="ri-calendar-check-line me-2"></i>Latest Period</h6>';
                    periodsHtml += '<strong>' + (latestPeriod.name || 'N/A') + '</strong>';
                    if (latestPeriod.description) {
                        periodsHtml += '<br><small>' + latestPeriod.description + '</small>';
                    }
                    periodsHtml += '<br><small class="fw-medium">' + latestFormattedStart + ' - ' + latestFormattedEnd + '</small>';
                    periodsHtml += '</div>';
                    periodsHtml += '<span class="badge ' + (latestPeriodStatus === 'Currently Active' ? 'bg-success' : 
                                                             latestPeriodStatus === 'Upcoming' ? 'bg-warning' :
                                                             latestPeriodStatus === 'Ended' ? 'bg-light text-dark' : 'bg-secondary') + '">' + latestPeriodStatus + '</span>';
                    periodsHtml += '</div>';
                    periodsHtml += '</div>';
                }
                
                periodsHtml += '<div class="mb-3">';
                periodsHtml += '<h6 class="text-muted fw-normal mb-2">All Periods (' + data.total_periods + ' total)</h6>';
                periodsHtml += '</div>';

                // Display all periods in a table
                periodsHtml += '<div class="table-responsive">';
                periodsHtml += '<table class="table table-sm table-bordered">';
                periodsHtml += '<thead class="table-light">';
                periodsHtml += '<tr>';
                periodsHtml += '<th>Period Name</th>';
                periodsHtml += '<th>Start Date</th>';
                periodsHtml += '<th>End Date</th>';
                periodsHtml += '<th>Status</th>';
                periodsHtml += '</tr>';
                periodsHtml += '</thead>';
                periodsHtml += '<tbody>';

                // Display periods sorted by end date (most recent first)
                sortedPeriods.forEach(function(period, index) {
                    var startDate = new Date(period.start_date);
                    var endDate = new Date(period.end_date);
                    
                    // Format dates
                    var formattedStart = startDate.toLocaleDateString('en-US', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
                    });
                    
                    var formattedEnd = endDate.toLocaleDateString('en-US', {
                        day: 'numeric', 
                        month: 'short',
                        year: 'numeric'
                    });
                    
                    // Determine period status using server time
                    var periodStatus = '';
                    if (!period.is_active) {
                        periodStatus = '<span class="badge bg-secondary">Inactive</span>';
                    } else if (currentTime >= startDate && currentTime <= endDate) {
                        periodStatus = '<span class="badge bg-success">Currently Active</span>';
                    } else if (currentTime < startDate) {
                        periodStatus = '<span class="badge bg-warning">Upcoming</span>';
                    } else {
                        periodStatus = '<span class="badge bg-light text-dark">Ended</span>';
                    }
                    
                    // Highlight the latest period (first in sorted list)
                    var rowClass = index === 0 ? 'table-primary' : '';
                    
                    periodsHtml += '<tr class="' + rowClass + '">';
                    periodsHtml += '<td>';
                    if (index === 0) {
                        periodsHtml += '<i class="ri-star-fill text-warning me-1" title="Latest Period"></i>';
                    }
                    periodsHtml += '<strong>' + (period.name || 'N/A') + '</strong>';
                    if (period.description) {
                        periodsHtml += '<br><small class="text-muted">' + period.description + '</small>';
                    }
                    periodsHtml += '</td>';
                    periodsHtml += '<td>' + formattedStart + '</td>';
                    periodsHtml += '<td>' + formattedEnd + '</td>';
                    periodsHtml += '<td>' + periodStatus + '</td>';
                    periodsHtml += '</tr>';
                });

                periodsHtml += '</tbody>';
                periodsHtml += '</table>';
                periodsHtml += '</div>';

                $('#view_periods_content').html(periodsHtml);
            } else {
                $('#view_periods_section').hide();
            }

            // Handle current status - show more comprehensive status information
            if (data.current_period || data.upcoming_period || (data.periods && data.periods.length > 0)) {
                $('#view_current_status_section').show();
                var statusHtml = '';
                
                if (data.current_period) {
                    statusHtml += '<div class="alert alert-success">';
                    statusHtml += '<i class="ri-time-line me-2"></i>';
                    statusHtml += '<strong>Currently Active Period:</strong> ' + data.current_period.name;
                    statusHtml += '<br><small>Ends on ' + new Date(data.current_period.end_date).toLocaleDateString('en-US', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    }) + '</small>';
                    statusHtml += '</div>';
                } else if (data.upcoming_period) {
                    statusHtml += '<div class="alert alert-warning">';
                    statusHtml += '<i class="ri-calendar-line me-2"></i>';
                    statusHtml += '<strong>Next Period:</strong> ' + data.upcoming_period.name;
                    statusHtml += '<br><small>Starts on ' + new Date(data.upcoming_period.start_date).toLocaleDateString('en-US', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    }) + '</small>';
                    statusHtml += '</div>';
                } else if (data.periods && data.periods.length > 0) {
                    // If no current or upcoming periods, show info about the latest ended period
                    var sortedPeriods = data.periods.slice().sort(function(a, b) {
                        return new Date(b.end_date) - new Date(a.end_date);
                    });
                    var latestPeriod = sortedPeriods[0];
                    
                    statusHtml += '<div class="alert alert-secondary">';
                    statusHtml += '<i class="ri-information-line me-2"></i>';
                    statusHtml += '<strong>Latest Period:</strong> ' + latestPeriod.name + ' (Ended)';
                    statusHtml += '<br><small>Ended on ' + new Date(latestPeriod.end_date).toLocaleDateString('en-US', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    }) + '</small>';
                    statusHtml += '<br><small class="text-muted">No active or upcoming periods configured.</small>';
                    statusHtml += '</div>';
                } else {
                    statusHtml += '<div class="alert alert-info">';
                    statusHtml += '<i class="ri-information-line me-2"></i>';
                    statusHtml += 'No periods configured for this payment option.';
                    statusHtml += '</div>';
                }
                
                $('#view_current_status_content').html(statusHtml);
            } else {
                $('#view_current_status_section').hide();
            }

            // Set payment ID for buttons in view modal
            $('.view-edit-btn').data('id', payment.id);
            $('.view-manage-periods-btn').data('id', payment.id);
        }

        function initializePaymentFunctions() {
            // Initialize DataTable
            var paymentTable = $('#program-payments-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true,
                columnDefs: [{
                    orderable: false,
                    targets: [5] // Action column is not sortable
                }],
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-squared justify-content-end mb-0");
                    // Initialize tooltips
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });
                }
            });

            // Debug click events
            console.log("Setting up click handlers");

            // Log all view buttons for debugging
            var viewButtons = document.querySelectorAll('.view-payment');
            console.log("View buttons found:", viewButtons.length);

            // Test direct DOM event listener for view buttons
            document.querySelectorAll('.view-payment').forEach(function(button) {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    console.log("View button clicked via DOM listener, ID:", id);
                });
            });

            // Use simplified event delegation for view button
            $(document).on('click', '.view-payment', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var paymentId = $(this).data('id');
                console.log("View button clicked for ID:", paymentId);

                // Show modal first
                $('#view-payment-modal').modal('show');
                $('#view-loading').show();

                // Get payment details
                $.ajax({
                    url: '/master-data/program-payments/getPaymentOption/' + paymentId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("View Ajax response:", response);
                        if (response && response.success) {
                            var data = response.data;
                            var payment = data.payment || data; // Handle both new and old response format

                            // Get server time for accurate period status
                            $.get('/master-data/program-payments/getCurrentServerTime')
                                .done(function(timeResponse) {
                                    var serverTime = timeResponse.success ? new Date(timeResponse.data.iso_format) : new Date();
                                    populatePaymentModal(data, payment, serverTime);
                                })
                                .fail(function() {
                                    console.warn('Failed to get server time, using client time');
                                    populatePaymentModal(data, payment, new Date());
                                });
                        } else {
                            console.error("Invalid response:", response);
                            alert('Failed to load payment option details');
                        }

                        // Hide loading spinner
                        $('#view-loading').hide();
                    },
                    error: function(xhr, status, error) {
                        console.error("View Ajax error:", xhr.responseText);
                        alert('An error occurred while fetching payment option details');
                        $('#view-loading').hide();
                    }
                });
            });

            $(document).on('click', '.edit-payment', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var paymentId = $(this).data('id');
                console.log("Edit button clicked for ID:", paymentId);

                // Show modal first
                $('#edit-payment-modal').modal('show');
                $('#edit-loading').show();

                // Get payment details
                $.ajax({
                    url: '/master-data/program-payments/getPaymentOption/' + paymentId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Edit Ajax response:", response);

                        if (response && response.success) {
                            var data = response.data;
                            var payment = data.payment || data; // Handle both new and old response format

                            // Set form action
                            $('#edit-payment-form').attr('action', '/master-data/program-payments/update/' + payment.id);
                            
                            // Populate form
                            $('#edit_payment_id').val(payment.id);
                            $('#edit_name').val(payment.name);
                            $('#edit_category').val(payment.category);
                            $('#edit_type').val(payment.type || 'all');
                            $('#edit_usd_amount').val(payment.usd_amount);

                            // Note: Date management has been moved to periods functionality

                            $('#edit_description').val(payment.description);
                            $('#edit_is_active').val(payment.is_active);
                        } else {
                            console.error("Invalid response:", response);
                            alert('Failed to load payment option details');
                        }

                        // Hide loading spinner
                        $('#edit-loading').hide();
                    },
                    error: function(xhr, status, error) {
                        console.error("Edit Ajax error:", xhr.responseText);
                        alert('An error occurred while fetching payment option details');
                        $('#edit-loading').hide();
                    }
                });
            });

            // Handle delete button click with event delegation
            $(document).on('click', '.delete-payment', function(e) {
                e.preventDefault();
                var paymentId = $(this).data('id');
                console.log("Delete button clicked for ID:", paymentId);

                // Set delete URL
                $('#confirm-delete-btn').attr('href', '/master-data/program-payments/delete/' + paymentId);

                // Show modal
                $('#delete-payment-modal').modal('show');
            });

            // Handle click on edit button in view modal
            $(document).on('click', '.view-edit-btn', function() {
                var paymentId = $(this).data('id');
                console.log("Edit button clicked from view modal for ID:", paymentId);

                // Close view modal
                $('#view-payment-modal').modal('hide');

                // Trigger edit click after a small delay to let the first modal close
                setTimeout(function() {
                    $('.edit-payment[data-id="' + paymentId + '"]').trigger('click');
                }, 500);
            });

            // Handle click on manage periods button in view modal
            $(document).on('click', '.view-manage-periods-btn', function() {
                var paymentId = $(this).data('id');
                console.log("Manage periods button clicked from view modal for ID:", paymentId);

                // Navigate to periods page
                window.location.href = '/master-data/program-payments/' + paymentId + '/periods';
            });

            // Form validation for add payment form
            $('#add-payment-form').on('submit', function(e) {
                if ($(this)[0].checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Show SweetAlert for validation errors
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                }
                $(this).addClass('was-validated');
            });

            // Form validation for edit payment form
            $('#edit-payment-form').on('submit', function(e) {
                if ($(this)[0].checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Show SweetAlert for validation errors
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                }
                $(this).addClass('was-validated');
            });
        }
    </script>
</body>

</html>