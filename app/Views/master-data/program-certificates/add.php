<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Add Certificate Template')); ?>
    <?= $this->include('partials/head-css') ?>

    <!-- Quill Editor CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <!-- File Upload CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <style>
        .certificate-designer {
            position: relative;
            border: 2px dashed #ddd;
            border-radius: 8px;
            background: #f8f9fa;
            min-height: 600px;
            overflow: hidden;
        }

        .certificate-template {
            position: relative;
            width: 100%;
            height: 100%;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .content-block {
            position: absolute;
            border: 2px solid #007bff;
            background: rgba(255, 255, 255, 0.9);
            cursor: move;
            min-width: 100px;
            min-height: 30px;
            padding: 5px;
            border-radius: 4px;
            user-select: none;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .content-block:hover {
            border-color: #0056b3;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .content-block.selected {
            border-color: #28a745;
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .block-controls {
            position: absolute;
            top: -30px;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 2px;
        }

        .block-controls button {
            border: none;
            background: none;
            padding: 2px 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .template-upload-area {
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .template-upload-area:hover {
            border-color: #0056b3;
            background: #e9ecef;
        }

        .template-upload-area.dragover {
            border-color: #28a745;
            background: #d4edda;
        }

        .preview-panel {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            height: 600px;
            overflow-y: auto;
        }

        .toolbar {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .block-type-btn {
            margin-right: 10px;
            margin-bottom: 5px;
        }

        .properties-panel {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            height: 300px;
            overflow-y: auto;
        }

        .coordinate-display {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        #quill-editor {
            height: 150px;
        }

        #block-quill-editor {
            height: 120px;
        }

        .pdf-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 400px;
            border: 2px dashed #28a745;
            border-radius: 8px;
            margin: 20px;
            background: rgba(40, 167, 69, 0.05);
            position: relative;
            z-index: 1;
        }

        .pdf-placeholder p {
            margin: 10px 0;
            font-weight: 500;
        }

        .pdf-placeholder i {
            color: #28a745;
        }
    </style>
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Add Certificate Template')); ?>

                    <!-- Flash Messages -->
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ri-checkbox-circle-line me-3 align-middle"></i> <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-3 align-middle"></i> <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Validation Errors -->
                    <?php if (session()->getFlashdata('validation')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-3 align-middle"></i>
                            <strong>Validation Errors:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach (session()->getFlashdata('validation') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="/master-data/program-certificates/create" method="post" enctype="multipart/form-data" id="certificate-form">
                        <div class="row">
                            <!-- Left Panel - Form -->
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Certificate Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="award_id" class="form-label">Award*</label>
                                            <select class="form-select" id="award_id" name="award_id" required>
                                                <option value="">Select Award</option>
                                                <?php foreach ($awards as $award): ?>
                                                    <option value="<?= $award['id'] ?>"><?= esc($award['title']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="template_file" class="form-label">Certificate Template File*</label>
                                            <div class="template-upload-area" id="template-upload">
                                                <i class="ri-upload-cloud-2-line fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">Click or drag to upload template</p>
                                                <input type="file" id="template_file" name="template_file" accept="image/*,application/pdf" required style="display: none;">
                                            </div>
                                            <small class="text-muted">Upload a background image (JPEG, PNG, GIF) or PDF template for your certificate.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="issue_date" class="form-label">Issue Date</label>
                                            <input type="date" class="form-control" id="issue_date" name="issue_date">
                                        </div>

                                        <div class="mb-3">
                                            <label for="published_at" class="form-label">Publish Date</label>
                                            <input type="datetime-local" class="form-control" id="published_at" name="published_at">
                                        </div>

                                        <div class="mb-3">
                                            <label for="is_active" class="form-label">Status*</label>
                                            <select class="form-select" id="is_active" name="is_active" required>
                                                <option value="1" selected>Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Content Block Tools -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Content Block Tools</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="toolbar">
                                            <button type="button" class="btn btn-outline-primary block-type-btn" data-type="text">
                                                <i class="ri-text"></i> Add Text
                                            </button>
                                            <button type="button" class="btn btn-outline-success block-type-btn" data-type="participant_name">
                                                <i class="ri-user-line"></i> Participant Name
                                            </button>
                                            <button type="button" class="btn btn-outline-info block-type-btn" data-type="award_title">
                                                <i class="ri-award-line"></i> Award Title
                                            </button>
                                            <button type="button" class="btn btn-outline-warning block-type-btn" data-type="program_name">
                                                <i class="ri-bookmark-line"></i> Program Name
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary block-type-btn" data-type="date">
                                                <i class="ri-calendar-line"></i> Date
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Block Properties -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Block Properties</h5>
                                    </div>
                                    <div class="card-body properties-panel" id="properties-panel">
                                        <p class="text-muted">Select a content block to edit its properties</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Panel - Designer & Preview -->
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">Certificate Designer</h5>
                                        <div>
                                            <button type="button" class="btn btn-outline-secondary me-2" id="preview-btn">
                                                <i class="ri-eye-line"></i> Preview
                                            </button>
                                            <button type="button" class="btn btn-outline-info me-2" id="debug-btn" title="Debug file upload">
                                                <i class="ri-bug-line"></i> Debug
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ri-save-line"></i> Save Certificate
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="certificate-designer" id="certificate-designer">
                                            <div class="certificate-template" id="certificate-template">
                                                <div class="template-placeholder text-center" style="padding: 100px 20px; color: #6c757d;">
                                                    <i class="ri-image-line fs-1 mb-3"></i>
                                                    <p class="mb-0">Upload a template file to start designing your certificate</p>
                                                    <small class="text-muted">Content blocks will be positioned over your template</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden field to store content blocks -->
                        <input type="hidden" name="content_blocks" id="content_blocks">
                    </form>
                </div>
            </div>

            <?= $this->include('partials/footer') ?>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="preview-modal" tabindex="-1" aria-labelledby="preview-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="preview-modal-label">Certificate Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="preview-panel" id="certificate-preview">
                        <!-- Preview will be generated here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- Quill Editor JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    
    <!-- Dropzone JS -->
    <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/assets/js/app.js"></script>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            let contentBlocks = [];
            let selectedBlock = null;
            let blockCounter = 0;
            let uploadedFile = null; // Store the uploaded file

            // Force file restoration function
            function ensureFileInInput() {
                if (uploadedFile && !templateFile.files[0]) {
                    try {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(uploadedFile);
                        templateFile.files = dataTransfer.files;
                        console.log('Force restored file to input:', uploadedFile.name);
                        return true;
                    } catch (error) {
                        console.error('Force file restoration failed:', error);
                        return false;
                    }
                }
                return templateFile.files[0] ? true : false;
            }

            // Initialize Quill editor for rich text editing
            const quill = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'Enter text content...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'size': ['small', false, 'large'] }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }]
                    ]
                }
            });

            // File preservation check - run every 2 seconds to ensure file stays in input
            setInterval(function() {
                if (uploadedFile && !templateFile.files[0]) {
                    console.log('File input was cleared, restoring from uploadedFile');
                    try {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(uploadedFile);
                        templateFile.files = dataTransfer.files;
                    } catch (error) {
                        console.error('Error in periodic file restoration:', error);
                    }
                }
            }, 2000);

            // Template upload handling
            const templateUpload = document.getElementById('template-upload');
            const templateFile = document.getElementById('template_file');
            const certificateTemplate = document.getElementById('certificate-template');

            templateUpload.addEventListener('click', () => templateFile.click());
            templateUpload.addEventListener('dragover', handleDragOver);
            templateUpload.addEventListener('drop', handleDrop);
            templateFile.addEventListener('change', handleFileSelect);

            function handleDragOver(e) {
                e.preventDefault();
                templateUpload.classList.add('dragover');
            }

            function handleDrop(e) {
                e.preventDefault();
                templateUpload.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    
                    // Store the file reference immediately
                    uploadedFile = file;
                    certificateTemplate.dataset.fileSelected = 'true';
                    certificateTemplate.dataset.fileName = file.name;
                    certificateTemplate.dataset.fileSize = file.size;
                    certificateTemplate.dataset.fileType = file.type;
                    
                    // Set the file to the input element
                    try {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        templateFile.files = dataTransfer.files;
                        
                        console.log('File dropped and stored:', {
                            name: file.name,
                            size: file.size,
                            type: file.type
                        });
                        
                        handleFile(file);
                    } catch (error) {
                        console.error('Error setting file to input:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'File Upload Error',
                            text: 'There was an issue processing the dropped file. Please try selecting the file manually.',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            }

            function handleFileSelect(e) {
                const file = e.target.files[0];
                if (file) {
                    // Store the file reference immediately
                    uploadedFile = file;
                    certificateTemplate.dataset.fileSelected = 'true';
                    certificateTemplate.dataset.fileName = file.name;
                    certificateTemplate.dataset.fileSize = file.size;
                    certificateTemplate.dataset.fileType = file.type;
                    
                    console.log('File selected and stored:', {
                        name: file.name,
                        size: file.size,
                        type: file.type
                    });
                    
                    handleFile(file);
                }
            }

            function handleFile(file) {
                if (!file.type.startsWith('image/') && file.type !== 'application/pdf') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: 'Please select an image file (JPEG, PNG, GIF) or PDF document',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Show loading for file processing
                Swal.fire({
                    title: 'Processing Template...',
                    text: 'Please wait while we process your template file.',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                if (file.type === 'application/pdf') {
                    // Handle PDF upload
                    // Clear placeholder and preserve existing content blocks
                    const existingBlocks = certificateTemplate.querySelectorAll('.content-block');
                    certificateTemplate.innerHTML = `
                        <div class="pdf-placeholder">
                            <i class="ri-file-pdf-line fs-1 text-success"></i>
                            <p class="text-success">PDF Template uploaded</p>
                            <p class="text-muted">Content blocks can be positioned over this area</p>
                            <small class="text-info">File: ${file.name}</small>
                        </div>
                    `;
                    
                    // Restore existing blocks
                    existingBlocks.forEach(block => {
                        certificateTemplate.appendChild(block);
                    });
                    
                    certificateTemplate.style.backgroundImage = 'none';
                    certificateTemplate.style.backgroundColor = '#f8f9fa';
                    certificateTemplate.style.minHeight = '600px';
                    certificateTemplate.dataset.templateType = 'pdf';
                    certificateTemplate.dataset.templateUrl = file.name; // Store filename for identification
                    certificateTemplate.dataset.fileName = file.name; // Store filename
                    templateUpload.innerHTML = `
                        <i class="ri-check-line fs-1 text-success"></i>
                        <p class="text-success mt-2">PDF template uploaded successfully</p>
                        <small class="text-muted">File: ${file.name}</small>
                    `;

                    // Close loading and show success
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'PDF Template Uploaded!',
                        text: 'Your PDF template has been uploaded successfully.',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    // Handle image upload
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Clear any placeholder and preserve existing content blocks
                        const existingBlocks = certificateTemplate.querySelectorAll('.content-block');
                        certificateTemplate.innerHTML = ''; // Clear placeholder
                        
                        // Restore existing blocks
                        existingBlocks.forEach(block => {
                            certificateTemplate.appendChild(block);
                        });
                        
                        certificateTemplate.style.backgroundImage = `url(${e.target.result})`;
                        certificateTemplate.style.backgroundSize = 'contain';
                        certificateTemplate.style.backgroundRepeat = 'no-repeat';
                        certificateTemplate.style.backgroundPosition = 'center';
                        certificateTemplate.style.backgroundColor = 'transparent';
                        certificateTemplate.style.minHeight = '600px';
                        certificateTemplate.dataset.templateType = 'image';
                        certificateTemplate.dataset.templateUrl = file.name; // Store filename for identification
                        certificateTemplate.dataset.fileName = file.name; // Store filename
                        
                        templateUpload.innerHTML = `
                            <i class="ri-check-line fs-1 text-success"></i>
                            <p class="text-success mt-2">Template uploaded successfully</p>
                            <small class="text-muted">File: ${file.name}</small>
                        `;

                        // Close loading and show success
                        Swal.close();
                        Swal.fire({
                            icon: 'success',
                            title: 'Image Template Uploaded!',
                            text: 'Your image template has been uploaded successfully.',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    };
                    reader.readAsDataURL(file);
                }
            }

            // Content block creation
            document.querySelectorAll('.block-type-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const type = this.dataset.type;
                    createContentBlock(type);
                });
            });

            function createContentBlock(type, x = 50, y = 50, value = '') {
                blockCounter++;
                const blockId = `block-${blockCounter}`;
                
                const block = {
                    id: blockId,
                    type: type,
                    value: value || getDefaultValue(type),
                    x: x,
                    y: y,
                    font_size: 14,
                    font_family: 'Arial',
                    font_weight: 'normal',
                    text_align: 'left',
                    color: '#000000',
                    is_active: 1,
                    is_deleted: 0
                };

                contentBlocks.push(block);
                renderContentBlock(block);
                selectBlock(blockId);

                // Show success feedback
                Swal.fire({
                    icon: 'success',
                    title: 'Block Added!',
                    text: `${type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())} block added successfully`,
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }

            function getDefaultValue(type) {
                switch(type) {
                    case 'participant_name': return '{{participant_name}}';
                    case 'award_title': return '{{award_title}}';
                    case 'program_name': return '{{program_name}}';
                    case 'date': return '{{date}}';
                    default: return 'Sample Text';
                }
            }

            function renderContentBlock(block) {
                const blockElement = document.createElement('div');
                blockElement.className = 'content-block';
                blockElement.id = block.id;
                blockElement.style.left = block.x + 'px';
                blockElement.style.top = block.y + 'px';
                blockElement.style.fontSize = block.font_size + 'px';
                blockElement.style.fontFamily = block.font_family;
                blockElement.style.fontWeight = block.font_weight;
                blockElement.style.textAlign = block.text_align;
                blockElement.style.color = block.color;
                blockElement.style.zIndex = '10'; // Ensure blocks are above template
                
                // Render content based on type
                let contentHtml = '';
                if (block.type === 'text') {
                    // For text blocks, allow HTML content
                    contentHtml = block.value;
                } else {
                    // For placeholder blocks, escape HTML
                    contentHtml = escapeHtml(block.value);
                }
                
                blockElement.innerHTML = `
                    <div class="block-controls" style="display: none;">
                        <button type="button" onclick="editBlock('${block.id}')" title="Edit">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button type="button" onclick="deleteBlock('${block.id}')" title="Delete">
                            <i class="ri-delete-bin-line text-danger"></i>
                        </button>
                    </div>
                    <div class="block-content">${contentHtml}</div>
                    <div class="coordinate-display">${block.x}, ${block.y}</div>
                `;

                // Make draggable
                makeDraggable(blockElement, block);

                // Add click handler
                blockElement.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectBlock(block.id);
                });

                certificateTemplate.appendChild(blockElement);
            }

            // Helper function to escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function makeDraggable(element, block) {
                let isDragging = false;
                let startX, startY, initialX, initialY;

                element.addEventListener('mousedown', initDrag);

                function initDrag(e) {
                    isDragging = true;
                    startX = e.clientX;
                    startY = e.clientY;
                    initialX = block.x;
                    initialY = block.y;

                    document.addEventListener('mousemove', drag);
                    document.addEventListener('mouseup', stopDrag);
                }

                function drag(e) {
                    if (!isDragging) return;

                    const deltaX = e.clientX - startX;
                    const deltaY = e.clientY - startY;
                    
                    block.x = Math.max(0, initialX + deltaX);
                    block.y = Math.max(0, initialY + deltaY);

                    element.style.left = block.x + 'px';
                    element.style.top = block.y + 'px';
                    
                    // Update coordinate display
                    const coordDisplay = element.querySelector('.coordinate-display');
                    if (coordDisplay) {
                        coordDisplay.textContent = `${block.x}, ${block.y}`;
                    }
                }

                function stopDrag() {
                    isDragging = false;
                    document.removeEventListener('mousemove', drag);
                    document.removeEventListener('mouseup', stopDrag);
                }
            }

            function selectBlock(blockId) {
                // Remove previous selection
                document.querySelectorAll('.content-block').forEach(el => {
                    el.classList.remove('selected');
                    el.querySelector('.block-controls').style.display = 'none';
                });

                // Select new block
                const blockElement = document.getElementById(blockId);
                if (blockElement) {
                    blockElement.classList.add('selected');
                    blockElement.querySelector('.block-controls').style.display = 'block';
                    selectedBlock = contentBlocks.find(b => b.id === blockId);
                    showBlockProperties(selectedBlock);
                }
            }

            function showBlockProperties(block) {
                const propertiesPanel = document.getElementById('properties-panel');
                
                // Determine if this block type should use rich text editor
                const useRichText = block.type === 'text';
                
                let contentEditor = '';
                if (useRichText) {
                    contentEditor = `
                        <label class="form-label">Content (Rich Text)</label>
                        <div id="block-quill-editor" style="height: 120px;"></div>
                        <input type="hidden" id="block-value" value="${block.value}">
                    `;
                } else {
                    contentEditor = `
                        <label class="form-label">Content</label>
                        <input type="text" class="form-control" id="block-value" value="${block.value}" ${block.type !== 'text' ? 'readonly' : ''}>
                        <small class="text-muted">This is a dynamic field that will be replaced with actual data</small>
                    `;
                }
                
                propertiesPanel.innerHTML = `
                    <div class="mb-3">
                        ${contentEditor}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">X Position</label>
                            <input type="number" class="form-control" id="block-x" value="${block.x}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Y Position</label>
                            <input type="number" class="form-control" id="block-y" value="${block.y}">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Font Size</label>
                            <input type="number" class="form-control" id="block-font-size" value="${block.font_size}" min="8" max="72">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <input type="color" class="form-control" id="block-color" value="${block.color}">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Font Weight</label>
                            <select class="form-select" id="block-font-weight">
                                <option value="normal" ${block.font_weight === 'normal' ? 'selected' : ''}>Normal</option>
                                <option value="bold" ${block.font_weight === 'bold' ? 'selected' : ''}>Bold</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Text Align</label>
                            <select class="form-select" id="block-text-align">
                                <option value="left" ${block.text_align === 'left' ? 'selected' : ''}>Left</option>
                                <option value="center" ${block.text_align === 'center' ? 'selected' : ''}>Center</option>
                                <option value="right" ${block.text_align === 'right' ? 'selected' : ''}>Right</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Font Family</label>
                            <select class="form-select" id="block-font-family">
                                <option value="Arial" ${block.font_family === 'Arial' ? 'selected' : ''}>Arial</option>
                                <option value="Times New Roman" ${block.font_family === 'Times New Roman' ? 'selected' : ''}>Times New Roman</option>
                                <option value="Helvetica" ${block.font_family === 'Helvetica' ? 'selected' : ''}>Helvetica</option>
                                <option value="Georgia" ${block.font_family === 'Georgia' ? 'selected' : ''}>Georgia</option>
                                <option value="Verdana" ${block.font_family === 'Verdana' ? 'selected' : ''}>Verdana</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Block Type</label>
                            <input type="text" class="form-control" value="${block.type}" readonly>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary mt-3" onclick="updateBlockProperties()">Update Properties</button>
                `;
                
                // Initialize Quill editor if needed
                if (useRichText) {
                    setTimeout(() => {
                        if (window.blockQuill) {
                            window.blockQuill = null;
                        }
                        
                        window.blockQuill = new Quill('#block-quill-editor', {
                            theme: 'snow',
                            placeholder: 'Enter your text content...',
                            modules: {
                                toolbar: [
                                    ['bold', 'italic', 'underline'],
                                    [{ 'size': ['small', false, 'large'] }],
                                    [{ 'color': [] }, { 'background': [] }],
                                    [{ 'align': [] }],
                                    ['clean']
                                ]
                            }
                        });
                        
                        // Set initial content
                        window.blockQuill.root.innerHTML = block.value;
                        
                        // Update hidden field on content change
                        window.blockQuill.on('text-change', function() {
                            document.getElementById('block-value').value = window.blockQuill.root.innerHTML;
                        });
                    }, 100);
                }
            }

            // Global functions for block management
            window.updateBlockProperties = function() {
                if (!selectedBlock) return;

                // Get content from either Quill editor or regular input
                let newValue;
                if (window.blockQuill && selectedBlock.type === 'text') {
                    newValue = window.blockQuill.root.innerHTML;
                } else {
                    newValue = document.getElementById('block-value').value;
                }

                selectedBlock.value = newValue;
                selectedBlock.x = parseInt(document.getElementById('block-x').value);
                selectedBlock.y = parseInt(document.getElementById('block-y').value);
                selectedBlock.font_size = parseInt(document.getElementById('block-font-size').value);
                selectedBlock.color = document.getElementById('block-color').value;
                selectedBlock.font_weight = document.getElementById('block-font-weight').value;
                selectedBlock.text_align = document.getElementById('block-text-align').value;
                selectedBlock.font_family = document.getElementById('block-font-family').value;

                // Update visual representation
                const blockElement = document.getElementById(selectedBlock.id);
                if (blockElement) {
                    blockElement.style.left = selectedBlock.x + 'px';
                    blockElement.style.top = selectedBlock.y + 'px';
                    blockElement.style.fontSize = selectedBlock.font_size + 'px';
                    blockElement.style.color = selectedBlock.color;
                    blockElement.style.fontWeight = selectedBlock.font_weight;
                    blockElement.style.textAlign = selectedBlock.text_align;
                    blockElement.style.fontFamily = selectedBlock.font_family;
                    
                    // Update content - strip HTML for display if it's rich text
                    const contentElement = blockElement.querySelector('.block-content');
                    if (selectedBlock.type === 'text') {
                        contentElement.innerHTML = newValue;
                    } else {
                        contentElement.textContent = newValue;
                    }
                    
                    blockElement.querySelector('.coordinate-display').textContent = `${selectedBlock.x}, ${selectedBlock.y}`;
                }

                Swal.fire({
                    title: 'Updated!',
                    text: 'Block properties updated successfully',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            };

            window.deleteBlock = function(blockId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to delete this content block?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f06548',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        contentBlocks = contentBlocks.filter(b => b.id !== blockId);
                        document.getElementById(blockId).remove();
                        
                        if (selectedBlock && selectedBlock.id === blockId) {
                            selectedBlock = null;
                            document.getElementById('properties-panel').innerHTML = '<p class="text-muted">Select a content block to edit its properties</p>';
                        }

                        // Show success feedback
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Content block has been deleted',
                            timer: 1500,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                });
            };

            // Debug functionality
            document.getElementById('debug-btn').addEventListener('click', function() {
                const fileInput = document.getElementById('template_file');
                const currentFile = fileInput.files[0];
                
                const debugInfo = {
                    'uploadedFile': uploadedFile ? {
                        name: uploadedFile.name,
                        size: uploadedFile.size,
                        type: uploadedFile.type
                    } : null,
                    'inputFile': currentFile ? {
                        name: currentFile.name,
                        size: currentFile.size,
                        type: currentFile.type
                    } : null,
                    'templateElement': {
                        fileSelected: certificateTemplate.dataset.fileSelected,
                        fileName: certificateTemplate.dataset.fileName,
                        fileType: certificateTemplate.dataset.fileType,
                        templateType: certificateTemplate.dataset.templateType
                    },
                    'canRestore': uploadedFile && !currentFile
                };
                
                console.log('Debug Info:', debugInfo);
                
                Swal.fire({
                    title: 'File Upload Debug',
                    html: `
                        <div style="text-align: left; font-family: monospace; font-size: 12px;">
                            <strong>Uploaded File:</strong><br>
                            ${uploadedFile ? `${uploadedFile.name} (${(uploadedFile.size/1024/1024).toFixed(2)} MB)` : 'None'}<br><br>
                            <strong>Input File:</strong><br>
                            ${currentFile ? `${currentFile.name} (${(currentFile.size/1024/1024).toFixed(2)} MB)` : 'None'}<br><br>
                            <strong>File Selected Flag:</strong> ${certificateTemplate.dataset.fileSelected || 'false'}<br>
                            <strong>Can Restore:</strong> ${uploadedFile && !currentFile ? 'Yes' : 'No'}
                        </div>
                    `,
                    confirmButtonText: 'OK',
                    showCancelButton: true,
                    cancelButtonText: uploadedFile && !currentFile ? 'Restore File' : 'Close'
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel && uploadedFile && !currentFile) {
                        ensureFileInInput();
                        Swal.fire({
                            icon: 'success',
                            title: 'File Restored',
                            text: 'File has been restored to input',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
            });

            // Preview functionality
            document.getElementById('preview-btn').addEventListener('click', function() {
                // Show loading for preview generation
                Swal.fire({
                    title: 'Generating Preview...',
                    text: 'Please wait while we generate your certificate preview.',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Use setTimeout to allow the loading to show
                setTimeout(() => {
                    generatePreview();
                    Swal.close();
                    new bootstrap.Modal(document.getElementById('preview-modal')).show();
                }, 500);
            });

            function generatePreview() {
                const preview = document.getElementById('certificate-preview');
                const templateType = certificateTemplate.dataset.templateType;
                const fileName = certificateTemplate.dataset.fileName;
                
                let backgroundStyle = '';
                let overlayContent = '';
                
                if (templateType === 'image') {
                    // Get the background image from the current template
                    const currentBackground = certificateTemplate.style.backgroundImage;
                    if (currentBackground && currentBackground !== 'none') {
                        backgroundStyle = `background-image: ${currentBackground}; background-size: contain; background-repeat: no-repeat; background-position: center;`;
                    } else {
                        backgroundStyle = 'background-color: #ffffff; border: 1px solid #ddd;';
                        overlayContent = '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #adb5bd;"><i class="ri-image-line fs-1"></i><br><small>Image Template</small></div>';
                    }
                } else if (templateType === 'pdf') {
                    backgroundStyle = 'background-color: #f8f9fa; border: 2px dashed #ddd;';
                    overlayContent = `<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #6c757d; z-index: 1;"><i class="ri-file-pdf-line fs-1"></i><br><small>PDF Template: ${fileName || 'Uploaded'}</small></div>`;
                } else {
                    backgroundStyle = 'background-color: #ffffff; border: 1px solid #ddd;';
                    overlayContent = '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #adb5bd;"><i class="ri-image-line fs-1"></i><br><small>No Template Uploaded</small></div>';
                }
                
                preview.innerHTML = `
                    <div style="
                        position: relative;
                        width: 800px;
                        height: 600px;
                        ${backgroundStyle}
                        border-radius: 8px;
                        margin: 0 auto;
                        overflow: hidden;
                    ">
                        ${overlayContent}
                        ${contentBlocks.map(block => `
                            <div style="
                                position: absolute;
                                left: ${block.x}px;
                                top: ${block.y}px;
                                font-size: ${block.font_size}px;
                                font-family: ${block.font_family};
                                font-weight: ${block.font_weight};
                                text-align: ${block.text_align};
                                color: ${block.color};
                                background: rgba(255, 255, 255, 0.9);
                                padding: 2px 4px;
                                border-radius: 2px;
                                z-index: 10;
                                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                            ">${block.value}</div>
                        `).join('')}
                    </div>
                `;
            }

            // Form submission
            document.getElementById('certificate-form').addEventListener('submit', function(e) {
                e.preventDefault(); // Always prevent default first
                
                console.log('Form submission started. Ensuring file is in input...');
                
                // Force file restoration if needed
                const fileRestored = ensureFileInInput();
                if (!fileRestored) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Required',
                        text: 'Please select a template file before submitting.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const finalFileInput = templateFile.files[0];
                console.log('Final file check:', finalFileInput ? finalFileInput.name : 'No file');

                // Validate file size (40MB limit)
                if (finalFileInput.size > 40 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'File size must be less than 40MB. Please select a smaller file.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
                if (!allowedTypes.includes(finalFileInput.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: 'Please select a valid image file (JPEG, PNG, GIF) or PDF document.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Validate that award is selected
                const awardId = document.getElementById('award_id').value;
                if (!awardId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Award Required',
                        text: 'Please select an award for this certificate.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Store content blocks in hidden field
                document.getElementById('content_blocks').value = JSON.stringify(contentBlocks);

                // All validations passed - show loading
                Swal.fire({
                    title: 'Creating Certificate...',
                    text: 'Please wait while we upload and create your certificate template.',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Create FormData manually to ensure file is included
                const formData = new FormData();
                
                // Add form fields
                formData.append('award_id', document.getElementById('award_id').value);
                formData.append('issue_date', document.getElementById('issue_date').value);
                formData.append('published_at', document.getElementById('published_at').value);
                formData.append('is_active', document.getElementById('is_active').value);
                formData.append('content_blocks', JSON.stringify(contentBlocks));
                
                // Add the file - this is the critical part
                formData.append('template_file', finalFileInput, finalFileInput.name);
                
                console.log('FormData created with file:', finalFileInput.name);
                console.log('FormData entries:');
                for (let pair of formData.entries()) {
                    if (pair[1] instanceof File) {
                        console.log(pair[0], `File: ${pair[1].name} (${pair[1].size} bytes)`);
                    } else {
                        console.log(pair[0], pair[1]);
                    }
                }

                // Submit using fetch
                fetch('/master-data/program-certificates/create', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.redirected) {
                        // CodeIgniter redirect - follow it
                        window.location.href = response.url;
                    } else {
                        return response.text();
                    }
                })
                .then(data => {
                    if (data) {
                        // If we get HTML back, it means there was an error or validation issue
                        // Check if it contains success or error indicators
                        if (data.includes('alert-success')) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Certificate created successfully',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '/master-data/program-certificates';
                            });
                        } else if (data.includes('alert-danger') || data.includes('error')) {
                            Swal.close();
                            // Let the page reload to show the error
                            document.open();
                            document.write(data);
                            document.close();
                        } else {
                            Swal.close();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    let errorMessage = 'There was an error uploading the certificate. Please try again.';
                    let errorTitle = 'Upload Error';
                    
                    // Provide more specific error messages based on error type
                    if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                        errorTitle = 'Connection Error';
                        errorMessage = 'Failed to connect to the server. Please check your internet connection and try again.';
                    } else if (error.name === 'TypeError' && error.message.includes('NetworkError')) {
                        errorTitle = 'Network Error';
                        errorMessage = 'A network error occurred. This could be due to a slow connection or large file size.';
                    } else if (error.message) {
                        errorMessage = error.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: errorTitle,
                        html: `
                            <div style="text-align: left;">
                                <p><strong>Error:</strong> ${errorMessage}</p>
                                <hr>
                                <p><strong>Troubleshooting Tips:</strong></p>
                                <ul style="margin: 0; padding-left: 20px; text-align: left;">
                                    <li>Check your internet connection</li>
                                    <li>Try uploading a smaller file (under 5MB)</li>
                                    <li>Refresh the page and try again</li>
                                    <li>Contact support if the problem persists</li>
                                </ul>
                            </div>
                        `,
                        confirmButtonText: 'OK'
                    });
                });
            });

            // Click outside to deselect
            certificateTemplate.addEventListener('click', function(e) {
                if (e.target === this) {
                    document.querySelectorAll('.content-block').forEach(el => {
                        el.classList.remove('selected');
                        el.querySelector('.block-controls').style.display = 'none';
                    });
                    selectedBlock = null;
                    document.getElementById('properties-panel').innerHTML = '<p class="text-muted">Select a content block to edit its properties</p>';
                }
            });

            // Handle flash messages with SweetAlert
            <?php if (session()->getFlashdata('success')): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?= session()->getFlashdata('success') ?>',
                    timer: 3000,
                    showConfirmButton: false
                });
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?= session()->getFlashdata('error') ?>',
                    confirmButtonText: 'OK'
                });
            <?php endif; ?>

            <?php if (session()->getFlashdata('validation')): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Errors!',
                    html: '<ul style="text-align: left;"><?php foreach (session()->getFlashdata('validation') as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul>',
                    confirmButtonText: 'OK'
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>
