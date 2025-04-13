<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title'=>'Generated LOA')); ?>
    <?= $this->include('partials/head-css') ?>
    
    <style>
        .loa-container {
            background-color: white;
            border: 1px solid #e9e9ef;
            border-radius: 4px;
            padding: 2rem;
            min-height: 800px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            font-size: 14px;
            line-height: 1.6;
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
        @media print {
            body * {
                visibility: hidden;
            }
            .card-body, .loa-container, .loa-container * {
                visibility: visible;
            }
            .loa-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                box-shadow: none;
                border: none;
            }
            .action-buttons {
                display: none !important;
            }
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
                    <?php echo view('partials/page-title', array('pagetitle'=>'Documents', 'title'=>'Generated LOA')); ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">
                                        LOA for: <?= $participant->full_name ?>
                                    </h5>
                                    <div class="flex-shrink-0 action-buttons">
                                        <a href="/program-documents/view/<?= $document->id ?>" class="btn btn-light btn-sm">
                                            <i class="ri-arrow-left-line align-middle me-1"></i> Back
                                        </a>
                                        <button onclick="window.print()" class="btn btn-primary btn-sm">
                                            <i class="ri-printer-line align-middle me-1"></i> Print
                                        </button>
                                        <button id="download-pdf" class="btn btn-success btn-sm">
                                            <i class="ri-download-2-line align-middle me-1"></i> Download PDF
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="loa-container">
                                        <div class="preview-header">
                                            <img src="/assets/images/logo-dark.png" alt="Logo" height="60" class="mb-3">
                                            <h2>LETTER OF AGREEMENT</h2>
                                        </div>
                                        
                                        <div class="loa-content">
                                            <?= $loaContent ?>
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
    
    <!-- html2pdf library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <!-- App js -->
    <script src="/assets/js/app.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // PDF Download handler
            document.getElementById('download-pdf').addEventListener('click', function() {
                const element = document.querySelector('.loa-container');
                const opt = {
                    margin: [10, 10, 10, 10],
                    filename: 'LOA-<?= $participant->full_name ?>-<?= date('Ymd') ?>.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                
                // Generate PDF
                html2pdf().set(opt).from(element).save();
            });
        });
    </script>
</body>

</html>
