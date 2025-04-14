<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'LOA Template Settings')); ?>
    <?= $this->include('partials/head-css') ?>

    <!-- Quill editor CSS -->
    <link href="/assets/libs/quill/quill.core.css" rel="stylesheet" type="text/css">
    <link href="/assets/libs/quill/quill.bubble.css" rel="stylesheet" type="text/css">
    <link href="/assets/libs/quill/quill.snow.css" rel="stylesheet" type="text/css">

    <style>
        .preview-container {
            background-color: white;
            border: 1px solid #e9e9ef;
            border-radius: 4px;
            padding: 2rem;
            min-height: 800px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        #editor-container {
            min-height: 400px;
        }

        .ql-toolbar.ql-snow {
            border-radius: 4px 4px 0 0;
        }

        .ql-container.ql-snow {
            border-radius: 0 0 4px 4px;
        }

        .variable-badge {
            background-color: #405189;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
            display: inline-block;
            font-size: 12px;
        }

        .preview-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }

        .preview-footer {
            margin-top: 3rem;
        }

        .preview-signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 4rem;
        }

        .signature-box {
            width: 45%;
            border-top: 1px solid #000;
            padding-top: 0.5rem;
        }

        .loa-heading {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 1.5rem;
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
                    <?php echo view('partials/page-title', array('pagetitle' => 'Documents', 'title' => 'LOA Template Settings')); ?>

                    <?php if (!isset($document) || $document->type !== 'loa'): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="alert alert-danger mb-0">
                                            <h4 class="alert-heading">Access Denied</h4>
                                            <p class="mb-0">This page is only accessible for LOA type documents.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex align-items-center">
                                        <h5 class="card-title mb-0 flex-grow-1">
                                            LOA Template for: <?= $document->name ?>
                                        </h5>
                                        <div class="flex-shrink-0">
                                            <a href="/documents/program-documents/view/<?= $document->id ?>" class="btn btn-light btn-sm">
                                                <i class="ri-arrow-left-line align-middle me-1"></i> Back to Document
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <form id="loa-template-form" action="/documents/program-documents/save-loa-template/<?= $document->id ?>" method="post">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Available Variables:</label>
                                                    <div class="d-flex flex-wrap">
                                                        <?php foreach ($placeholders as $placeholder): ?>
                                                            <span class="variable-badge" data-variable="{{<?= $placeholder['key'] ?>}}">
                                                                <?= $placeholder['value'] ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>

                                                <div class="col-md-12 mb-4">
                                                    <label class="form-label">LOA Content:</label>
                                                    <div id="editor-container">
                                                        <?= $loaTemplate ?? ''; ?>
                                                    </div>
                                                    <input type="hidden" name="loa_template" id="loa-template-input">
                                                </div>

                                                <div class="col-md-12 mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="ri-save-line align-middle me-1"></i> Save Template
                                                        </button>
                                                        <button type="button" class="btn btn-info" id="preview-button">
                                                            <i class="ri-eye-line align-middle me-1"></i> Preview
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Preview</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="preview-container" id="preview-content">
                                            <!-- Preview content will be inserted here -->
                                            <div class="preview-header">
                                                <img src="/assets/images/logo-dark.png" alt="Logo" height="60" class="mb-3">
                                                <h2>LETTER OF AGREEMENT</h2>
                                            </div>
                                            <div class="preview-body">
                                                <!-- Template content preview will be rendered here -->
                                                <p>Please create your LOA template using the editor above.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
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

    <!-- quill js -->
    <script src="/assets/libs/quill/quill.min.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var quill = new Quill('#editor-container', {
                modules: {
                    toolbar: [
                        [{
                            'header': [1, 2, 3, 4, 5, 6, false]
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            'color': []
                        }, {
                            'background': []
                        }],
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
                        ['link'],
                        ['clean']
                    ]
                },
                placeholder: 'Write your LOA template here...',
                theme: 'snow'
            });

            // Improved variable insertion with better cursor handling
            document.querySelectorAll('.variable-badge').forEach(function(badge) {
                badge.addEventListener('click', function() {
                    const variable = this.dataset.variable;
                    let cursorPosition = quill.getSelection();

                    // Focus the editor if not already focused
                    if (!cursorPosition) {
                        quill.focus();
                        cursorPosition = quill.getSelection() || {
                            index: quill.getLength(),
                            length: 0
                        };
                    }

                    // Insert the variable at cursor position
                    quill.insertText(cursorPosition.index, variable, 'api');

                    // Set cursor position right after the inserted variable
                    quill.setSelection(cursorPosition.index + variable.length, 0);

                    // Visual feedback that variable was inserted
                    badge.classList.add('bg-success');
                    setTimeout(() => {
                        badge.classList.remove('bg-success');
                    }, 300);
                });
            });

            // Set form content before submit
            document.getElementById('loa-template-form').addEventListener('submit', function() {
                var content = quill.root.innerHTML;
                document.getElementById('loa-template-input').value = content;
            });

            // Load real participant data for preview
            const fetchRandomParticipant = async () => {
                try {
                    // Get program ID from session
                    const programId = <?= session('current_program') ?>;

                    // Fetch random participant from API
                    const response = await fetch(`/api/participants/program/${programId}/random`);
                    if (!response.ok) {
                        throw new Error('Failed to fetch participant data');
                    }

                    const data = await response.json();
                    return data.participant || fallbackParticipant();
                } catch (error) {
                    console.error('Error fetching participant:', error);
                    return fallbackParticipant();
                }
            };

            // Fallback participant data if API fails
            const fallbackParticipant = () => {
                return {
                    id: 1,
                    full_name: '<?= isset($participant) ? $participant->full_name : "John Doe" ?>',
                    email: '<?= isset($participant) ? ($participant->email ?? "participant@example.com") : "participant@example.com" ?>',
                    phone: '<?= isset($participant) ? ($participant->phone ?? "+6281234567890") : "+6281234567890" ?>',
                    institution: '<?= isset($participant) ? ($participant->institution ?? "Sample University") : "Sample University" ?>',
                    current_address: '<?= isset($participant) ? ($participant->current_address ?? "Jakarta, Indonesia") : "Jakarta, Indonesia" ?>',
                    origin_address: '<?= isset($participant) ? ($participant->origin_address ?? "Jakarta, Indonesia") : "Jakarta, Indonesia" ?>'
                };
            };

            // Preview button with real participant data
            document.getElementById('preview-button').addEventListener('click', async function() {
                // Show loading indicator
                const previewBody = document.querySelector('#preview-content .preview-body');
                previewBody.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Generating preview...</p></div>';

                // Get participant data for preview
                let participant;
                try {
                    participant = await fetchRandomParticipant();
                } catch (error) {
                    participant = fallbackParticipant();
                }

                // Get content from editor
                var content = quill.root.innerHTML;

                // Replace variables with participant data
                content = content
                    .replace(/\{\{participant_name\}\}/g, participant.full_name)
                    .replace(/\{\{program_name\}\}/g, '<?= $document->name ?>')
                    .replace(/\{\{institution\}\}/g, participant.institution || 'Youth Break the Boundaries')
                    .replace(/\{\{today_date\}\}/g, '<?= date("F d, Y") ?>')
                    .replace(/\{\{start_date\}\}/g, '<?= isset($program->start_date) ? date("F d, Y", strtotime($program->start_date)) : "April 30, 2025" ?>')
                    .replace(/\{\{end_date\}\}/g, '<?= isset($program->end_date) ? date("F d, Y", strtotime($program->end_date)) : "December 31, 2025" ?>')
                    .replace(/\{\{participant_address\}\}/g, participant.current_address || participant.origin_address || 'Jakarta, Indonesia')
                    .replace(/\{\{reference_number\}\}/g, 'LOA-<?= date("Y") ?>-<?= $document->id ?>-' + participant.id)
                    .replace(/\{\{participant_email\}\}/g, participant.email || 'participant@example.com')
                    .replace(/\{\{participant_phone\}\}/g, participant.phone || '+6281234567890');

                // Add signature placeholders if not present
                if (!content.includes('{{participant_signature}}') && !content.includes('{{admin_signature}}')) {
                    content += `
                    <div class="preview-signature-section">
                        <div class="signature-box">
                            <p><strong>Participant:</strong></p>
                            <p>${participant.full_name}</p>
                        </div>
                        <div class="signature-box">
                            <p><strong>For <?= $program->name ?? 'Program' ?>:</strong></p>
                            <p><?= session('name') ?? 'Admin' ?></p>
                        </div>
                    </div>`;
                } else {
                    content = content
                        .replace(/\{\{participant_signature\}\}/g, '<div class="signature-box"><p><strong>Participant:</strong></p><p>' + participant.full_name + '</p></div>')
                        .replace(/\{\{admin_signature\}\}/g, '<div class="signature-box"><p><strong>For <?= $program->name ?? 'Program' ?>:</strong></p><p><?= session('name') ?? 'Admin' ?></p></div>');
                }

                previewBody.innerHTML = content;

                // Show notification about the preview
                const previewAlert = document.createElement('div');
                previewAlert.className = 'alert alert-info mt-3';
                previewAlert.innerHTML = `<strong>Preview with participant data:</strong> ${participant.full_name} (${participant.email || 'No email'})`;
                previewBody.prepend(previewAlert);

                // Auto-hide the alert after 5 seconds
                setTimeout(() => {
                    previewAlert.classList.add('fade');
                    setTimeout(() => previewAlert.remove(), 500);
                }, 5000);
            });

            // Initialize with template content if available
            <?php if (isset($loaTemplate) && !empty($loaTemplate)): ?>
                // If there's a saved template, trigger preview immediately
                document.getElementById('preview-button').click();
            <?php endif; ?>
        });
    </script>
</body>

</html>