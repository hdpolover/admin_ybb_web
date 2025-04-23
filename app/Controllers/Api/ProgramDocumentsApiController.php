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
        // Start logging the LOA generation process
        log_message('info', "[LOA Generation] Starting LOA generation process for document ID: {$documentId}, participant ID: {$participantId}");
        
        // Increase execution time limit for PDF generation
        ini_set('max_execution_time', 300); // 5 minutes
        set_time_limit(300); // Backup approach
        log_message('info', "[LOA Generation] Execution time limit increased to 300 seconds");

        // Validate required parameters
        if ($documentId === null || $participantId === null) {
            log_message('error', "[LOA Generation] Missing required parameters. Document ID: {$documentId}, Participant ID: {$participantId}");
            return $this->failValidationErrors('Document ID and Participant ID are required');
        }

        // Get the document
        log_message('info', "[LOA Generation] Fetching program document with ID: {$documentId}");
        $document = $this->model->find($documentId);
        if (!$document) {
            log_message('error', "[LOA Generation] Program document not found with ID: {$documentId}");
            return $this->failNotFound('Program document not found');
        }
        log_message('info', "[LOA Generation] Program document found: " . json_encode($document));

        try {
            // Load participant data
            log_message('info', "[LOA Generation] Fetching participant data with ID: {$participantId}");
            $participantModel = new \App\Models\ParticipantModel();
            $participant = $participantModel->find($participantId);

            if (!$participant) {
                log_message('error', "[LOA Generation] Participant not found with ID: {$participantId}");
                return $this->failNotFound('Participant not found');
            }
            log_message('info', "[LOA Generation] Participant found: " . json_encode($participant));

            // Get program data
            log_message('info', "[LOA Generation] Fetching program data with ID: {$participant->program_id}");
            $programModel = new \App\Models\ProgramModel();
            $program = (object)$programModel->find($participant->program_id);

            if (!$program) {
                log_message('error', "[LOA Generation] Program not found with ID: {$participant->program_id}");
                return $this->failNotFound('Program not found');
            }
            log_message('info', "[LOA Generation] Program found: " . json_encode($program));

            // get program category
            log_message('info', "[LOA Generation] Fetching program category with ID: {$program->program_category_id}");
            $programCategoryModel = new \App\Models\ProgramCategoryModel();
            $programCategory = $programCategoryModel->find($program->program_category_id);

            if (!$programCategory) {
                log_message('error', "[LOA Generation] Program category not found with ID: {$program->program_category_id}");
                return $this->failNotFound('Program category not found');
            }
            log_message('info', "[LOA Generation] Program category found: " . json_encode($programCategory));

            $programData = [
                'name' => $program->name,
                'location' => $programCategory->location,
                'start_date' => $program->start_date,
                'end_date' => $program->end_date,
                'logo_url' => $programCategory->logo_url,
                'web_url' => $programCategory->web_url,
                'email' => $programCategory->email,
                'tagline' => $programCategory->tagline,
                'contact' => $programCategory->contact,
                'main_name' => $programCategory->name,
            ];
            log_message('info', "[LOA Generation] Program data prepared: " . json_encode($programData));

            // Get the LOA template
            log_message('info', "[LOA Generation] Fetching LOA template for document ID: {$documentId}");
            $loaTemplateModel = new \App\Models\LoaTemplateModel();
            $template = $loaTemplateModel->getLoaTemplateByProgramDocumentId($documentId);

            if (!$template) {
                log_message('error', "[LOA Generation] LOA template not found for document ID: {$documentId}");
                return $this->failNotFound('LOA template not found for this document');
            }
            log_message('info', "[LOA Generation] LOA template found with ID: {$template->id}");

            // Generate the LOA content with template and participant data
            log_message('info', "[LOA Generation] Generating LOA content from template");
            $loaContent = $this->generateLoaContent($template->body, $participant, $programData);
            log_message('info', "[LOA Generation] LOA content generated successfully");

            // Generate PDF
            $filename = 'LOA-' . $participant->full_name . '-' . date('Ymd') . '.pdf';
            log_message('info', "[LOA Generation] Starting PDF generation with filename: {$filename}");
            $pdfContent = $this->generateLoaPdf($loaContent, $programData);
            log_message('info', "[LOA Generation] PDF generated successfully, size: " . strlen($pdfContent) . " bytes");

            // Encode the PDF as base64 for sending in the JSON response
            log_message('info', "[LOA Generation] Encoding PDF as base64");
            $encodedPdf = base64_encode($pdfContent);
            log_message('info', "[LOA Generation] PDF encoded successfully, encoded size: " . strlen($encodedPdf) . " bytes");
            
            // Return success response with the file information
            log_message('info', "[LOA Generation] LOA generation completed successfully for participant: {$participant->full_name}");
            return $this->respondSuccess([  
                'file_name' => $filename,
                'mime_type' => 'application/pdf',
                'file_data' => $encodedPdf,
                'message' => 'LOA generated successfully'
            ]);
        } catch (\Exception $e) {
            log_message('error', "[LOA Generation] Error generating LOA: " . $e->getMessage());
            log_message('error', "[LOA Generation] Error details - File: " . $e->getFile() . ", Line: " . $e->getLine());
            log_message('error', "[LOA Generation] Stack trace: " . $e->getTraceAsString());
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
        log_message('info', "[LOA Generation] Starting content generation with template length: " . strlen($template));
        
        // Replace variables with actual data
        $content = $template;
        log_message('debug', "[LOA Generation] Processing template variables for participant: " . $participant->full_name);
        
        $content = str_replace('{{participant_name}}', strtoupper($participant->full_name), $content);
        $content = str_replace('{{program_name}}', $programData['name'], $content);
        
        $institution = $participant->institution ?? 'Youth Break the Boundaries';
        log_message('debug', "[LOA Generation] Using institution: " . $institution);
        $content = str_replace('{{institution}}', strtoupper($institution), $content);
        
        $content = str_replace('{{today_date}}', date('F d, Y'), $content);
        
        $startDate = isset($programData['start_date']) ? date("F d, Y", strtotime($programData['start_date'])) : "April 30, 2025";
        log_message('debug', "[LOA Generation] Using start date: " . $startDate);
        $content = str_replace('{{start_date}}', $startDate, $content);
        
        $endDate = isset($programData['end_date']) ? date("F d, Y", strtotime($programData['end_date'])) : "December 31, 2025";
        log_message('debug', "[LOA Generation] Using end date: " . $endDate);
        $content = str_replace('{{end_date}}', $endDate, $content);
        
        // program location
        $location = isset($programData['location']) ? $programData['location'] : 'Youth Break the Boundaries';
        log_message('debug', "[LOA Generation] Using program location: " . $location);
        $content = str_replace('{{program_location}}', $location, $content);
        
        log_message('info', "[LOA Generation] Content generation completed, final content length: " . strlen($content));
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
        log_message('info', "[LOA Generation] Starting PDF generation with content length: " . strlen($loaContent));

        try {
            // Generate HTML content with header and footer
            log_message('info', "[LOA Generation] Getting PDF template with header and footer");
            $htmlContent = $this->getLoaPdfTemplate($loaContent, $programData);
            log_message('debug', "[LOA Generation] Full HTML template length: " . strlen($htmlContent));

            // Create PDF using Dompdf with optimized settings
            log_message('info', "[LOA Generation] Initializing Dompdf");
            $dompdf = new \Dompdf\Dompdf();
            $options = $dompdf->getOptions();
            
            // Configure Dompdf options
            log_message('debug', "[LOA Generation] Setting Dompdf options");
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
            log_message('info', "[LOA Generation] Loading HTML content into Dompdf");
            $dompdf->loadHtml($htmlContent);
            log_message('debug', "[LOA Generation] Setting paper size to A4 portrait");
            $dompdf->setPaper('A4', 'portrait');

            // Render PDF (with memory optimization)
            log_message('info', "[LOA Generation] Starting PDF rendering process");
            $dompdf->render();
            log_message('info', "[LOA Generation] PDF rendering completed successfully");

            // Get the PDF content
            log_message('info', "[LOA Generation] Retrieving PDF output");
            $output = $dompdf->output();
            log_message('info', "[LOA Generation] PDF generation completed successfully, output size: " . strlen($output) . " bytes");
            
            return $output;
        } catch (\Exception $e) {
            log_message('error', "[LOA Generation] Error in PDF generation: " . $e->getMessage());
            log_message('error', "[LOA Generation] Error location: " . $e->getFile() . " on line " . $e->getLine());
            log_message('error', "[LOA Generation] Stack trace: " . $e->getTraceAsString());
            throw $e; // Re-throw to be caught by the main try-catch block
        }
    }
    /**
     * Get full HTML template for LOA PDF
     * @param string $bodyContent The main content of the LOA
     * @param string $programLogoUrl Optional URL for program logo
     * @return string Complete HTML structure for PDF
     */
    private function getLoaPdfTemplate($bodyContent, $programData)
    {
        log_message('info', "[LOA Generation] Starting HTML template generation for PDF");
        try {
            // Get program logo with an efficient approach that supports network images
            log_message('info', "[LOA Generation] Getting optimized logo, URL: " . ($programData['logo_url'] ?? 'none'));
            $logoImg = $this->getOptimizedLogoForPdf($programData['logo_url']);
            log_message('debug', "[LOA Generation] Logo HTML generated, length: " . strlen($logoImg));

            // Get optimized signature image
            log_message('info', "[LOA Generation] Getting optimized signature image");
            $signatureImg = $this->getOptimizedSignatureForPdf();
            log_message('debug', "[LOA Generation] Signature HTML generated, length: " . strlen($signatureImg));

            // Add Quill editor styles to preserve formatting
            log_message('debug', "[LOA Generation] Adding CSS styles for proper formatting");
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
            log_message('info', "[LOA Generation] Loading view template for PDF rendering");
            $view = \Config\Services::renderer();
            log_message('debug', "[LOA Generation] Setting view variables: logo, signature, body content, and styles");
            
            // Render the view template with all necessary variables
            try {
                log_message('debug', "[LOA Generation] Rendering view template with body content length: " . strlen($bodyContent));
                $renderedTemplate = $view->setVar('logoImg', $logoImg)
                    ->setVar('signatureImg', $signatureImg)
                    ->setVar('bodyContent', $bodyContent)
                    ->setVar('quillStyles', $quillStyles)
                    ->setVar('programData', $programData)
                    ->render('documents/program-documents/main_loa_template');
                
                log_message('info', "[LOA Generation] View template rendered successfully, length: " . strlen($renderedTemplate));
                return $renderedTemplate;
            } catch (\Exception $e) {
                log_message('error', "[LOA Generation] Error rendering view template: " . $e->getMessage());
                log_message('error', "[LOA Generation] Error location: " . $e->getFile() . " on line " . $e->getLine());
                throw $e;
            }
        } catch (\Exception $e) {
            log_message('error', "[LOA Generation] Error generating HTML template: " . $e->getMessage());
            log_message('error', "[LOA Generation] Error location: " . $e->getFile() . " on line " . $e->getLine());
            throw $e;
        }
    }
    /**
     * Get optimized logo for PDF generation
     * Efficiently handles both local and network images
     * 
     * @param string $programLogoUrl Optional URL for program logo
     * @return string HTML element with optimized image
     */    private function getOptimizedLogoForPdf($programLogoUrl = null)
    {
        // Default text-based fallback
        $logoHtml = '<h3 style="font-size: 11pt;">Youth Break the Boundaries</h3>';
        
        try {
            // Normalize default logo path - try multiple approaches for better compatibility
            $defaultLogoPath = FCPATH . 'assets/images/logo-dark.png';
            $altDefaultLogoPath = dirname(APPPATH) . '/public/assets/images/logo-dark.png';
            
            // Create a log entry for debugging in production
            log_message('info', 'Logo process started. URL: ' . ($programLogoUrl ?? 'none') . 
                       ', Default path: ' . $defaultLogoPath . 
                       ', Alt path: ' . $altDefaultLogoPath);
            
            // Direct Base64 embedding approach - most reliable for hosting environments
            if (!empty($programLogoUrl)) {
                // Handle both absolute and relative URLs
                if (strpos($programLogoUrl, 'http') !== 0) {
                    // Convert relative URL to absolute
                    $base_url = base_url();
                    $programLogoUrl = rtrim($base_url, '/') . '/' . ltrim($programLogoUrl, '/');
                }
                
                log_message('info', 'Attempting to fetch remote logo: ' . $programLogoUrl);
                
                // Try to fetch with cURL first (more reliable on hosting)
                $logoData = $this->fetchImageWithCurl($programLogoUrl);
                
                // If cURL failed, try file_get_contents
                if ($logoData === false) {
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 3,
                            'header' => 'User-Agent: YBB-LOA-Generator'
                        ],
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false
                        ]
                    ]);
                    
                    $logoData = @file_get_contents($programLogoUrl, false, $context);
                    log_message('info', 'file_get_contents result: ' . ($logoData ? 'success' : 'failed'));
                }
                
                // If we got the image data, convert it to base64
                if ($logoData !== false) {
                    // Determine image type from content or default to png
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $logoType = $finfo->buffer($logoData);
                    $logoType = str_replace('image/', '', $logoType);
                    
                    // Encode to base64
                    $logoBase64 = base64_encode($logoData);
                    $logoHtml = '<img src="data:image/' . $logoType . ';base64,' . $logoBase64 . '" alt="Logo" class="loa-logo">';
                    
                    log_message('info', 'Successfully embedded remote logo as base64');
                    return $logoHtml;
                }
                
                log_message('warning', 'Failed to fetch remote logo, falling back to default');
            }
            
            // Try to use default logo if remote failed or wasn't specified
            if (file_exists($defaultLogoPath)) {
                $logoPath = $defaultLogoPath;
            } elseif (file_exists($altDefaultLogoPath)) {
                $logoPath = $altDefaultLogoPath;
            } else {
                // Last resort - try to find logo in common locations
                $possiblePaths = [
                    ROOTPATH . 'public/assets/images/logo-dark.png',
                    ROOTPATH . 'assets/images/logo-dark.png',
                    $_SERVER['DOCUMENT_ROOT'] . '/assets/images/logo-dark.png'
                ];
                
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $logoPath = $path;
                        break;
                    }
                }
            }
            
            // If we have a local file, use it
            if (isset($logoPath) && file_exists($logoPath)) {
                log_message('info', 'Using local logo file: ' . $logoPath);
                
                $logoData = file_get_contents($logoPath);
                $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
                $logoBase64 = base64_encode($logoData);
                
                $logoHtml = '<img src="data:image/' . $logoType . ';base64,' . $logoBase64 . '" alt="Logo" class="loa-logo">';
                return $logoHtml;
            }
            
            log_message('warning', 'All logo attempts failed, using text fallback');
            return $logoHtml;
        } catch (\Exception $e) {
            // Detailed error logging for production debugging
            log_message('error', 'Error loading logo for PDF: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return $logoHtml;
        }
    }
    
    /**
     * Helper method to fetch an image using cURL
     * More reliable than file_get_contents in many hosting environments
     * 
     * @param string $url The URL to fetch
     * @return string|false The image data or false on failure
     */
    private function fetchImageWithCurl($url)
    {
        if (!function_exists('curl_init')) {
            log_message('info', 'cURL not available for image fetching');
            return false;
        }
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'YBB-LOA-Generator');
            
            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            log_message('info', 'cURL fetch result: HTTP ' . $httpCode);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return $data;
            }
            return false;
        } catch (\Exception $e) {
            log_message('error', 'cURL error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get optimized signature image for PDF generation
     * Efficiently handles the chairman signature image
     * 
     * @param string $customSignatureUrl Optional URL for custom signature
     * @return string HTML element with optimized signature
     */    private function getOptimizedSignatureForPdf($customSignatureUrl = null)
    {
        // Default text-based fallback
        $signatureHtml = '<p style="font-style: italic;">Signature not available</p>';
        
        try {
            // Normalize default signature path - try multiple approaches for better compatibility
            $defaultSignaturePath = FCPATH . 'assets/ybb/ttd_aldi.png';
            $altDefaultSignaturePath = dirname(APPPATH) . '/public/assets/ybb/ttd_aldi.png';
            
            // Create a log entry for debugging in production
            log_message('info', 'Signature process started. URL: ' . ($customSignatureUrl ?? 'none') . 
                       ', Default path: ' . $defaultSignaturePath . 
                       ', Alt path: ' . $altDefaultSignaturePath);
            
            // Direct Base64 embedding approach - most reliable for hosting environments
            if (!empty($customSignatureUrl)) {
                // Handle both absolute and relative URLs
                if (strpos($customSignatureUrl, 'http') !== 0) {
                    // Convert relative URL to absolute
                    $base_url = base_url();
                    $customSignatureUrl = rtrim($base_url, '/') . '/' . ltrim($customSignatureUrl, '/');
                }
                
                log_message('info', 'Attempting to fetch remote signature: ' . $customSignatureUrl);
                
                // Try to fetch with cURL first (more reliable on hosting)
                $signatureData = $this->fetchImageWithCurl($customSignatureUrl);
                
                // If cURL failed, try file_get_contents
                if ($signatureData === false) {
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 3,
                            'header' => 'User-Agent: YBB-LOA-Generator'
                        ],
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false
                        ]
                    ]);
                    
                    $signatureData = @file_get_contents($customSignatureUrl, false, $context);
                    log_message('info', 'file_get_contents result: ' . ($signatureData ? 'success' : 'failed'));
                }
                
                // If we got the image data, convert it to base64
                if ($signatureData !== false) {
                    // Determine image type from content or default to png
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $signatureType = $finfo->buffer($signatureData);
                    $signatureType = str_replace('image/', '', $signatureType);
                    
                    // Encode to base64
                    $signatureBase64 = base64_encode($signatureData);
                    $signatureHtml = '<img src="data:image/' . $signatureType . ';base64,' . $signatureBase64 . '" alt="Signature" class="loa-signature">';
                    
                    log_message('info', 'Successfully embedded remote signature as base64');
                    return $signatureHtml;
                }
                
                log_message('warning', 'Failed to fetch remote signature, falling back to default');
            }
            
            // Try to use default signature if remote failed or wasn't specified
            if (file_exists($defaultSignaturePath)) {
                $signaturePath = $defaultSignaturePath;
            } elseif (file_exists($altDefaultSignaturePath)) {
                $signaturePath = $altDefaultSignaturePath;
            } else {
                // Last resort - try to find signature in common locations
                $possiblePaths = [
                    ROOTPATH . 'public/assets/ybb/ttd_aldi.png',
                    ROOTPATH . 'assets/ybb/ttd_aldi.png',
                    $_SERVER['DOCUMENT_ROOT'] . '/assets/ybb/ttd_aldi.png'
                ];
                
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $signaturePath = $path;
                        break;
                    }
                }
            }
            
            // If we have a local file, use it
            if (isset($signaturePath) && file_exists($signaturePath)) {
                log_message('info', 'Using local signature file: ' . $signaturePath);
                
                $signatureData = file_get_contents($signaturePath);
                $signatureType = pathinfo($signaturePath, PATHINFO_EXTENSION);
                $signatureBase64 = base64_encode($signatureData);
                
                $signatureHtml = '<img src="data:image/' . $signatureType . ';base64,' . $signatureBase64 . '" alt="Signature" class="loa-signature">';
                return $signatureHtml;
            }
            
            log_message('warning', 'All signature attempts failed, using text fallback');
            return $signatureHtml;
        } catch (\Exception $e) {
            // Detailed error logging for production debugging
            log_message('error', 'Error loading signature for PDF: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return $signatureHtml;
        }
    }
}
