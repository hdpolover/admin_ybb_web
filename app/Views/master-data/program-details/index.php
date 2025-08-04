<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title' => 'Program Details')); ?>

    <?= $this->include('partials/head-css') ?>

    <!-- Quill editor CSS -->
    <link href="/assets/libs/quill/quill.core.css" rel="stylesheet" type="text/css">
    <link href="/assets/libs/quill/quill.bubble.css" rel="stylesheet" type="text/css">
    <link href="/assets/libs/quill/quill.snow.css" rel="stylesheet" type="text/css">

    <!-- Custom styles for this page -->
    <style>
        .info-section {
            margin-bottom: 2rem;
        }

        .info-section h5 {
            font-weight: 600;
            border-bottom: 1px solid #e9e9ef;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            color: #495057;
        }

        .info-label {
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #343a40;
            display: block;
        }

        .info-content {
            font-size: 1rem;
            color: #212529;
            margin-bottom: 1.25rem;
            word-break: break-word;
        }

        .info-card {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            transition: all 0.25s ease;
        }

        .info-card:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            background-color: #fff;
        }

        .media-container {
            display: flex;
            justify-content: center;
            margin: 0.5rem 0 1.25rem;
            border: 1px solid #e9e9ef;
            padding: 0.5rem;
            border-radius: 0.375rem;
        }

        .badge-large {
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
        }

        /* Tab styles */
        .nav-pills .nav-link.active {
            background-color: #405189;
        }

        .nav-pills .nav-link {
            font-weight: 600;
            font-size: 1.05rem;
        }

        .cursor-on-hover {
            cursor: pointer;
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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Program Details')); ?>

                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1"><?= $currentProgram->name ?> Program Details</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-warning btn-sm me-2" onclick="clearProgramCache()">
                                        <i class="ri-refresh-line"></i> Clear Cache
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Nav tabs -->
                                <ul class="nav nav-pills nav-justified mb-4" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active cursor-on-hover" data-bs-toggle="tab" href="#general-details" role="tab" aria-selected="true">
                                            General Information
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link cursor-on-hover" data-bs-toggle="tab" href="#program-specifics" role="tab" aria-selected="false" tabindex="-1">
                                            Program Specifics
                                        </a>
                                    </li>
                                </ul>
                                <!-- Tab panes -->
                                <div class="tab-content text-muted">
                                    <div class="tab-pane active" id="general-details" role="tabpanel">
                                        <!-- Program Identity Section -->
                                        <div class="info-section">
                                            <h5>Program Identity</h5>
                                            <div class="row">
                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Program Category Name</span>
                                                        <div class="info-content"><?= !empty($currentProgramCategory->name) ? $currentProgramCategory->name : 'Not specified' ?></div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Program Type</span>
                                                        <div class="info-content">
                                                            <?php
                                                            $typeName = 'Not specified';
                                                            if (!empty($currentProgramCategory->program_type_id) && !empty($programTypes)) {
                                                                foreach ($programTypes as $type) {
                                                                    if ($type->id == $currentProgramCategory->program_type_id) {
                                                                        $typeName = $type->name;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            echo $typeName;
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Tagline</span>
                                                        <div class="info-content"><?= !empty($currentProgramCategory->tagline) ? $currentProgramCategory->tagline : 'No tagline available' ?></div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Website URL</span>
                                                        <div class="info-content">
                                                            <?php if (!empty($currentProgramCategory->web_url)) : ?>
                                                                <a href="<?= $currentProgramCategory->web_url ?>" target="_blank"><?= $currentProgramCategory->web_url ?></a>
                                                            <?php else : ?>
                                                                No website URL available
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                        <!-- Media Section -->
                                        <div class="info-section">
                                            <h5>Media Assets</h5>
                                            <div class="row">
                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Logo Image</span>
                                                        <?php if (!empty($currentProgramCategory->logo_url)) : ?>
                                                            <div class="media-container">
                                                                <img src="<?= $currentProgramCategory->logo_url ?>"
                                                                    alt="Logo Image" class="img-fluid rounded" style="max-height: 120px;">
                                                            </div>
                                                        <?php else : ?>
                                                            <div class="info-content">No logo image available</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Main Banner Image</span>
                                                        <?php if (!empty($currentProgramCategory->main_banner_url)) : ?>
                                                            <div class="media-container">
                                                                <img src="<?= $currentProgramCategory->main_banner_url ?>"
                                                                    alt="Main Banner Image" class="img-fluid rounded" style="max-height: 180px;">
                                                            </div>
                                                        <?php else : ?>
                                                            <div class="info-content">No main banner image available</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-12 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Main Video URL</span>
                                                        <div class="info-content">
                                                            <?php if (!empty($currentProgramCategory->main_video_url)) : ?>
                                                                <a href="<?= $currentProgramCategory->main_video_url ?>" target="_blank"><?= $currentProgramCategory->main_video_url ?></a>
                                                            <?php else : ?>
                                                                No main video URL available
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Description Section -->
                                        <div class="info-section">
                                            <h5>Program Description</h5>
                                            <div class="info-card">
                                                <span class="info-label">Description</span>
                                                <div class="info-content"><?= !empty($currentProgramCategory->description) ? nl2br($currentProgramCategory->description) : 'No description available' ?></div>
                                            </div>
                                        </div>

                                        <!-- Contact Information Section -->
                                        <div class="info-section">
                                            <h5>Contact Information</h5>
                                            <div class="row">
                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Contact</span>
                                                        <div class="info-content"><?= !empty($currentProgramCategory->contact) ? $currentProgramCategory->contact : 'Not available' ?></div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Location</span>
                                                        <div class="info-content"><?= !empty($currentProgramCategory->location) ? $currentProgramCategory->location : 'Not available' ?></div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Email</span>
                                                        <div class="info-content">
                                                            <?php if (!empty($currentProgramCategory->email)) : ?>
                                                                <a href="mailto:<?= $currentProgramCategory->email ?>"><?= $currentProgramCategory->email ?></a>
                                                            <?php else : ?>
                                                                Not available
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Social Media Section -->
                                        <div class="info-section">
                                            <h5>Social Media</h5>
                                            <div class="row">
                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Instagram</span>
                                                        <div class="info-content">
                                                            <?php if (!empty($currentProgramCategory->instagram)) : ?>
                                                                <a href="<?= $currentProgramCategory->instagram ?>" target="_blank"><?= $currentProgramCategory->instagram ?></a>
                                                            <?php else : ?>
                                                                Not available
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">TikTok</span>
                                                        <div class="info-content">
                                                            <?php if (!empty($currentProgramCategory->tiktok)) : ?>
                                                                <a href="<?= $currentProgramCategory->tiktok ?>" target="_blank"><?= $currentProgramCategory->tiktok ?></a>
                                                            <?php else : ?>
                                                                Not available
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">YouTube</span>
                                                        <div class="info-content">
                                                            <?php if (!empty($currentProgramCategory->youtube)) : ?>
                                                                <a href="<?= $currentProgramCategory->youtube ?>" target="_blank"><?= $currentProgramCategory->youtube ?></a>
                                                            <?php else : ?>
                                                                Not available
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Telegram</span>
                                                        <div class="info-content">
                                                            <?php if (!empty($currentProgramCategory->telegram)) : ?>
                                                                <a href="<?= $currentProgramCategory->telegram ?>" target="_blank"><?= $currentProgramCategory->telegram ?></a>
                                                            <?php else : ?>
                                                                Not available
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Sponsor Canva URL</span>
                                                        <div class="info-content">
                                                            <?php if (!empty($currentProgramCategory->sponsor_url)) : ?>
                                                                <a href="<?= $currentProgramCategory->sponsor_url ?>" target="_blank"><?= $currentProgramCategory->sponsor_url ?></a>
                                                            <?php else : ?>
                                                                Not available
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Additional Information Section -->
                                        <div class="info-section">
                                            <h5>Additional Information</h5>
                                            <div class="info-card">
                                                <span class="info-label">About</span>
                                                <div class="info-content"><?= !empty($currentProgramCategory->about) ? nl2br($currentProgramCategory->about) : 'Not available' ?></div>
                                            </div>

                                            <div class="info-card">
                                                <span class="info-label">Core Values</span>
                                                <div class="info-content"><?= !empty($currentProgramCategory->core_values) ? nl2br($currentProgramCategory->core_values) : 'Not available' ?></div>
                                            </div>

                                            <div class="info-card">
                                                <span class="info-label">Objectives</span>
                                                <div class="info-content"><?= !empty($currentProgramCategory->objectives) ? nl2br($currentProgramCategory->objectives) : 'Not available' ?></div>
                                            </div>

                                            <div class="info-card">
                                                <span class="info-label">Benefits</span>
                                                <div class="info-content"><?= !empty($currentProgramCategory->benefits) ? nl2br($currentProgramCategory->benefits) : 'Not available' ?></div>
                                            </div>
                                        </div>

                                        <div class="text-end mt-4">
                                            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#updateCategoryModal">Update Details</button>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="program-specifics" role="tabpanel">
                                        <!-- Basic Information Section -->
                                        <div class="info-section">
                                            <h5>Basic Information</h5>
                                            <div class="row">
                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Program Name</span>
                                                        <div class="info-content"><?= $currentProgram->name ?? 'Not specified' ?></div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Theme</span>
                                                        <div class="info-content"><?= $currentProgram->theme ?? 'Not specified' ?></div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-12 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Description</span>
                                                        <div class="info-content"><?= $currentProgram->description ?? 'Not specified' ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Dates & Status Section -->
                                        <div class="info-section">
                                            <h5>Dates & Status</h5>
                                            <div class="row">
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="info-card">
                                                        <span class="info-label">Start Date</span>
                                                        <div class="info-content"><?= isset($currentProgram->start_date) ? date('F d, Y', strtotime($currentProgram->start_date)) : 'Not specified' ?></div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4 col-md-6">
                                                    <div class="info-card">
                                                        <span class="info-label">End Date</span>
                                                        <div class="info-content"><?= isset($currentProgram->end_date) ? date('F d, Y', strtotime($currentProgram->end_date)) : 'Not specified' ?></div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4 col-md-6">
                                                    <div class="info-card">
                                                        <span class="info-label">Status</span>
                                                        <div class="info-content">
                                                            <?php if (isset($currentProgram->is_active)): ?>
                                                                <span class="badge badge-large bg-<?= $currentProgram->is_active ? 'success' : 'danger' ?>">
                                                                    <?= $currentProgram->is_active ? 'Active' : 'Inactive' ?>
                                                                </span>
                                                            <?php else: ?>
                                                                Not specified
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4 col-md-6">
                                                    <div class="info-card">
                                                        <span class="info-label">Registration Status</span>
                                                        <div class="info-content">
                                                            <?php if (isset($currentProgram->is_registration_open)): ?>
                                                                <span class="badge badge-large bg-<?= $currentProgram->is_registration_open ? 'success' : 'danger' ?>">
                                                                    <?= $currentProgram->is_registration_open ? 'Open' : 'Closed' ?>
                                                                </span>
                                                            <?php else: ?>
                                                                Not specified
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Media & Assets Section -->
                                        <div class="info-section">
                                            <h5>Media & Assets</h5>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Banner Image</span>
                                                        <?php if (!empty($currentProgram->banner_url)) : ?>
                                                            <div class="media-container">
                                                                <img src="<?= $currentProgram->banner_url ?>"
                                                                    alt="Banner Image" class="img-fluid rounded" style="max-height: 200px;">
                                                            </div>
                                                        <?php else : ?>
                                                            <div class="info-content">No banner image available</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Registration Video URL</span>
                                                        <div class="info-content">
                                                            <?php if (!empty($currentProgram->registration_video_url)) : ?>
                                                                <a href="<?= $currentProgram->registration_video_url ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="ri-video-line me-1"></i> View Video
                                                                </a>
                                                            <?php else : ?>
                                                                Not available
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Twibbon Video URL</span>
                                                        <div class="info-content">
                                                            <?php if (!empty($currentProgram->twibbon_video_url)) : ?>
                                                                <a href="<?= $currentProgram->twibbon_video_url ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="ri-video-line me-1"></i> View Video
                                                                </a>
                                                            <?php else : ?>
                                                                Not available
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">T-Shirt Chart URL</span>
                                                        <div class="info-content">
                                                            <?php if (!empty($currentProgram->tshirt_chart_url)) : ?>
                                                                <a href="<?= $currentProgram->tshirt_chart_url ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="ri-file-chart-line me-1"></i> View T-Shirt Chart
                                                                </a>
                                                            <?php else : ?>
                                                                Not available
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-12">
                                                    <div class="info-card">
                                                        <span class="info-label">Twibbon URL</span>
                                                        <div class="info-content"><?= $currentProgram->twibbon ?? 'Not available' ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Program Content Section -->
                                        <div class="info-section">
                                            <h5>Program Content</h5>
                                            <div class="info-card">
                                                <span class="info-label">Guideline URL</span>
                                                <div class="info-content"><?= $currentProgram->guideline ?? 'No guidelines available' ?></div>
                                            </div>

                                            <div class="info-card">
                                                <span class="info-label">Essay Guideline URL</span>
                                                <div class="info-content"><?= $currentProgram->essay_guideline_url ?? 'No essay guidelines available' ?></div>
                                            </div>

                                            <div class="info-card">
                                                <span class="info-label">Main Essay Question</span>
                                                <div class="info-content"><?= $currentProgram->main_essay_question ?? 'No essay question available' ?></div>
                                            </div>



                                            <div class="info-card">
                                                <span class="info-label">Share Description</span>
                                                <div class="info-content"><?= $currentProgram->share_desc ?? 'Not available' ?></div>
                                            </div>

                                            <div class="info-card">
                                                <span class="info-label">Confirmation Description</span>
                                                <div class="info-content"><?= $currentProgram->confirmation_desc ?? 'Not available' ?></div>
                                            </div>
                                        </div>

                                        <div class="text-end mt-4">
                                            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#updateProgramModal">Update Program Details</button>
                                        </div>
                                    </div>
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

    <!-- Update Category Modal -->
    <?= $this->include('master-data/program-details/specific_update_modal') ?>
    <?= $this->include('master-data/program-details/category_update_modal') ?>

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <!-- quill js -->
    <script src="/assets/libs/quill/quill.min.js"></script>

    <!-- Form submission script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() { // Define a consistent toolbar configuration for all editors
            const quillToolbarOptions = {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'link'],  // Added link here
                        [{
                            'header': 1
                        }, {
                            'header': 2
                        }],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        [{
                            'script': 'sub'
                        }, {
                            'script': 'super'
                        }],
                        [{
                            'indent': '-1'
                        }, {
                            'indent': '+1'
                        }],
                        [{
                            'size': ['small', false, 'large', 'huge']
                        }],
                        [{
                            'header': [1, 2, 3, 4, 5, 6, false]
                        }],
                        [{
                            'color': []
                        }, {
                            'background': []
                        }],
                        [{
                            'font': []
                        }],
                        [{
                            'align': []
                        }],
                        ['clean']
                    ]
                }
            };

            // Initialize Quill editors with consistent configuration
            const aboutEditor = new Quill('#edit_about', quillToolbarOptions);
            const coreValuesEditor = new Quill('#edit_core_values', quillToolbarOptions);
            const objectivesEditor = new Quill('#edit_objectives', quillToolbarOptions);
            const benefitsEditor = new Quill('#edit_benefits', quillToolbarOptions);
            const programDescEditor = new Quill('#edit_program_description', quillToolbarOptions);
            const shareDescEditor = new Quill('#edit_share_desc', quillToolbarOptions);
            const confirmationDescEditor = new Quill('#edit_confirmation_desc', quillToolbarOptions);
        });

        // Cache management function
        function clearProgramCache() {
            Swal.fire({
                title: 'Clear Website Cache?',
                text: "This will clear the cache so that any updates will be immediately visible on the website. This action is safe and recommended after making changes.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Clear Cache',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Clearing Cache...',
                        text: 'Please wait while we clear the website cache.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Clear cache via AJAX
                    fetch('/cache/clear/programs', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cache Cleared!',
                                text: 'Website cache has been cleared successfully. Changes should now be visible on the website.',
                                confirmButtonColor: '#3085d6'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Failed to clear cache',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An unexpected error occurred while clearing cache',
                            confirmButtonColor: '#3085d6'
                        });
                    });
                }
            });
        }
    </script>
</body>

</html>