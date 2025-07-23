<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;
use Config\Services;
use App\Traits\Cacheable;

class ApiBaseController extends ResourceController
{
    use ResponseTrait, Cacheable;

    protected $format = 'json';
    protected $request;

    /**
     * HTTP Status Codes
     */
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_NO_CONTENT = 204;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_UNPROCESSABLE_ENTITY = 422;
    const HTTP_INTERNAL_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;

    /**
     * Constructor - initialize helpers and set CORS headers
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Call parent initController first
        parent::initController($request, $response, $logger);
        
        // Ensure request and response properties are set
        $this->request = $request;
        $this->response = $response;
        
        helper(['form', 'url', 'filesystem']);

        // Set CORS headers
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
    }
    
    /**
     * Override respond method to ensure response object is available
     *
     * @param mixed   $data
     * @param integer $status
     * @param string  $message
     *
     * @return mixed
     */
    public function respond($data = null, int $status = null, string $message = '')
    {
        // Check if response is null and create a new one if needed
        if ($this->response === null) {
            $this->response = Services::response();
        }
        
        return parent::respond($data, $status, $message);
    }

    /**
     * Get input data from various sources (JSON, POST, PUT)
     * Works with any HTTP method including PUT/PATCH
     * 
     * @param string|null $key Specific input field to retrieve (null for all data)
     * @param mixed $default Default value if key not found
     * @return mixed The input data (array or single value)
     */
    protected function getInput($key = null, $default = null)
    {
        // Try to get JSON input first (application/json)
        $json = $this->request->getJSON(true);
        
        // Then check for form data based on request method
        $form = [];
        if (in_array($this->request->getMethod(), ['PUT', 'PATCH', 'DELETE'])) {
            // For PUT/PATCH/DELETE, use getRawInput()
            $form = $this->request->getRawInput();
        } else {
            // For POST/GET, use getPost()
            $form = $this->request->getPost();
        }
        
        // Combine the data with JSON taking precedence
        $data = array_merge($form, is_array($json) ? $json : []);
        
        // Return specific key or all data
        if ($key !== null) {
            return $data[$key] ?? $default;
        }
        
        return $data;
    }

    /**
     * Return success response with data
     * 
     * @param mixed $data Data to return
     * @param int $code HTTP status code
     * @param string $message Success message
     * @param array|null $pagination Pagination information
     * @param array $additional Additional data to include
     * @return ResponseInterface
     */
    protected function respondSuccess($data, int $code = self::HTTP_OK, string $message = 'Success', array $pagination = null, array $additional = []): ResponseInterface
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];

        if ($pagination) {
            $response['pagination'] = $pagination;
        }

        if (!empty($additional)) {
            $response = array_merge($response, $additional);
        }

        return $this->respond($response, $code);
    }

    /**
     * Return an empty success response
     * 
     * @param string $message Success message
     * @param int $code HTTP status code
     * @return ResponseInterface
     */
    protected function respondNoContent(string $message = 'No Content', int $code = self::HTTP_NO_CONTENT): ResponseInterface
    {
        return $this->respond([
            'status' => 'success',
            'message' => $message,
        ], $code);
    }

    /**
     * Return created response
     * 
     * @param mixed $data Created data
     * @param string $message Success message
     * @return mixed
     */
    protected function respondCreated($data = null, ?string $message = ''): mixed
    {
        return $this->respondSuccess($data, self::HTTP_CREATED, $message);
    }

    /**
     * Return error response
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param mixed $errors Additional error data
     * @return ResponseInterface
     */
    protected function respondError(string $message = 'An error occurred', int $code = self::HTTP_INTERNAL_ERROR, $errors = null): ResponseInterface
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->respond($response, $code);
    }

    /**
     * Return validation error response
     * 
     * @param mixed $errors Validation errors (string or array)
     * @param string $message Error message
     * @return ResponseInterface
     */
    protected function respondValidationErrors($errors, string $message = 'Validation error'): ResponseInterface
    {
        if (is_string($errors)) {
            $errors = ['message' => $errors];
        }

        return $this->respondError($message, self::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Return not found response
     * 
     * @param string $message Not found message
     * @return ResponseInterface
     */
    protected function respondNotFound(string $message = 'Resource not found'): ResponseInterface
    {
        return $this->respondError($message, self::HTTP_NOT_FOUND);
    }

    /**
     * Return unauthorized response
     * 
     * @param string $message Unauthorized message
     * @return ResponseInterface
     */
    protected function respondUnauthorized(string $message = 'Unauthorized'): ResponseInterface
    {
        return $this->respondError($message, self::HTTP_UNAUTHORIZED);
    }

    /**
     * Return forbidden response
     * 
     * @param string $message Forbidden message
     * @return ResponseInterface
     */
    protected function respondForbidden(string $message = 'Forbidden'): ResponseInterface
    {
        return $this->respondError($message, self::HTTP_FORBIDDEN);
    }

    /**
     * Return not implemented response
     * 
     * @param string $message Not implemented message
     * @return ResponseInterface
     */
    protected function respondNotImplemented(string $message = 'Not implemented'): ResponseInterface
    {
        return $this->respondError($message, self::HTTP_NOT_IMPLEMENTED);
    }

    /**
     * For backward compatibility
     */
    protected function apiResponse($data, $status = 200, $message = "Success", $pagination = null)
    {
        return $this->respondSuccess($data, $status, $message, $pagination);
    }
}