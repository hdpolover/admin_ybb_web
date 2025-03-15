<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title'=>'Participants')); ?>

    <?= $this->include('partials/head-css') ?>
    
     <!--datatable css-->
     <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
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
                                    <h4 class="card-title">Participants List</h4>
                                    <p class="card-title-desc">View and manage all registered participants</p>

                                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Registration Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(isset($participants) && is_array($participants)): ?>
                                                <?php foreach($participants as $participant): ?>
                                                    <tr>
                                                        <td><?= $participant['id'] ?></td>
                                                        <td><?= $participant['name'] ?></td>
                                                        <td><?= $participant['email'] ?></td>
                                                        <td><?= $participant['phone'] ?></td>
                                                        <td><?= date('Y-m-d', strtotime($participant['registration_date'])) ?></td>
                                                        <td>
                                                            <?php if($participant['status'] == 'active'): ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php elseif($participant['status'] == 'pending'): ?>
                                                                <span class="badge bg-warning">Pending</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <a href="<?= base_url('participants/view/' . $participant['id']) ?>" class="btn btn-primary btn-sm">View</a>
                                                            <a href="<?= base_url('participants/edit/' . $participant['id']) ?>" class="btn btn-info btn-sm">Edit</a>
                                                            <button type="button" class="btn btn-danger btn-sm delete-participant" data-id="<?= $participant['id'] ?>">Delete</button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No participants found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
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

    <script>
        $(document).ready(function() {
            // Initialize DataTable with options
            var dataTable = $('#datatable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                responsive: true
            });
            
            // Handle delete participant
            $('.delete-participant').on('click', function() {
                var participantId = $(this).data('id');
                if (confirm('Are you sure you want to delete this participant?')) {
                    // Add AJAX call to delete participant
                    $.ajax({
                        url: '<?= base_url('participants/delete') ?>/' + participantId,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('Participant deleted successfully');
                                // Reload the page to reflect the changes
                                location.reload();
                            } else {
                                alert('Failed to delete participant');
                            }
                        },
                        error: function() {
                            alert('An error occurred while trying to delete the participant');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>