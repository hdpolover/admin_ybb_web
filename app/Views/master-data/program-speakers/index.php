<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Program Speakers')); ?>

    <?= $this->include('partials/head-css') ?>

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <!-- Dropzone css -->
    <link rel="stylesheet" href="/assets/libs/dropzone/dropzone.css" type="text/css" />

    <!-- jQuery UI for sortable -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">

    <style>
        .speaker-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
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

        #program-speakers-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }
        
        /* SweetAlert customizations */
        .swal2-popup {
            font-size: 0.875rem;
        }
        
        .swal2-actions {
            margin-top: 1.5rem;
        }

        /* Speaker stats cards */
        .stats-card {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: box-shadow 0.15s ease-in-out;
        }

        .stats-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .stats-card .text-primary {
            color: #0d6efd !important;
        }

        .stats-card .text-warning {
            color: #ffc107 !important;
        }

        .stats-card .text-info {
            color: #0dcaf0 !important;
        }

        .stats-card .text-success {
            color: #198754 !important;
        }

        /* Photo upload preview */
        .photo-preview {
            max-width: 150px;
            max-height: 150px;
            border-radius: 0.375rem;
            margin-top: 0.5rem;
        }

        /* URL input styling */
        .url-input {
            padding-left: 2.5rem;
        }

        .url-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        /* Keynote speaker styling */
        .keynote-indicator {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            font-size: 0.75rem;
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
        }

        /* Form sections */
        .form-section {
            border-left: 3px solid #0d6efd;
            padding-left: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-section h6 {
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 1rem;
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Program Speakers')); ?>

                    <!-- Speaker Statistics -->
                    <?php if ($program && isset($stats)): ?>
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-user-line fs-2 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0 text-dark"><?= $stats->total ?></h5>
                                        <p class="mb-0 text-muted">Total Speakers</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-vip-crown-line fs-2 text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0 text-dark"><?= $stats->keynote ?></h5>
                                        <p class="mb-0 text-muted">Keynote Speakers</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-group-line fs-2 text-info"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0 text-dark"><?= $stats->regular ?></h5>
                                        <p class="mb-0 text-muted">Regular Speakers</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-presentation-line fs-2 text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0 text-dark"><?= $stats->with_sessions ?></h5>
                                        <p class="mb-0 text-muted">With Sessions</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Program Speakers</h4>
                                    <div class="flex-shrink-0">
                                        <?php if ($program): ?>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-speaker-modal">
                                                <i class="ri-add-line align-bottom me-1"></i> Add Speaker
                                            </button>
                                        <?php else: ?>
                                            <div class="alert alert-warning mb-0">
                                                <i class="ri-error-warning-line me-1 align-middle"></i>
                                                Please select a program first
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <table id="program-speakers-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Title</th>
                                                <th scope="col">Organization</th>
                                                <th scope="col">Type</th>
                                                <th scope="col">Session</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (isset($speakers) && is_array($speakers)) : ?>
                                                <?php foreach ($speakers as $index => $speaker) : ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td><?= esc($speaker->name) ?></td>
                                                        <td><?= esc($speaker->title ?? 'N/A') ?></td>
                                                        <td><?= esc($speaker->organization ?? 'N/A') ?></td>
                                                        <td>
                                                            <?php if ($speaker->is_keynote): ?>
                                                                <span class="badge bg-warning-subtle text-warning">Keynote</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-info-subtle text-info">Regular</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= esc($speaker->session_title ?? 'No Session') ?></td>
                                                        <td>
                                                            <?php if ($speaker->is_active): ?>
                                                                <span class="badge bg-success-subtle text-success">Active</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <div class="view">
                                                                    <button type="button" class="btn btn-sm btn-info view-speaker" data-id="<?= $speaker->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                                                        <i class="ri-eye-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="edit">
                                                                    <button type="button" class="btn btn-sm btn-success edit-speaker" data-id="<?= $speaker->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                                        <i class="ri-pencil-fill"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="remove">
                                                                    <button type="button" class="btn btn-sm btn-danger delete-speaker" data-id="<?= $speaker->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                                        <i class="ri-delete-bin-fill"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">
                                                        <i class="ri-user-line fs-2 d-block mb-2"></i>
                                                        No speakers found for this program
                                                    </td>
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
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- View Speaker Modal -->
    <div class="modal fade" id="view-speaker-modal" tabindex="-1" aria-labelledby="view-speaker-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-loading" id="view-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="view-speaker-modal-label">Speaker Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <img id="view_photo" src="/assets/images/users/avatar-1.jpg" alt="Speaker Photo" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            <div id="view_keynote_badge"></div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="fw-semibold">Name</h6>
                                        <p id="view_name">Loading...</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="fw-semibold">Title</h6>
                                        <p id="view_title">Loading...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="fw-semibold">Organization</h6>
                                        <p id="view_organization">Loading...</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="fw-semibold">Email</h6>
                                        <p id="view_email">Loading...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <h6 class="fw-semibold">Biography</h6>
                                <p id="view_bio">Loading...</p>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="fw-semibold">Expertise Areas</h6>
                                        <p id="view_expertise">Loading...</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="fw-semibold">Social Links</h6>
                                        <div id="view_social_links">Loading...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <h6 class="fw-semibold">Session Title</h6>
                                <p id="view_session_title">Loading...</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <h6 class="fw-semibold">Session Time</h6>
                                <p id="view_session_time">Loading...</p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-semibold">Session Description</h6>
                        <p id="view_session_description">Loading...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success view-edit-btn" data-id="">Edit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Speaker Modal -->
    <div class="modal fade" id="add-speaker-modal" tabindex="-1" aria-labelledby="add-speaker-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-speaker-modal-label">Add New Speaker</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/program-speakers/create" method="post" id="add-speaker-form" enctype="multipart/form-data">
                    <div class="modal-body">
                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <h6><i class="ri-user-line me-2"></i>Basic Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Speaker Name*</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback">Please enter speaker name.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title/Position</label>
                                        <input type="text" class="form-control" id="title" name="title" placeholder="e.g., CEO, Professor, Director">
                                        <div class="invalid-feedback">Please enter speaker title.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="organization" class="form-label">Organization</label>
                                        <input type="text" class="form-control" id="organization" name="organization" placeholder="e.g., Company, University">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email">
                                        <div class="invalid-feedback">Please enter a valid email.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="is_keynote" class="form-label">Speaker Type*</label>
                                        <select class="form-select" id="is_keynote" name="is_keynote" required>
                                            <option value="0" selected>Regular Speaker</option>
                                            <option value="1">Keynote Speaker</option>
                                        </select>
                                        <div class="invalid-feedback">Please select speaker type.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="is_active" class="form-label">Status*</label>
                                        <select class="form-select" id="is_active" name="is_active" required>
                                            <option value="1" selected>Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                        <div class="invalid-feedback">Please select status.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label">Biography</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Brief biography of the speaker..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="expertise_areas" class="form-label">Expertise Areas</label>
                                <textarea class="form-control" id="expertise_areas" name="expertise_areas" rows="2" placeholder="e.g., Machine Learning, Data Science, Leadership"></textarea>
                                <small class="text-muted">Separate multiple areas with commas</small>
                            </div>
                        </div>

                        <!-- Photo Upload Section -->
                        <div class="form-section">
                            <h6><i class="ri-image-line me-2"></i>Speaker Photo</h6>
                            <div class="mb-3">
                                <div class="dropzone" id="add-photo-dropzone">
                                    <div class="dz-message needsclick">
                                        <div class="mb-3">
                                            <i class="display-4 text-muted ri-upload-cloud-2-fill"></i>
                                        </div>
                                        <h5>Drop speaker photo here or click to upload.</h5>
                                        <p class="text-muted">Supported formats: JPG, PNG, GIF. Max size: 5MB</p>
                                    </div>
                                </div>
                                <input type="hidden" id="photo_url_value" name="photo_url" value="">
                            </div>
                        </div>

                        <!-- Social Media Section -->
                        <div class="form-section">
                            <h6><i class="ri-links-line me-2"></i>Social Media & Links</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                        <div class="position-relative">
                                            <i class="ri-linkedin-line url-icon"></i>
                                            <input type="url" class="form-control url-input" id="linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/in/username">
                                        </div>
                                        <div class="invalid-feedback">Please enter a valid LinkedIn URL.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="instagram_url" class="form-label">Instagram URL</label>
                                        <div class="position-relative">
                                            <i class="ri-instagram-line url-icon"></i>
                                            <input type="url" class="form-control url-input" id="instagram_url" name="instagram_url" placeholder="https://instagram.com/username">
                                        </div>
                                        <div class="invalid-feedback">Please enter a valid Instagram URL.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Session Information Section -->
                        <div class="form-section">
                            <h6><i class="ri-presentation-line me-2"></i>Session Information</h6>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="session_title" class="form-label">Session Title</label>
                                        <input type="text" class="form-control" id="session_title" name="session_title" placeholder="Title of the presentation/talk">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="session_time" class="form-label">Session Time</label>
                                        <input type="datetime-local" class="form-control" id="session_time" name="session_time">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="session_description" class="form-label">Session Description</label>
                                <textarea class="form-control" id="session_description" name="session_description" rows="3" placeholder="Brief description of the session content..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Speaker</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Speaker Modal -->
    <div class="modal fade" id="edit-speaker-modal" tabindex="-1" aria-labelledby="edit-speaker-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-loading" id="edit-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="edit-speaker-modal-label">Edit Speaker</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/master-data/program-speakers/update/" method="post" id="edit-speaker-form" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="edit_speaker_id" name="id">

                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <h6><i class="ri-user-line me-2"></i>Basic Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_name" class="form-label">Speaker Name*</label>
                                        <input type="text" class="form-control" id="edit_name" name="name" required>
                                        <div class="invalid-feedback">Please enter speaker name.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_title" class="form-label">Title/Position</label>
                                        <input type="text" class="form-control" id="edit_title" name="title" placeholder="e.g., CEO, Professor, Director">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_organization" class="form-label">Organization</label>
                                        <input type="text" class="form-control" id="edit_organization" name="organization" placeholder="e.g., Company, University">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="edit_email" name="email">
                                        <div class="invalid-feedback">Please enter a valid email.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_is_keynote" class="form-label">Speaker Type*</label>
                                        <select class="form-select" id="edit_is_keynote" name="is_keynote" required>
                                            <option value="0">Regular Speaker</option>
                                            <option value="1">Keynote Speaker</option>
                                        </select>
                                        <div class="invalid-feedback">Please select speaker type.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_is_active" class="form-label">Status*</label>
                                        <select class="form-select" id="edit_is_active" name="is_active" required>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                        <div class="invalid-feedback">Please select status.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_bio" class="form-label">Biography</label>
                                <textarea class="form-control" id="edit_bio" name="bio" rows="4" placeholder="Brief biography of the speaker..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="edit_expertise_areas" class="form-label">Expertise Areas</label>
                                <textarea class="form-control" id="edit_expertise_areas" name="expertise_areas" rows="2" placeholder="e.g., Machine Learning, Data Science, Leadership"></textarea>
                                <small class="text-muted">Separate multiple areas with commas</small>
                            </div>
                        </div>

                        <!-- Photo Upload Section -->
                        <div class="form-section">
                            <h6><i class="ri-image-line me-2"></i>Speaker Photo</h6>
                            <div class="mb-3">
                                <div class="dropzone" id="edit-photo-dropzone">
                                    <div class="dz-message needsclick">
                                        <div class="mb-3">
                                            <i class="display-4 text-muted ri-upload-cloud-2-fill"></i>
                                        </div>
                                        <h5>Drop speaker photo here or click to upload.</h5>
                                        <p class="text-muted">Supported formats: JPG, PNG, GIF. Max size: 5MB</p>
                                    </div>
                                </div>
                                <input type="hidden" id="edit_photo_url_value" name="photo_url" value="">
                            </div>
                            
                            <div class="current-photo mb-3" id="current-photo-container" style="display: none;">
                                <label class="form-label">Current Photo:</label>
                                <div class="position-relative">
                                    <img id="current-photo-img" src="" alt="Current Photo" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                                <small class="text-muted">Upload a new photo to replace the current one.</small>
                            </div>
                        </div>

                        <!-- Social Media Section -->
                        <div class="form-section">
                            <h6><i class="ri-links-line me-2"></i>Social Media & Links</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_linkedin_url" class="form-label">LinkedIn URL</label>
                                        <div class="position-relative">
                                            <i class="ri-linkedin-line url-icon"></i>
                                            <input type="url" class="form-control url-input" id="edit_linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/in/username">
                                        </div>
                                        <div class="invalid-feedback">Please enter a valid LinkedIn URL.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_instagram_url" class="form-label">Instagram URL</label>
                                        <div class="position-relative">
                                            <i class="ri-instagram-line url-icon"></i>
                                            <input type="url" class="form-control url-input" id="edit_instagram_url" name="instagram_url" placeholder="https://instagram.com/username">
                                        </div>
                                        <div class="invalid-feedback">Please enter a valid Instagram URL.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Session Information Section -->
                        <div class="form-section">
                            <h6><i class="ri-presentation-line me-2"></i>Session Information</h6>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="edit_session_title" class="form-label">Session Title</label>
                                        <input type="text" class="form-control" id="edit_session_title" name="session_title" placeholder="Title of the presentation/talk">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_session_time" class="form-label">Session Time</label>
                                        <input type="datetime-local" class="form-control" id="edit_session_time" name="session_time">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_session_description" class="form-label">Session Description</label>
                                <textarea class="form-control" id="edit_session_description" name="session_description" rows="3" placeholder="Brief description of the session content..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update Speaker</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    <!-- Dropzone js -->
    <script src="/assets/libs/dropzone/dropzone-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <script src="/assets/js/pages/datatables.init.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <!-- Custom JavaScript -->
    <script type="text/javascript">
        // Global variables for dropzones
        let addPhotoDropzone;
        let editPhotoDropzone;

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
                initializeSpeakerFunctions();
            } else {
                console.error("jQuery is not loaded!");
            }
        });

        function initializeSpeakerFunctions() {
            // Disable Dropzone auto-discover to prevent automatic initialization
            Dropzone.autoDiscover = false;

            // Initialize Add Speaker Photo Dropzone
            addPhotoDropzone = new Dropzone("#add-photo-dropzone", {
                url: window.location.pathname, // Will be overridden in form submit
                autoProcessQueue: false,
                uploadMultiple: false,
                maxFiles: 1,
                maxFilesize: 5, // MB
                acceptedFiles: "image/jpeg,image/jpg,image/png,image/gif",
                addRemoveLinks: true,
                thumbnailWidth: 120,
                thumbnailHeight: 120,
                createImageThumbnails: true,
                dictRemoveFile: "Remove",
                dictFileTooBig: "File is too big ({{filesize}}MB). Max filesize: {{maxFilesize}}MB.",
                dictInvalidFileType: "Invalid file type. Only JPG, PNG and GIF files are allowed.",
                init: function() {
                    console.log("Add photo dropzone initialized");
                },
                success: function(file, response) {
                    console.log("Add photo upload success:", response);
                },
                error: function(file, errorMessage) {
                    console.error("Add photo upload error:", errorMessage);
                }
            });

            // Initialize Edit Speaker Photo Dropzone
            editPhotoDropzone = new Dropzone("#edit-photo-dropzone", {
                url: window.location.pathname, // Will be overridden in form submit
                autoProcessQueue: false,
                uploadMultiple: false,
                maxFiles: 1,
                maxFilesize: 5, // MB
                acceptedFiles: "image/jpeg,image/jpg,image/png,image/gif",
                addRemoveLinks: true,
                thumbnailWidth: 120,
                thumbnailHeight: 120,
                createImageThumbnails: true,
                dictRemoveFile: "Remove",
                dictFileTooBig: "File is too big ({{filesize}}MB). Max filesize: {{maxFilesize}}MB.",
                dictInvalidFileType: "Invalid file type. Only JPG, PNG and GIF files are allowed.",
                init: function() {
                    console.log("Edit photo dropzone initialized");
                },
                success: function(file, response) {
                    console.log("Edit photo upload success:", response);
                },
                error: function(file, errorMessage) {
                    console.error("Edit photo upload error:", errorMessage);
                }
            });

            // Initialize DataTable with standard configuration
            var speakerTable = $('#program-speakers-table').DataTable({
                responsive: true,
                lengthChange: false,
                pageLength: 10,
                searching: true,
                ordering: true,
                columnDefs: [{
                    orderable: false,
                    targets: [0, 4, 6, 7] // Number, Type, Status, and Action columns are not sortable
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

            // Use event delegation for view button
            $(document).on('click', '.view-speaker', function(e) {
                e.preventDefault();

                var speakerId = $(this).data('id');
                console.log("View button clicked for ID:", speakerId);

                // Show modal first
                $('#view-speaker-modal').modal('show');
                $('#view-loading').show();

                // Get speaker details
                $.ajax({
                    url: '/master-data/program-speakers/getSpeaker/' + speakerId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("View Ajax response:", response);
                        $('#view-loading').hide();

                        if (response && response.data) {
                            var speaker = response.data;

                            // Populate modal
                            $('#view_name').text(speaker.name || 'N/A');
                            $('#view_title').text(speaker.title || 'N/A');
                            $('#view_organization').text(speaker.organization || 'N/A');
                            $('#view_email').text(speaker.email || 'N/A');
                            $('#view_bio').text(speaker.bio || 'No biography provided');
                            $('#view_expertise').text(speaker.expertise_areas || 'Not specified');

                            // Photo
                            if (speaker.photo_url) {
                                $('#view_photo').attr('src', speaker.photo_url);
                            } else {
                                $('#view_photo').attr('src', '/assets/images/users/avatar-1.jpg');
                            }

                            // Keynote badge
                            if (speaker.is_keynote == 1) {
                                $('#view_keynote_badge').html('<span class="badge bg-warning-subtle text-warning">Keynote Speaker</span>');
                            } else {
                                $('#view_keynote_badge').html('<span class="badge bg-info-subtle text-info">Regular Speaker</span>');
                            }

                            // Social links
                            var socialLinks = '';
                            if (speaker.linkedin_url) {
                                socialLinks += '<a href="' + speaker.linkedin_url + '" target="_blank" class="btn btn-sm btn-outline-primary me-2"><i class="ri-linkedin-line"></i> LinkedIn</a>';
                            }
                            if (speaker.instagram_url) {
                                socialLinks += '<a href="' + speaker.instagram_url + '" target="_blank" class="btn btn-sm btn-outline-danger me-2"><i class="ri-instagram-line"></i> Instagram</a>';
                            }
                            if (speaker.email) {
                                socialLinks += '<a href="mailto:' + speaker.email + '" class="btn btn-sm btn-outline-secondary"><i class="ri-mail-line"></i> Email</a>';
                            }
                            $('#view_social_links').html(socialLinks || 'No social links provided');

                            // Session information
                            $('#view_session_title').text(speaker.session_title || 'No session assigned');
                            $('#view_session_description').text(speaker.session_description || 'No session description');
                            
                            // Format session time
                            var sessionTime = 'Not scheduled';
                            if (speaker.session_time) {
                                sessionTime = new Date(speaker.session_time).toLocaleString('en-US', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                            }
                            $('#view_session_time').text(sessionTime);

                            // Set speaker ID for the edit button in view modal
                            $('.view-edit-btn').data('id', speaker.id);
                        } else {
                            console.error("Invalid response:", response);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load speaker details',
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
                            text: 'An error occurred while fetching speaker details',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                });
            });

            // Handle edit button click
            $(document).on('click', '.edit-speaker', function(e) {
                e.preventDefault();

                var speakerId = $(this).data('id');
                console.log("Edit button clicked for ID:", speakerId);

                // Show modal first
                $('#edit-speaker-modal').modal('show');
                $('#edit-loading').show();

                // Get speaker details
                $.ajax({
                    url: '/master-data/program-speakers/getSpeaker/' + speakerId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#edit-loading').hide();
                        console.log("Edit Ajax response:", response);

                        if (response && response.data) {
                            var speaker = response.data;

                            // Set form action URL with speaker ID
                            $('#edit-speaker-form').attr('action', '/master-data/program-speakers/update/' + speaker.id);

                            // Populate form
                            $('#edit_speaker_id').val(speaker.id);
                            $('#edit_name').val(speaker.name);
                            $('#edit_title').val(speaker.title);
                            $('#edit_organization').val(speaker.organization);
                            $('#edit_email').val(speaker.email);
                            $('#edit_bio').val(speaker.bio);
                            $('#edit_expertise_areas').val(speaker.expertise_areas);
                            $('#edit_linkedin_url').val(speaker.linkedin_url);
                            $('#edit_instagram_url').val(speaker.instagram_url);
                            $('#edit_session_title').val(speaker.session_title);
                            $('#edit_session_description').val(speaker.session_description);
                            $('#edit_is_keynote').val(speaker.is_keynote);
                            $('#edit_is_active').val(speaker.is_active);

                            // Handle session time
                            if (speaker.session_time_formatted) {
                                $('#edit_session_time').val(speaker.session_time_formatted);
                            }

                            // Handle photo display
                            editPhotoDropzone.removeAllFiles();
                            if (speaker.photo_url && speaker.photo_url.trim() !== '') {
                                // Show current photo
                                $('#current-photo-img').attr('src', speaker.photo_url);
                                $('#current-photo-container').show();
                                
                                // Display existing image as a mockfile in Dropzone
                                let existingFile = {
                                    name: "Current Photo",
                                    size: 12345, // Placeholder size
                                    accepted: true
                                };
                                editPhotoDropzone.displayExistingFile(existingFile, speaker.photo_url);
                            } else {
                                $('#current-photo-container').hide();
                            }
                        } else {
                            console.error("Invalid response:", response);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load speaker details for editing',
                                icon: 'error',
                                confirmButtonColor: '#f06548'
                            });
                            $('#edit-speaker-modal').modal('hide');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#edit-loading').hide();
                        console.error("Edit Ajax error:", xhr.responseText);
                        
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while fetching speaker details',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                        $('#edit-speaker-modal').modal('hide');
                    }
                });
            });

            // Reset add modal when hidden
            $('#add-speaker-modal').on('hidden.bs.modal', function () {
                $('#add-speaker-form')[0].reset();
                $('#add-speaker-form').removeClass('was-validated');
                addPhotoDropzone.removeAllFiles();
            });

            // Reset edit modal when hidden
            $('#edit-speaker-modal').on('hidden.bs.modal', function () {
                $('#edit-speaker-form')[0].reset();
                $('#edit-speaker-form').removeClass('was-validated');
                editPhotoDropzone.removeAllFiles();
                $('#current-photo-container').hide();
            });

            // Handle delete button click
            $(document).on('click', '.delete-speaker', function(e) {
                e.preventDefault();

                var speakerId = $(this).data('id');
                console.log("Delete button clicked for ID:", speakerId);

                // Show SweetAlert confirmation
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this deletion!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!',
                    confirmButtonColor: '#f06548',
                    cancelButtonColor: '#74788d',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait while we delete the speaker.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                                // Send AJAX delete request
                                $.ajax({
                                    url: '/master-data/program-speakers/delete/' + speakerId,
                                    type: 'GET',
                                    success: function(response) {
                                        // Show success message
                                        Swal.fire({
                                            title: 'Deleted!',
                                            text: 'The speaker has been deleted successfully.',
                                            icon: 'success',
                                            confirmButtonColor: '#0ab39c'
                                        }).then(() => {
                                            // Reload the page
                                            window.location.reload();
                                        });
                                    },
                                    error: function(xhr, status, error) {
                                        console.error("Delete Ajax error:", xhr.responseText);
                                        Swal.fire({
                                            title: 'Error!',
                                            text: 'Failed to delete the speaker. Please try again.',
                                            icon: 'error',
                                            confirmButtonColor: '#f06548'
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            });

            // Handle click on edit button in view modal
            $(document).on('click', '.view-edit-btn', function() {
                var speakerId = $(this).data('id');
                $('#view-speaker-modal').modal('hide');

                // Wait for view modal to close before opening edit modal
                setTimeout(function() {
                    $('.edit-speaker[data-id="' + speakerId + '"]').trigger('click');
                }, 500);
            });

            // Form validation and submission with SweetAlert for add speaker form
            $('#add-speaker-form').on('submit', function(e) {
                e.preventDefault();
                
                if ($(this)[0].checkValidity() === false) {
                    e.stopPropagation();
                    $(this).addClass('was-validated');
                    
                    // Show SweetAlert for validation errors
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    return;
                }
                
                $(this).addClass('was-validated');
                
                // Show loading state
                Swal.fire({
                    title: 'Creating Speaker...',
                    text: 'Please wait while we process your request.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                        
                        // Create FormData object for file upload
                        var formData = new FormData(this);
                        
                        // Add Dropzone file to the form data if available
                        const dropzoneFiles = addPhotoDropzone.getAcceptedFiles();
                        console.log("Dropzone files:", dropzoneFiles);
                        
                        if (dropzoneFiles.length > 0) {
                            console.log("Adding file to FormData:", dropzoneFiles[0].name);
                            formData.append('photo_url', dropzoneFiles[0]);
                        } else {
                            console.log("No files in dropzone");
                        }
                        
                        // Debug FormData contents
                        console.log("FormData contents:");
                        for (var pair of formData.entries()) {
                            console.log(pair[0] + ': ' + pair[1]);
                        }
                        
                        // Send AJAX request
                        $.ajax({
                            url: $(this).attr('action'),
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            success: function(response) {
                                $('#add-speaker-modal').modal('hide');
                                
                                if (response && response.success) {
                                    // Reset form and dropzone
                                    $('#add-speaker-form')[0].reset();
                                    $('#add-speaker-form').removeClass('was-validated');
                                    addPhotoDropzone.removeAllFiles();
                                    
                                    // Show success message
                                    Swal.fire({
                                        title: 'Success!',
                                        text: response.message || 'Speaker created successfully.',
                                        icon: 'success',
                                        confirmButtonColor: '#0ab39c'
                                    }).then(() => {
                                        // Reload the page
                                        window.location.reload();
                                    });
                                } else {
                                    // Show error message
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.message || 'Failed to create the speaker.',
                                        icon: 'error',
                                        confirmButtonColor: '#f06548'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Create Ajax error:", xhr.responseText);
                                let errorMessage = 'Failed to create the speaker. Please try again.';
                                
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response && response.message) {
                                        errorMessage = response.message;
                                    }
                                    
                                    // Check if we have specific field errors
                                    if (response && response.errors) {
                                        let errorDetails = [];
                                        for (const field in response.errors) {
                                            errorDetails.push(response.errors[field]);
                                        }
                                        if (errorDetails.length > 0) {
                                            errorMessage = errorDetails.join('<br>');
                                        }
                                    }
                                } catch (e) {
                                    console.error("Error parsing response:", e);
                                }
                                
                                Swal.fire({
                                    title: 'Error!',
                                    html: errorMessage,
                                    icon: 'error',
                                    confirmButtonColor: '#f06548'
                                });
                            }
                        });
                    }
                });
            });

            // Form validation and submission with SweetAlert for edit speaker form
            $('#edit-speaker-form').on('submit', function(e) {
                e.preventDefault();
                
                if ($(this)[0].checkValidity() === false) {
                    e.stopPropagation();
                    $(this).addClass('was-validated');
                    
                    // Show SweetAlert for validation errors
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                    return;
                }
                
                $(this).addClass('was-validated');
                
                // Show loading state
                Swal.fire({
                    title: 'Updating Speaker...',
                    text: 'Please wait while we process your request.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                        
                        // Create FormData object for file upload
                        var formData = new FormData(this);
                        
                        // Add Dropzone file to the form data if available
                        const dropzoneFiles = editPhotoDropzone.getAcceptedFiles();
                        console.log("Edit dropzone files:", dropzoneFiles);
                        
                        if (dropzoneFiles.length > 0) {
                            console.log("Adding edit file to FormData:", dropzoneFiles[0].name);
                            formData.append('photo_url', dropzoneFiles[0]);
                        } else {
                            console.log("No files in edit dropzone");
                        }
                        
                        // Debug FormData contents
                        console.log("Edit FormData contents:");
                        for (var pair of formData.entries()) {
                            console.log(pair[0] + ': ' + pair[1]);
                        }
                        
                        // Send AJAX request
                        $.ajax({
                            url: $(this).attr('action'),
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            success: function(response) {
                                $('#edit-speaker-modal').modal('hide');
                                
                                if (response && response.success) {
                                    // Show success message
                                    Swal.fire({
                                        title: 'Success!',
                                        text: response.message || 'Speaker updated successfully.',
                                        icon: 'success',
                                        confirmButtonColor: '#0ab39c'
                                    }).then(() => {
                                        // Reload the page
                                        window.location.reload();
                                    });
                                } else {
                                    // Show error message
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.message || 'Failed to update the speaker.',
                                        icon: 'error',
                                        confirmButtonColor: '#f06548'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Update Ajax error:", xhr.responseText);
                                let errorMessage = 'Failed to update the speaker. Please try again.';
                                
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response && response.message) {
                                        errorMessage = response.message;
                                    }
                                    
                                    // Check if we have specific field errors
                                    if (response && response.errors) {
                                        let errorDetails = [];
                                        for (const field in response.errors) {
                                            errorDetails.push(response.errors[field]);
                                        }
                                        if (errorDetails.length > 0) {
                                            errorMessage = errorDetails.join('<br>');
                                        }
                                    }
                                } catch (e) {
                                    console.error("Error parsing response:", e);
                                }
                                
                                Swal.fire({
                                    title: 'Error!',
                                    html: errorMessage,
                                    icon: 'error',
                                    confirmButtonColor: '#f06548'
                                });
                            }
                        });
                    }
                });
            });
        }
    </script>
</body>

</html>