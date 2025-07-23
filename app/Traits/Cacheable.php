<?php

namespace App\Traits;

use App\Services\RedisCacheService;

/**
 * Cacheable Trait
 * 
 * Provides caching functionality to API controllers
 */
trait Cacheable
{
    protected RedisCacheService $cacheService;
    
    /**
     * Initialize cache service
     */
    protected function initCache(): void
    {
        if (!isset($this->cacheService)) {
            $this->cacheService = new RedisCacheService();
        }
    }

    /**
     * Cache response data with automatic endpoint detection
     * 
     * @param callable $callback Function that returns data to cache
     * @param array $parameters Additional parameters for cache key
     * @param string|null $userId User ID for user-specific caching
     * @param int|null $customTtl Custom TTL override
     * @return mixed
     */
    protected function cacheResponse(callable $callback, array $parameters = [], ?string $userId = null, ?int $customTtl = null)
    {
        $this->initCache();
        
        // Skip cache if debugging
        if ($this->cacheService->shouldBypassCache()) {
            return $callback();
        }
        
        // Get current endpoint from request
        $endpoint = $this->request->getUri()->getPath();
        
        // Generate cache key
        $key = $this->cacheService->generateKey($endpoint, $parameters, $userId);
        
        // Try to get from cache
        $cached = $this->cacheService->get($key);
        if ($cached !== null) {
            return $cached;
        }
        
        // Execute callback and cache result
        $data = $callback();
        if ($data !== null) {
            if ($customTtl) {
                $this->cacheService->set($key, $data, $customTtl);
            } else {
                $this->cacheService->set($key, $data);
            }
        }
        
        return $data;
    }

    /**
     * Cache GET request with automatic parameter detection
     */
    protected function cacheGet(callable $callback, ?string $userId = null): mixed
    {
        $this->initCache();
        
        // Get all URL parameters
        $parameters = $this->request->getGet();
        
        // Add route parameters (like ID from URL)
        $segments = $this->request->getUri()->getSegments();
        if (!empty($segments)) {
            $parameters['segments'] = $segments;
        }
        
        return $this->cacheResponse($callback, $parameters, $userId);
    }

    /**
     * Cache response specifically for user data
     */
    protected function cacheUserData(callable $callback, string $userId): mixed
    {
        return $this->cacheResponse($callback, [], $userId);
    }

    /**
     * Cache response for program-specific data
     */
    protected function cacheProgramData(callable $callback, string $programId): mixed
    {
        return $this->cacheResponse($callback, ['program_id' => $programId]);
    }

    /**
     * Cache response for participant-specific data
     */
    protected function cacheParticipantData(callable $callback, string $participantId, ?string $userId = null): mixed
    {
        return $this->cacheResponse($callback, ['participant_id' => $participantId], $userId);
    }

    /**
     * Invalidate cache after data modification
     */
    protected function invalidateUserCache(string $userId): bool
    {
        $this->initCache();
        return $this->cacheService->invalidateUserSpecificCache($userId);
    }

    /**
     * Invalidate cache after program modification
     */
    protected function invalidateProgramCache(string $programId): bool
    {
        $this->initCache();
        return $this->cacheService->invalidateProgramCache($programId);
    }

    /**
     * Invalidate cache after payment modification
     */
    protected function invalidatePaymentCache(string $participantId): bool
    {
        $this->initCache();
        return $this->cacheService->invalidatePaymentCache($participantId);
    }

    /**
     * Invalidate landing page cache
     */
    protected function invalidateLandingCache(): bool
    {
        $this->initCache();
        return $this->cacheService->invalidateLandingPageCache();
    }

    /**
     * Check if current request should use cache
     */
    protected function shouldUseCache(): bool
    {
        $this->initCache();
        
        // Don't cache non-GET requests
        if ($this->request->getMethod() !== 'GET') {
            return false;
        }
        
        // Don't cache if bypass parameter is set
        if ($this->cacheService->shouldBypassCache()) {
            return false;
        }
        
        return true;
    }

    /**
     * Get cache statistics (for debugging)
     */
    protected function getCacheStats(): array
    {
        $this->initCache();
        return $this->cacheService->getStats();
    }
}
