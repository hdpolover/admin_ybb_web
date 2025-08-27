<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramModel;
use App\Models\ProgramSpeakerModel;
use App\Models\ProgramVideoTestimonyModel;
use App\Models\ProgramSubthemeModel;
use App\Models\ProgramScheduleModel;
use App\Models\ProgramTestimonyModel;
use App\Models\ProgramPhotoModel;
use App\Models\ProgramPaymentModel;
use App\Models\ProgramDocumentModel;
use App\Models\ProgramCategoryModel;
use App\Models\ProgramRundownModel;
use App\Models\FaqModel;
use App\Models\ParticipantModel;

class ProgramsApiController extends ApiBaseController
{
    protected $model;
    protected $speakerModel;
    protected $videoTestimonyModel;
    protected $subthemeModel;
    protected $scheduleModel;
    protected $testimonyModel;
    protected $photoModel;
    protected $paymentModel;
    protected $documentModel;
    protected $categoryModel;
    protected $rundownModel;
    protected $faqModel;
    protected $participantModel;

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
        
        // Initialize models
        $this->model = new ProgramModel();
        $this->speakerModel = new ProgramSpeakerModel();
        $this->videoTestimonyModel = new ProgramVideoTestimonyModel();
        $this->subthemeModel = new ProgramSubthemeModel();
        $this->scheduleModel = new ProgramScheduleModel();
        $this->testimonyModel = new ProgramTestimonyModel();
        $this->photoModel = new ProgramPhotoModel();
        $this->paymentModel = new ProgramPaymentModel();
        $this->documentModel = new ProgramDocumentModel();
        $this->categoryModel = new ProgramCategoryModel();
        $this->rundownModel = new ProgramRundownModel();
        $this->faqModel = new FaqModel();
        $this->participantModel = new ParticipantModel();
    }

    /**
     * Get All Programs
     * GET /api/programs
     * High Priority Cache: 2 hours TTL
     */
    public function index()
    {
        $programs = $this->cacheResponse(function() {
            return $this->model->getPrograms();
        }, [], null, 7200); // 2 hours cache
        
        return $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully');
    }

    /**
     * Get Program by Slug with comprehensive details
     * GET /api/programs/slug/{slug}
     * High Priority Cache: 2 hours TTL
     */
    public function getBySlug($slug = null)
    {
        if ($slug === null) {
            return $this->respondValidationErrors('Slug is required');
        }
        
        // Check if slug contains valid characters
        if (!preg_match('/^[a-zA-Z0-9\- ]+$/', $slug)) {
            return $this->respondValidationErrors('Invalid slug format');
        }
        
        // Convert slug to program name format (replace hyphens with spaces and capitalize words)
        $programName = str_replace('-', ' ', $slug);
        
        $programData = $this->cacheResponse(function() use ($programName) {
            $program = $this->model->getProgramByName($programName);
            
            if (!$program) {
                return null;
            }

            // Get all related data for the program
            $category = $this->categoryModel->find($program->program_category_id);
            $speakers = $this->speakerModel->getByProgramId($program->id, true); // only active speakers
            $videoTestimonies = $this->videoTestimonyModel->getActiveVideoTestimonies($program->id);
            $schedules = $this->scheduleModel->getByProgramId($program->id);
            $photos = $this->photoModel->getActivePhotos($program->program_category_id);
            $participantPhotos = $this->participantModel->getProgramParticipantsPhotos($program->id, 5);
            $faqs = $this->faqModel->getActiveFaqsByProgramId($program->id);
            $rundowns = $this->rundownModel->getActiveRundowns($program->id);

            return [
                'title' => $program->name ?? 'Program Detail',
                'program' => $program,
                'category' => $category ?? [],
                'photos' => $photos,
                'participant_photos' => $participantPhotos,
                'schedules' => $schedules,
                'faqs' => $faqs,
                'rundowns' => $rundowns,
                'video_testimonies' => $videoTestimonies,
                'speakers' => $speakers
            ];
        }, ['slug' => $slug], null, 7200); // 2 hours cache
        
        if (!$programData) {
            return $this->respondNotFound('Program not found');
        }
        
        return $this->respondSuccess($programData, self::HTTP_OK, 'Program details retrieved successfully');
    }

    /**
     * Get Single Program with comprehensive details
     * GET /api/programs/{id}
     * High Priority Cache: 2 hours TTL
     */
    public function show($id = null)
    {
        if ($id === null) {
            return $this->respondValidationErrors('Program ID is required');
        }

        $programData = $this->cacheProgramData(function() use ($id) {
            $program = $this->model->find($id);
            
            if (!$program) {
                return null;
            }

            // Get all related data for the program
            $category = $this->categoryModel->find($program->program_category_id);
            $speakers = $this->speakerModel->getByProgramId($id, true); // only active speakers
            $videoTestimonies = $this->videoTestimonyModel->getActiveVideoTestimonies($id);
            $schedules = $this->scheduleModel->getByProgramId($id);
            $photos = $this->photoModel->getActivePhotos($program->program_category_id);
            $participantPhotos = $this->participantModel->getProgramParticipantsPhotos($id, 5);
            $faqs = $this->faqModel->getActiveFaqsByProgramId($id);
            $rundowns = $this->rundownModel->getActiveRundowns($id);

            return [
                'title' => $program->name ?? 'Program Detail',
                'program' => $program,
                'category' => $category ?? [],
                'photos' => $photos,
                'participant_photos' => $participantPhotos,
                'schedules' => $schedules,
                'faqs' => $faqs,
                'rundowns' => $rundowns,
                'video_testimonies' => $videoTestimonies,
                'speakers' => $speakers
            ];
        }, $id);
        
        if (!$programData) {
            return $this->respondNotFound('Program not found');
        }
        
        return $this->respondSuccess($programData, self::HTTP_OK, 'Program details retrieved successfully');
    }
    
    /**
     * Get programs by category ID
     * GET /api/programs/category/{categoryId}
     * High Priority Cache: 1 hour TTL
     */
    public function getByCategory($categoryId = null)
    {
        if ($categoryId === null) {
            return $this->respondValidationErrors('Category ID is required');
        }
        
        $programs = $this->cacheResponse(function() use ($categoryId) {
            return $this->model->getAllPrograms($categoryId);
        }, ['category_id' => $categoryId], null, 3600); // 1 hour cache

        if (empty($programs)) {
            return $this->respondNotFound('No programs found for this category ID');
        }

        return $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully');
    }

    /**
     * Get programs not in a category
     * GET /api/programs/not-in-category/{categoryId}
     * Medium Priority Cache: 30 minutes TTL
     */
    public function getNotInCategory($categoryId = null)
    {
        if ($categoryId === null) {
            return $this->respondValidationErrors('Category ID is required');
        }
        
        // Validate category ID is numeric
        if (!is_numeric($categoryId)) {
            return $this->respondValidationErrors('Invalid category ID format');
        }
        
        $programs = $this->cacheResponse(function() use ($categoryId) {
            return $this->model->getOtherPrograms($categoryId);
        }, ['exclude_category_id' => $categoryId], null, 1800); // 30 minutes cache
        
        if (empty($programs)) {
            return $this->respondNotFound('No programs found outside this category');
        }
        
        return $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully');
    }
    
    /**
     * Get programs by user ID
     * GET /api/programs/user/{userId}
     * Medium Priority Cache: 15 minutes TTL
     */
    public function getByUser($userId = null)
    {
        if ($userId === null) {
            return $this->respondValidationErrors('User ID is required');
        }
        
        // Validate user ID is numeric
        if (!is_numeric($userId)) {
            return $this->respondValidationErrors('Invalid user ID format');
        }
        
        $programs = $this->cacheUserData(function() use ($userId) {
            return $this->model->getProgramsByUserId($userId);
        }, $userId);
        
        if (empty($programs)) {
            return $this->respondNotFound('No programs found for this user');
        }
        
        return $this->respondSuccess($programs, self::HTTP_OK, 'Programs retrieved successfully');
    }
}