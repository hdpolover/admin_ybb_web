# Redis Caching Quick Reference Guide

## When to Use Caching

| Scenario | Cache? | TTL | Invalidation Strategy |
|----------|--------|-----|----------------------|
| Database queries that run frequently | ✅ | 15-60 min | On data change |
| User-specific data that rarely changes | ✅ | 1-24 hours | On user profile update |
| Expensive calculations or aggregations | ✅ | 15-30 min | On related data change |
| Real-time data (e.g., live chat) | ❌ | N/A | N/A |
| Security-critical information | ⚠️ | 5-15 min | On any security event |
| Session data | ✅ | Session duration | On logout |
| Search results | ✅ | 5-15 min | When indexed data changes |
| UI components and menus | ✅ | 1-6 hours | When structure changes |
| API responses for external services | ✅ | Varies by API | On refresh trigger |

## Cache Key Structure

Follow this pattern for all cache keys:
```
{entity_type}_{purpose}_{identifier}
```

Examples:
- `participants_export_123` - Participant export for program 123
- `program_category_45` - Program category with ID 45
- `user_preferences_67` - Preferences for user 67
- `payment_status_89` - Payment status for transaction 89

## Common TTL Values

| Data Type | TTL | Example |
|-----------|-----|---------|
| UI components | 3600 (1 hour) | Menus, navigation |
| User preferences | 86400 (24 hours) | Theme settings |
| Program data | 3600 (1 hour) | Program details |
| Statistics | 900 (15 min) | Dashboard metrics |
| Export data | 1800 (30 min) | Participant lists |
| Search results | 300 (5 min) | Search queries |

## Cache Helper Functions

Use these helper functions for cache invalidation:

| Function | Purpose | Example |
|----------|---------|---------|
| `invalidate_participant_cache()` | Clear participant data | After registration update |
| `invalidate_program_category_cache()` | Clear program categories | After adding/editing programs |
| `invalidate_export_cache()` | Clear export data | After participant status change |
| `invalidate_payment_cache()` | Clear payment data | After payment processing |
| `invalidate_topbar_data_cache()` | Clear UI components | After menu structure change |
| `invalidate_web_settings_cache()` | Clear settings | After changing web settings |

## How to Implement Caching in 5 Steps

1. **Identify** if the data is suitable for caching (see "When to Use Caching" table)
2. **Create** a consistent cache key following the established pattern
3. **Check** the cache first before doing expensive operations
4. **Save** results to cache with an appropriate TTL
5. **Invalidate** related caches when data changes

## Code Snippet Template

```php
// Step 1: Create a cache key
$cacheKey = "entity_purpose_{$id}";

// Step 2: Try to get from cache
$cache = \Config\Services::cache();
$data = $cache->get($cacheKey);

// Step 3: Return cached data if available
if ($data !== null) {
    log_message('info', "Cache hit for {$cacheKey}");
    return $data;
}

// Step 4: Cache miss - get data from source
log_message('info', "Cache miss for {$cacheKey}");
$data = $this->getDataFromSource($id);

// Step 5: Store in cache with appropriate TTL
$cache->save($cacheKey, $data, 1800); // 30 minutes

return $data;
```

## Troubleshooting

| Issue | Possible Cause | Solution |
|-------|----------------|----------|
| Stale data | Missing invalidation | Check model hooks & invalidation calls |
| No cache hits | Incorrect cache key | Verify key naming consistency |
| High memory usage | Large cached objects | Reduce TTL or limit cached data size |
| Performance not improved | Cache misses too frequent | Check TTL values and invalidation frequency |
| Errors after deployment | Cache structure change | Clear all cache after major updates |

## Resources

- Full documentation: `docs/REDIS_CACHING_IMPLEMENTATION.md`
- Code examples: `docs/REDIS_CACHING_CODE_EXAMPLES.md`
- CodeIgniter cache library: `\Config\Services::cache()`

---

Last updated: July 21, 2025
