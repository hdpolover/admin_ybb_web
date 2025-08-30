<?php

namespace App\Controllers\Api;

use App\Models\AmbassadorModel;
use App\Models\ProgramModel;
use App\Models\ProgramCategoryModel;
use App\Models\ParticipantModel;
use App\Models\AmbassadorParticipantReferralModel;
use App\Models\ProgramPaymentModel;
use App\Controllers\Api\ApiBaseController;
use App\Libraries\JWTHandler;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;

class AmbassadorsApiController extends ApiBaseController
{
    protected $model;
    protected $programModel;
    protected $programCategoryModel;
    protected $ambassadorParticipantReferralModel;
    protected $participantModel;
    protected $programPaymentModel;
    protected $jwtHandler;

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

        // Initialize models and services
        $this->model = new AmbassadorModel();
        $this->programModel = new ProgramModel();
        $this->programCategoryModel = new ProgramCategoryModel();
        $this->ambassadorParticipantReferralModel = new AmbassadorParticipantReferralModel();
        $this->participantModel = new ParticipantModel();
        $this->programPaymentModel = new ProgramPaymentModel();
        $this->jwtHandler = new JWTHandler();
    }

    /**
     * Get JWT token from header and validate it
     * 
     * @return object|false User data from token or false if invalid
     */
    protected function getAuthenticatedUser()
    {
        $token = $this->jwtHandler->getTokenFromHeader();
        
        if (empty($token)) {
            return false;
        }
        
        return $this->jwtHandler->getUserFromToken($token);
    }

    /**
     * Get all ambassadors for the current program
     * GET /api/ambassadors
     * 
     * IMPORTANT: Returns ambassadors for specified program or current session program
     */
    public function index()
    {
        // Prevent caching of ambassador data
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        try {
            // Get program ID from query parameter or session
            $programId = $this->request->getGet('program_id') ?? session('current_program');
            
            if (!$programId) {
                // Return all ambassadors if no program filter
                $programId = null;
            }

            // Get pagination parameters
            $limit = $this->request->getGet('limit') ?? 10;
            $offset = $this->request->getGet('offset') ?? 0;
            
            // Set up filters
            $filters = [
                'is_deleted' => 0
            ];
            
            // Add program filter if specified
            if ($programId) {
                $filters['program_id'] = $programId;
            }
            
            // Add any additional filters from request
            $status = $this->request->getGet('status');
            if (!empty($status)) {
                $filters['is_active'] = $status;
            }

            // Get ambassadors data filtered by program ID
            $result = $this->model->getAmbassadors($limit, $offset, $filters);

            if (!$result) {
                return $this->respondSuccess([
                    'data' => [],
                    'total' => 0,
                    'program_id' => $programId
                ]);
            }

            return $this->respondSuccess([
                'data' => $result['data'],
                'total' => $result['total'],
                'program_id' => $programId,
                'pagination' => [
                    'limit' => (int)$limit,
                    'offset' => (int)$offset,
                    'current_page' => floor($offset / $limit) + 1,
                    'total_pages' => ceil($result['total'] / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to retrieve ambassadors: ' . $e->getMessage());
        }
    }

    /**
     * Get all participants referred by an ambassador
     * GET /api/ambassadors/{id}/referrals
     * 
     * IMPORTANT: Only returns data if ambassador belongs to the currently selected program
     */
    public function getAmbassadorReferrals($id)
    {
        // Prevent caching of ambassador data
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        try {
            // Get current program ID from session
            $programId = session('current_program');
            
            if (!$programId) {
                return $this->failValidationErrors('No program selected');
            }

            $ambassador = $this->model->find($id);

            if (!$ambassador) {
                return $this->failNotFound('Ambassador not found');
            }
            
            // Security check: Ensure ambassador belongs to the current program
            if ($ambassador->program_id != $programId) {
                return $this->failNotFound('Ambassador not found in selected program');
            }

            // Get comprehensive list of participant IDs referred by this ambassador
            $participantIds = $this->model->getComprehensiveReferralParticipantIds($id);

            if (empty($participantIds)) {
                return $this->respondSuccess([
                    'referrals' => [],
                    'total' => 0
                ]);
            }

            // Get participant details
            $participants = $this->participantModel->getParticipantsByIds($participantIds);

            return $this->respondSuccess([
                'referrals' => $participants,
                'total' => count($participants)
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to retrieve referrals: ' . $e->getMessage());
        }
    }

    /**
     * Generate referral link for an ambassador.
     * GET /api/ambassadors/{id}/generate-link
     * 
     * IMPORTANT: Only generates link if ambassador belongs to the currently selected program
     */
    public function generateLink($id)
    {
        // Prevent caching of ambassador data
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        try {
            // Get current program ID from session
            $programId = session('current_program');
            
            if (!$programId) {
                return $this->failValidationErrors('No program selected');
            }

            $ambassador = $this->model->find($id);

            if (!$ambassador) {
                return $this->failNotFound('Ambassador not found');
            }
            
            // Security check: Ensure ambassador belongs to the current program
            if ($ambassador->program_id != $programId) {
                return $this->failNotFound('Ambassador not found in selected program');
            }

            $program = $this->programModel->getProgramById($ambassador->program_id);
            $programCategoryId = $program->program_category_id ?? null;
            $programCategory = $this->programCategoryModel->getProgramCategoryById($programCategoryId);

            if (!$program || !$programCategory || !$programCategory->web_url) {
                return $this->failValidationErrors('Program or Program Category not found or web URL is missing');
            }

            $refCode = $ambassador->ref_code;
            $webUrl = rtrim($programCategory->web_url, '/'); // Remove trailing slash if present

            // Encrypt the ref code
            $query = $refCode;
            $encryptedQuery = url_encrypt($query);
            
            if ($encryptedQuery === false) {
                log_message('error', 'Failed to encrypt ref_code: ' . $refCode);
                return $this->failServerError('Failed to generate referral link');
            }
            
            // The url_encrypt function already makes the string URL-safe,
            // but we'll use urlencode as an extra precaution when adding to the URL
            $referralLink = $webUrl . '/sign-up?q=' . urlencode($encryptedQuery);

            $data = [
                'ref_code' => $refCode,
                'web_url' => $webUrl,
                'encrypted_query' => $encryptedQuery,
                'referral_link' => $referralLink
            ];
            
            log_message('info', 'Generated referral link for ambassador ID: ' . $id);
            
            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            log_message('error', 'Error generating referral link: ' . $e->getMessage());
            return $this->failServerError('An error occurred while generating the referral link: ' . $e->getMessage());
        }
    }

    /**
     * 🔍 Check Encrypted Query
     * GET /api/ambassadors/check-query
     */
    public function checkEncryptedQuery()
    {
        // Prevent caching of ambassador data
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        try {
            // Get data from GET request instead of POST
            $encryptedQuery = $this->request->getGet('encrypted_query');

            if (empty($encryptedQuery)) {
                log_message('error', 'Empty encrypted query provided');
                return $this->respondError('Encrypted query is required', 400);
            }

            // URL decode the encrypted query if needed
            $encryptedQuery = urldecode($encryptedQuery);
            
            // Trim any whitespace
            $encryptedQuery = trim($encryptedQuery);
            
            // Basic validation of the encrypted data format
            if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $encryptedQuery)) {
                log_message('error', 'Invalid encrypted query format: ' . $encryptedQuery);
                return $this->respondError('Invalid encrypted query format', 400);
            }

            // Try to decrypt the query
            try {
                // Set the second parameter to false to get a string instead of array
                $decryptedQuery = url_decrypt($encryptedQuery, false);

                if ($decryptedQuery === false) {
                    log_message('error', 'Decryption failed for query: ' . $encryptedQuery);
                    return $this->respondError('Decryption failed: Invalid query', 400);
                }

                // get ambassador details by ref_code
                $ambassador = $this->model->getByRefCode($decryptedQuery);

                if (!$ambassador) {
                    log_message('error', 'Ambassador not found for ref_code: ' . $decryptedQuery);
                    return $this->failNotFound('Ambassador not found');
                }

                $data = [
                    'ref_code' => $ambassador->ref_code,
                    'is_valid' => true,
                    'ambassador' => $ambassador,
                ];

                log_message('info', 'Successfully validated referral code: ' . $decryptedQuery);
                return $this->respondSuccess($data);
            } catch (\Exception $e) {
                log_message('error', 'Exception during decryption: ' . $e->getMessage());
                return $this->respondError('Decryption failed: ' . $e->getMessage(), 400);
            }
        } catch (\Exception $e) {
            log_message('error', 'Server error in checkEncryptedQuery: ' . $e->getMessage());
            return $this->failServerError('An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Get ambassador by ID with shareable referral link
     * GET /api/ambassadors/{id}
     * 
     * IMPORTANT: Only returns ambassador if they belong to the currently selected program
     */
    public function getAmbassador($id)
    {
        // Prevent caching of ambassador data
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        try {
            // Get current program ID from session
            $programId = session('current_program');
            
            if (!$programId) {
                return $this->failValidationErrors('No program selected');
            }

            $ambassador = $this->model->find($id);

            if (!$ambassador) {
                return $this->failNotFound('Ambassador not found');
            }
            
            // Security check: Ensure ambassador belongs to the current program
            if ($ambassador->program_id != $programId) {
                return $this->failNotFound('Ambassador not found in selected program');
            }

            // Generate shareable referral link
            $shareableLink = null;
            try {
                $program = $this->programModel->getProgramById($ambassador->program_id);
                $programCategoryId = $program->program_category_id ?? null;
                $programCategory = $this->programCategoryModel->getProgramCategoryById($programCategoryId);

                if ($program && $programCategory && $programCategory->web_url) {
                    $refCode = $ambassador->ref_code;
                    $webUrl = rtrim($programCategory->web_url, '/'); // Remove trailing slash if present

                    // add https:// if not present
                    if (!preg_match('/^https?:\/\//', $webUrl)) {
                        $webUrl = 'https://' . $webUrl;
                    }

                    // Encrypt the ref code
                    $encryptedQuery = url_encrypt($refCode);
                    
                    if ($encryptedQuery !== false) {
                        $shareableLink = $webUrl . '/sign-up?q=' . urlencode($encryptedQuery);
                    }
                }
            } catch (\Exception $linkError) {
                // Log the error but don't fail the entire request
                log_message('error', 'Failed to generate shareable link for ambassador ' . $id . ': ' . $linkError->getMessage());
            }

            return $this->respondSuccess([
                'ambassador' => $ambassador,
                'program_id' => $programId,
                'shareable_link' => $shareableLink
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to retrieve ambassador: ' . $e->getMessage());
        }
    }

    /**
     * Get ambassador by ref code
     * GET /api/ambassadors/programs/{programId}/ref-code/{refCode}
     */
    public function getAmbassadorByRefAndProgram($programId, $refCode)
    {
        // Prevent caching of ambassador data
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        $ambassador = $this->model->getAmbassadorByRefCodeAndProgramId($refCode, $programId);

        if (!$ambassador) {
            return $this->failNotFound('Ambassador referral code is not valid for this program. Please check the code and try again.');
        }

        $data = [
            'ref_code' => $ambassador->ref_code,
            'is_valid' => true,
            'ambassador' => $ambassador,
        ];

        return $this->respondSuccess($data);
    }

    /**
     * Get ambassador dashboard overview metrics
     * GET /api/ambassador/dashboard/overview
     */
    public function getDashboardOverview()
    {
        // Authenticate ambassador
        $userData = $this->getAuthenticatedUser();
        
        if (!$userData) {
            return $this->respondUnauthorized('No authentication data found');
        }
        
        if (intval($userData->type) !== 3) {
            return $this->respondUnauthorized('User type is ' . $userData->type . ' (int: ' . intval($userData->type) . '), expected 3 (ambassador)');
        }

        try {
            $ambassadorId = $userData->id;
            $programId = $userData->program_id;

            // Get basic ambassador info
            $ambassador = $this->model->find($ambassadorId);
            if (!$ambassador) {
                return $this->respondNotFound('Ambassador not found');
            }

            // Get program information
            $program = $this->programModel->find($programId);

            // Get referral counts (without program filter to avoid database error)
            $referralCounts = $this->model->getComprehensiveReferralCounts($programId, $ambassadorId);
            $totalReferrals = $referralCounts[$ambassadorId] ?? 0;

            // Get participant IDs referred by this ambassador
            $participantIds = $this->model->getComprehensiveReferralParticipantIds($ambassadorId);

            // Get detailed participant statistics
            $participantStats = $this->getDetailedParticipantStats($participantIds);
            
            // Get recent referral activity
            $recentActivity = $this->getRecentReferralActivity($participantIds, 10);
            
            // Get conversion and performance metrics
            $conversionMetrics = $this->getConversionMetrics($participantIds);
            
            // Get payment statistics
            $paymentStats = $this->getPaymentStatistics($participantIds);

            // Generate shareable referral link
            $shareableLink = null;
            try {
                $program = $this->programModel->getProgramById($ambassador->program_id);
                $programCategoryId = $program->program_category_id ?? null;
                $programCategory = $this->programCategoryModel->getProgramCategoryById($programCategoryId);

                if ($program && $programCategory && $programCategory->web_url) {
                    $refCode = $ambassador->ref_code;
                    $webUrl = rtrim($programCategory->web_url, '/');

                    // add https:// if not present
                    if (!preg_match('/^https?:\/\//', $webUrl)) {
                        $webUrl = 'https://' . $webUrl;
                    }

                    $encryptedQuery = url_encrypt($refCode);
                    
                    if ($encryptedQuery !== false) {
                        $shareableLink = $webUrl . '/sign-up?q=' . urlencode($encryptedQuery);
                    }
                }
            } catch (\Exception $linkError) {
                log_message('error', 'Failed to generate shareable link in overview: ' . $linkError->getMessage());
            }

            // Calculate achievement progress
            $achievements = $this->calculateAchievements($totalReferrals, $participantStats, $conversionMetrics);

            $overview = [
                'ambassador' => [
                    'id' => $ambassador->id,
                    'name' => $ambassador->name,
                    'email' => $ambassador->email,
                    'ref_code' => $ambassador->ref_code,
                    'phone_number' => $ambassador->phone_number ?? null,
                    'institution' => $ambassador->institution ?? null,
                    'gender' => $ambassador->gender ?? null,
                    'is_active' => (bool)$ambassador->is_active,
                    'member_since' => date('M d, Y', strtotime($ambassador->created_at)),
                    'shareable_link' => $shareableLink
                ],
                'metrics' => [
                    'total_referrals' => $totalReferrals,
                    'active_participants' => count($participantIds),
                    'completed_registrations' => $participantStats['completed_forms'],
                    'pending_registrations' => $participantStats['incomplete_forms'],
                    'conversion_rate' => $conversionMetrics['completion_rate'],
                    'this_month_referrals' => $conversionMetrics['this_month_count'],
                    'last_30_days_referrals' => $conversionMetrics['last_30_days_count']
                ],
                'participant_breakdown' => [
                    'total' => count($participantIds),
                    'by_category' => $participantStats['by_category'],
                    'by_status' => $participantStats['by_status'],
                    'by_nationality' => $participantStats['top_countries'],
                    'by_institution_type' => $participantStats['institution_types']
                ],
                'payment_summary' => [
                    'total_payments_processed' => $paymentStats['paid_count'] + $paymentStats['pending_count'],
                    'paid_participants' => $paymentStats['paid_count'],
                    'pending_payments' => $paymentStats['pending_count'],
                    'payment_completion_rate' => $paymentStats['payment_rate'],
                    'payment_efficiency_score' => round($paymentStats['payment_rate'], 1)
                ],
                'performance_insights' => [
                    'best_performing_month' => $conversionMetrics['best_month'],
                    'registration_trend' => $conversionMetrics['trend_direction'],
                    'engagement_score' => $conversionMetrics['engagement_score'],
                    'quality_score' => $conversionMetrics['quality_score']
                ],
                'recent_activity' => $recentActivity,
                'achievements' => $achievements,
                'program' => [
                    'id' => $programId,
                    'name' => $program->name ?? 'Unknown Program',
                    'start_date' => $program->start_date ?? null,
                    'end_date' => $program->end_date ?? null,
                    'status' => $this->getProgramStatus($program)
                ],
                'quick_stats' => [
                    'total_referrals' => $totalReferrals,
                    'this_week' => $conversionMetrics['this_week_count'],
                    'completion_rate' => round($conversionMetrics['completion_rate'], 1) . '%',
                    'ranking' => $this->getAmbassadorRanking($ambassadorId, $programId, $totalReferrals)
                ]
            ];

            return $this->respondSuccess($overview, self::HTTP_OK, 'Dashboard overview retrieved successfully');

        } catch (\Exception $e) {
            log_message('error', 'Ambassador dashboard overview error: ' . $e->getMessage());
            log_message('error', 'Failed to retrieve dashboard data: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve dashboard data: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get detailed participant statistics for ambassador overview
     * 
     * @param array $participantIds List of participant IDs
     * @return array Detailed statistics
     */
    private function getDetailedParticipantStats($participantIds)
    {
        if (empty($participantIds)) {
            return [
                'completed_forms' => 0,
                'incomplete_forms' => 0,
                'by_category' => ['fully_funded' => 0, 'self_funded' => 0],
                'by_status' => ['active' => 0, 'inactive' => 0],
                'top_countries' => [],
                'institution_types' => []
            ];
        }

        try {
            $participantModel = new \App\Models\ParticipantModel();
            
            // Get participants with their status information
            $participants = $participantModel->select('
                participants.id,
                participants.category,
                participants.nationality,
                participants.institution,
                participants.is_active,
                participant_statuses.form_status
            ')
            ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
            ->whereIn('participants.id', $participantIds)
            ->where('participants.is_deleted', 0)
            ->findAll();

            $stats = [
                'completed_forms' => 0,
                'incomplete_forms' => 0,
                'by_category' => ['fully_funded' => 0, 'self_funded' => 0],
                'by_status' => ['active' => 0, 'inactive' => 0],
                'top_countries' => [],
                'institution_types' => []
            ];

            $countryCounts = [];
            $institutionTypes = [];

            foreach ($participants as $participant) {
                // Form completion status
                if ($participant->form_status == 2) { // 2 = submitted
                    $stats['completed_forms']++;
                } else { // 0 = not started, 1 = in progress
                    $stats['incomplete_forms']++;
                }

                // Category breakdown
                $category = strtolower($participant->category ?? 'self_funded');
                if (isset($stats['by_category'][$category])) {
                    $stats['by_category'][$category]++;
                }

                // Active status
                if ($participant->is_active) {
                    $stats['by_status']['active']++;
                } else {
                    $stats['by_status']['inactive']++;
                }

                // Country statistics
                if (!empty($participant->nationality)) {
                    $country = trim($participant->nationality);
                    if (!isset($countryCounts[$country])) {
                        $countryCounts[$country] = 0;
                    }
                    $countryCounts[$country]++;
                }

                // Institution type analysis
                if (!empty($participant->institution)) {
                    $institution = trim($participant->institution);
                    if (stripos($institution, 'university') !== false) {
                        $type = 'University';
                    } elseif (stripos($institution, 'college') !== false) {
                        $type = 'College';
                    } elseif (stripos($institution, 'school') !== false) {
                        $type = 'School';
                    } elseif ($institution === 'N/A' || empty($institution)) {
                        $type = 'Not Specified';
                    } else {
                        $type = 'Other Institution';
                    }
                    
                    if (!isset($institutionTypes[$type])) {
                        $institutionTypes[$type] = 0;
                    }
                    $institutionTypes[$type]++;
                }
            }

            // Sort and limit top countries
            arsort($countryCounts);
            $stats['top_countries'] = array_slice($countryCounts, 0, 5, true);

            // Sort institution types
            arsort($institutionTypes);
            $stats['institution_types'] = $institutionTypes;

            return $stats;

        } catch (\Exception $e) {
            log_message('error', 'Error getting participant stats: ' . $e->getMessage());
            return [
                'completed_forms' => 0,
                'incomplete_forms' => 0,
                'by_category' => ['fully_funded' => 0, 'self_funded' => 0],
                'by_status' => ['active' => 0, 'inactive' => 0],
                'top_countries' => [],
                'institution_types' => []
            ];
        }
    }

    /**
     * Get recent referral activity
     * 
     * @param array $participantIds List of participant IDs
     * @param int $limit Number of recent activities to return
     * @return array Recent activity data
     */
    private function getRecentReferralActivity($participantIds, $limit = 10)
    {
        if (empty($participantIds)) {
            return [];
        }

        try {
            $participantModel = new \App\Models\ParticipantModel();
            
            $recentParticipants = $participantModel->select('
                participants.id,
                participants.full_name,
                participants.nationality,
                participants.created_at,
                users.email,
                participant_statuses.form_status
            ')
            ->join('users', 'users.id = participants.user_id', 'left')
            ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
            ->whereIn('participants.id', $participantIds)
            ->where('participants.is_deleted', 0)
            ->orderBy('participants.created_at', 'DESC')
            ->limit($limit)
            ->findAll();

            $activities = [];
            foreach ($recentParticipants as $participant) {
                $formStatus = 'Not Started';
                if ($participant->form_status == 1) {
                    $formStatus = 'In Progress';
                } elseif ($participant->form_status == 2) {
                    $formStatus = 'Submitted';
                }
                
                $activities[] = [
                    'participant_id' => $participant->id,
                    'participant_name' => $participant->full_name,
                    'participant_email' => $participant->email ?? 'N/A',
                    'nationality' => $participant->nationality ?? 'N/A',
                    'registration_date' => date('M d, Y', strtotime($participant->created_at)),
                    'days_ago' => $this->calculateDaysAgo($participant->created_at),
                    'form_completed' => ($participant->form_status == 2),
                    'status' => $formStatus
                ];
            }

            return $activities;

        } catch (\Exception $e) {
            log_message('error', 'Error getting recent activity: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get conversion and performance metrics
     * 
     * @param array $participantIds List of participant IDs
     * @return array Conversion metrics
     */
    private function getConversionMetrics($participantIds)
    {
        if (empty($participantIds)) {
            return [
                'completion_rate' => 0,
                'this_month_count' => 0,
                'last_30_days_count' => 0,
                'this_week_count' => 0,
                'best_month' => 'N/A',
                'trend_direction' => 'stable',
                'engagement_score' => 0,
                'quality_score' => 0
            ];
        }

        try {
            $participantModel = new \App\Models\ParticipantModel();
            
            $participants = $participantModel->select('
                participants.created_at,
                participant_statuses.form_status
            ')
            ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
            ->whereIn('participants.id', $participantIds)
            ->where('participants.is_deleted', 0)
            ->findAll();

            $totalParticipants = count($participants);
            $completedForms = 0;
            $thisMonthCount = 0;
            $last30DaysCount = 0;
            $thisWeekCount = 0;
            $monthlyBreakdown = [];

            $now = new \DateTime();
            $thisMonth = $now->format('Y-m');
            $last30Days = $now->sub(new \DateInterval('P30D'))->format('Y-m-d');
            $now = new \DateTime(); // Reset
            $thisWeekStart = $now->sub(new \DateInterval('P7D'))->format('Y-m-d');

            foreach ($participants as $participant) {
                $createdDate = new \DateTime($participant->created_at);
                $createdMonth = $createdDate->format('Y-m');
                $createdDateStr = $createdDate->format('Y-m-d');

                // Count completed forms (submitted = 2)
                if ($participant->form_status == 2) {
                    $completedForms++;
                }

                // This month count
                if ($createdMonth === $thisMonth) {
                    $thisMonthCount++;
                }

                // Last 30 days count
                if ($createdDateStr >= $last30Days) {
                    $last30DaysCount++;
                }

                // This week count
                if ($createdDateStr >= $thisWeekStart) {
                    $thisWeekCount++;
                }

                // Monthly breakdown for best month calculation
                if (!isset($monthlyBreakdown[$createdMonth])) {
                    $monthlyBreakdown[$createdMonth] = 0;
                }
                $monthlyBreakdown[$createdMonth]++;
            }

            $completionRate = $totalParticipants > 0 ? ($completedForms / $totalParticipants) * 100 : 0;

            // Find best performing month
            $bestMonth = 'N/A';
            if (!empty($monthlyBreakdown)) {
                $maxCount = max($monthlyBreakdown);
                $bestMonthKey = array_search($maxCount, $monthlyBreakdown);
                $bestMonth = date('M Y', strtotime($bestMonthKey . '-01'));
            }

            // Calculate trend (simple comparison of last 2 months)
            $trendDirection = 'stable';
            if (count($monthlyBreakdown) >= 2) {
                $months = array_keys($monthlyBreakdown);
                sort($months);
                $lastMonth = end($months);
                $secondLastMonth = prev($months);
                
                if ($monthlyBreakdown[$lastMonth] > $monthlyBreakdown[$secondLastMonth]) {
                    $trendDirection = 'increasing';
                } elseif ($monthlyBreakdown[$lastMonth] < $monthlyBreakdown[$secondLastMonth]) {
                    $trendDirection = 'decreasing';
                }
            }

            // Calculate engagement and quality scores
            $engagementScore = min(100, round(($last30DaysCount / max(1, $totalParticipants)) * 100));
            $qualityScore = round($completionRate);

            return [
                'completion_rate' => round($completionRate, 1),
                'this_month_count' => $thisMonthCount,
                'last_30_days_count' => $last30DaysCount,
                'this_week_count' => $thisWeekCount,
                'best_month' => $bestMonth,
                'trend_direction' => $trendDirection,
                'engagement_score' => $engagementScore,
                'quality_score' => $qualityScore
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error getting conversion metrics: ' . $e->getMessage());
            return [
                'completion_rate' => 0,
                'this_month_count' => 0,
                'last_30_days_count' => 0,
                'this_week_count' => 0,
                'best_month' => 'N/A',
                'trend_direction' => 'stable',
                'engagement_score' => 0,
                'quality_score' => 0
            ];
        }
    }

    /**
     * Get payment statistics for referred participants (analytics only, no monetary data)
     * 
     * @param array $participantIds List of participant IDs
     * @return array Payment statistics
     */
    private function getPaymentStatistics($participantIds)
    {
        if (empty($participantIds)) {
            return [
                'total_payments' => 0,
                'paid_count' => 0,
                'pending_count' => 0,
                'payment_rate' => 0,
                'completion_efficiency' => 0
            ];
        }

        try {
            $paymentModel = new \App\Models\PaymentModel();
            
            $payments = $paymentModel->select('
                payments.status,
                payments.participant_id
            ')
            ->whereIn('payments.participant_id', $participantIds)
            ->where('payments.is_deleted', 0)
            ->findAll();

            $totalPayments = 0;
            $paidCount = 0;
            $pendingCount = 0;
            $uniqueParticipants = [];

            foreach ($payments as $payment) {
                $uniqueParticipants[$payment->participant_id] = true;
                $totalPayments++;
                
                if ($payment->status == 2) { // Successful payment
                    $paidCount++;
                } elseif ($payment->status == 1) { // Pending payment
                    $pendingCount++;
                }
            }

            $totalParticipants = count($participantIds);
            $participantsWithPayments = count($uniqueParticipants);
            $paymentRate = $totalParticipants > 0 ? ($participantsWithPayments / $totalParticipants) * 100 : 0;
            $completionEfficiency = $totalPayments > 0 ? ($paidCount / $totalPayments) * 100 : 0;

            return [
                'total_payments' => $totalPayments,
                'paid_count' => $paidCount,
                'pending_count' => $pendingCount,
                'payment_rate' => round($paymentRate, 1),
                'completion_efficiency' => round($completionEfficiency, 1)
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error getting payment statistics: ' . $e->getMessage());
            return [
                'total_payments' => 0,
                'paid_count' => 0,
                'pending_count' => 0,
                'payment_rate' => 0,
                'completion_efficiency' => 0
            ];
        }
    }

    /**
     * Calculate achievements and badges for ambassador
     * 
     * @param int $totalReferrals Total number of referrals
     * @param array $participantStats Participant statistics
     * @param array $conversionMetrics Conversion metrics
     * @return array Achievement data
     */
    private function calculateAchievements($totalReferrals, $participantStats, $conversionMetrics)
    {
        $achievements = [];

        // Referral milestones
        $referralMilestones = [
            ['threshold' => 10, 'title' => 'Getting Started', 'icon' => '🌱'],
            ['threshold' => 50, 'title' => 'Rising Star', 'icon' => '⭐'],
            ['threshold' => 100, 'title' => 'Top Performer', 'icon' => '🏆'],
            ['threshold' => 250, 'title' => 'Super Ambassador', 'icon' => '💎'],
            ['threshold' => 500, 'title' => 'Legend', 'icon' => '👑']
        ];

        foreach ($referralMilestones as $milestone) {
            if ($totalReferrals >= $milestone['threshold']) {
                $achievements[] = [
                    'title' => $milestone['title'],
                    'icon' => $milestone['icon'],
                    'description' => "Reached {$milestone['threshold']} referrals",
                    'achieved' => true,
                    'date_achieved' => date('M Y') // Simplified for now
                ];
            }
        }

        // Quality achievements
        if ($conversionMetrics['quality_score'] >= 80) {
            $achievements[] = [
                'title' => 'Quality Champion',
                'icon' => '🎯',
                'description' => 'High registration completion rate',
                'achieved' => true,
                'date_achieved' => date('M Y')
            ];
        }

        // Recent activity achievements
        if ($conversionMetrics['this_month_count'] >= 20) {
            $achievements[] = [
                'title' => 'Monthly Champion',
                'icon' => '🔥',
                'description' => 'Strong performance this month',
                'achieved' => true,
                'date_achieved' => date('M Y')
            ];
        }

        return $achievements;
    }

    /**
     * Get program status based on dates
     * 
     * @param object $program Program object
     * @return string Program status
     */
    private function getProgramStatus($program)
    {
        if (!$program) {
            return 'unknown';
        }

        $now = new \DateTime();
        
        if ($program->start_date) {
            $startDate = new \DateTime($program->start_date);
            if ($now < $startDate) {
                return 'upcoming';
            }
        }

        if ($program->end_date) {
            $endDate = new \DateTime($program->end_date);
            if ($now > $endDate) {
                return 'completed';
            }
        }

        return 'active';
    }

    /**
     * Get ambassador ranking within program
     * 
     * @param int $ambassadorId Ambassador ID
     * @param int $programId Program ID  
     * @param int $totalReferrals Current ambassador's referral count
     * @return int Ranking position
     */
    private function getAmbassadorRanking($ambassadorId, $programId, $totalReferrals)
    {
        try {
            // Get all ambassadors' referral counts for this program
            $allCounts = $this->model->getComprehensiveReferralCounts($programId);
            
            // Sort by referral count (descending)
            arsort($allCounts);
            
            // Find current ambassador's rank
            $rank = 1;
            foreach ($allCounts as $ambassadorIdKey => $count) {
                if ($ambassadorIdKey == $ambassadorId) {
                    return $rank;
                }
                $rank++;
            }

            return $rank;

        } catch (\Exception $e) {
            log_message('error', 'Error getting ambassador ranking: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Calculate days ago from given date
     * 
     * @param string $date Date string
     * @return int Days ago
     */
    private function calculateDaysAgo($date)
    {
        $now = new \DateTime();
        $createdDate = new \DateTime($date);
        $interval = $now->diff($createdDate);
        return $interval->days;
    }

    /**
     * Get detailed list of referred participants for ambassador dashboard with comprehensive filtering
     * GET /api/ambassador/dashboard/participants
     */
    public function getDashboardParticipants()
    {
        // Authenticate ambassador
        $userData = $this->getAuthenticatedUser();
        if (!$userData || intval($userData->type) !== 3) {
            return $this->respondUnauthorized('Ambassador authentication required');
        }

        try {
            $ambassadorId = $userData->id;
            $programId = $userData->program_id;

            // Get comprehensive filter parameters
            $filters = $this->getDashboardParticipantFilters();
            
            // Get pagination parameters
            $page = max(1, $filters['page']);
            $limit = min($filters['per_page'], 100); // Max 100 per page
            $offset = ($page - 1) * $limit;

            // Get participant IDs referred by this ambassador
            $participantIds = $this->model->getComprehensiveReferralParticipantIds($ambassadorId);
            $totalParticipants = count($participantIds);

            $participants = [];
            $totalFiltered = $totalParticipants;

            // Only try to get participant details if we have IDs
            if (!empty($participantIds)) {
                try {
                    // Build query with enhanced filtering
                    $queryResult = $this->buildFilteredParticipantsQuery($participantIds, $filters);
                    $allParticipants = $queryResult['participants'];
                    $totalFiltered = count($allParticipants);

                    // Apply pagination
                    $paginatedParticipants = array_slice($allParticipants, $offset, $limit);

                    // Format participant data
                    $participants = $this->formatParticipantData($paginatedParticipants);

                } catch (\Exception $dbError) {
                    log_message('error', 'Database error getting participant details: ' . $dbError->getMessage());
                }
            }

            $response = [
                'participants' => $participants,
                'total_participants' => $totalParticipants,
                'filtered_count' => $totalFiltered,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $totalFiltered,
                    'last_page' => $totalFiltered > 0 ? ceil($totalFiltered / $limit) : 1,
                    'from' => $totalFiltered > 0 ? $offset + 1 : 0,
                    'to' => $totalFiltered > 0 ? min($offset + $limit, $totalFiltered) : 0
                ],
                'filters_applied' => array_filter($filters),
                'available_filters' => [
                    'form_status' => ['not_started', 'in_progress', 'submitted'],
                    'category' => ['fully_funded', 'self_funded'],
                    'sort_by' => ['created_at', 'full_name', 'email', 'nationality', 'institution', 'form_status'],
                    'sort_order' => ['ASC', 'DESC']
                ],
                'last_updated' => date('Y-m-d H:i:s')
            ];

            return $this->respondSuccess($response, self::HTTP_OK, 'Participants retrieved successfully');

        } catch (\Exception $e) {
            log_message('error', 'Ambassador participants error: ' . $e->getMessage());
            log_message('error', 'Failed to retrieve participants data: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve participants data: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get payment analytics and trends for ambassador dashboard
     * GET /api/ambassador/dashboard/payments
     */
    public function getDashboardPayments()
    {
        // Authenticate ambassador
        $userData = $this->getAuthenticatedUser();
        if (!$userData || intval($userData->type) !== 3) {
            return $this->respond(['status' => 'error', 'message' => 'Ambassador authentication required'], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        try {
            $ambassadorId = $userData->id;
            $programId = $userData->program_id;

            // Get participant IDs referred by this ambassador (cached for performance)
            $participantIds = $this->model->getComprehensiveReferralParticipantIds($ambassadorId);

            if (empty($participantIds)) {
                return $this->respondSuccess($this->getEmptyPaymentAnalytics(), self::HTTP_OK, 'Payment analytics retrieved successfully');
            }

            // Get all payment and participant data in optimized queries
            $paymentData = $this->getOptimizedPaymentData($participantIds);
            $participantData = $this->getOptimizedParticipantData($participantIds);

            // Build comprehensive analytics
            $analytics = $this->buildComprehensivePaymentAnalytics($paymentData, $participantData, $participantIds);

            return $this->respondSuccess($analytics, self::HTTP_OK, 'Payment analytics retrieved successfully');

        } catch (\Exception $e) {
            log_message('error', 'Ambassador payment analytics error: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve payment analytics', self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get empty payment analytics structure for when no participants exist
     * 
     * @return array Empty analytics structure
     */
    private function getEmptyPaymentAnalytics()
    {
        return [
            'summary' => [
                'total_participants' => 0,
                'paid_participants' => 0,
                'pending_participants' => 0,
                'created_participants' => 0,
                'cancelled_participants' => 0,
                'rejected_participants' => 0,
                'unpaid_participants' => 0,
                'payment_completion_rate' => 0,
                'average_payment_time_days' => 0,
                'total_payments_processed' => 0,
                'conversion_efficiency' => 0
            ],
            'detailed_breakdown' => [
                'by_status' => [
                    'completed' => ['count' => 0, 'percentage' => 0],
                    'pending' => ['count' => 0, 'percentage' => 0],
                    'created' => ['count' => 0, 'percentage' => 0],
                    'cancelled' => ['count' => 0, 'percentage' => 0],
                    'rejected' => ['count' => 0, 'percentage' => 0],
                    'not_started' => ['count' => 0, 'percentage' => 0]
                ],
                'by_payment_method' => [],
                'by_program' => [],
                'by_nationality' => [],
                'by_month' => []
            ],
            'trends' => [
                'payment_timeline' => [],
                'conversion_funnel' => [
                    'referred' => 0,
                    'registered' => 0,
                    'form_completed' => 0,
                    'payment_initiated' => 0,
                    'payment_completed' => 0
                ],
                'performance_metrics' => [
                    'conversion_rate' => 0,
                    'payment_success_rate' => 0,
                    'average_days_to_payment' => 0,
                    'monthly_growth_rate' => 0
                ]
            ],
            'recent_activity' => [],
            'insights' => [
                'top_converting_countries' => [],
                'best_performing_months' => [],
                'payment_patterns' => []
            ]
        ];
    }

    /**
     * Get optimized payment data with minimal queries (analytics focused, no monetary data)
     * 
     * @param array $participantIds List of participant IDs
     * @return array Payment data
     */
    private function getOptimizedPaymentData($participantIds)
    {
        $paymentModel = new \App\Models\PaymentModel();
        
        // Single optimized query to get payment analytics data (no monetary values)
        $payments = $paymentModel->select('
            payments.id,
            payments.participant_id,
            payments.status,
            payments.created_at,
            payments.updated_at,
            payment_methods.name as payment_method_name,
            payment_methods.type as payment_method_type,
            participants.full_name,
            participants.nationality,
            participants.program_id,
            participants.created_at as registration_date,
            programs.name as program_name
        ')
        ->join('payment_methods', 'payment_methods.id = payments.payment_method_id', 'left')
        ->join('participants', 'participants.id = payments.participant_id', 'inner')
        ->join('programs', 'programs.id = participants.program_id', 'left')
        ->whereIn('payments.participant_id', $participantIds)
        ->where('payments.is_deleted', 0)
        ->orderBy('payments.created_at', 'DESC')
        ->findAll();

        return $payments;
    }

    /**
     * Get optimized participant data for analytics
     * 
     * @param array $participantIds List of participant IDs
     * @return array Participant data
     */
    private function getOptimizedParticipantData($participantIds)
    {
        $participantModel = new \App\Models\ParticipantModel();
        
        // Get participant data with status information
        $participants = $participantModel->select('
            participants.id,
            participants.full_name,
            participants.nationality,
            participants.program_id,
            participants.category,
            participants.created_at,
            participant_statuses.form_status,
            programs.name as program_name
        ')
        ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
        ->join('programs', 'programs.id = participants.program_id', 'left')
        ->whereIn('participants.id', $participantIds)
        ->where('participants.is_deleted', 0)
        ->findAll();

        return $participants;
    }

    /**
     * Build comprehensive payment analytics from optimized data
     * 
     * @param array $paymentData Payment data from database
     * @param array $participantData Participant data from database
     * @param array $participantIds All participant IDs
     * @return array Comprehensive analytics
     */
    private function buildComprehensivePaymentAnalytics($paymentData, $participantData, $participantIds)
    {
        // Initialize counters and arrays
        $paidParticipants = [];
        $pendingParticipants = [];
        $createdParticipants = [];
        $cancelledParticipants = [];
        $rejectedParticipants = [];
        $paymentMethods = [];
        $programBreakdown = [];
        $nationalityBreakdown = [];
        $monthlyData = [];
        $recentActivity = [];
        $paymentTimeline = [];
        $conversionFunnel = ['referred' => 0, 'registered' => 0, 'form_completed' => 0, 'payment_initiated' => 0, 'payment_completed' => 0];
        $paymentTimes = [];
        
        // Process payment data
        foreach ($paymentData as $payment) {
            $participantId = $payment->participant_id;
            $status = $payment->status;
            $month = date('Y-m', strtotime($payment->created_at));
            
            // Track participants by payment status
            switch ($status) {
                case 0: // Created
                    $createdParticipants[$participantId] = true;
                    break;
                case 1: // Pending
                    $pendingParticipants[$participantId] = true;
                    break;
                case 2: // Completed
                    $paidParticipants[$participantId] = true;
                    
                    // Calculate payment time (registration to payment)
                    if ($payment->registration_date) {
                        $registrationTime = strtotime($payment->registration_date);
                        $paymentTime = strtotime($payment->created_at);
                        $daysDiff = ($paymentTime - $registrationTime) / (24 * 60 * 60);
                        if ($daysDiff >= 0) {
                            $paymentTimes[] = $daysDiff;
                        }
                    }
                    break;
                case 3: // Cancelled
                    $cancelledParticipants[$participantId] = true;
                    break;
                case 4: // Rejected
                    $rejectedParticipants[$participantId] = true;
                    break;
            }
            
            // Payment method breakdown (count only)
            $methodName = $payment->payment_method_name ?? 'Unknown';
            if (!isset($paymentMethods[$methodName])) {
                $paymentMethods[$methodName] = [
                    'count' => 0,
                    'created' => 0,
                    'pending' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                    'rejected' => 0
                ];
            }
            $paymentMethods[$methodName]['count']++;
            
            switch ($status) {
                case 0:
                    $paymentMethods[$methodName]['created']++;
                    break;
                case 1:
                    $paymentMethods[$methodName]['pending']++;
                    break;
                case 2:
                    $paymentMethods[$methodName]['completed']++;
                    break;
                case 3:
                    $paymentMethods[$methodName]['cancelled']++;
                    break;
                case 4:
                    $paymentMethods[$methodName]['rejected']++;
                    break;
            }
            
            // Program breakdown (count only)
            $programName = $payment->program_name ?? 'Unknown Program';
            if (!isset($programBreakdown[$programName])) {
                $programBreakdown[$programName] = [
                    'total' => 0,
                    'created' => 0,
                    'pending' => 0,
                    'paid' => 0,
                    'cancelled' => 0,
                    'rejected' => 0
                ];
            }
            $programBreakdown[$programName]['total']++;
            
            switch ($status) {
                case 0:
                    $programBreakdown[$programName]['created']++;
                    break;
                case 1:
                    $programBreakdown[$programName]['pending']++;
                    break;
                case 2:
                    $programBreakdown[$programName]['paid']++;
                    break;
                case 3:
                    $programBreakdown[$programName]['cancelled']++;
                    break;
                case 4:
                    $programBreakdown[$programName]['rejected']++;
                    break;
            }
            
            // Nationality breakdown
            $nationality = $payment->nationality ?? 'Unknown';
            if (!isset($nationalityBreakdown[$nationality])) {
                $nationalityBreakdown[$nationality] = ['total' => 0, 'paid' => 0, 'completion_rate' => 0];
            }
            $nationalityBreakdown[$nationality]['total']++;
            if ($status == 2) {
                $nationalityBreakdown[$nationality]['paid']++;
            }
            
            // Monthly data (count only)
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = [
                    'month' => $month,
                    'payments' => 0,
                    'unique_participants' => []
                ];
            }
            $monthlyData[$month]['payments']++;
            $monthlyData[$month]['unique_participants'][$participantId] = true;
            
            // Recent activity (limited to 20 most recent, no monetary data)
            if (count($recentActivity) < 20) {
                $recentActivity[] = [
                    'participant_name' => $payment->full_name,
                    'nationality' => $payment->nationality,
                    'program' => $payment->program_name,
                    'status' => $this->getPaymentStatusText($status),
                    'payment_method' => $payment->payment_method_name,
                    'payment_date' => date('M j, Y H:i', strtotime($payment->created_at)),
                    'days_ago' => $this->calculateDaysAgo($payment->created_at)
                ];
            }
        }
        
        // Process participant data for conversion funnel
        $formCompleted = 0;
        $registered = count($participantData);
        
        foreach ($participantData as $participant) {
            if ($participant->form_status == 2) { // Completed
                $formCompleted++;
            }
        }
        
        // Calculate conversion funnel
        $conversionFunnel = [
            'referred' => count($participantIds),
            'registered' => $registered,
            'form_completed' => $formCompleted,
            'payment_initiated' => count($paymentData) > 0 ? count(array_unique(array_column($paymentData, 'participant_id'))) : 0,
            'payment_completed' => count($paidParticipants)
        ];
        
        // Calculate summary statistics
        $totalParticipants = count($participantIds);
        $paidCount = count($paidParticipants);
        $pendingCount = count($pendingParticipants);
        $createdCount = count($createdParticipants);
        $cancelledCount = count($cancelledParticipants);
        $rejectedCount = count($rejectedParticipants);
        $unpaidCount = $totalParticipants - $paidCount - $pendingCount - $createdCount - $cancelledCount - $rejectedCount;
        $paymentRate = $totalParticipants > 0 ? round(($paidCount / $totalParticipants) * 100, 1) : 0;
        $averagePaymentTime = !empty($paymentTimes) ? round(array_sum($paymentTimes) / count($paymentTimes), 1) : 0;
        
        // Calculate success rates for payment methods
        foreach ($paymentMethods as $method => &$data) {
            $data['success_rate'] = $data['count'] > 0 ? round(($data['completed'] / $data['count']) * 100, 1) : 0;
        }
        
        // Calculate completion rates for nationalities
        foreach ($nationalityBreakdown as $country => &$data) {
            $data['completion_rate'] = $data['total'] > 0 ? round(($data['paid'] / $data['total']) * 100, 1) : 0;
        }
        
        // Sort and limit data for performance
        arsort($nationalityBreakdown);
        $topCountries = array_slice($nationalityBreakdown, 0, 10, true);
        
        arsort($paymentMethods);
        $topPaymentMethods = array_slice($paymentMethods, 0, 5, true);
        
        // Sort monthly data
        ksort($monthlyData);
        foreach ($monthlyData as &$month) {
            $month['unique_participants'] = count($month['unique_participants']);
        }
        $paymentTimeline = array_values($monthlyData);
        
        // Calculate trends and insights
        $monthlyGrowthRate = $this->calculateMonthlyGrowthRate($paymentTimeline);
        $conversionRate = $conversionFunnel['referred'] > 0 ? round(($conversionFunnel['payment_completed'] / $conversionFunnel['referred']) * 100, 1) : 0;
        $paymentSuccessRate = $conversionFunnel['payment_initiated'] > 0 ? round(($conversionFunnel['payment_completed'] / $conversionFunnel['payment_initiated']) * 100, 1) : 0;
        
        // Build final analytics structure (no monetary data)
        return [
            'summary' => [
                'total_participants' => $totalParticipants,
                'paid_participants' => $paidCount,
                'pending_participants' => $pendingCount,
                'created_participants' => $createdCount,
                'cancelled_participants' => $cancelledCount,
                'rejected_participants' => $rejectedCount,
                'unpaid_participants' => $unpaidCount,
                'payment_completion_rate' => $paymentRate,
                'average_payment_time_days' => $averagePaymentTime,
                'total_payments_processed' => count($paymentData),
                'conversion_efficiency' => $conversionRate
            ],
            'detailed_breakdown' => [
                'by_status' => [
                    'completed' => ['count' => $paidCount, 'percentage' => round(($paidCount / max(1, $totalParticipants)) * 100, 1)],
                    'pending' => ['count' => $pendingCount, 'percentage' => round(($pendingCount / max(1, $totalParticipants)) * 100, 1)],
                    'created' => ['count' => $createdCount, 'percentage' => round(($createdCount / max(1, $totalParticipants)) * 100, 1)],
                    'cancelled' => ['count' => $cancelledCount, 'percentage' => round(($cancelledCount / max(1, $totalParticipants)) * 100, 1)],
                    'rejected' => ['count' => $rejectedCount, 'percentage' => round(($rejectedCount / max(1, $totalParticipants)) * 100, 1)],
                    'not_started' => ['count' => $unpaidCount, 'percentage' => round(($unpaidCount / max(1, $totalParticipants)) * 100, 1)]
                ],
                'by_payment_method' => $topPaymentMethods,
                'by_program' => array_slice($programBreakdown, 0, 5, true),
                'by_nationality' => $topCountries,
                'by_month' => array_slice($paymentTimeline, -6) // Last 6 months
            ],
            'trends' => [
                'payment_timeline' => $paymentTimeline,
                'conversion_funnel' => $conversionFunnel,
                'performance_metrics' => [
                    'conversion_rate' => $conversionRate,
                    'payment_success_rate' => $paymentSuccessRate,
                    'average_days_to_payment' => $averagePaymentTime,
                    'monthly_growth_rate' => $monthlyGrowthRate
                ]
            ],
            'recent_activity' => $recentActivity,
            'insights' => [
                'top_converting_countries' => array_slice($topCountries, 0, 5, true),
                'best_performing_months' => $this->getBestPerformingMonths($paymentTimeline),
                'payment_patterns' => $this->analyzePaymentPatterns($paymentData)
            ],
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Calculate monthly growth rate from payment timeline
     * 
     * @param array $timeline Payment timeline data
     * @return float Monthly growth rate percentage
     */
    private function calculateMonthlyGrowthRate($timeline)
    {
        if (count($timeline) < 2) {
            return 0;
        }
        
        $recent = array_slice($timeline, -2);
        $previousMonth = $recent[0]['payments'] ?? 0;
        $currentMonth = $recent[1]['payments'] ?? 0;
        
        if ($previousMonth == 0) {
            return $currentMonth > 0 ? 100 : 0;
        }
        
        return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 1);
    }

    /**
     * Get best performing months from timeline (analytics only, no revenue)
     * 
     * @param array $timeline Payment timeline data
     * @return array Best performing months
     */
    private function getBestPerformingMonths($timeline)
    {
        if (empty($timeline)) {
            return [];
        }
        
        // Sort by payment count and unique participants
        $sorted = $timeline;
        usort($sorted, function($a, $b) {
            return ($b['payments'] + $b['unique_participants']) - ($a['payments'] + $a['unique_participants']);
        });
        
        return array_slice($sorted, 0, 3);
    }

    /**
     * Analyze payment patterns for insights
     * 
     * @param array $paymentData Payment data
     * @return array Payment pattern insights
     */
    private function analyzePaymentPatterns($paymentData)
    {
        if (empty($paymentData)) {
            return [];
        }
        
        $patterns = [];
        $hourCounts = array_fill(0, 24, 0);
        $dayOfWeekCounts = array_fill(0, 7, 0);
        
        foreach ($paymentData as $payment) {
            if ($payment->status == 2) { // Only successful payments
                $hour = intval(date('H', strtotime($payment->created_at)));
                $dayOfWeek = intval(date('w', strtotime($payment->created_at)));
                
                $hourCounts[$hour]++;
                $dayOfWeekCounts[$dayOfWeek]++;
            }
        }
        
        // Find peak hour and day
        $peakHour = array_search(max($hourCounts), $hourCounts);
        $peakDay = array_search(max($dayOfWeekCounts), $dayOfWeekCounts);
        
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        $patterns[] = [
            'type' => 'peak_payment_hour',
            'value' => $peakHour . ':00',
            'count' => $hourCounts[$peakHour],
            'insight' => "Most payments occur at {$peakHour}:00"
        ];
        
        $patterns[] = [
            'type' => 'peak_payment_day',
            'value' => $dayNames[$peakDay],
            'count' => $dayOfWeekCounts[$peakDay],
            'insight' => "Most payments occur on {$dayNames[$peakDay]}"
        ];
        
        return $patterns;
    }

    /**
     * Get ambassador profile information with shareable link
     * GET /api/ambassador/dashboard/profile
     */
    public function getDashboardProfile()
    {
        // Authenticate ambassador
        $userData = $this->getAuthenticatedUser();
        if (!$userData || intval($userData->type) !== 3) {
            return $this->respondUnauthorized('Ambassador authentication required');
        }

        try {
            $ambassadorId = $userData->id;
            $programId = $userData->program_id;

            // Get ambassador information
            $ambassador = $this->model->find($ambassadorId);
            if (!$ambassador) {
                return $this->failNotFound('Ambassador not found');
            }

            // Get program information
            $program = $this->programModel->find($programId);
            if (!$program) {
                return $this->failNotFound('Program not found');
            }

            // Get program category for generating the shareable link
            $programCategory = $this->programCategoryModel->find($program->program_category_id);

            // Generate shareable referral link
            $shareableLink = null;
            $linkGenerationError = null;
            
            try {
                if ($programCategory && !empty($programCategory->web_url)) {
                    $refCode = $ambassador->ref_code;
                    $webUrl = rtrim($programCategory->web_url, '/');
                    
                    // Add https:// if not present
                    if (!preg_match('/^https?:\/\//', $webUrl)) {
                        $webUrl = 'https://' . $webUrl;
                    }

                    // Encrypt the ref code
                    $encryptedQuery = url_encrypt($refCode);
                    
                    if ($encryptedQuery !== false) {
                        $shareableLink = $webUrl . '/sign-up?q=' . urlencode($encryptedQuery);
                    } else {
                        $linkGenerationError = 'Failed to encrypt referral code';
                    }
                } else {
                    $linkGenerationError = 'Program web URL not configured';
                }
            } catch (\Exception $linkError) {
                $linkGenerationError = 'Error generating shareable link: ' . $linkError->getMessage();
                log_message('error', 'Failed to generate shareable link for ambassador profile ' . $ambassadorId . ': ' . $linkError->getMessage());
            }

            // Get program status
            $programStatus = $this->getProgramStatus($program);

            // Build profile response
            $profileData = [
                'ambassador' => [
                    'id' => $ambassador->id,
                    'name' => $ambassador->name,
                    'email' => $ambassador->email,
                    'institution' => $ambassador->institution,
                    'phone_number' => $ambassador->phone_number,
                    'ref_code' => $ambassador->ref_code,
                    'is_active' => (bool)$ambassador->is_active,
                    'created_at' => $ambassador->created_at,
                    'notes' => $ambassador->notes ?? null,
                    'gender' => $ambassador->gender ?? null
                ],
                'program' => [
                    'id' => $program->id,
                    'name' => $program->name,
                    'description' => $program->description,
                    'start_date' => $program->start_date,
                    'end_date' => $program->end_date,
                    'status' => $programStatus,
                    'category' => [
                        'id' => $programCategory->id ?? null,
                        'name' => $programCategory->name ?? null,
                        'web_url' => $programCategory->web_url ?? null
                    ]
                ],
                'referral_link' => [
                    'url' => $shareableLink,
                    'ref_code' => $ambassador->ref_code,
                    'is_available' => !is_null($shareableLink),
                    'error_message' => $linkGenerationError
                ],
                'last_updated' => date('Y-m-d H:i:s')
            ];

            return $this->respondSuccess($profileData, self::HTTP_OK, 'Ambassador profile retrieved successfully');

        } catch (\Exception $e) {
            log_message('error', 'Ambassador profile error: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve ambassador profile: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get referral performance over time for ambassador dashboard
     * GET /api/ambassador/dashboard/performance
     */
    public function getDashboardPerformance()
    {
        // Authenticate ambassador
        $userData = $this->getAuthenticatedUser();
        if (!$userData || intval($userData->type) !== 3) {
            return $this->respond(['status' => 'error', 'message' => 'Ambassador authentication required'], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        try {
            $ambassadorId = $userData->id;
            $programId = $userData->program_id;

            // Get participant IDs referred by this ambassador
            $participantIds = $this->model->getComprehensiveReferralParticipantIds($ambassadorId);

            if (empty($participantIds)) {
                $performance = [
                    'referral_timeline' => [],
                    'conversion_metrics' => [
                        'total_referrals' => 0,
                        'completed_registrations' => 0,
                        'payment_completions' => 0,
                        'registration_rate' => 0,
                        'payment_rate' => 0
                    ],
                    'monthly_performance' => [],
                    'top_performing_months' => [],
                    'achievement_progress' => []
                ];

                return $this->respond(['status' => 'success', 'message' => 'Performance data retrieved successfully', 'data' => $performance], ResponseInterface::HTTP_OK);
            }

            // Get detailed participant and payment information
            $participantModel = new \App\Models\ParticipantModel();
            $paymentModel = new \App\Models\PaymentModel();

            // Get participants with their registration details
            $participants = $participantModel->select('
                participants.id,
                participants.full_name,
                participants.created_at,
                participant_statuses.form_status
            ')
            ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
            ->whereIn('participants.id', $participantIds)
            ->where('participants.is_deleted', 0)
            ->findAll();

            // Get payment completions
            $paymentCompletions = $paymentModel->select('participant_id, created_at')
                ->whereIn('participant_id', $participantIds)
                ->where('status', 2) // Successful payments
                ->where('is_deleted', 0)
                ->findAll();

            // Build timeline data
            $timeline = [];
            $monthlyStats = [];
            $completedRegistrations = 0;
            $paymentCount = count($paymentCompletions);

            // Process participants for timeline
            foreach ($participants as $participant) {
                $month = date('Y-m', strtotime($participant->created_at));
                $monthLabel = date('M Y', strtotime($participant->created_at));

                if (!isset($timeline[$month])) {
                    $timeline[$month] = [
                        'month' => $monthLabel,
                        'referrals' => 0,
                        'registrations' => 0
                    ];
                }

                $timeline[$month]['referrals']++;
                
                if ($participant->form_status == 2) { // 2 = submitted
                    $timeline[$month]['registrations']++;
                    $completedRegistrations++;
                }
            }

            // Sort timeline
            ksort($timeline);
            $timelineData = array_values($timeline);

            // Calculate monthly performance for the last 6 months
            $last6Months = [];
            $now = new \DateTime();
            for ($i = 5; $i >= 0; $i--) {
                $date = clone $now;
                $date->sub(new \DateInterval('P' . $i . 'M'));
                $monthKey = $date->format('Y-m');
                $monthLabel = $date->format('M Y');
                
                $monthData = [
                    'month' => $monthLabel,
                    'referrals' => 0,
                    'registrations' => 0,
                    'conversion_rate' => 0
                ];
                
                if (isset($timeline[$monthKey])) {
                    $monthData = $timeline[$monthKey];
                    $monthData['conversion_rate'] = $monthData['referrals'] > 0 ? 
                        round(($monthData['registrations'] / $monthData['referrals']) * 100, 1) : 0;
                }
                
                $last6Months[] = $monthData;
            }

            // Find top performing months (highest referral counts)
            $topMonths = $timeline;
            uasort($topMonths, function($a, $b) {
                return $b['referrals'] - $a['referrals'];
            });
            $topPerformingMonths = array_slice(array_values($topMonths), 0, 3);

            // Calculate conversion metrics
            $totalReferrals = count($participantIds);
            $registrationRate = $totalReferrals > 0 ? round(($completedRegistrations / $totalReferrals) * 100, 1) : 0;
            $paymentRate = $totalReferrals > 0 ? round(($paymentCount / $totalReferrals) * 100, 1) : 0;

            // Achievement progress (based on milestones)
            $achievementMilestones = [
                ['threshold' => 10, 'title' => 'Getting Started', 'completed' => $totalReferrals >= 10],
                ['threshold' => 50, 'title' => 'Rising Star', 'completed' => $totalReferrals >= 50],
                ['threshold' => 100, 'title' => 'Top Performer', 'completed' => $totalReferrals >= 100],
                ['threshold' => 250, 'title' => 'Super Ambassador', 'completed' => $totalReferrals >= 250],
                ['threshold' => 500, 'title' => 'Legend', 'completed' => $totalReferrals >= 500]
            ];

            $achievementProgress = [];
            foreach ($achievementMilestones as $milestone) {
                $progress = min(100, round(($totalReferrals / $milestone['threshold']) * 100, 1));
                $achievementProgress[] = [
                    'title' => $milestone['title'],
                    'threshold' => $milestone['threshold'],
                    'current' => $totalReferrals,
                    'progress' => $progress,
                    'completed' => $milestone['completed']
                ];
            }

            $performance = [
                'referral_timeline' => $timelineData,
                'conversion_metrics' => [
                    'total_referrals' => $totalReferrals,
                    'completed_registrations' => $completedRegistrations,
                    'payment_completions' => $paymentCount,
                    'registration_rate' => $registrationRate,
                    'payment_rate' => $paymentRate
                ],
                'monthly_performance' => $last6Months,
                'top_performing_months' => $topPerformingMonths,
                'achievement_progress' => $achievementProgress
            ];

            return $this->respond(['status' => 'success', 'message' => 'Performance data retrieved successfully', 'data' => $performance], ResponseInterface::HTTP_OK);

        } catch (\Exception $e) {
            log_message('error', 'Ambassador performance error: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Failed to retrieve performance data'], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get participant payment summary and status for ambassador dashboard
     * GET /api/ambassador/dashboard/participant-payment/{participantId} (single participant)
     * GET /api/ambassador/dashboard/participants-payment (filtered list with pagination)
     */
    public function getParticipantPaymentSummary($participantId = null)
    {
        // Authenticate ambassador
        $userData = $this->getAuthenticatedUser();
        if (!$userData || intval($userData->type) !== 3) {
            return $this->respondUnauthorized('Ambassador authentication required');
        }

        try {
            $ambassadorId = $userData->id;
            $programId = $userData->program_id;

            // Get participant IDs referred by this ambassador
            $participantIds = $this->model->getComprehensiveReferralParticipantIds($ambassadorId);
            
            if (empty($participantIds)) {
                return $this->respondSuccess([
                    'participants' => [],
                    'total' => 0,
                    'message' => 'No participants found'
                ]);
            }

            // If specific participant ID is provided, return single participant details
            if ($participantId) {
                return $this->getSingleParticipantPaymentSummary($participantId, $participantIds);
            }

            // Otherwise, return filtered list of participants with payment summaries
            return $this->getFilteredParticipantsPaymentSummaries($participantIds);

        } catch (\Exception $e) {
            log_message('error', 'Ambassador participants payment summary error: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve participants payment summary: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get filtered list of participants with payment summaries
     * 
     * @param array $participantIds List of allowed participant IDs
     * @return object Response
     */
    private function getFilteredParticipantsPaymentSummaries($participantIds)
    {
        // Get filter parameters
        $filters = [
            'payment_status' => $this->request->getVar('payment_status'), // 'completed', 'pending', 'failed', 'not_started'
            'form_status' => $this->request->getVar('form_status'), // 'submitted', 'in_progress', 'not_started'
            'payment_method' => $this->request->getVar('payment_method'), // specific payment method
            'date_from' => $this->request->getVar('date_from'), // registration date filter
            'date_to' => $this->request->getVar('date_to'),
            'search' => trim($this->request->getVar('search') ?? ''), // search by name or email
            'category' => $this->request->getVar('category'), // 'fully_funded', 'self_funded'
            'nationality' => $this->request->getVar('nationality'), // specific country
            'sort_by' => $this->request->getVar('sort_by') ?? 'created_at',
            'sort_order' => strtoupper($this->request->getVar('sort_order') ?? 'DESC')
        ];
        
        // Get pagination parameters
        $page = (int)($this->request->getVar('page') ?? 1);
        $limit = min((int)($this->request->getVar('limit') ?? 20), 100);
        $offset = ($page - 1) * $limit;

        // Get participants with basic filters
        $participantModel = new \App\Models\ParticipantModel();
        $query = $participantModel->select('
            participants.id,
            participants.full_name,
            users.email,
            participants.nationality,
            participants.category,
            participants.created_at,
            participants.program_id,
            participant_statuses.form_status,
            programs.name as program_name
        ')
        ->join('users', 'users.id = participants.user_id', 'left')
        ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
        ->join('programs', 'programs.id = participants.program_id', 'left')
        ->whereIn('participants.id', $participantIds)
        ->where('participants.is_deleted', 0);

        // Apply participant-based filters
        $this->applyParticipantFilters($query, $filters);

        // Get total count before pagination
        $totalQuery = clone $query;
        $totalFilteredCount = $totalQuery->countAllResults();

        // Apply sorting and pagination
        $validSortFields = ['created_at', 'full_name', 'nationality', 'category'];
        $sortBy = in_array($filters['sort_by'], $validSortFields) ? $filters['sort_by'] : 'created_at';
        $sortOrder = in_array($filters['sort_order'], ['ASC', 'DESC']) ? $filters['sort_order'] : 'DESC';
        
        $participants = $query
            ->orderBy('participants.' . $sortBy, $sortOrder)
            ->limit($limit, $offset)
            ->findAll();

        // Get payment data for each participant and apply payment-based filters
        $participantsWithPayments = [];
        foreach ($participants as $participant) {
            $paymentData = $this->getParticipantPaymentData($participant->id);
            
            // Apply payment-based filters
            if ($this->shouldIncludeParticipantByPaymentFilters($paymentData, $filters)) {
                $participantsWithPayments[] = [
                    'participant' => [
                        'id' => $participant->id,
                        'full_name' => $participant->full_name,
                        'email' => $participant->email,
                        'nationality' => $participant->nationality,
                        'category' => $participant->category,
                        'registration_date' => $participant->created_at,
                        'form_status' => $this->getFormStatusText($participant->form_status),
                        'form_status_code' => $participant->form_status,
                        'program_name' => $participant->program_name
                    ],
                    'payment_summary' => $paymentData['summary'],
                    'latest_payment' => $paymentData['latest_payment']
                ];
            }
        }

        $response = [
            'participants' => $participantsWithPayments,
            'total' => count($participantIds),
            'filtered_count' => $totalFilteredCount,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalFilteredCount / $limit),
                'per_page' => $limit,
                'total_items' => $totalFilteredCount
            ],
            'filters_applied' => array_filter($filters), // Remove empty filters
            'available_filters' => [
                'payment_status' => ['completed', 'pending', 'failed', 'not_started'],
                'form_status' => ['submitted', 'in_progress', 'not_started'],
                'category' => ['fully_funded', 'self_funded'],
                'sort_by' => ['created_at', 'full_name', 'nationality', 'category'],
                'sort_order' => ['ASC', 'DESC']
            ],
            'last_updated' => date('Y-m-d H:i:s')
        ];

        return $this->respondSuccess($response, self::HTTP_OK, 'Participants payment data retrieved successfully');
    }

    /**
     * Apply participant-based filters to query
     * 
     * @param object $query Query builder instance
     * @param array $filters Filter parameters
     */
    private function applyParticipantFilters($query, $filters)
    {
        // Search filter (name or email)
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->groupStart()
                ->like('participants.full_name', $searchTerm)
                ->orLike('users.email', $searchTerm)
                ->groupEnd();
        }

        // Form status filter
        if (!empty($filters['form_status'])) {
            $formStatusMap = [
                'not_started' => 0,
                'in_progress' => 1,
                'submitted' => 2
            ];
            if (isset($formStatusMap[$filters['form_status']])) {
                $query->where('participant_statuses.form_status', $formStatusMap[$filters['form_status']]);
            }
        }

        // Category filter
        if (!empty($filters['category'])) {
            $query->where('participants.category', $filters['category']);
        }

        // Nationality filter
        if (!empty($filters['nationality'])) {
            $query->where('participants.nationality', $filters['nationality']);
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->where('participants.created_at >=', $filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $query->where('participants.created_at <=', $filters['date_to'] . ' 23:59:59');
        }
    }

    /**
     * Get payment data for a specific participant
     * 
     * @param int $participantId Participant ID
     * @return array Payment data
     */
    private function getParticipantPaymentData($participantId)
    {
        $paymentModel = new \App\Models\PaymentModel();
        $payments = $paymentModel->select('
            payments.id,
            payments.status,
            payments.created_at,
            payments.payment_method_id,
            payments.notes,
            payment_methods.name as payment_method_name
        ')
        ->join('payment_methods', 'payment_methods.id = payments.payment_method_id', 'left')
        ->where('payments.participant_id', $participantId)
        ->where('payments.is_deleted', 0)
        ->orderBy('payments.created_at', 'DESC')
        ->findAll();

        $summary = [
            'total_payments' => count($payments),
            'completed_payments' => 0,
            'pending_payments' => 0,
            'failed_payments' => 0,
            'payment_completion_status' => 'not_started'
        ];

        $latestPayment = null;

        foreach ($payments as $payment) {
            switch ($payment->status) {
                case 2: // Successful
                    $summary['completed_payments']++;
                    break;
                case 1: // Pending
                    $summary['pending_payments']++;
                    break;
                case 0: // Failed
                case 3: // Cancelled
                    $summary['failed_payments']++;
                    break;
            }
        }

        // Determine overall payment completion status
        if ($summary['completed_payments'] > 0) {
            $summary['payment_completion_status'] = 'completed';
        } elseif ($summary['pending_payments'] > 0) {
            $summary['payment_completion_status'] = 'pending';
        } elseif ($summary['failed_payments'] > 0) {
            $summary['payment_completion_status'] = 'failed';
        }

        // Get latest payment info
        if (!empty($payments)) {
            $latestPayment = [
                'status' => $this->getPaymentStatusText($payments[0]->status),
                'payment_method' => $payments[0]->payment_method_name ?? 'Not specified',
                'created_at' => $payments[0]->created_at
            ];
        }

        return [
            'summary' => $summary,
            'latest_payment' => $latestPayment
        ];
    }

    /**
     * Check if participant should be included based on payment filters
     * 
     * @param array $paymentData Payment data for participant
     * @param array $filters Filter parameters
     * @return bool Whether to include participant
     */
    private function shouldIncludeParticipantByPaymentFilters($paymentData, $filters)
    {
        // Payment status filter
        if (!empty($filters['payment_status'])) {
            if ($paymentData['summary']['payment_completion_status'] !== $filters['payment_status']) {
                return false;
            }
        }

        // Payment method filter
        if (!empty($filters['payment_method']) && $paymentData['latest_payment']) {
            if (stripos($paymentData['latest_payment']['payment_method'], $filters['payment_method']) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get single participant payment summary (original functionality)
     * 
     * @param int $participantId Participant ID
     * @param array $allowedParticipantIds List of allowed participant IDs
     * @return object Response
     */
    private function getSingleParticipantPaymentSummary($participantId, $allowedParticipantIds)
    {
        // Validate participant ID
        if (empty($participantId) || !is_numeric($participantId)) {
            return $this->failValidationErrors('Valid participant ID is required');
        }

        // Verify that this participant was referred by the authenticated ambassador
        if (!in_array($participantId, $allowedParticipantIds)) {
            return $this->failNotFound('Participant not found in your referrals');
        }

        // Get participant basic information
        $participantModel = new \App\Models\ParticipantModel();
        $participant = $participantModel->select('
            participants.id,
            participants.full_name,
            users.email,
            participants.nationality,
            participants.category,
            participants.created_at,
            participants.program_id,
            participant_statuses.form_status,
            programs.name as program_name
        ')
        ->join('users', 'users.id = participants.user_id', 'left')
        ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
        ->join('programs', 'programs.id = participants.program_id', 'left')
        ->where('participants.id', $participantId)
        ->where('participants.is_deleted', 0)
        ->first();

        if (!$participant) {
            return $this->failNotFound('Participant not found');
        }

        // Get payment information
        $paymentModel = new \App\Models\PaymentModel();
        $payments = $paymentModel->select('
            payments.id,
            payments.status,
            payments.created_at,
            payments.payment_method_id,
            payments.notes,
            payment_methods.name as payment_method_name
        ')
        ->join('payment_methods', 'payment_methods.id = payments.payment_method_id', 'left')
        ->where('payments.participant_id', $participantId)
        ->where('payments.is_deleted', 0)
        ->orderBy('payments.created_at', 'DESC')
        ->findAll();

        // Get program payment information
        $programPayments = $this->programPaymentModel->select('
            program_payments.id,
            program_payments.name as payment_name,
            program_payments.description,
            program_payments.category as payment_type,
            program_payments.end_date as deadline,
            program_payments.is_active as payment_status
        ')
        ->where('program_payments.program_id', $participant->program_id)
        ->where('program_payments.is_deleted', 0)
        ->orderBy('program_payments.created_at', 'ASC')
        ->findAll();

        // Process payment data
        $paymentSummary = [
            'total_payments' => count($payments),
            'completed_payments' => 0,
            'pending_payments' => 0,
            'failed_payments' => 0,
            'latest_payment_date' => null,
            'payment_completion_status' => 'not_started'
        ];

        $paymentHistory = [];
        foreach ($payments as $payment) {
            // Count payment statuses
            switch ($payment->status) {
                case 2: // Successful
                    $paymentSummary['completed_payments']++;
                    break;
                case 1: // Pending
                    $paymentSummary['pending_payments']++;
                    break;
                case 0: // Failed
                case 3: // Cancelled
                    $paymentSummary['failed_payments']++;
                    break;
            }

            // Build payment history
            $paymentHistory[] = [
                'status' => $this->getPaymentStatusText($payment->status),
                'payment_method' => $payment->payment_method_name ?? 'Not specified',
                'created_at' => $payment->created_at,
                'notes' => $payment->notes
            ];
        }

        // Determine overall payment completion status
        if ($paymentSummary['completed_payments'] > 0) {
            $paymentSummary['payment_completion_status'] = 'completed';
            $paymentSummary['latest_payment_date'] = $payments[0]->created_at ?? null;
        } elseif ($paymentSummary['pending_payments'] > 0) {
            $paymentSummary['payment_completion_status'] = 'pending';
        } elseif ($paymentSummary['failed_payments'] > 0) {
            $paymentSummary['payment_completion_status'] = 'failed';
        }

        // Process program payment requirements
        $paymentRequirements = [];
        foreach ($programPayments as $programPayment) {
            $paymentRequirements[] = [
                'id' => $programPayment->id,
                'name' => $programPayment->payment_name,
                'description' => $programPayment->description,
                'type' => $programPayment->payment_type,
                'deadline' => $programPayment->deadline,
                'status' => $this->getProgramPaymentStatusText($programPayment->payment_status)
            ];
        }

        // Build response data
        $paymentData = [
            'participant' => [
                'id' => $participant->id,
                'full_name' => $participant->full_name,
                'email' => $participant->email,
                'nationality' => $participant->nationality,
                'category' => $participant->category,
                'registration_date' => $participant->created_at,
                'form_status' => $this->getFormStatusText($participant->form_status),
                'form_status_code' => $participant->form_status,
                'program_name' => $participant->program_name
            ],
            'payment_summary' => $paymentSummary,
            'payment_history' => $paymentHistory,
            'program_payment_requirements' => $paymentRequirements,
            'last_updated' => date('Y-m-d H:i:s')
        ];

        return $this->respondSuccess($paymentData, self::HTTP_OK, 'Participant payment summary retrieved successfully');
    }

    /**
     * Get comprehensive filter parameters for dashboard participants
     * 
     * @return array Processed filter parameters
     */
    private function getDashboardParticipantFilters()
    {
        $request = service('request');
        
        return [
            'search' => trim($request->getVar('search') ?? ''),
            'form_status' => $request->getVar('form_status'),
            'category' => $request->getVar('category'),
            'nationality' => $request->getVar('nationality'),
            'institution' => $request->getVar('institution'),
            'date_from' => $request->getVar('date_from'),
            'date_to' => $request->getVar('date_to'),
            'page' => max(1, (int)($request->getVar('page') ?? 1)),
            'per_page' => min(100, max(10, (int)($request->getVar('per_page') ?? 20))),
            'sort_by' => in_array($request->getVar('sort_by'), ['created_at', 'full_name', 'email', 'nationality', 'institution', 'form_status']) 
                        ? $request->getVar('sort_by') : 'created_at',
            'sort_order' => in_array(strtoupper($request->getVar('sort_order')), ['ASC', 'DESC']) 
                           ? strtoupper($request->getVar('sort_order')) : 'DESC'
        ];
    }

    /**
     * Build filtered participants query with comprehensive filtering
     * 
     * @param array $participantIds List of allowed participant IDs
     * @param array $filters Filter parameters
     * @return array Query result with participants data
     */
    private function buildFilteredParticipantsQuery($participantIds, $filters)
    {
        $db = \Config\Database::connect();
        
        // Build the SQL query with proper joins
        $sql = "
            SELECT 
                p.id,
                p.full_name,
                u.email,
                p.nationality,
                p.institution,
                p.phone_number,
                p.category,
                p.created_at,
                p.updated_at,
                ps.form_status,
                CASE 
                    WHEN ps.form_status = 2 THEN 'submitted'
                    WHEN ps.form_status = 1 THEN 'in_progress'
                    WHEN ps.form_status = 0 THEN 'not_started'
                    ELSE 'not_started'
                END as form_status_label
            FROM participants p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN participant_statuses ps ON p.id = ps.participant_id
            WHERE p.id IN (" . implode(',', array_map('intval', $participantIds)) . ")
            AND p.is_deleted = 0
        ";

        $params = [];

        // Apply search filter
        if (!empty($filters['search'])) {
            $sql .= " AND (p.full_name LIKE ? OR u.email LIKE ? OR p.nationality LIKE ? OR p.institution LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        // Apply form status filter
        if (!empty($filters['form_status'])) {
            switch ($filters['form_status']) {
                case 'submitted':
                    $sql .= " AND ps.form_status = 2";
                    break;
                case 'in_progress':
                    $sql .= " AND ps.form_status = 1";
                    break;
                case 'not_started':
                    $sql .= " AND (ps.form_status IS NULL OR ps.form_status = 0)";
                    break;
            }
        }

        // Apply category filter
        if (!empty($filters['category'])) {
            $sql .= " AND p.category = ?";
            $params[] = $filters['category'];
        }

        // Apply nationality filter
        if (!empty($filters['nationality'])) {
            $sql .= " AND p.nationality = ?";
            $params[] = $filters['nationality'];
        }

        // Apply institution filter
        if (!empty($filters['institution'])) {
            $sql .= " AND p.institution LIKE ?";
            $params[] = "%" . $filters['institution'] . "%";
        }

        // Apply date range filters
        if (!empty($filters['date_from'])) {
            $sql .= " AND p.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND p.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        // Apply sorting
        $sortBy = $filters['sort_by'];
        $sortOrder = $filters['sort_order'];
        
        if ($sortBy === 'email') {
            $sql .= " ORDER BY u.email $sortOrder";
        } elseif ($sortBy === 'form_status') {
            $sql .= " ORDER BY ps.form_status $sortOrder";
        } else {
            $sql .= " ORDER BY p.$sortBy $sortOrder";
        }

        // Execute query
        $query = $db->query($sql, $params);
        $participants = $query->getResultArray();

        return [
            'participants' => $participants,
            'total' => count($participants)
        ];
    }

    /**
     * Format participant data for response
     * 
     * @param array $participants Raw participant data
     * @return array Formatted participant data
     */
    private function formatParticipantData($participants)
    {
        $formattedParticipants = [];
        
        foreach ($participants as $participant) {
            $formattedParticipants[] = [
                'id' => (int)$participant['id'],
                'full_name' => $participant['full_name'] ?? 'N/A',
                'email' => $participant['email'] ?? 'N/A',
                'nationality' => $participant['nationality'] ?? 'N/A',
                'institution' => $participant['institution'] ?? 'N/A',
                'phone_number' => $participant['phone_number'] ?? '',
                'category' => $participant['category'] ?? 'N/A',
                'form_status' => $participant['form_status_label'],
                'form_status_code' => (int)($participant['form_status'] ?? 0),
                'registration_date' => $participant['created_at'] ? date('Y-m-d', strtotime($participant['created_at'])) : null,
                'last_updated' => $participant['updated_at'] ? date('Y-m-d', strtotime($participant['updated_at'])) : null,
                'days_since_registration' => $participant['created_at'] ? $this->calculateDaysAgo($participant['created_at']) : 0
            ];
        }

        return $formattedParticipants;
    }

    /**
     * Get human-readable payment status text
     * 
     * @param int $status Payment status code
     * @return string Status text
     */
    private function getPaymentStatusText($status)
    {
        switch ($status) {
            case 0: return 'Created';
            case 1: return 'Pending';
            case 2: return 'Completed';
            case 3: return 'Cancelled';
            case 4: return 'Rejected';
            default: return 'Unknown';
        }
    }

    /**
     * Get human-readable form status text
     * 
     * @param int $status Form status code
     * @return string Status text
     */
    private function getFormStatusText($status)
    {
        switch ($status) {
            case 0: return 'Not Started';
            case 1: return 'In Progress';
            case 2: return 'Submitted';
            default: return 'Unknown';
        }
    }

    /**
     * Get human-readable program payment status text
     * 
     * @param int $status Program payment status code
     * @return string Status text
     */
    private function getProgramPaymentStatusText($status)
    {
        switch ($status) {
            case 0: return 'Inactive';
            case 1: return 'Active';
            default: return 'Unknown';
        }
    }


    /**
     * show - GET {{api_url}}/ambassadors/1
     * Auto-generated method
     */
    public function show($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement show logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'show executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute show',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * getParticipantReferrals - GET {{api_url}}/ambassadors/AMB001/referrals
     * Auto-generated method
     */
    public function getParticipantReferrals($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement getParticipantReferrals logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'getParticipantReferrals executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute getParticipantReferrals',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * checkQuery - GET {{api_url}}/ambassadors/check-query?encrypted_data=sample_encrypted_string
     * Auto-generated method
     */
    public function checkQuery($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement check-query logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'check-query executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute check-query',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * getByProgramId - GET {{api_url}}/ambassadors/programs/{{program_id}}/ref-code/AMB001
     * Auto-generated method
     */
    public function getByProgramId($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            // TODO: Implement getByProgramId logic
            
            return $this->respond([
                'success' => true,
                'data' => $data ?? ['id' => $id],
                'message' => 'getByProgramId executed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to execute getByProgramId',
                'error' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
