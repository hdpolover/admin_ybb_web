<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Program Testimonies')); ?>
    <!-- glightbox css -->
    <link rel="stylesheet" href="/assets/libs/glightbox/css/glightbox.min.css">
    <!-- Sweet Alert css-->
    <link href="/assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
    
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Program Testimonies')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Testimonials</h5>
                                    <div class="flex-shrink-0">
                                        <button class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addTestimonyModal">
                                            <i class="ri-add-line align-middle me-1"></i> Add New Testimony
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Flash messages will be handled by SweetAlert in JS -->
                                    <input type="hidden" id="success_message" value="<?= session()->getFlashdata('success') ?>">
                                    <input type="hidden" id="error_message" value="<?= session()->getFlashdata('error') ?>">                                    <table id="testimonies-datatable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 5%;">#</th>
                                                <th scope="col" style="width: 10%;">Photo</th>
                                                <th scope="col" style="width: 20%;">Person Details</th>
                                                <th scope="col" style="width: 50%;">Testimony</th>
                                                <th scope="col" style="width: 15%;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($testimonies)) : ?>
                                                <?php $index = 0; ?>
                                                <?php foreach ($testimonies as $testimony) : ?>
                                                    <?php $index++; ?>
                                                    <tr>
                                                        <td><?= $index ?></td>
                                                        <td>    
                                                            <?php if (!empty($testimony->img_url)) : ?>
                                                                <img src="<?= $testimony->img_url ?>" alt="<?= $testimony->person_name ?>" class="img-fluid rounded" width="48">
                                                            <?php else : ?>
                                                                <img src="/assets/images/users/avatar-blank.jpg" alt="No Image" class="img-fluid rounded" width="48">
                                                            <?php endif; ?>
                                                        </td>
                                                        <td style="word-break: break-word; white-space: normal;"><?= $testimony->person_name ?> (<?= $testimony->occupation ?? '-' ?>, <?= $testimony->institution ?? '-' ?>)</td>
                                                        <td style="word-break: break-word; white-space: normal;"><?= nl2br($testimony->testimony) ?></td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <div class="edit">
                                                                    <button class="btn btn-sm btn-success edit-testimony" 
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editTestimonyModal"
                                                                        data-id="<?= $testimony->id ?>"
                                                                        data-person_name="<?= $testimony->person_name ?>"
                                                                        data-testimony="<?= htmlspecialchars($testimony->testimony) ?>"
                                                                        data-occupation="<?= $testimony->occupation ?>"
                                                                        data-institution="<?= $testimony->institution ?>"
                                                                        data-img_url="<?= $testimony->img_url ?>"
                                                                        data-is_active="<?= $testimony->is_active ?>"
                                                                        data-bs-tooltip="tooltip" data-bs-placement="top" title="Edit">
                                                                        <i class="ri-pencil-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="remove">
                                                                    <button class="btn btn-sm btn-danger delete-testimony"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#deleteTestimonyModal"
                                                                        data-id="<?= $testimony->id ?>"
                                                                        data-person_name="<?= $testimony->person_name ?>"
                                                                        data-bs-tooltip="tooltip" data-bs-placement="top" title="Delete">
                                                                        <i class="ri-delete-bin-fill"></i>
                                                                    </button>
                                                                </div>
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
            </div>

            <?= $this->include('partials/footer') ?>
        </div>
    </div>

    <!-- Add Testimony Modal -->
    <div class="modal fade" id="addTestimonyModal" tabindex="-1" aria-labelledby="addTestimonyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTestimonyModalLabel">Add New Testimony</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('master-data/program-testimonies/create') ?>" method="post" id="addTestimonyForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="person_name" class="form-label">Person Name</label>
                            <input type="text" class="form-control" id="person_name" name="person_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="occupation" class="form-label">Occupation</label>
                            <input type="text" class="form-control" id="occupation" name="occupation">
                        </div>
                        <div class="mb-3">
                            <label for="institution" class="form-label">Institution</label>
                            <input type="text" class="form-control" id="institution" name="institution">
                        </div>
                        <div class="mb-3">
                            <label for="testimony" class="form-label">Testimony</label>
                            <textarea class="form-control" id="testimony" name="testimony" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="photo_file" class="form-label">Upload Photo</label>
                            <input type="file" class="form-control" id="photo_file" name="photo_file" accept="image/*">
                            <small class="text-muted">Select an image file (JPG, PNG, GIF)</small>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Testimony</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Testimony Modal -->
    <div class="modal fade" id="editTestimonyModal" tabindex="-1" aria-labelledby="editTestimonyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTestimonyModalLabel">Edit Testimony</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post" id="editTestimonyForm" enctype="multipart/form-data">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_person_name" class="form-label">Person Name</label>
                            <input type="text" class="form-control" id="edit_person_name" name="person_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_occupation" class="form-label">Occupation</label>
                            <input type="text" class="form-control" id="edit_occupation" name="occupation">
                        </div>
                        <div class="mb-3">
                            <label for="edit_institution" class="form-label">Institution</label>
                            <input type="text" class="form-control" id="edit_institution" name="institution">
                        </div>
                        <div class="mb-3">
                            <label for="edit_testimony" class="form-label">Testimony</label>
                            <textarea class="form-control" id="edit_testimony" name="testimony" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_photo_file" class="form-label">Upload New Photo</label>
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
                                <img id="preview_img" src="" alt="Preview" class="img-fluid img-thumbnail" style="max-height: 150px; display: none;">
                                <p id="no_image_text" class="text-muted">No image available</p>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Testimony</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Testimony Modal -->
    <div class="modal fade" id="deleteTestimonyModal" tabindex="-1" aria-labelledby="deleteTestimonyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTestimonyModalLabel">Delete Testimony</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the testimony from "<span id="delete_testimony_name"></span>"?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="delete_testimony_link" class="btn btn-danger">Delete Testimony</a>
                </div>
            </div>
        </div>
    </div>    
    
    <?= $this->include('partials/vendor-scripts') ?>

    <!-- glightbox js -->
    <script src="/assets/libs/glightbox/js/glightbox.min.js"></script>
    <!-- Sweet Alerts js -->
    <script src="/assets/libs/sweetalert2/sweetalert2.min.js"></script>

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

    <!-- App js -->
    <script src="/assets/js/app.js"></script>    
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize DataTable
            var testimoniesTable = $('#testimonies-datatable').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true,
                columnDefs: [{
                    orderable: false,
                    targets: [1, 4] // Photo and Action columns are not sortable
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

            // Edit testimony modal
            document.querySelectorAll('.edit-testimony').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const personName = this.getAttribute('data-person_name');
                    const testimony = this.getAttribute('data-testimony');
                    const occupation = this.getAttribute('data-occupation');
                    const institution = this.getAttribute('data-institution');
                    const imgUrl = this.getAttribute('data-img_url');
                    const isActive = this.getAttribute('data-is_active');

                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_person_name').value = personName;
                    document.getElementById('edit_testimony').value = testimony;
                    document.getElementById('edit_occupation').value = occupation || '';
                    document.getElementById('edit_institution').value = institution || '';
                    document.getElementById('edit_img_url').value = imgUrl || '';
                    document.getElementById('edit_is_active').checked = (isActive === '1');

                    const previewImg = document.getElementById('preview_img');
                    const noImageText = document.getElementById('no_image_text');

                    if (imgUrl && imgUrl.trim() !== '') {
                        previewImg.src = imgUrl;
                        previewImg.style.display = 'block';
                        noImageText.style.display = 'none';
                    } else {
                        previewImg.style.display = 'none';
                        noImageText.style.display = 'block';
                    }

                    document.getElementById('editTestimonyForm').action = '<?= base_url('master-data/program-testimonies/update/') ?>' + id;
                });
            });

            // Delete testimony modal
            document.querySelectorAll('.delete-testimony').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const personName = this.getAttribute('data-person_name');

                    // Update the modal content
                    document.getElementById('delete_testimony_name').textContent = personName;
                    document.getElementById('delete_testimony_link').href = '<?= base_url('master-data/program-testimonies/delete/') ?>' + id;
                });
            });
        });
    </script>
</body>

</html>