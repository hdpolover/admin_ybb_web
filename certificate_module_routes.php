<?php

/*
|--------------------------------------------------------------------------
| Certificate Module Routes
|--------------------------------------------------------------------------
|
| Add these routes to your app/Config/Routes.php file
| These routes provide a complete RESTful API for the certificate module
|
*/

// Program Awards Routes
$routes->group('api/program-awards', ['namespace' => 'App\Controllers'], static function ($routes) {
    $routes->get('/', 'ProgramAwards::index');                    // GET /api/program-awards
    $routes->get('(:num)', 'ProgramAwards::show/$1');             // GET /api/program-awards/1
    $routes->post('/', 'ProgramAwards::create');                  // POST /api/program-awards
    $routes->put('(:num)', 'ProgramAwards::update/$1');           // PUT /api/program-awards/1
    $routes->delete('(:num)', 'ProgramAwards::delete/$1');        // DELETE /api/program-awards/1
    $routes->get('program/(:num)', 'ProgramAwards::byProgram/$1'); // GET /api/program-awards/program/1
});

// Program Certificates Routes
$routes->group('api/program-certificates', ['namespace' => 'App\Controllers'], static function ($routes) {
    $routes->get('/', 'ProgramCertificates::index');              // GET /api/program-certificates
    $routes->get('(:num)', 'ProgramCertificates::show/$1');       // GET /api/program-certificates/1
    $routes->post('/', 'ProgramCertificates::create');            // POST /api/program-certificates
    $routes->put('(:num)', 'ProgramCertificates::update/$1');     // PUT /api/program-certificates/1
    $routes->delete('(:num)', 'ProgramCertificates::delete/$1');  // DELETE /api/program-certificates/1
    $routes->get('program/(:num)', 'ProgramCertificates::byProgram/$1'); // GET /api/program-certificates/program/1
    $routes->put('(:num)/publish', 'ProgramCertificates::publish/$1');   // PUT /api/program-certificates/1/publish
    $routes->get('published', 'ProgramCertificates::published');  // GET /api/program-certificates/published
});

// Program Certificate Content Blocks Routes
$routes->group('api/certificate-content-blocks', ['namespace' => 'App\Controllers'], static function ($routes) {
    $routes->get('/', 'ProgramCertificateContentBlocks::index'); // GET /api/certificate-content-blocks
    $routes->get('(:num)', 'ProgramCertificateContentBlocks::show/$1'); // GET /api/certificate-content-blocks/1
    $routes->post('/', 'ProgramCertificateContentBlocks::create'); // POST /api/certificate-content-blocks
    $routes->put('(:num)', 'ProgramCertificateContentBlocks::update/$1'); // PUT /api/certificate-content-blocks/1
    $routes->delete('(:num)', 'ProgramCertificateContentBlocks::delete/$1'); // DELETE /api/certificate-content-blocks/1
    $routes->get('certificate/(:num)', 'ProgramCertificateContentBlocks::byCertificate/$1'); // GET /api/certificate-content-blocks/certificate/1
    $routes->put('(:num)/position', 'ProgramCertificateContentBlocks::updatePosition/$1'); // PUT /api/certificate-content-blocks/1/position
    $routes->post('bulk', 'ProgramCertificateContentBlocks::bulkCreate'); // POST /api/certificate-content-blocks/bulk
});

// Participant Awards Routes
$routes->group('api/participant-awards', ['namespace' => 'App\Controllers'], static function ($routes) {
    $routes->get('/', 'ParticipantAwards::index');                // GET /api/participant-awards
    $routes->get('(:num)', 'ParticipantAwards::show/$1');         // GET /api/participant-awards/1
    $routes->post('/', 'ParticipantAwards::create');              // POST /api/participant-awards
    $routes->put('(:num)', 'ParticipantAwards::update/$1');       // PUT /api/participant-awards/1
    $routes->delete('(:num)', 'ParticipantAwards::delete/$1');    // DELETE /api/participant-awards/1
    $routes->get('participant/(:num)', 'ParticipantAwards::byParticipant/$1'); // GET /api/participant-awards/participant/1
    $routes->get('award/(:num)', 'ParticipantAwards::byAward/$1'); // GET /api/participant-awards/award/1
    $routes->post('bulk-assign', 'ParticipantAwards::bulkAssign'); // POST /api/participant-awards/bulk-assign
});

