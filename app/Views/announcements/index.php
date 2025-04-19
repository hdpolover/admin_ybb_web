<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Announcements')); ?>

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
        .description-cell {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .modal-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }

        /* Improve table responsiveness */
        .table-responsive.table-card {
            border-radius: 0.25rem;
            box-shadow: 0 1px 2px rgba(56, 65, 74, 0.15);
        }

        #announcements-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Content', 'title' => 'Announcements')); ?>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Program Announcements</h4>
                                    <div class="flex-shrink-0">
                                        <?php if (session('current_program')): ?>
                                            <a href="/announcements/add" class="btn btn-primary">
                                                <i class="ri-add-line align-bottom me-1"></i> Add Announcement
                                            </a>
                                        <?php else: ?>
                                            <div class="alert alert-warning mb-0">
                                                <i class="ri-error-warning-line me-1 align-middle"></i>
                                                Please select a program first
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div>
                                                <label for="status-filter" class="form-label">Filter by Status</label>
                                                <select class="form-select" id="status-filter">
                                                    <option value="">All Status</option>
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <table id="announcements-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Title</th>
                                                <th scope="col">Content</th>
                                                <th scope="col">Image</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($programAnnouncements)) : ?>
                                                <?php foreach ($programAnnouncements as $index => $announcement) : ?>
                                                    <tr data-status="<?= $announcement->is_active ?>">
                                                        <td><?= $index + 1 ?></td>
                                                        <td class="description-cell" data-bs-toggle="tooltip" title="<?= htmlspecialchars($announcement->title) ?>">
                                                            <?= mb_strimwidth(strip_tags($announcement->title), 0, 50, "...") ?>
                                                        </td>
                                                        <td class="description-cell" data-bs-toggle="tooltip" title="<?= htmlspecialchars($announcement->content) ?>">
                                                            <?= mb_strimwidth(strip_tags($announcement->content), 0, 40, "...") ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($announcement->img_url)): ?>
                                                                <a href="<?= $announcement->img_url ?>" target="_blank">
                                                                    <img src="<?= $announcement->img_url ?>" alt="Image" class="rounded avatar-sm">
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">No image</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($announcement->is_active == 1): ?>
                                                                <span class="badge bg-success-subtle text-success">Active</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <div class="view">
                                                                    <button type="button" class="btn btn-sm btn-info view-announcement" data-id="<?= $announcement->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                                                        <i class="ri-eye-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="edit">
                                                                    <a href="/announcements/edit/<?= $announcement->id ?>" class="btn btn-sm btn-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                                        <i class="ri-pencil-fill"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="remove">
                                                                    <button type="button" class="btn btn-sm btn-danger remove-announcement" data-id="<?= $announcement->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                                        <i class="ri-delete-bin-fill"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No announcements found</td>
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

    <!-- View Announcement Modal -->
    <div class="modal fade" id="view-announcement-modal" tabindex="-1" aria-labelledby="view-announcement-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-loading" id="view-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="view-announcement-modal-label">
                        <i class="ri-megaphone-fill me-1 text-primary"></i>Announcement Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>                <div class="modal-body">
                    <div class="border rounded shadow-none p-3 mb-4" id="view_status_container">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="text-primary mb-0" id="view_title">Loading...</h2>
                            <span class="badge fs-6 px-3 py-2" id="view_status_badge">Loading...</span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <!-- Content and Image in tabs -->
                        <div class="col-md-12">
                            <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tab_content" role="tab">
                                        <i class="ri-file-text-line me-1 align-bottom"></i> Content
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab_image" role="tab">
                                        <i class="ri-image-line me-1 align-bottom"></i> Featured Image
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab_meta" role="tab">
                                        <i class="ri-settings-3-line me-1 align-bottom"></i> SEO & Metadata
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content border border-top-0 p-3">
                                <div class="tab-pane active" id="tab_content" role="tabpanel">
                                    <div id="view_content" class="bg-light p-3 rounded">Loading...</div>
                                </div>

                                <div class="tab-pane" id="tab_image" role="tabpanel">
                                    <div class="text-center p-3">
                                        <img id="view_image" src="" alt="Announcement Image" class="img-fluid rounded shadow-sm" style="max-height: 350px; display: none;">
                                        <div id="no_image_text" class="alert alert-info mb-0">
                                            <i class="ri-information-line me-2"></i>No featured image available for this announcement
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab_meta" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <div class="card border-0 bg-light">
                                                <div class="card-header bg-info-subtle text-info">
                                                    <i class="ri-link me-1"></i> URL Slug
                                                </div>
                                                <div class="card-body py-2">
                                                    <code id="view_slug" class="fs-6">Loading...</code>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <div class="card border-0 bg-light">
                                                <div class="card-header bg-info-subtle text-info">
                                                    <i class="ri-heading me-1"></i> Meta Title
                                                </div>
                                                <div class="card-body py-2">
                                                    <p id="view_meta_title" class="mb-0">Loading...</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <div class="card border-0 bg-light">
                                                <div class="card-header bg-info-subtle text-info">
                                                    <i class="ri-text me-1"></i> Meta Description
                                                </div>
                                                <div class="card-body py-2">
                                                    <p id="view_meta_description" class="mb-0">Loading...</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="card border-0 bg-light">
                                                <div class="card-header bg-info-subtle text-info">
                                                    <i class="ri-price-tag-3-line me-1"></i> Tags
                                                </div>
                                                <div class="card-body py-2" id="view_tags_container">
                                                    <div id="view_tags">Loading...</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-2 bg-light rounded mb-3">
                                <div class="flex-shrink-0">
                                    <i class="ri-eye-line fs-3 text-muted"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fs-13 mb-0">Visible To</h6>
                                    <p class="text-muted mb-0" id="view_visible_to">Loading...</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-2 bg-light rounded mb-3">
                                <div class="flex-shrink-0">
                                    <i class="ri-time-line fs-3 text-muted"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fs-13 mb-0">Last Updated</h6>
                                    <p class="text-muted mb-0" id="view_updated">Loading...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success view-edit-btn" data-id="">
                        <i class="ri-pencil-fill me-1"></i>Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Announcement Modal -->
    <div class="modal fade" id="delete-announcement-modal" tabindex="-1" aria-labelledby="delete-announcement-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-announcement-modal-label">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this announcement? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <form id="delete-announcement-form" action="/announcements/delete" method="post" style="display: inline;">
                        <input type="hidden" id="delete_announcement_id" name="id">
                        <button type="submit" class="btn btn-danger" id="confirm-delete-btn">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/customizer') ?>

    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>

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
                initializeAnnouncementFunctions();
            } else {
                console.error("jQuery is not loaded!");
            }
        });

        function initializeAnnouncementFunctions() {
            // Initialize DataTable with improved configuration
            var announcementsTable = $('#announcements-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true,
                columnDefs: [{
                    orderable: false,
                    targets: [3, 5] // Image and Action columns are not sortable
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

            // Status filter functionality
            $('#status-filter').on('change', function() {
                var status = $(this).val();
                if (status === '') {
                    announcementsTable.column(4).search('').draw();
                } else {
                    announcementsTable.column(4).search(status).draw();
                }
            });

            // Handle view button click
            $(document).on('click', '.view-announcement', function(e) {
                e.preventDefault();

                var announcementId = $(this).data('id');
                console.log("View button clicked for ID:", announcementId);

                // Show modal first
                $('#view-announcement-modal').modal('show');
                $('#view-loading').show();

                // Get announcement details
                $.ajax({
                    url: '/announcements/view/' + announcementId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("View Ajax response:", response);
                        $('#view-loading').hide();

                        if (response && response.success) {
                            var announcement = response.data;

                            // Populate modal
                            $('#view_title').text(announcement.title || 'N/A');

                            // Format status with badge
                            var statusBadge = announcement.is_active == 1 ?
                                '<span class="badge bg-success-subtle text-success">Active</span>' :
                                '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
                            $('#view_status').html(statusBadge);

                            // Format visible_to
                            var visibleToMapping = {
                                '1': 'Everyone',
                                '2': 'Registered Users Only',
                                '3': 'Participants Only'
                            };
                            $('#view_visible_to').text(visibleToMapping[announcement.visible_to] || 'Everyone');                            // Set content
                            $('#view_content').html(announcement.content || 'No content provided');

                            // Handle image
                            if (announcement.img_url) {
                                $('#view_image').attr('src', announcement.img_url).show();
                                $('#no_image_text').hide();
                            } else {
                                $('#view_image').hide();
                                $('#no_image_text').show();
                            }

                            // Update the status badge
                            if (announcement.is_active == 1) {
                                $('#view_status_badge').removeClass('bg-danger-subtle text-danger').addClass('bg-success-subtle text-success').html('<i class="ri-checkbox-circle-line me-1"></i>Active');
                            } else {
                                $('#view_status_badge').removeClass('bg-success-subtle text-success').addClass('bg-danger-subtle text-danger').html('<i class="ri-close-circle-line me-1"></i>Inactive');
                            }

                            // Update SEO metadata
                            $('#view_slug').text(announcement.slug || 'No slug defined');
                            $('#view_meta_title').text(announcement.meta_title || 'No meta title defined');
                            $('#view_meta_description').text(announcement.meta_description || 'No meta description defined');
                            
                            // Handle tags
                            if (announcement.tags && announcement.tags.length > 0) {
                                let tagsHtml = '';
                                const tagsList = announcement.tags.split(',');
                                tagsList.forEach(tag => {
                                    if (tag.trim()) {
                                        tagsHtml += `<span class="badge bg-primary-subtle text-primary me-1 mb-1">${tag.trim()}</span>`;
                                    }
                                });
                                $('#view_tags').html(tagsHtml || 'No tags defined');
                            } else {
                                $('#view_tags').html('<span class="text-muted">No tags defined</span>');
                            }

                            $('#view_updated').text(formatDate(announcement.updated_at || 'N/A'));

                            // Set announcement ID for the edit button in view modal
                            $('.view-edit-btn').data('id', announcement.id);
                        } else {
                            console.error("Invalid response:", response);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load announcement details',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("View Ajax error:", xhr.responseText);
                        $('#view-loading').hide();
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while fetching announcement details',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                });
            });

            // Handle delete button click
            $(document).on('click', '.remove-announcement', function(e) {
                e.preventDefault();

                var announcementId = $(this).data('id');
                console.log("Delete button clicked for ID:", announcementId);

                // Set the announcement ID in the hidden form field and show confirmation modal
                $('#delete_announcement_id').val(announcementId);

                // Update the form action with the correct ID
                $('#delete-announcement-form').attr('action', '/announcements/delete/' + announcementId);

                $('#delete-announcement-modal').modal('show');
            });

            // Handle click on edit button in view modal
            $(document).on('click', '.view-edit-btn', function() {
                var announcementId = $(this).data('id');
                $('#view-announcement-modal').modal('hide');

                // Redirect to edit page
                window.location.href = '/announcements/edit/' + announcementId;
            });

            // Helper function to format date
            function formatDate(dateString) {
                if (!dateString || dateString === 'N/A') return 'N/A';

                var date = new Date(dateString);
                if (isNaN(date)) return 'N/A';

                return date.toLocaleDateString('en-US', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }
    </script>
</body>

</html>