<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Main Configuration')); ?>
    <?= $this->include('partials/head-css') ?> <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
    <style>
        .form-switch .form-check-input {
            width: 3rem;
            height: 1.5rem;
            cursor: pointer;
        }

        .form-switch .form-check-input:checked {
            background-color: #0ab39c;
            border-color: #0ab39c;
        }

        .card {
            transition: all 0.3s ease;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .settings-container .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .settings-icon {
            font-size: 1.5rem;
            margin-right: 0.5rem;
        }

        .settings-label {
            font-weight: 500;
        }

        .bg-gray-soft {
            background-color: #e9ecef;
        }

        .text-gray {
            color: #6c757d;
        }

        .settings-header {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 0.25rem;
            background-color: #f8f9fa;
            border-left: 4px solid #405189;
        }

        .settings-header h4 {
            margin-bottom: 0.25rem;
        }

        .settings-header p {
            margin-bottom: 0;
            color: #6c757d;
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Settings', 'title' => 'Main Configuration')); ?>
                    <!-- Settings Header -->
                    <div class="settings-header">
                        <h4><i class="ri-settings-4-line me-2"></i>Configuration Dashboard</h4>
                        <p>Manage your program settings and system configurations from one central location.</p>
                    </div>

                    <!-- Program Info Card -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-soft-info border-0">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="ri-information-line text-info fs-24 p-1"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="card-title mb-1">Currently Configuring: <?= htmlspecialchars($program->name ?? 'Program') ?></h5>
                                            <p class="card-text mb-0">
                                                Status: <span class="badge <?= (isset($program->is_active) && $program->is_active == 1) ? 'bg-success' : 'bg-danger' ?>"><?= (isset($program->is_active) && $program->is_active == 1) ? 'Active' : 'Inactive' ?></span>
                                                Registration: <span class="badge <?= (isset($program->is_registration_open) && $program->is_registration_open == 1) ? 'bg-success' : 'bg-danger' ?>"><?= (isset($program->is_registration_open) && $program->is_registration_open == 1) ? 'Open' : 'Closed' ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flash Messages --><?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-checkbox-circle-line me-3 align-middle"></i> <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-3 align-middle"></i> <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- SweetAlert Flash Messages -->
                    <?php if (session()->getFlashdata('swalSuccess')): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    title: '<strong>Success!</strong>',
                                    html: '<div class="text-success"><i class="ri-check-double-line fs-5 me-2"></i><?= session()->getFlashdata('swalSuccess') ?></div>',
                                    icon: 'success',
                                    confirmButtonColor: '#0ab39c',
                                    customClass: {
                                        confirmButton: 'btn btn-success'
                                    }
                                });
                            });
                        </script>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('swalError')): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    title: '<strong>Error!</strong>',
                                    html: '<div class="text-danger"><i class="ri-error-warning-line fs-5 me-2"></i><?= session()->getFlashdata('swalError') ?></div>',
                                    icon: 'error',
                                    confirmButtonColor: '#f06548',
                                    customClass: {
                                        confirmButton: 'btn btn-danger'
                                    }
                                });
                            });
                        </script>
                    <?php endif; ?>

                    <!-- Main Settings Card -->
                    <div class="row settings-container">
                        <div class="col-xxl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex bg-soft-primary">
                                    <h4 class="card-title mb-0 flex-grow-1"><i class="ri-settings-3-line me-2"></i>Main Configuration Settings</h4>
                                </div><!-- end card header -->
                                <div class="card-body p-4">
                                    <form id="settingsForm" action="<?= base_url('settings/main-config/update/' . $settings->id) ?>" method="POST" class="needs-validation" novalidate>
                                        <div class="row mb-4">
                                            <div class="col-lg-6">
                                                <div class="card border rounded shadow-none border-primary border-opacity-25">
                                                    <div class="card-header bg-soft-primary">
                                                        <h5 class="card-title mb-0"><i class="ri-toggle-line me-2"></i>System Status Settings</h5>
                                                    </div>
                                                    <div class="card-body"> <!-- Maintenance Mode -->
                                                        <div class="d-flex align-items-center mb-4 p-3 border-bottom">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <i class="ri-tools-fill text-warning fs-18 me-2"></i>
                                                                    <h5 class="fs-14 mb-0">Maintenance Mode</h5>
                                                                </div>
                                                                <p class="text-muted mb-0">When enabled, the site will be inaccessible to users and display a maintenance message.</p>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <div class="form-check form-switch form-switch-md mb-0 d-flex align-items-center">
                                                                    <input type="checkbox" class="form-check-input" id="is_maintenance_mode" name="is_maintenance_mode" value="1" <?= (isset($settings->is_maintenance_mode) && $settings->is_maintenance_mode == 1) ? 'checked' : '' ?>>
                                                                    <span class="ms-2 badge <?= (isset($settings->is_maintenance_mode) && $settings->is_maintenance_mode == 1) ? 'bg-danger' : 'bg-gray-soft text-gray' ?>"><?= (isset($settings->is_maintenance_mode) && $settings->is_maintenance_mode == 1) ? 'Enabled' : 'Disabled' ?></span>
                                                                </div>
                                                            </div>
                                                        </div> <!-- Registration Open -->
                                                        <div class="d-flex align-items-center mb-4 p-3 border-bottom">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <i class="ri-user-add-line text-info fs-18 me-2"></i>
                                                                    <h5 class="fs-14 mb-0">Registration Open</h5>
                                                                </div>
                                                                <p class="text-muted mb-0">When enabled, users can register for the program. When disabled, registration will be closed.</p>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <div class="form-check form-switch form-switch-md mb-0 d-flex align-items-center">
                                                                    <input type="checkbox" class="form-check-input" id="is_registration_open" name="is_registration_open" value="1" <?= (isset($program->is_registration_open) && $program->is_registration_open == 1) ? 'checked' : '' ?>>
                                                                    <span class="ms-2 badge <?= (isset($program->is_registration_open) && $program->is_registration_open == 1) ? 'bg-success' : 'bg-gray-soft text-gray' ?>"><?= (isset($program->is_registration_open) && $program->is_registration_open == 1) ? 'Open' : 'Closed' ?></span>
                                                                </div>
                                                            </div>
                                                        </div> <!-- Verification Required -->
                                                        <div class="d-flex align-items-center mb-4 p-3 border-bottom">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <i class="ri-mail-check-line text-success fs-18 me-2"></i>
                                                                    <h5 class="fs-14 mb-0">Email Verification Required</h5>
                                                                </div>
                                                                <p class="text-muted mb-0">When enabled, users must verify their email address before accessing the system.</p>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <div class="form-check form-switch form-switch-md mb-0 d-flex align-items-center">
                                                                    <input type="checkbox" class="form-check-input" id="is_verification_required" name="is_verification_required" value="1" <?= (isset($settings->is_verification_required) && $settings->is_verification_required == 1) ? 'checked' : '' ?>>
                                                                    <span class="ms-2 badge <?= (isset($settings->is_verification_required) && $settings->is_verification_required == 1) ? 'bg-primary' : 'bg-gray-soft text-gray' ?>"><?= (isset($settings->is_verification_required) && $settings->is_verification_required == 1) ? 'Required' : 'Optional' ?></span>
                                                                </div>
                                                            </div>
                                                        </div> <!-- Program Active Status -->
                                                        <div class="d-flex align-items-center p-3">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <i class="ri-checkbox-circle-line text-primary fs-18 me-2"></i>
                                                                    <h5 class="fs-14 mb-0">Program Active Status</h5>
                                                                </div>
                                                                <p class="text-muted mb-0">When enabled, the program is active and operational. When disabled, the program is considered inactive.</p>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <div class="form-check form-switch form-switch-md mb-0 d-flex align-items-center">
                                                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?= (isset($program->is_active) && $program->is_active == 1) ? 'checked' : '' ?>>
                                                                    <span class="ms-2 badge <?= (isset($program->is_active) && $program->is_active == 1) ? 'bg-info' : 'bg-gray-soft text-gray' ?>"><?= (isset($program->is_active) && $program->is_active == 1) ? 'Active' : 'Inactive' ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="card border rounded shadow-none border-success border-opacity-25 h-100">
                                                    <div class="card-header bg-soft-success d-flex align-items-center">
                                                        <i class="ri-money-dollar-circle-line me-2 settings-icon text-success"></i>
                                                        <h5 class="card-title mb-0 settings-label">Financial Settings</h5>
                                                    </div>
                                                    <div class="card-body p-4"> <!-- USD to IDR Exchange Rate -->
                                                        <div class="mb-3">
                                                            <div class="d-flex align-items-center mb-3">
                                                                <i class="ri-exchange-dollar-line text-success fs-20 me-2"></i>
                                                                <label for="usd_in_idr" class="form-label mb-0 fs-15 fw-medium">USD to IDR Exchange Rate</label>
                                                            </div>
                                                            <div class="alert alert-soft-info border-0 py-2 px-3 mb-3">
                                                                <i class="ri-information-line me-1"></i>
                                                                This rate applies only to <strong><?= htmlspecialchars($program->name ?? 'this program') ?></strong>. Other programs/batches are not affected.
                                                            </div>
                                                            <div class="input-group input-group-lg">
                                                                <span class="input-group-text bg-success bg-opacity-10 text-success">IDR</span>
                                                                <input type="text" class="form-control form-control-lg" id="usd_in_idr" name="usd_in_idr" value="<?= $program->usd_in_idr ?? $settings->usd_in_idr ?? 15000 ?>" min="0" step="100">
                                                            </div>
                                                            <div class="form-text mt-2">
                                                                <span class="badge bg-light text-dark">Current rate for <?= htmlspecialchars($program->name ?? 'this program') ?>: 1 USD = <?= number_format($program->usd_in_idr ?? $settings->usd_in_idr ?? 15000, 0, ',', '.') ?> IDR</span>
                                                                <input type="hidden" name="current_usd_in_idr" value="<?= $program->usd_in_idr ?? $settings->usd_in_idr ?? 15000 ?>">
                                                            </div>
                                                        </div>

                                                        <!-- Sibling Programs Rate Comparison -->
                                                        <?php if (!empty($siblingPrograms) && count($siblingPrograms) > 1): ?>
                                                        <hr class="my-3">
                                                        <div class="mb-0">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="ri-list-check-2 text-success fs-18 me-2"></i>
                                                                <span class="fw-medium fs-14">Exchange Rates for All Batches</span>
                                                            </div>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-borderless mb-0">
                                                                    <thead>
                                                                        <tr class="text-muted small">
                                                                            <th class="ps-0">Program / Batch</th>
                                                                            <th class="text-end pe-0">Rate (IDR)</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($siblingPrograms as $sibling): ?>
                                                                        <tr class="<?= ($sibling->id == $program->id) ? 'fw-semibold' : '' ?>">
                                                                            <td class="ps-0">
                                                                                <?php if ($sibling->id == $program->id): ?>
                                                                                    <i class="ri-arrow-right-s-fill text-success me-1"></i>
                                                                                <?php endif; ?>
                                                                                <?= htmlspecialchars($sibling->name) ?>
                                                                                <?php if ($sibling->id == $program->id): ?>
                                                                                    <span class="badge bg-success-subtle text-success ms-1">Current</span>
                                                                                <?php endif; ?>
                                                                                <?php if (!$sibling->is_active): ?>
                                                                                    <span class="badge bg-secondary-subtle text-secondary ms-1">Inactive</span>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                            <td class="text-end pe-0">
                                                                                <span class="<?= ($sibling->id == $program->id) ? 'text-success' : '' ?>">
                                                                                    <?= number_format($sibling->usd_in_idr ?? 0, 0, ',', '.') ?>
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <p class="text-muted small mb-0 mt-2">
                                                                <i class="ri-swap-line me-1"></i>Switch program in the topbar to edit another batch's rate.
                                                            </p>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-5">
                                            <div class="col-12 text-center">
                                                <div class="card border-0 bg-light p-3">
                                                    <div class="card-body">
                                                        <button type="button" id="saveSettingsBtn" class="btn btn-primary btn-lg px-5">
                                                            <i class="ri-save-line me-1"></i> Save Configuration
                                                        </button>
                                                        <p class="text-muted small mt-2 mb-0">All changes will be applied immediately after saving</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
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

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize SweetAlert if needed
            if (typeof Swal === 'undefined') {
                // Load SweetAlert if not available
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                document.head.appendChild(script);
            }
            // Add animation effect to toggle switches and update badges
            const toggleSwitches = document.querySelectorAll('.form-check-input[type="checkbox"]');
            toggleSwitches.forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    const parentDiv = this.closest('.d-flex');
                    const badge = parentDiv.querySelector('.badge');

                    // Add animation
                    if (parentDiv) {
                        parentDiv.classList.add('animate__animated', 'animate__pulse');
                        setTimeout(() => {
                            parentDiv.classList.remove('animate__animated', 'animate__pulse');
                        }, 1000);
                    }

                    // Update badge
                    if (badge) {
                        // Handle specific toggle updates
                        if (this.id === 'is_maintenance_mode') {
                            if (this.checked) {
                                badge.textContent = 'Enabled';
                                badge.className = 'ms-2 badge bg-danger';
                            } else {
                                badge.textContent = 'Disabled';
                                badge.className = 'ms-2 badge bg-gray-soft text-gray';
                            }
                        } else if (this.id === 'is_registration_open') {
                            if (this.checked) {
                                badge.textContent = 'Open';
                                badge.className = 'ms-2 badge bg-success';
                            } else {
                                badge.textContent = 'Closed';
                                badge.className = 'ms-2 badge bg-gray-soft text-gray';
                            }
                        } else if (this.id === 'is_verification_required') {
                            if (this.checked) {
                                badge.textContent = 'Required';
                                badge.className = 'ms-2 badge bg-primary';
                            } else {
                                badge.textContent = 'Optional';
                                badge.className = 'ms-2 badge bg-gray-soft text-gray';
                            }
                        } else if (this.id === 'is_active') {
                            if (this.checked) {
                                badge.textContent = 'Active';
                                badge.className = 'ms-2 badge bg-info';
                            } else {
                                badge.textContent = 'Inactive';
                                badge.className = 'ms-2 badge bg-gray-soft text-gray';
                            }
                        }
                    }
                });
            });

            // Form validation
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    // Make sure currency field always passes validation
                    var usdInIdrField = document.getElementById('usd_in_idr');
                    if (usdInIdrField) {
                        usdInIdrField.setCustomValidity('');
                    }

                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // SweetAlert implementation for settings form
            document.getElementById('saveSettingsBtn').addEventListener('click', function() {
                var form = document.getElementById('settingsForm');
                // Get the exchange rate field
                var usdInIdrField = document.getElementById('usd_in_idr');
                var currentUsdInIdr = document.querySelector('input[name="current_usd_in_idr"]').value;

                console.log('Save button clicked');
                console.log('USD in IDR field value: "' + usdInIdrField.value + '"');
                console.log('Current USD in IDR value: "' + currentUsdInIdr + '"');

                // If field is empty or only whitespace, set it to the current value
                if (!usdInIdrField.value.trim()) {
                    console.log('USD in IDR field is empty, setting to current value');
                    usdInIdrField.value = currentUsdInIdr;
                    console.log('New value: ' + usdInIdrField.value);
                }

                // Force validation to pass regardless
                usdInIdrField.setCustomValidity('');
                console.log('Set custom validity to empty string');

                // Debug form elements
                var formData = new FormData(form);
                console.log('Form data:');
                for (var pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }

                // Check form validity (should now always pass for the currency field)
                if (!form.checkValidity()) {
                    console.log('Form is not valid');
                    form.classList.add('was-validated');
                    return;
                }
                console.log('Form is valid, proceeding with SweetAlert');

                // Show confirmation dialog with custom design
                Swal.fire({
                    title: '<strong>Save Configuration Changes?</strong>',
                    html: '<div class="text-start"><p>You\'re about to update the following settings:</p>' +
                        '<ul class="list-group mb-3">' +
                        '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                        'Maintenance Mode <span class="badge bg-' + (document.getElementById('is_maintenance_mode').checked ? 'danger' : 'secondary') + ' rounded-pill">' + (document.getElementById('is_maintenance_mode').checked ? 'Enabled' : 'Disabled') + '</span></li>' +
                        '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                        'Registration <span class="badge bg-' + (document.getElementById('is_registration_open').checked ? 'success' : 'secondary') + ' rounded-pill">' + (document.getElementById('is_registration_open').checked ? 'Open' : 'Closed') + '</span></li>' +
                        '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                        'Email Verification <span class="badge bg-' + (document.getElementById('is_verification_required').checked ? 'primary' : 'secondary') + ' rounded-pill">' + (document.getElementById('is_verification_required').checked ? 'Required' : 'Optional') + '</span></li>' +
                        '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                        'Program Status <span class="badge bg-' + (document.getElementById('is_active').checked ? 'info' : 'secondary') + ' rounded-pill">' + (document.getElementById('is_active').checked ? 'Active' : 'Inactive') + '</span></li>' +
                        '</ul>' +
                        '<p>Are you sure you want to save these changes?</p></div>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="ri-save-line me-1"></i> Yes, save changes',
                    cancelButtonText: '<i class="ri-close-line me-1"></i> Cancel',
                    confirmButtonColor: '#0ab39c',
                    cancelButtonColor: '#f06548',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-danger'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state with a more attractive design
                        Swal.fire({
                            title: '<strong>Saving Configuration</strong>',
                            html: '<div class="d-flex flex-column align-items-center">' +
                                '<div class="spinner-border text-primary mb-3" role="status"></div>' +
                                '<p class="mb-0">Please wait while your settings are being saved...</p>' +
                                '</div>',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        // Submit the form
                        console.log('User confirmed, submitting form...');
                        form.submit();
                        console.log('Form submitted');
                    }
                });
            });
        });
    </script>
</body>

</html>