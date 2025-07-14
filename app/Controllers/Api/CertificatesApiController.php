<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ParticipantAwardModel;
use App\Models\ProgramCertificateModel;
use App\Models\ParticipantModel;
use App\Models\ProgramModel;
use App\Models\ProgramAwardModel;

class CertificatesApiController extends ApiBaseController
{
    protected $participantAwardModel;
    protected $programCertificateModel;
    protected $participantModel;
    protected $programModel;
    protected $programAwardModel;

    /**
     * Initialize controller, set models
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);

        // Initialize models
        $this->participantAwardModel = new ParticipantAwardModel();
        $this->programCertificateModel = new ProgramCertificateModel();
        $this->participantModel = new ParticipantModel();
        $this->programModel = new ProgramModel();
        $this->programAwardModel = new ProgramAwardModel();
    }

    /**
     * 🟢 Get All Participant Certificates (READ)
     * GET /api/certificates/participant/{participantId}
     */
    public function getParticipantCertificates($participantId = null)
    {
        log_message('info', "[Certificate API] Getting certificates for participant ID: {$participantId}");

        try {
            // Validate participant ID
            if ($participantId === null) {
                return $this->failValidationErrors('Participant ID is required');
            }

            // Check if participant exists
            $participant = $this->participantModel->find($participantId);
            if (!$participant) {
                return $this->failNotFound('Participant not found');
            }

            // Get participant certificates with full details
            $certificates = $this->participantAwardModel->getParticipantAwards($participantId);

            log_message('info', "[Certificate API] Found " . count($certificates) . " certificates for participant ID: {$participantId}");

            return $this->respondSuccess([
                'participant' => [
                    'id' => $participant->id,
                    'full_name' => $participant->full_name,
                    'account_id' => $participant->account_id
                ],
                'certificates' => $certificates,
                'total_count' => count($certificates)
            ]);

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error getting participant certificates: " . $e->getMessage());
            return $this->fail('Failed to retrieve participant certificates', 500);
        }
    }

    /**
     * 🟢 Get All Program Certificates (READ)
     * GET /api/certificates/program/{programId}
     */
    public function getProgramCertificates($programId = null)
    {
        log_message('info', "[Certificate API] Getting certificates for program ID: {$programId}");

        try {
            // Validate program ID
            if ($programId === null) {
                return $this->failValidationErrors('Program ID is required');
            }

            // Check if program exists
            $program = $this->programModel->find($programId);
            if (!$program) {
                return $this->failNotFound('Program not found');
            }

            // Get program awards (these can have certificates)
            $awards = $this->programAwardModel->where('program_id', $programId)
                                             ->where('is_active', 1)
                                             ->where('is_deleted', 0)
                                             ->findAll();

            log_message('info', "[Certificate API] Found " . count($awards) . " awards for program ID: {$programId}");

            return $this->respondSuccess([
                'program' => [
                    'id' => $program->id,
                    'name' => $program->name
                ],
                'awards' => $awards,
                'total_count' => count($awards)
            ]);

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error getting program certificates: " . $e->getMessage());
            return $this->fail('Failed to retrieve program certificates', 500);
        }
    }

    /**
     * 🔍 Get Participants Eligible for Certificate by Award
     * GET /api/certificates/award/{awardId}/participants
     */
    public function getCertificateParticipants($awardId = null)
    {
        log_message('info', "[Certificate API] Getting participants for award ID: {$awardId}");

        try {
            // Validate award ID
            if ($awardId === null) {
                return $this->failValidationErrors('Award ID is required');
            }

            // Check if award exists
            $award = $this->programAwardModel->find($awardId);
            if (!$award) {
                return $this->failNotFound('Award not found');
            }

            // Get participants assigned to this award
            $participants = $this->participantAwardModel->getAwardParticipants($awardId);

            log_message('info', "[Certificate API] Found " . count($participants) . " participants for award ID: {$awardId}");

            return $this->respondSuccess([
                'award' => [
                    'id' => $award->id,
                    'title' => $award->title,
                    'award_type' => $award->award_type
                ],
                'participants' => $participants,
                'total_count' => count($participants)
            ]);

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error getting certificate participants: " . $e->getMessage());
            return $this->fail('Failed to retrieve certificate participants', 500);
        }
    }

