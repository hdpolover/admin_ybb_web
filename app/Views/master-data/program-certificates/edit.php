<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Edit Certificate Template')); ?>
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
            background: rgba(0, 123, 255, 0.1);
            cursor: move;
            min-width: 100px;
            min-height: 30px;
            padding: 5px;
            border-radius: 4px;
            user-select: none;
        }

        .content-block:hover {
            border-color: #0056b3;
            background: rgba(0, 123, 255, 0.2);
        }

        .content-block.selected {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.2);
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

        .current-template {
            text-align: center;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
            margin-bottom: 10px;
        }

        .current-template img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 4px;
        }

        .pdf-template-preview {
            text-align: center;
            padding: 20px;
        }

        .pdf-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 400px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            margin: 20px;
        }

        .pdf-placeholder p {
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Master Data', 'title' => 'Edit Certificate Template')); ?>

                    <form action="/master-data/program-certificates/update/<?= $certificate['id'] ?>" method="post" enctype="multipart/form-data" id="certificate-form">
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
                                                    <option value="<?= $award['id'] ?>" <?= $award['id'] == $certificate['award_id'] ? 'selected' : '' ?>>
                                                        <?= esc($award['title']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Current Template</label>
                                            <?php if (!empty($certificate['template_url'])): ?>
                                                <div class="current-template">
                                                    <?php 
                                                    // Since template_url now stores the full URL, use it directly
                                                    $templateUrl = $certificate['template_url'];
                                                    $templateType = $certificate['template_type'] ?? 'image';
                                                    ?>
                                                    
                                                    <?php if ($templateType === 'pdf'): ?>
                                                        <div class="pdf-template-preview">
                                                            <i class="ri-file-pdf-line fs-1 text-danger"></i>
                                                            <p class="text-muted mt-2 mb-0">PDF Template</p>
                                                            <a href="<?= $templateUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                                                <i class="ri-external-link-line"></i> View PDF
                                                            </a>
                                                        </div>
                                                    <?php else: ?>
                                                        <img src="<?= $templateUrl ?>" alt="Current Template">
                                                        <p class="text-muted mt-2 mb-0">Current template image</p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <label for="template_file" class="form-label">Update Template (Optional)</label>
                                            <div class="template-upload-area" id="template-upload">
                                                <i class="ri-upload-cloud-2-line fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">Click or drag to upload new template</p>
                                                <input type="file" id="template_file" name="template_file" accept="image/*,application/pdf" style="display: none;">
                                            </div>
                                            <small class="text-muted">Upload a background image (JPEG, PNG, GIF) or PDF template. Leave empty to keep current template.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="issue_date" class="form-label">Issue Date</label>
                                            <input type="date" class="form-control" id="issue_date" name="issue_date" value="<?= $certificate['issue_date'] ?? '' ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="published_at" class="form-label">Publish Date</label>
                                            <input type="datetime-local" class="form-control" id="published_at" name="published_at" value="<?= $certificate['published_at'] ? date('Y-m-d\TH:i', strtotime($certificate['published_at'])) : '' ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="is_active" class="form-label">Status*</label>
                                            <select class="form-select" id="is_active" name="is_active" required>
                                                <option value="1" <?= $certificate['is_active'] == 1 ? 'selected' : '' ?>>Active</option>
                                                <option value="0" <?= $certificate['is_active'] == 0 ? 'selected' : '' ?>>Inactive</option>
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
                                            <a href="/master-data/program-certificates" class="btn btn-outline-secondary me-2">
                                                <i class="ri-arrow-left-line"></i> Back
                                            </a>
                                            <button type="button" class="btn btn-outline-secondary me-2" id="preview-btn">
                                                <i class="ri-eye-line"></i> Preview
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ri-save-line"></i> Update Certificate
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="certificate-designer" id="certificate-designer">
                                            <div class="certificate-template" id="certificate-template">
                                                <!-- Content blocks will be loaded here -->
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
            let contentBlocks = <?= json_encode($contentBlocks) ?> || [];
            let selectedBlock = null;
            let blockCounter = contentBlocks.length;

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

            // Template upload handling
            const templateUpload = document.getElementById('template-upload');
            const templateFile = document.getElementById('template_file');
            const certificateTemplate = document.getElementById('certificate-template');

            // Set initial background if template exists
            <?php if (!empty($certificate['template_url'])): ?>
            <?php 
            // Since template_url now stores the full URL, use it directly
            $templateUrl = $certificate['template_url'];
            $templateType = $certificate['template_type'] ?? 'image';
            ?>
            <?php if ($templateType === 'image'): ?>
            certificateTemplate.style.backgroundImage = 'url(<?= $templateUrl ?>)';
            <?php else: ?>
            // For PDF templates, show a placeholder or use PDF.js for rendering
            certificateTemplate.innerHTML = '<div class="pdf-placeholder"><i class="ri-file-pdf-line fs-1 text-muted"></i><p class="text-muted">PDF Template - Content blocks will be positioned relative to PDF</p></div>';
            certificateTemplate.style.backgroundColor = '#f8f9fa';
            <?php endif; ?>
            <?php endif; ?>

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
                    handleFile(files[0]);
                }
            }

            function handleFileSelect(e) {
                const file = e.target.files[0];
                if (file) {
                    handleFile(file);
                }
            }

            function handleFile(file) {
                if (!file.type.startsWith('image/') && file.type !== 'application/pdf') {
                    Swal.fire('Error', 'Please select an image file (JPEG, PNG, GIF) or PDF document', 'error');
                    return;
                }

                if (file.type === 'application/pdf') {
                    // Handle PDF upload
                    certificateTemplate.innerHTML = '<div class="pdf-placeholder"><i class="ri-file-pdf-line fs-1 text-success"></i><p class="text-success">PDF Template uploaded - Preview not available</p><p class="text-muted">Content blocks can still be positioned</p></div>';
                    certificateTemplate.style.backgroundImage = 'none';
                    certificateTemplate.style.backgroundColor = '#f8f9fa';
                    templateUpload.innerHTML = `
                        <i class="ri-check-line fs-1 text-success"></i>
                        <p class="text-success mt-2">PDF template uploaded successfully</p>
                    `;
                } else {
                    // Handle image upload
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        certificateTemplate.innerHTML = ''; // Clear any PDF placeholder
                        certificateTemplate.style.backgroundImage = `url(${e.target.result})`;
                        certificateTemplate.style.backgroundSize = 'contain';
                        certificateTemplate.style.backgroundRepeat = 'no-repeat';
                        certificateTemplate.style.backgroundPosition = 'center';
                        certificateTemplate.style.backgroundColor = 'transparent';
                        templateUpload.innerHTML = `
                            <i class="ri-check-line fs-1 text-success"></i>
                            <p class="text-success mt-2">Image template uploaded successfully</p>
                        `;
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

            function createContentBlock(type, x = 50, y = 50, value = '', existingBlock = null) {
                blockCounter++;
                const blockId = existingBlock ? existingBlock.id : `block-${blockCounter}`;
                
                const block = existingBlock || {
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
                    is_active: 1
                };

                if (!existingBlock) {
                    contentBlocks.push(block);
                }
                
                renderContentBlock(block);
                selectBlock(blockId);
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
                blockElement.style.fontSize = (block.font_size || 14) + 'px';
                blockElement.style.fontFamily = block.font_family || 'Arial';
                blockElement.style.fontWeight = block.font_weight || 'normal';
                blockElement.style.textAlign = block.text_align || 'left';
                blockElement.style.color = block.color || '#000000';
                blockElement.innerHTML = `
                    <div class="block-controls" style="display: none;">
                        <button type="button" onclick="editBlock('${block.id}')" title="Edit">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button type="button" onclick="deleteBlock('${block.id}')" title="Delete">
                            <i class="ri-delete-bin-line text-danger"></i>
                        </button>
                    </div>
                    <div class="block-content">${block.value}</div>
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
                propertiesPanel.innerHTML = `
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <input type="text" class="form-control" id="block-value" value="${block.value}">
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
                            <input type="number" class="form-control" id="block-font-size" value="${block.font_size || 14}" min="8" max="72">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <input type="color" class="form-control" id="block-color" value="${block.color || '#000000'}">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Font Weight</label>
                            <select class="form-select" id="block-font-weight">
                                <option value="normal" ${(block.font_weight || 'normal') === 'normal' ? 'selected' : ''}>Normal</option>
                                <option value="bold" ${block.font_weight === 'bold' ? 'selected' : ''}>Bold</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Text Align</label>
                            <select class="form-select" id="block-text-align">
                                <option value="left" ${(block.text_align || 'left') === 'left' ? 'selected' : ''}>Left</option>
                                <option value="center" ${block.text_align === 'center' ? 'selected' : ''}>Center</option>
                                <option value="right" ${block.text_align === 'right' ? 'selected' : ''}>Right</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary mt-3" onclick="updateBlockProperties()">Update Properties</button>
                `;
            }

            // Global functions for block management
            window.updateBlockProperties = function() {
                if (!selectedBlock) return;

                selectedBlock.value = document.getElementById('block-value').value;
                selectedBlock.x = parseInt(document.getElementById('block-x').value);
                selectedBlock.y = parseInt(document.getElementById('block-y').value);
                selectedBlock.font_size = parseInt(document.getElementById('block-font-size').value);
                selectedBlock.color = document.getElementById('block-color').value;
                selectedBlock.font_weight = document.getElementById('block-font-weight').value;
                selectedBlock.text_align = document.getElementById('block-text-align').value;

                // Update visual representation
                const blockElement = document.getElementById(selectedBlock.id);
                if (blockElement) {
                    blockElement.style.left = selectedBlock.x + 'px';
                    blockElement.style.top = selectedBlock.y + 'px';
                    blockElement.style.fontSize = selectedBlock.font_size + 'px';
                    blockElement.style.color = selectedBlock.color;
                    blockElement.style.fontWeight = selectedBlock.font_weight;
                    blockElement.style.textAlign = selectedBlock.text_align;
                    
                    blockElement.querySelector('.block-content').textContent = selectedBlock.value;
                    blockElement.querySelector('.coordinate-display').textContent = `${selectedBlock.x}, ${selectedBlock.y}`;
                }

                Swal.fire({
                    title: 'Updated!',
                    text: 'Block properties updated successfully',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
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
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        contentBlocks = contentBlocks.filter(b => b.id !== blockId);
                        document.getElementById(blockId).remove();
                        
                        if (selectedBlock && selectedBlock.id === blockId) {
                            selectedBlock = null;
                            document.getElementById('properties-panel').innerHTML = '<p class="text-muted">Select a content block to edit its properties</p>';
                        }
                    }
                });
            };

            // Load existing content blocks
            contentBlocks.forEach(block => {
                renderContentBlock(block);
            });

            // Preview functionality
            document.getElementById('preview-btn').addEventListener('click', function() {
                generatePreview();
                new bootstrap.Modal(document.getElementById('preview-modal')).show();
            });

            function generatePreview() {
                const preview = document.getElementById('certificate-preview');
                const templateStyle = window.getComputedStyle(certificateTemplate);
                
                preview.innerHTML = `
                    <div style="
                        position: relative;
                        width: ${document.getElementById('width').value}px;
                        height: ${document.getElementById('height').value}px;
                        background-image: ${templateStyle.backgroundImage};
                        background-size: contain;
                        background-repeat: no-repeat;
                        background-position: center;
                        border: 1px solid #ddd;
                        margin: 0 auto;
                    ">
                        ${contentBlocks.map(block => `
                            <div style="
                                position: absolute;
                                left: ${block.x}px;
                                top: ${block.y}px;
                                font-size: ${block.font_size || 14}px;
                                font-family: ${block.font_family || 'Arial'};
                                font-weight: ${block.font_weight || 'normal'};
                                text-align: ${block.text_align || 'left'};
                                color: ${block.color || '#000000'};
                            ">${block.value}</div>
                        `).join('')}
                    </div>
                `;
            }

            // Form submission
            document.getElementById('certificate-form').addEventListener('submit', function(e) {
                // Store content blocks in hidden field
                document.getElementById('content_blocks').value = JSON.stringify(contentBlocks);

                // Show loading
                Swal.fire({
                    title: 'Updating Certificate...',
                    text: 'Please wait while we update your certificate template.',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
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
        });
    </script>
</body>
</html>
