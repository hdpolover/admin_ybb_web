<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title'=>'Edit Document')); ?>
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
                    <?php echo view('partials/page-title', array('pagetitle'=>'Documents', 'title'=>'Edit Document')); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title mb-0 flex-grow-1">Edit Document</h5>
                                        <div class="flex-shrink-0">
                                            <a href="/documents/program-documents/view/<?= $document->id ?>" class="btn btn-light btn-sm">
                                                <i class="ri-arrow-left-line align-middle me-1"></i> Back to Document
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if(session()->has('error')): ?>
                                        <div class="alert alert-danger"><?= session('error') ?></div>
                                    <?php endif; ?>
                                    
                                    <form action="/program-documents/update/<?= $document->id ?>" method="post" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Document Name*</label>
                                                    <input type="text" class="form-control" id="name" name="name" 
                                                           value="<?= $document->name ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="type" class="form-label">Document Type*</label>
                                                    <select class="form-select" id="type" name="type">
                                                        <option value="">Not specified</option>
                                                        <option value="loa" <?= $document->type === 'loa' ? 'selected' : '' ?>>
                                                            Letter of Acceptance (LOA)
                                                        </option>
                                                        <option value="agreement" <?= $document->type === 'agreement' ? 'selected' : '' ?>>
                                                            Agreement Letter
                                                        </option>
                                                        <option value="complement" <?= $document->type === 'complement' ? 'selected' : '' ?>>
                                                            Complementary Document
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="desc" class="form-label">Description</label>
                                            <textarea class="form-control" id="desc" name="desc" rows="3"><?= $document->desc ?></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label d-block">Document Source</label>
                                                    <div class="btn-group" role="group">
                                                        <input type="radio" class="btn-check" name="url_type" id="url_type_file" 
                                                               value="file" <?= $document->is_upload == 1 ? 'checked' : '' ?>>
                                                        <label class="btn btn-outline-primary" for="url_type_file">Upload File</label>
                                                        
                                                        <input type="radio" class="btn-check" name="url_type" id="url_type_drive" 
                                                               value="drive" <?= $document->is_upload == 0 ? 'checked' : '' ?>>
                                                        <label class="btn btn-outline-primary" for="url_type_drive">External Link</label>
                                                    </div>
                                                </div>
                                                
                                                <div id="file_section" class="mb-3 <?= $document->is_upload == 0 ? 'd-none' : '' ?>">
                                                    <label for="document_file" class="form-label">Upload New Document</label>
                                                    <input type="file" class="form-control" id="document_file" name="document_file">
                                                    <small class="text-muted">Leave empty to keep the current file</small>
                                                    
                                                    <?php if(!empty($document->file_url)): ?>
                                                    <div class="mt-2">
                                                        <span class="badge bg-info">Current file:</span>
                                                        <a href="<?= $document->file_url ?>" target="_blank" class="text-primary">
                                                            <?= basename($document->file_url) ?>
                                                        </a>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div id="drive_section" class="mb-3 <?= $document->is_upload == 1 ? 'd-none' : '' ?>">
                                                    <label for="drive_url" class="form-label">External Document Link</label>
                                                    <input type="url" class="form-control" id="drive_url" name="drive_url" 
                                                           value="<?= $document->drive_url ?>" placeholder="https://drive.google.com/file/...">
                                                    <small class="text-muted">Enter Google Drive or other external document link</small>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="visibility" class="form-label">Document Visibility</label>
                                                    <select class="form-select" id="visibility" name="visibility">
                                                        <option value="1" <?= $document->visibility == 1 ? 'selected' : '' ?>>Public</option>
                                                        <option value="0" <?= $document->visibility == 0 ? 'selected' : '' ?>>Private</option>
                                                    </select>
                                                    <small class="text-muted">Public documents are visible to all users</small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="is_active" class="form-label">Status</label>
                                                    <select class="form-select" id="is_active" name="is_active">
                                                        <option value="1" <?= $document->is_active == 1 ? 'selected' : '' ?>>Active</option>
                                                        <option value="0" <?= $document->is_active == 0 ? 'selected' : '' ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-end gap-2 mt-4">
                                            <a href="/documents/program-documents/view/<?= $document->id ?>" class="btn btn-light">Cancel</a>
                                            <button type="submit" class="btn btn-primary">Update Document</button>
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
    <!-- END layout-wrapper -->

    <?= $this->include('partials/vendor-scripts') ?>
    
    <!-- App js -->
    <script src="/assets/js/app.js"></script>
    
    <script>
        $(document).ready(function() {
            // Handle document source type toggle
            $('input[name="url_type"]').on('change', function() {
                if (this.value === 'file') {
                    $('#file_section').removeClass('d-none');
                    $('#drive_section').addClass('d-none');
                } else {
                    $('#file_section').addClass('d-none');
                    $('#drive_section').removeClass('d-none');
                }
            });
        });
    </script>
</body>
</html>
