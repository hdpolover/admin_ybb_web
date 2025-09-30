<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title' => 'Payment Periods')); ?>

    <?= $this->include('partials/head-css') ?>

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => $title)); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="card-title"><?= $title ?></h4>
                                    <div>
                                        <a href="<?= base_url('master-data/program-payments') ?>" class="btn btn-secondary btn-sm">
                                            <i class="ri-arrow-left-line"></i> Back to Payments
                                        </a>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="openAddPeriodModal()">
                                            <i class="ri-add-line"></i> Add Period
                                        </button>
                                    </div>
                                </div>
            <div class="card-body">
                <!-- Payment Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6><strong>Payment Details:</strong></h6>
                        <p><strong>Name:</strong> <?= $payment->name ?></p>
                        <p><strong>Category:</strong> <span class="badge bg-<?= $payment->category == 'registration' ? 'primary' : 'info' ?>"><?= ucwords(str_replace('_', ' ', $payment->category)) ?></span></p>
                        <?php if ($payment->type): ?>
                        <p><strong>Type:</strong> <span class="badge bg-secondary"><?= ucwords(str_replace('_', ' ', $payment->type)) ?></span></p>
                        <?php endif; ?>
                        <p><strong>USD Amount:</strong> $<?= number_format($payment->usd_amount, 2) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>Current Status:</strong></h6>
                        <p><strong>Active:</strong> <span class="badge bg-<?= $payment->is_active ? 'success' : 'danger' ?>"><?= $payment->is_active ? 'Yes' : 'No' ?></span></p>
                        <p><strong>Total Periods:</strong> <?= count($periods) ?></p>
                        <p><strong>Description:</strong> <?= $payment->description ?: 'No description' ?></p>
                    </div>
                </div>

                <!-- Periods Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 50px;">#</th>
                                <th scope="col">Period Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Start Date</th>
                                <th scope="col">End Date</th>
                                <th scope="col" style="width: 80px;">Order</th>
                                <th scope="col" style="width: 100px;">Status</th>
                                <th scope="col" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($periods)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No periods found for this payment</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($periods as $index => $period): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= $period->name ?></strong>
                                    <?php 
                                    $now = new DateTime();
                                    $start = new DateTime($period->start_date);
                                    $end = new DateTime($period->end_date);
                                    if ($now >= $start && $now <= $end): ?>
                                    <br><small class="text-success"><i class="ri-time-line"></i> Currently Active</small>
                                    <?php elseif ($now < $start): ?>
                                    <br><small class="text-warning"><i class="ri-time-line"></i> Upcoming</small>
                                    <?php else: ?>
                                    <br><small class="text-muted"><i class="ri-time-line"></i> Ended</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= $period->description ?: '<em class="text-muted">No description</em>' ?></td>
                                <td>
                                    <?php
                                    $startDateTime = new DateTime($period->start_date);
                                    $isStartMidnight = $startDateTime->format('H:i:s') === '00:00:00';
                                    ?>
                                    <?= $isStartMidnight ? date('M j, Y', strtotime($period->start_date)) : date('M j, Y g:i A', strtotime($period->start_date)) ?>
                                    <br><small class="text-muted"><?= date('Y-m-d', strtotime($period->start_date)) ?><?= $isStartMidnight ? '' : ' ' . date('H:i', strtotime($period->start_date)) ?></small>
                                </td>
                                <td>
                                    <?php
                                    $endDateTime = new DateTime($period->end_date);
                                    $isEndMidnight = $endDateTime->format('H:i:s') === '00:00:00';
                                    $isEndEndOfDay = $endDateTime->format('H:i:s') === '23:59:59';
                                    ?>
                                    <?= ($isEndMidnight || $isEndEndOfDay) ? date('M j, Y', strtotime($period->end_date)) : date('M j, Y g:i A', strtotime($period->end_date)) ?>
                                    <br><small class="text-muted"><?= date('Y-m-d', strtotime($period->end_date)) ?><?= ($isEndMidnight || $isEndEndOfDay) ? '' : ' ' . date('H:i', strtotime($period->end_date)) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark"><?= $period->order_number ?></span></td>
                                <td>
                                    <span class="badge bg-<?= $period->is_active ? 'success' : 'danger' ?>">
                                        <?= $period->is_active ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editPeriod(<?= $period->id ?>)" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Period">
                                            <i class="ri-pencil-fill"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deletePeriod(<?= $period->id ?>, '<?= addslashes($period->name) ?>')" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Period">
                                            <i class="ri-delete-bin-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Period Modal -->
