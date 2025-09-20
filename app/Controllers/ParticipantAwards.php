<?php

namespace App\Controllers;

use App\Models\ParticipantAwardModel;

class ParticipantAwards extends AdminBaseController
{
    protected $participantAwardModel;

    public function __construct()
    {
        $this->participantAwardModel = new ParticipantAwardModel();
    }

    /**
     * Get all participant awards
     */
    public function index()
    {
        try {
            $participantId = $this->request->getGet('participant_id');
            $awardId = $this->request->getGet('award_id');
            
            if ($participantId) {
                $awards = $this->participantAwardModel->getParticipantAwards($participantId);
            } elseif ($awardId) {
                $awards = $this->participantAwardModel->getAwardParticipants($awardId);
            } else {
                $awards = $this->participantAwardModel->select('participant_awards.*, program_awards.title as award_title, participants.full_name, users.full_name as assigned_by_name')
                                                      ->join('program_awards', 'program_awards.id = participant_awards.award_id', 'left')
                                                      ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                                                      ->join('users', 'users.id = participant_awards.assigned_by', 'left')
                                                      ->where('participant_awards.is_active', 1)
                                                      ->where('participant_awards.is_deleted', 0)
                                                      ->orderBy('participant_awards.assigned_at', 'DESC')
                                                      ->findAll();
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $awards,
                'message' => 'Participant awards retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch participant awards: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve participant awards: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Get a specific participant award
     */
    public function show($id)
    {
        try {
            $award = $this->participantAwardModel->getParticipantAwardWithDetails($id);

            if (!$award) {
                return $this->response->setStatusCode(404)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Participant award not found'
                                     ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $award,
                'message' => 'Participant award retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch participant award: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve participant award: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Create a new participant award
     */
    public function create()
    {
        try {
            $data = $this->request->getJSON(true) ?: $this->request->getPost();

            // Set default values
            $data['is_active'] = $data['is_active'] ?? 1;
            $data['is_deleted'] = 0;
            $data['assigned_at'] = $data['assigned_at'] ?? date('Y-m-d H:i:s');

            // Check if participant already has this award
            if ($this->participantAwardModel->hasParticipantAward($data['participant_id'], $data['award_id'])) {
                return $this->response->setStatusCode(409)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Participant already has this award'
                                     ]);
            }

            if ($this->participantAwardModel->save($data)) {
                $insertId = $this->participantAwardModel->getInsertID();
                $award = $this->participantAwardModel->getParticipantAwardWithDetails($insertId);

                return $this->response->setStatusCode(201)
                                     ->setJSON([
                                         'success' => true,
                                         'data' => $award,
                                         'message' => 'Participant award created successfully'
                                     ]);
            } else {
                $errors = $this->participantAwardModel->errors();
                return $this->response->setStatusCode(400)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Validation failed',
                                         'errors' => $errors
                                     ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to create participant award: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to create participant award: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Update a participant award
     */
    public function update($id)
    {
        try {
            $award = $this->participantAwardModel->where('is_deleted', 0)->find($id);

            if (!$award) {
                return $this->response->setStatusCode(404)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Participant award not found'
                                     ]);
            }

            $data = $this->request->getJSON(true) ?: $this->request->getPost();

            if ($this->participantAwardModel->update($id, $data)) {
                $updatedAward = $this->participantAwardModel->getParticipantAwardWithDetails($id);

                return $this->response->setJSON([
                    'success' => true,
                    'data' => $updatedAward,
                    'message' => 'Participant award updated successfully'
                ]);
            } else {
                $errors = $this->participantAwardModel->errors();
                return $this->response->setStatusCode(400)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Validation failed',
                                         'errors' => $errors
                                     ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to update participant award: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to update participant award: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Delete a participant award (soft delete)
     */
    public function delete($id)
    {
        try {
            $award = $this->participantAwardModel->where('is_deleted', 0)->find($id);

            if (!$award) {
                return $this->response->setStatusCode(404)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Participant award not found'
                                     ]);
            }

            if ($this->participantAwardModel->softDelete($id)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Participant award deleted successfully'
                ]);
            } else {
                return $this->response->setStatusCode(500)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Failed to delete participant award'
                                     ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to delete participant award: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to delete participant award: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Get awards for a specific participant
     */
    public function byParticipant($participantId)
    {
        try {
            $awards = $this->participantAwardModel->getParticipantAwards($participantId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $awards,
                'message' => 'Participant awards retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch participant awards: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve participant awards: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Get participants for a specific award
     */
    public function byAward($awardId)
    {
        try {
            $participants = $this->participantAwardModel->getAwardParticipants($awardId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $participants,
                'message' => 'Award participants retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch award participants: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve award participants: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Bulk assign awards to participants
     */
    public function bulkAssign()
    {
        try {
            $data = $this->request->getJSON(true) ?: $this->request->getPost();
            
            if (!isset($data['participant_ids']) || !isset($data['award_id']) || !isset($data['assigned_by'])) {
                return $this->response->setStatusCode(400)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'participant_ids, award_id, and assigned_by are required'
                                     ]);
            }

            $participantIds = $data['participant_ids'];
            $awardId = $data['award_id'];
            $assignedBy = $data['assigned_by'];
            $notes = $data['notes'] ?? '';
            $assignedAt = $data['assigned_at'] ?? date('Y-m-d H:i:s');

            $created = [];
            $skipped = [];
            $errors = [];

            foreach ($participantIds as $participantId) {
                // Check if participant already has this award
                if ($this->participantAwardModel->hasParticipantAward($participantId, $awardId)) {
                    $skipped[] = $participantId;
                    continue;
                }

                $awardData = [
                    'participant_id' => $participantId,
                    'award_id' => $awardId,
                    'assigned_by' => $assignedBy,
                    'assigned_at' => $assignedAt,
                    'notes' => $notes,
                    'is_active' => 1,
                    'is_deleted' => 0
                ];

                if ($this->participantAwardModel->save($awardData)) {
                    $created[] = $participantId;
                } else {
                    $errors[$participantId] = $this->participantAwardModel->errors();
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'created' => $created,
                    'skipped' => $skipped,
                    'errors' => $errors
                ],
                'message' => sprintf('Bulk assignment completed. Created: %d, Skipped: %d, Errors: %d', 
                    count($created), count($skipped), count($errors))
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to bulk assign awards: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to bulk assign awards: ' . $e->getMessage()
                                 ]);
        }
    }
}
