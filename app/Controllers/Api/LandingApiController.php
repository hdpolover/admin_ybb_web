<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramCategoryModel;
use App\Models\ProgramModel;
use App\Models\ProgramTestimonyModel;
use App\Models\ProgramVideoTestimonyModel;
use App\Models\ProgramSpeakerModel;
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
    protected $videoTestimonyModel;
    protected $speakerModel;
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
        $this->videoTestimonyModel = new ProgramVideoTestimonyModel();
        $this->speakerModel = new ProgramSpeakerModel();
        $this->faqModel = new FaqModel();
        $this->announcementModel = new AnnouncementModel();
        $this->photoModel = new ProgramPhotoModel();
        $this->participantModel = new ParticipantModel();
        $this->programSubthemeModel = new ProgramSubthemeModel();
    }

    /**
     * Clear cache for testing - DEVELOPMENT ONLY
     * GET /api/landing/clear-cache?web_url={url}
     */
    public function clearCache()
    {
        try {
            $this->invalidateLandingCache();
            
            return $this->respondSuccess([
                'message' => 'Cache cleared successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->respondError('Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Test endpoint to verify video testimonies - DEVELOPMENT ONLY
     * GET /api/landing/test-video-testimonies?web_url={url}
     */
    public function testVideoTestimonies()
    {
        try {
            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            $normalizedWebUrl = normalize_web_url($webUrl);

            // Get program category by web_url (no cache)
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // Get all programs for this category
            $allPrograms = $this->programModel->getAllPrograms($category->id);

            // Get active video testimonies for this category using the new method
            $videoTestimonies = $this->videoTestimonyModel->getActiveVideoTestimoniesByCategory($category->id);

            // Group video testimonies by program_id
            $groupedVideoTestimonies = [];
            foreach ($videoTestimonies as $videoTestimony) {
                $programId = $videoTestimony->program_id;
                
                if (!isset($groupedVideoTestimonies[$programId])) {
                    // Find program details for this program_id
                    $programInfo = null;
                    foreach ($allPrograms as $program) {
                        if ($program->id == $programId) {
                            $programInfo = $program;
                            break;
                        }
                    }
                    
                }
                
                $groupedVideoTestimonies[$programId]['videos'][] = $videoTestimony;
            }

            return $this->respondSuccess([
                'category' => $category,
                'programs_count' => count($allPrograms),
                'programs' => $allPrograms,
                'raw_video_testimonies_count' => count($videoTestimonies),
                'raw_video_testimonies' => $videoTestimonies,
                'grouped_video_testimonies_count' => count($groupedVideoTestimonies),
                'grouped_video_testimonies' => array_values($groupedVideoTestimonies),
                'model_methods_available' => [
                    'getActiveVideoTestimoniesByCategory' => method_exists($this->videoTestimonyModel, 'getActiveVideoTestimoniesByCategory'),
                    'getAllVideoTestimoniesByCategory' => method_exists($this->videoTestimonyModel, 'getAllVideoTestimoniesByCategory'),
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->respondError('Error testing video testimonies: ' . $e->getMessage());
        }
    }

    /**
     * Test endpoint to verify payment flags - DEVELOPMENT ONLY
     * GET /api/landing/test-payment-flags?web_url={url}
     */
    public function testPaymentFlags()
    {
        try {
            $webUrl = $this->request->getGet('web_url');

            if (empty($webUrl)) {
                return $this->respondValidationErrors('web_url parameter is required');
            }

            $normalizedWebUrl = normalize_web_url($webUrl);

            // Get program category by web_url (no cache)
            $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

            if (!$category) {
                return $this->respondNotFound('Program category not found');
            }

            // Get all programs for this category (should include payment flags)
            $allPrograms = $this->programModel->getAllPrograms($category->id);

            return $this->respondSuccess([
                'category' => $category,
                'programs_count' => count($allPrograms),
                'programs' => $allPrograms,
                'payment_flags_check' => array_map(function($program) {
                    return [
                        'program_id' => $program->id,
                        'program_name' => $program->name,
                        'has_payment_flags' => isset($program->registration_payments),
                        'payment_flags' => $program->registration_payments ?? null
                    ];
                }, $allPrograms),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->respondError('Error testing payment flags: ' . $e->getMessage());
        }
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

            // $data = $this->cacheResponse(function() use ($normalizedWebUrl) { // DISABLE CACHE
            $data = (function() use ($normalizedWebUrl) {
                // Get program category by web_url
                $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

                if (!$category) {
                    return null;
                }

                // Get all programs for this category
                $allPrograms = $this->programModel->getAllPrograms($category->id);

                // Get active testimonies for this category
                $testimonies = $this->testimonyModel->getActiveTestimonies($category->id);

                // Get active video testimonies for this category
                $videoTestimonies = $this->videoTestimonyModel->getActiveVideoTestimoniesByCategory($category->id);

                $hasVideoTestimonies = !empty($videoTestimonies);

                // If video testimonies are empty, get video testimonies from other categories
                if (empty($videoTestimonies)) {
                    $videoTestimonies = $this->videoTestimonyModel->getAllVideoTestimoniesByCategory($category->id);
                }

                // Group video testimonies by program_id
                $groupedVideoTestimonies = [];
                foreach ($videoTestimonies as $videoTestimony) {
                    $programId = $videoTestimony->program_id;
                    
                    if (!isset($groupedVideoTestimonies[$programId])) {
                        // Find program details for this program_id
                        $programInfo = null;
                        foreach ($allPrograms as $program) {
                            if ($program->id == $programId) {
                                $programInfo = $program;
                                break;
                            }
                        }
                        
                        $groupedVideoTestimonies[$programId] = [
                            'program_id' => $programId,
                            'program_name' => $programInfo ? $programInfo->name : 'Unknown Program',
                            'program_web_url' => $programInfo ? ($programInfo->web_url ?? null) : null,
                            'videos' => []
                        ];
                    }
                    
                    $groupedVideoTestimonies[$programId]['videos'][] = $videoTestimony;
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

                // Compile home data
                return [
                    'category' => $category,
                    'programs' => $allPrograms,
                    'testimonies' => $testimonies,
                    'hasVideoTestimonies' => $hasVideoTestimonies,
                    'videoTestimonies' => array_values($groupedVideoTestimonies), // Convert to indexed array
                    'hasPhotos' => $hasPhotos,
                    'photos' => $groupedPhotos,
                ];
            })(); // }, ['web_url' => $normalizedWebUrl], null, 1800); // 30 minutes cache

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

            // $data = $this->cacheResponse(function() use ($normalizedWebUrl) { // DISABLE CACHE
            $data = (function() use ($normalizedWebUrl) {
                // Get program category by web_url
                $category = $this->programCategoryModel->getProgramCategoryByParams(['web_url' => $normalizedWebUrl]);

                if (!$category) {
                    return null;
                }

                // Get active programs for this category
                $activePrograms = $this->programModel->getActivePrograms($category->id);

                // Get previous programs for this category (inactive programs from same category)
                $previousPrograms = $this->programModel->getPreviousPrograms($normalizedWebUrl, $category->id);

                // Get other programs (active programs from different categories)
                $otherPrograms = $this->programModel->getOtherPrograms($category->id);

                return [
                    'category' => $category,
                    'activePrograms' => $activePrograms,
                    'previousPrograms' => $previousPrograms,
                    'otherPrograms' => $otherPrograms
                ];
            })(); // }, ['web_url' => $normalizedWebUrl, 'v' => '3'], null, 3600); // 1 hour cache

            if ($data === null) {
                return $this->respondNotFound('Program category not found');
            }

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get video testimonies data
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function videoTestimonies()
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

            // Get all programs for this category
            $allPrograms = $this->programModel->getAllPrograms($category->id);

            // Get active video testimonies for this category
            $videoTestimonies = $this->videoTestimonyModel->getActiveVideoTestimoniesByCategory($category->id);

            $hasVideoTestimonies = !empty($videoTestimonies);

            // If video testimonies are empty, get video testimonies from other categories
            if (empty($videoTestimonies)) {
                $videoTestimonies = $this->videoTestimonyModel->getAllVideoTestimoniesByCategory($category->id);
            }

            // Group video testimonies by program_id
            $groupedVideoTestimonies = [];
            foreach ($videoTestimonies as $videoTestimony) {
                $programId = $videoTestimony->program_id;
                
                if (!isset($groupedVideoTestimonies[$programId])) {
                    // Find program details for this program_id
                    $programInfo = null;
                    foreach ($allPrograms as $program) {
                        if ($program->id == $programId) {
                            $programInfo = $program;
                            break;
                        }
                    }
                    
                    $groupedVideoTestimonies[$programId] = [
                        'program_id' => $programId,
                        'program_name' => $programInfo ? $programInfo->name : 'Unknown Program',
                        'program_web_url' => $programInfo ? ($programInfo->web_url ?? null) : null,
                        'videos' => []
                    ];
                }
                
                $groupedVideoTestimonies[$programId]['videos'][] = $videoTestimony;
            }

            // Get other programs for this category to show cross-program video testimonies
            $otherPrograms = $this->programModel->getOtherPrograms($category->id);

            // Structure other program video testimonies
            $otherProgramVideoTestimonies = [];

            foreach ($otherPrograms as $program) {
                $programCategoryId = $program->program_category_id;
                $programVideoTestimonies = $this->videoTestimonyModel->getActiveVideoTestimoniesByCategory($programCategoryId);

                // Just get 3 video testimonies per program
                $programVideoTestimonies = array_slice($programVideoTestimonies, 0, 3);

                if (!empty($programVideoTestimonies)) {
                    $otherProgramVideoTestimonies[] = [
                        'name' => $program->name,
                        'web_url' => $program->web_url,
                        'videos' => $programVideoTestimonies
                    ];
                }
            }

            return $this->respondSuccess([
                'category' => $category,
                'hasVideoTestimonies' => $hasVideoTestimonies,
                'videoTestimonies' => array_values($groupedVideoTestimonies), // Convert to indexed array
                'otherProgramVideoTestimonies' => $otherProgramVideoTestimonies
            ]);
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

                // Get active programs for this category (now returns array)
                $activePrograms = $this->programModel->getActivePrograms($programCategory->id);

                if (!$activePrograms || empty($activePrograms)) {
                    return ['category' => $programCategory, 'error' => 'No active program found'];
                }

                // Get the latest active program (most recent by end_date)
                $currentActiveProgram = null;
                foreach ($activePrograms as $program) {
                    if ($currentActiveProgram === null || strtotime($program->end_date) > strtotime($currentActiveProgram->end_date)) {
                        $currentActiveProgram = $program;
                    }
                }

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
            }, ['web_url' => $normalizedWebUrl, 'v' => '2'], null, 1800); // 30 minutes cache for faster loading

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
     * Get announcements data from all programs in the category
     * Shows announcements from both active and inactive programs within the same category,
     * but only active and non-deleted announcements are returned.
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

                // get all programs for this category (both active and inactive)
                $allPrograms = $this->programModel->getAllPrograms($category->id);
                
                // get active programs for fallback
                $activePrograms = $this->programModel->getActivePrograms($category->id);

                // get latest program for this category from active programs based on end date
                $latestProgram = null;

                // Check if active programs exist and process accordingly
                if ($activePrograms) {
                    if (is_object($activePrograms) && !is_array($activePrograms)) {
                        // Handle single object case
                        $latestProgram = $activePrograms;
                    } else if (is_array($activePrograms)) {
                        // Handle array case
                        if (count($activePrograms) == 1) {
                            $latestProgram = $activePrograms[0];
                        } else {
                            // find the latest program based on end date
                            foreach ($activePrograms as $program) {
                                if ($latestProgram === null || strtotime($program->end_date) > strtotime($latestProgram->end_date)) {
                                    $latestProgram = $program;
                                }
                            }
                        }
                    }
                }

                // get announcements from all programs in this category (active and inactive programs, but only active announcements)
                $news = $this->announcementModel->getAnnouncementsByProgramCategory($category->id);

                // only return news with visible_to = 1
                $news = array_filter($news, function ($announcement) {
                    return $announcement->visible_to == 1;
                });

                $visibleAnnouncementsCount = count($news);

                return [
                    'category' => $category,
                    'programs' => $activePrograms,
                    'latestProgram' => $latestProgram,
                    'announcements' => $news,
                    'visible_announcements_count' => $visibleAnnouncementsCount,
                    'all_programs' => $allPrograms, // Include all programs for reference
                ];
            }, ['web_url' => $normalizedWebUrl], null, 1800); // 30 minutes cache

            if ($data === null) {
                return $this->respondNotFound('Program category not found');
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

            // Get speakers for this program
            $speakers = $this->speakerModel->getByProgramId($program->id, true); // only active speakers
            $keynoteSpeakers = $this->speakerModel->getKeynoteSpeakers($program->id);
            $regularSpeakers = $this->speakerModel->getRegularSpeakers($program->id);

            return $this->respondSuccess([
                'category' => $category,
                'program' => $program,
                'speakers' => $speakers,
                'keynote_speakers' => $keynoteSpeakers,
                'regular_speakers' => $regularSpeakers
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

            // Get speakers for this program
            $speakers = $this->speakerModel->getByProgramId($program->id, true); // only active speakers
            $keynoteSpeakers = $this->speakerModel->getKeynoteSpeakers($program->id);
            $regularSpeakers = $this->speakerModel->getRegularSpeakers($program->id);

            // get participant photos by program id
            $photos = $this->participantModel->getParticipantPhotosByProgramId($program->id);

            return $this->respondSuccess([
                'category' => $category,
                'program' => $program,
                'speakers' => $speakers,
                'keynote_speakers' => $keynoteSpeakers,
                'regular_speakers' => $regularSpeakers
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

            // Get active programs for this category (now returns array)
            $activePrograms = $this->programModel->getActivePrograms($programCategory->id);

            if (!$activePrograms || empty($activePrograms)) {
                return $this->respondNotFound('No active program found for this category');
            }

            // Get the latest active program (most recent by end_date)
            $currentActiveProgram = null;
            foreach ($activePrograms as $program) {
                if ($currentActiveProgram === null || strtotime($program->end_date) > strtotime($currentActiveProgram->end_date)) {
                    $currentActiveProgram = $program;
                }
            }

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
