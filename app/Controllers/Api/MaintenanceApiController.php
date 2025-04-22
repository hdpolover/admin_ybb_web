<?php

namespace App\Controllers\Api;

class MaintenanceApiController extends ApiBaseController
{
    /**
     * Check if a site is in maintenance mode
     * GET /api/maintenance/check
     */
    public function check()
    {
        $webUrl = $this->request->getGet('web_url');
        
        if (empty($webUrl)) {
            return $this->respondValidationErrors('Web URL is required');
        }

        // Normalize the web URL
        $webUrl = normalize_web_url($webUrl);
        
        $maintenanceStatus = is_maintenance_mode($webUrl);
        
        return $this->respondSuccess([
            'in_maintenance' => $maintenanceStatus !== false,
            'site_name' => $maintenanceStatus ? $maintenanceStatus->name : null
        ], self::HTTP_OK, 'Maintenance status retrieved successfully');
    }
}