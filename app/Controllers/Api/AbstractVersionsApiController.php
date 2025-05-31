<?php

namespace App\Controllers\Api;

use App\Models\AbstractModel;
use App\Models\AbstractVersionModel;
use App\Models\AbstractAuthorModel;
use App\Models\ProgramModel;
use App\Models\ParticipantModel;
use App\Models\AbstractTopicModel;


use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;

class AbstractVersionsApiController extends ApiBaseController
{
    protected $abstractModel;
    protected $abstractVersionModel;
    protected $abstractAuthorModel;
    protected $programModel;
    protected $participantModel;
    protected $abstractTopicModel;

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
        $this->abstractModel = new AbstractModel();
        $this->abstractVersionModel = new AbstractVersionModel();
        $this->abstractAuthorModel = new AbstractAuthorModel();
        $this->programModel = new ProgramModel();
        $this->participantModel = new ParticipantModel();
        $this->abstractTopicModel = new AbstractTopicModel();
    }

    /**
     * Get topics by program ID
     *
     * @param int $programId The program ID
     * @return ResponseInterface
     */
    public function getAbstractTopicsByProgramId($programId = null)
    {
        // Validate program ID
        if (empty($programId) || !is_numeric($programId)) {
            return $this->respondValidationErrors('Invalid program ID');
        }

        // Check if program exists
        if (!$this->programModel->find($programId)) {
            return $this->respondNotFound('Program not found');
        }

        try {
            // Get topics for the program
            $topics = $this->abstractTopicModel->where('program_id', $programId)
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll();

            if (empty($topics)) {
                return $this->respondNotFound('No topics found');
            }

            return $this->respondSuccess(
                $topics,
                SELF::HTTP_OK,
                "Topics retrieved successfully",
            );
        } catch (\Exception $e) {
            log_message('error', 'Error getting topics: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve topics');
        }
    }
    
    /**
     * Get abstract version by ID
     *
     * @param int $id The abstract version ID
     * @return ResponseInterface
     */
    public function getAbstractVersionById($id = null)
    {
        // Validate ID
        if (empty($id) || !is_numeric($id)) {
            return $this->respondValidationErrors('Invalid abstract version ID');
        }

        // Check if abstract version exists
        $abstractVersion = $this->abstractVersionModel->find($id);
        if (!$abstractVersion) {
            return $this->respondNotFound('Abstract version not found');
        }

        try {
            // Get abstract version details
            $abstractVersionDetails = $this->abstractVersionModel->getAbstractVersionById($id);
            
            // Get abstract details
            $abstract = $this->abstractModel->find($abstractVersionDetails->abstract_id);

             // Get authors if any
            $authors = $this->abstractAuthorModel
                ->where('abstract_id', $abstract->id)
                ->findAll();
                
            // Add authors to response
            $abstract->authors = $authors;
            
            return $this->respondSuccess(
                [
                    'abstract_version' => $abstractVersionDetails,
                    'abstract' => $abstract
                ],
                SELF::HTTP_OK,
                "Abstract version retrieved successfully"
            );
        } catch (\Exception $e) {
            log_message('error', 'Error getting abstract version: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve abstract version');
        }
    }
}
