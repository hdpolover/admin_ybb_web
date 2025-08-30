<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\AnnouncementModel;

class ProgramAnnouncementsApiController extends ApiBaseController
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
        $this->model = new AnnouncementModel();
    }

    /**
     * 🟢 Get All Program announcements (READ)
     * GET /api/program-announcements
     */

    public function index()
    {

        $announcements = $this->model->findAll();
        return $this->respondSuccess($announcements);
    }

    /**
     * 🔍 Get Single Program Announcement (READ)
     * GET /api/program-announcements/{id} or {slug}
     */
    public function show($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors('ID or slug is required');
        }

        // Check if the ID is numeric (for ID lookup)
        if (is_numeric($id)) {
            $announcement = $this->model->find($id);
        } else {
            // If not numeric, treat it as a slug
            $announcement = $this->model->where('slug', $id)->first();
        }

        if (!$announcement) {
            return $this->failNotFound('Announcement not found');
        }

        // get program category data based on announcement program id
        $programModel = new \App\Models\ProgramModel();
        $program = $programModel->find($announcement->program_id);

        if (!$program) {
            return $this->failNotFound('Program not found for this announcement');
        }

        // get program category data based on program id
        $programCategoryModel = new \App\Models\ProgramCategoryModel();
        $category = $programCategoryModel->find($program->program_category_id);
        if (!$category) {
            return $this->failNotFound('Program category not found for this program');
        }

        $data = [
            'category' => $category,
            'announcement' => $announcement,
        ];

        return $this->respondSuccess($data, self::HTTP_OK, 'Announcement retrieved successfully', $data);
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
