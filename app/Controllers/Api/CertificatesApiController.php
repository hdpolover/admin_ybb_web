<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Services\CertificateService;
use App\Models\ParticipantModel;
use App\Models\ProgramModel;
use App\Models\ProgramAwardModel;
use App\Models\ProgramCertificateModel;
use App\Models\ProgramCertificateContentBlockModel;
use App\Models\ParticipantAwardModel;

class CertificatesApiController extends ApiBaseController
{
    protected CertificateService $certificateService;
    protected ParticipantModel $participantModel;
    protected ProgramModel $programModel;
    protected ProgramAwardModel $programAwardModel;
    protected ProgramCertificateModel $programCertificateModel;
    protected ProgramCertificateContentBlockModel $contentBlockModel;
    protected ParticipantAwardModel $participantAwardModel;

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

        // Initialize service and models
        $this->certificateService = new CertificateService();
        $this->participantModel = new ParticipantModel();
        $this->programModel = new ProgramModel();
        $this->programAwardModel = new ProgramAwardModel();
        $this->programCertificateModel = new ProgramCertificateModel();
        $this->contentBlockModel = new ProgramCertificateContentBlockModel();
        $this->participantAwardModel = new ParticipantAwardModel();
    }

    /**
     * 📄 Generate Certificate PDF using Python Service
     * POST /api/certificates/generate
     * Body: { "participant_id": 123, "award_id": 456 }
     */
    public function generateCertificate()
    {
        log_message('info', "[Certificate API] Starting certificate generation via Python service");

        try {
            $data = $this->request->getJSON(true);
            
            // Validate required parameters
            $participantId = $data['participant_id'] ?? null;
            $awardId = $data['award_id'] ?? null;
            
            if (!$participantId || !$awardId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Participant ID and Award ID are required'
                ])->setStatusCode(400);
            }
            
            // Build certificate data from database
            $certificateData = $this->buildCertificateData($participantId, $awardId);
            
            if (!$certificateData['success']) {
                return $this->response->setJSON($certificateData)->setStatusCode(404);
            }
            
            // Generate certificate using Python service
            $result = $this->certificateService->generateCertificate($certificateData['data']);
            
            if ($result['success']) {
                // Save certificate record (create participant_awards record if it doesn't exist)
                $this->ensureParticipantAward($participantId, $awardId);
                
                log_message('info', "[Certificate API] Certificate generated successfully via Python service");
                
                return $this->response->setJSON([
                    'success' => true,
                    'data' => [
                        'certificate_id' => $result['data']['certificate_id'],
                        'file_name' => $result['data']['file_name'],
                        'file_size' => $result['data']['file_size'],
                        'file_data' => $result['data']['file_data'], // Base64 encoded PDF
                        'generated_at' => $result['data']['generated_at'],
                        'participant_name' => $certificateData['data']['participant']['full_name'],
                        'award_title' => $certificateData['data']['award']['title'],
                        'program_name' => $certificateData['data']['program']['name']
                    ]
                ]);
            } else {
                log_message('error', "[Certificate API] Certificate generation failed: " . 
                           ($result['error']['message'] ?? 'Unknown error'));
                return $this->response->setJSON($result)->setStatusCode(400);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Certificate generation controller error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Certificate generation failed'
            ])->setStatusCode(500);
        }
    }

    /**
     * 🏥 Health check endpoint
     * GET /api/certificates/health
     */
    public function health()
    {
        log_message('info', "[Certificate API] Health check requested");
        
        $health = $this->certificateService->checkHealth();
        return $this->response->setJSON($health);
    }

    /**
     * 🔤 Get available placeholders
     * GET /api/certificates/placeholders
     */
    public function getPlaceholders()
    {
        log_message('info', "[Certificate API] Placeholders requested");
        
        $placeholders = $this->certificateService->getAvailablePlaceholders();
        return $this->response->setJSON($placeholders);
    }

    /**
     * 👤 Get participant certificates (based on participant_awards)
     * GET /api/certificates/participant/{participantId}
     */
    public function getParticipantCertificates($participantId = null)
    {
        log_message('info', "[Certificate API] Getting certificates for participant ID: {$participantId}");

        try {
            if ($participantId === null) {
                return $this->failValidationErrors('Participant ID is required');
            }

            // Get participant awards (these represent certificates they can generate)
            $certificates = $this->participantAwardModel->select('
                    participant_awards.*,
                    program_awards.title as award_title,
                    program_awards.description as award_description,
                    program_awards.award_type,
                    participants.full_name as participant_name,
                    programs.name as program_name
                ')
                ->join('program_awards', 'program_awards.id = participant_awards.award_id', 'left')
                ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                ->join('programs', 'programs.id = participants.program_id', 'left')
                ->where('participant_awards.participant_id', $participantId)
                ->where('participant_awards.is_active', 1)
                ->where('participant_awards.is_deleted', 0)
                ->findAll();

            log_message('info', "[Certificate API] Found " . count($certificates) . " certificates for participant: {$participantId}");

            return $this->respondSuccess($certificates);

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error getting participant certificates: " . $e->getMessage());
            return $this->fail('Failed to retrieve participant certificates', 500);
        }
    }

    /**
     * 🏆 Get participants assigned to an award (eligible for certificates)
     * GET /api/certificates/award/{awardId}/participants
     */
    public function getCertificateParticipants($awardId = null)
    {
        log_message('info', "[Certificate API] Getting participants for award ID: {$awardId}");

        try {
            if ($awardId === null) {
                return $this->failValidationErrors('Award ID is required');
            }

            $participants = $this->participantAwardModel->select('
                    participant_awards.*,
                    participants.full_name as participant_name,
                    participants.account_id,
                    participants.nationality,
                    participants.institution,
                    program_awards.title as award_title
                ')
                ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                ->join('program_awards', 'program_awards.id = participant_awards.award_id', 'left')
                ->where('participant_awards.award_id', $awardId)
                ->where('participant_awards.is_active', 1)
                ->where('participant_awards.is_deleted', 0)
                ->findAll();

            log_message('info', "[Certificate API] Found " . count($participants) . " participants for award: {$awardId}");

            return $this->respondSuccess($participants);

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error getting certificate participants: " . $e->getMessage());
            return $this->fail('Failed to retrieve certificate participants', 500);
        }
    }

    /**
     * 🎓 Get program certificates (awards available for certificates in a program)
     * GET /api/certificates/program/{programId}
     */
    public function getProgramCertificates($programId = null)
    {
        log_message('info', "[Certificate API] Getting certificates for program ID: {$programId}");

        try {
            if ($programId === null) {
                return $this->failValidationErrors('Program ID is required');
            }

            // Get program awards that have certificate templates
            $certificates = $this->programAwardModel->select('
                    program_awards.*,
                    program_certificates.id as template_id,
                    program_certificates.template_url,
                    program_certificates.template_type,
                    program_certificates.published_at,
                    COUNT(participant_awards.id) as recipient_count
                ')
                ->join('program_certificates', 'program_certificates.award_id = program_awards.id', 'left')
                ->join('participant_awards', 'participant_awards.award_id = program_awards.id AND participant_awards.is_active = 1', 'left')
                ->where('program_awards.program_id', $programId)
                ->where('program_awards.is_active', 1)
                ->where('program_awards.is_deleted', 0)
                ->groupBy('program_awards.id')
                ->findAll();

            log_message('info', "[Certificate API] Found " . count($certificates) . " certificate types for program: {$programId}");

            return $this->respondSuccess($certificates);

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error getting program certificates: " . $e->getMessage());
            return $this->fail('Failed to retrieve program certificates', 500);
        }
    }

    /**
     * 📋 Get single certificate details
     * GET /api/certificates/{certificateId}
     */
    public function getCertificateDetails($certificateId = null)
    {
        log_message('info', "[Certificate API] Getting certificate details for ID: {$certificateId}");

        try {
            if ($certificateId === null) {
                return $this->failValidationErrors('Certificate ID is required');
            }

            $certificate = $this->participantAwardModel->select('
                    participant_awards.*,
                    participants.full_name as participant_name,
                    participants.account_id,
                    participants.nationality,
                    program_awards.title as award_title,
                    program_awards.description as award_description,
                    program_awards.award_type,
                    programs.name as program_name
                ')
                ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                ->join('program_awards', 'program_awards.id = participant_awards.award_id', 'left')
                ->join('programs', 'programs.id = participants.program_id', 'left')
                ->where('participant_awards.id', $certificateId)
                ->where('participant_awards.is_active', 1)
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
     * ❌ Revoke certificate (soft delete)
     * DELETE /api/certificates/{certificateId}
     */
    public function revokeCertificate($certificateId = null)
    {
        log_message('info', "[Certificate API] Revoking certificate ID: {$certificateId}");

        try {
            if ($certificateId === null) {
                return $this->failValidationErrors('Certificate ID is required');
            }

            // Soft delete the participant award (certificate assignment)
            $updateData = [
                'is_active' => 0,
                'is_deleted' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->participantAwardModel->update($certificateId, $updateData)) {
                log_message('info', "[Certificate API] Certificate revoked successfully: {$certificateId}");
                
                return $this->respondSuccess([
                    'message' => 'Certificate revoked successfully',
                    'certificate_id' => $certificateId
                ]);
            } else {
                return $this->fail('Failed to revoke certificate', 500);
            }

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error revoking certificate: " . $e->getMessage());
            return $this->fail('Failed to revoke certificate', 500);
        }
    }

    /**
     * 📊 Get certificate statistics for participant
     * GET /api/certificates/stats/{participantId}
     */
    public function getCertificateStats($participantId = null)
    {
        log_message('info', "[Certificate API] Getting certificate stats for participant ID: {$participantId}");

        try {
            if ($participantId === null) {
                return $this->failValidationErrors('Participant ID is required');
            }

            $stats = [
                'total_certificates' => $this->participantAwardModel
                    ->where('participant_id', $participantId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->countAllResults(),
                
                'certificates_by_type' => $this->participantAwardModel->select('
                        program_awards.award_type,
                        COUNT(*) as count
                    ')
                    ->join('program_awards', 'program_awards.id = participant_awards.award_id', 'left')
                    ->where('participant_awards.participant_id', $participantId)
                    ->where('participant_awards.is_active', 1)
                    ->where('participant_awards.is_deleted', 0)
                    ->groupBy('program_awards.award_type')
                    ->findAll()
            ];

            log_message('info', "[Certificate API] Certificate stats retrieved for participant ID: {$participantId}");

            return $this->respondSuccess($stats);

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error getting certificate stats: " . $e->getMessage());
            return $this->fail('Failed to retrieve certificate stats', 500);
        }
    }

    /**
     * 🔄 Regenerate Certificate using Python Service
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

            // Build certificate data
            $certificateData = $this->buildCertificateData(
                $existingCertificate->participant_id, 
                $existingCertificate->award_id
            );

            if (!$certificateData['success']) {
                return $this->response->setJSON($certificateData)->setStatusCode(404);
            }

            // Generate new certificate using Python service
            $result = $this->certificateService->generateCertificate($certificateData['data']);

            if ($result['success']) {
                log_message('info', "[Certificate API] Certificate regenerated successfully: {$certificateId}");

                return $this->response->setJSON([
                    'success' => true,
                    'data' => [
                        'certificate_id' => $certificateId,
                        'file_name' => $result['data']['file_name'],
                        'file_size' => $result['data']['file_size'],
                        'file_data' => $result['data']['file_data'], // Base64 encoded PDF
                        'generated_at' => $result['data']['generated_at'],
                        'participant_name' => $certificateData['data']['participant']['full_name'],
                        'award_title' => $certificateData['data']['award']['title'],
                        'program_name' => $certificateData['data']['program']['name']
                    ]
                ]);
            } else {
                return $this->response->setJSON($result)->setStatusCode(400);
            }

        } catch (\Exception $e) {
            log_message('error', "[Certificate API] Error regenerating certificate: " . $e->getMessage());
            return $this->fail('Failed to regenerate certificate', 500);
        }
    }

    /**
     * Build certificate data from database for Python service
     */
    protected function buildCertificateData(int $participantId, int $awardId): array
    {
        try {
            // Get participant data
            $participant = $this->participantModel->find($participantId);
            log_message('debug', 'Participant type: ' . gettype($participant) . ', value: ' . print_r($participant, true));
            if (!$participant) {
                return ['success' => false, 'message' => 'Participant not found'];
            }

            // Get program data
            $program = $this->programModel->find($participant->program_id);
            log_message('debug', 'Program type: ' . gettype($program) . ', value: ' . print_r($program, true));
            if (!$program) {
                return ['success' => false, 'message' => 'Program not found'];
            }

            // Get award data
            $award = $this->programAwardModel->find($awardId);
            log_message('debug', 'Award type: ' . gettype($award) . ', value: ' . print_r($award, true));
            if (!$award) {
                return ['success' => false, 'message' => 'Award not found'];
            }

            // Get certificate template
            log_message('debug', 'About to query certificate template with program->id: ' . $program->id . ', awardId: ' . $awardId);
            $certificateTemplate = $this->programCertificateModel
                ->where('program_id', $program->id)
                ->where('award_id', $awardId)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->first();
            log_message('debug', 'Certificate template result type: ' . gettype($certificateTemplate));
                
            if (!$certificateTemplate) {
                return ['success' => false, 'message' => 'Certificate template not found for this award'];
            }

            // Get content blocks
            log_message('debug', 'About to query content blocks with certificateTemplate->id: ' . $certificateTemplate->id);
            $contentBlocks = $this->contentBlockModel
                ->where('certificate_id', $certificateTemplate->id)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->orderBy('id', 'ASC')
                ->findAll();
            log_message('debug', 'Content blocks result type: ' . gettype($contentBlocks) . ', count: ' . count($contentBlocks));

            // Build certificate data structure for Python service
            $certificateData = [
                'participant' => [
                    'id' => (int)$participant->id,
                    'account_id' => $participant->account_id,
                    'full_name' => $participant->full_name,
                    'birthdate' => $participant->birthdate,
                    'gender' => $participant->gender,
                    'nationality' => $participant->nationality,
                    'nationality_code' => $participant->nationality_code,
                    'education_level' => $participant->education_level,
                    'major' => $participant->major,
                    'institution' => $participant->institution,
                    'occupation' => $participant->occupation,
                    'category' => $participant->category,
                    'picture_url' => $participant->picture_url,
                    'instagram_account' => $participant->instagram_account,
                    'experiences' => $participant->experiences,
                    'achievements' => $participant->achievements,
                    'tshirt_size' => $participant->tshirt_size,
                    'registration_date' => $participant->created_at
                ],
                'program' => [
                    'id' => (int)$program->id,
                    'name' => $program->name,
                    'theme' => $program->theme,
                    'start_date' => $program->start_date,
                    'end_date' => $program->end_date
                ],
                'award' => [
                    'id' => (int)$award->id,
                    'title' => $award->title,
                    'description' => $award->description,
                    'award_type' => $award->award_type,
                    'order_number' => (int)$award->order_number
                ],
                'certificate_template' => [
                    'id' => (int)$certificateTemplate->id,
                    'template_url' => $certificateTemplate->template_url,
                    'template_type' => $certificateTemplate->template_type,
                    'issue_date' => $certificateTemplate->issue_date,
                    'published_at' => $certificateTemplate->published_at
                ],
                'content_blocks' => array_map(function($block) {
                    return [
                        'id' => (int)$block->id,
                        'type' => $block->type,
                        'value' => $block->value,
                        'x' => (int)$block->x,
                        'y' => (int)$block->y,
                        'font_size' => (int)$block->font_size,
                        'font_family' => $block->font_family,
                        'font_weight' => $block->font_weight,
                        'text_align' => $block->text_align,
                        'color' => $block->color
                    ];
                }, $contentBlocks),
                'assignment_info' => [
                    'assigned_by' => session('user_id'),
                    'assigned_at' => date('c'),
                    'notes' => 'Generated via admin panel'
                ]
            ];

            return ['success' => true, 'data' => $certificateData];

        } catch (\Exception $e) {
            log_message('error', 'Build certificate data error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to build certificate data'];
        }
    }

    /**
     * Ensure participant_awards record exists
     */
    protected function ensureParticipantAward(int $participantId, int $awardId): void
    {
        try {
            // Check if participant_awards record exists
            $existingRecord = $this->participantAwardModel
                ->where('participant_id', $participantId)
                ->where('award_id', $awardId)
                ->first();

            if (!$existingRecord) {
                // Create new participant_awards record
                $data = [
                    'participant_id' => $participantId,
                    'award_id' => $awardId,
                    'assigned_by' => session('user_id'),
                    'assigned_at' => date('Y-m-d H:i:s'),
                    'notes' => 'Created automatically during certificate generation',
                    'is_active' => 1,
                    'is_deleted' => 0
                ];

                $this->participantAwardModel->insert($data);
                log_message('info', "[Certificate API] Created participant_awards record for participant {$participantId}, award {$awardId}");
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to create participant_awards record: ' . $e->getMessage());
        }
    }


    /**
     * participant - GET {{api_url}}/certificates/participant/{{participant_id}}
     * Auto-generated method
     */
    public function participant($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement participant logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'participant executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute participant',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * participants - GET {{api_url}}/certificates/award/1/participants
     * Auto-generated method
     */
    public function participants($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement participants logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'participants executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute participants',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * program - GET {{api_url}}/certificates/program/{{program_id}}
     * Auto-generated method
     */
    public function program($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement program logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'program executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute program',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * show - GET {{api_url}}/certificates/1
     * Auto-generated method
     */
    public function show($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement show logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'show executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute show',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * generate - POST {{api_url}}/certificates/generate
     * Auto-generated method
     */
    public function generate($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement generate logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'generate executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute generate',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * regenerate - POST {{api_url}}/certificates/1/regenerate
     * Auto-generated method
     */
    public function regenerate($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement regenerate logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'regenerate executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute regenerate',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * delete - DELETE {{api_url}}/certificates/1
     * Auto-generated method
     */
    public function delete($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement delete logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'delete executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute delete',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * stats - GET {{api_url}}/certificates/stats/{{participant_id}}
     * Auto-generated method
     */
    public function stats($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement stats logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'stats executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute stats',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * placeholders - GET {{api_url}}/certificates/placeholders
     * Auto-generated method
     */
    public function placeholders($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement placeholders logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'placeholders executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute placeholders',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
