<?= $this->include('partials/main') ?>

<head> <?php echo view('partials/title-meta', array('title' => 'Program Photos')); ?>
    <!-- glightbox css -->
    <link rel="stylesheet" href="/assets/libs/glightbox/css/glightbox.min.css">
    <!-- Sweet Alert css-->
    <link href="/assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
    <?= $this->include('partials/head-css') ?>
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Program Photos')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Photo Gallery</h5>
                                    <div class="flex-shrink-0">
                                        <button class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addPhotoModal">
                                            <i class="ri-add-line align-middle me-1"></i> Add New Photo
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Flash messages will be handled by SweetAlert in JS -->
                                    <input type="hidden" id="success_message" value="<?= session()->getFlashdata('success') ?>">
                                    <input type="hidden" id="error_message" value="<?= session()->getFlashdata('error') ?>">

                                    <div class="row gallery-wrapper">
                                        <?php if (empty($program_photos)) : ?>
                                            <div class="col-12 text-center p-5">
                                                <h5 class="text-muted">No photos available for this program</h5>
                                                <p>Click "Add New Photo" button to add photos to the gallery</p>
                                            </div>
                                        <?php else : ?>
                                            <?php foreach ($program_photos as $photo) : ?>
                                                <div class="element-item col-xxl-3 col-xl-4 col-sm-6">
                                                    <div class="gallery-box card">
                                                        <div class="gallery-container">
                                                            <a class="image-popup" href="<?= $photo->img_url ?>" title="<?= $photo->title ?>">
                                                                <img class="gallery-img img-fluid mx-auto" src="<?= $photo->img_url ?>" alt="<?= $photo->title ?>" />
                                                                <div class="gallery-overlay">
                                                                    <h5 class="overlay-caption"><?= $photo->title ?></h5>
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <div class="box-content">
                                                            <div class="d-flex align-items-center mt-1">
                                                                <div class="flex-grow-1 text-muted"><?= $photo->description ?></div>
                                                                <div class="flex-shrink-0">
                                                                    <div class="d-flex gap-3"> <a href="#" class="text-primary edit-photo"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#editPhotoModal"
                                                                            data-id="<?= $photo->id ?>"
                                                                            data-title="<?= $photo->title ?>"
                                                                            data-year="<?= $photo->year ?>"
                                                                            data-description="<?= $photo->description ?>"
                                                                            data-img_url="<?= $photo->img_url ?>"
                                                                            data-is_active="<?= $photo->is_active ?>">
                                                                            <i class="ri-edit-box-line fs-16"></i>
                                                                        </a>
                                                                        <a href="#" class="text-danger delete-photo"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#deletePhotoModal"
                                                                            data-id="<?= $photo->id ?>"
                                                                            data-title="<?= $photo->title ?>">
                                                                            <i class="ri-delete-bin-line fs-16"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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

    <?= $this->include('partials/customizer') ?>

    <!-- Add Photo Modal -->
    <div class="modal fade" id="addPhotoModal" tabindex="-1" aria-labelledby="addPhotoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPhotoModalLabel">Add New Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>                <div class="modal-body">
                    <form id="addPhotoForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="year" class="form-label">Year</label>
                            <input type="number" class="form-control" id="year" name="year" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="photo_file" class="form-label">Upload Image</label>
                            <input type="file" class="form-control" id="photo_file" name="photo_file" accept="image/*" required>
                            <small class="text-muted">Select an image file (JPG, PNG, GIF)</small>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Photo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Photo Modal -->
    <div class="modal fade" id="editPhotoModal" tabindex="-1" aria-labelledby="editPhotoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPhotoModalLabel">Edit Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>                <div class="modal-body">
                    <form id="editPhotoForm" enctype="multipart/form-data">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_year" class="form-label">Year</label>
                            <input type="number" class="form-control" id="edit_year" name="year" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_photo_file" class="form-label">Upload New Image</label>
                            <input type="file" class="form-control" id="edit_photo_file" name="photo_file" accept="image/*">
                            <small class="text-muted">Select a new image file or leave empty to keep current image</small>
                            <input type="hidden" id="edit_img_url" name="img_url">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                        <div class="mb-3">
                            <div class="text-center">
                                <img id="preview_img" src="" alt="Preview" class="img-fluid img-thumbnail" style="max-height: 200px;">
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Photo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Photo Modal -->
    <div class="modal fade" id="deletePhotoModal" tabindex="-1" aria-labelledby="deletePhotoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePhotoModalLabel">Delete Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the photo "<span id="delete_photo_title"></span>"?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="delete_photo_btn" class="btn btn-danger">Delete Photo</button>
                    <input type="hidden" id="delete_photo_id">
                </div>
            </div>
        </div>
    </div> <?= $this->include('partials/vendor-scripts') ?>

    <!-- glightbox js -->
    <script src="/assets/libs/glightbox/js/glightbox.min.js"></script>
    <!-- isotope-layout -->
    <script src="/assets/libs/isotope-layout/isotope.pkgd.min.js"></script>
    <!-- Sweet Alerts js -->
    <script src="/assets/libs/sweetalert2/sweetalert2.min.js"></script>

    <!-- App js -->    <script src="/assets/js/app.js"></script>    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize the lightbox
            const lightbox = GLightbox({
                selector: '.image-popup',
                loop: true,
            });
            
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
            
            // Maximum allowed file size in MB
            const MAX_FILE_SIZE = 2; // 2MB
            
            // File size validation function
            function validateFileSize(fileInput, maxSize) {
                if (!fileInput.files || !fileInput.files[0]) {
                    return true; // No file selected, validation passes
                }
                
                const fileSize = fileInput.files[0].size / 1024 / 1024; // Convert to MB
                if (fileSize > maxSize) {
                    Swal.fire({
                        title: 'File Too Large!',
                        html: `The selected image is <strong>${fileSize.toFixed(2)} MB</strong>.<br>Maximum allowed size is <strong>${maxSize} MB</strong>.`,
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    fileInput.value = ''; // Clear the input
                    return false;
                }
                return true;
            }
            
            // Loading overlay function
            function showLoading(message = 'Processing...') {
                return Swal.fire({
                    title: message,
                    html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
            
            // Display error message from AJAX response
            function displayErrorMessage(errors) {
                let errorMessage = '';
                if (typeof errors === 'object') {
                    // Handle multiple errors from validator
                    for (const key in errors) {
                        errorMessage += errors[key] + '<br>';
                    }
                } else if (Array.isArray(errors)) {
                    // Handle array of error messages
                    errorMessage = errors.join('<br>');
                } else {
                    // Handle single error message
                    errorMessage = errors;
                }
                
                Swal.fire({
                    title: 'Error!',
                    html: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#f06548'
                });
            }
            
            // Function to refresh the gallery after CRUD operations
            function refreshGallery() {
                window.location.reload();
            }
            
            // Add Photo Form AJAX submission
            document.getElementById('addPhotoForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate file size
                const fileInput = document.getElementById('photo_file');
                if (!validateFileSize(fileInput, MAX_FILE_SIZE)) {
                    return false;
                }
                
                // Show loading overlay
                showLoading('Saving photo...');
                
                // Create FormData object
                const formData = new FormData(this);
                
                // Send AJAX request
                fetch('<?= base_url('master-data/program-photos/ajax-create') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        // Success
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        }).then(() => {
                            // Close modal and refresh gallery
                            $('#addPhotoModal').modal('hide');
                            document.getElementById('addPhotoForm').reset();
                            refreshGallery();
                        });
                    } else {
                        // Error
                        displayErrorMessage(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                });
            });
            
            // Add file input change event for validation
            document.getElementById('photo_file').addEventListener('change', function() {
                validateFileSize(this, MAX_FILE_SIZE);
            });
            
            // Edit photo modal
            document.querySelectorAll('.edit-photo').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const title = this.getAttribute('data-title');
                    const year = this.getAttribute('data-year');
                    const description = this.getAttribute('data-description');
                    const imgUrl = this.getAttribute('data-img_url');
                    const isActive = this.getAttribute('data-is_active');

                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_title').value = title;
                    document.getElementById('edit_year').value = year;
                    document.getElementById('edit_description').value = description;
                    document.getElementById('edit_img_url').value = imgUrl;
                    document.getElementById('edit_is_active').checked = (isActive === '1');
                    document.getElementById('preview_img').src = imgUrl;
                });
            });
            
            // Edit Photo Form AJAX submission
            document.getElementById('editPhotoForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate file size if a new file is selected
                const fileInput = document.getElementById('edit_photo_file');
                if (fileInput.files.length > 0 && !validateFileSize(fileInput, MAX_FILE_SIZE)) {
                    return false;
                }
                
                // Show loading overlay
                showLoading('Updating photo...');
                
                // Get photo ID
                const photoId = document.getElementById('edit_id').value;
                
                // Create FormData object
                const formData = new FormData(this);
                
                // Send AJAX request
                fetch(`<?= base_url('master-data/program-photos/ajax-update/') ?>${photoId}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        // Success
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        }).then(() => {
                            // Close modal and refresh gallery
                            $('#editPhotoModal').modal('hide');
                            refreshGallery();
                        });
                    } else {
                        // Error
                        displayErrorMessage(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                });
            });
            
            // Edit form file input validation
            document.getElementById('edit_photo_file').addEventListener('change', function() {
                validateFileSize(this, MAX_FILE_SIZE);
            });
            
            // Delete photo modal setup
            document.querySelectorAll('.delete-photo').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const title = this.getAttribute('data-title');

                    // Update the modal content
                    document.getElementById('delete_photo_title').textContent = title;
                    document.getElementById('delete_photo_id').value = id;
                });
            });
            
            // Delete Photo AJAX request
            document.getElementById('delete_photo_btn').addEventListener('click', function() {
                // Show loading overlay
                showLoading('Deleting photo...');
                
                // Get photo ID
                const photoId = document.getElementById('delete_photo_id').value;
                
                // Send AJAX request
                fetch(`<?= base_url('master-data/program-photos/ajax-delete/') ?>${photoId}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        // Success
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        }).then(() => {
                            // Close modal and refresh gallery
                            $('#deletePhotoModal').modal('hide');
                            refreshGallery();
                        });
                    } else {
                        // Error
                        displayErrorMessage(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                });
            });
        });
    </script>
</body>

</html>