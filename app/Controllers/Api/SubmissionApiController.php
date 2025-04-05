<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Main Payments API Controller that routes to specialized payment controllers
 * Maintains backward compatibility with the existing API endpoints
 */
class SubmissionApiController extends ApiBaseController
{
    
    protected $participantModel;
    protected $programSubthemeModel;
    protected $participantSubthemeModel;
    protected $participantEssayModel;
    protected $programEssayModel;

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
        $this->participantModel = new \App\Models\ParticipantModel();
        $this->programSubthemeModel = new \App\Models\ProgramSubthemeModel();
        $this->participantSubthemeModel = new \App\Models\ParticipantSubthemeModel();
        $this->participantEssayModel = new \App\Models\ParticipantEssayModel();
        $this->programEssayModel = new \App\Models\ProgramEssayModel();
    }

     /**
     * Get submission of participant data
     * 
     * @param int|null $participantId
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index($participantId = null)
    {
        try {
            if (!$participantId) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            // Get participant data
            $participant = $this->participantModel->find($participantId);

            if (!$participant) {
                return $this->respondError('Participant not found', self::HTTP_NOT_FOUND);
            }

            // get essays by participant id
            $essays = $this->participantEssayModel->getEssaysByParticipantId($participantId);

            // get subthemes by participant id
            $subthemes = $this->participantSubthemeModel->getSubthemesByParticipantId($participantId);

            // get all subthemes by program id
            $programId = $participant->program_id;
            $allSubthemes = $this->programSubthemeModel->getAllSubthemes($programId);

            // get all essays by program id
            $allEssays = $this->programEssayModel->getEssaysByProgramId($programId);

            // Compile home data
            $data = [
                'participant' => $participant,
                'participant_essays' => $essays,
                'participant_subtheme' => $subthemes,
                'program_subthemes' => $allSubthemes,
                'program_essays' => $allEssays
            ];

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get submission of participant data by participant ids
     * 
     * @param array|null $participantIds
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getSubmissionByParticipantId($participantId = null)
    {
        try {
            if (!$participantId) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            // Get participant data
            $participant = $this->participantModel->find($participantId);

            if (!$participant) {
                return $this->respondError('Participant not found', self::HTTP_NOT_FOUND);
            }

            // get essays by participant id
            $essays = $this->participantEssayModel->getParticipantEssaysByParticipantId($participantId);

            // get subthemes by participant id
            $subthemes = $this->participantSubthemeModel->getSubthemesByParticipantId($participantId);

            // Compile home data
            $data = [
                'participant' => $participant,
                'essays' => $essays,
                'subthemes' => $subthemes,
            ];

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get submission form data including program essays and subthemes
     * 
     * @param int|null $programId
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getSubmissionFormData($programId = null)
    {
        try {
            if (!$programId) {
                return $this->respondError('Program ID is required', self::HTTP_BAD_REQUEST);
            }

            // Get program essays
            $essays = $this->programEssayModel->getEssaysByProgramId($programId);
            
            // Get program subthemes
            $subthemes = $this->programSubthemeModel->getSubthemesByProgramId($programId);

            if (empty($essays) && empty($subthemes)) {
                return $this->respondError('No submission form data found for this program', self::HTTP_NOT_FOUND);
            }

            // Compile form data
            $data = [
                'program_id' => $programId,
                'essays' => $essays,
                'subthemes' => $subthemes
            ];

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }
   
}