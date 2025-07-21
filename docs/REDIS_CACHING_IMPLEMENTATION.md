# Redis Caching Implementation

## Overview

This document outlines the Redis caching implementation in the YBB Admin Web application. Redis caching has been integrated to improve application performance, reduce database load, and enhance the user experience for data-intensive operations such as exports, statistics, and UI components.

## Table of Contents

1. [Core Components](#core-components)
2. [Cache Key Structure](#cache-key-structure)
3. [TTL (Time-To-Live) Strategy](#ttl-time-to-live-strategy)
4. [Cache Invalidation](#cache-invalidation)
5. [Performance Monitoring](#performance-monitoring)
6. [Implementation Areas](#implementation-areas)
7. [Troubleshooting](#troubleshooting)

## Core Components

### Cache Helper

The application uses a centralized `CacheHelper.php` for cache management, providing standardized functions for cache operations across the application. This helper includes functions for:

- Invalidating specific cache types (participant data, program data, etc.)
- Creating consistent cache keys
- Managing cache dependencies

### Model Hooks

Cache invalidation is automatically handled through model hooks, ensuring data consistency between the cache and database:

- `afterInsert`: Invalidates relevant caches after new records are created
- `afterUpdate`: Invalidates caches when records are modified
- `afterDelete`: Cleans up caches when records are removed

### Global Helper Loading

Cache helpers are loaded globally through:

- The BaseController's `$helpers` array, making them available to all controllers
- Direct helper loading in models that need cache invalidation

## Cache Key Structure

Cache keys follow a consistent naming convention to ensure clear organization and prevent collisions:

- `{entity_type}_{purpose}_{identifier}`

Examples:
- `participants_export_123` - Participant export data for program ID 123
- `program_category_45` - Program category data for category ID 45
- `participant_stats_67` - Participant statistics for program ID 67

## TTL (Time-To-Live) Strategy

Different types of data have different cache durations based on their volatility and importance:

| Data Type | TTL (seconds) | Rationale |
|-----------|---------------|-----------|
| UI Components | 3600 (1 hour) | Change infrequently, high access frequency |
| Export Data | 1800 (30 min) | May change occasionally, high processing cost |
| Statistics | 900 (15 min) | Change more frequently, moderate processing cost |
| User Preferences | 86400 (24 hours) | Very stable data, can be cached longer |
| Search Results | 300 (5 min) | More volatile, should update more frequently |

## Cache Invalidation

### Automatic Invalidation

The application uses several strategies for automatic cache invalidation:

1. **Model-based**: Through model hooks (afterInsert, afterUpdate, afterDelete)
2. **Controller-based**: After specific actions like approvals or status changes
3. **Relationship-based**: Invalidating related caches when parent records change

### Manual Invalidation

For admin operations or data imports, manual cache invalidation is available through:

- Admin panel actions
- System maintenance tools
- Deployment scripts

### Invalidation Functions

The application provides specialized invalidation functions for different data types:

```php
// Invalidate participant-related caches
invalidate_participant_cache($programId = null);

// Invalidate program category caches
invalidate_program_category_cache($categoryId = null, $webUrl = null);

// Invalidate export-related caches
invalidate_export_cache($programId = null);

// Invalidate web settings caches
invalidate_web_settings_cache($programCategoryId = null, $webUrl = null);

// Invalidate topbar UI data
invalidate_topbar_data_cache();

// Invalidate payment-related caches
invalidate_payment_cache($participantId = null, $paymentId = null);
```

## Performance Monitoring

The caching system includes built-in performance monitoring:

1. **Timing Metrics**: Tracks cache hits/misses and processing times
2. **Log Entries**: Documents cache operations for analysis
3. **Performance Headers**: Development environment includes timing data in responses

Example log entries:
```
[2025-07-21 10:15:22] [info] PDF Export data retrieval completed in 0.12 seconds
[2025-07-21 10:15:22] [info] Invalidated export cache for program ID 45
```

## Implementation Areas

### 1. Export Operations

- **Cache Target**: `getParticipantsWithEssays()` method
- **Files**: `ExportController.php`, `ParticipantModel.php`
- **TTL**: 1800 seconds (30 minutes)
- **Key Format**: `participants_export_{programId}`
- **Invalidation**: On participant create/update/delete

### 2. Participant Statistics

- **Cache Target**: Country statistics, participation counts
- **Files**: `ParticipantModel.php`, `DashboardController.php`
- **TTL**: 900 seconds (15 minutes)
- **Key Format**: `participant_stats_{programId}`
- **Invalidation**: On participant status changes or new registrations

### 3. UI Components

- **Cache Target**: Topbar data, program menus
- **Files**: `BaseController.php`, `ViewComponents.php`
- **TTL**: 3600 seconds (1 hour)
- **Key Format**: `topbar_data_{userType}`
- **Invalidation**: On program changes or user role updates

### 4. Payment Processing

- **Cache Target**: Payment verification, status checks
- **Files**: `PaymentModel.php`, `PaymentController.php`
- **TTL**: 900 seconds (15 minutes)
- **Key Format**: `payment_data_{participantId}`
- **Invalidation**: On payment status changes

### 5. Program Categories

- **Cache Target**: Program hierarchies and category listings
- **Files**: `ProgramCategoryModel.php`
- **TTL**: 3600 seconds (1 hour)
- **Key Format**: `program_category_{categoryId}`
- **Invalidation**: On category updates or program changes

## Troubleshooting

### Common Issues

1. **Stale Data**: If changes aren't reflecting in the UI
   - Solution: Use the appropriate invalidation function or clear cache manually

2. **Cache Misses**: If performance is unexpectedly slow
   - Check Redis connection settings
   - Verify cache key format consistency
   - Look for missing invalidation calls

3. **Memory Usage**: If Redis memory consumption is high
   - Review TTL values for frequently changing data
   - Consider implementing cache size limits for large result sets
   - Use data compression for large cached objects

### Debugging Tools

1. **Redis CLI Commands**:
   ```
   KEYS *participant*  # Find all participant-related cache keys
   GET participants_export_123  # View cached data for program 123
   TTL participants_export_123  # Check remaining TTL for a key
   ```

2. **Application Logs**:
   - Check for "Cache hit" and "Cache miss" entries
   - Look for invalidation events in the logs
   - Monitor cache performance metrics

3. **Redis Monitoring**:
   - Use Redis INFO command to check memory usage
   - Monitor cache hit rate with Redis STATS
   - Check for cache evictions if memory limits are reached

## Conclusion

The Redis caching implementation provides significant performance improvements, especially for data-intensive operations like exports and statistics generation. By following consistent patterns for cache keys, TTLs, and invalidation, the system maintains data consistency while reducing database load and improving user experience.

For questions or issues related to the caching implementation, please contact the development team.

---

Last updated: July 21, 2025
