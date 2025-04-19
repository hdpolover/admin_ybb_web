<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title' => $title)); ?>

    <?= $this->include('partials/head-css') ?>

    <!-- Quill editor CSS -->
    <link href="/assets/libs/quill/quill.core.css" rel="stylesheet" type="text/css">
    <link href="/assets/libs/quill/quill.bubble.css" rel="stylesheet" type="text/css">
    <link href="/assets/libs/quill/quill.snow.css" rel="stylesheet" type="text/css">

    <!-- Dropzone css -->
    <link rel="stylesheet" href="/assets/libs/dropzone/dropzone.css" type="text/css" />

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

                    <?php
                    $pageTitle = isset($isAdd) && $isAdd ? 'Add Announcement' : 'Edit Announcement';
                    echo view('partials/page-title', array('pagetitle' => 'Announcements', 'title' => $pageTitle));
                    ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0"><?= isset($isAdd) && $isAdd ? 'Add New Announcement' : 'Edit Announcement' ?></h4>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Determine the form action based on whether we're adding or editing
                                    $formAction = isset($isAdd) && $isAdd ? '/announcements/create' : '/announcements/update/' . $announcement->id;
                                    ?>
                                    <form id="announcement-form" action="<?= $formAction ?>" method="post" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="title" class="form-label">Title*</label>
                                                    <input type="text" class="form-control" id="title" name="title" value="<?= esc($announcement->title) ?>" required>
                                                    <div class="invalid-feedback">Please provide a title.</div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="content" class="form-label">Content*</label>
                                                    <input type="hidden" name="content" id="content_hidden" value="<?= htmlspecialchars($announcement->content) ?>" style="display: none;">
                                                    <div id="editor-container" style="height: 300px;"></div>
                                                    <div class="invalid-feedback">Please provide content.</div>
                                                </div>
                                                <!-- SEO Section -->
                                                <div class="mt-4">
                                                    <h5>SEO Settings</h5>
                                                    <div class="mb-3">
                                                        <label for="slug" class="form-label">Slug</label>
                                                        <input type="text" class="form-control" id="slug" name="slug" value="<?= esc($announcement->slug ?? '') ?>" readonly>
                                                        <small class="text-muted">Auto-generated from title. Will be used in URLs.</small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="meta_title" class="form-label">Meta Title</label>
                                                        <input type="text" class="form-control" id="meta_title" name="meta_title" value="<?= esc($announcement->meta_title ?? '') ?>">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="meta_description" class="form-label">Meta Description</label>
                                                        <textarea class="form-control" id="meta_description" name="meta_description" rows="3"><?= esc($announcement->meta_description ?? '') ?></textarea>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="tags" class="form-label">Tags</label>
                                                        <input type="text" class="form-control" id="tags" name="tags" value="<?= esc($announcement->tags ?? '') ?>">
                                                        <small class="text-muted">Separate tags with commas.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <!-- Status -->
                                                <div class="card border">
                                                    <div class="card-header bg-light">
                                                        <h5 class="card-title mb-0">Status</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="mb-3">
                                                            <div class="form-check form-switch form-switch-lg">
                                                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?= $announcement->is_active == 1 ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="is_active">Active</label>
                                                            </div>
                                                            <small class="text-muted">Inactive announcements will not be displayed.</small>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="visible_to" class="form-label">Visible To</label>
                                                            <select class="form-select" id="visible_to" name="visible_to">
                                                                <option value="1" <?= ($announcement->visible_to == '1') ? 'selected' : '' ?>>Public</option>
                                                                <option value="2" <?= ($announcement->visible_to == '2') ? 'selected' : '' ?>>Participants</option>
                                                                <option value="3" <?= ($announcement->visible_to == '3') ? 'selected' : '' ?>>Program Participants</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Image Upload -->
                                                <div class="card border mt-3">
                                                    <div class="card-header bg-light">
                                                        <h5 class="card-title mb-0">Featured Image</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="mb-3">
                                                            <div class="dropzone" id="image-dropzone">
                                                                <div class="dz-message needsclick">
                                                                    <div class="mb-3">
                                                                        <i class="display-4 text-muted ri-upload-cloud-2-fill"></i>
                                                                    </div>
                                                                    <h5>Drop image here or click to upload.</h5>
                                                                    <p class="text-muted">Supported formats: JPG, PNG, GIF. Max size: 2MB</p>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" id="img_url_value" name="img_url" value="">
                                                        </div>

                                                        <?php if (!empty($announcement->img_url)) : ?>
                                                            <div class="current-image mb-3">
                                                                <label class="form-label">Current Image:</label>
                                                                <div class="position-relative">
                                                                    <img src="<?= esc($announcement->img_url) ?>" alt="Current Image" class="img-thumbnail" style="max-height: 150px;">
                                                                </div>
                                                                <small class="text-muted">Upload a new image to replace the current one.</small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 text-end">
                                            <a href="/announcements" class="btn btn-light me-2">Cancel</a>
                                            <button type="submit" class="btn btn-primary">
                                                <?= isset($isAdd) && $isAdd ? 'Create Announcement' : 'Update Announcement' ?>
                                            </button>
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
    <!-- END layout-wrapper --> <?= $this->include('partials/vendor-scripts') ?>

    <!-- Dropzone js -->
    <script src="/assets/libs/dropzone/dropzone-min.js"></script>

    <!-- Quill js -->
    <script src="/assets/libs/quill/quill.min.js"></script> 
    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <script type="text/javascript">
        // Disable Dropzone auto-discover to prevent automatic initialization
        Dropzone.autoDiscover = false;

        document.addEventListener('DOMContentLoaded', function() {
            // Function to convert title to slug format
            function slugify(text) {
                return text.toString().toLowerCase()
                    .replace(/\s+/g, '-') // Replace spaces with -
                    .replace(/[^\w\-]+/g, '') // Remove all non-word chars
                    .replace(/\-\-+/g, '-') // Replace multiple - with single -
                    .replace(/^-+/, '') // Trim - from start of text
                    .replace(/-+$/, ''); // Trim - from end of text
            }

            // Get title and slug elements
            const titleInput = document.getElementById('title');
            const slugInput = document.getElementById('slug');

            // Auto-generate slug from title if the slug is empty or hasn't been manually edited
            titleInput.addEventListener('input', function() {
                slugInput.value = slugify(this.value);
            });            // Initialize Quill editor
            var quill = new Quill('#editor-container', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            'color': []
                        }, {
                            'background': []
                        }],
                        [{
                            'header': 1
                        }, {
                            'header': 2
                        }],
                        ['blockquote', 'code-block'],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        [{
                            'indent': '-1'
                        }, {
                            'indent': '+1'
                        }],
                        [{
                            'align': []
                        }],
                        ['link', 'image'],
                        ['clean']
                    ]
                },
                placeholder: 'Write your announcement content here...'
            });
            
            // Set the Quill editor content from the hidden input field
            const contentHidden = document.getElementById('content_hidden');
            if (contentHidden && contentHidden.value) {
                quill.root.innerHTML = contentHidden.value;
            }

            // Initialize Dropzone
            Dropzone.autoDiscover = false;

            const myDropzone = new Dropzone("#image-dropzone", {
                url: window.location.pathname, // Will be overridden in form submit
                autoProcessQueue: false,
                uploadMultiple: false,
                maxFiles: 1,
                maxFilesize: 2, // MB
                acceptedFiles: "image/jpeg,image/png,image/gif",
                addRemoveLinks: true,
                thumbnailWidth: 120,
                thumbnailHeight: 120,
                createImageThumbnails: true,
                dictRemoveFile: "Remove",
                dictFileTooBig: "File is too big ({{filesize}}MB). Max filesize: {{maxFilesize}}MB.",
                dictInvalidFileType: "Invalid file type. Only JPG, PNG and GIF files are allowed."
            });

            <?php if (!empty($announcement->img_url)) : ?>
                // Display existing image as a mockfile in Dropzone
                let existingFile = {
                    name: "Current Image",
                    size: 12345, // Placeholder size
                    accepted: true
                };
                myDropzone.displayExistingFile(existingFile, "<?= esc($announcement->img_url) ?>");
            <?php endif; ?> // Handle form submission with AJAX
            document.getElementById('announcement-form').addEventListener('submit', function(e) {
                e.preventDefault();

                // Get content from Quill editor and update hidden input
                document.getElementById('content_hidden').value = quill.root.innerHTML;

                // Show loading spinner
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we update the announcement',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Create FormData object
                const formData = new FormData(this);

                // Add Dropzone file to the form data if available
                const dropzoneFiles = myDropzone.getAcceptedFiles();
                if (dropzoneFiles.length > 0) {
                    formData.append('img_url', dropzoneFiles[0]);
                }

                // Submit form via AJAX
                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#0ab39c'
                            }).then(() => {
                                // Redirect to announcements list
                                window.location.href = '/announcements';
                            });
                        } else {
                            // Show error message
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to update announcement',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });

                            // Show validation errors if any
                            if (data.errors) {
                                const errorMessages = Object.values(data.errors).join('\n');
                                console.error('Validation errors:', data.errors);

                                // Display validation errors
                                if (errorMessages) {
                                    Swal.fire({
                                        title: 'Validation Error',
                                        text: errorMessages,
                                        icon: 'warning',
                                        confirmButtonColor: '#f7b84b'
                                    });
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Show error message
                        Swal.fire({
                            title: 'Error!',
                            text: 'An unexpected error occurred. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    });
            });

            // Check for flash messages
            <?php if (session()->has('success')): ?>
                Swal.fire({
                    title: 'Success!',
                    text: '<?= session("success") ?>',
                    icon: 'success',
                    confirmButtonColor: '#0ab39c'
                });
            <?php endif; ?>

            <?php if (session()->has('error')): ?>
                Swal.fire({
                    title: 'Error!',
                    text: '<?= session("error") ?>',
                    icon: 'error',
                    confirmButtonColor: '#f06548'
                });
            <?php endif; ?>
        });
    </script>
</body>

</html>