<div class="modal fade" id="addPeriodModal" tabindex="-1" aria-labelledby="addPeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="addPeriodModalLabel">Add New Period</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPeriodForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_period_name" class="form-label">Period Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_period_name" name="name" required>
                                <div class="form-text">e.g., "Main Registration", "Extension", "Final Extension"</div>
                                <div class="invalid-feedback">Please provide a period name.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_period_description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="add_period_description" name="description">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_start_date" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="add_start_date" name="start_date" required>
                                <div class="invalid-feedback">Please provide a start date and time.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_end_date" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="add_end_date" name="end_date" required>
                                <div class="invalid-feedback">Please provide an end date and time.</div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i>
                        <strong>Note:</strong> Periods cannot overlap with existing periods. There can be gaps between periods, but no overlapping dates are allowed.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addPeriodBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        Add Period
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Period Modal -->
<div class="modal fade" id="editPeriodModal" tabindex="-1" aria-labelledby="editPeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="editPeriodModalLabel">Edit Period</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPeriodForm">
                <input type="hidden" id="edit_period_id" name="period_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_period_name" class="form-label">Period Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_period_name" name="name" required>
                                <div class="invalid-feedback">Please provide a period name.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_period_description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="edit_period_description" name="description">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_start_date" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="edit_start_date" name="start_date" required>
                                <div class="invalid-feedback">Please provide a start date and time.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_end_date" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="edit_end_date" name="end_date" required>
                                <div class="invalid-feedback">Please provide an end date and time.</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_is_active" class="form-label">Status</label>
                                <select class="form-select" id="edit_is_active" name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="editPeriodBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        Update Period
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const baseUrl = '<?= base_url() ?>';
const paymentId = <?= $payment->id ?>;

console.log('=== PAGE INITIALIZATION ===');
console.log('Base URL:', baseUrl);
console.log('Payment ID:', paymentId);
console.log('jQuery loaded:', typeof $ !== 'undefined');
console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined');
console.log('Swal loaded:', typeof Swal !== 'undefined');

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - starting initialization');
    // Check for flash messages
    <?php if (session()->has('success')): ?>
        Swal.fire({
            title: 'Success!',
            text: '<?= addslashes(session('success')) ?>',
            icon: 'success',
            confirmButtonColor: '#0ab39c'
        });
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
        Swal.fire({
            title: 'Error!',
            text: '<?= addslashes(session('error')) ?>',
            icon: 'error',
            confirmButtonColor: '#f06548'
        });
    <?php endif; ?>

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize form validation
    initFormValidation();
    
    // Add test button for debugging
    console.log('Adding test AJAX button for debugging');
    
    // Test AJAX functionality
    window.testAjax = function() {
        console.log('Testing AJAX functionality...');
        
        $.ajax({
            url: baseUrl + 'api/server-time',
            method: 'GET',
            dataType: 'json',
            timeout: 10000
        })
        .done(function(response) {
            console.log('Test AJAX successful:', response);
            Swal.fire({
                title: 'AJAX Test Successful!',
                text: 'Server responded correctly. Current server time: ' + (response.server_time ? response.server_time.time_24 : 'Unknown'),
                icon: 'success'
            });
        })
        .fail(function(xhr, status, error) {
            console.error('Test AJAX failed:', xhr, status, error);
            Swal.fire({
                title: 'AJAX Test Failed!',
                text: 'Status: ' + status + ', Error: ' + error + ', Response: ' + xhr.responseText,
                icon: 'error'
            });
        });
    };
    
    console.log('Test function created. Call window.testAjax() in console to test.');
});

