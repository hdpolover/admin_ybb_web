<?php

/**
 * API Helper - Utility functions for working with API controllers and JSON handling
 */

use Config\Services;

if (!function_exists('prepare_api_request')) {
    /**
     * Prepares an API request from form data
     *
     * @param string $method HTTP method (POST, PUT, DELETE, etc)
     * @param array $formData Form data to convert to JSON
     * @return object Returns a new request object configured for API call
     */
    function prepare_api_request(string $method, array $formData)
    {
        // Convert form data to JSON
        $jsonBody = json_encode($formData);
        
        // Set server vars for the request
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        
        // Create a new request instance
        $apiRequest = Services::request();
        $apiRequest->setBody($jsonBody);
        
        return $apiRequest;
    }
}

if (!function_exists('call_api_controller')) {
    /**
     * Calls an API controller method with the prepared request
     *
     * @param object $apiController The API controller instance
     * @param string $method The controller method to call
     * @param array $params Parameters to pass to the method
     * @return mixed The API response
     */
    function call_api_controller($apiController, string $method, array $params = [])
    {
        // Store the original request
        $oldRequest = Services::request();
        
        // Get the prepared API request
        $apiRequest = Services::request();
        
        // Inject our API request
        Services::injectMock('request', $apiRequest);
        
        // Call the controller method
        $response = call_user_func_array([$apiController, $method], $params);
        
        // Restore the original request
        Services::injectMock('request', $oldRequest);
        
        return $response;
    }
}

if (!function_exists('handle_api_form')) {
    /**
     * Process form data through an API controller
     *
     * @param array $formData The form data (usually $_POST)
     * @param object $apiController The API controller instance
     * @param string $method The API method to call
     * @param array $params Additional parameters for the API method
     * @return object The API response object
     */
    function handle_api_form(array $formData, $apiController, string $method, array $params = [])
    {
        // Determine HTTP method based on the API method name
        $httpMethod = 'POST';
        if (strpos($method, 'update') !== false) {
            $httpMethod = 'PUT';
        } elseif (strpos($method, 'delete') !== false) {
            $httpMethod = 'DELETE';
        }
        
        // Prepare the API request
        $apiRequest = prepare_api_request($httpMethod, $formData);
        
        // Store the original request
        $oldRequest = Services::request();
        
        // Inject our API request
        Services::injectMock('request', $apiRequest);
        
        // Call the API controller method
        $response = call_user_func_array([$apiController, $method], $params);
        
        // Restore the original request
        Services::injectMock('request', $oldRequest);
        
        return $response;
    }
}

if (!function_exists('parse_api_response')) {
    /**
     * Parse the API response and determine if it was successful
     *
     * @param mixed $response The API response
     * @return array Associative array with success status and message
     */
    function parse_api_response($response)
    {
        // Get response body as object
        $responseObj = json_decode($response->getBody());
        
        $result = [
            'success' => false,
            'message' => 'An error occurred',
            'data' => null
        ];
        
        if (isset($responseObj->status) && $responseObj->status === 'success') {
            $result['success'] = true;
            $result['message'] = $responseObj->message ?? 'Operation completed successfully';
            $result['data'] = $responseObj->data ?? null;
        } else if (isset($responseObj->message)) {
            $result['message'] = $responseObj->message;
        }
        
        return $result;
    }
}