    /**
     * 🔍 Get Single Certificate Details (READ)
     * GET /api/certificates/{certificateId}
     */
    public function getCertificateDetails($certificateId = null)
    {
        log_message('info', "[Certificate API] Getting certificate details for ID: {$certificateId}");

        try {
            // Validate certificate ID
            if ($certificateId === null) {
                return $this->failValidationErrors('Certificate ID is required');
            }

            // Get certificate with full details (from participant_awards table)
            $certificate = $this->participantAwardModel
                                ->select('participant_awards.*, program_awards.title as award_title, program_awards.description as award_description,
                                         program_awards.award_type, participants.full_name, participants.account_id,
                                         program_certificates.template_data')
                                ->join('program_awards', 'program_awards.id = participant_awards.award_id', 'left')
                                ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                                ->join('program_certificates', 'program_certificates.award_id = participant_awards.award_id', 'left')
                                ->where('participant_awards.id', $certificateId)
                                ->where('participant_awards.is_deleted', 0)
                                ->first();

            if (!$certificate) {
                return $this->failNotFound('Certificate not found');
            }

            log_message('info', "[Certificate API] Certificate details retrieved for ID: {$certificateId}");

            return $this->respondSuccess($certificate);

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error getting certificate details: " . $e->getMessage());
            return $this->fail('Failed to retrieve certificate details', 500);
        }
    }

    /**
     * 📄 Generate Certificate PDF
     * POST /api/certificates/generate
     * Body: { "participant_id": 123, "award_id": 456 }
     */
    public function generateCertificate()
    {
        log_message('info', "[Certificate API] Starting certificate generation");

        try {
            // Increase execution time limit for PDF generation
            ini_set('max_execution_time', 300);
            set_time_limit(300);

            // Get request data
            $participantId = $this->request->getPost('participant_id') ?? $this->request->getJSON(true)['participant_id'] ?? null;
            $awardId = $this->request->getPost('award_id') ?? $this->request->getJSON(true)['award_id'] ?? null;

            // Validate required parameters
            if (!$participantId || !$awardId) {
                return $this->failValidationErrors('Participant ID and Award ID are required');
            }

            log_message('info', "[Certificate API] Generating certificate for participant ID: {$participantId}, award ID: {$awardId}");

            // Check if participant exists
            $participant = $this->participantModel->find($participantId);
            if (!$participant) {
                return $this->failNotFound('Participant not found');
            }

            // Check if award exists
            $award = $this->programAwardModel->find($awardId);
            if (!$award) {
                return $this->failNotFound('Award not found');
            }

            // Check if participant is assigned to this award (eligible for certificate)
            $isAssigned = $this->participantAwardModel->where('participant_id', $participantId)
                                                      ->where('award_id', $awardId)
                                                      ->where('is_deleted', 0)
                                                      ->first();
            if (!$isAssigned) {
                return $this->failNotFound('Participant is not assigned to this award');
            }

            // Check if certificate has already been generated (by checking certificate_path)
            $existingCertificate = $this->participantAwardModel->where('participant_id', $participantId)
                                                               ->where('award_id', $awardId)
                                                               ->where('certificate_path IS NOT NULL')
                                                               ->where('is_deleted', 0)
                                                               ->first();

            if ($existingCertificate) {
                log_message('info', "[Certificate API] Certificate already exists for participant ID: {$participantId}, award ID: {$awardId}");
                return $this->respondSuccess([
                    'message' => 'Certificate already exists',
                    'certificate_id' => $existingCertificate->id,
                    'generated_at' => $existingCertificate->generated_at
                ]);
            }

            // Get program data for certificate generation
            $program = $this->programModel->find($participant->program_id);
            if (!$program) {
                return $this->failNotFound('Program not found for participant');
            }

            // Generate certificate content
            $certificateContent = $this->generateCertificateContent($participant, $award, $program);

            // Generate PDF (we'll use a default template since we're not using program_certificates table)
            $filename = 'Certificate-' . $participant->full_name . '-' . $award->title . '-' . date('Ymd') . '.pdf';
            $pdfContent = $this->generateCertificatePdf($certificateContent, null);

            // Save certificate record to database
            // Update participant award with certificate path and generated timestamp
            $updateData = [
                'certificate_path' => $filename,
                'certificate_generated_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->participantAwardModel->where('participant_id', $participantId)
                                                  ->where('award_id', $awardId)
                                                  ->set($updateData)
                                                  ->update();

            if (!$result) {
                return $this->fail('Failed to update certificate record', 500);
            }

            // Encode PDF as base64
            $encodedPdf = base64_encode($pdfContent);

            log_message('info', "[Certificate API] Certificate generated successfully for participant: {$participant->full_name}");

            return $this->respondSuccess([
                'certificate_id' => $isAssigned->id, // Use the participant award ID
                'file_name' => $filename,
                'mime_type' => 'application/pdf',
                'file_data' => $encodedPdf,
                'message' => 'Certificate generated successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error generating certificate: " . $e->getMessage());
            log_message('error', "[Certificate API] Stack trace: " . $e->getTraceAsString());
            return $this->fail('Failed to generate certificate: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 🗑️ Revoke Certificate (SOFT DELETE)
     * DELETE /api/certificates/{certificateId}
     */
    public function revokeCertificate($certificateId = null)
    {
        log_message('info', "[Certificate API] Revoking certificate ID: {$certificateId}");

        try {
            // Validate certificate ID
            if ($certificateId === null) {
                return $this->failValidationErrors('Certificate ID is required');
            }

            // Check if certificate exists
            $certificate = $this->participantAwardModel->find($certificateId);
            if (!$certificate) {
                return $this->failNotFound('Certificate not found');
            }

            // Clear certificate data (revoke certificate)
            $result = $this->participantAwardModel->update($certificateId, [
                'certificate_path' => null,
                'certificate_generated_at' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$result) {
                return $this->fail('Failed to revoke certificate', 500);
            }

            log_message('info', "[Certificate API] Certificate revoked successfully: {$certificateId}");

            return $this->respondSuccess([
                'message' => 'Certificate revoked successfully',
                'certificate_id' => $certificateId
            ]);

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error revoking certificate: " . $e->getMessage());
            return $this->fail('Failed to revoke certificate', 500);
        }
    }

    /**
     * 📋 Get Certificate History/Stats
     * GET /api/certificates/stats/{participantId}
     */
    public function getCertificateStats($participantId = null)
    {
        log_message('info', "[Certificate API] Getting certificate stats for participant ID: {$participantId}");

        try {
            // Validate participant ID
            if ($participantId === null) {
                return $this->failValidationErrors('Participant ID is required');
            }

            // Check if participant exists
            $participant = $this->participantModel->find($participantId);
            if (!$participant) {
                return $this->failNotFound('Participant not found');
            }

            // Get all certificates for participant (awards with certificate data)
            $certificates = $this->participantAwardModel
                ->select('participant_awards.*, program_awards.title as award_title, 
                         program_awards.description as award_description, program_awards.award_type,
                         programs.name as program_name, program_certificates.template_data')
                ->join('program_awards', 'program_awards.id = participant_awards.award_id')
                ->join('programs', 'programs.id = program_awards.program_id')
                ->join('program_certificates', 'program_certificates.award_id = participant_awards.award_id', 'left')
                ->where('participant_awards.participant_id', $participantId)
                ->where('participant_awards.is_deleted', 0)
                ->findAll();

            // Calculate stats
            $stats = [
                'total_certificates' => count($certificates),
                'generated_certificates' => count(array_filter($certificates, function($cert) {
                    return !empty($cert->certificate_path);
                })),
                'pending_certificates' => count(array_filter($certificates, function($cert) {
                    return empty($cert->certificate_path);
                })),
                'latest_certificate' => !empty($certificates) ? $certificates[0] : null,
                'certificate_list' => $certificates
            ];

            log_message('info', "[Certificate API] Certificate stats retrieved for participant ID: {$participantId}");

            return $this->respondSuccess($stats);

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error getting certificate stats: " . $e->getMessage());
            return $this->fail('Failed to retrieve certificate stats', 500);
        }
    }

    /**
     * 🔄 Regenerate Certificate
     * POST /api/certificates/{certificateId}/regenerate
     */
    public function regenerateCertificate($certificateId = null)
    {
        log_message('info', "[Certificate API] Regenerating certificate ID: {$certificateId}");

        try {
            // Validate certificate ID
            if ($certificateId === null) {
                return $this->failValidationErrors('Certificate ID is required');
            }

            // Get existing certificate (from participant_awards table)
            $existingCertificate = $this->participantAwardModel->find($certificateId);
            if (!$existingCertificate) {
                return $this->failNotFound('Certificate not found');
            }

            // Get participant and award data
            $participant = $this->participantModel->find($existingCertificate->participant_id);
            $award = $this->programAwardModel->find($existingCertificate->award_id);
            $program = $this->programModel->find($participant->program_id);

            // Generate new certificate content
            $certificateContent = $this->generateCertificateContent($participant, $award, $program);

            // Generate PDF
            $filename = 'Certificate-' . $participant->full_name . '-' . $award->title . '-' . date('Ymd') . '.pdf';
            $pdfContent = $this->generateCertificatePdf($certificateContent, null);

            // Update certificate record
            $updateData = [
                'certificate_path' => $filename,
                'certificate_generated_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->participantAwardModel->update($certificateId, $updateData);

            if (!$result) {
                return $this->fail('Failed to update certificate record', 500);
            }

            // Encode PDF as base64
            $encodedPdf = base64_encode($pdfContent);

            log_message('info', "[Certificate API] Certificate regenerated successfully: {$certificateId}");

            return $this->respondSuccess([
                'certificate_id' => $certificateId,
                'file_name' => $filename,
                'mime_type' => 'application/pdf',
                'file_data' => $encodedPdf,
                'message' => 'Certificate regenerated successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error regenerating certificate: " . $e->getMessage());
            return $this->fail('Failed to regenerate certificate', 500);
        }
    }

    /**
     * Generate certificate content with participant data
     * @param object $participant Participant object
     * @param object $award Award object
     * @param object $program Program object
     * @return string
     */
    private function generateCertificateContent($participant, $award, $program)
    {
        log_message('info', "[Certificate API] Generating certificate content for participant: " . $participant->full_name);

        // Format dates
        $startDate = isset($program->start_date) ? date("F d, Y", strtotime($program->start_date)) : date("F d, Y");
        $endDate = isset($program->end_date) ? date("F d, Y", strtotime($program->end_date)) : date("F d, Y");
        
        // Create the award type text
        $awardTypeText = ucwords(str_replace('_', ' ', $award->award_type ?? 'Achievement'));
        
        // Generate certificate content
        $content = '<div class="award-title">' . esc($award->title ?? 'Excellence Award') . '</div>
        
        <p class="cert-text">This is to certify that</p>
        
        <div class="participant-name">' . esc($participant->full_name) . '</div>
        
        <p class="cert-text">has successfully demonstrated outstanding performance and completed all requirements for the</p>
        
        <div class="program-name">' . esc($program->name ?? 'Youth Break the Boundaries Program') . '</div>
        
        <p class="cert-text">Program Duration: ' . $startDate . ' to ' . $endDate . '</p>';
        
        // Add description if available
        if (!empty($award->description)) {
            $content .= '<div class="description">' . esc($award->description) . '</div>';
        }
        
        $content .= '<p class="cert-text">This certificate is awarded in recognition of exceptional dedication, leadership, and achievement in the program.</p>';

        return $content;
    }

    /**
     * Generate PDF from certificate content
     * @param string $content HTML content for the certificate
     * @param object $template Certificate template object
     * @return string PDF content as binary string
     */
    private function generateCertificatePdf($content, $template)
    {
        log_message('info', "[Certificate API] Starting PDF generation for certificate");

        try {
            // Generate HTML content with styling
            $htmlContent = $this->getCertificatePdfTemplate($content, $template);

            // Create PDF using Dompdf
            $dompdf = new \Dompdf\Dompdf();
            $options = $dompdf->getOptions();
            
            // Configure Dompdf options
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('isFontSubsettingEnabled', true);
            $options->set('defaultMediaType', 'print');
            $dompdf->setOptions($options);

            // Load HTML content
            $dompdf->loadHtml($htmlContent);
            $dompdf->setPaper('A4', 'landscape'); // Certificates are usually landscape

            // Render PDF
            $dompdf->render();

            // Get the PDF content
            $output = $dompdf->output();
            
            log_message('info', "[Certificate API] PDF generation completed, size: " . strlen($output) . " bytes");
            
            return $output;

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error in PDF generation: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get full HTML template for certificate PDF
     * @param string $content The main content of the certificate
     * @param object $template Certificate template object
     * @return string Complete HTML structure for PDF
     */
    private function getCertificatePdfTemplate($content, $template)
    {
        // Get current date for certificate
        $currentDate = date('F d, Y');
        $certificateId = 'CERT-' . date('Ymd-His') . '-' . uniqid();
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate</title>
    <style>
        @page {
            margin: 0.5in;
            size: A4 landscape;
        }
        body {
            font-family: "Times New Roman", serif;
            margin: 0;
            padding: 20px;
            background: #ffffff;
        }
        .certificate-container {
            width: 100%;
            height: 100%;
            border: 15px solid #1e3a8a;
            padding: 40px;
            text-align: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            box-sizing: border-box;
            position: relative;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .header {
            margin-bottom: 30px;
        }
        .certificate-title {
            font-size: 42px;
            color: #1e3a8a;
            margin-bottom: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 4px;
        }
        .award-title {
            font-size: 32px;
            color: #059669;
            margin-bottom: 40px;
            font-style: italic;
            font-weight: normal;
        }
        .cert-text {
            font-size: 20px;
            color: #374151;
            margin: 15px 0;
            line-height: 1.6;
        }
        .participant-name {
            font-size: 36px;
            color: #dc2626;
            margin: 25px 0;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .program-name {
            font-size: 28px;
            color: #7c3aed;
            margin: 25px 0;
            font-weight: bold;
            font-style: italic;
        }
        .description {
            font-size: 18px;
            color: #6b7280;
            margin: 20px 0;
            font-style: italic;
            max-width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            position: absolute;
            bottom: 40px;
            left: 40px;
            right: 40px;
        }
        .signature-section {
            text-align: center;
            width: 250px;
        }
        .signature-line {
            border-top: 2px solid #000;
            margin-bottom: 10px;
            padding-top: 10px;
        }
        .signature-title {
            font-size: 14px;
            color: #374151;
            font-weight: bold;
        }
        .cert-info {
            text-align: right;
            font-size: 12px;
            color: #6b7280;
        }
        .date-section {
            margin-top: 30px;
            font-size: 16px;
            color: #374151;
        }
        .decorative-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 3px solid #d1d5db;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="decorative-border"></div>
        
        <div class="header">
            <div class="certificate-title">Certificate of Achievement</div>
        </div>
        
        ' . $content . '
        
        <div class="date-section">
            <p>Issued on: <strong>' . $currentDate . '</strong></p>
        </div>
        
        <div class="footer">
            <div class="signature-section">
                <div class="signature-line">Authorized Signature</div>
                <div class="signature-title">Program Director</div>
            </div>
            <div class="cert-info">
                <p>Certificate ID: ' . $certificateId . '</p>
                <p>Youth Break the Boundaries</p>
            </div>
        </div>
    </div>
</body>
</html>';

        return $html;
    }
}
