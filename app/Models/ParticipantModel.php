<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        helper(['cache_helper', 'general']);
    }
    
    protected $table = 'participants';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';


    // `id`, `user_id`, `account_id`, `full_name`, `birthdate`, `ref_code_ambassador`, `program_id`, `gender`, `origin_address`, `current_address`, `nationality`, `occupation`, `institution`, `organizations`, `country_code`, `phone_number`, `picture_url`, `instagram_account`, `emergency_account`, `contact_relation`, `disease_history`, `tshirt_size`, `category`, `experiences`, `achievements`, `resume_url`, `knowledge_source`, `source_account_name`, `twibbon_link`, `requirement_link`, `is_active`, `is_deleted`, `created_at`, `updated_at
    protected $allowedFields = [
        'user_id',
        'account_id',
        'full_name',
        'nickname',
        'program_id',
        'gender',
        'birthdate',
        'origin_address',
        'current_address',
        'nationality',
        'nationality_flag',
        'nationality_code',
        'occupation',
        'education_level',
        'major',
        'institution',
        'organizations',
        'country_code',
        'phone_flag',
        'phone_number',
        'picture_url',
        'instagram_account',
        'emergency_account',
        'emergency_country_code',
        'emergency_phone_flag',
        'contact_relation',
        'disease_history',
        'tshirt_size',
        'category',
        'experiences',
        'achievements',
        'resume_url',
        'knowledge_source',
        'source_account_name',
        'twibbon_link',
        'requirement_link',
        'score_total',
        'score_status',
        'is_active',
        'is_deleted'
    ];

    /**
     * Get random participants for a specific program
     *
     * @param int $programId The program ID
     * @param int $limit Maximum number of participants to return
     * @return array Array of participant data
     */
    public function getRandomParticipantForProgram($web_url)
    {
        if (empty($web_url)) {
            return [];
        }

        $builder = $this->builder();

        // Join with programs and program_categories to find the program with matching web_url
        return $builder->select('participants.*')
            ->join('programs', 'programs.id = participants.program_id', 'inner')
            ->join('program_categories', 'program_categories.id = programs.program_category_id', 'inner')
            ->where('program_categories.web_url', $web_url)
            ->where('participants.is_active', 1)
            ->where('participants.is_deleted', 0)
            ->where('participants.full_name IS NOT NULL')
            ->where('participants.full_name !=', '')
            ->where('participants.nationality IS NOT NULL')
            ->where('participants.nationality !=', '')
            ->orderBy('RAND()') // Random order
            ->limit(1)
            ->get()
            ->getFirstRow();
    }

    // get by id
    public function getById($id)
    {
        // join participant data with user, program, payment, essay, subtheme etc
        $builder = $this->builder();

        $builder->select('participants.*, users.*, programs.*, payments.*, participant_essays.*')
            ->join('users', 'users.id = participants.user_id', 'left')
            ->join('programs', 'programs.id = participants.program_id', 'left')
            ->join('payments', 'payments.participant_id = participants.id', 'left')
            ->join('participant_essays', 'participant_essays.participant_id = participants.id', 'left')
            ->where('participants.id', $id)
            ->where('participants.is_active', 1)
            ->where('participants.is_deleted', 0)
            ->orderBy('participants.created_at', 'DESC');

        return $builder->get()->getRow();
    }

    /**
     * Get participants by an array of participant IDs
     *
     * @param array $participantIds Array of participant IDs
     * @return array Array of participant data
     */
    public function getParticipantsByIds(array $participantIds)
    {
        if (empty($participantIds)) {
            return [];
        }

        $builder = $this->builder();

        return $builder->whereIn('id', $participantIds)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->get()
            ->getResultArray();
    }

    /**
     * Get photos of participants for a specific program
     *
     * @param int $programId The program ID
     * @param int $limit Maximum number of photos to return
     * @return array List of participant picture URLs
     */
    public function getProgramParticipantsPhotos($programId, $limit = 5)
    {
        $builder = $this->builder();

        $result = $builder->select('picture_url')
            ->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->where('picture_url IS NOT NULL')
            ->where('picture_url !=', '')
            ->orderBy('RAND()')  // Random order
            ->limit($limit)
            ->get()
            ->getResultArray();

        return array_column($result, 'picture_url');
    }

    // get participants by user id
    public function getParticipantsByUserId($userId)
    {
        // there can be more than one participant with the same user id
        // so we need to return all participants with the same user id
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * Get all participants with pagination
     *
     * @param int $limit Number of records to return
     * @param int $offset Starting position for query
     * @param array $filters Optional filters for the query
     * @return array Array containing data and total count
     */
    public function getAllParticipants($limit = 10, $offset = 0, $filters = [])
    {
        $builder = $this->builder();

        // Apply filters if provided
        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                $builder->whereIn($key, $value);
            } else {
                $builder->where($key, $value);
            }
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        // Apply pagination
        $builder->limit($limit, $offset);

        // Get results
        $participants = $builder->get()->getResult();

        return [
            'data' => $participants,
            'total' => $total
        ];
    }

    /**
     * Get participants by program ID
     *
     * @param int $programId
     * @return array
     */
    public function getParticipantsByProgramId($programId)
    {
        return $this->where('program_id', $programId)->findAll();
    }

    /**
     * Get participant by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getParticipant($id)
    {
        return $this->find($id);
    }

    public function getParticipants($limit = 10, $offset = 0, $filters = [])
    {
        $builder = $this->builder();

        // Apply filters dynamically
        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                $builder->whereIn($key, $value);
            } else {
                $builder->where($key, $value);
            }
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        // Apply pagination
        $builder->limit($limit, $offset);

        // Select all fields
        $builder->select('*');

        // Execute query
        $result = $builder->get()->getResultArray();

        $participants = [];

        // Map to entities
        foreach ($result as $row) {
            $tempParticipant = $row;

            $userId = $row['user_id'];

            // set user
            $userModel =  new UserModel();

            $user = $userModel->find($userId);

            $tempParticipant['user'] = $user;

            // set essay
            $participantEssayModel = new ParticipantEssayModel();

            $participantEssay = $participantEssayModel->getParticipantEssayByParticipantId($row['id']);

            $tempParticipant['essays'] = $participantEssay;

            // set payments - Always fetch fresh payment data for participants
            $paymentModel = new PaymentModel();

            // Use the correct method name to get participant payments
            $payments = $paymentModel->getPaymentsByParticipantId($row['id']);

            $tempParticipant['payments'] = $payments;

            $participants[] = $tempParticipant;
        }

        return [
            'data' => $participants,
            'total' => $total
        ];
    }

    /**
     * Get participants for the current program
     *
     * @param int $limit Number of records to return
     * @param int $offset Starting position for query
     * @param array $filters Optional additional filters
     * @return array Array containing data and total count
     */
    public function getCurrentProgramParticipants($limit = 10, $offset = 0, $filters = [])
    {
        $session = session();
        $currentProgramId = $session->get('current_program');

        if (!$currentProgramId) {
            return [
                'data' => [],
                'total' => 0
            ];
        }

        // Merge program_id filter with other filters
        $filters['program_id'] = $currentProgramId;

        // Use existing getParticipants method with the program filter
        return $this->getParticipants($limit, $offset, $filters);
    }

    // get participant by params
    public function getParticipantByParams($params)
    {
        $builder = $this->builder();

        // Apply filters dynamically
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $builder->whereIn($key, $value);
            } else {
                $builder->where($key, $value);
            }
        }

        // Select all fields
        $builder->select('*');

        // Execute query and return one result
        return $builder->get()->getRow();
    }

    public function createParticipant($data)
    {
        // Validate input data
        if (empty($data['user_id']) || empty($data['program_id']) || empty($data['full_name'])) {
            throw new \InvalidArgumentException('Missing required fields: user_id, program_id, full_name');
        }

        // Set default values
        $data['account_id'] = $this->generateAccountId($data['user_id']);
        $data['is_active'] = 1;
        $data['is_deleted'] = 0;

        // Insert participant data
        $this->save($data);

        // Return the complete participant object
        return $this->find($this->insertID());
    }

    public function generateAccountId($userId)
    {
        // Generate a unique account ID with uniqid() from user id
        $accountId = uniqid($userId);

        // Check if the account ID already exists
        $existingAccount = $this->where('account_id', $accountId)->first();

        if ($existingAccount) {
            // If it exists, generate a new one
            return $this->generateAccountId($userId);
        }

        // If it doesn't exist, return the new account ID
        return $accountId;
    }

    /**
     * Get all programs that a user is participating in
     *
     * @param int $userId The user ID
     * @return array List of programs the user is participating in
     */
    public function getUserPrograms($userId)
    {
        if (empty($userId)) {
            return [];
        }

        // First get all participants entries for this user
        $participants = $this->where('user_id', $userId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->findAll();

        if (empty($participants)) {
            return [];
        }

        // Extract program IDs
        $programIds = [];
        foreach ($participants as $participant) {
            $programIds[] = $participant->program_id;
        }

        // Get program details
        $programModel = new \App\Models\ProgramModel();
        $programs = $programModel->whereIn('id', $programIds)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->findAll();

        return $programs;
    }

    public function getTotalParticipants($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->countAllResults();
    }

    public function getTotalCountries($programId)
    {
        // Create a cache key based on program ID
        $cacheKey = "total_countries_{$programId}";
        
        // Try to get from cache first
        $cache = \Config\Services::cache();
        $totalCountries = $cache->get($cacheKey);
        
        if ($totalCountries !== null) {
            return $totalCountries;
        }
        
        // Cache miss - calculate from database
        // Use case-insensitive distinct count and handle empty/null values
        $totalCountries = $this->select('COUNT(DISTINCT CASE 
                WHEN nationality IS NULL OR TRIM(nationality) = "" THEN "Unknown" 
                ELSE UPPER(TRIM(nationality)) 
            END) as total_countries')
            ->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->get()
            ->getRow()
            ->total_countries;
            
        // Save to cache for 4 hours (14400 seconds)
        $cache->save($cacheKey, $totalCountries, 14400);
        
        return $totalCountries;
    }

    public function getCountriesData($programId)
    {
        // Create a cache key based on program ID
        $cacheKey = "countries_data_{$programId}";
        
        // Try to get from cache first
        $cache = \Config\Services::cache();
        $countriesData = $cache->get($cacheKey);
        
        if ($countriesData !== null) {
            return $countriesData;
        }
        
        // Cache miss - calculate from database
        // Group by normalized country names (case-insensitive, handle empty values)
        $countriesData = $this->select('
                CASE 
                    WHEN nationality IS NULL OR TRIM(nationality) = "" THEN "Unknown" 
                    ELSE TRIM(nationality)
                END as nationality, 
                COUNT(*) as participants_count
            ')
            ->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->groupBy('CASE 
                WHEN nationality IS NULL OR TRIM(nationality) = "" THEN "Unknown" 
                ELSE UPPER(TRIM(nationality))
            END')
            ->orderBy('participants_count', 'DESC')
            ->get()
            ->getResult();
            
        // Save to cache for 4 hours (14400 seconds)
        $cache->save($cacheKey, $countriesData, 14400);
        
        return $countriesData;
    }

    /**
     * Get participant statistics for a program
     *
     * @param int $programId
     * @return object
     */
    public function getParticipantStats($programId)
    {
        // Create a cache key based on program ID and today's date (for daily refresh)
        $cacheKey = "participant_stats_{$programId}_" . date('Ymd');
        
        // Try to get from cache first
        $cache = \Config\Services::cache();
        $stats = $cache->get($cacheKey);
        
        if ($stats !== null) {
            // Return cached stats
            return $stats;
        }
        
        // Cache miss - calculate stats from database
        // Count participants by category: full_funded or self_funded
        $builder = $this->db->table($this->table);
        $results = $builder->select('COUNT(*) as count, category')
            ->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->groupBy('category')
            ->get()
            ->getResult();

        // Initialize counts
        $categoryCounts = [
            'fully_funded' => 0,
            'self_funded' => 0
        ];

        // Map results to category counts
        foreach ($results as $row) {
            $category = strtolower($row->category);
            if (array_key_exists($category, $categoryCounts)) {
                $categoryCounts[$category] = $row->count;
            }
        }

        // Get total participants
        $totalParticipants = $builder->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->countAllResults();

        // Get participants registered in the last 30 days
        $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
        $recentParticipants = $builder->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->where('created_at >=', $thirtyDaysAgo)
            ->countAllResults();

        $stats = (object) [
            'total' => $totalParticipants,
            'recent' => $recentParticipants,
            'category_counts' => $categoryCounts
        ];
        
        // Save to cache for 1 hour (3600 seconds)
        $cache->save($cacheKey, $stats, 3600);
        
        return $stats;
    }
    /**
     * Search participants by custom parameters with users table join
     * 
     * @param array $searchParams Search parameters
     * @param int $limit Items per page
     * @param int $page Page number
     * @param array $includeOptions Optional related data to include
     * @return array Result with data and total count
     */
    public function searchParticipants($searchParams, $limit = 10, $page = 1, $includeOptions = [])
    {
        // Check if payments are requested - if so, skip caching entirely
        $includePayments = in_array('payments', $includeOptions);
        
        if (!$includePayments) {
            // Create a unique cache key based on search parameters (only when payments not included)
            $cacheKey = "participant_search_" . md5(json_encode($searchParams) . "_limit_{$limit}_page_{$page}_includes_" . json_encode($includeOptions));
            
            // Try to get from cache
            $cache = \Config\Services::cache();
            $results = $cache->get($cacheKey);
            
            if ($results !== null) {
                // Return cached results (only when payments not included)
                return $results;
            }
        }
        
        // Cache miss OR payment data requested - perform fresh search
        $builder = $this->builder();
        $offset = ($page - 1) * $limit;
        // Join with users table and programs table to enable search on user fields and program_category_id
        $builder->select('
            participants.*,
            users.id as user_id_full,
            users.full_name as user_full_name,
            users.email as user_email,
            users.is_verified as user_is_verified,
            users.program_category_id as user_program_category_id,
            users.is_active as user_is_active,
            users.created_at as user_created_at,
            users.updated_at as user_updated_at,
            programs.program_category_id as program_category_id
        ')
            ->join('users', 'users.id = participants.user_id', 'inner')
            ->join('programs', 'programs.id = participants.program_id', 'left')
            ->where('participants.is_active', 1)
            ->where('participants.is_deleted', 0)
            ->where('users.is_active', 1)
            ->where('users.is_deleted', 0);

        // Apply search filters dynamically
        foreach ($searchParams as $key => $value) {
            if (empty($value)) continue;

            switch ($key) {
                case 'email':
                    $builder->where('users.email', $value);
                    break;

                case 'user_full_name':
                    $builder->like('users.full_name', $value);
                    break;

                case 'full_name':
                    $builder->like('participants.full_name', $value);
                    break;

                case 'phone_number':
                    $builder->like('participants.phone_number', $value);
                    break;
                case 'program_id':
                    $builder->where('participants.program_id', (int)$value);
                    break;

                case 'program_category_id':
                    $builder->where('programs.program_category_id', (int)$value);
                    break;

                case 'gender':
                    $builder->where('participants.gender', $value);
                    break;

                case 'nationality':
                    $builder->like('participants.nationality', $value);
                    break;

                case 'institution':
                    $builder->like('participants.institution', $value);
                    break;

                case 'occupation':
                    $builder->like('participants.occupation', $value);
                    break;

                case 'category':
                    $builder->where('participants.category', $value);
                    break;

                case 'is_verified':
                    $builder->where('users.is_verified', (int)$value);
                    break;

                default:
                    // For any other fields that exist in participants table
                    if (in_array($key, $this->allowedFields)) {
                        if (is_numeric($value)) {
                            $builder->where("participants.{$key}", $value);
                        } else {
                            $builder->like("participants.{$key}", $value);
                        }
                    }
                    break;
            }
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        // Apply pagination and ordering
        $builder->orderBy('participants.created_at', 'DESC')
            ->limit($limit, $offset);

        // Execute query
        $results = $builder->get()->getResultArray();

        // Process results to include related data
        $participants = [];
        foreach ($results as $row) {
            $participant = $row;
            // Add complete user data as nested object
            $participant['user'] = [
                'id' => $row['user_id_full'],
                'full_name' => $row['user_full_name'],
                'email' => $row['user_email'],
                'is_verified' => $row['user_is_verified'],
                'program_category_id' => $row['user_program_category_id'],
                'is_active' => $row['user_is_active'],
                'created_at' => $row['user_created_at'],
                'updated_at' => $row['user_updated_at']
            ];

            // Add program category info
            $participant['program_category_id'] = $row['program_category_id'];

            // Remove duplicate user fields from main object
            unset(
                $participant['user_id_full'],
                $participant['user_full_name'],
                $participant['user_email'],
                $participant['user_is_verified'],
                $participant['user_program_category_id'],
                $participant['user_is_active'],
                $participant['user_created_at'],
                $participant['user_updated_at']
            );

            // Load additional related data based on include options
            if (in_array('essays', $includeOptions)) {
                $participant['essays'] = $this->getParticipantEssays($row['id']);
            }

            if (in_array('payments', $includeOptions)) {
                $participant['payments'] = $this->getParticipantPayments($row['id']);
            }

            $participants[] = $participant;
        }

        $searchResults = [
            'data' => $participants,
            'total' => $total
        ];
        
        // Only cache search results when payments are NOT included
        // Payment data should always be fresh to ensure real-time accuracy
        if (!$includePayments) {
            // Cache the search results for 30 minutes (1800 seconds)
            // For search results, we use a shorter cache time since the data might change
            $cache = \Config\Services::cache();
            $cache->save($cacheKey, $searchResults, 1800);
        } else {
            // Log when payment data is served fresh without caching
            log_message('info', "Participant search with payments - Fresh data served, no caching applied");
        }
        
        return $searchResults;
    }

    /**
     * Get participant essays by participant ID
     * Helper method for search results enhancement
     */
    private function getParticipantEssays($participantId)
    {
        $essayModel = new ParticipantEssayModel();
        return $essayModel->getParticipantEssayByParticipantId($participantId);
    }

    /**
     * Get participant payments by participant ID
     * Helper method for search results enhancement
     * 
     * IMPORTANT: Payment data is NEVER cached to ensure real-time information
     */
    private function getParticipantPayments($participantId)
    {
        $paymentModel = new PaymentModel();
        // Always fetch fresh payment data - NO CACHING for payment information
        return $paymentModel->getPaymentsByParticipantId($participantId);
    }

    /**
     * Load valid countries from JSON file
     * 
     * @return array Array of valid country names (normalized to uppercase)
     */
    private function getValidCountries()
    {
        $jsonPath = FCPATH . 'assets/json/country-list.json';
        
        if (!file_exists($jsonPath)) {
            return [];
        }
        
        $jsonContent = file_get_contents($jsonPath);
        $countries = json_decode($jsonContent, true);
        
        if (!$countries) {
            return [];
        }
        
        // Extract country names and normalize them
        $validCountries = [];
        foreach ($countries as $country) {
            if (isset($country['countryName'])) {
                $validCountries[] = strtoupper(trim($country['countryName']));
            }
        }
        
        return $validCountries;
    }

    /**
     * Get total valid countries (matching against countries JSON)
     *
     * @param int $programId
     * @return int
     */
    public function getTotalValidCountries($programId)
    {
        $validCountries = $this->getValidCountries();
        
        if (empty($validCountries)) {
            // Fallback to original method if JSON is not available
            return $this->getTotalCountries($programId);
        }
        
        // Create IN clause for valid countries
        $validCountriesStr = "'" . implode("','", array_map('addslashes', $validCountries)) . "'";
        
        return $this->select('COUNT(DISTINCT CASE 
                WHEN nationality IS NULL OR TRIM(nationality) = "" THEN NULL
                WHEN UPPER(TRIM(nationality)) IN (' . $validCountriesStr . ') THEN UPPER(TRIM(nationality))
                ELSE NULL
            END) as total_countries')
            ->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->get()
            ->getRow()
            ->total_countries;
    }

    /**
     * Get valid countries data (matching against countries JSON)
     *
     * @param int $programId
     * @return array
     */
    public function getValidCountriesData($programId)
    {
        $validCountries = $this->getValidCountries();
        
        if (empty($validCountries)) {
            // Fallback to original method if JSON is not available
            return $this->getCountriesData($programId);
        }
        
        // Create IN clause for valid countries
        $validCountriesStr = "'" . implode("','", array_map('addslashes', $validCountries)) . "'";
        
        $results = $this->select('
                TRIM(nationality) as nationality, 
                COUNT(*) as participants_count
            ')
            ->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->where('nationality IS NOT NULL')
            ->where('TRIM(nationality) !=', '')
            ->having('UPPER(TRIM(nationality)) IN (' . $validCountriesStr . ')')
            ->groupBy('UPPER(TRIM(nationality))')
            ->orderBy('participants_count', 'DESC')
            ->get()
            ->getResult();
            
        return $results;
    }

    /**
     * Get invalid/unmatched countries data for debugging
     *
     * @param int $programId
     * @return array
     */
    public function getInvalidCountriesData($programId)
    {
        $validCountries = $this->getValidCountries();
        
        if (empty($validCountries)) {
            return [];
        }
        
        // Create IN clause for valid countries
        $validCountriesStr = "'" . implode("','", array_map('addslashes', $validCountries)) . "'";
        
        return $this->select('
                CASE 
                    WHEN nationality IS NULL OR TRIM(nationality) = "" THEN "Empty/Null" 
                    ELSE TRIM(nationality)
                END as nationality, 
                COUNT(*) as participants_count
            ')
            ->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->groupBy('CASE 
                WHEN nationality IS NULL OR TRIM(nationality) = "" THEN "Empty/Null"
                ELSE UPPER(TRIM(nationality))
            END')
            ->having('CASE 
                WHEN nationality IS NULL OR TRIM(nationality) = "" THEN 1
                ELSE UPPER(TRIM(nationality)) NOT IN (' . $validCountriesStr . ')
            END')
            ->orderBy('participants_count', 'DESC')
            ->get()
            ->getResult();
    }
    
    /**
     * Check if participant is eligible to switch category
     * Optimized with joins to minimize database queries
     * 
     * @param int $participantId
     * @return array Eligibility result with detailed information
     */
    public function checkCategorySwitchEligibility($participantId)
    {
        try {
            // Single query to get participant with related data
            $builder = $this->builder();
            $participant = $builder->select('
                    participants.*,
                    ps.form_status,
                    ps.payment_status,
                    ps.general_status,
                    ps.document_status
                ')
                ->join('participant_statuses ps', 'ps.participant_id = participants.id', 'left')
                ->where('participants.id', $participantId)
                ->where('participants.is_deleted', 0)
                ->get()
                ->getFirstRow();

            if (!$participant) {
                return [
                    'eligible' => false,
                    'reason' => 'Participant not found',
                    'participant' => null,
                    'current_category' => null,
                    'target_category' => null,
                    'target_payment' => null
                ];
            }

            // Determine current and target categories
            $currentCategory = $participant->category ?? 'self_funded';
            $targetCategory = ($currentCategory === 'fully_funded') ? 'self_funded' : 'fully_funded';

            // Condition 1: Check for successful registration payments using optimized query
            $paymentModel = new \App\Models\PaymentModel();
            $hasSuccessfulPayment = $paymentModel->hasSuccessfulRegistrationPayment($participantId);

            if ($hasSuccessfulPayment['has_payment']) {
                return [
                    'eligible' => false,
                    'reason' => "Cannot switch category. You have already made a successful registration payment for {$hasSuccessfulPayment['payment_type']} category (Payment: {$hasSuccessfulPayment['payment_name']})",
                    'participant' => $participant,
                    'current_category' => $currentCategory,
                    'target_category' => $targetCategory,
                    'target_payment' => null
                ];
            }

            // Condition 2: Check submission form status
            if ($participant->form_status == 2) {
                return [
                    'eligible' => false,
                    'reason' => 'Cannot switch category. You have already submitted the submission form',
                    'participant' => $participant,
                    'current_category' => $currentCategory,
                    'target_category' => $targetCategory,
                    'target_payment' => null
                ];
            }

            // Condition 3: Check if target category payment is available
            $programPaymentModel = new \App\Models\ProgramPaymentModel();
            $targetPayment = $programPaymentModel->getAvailableRegistrationPayment($participant->program_id, $targetCategory);

            if (!$targetPayment) {
                return [
                    'eligible' => false,
                    'reason' => "Cannot switch to {$targetCategory} category. Registration payment for this category is not currently available or has expired",
                    'participant' => $participant,
                    'current_category' => $currentCategory,
                    'target_category' => $targetCategory,
                    'target_payment' => null
                ];
            }

            // All conditions passed
            return [
                'eligible' => true,
                'reason' => 'Participant is eligible to switch category',
                'participant' => $participant,
                'current_category' => $currentCategory,
                'target_category' => $targetCategory,
                'target_payment' => $targetPayment
            ];

        } catch (\Exception $e) {
            log_message('error', "Error checking category switch eligibility: " . $e->getMessage());
            return [
                'eligible' => false,
                'reason' => 'An error occurred while checking eligibility',
                'participant' => null,
                'current_category' => null,
                'target_category' => null,
                'target_payment' => null
            ];
        }
    }

    /**
     * Switch participant category with optimized database operations
     * 
     * @param int $participantId
     * @param string $newCategory
     * @return bool Success status
     */
    public function switchParticipantCategory($participantId, $newCategory)
    {
        try {
            // Validate category
            if (!in_array($newCategory, ['fully_funded', 'self_funded'])) {
                throw new \InvalidArgumentException('Invalid category. Must be fully_funded or self_funded');
            }

            // Update participant category using CodeIgniter model's update method
            $updateData = [
                'category' => $newCategory,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->update($participantId, $updateData);

            if ($result) {
                // Invalidate related caches
                $this->invalidateParticipantCaches($participantId);
                
                log_message('info', "Participant category updated successfully - ID: {$participantId}, New Category: {$newCategory}");
                return true;
            }

            return false;

        } catch (\Exception $e) {
            log_message('error', "Error switching participant category: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache invalidation hooks
     */
    protected function afterInsert(array $data)
    {
        // Get the program ID from the inserted data
        $programId = $data['data']['program_id'] ?? null;
        
        // Invalidate the cache for this participant's program
        if ($programId) {
            // Invalidate export cache
            if (function_exists('invalidate_export_cache')) {
                invalidate_export_cache($programId);
            }
            
            // Invalidate participant stats cache
            if (function_exists('invalidate_participant_cache')) {
                invalidate_participant_cache($programId);
            }
        }
        
        return $data;
    }
    
    protected function afterUpdate(array $data)
    {
        // If the whole record is available
        if (isset($data['id'])) {
            $participant = $this->find($data['id']);
            $programId = $participant->program_id ?? null;
        } 
        // Otherwise, try to get it from the data
        else {
            $programId = $data['data']['program_id'] ?? null;
        }
        
        // Invalidate the cache for this participant's program
        if ($programId) {
            // Invalidate export cache
            if (function_exists('invalidate_export_cache')) {
                invalidate_export_cache($programId);
            }
            
            // Invalidate participant stats cache
            if (function_exists('invalidate_participant_cache')) {
                invalidate_participant_cache($programId);
            }
        }
        
        return $data;
    }
    
    protected function afterDelete(array $data)
    {
        // Similar cache invalidation as afterUpdate
        if (isset($data['id'])) {
            $participant = $this->find($data['id']);
            $programId = $participant->program_id ?? null;
        } else {
            $programId = $data['data']['program_id'] ?? null;
        }
        
        // Invalidate the cache for this participant's program
        if ($programId) {
            // Invalidate export cache
            if (function_exists('invalidate_export_cache')) {
                invalidate_export_cache($programId);
            }
            
            // Invalidate participant stats cache
            if (function_exists('invalidate_participant_cache')) {
                invalidate_participant_cache($programId);
            }
        }
        
        return $data;
    }

    /**
     * Get normalized participants data for export with proper essay handling
     * Only includes relevant fields and dynamically loads essays based on program configuration
     */
    public function getNormalizedParticipantsForExport(array $filters): array
    {
        try {
            // CRITICAL: Program ID is required
            if (!isset($filters['program_id']) || empty($filters['program_id'])) {
                throw new \RuntimeException('Program ID filter is required for participant export');
            }

            $programId = $filters['program_id'];
            log_message('info', "Starting normalized participant export for program $programId");

            // Get program essays configuration first to determine how many essays this program has
            $programEssayModel = new \App\Models\ProgramEssayModel();
            $programEssays = $programEssayModel->getActiveEssays($programId);
            $essayCount = count($programEssays);
            
            log_message('info', "Program $programId has $essayCount essays configured");

            // Use export database connection with extended timeout
            $db = \Config\Database::connect('export');
            $builder = $this->builder();

            // Build dynamic SELECT clause with only relevant fields
            $selectFields = [
                // Participant core fields
                'participants.id as participant_id',
                'participants.account_id as participant_account_id', 
                'participants.full_name as participant_full_name',
                'participants.nickname as participant_nickname',
                'participants.gender as participant_gender',
                'participants.birthdate as participant_birthdate',
                'participants.nationality as participant_nationality',
                'participants.nationality_code as participant_nationality_code',
                'participants.phone_number as participant_phone',
                'participants.country_code as participant_country_code',
                'participants.category as participant_category',
                'participants.occupation as participant_occupation',
                'participants.education_level as participant_education_level',
                'participants.major as participant_major',
                'participants.institution as participant_institution',
                'participants.current_address as participant_current_address',
                'participants.instagram_account as participant_instagram',
                'participants.tshirt_size as participant_tshirt_size',
                'participants.created_at as participant_registered_at',
                
                // User fields
                'users.email as participant_email',
                'users.is_verified as user_is_verified',
                
                // Program fields
                'programs.name as program_name',
                'programs.start_date as program_start_date',
                'programs.end_date as program_end_date',
                'programs.theme as program_theme',
                
                // Status fields (with left join)
                'ps.form_status as form_status_code',
                'ps.payment_status as payment_status_code', 
                'ps.general_status as general_status_code',
                'ps.document_status as document_status_code'
            ];

            // Add essay fields dynamically based on program configuration
            for ($i = 1; $i <= $essayCount; $i++) {
                $selectFields[] = "MAX(CASE WHEN e.essay_order = $i THEN e.answer END) AS essay_$i";
                $selectFields[] = "MAX(CASE WHEN e.essay_order = $i THEN e.question END) AS essay_{$i}_question";
            }

            $builder->select(implode(', ', $selectFields));

            // Add necessary joins
            $builder->join('users', 'users.id = participants.user_id', 'left')
                   ->join('programs', 'programs.id = participants.program_id', 'left')
                   ->join('participant_statuses ps', 'ps.participant_id = participants.id', 'left');

            // Add essay join with proper ordering based on program essays
            if ($essayCount > 0) {
                $essaySubquery = "(
                    SELECT 
                        pae.participant_id,
                        pae.answer,
                        pe.questions as question,
                        ROW_NUMBER() OVER (PARTITION BY pae.participant_id ORDER BY pe.id) AS essay_order
                    FROM participant_essays pae
                    JOIN program_essays pe ON pe.id = pae.program_essay_id 
                    WHERE pe.program_id = $programId 
                      AND pe.is_deleted = 0 
                      AND pe.is_active = 1
                      AND pae.is_deleted = 0
                )";
                
                $builder->join("{$essaySubquery} e", 'e.participant_id = participants.id', 'left');
                log_message('info', "Added essay join for $essayCount essays in program $programId");
            }

            // Apply filters
            $builder->where('participants.program_id', $programId)
                   ->where('participants.is_deleted', 0);

            // Apply additional filters
            if (isset($filters['category'])) {
                $builder->where('participants.category', $filters['category']);
            }

            if (isset($filters['form_status'])) {
                $builder->where('ps.form_status', $filters['form_status']);
            }

            if (isset($filters['payment_status'])) {
                $builder->where('ps.payment_status', $filters['payment_status']);
            }

            if (isset($filters['general_status'])) {
                $builder->where('ps.general_status', $filters['general_status']);
            }

            if (isset($filters['date_from'])) {
                $builder->where('participants.created_at >=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $builder->where('participants.created_at <=', $filters['date_to']);
            }

            // Handle payment-based filtering
            if (isset($filters['only_paid']) && $filters['only_paid']) {
                $subQuery = $db->table('payments')
                    ->select('participant_id')
                    ->where('status', 1) // Approved payments
                    ->where('is_deleted', 0);
                $builder->whereIn('participants.id', $subQuery);
            }

            // Add GROUP BY for essay aggregation
            $groupByFields = [
                'participants.id', 'participants.account_id', 'participants.full_name',
                'participants.gender', 'participants.birthdate', 'participants.nationality',
                'participants.nationality_code', 'participants.phone_number', 'participants.country_code',
                'participants.category', 'participants.occupation', 'participants.education_level',
                'participants.major', 'participants.institution', 'participants.current_address',
                'participants.instagram_account', 'participants.tshirt_size', 'participants.created_at',
                'users.email', 'users.is_verified', 'programs.name', 'programs.start_date',
                'programs.end_date', 'programs.theme', 'ps.form_status', 'ps.payment_status',
                'ps.general_status', 'ps.document_status'
            ];
            
            $builder->groupBy($groupByFields);

            // Order by registration date
            $builder->orderBy('participants.created_at', 'DESC');

            // Get total count
            $countBuilder = clone $builder;
            $countBuilder->select('COUNT(DISTINCT participants.id) as total_count');
            $countResult = $countBuilder->get()->getRowArray();
            $totalCount = $countResult['total_count'] ?? 0;

            log_message('info', "Found $totalCount participants for export from program $programId");

            // Handle chunked processing for large datasets
            if ($totalCount <= 1000) {
                $result = $builder->get()->getResultArray();
            } else {
                // Process in chunks
                $chunkSize = 500;
                $result = [];
                
                for ($offset = 0; $offset < $totalCount; $offset += $chunkSize) {
                    $db->reconnect();
                    $chunkBuilder = clone $builder;
                    $chunkData = $chunkBuilder->limit($chunkSize, $offset)->get()->getResultArray();
                    $result = array_merge($result, $chunkData);
                    
                    log_message('info', "Processed " . count($result) . " of $totalCount participants for program $programId");
                    usleep(100000); // 0.1 second delay
                }
            }

        // Normalize the data
        $normalizedResult = [];
        foreach ($result as $participant) {
            $normalizedParticipant = $this->normalizeParticipantForExport($participant, $essayCount);
            
            // Sanitize the normalized data to prevent API issues
            $sanitizedParticipant = $this->sanitizeParticipantData($normalizedParticipant);
            $normalizedResult[] = $sanitizedParticipant;
        }            log_message('info', "Completed normalized participant export for program $programId: " . count($normalizedResult) . " records");
            return $normalizedResult;

        } catch (\Exception $e) {
            log_message('error', 'Error in getNormalizedParticipantsForExport: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve normalized participants data: ' . $e->getMessage());
        }
    }

    /**
     * Normalize participant data for export - Optimized for admin use
     * Adds human-readable status translations and prioritizes essential information
     */
    public function normalizeParticipantForExport(array $participant, int $essayCount): array
    {
        $normalized = [
            // === CORE IDENTIFICATION (High Priority) ===
            'Participant_ID' => $participant['participant_id'] ?? 'N/A',
            'Account_ID' => $participant['participant_account_id'] ?? 'N/A',
            'Full_Name' => $participant['participant_full_name'] ?? 'Unknown',
            'Nickname' => $participant['participant_nickname'] ?? 'Not Provided',
            'Email' => $participant['participant_email'] ?? 'No Email',
            
            // === CONTACT INFORMATION (High Priority) ===
            'Phone' => $this->formatPhoneNumber($participant),
            'Nationality' => $participant['participant_nationality'] ?? 'Not Specified',
            'Current_Address' => $this->cleanAddress($participant['participant_current_address'] ?? ''),
            
            // === PERSONAL DETAILS (High Priority) ===
            'Gender' => $this->formatGender($participant['participant_gender'] ?? ''),
            'Birthdate' => $this->formatDate($participant['participant_birthdate']),
            'Age' => $this->calculateAge($participant['participant_birthdate']),
            'Category' => $this->formatCategory($participant['participant_category'] ?? ''),
            
            // === ACADEMIC/PROFESSIONAL INFO (Medium Priority) ===
            'Education_Level' => $this->formatEducationLevel($participant['participant_education_level'] ?? ''),
            'Major_Field' => $participant['participant_major'] ?? 'Not Specified',
            'Institution' => $participant['participant_institution'] ?? 'Not Specified',
            'Occupation' => $participant['participant_occupation'] ?? 'Not Specified',
            
            // === PROGRAM INFORMATION (Medium Priority) ===
            'Program' => $participant['program_name'] ?? 'Unknown Program',
            'Program_Theme' => $participant['program_theme'] ?? 'Not Specified',
            'Registration_Date' => $this->formatDateTime($participant['participant_registered_at']),
            'Document_Status' => $this->getDocumentStatusText($participant['document_status_code'] ?? 0),
            
            // === ADDITIONAL INFO (Lower Priority) ===
            'Instagram_Account' => $this->formatInstagram($participant['participant_instagram'] ?? ''),
            'TShirt_Size' => strtoupper($participant['participant_tshirt_size'] ?? 'Not Specified'),
        ];

        // === ESSAYS (Dynamic based on program) ===
        for ($i = 1; $i <= $essayCount; $i++) {
            if (!empty($participant["essay_$i"])) {
                $question = $participant["essay_{$i}_question"] ?? "Essay $i";
                $answer = $this->cleanEssayText($participant["essay_$i"]);
                
                // Use API-friendly column name
                $columnName = "Essay_$i";
                if (!empty($question) && $question !== "Essay $i") {
                    // Add question as suffix but keep it API-friendly
                    $cleanQuestion = $this->formatEssayColumnNameSafe($question, $i);
                    $columnName = $cleanQuestion;
                }
                    
                $normalized[$columnName] = $answer;
            }
        }

        return $normalized;
    }

    /**
     * Get human-readable form status text
     */
    public function getFormStatusText(int $status): string
    {
        switch ($status) {
            case 0: return 'Incomplete';
            case 1: return 'Complete';
            case 2: return 'Under Review';
            case 3: return 'Approved';
            case 4: return 'Rejected';
            default: return 'Unknown';
        }
    }

    /**
     * Get human-readable payment status text  
     */
    public function getPaymentStatusText(int $status): string
    {
        switch ($status) {
            case 0: return 'Not Paid';
            case 1: return 'Paid';
            case 2: return 'Partial Payment';
            case 3: return 'Refunded';
            default: return 'Unknown';
        }
    }

    /**
     * Get human-readable general status text
     */
    public function getGeneralStatusText(int $status): string
    {
        switch ($status) {
            case 0: return 'Registered';
            case 1: return 'Active';
            case 2: return 'Completed';
            case 3: return 'Withdrawn';
            case 4: return 'Suspended';
            default: return 'Unknown';
        }
    }

    /**
     * Get human-readable document status text
     */
    public function getDocumentStatusText(int $status): string
    {
        switch ($status) {
            case 0: return 'Not Submitted';
            case 1: return 'Submitted';
            case 2: return 'Under Review';
            case 3: return 'Approved';
            case 4: return 'Rejected';
            default: return 'Unknown';
        }
    }

    /**
     * Invalidate participant caches - enhanced for comprehensive coverage
     */
    public function invalidateParticipantCaches($participantId = null, $programId = null): void
    {
        if (!function_exists('cache')) {
            return;
        }

        $cache = \Config\Services::cache();
        
        // Comprehensive participant cache patterns
        $patterns = [
            "participants",
            "participants_list",
            "export_participants", 
            "participant_essays",
            "participant_statuses",
            "program_participants"
        ];

        if ($participantId) {
            $patterns = array_merge($patterns, [
                "participant_$participantId",
                "essays_participant_$participantId",
                "status_participant_$participantId"
            ]);
        }

        if ($programId) {
            $patterns = array_merge($patterns, [
                "participants_program_$programId",
                "export_program_$programId",
                "stats_program_$programId"
            ]);
        }

        foreach ($patterns as $pattern) {
            $cache->delete($pattern);
        }

        log_message('info', 'Invalidated participant caches for participant: ' . ($participantId ?? 'all') . ', program: ' . ($programId ?? 'all'));
    }
    
    // === HELPER METHODS FOR EXPORT OPTIMIZATION ===
    
    /**
     * Format phone number with country code
     */
    private function formatPhoneNumber(array $participant): string
    {
        $phone = $participant['participant_phone'] ?? '';
        $countryCode = $participant['participant_country_code'] ?? '';
        
        if (empty($phone)) {
            return 'No Phone';
        }
        
        if (!empty($countryCode) && !str_starts_with($phone, $countryCode)) {
            return $countryCode . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Clean and format address
     */
    private function cleanAddress(string $address): string
    {
        if (empty($address)) {
            return 'Not Provided';
        }
        
        // Clean multiple spaces and line breaks
        $cleaned = preg_replace('/\s+/', ' ', trim($address));
        
        // Limit length for export readability
        if (strlen($cleaned) > 150) {
            $cleaned = substr($cleaned, 0, 147) . '...';
        }
        
        return $cleaned;
    }
    
    /**
     * Format gender for display
     */
    private function formatGender(string $gender): string
    {
        switch (strtolower($gender)) {
            case 'm':
            case 'male':
                return 'Male';
            case 'f':
            case 'female':
                return 'Female';
            case 'o':
            case 'other':
                return 'Other';
            default:
                return 'Not Specified';
        }
    }
    
    /**
     * Format date consistently
     */
    private function formatDate($date): string
    {
        if (empty($date)) {
            return 'Not Provided';
        }
        
        try {
            return date('Y-m-d', strtotime($date));
        } catch (\Exception $e) {
            return 'Invalid Date';
        }
    }
    
    /**
     * Format datetime consistently
     */
    private function formatDateTime($datetime): string
    {
        if (empty($datetime)) {
            return 'Not Available';
        }
        
        try {
            return date('Y-m-d H:i:s', strtotime($datetime));
        } catch (\Exception $e) {
            return 'Invalid DateTime';
        }
    }
    
    /**
     * Calculate age from birthdate
     */
    private function calculateAge($birthdate): string
    {
        if (empty($birthdate)) {
            return 'Unknown';
        }
        
        try {
            $birth = new \DateTime($birthdate);
            $today = new \DateTime();
            $age = $today->diff($birth)->y;
            return $age . ' years';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
    
    /**
     * Format participant category
     */
    private function formatCategory(string $category): string
    {
        switch (strtolower($category)) {
            case 'student':
                return 'Student';
            case 'professional':
                return 'Professional';
            case 'entrepreneur':
                return 'Entrepreneur';
            case 'other':
                return 'Other';
            default:
                return ucfirst($category) ?: 'Not Specified';
        }
    }
    
    /**
     * Format education level
     */
    private function formatEducationLevel(string $level): string
    {
        switch (strtolower($level)) {
            case 'high_school':
            case 'highschool':
                return 'High School';
            case 'bachelor':
            case 'undergraduate':
                return 'Bachelor\'s Degree';
            case 'master':
            case 'masters':
                return 'Master\'s Degree';
            case 'phd':
            case 'doctorate':
                return 'PhD/Doctorate';
            case 'diploma':
                return 'Diploma';
            default:
                return ucfirst(str_replace('_', ' ', $level)) ?: 'Not Specified';
        }
    }
    
    /**
     * Format Instagram account
     */
    private function formatInstagram(string $instagram): string
    {
        if (empty($instagram)) {
            return 'Not Provided';
        }
        
        // Remove @ if present and add it back
        $instagram = ltrim($instagram, '@');
        
        if (empty($instagram)) {
            return 'Not Provided';
        }
        
        return '@' . $instagram;
    }
    
    /**
     * Clean essay text for export
     */
    private function cleanEssayText(string $text): string
    {
        if (empty($text)) {
            return 'No Response';
        }
        
        // Remove HTML tags
        $cleaned = strip_tags($text);
        
        // Clean multiple spaces and line breaks
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));
        
        // Remove null bytes and control characters
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned);
        
        // Limit length for export readability
        if (strlen($cleaned) > 500) {
            $cleaned = substr($cleaned, 0, 497) . '...';
        }
        
        return $cleaned ?: 'No Response';
    }
    
    /**
     * Format essay column name from question - API-friendly version
     */
    private function formatEssayColumnNameSafe(string $question, int $essayNumber): string
    {
        // Clean the question for use as column name - API safe
        $columnName = strip_tags($question);
        $columnName = preg_replace('/[^\w\s]/', '', $columnName); // Remove special chars
        $columnName = preg_replace('/\s+/', '_', trim($columnName)); // Replace spaces with underscores
        
        // Limit length and add essay number
        if (strlen($columnName) > 30) {
            $columnName = substr($columnName, 0, 27) . '...';
        }
        
        return "Essay_{$essayNumber}_" . $columnName;
    }
    
    /**
     * Format essay column name from question
     */
    private function formatEssayColumnName(string $question, int $essayNumber): string
    {
        // Clean the question for use as column name
        $columnName = strip_tags($question);
        $columnName = preg_replace('/[^\w\s-]/', '', $columnName);
        $columnName = trim($columnName);
        
        // Limit length and add essay number
        if (strlen($columnName) > 50) {
            $columnName = substr($columnName, 0, 47) . '...';
        }
        
        return "Essay $essayNumber: $columnName";
    }
    
    /**
     * Sanitize participant data to prevent API issues
     * Removes control characters, null bytes, and limits field lengths
     */
    private function sanitizeParticipantData(array $participant): array
    {
        $sanitized = [];
        
        foreach ($participant as $field => $value) {
            if (is_string($value)) {
                // Remove null bytes and control characters
                $cleanValue = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
                
                // Ensure UTF-8 encoding
                if (!mb_check_encoding($cleanValue, 'UTF-8')) {
                    $cleanValue = mb_convert_encoding($cleanValue, 'UTF-8', 'UTF-8');
                }
                
                // Limit field length to prevent oversized payloads
                $maxLength = $this->getFieldMaxLength($field);
                if (strlen($cleanValue) > $maxLength) {
                    $cleanValue = substr($cleanValue, 0, $maxLength - 3) . '...';
                }
                
                // Trim whitespace
                $sanitized[$field] = trim($cleanValue);
            } else {
                $sanitized[$field] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get maximum allowed length for different field types
     */
    private function getFieldMaxLength(string $fieldName): int
    {
        // Define field length limits
        if (strpos($fieldName, 'Essay_') === 0) {
            return 2000; // Limit essays to 2000 characters
        }
        
        switch ($fieldName) {
            case 'Full_Name':
            case 'Institution':
            case 'Program':
                return 200;
            
            case 'Email':
            case 'Phone':
                return 100;
                
            case 'Current_Address':
                return 500;
                
            case 'Instagram_Account':
                return 50;
                
            default:
                return 1000; // Default limit for other fields
        }
    }

    /**
     * Get participants data for DataTable with optimized single query
     * 
     * @param array $params DataTable parameters
     * @return array
     */
    public function getDataTableData($params)
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $search = $params['search'] ?? '';
        $order = $params['order'] ?? ['column' => 4, 'dir' => 'desc'];
        $programId = $params['program_id'] ?? null;
        
        // Validate program ID
        if (!$programId) {
            return [
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'No program selected'
            ];
        }

        // Column names for ordering
        $columns = [
            'participants.created_at',           // Order number
            'participants.account_id',           // Account ID
            'participants.full_name',            // Participant Details
            'participant_statuses.form_status',  // Submission Status
            'participants.created_at',           // Registered On
        ];

        $orderColumn = $columns[$order['column']] ?? 'participants.created_at';

        // Base query with optimized joins - single query to get all needed data
        $builder = $this->db->table('participants')
            ->select('
                participants.id,
                participants.account_id,
                participants.full_name,
                participants.picture_url,
                participants.nationality,
                participants.category,
                participants.created_at,
                users.email,
                users.id as user_id,
                COALESCE(participant_statuses.form_status, 0) as form_status
            ')
            ->join('users', 'users.id = participants.user_id', 'inner')
            ->join('participant_statuses', 'participant_statuses.participant_id = participants.id', 'left')
            ->where('participants.program_id', $programId)
            ->where('participants.is_deleted', 0);

        // Clone builder for count query (before filters)
        $countBuilder = clone $builder;

        // Apply search filters
        if (!empty($search)) {
            $builder->groupStart()
                ->like('participants.full_name', $search)
                ->orLike('users.email', $search)
                ->orLike('participants.phone_number', $search)
                ->orLike('participants.account_id', $search)
                ->orLike('participants.nationality', $search)
                ->groupEnd();
                
            // Apply same search to count builder
            $countBuilder->groupStart()
                ->like('participants.full_name', $search)
                ->orLike('users.email', $search)
                ->orLike('participants.phone_number', $search)
                ->orLike('participants.account_id', $search)
                ->orLike('participants.nationality', $search)
                ->groupEnd();
        }

        // Apply category filter
        if (isset($params['category']) && $params['category'] !== '' && $params['category'] !== null) {
            $builder->where('participants.category', $params['category']);
            $countBuilder->where('participants.category', $params['category']);
        }

        // Apply form status filter
        if (isset($params['form_status']) && $params['form_status'] !== '' && $params['form_status'] !== null) {
            $builder->where('COALESCE(participant_statuses.form_status, 0)', $params['form_status']);
            $countBuilder->where('COALESCE(participant_statuses.form_status, 0)', $params['form_status']);
        }

        // Get total and filtered counts efficiently
        $totalRecords = $countBuilder->countAllResults();
        
        // Get paginated results with ordering
        $results = $builder
            ->orderBy($orderColumn, $order['dir'])
            ->limit($length, $start)
            ->get()
            ->getResult();

        // Format data for DataTable
        $data = [];
        $counter = $start + 1;

        foreach ($results as $row) {
            $data[] = [
                'order_number' => $counter++,
                'account_id' => $row->account_id,
                'participant_details' => [
                    'full_name' => $row->full_name,
                    'picture_url' => $row->picture_url,
                    'email' => $row->email,
                    'nationality' => $row->nationality ?? 'N/A'
                ],
                'submission_status' => $this->getFormStatusBadge($row->form_status),
                'registered_on' => date('M d, Y', strtotime($row->created_at)),
                'actions' => $this->generateActionButtons($row->id)
            ];
        }

        return [
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ];
    }

    /**
     * Generate form status badge HTML
     * 
     * @param int $formStatus
     * @return string
     */
    private function getFormStatusBadge($formStatus)
    {
        switch ($formStatus) {
            case 0:
                return '<div class="submission-status-container">
                    <span class="badge bg-secondary-subtle text-secondary">Not Started</span>
                </div>';
            case 1:
                return '<div class="submission-status-container">
                    <span class="badge bg-warning-subtle text-warning">In Progress</span>
                </div>';
            case 2:
                return '<div class="submission-status-container">
                    <span class="badge bg-success-subtle text-success">Submitted</span>
                </div>';
            default:
                return '<div class="submission-status-container">
                    <span class="badge bg-secondary-subtle text-secondary">Unknown</span>
                </div>';
        }
    }

    /**
     * Generate action buttons HTML
     * 
     * @param int $participantId
     * @return string
     */
    private function generateActionButtons($participantId)
    {
        return '
            <div class="d-flex gap-2">
                <a href="' . base_url('users/participants/view/' . $participantId) . '" class="btn btn-sm btn-soft-primary" title="View">
                    <i class="ri-eye-fill align-bottom"></i>
                </a>
                <a href="' . base_url('participants/edit/' . $participantId) . '" class="btn btn-sm btn-soft-warning" title="Edit">
                    <i class="ri-pencil-fill align-bottom"></i>
                </a>
                <button type="button" class="btn btn-sm btn-soft-danger delete-participant" data-id="' . $participantId . '" title="Delete">
                    <i class="ri-delete-bin-2-line align-bottom"></i>
                </button>
            </div>';
    }
}