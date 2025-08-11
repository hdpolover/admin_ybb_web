# Cache Invalidation Implementation Guide

## Problem Analysis

Your application uses Redis caching to improve frontend performance, but cache is not being invalidated when master data is updated through the admin panel. This causes the frontend to serve stale cached data even after updates.

## ❓ Why Don't I See Cache Files on Hosting?

### This is NORMAL and Expected! 

Your application is configured to use **Redis cache** as the primary caching mechanism:

```php
// app/Config/Cache.php
public string $handler = 'redis';           // Primary: Redis (in-memory)
public string $backupHandler = 'file';      // Backup: File cache
```

**Key Points:**
- ✅ **Redis stores data in memory (RAM), NOT in files**
- ✅ **No physical cache files are created when Redis is working**
- ✅ **File cache only activates when Redis is unavailable**
- ✅ **If file cache was used, files would be in `writable/cache/`, not public folder**

### To Check Your Cache Status:
Upload and run `check_hosting_cache_status.php` to verify your cache is working correctly.

### 🚀 NEW: Automatic Cache Fallback 

**When Redis is unavailable, the system now automatically falls back to direct API calls!**

- ✅ **No errors or downtime** if Redis fails
- ✅ **Seamless fallback** to direct database queries
- ✅ **Automatic recovery** when Redis becomes available again
- ✅ **Configurable logging** to monitor fallback events

**Test the fallback:** Upload and run `test_cache_fallback.php`

## Solution Overview

The system has a robust caching mechanism with automatic invalidation capabilities, but it's not being used in all admin controllers. Here's how to fix it:

## Immediate Solutions

### Option 1: Manual Cache Clearing (Quick Fix)

After making updates in the admin panel, visit these URLs to clear cache:

1. **Clear all program-related caches**: `https://yourdomain.com/cache/clear/programs`
2. **Clear landing page cache**: `https://yourdomain.com/cache/clear/landing` 
3. **Clear specific program cache**: `https://yourdomain.com/cache/clear/program/{program_id}`

### Option 2: Automatic Cache Invalidation (Recommended)

## Implementation Steps

### Step 1: Add Cacheable Trait to Admin Controllers

For each admin controller that handles master data updates, add the `Cacheable` trait:

```php
<?php
namespace App\Controllers;

use App\Traits\Cacheable;

class YourAdminController extends BaseController
{
    use Cacheable;
    
    // ... rest of your controller
}
```

### Step 2: Add Cache Invalidation to CRUD Operations

Add the appropriate cache invalidation method after successful operations:

#### For Announcements, FAQs, Program Details:
```php
// After successful create/update/delete
$this->invalidateLandingCache();
```

#### For Program-specific data (payments, schedules, subthemes):
```php
// After successful create/update/delete
$this->invalidateProgramCache($programId);
$this->invalidateLandingCache(); // Also clear landing cache
```

#### For Participant data:
```php
// After successful create/update/delete
$this->invalidateUserCache($userId);
$this->invalidateProgramCache($programId);
```

### Step 3: Implementation Example (Already Done for Announcements)

I've already implemented cache invalidation for the `Announcements` controller. Here's what was added:

1. **Added Cacheable trait**:
```php
use App\Traits\Cacheable;

class Announcements extends BaseController
{
    use Cacheable;
    // ...
}
```

2. **Added cache invalidation after successful operations**:
```php
// In create() method after successful creation
$this->invalidateLandingCache();

// In update() method after successful update  
$this->invalidateLandingCache();

// In delete() method after successful deletion
$this->invalidateLandingCache();
```

## Controllers That Need Cache Invalidation

Based on your routes, these admin controllers should have cache invalidation added:

### High Priority (affects frontend directly):
- ✅ `Announcements` (already implemented)
- ✅ `ProgramDetails` (already implemented)
- ✅ `ProgramPhotos` (implemented)
- ✅ `ProgramTestimonies` (implemented)
- ✅ `Faqs` (implemented)

### Medium Priority:
- ✅ `ProgramPayments` - affects program data (COMPLETE)
- ✅ `ProgramSchedules` - affects program timeline (COMPLETE)
- ✅ `SubmissionForm` - affects form data (COMPLETE)
- ✅ `PaymentMethods` - affects payment options (COMPLETE)

### Lower Priority:
- ✅ `AbstractTopics` - affects abstract configuration (COMPLETE)
- ✅ `AbstractSettings` - affects abstract submission settings (COMPLETE)
- ✅ `ProgramAwards` - affects award information (COMPLETE)
- ✅ `ProgramCertificates` - affects certificate templates (COMPLETE)

## Cache Invalidation Methods Available

The `Cacheable` trait provides these methods:

1. `$this->invalidateLandingCache()` - Clears landing page, web settings, announcements
2. `$this->invalidateProgramCache($programId)` - Clears program-specific cache
3. `$this->invalidateUserCache($userId)` - Clears user-specific cache  
4. `$this->invalidatePaymentCache($participantId)` - Clears payment cache

## Implementation Template

