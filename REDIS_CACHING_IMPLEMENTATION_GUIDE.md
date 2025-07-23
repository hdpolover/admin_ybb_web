# Redis Caching Implementation Guide

## Overview

This implementation adds comprehensive Redis caching to the CodeIgniter 4 API endpoints based on the priority system defined in `API_ENDPOINTS_DOCUMENTATION.md`. The caching system is designed to improve performance while maintaining data consistency.

## Files Added/Modified

### Core Services
- `app/Services/RedisCacheService.php` - Main caching service
- `app/Traits/Cacheable.php` - Caching trait for controllers
- `app/Controllers/Api/ApiBaseController.php` - Updated to include caching trait

### Controllers Updated
- `app/Controllers/Api/WebSettingApiController.php` - High priority caching (2 hours)
- `app/Controllers/Api/ProgramsApiController.php` - High/Medium priority caching
- `app/Controllers/Api/LandingApiController.php` - High/Medium priority caching
- `app/Controllers/Api/ParticipantsApiController.php` - Medium/Low priority caching
- `app/Controllers/Api/PaymentsApiController.php` - Low priority caching
- `app/Controllers/Api/ProgramPaymentsApiController.php` - Medium priority caching

### Management Tools
- `app/Commands/CacheCommand.php` - CLI commands for cache management
- `test_redis_cache.php` - Test script to verify implementation

## Cache Priority System

### High Priority (2 hours TTL)
- **Web Settings** (`/web-settings`) - Site configuration data
- **Programs** (`/programs/*`) - Program details and listings
- **Landing Pages** (`/landing/home`, `/landing/programs`) - Public pages
- **Payment Methods** (`/payment-methods/program/*`) - Payment configuration

### Medium Priority (30 minutes TTL)
- **Participant Lists** (`/participants/user/*`) - User-specific data
- **Program Payments** (`/program-payments/program/*`) - Payment information
- **Announcements** (`/landing/announcements`) - News and updates
- **Program Categories** (`/program-categories/*`) - Category data

### Low Priority (5-10 minutes TTL)
- **Payment Status** (`/payments/participants/*`) - Frequently changing data
- **Participant Details** (`/participants/*`) - Individual participant data
- **Search Results** (`/participants/search`) - Dynamic search results

## Usage

### In Controllers

Controllers that extend `ApiBaseController` automatically have access to caching methods:

```php
// Basic caching with automatic TTL
$data = $this->cacheResponse(function() {
    return $this->model->getData();
}, ['param1' => $value1]);

// User-specific caching
$userData = $this->cacheUserData(function() use ($userId) {
    return $this->model->getUserData($userId);
}, $userId);

// Program-specific caching
$programData = $this->cacheProgramData(function() use ($programId) {
    return $this->model->getProgramData($programId);
}, $programId);

// Participant-specific caching
$participantData = $this->cacheParticipantData(function() use ($participantId) {
    return $this->model->getParticipantData($participantId);
}, $participantId);
```

### Cache Invalidation

Invalidate cache when data changes:

```php
// After user data modification
$this->invalidateUserCache($userId);

// After program modification
$this->invalidateProgramCache($programId);

// After payment modification
$this->invalidatePaymentCache($participantId);

// After landing page content changes
$this->invalidateLandingCache();
```

### CLI Management

```bash
# Clear all cache
php spark cache:manage clear

# View cache statistics
php spark cache:manage stats

# Test cache functionality
php spark cache:manage test

# Warm up critical endpoints
php spark cache:manage warmup
```

### Debug Mode

Add `?no_cache=1` to any API URL to bypass cache for debugging:
```
GET /api/programs/category/1?no_cache=1
```

## Configuration

### Redis Settings (app/Config/Cache.php)

```php
public string $handler = 'redis';
public string $backupHandler = 'file';

public array $redis = [
    'host'     => '127.0.0.1',
    'password' => null,
    'port'     => 6379,
    'timeout'  => 0,
    'database' => 0,
    'prefix'   => 'ybb_app_'
];
```

### Cache Key Format

Keys follow the pattern: `{domain}:{endpoint}:{parameters}:{version}`

Examples:
- `example.com:web-settings:url=main:v1`
- `example.com:programs:category:1:v1`
- `example.com:participants:user:123:v1`

## Performance Impact

Based on the documentation estimates:

- **High Priority endpoints**: 70-90% performance improvement
- **Medium Priority endpoints**: 40-70% performance improvement  
- **Low Priority endpoints**: 20-40% performance improvement

## Cache Invalidation Strategy

### Automatic Triggers
1. **User Registration** - Clears user-specific caches
2. **Program Updates** - Clears program-related caches
3. **Payment Completion** - Clears payment status caches
4. **Form Submissions** - Clears participant data caches
5. **Admin Changes** - Clears relevant content caches

### Manual Invalidation
Use CLI commands or call invalidation methods directly in your code when needed.

## Monitoring and Debugging

### Logging
All cache operations are logged with INFO level:
- Cache HIT/MISS events
- Cache SET/DELETE operations
- TTL information

### Error Handling
The system gracefully falls back to database queries if Redis is unavailable, ensuring the application continues to function.

### Performance Monitoring
Monitor cache hit/miss ratios through logs to optimize caching strategies.

## Best Practices

1. **Use appropriate TTL values** - Balance between performance and data freshness
2. **Implement proper invalidation** - Ensure cache is cleared when data changes
3. **Monitor cache hit ratios** - Adjust caching strategy based on usage patterns
4. **Use cache versioning** - Increment version number for easy cache invalidation
5. **Handle Redis failures gracefully** - Always have a fallback strategy

## Testing

Run the test script to verify the implementation:

```bash
php test_redis_cache.php
```

This will test:
- Basic cache operations (SET/GET/DELETE)
- TTL functionality
- Key generation
- Cache statistics
- Bypass functionality

## Troubleshooting

### Common Issues

1. **Redis Connection Failed**
   - Check if Redis server is running
   - Verify connection settings in `app/Config/Cache.php`
   - Check firewall/network connectivity

2. **Cache Not Working**
   - Verify Redis handler is set as primary in config
   - Check Redis server logs for errors
   - Run `php spark cache:manage test` for diagnostics

3. **Performance Not Improved**
   - Monitor cache hit/miss ratios in logs
   - Verify TTL values are appropriate
   - Check if cache keys are being generated correctly

4. **Stale Data Issues**
   - Implement proper cache invalidation in data modification methods
   - Consider reducing TTL for frequently changing data
   - Use versioning to force cache refresh

## Future Enhancements

1. **Cache Warming** - Pre-populate cache for critical endpoints
2. **Advanced Invalidation** - Pattern-based cache clearing
3. **Cache Analytics** - Detailed hit/miss statistics dashboard
4. **Distributed Caching** - Multi-server Redis setup
5. **Cache Compression** - Reduce memory usage for large datasets

## Support

For issues or questions about the caching implementation:

1. Check the logs in `writable/logs/` for cache-related errors
2. Run the test script to diagnose issues
3. Use the CLI commands for debugging and management
4. Review this documentation for best practices

Remember to monitor your Redis server resources and adjust TTL values based on your application's specific needs and usage patterns.
