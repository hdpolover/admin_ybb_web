<?php

namespace App\Controllers\Api;

use App\Models\AmbassadorModel;
use App\Models\ProgramModel;
use App\Models\ProgramCategoryModel;
use App\Models\ParticipantModel;
use App\Models\AmbassadorParticipantReferralModel;
use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;

class AmbassadorsApiController extends ApiBaseController
{
    protected $model;
    protected $programModel;
    protected $programCategoryModel;
    protected $ambassadorParticipantReferralModel;
    protected $participantModel;

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
        $this->ambassadorParticipantReferralModel = new AmbassadorParticipantReferralModel();
        $this->participantModel = new ParticipantModel();
    }

    /**
     * Get all participants referred by an ambassador
     * GET /api/ambassadors/{id}/referrals
     */
    public function getAmbassadorReferrals($id)
    {
        // Check if ambassador exists
        $ambassador = $this->model->find($id);

        if (!$ambassador) {
            return $this->failNotFound('Ambassador not found');
        }

        try {
            // Get comprehensive list of participant IDs referred by this ambassador
            $participantIds = $this->model->getComprehensiveReferralParticipantIds($id);

            if (empty($participantIds)) {
                return $this->respondSuccess([
                    'referrals' => [],
                    'total' => 0
                ]);
            }

            // Get participant details
            $participants = $this->participantModel->getParticipantsByIds($participantIds);

            return $this->respondSuccess([
                'referrals' => $participants,
                'total' => count($participants)
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to retrieve referrals: ' . $e->getMessage());
        }
    }

    /**
     * Generate referral link for an ambassador.
     * GET /api/ambassadors/{id}/generate-link
     */
    public function generateLink($id)
    {
        try {
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
            $webUrl = rtrim($programCategory->web_url, '/'); // Remove trailing slash if present

            // Encrypt the ref code
            $query = $refCode;
            $encryptedQuery = url_encrypt($query);
            
            if ($encryptedQuery === false) {
                log_message('error', 'Failed to encrypt ref_code: ' . $refCode);
                return $this->failServerError('Failed to generate referral link');
            }
            
            // The url_encrypt function already makes the string URL-safe,
            // but we'll use urlencode as an extra precaution when adding to the URL
            $referralLink = $webUrl . '/sign-up?q=' . urlencode($encryptedQuery);

            $data = [
                'ref_code' => $refCode,
                'web_url' => $webUrl,
                'encrypted_query' => $encryptedQuery,
                'referral_link' => $referralLink
            ];
            
            log_message('info', 'Generated referral link for ambassador ID: ' . $id);
            
            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            log_message('error', 'Error generating referral link: ' . $e->getMessage());
            return $this->failServerError('An error occurred while generating the referral link: ' . $e->getMessage());
        }
    }

    /**
     * 🔍 Check Encrypted Query
     * GET /api/ambassadors/check-query
     */
    public function checkEncryptedQuery()
    {
        try {
            // Get data from GET request instead of POST
            $encryptedQuery = $this->request->getGet('encrypted_query');

            if (empty($encryptedQuery)) {
                log_message('error', 'Empty encrypted query provided');
                return $this->respondError('Encrypted query is required', 400);
            }

            // URL decode the encrypted query if needed
            $encryptedQuery = urldecode($encryptedQuery);
            
            // Trim any whitespace
            $encryptedQuery = trim($encryptedQuery);
            
            // Basic validation of the encrypted data format
            if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $encryptedQuery)) {
                log_message('error', 'Invalid encrypted query format: ' . $encryptedQuery);
                return $this->respondError('Invalid encrypted query format', 400);
            }

            // Try to decrypt the query
            try {
                // Set the second parameter to false to get a string instead of array
                $decryptedQuery = url_decrypt($encryptedQuery, false);

                if ($decryptedQuery === false) {
                    log_message('error', 'Decryption failed for query: ' . $encryptedQuery);
                    return $this->respondError('Decryption failed: Invalid query', 400);
                }

                // get ambassador details by ref_code
                $ambassador = $this->model->getByRefCode($decryptedQuery);

                if (!$ambassador) {
                    log_message('error', 'Ambassador not found for ref_code: ' . $decryptedQuery);
                    return $this->failNotFound('Ambassador not found');
                }

                $data = [
                    'ref_code' => $ambassador->ref_code,
                    'is_valid' => true,
                    'ambassador' => $ambassador,
                ];

                log_message('info', 'Successfully validated referral code: ' . $decryptedQuery);
                return $this->respondSuccess($data);
            } catch (\Exception $e) {
                log_message('error', 'Exception during decryption: ' . $e->getMessage());
                return $this->respondError('Decryption failed: ' . $e->getMessage(), 400);
            }
        } catch (\Exception $e) {
            log_message('error', 'Server error in checkEncryptedQuery: ' . $e->getMessage());
            return $this->failServerError('An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Get ambassador by ID
     * GET /api/ambassadors/{id}
     */
    public function getAmbassador($id)
    {
        $ambassador = $this->model->find($id);

        if (!$ambassador) {
            return $this->failNotFound('Ambassador not found');
        }

        return $this->respondSuccess($ambassador);
    }

    /**
     * Get ambassador by ref code
     * GET /api/ambassadors/programs/{programId}/ref-code/{refCode}
     */
    public function getAmbassadorByRefAndProgram($programId, $refCode)
    {
        $ambassador = $this->model->getAmbassadorByRefCodeAndProgramId($refCode, $programId);

        if (!$ambassador) {
            return $this->failNotFound('Ambassador referral code is not valid for this program. Please check the code and try again.');
        }

        $data = [
            'ref_code' => $ambassador->ref_code,
            'is_valid' => true,
            'ambassador' => $ambassador,
        ];

        return $this->respondSuccess($data);
    }
}
