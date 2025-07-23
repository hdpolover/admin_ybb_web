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
use App\Models\ProgramSubthemeModel;

class LandingApiController extends ApiBaseController
{
    protected $programCategoryModel;
    protected $programModel;
    protected $testimonyModel;
    protected $faqModel;
    protected $announcementModel;
    protected $photoModel;
    protected $participantModel;
    protected $programSubthemeModel;
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
        $this->programSubthemeModel = new ProgramSubthemeModel();
    }

    /**
     * Get homepage data
     * High Priority Cache: 30 minutes TTL
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

            $normalizedWebUrl = normalize_web_url($webUrl);

            $data = $this->cacheResponse(function() use ($normalizedWebUrl) {
                // Get program category by web_url
                $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

                if (!$category) {
                    return null;
                }

                // Get all programs for this category
                $allPrograms = $this->programModel->getAllPrograms($category->id);

                // Get active testimonies for this category
                $testimonies = $this->testimonyModel->getActiveTestimonies($category->id);

                // Get active photos for this category
                $photos = $this->photoModel->getActivePhotos($category->id);

                $hasPhotos = !empty($photos);

                // If photos are empty, get photos from other programs
                if (empty($photos)) {
                    $photos = $this->photoModel->getAllPhotos();
                }

                // Group photos by year from year field
                $groupedPhotos = [];

                foreach ($photos as $photo) {
                    $year = $photo->year ?? 'Unknown';

                    if (!isset($groupedPhotos[$year])) {
                        $groupedPhotos[$year] = [];
                    }

                    $groupedPhotos[$year][] = $photo;
                }

                // Sort the years in descending order (newest first)
                krsort($groupedPhotos);

                // Compile home data
                return [
                    'category' => $category,
                    'programs' => $allPrograms,
                    'testimonies' => $testimonies,
                    'hasPhotos' => $hasPhotos,
                    'photos' => $groupedPhotos,
                ];
            }, ['web_url' => $normalizedWebUrl], null, 1800); // 30 minutes cache

            if ($data === null) {
                return $this->respondNotFound('Program category not found');
            }

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get all programs for a category
     * High Priority Cache: 1 hour TTL
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

            $normalizedWebUrl = normalize_web_url($webUrl);

            $data = $this->cacheResponse(function() use ($normalizedWebUrl) {
                // Get program category by web_url
                $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

                if (!$category) {
                    return null;
                }

                // Get all programs for this category
                $programs = $this->programModel->getActivePrograms($category->id);

                // get other programs
                $otherPrograms = $this->programModel->getOtherPrograms($category->id);

                return [
                    'category' => $category,
                    'programs' => $programs,
                    'otherPrograms' => $otherPrograms
                ];
            }, ['web_url' => $normalizedWebUrl], null, 3600); // 1 hour cache

            if ($data === null) {
                return $this->respondNotFound('Program category not found');
            }

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get gallery data
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function gallery()
    {
        try {
            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            $normalizedWebUrl = normalize_web_url($webUrl);

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // Get active photos for this category
            $photos = $this->photoModel->getActivePhotos($category->id);

            $hasPhotos = !empty($photos);

            // If photos are empty, get photos from other programs
            if (empty($photos)) {
                $photos = $this->photoModel->getAllPhotos();
            }

            // Group photos by year from year field
            $groupedPhotos = [];

            foreach ($photos as $photo) {
                $year = $photo->year ?? 'Unknown';

                if (!isset($groupedPhotos[$year])) {
                    $groupedPhotos[$year] = [];
                }

                $groupedPhotos[$year][] = $photo;
            }

            // Sort the years in descending order (newest first)
            krsort($groupedPhotos);

            // Get other programs for this category
            $otherPrograms = $this->programModel->getOtherPrograms($category->id);

            // Structure other program photos
            $otherProgramPhotos = [];

            foreach ($otherPrograms as $program) {
                $programCategoryId = $program->program_category_id;
                $programPhotos = $this->photoModel->getActivePhotos($programCategoryId);

                // just get 4 photos
                $programPhotos = array_slice($programPhotos, 0, 4);

                if (!empty($programPhotos)) {
                    $otherProgramPhotos[] = [
                        'name' => $program->name,
                        'web_url' => $program->web_url,
                        'photos' => $programPhotos
                    ];
                }
            }

            return $this->respondSuccess([
                'category' => $category,
                'hasPhotos' => $hasPhotos,
                'photos' => $groupedPhotos,
                'otherProgramPhotos' => $otherProgramPhotos
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

            $normalizedWebUrl = normalize_web_url($webUrl);

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            return $this->respondSuccess([
                'category' => $category,
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get insights data
     * Medium Priority Cache: 1 hour TTL
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

            $normalizedWebUrl = normalize_web_url($webUrl);

            $data = $this->cacheResponse(function() use ($normalizedWebUrl) {
                // Get program category by web_url
                $programCategory = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

                if (!$programCategory) {
                    return null;
                }

                // Get latest program for this category
                $currentActiveProgram = $this->programModel->getActivePrograms($programCategory->id);

                if (!$currentActiveProgram) {
                    return ['category' => $programCategory, 'error' => 'No active program found'];
                }

                // Get insights for the active program
                $totalRegisteredParticipants = $this->participantModel->getTotalParticipants($currentActiveProgram->id);
                $totalValidCountries = $this->participantModel->getTotalValidCountries($currentActiveProgram->id);
                $validCountriesDataCollection = $this->participantModel->getValidCountriesData($currentActiveProgram->id);
                
                // Get program subthemes for the active program
                $programSubthemes = $this->programSubthemeModel->getActiveSubthemes($currentActiveProgram->id);

                // Add subthemes to the program object
                $programWithSubthemes = clone $currentActiveProgram;
                $programWithSubthemes->program_subthemes = $programSubthemes;

                $activeProgramInsightsData = [
                    'program' => $programWithSubthemes,
                    'total_registered_participants' => $totalRegisteredParticipants,
                    'total_countries' => $totalValidCountries,
                    'countries_data' => $validCountriesDataCollection,
                ];

                // Get insights for this category
                $categoryInsightsData = [
                    'active_program_insights' => $activeProgramInsightsData,
                ];

                return [
                    'category' => $programCategory,
                    'insightsData' => $categoryInsightsData,
                ];
            }, ['web_url' => $normalizedWebUrl], null, 3600); // 1 hour cache

            if ($data === null) {
                return $this->respondNotFound('Program category not found');
            }

            if (isset($data['error'])) {
                return $this->respondNotFound($data['error']);
            }

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get announcements data
     * Medium Priority Cache: 30 minutes TTL
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function announcements()
    {
        try {
            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            $normalizedWebUrl = normalize_web_url($webUrl);

            $data = $this->cacheResponse(function() use ($normalizedWebUrl) {
                // Get program category by web_url
                $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

                if (!$category) {
                    return null;
                }

                // get programs
                $programs = $this->programModel->getActivePrograms($category->id);

                if (!$programs) {
                    return ['category' => $category, 'error' => 'No active program found'];
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
                $news = $this->announcementModel->getByProgramId($latestProgram->id, true, false);

                // only return news with visible_to = 1
                $news = array_filter($news, function ($announcement) {
                    return $announcement->visible_to == 1;
                });

                $visibleAnnouncementsCount = count($news);

                return [
                    'category' => $category,
                    'programs' => $programs,
                    'latestProgram' => $latestProgram,
                    'announcements' => $news,
                    'visible_announcements_count' => $visibleAnnouncementsCount,
                ];
            }, ['web_url' => $normalizedWebUrl], null, 1800); // 30 minutes cache

            if ($data === null) {
                return $this->respondNotFound('Program category not found');
            }

            if (isset($data['error'])) {
                return $this->respondNotFound($data['error']);
            }

            return $this->respondSuccess($data, self::HTTP_OK, 'Announcements data retrieved successfully');
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

            // Normalize web_url
            $normalizedWebUrl = normalize_web_url($webUrl);

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

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


            $normalizedWebUrl = normalize_web_url($webUrl);

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

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

            // Normalize web_url
            $normalizedWebUrl = normalize_web_url($webUrl);

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

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

            // Normalize web_url
            $normalizedWebUrl = normalize_web_url($webUrl);

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

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

            // Normalize web_url
            $normalizedWebUrl = normalize_web_url($webUrl);

            // Get program category by web_url
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

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

    /**
     * Get insights data with country validation debug info
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function insightsDebug()
    {
        try {
            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            $normalizedWebUrl = normalize_web_url($webUrl);

            // Get program category by web_url
            $programCategory = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

            if (!$programCategory) {
                return $this->respondNotFound('Program category not found');
            }

            // Get latest program for this category
            $currentActiveProgram = $this->programModel->getActivePrograms($programCategory->id);

            if (!$currentActiveProgram) {
                return $this->respondNotFound('No active program found for this category');
            }

            // Get insights for the active program
            $totalRegisteredParticipants = $this->participantModel->getTotalParticipants($currentActiveProgram->id);
            $totalValidCountries = $this->participantModel->getTotalValidCountries($currentActiveProgram->id);
            $validCountriesDataCollection = $this->participantModel->getValidCountriesData($currentActiveProgram->id);
            $invalidCountriesDataCollection = $this->participantModel->getInvalidCountriesData($currentActiveProgram->id);
            
            // Also get original data for comparison
            $totalOriginalCountries = $this->participantModel->getTotalCountries($currentActiveProgram->id);
            $originalCountriesDataCollection = $this->participantModel->getCountriesData($currentActiveProgram->id);
            
            // Get program subthemes for the active program
            $programSubthemes = $this->programSubthemeModel->getActiveSubthemes($currentActiveProgram->id);

            // Add subthemes to the program object
            $programWithSubthemes = clone $currentActiveProgram;
            $programWithSubthemes->program_subthemes = $programSubthemes;

            $debugInsightsData = [
                'program' => $programWithSubthemes,
                'total_registered_participants' => $totalRegisteredParticipants,
                'original_total_countries' => $totalOriginalCountries,
                'valid_total_countries' => $totalValidCountries,
                'original_countries_data' => $originalCountriesDataCollection,
                'valid_countries_data' => $validCountriesDataCollection,
                'invalid_countries_data' => $invalidCountriesDataCollection,
            ];

            return $this->respondSuccess([
                'category' => $programCategory,
                'debugData' => $debugInsightsData,
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
