<?php

namespace App\Controllers;

use App\Libraries\YbbExport;

/**
 * Test Export Controller
 * 
 * This controller provides endpoints to test the YBB Export integration
 */
class TestExportController extends BaseController
{
    private YbbExport $ybbExport;

    public function __construct()
    {
        $this->ybbExport = new YbbExport();
    }

    /**
     * Test dashboard
     */
    public function index()
    {
        return view('test/export_test', [
            'title' => 'YBB Export Integration Test'
        ]);
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $result = $this->ybbExport->testConnection();
            
            return $this->response->setJSON([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test participant export with sample data
     */
    public function testParticipantExport()
    {
        try {
            $sampleData = [
                [
                    'id' => 1,
                    'form_id' => 'YBB2024_001',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+1234567890',
                    'birthdate' => '1995-05-15',
                    'nationality' => 'American',
                    'state' => 'California',
                    'form_status' => 1,
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 2,
                    'form_id' => 'YBB2024_002',
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane.smith@example.com',
                    'phone' => '+1234567891',
                    'birthdate' => '1996-03-20',
                    'nationality' => 'Canadian',
                    'state' => 'Ontario',
                    'form_status' => 2,
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ];

            $options = [
                'template' => 'standard',
                'format' => 'excel',
                'filename' => 'test_participants_' . date('Y-m-d_H-i-s') . '.xlsx'
            ];

            $result = $this->ybbExport->exportParticipants($sampleData, $options);

            return $this->response->setJSON($result);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Export test failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test payment export with sample data
     */
    public function testPaymentExport()
    {
        try {
            $sampleData = [
                [
                    'id' => 1,
                    'participant_id' => 1,
                    'payment_method_id' => 1,
                    'amount' => 1500.00,
                    'usd_amount' => 1500.00,
                    'payment_date' => '2024-01-20',
                    'payment_status' => 1,
                    'reference_number' => 'PAY_001_2024',
                    'notes' => 'Registration fee payment',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ];

            $options = [
                'template' => 'standard',
                'format' => 'excel',
                'filename' => 'test_payments_' . date('Y-m-d_H-i-s') . '.xlsx'
            ];

            $result = $this->ybbExport->exportPayments($sampleData, $options);

            return $this->response->setJSON($result);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment export test failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get export status
     */
    public function getExportStatus($exportId)
    {
        try {
            $result = $this->ybbExport->getExportStatus($exportId);

            return $this->response->setJSON($result);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Status check failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download export file
     */
    public function downloadExport($exportId)
    {
        try {
            $result = $this->ybbExport->downloadExport($exportId);

            if ($result['success']) {
                return $this->response->download($result['file_path'], null, true);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Download failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get available templates
     */
    public function getTemplates($exportType = null)
    {
        try {
            $result = $this->ybbExport->getTemplates($exportType);

            return $this->response->setJSON($result);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Templates retrieval failed: ' . $e->getMessage()
            ]);
        }
    }
}
