<?php

namespace App\Controllers\Api;

use App\Models\AmbassadorModel;
use App\Models\ProgramModel;
use App\Models\ProgramCategoryModel;
use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;

class AmbassadorsApiController extends ApiBaseController
{
    protected $model;
    protected $programModel;
    protected $programCategoryModel;

    /**
     * Initialize controller, set models
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);

        // Initialize models
        $this->model = new AmbassadorModel();
        $this->programModel = new ProgramModel();
        $this->programCategoryModel = new ProgramCategoryModel();
    }

    /**
     * Create Ambassador
     * POST /api/ambassadors/create
     */
    public function create()
    {

        $name        = $this->request->getPost('name');
        $email       = $this->request->getPost('email');
        $refCode     = $this->request->getPost('ref_code');
        $programId   = $this->request->getPost('program_id');
        $institution = $this->request->getPost('institution');
        $gender      = $this->request->getPost('gender');

        if (empty($name) || empty($email) || empty($refCode) || empty($programId) || empty($institution) || empty($gender)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'All fields are required: name, email, ref_code, program_id, institution, gender.'
            ])->setStatusCode(400);
        }

        $existing = $this->model->where('email', $email)->first();
        if ($existing) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Ambassador with this email already exists.'
            ])->setStatusCode(409);
        }

        $data = [
            'name'        => $name,
            'email'       => $email,
            'ref_code'    => $refCode,
            'program_id'  => $programId,
            'institution' => $institution,
            'gender'      => $gender,
        ];

        try {
            $ambassador = $this->model->createAmbassador($data);

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Ambassador created successfully.',
                'data' => $ambassador
            ])->setStatusCode(201);

        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Generate referral link for an ambassador.
     * GET /api/ambassadors/{id}/generate-link
     */
    public function generateLink($id)
    {

        $ambassador = $this->model->find($id);

        if (!$ambassador) {
            return $this->failNotFound('Ambassador not found');
        }

        $program = $this->programModel->getProgramById($ambassador->program_id);
        $programCategoryId = $program->program_category_id ?? null;
        $programCategory = $this->programCategoryModel->getProgramCategoryById($programCategoryId);

        if (!$program || !$programCategory || !$programCategory->web_url) {
            return $this->failValidationErrors('Program or Program Category not found or web URL is missing');
        }

        $refCode = $ambassador->ref_code;
        $webUrl = $programCategory->web_url;

        $query = 'ref=' . $refCode;
        $encryptedQuery = url_encrypt($query);

        $referralLink = $webUrl . '/sign-up?' . urlencode($encryptedQuery);

        $data = [
            'ref_code' => $refCode,
            'web_url' => $webUrl,
            'encrypted_query' => $encryptedQuery,
            'referral_link' => $referralLink
        ];

        return $this->respondSuccess($data);
    }

    /**
     * 🔍 Check Encrypted Query
     * POST /api/ambassadors/check-query
     */
    public function checkEncryptedQuery()
    {
        try {
            // Get data from POST request
            $encryptedQuery = $this->request->getPost('encrypted_query');

            if (empty($encryptedQuery)) {
                return $this->fail('Encrypted query is required', 400);
            }

            // decode url
            $encryptedQuery = urldecode($encryptedQuery);

            // Try to decrypt the query
            try {
                // Set the second parameter to false to get a string instead of array
                $decryptedQuery = url_decrypt($encryptedQuery, false);

                if ($decryptedQuery === false) {
                    return $this->respondError('Decryption failed');
                }

                $params = [];

                // Parse the decrypted query string into an associative array
                // Only parse if it's a string
                if (is_string($decryptedQuery)) {
                    parse_str($decryptedQuery, $params);
                } else if (is_array($decryptedQuery)) {
                    // If already an array, use directly
                    $params = $decryptedQuery;
                }

                // get ambassador details by ref_code
                $ambassador = $this->model->getAmbassadorByRefCode($params['ref'] ?? null);

                if (!$ambassador) {
                    return $this->failNotFound('Ambassador not found');
                }

                $data = [
                    'ref_code' => $params['ref'] ?? null,
                    'is_valid' => true,
                    'ambassador' => $ambassador,
                ];

                return $this->respondSuccess($data);
            } catch (\Exception $e) {
                return $this->respondError('Decryption failed: ' . $e->getMessage(), 400);
            }
        } catch (\Exception $e) {
            return $this->failServerError('An error occurred: ' . $e->getMessage());
        }
    }
}