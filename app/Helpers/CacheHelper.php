<?php

/**
 * Cache Helper
 * 
 * Helper functions for managing cache in the application
 * Provides centralized cache key generation and invalidation functions
 */

if (!function_exists('invalidate_participant_cache')) {
    /**
     * Invalidate cache related to a specific participant
     * 
     * @param int $participantId The participant ID
     * @param int|null $programId Optional program ID
     * @return void
     */
    function invalidate_participant_cache($participantId, $programId = null)
    {
        $cache = \Config\Services::cache();
        
        // Delete specific participant keys
        $cache->delete("participant_{$participantId}");
        $cache->delete("participant_details_{$participantId}");
        
        // Delete participant award caches if any - since we don't know which awards
        // are associated, we'd need to clear all participant awards cache elsewhere
        
        // If program ID is provided, invalidate program-specific caches
        if ($programId) {
            invalidate_program_cache($programId);
        }
        
        // Note: Since CodeIgniter's cache doesn't have a native way to get all keys or pattern matching,
        // we use specific key deletion instead
    }
}

if (!function_exists('invalidate_program_cache')) {
    /**
     * Invalidate cache related to a specific program
     * 
     * @param int $programId The program ID
     * @return void
     */
    function invalidate_program_cache($programId)
    {
        $cache = \Config\Services::cache();
        
        // Delete all program-related statistics caches
        $cache->delete("participant_stats_{$programId}_" . date('Ymd')); // Today's stats
        $cache->delete("participant_stats_{$programId}_" . date('Ymd', strtotime('-1 day'))); // Yesterday's stats
        $cache->delete("total_countries_{$programId}");
        $cache->delete("countries_data_{$programId}");
        $cache->delete("program_certificates_{$programId}");
        $cache->delete("awards_data_{$programId}");
        $cache->delete("program_details_{$programId}");
        
        // For search caches, we can't easily clear by pattern, so they'll expire naturally
        // This is a limitation when using Redis without direct Redis commands
    }
}

if (!function_exists('invalidate_certificate_cache')) {
    /**
     * Invalidate cache related to certificates
     * 
     * @param int|null $participantId Optional participant ID
     * @param int|null $programId Optional program ID
     * @param int|null $awardId Optional award ID
     * @param int|null $templateId Optional certificate template ID
     * @return void
     */
    function invalidate_certificate_cache($participantId = null, $programId = null, $awardId = null, $templateId = null)
    {
        $cache = \Config\Services::cache();
        
        if ($participantId) {
            // Delete participant certificate caches
            $cache->delete("participant_certificates_{$participantId}");
            
            // If award ID is also provided, delete specific participant-award cache
            if ($awardId) {
                $cache->delete("participant_award_{$participantId}_{$awardId}");
                
                // If template ID is also provided, delete specific certificate cache
                if ($templateId) {
                    $cache->delete("participant_certificate_{$participantId}_{$awardId}_{$templateId}");
                }
            }
        }
        
        if ($programId) {
            // Delete program certificate caches
            $cache->delete("program_certificates_{$programId}");
            $cache->delete("awards_data_{$programId}");
        }
        
        if ($awardId && !$participantId) {
            // Delete award-related caches
            $cache->delete("award_details_{$awardId}");
            
            if ($templateId) {
                $cache->delete("certificate_template_{$templateId}");
            }
        }
        
        // If no specific IDs provided, nothing we can do without pattern-based deletion
        // The caches will expire based on their TTL
    }
}

if (!function_exists('invalidate_all_stats_cache')) {
    /**
     * Invalidate specific statistics caches
     * 
     * @param array $programIds List of program IDs to clear stats for
     * @return void
     */
    function invalidate_all_stats_cache($programIds = [])
    {
        $cache = \Config\Services::cache();
        
        if (empty($programIds)) {
            // Without specific program IDs and without pattern matching,
            // we can't effectively clear all stats
            return;
        }
        
        // Clear specific program stats
        foreach ($programIds as $programId) {
            invalidate_program_cache($programId);
        }
    }
}

