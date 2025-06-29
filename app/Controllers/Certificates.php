<?php

namespace App\Controllers;

use App\Models\ProgramAwardModel;
use App\Models\ProgramCertificateModel;
use App\Models\ParticipantAwardModel;
use App\Models\ParticipantCertificateModel;
use App\Models\ParticipantModel;

class Certificates extends BaseController
{
    protected $programAwardModel;
    protected $programCertificateModel;
    protected $participantAwardModel;
    protected $participantCertificateModel;
    protected $participantModel;

    public function __construct()
    {
        $this->programAwardModel = new ProgramAwardModel();
        $this->programCertificateModel = new ProgramCertificateModel();
        $this->participantAwardModel = new ParticipantAwardModel();
        $this->participantCertificateModel = new ParticipantCertificateModel();
        $this->participantModel = new ParticipantModel();
    }

    /**
     * Display the certificate management page
     */
    public function index()
    {
        $programId = session('current_program');
        
        if (!$programId) {
            return redirect()->to('/welcome')->with('error', 'Please select a program first.');
        }

        $data = [
            'title' => 'Certificate Management',
            'pagetitle' => 'Documents',
            'programId' => $programId
        ];

        return view('documents/certificates/index', $data);
    }

    /**
     * Get data for DataTables AJAX request
     */
    public function getData()
    {
        $programId = session('current_program');
        
        if (!$programId) {
            log_message('error', 'Certificates::getData - No program selected in session');
            return $this->response->setJSON(['error' => 'No program selected']);
        }

        log_message('info', 'Certificates::getData - Program ID: ' . $programId);

        try {
            // Get all awards for the current program with participant counts
            $awards = $this->programAwardModel
                ->select('program_awards.*, 
                         COUNT(DISTINCT participant_awards.participant_id) as participants_count,
                         COUNT(DISTINCT participant_certificates.participant_id) as certificates_issued')
                ->join('participant_awards', 'participant_awards.award_id = program_awards.id AND participant_awards.is_active = 1 AND participant_awards.is_deleted = 0', 'left')
                ->join('participant_certificates', 'participant_certificates.award_id = program_awards.id AND participant_certificates.is_active = 1 AND participant_certificates.is_deleted = 0', 'left')
                ->where('program_awards.program_id', $programId)
                ->where('program_awards.is_active', 1)
                ->where('program_awards.is_deleted', 0)
                ->groupBy('program_awards.id')
                ->orderBy('program_awards.order_number', 'ASC')
                ->findAll();

            // Get certificate templates for each award
            foreach ($awards as &$award) {
                // Convert array to object if needed
                if (is_array($award)) {
                    $award = (object) $award;
                }
                
                $certificates = $this->programCertificateModel
                    ->where('award_id', $award->id)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
                
                $award->certificate_templates = $certificates;
                $award->has_certificate_template = count($certificates) > 0;
            }

            // Format data for DataTables
            $data = [];
            foreach ($awards as $award) {
                $certificateStatus = $award->has_certificate_template 
                    ? '<span class="badge bg-success">Available</span>' 
                    : '<span class="badge bg-warning">No Template</span>';

                $progressText = $award->participants_count > 0 
                    ? "{$award->certificates_issued} / {$award->participants_count}" 
                    : "0 / 0";

                $progressPercent = $award->participants_count > 0 
                    ? round(($award->certificates_issued / $award->participants_count) * 100, 1) 
                    : 0;

                $actions = '<div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary btn-sm" onclick="viewAwardDetails(' . $award->id . ')" title="View Details">
                        <i class="ri-eye-line"></i>
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="manageParticipants(' . $award->id . ')" title="Manage Participants">
                        <i class="ri-group-line"></i>
                    </button>';

                if ($award->has_certificate_template) {
                    $actions .= '<button type="button" class="btn btn-info btn-sm" onclick="issueCertificates(' . $award->id . ')" title="Issue Certificates">
                        <i class="ri-award-line"></i>
                    </button>';
                }

                $actions .= '</div>';

                $data[] = [
                    'id' => $award->id,
                    'title' => esc($award->title),
                    'award_type' => ucfirst(str_replace('_', ' ', $award->award_type)),
                    'description' => esc($award->description),
                    'participants_count' => $award->participants_count,
                    'certificates_issued' => $award->certificates_issued,
                    'progress' => '<div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" style="width: ' . $progressPercent . '%;" aria-valuenow="' . $progressPercent . '" aria-valuemin="0" aria-valuemax="100">
                            ' . $progressText . '
                        </div>
                    </div>',
                    'certificate_status' => $certificateStatus,
                    'actions' => $actions
                ];
            }

            log_message('info', 'Certificates::getData - Found ' . count($awards) . ' awards, returning ' . count($data) . ' data rows');

            return $this->response->setJSON(['data' => $data]);

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::getData: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON(['error' => 'Failed to load data: ' . $e->getMessage()]);
        }
    }

    /**
     * Get award details with participants
     */
    public function getAwardDetails($awardId)
    {
        try {
            // Get award details
            $award = $this->programAwardModel->find($awardId);
            if (!$award) {
                return $this->response->setJSON(['error' => 'Award not found']);
            }

            // Get participants assigned to this award
            $participants = $this->participantAwardModel
                ->select('participant_awards.*, participants.full_name, participants.account_id, users.email, 
                         participant_certificates.id as certificate_id, participant_certificates.generated_at')
                ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                ->join('users', 'users.id = participants.user_id', 'left')
                ->join('participant_certificates', 'participant_certificates.participant_id = participant_awards.participant_id AND participant_certificates.award_id = participant_awards.award_id AND participant_certificates.is_active = 1 AND participant_certificates.is_deleted = 0', 'left')
                ->where('participant_awards.award_id', $awardId)
                ->where('participant_awards.is_active', 1)
                ->where('participant_awards.is_deleted', 0)
                ->orderBy('participant_awards.assigned_at', 'DESC')
                ->findAll();

            // Get certificate templates for this award
            $certificates = $this->programCertificateModel
                ->where('award_id', $awardId)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->findAll();

            return $this->response->setJSON([
                'award' => $award,
                'participants' => $participants,
                'certificates' => $certificates
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::getAwardDetails: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load award details']);
        }
    }

    /**
     * Get available participants for assignment
     */
    public function getAvailableParticipants($awardId)
    {
        $programId = session('current_program');
        
        try {
            // Get participants who are not yet assigned to this award
            $participants = $this->participantModel
                ->select('participants.id, participants.full_name, participants.account_id, users.email')
                ->join('users', 'users.id = participants.user_id', 'left')
                ->join('participant_awards', 'participant_awards.participant_id = participants.id AND participant_awards.award_id = ' . $awardId . ' AND participant_awards.is_active = 1 AND participant_awards.is_deleted = 0', 'left')
                ->where('participants.program_id', $programId)
                ->where('participants.is_active', 1)
                ->where('participants.is_deleted', 0)
                ->where('participant_awards.id IS NULL')
                ->orderBy('participants.full_name', 'ASC')
                ->findAll();

            return $this->response->setJSON(['participants' => $participants]);

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::getAvailableParticipants: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load participants']);
        }
    }

    /**
     * Assign participants to an award
     */
    public function assignParticipants()
    {
        try {
            $data = $this->request->getJSON(true);
            $awardId = $data['award_id'] ?? null;
            $participantIds = $data['participant_ids'] ?? [];
            $notes = $data['notes'] ?? '';

            if (!$awardId || empty($participantIds)) {
                return $this->response->setJSON(['error' => 'Award ID and participant IDs are required']);
            }

            $userId = session('user_id');
            $assignedCount = 0;
            $errors = [];

            foreach ($participantIds as $participantId) {
                // Check if already assigned
                if (!$this->participantAwardModel->hasParticipantAward($participantId, $awardId)) {
                    $assignData = [
                        'participant_id' => $participantId,
                        'award_id' => $awardId,
                        'assigned_by' => $userId,
                        'assigned_at' => date('Y-m-d H:i:s'),
                        'notes' => $notes,
                        'is_active' => 1,
                        'is_deleted' => 0
                    ];

                    if ($this->participantAwardModel->insert($assignData)) {
                        $assignedCount++;
                    } else {
                        $errors[] = "Failed to assign participant ID: $participantId";
                    }
                }
            }

            if ($assignedCount > 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "$assignedCount participant(s) assigned successfully",
                    'errors' => $errors
                ]);
            } else {
                return $this->response->setJSON(['error' => 'No participants were assigned']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::assignParticipants: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to assign participants']);
        }
    }

    /**
     * Remove participant from award
     */
    public function removeParticipant()
    {
        try {
            $data = $this->request->getJSON(true);
            $participantAwardId = $data['participant_award_id'] ?? null;

            if (!$participantAwardId) {
                return $this->response->setJSON(['error' => 'Participant award ID is required']);
            }

            if ($this->participantAwardModel->softDelete($participantAwardId)) {
                // Also remove any issued certificates for this participant-award combination
                $participantAward = $this->participantAwardModel->find($participantAwardId);
                if ($participantAward) {
                    $this->participantCertificateModel
                        ->where('participant_id', $participantAward->participant_id)
                        ->where('award_id', $participantAward->award_id)
                        ->set(['is_deleted' => 1, 'is_active' => 0])
                        ->update();
                }

                return $this->response->setJSON(['success' => true, 'message' => 'Participant removed from award']);
            } else {
                return $this->response->setJSON(['error' => 'Failed to remove participant']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::removeParticipant: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to remove participant']);
        }
    }

    /**
     * Issue certificates to participants
     */
    public function issueCertificates()
    {
        try {
            $data = $this->request->getJSON(true);
            $awardId = $data['award_id'] ?? null;
            $participantIds = $data['participant_ids'] ?? [];

            if (!$awardId) {
                return $this->response->setJSON(['error' => 'Award ID is required']);
            }

            // Get certificate template for this award
            $certificate = $this->programCertificateModel
                ->where('award_id', $awardId)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->first();

            if (!$certificate) {
                return $this->response->setJSON(['error' => 'No certificate template found for this award']);
            }

            $issuedCount = 0;
            $errors = [];

            // If no specific participants provided, issue to all assigned participants
            if (empty($participantIds)) {
                $assignedParticipants = $this->participantAwardModel
                    ->select('participant_id')
                    ->where('award_id', $awardId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
                
                $participantIds = array_column($assignedParticipants, 'participant_id');
            }

            foreach ($participantIds as $participantId) {
                // Check if certificate already issued
                if (!$this->participantCertificateModel->hasParticipantCertificate($participantId, $certificate->id)) {
                    $certificateData = [
                        'participant_id' => $participantId,
                        'award_id' => $awardId,
                        'certificate_id' => $certificate->id,
                        'generated_at' => date('Y-m-d H:i:s'),
                        'is_active' => 1,
                        'is_deleted' => 0
                    ];

                    if ($this->participantCertificateModel->insert($certificateData)) {
                        $issuedCount++;
                    } else {
                        $errors[] = "Failed to issue certificate to participant ID: $participantId";
                    }
                }
            }

            if ($issuedCount > 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "$issuedCount certificate(s) issued successfully",
                    'errors' => $errors
                ]);
            } else {
                return $this->response->setJSON(['error' => 'No certificates were issued']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::issueCertificates: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to issue certificates']);
        }
    }

    /**
     * Revoke certificate from participant
     */
    public function revokeCertificate()
    {
        try {
            $data = $this->request->getJSON(true);
            $participantCertificateId = $data['participant_certificate_id'] ?? null;

            if (!$participantCertificateId) {
                return $this->response->setJSON(['error' => 'Participant certificate ID is required']);
            }

            if ($this->participantCertificateModel->softDelete($participantCertificateId)) {
                return $this->response->setJSON(['success' => true, 'message' => 'Certificate revoked successfully']);
            } else {
                return $this->response->setJSON(['error' => 'Failed to revoke certificate']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::revokeCertificate: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to revoke certificate']);
        }
    }

    /**
     * Test method to debug data retrieval without authentication
     */
    public function testGetData()
    {
        // Force set program ID for testing
        $programId = 8; // Middle East Youth Summit 2025
        
        log_message('info', 'Certificates::testGetData - Program ID: ' . $programId);

        try {
            // Get all awards for the current program with participant counts
            $awards = $this->programAwardModel
                ->select('program_awards.*, 
                         COUNT(DISTINCT participant_awards.participant_id) as participants_count,
                         COUNT(DISTINCT participant_certificates.participant_id) as certificates_issued')
                ->join('participant_awards', 'participant_awards.award_id = program_awards.id AND participant_awards.is_active = 1 AND participant_awards.is_deleted = 0', 'left')
                ->join('participant_certificates', 'participant_certificates.award_id = program_awards.id AND participant_certificates.is_active = 1 AND participant_certificates.is_deleted = 0', 'left')
                ->where('program_awards.program_id', $programId)
                ->where('program_awards.is_active', 1)
                ->where('program_awards.is_deleted', 0)
                ->groupBy('program_awards.id')
                ->orderBy('program_awards.order_number', 'ASC')
                ->findAll();

            log_message('info', 'Certificates::testGetData - Found ' . count($awards) . ' awards');

            // Get certificate templates for each award
            foreach ($awards as &$award) {
                // Convert array to object if needed
                if (is_array($award)) {
                    $award = (object) $award;
                }
                
                $certificates = $this->programCertificateModel
                    ->where('award_id', $award->id)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
                
                $award->certificate_templates = $certificates;
                $award->has_certificate_template = count($certificates) > 0;
                
                log_message('info', 'Award: ' . $award->title . ' - Participants: ' . $award->participants_count . ' - Certificates: ' . $award->certificates_issued);
            }

            // Format data for DataTables
            $data = [];
            foreach ($awards as $award) {
                $certificateStatus = $award->has_certificate_template 
                    ? '<span class="badge bg-success">Available</span>' 
                    : '<span class="badge bg-warning">No Template</span>';

                $progressText = $award->participants_count > 0 
                    ? "{$award->certificates_issued} / {$award->participants_count}" 
                    : "0 / 0";

                $progressPercent = $award->participants_count > 0 
                    ? round(($award->certificates_issued / $award->participants_count) * 100, 1) 
                    : 0;

                $actions = '<div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary btn-sm" onclick="viewAwardDetails(' . $award->id . ')" title="View Details">
                        <i class="ri-eye-line"></i>
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="manageParticipants(' . $award->id . ')" title="Manage Participants">
                        <i class="ri-group-line"></i>
                    </button>';

                if ($award->has_certificate_template) {
                    $actions .= '<button type="button" class="btn btn-info btn-sm" onclick="issueCertificates(' . $award->id . ')" title="Issue Certificates">
                        <i class="ri-award-line"></i>
                    </button>';
                }

                $actions .= '</div>';

                $data[] = [
                    'id' => $award->id,
                    'title' => esc($award->title),
                    'award_type' => ucfirst(str_replace('_', ' ', $award->award_type)),
                    'description' => esc($award->description),
                    'participants_count' => $award->participants_count,
                    'certificates_issued' => $award->certificates_issued,
                    'progress' => '<div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" style="width: ' . $progressPercent . '%;" aria-valuenow="' . $progressPercent . '" aria-valuemin="0" aria-valuemax="100">
                            ' . $progressText . '
                        </div>
                    </div>',
                    'certificate_status' => $certificateStatus,
                    'actions' => $actions
                ];
            }

            log_message('info', 'Certificates::testGetData - Returning ' . count($data) . ' data rows');

            // Return both JSON and HTML for debugging
            $response = [
                'success' => true,
                'program_id' => $programId,
                'awards_found' => count($awards),
                'data_rows' => count($data),
                'data' => $data,
                'debug_info' => [
                    'session_program' => session('current_program'),
                    'forced_program' => $programId,
                    'awards_raw' => $awards
                ]
            ];

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::testGetData: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'error' => 'Failed to load data: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Simple test endpoint
     */
    public function simpleTest()
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Certificates controller is working!',
            'timestamp' => date('Y-m-d H:i:s'),
            'session_program' => session('current_program'),
            'session_user' => session('user_id')
        ]);
    }
}
