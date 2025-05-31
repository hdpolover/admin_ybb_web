<?php

namespace App\Controllers\Api;

use App\Models\ProgramModel;
use App\Models\AbstractSettingModel;



use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;

class AbstractSettingsApiController extends ApiBaseController
{

    protected $programModel;
    protected $abstractSettingModel;

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
        $this->programModel = new ProgramModel();
        $this->abstractSettingModel = new AbstractSettingModel();
    }

    /**
     * Get abstract settings by program ID
     *
     * @param int $programId The program ID
     * @return ResponseInterface
     */
    public function getAbstractSettingsByProgramId($programId = null)
    {
        // Validate program ID
        if (empty($programId) || !is_numeric($programId)) {
            return $this->respondValidationErrors('Invalid program ID');
        }

        // Check if program exists
        $program = $this->programModel->find($programId);
        if (!$program) {
            return $this->respondNotFound('Program not found');
        }

        // Get abstract settings by program ID
        $settings = $this->abstractSettingModel->getByProgramId($programId);
        if (!$settings) {
            return $this->respondNotFound('Abstract settings not found for this program');
        }

        return $this->respondSuccess($settings);
    }
}
