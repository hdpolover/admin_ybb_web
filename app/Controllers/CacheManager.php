<?php

namespace App\Controllers;

use App\Traits\Cacheable;

class CacheManager extends AdminBaseController
{
    use Cacheable;

    public function __construct()
    {
        // Initialize request for cache trait
        $this->request = \Config\Services::request();
    }

    /**
     * Clear all program-related caches
     * GET /cache/clear/programs
     */
    public function clearProgramCaches()
    {
        try {
            // Clear landing page cache (includes all program categories and programs)
            $this->invalidateLandingCache();
            
            log_message('info', 'All program-related caches cleared successfully');
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Program caches cleared successfully'
                ]);
            }
            
            return redirect()->back()->with('message', 'Program caches cleared successfully');
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to clear program caches: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to clear caches: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->with('error', 'Failed to clear caches');
        }
    }

    /**
     * Clear cache for a specific program
     * GET /cache/clear/program/{id}
     */
    public function clearProgramCache($programId = null)
    {
        if (!$programId) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Program ID is required'
                ]);
            }
            return redirect()->back()->with('error', 'Program ID is required');
        }

        try {
            // Clear program-specific cache
            $this->invalidateProgramCache($programId);
            
            // Also clear landing page cache since it includes program data
            $this->invalidateLandingCache();
            
            log_message('info', 'Cache cleared for program ID: ' . $programId);
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Program cache cleared successfully'
                ]);
            }
            
            return redirect()->back()->with('message', 'Program cache cleared successfully');
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to clear cache for program ' . $programId . ': ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to clear cache: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->with('error', 'Failed to clear cache');
        }
    }

    /**
     * Clear all landing page caches
     * GET /cache/clear/landing
     */
    public function clearLandingCache()
    {
        try {
            $this->invalidateLandingCache();
            
            log_message('info', 'Landing page cache cleared successfully');
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Landing page cache cleared successfully'
                ]);
            }
            
            return redirect()->back()->with('message', 'Landing page cache cleared successfully');
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to clear landing page cache: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to clear cache: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->with('error', 'Failed to clear cache');
        }
    }

    /**
     * Get cache statistics
     * GET /cache/stats
     */
    public function stats()
    {
        try {
            $stats = $this->getCacheStats();
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => $stats
                ]);
            }
            
            // For non-AJAX requests, you could return a view with cache stats
            return $this->response->setJSON($stats);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to get cache stats: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to get cache stats: ' . $e->getMessage()
                ]);
            }
            
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }
}