// Participant Certificates Routes
$routes->group('api/participant-certificates', ['namespace' => 'App\Controllers'], static function ($routes) {
    $routes->get('/', 'ParticipantCertificates::index');          // GET /api/participant-certificates
    $routes->get('(:num)', 'ParticipantCertificates::show/$1');   // GET /api/participant-certificates/1
    $routes->post('/', 'ParticipantCertificates::create');        // POST /api/participant-certificates
    $routes->put('(:num)', 'ParticipantCertificates::update/$1'); // PUT /api/participant-certificates/1
    $routes->delete('(:num)', 'ParticipantCertificates::delete/$1'); // DELETE /api/participant-certificates/1
    $routes->get('participant/(:num)', 'ParticipantCertificates::byParticipant/$1'); // GET /api/participant-certificates/participant/1
    $routes->get('certificate/(:num)', 'ParticipantCertificates::byCertificate/$1'); // GET /api/participant-certificates/certificate/1
    $routes->get('award/(:num)', 'ParticipantCertificates::byAward/$1'); // GET /api/participant-certificates/award/1
    $routes->post('bulk-generate', 'ParticipantCertificates::bulkGenerate'); // POST /api/participant-certificates/bulk-generate
});

/*
|--------------------------------------------------------------------------
| Alternative Route Structure (Non-API)
|--------------------------------------------------------------------------
|
| If you prefer traditional controller routes without the 'api' prefix:
|

// Program Awards
$routes->resource('program-awards', ['controller' => 'ProgramAwards']);
$routes->get('program-awards/program/(:num)', 'ProgramAwards::byProgram/$1');

// Program Certificates
$routes->resource('program-certificates', ['controller' => 'ProgramCertificates']);
$routes->get('program-certificates/program/(:num)', 'ProgramCertificates::byProgram/$1');
$routes->put('program-certificates/(:num)/publish', 'ProgramCertificates::publish/$1');
$routes->get('program-certificates/published', 'ProgramCertificates::published');

// Certificate Content Blocks
$routes->resource('certificate-content-blocks', ['controller' => 'ProgramCertificateContentBlocks']);
$routes->get('certificate-content-blocks/certificate/(:num)', 'ProgramCertificateContentBlocks::byCertificate/$1');
$routes->put('certificate-content-blocks/(:num)/position', 'ProgramCertificateContentBlocks::updatePosition/$1');
$routes->post('certificate-content-blocks/bulk', 'ProgramCertificateContentBlocks::bulkCreate');

// Participant Awards
$routes->resource('participant-awards', ['controller' => 'ParticipantAwards']);
$routes->get('participant-awards/participant/(:num)', 'ParticipantAwards::byParticipant/$1');
$routes->get('participant-awards/award/(:num)', 'ParticipantAwards::byAward/$1');
$routes->post('participant-awards/bulk-assign', 'ParticipantAwards::bulkAssign');

// Participant Certificates
$routes->resource('participant-certificates', ['controller' => 'ParticipantCertificates']);
$routes->get('participant-certificates/participant/(:num)', 'ParticipantCertificates::byParticipant/$1');
$routes->get('participant-certificates/certificate/(:num)', 'ParticipantCertificates::byCertificate/$1');
$routes->get('participant-certificates/award/(:num)', 'ParticipantCertificates::byAward/$1');
$routes->post('participant-certificates/bulk-generate', 'ParticipantCertificates::bulkGenerate');

*/

/*
|--------------------------------------------------------------------------
| Example Usage
|--------------------------------------------------------------------------
|
| Here are some example API calls you can make:
|
| 1. Create a Program Award:
|    POST /api/program-awards
|    {
|        "program_id": 1,
|        "title": "Best Innovation",
|        "description": "Award for the most innovative project",
|        "award_type": "winner",
|        "order_number": 1
|    }
|
| 2. Get Awards for a Program:
|    GET /api/program-awards/program/1
|
| 3. Create a Certificate:
|    POST /api/program-certificates
|    {
|        "program_id": 1,
|        "award_id": 1,
|        "template_url": "https://example.com/certificate-template.png",
|        "issue_date": "2024-01-15"
|    }
|
| 4. Add Content Blocks to Certificate:
|    POST /api/certificate-content-blocks/bulk
|    {
|        "blocks": [
|            {
|                "certificate_id": 1,
|                "type": "text",
|                "value": "Certificate of Achievement",
|                "x": 100,
|                "y": 50,
|                "font_size": 24,
|                "font_weight": "bold"
|            },
|            {
|                "certificate_id": 1,
|                "type": "placeholder",
|                "value": "{participant_name}",
|                "x": 200,
|                "y": 150,
|                "font_size": 18
|            }
|        ]
|    }
|
| 5. Assign Award to Participants (Bulk):
|    POST /api/participant-awards/bulk-assign
|    {
|        "participant_ids": [1, 2, 3],
|        "award_id": 1,
|        "assigned_by": 1,
|        "notes": "Outstanding performance"
|    }
|
| 6. Generate Certificates for Participants (Bulk):
|    POST /api/participant-certificates/bulk-generate
|    {
|        "participant_ids": [1, 2, 3],
|        "certificate_id": 1,
|        "award_id": 1
|    }
|
*/
