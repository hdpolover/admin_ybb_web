<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class HealthController extends BaseController
{
    /**
     * Health check endpoint for deployment verification
     * 
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $checks = [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => ENVIRONMENT,
            'php_version' => phpversion(),
            'ci_version' => \CodeIgniter\CodeIgniter::CI_VERSION,
        ];
        
        // Check database connection
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
            $checks['database'] = 'connected';
        } catch (\Exception $e) {
            $checks['database'] = 'error: ' . $e->getMessage();
            $checks['status'] = 'error';
        }
        
        // Check writable directories
        $writableDirs = ['logs', 'cache', 'session', 'uploads'];
        $checks['writable_dirs'] = [];
        
        foreach ($writableDirs as $dir) {
            $path = WRITEPATH . $dir;
            $checks['writable_dirs'][$dir] = is_writable($path) ? 'writable' : 'not writable';
            
            if (!is_writable($path)) {
                $checks['status'] = 'warning';
            }
        }
        
        // Check critical configuration
        $checks['base_url'] = base_url();
        $checks['timezone'] = date_default_timezone_get();
        
        // Set appropriate HTTP status code
        $statusCode = 200;
        if ($checks['status'] === 'error') {
            $statusCode = 500;
        } elseif ($checks['status'] === 'warning') {
            $statusCode = 200; // Still return 200 for warnings
        }
        
        return $this->response
            ->setJSON($checks)
            ->setStatusCode($statusCode);
    }
    
    /**
     * Simple ping endpoint
     * 
     * @return ResponseInterface
     */
    public function ping(): ResponseInterface
    {
        return $this->response
            ->setJSON([
                'status' => 'ok',
                'message' => 'pong',
                'timestamp' => time()
            ])
            ->setStatusCode(200);
    }
}