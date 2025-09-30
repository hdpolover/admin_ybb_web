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
<div class="modal fade" id="addPeriodModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add New Period</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="addPeriodForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_period_name">Period Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_period_name" name="name" required>
                                <small class="form-text text-muted">e.g., "Main Registration", "Extension", "Final Extension"</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_period_description">Description</label>
                                <input type="text" class="form-control" id="add_period_description" name="description">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_start_date">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="add_start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_end_date">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="add_end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i>
                        <strong>Note:</strong> Periods cannot overlap with existing periods. There can be gaps between periods, but no overlapping dates are allowed.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Period</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Period Modal -->
<div class="modal fade" id="editPeriodModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Period</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="editPeriodForm">
                <input type="hidden" id="edit_period_id" name="period_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_period_name">Period Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_period_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_period_description">Description</label>
                                <input type="text" class="form-control" id="edit_period_description" name="description">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_start_date">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="edit_start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_end_date">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="edit_end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_is_active">Status</label>
                                <select class="form-control" id="edit_is_active" name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Period</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const baseUrl = '<?= base_url() ?>';
const paymentId = <?= $payment->id ?>;

// Initialize tooltips when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function openAddPeriodModal() {
    $('#addPeriodForm')[0].reset();
    $('#addPeriodModal').modal('show');
}

function editPeriod(periodId) {
    // Fetch period data
    $.get(baseUrl + 'master-data/program-payments/periods/' + periodId + '/get', function(response) {
        if (response.success) {
            const period = response.data;
            $('#edit_period_id').val(period.id);
            $('#edit_period_name').val(period.name);
            $('#edit_period_description').val(period.description);
            
            // Format datetime for input
            const startDate = new Date(period.start_date);
            const endDate = new Date(period.end_date);
            
            $('#edit_start_date').val(formatDateTimeLocal(startDate));
            $('#edit_end_date').val(formatDateTimeLocal(endDate));
            $('#edit_is_active').val(period.is_active);
            
            $('#editPeriodModal').modal('show');
        } else {
            Swal.fire('Error', response.message, 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Failed to fetch period data', 'error');
    });
}

function deletePeriod(periodId, periodName) {
    Swal.fire({
        title: 'Are you sure?',
        text: `Delete period "${periodName}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(baseUrl + 'master-data/program-payments/periods/' + periodId + '/delete', function(response) {
                if (response.success) {
                    Swal.fire('Deleted!', response.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Failed to delete period', 'error');
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

// Form submissions
$('#addPeriodForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    
    $.post(baseUrl + 'master-data/program-payments/' + paymentId + '/periods/create', formData, function(response) {
        if (response.success) {
            Swal.fire('Success', response.message, 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', response.message, 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Failed to add period', 'error');
    });
});

$('#editPeriodForm').on('submit', function(e) {
    e.preventDefault();
    
    const periodId = $('#edit_period_id').val();
    const formData = $(this).serialize();
    
    $.post(baseUrl + 'master-data/program-payments/periods/' + periodId + '/update', formData, function(response) {
        if (response.success) {
            Swal.fire('Success', response.message, 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', response.message, 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Failed to update period', 'error');
    });
});
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