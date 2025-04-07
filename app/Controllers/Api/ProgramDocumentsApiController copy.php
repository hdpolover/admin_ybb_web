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
}
