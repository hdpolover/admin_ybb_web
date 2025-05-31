<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title' => 'View Abstract')); ?>

    <?= $this->include('partials/head-css') ?>

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

                    <?php echo view('partials/page-title', array('pagetitle' => 'Submissions', 'title' => 'View Abstract')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Abstract Details</h5>
                                    <div class="flex-shrink-0">
                                        <a href="<?= base_url('submissions/abstracts-papers') ?>" class="btn btn-secondary waves-effect waves-light me-2">
                                            <i class="ri-arrow-left-line align-middle me-1"></i> Back to List
                                        </a>
                                        <a href="<?= base_url('submissions/abstracts-papers/edit/' . $abstract->id) ?>" class="btn btn-primary waves-effect waves-light">
                                            <i class="ri-pencil-line align-middle me-1"></i> Edit
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <h5 class="text-muted fw-medium">Participant Information</h5>
                                            <div class="p-3 border rounded mb-3">
                                                <p><strong>Name:</strong> <?= $participant ? $participant->full_name : 'N/A' ?></p>
                                                <p><strong>Institution:</strong> <?= $participant ? $participant->institution : 'N/A' ?></p>
                                                <p><strong>Email:</strong> <?= $participant ? $participant->email : 'N/A' ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <h5 class="text-muted fw-medium">Abstract Information</h5>
                                            <div class="p-3 border rounded mb-3">
                                                <p><strong>Topic:</strong> <?= $topic ? $topic->name : 'No topic selected' ?></p>
                                                <p><strong>Status:</strong> 
                                                    <?php 
                                                    $statusText = 'Unknown';
                                                    $badgeClass = 'bg-secondary';
                                                    
                                                    if ($abstract->status === 'draft') {
                                                        $statusText = 'Draft';
                                                        $badgeClass = 'bg-secondary';
                                                    } elseif ($abstract->status === 'submitted') {
                                                        $statusText = 'Submitted';
                                                        $badgeClass = 'bg-primary';
                                                    } elseif ($abstract->status === 'under_review') {
                                                        $statusText = 'Under Review';
                                                        $badgeClass = 'bg-info';
                                                    } elseif ($abstract->status === 'accepted') {
                                                        $statusText = 'Accepted';
                                                        $badgeClass = 'bg-success';
                                                    } elseif ($abstract->status === 'rejected') {
                                                        $statusText = 'Rejected';
                                                        $badgeClass = 'bg-danger';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                                </p>
                                                <p><strong>Submission Date:</strong> <?= date('d M Y', strtotime($abstract->created_at)) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Abstract Versions Section -->
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <h5 class="text-muted fw-medium">Abstract Versions</h5>
                                            <div class="mb-3">
                                                <select id="version-select" class="form-select">
                                                    <?php foreach ($versions as $version): ?>
                                                    <option value="<?= $version->id ?>">Version <?= $version->version_number ?> (<?= date('d M Y', strtotime($version->created_at)) ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            
                                            <?php if (!empty($versions)): ?>
                                            <div class="p-3 border rounded mb-3" id="version-content">
                                                <h4 id="version-title"><?= $versions[0]->title ?></h4>
                                                <div class="mt-3 mb-3 p-3 bg-light rounded">
                                                    <div id="version-content-text"><?= nl2br($versions[0]->content) ?></div>
                                                </div>
                                                <p><strong>Keywords:</strong> <span id="version-keywords"><?= $versions[0]->keywords ?></span></p>
                                            </div>
                                            <?php else: ?>
                                            <div class="alert alert-info">No abstract versions available</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Authors Section -->
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <h5 class="text-muted fw-medium">Authors</h5>
                                            
                                            <?php if (!empty($authors)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Name</th>
                                                            <th>Institution</th>
                                                            <th>Email</th>
                                                            <th>Registered Participant</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($authors as $author): ?>
                                                        <tr>
                                                            <td><?= $author->full_name ?></td>
                                                            <td><?= $author->institution ?></td>
                                                            <td><?= $author->email ?></td>
                                                            <td><?= $author->is_participant ? 'Yes' : 'No' ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php else: ?>
                                            <div class="alert alert-info">No authors available</div>
                                            <?php endif; ?>
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

    <?= $this->include('partials/customizer') ?>

    <?= $this->include('partials/vendor-scripts') ?>

    <script>
        $(document).ready(function() {
            // Version select change event
            $('#version-select').on('change', function() {
                const versionId = $(this).val();
                
                // Find the selected version data
                <?php foreach ($versions as $version): ?>
                if (versionId == <?= $version->id ?>) {
                    $('#version-title').text('<?= addslashes($version->title) ?>');
                    $('#version-content-text').html('<?= addslashes(nl2br($version->content)) ?>');
                    $('#version-keywords').text('<?= addslashes($version->keywords) ?>');
                }
                <?php endforeach; ?>
            });
        });
    </script>

    <!-- App js -->
    <script src="<?= base_url() ?>assets/js/app.js"></script>
</body>

</html>
