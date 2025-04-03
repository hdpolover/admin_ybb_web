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
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->model = new AmbassadorModel();
        $this->programModel = new ProgramModel();
        $this->programCategoryModel = new ProgramCategoryModel();
    }

    /**
     * Get all ambassadors with pagination and filtering.
     * GET /api/ambassadors
     */
    public function index()
    {
        try {
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 10);
            $offset = ($page - 1) * $limit;

            $filters = [];
            foreach ($this->request->getGet() as $key => $value) {
                if (!in_array($key, ['page', 'limit'])) {
                    $filters[$key] = $value;
                }
            }

            $result = $this->model->getAmbassadors($limit, $offset, $filters);

            if (!$result || empty($result['data'])) {
                return $this->failNotFound('Ambassadors not found');
            }

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
     * Get a single ambassador by ID.
     * GET /api/ambassadors/{id}
     */
    public function show($id = null)
    {
        $ambassador = $this->model->find($id);
        return $ambassador ? $this->apiResponse($ambassador) : $this->failNotFound("Ambassador not found");
    }

    /**
     * Get participants referred by an ambassador using ref code.
     * GET /api/ambassadors/participants/{refCode}
     */
    public function getParticipantsByRefCode($refCode = null)
    {
        try {
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 10);
            $offset = ($page - 1) * $limit;

            $filters = [];
            foreach ($this->request->getGet() as $key => $value) {
                if (!in_array($key, ['page', 'limit'])) {
                    $filters[$key] = $value;
                }
            }

            $filters['ref_code_ambassador'] = $refCode;

            $result = $this->model->getReferredParticipants($limit, $offset, $filters);

            if (!$result || empty($result['data'])) {
                return $this->failNotFound('Participants not found');
            }

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

        if (!$program) {
            return $this->failValidationError('Program not found');
        }

        $programCategory = $this->programCategoryModel->getProgramCategoryById($program->program_category_id);

        if (!$programCategory) {
            return $this->failValidationError('Program category not found');
        }

        $refCode = $ambassador->ref_code;
        $webUrl = $programCategory->web_url;

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