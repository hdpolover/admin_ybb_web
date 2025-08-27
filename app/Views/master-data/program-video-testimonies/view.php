<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title'=>'View Video Testimony')); ?>
    <?= $this->include('partials/head-css') ?>
    
    <style>
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
        
        .video-details-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
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
                    <?php echo view('partials/page-title', array('pagetitle'=>'Video Testimonies', 'title'=>'View Video Testimony')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Video Testimony Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-8 mb-4">
                                            <!-- Video Player -->
                                            <div class="video-embed-container">
                                                <iframe src="https://www.youtube.com/embed/<?= $videoTestimony->youtube_video_id ?>?rel=0&modestbranding=1&controls=1" 
                                                        frameborder="0" 
                                                        allowfullscreen>
                                                </iframe>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <!-- Person and Video Details -->
                                            <div class="video-details-card">
                                                <h4><?= $videoTestimony->person_name ?></h4>
                                                
                                                <?php if (!empty($videoTestimony->occupation)) : ?>
                                                    <p class="text-muted mb-1">
                                                        <i class="ri-briefcase-line me-1"></i>
                                                        <?= $videoTestimony->occupation ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($videoTestimony->institution)) : ?>
                                                    <p class="text-muted mb-3">
                                                        <i class="ri-building-line me-1"></i>
                                                        <?= $videoTestimony->institution ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($videoTestimony->video_title)) : ?>
                                                    <div class="mb-3">
                                                        <h6 class="mb-1">Video Title</h6>
                                                        <p class="mb-0"><?= $videoTestimony->video_title ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($videoTestimony->video_description)) : ?>
                                                    <div class="mb-3">
                                                        <h6 class="mb-1">Description</h6>
                                                        <p class="mb-0"><?= nl2br($videoTestimony->video_description) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($videoTestimony->duration)) : ?>
                                                    <div class="mb-3">
                                                        <h6 class="mb-1">Duration</h6>
                                                        <p class="mb-0">
                                                            <i class="ri-time-line me-1"></i>
                                                            <?= $videoTestimony->duration ?>
                                                        </p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="mb-3">
                                                    <h6 class="mb-1">Status</h6>
                                                    <span class="badge <?= $videoTestimony->is_active ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= $videoTestimony->is_active ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h6 class="mb-1">YouTube URL</h6>
                                                    <a href="<?= $videoTestimony->youtube_url ?>" target="_blank" class="text-primary text-decoration-none">
                                                        <i class="ri-external-link-line me-1"></i>
                                                        View on YouTube
                                                    </a>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h6 class="mb-1">Created</h6>
                                                    <p class="mb-0 text-muted small">
                                                        <?= date('F j, Y \a\t g:i A', strtotime($videoTestimony->created_at)) ?>
                                                    </p>
                                                </div>
                                                
                                                <?php if ($videoTestimony->updated_at !== $videoTestimony->created_at) : ?>
                                                    <div class="mb-3">
                                                        <h6 class="mb-1">Last Updated</h6>
                                                        <p class="mb-0 text-muted small">
                                                            <?= date('F j, Y \a\t g:i A', strtotime($videoTestimony->updated_at)) ?>
                                                        </p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="d-flex gap-2">
                                                <a href="<?= base_url('master-data/program-video-testimonies') ?>" class="btn btn-secondary">
                                                    <i class="ri-arrow-left-line me-1"></i>
                                                    Back to List
                                                </a>
                                                <button class="btn btn-primary edit-video-testimony" 
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#editVideoTestimonyModal"
                                                   data-id="<?= $videoTestimony->id ?>"
                                                   data-person_name="<?= htmlspecialchars($videoTestimony->person_name) ?>"
                                                   data-occupation="<?= htmlspecialchars($videoTestimony->occupation) ?>"
                                                   data-institution="<?= htmlspecialchars($videoTestimony->institution) ?>"
                                                   data-youtube_url="<?= htmlspecialchars($videoTestimony->youtube_url) ?>"
                                                   data-video_title="<?= htmlspecialchars($videoTestimony->video_title) ?>"
                                                   data-video_description="<?= htmlspecialchars($videoTestimony->video_description) ?>"
                                                   data-duration="<?= htmlspecialchars($videoTestimony->duration) ?>"
                                                   data-is_active="<?= $videoTestimony->is_active ?>">
                                                    <i class="ri-pencil-line me-1"></i>
                                                    Edit Video Testimony
                                                </button>
                                                <button class="btn btn-danger delete-video-testimony"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#deleteVideoTestimonyModal"
                                                   data-id="<?= $videoTestimony->id ?>"
                                                   data-person_name="<?= htmlspecialchars($videoTestimony->person_name) ?>">
                                                    <i class="ri-delete-bin-line me-1"></i>
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?= $this->include('partials/footer') ?>
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_person_name" class="form-label">Person Name *</label>
                                    <input type="text" class="form-control" id="edit_person_name" name="person_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_occupation" class="form-label">Occupation</label>
                                    <input type="text" class="form-control" id="edit_occupation" name="occupation">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_institution" class="form-label">Institution</label>
                                    <input type="text" class="form-control" id="edit_institution" name="institution">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_duration" class="form-label">Duration</label>
                                    <input type="text" class="form-control" id="edit_duration" name="duration" placeholder="e.g., 2:30">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_youtube_url" class="form-label">YouTube URL *</label>
                            <input type="url" class="form-control" id="edit_youtube_url" name="youtube_url" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_video_title" class="form-label">Video Title</label>
                            <input type="text" class="form-control" id="edit_video_title" name="video_title">
                        </div>
                        <div class="mb-3">
                            <label for="edit_video_description" class="form-label">Video Description</label>
                            <textarea class="form-control" id="edit_video_description" name="video_description" rows="3"></textarea>
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

    <!-- Delete Video Testimony Modal -->
    <div class="modal fade" id="deleteVideoTestimonyModal" tabindex="-1" aria-labelledby="deleteVideoTestimonyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteVideoTestimonyModalLabel">Delete Video Testimony</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the video testimony from "<span id="delete_person_name"></span>"?</p>
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

    <!-- App js -->
    <script src="/assets/js/app.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Edit video testimony modal
            document.querySelectorAll('.edit-video-testimony').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const personName = this.getAttribute('data-person_name');
                    const occupation = this.getAttribute('data-occupation');
                    const institution = this.getAttribute('data-institution');
                    const youtubeUrl = this.getAttribute('data-youtube_url');
                    const videoTitle = this.getAttribute('data-video_title');
                    const videoDescription = this.getAttribute('data-video_description');
                    const duration = this.getAttribute('data-duration');
                    const isActive = this.getAttribute('data-is_active');

                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_person_name').value = personName;
                    document.getElementById('edit_occupation').value = occupation || '';
                    document.getElementById('edit_institution').value = institution || '';
                    document.getElementById('edit_youtube_url').value = youtubeUrl || '';
                    document.getElementById('edit_video_title').value = videoTitle || '';
                    document.getElementById('edit_video_description').value = videoDescription || '';
                    document.getElementById('edit_duration').value = duration || '';
                    document.getElementById('edit_is_active').checked = (isActive === '1');

                    // Update form action
                    document.getElementById('editVideoTestimonyForm').action = '<?= base_url('master-data/program-video-testimonies/update') ?>/' + id;
                });
            });

            // Delete video testimony modal
            document.querySelectorAll('.delete-video-testimony').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const personName = this.getAttribute('data-person_name');

                    document.getElementById('delete_person_name').textContent = personName;
                    document.getElementById('confirmDeleteBtn').href = '<?= base_url('master-data/program-video-testimonies/delete') ?>/' + id;
                });
            });
        });
    </script>
</body>
</html>