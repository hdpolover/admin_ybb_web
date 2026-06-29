<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        helper(['cache_helper']);
    }

    /**
     * Get participant registration statistics by day/week/month
     *
     * @param string $programId Program ID
     * @param string $period Period (day, week, month)
     * @param int $limit Number of periods to fetch
     * @return array
     */    
    public function getParticipantRegistrationStats($programId, $period = 'day', $limit = 30)
    {
        // Create a cache key including parameters
        $cacheKey = "dashboard_registration_stats_{$programId}_{$period}_{$limit}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $result = $cache->get($cacheKey);
        
        if ($result !== null) {
            log_message('info', "DashboardModel::getParticipantRegistrationStats - Returning cached stats for program ID: {$programId}, period: {$period}");
            return $result;
        }
        
        // Cache miss - calculate from database
        log_message('info', "DashboardModel::getParticipantRegistrationStats - Cache miss, calculating stats for program ID: {$programId}, period: {$period}");
        
        $groupFormat = '';
        switch ($period) {
            case 'week':
                $groupFormat = 'YEARWEEK(created_at)';
                $dateFormat = 'YEARWEEK(created_at)';
                $labelFormat = 'CONCAT(DATE_FORMAT(MIN(created_at), "%b %d"), " - ", DATE_FORMAT(MAX(created_at), "%b %d, %Y"))';
                break;
            case 'month':
                $groupFormat = 'YEAR(created_at), MONTH(created_at)';
                $dateFormat = 'DATE_FORMAT(created_at, "%Y-%m")';
                $labelFormat = 'DATE_FORMAT(MIN(created_at), "%b %Y")';
                break;
            default: // day
                $groupFormat = 'DATE(created_at)';
                $dateFormat = 'DATE(created_at)';
                $labelFormat = 'DATE_FORMAT(created_at, "%b %d, %Y")';
                break;
        }
        if ($period === 'week' || $period === 'month') {
            $query = $this->db->query("
                SELECT 
                    {$dateFormat} AS date,
                    {$labelFormat} AS label,
                    COUNT(*) AS total
                FROM participants
                WHERE program_id = ?
                GROUP BY {$groupFormat}
                ORDER BY MIN(created_at) DESC
                LIMIT ?
            ", [$programId, $limit]);
        } else {
            $query = $this->db->query("
                SELECT 
                    {$dateFormat} AS date,
                    {$labelFormat} AS label,
                    COUNT(*) AS total
                FROM participants
                WHERE program_id = ?
                GROUP BY {$groupFormat}
                ORDER BY date DESC
                LIMIT ?
            ", [$programId, $limit]);
        }

        // Reverse to show oldest first for timeline charts. Reverse BEFORE caching
        // so the cache-hit and cache-miss paths return the same (chronological) order.
        $result = array_reverse($query->getResult());

        // Cache for 15 minutes (900 seconds)
        $cache->save($cacheKey, $result, 900);

        return $result;
    }

    /**
     * Get participant statistics by gender
     *
     * @param string $programId Program ID
     * @return array
     */
    public function getGenderDistribution($programId)
    {
        // Create a cache key
        $cacheKey = "dashboard_gender_stats_{$programId}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $result = $cache->get($cacheKey);
        
        if ($result !== null) {
            log_message('info', "DashboardModel::getGenderDistribution - Returning cached stats for program ID: {$programId}");
            return $result;
        }
        
        // Cache miss - calculate from database
        log_message('info', "DashboardModel::getGenderDistribution - Cache miss, calculating stats for program ID: {$programId}");
        
        $query = $this->db->query("
            SELECT 
                CASE
                    WHEN gender IS NULL OR gender = '' THEN 'Not Specified'
                    ELSE gender
                END AS gender,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
            GROUP BY CASE
                WHEN gender IS NULL OR gender = '' THEN 'Not Specified'
                ELSE gender
            END
            ORDER BY total DESC
        ", [$programId]);

        $result = $query->getResult();
        
        // Cache for 15 minutes (900 seconds)
        $cache->save($cacheKey, $result, 900);
        
        return $result;
    }

    /**
     * Get participant statistics by nationality
     *
     * @param string $programId Program ID
     * @param int $limit Top N nationalities to return
     * @return array
     */
    public function getNationalityDistribution($programId, $limit = 10)
    {
        // Create a cache key including parameters
        $cacheKey = "dashboard_nationality_stats_{$programId}_{$limit}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $result = $cache->get($cacheKey);
        
        if ($result !== null) {
            log_message('info', "DashboardModel::getNationalityDistribution - Returning cached stats for program ID: {$programId}, limit: {$limit}");
            return $result;
        }
        
        // Cache miss - calculate from database
        log_message('info', "DashboardModel::getNationalityDistribution - Cache miss, calculating stats for program ID: {$programId}, limit: {$limit}");
        
        $query = $this->db->query("
            SELECT 
                CASE
                    WHEN nationality IS NULL OR nationality = '' THEN 'Not Specified'
                    ELSE nationality
                END AS nationality,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
            GROUP BY CASE
                WHEN nationality IS NULL OR nationality = '' THEN 'Not Specified'
                ELSE nationality
            END
            ORDER BY total DESC
            LIMIT ?
        ", [$programId, $limit]);

        $result = $query->getResult();
        
        // Cache for 15 minutes (900 seconds)
        $cache->save($cacheKey, $result, 900);
        
        return $result;
    }

    /**
     * Get participant statistics by age group
     *
     * @param string $programId Program ID
     * @return array
     */
    public function getAgeDistribution($programId)
    {
        // Create a cache key
        $cacheKey = "dashboard_age_stats_{$programId}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $result = $cache->get($cacheKey);
        
        if ($result !== null) {
            log_message('info', "DashboardModel::getAgeDistribution - Returning cached stats for program ID: {$programId}");
            return $result;
        }
        
        // Cache miss - calculate from database
        log_message('info', "DashboardModel::getAgeDistribution - Cache miss, calculating stats for program ID: {$programId}");
        
        $query = $this->db->query("
            SELECT 
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) < 18 THEN 'Under 18'
                    WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 24 THEN '18-24'
                    WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 25 AND 34 THEN '25-34'
                    WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 35 AND 44 THEN '35-44'
                    WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 45 AND 54 THEN '45-54'
                    WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 55 THEN '55+'
                    ELSE 'Unknown'
                END AS age_group,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ? AND birthdate IS NOT NULL
            GROUP BY age_group
            ORDER BY FIELD(age_group, 'Under 18', '18-24', '25-34', '35-44', '45-54', '55+', 'Unknown')
        ", [$programId]);

        $result = $query->getResult();
        
        // Cache for 15 minutes (900 seconds)
        $cache->save($cacheKey, $result, 900);
        
        return $result;
    }

    /**
     * Get participant statistics by knowledge source (where they heard about the program)
     *
     * @param string $programId Program ID
     * @param int|null $limit Top N sources to return; null returns all
     * @return array
     */
    /**
     * SQL CASE expression that normalizes the free-text knowledge_source column
     * into canonical buckets. The form used a controlled dropdown, but the data
     * still contains case/label variants ("other" vs "Others", "friend" vs
     * "Friends/Family", "website"). Normalizing here gives clean stats without
     * mutating the stored rows.
     *
     * @return string
     */
    private function knowledgeSourceCaseSql(): string
    {
        return "
            CASE
                WHEN knowledge_source IS NULL OR knowledge_source = '' THEN 'Not Specified'
                WHEN LOWER(knowledge_source) IN ('instagram', 'ig') THEN 'Instagram'
                WHEN LOWER(knowledge_source) IN ('facebook', 'fb') THEN 'Facebook'
                WHEN LOWER(knowledge_source) = 'linkedin' THEN 'LinkedIn'
                WHEN LOWER(knowledge_source) IN ('twitter', 'x') THEN 'Twitter'
                WHEN LOWER(knowledge_source) IN ('website', 'web') THEN 'Website'
                WHEN LOWER(knowledge_source) IN ('friend', 'friends', 'friends/family', 'family') THEN 'Friends/Family'
                WHEN LOWER(knowledge_source) IN ('other', 'others') THEN 'Others'
                ELSE knowledge_source
            END
        ";
    }

    public function getKnowledgeSourceDistribution($programId, $limit = null)
    {
        // Create a cache key including parameters
        $cacheKey = "dashboard_knowledge_source_stats_{$programId}_{$limit}";

        // Try to get from cache
        $cache = \Config\Services::cache();
        $result = $cache->get($cacheKey);

        if ($result !== null) {
            log_message('info', "DashboardModel::getKnowledgeSourceDistribution - Returning cached stats for program ID: {$programId}, limit: {$limit}");
            return $result;
        }

        // Cache miss - calculate from database
        log_message('info', "DashboardModel::getKnowledgeSourceDistribution - Cache miss, calculating stats for program ID: {$programId}, limit: {$limit}");

        $sourceCase = $this->knowledgeSourceCaseSql();

        $sql = "
            SELECT
                {$sourceCase} AS source,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
            GROUP BY source
            ORDER BY total DESC
        ";

        $params = [$programId];

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = (int)$limit;
        }

        $query = $this->db->query($sql, $params);
        $result = $query->getResult();

        // Cache for 15 minutes (900 seconds)
        $cache->save($cacheKey, $result, 900);

        return $result;
    }

    /**
     * Get participant knowledge source breakdown cross-tabulated by country
     *
     * @param string $programId Program ID
     * @return array Rows with ->nationality, ->source, ->total
     */
    public function getKnowledgeSourceByCountry($programId)
    {
        // Create a cache key
        $cacheKey = "dashboard_knowledge_source_by_country_{$programId}";

        // Try to get from cache
        $cache = \Config\Services::cache();
        $result = $cache->get($cacheKey);

        if ($result !== null) {
            log_message('info', "DashboardModel::getKnowledgeSourceByCountry - Returning cached stats for program ID: {$programId}");
            return $result;
        }

        // Cache miss - calculate from database
        log_message('info', "DashboardModel::getKnowledgeSourceByCountry - Cache miss, calculating stats for program ID: {$programId}");

        $sourceCase = $this->knowledgeSourceCaseSql();

        $query = $this->db->query("
            SELECT
                CASE
                    WHEN nationality IS NULL OR nationality = '' THEN 'Not Specified'
                    ELSE nationality
                END AS nationality,
                {$sourceCase} AS source,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
            GROUP BY
                CASE WHEN nationality IS NULL OR nationality = '' THEN 'Not Specified' ELSE nationality END,
                {$sourceCase}
            ORDER BY nationality, total DESC
        ", [$programId]);

        $result = $query->getResult();

        // Cache for 15 minutes (900 seconds)
        $cache->save($cacheKey, $result, 900);

        return $result;
    }

    // -------------------------------------------------------------------------
    // Normalization helpers (SQL CASE fragments, no DB mutation)
    // -------------------------------------------------------------------------

    /**
     * SQL CASE expression that normalises the gender enum to display-ready labels.
     * The enum is ('male','female') but legacy rows may be NULL or empty.
     */
    private function genderCaseSql(): string
    {
        return "
            CASE
                WHEN gender IS NULL OR gender = '' THEN 'Not Specified'
                WHEN LOWER(gender) = 'male'   THEN 'Male'
                WHEN LOWER(gender) = 'female' THEN 'Female'
                ELSE 'Not Specified'
            END
        ";
    }

    /**
     * SQL CASE expression that normalises nationality to a trimmed label.
     * Collapses NULL/empty to 'Not Specified'; all other values are TRIM()'d.
     */
    private function nationalityCaseSql(): string
    {
        return "
            CASE
                WHEN nationality IS NULL OR TRIM(nationality) = '' THEN 'Not Specified'
                ELSE TRIM(nationality)
            END
        ";
    }

    /**
     * SQL CASE expression that maps a date-of-birth column to age bands.
     * NULL birthdates → 'Unknown'. Matches the buckets used by getAgeDistribution().
     */
    private function ageBandCaseSql(): string
    {
        return "
            CASE
                WHEN birthdate IS NULL THEN 'Unknown'
                WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) < 18              THEN 'Under 18'
                WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 24 THEN '18-24'
                WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 25 AND 34 THEN '25-34'
                WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 35 AND 44 THEN '35-44'
                WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 45 AND 54 THEN '45-54'
                WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 55             THEN '55+'
                ELSE 'Unknown'
            END
        ";
    }

    // -------------------------------------------------------------------------
    // Gender analytics methods
    // -------------------------------------------------------------------------

    /**
     * Gender distribution with normalised display labels (Male / Female / Not Specified).
     */
    public function getGenderDistributionNormalized($programId)
    {
        $cacheKey = "dashboard_gender_normalized_{$programId}";
        $cache    = \Config\Services::cache();
        $result   = $cache->get($cacheKey);

        if ($result !== null) {
            return $result;
        }

        $genderCase = $this->genderCaseSql();

        $query = $this->db->query("
            SELECT
                {$genderCase} AS gender,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
            GROUP BY {$genderCase}
            ORDER BY total DESC
        ", [$programId]);

        $result = $query->getResult();
        $cache->save($cacheKey, $result, 900);

        return $result;
    }

    /**
     * Cross-tab: Gender × Nationality.
     * Rows: ->gender, ->nationality, ->total
     */
    public function getGenderByNationality($programId)
    {
        $cacheKey = "dashboard_gender_by_nationality_{$programId}";
        $cache    = \Config\Services::cache();
        $result   = $cache->get($cacheKey);

        if ($result !== null) {
            return $result;
        }

        $genderCase      = $this->genderCaseSql();
        $nationalityCase = $this->nationalityCaseSql();

        $query = $this->db->query("
            SELECT
                {$genderCase}      AS gender,
                {$nationalityCase} AS nationality,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
            GROUP BY
                {$genderCase},
                {$nationalityCase}
            ORDER BY gender, total DESC
        ", [$programId]);

        $result = $query->getResult();
        $cache->save($cacheKey, $result, 900);

        return $result;
    }

    /**
     * Cross-tab: Gender × Age band.
     * Rows: ->gender, ->age_group, ->total
     */
    public function getGenderByAge($programId)
    {
        $cacheKey = "dashboard_gender_by_age_{$programId}";
        $cache    = \Config\Services::cache();
        $result   = $cache->get($cacheKey);

        if ($result !== null) {
            return $result;
        }

        $genderCase = $this->genderCaseSql();
        $ageCase    = $this->ageBandCaseSql();

        $query = $this->db->query("
            SELECT
                {$genderCase} AS gender,
                {$ageCase}    AS age_group,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
            GROUP BY
                {$genderCase},
                {$ageCase}
            ORDER BY gender, total DESC
        ", [$programId]);

        $result = $query->getResult();
        $cache->save($cacheKey, $result, 900);

        return $result;
    }

    // -------------------------------------------------------------------------
    // Nationality analytics methods
    // -------------------------------------------------------------------------

    /**
     * Cross-tab: Nationality × Knowledge Source.
     * Rows: ->nationality, ->source, ->total
     */
    public function getNationalityBySource($programId)
    {
        $cacheKey = "dashboard_nationality_by_source_{$programId}";
        $cache    = \Config\Services::cache();
        $result   = $cache->get($cacheKey);

        if ($result !== null) {
            return $result;
        }

        $nationalityCase = $this->nationalityCaseSql();
        $sourceCase      = $this->knowledgeSourceCaseSql();

        $query = $this->db->query("
            SELECT
                {$nationalityCase} AS nationality,
                {$sourceCase}      AS source,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
            GROUP BY
                {$nationalityCase},
                {$sourceCase}
            ORDER BY nationality, total DESC
        ", [$programId]);

        $result = $query->getResult();
        $cache->save($cacheKey, $result, 900);

        return $result;
    }

    /**
     * Cross-tab: Nationality × Gender.
     * Rows: ->nationality, ->gender, ->total
     */
    public function getNationalityByGender($programId)
    {
        $cacheKey = "dashboard_nationality_by_gender_{$programId}";
        $cache    = \Config\Services::cache();
        $result   = $cache->get($cacheKey);

        if ($result !== null) {
            return $result;
        }

        $nationalityCase = $this->nationalityCaseSql();
        $genderCase      = $this->genderCaseSql();

        $query = $this->db->query("
            SELECT
                {$nationalityCase} AS nationality,
                {$genderCase}      AS gender,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
            GROUP BY
                {$nationalityCase},
                {$genderCase}
            ORDER BY nationality, total DESC
        ", [$programId]);

        $result = $query->getResult();
        $cache->save($cacheKey, $result, 900);

        return $result;
    }

    // -------------------------------------------------------------------------
    // Age analytics methods
    // -------------------------------------------------------------------------

    /**
     * Cross-tab: Age band × Gender.
     * Rows: ->age_group, ->gender, ->total
     */
    public function getAgeByGender($programId)
    {
        $cacheKey = "dashboard_age_by_gender_{$programId}";
        $cache    = \Config\Services::cache();
        $result   = $cache->get($cacheKey);

        if ($result !== null) {
            return $result;
        }

        $ageCase    = $this->ageBandCaseSql();
        $genderCase = $this->genderCaseSql();

        $query = $this->db->query("
            SELECT
                {$ageCase}    AS age_group,
                {$genderCase} AS gender,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
            GROUP BY
                {$ageCase},
                {$genderCase}
            ORDER BY
                FIELD({$ageCase}, 'Under 18','18-24','25-34','35-44','45-54','55+','Unknown'),
                total DESC
        ", [$programId]);

        $result = $query->getResult();
        $cache->save($cacheKey, $result, 900);

        return $result;
    }

    /**
     * Cross-tab: Age band × Nationality (all nationalities).
     * Rows: ->age_group, ->nationality, ->total
     */
    public function getAgeByNationality($programId)
    {
        $cacheKey = "dashboard_age_by_nationality_{$programId}";
        $cache    = \Config\Services::cache();
        $result   = $cache->get($cacheKey);

        if ($result !== null) {
            return $result;
        }

        $ageCase         = $this->ageBandCaseSql();
        $nationalityCase = $this->nationalityCaseSql();

        $query = $this->db->query("
            SELECT
                {$ageCase}         AS age_group,
                {$nationalityCase} AS nationality,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
            GROUP BY
                {$ageCase},
                {$nationalityCase}
            ORDER BY
                FIELD({$ageCase}, 'Under 18','18-24','25-34','35-44','45-54','55+','Unknown'),
                total DESC
        ", [$programId]);

        $result = $query->getResult();
        $cache->save($cacheKey, $result, 900);

        return $result;
    }

    // -------------------------------------------------------------------------
    // Age analytics — full distribution (includes NULL birthdate → Unknown)
    // -------------------------------------------------------------------------

    /**
     * Age-band distribution WITHOUT the birthdate IS NOT NULL filter.
     * NULL/invalid birthdates are mapped to 'Unknown' via ageBandCaseSql().
     * Use this for the Age Analytics page so that grandTotal matches the
     * program-wide participant count shown on every other analytics page.
     * Do NOT replace getAgeDistribution() — it is used by the dashboard index modal.
     *
     * @param string $programId
     * @return array  Rows with ->age_group and ->total, ordered by canonical band order.
     */
    public function getAgeDistributionFull($programId)
    {
        $cacheKey = "dashboard_age_stats_full_{$programId}";
        $cache    = \Config\Services::cache();
        $result   = $cache->get($cacheKey);

        if ($result !== null) {
            log_message('info', "DashboardModel::getAgeDistributionFull - Returning cached stats for program ID: {$programId}");
            return $result;
        }

        log_message('info', "DashboardModel::getAgeDistributionFull - Cache miss, calculating for program ID: {$programId}");

        $ageCase = $this->ageBandCaseSql();

        $query = $this->db->query("
            SELECT
                {$ageCase} AS age_group,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
            GROUP BY {$ageCase}
            ORDER BY FIELD({$ageCase}, 'Under 18','18-24','25-34','35-44','45-54','55+','Unknown')
        ", [$programId]);

        $result = $query->getResult();
        $cache->save($cacheKey, $result, 900);

        return $result;
    }

    // -------------------------------------------------------------------------
    // Registrations analytics methods
    // -------------------------------------------------------------------------

    /**
     * Registration summary stats for a program:
     * first date, last date, total active days, avg per day, peak date + count.
     *
     * @param string $programId
     * @return \stdClass
     */
    public function getRegistrationSummary($programId)
    {
        $cacheKey = "dashboard_registration_summary_{$programId}";
        $cache    = \Config\Services::cache();
        $result   = $cache->get($cacheKey);

        if ($result !== null) {
            return $result;
        }

        $summary = new \stdClass();

        // Aggregate: first/last date, unique days with registrations
        $q = $this->db->query("
            SELECT
                DATE_FORMAT(MIN(created_at), '%b %d, %Y') AS first_date,
                DATE_FORMAT(MAX(created_at), '%b %d, %Y') AS last_date,
                COUNT(DISTINCT DATE(created_at)) AS total_days,
                COUNT(*) AS total
            FROM participants
            WHERE program_id = ?
        ", [$programId]);
        $row = $q->getRow();

        $summary->first_date  = $row->first_date  ?? 'N/A';
        $summary->last_date   = $row->last_date   ?? 'N/A';
        $summary->total_days  = (int)($row->total_days ?? 0);
        $summary->total       = (int)($row->total ?? 0);
        $summary->avg_per_day = $summary->total_days > 0
            ? round($summary->total / $summary->total_days, 1) : 0;

        // Peak day
        $q2 = $this->db->query("
            SELECT DATE_FORMAT(DATE(created_at), '%b %d, %Y') AS peak_date, COUNT(*) AS peak_count
            FROM participants
            WHERE program_id = ?
            GROUP BY DATE(created_at)
            ORDER BY peak_count DESC
            LIMIT 1
        ", [$programId]);
        $peak = $q2->getRow();

        $summary->peak_date  = $peak ? $peak->peak_date  : 'N/A';
        $summary->peak_count = $peak ? (int)$peak->peak_count : 0;

        $cache->save($cacheKey, $summary, 900);

        return $summary;
    }

    // -------------------------------------------------------------------------
    // Ambassador analytics methods
    // -------------------------------------------------------------------------

    /**
     * Full ambassador referral list (no LIMIT) for the leaderboard page.
     * Rows: ->ambassador_name, ->total_referrals
     *
     * @param string $programId
     * @return array
     */
    public function getAmbassadorReferralsFull($programId)
    {
        $cacheKey = "dashboard_ambassador_stats_{$programId}_full";
        $cache    = \Config\Services::cache();
        $result   = $cache->get($cacheKey);

        if ($result !== null) {
            log_message('info', "DashboardModel::getAmbassadorReferralsFull - Returning cached stats for program ID: {$programId}");
            return $result;
        }

        log_message('info', "DashboardModel::getAmbassadorReferralsFull - Cache miss, calculating for program ID: {$programId}");

        $query = $this->db->query("
            SELECT
                a.name AS ambassador_name,
                COUNT(p.id) AS total_referrals
            FROM ambassadors a
            LEFT JOIN participants p ON a.ref_code = p.ref_code_ambassador AND p.program_id = ?
            WHERE a.program_id = ?
            GROUP BY a.id, a.name
            ORDER BY total_referrals DESC
        ", [$programId, $programId]);

        $result = $query->getResult();
        $cache->save($cacheKey, $result, 900);

        return $result;
    }

    // -------------------------------------------------------------------------

    /**
     * Get ambassador referral statistics
     *
     * @param string $programId Program ID
     * @param int $limit Top N ambassadors to return
     * @return array
     */
    public function getAmbassadorReferrals($programId, $limit = 10)
    {
        // Create a cache key including parameters
        $cacheKey = "dashboard_ambassador_stats_{$programId}_{$limit}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $result = $cache->get($cacheKey);
        
        if ($result !== null) {
            log_message('info', "DashboardModel::getAmbassadorReferrals - Returning cached stats for program ID: {$programId}, limit: {$limit}");
            return $result;
        }
        
        // Cache miss - calculate from database
        log_message('info', "DashboardModel::getAmbassadorReferrals - Cache miss, calculating stats for program ID: {$programId}, limit: {$limit}");
        
        $query = $this->db->query("
            SELECT 
                a.name AS ambassador_name,
                COUNT(p.id) AS total_referrals
            FROM ambassadors a
            LEFT JOIN participants p ON a.ref_code = p.ref_code_ambassador AND p.program_id = ?
            WHERE a.program_id = ?
            GROUP BY a.id
            ORDER BY total_referrals DESC
            LIMIT ?
        ", [$programId, $programId, $limit]);

        $result = $query->getResult();
        
        // Cache for 15 minutes (900 seconds)
        $cache->save($cacheKey, $result, 900);
        
        return $result;
    }

    /**
     * Get summary statistics for a program
     * 
     * @param string $programId Program ID
     * @return object
     */
    public function getProgramSummary($programId)
    {
        // Create a cache key
        $cacheKey = "dashboard_summary_{$programId}";
        
        // Try to get from cache
        $cache = \Config\Services::cache();
        $stats = $cache->get($cacheKey);
        
        if ($stats !== null) {
            log_message('info', "DashboardModel::getProgramSummary - Returning cached summary for program ID: {$programId}");
            return $stats;
        }
        
        // Cache miss - calculate from database
        log_message('info', "DashboardModel::getProgramSummary - Cache miss, calculating summary for program ID: {$programId}");
        
        $stats = new \stdClass();

        // Total participants
        $query = $this->db->query("
            SELECT COUNT(*) as total FROM participants WHERE program_id = ?
        ", [$programId]);
        $stats->total_participants = $query->getRow()->total;

        // Participants added today
        $query = $this->db->query("
            SELECT COUNT(*) as total FROM participants 
            WHERE program_id = ? AND DATE(created_at) = CURDATE()
        ", [$programId]);
        $stats->participants_today = $query->getRow()->total;

        // Total ambassadors
        $query = $this->db->query("
            SELECT COUNT(*) as total FROM ambassadors WHERE program_id = ?
        ", [$programId]);
        $stats->total_ambassadors = $query->getRow()->total;

        // Total with referral codes
        $query = $this->db->query("
            SELECT COUNT(*) as total FROM participants 
            WHERE program_id = ? AND ref_code_ambassador IS NOT NULL AND ref_code_ambassador != ''
        ", [$programId]);
        $stats->total_referred = $query->getRow()->total;

        // Referral percentage
        $stats->referral_percentage = ($stats->total_participants > 0)
            ? round(($stats->total_referred / $stats->total_participants) * 100, 1)
            : 0;
            
        // Cache for 15 minutes (900 seconds)
        $cache->save($cacheKey, $stats, 900);

        return $stats;
    }
}
