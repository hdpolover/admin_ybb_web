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
    private bool $enableFallback;
    private bool $logFallbacks;

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
        $this->domain = $this->sanitizeDomain($_SERVER['HTTP_HOST'] ?? 'localhost');
        
        // Load cache configuration
        $cacheConfig = config('Cache');
        $this->enableFallback = $cacheConfig->enableFallback ?? true;
        $this->logFallbacks = $cacheConfig->logFallbacks ?? true;
    }

    /**
     * Sanitize domain name for use in cache keys
     * Remove or replace reserved characters
     */
    private function sanitizeDomain(string $domain): string
    {
        // Remove port number and replace reserved characters
        $domain = preg_replace('/:\d+$/', '', $domain); // Remove port
        $domain = preg_replace('/[{}()\\/\\@:]/', '_', $domain); // Replace reserved chars
        $domain = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $domain); // Keep only safe chars
        return $domain;
    }

    /**
     * Sanitize cache key to remove reserved characters
     * CodeIgniter cache doesn't allow: {}()/\@:
     */
    private function sanitizeKey(string $key): string
    {
        // Replace reserved characters with underscores
        $key = preg_replace('/[{}()\\/\\@:]/', '_', $key);
        // Ensure no double underscores
        $key = preg_replace('/_+/', '_', $key);
        // Remove leading/trailing underscores
        $key = trim($key, '_');
        return $key;
    }

    /**
     * Generate cache key based on endpoint and parameters
     * Format: {domain}_{endpoint}_{parameters}_v{version}
     */
    public function generateKey(string $endpoint, array $parameters = [], ?string $userId = null): string
    {
        // Clean endpoint (remove leading slash and normalize)
        $endpoint = ltrim($endpoint, '/');
        $endpoint = str_replace('/', '_', $endpoint);
        
        // Add user context if provided (for user-specific caching)
        if ($userId) {
            $parameters['user_id'] = $userId;
        }
        
        // Sort parameters for consistent key generation
        ksort($parameters);
        $paramString = empty($parameters) ? '' : '_' . md5(serialize($parameters));
        
        // Create initial key
        $key = sprintf('%s_%s%s_v%d', $this->domain, $endpoint, $paramString, $this->version);
        
        // Sanitize the final key
        return $this->sanitizeKey($key);
    }

    /**
     * Get TTL based on endpoint priority
     */
    public function getTtl(string $endpoint): int
    {
        $endpoint = ltrim($endpoint, '/');
        $endpoint = str_replace('/', '_', $endpoint); // Convert to underscore format
        
        // Check high priority endpoints
        foreach (self::HIGH_PRIORITY_ENDPOINTS as $pattern) {
            $pattern = str_replace('/', '_', $pattern); // Convert pattern to underscore format
            if (strpos($endpoint, $pattern) !== false) {
                return self::HIGH_PRIORITY_TTL;
            }
        }
        
        // Check medium priority endpoints
        foreach (self::MEDIUM_PRIORITY_ENDPOINTS as $pattern) {
            $pattern = str_replace('/', '_', $pattern);
            if (strpos($endpoint, $pattern) !== false) {
                return self::MEDIUM_PRIORITY_TTL;
            }
        }
        
        // Check low priority endpoints
        foreach (self::LOW_PRIORITY_ENDPOINTS as $pattern) {
            $pattern = str_replace('/', '_', $pattern);
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
                $keyParts = explode('_', $key);
                if (count($keyParts) >= 2) {
                    // Remove domain part and version part, get endpoint
                    $endpointParts = array_slice($keyParts, 1, -1); // Remove first (domain) and last (version)
                    if (!empty($endpointParts)) {
                        $endpoint = implode('_', $endpointParts);
                        // Remove hash part if present
                        $endpoint = preg_replace('/_[a-f0-9]{32}$/', '', $endpoint);
                        $ttl = $this->getTtl($endpoint);
                    } else {
                        $ttl = self::LOW_PRIORITY_TTL;
                    }
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
            $this->logger->info("Cache PATTERN DELETE: {$pattern}");

            $handler = $this->cache;

            // Use reflection to access protected redis instance in RedisHandler
            // We need to check if the class name contains RedisHandler as it might be namespaced differently
            if (strpos(get_class($handler), 'RedisHandler') !== false) {
                try {
                    $refProperty = new \ReflectionProperty($handler, 'redis');
                    $refProperty->setAccessible(true);
                    $redis = $refProperty->getValue($handler);
                    
                    if ($redis) {
                        // Get prefix from config
                        $prefix = '';
                        $config = config('Cache');
                        if ($config) {
                             $prefix = $config->prefix ?? '';
                        }
                        
                        $fullPattern = $prefix . $pattern . '*';
                        
                        // Use keys command (caution using in prod with large datasets)
                        $keys = $redis->keys($fullPattern);
                        if (!empty($keys)) {
                            $redis->del($keys);
                            $this->logger->info("Deleted " . count($keys) . " keys matching {$fullPattern}");
                        }
                        return true;
                    }
                } catch (\Exception $ex) {
                    $this->logger->warning("Reflection access to Redis failed: " . $ex->getMessage());
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Cache PATTERN DELETE error for pattern {$pattern}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache or retrieve data with automatic key generation
     */
    public function remember(string $endpoint, array $parameters = [], ?callable $callback = null, ?string $userId = null)
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
            "participants_user_{$userId}",
            "payments_participants_{$userId}",
            "api_user_current",
        ];
        
        foreach ($patterns as $pattern) {
            $this->deleteByPattern($pattern);
        }
        
        return true;
    }

    public function invalidateProgramCache(string $programId): bool
    {
        $patterns = [
            "programs_category",
            "programs_{$programId}",
            "program-payments_program_{$programId}",
            "payment-methods_program_{$programId}",
            "documents_program_{$programId}",
            "all_categories_with_programs",
        ];
        
        foreach ($patterns as $pattern) {
            $this->deleteByPattern($pattern);
        }
        
        return true;
    }

    public function invalidatePaymentCache(string $participantId): bool
    {
        $patterns = [
            "payments_participants_{$participantId}",
            "payments_participant",
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
            "all_categories_with_programs",
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

    /**
     * Check if cache service is available
     * Tests basic cache connectivity
     */
    public function isCacheAvailable(): bool
    {
        if (!$this->enableFallback) {
            return true; // If fallback is disabled, always try to use cache
        }
        
        try {
            // Try to perform a simple cache operation
            $testKey = 'cache_availability_test_' . time();
            $testValue = 'test_value';
            
            // Try to set and immediately get a test value
            $saveResult = $this->cache->save($testKey, $testValue, 10);
            if (!$saveResult) {
                if ($this->logFallbacks) {
                    $this->logger->warning("Cache availability test failed: save operation returned false");
                }
                return false;
            }
            
            $getValue = $this->cache->get($testKey);
            $this->cache->delete($testKey); // Clean up
            
            $isAvailable = $getValue === $testValue;
            if (!$isAvailable && $this->logFallbacks) {
                $this->logger->warning("Cache availability test failed: retrieved value doesn't match saved value");
            }
            
            return $isAvailable;
        } catch (\Exception $e) {
            if ($this->logFallbacks) {
                $this->logger->error("Cache availability check failed: " . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Get cached data with fallback handling
     * Returns null if cache fails, allowing caller to handle fallback
     */
    public function getWithFallback(string $key)
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
            if ($this->logFallbacks) {
                $this->logger->warning("Cache GET failed for key {$key}, falling back to direct API call: " . $e->getMessage());
            }
            return null; // Return null to trigger fallback
        }
    }

    /**
     * Store data in cache with fallback handling
     * Silently fails if cache is unavailable, allowing operation to continue
     */
    public function setWithFallback(string $key, $data, ?int $ttl = null): bool
    {
        try {
            if ($ttl === null) {
                // Extract endpoint from key to determine TTL
                $keyParts = explode('_', $key);
                if (count($keyParts) >= 2) {
                    // Remove domain part and version part, get endpoint
                    $endpointParts = array_slice($keyParts, 1, -1); // Remove first (domain) and last (version)
                    if (!empty($endpointParts)) {
                        $endpoint = implode('_', $endpointParts);
                        // Remove hash part if present
                        $endpoint = preg_replace('/_[a-f0-9]{32}$/', '', $endpoint);
                        $ttl = $this->getTtl($endpoint);
                    } else {
                        $ttl = self::LOW_PRIORITY_TTL;
                    }
                } else {
                    $ttl = self::LOW_PRIORITY_TTL;
                }
            }
            
            $result = $this->cache->save($key, $data, $ttl);
            if ($result) {
                $this->logger->info("Cache SET successful for key: {$key}, TTL: {$ttl}s");
            } else {
                if ($this->logFallbacks) {
                    $this->logger->warning("Cache SET failed for key: {$key}, continuing without cache");
                }
            }
            return $result;
        } catch (\Exception $e) {
            if ($this->logFallbacks) {
                $this->logger->warning("Cache SET failed for key {$key}, continuing without cache: " . $e->getMessage());
            }
            return false; // Fail silently, don't break the API call
        }
    }
}
