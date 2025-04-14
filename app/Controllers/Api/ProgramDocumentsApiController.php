<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramDocumentModel;

class ProgramDocumentsApiController extends ApiBaseController
{
    protected $model;

    /**
     * Initialize controller, set model
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);

        // Initialize model - this is what was previously in the constructor
        $this->model = new ProgramDocumentModel();
    }

    /**
     * 🟢 Get All Program Documents (READ)
     * GET /api/program-documents
     */

    public function index()
    {

        $documents = $this->model->findAll();
        return $this->respondSuccess($documents);
    }

    /**
     * 🔍 Get Single Program Document (READ)
     * GET /api/program-documents/{id}
     */
    public function show($id = null)
    {
        $document = $this->model->find($id);

        if (!$document) {
            return $this->failNotFound('Document not found');
        }

        return $this->respondSuccess($document);
    }

    /**
     * 🔍 Get Program Documents by Program ID (READ)
     * GET /api/program-documents/program/{programId}
     */
    public function getByProgram($programId = null)
    {
        if ($programId === null) {
            return $this->failValidationErrors('Program ID is required');
        }

        $documents = $this->model->getProgramDocumentsByProgramId($programId);

        if (!$documents) {
            return $this->failNotFound('No documents found for this program ID');
        }

        return $this->respondSuccess($documents);
    }
    /**
     * 📄 Generate LOA by Program Document ID and Participant ID
     * GET /api/program-documents/{documentId}/participants/{participantId}/generate
     * This endpoint generates a Letter of Acceptance (LOA) for a specific participant and returns a PDF file
     */
    public function generateLOA($documentId = null, $participantId = null)
    {
        // Increase execution time limit for PDF generation
        ini_set('max_execution_time', 300); // 5 minutes
        set_time_limit(300); // Backup approach

        // Validate required parameters
        if ($documentId === null || $participantId === null) {
            return $this->failValidationErrors('Document ID and Participant ID are required');
        }

        // Get the document
        $document = $this->model->find($documentId);
        if (!$document) {
            return $this->failNotFound('Program document not found');
        }

        try {
            // Load participant data
            $participantModel = new \App\Models\ParticipantModel();
            $participant = $participantModel->find($participantId);

            if (!$participant) {
                return $this->failNotFound('Participant not found');
            }

            // Get program data
            $programModel = new \App\Models\ProgramModel();
            $program = (object)$programModel->find($participant->program_id);

            if (!$program) {
                return $this->failNotFound('Program not found');
            }

            // get program category
            $programCategoryModel = new \App\Models\ProgramCategoryModel();
            $programCategory = $programCategoryModel->find($program->program_category_id);

            if (!$programCategory) {
                return $this->failNotFound('Program category not found');
            }

            $programData = [
                'name' => $program->name,
                'location' => $programCategory->location,
                'start_date' => $program->start_date,
                'end_date' => $program->end_date,
                'logo_url' => $program->logo_url,
                'web_url' => $programCategory->web_url,
                'email' => $programCategory->email,
                'tagline' => $programCategory->tagline,
                'contact' => $programCategory->contact,
                'main_name' => $programCategory->name,
            ];

            // Get the LOA template
            $loaTemplateModel = new \App\Models\LoaTemplateModel();
            $template = $loaTemplateModel->getLoaTemplateByProgramDocumentId($documentId);

            if (!$template) {
                return $this->failNotFound('LOA template not found for this document');
            }

            // Generate the LOA content with template and participant data
            $loaContent = $this->generateLoaContent($template->body, $participant, $programData);


            // Generate PDF
            $filename = 'LOA-' . $participant->full_name . '-' . date('Ymd') . '.pdf';
            $pdfContent = $this->generateLoaPdf($loaContent, $programData);

            // Encode the PDF as base64 for sending in the JSON response
            $encodedPdf = base64_encode($pdfContent);
            
            // Return success response with the file information
            return $this->respondSuccess([  
                'file_name' => $filename,
                'mime_type' => 'application/pdf',
                'file_data' => $encodedPdf,
                'message' => 'LOA generated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    /**
     * Generate LOA content with participant data
     * @param string $template Template content
     * @param object $participant Participant object
     * @param object $program Program object
     * @param object $document Document object
     * @return string
     */
    private function generateLoaContent($template, $participant, $programData)
    {
        // Replace variables with actual data
        $content = $template;
        $content = str_replace('{{participant_name}}', strtoupper($participant->full_name), $content);
        $content = str_replace('{{program_name}}', $programData['name'], $content);
        $content = str_replace('{{institution}}', strtoupper($participant->institution ?? 'Youth Break the Boundaries'), $content);
        $content = str_replace('{{today_date}}', date('F d, Y'), $content);
        $content = str_replace('{{start_date}}', isset($programData['start_date']) ? date("F d, Y", strtotime($programData['start_date'])) : "April 30, 2025", $content);
        $content = str_replace('{{end_date}}', isset($programData['end_date']) ? date("F d, Y", strtotime($programData['end_date'])) : "December 31, 2025", $content);
        // program location
        $content = str_replace('{{program_location}}', isset($programData['location']) ? $programData['location'] : 'Youth Break the Boundaries', $content);
        return $content;
    }
    /**
     * Generate PDF from LOA content
     * @param string $loaContent HTML content for the LOA
     * @param object $participant Participant object
     * @param string $programLogoUrl Optional URL for program logo
     * @return string PDF content as binary string
     */
    private function generateLoaPdf($loaContent, $programData)
    {
        // Generate HTML content with header and footer
        $htmlContent = $this->getLoaPdfTemplate($loaContent, $programData);

        // Create PDF using Dompdf with optimized settings
        $dompdf = new \Dompdf\Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('defaultMediaType', 'print');
        $options->set('debugKeepTemp', false);
        $options->set('debugCss', false);
        $options->set('debugLayout', false);
        $options->set('debugLayoutLines', false);
        $options->set('debugLayoutBlocks', false);
        $options->set('debugLayoutInline', false);
        $options->set('debugLayoutPaddingBox', false);
        $dompdf->setOptions($options);

        // Load HTML content
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF (with memory optimization)
        $dompdf->render();

        // Return the PDF content
        return $dompdf->output();
    }
    /**
     * Get full HTML template for LOA PDF
     * @param string $bodyContent The main content of the LOA
     * @param string $programLogoUrl Optional URL for program logo
     * @return string Complete HTML structure for PDF
     */
    private function getLoaPdfTemplate($bodyContent, $programData)
    {        // Get program logo with an efficient approach that supports network images
        $logoImg = $this->getOptimizedLogoForPdf($programData['logo_url']);

        // Get optimized signature image
        $signatureImg = $this->getOptimizedSignatureForPdf();

        // Add Quill editor styles to preserve formatting
        $quillStyles = '
        /* Quill Editor Styles */
        .ql-align-center {
            text-align: center !important;
        }
        .ql-align-right {
            text-align: right !important;
        }
        .ql-align-justify {
            text-align: justify !important;
        }        /* Indentation classes - complete rewrite for better tab handling */
        p.ql-indent-1, .ql-indent-1 {
            padding-left: 3em !important;
            text-indent: 0 !important;
        }
        p.ql-indent-2, .ql-indent-2 {
            padding-left: 6em !important;
            text-indent: 0 !important;
        }
        p.ql-indent-3, .ql-indent-3 {
            padding-left: 9em !important;
            text-indent: 0 !important;
        }
        p.ql-indent-4, .ql-indent-4 {
            padding-left: 12em !important;
            text-indent: 0 !important;
        }
        p.ql-indent-5, .ql-indent-5 {
            padding-left: 15em !important;
            text-indent: 0 !important;
        }
        p.ql-indent-6, .ql-indent-6 {
            padding-left: 18em !important;
            text-indent: 0 !important;
        }
        p.ql-indent-7, .ql-indent-7 {
            padding-left: 21em !important;
            text-indent: 0 !important;
        }
        p.ql-indent-8, .ql-indent-8 {
            padding-left: 24em !important;
            text-indent: 0 !important;
        }
        /* Tab handling */
        .tab-indent {
            display: inline-block;
            width: 2em;
        }
        /* List styling */
        ul {
            padding-left: 1.5em !important;
            list-style-type: disc !important;
        }
        ol {
            padding-left: 1.5em !important;
            list-style-type: decimal !important;
        }
        li {
            padding-left: 0.5em !important;
        }        /* Additional spacing and structure */
        p {
            margin-bottom: 0.5em !important;
            margin-top: 0.5em !important;
        }
        
        /* Default tab for paragraphs */
        p:not(.ql-align-center):not(.ql-align-right):not(.ql-align-justify) {
            text-indent: 2em !important;
        }
        /* For aligned paragraphs, still add tab but adjust for alignment */
        p.ql-align-justify {
            text-indent: 2em !important;
        }';

        // Load the template file and pass the variables
        $view = \Config\Services::renderer();
        return $view->setVar('logoImg', $logoImg)
            ->setVar('signatureImg', $signatureImg)
            ->setVar('bodyContent', $bodyContent)
            ->setVar('quillStyles', $quillStyles)
            ->setVar('programData', $programData)
            ->render('documents/program-documents/main_loa_template');
    }
    /**
     * Get optimized logo for PDF generation
     * Efficiently handles both local and network images
     * 
     * @param string $programLogoUrl Optional URL for program logo
     * @return string HTML element with optimized image
     */
    private function getOptimizedLogoForPdf($programLogoUrl = null)
    {
        $defaultLogoPath = FCPATH . 'assets/images/logo-dark.png';
        $logoHtml = '<h3 style="font-size: 11pt;">Youth Break the Boundaries</h3>'; // Fallback if no logo is available

        try {
            // Determine which logo to use
            $logoPath = $programLogoUrl ? null : $defaultLogoPath;
            $useNetworkImage = !empty($programLogoUrl);

            if ($useNetworkImage) {
                // For network images, use a local cached copy or download it
                $cacheDir = FCPATH . 'writable/cache/logos/';

                // Create cache directory if it doesn't exist
                if (!is_dir($cacheDir)) {
                    mkdir($cacheDir, 0755, true);
                }

                // Generate a cache filename based on the URL
                $cacheFilename = md5($programLogoUrl) . '.png';
                $cachePath = $cacheDir . $cacheFilename;

                // Check if we have a cached version
                if (!file_exists($cachePath) || (filemtime($cachePath) < strtotime('-1 day'))) {
                    // Image isn't cached or cache is older than a day, download it
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 3, // Short timeout to avoid hanging
                            'header' => 'User-Agent: YBB-LOA-Generator'
                        ]
                    ]);

                    $imageData = @file_get_contents($programLogoUrl, false, $context);

                    if ($imageData !== false) {
                        // Save to cache
                        file_put_contents($cachePath, $imageData);
                        $logoPath = $cachePath;
                    } else {
                        // Download failed, use default logo
                        $logoPath = file_exists($defaultLogoPath) ? $defaultLogoPath : null;
                    }
                } else {
                    // Use cached version
                    $logoPath = $cachePath;
                }
            }

            // Process the logo if we have a valid path
            if ($logoPath && file_exists($logoPath)) {
                // Convert to base64
                $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
                $logoData = base64_encode(file_get_contents($logoPath));
                $logoSrc = 'data:image/' . $logoType . ';base64,' . $logoData;

                // Generate image HTML without styling - let the template handle it
                $logoHtml = '<img src="' . $logoSrc . '" alt="Logo" class="loa-logo">';
            }

            return $logoHtml;
        } catch (\Exception $e) {
            // In case of any errors, return the text-based fallback
            log_message('error', 'Error loading logo for PDF: ' . $e->getMessage());
            return $logoHtml;
        }
    }

    /**
     * Get optimized signature image for PDF generation
     * Efficiently handles the chairman signature image
     * 
     * @param string $customSignatureUrl Optional URL for custom signature
     * @return string HTML element with optimized signature
     */
    private function getOptimizedSignatureForPdf($customSignatureUrl = null)
    {
        $defaultSignaturePath = FCPATH . 'assets/ybb/ttd_aldi.png';
        $signatureHtml = '<p style="font-style: italic;">Signature not available</p>'; // Fallback if no signature is available

        try {
            // Determine which signature to use
            $signaturePath = $customSignatureUrl ? null : $defaultSignaturePath;
            $useNetworkImage = !empty($customSignatureUrl);

            if ($useNetworkImage) {
                // For network images, use a local cached copy or download it
                $cacheDir = FCPATH . 'writable/cache/signatures/';

                // Create cache directory if it doesn't exist
                if (!is_dir($cacheDir)) {
                    mkdir($cacheDir, 0755, true);
                }

                // Generate a cache filename based on the URL
                $cacheFilename = md5($customSignatureUrl) . '.png';
                $cachePath = $cacheDir . $cacheFilename;

                // Check if we have a cached version
                if (!file_exists($cachePath) || (filemtime($cachePath) < strtotime('-1 day'))) {
                    // Image isn't cached or cache is older than a day, download it
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 3, // Short timeout to avoid hanging
                            'header' => 'User-Agent: YBB-LOA-Generator'
                        ]
                    ]);

                    $imageData = @file_get_contents($customSignatureUrl, false, $context);

                    if ($imageData !== false) {
                        // Save to cache
                        file_put_contents($cachePath, $imageData);
                        $signaturePath = $cachePath;
                    } else {
                        // Download failed, use default signature
                        $signaturePath = file_exists($defaultSignaturePath) ? $defaultSignaturePath : null;
                    }
                } else {
                    // Use cached version
                    $signaturePath = $cachePath;
                }
            }

            // Process the signature if we have a valid path
            if ($signaturePath && file_exists($signaturePath)) {
                // Convert to base64
                $signatureType = pathinfo($signaturePath, PATHINFO_EXTENSION);
                $signatureData = base64_encode(file_get_contents($signaturePath));
                $signatureSrc = 'data:image/' . $signatureType . ';base64,' . $signatureData;

                // Generate image HTML with class for styling
                $signatureHtml = '<img src="' . $signatureSrc . '" alt="Signature" class="loa-signature">';
            }

            return $signatureHtml;
        } catch (\Exception $e) {
            // In case of any errors, return the text-based fallback
            log_message('error', 'Error loading signature for PDF: ' . $e->getMessage());
            return $signatureHtml;
        }
    }
}
