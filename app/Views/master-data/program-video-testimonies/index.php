<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Program Video Testimonies')); ?>
    <?= $this->include('partials/head-css') ?>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    
    <!-- Sortable CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css">
    
    <style>
        .video-thumbnail {
            position: relative;
            display: inline-block;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .video-thumbnail img {
            width: 120px;
            height: 68px;
            object-fit: cover;
        }
        
        .video-thumbnail .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50%;
            padding: 8px;
            color: white;
            font-size: 16px;
        }
        
        .sortable-handle {
            cursor: move;
            color: #6c757d;
        }
        
        .sortable-handle:hover {
            color: #495057;
        }
        
        .video-embed-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            max-width: 100%;
            background: #000;
            border-radius: 8px;
        }
        
        .video-embed-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Program Video Testimonies')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Video Testimonials for "<?= $program->name ?>"</h5>
                                    <div class="flex-shrink-0">
                                        <button class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addVideoTestimonyModal">
                                            <i class="ri-add-line align-middle me-1"></i> Add New Video Testimony
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Flash messages will be handled by SweetAlert in JS -->
                                    <input type="hidden" id="success_message" value="<?= session()->getFlashdata('success') ?>">
                                    <input type="hidden" id="error_message" value="<?= session()->getFlashdata('error') ?>">
                                    
                                    <table id="video-testimonies-datatable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 3%;"> 
                                                    <i class="ri-drag-move-line sortable-header-icon" title="Drag to reorder"></i>
                                                </th>
                                                <th scope="col" style="width: 5%;">#</th>
                                                <th scope="col" style="width: 20%;">Video Thumbnail</th>
                                                <th scope="col" style="width: 15%;">YouTube URL</th>
                                                <th scope="col" style="width: 30%;">Description</th>
                                                <th scope="col" style="width: 10%;">Status</th>
                                                <th scope="col" style="width: 17%;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sortable-tbody">
                                            <?php if (!empty($videoTestimonies)) : ?>
                                                <?php $index = 0; ?>
                                                <?php foreach ($videoTestimonies as $videoTestimony) : ?>
                                                    <?php $index++; ?>
                                                    <tr data-id="<?= $videoTestimony->id ?>">
                                                        <td>
                                                            <i class="ri-drag-move-line sortable-handle" title="Drag to reorder"></i>
                                                        </td>
                                                        <td><?= $index ?></td>
                                                        <td>
                                                            <?php if (!empty($videoTestimony->youtube_video_id)) : ?>
                                                                <div class="video-thumbnail">
                                                                    <img src="https://img.youtube.com/vi/<?= $videoTestimony->youtube_video_id ?>/hqdefault.jpg" alt="Video Thumbnail" class="img-fluid">
                                                                    <div class="play-overlay">
                                                                        <i class="ri-play-fill"></i>
                                                                    </div>
                                                                </div>
                                                            <?php else : ?>
                                                                <div class="text-center p-3 bg-light rounded">
                                                                    <i class="ri-video-line ri-2x text-muted"></i>
                                                                    <p class="mt-1 mb-0 text-muted small">No video</p>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="text-break">
                                                                <a href="<?= $videoTestimony->youtube_url ?>" target="_blank" class="text-decoration-none">
                                                                    <small><?= character_limiter($videoTestimony->youtube_url, 50) ?></small>
                                                                    <i class="ri-external-link-line ms-1"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($videoTestimony->description)) : ?>
                                                                <div class="text-muted small">
                                                                    <?= character_limiter($videoTestimony->description, 100) ?>
                                                                </div>
                                                            <?php else : ?>
                                                                <span class="text-muted">No description</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?= $videoTestimony->is_active ? 'bg-success' : 'bg-danger' ?>">
                                                                <?= $videoTestimony->is_active ? 'Active' : 'Inactive' ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <div class="view">
                                                                    <button class="btn btn-sm btn-info view-video" 
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#viewVideoModal"
                                                                        data-video-id="<?= $videoTestimony->youtube_video_id ?>"
                                                                        data-title="Video Testimony #<?= $videoTestimony->id ?>"
                                                                        data-bs-tooltip="tooltip" data-bs-placement="top" title="View Video">
                                                                        <i class="ri-eye-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="edit">
                                                                    <button class="btn btn-sm btn-success edit-video-testimony" 
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editVideoTestimonyModal"
                                                                        data-id="<?= $videoTestimony->id ?>"
                                                                        data-youtube_url="<?= htmlspecialchars($videoTestimony->youtube_url) ?>"
                                                                        data-description="<?= htmlspecialchars($videoTestimony->description ?? '') ?>"
                                                                        data-is_active="<?= $videoTestimony->is_active ?>"
                                                                        data-bs-tooltip="tooltip" data-bs-placement="top" title="Edit">
                                                                        <i class="ri-pencil-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="remove">
                                                                    <button class="btn btn-sm btn-danger delete-video-testimony"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#deleteVideoTestimonyModal"
                                                                        data-id="<?= $videoTestimony->id ?>"
                                                                        data-video-url="<?= htmlspecialchars($videoTestimony->youtube_url) ?>"
                                                                        data-bs-tooltip="tooltip" data-bs-placement="top" title="Delete">
                                                                        <i class="ri-delete-bin-fill"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">No video testimonies found for this program</td>
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

            <?= $this->include('partials/footer') ?>
        </div>
    </div>

    <!-- Add Video Testimony Modal -->
    <div class="modal fade" id="addVideoTestimonyModal" tabindex="-1" aria-labelledby="addVideoTestimonyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addVideoTestimonyModalLabel">Add New Video Testimony</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('master-data/program-video-testimonies/create') ?>" method="post" id="addVideoTestimonyForm">
                        <div class="mb-3">
                            <label for="youtube_url" class="form-label">YouTube URL *</label>
                            <input type="url" class="form-control" id="youtube_url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..." required>
                            <div class="form-text">Supported formats: youtube.com/watch?v=..., youtu.be/..., youtube.com/embed/..., youtube.com/shorts/...</div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Brief description of the video testimony..."></textarea>
                            <div class="form-text">Optional: Add a description to provide context about this video testimony.</div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Video Testimony</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Video Testimony Modal -->
    <div class="modal fade" id="editVideoTestimonyModal" tabindex="-1" aria-labelledby="editVideoTestimonyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editVideoTestimonyModalLabel">Edit Video Testimony</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post" id="editVideoTestimonyForm">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_youtube_url" class="form-label">YouTube URL *</label>
                            <input type="url" class="form-control" id="edit_youtube_url" name="youtube_url" required>
                            <div class="form-text">Supported formats: youtube.com/watch?v=..., youtu.be/..., youtube.com/embed/..., youtube.com/shorts/...</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="4" placeholder="Brief description of the video testimony..."></textarea>
                            <div class="form-text">Optional: Add a description to provide context about this video testimony.</div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">Active</label>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Video Testimony</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Video Modal -->
    <div class="modal fade" id="viewVideoModal" tabindex="-1" aria-labelledby="viewVideoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewVideoModalLabel">Video Testimony</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="video-embed-container">
                        <iframe id="videoFrame" width="100%" height="100%" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Video Testimony Modal -->
    <div class="modal fade" id="deleteVideoTestimonyModal" tabindex="-1" aria-labelledby="deleteVideoTestimonyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteVideoTestimonyModalLabel">Delete Video Testimony</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this video testimony?</p>
                    <p class="text-muted">Video URL: <span id="delete_video_url" class="text-break"></span></p>
                    <p class="text-muted">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/customizer') ?>

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- Required datatable js -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    
    <!-- Sortable JS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>    
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize DataTable
            var videoTestimoniesTable = $('#video-testimonies-datatable').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: false, // Disable default ordering since we use sortable
                columnDefs: [{
                    orderable: false,
                    targets: [0, 2, 6] // Handle, Video, and Action columns are not sortable
                }],
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-squared justify-content-end mb-0");
                    // Initialize tooltips
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-tooltip="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });
                }
            });

            // Initialize Sortable for drag and drop reordering
            if (document.getElementById('sortable-tbody')) {
                new Sortable(document.getElementById('sortable-tbody'), {
                    handle: '.sortable-handle',
                    animation: 150,
                    onEnd: function(evt) {
                        var orderData = {};
                        var rows = document.querySelectorAll('#sortable-tbody tr[data-id]');
                        
                        rows.forEach(function(row, index) {
                            var id = row.getAttribute('data-id');
                            orderData[id] = index + 1;
                        });

                        // Send AJAX request to update order
                        fetch('<?= base_url('master-data/program-video-testimonies/updateOrder') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(orderData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Display order updated successfully',
                                    icon: 'success',
                                    confirmButtonColor: '#0ab39c',
                                    timer: 2000
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message || 'Failed to update order',
                                    icon: 'error',
                                    confirmButtonColor: '#f06548'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while updating the order',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                        });
                    }
                });
            }

            // Show SweetAlert notifications
            const successMessage = document.getElementById('success_message').value;
            const errorMessage = document.getElementById('error_message').value;

            if (successMessage) {
                Swal.fire({
                    title: 'Success!',
                    text: successMessage,
                    icon: 'success',
                    confirmButtonColor: '#0ab39c'
                });
            }

            if (errorMessage) {
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#f06548'
                });
            }

            // Edit video testimony modal
            document.querySelectorAll('.edit-video-testimony').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const youtubeUrl = this.getAttribute('data-youtube_url');
                    const description = this.getAttribute('data-description');
                    const isActive = this.getAttribute('data-is_active');

                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_youtube_url').value = youtubeUrl || '';
                    document.getElementById('edit_description').value = description || '';
                    document.getElementById('edit_is_active').checked = (isActive === '1');

                    // Update form action
                    document.getElementById('editVideoTestimonyForm').action = '<?= base_url('master-data/program-video-testimonies/update') ?>/' + id;
                });
            });

            // View video modal
            document.querySelectorAll('.view-video').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const videoId = this.getAttribute('data-video-id');
                    const title = this.getAttribute('data-title');
                    
                    document.getElementById('viewVideoModalLabel').textContent = title;
                    document.getElementById('videoFrame').src = `https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1&controls=1`;
                });
            });

            // Reset video when modal is closed
            document.getElementById('viewVideoModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('videoFrame').src = '';
            });

            // Delete video testimony modal
            document.querySelectorAll('.delete-video-testimony').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const videoUrl = this.getAttribute('data-video-url');

                    document.getElementById('delete_video_url').textContent = videoUrl;
                    document.getElementById('confirmDeleteBtn').href = '<?= base_url('master-data/program-video-testimonies/delete') ?>/' + id;
                });
            });
        });
    </script>
</body>
</html>