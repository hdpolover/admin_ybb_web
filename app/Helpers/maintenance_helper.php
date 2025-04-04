<?php

/**
 * Maintenance Mode Helper
 * 
 * This helper provides functions to check if a website is in maintenance mode
 * and handle maintenance mode properly in the application.
 */

if (!function_exists('is_maintenance_mode')) {
    /**
     * Check if a website is in maintenance mode
     * 
     * @param string $webUrl The web URL to check maintenance status for
     * @return bool|object False if not in maintenance mode, maintenance data if in maintenance mode
     */
    function is_maintenance_mode(string $webUrl)
    {
        $db = \Config\Database::connect();
        
        $query = $db->table('web_settings')
            ->select('is_maintenance_mode, name')
            ->where('web_url', $webUrl)
            ->get();
        
        $result = $query->getRow();
        
        if (!$result || !$result->is_maintenance_mode) {
            return false;
        }
        
        // Return maintenance data if site is in maintenance mode
        return $result;
    }
}

if (!function_exists('handle_maintenance_mode')) {
    /**
     * Handle maintenance mode check and response for API
     * 
     * @param string $webUrl The web URL to check
     * @param \CodeIgniter\HTTP\ResponseInterface $response The response object
     * @return null|\CodeIgniter\HTTP\ResponseInterface Null if not in maintenance mode, response if in maintenance
     */
    function handle_maintenance_mode(string $webUrl, $response = null)
    {
        $maintenanceStatus = is_maintenance_mode($webUrl);
        
        if (!$maintenanceStatus) {
            return null;
        }
        
        // If no response object is provided, create one
        if ($response === null) {
            $response = service('response');
        }
        
        // Return maintenance mode response
        return $response->setStatusCode(503) // Service Unavailable
            ->setJSON([
                'success' => false,
                'message' => 'This site is currently under maintenance. Please try again later.',
                'maintenance' => true,
                'site_name' => $maintenanceStatus->name ?? 'Site'
            ]);
    }
}