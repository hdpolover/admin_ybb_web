<?php

namespace App\Controllers;

use App\Models\ParticipantCertificateModel;

class ParticipantCertificates extends BaseController
{
    protected $participantCertificateModel;

    public function __construct()
    {
        $this->participantCertificateModel = new ParticipantCertificateModel();
    }

    /**
     * Get all participant certificates
     */
    public function index()
    {
        try {
            $participantId = $this->request->getGet('participant_id');
            $certificateId = $this->request->getGet('certificate_id');
            $awardId = $this->request->getGet('award_id');
            
            if ($participantId) {
                $certificates = $this->participantCertificateModel->getParticipantCertificates($participantId);
            } elseif ($certificateId) {
                $certificates = $this->participantCertificateModel->getCertificateParticipants($certificateId);
            } elseif ($awardId) {
                $certificates = $this->participantCertificateModel->getCertificatesByAward($awardId);
            } else {
                $certificates = $this->participantCertificateModel->select('participant_certificates.*, program_awards.title as award_title, participants.full_name, participants.account_id')
                                                                  ->join('program_awards', 'program_awards.id = participant_certificates.award_id', 'left')
                                                                  ->join('participants', 'participants.id = participant_certificates.participant_id', 'left')
                                                                  ->where('participant_certificates.is_active', 1)
                                                                  ->where('participant_certificates.is_deleted', 0)
                                                                  ->orderBy('participant_certificates.generated_at', 'DESC')
                                                                  ->findAll();
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $certificates,
                'message' => 'Participant certificates retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch participant certificates: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve participant certificates: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Get a specific participant certificate
     */
    public function show($id)
    {
        try {
            $certificate = $this->participantCertificateModel->getCertificateWithDetails($id);

            if (!$certificate) {
                return $this->response->setStatusCode(404)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Participant certificate not found'
                                     ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $certificate,
                'message' => 'Participant certificate retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch participant certificate: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve participant certificate: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Create a new participant certificate
     */
    public function create()
    {
        try {
            $data = $this->request->getJSON(true) ?: $this->request->getPost();

            // Set default values
            $data['is_active'] = $data['is_active'] ?? 1;
            $data['is_deleted'] = 0;
            $data['generated_at'] = $data['generated_at'] ?? date('Y-m-d H:i:s');

            // Check if participant already has this certificate
            if ($this->participantCertificateModel->hasParticipantCertificate($data['participant_id'], $data['certificate_id'])) {
                return $this->response->setStatusCode(409)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Participant already has this certificate'
                                     ]);
            }

            if ($this->participantCertificateModel->save($data)) {
                $insertId = $this->participantCertificateModel->getInsertID();
                $certificate = $this->participantCertificateModel->getCertificateWithDetails($insertId);

                return $this->response->setStatusCode(201)
                                     ->setJSON([
                                         'success' => true,
                                         'data' => $certificate,
                                         'message' => 'Participant certificate created successfully'
                                     ]);
            } else {
                $errors = $this->participantCertificateModel->errors();
                return $this->response->setStatusCode(400)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Validation failed',
                                         'errors' => $errors
                                     ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to create participant certificate: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to create participant certificate: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Update a participant certificate
     */
    public function update($id)
    {
        try {
            $certificate = $this->participantCertificateModel->where('is_deleted', 0)->find($id);

            if (!$certificate) {
                return $this->response->setStatusCode(404)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Participant certificate not found'
                                     ]);
            }

            $data = $this->request->getJSON(true) ?: $this->request->getPost();

            if ($this->participantCertificateModel->update($id, $data)) {
                $updatedCertificate = $this->participantCertificateModel->getCertificateWithDetails($id);

                return $this->response->setJSON([
                    'success' => true,
                    'data' => $updatedCertificate,
                    'message' => 'Participant certificate updated successfully'
                ]);
            } else {
                $errors = $this->participantCertificateModel->errors();
                return $this->response->setStatusCode(400)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Validation failed',
                                         'errors' => $errors
                                     ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to update participant certificate: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to update participant certificate: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Delete a participant certificate (soft delete)
     */
    public function delete($id)
    {
        try {
            $certificate = $this->participantCertificateModel->where('is_deleted', 0)->find($id);

            if (!$certificate) {
                return $this->response->setStatusCode(404)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Participant certificate not found'
                                     ]);
            }

            if ($this->participantCertificateModel->softDelete($id)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Participant certificate deleted successfully'
                ]);
            } else {
                return $this->response->setStatusCode(500)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Failed to delete participant certificate'
                                     ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to delete participant certificate: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to delete participant certificate: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Get certificates for a specific participant
     */
    public function byParticipant($participantId)
    {
        try {
            $certificates = $this->participantCertificateModel->getParticipantCertificates($participantId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $certificates,
                'message' => 'Participant certificates retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch participant certificates: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve participant certificates: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Get participants for a specific certificate
     */
    public function byCertificate($certificateId)
    {
        try {
            $participants = $this->participantCertificateModel->getCertificateParticipants($certificateId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $participants,
                'message' => 'Certificate participants retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch certificate participants: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve certificate participants: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Get certificates by award
     */
    public function byAward($awardId)
    {
        try {
            $certificates = $this->participantCertificateModel->getCertificatesByAward($awardId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $certificates,
                'message' => 'Award certificates retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch award certificates: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve award certificates: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Bulk generate certificates for participants
     */
    public function bulkGenerate()
    {
        try {
            $data = $this->request->getJSON(true) ?: $this->request->getPost();
            
            if (!isset($data['participant_ids']) || !isset($data['certificate_id']) || !isset($data['award_id'])) {
                return $this->response->setStatusCode(400)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'participant_ids, certificate_id, and award_id are required'
                                     ]);
            }

            $participantIds = $data['participant_ids'];
            $certificateId = $data['certificate_id'];
            $awardId = $data['award_id'];
            $generatedAt = $data['generated_at'] ?? date('Y-m-d H:i:s');

            $created = [];
            $skipped = [];
            $errors = [];

            foreach ($participantIds as $participantId) {
                // Check if participant already has this certificate
                if ($this->participantCertificateModel->hasParticipantCertificate($participantId, $certificateId)) {
                    $skipped[] = $participantId;
                    continue;
                }

                $certificateData = [
                    'participant_id' => $participantId,
                    'award_id' => $awardId,
                    'certificate_id' => $certificateId,
                    'generated_at' => $generatedAt,
                    'is_active' => 1,
                    'is_deleted' => 0
                ];

                if ($this->participantCertificateModel->save($certificateData)) {
                    $created[] = $participantId;
                } else {
                    $errors[$participantId] = $this->participantCertificateModel->errors();
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'created' => $created,
                    'skipped' => $skipped,
                    'errors' => $errors
                ],
                'message' => sprintf('Bulk generation completed. Created: %d, Skipped: %d, Errors: %d', 
                    count($created), count($skipped), count($errors))
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to bulk generate certificates: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to bulk generate certificates: ' . $e->getMessage()
                                 ]);
        }
    }
}
