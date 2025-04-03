<?php

namespace App\Controllers\Api;

use App\Models\AmbassadorModel;
use App\Models\ProgramModel;
use App\Controllers\Api\ApiBaseController;

class AmbassadorsApiController extends ApiBaseController
{
    protected $model;
    protected $programModel; 

    public function __construct()
    {
        parent::__construct();
        $this->model = new AmbassadorModel();
        $this->programModel = new ProgramModel();
    }

    /**
     * 🟢 Get All Ambassadors (READ)
     * GET /api/ambassadors
     */
    public function index()
    {
        try {
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 10);
            $offset = ($page - 1) * $limit;

            // Build filters from query params
            $filters = [];
         
            // Add any additional filters from query params
            foreach ($this->request->getGet() as $key => $value) {
                if (!in_array($key, ['page', 'limit'])) {
                    $filters[$key] = $value;
                }
            }

            // Get data using custom method
            $result = $this->model->getAmbassadors($limit, $offset, $filters);

            $totalPages = ceil($result['total'] / $limit);

            return $this->apiResponse($result['data'], 200, "Success", [
                'current_page' => $page,
                'per_page' => $limit,
                'total_items' => $result['total'],
                'total_pages' => $totalPages
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * 🔍 Get Single Ambassador (READ)
     * GET /api/ambassadors/{id}
     */
    public function show($id = null)
    {
        $ambassador = $this->model->find($id);
        return $ambassador ? $this->apiResponse($ambassador) : $this->failNotFound("Ambassador not found");
    }

    // get participants based on ambassador ref code
    public function getParticipantsByRefCode($refCode = null)
    {
        try {
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 10);
            $offset = ($page - 1) * $limit;

            // Build filters from query params
            $filters = [];
         
            // Add any additional filters from query params
            foreach ($this->request->getGet() as $key => $value) {
                if (!in_array($key, ['page', 'limit'])) {
                    $filters[$key] = $value;
                }
            }

            $filters['ref_code_ambassador'] = $refCode;

            // Get data using custom method
            $result = $this->model->getReferredParticipants($limit, $offset, $filters);

            $totalPages = ceil($result['total'] / $limit);

            return $this->apiResponse($result['data'], 200, "Success", [
                'current_page' => $page,
                'per_page' => $limit,
                'total_items' => $result['total'],
                'total_pages' => $totalPages
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * 🔍 Generate link referal (READ)
     * GET /api/ambassadors/{id}/generate-link
     */
    public function generateLink($id)
    {
        $ambassador = $this->model->find($id);

        if (!$ambassador) {
            return $this->failNotFound('Ambassador not found');
        }

        $program = $this->programModel->getProgramById($ambassador->program_id);

        if (!$program || !$program->web_url) {
            return $this->failValidationError('web_url is not set for this program');
        }

        $refCode = $ambassador->ref_code;
        $webUrl = $program->web_url;
        
        $query = 'ref=' . $refCode;
        $encryptedQuery = $this->encrypt_decrypt($query, 'encrypt');

        $referralLink = $webUrl . '/sign-up?' . urlencode($encryptedQuery);

        return $this->apiResponse(['link' => $referralLink], 200, "Referral link generated successfully");
    }

    private function encrypt_decrypt($string, $action = 'encrypt', $secret_key = 'ybb_program')
    {
        $secret_iv = 'ybb_iv';
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
}
