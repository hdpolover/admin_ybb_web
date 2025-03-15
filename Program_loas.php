

<?php

defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

class Program_Loas extends RestController
{

    function __construct()
    {
        parent::__construct();
    }

    function index_get()
    {
        $id = $this->get('id');
        if ($id == '') {
            $loas = $this->mCore->get_data('program_loas', ['is_active' => 1])->result_array();
            if ($loas) {
                $this->response([
                    'status' => true,
                    'data' => $loas
                ], 200);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'No result were found'
                ], 404);
            }
        } else {
            $loas = $this->mCore->get_data('program_loas', ['id' => $id, 'is_active' => 1])->row();
            if ($loas) {
                $this->response([
                    'status' => true,
                    'data' => $loas
                ], 200);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'No result were found'
                ], 404);
            }
        }
    }

    function list_get()
    {
        $program_id = $this->get('program_id');

        $loas = $this->mCore->get_data('program_loas', ['program_id' => $program_id, 'is_active' => 1])->result_array();
        if ($loas) {
            $this->response([
                'status' => true,
                'data' => $loas
            ], 200);
        } else {
            $this->response([
                'status' => false,
                'message' => 'No result were found'
            ], 404);
        }
    }

    function generate_get()
    {
        $program_id = $this->get('program_id');
        
        // Get the LOA template information for this program
        $loas = $this->mCore->get_data('program_loas', ['program_id' => $program_id, 'is_active' => 1])->row_array();
        
        if (!$loas) {
            $this->response([
                'status' => false,
                'message' => 'No LOA template found for this program'
            ], 404);
            return;
        }
        
        // Parse the required fields from the template configuration
        $required_fields = json_decode($loas['required_fields'] ?? '[]', true);
        if (empty($required_fields)) {
            $required_fields = ['author_names', 'paper_title']; // Default fields if none specified
        }
        
        // Check for missing required fields
        $missing_fields = [];
        foreach ($required_fields as $field) {
            if (!$this->get($field)) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            $this->response([
                'status' => false,
                'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
            ], 400);
            return;
        }
        
        // Build data array dynamically based on required fields
        $data = [
            'template' => $loas['template_name'],
        ];
        
        // Get each required field from request and process it
        foreach ($required_fields as $field) {
            $value = $this->get($field);
            // Apply formatting if needed (can be extended with custom formatting per field)
            if (in_array($field, ['author_names', 'paper_title', 'institution', 'name'])) {
                $value = ucwords($value);
            }
            $data[$field] = $value;
        }
        
        // Generate PDF
        $this->load->library('pdf');
        $this->pdf->set_paper('A4', 'portrait'); // Fixed typo: 'potrait' to 'portrait'
        
        // Use author_names or name for filename, with fallback
        $filename = $data['author_names'] ?? $data['name'] ?? 'document';
        $filename = preg_replace('/[^a-zA-Z0-9]/', '_', $filename); // Sanitize filename
        $this->pdf->filename = $filename . ".pdf";
        $this->pdf->load_view('pdf/' . $data['template'], $data);
    }
    
    function get_details_get()
    {
        $participant_id = $this->get('participant_id');

        $author = $this->mCore->get_data('paper_authors', ['participant_id' => $participant_id, 'is_active' => "1"])->row_array();

        // check if author found
        if ($author) {
            $paper_detail = $this->mCore->get_data('paper_details', ['id' => $author['paper_detail_id'], 'is_active' => "1"])->row_array();

            // check if paper detail found
            if ($paper_detail) {
                $paper_abstract = $this->mCore->get_data('paper_abstracts', ['id' => $paper_detail['paper_abstract_id'], 'is_active' => "1"])->row_array();

                // check if paper abstract found
                if ($paper_abstract) {
                    // check if abstract status is 2
                    if ($paper_abstract['status'] != 2) {
                        $this->response([
                            'status' => false,
                            'message' => 'No abstracts with accepetd status were found'
                        ], 404);
                    } else {
                        // get list of authors
                        $authors = $this->mCore->get_data('paper_authors', ['paper_detail_id' => $paper_detail['id'], 'is_active' => "1"])->result_array();

                        $author_names = [];

                        foreach ($authors as $author) {
                            $author_names[] = $author['name'];
                        }

                        $data = [
                            'paper_title' => $paper_abstract['title'],
                            'author_names' => implode(",", $author_names),
                        ];
                        $this->response([
                            'status' => true,
                            'data' => $data
                        ], 200);
                    }
                    
                } else {
                    $this->response([
                        'status' => false,
                        'message' => 'No paper abstract were found'
                    ], 404);
                }
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'No paper details were found'
                ], 404);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'No authors were found'
            ], 404);
        }

        
    }
}
