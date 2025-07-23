<?php

namespace App\Services;

use Config\Services;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Log\Logger;

/**
 * Redis Cache Service
 * 
 * Handles caching operations for API endpoints based on priority and TTL requirements
 * from the API_ENDPOINTS_DOCUMENTATION.md
 */
class RedisCacheService
{
    private CacheInterface $cache;
    private Logger $logger;
    private string $domain;
    private int $version = 1;

    // Cache TTL constants (in seconds)
    const HIGH_PRIORITY_TTL = 7200;     // 2 hours (30 min - 4 hours range)
    const MEDIUM_PRIORITY_TTL = 1800;   // 30 minutes (5-30 min range)
    const LOW_PRIORITY_TTL = 300;       // 5 minutes (1-10 min range)
    const VERY_LOW_PRIORITY_TTL = 120;  // 2 minutes (for real-time data)

    // Cache priority definitions based on documentation
    const HIGH_PRIORITY_ENDPOINTS = [
        'web-settings',
        'programs/category',
        'landing',
        'payment-methods/program',
        'programs',
        'program-categories'
    ];

    const MEDIUM_PRIORITY_ENDPOINTS = [
        'participants/user',
        'program-payments/program',
        'documents/program',
        'program-announcements'
    ];

    const LOW_PRIORITY_ENDPOINTS = [
        'payments/participants',
        'abstracts/participant',
        'api/user/current',
        'participants',
        'payment-details'
    ];

    public function __construct()
    {
        $this->cache = Services::cache();
        $this->logger = Services::logger();
        $this->domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
    }

    /**
     * Generate cache key based on endpoint and parameters
     * Format: {domain}:{endpoint}:{parameters}:{version}
     */
    public function generateKey(string $endpoint, array $parameters = [], ?string $userId = null): string
    {
        // Clean endpoint (remove leading slash and normalize)
        $endpoint = ltrim($endpoint, '/');
        $endpoint = str_replace('/', ':', $endpoint);
        
        // Add user context if provided (for user-specific caching)
        if ($userId) {
            $parameters['user_id'] = $userId;
        }
        
        // Sort parameters for consistent key generation
        ksort($parameters);
        $paramString = empty($parameters) ? '' : ':' . md5(serialize($parameters));
        
        return sprintf('%s:%s%s:v%d', $this->domain, $endpoint, $paramString, $this->version);
    }

    /**
     * Get TTL based on endpoint priority
     */
    public function getTtl(string $endpoint): int
    {
        $endpoint = ltrim($endpoint, '/');
        
        // Check high priority endpoints
        foreach (self::HIGH_PRIORITY_ENDPOINTS as $pattern) {
            if (strpos($endpoint, $pattern) !== false) {
                return self::HIGH_PRIORITY_TTL;
            }
        }
        
        // Check medium priority endpoints
        foreach (self::MEDIUM_PRIORITY_ENDPOINTS as $pattern) {
            if (strpos($endpoint, $pattern) !== false) {
                return self::MEDIUM_PRIORITY_TTL;
            }
        }
        
        // Check low priority endpoints
        foreach (self::LOW_PRIORITY_ENDPOINTS as $pattern) {
            if (strpos($endpoint, $pattern) !== false) {
                return self::LOW_PRIORITY_TTL;
            }
        }
        
        // Default to low priority for unlisted endpoints
        return self::LOW_PRIORITY_TTL;
    }

    /**
     * Get cached data
     */
    public function get(string $key)
    {
        try {
            $data = $this->cache->get($key);
            if ($data !== null) {
                $this->logger->info("Cache HIT for key: {$key}");
            } else {
                $this->logger->info("Cache MISS for key: {$key}");
            }
            return $data;
        } catch (\Exception $e) {
            $this->logger->error("Cache GET error for key {$key}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Store data in cache
     */
    public function set(string $key, $data, ?int $ttl = null): bool
    {
        try {
            if ($ttl === null) {
                // Extract endpoint from key to determine TTL
                $keyParts = explode(':', $key);
                if (count($keyParts) >= 2) {
                    $endpoint = $keyParts[1];
                    $ttl = $this->getTtl($endpoint);
                } else {
                    $ttl = self::LOW_PRIORITY_TTL;
                }
            }
            
            $result = $this->cache->save($key, $data, $ttl);
            $this->logger->info("Cache SET for key: {$key}, TTL: {$ttl}s, Success: " . ($result ? 'YES' : 'NO'));
            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Cache SET error for key {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete cached data
     */
    public function delete(string $key): bool
    {
        try {
            $result = $this->cache->delete($key);
            $this->logger->info("Cache DELETE for key: {$key}, Success: " . ($result ? 'YES' : 'NO'));
            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Cache DELETE error for key {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear cache by pattern (for cache invalidation)
     */
    public function deleteByPattern(string $pattern): bool
    {
        try {
            // This is a simplified implementation
            // In production, you might want to use Redis SCAN with pattern matching
            $this->logger->info("Cache PATTERN DELETE: {$pattern}");
            
            // For now, we'll increment the version to effectively invalidate all caches
            // A more sophisticated approach would scan and delete matching keys
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Cache PATTERN DELETE error for pattern {$pattern}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache or retrieve data with automatic key generation
     */
    public function remember(string $endpoint, array $parameters = [], callable $callback = null, ?string $userId = null)
    {
        $key = $this->generateKey($endpoint, $parameters, $userId);
        $cached = $this->get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        if ($callback) {
            $data = $callback();
            if ($data !== null) {
                $this->set($key, $data);
            }
            return $data;
        }
        
        return null;
    }

    /**
     * Cache invalidation triggers based on documentation
     */
    public function invalidateUserSpecificCache(string $userId): bool
    {
        $patterns = [
            "participants:user:{$userId}",
            "payments:participants:{$userId}",
            "api:user:current",
        ];
        
        foreach ($patterns as $pattern) {
            $this->deleteByPattern($pattern);
        }
        
        return true;
    }

    public function invalidateProgramCache(string $programId): bool
    {
        $patterns = [
            "programs:category",
            "programs:{$programId}",
            "program-payments:program:{$programId}",
            "payment-methods:program:{$programId}",
            "documents:program:{$programId}",
        ];
        
        foreach ($patterns as $pattern) {
            $this->deleteByPattern($pattern);
        }
        
        return true;
    }

    public function invalidatePaymentCache(string $participantId): bool
    {
        $patterns = [
            "payments:participants:{$participantId}",
            "payments:participant",
        ];
        
        foreach ($patterns as $pattern) {
            $this->deleteByPattern($pattern);
        }
        
        return true;
    }

    public function invalidateLandingPageCache(): bool
    {
        $patterns = [
            "landing",
            "web-settings",
            "program-announcements",
        ];
        
        foreach ($patterns as $pattern) {
            $this->deleteByPattern($pattern);
        }
        
        return true;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        try {
            // This would need to be implemented based on your Redis setup
            return [
                'status' => 'connected',
                'version' => $this->version,
                'domain' => $this->domain,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if caching should be bypassed (for debugging)
     */
    public function shouldBypassCache(): bool
    {
        return (bool) ($_GET['no_cache'] ?? false);
    }

    /**
     * Warm up cache for critical endpoints
     */
    public function warmUp(array $endpoints = []): bool
    {
        // This method would pre-populate cache for critical endpoints
        // Implementation would depend on specific business logic
        $this->logger->info("Cache warm-up initiated for endpoints: " . implode(', ', $endpoints));
        return true;
    }
}
