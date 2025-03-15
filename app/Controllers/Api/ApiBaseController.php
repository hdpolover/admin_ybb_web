<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class ApiBaseController extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';

    public function __construct()
    {
        helper(['form', 'url', 'filesystem']);

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
    }

    protected function apiResponse($data, $status = 200, $message = "Success", $pagination = null)
    {
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];

        if ($pagination) {
            $response['pagination'] = $pagination;
        }

        return $this->respond($response, $status);
    }
}