<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title' => 'Program Payments')); ?>    <!-- DataTables css -->
    <link href="/assets/libs/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/libs/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <?= $this->include('partials/head-css') ?>

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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Program Payments')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Payment Options Configuration</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center mb-4">
                                        <div class="col-md-6">
                                            <div class="d-flex flex-wrap gap-2">
                                                <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" data-bs-target="#add-payment-modal">
                                                    <i class="ri-add-line align-bottom me-1"></i> Add Payment Option
                                                </button>
                                                <button type="button" class="btn btn-info export-btn">
                                                    <i class="ri-file-download-line align-bottom me-1"></i> Export
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-md-end">
                                                <div class="search-box ms-2">
                                                    <input type="text" class="form-control search" placeholder="Search...">
                                                    <i class="ri-search-line search-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="program-payments-table" class="table align-middle table-nowrap table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col" style="width: 50px;">#</th>
                                                    <th scope="col">Payment Option</th>
                                                    <th scope="col">Amount <button type="button" class="btn btn-sm btn-link text-info p-0 ms-1" data-bs-toggle="modal" data-bs-target="#amount-info-modal"><i class="ri-information-line"></i></button></th>
                                                    <th scope="col">Valid Period</th>
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
                                                            <td>
                                                                <?= isset($payment->start_date) ? date('d M Y', strtotime($payment->start_date)) : 'N/A' ?> -
                                                                <?= isset($payment->end_date) ? date('d M Y', strtotime($payment->end_date)) : 'N/A' ?>
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
                                                        <td colspan="8" class="text-center">No payments found</td>
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

    <?= $this->include('partials/customizer') ?>

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- jQuery first (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables js -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

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
                    <div class="modal-body">                        <div class="row">                            <div class="col-md-6">
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

                        <div class="row">                            <div class="col-md-6">
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

                        <div class="row">                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date*</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                    <div class="invalid-feedback">Please select a start date.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date*</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                    <div class="invalid-feedback">Please select an end date.</div>
                                </div>
                            </div>
                        </div>                        <div class="mb-3">
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
    </div> <!-- Edit Payment Modal -->
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
                    <div class="modal-body">                        <div class="row">
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="edit_start_date" name="start_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="edit_end_date" name="end_date">
                                </div>
                            </div>
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
    </div> <!-- View Payment Modal -->
    <div class="modal fade" id="view-payment-modal" tabindex="-1" aria-labelledby="view-payment-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
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
                <div class="modal-body">                    <div class="row">
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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Valid From</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_start_date"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted fw-normal">Valid Until</h5>
                                <p class="text-dark fw-medium fs-15 mb-3" id="view_end_date"></p>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary view-edit-btn">Edit</button>
                </div>
            </div>
        </div>
    </div> 
    
    <!-- DataTables js -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <!-- Custom JavaScript -->    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM loaded");

            // Check for flash messages
            <?php if(session()->has('success')): ?>
                Swal.fire({
                    title: 'Success!',
                    text: '<?= session('success') ?>',
                    icon: 'success',
                    confirmButtonColor: '#0ab39c'
                });
            <?php endif; ?>

            <?php if(session()->has('error')): ?>
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

        function initializePaymentFunctions() { // Initialize DataTable
            var paymentTable = $('#program-payments-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true,
                columnDefs: [{
                    orderable: false,
                    targets: [5] // Action column is not sortable (adjusted to match column index)
                }],
                language: {
                    search: "",
                    searchPlaceholder: "Search...",
                    emptyTable: "No payment options found"
                }
            });

            // Connect search box
            $('.search').keyup(function() {
                paymentTable.search($(this).val()).draw();
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
                        console.log("View Ajax response:", response);                        if (response && response.success) {
                            var payment = response.data;
                            
                            // Populate modal
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
                            $('#view_usd_amount').text(usdAmount);

                            // Format dates
                            var startDate = payment.start_date ?
                                new Date(payment.start_date).toLocaleDateString('en-US', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric'
                                }) : 'N/A';
                            var endDate = payment.end_date ?
                                new Date(payment.end_date).toLocaleDateString('en-US', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric'
                                }) : 'N/A';

                            $('#view_start_date').text(startDate);
                            $('#view_end_date').text(endDate);
                            $('#view_description').text(payment.description || 'No description provided');

                            // Format status with badge
                            var statusBadge = payment.is_active == 1 ?
                                '<span class="badge bg-success">Active</span>' :
                                '<span class="badge bg-secondary">Inactive</span>';
                            $('#view_status').html(statusBadge);

                            // Set payment ID for the edit button in view modal
                            $('.view-edit-btn').data('id', payment.id);
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
                            var payment = response.data;

                            // Set form action
                            $('#edit-payment-form').attr('action', '/master-data/program-payments/update/' + payment.id);                            // Populate form
                            $('#edit_payment_id').val(payment.id);
                            $('#edit_name').val(payment.name);
                            $('#edit_category').val(payment.category);
                            $('#edit_type').val(payment.type || 'all');
                            $('#edit_usd_amount').val(payment.usd_amount);
                            
                            // Format dates for date input (yyyy-mm-dd)
                            if (payment.start_date) {
                                var startDate = new Date(payment.start_date);
                                var formattedStartDate = startDate.getFullYear() + '-' + 
                                    String(startDate.getMonth() + 1).padStart(2, '0') + '-' + 
                                    String(startDate.getDate()).padStart(2, '0');
                                $('#edit_start_date').val(formattedStartDate);
                            }
                            
                            if (payment.end_date) {
                                var endDate = new Date(payment.end_date);
                                var formattedEndDate = endDate.getFullYear() + '-' + 
                                    String(endDate.getMonth() + 1).padStart(2, '0') + '-' + 
                                    String(endDate.getDate()).padStart(2, '0');
                                $('#edit_end_date').val(formattedEndDate);
                            }
                            
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
            });            // Form validation for add payment form
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