Here's a template for adding cache invalidation to any admin controller:

```php
<?php
namespace App\Controllers;

use App\Traits\Cacheable;

class YourController extends BaseController
{
    use Cacheable;
    
    public function create()
    {
        // ... your existing create logic ...
        
        if ($operationSuccessful) {
            // Add appropriate cache invalidation
            $this->invalidateLandingCache(); // or specific cache method
            
            // ... return success response ...
        }
    }
    
    public function update($id)
    {
        // ... your existing update logic ...
        
        if ($operationSuccessful) {
            // Add appropriate cache invalidation
            $this->invalidateLandingCache(); // or specific cache method
            
            // ... return success response ...
        }
    }
    
    public function delete($id)
    {
        // ... your existing delete logic ...
        
        if ($operationSuccessful) {
            // Add appropriate cache invalidation
            $this->invalidateLandingCache(); // or specific cache method
            
            // ... return success response ...
        }
    }
}
```

## Cache File Management

### Where cache files are stored:
- Local: `writable/cache/`
- Server: You need to upload the `writable/cache/` directory to your hosting

### Why Cache Files Aren't Created on Cloud Hosting:

**Your application uses Redis as the primary cache handler** (as configured in `app/Config/Cache.php`):

```php
public string $handler = 'redis';
public string $backupHandler = 'file';
```

This means:

1. **Primary Storage**: Cache is stored in **Redis memory**, not files
2. **File Backup**: Only creates files if Redis is unavailable (fallback to file handler)
3. **Cloud Issue**: Your hosting might not have Redis installed/configured properly

### 🆕 Automatic Fallback Mechanism

**NEW FEATURE**: The system now includes automatic fallback when Redis is unavailable:

#### How It Works:
1. **Cache Availability Check**: Before each cache operation, system tests Redis connectivity
2. **Automatic Fallback**: If Redis fails, API calls execute directly without caching
3. **Seamless Operation**: Users experience no errors or downtime
4. **Smart Recovery**: When Redis becomes available, caching automatically resumes

#### Configuration Options:
```php
// app/Config/Cache.php
public bool $enableFallback = true;    // Enable automatic fallback
public bool $logFallbacks = true;      // Log fallback events for monitoring
```

#### Fallback Scenarios:
- ✅ **Redis server down**: Direct API calls continue working
- ✅ **Redis connection timeout**: No user-facing errors
- ✅ **Redis memory full**: Application continues normally
- ✅ **Shared hosting without Redis**: Automatic graceful degradation

#### Performance Impact:
- **With Redis**: Fast cached responses (30min-2hr TTL)
- **Fallback Mode**: Slightly slower but fully functional
- **No Downtime**: Application always remains available

### Troubleshooting Cache on Cloud Hosting:

#### ✨ NEW: Automatic Fallback (Recommended)
**The system now handles Redis issues automatically!** No manual configuration needed.

- Upload `test_cache_fallback.php` to test the fallback mechanism
- Check logs in `writable/logs/` for fallback events
- Application continues working even if Redis is unavailable

#### Option 1: Check Redis Availability
Test if Redis is working on your hosting:
```php
// Create a test file: test_redis.php in your public folder
<?php
try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    echo "Redis is working!";
    $redis->set('test', 'value');
    echo " Test value: " . $redis->get('test');
} catch (Exception $e) {
    echo "Redis error: " . $e->getMessage();
    echo " (Fallback mode will be used automatically)";
}
?>
```

#### Option 2: Switch to File-Based Caching (Legacy approach)
If you prefer file-based caching, update `app/Config/Cache.php`:

```php
public string $handler = 'file';  // Change from 'redis' to 'file'
public string $backupHandler = 'file';
```

#### Option 3: Enable Redis on Hosting
Contact your hosting provider to:
- Enable Redis extension for PHP
- Configure Redis server access
- Provide Redis connection details

**Note**: With the new fallback mechanism, Options 2 and 3 are optional. The system works automatically regardless of Redis availability.

### Cache files in public folder:
- The cache files are stored in `writable/cache/`, not in the `public` folder
- You do NOT need to upload cache files themselves to hosting
- The caching system will recreate them as needed
- Just make sure the `writable/cache/` directory has proper write permissions (755 or 777)

## Testing Cache Invalidation

1. Make a change in admin panel
2. Check if cache was cleared: `https://yourdomain.com/cache/stats`
3. Verify frontend shows updated data
4. Check cache files in `writable/cache/` directory

## Production Deployment

1. Ensure `writable/cache/` directory exists on server
2. Set proper permissions: `chmod 755 writable/cache/`
3. Upload your updated controller files
4. Cache will automatically regenerate as needed

## Debugging

If cache invalidation isn't working:

1. Check logs in `writable/logs/` for cache-related errors
2. Verify Redis is running (if using Redis cache)
3. Check cache configuration in `app/Config/Cache.php`
4. Manually clear cache using the cache manager endpoints

The cache files themselves don't need to be uploaded to hosting - they will be automatically generated when needed.