if (!function_exists('invalidate_search_cache')) {
    /**
     * Invalidate search-related caches
     * 
     * This function uses a flag file to signal that search cache is invalid.
     * On next search, the flag can be checked to determine if cache should be bypassed.
     * 
     * @return void
     */
    function invalidate_search_cache()
    {
        $cache = \Config\Services::cache();
        
        // Set an invalidation flag that search methods can check
        // Since we can't delete by pattern, this is a workaround
        $cache->save('search_cache_invalid_flag', time(), 3600);
        
        // Additional keys that may help signal invalidation
        $cache->save('search_cache_version', time(), 86400); // 24 hours
    }
}

if (!function_exists('invalidate_payment_cache')) {
    /**
     * Invalidate payment-related caches
     * 
     * @param int|null $paymentId Optional payment ID
     * @param int|null $participantId Optional participant ID
     * @return void
     */
    function invalidate_payment_cache($paymentId = null, $participantId = null)
    {
        $cache = \Config\Services::cache();
        
        // Delete payment stats caches
        $cache->delete('payment_stats');
        $cache->delete('payment_stats_by_currency');
        $cache->delete('pending_manual_payments');
        
        if ($participantId) {
            $cache->delete("participant_payments_{$participantId}");
            $cache->delete("has_successful_payments_{$participantId}");
        }
        
        if ($paymentId) {
            $cache->delete("payment_details_{$paymentId}");
        }
    }
}

if (!function_exists('invalidate_program_category_cache')) {
    /**
     * Invalidate program category cache
     * 
     * @param int|null $categoryId Optional category ID
     * @param string|null $webUrl Optional web URL
     * @return void
     */
    function invalidate_program_category_cache($categoryId = null, $webUrl = null)
    {
        $cache = \Config\Services::cache();
        
        // Always invalidate the all categories cache
        $cache->delete('all_categories_with_programs');
        
        // Delete specific category cache if provided
        if ($categoryId) {
            $cache->delete("program_category_{$categoryId}");
        }
        
        // Delete web URL category cache if provided
        if ($webUrl) {
            $cache->delete("program_category_web_url_" . md5($webUrl));
        }
        
        // Also invalidate topbar data which depends on categories
        invalidate_topbar_data_cache();
        
        log_message('info', 'Invalidated program category cache' . 
                  ($categoryId ? " for category ID {$categoryId}" : '') .
                  ($webUrl ? " and URL {$webUrl}" : ''));
    }
}

if (!function_exists('invalidate_export_cache')) {
    /**
     * Invalidate export-related caches
     * 
     * @param int|null $programId Optional program ID
     * @return void
     */
    function invalidate_export_cache($programId = null)
    {
        $cache = \Config\Services::cache();
        
        if ($programId) {
            // Invalidate specific program export caches
            $cache->delete("participants_export_{$programId}");
            log_message('info', "Invalidated export cache for program ID {$programId}");
        } else {
            // For CodeIgniter 4, we can't wildcard delete, so we need to
            // clear the entire cache in this case
            $cache->clean();
            log_message('info', 'Cleared entire cache due to export cache invalidation');
        }
    }
}

if (!function_exists('invalidate_web_settings_cache')) {
    /**
     * Invalidate web settings cache
     * 
     * @param int|null $programCategoryId Optional program category ID
     * @param string|null $webUrl Optional web URL
     * @return void
     */
    function invalidate_web_settings_cache($programCategoryId = null, $webUrl = null)
    {
        $cache = \Config\Services::cache();
        
        // Delete the all settings cache
        $cache->delete('web_settings_all');
        
        // Delete specific program category settings cache if provided
        if ($programCategoryId) {
            $cache->delete("web_settings_category_{$programCategoryId}");
            $cache->delete("web_settings_program_{$programCategoryId}");
        }
        
        // Delete specific web URL settings cache if provided
        if ($webUrl) {
            $cache->delete("web_settings_url_" . md5($webUrl));
        }
        
        log_message('info', 'Invalidated web settings cache' . 
                  ($programCategoryId ? " for program category ID {$programCategoryId}" : '') .
                  ($webUrl ? " and URL {$webUrl}" : ''));
    }
}

