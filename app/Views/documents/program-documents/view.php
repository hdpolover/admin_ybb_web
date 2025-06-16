<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'View Document')); ?>
    <?= $this->include('partials/head-css') ?>

    <!-- Document viewer styles -->
    <style>
        .document-details .title {
            font-weight: 600;
            color: #495057;
        }

        .document-details .value {
            color: #6c757d;
        }

        .document-preview {
            min-height: 500px;
            border: 1px solid #e9e9ef;
            border-radius: 4px;
        }

        .document-preview iframe {
            width: 100%;
            height: 100%;
            min-height: 500px;
            border: none;
        }

        .document-preview .no-preview {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 500px;
            background-color: #f8f9fa;
        }

        .badge-document-type {
            font-size: 0.8rem;
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Documents', 'title' => 'Document Details')); ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h4 class="card-title mb-0 flex-grow-1">
                                        <?= $document->name ?>
                                        <?php if ($document->type): ?>
                                            <span class="badge rounded-pill 
                                            <?php
                                            echo match ($document->type) {
                                                'loa' => 'bg-primary',
                                                'agreement' => 'bg-success',
                                                'complement' => 'bg-warning',
                                                default => 'bg-info'
                                            };
                                            ?> badge-document-type">
                                                <?= ucfirst($document->type) ?>
                                            </span>
                                        <?php endif; ?>
                                    </h4>

                                    <div class="flex-shrink-0">
                                        <a href="/documents/program-documents" class="btn btn-light btn-sm">
                                            <i class="ri-arrow-left-line align-middle me-1"></i> Back to List
                                        </a>

                                        <?php if (!empty($document->file_url) || !empty($document->drive_url)): ?>
                                            <a href="<?= !empty($document->file_url) ? $document->file_url : $document->drive_url ?>"
                                                class="btn btn-primary btn-sm" target="_blank">
                                                <i class="ri-download-2-line align-middle me-1"></i> Download
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="document-details mb-4">
                                                <h5 class="fs-14 mb-3">Document Information</h5>

                                                <div class="table-responsive">
                                                    <table class="table table-borderless mb-0">
                                                        <tbody>
                                                            <tr>
                                                                <th class="ps-0 title" scope="row">Document Name</th>
                                                                <td class="value"><?= $document->name ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th class="ps-0 title" scope="row">Document Type</th>
                                                                <td class="value"><?= ucfirst($document->type ?? 'Not specified') ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th class="ps-0 title" scope="row">Description</th>
                                                                <td class="value"><?= $document->desc ?? 'No description available' ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th class="ps-0 title" scope="row">Upload Type</th>
                                                                <td class="value">
                                                                    <?php if ($document->is_upload == 1): ?>
                                                                        <span class="badge bg-success">Uploaded File</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-info">External Link</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="ps-0 title" scope="row">Generated</th>
                                                                <td class="value">
                                                                    <?php if ($document->is_generated == 1): ?>
                                                                        <span class="badge bg-warning">Yes</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-light text-dark">No</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="ps-0 title" scope="row">Visibility</th>
                                                                <td class="value">
                                                                    <?php if ($document->visibility == 1): ?>
                                                                        <span class="badge bg-primary">Public</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-dark">Private</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="ps-0 title" scope="row">Status</th>
                                                                <td class="value">
                                                                    <?php if ($document->is_active == 1): ?>
                                                                        <span class="badge bg-success">Active</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-danger">Inactive</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="ps-0 title" scope="row">Date Added</th>
                                                                <td class="value"><?= date('d F Y, h:i A', strtotime($document->created_at)) ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th class="ps-0 title" scope="row">Last Updated</th>
                                                                <td class="value"><?= date('d F Y, h:i A', strtotime($document->updated_at)) ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>                                            <div class="d-flex gap-2 mt-4">
                                                <?php if ($document->type === 'loa'): ?>
                                                    <a href="/documents/program-documents/loa-settings/<?= $document->id ?>" class="btn btn-info">
                                                        <i class="ri-file-text-line align-middle me-1"></i>
                                                        <?= isset($hasLoaTemplate) && $hasLoaTemplate ? 'Edit LOA Template' : 'Create LOA Template' ?>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="/documents/program-documents" class="btn btn-secondary">
                                                    <i class="ri-arrow-left-line align-middle me-1"></i> Back to Documents
                                                </a>
                                            </div>
                                        </div>

                                        <div class="col-md-7">
                                            <div class="document-preview">
                                                <?php if (!empty($document->file_url) && isPreviewableFile($document->file_url)): ?>
                                                    <iframe src="<?= $document->file_url ?>" title="<?= $document->name ?>" allowfullscreen></iframe>
                                                <?php elseif (!empty($document->drive_url) && isGoogleDrivePreviewable($document->drive_url)): ?>
                                                    <iframe src="<?= convertToGooglePreviewUrl($document->drive_url) ?>" title="<?= $document->name ?>" allowfullscreen></iframe>
                                                <?php else: ?>
                                                    <div class="no-preview d-flex flex-column align-items-center">
                                                        <i class="ri-file-text-line fs-1 text-muted mb-3"></i>
                                                        <p class="mb-2">No preview available for this document.</p>
                                                        <?php if (!empty($document->file_url) || !empty($document->drive_url)): ?>
                                                            <a href="<?= !empty($document->file_url) ? $document->file_url : $document->drive_url ?>"
                                                                class="btn btn-sm btn-primary" target="_blank">
                                                                <i class="ri-external-link-line align-middle me-1"></i> Open Document
                                                            </a>
                                                        <?php else: ?>
                                                            <p class="text-muted small">No document link available.</p>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
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
    <!-- END layout-wrapper -->    <?= $this->include('partials/vendor-scripts') ?>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>
</body>

</html>

<?php
// Helper functions for document previewing

function isPreviewableFile($url)
{
    $extension = pathinfo($url, PATHINFO_EXTENSION);
    $previewable_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
    return in_array(strtolower($extension), $previewable_extensions);
}

function isGoogleDrivePreviewable($url)
{
    return strpos($url, 'drive.google.com') !== false;
}

function convertToGooglePreviewUrl($url)
{
    // Extract file ID from Google Drive URL
    if (preg_match('/\/d\/(.*?)\//', $url, $matches)) {
        $fileId = $matches[1];
        return "https://drive.google.com/file/d/{$fileId}/preview";
    }

    return $url;
}
?>