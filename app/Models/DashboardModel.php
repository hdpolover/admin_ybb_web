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

        $result = $query->getResult();
        
        // Cache for 15 minutes (900 seconds)
        $cache->save($cacheKey, $result, 900);
        
        // Reverse to show oldest first for timeline charts
        return array_reverse($result);
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
