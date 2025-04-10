<?php

namespace App\Controllers;

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Response;

class ExampleClientController extends BaseController
{
    /**
     * Example method to create a transaction with proof image for manual transfers
     * 
     * @return Response
     */
    public function createManualTransaction()
    {
        // Initialize the client with base URL (adjust to your API endpoint)
        $client = \Config\Services::curlrequest([
            'baseURI' => 'https://your-api-domain.com/api',
            'timeout' => 30
        ]);
        
        // Get form data from submitted form
        $participantId = $this->request->getPost('participant_id');
        $programPaymentId = $this->request->getPost('program_payment_id');
        $paymentMethodId = $this->request->getPost('payment_method_id'); // Should be 2 for manual payment
        $accountName = $this->request->getPost('account_name');
        $sourceName = $this->request->getPost('source_name');
        $notes = $this->request->getPost('notes');
        $paymentDate = $this->request->getPost('payment_date');
        
        // Get the uploaded file
        $proofFile = $this->request->getFile('proof');
        
        // Check if we have a valid file
        if ($proofFile && $proofFile->isValid() && !$proofFile->hasMoved()) {
            // Create multipart data array for form submission
            $multipartData = [
                [
                    'name'     => 'participant_id',
                    'contents' => $participantId
                ],
                [
                    'name'     => 'program_payment_id',
                    'contents' => $programPaymentId
                ],
                [
                    'name'     => 'payment_method_id',
                    'contents' => $paymentMethodId
                ]
            ];
            
            // Add optional fields if they exist
            if (!empty($accountName)) {
                $multipartData[] = [
                    'name'     => 'account_name',
                    'contents' => $accountName
                ];
            }
            
            if (!empty($sourceName)) {
                $multipartData[] = [
                    'name'     => 'source_name',
                    'contents' => $sourceName
                ];
            }
            
            if (!empty($notes)) {
                $multipartData[] = [
                    'name'     => 'notes',
                    'contents' => $notes
                ];
            }
            
            if (!empty($paymentDate)) {
                $multipartData[] = [
                    'name'     => 'payment_date',
                    'contents' => $paymentDate
                ];
            }
            
            // Add the file to the multipart data
            $multipartData[] = [
                'name'     => 'proof',
                'contents' => fopen($proofFile->getTempName(), 'r'),
                'filename' => $proofFile->getName()
            ];
            
            try {
                // Make the API request
                $response = $client->post('payment/transaction/create', [
                    'multipart' => $multipartData,
                    'headers' => [
                        'Accept' => 'application/json',
                        // Add any authorization headers if needed
                        'Authorization' => 'Bearer ' . session()->get('token')
                    ]
                ]);
                
                // Parse the response
                $result = json_decode($response->getBody(), true);
                
                // Handle the response accordingly
                if ($response->getStatusCode() === 200) {
                    // Transaction was successful
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Manual transaction created successfully',
                        'data' => $result['data'] ?? $result
                    ]);
                } else {
                    // Something went wrong
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => $result['message'] ?? 'Failed to create transaction',
                        'errors' => $result['errors'] ?? null
                    ])->setStatusCode($response->getStatusCode());
                }
            } catch (\Exception $e) {
                log_message('error', 'API call error: ' . $e->getMessage());
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to connect to the payment server: ' . $e->getMessage()
                ])->setStatusCode(500);
            }
        } else {
            // No valid file was uploaded
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please upload a valid proof file'
            ])->setStatusCode(400);
        }
    }
    
    /**
     * Alternative approach using JavaScript fetch API from a form
     * This method just renders the view with the form
     */
    public function showTransactionForm()
    {
        return view('payment/manual_transaction_form');
    }
}
