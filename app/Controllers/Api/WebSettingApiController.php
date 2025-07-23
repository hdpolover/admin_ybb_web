<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\WebSettingModel;

class WebSettingApiController extends ApiBaseController
{
    protected $model;

    /**
     * Initialize controller, set model
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);

        // Initialize model - this is what was previously in the constructor
        $this->model = new WebSettingModel();
    }

    /**
     * Get All Web Settings
     * GET /api/web-settings
     * High Priority Cache: 2 hours TTL
     */
    public function index()
    {
        $url = $this->request->getGet('url');

        // normalize the web URL
        if ($url) {
            $url = normalize_web_url($url);
        }
        
        $result = $this->cacheResponse(function() use ($url) {
            if ($url) {
                $webSetting = $this->model->getSettingByWebUrl($url);
                if (!$webSetting) {
                    return null; // This will not be cached
                }
                return $webSetting;
            }

            // If no URL is provided, return all web settings
            return $this->model->findAll();
        }, ['url' => $url], null, 7200); // 2 hours cache for web settings
        
        if ($result === null && $url) {
            return $this->respondNotFound('Web setting not found for the given URL');
        }
        
        return $this->respondSuccess($result, self::HTTP_OK, 'Web setting retrieved successfully');
    }

 
    
}