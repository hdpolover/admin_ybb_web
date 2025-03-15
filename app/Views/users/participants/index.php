<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title'=>'Participants')); ?>
    <?= $this->include('partials/head-css') ?>
    
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
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
                    <?php echo view('partials/page-title', array('pagetitle'=>'Users', 'title'=>'Participants')); ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <h4 class="card-title mb-0">Participants List</h4>
                                            <p class="text-muted mb-0 mt-2">Showing participants for the current program</p>
                                        </div>
                                        <div>
                                            <a href="<?= base_url('participants/add') ?>" class="btn btn-primary">
                                                <i class="ri-add-line align-bottom me-1"></i> Add New Participant
                                            </a>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="participants-table" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Phone</th>
                                                    <th>Registration Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($participants)): ?>
                                                    <?php foreach ($participants as $participant): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar-xs me-2">
                                                                        <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                            <?= strtoupper(substr($participant['name'] ?? '', 0, 1)) ?>
                                                                        </span>
                                                                    </div>
                                                                    <?= esc($participant['name']) ?>
                                                                </div>
                                                            </td>
                                                            <td><?= esc($participant['email']) ?></td>
                                                            <td><?= esc($participant['phone']) ?></td>
                                                            <td><?= date('M d, Y', strtotime($participant['created_at'])) ?></td>
                                                            <td>
                                                                <?php 
                                                                $statusClass = [
                                                                    'active' => 'bg-success-subtle text-success',
                                                                    'pending' => 'bg-warning-subtle text-warning',
                                                                    'inactive' => 'bg-danger-subtle text-danger'
                                                                ];
                                                                $status = strtolower($participant['status'] ?? 'pending');
                                                                ?>
                                                                <span class="badge <?= $statusClass[$status] ?? '' ?>">
                                                                    <?= ucfirst($status) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex gap-2">
                                                                    <a href="<?= base_url('participants/view/' . $participant['id']) ?>" class="btn btn-sm btn-soft-primary">
                                                                        <i class="ri-eye-fill align-bottom"></i>
                                                                    </a>
                                                                    <a href="<?= base_url('participants/edit/' . $participant['id']) ?>" class="btn btn-sm btn-soft-warning">
                                                                        <i class="ri-pencil-fill align-bottom"></i>
                                                                    </a>
                                                                    <button type="button" class="btn btn-sm btn-soft-danger delete-participant" data-id="<?= $participant['id'] ?>">
                                                                        <i class="ri-delete-bin-2-line align-bottom"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">No participants found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <?php if (isset($pager) && $pager['totalPages'] > 1): ?>
                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <div class="text-muted">
                                            Showing <span class="fw-semibold"><?= ($pager['currentPage'] - 1) * $pager['perPage'] + 1 ?></span> to 
                                            <span class="fw-semibold"><?= min($pager['currentPage'] * $pager['perPage'], $pager['total']) ?></span> of 
                                            <span class="fw-semibold"><?= $pager['total'] ?></span> results
                                        </div>
                                        <ul class="pagination pagination-separated mb-0">
                                            <li class="page-item <?= $pager['currentPage'] <= 1 ? 'disabled' : '' ?>">
                                                <a href="?page=<?= $pager['currentPage'] - 1 ?>" class="page-link">Previous</a>
                                            </li>
                                            <?php for ($i = 1; $i <= $pager['totalPages']; $i++): ?>
                                                <li class="page-item <?= $i === $pager['currentPage'] ? 'active' : '' ?>">
                                                    <a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?= $pager['currentPage'] >= $pager['totalPages'] ? 'disabled' : '' ?>">
                                                <a href="?page=<?= $pager['currentPage'] + 1 ?>" class="page-link">Next</a>
                                            </li>
                                        </ul>
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

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- List initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle delete participant
            document.querySelectorAll('.delete-participant').forEach(function(button) {
                button.addEventListener('click', function() {
                    var participantId = this.dataset.id;
                    if (confirm('Are you sure you want to delete this participant?')) {
                        fetch('<?= base_url('participants/delete/') ?>' + participantId, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Participant deleted successfully');
                                window.location.reload();
                            } else {
                                alert('Failed to delete participant: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while trying to delete the participant');
                        });
                    }
                });
            });
        });
    </script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>
</body>
</html>