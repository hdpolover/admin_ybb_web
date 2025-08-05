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

            // set payments
            $paymentModel = new PaymentModel();

            $payments = $paymentModel->getPayments($row['id']);

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
        // Create a unique cache key based on search parameters
        $cacheKey = "participant_search_" . md5(json_encode($searchParams) . "_limit_{$limit}_page_{$page}_includes_" . json_encode($includeOptions));
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $results = $cache->get($cacheKey);
        
        if ($results !== null) {
            // Return cached results
            return $results;
        }
        
        // Cache miss - perform the search
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
        
        // Cache the search results for 30 minutes (1800 seconds)
        // For search results, we use a shorter cache time since the data might change
        $cache->save($cacheKey, $searchResults, 1800);
        
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
     */
    private function getParticipantPayments($participantId)
    {
        $paymentModel = new PaymentModel();
        return $paymentModel->getPayments($participantId);
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
}