// Initialize form validation
function initFormValidation() {
    // Add custom validation for date range
    $('#add_end_date, #edit_end_date').on('change', function() {
        const startDateInput = $(this).closest('form').find('[name="start_date"]');
        const endDateInput = $(this);
        
        if (startDateInput.val() && endDateInput.val()) {
            const startDate = new Date(startDateInput.val());
            const endDate = new Date(endDateInput.val());
            
            if (endDate <= startDate) {
                endDateInput[0].setCustomValidity('End date must be after start date');
                endDateInput.addClass('is-invalid');
            } else {
                endDateInput[0].setCustomValidity('');
                endDateInput.removeClass('is-invalid');
            }
        }
    });
    
    $('#add_start_date, #edit_start_date').on('change', function() {
        const startDateInput = $(this);
        const endDateInput = $(this).closest('form').find('[name="end_date"]');
        
        if (startDateInput.val() && endDateInput.val()) {
            const startDate = new Date(startDateInput.val());
            const endDate = new Date(endDateInput.val());
            
            if (endDate <= startDate) {
                endDateInput[0].setCustomValidity('End date must be after start date');
                endDateInput.addClass('is-invalid');
            } else {
                endDateInput[0].setCustomValidity('');
                endDateInput.removeClass('is-invalid');
            }
        }
    });
}

// Show loading state on button
function showButtonLoading(buttonId, text = 'Processing...') {
    console.log('showButtonLoading called for:', buttonId);
    const button = document.getElementById(buttonId);
    
    if (!button) {
        console.error('Button not found:', buttonId);
        return;
    }
    
    const spinner = button.querySelector('.spinner-border');
    
    if (!spinner) {
        console.error('Spinner not found in button:', buttonId);
        return;
    }
    
    const originalText = button.innerHTML;
    
    spinner.classList.remove('d-none');
    button.disabled = true;
    button.setAttribute('data-original-text', originalText);
    
    // Update text while keeping spinner
    const textNode = button.childNodes[button.childNodes.length - 1];
    if (textNode && textNode.nodeType === Node.TEXT_NODE) {
        textNode.textContent = ' ' + text;
    }
    
    console.log('Loading state set for button:', buttonId);
}

// Hide loading state on button
function hideButtonLoading(buttonId) {
    console.log('hideButtonLoading called for:', buttonId);
    const button = document.getElementById(buttonId);
    
    if (!button) {
        console.error('Button not found for hiding loading:', buttonId);
        return;
    }
    
    const spinner = button.querySelector('.spinner-border');
    
    if (spinner) {
        spinner.classList.add('d-none');
    }
    
    button.disabled = false;
    
    const originalText = button.getAttribute('data-original-text');
    if (originalText) {
        button.innerHTML = originalText;
    }
    
    console.log('Loading state removed for button:', buttonId);
}

function openAddPeriodModal() {
    console.log('openAddPeriodModal called');
    
    // Reset form and validation states
    const form = document.getElementById('addPeriodForm');
    form.reset();
    form.classList.remove('was-validated');
    
    // Clear any validation errors
    $(form).find('.is-invalid').removeClass('is-invalid');
    
    // Show modal using Bootstrap 5 API
    const modal = new bootstrap.Modal(document.getElementById('addPeriodModal'));
    modal.show();
    
    console.log('Modal should be open now');
}

function editPeriod(periodId) {
    console.log('=== EDIT PERIOD CALLED ===');
    console.log('Period ID:', periodId);
    
    // Show loading toast
    Swal.fire({
        title: 'Loading...',
        text: 'Fetching period details',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Fetch period data
    console.log('Fetching period data for ID:', periodId);
    $.get(baseUrl + 'master-data/program-payments/periods/' + periodId + '/get')
        .done(function(response) {
            Swal.close();
            
            if (response.success) {
                const period = response.data;
                
                // Reset form validation
                const form = document.getElementById('editPeriodForm');
                form.classList.remove('was-validated');
                $(form).find('.is-invalid').removeClass('is-invalid');
                
                // Populate form
                $('#edit_period_id').val(period.id);
                $('#edit_period_name').val(period.name);
                $('#edit_period_description').val(period.description);
                
                // Format datetime for input
                const startDate = new Date(period.start_date);
                const endDate = new Date(period.end_date);
                
                $('#edit_start_date').val(formatDateTimeLocal(startDate));
                $('#edit_end_date').val(formatDateTimeLocal(endDate));
                $('#edit_is_active').val(period.is_active);
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editPeriodModal'));
                modal.show();
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.message || 'Failed to fetch period data',
                    icon: 'error',
                    confirmButtonColor: '#f06548'
                });
            }
        })
        .fail(function(xhr) {
            Swal.close();
            let errorMessage = 'Failed to fetch period data';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status) {
                errorMessage += ` (HTTP ${xhr.status})`;
            }
            
            Swal.fire({
                title: 'Connection Error!',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#f06548'
            });
        });
}