if (!function_exists('invalidate_topbar_data_cache')) {
    /**
     * Invalidate topbar data cache for all users
     * This is a brute force approach since we can't easily 
     * identify all user-specific topbar cache keys
     * 
     * @param string|null $userId Optional specific user ID
     * @return void
     */
    function invalidate_topbar_data_cache($userId = null)
    {
        $cache = \Config\Services::cache();
        
        if ($userId) {
            // Delete specific user's topbar data
            $cache->delete("topbar_data_{$userId}");
            log_message('info', "Invalidated topbar data cache for user ID {$userId}");
        } else {
            // Since we can't do pattern matching deletion with CodeIgniter's cache,
            // we'll set a flag that BaseController can check
            $cache->save('topbar_data_invalid_flag', time(), 86400);
            log_message('info', 'Set topbar data invalid flag for all users');
        }
    }
}

if (!function_exists('invalidate_dashboard_cache')) {
    /**
     * Invalidate dashboard-related caches
     * 
     * @param int|null $programId Optional program ID
     * @return void
     */
    function invalidate_dashboard_cache($programId = null)
    {
        $cache = \Config\Services::cache();
        
        if ($programId) {
            // Invalidate specific program dashboard caches
            $cache->delete("dashboard_summary_{$programId}");
            
            // Clear registration stats for all periods
            foreach (['day', 'week', 'month'] as $period) {
                for ($limit = 10; $limit <= 60; $limit += 10) {
                    $cache->delete("dashboard_registration_stats_{$programId}_{$period}_{$limit}");
                }
            }
            
            // Clear gender statistics
            $cache->delete("dashboard_gender_stats_{$programId}");
            
            // Clear nationality statistics for different limits
            for ($limit = 5; $limit <= 50; $limit += 5) {
                $cache->delete("dashboard_nationality_stats_{$programId}_{$limit}");
            }
            
            // Clear age statistics
            $cache->delete("dashboard_age_stats_{$programId}");
            
            // Clear ambassador statistics for different limits
            for ($limit = 5; $limit <= 20; $limit += 5) {
                $cache->delete("dashboard_ambassador_stats_{$programId}_{$limit}");
            }
            
            log_message('info', "Invalidated dashboard cache for program ID {$programId}");
        } else {
            // Set an invalidation flag for all dashboard caches
            $cache->save('dashboard_cache_invalid_flag', time(), 3600);
            log_message('info', 'Set dashboard cache invalid flag');
        }
    }
}

if (!function_exists('register_cache_clear_hook')) {
    /**
     * Register a hook to clear cache after model operations
     * To be called in a model's __construct method
     * 
     * @param Model $model The model instance
     * @param string $entityType The entity type for cache key creation
     * @return void
     */
    function register_cache_clear_hook($model, $entityType)
    {
        // Load the Events library
        $events = \Config\Services::events();
        
        // Register post-save hook
        $events->on('post_model_save_' . $model->table, function($data) use ($entityType) {
            helper(['cache']);
            
            // Determine ID to use for cache invalidation
            $id = $data['id'] ?? null;
            if ($id === null) {
                return; // Can't do anything without an ID
            }
            
            // Invalidate cache based on entity type
            switch ($entityType) {
                case 'participant':
                    invalidate_participant_cache($id);
                    invalidate_search_cache();
                    
                    // If we have a program ID, invalidate dashboard stats too
                    $model = \Config\Services::modelFactory()->createModel('\App\Models\ParticipantModel');
                    $participant = $model->find($id);
                    if ($participant && isset($participant->program_id)) {
                        invalidate_dashboard_cache($participant->program_id);
                    }
                    break;
                    
                case 'payment':
                    invalidate_payment_cache($id);
                    break;
                    
                case 'certificate':
                    invalidate_certificate_cache(null, null, null, $id);
                    break;
                    
                case 'award':
                    invalidate_certificate_cache(null, null, $id);
                    break;
                    
                case 'program':
                    invalidate_program_cache($id);
                    invalidate_dashboard_cache($id); // Clear dashboard data when program changes
                    invalidate_topbar_data_cache(); // Clear topbar data when program changes
                    break;
                    
                case 'program_category':
                    invalidate_program_cache($id);
                    invalidate_web_settings_cache($id);
                    invalidate_topbar_data_cache(); // Clear topbar data when category changes
                    break;
                    
                case 'web_setting':
                    invalidate_web_settings_cache($id);
                    break;
                    
                default:
                    // Default behavior - log only
                    log_message('info', "Cache clear hook called for unknown entity type: {$entityType}");
            }
            
            // Log the cache invalidation
            log_message('info', "Cache cleared for {$entityType} with ID {$id}");
        });
    }
}
