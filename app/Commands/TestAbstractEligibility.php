<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Controllers\Api\AbstractsApiController;
use CodeIgniter\HTTP\IncomingRequest;
use Config\Services;

class TestAbstractEligibility extends BaseCommand
{
    protected $group = 'Custom';
    protected $name = 'test:abstract:eligibility';
    protected $description = 'Test if participant is eligible for abstract submission';

    public function run(array $params)
    {
        $participantId = $params[0] ?? CLI::prompt('Participant ID');
        
        // Create the controller
        $request = Services::request();
        $response = Services::response();
        $logger = Services::logger();
        
        $controller = new AbstractsApiController();
        $controller->initController($request, $response, $logger);
        
        // Check eligibility
        $isEligible = $controller->isEligibleForSubmission($participantId);
        
        if ($isEligible) {
            CLI::write('Participant is ELIGIBLE for abstract submission', 'green');
        } else {
            CLI::error('Participant is NOT ELIGIBLE for abstract submission');
        }
    }
}