function deletePeriod(periodId, periodName) {
    Swal.fire({
        title: 'Are you sure?',
        html: `Delete period <strong>"${periodName}"</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f06548',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="ri-delete-bin-line me-1"></i> Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show deleting progress
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we delete the period',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.post(baseUrl + 'master-data/program-payments/periods/' + periodId + '/delete')
                .done(function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message || 'Period has been deleted successfully.',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c',
                            timer: 2000,
                            timerProgressBar: true
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message || 'Failed to delete period',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                })
                .fail(function(xhr) {
                    let errorMessage = 'Failed to delete period';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status) {
                        errorMessage += ` (HTTP ${xhr.status})`;
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                });
        }
    });
}

function formatDateTimeLocal(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Self-contained form handler to avoid conflicts with app.js
(function() {
    'use strict';
    
    console.log('=== ISOLATED FORM HANDLER STARTING ===');
    
    // Wait for both DOM and jQuery to be ready
    function initializeFormHandlers() {
        console.log('Initializing form handlers...');
        
        // Make sure we have all required elements
        const addForm = document.getElementById('addPeriodForm');
        const addBtn = document.getElementById('addPeriodBtn');
        
        if (!addForm) {
            console.error('Add form not found!');
            return;
        }
        
        if (!addBtn) {
            console.error('Add button not found!');
            return;
        }
        
        console.log('Form and button found, setting up handlers');
        
        // Remove any existing handlers to avoid conflicts
        $(addForm).off('submit.periodsHandler');
        
        // Add our isolated handler using fetch API to avoid jQuery conflicts
        $(addForm).on('submit.periodsHandler', function(e) {
            console.log('=== ISOLATED FORM SUBMISSION ===');
            
            e.preventDefault();
            e.stopPropagation();
            
            const form = this;
            console.log('Form submitted, checking validity...');
            
            // Simple validation check
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    console.log('Invalid field:', field.name);
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                console.log('Validation failed');
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please fill in all required fields.',
                    icon: 'warning',
                    confirmButtonColor: '#f1b44c'
                });
                return false;
            }
            
            console.log('Validation passed, sending request...');
            
            // Show loading state
            addBtn.disabled = true;
            addBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding Period...';
            
            // Get form data and format dates properly
            const formData = new FormData(form);
            const urlParams = new URLSearchParams();
            
            for (const [key, value] of formData.entries()) {
                if (key === 'start_date' || key === 'end_date') {
                    // Convert datetime-local format to Y-m-d H:i:s format
                    if (value) {
                        const dateObj = new Date(value);
                        const formattedDate = dateObj.getFullYear() + '-' +
                            String(dateObj.getMonth() + 1).padStart(2, '0') + '-' +
                            String(dateObj.getDate()).padStart(2, '0') + ' ' +
                            String(dateObj.getHours()).padStart(2, '0') + ':' +
                            String(dateObj.getMinutes()).padStart(2, '0') + ':' +
                            String(dateObj.getSeconds()).padStart(2, '0');
                        urlParams.append(key, formattedDate);
                        console.log(`Formatted ${key}:`, value, '->', formattedDate);
                    } else {
                        urlParams.append(key, value);
                    }
                } else {
                    urlParams.append(key, value);
                }
            }
            
            const ajaxUrl = baseUrl + 'master-data/program-payments/' + paymentId + '/periods/create';
            
            console.log('Sending to:', ajaxUrl);
            console.log('Data:', urlParams.toString());
            console.log('Individual parameters:');
            for (const [key, value] of urlParams.entries()) {
                console.log(`  ${key}: ${value}`);
            }
            
            // Use fetch instead of jQuery to avoid conflicts
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: urlParams
            })
            .then(response => {
                console.log('Response received:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                return response.text(); // Get as text first to see what we're dealing with
            })
            .then(text => {
                console.log('Response text:', text);
                
                // Try to parse as JSON
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                }
                
                console.log('Parsed response:', data);
                
                // Reset button
                addBtn.disabled = false;
                addBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2 d-none"></span>Add Period';
                
                if (data.success) {
                    console.log('Success!');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addPeriodModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: data.message || 'Period has been added successfully.',
                        icon: 'success',
                        confirmButtonColor: '#0ab39c'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    console.log('Server returned error:', data.message);
                    
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to add period',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                }
            })
            .catch(error => {
                console.error('Request failed:', error);
                
                // Reset button
                addBtn.disabled = false;
                addBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2 d-none"></span>Add Period';
                
                Swal.fire({
                    title: 'Connection Error!',
                    text: error.message || 'Failed to add period',
                    icon: 'error',
                    confirmButtonColor: '#f06548'
                });
            });
            
            return false;
        });
        
        // Also set up edit form handler
        const editForm = document.getElementById('editPeriodForm');
        const editBtn = document.getElementById('editPeriodBtn');
        
        if (editForm && editBtn) {
            console.log('Setting up edit form handler...');
            
            // Remove any existing handlers
            $(editForm).off('submit.periodsEditHandler');
            
            // Add edit form handler
            $(editForm).on('submit.periodsEditHandler', function(e) {
                console.log('=== EDIT FORM SUBMISSION ===');
                
                e.preventDefault();
                e.stopPropagation();
                
                const form = this;
                console.log('Edit form submitted, checking validity...');
                
                // Simple validation check
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                        console.log('Invalid field:', field.name);
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                if (!isValid) {
                    console.log('Edit form validation failed');
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'warning',
                        confirmButtonColor: '#f1b44c'
                    });
                    return false;
                }
                
                console.log('Edit form validation passed, sending request...');
                
                // Show loading state
                editBtn.disabled = true;
                editBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating Period...';
                
                // Get form data and format dates properly
                const formData = new FormData(form);
                const urlParams = new URLSearchParams();
                
                for (const [key, value] of formData.entries()) {
                    if (key === 'start_date' || key === 'end_date') {
                        // Convert datetime-local format to Y-m-d H:i:s format
                        if (value) {
                            const dateObj = new Date(value);
                            const formattedDate = dateObj.getFullYear() + '-' +
                                String(dateObj.getMonth() + 1).padStart(2, '0') + '-' +
                                String(dateObj.getDate()).padStart(2, '0') + ' ' +
                                String(dateObj.getHours()).padStart(2, '0') + ':' +
                                String(dateObj.getMinutes()).padStart(2, '0') + ':' +
                                String(dateObj.getSeconds()).padStart(2, '0');
                            urlParams.append(key, formattedDate);
                            console.log(`Formatted ${key}:`, value, '->', formattedDate);
                        } else {
                            urlParams.append(key, value);
                        }
                    } else {
                        urlParams.append(key, value);
                    }
                }
                
                // Get the period ID
                const periodId = document.getElementById('edit_period_id').value;
                const ajaxUrl = baseUrl + 'master-data/program-payments/periods/' + periodId + '/update';
                
                console.log('Updating period ID:', periodId);
                console.log('Sending to:', ajaxUrl);
                console.log('Data:', urlParams.toString());
                console.log('Individual parameters:');
                for (const [key, value] of urlParams.entries()) {
                    console.log(`  ${key}: ${value}`);
                }
                
                // Use fetch for consistency
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: urlParams
                })
                .then(response => {
                    console.log('Edit response received:', response.status, response.statusText);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    return response.text();
                })
                .then(text => {
                    console.log('Edit response text:', text);
                    
                    // Try to parse as JSON
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                    }
                    
                    console.log('Edit parsed response:', data);
                    
                    // Reset button
                    editBtn.disabled = false;
                    editBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2 d-none"></span>Update Period';
                    
                    if (data.success) {
                        console.log('Edit success!');
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editPeriodModal'));
                        if (modal) {
                            modal.hide();
                        }
                        
                        // Show success message
                        Swal.fire({
                            title: 'Updated!',
                            text: data.message || 'Period has been updated successfully.',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        console.log('Edit server returned error:', data.message);
                        
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to update period',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                })
                .catch(error => {
                    console.error('Edit request failed:', error);
                    
                    // Reset button
                    editBtn.disabled = false;
                    editBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2 d-none"></span>Update Period';
                    
                    Swal.fire({
                        title: 'Connection Error!',
                        text: error.message || 'Failed to update period',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                });
                
                return false;
            });
            
            console.log('Edit form handler attached successfully');
        } else {
            console.log('Edit form or button not found');
        }
        
        console.log('All form handlers attached successfully');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Add a small delay to let other scripts finish
            setTimeout(initializeFormHandlers, 100);
        });
    } else {
        // DOM already ready
        setTimeout(initializeFormHandlers, 100);
    }
    
})();
</script>

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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>