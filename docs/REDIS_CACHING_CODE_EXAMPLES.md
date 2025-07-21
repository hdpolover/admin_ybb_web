# Redis Caching Code Examples

This document provides code examples for implementing Redis caching in new features. Use these patterns to maintain consistency across the application.

## Table of Contents

1. [Basic Cache Implementation](#basic-cache-implementation)
2. [Cache Invalidation](#cache-invalidation)
3. [Controller Implementation](#controller-implementation)
4. [Model Hooks](#model-hooks)
5. [Performance Tracking](#performance-tracking)

## Basic Cache Implementation

### Simple Method Caching

Use this pattern for caching method results with a simple key:

```php
public function getCountryStatistics($programId)
{
    // Create a cache key
    $cacheKey = "country_stats_{$programId}";
    
    // Try to get from cache
    $cache = \Config\Services::cache();
    $stats = $cache->get($cacheKey);
    
    if ($stats !== null) {
        log_message('info', "Returning cached country statistics for program ID: {$programId}");
        return $stats;
    }
    
    // Cache miss - calculate from database
    log_message('info', "Cache miss, calculating country statistics for program ID: {$programId}");
    
    // Your complex database query or calculation here
    $stats = $this->calculateCountryStatistics($programId);
    
    // Store in cache for 15 minutes (900 seconds)
    $cache->save($cacheKey, $stats, 900);
    
    return $stats;
}
```

### Caching with Complex Keys

For cases where the cache key depends on multiple parameters:

```php
public function searchParticipants($programId, $filters = [])
{
    // Create a more complex cache key based on parameters
    $filterHash = md5(json_encode($filters));
    $cacheKey = "participant_search_{$programId}_{$filterHash}";
    
    // Try to get from cache
    $cache = \Config\Services::cache();
    $results = $cache->get($cacheKey);
    
    if ($results !== null) {
        return $results;
    }
    
    // Cache miss - perform search
    $results = $this->performDatabaseSearch($programId, $filters);
    
    // Store in cache for 5 minutes (300 seconds) - searches change frequently
    $cache->save($cacheKey, $results, 300);
    
    return $results;
}
```

## Cache Invalidation

### Using Invalidation Helpers

Always use the standard invalidation functions when updating data:

```php
public function updateParticipantStatus($participantId, $newStatus)
{
    // Get participant data to find programId
    $participant = $this->find($participantId);
    $programId = $participant->program_id;
    
    // Update the status in the database
    $this->update($participantId, ['status_id' => $newStatus]);
    
    // Invalidate relevant caches
    if (function_exists('invalidate_participant_cache')) {
        invalidate_participant_cache($programId);
    }
    
    if (function_exists('invalidate_export_cache')) {
        invalidate_export_cache($programId);
    }
    
    return true;
}
```

### Creating New Invalidation Functions

When adding a new feature that needs cache invalidation:

```php
if (!function_exists('invalidate_award_cache')) {
    /**
     * Invalidate award-related caches
     * 
     * @param int|null $programId Optional program ID
     * @param int|null $awardId Optional award ID
     * @return void
     */
    function invalidate_award_cache($programId = null, $awardId = null)
    {
        $cache = \Config\Services::cache();
        
        if ($programId) {
            $cache->delete("program_awards_{$programId}");
        }
        
        if ($awardId) {
            $cache->delete("award_details_{$awardId}");
        }
        
        // If no specific ID provided, clear all award caches
        if (!$programId && !$awardId) {
            // We don't have wildcard delete in CodeIgniter 4
            // Use clean() with caution as it clears all cache
            // $cache->clean();
            
            // Alternative: Track keys in an array cache
            $awardKeyList = $cache->get('award_cache_keys') ?? [];
            foreach ($awardKeyList as $key) {
                $cache->delete($key);
            }
        }
        
        log_message('info', 'Invalidated award cache' . 
                  ($programId ? " for program ID {$programId}" : '') .
                  ($awardId ? " for award ID {$awardId}" : ''));
    }
}
```

## Controller Implementation

### Caching in Controllers

When implementing caching in a controller:

```php
public function displayAwardsGallery()
{
    $programId = $this->request->getGet('program_id') ?? session('current_program');
    
    // Create a cache key
    $cacheKey = "awards_gallery_{$programId}";
    
    // Try to get from cache
    $cache = \Config\Services::cache();
    $viewData = $cache->get($cacheKey);
    
    if ($viewData === null) {
        // Cache miss - get data from models
        $awards = $this->awardModel->getProgramAwards($programId);
        $categories = $this->categoryModel->getAwardCategories($programId);
        
        $viewData = [
            'awards' => $awards,
            'categories' => $categories,
            'programId' => $programId
        ];
        
        // Cache for 1 hour
        $cache->save($cacheKey, $viewData, 3600);
    }
    
    // Add non-cached data (like current user info)
    $viewData['user'] = $this->getUserData();
    
    return view('awards/gallery', $viewData);
}
```

### Tracking Performance in Controllers

```php
public function exportData()
{
    // Track start time
    $startTime = microtime(true);
    
    $programId = $this->request->getGet('program_id');
    
    // Try to get from cache
    $cache = \Config\Services::cache();
    $data = $cache->get("export_data_{$programId}");
    
    if ($data !== null) {
        $cacheTime = microtime(true) - $startTime;
        log_message('info', "Cache hit. Data retrieved in {$cacheTime} seconds");
    } else {
        // Cache miss - get data
        $data = $this->getExportData($programId);
        
        $queryTime = microtime(true) - $startTime;
        log_message('info', "Cache miss. Data retrieved in {$queryTime} seconds");
        
        // Cache for 30 minutes
        $cache->save("export_data_{$programId}", $data, 1800);
    }
    
    // Process and output data
    $outputTime = microtime(true) - $startTime;
    log_message('info', "Total export time: {$outputTime} seconds");
    
    return $this->response->download('export.xlsx', $this->generateExcel($data));
}
```

## Model Hooks

### Implementing Cache Invalidation in Models

```php
class AwardModel extends Model
{
    protected $table = 'awards';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = ['title', 'description', 'program_id', 'image_url'];
    
    public function __construct()
    {
        parent::__construct();
        helper(['cache_helper']);
    }
    
    // Cache invalidation hooks
    protected function afterInsert(array $data)
    {
        $programId = $data['data']['program_id'] ?? null;
        
        if ($programId && function_exists('invalidate_award_cache')) {
            invalidate_award_cache($programId);
        }
        
        return $data;
    }
    
    protected function afterUpdate(array $data)
    {
        // For single record update
        if (isset($data['id'])) {
            $award = $this->find($data['id']);
            $programId = $award->program_id ?? null;
        } 
        // For where condition updates
        else {
            $programId = $data['data']['program_id'] ?? null;
        }
        
        if ($programId && function_exists('invalidate_award_cache')) {
            invalidate_award_cache($programId);
        }
        
        return $data;
    }
    
    protected function afterDelete(array $data)
    {
        // Similar to afterUpdate
        if (isset($data['id'])) {
            $award = $this->find($data['id']);
            $programId = $award->program_id ?? null;
            
            if ($programId && function_exists('invalidate_award_cache')) {
                invalidate_award_cache($programId);
            }
        }
        
        return $data;
    }
}
```

## Performance Tracking

### Comprehensive Performance Tracking

```php
public function generateReport($programId)
{
    // Start timing
    $timing = [
        'start' => microtime(true),
        'cache_check' => 0,
        'data_retrieval' => 0,
        'processing' => 0,
        'rendering' => 0,
        'total' => 0
    ];
    
    // Check cache
    $cache = \Config\Services::cache();
    $reportData = $cache->get("report_data_{$programId}");
    
    $timing['cache_check'] = microtime(true) - $timing['start'];
    
    if ($reportData === null) {
        $dataStart = microtime(true);
        
        // Get data from database
        $participants = $this->participantModel->getProgramParticipants($programId);
        $payments = $this->paymentModel->getProgramPayments($programId);
        $certificates = $this->certificateModel->getProgramCertificates($programId);
        
        $reportData = [
            'participants' => $participants,
            'payments' => $payments,
            'certificates' => $certificates
        ];
        
        // Cache the data
        $cache->save("report_data_{$programId}", $reportData, 1800);
        
        $timing['data_retrieval'] = microtime(true) - $dataStart;
    }
    
    // Process the data
    $processStart = microtime(true);
    $statistics = $this->calculateStatistics($reportData);
    $timing['processing'] = microtime(true) - $processStart;
    
    // Render the report
    $renderStart = microtime(true);
    $report = $this->renderReport($reportData, $statistics);
    $timing['rendering'] = microtime(true) - $renderStart;
    
    // Calculate total time
    $timing['total'] = microtime(true) - $timing['start'];
    
    // Log all timing information
    log_message('info', "Report generation timing: " . json_encode($timing));
    
    // In development environment, add timing info to the report
    if (ENVIRONMENT === 'development') {
        $report .= $this->renderTimingInfo($timing);
    }
    
    return $report;
}
```

## Best Practices

1. **Consistent Key Names**: Follow the established pattern `{entity}_{purpose}_{identifier}`
2. **Appropriate TTLs**: Use shorter TTLs for volatile data, longer for stable data
3. **Logging**: Include detailed logging for cache hits/misses and performance
4. **Invalidation**: Always invalidate related caches when data changes
5. **Error Handling**: Gracefully handle cache failures, falling back to database
6. **Memory Usage**: Be mindful of caching large data structures

## Example Redis Configuration

This application uses the following Redis configuration in `.env`:

```
cache.handler = 'redis'
cache.redis.host = '127.0.0.1'
cache.redis.password = null
cache.redis.port = 6379
cache.redis.database = 0
```

## Additional Resources

- [CodeIgniter 4 Cache Documentation](https://codeigniter.com/user_guide/libraries/caching.html)
- [Redis Documentation](https://redis.io/documentation)
- See `docs/REDIS_CACHING_IMPLEMENTATION.md` for the full caching architecture documentation
