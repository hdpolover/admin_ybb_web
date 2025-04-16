<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramCategoryModel;
use App\Models\ProgramModel;
use App\Models\ProgramTestimonyModel;
// faq model
use App\Models\FaqModel;
use App\Models\AnnouncementModel;
// photo model
use App\Models\ProgramPhotoModel;
use App\Models\ParticipantModel;

class LandingApiController extends ApiBaseController
{
    protected $programCategoryModel;
    protected $programModel;
    protected $testimonyModel;
    protected $faqModel;
    protected $announcementModel;
    protected $photoModel;
    protected $participantModel;
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
        $this->programCategoryModel = new ProgramCategoryModel();
        $this->programModel = new ProgramModel();
        $this->testimonyModel = new ProgramTestimonyModel();
        $this->faqModel = new FaqModel();
        $this->announcementModel = new AnnouncementModel();
        $this->photoModel = new ProgramPhotoModel();
        $this->participantModel = new ParticipantModel();
    }

    /**
     * Get homepage data
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function home()
    {
        try {
            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $webUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // Get all programs for this category
            $allPrograms = $this->programModel->getAllPrograms($category->id);

            // Get active testimonies for this category
            $testimonies = $this->testimonyModel->getActiveTestimonies($category->id);

            // Get active photos for this category
            $photos = $this->photoModel->getActivePhotos($category->id);

            // If photos are empty, get photos from other programs
            if (empty($photos)) {
                $photos = $this->photoModel->getAllPhotos();
            }

            // Compile home data
            $data = [
                'category' => $category,
                'programs' => $allPrograms,
                'testimonies' => $testimonies,
                'photos' => $photos,
            ];

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get all programs for a category
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function programs()
    {
        try {
            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $webUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // Get all programs for this category
            $programs = $this->programModel->getActivePrograms($category->id);

            // get other programs
            $otherPrograms = $this->programModel->getOtherPrograms($category->id);

            return $this->respondSuccess([
                'category' => $category,
                'programs' => $programs,
                'otherPrograms' => $otherPrograms
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get partners and sponsors
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function partnersSponsors()
    {
        try {
            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $webUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // Get partners and sponsors for this category
            // Note: You may need to create a PartnersModel to implement this functionality
            $partners = []; // Replace with actual data retrieval

            return $this->respondSuccess([
                'category' => $category,
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get insights data
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function insights()
    {
        try {
            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $webUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // get latest program for this category
            $activeProgram = $this->programModel->getActivePrograms($category->id);

            if (!$activeProgram) {
                return $this->respondNotFound('No active program found for this category');
            }

            // Get insights for the active program
            $totalParticipants = $this->participantModel->getTotalParticipants($activeProgram->id);
            $totalCountries = $this->participantModel->getTotalCountries($activeProgram->id);
            $countriesData = $this->participantModel->getCountriesData($activeProgram->id);

            $activeProgramInsights = [
                'program' => $activeProgram,
                'total_registered_participants' =>$totalParticipants,
                'total_countries' => $totalCountries,
                'countries_data' => $countriesData,
            ];


            // Get insights for this category
            // Note: You may need to create an InsightsModel to implement this functionality
            $insightsData = [
                'activeProgramInsights' => $activeProgramInsights,
            ];

            return $this->respondSuccess([
                'category' => $category,
                'insightsData' => $insightsData,
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get help and news page data
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function helpAndNews()
    {
        try {
            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $webUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // get programs
            $programs = $this->programModel->getActivePrograms($category->id);

            if (!$programs) {
                return $this->respondNotFound('No active program found for this category');
            }

            // get latest program for this category from $programs based on end date
            $latestProgram = null;

            // Check if programs is an array or object and process accordingly
            if (is_object($programs) && !is_array($programs)) {
                // Handle single object case
                $latestProgram = $programs;
            } else if (is_array($programs)) {
                // Handle array case
                if (count($programs) == 1) {
                    $latestProgram = $programs[0];
                } else {
                    // find the latest program based on end date
                    foreach ($programs as $program) {
                        if ($latestProgram === null || strtotime($program->end_date) > strtotime($latestProgram->end_date)) {
                            $latestProgram = $program;
                        }
                    }
                }
            }



            // get news for this category
            $news = $this->announcementModel->getByProgramId($latestProgram->id);

            // get faqs for this category
            $faqs = $this->faqModel->getActiveFaqsByProgramId($latestProgram->id);

            $data = [
                'category' => $category,
                'programs' => $programs,
                'latestProgram' => $latestProgram,
                'news' => $news,
                'faqs' => $faqs,
            ];

            // var_dump($data); // Debugging line to check the data structure
             return $this->respondSuccess($data, self::HTTP_OK, 'Help and news data retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }



    /**
     * Get program details
     * 
     * @param int $id Program ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function programDetail($id = null)
    {
        try {
            if (empty($id)) {
                return $this->respondValidationErrors('Program ID is required');
            }

            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $webUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // Get program details
            $program = $this->programModel->getProgramByIdAndCategory($id, $category->id);

            if (!$program) {
                return $this->respondNotFound('Program not found');
            }

            return $this->respondSuccess([
                'category' => $category,
                'program' => $program,
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get program details by slug
     * 
     * @param string $slug Program slug
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function programBySlug($slug = null)
    {
        try {
            if (empty($slug)) {
                return $this->respondValidationErrors('Program slug is required');
            }

            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $webUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // Get program details by slug
            $program = $this->programModel->getProgramBySlugAndCategory($slug, $category->id);

            if (!$program) {
                return $this->respondNotFound('Program not found');
            }

            // get participant photos by program id
            $photos = $this->participantModel->getParticipantPhotosByProgramId($program->id);

            return $this->respondSuccess([
                'category' => $category,
                'program' => $program
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get a single announcement
     * 
     * @param int $id Announcement ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function announcementDetail($id = null)
    {
        try {
            if (empty($id)) {
                return $this->respondValidationErrors('Announcement ID is required');
            }

            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $webUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // Get announcement details
            $announcement = $this->announcementModel->getAnnouncementByIdAndProgramCategory($id, $category->id);

            if (!$announcement) {
                return $this->respondNotFound('Announcement not found');
            }

            return $this->respondSuccess([
                'category' => $category,
                'announcement' => $announcement
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get announcement by slug
     * 
     * @param string $slug Announcement slug
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function announcementBySlug($slug = null)
    {
        try {
            if (empty($slug)) {
                return $this->respondValidationErrors('Announcement slug is required');
            }

            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $webUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // Get announcement details by slug
            $announcement = $this->announcementModel->getAnnouncementBySlugAndProgramCategory($slug, $category->id);

            if (!$announcement) {
                return $this->respondNotFound('Announcement not found');
            }

            return $this->respondSuccess([
                'category' => $category,
                'announcement' => $announcement
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
    /**
     * Get detailed help and news data
     * 
     * @param int $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function helpAndNewsDetail($id)
    {
        try {
            if (empty($id)) {
                return $this->respondValidationErrors('ID is required');
            }

            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $webUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // Get help and news details
            $helpNews = $this->faqModel->getFAQByIdAndProgramCategory($id, $category->id);

            if (!$helpNews) {
                return $this->respondNotFound('Help and news not found');
            }

            return $this->respondSuccess([
                'category' => $category,
                'help_news' => $helpNews
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
