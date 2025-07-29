<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YBB Export Integration Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .test-result {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
        }
        .test-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .test-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .test-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .code-block {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1><i class="fas fa-vial me-2"></i>YBB Export Integration Test</h1>
                <p class="lead">Test the integration between CodeIgniter and the YBB Export API service.</p>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">API Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>API URL:</strong> <code id="apiUrl">Loading...</code><br>
                                <strong>Environment:</strong> <code><?= ENVIRONMENT ?></code><br>
                            </div>
                            <div class="col-md-6">
                                <strong>Current Time:</strong> <code><?= date('Y-m-d H:i:s') ?></code><br>
                                <strong>CodeIgniter Version:</strong> <code><?= \CodeIgniter\CodeIgniter::CI_VERSION ?></code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test 1: API Connection -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">1. API Connection Test</h5>
                        <button class="btn btn-primary btn-sm" onclick="testConnection()">
                            <i class="fas fa-plug me-1"></i>Test Connection
                        </button>
                    </div>
                    <div class="card-body">
                        <p>This test verifies that the YBB Export API service is accessible and responding.</p>
                        <div id="connectionResult"></div>
                    </div>
                </div>

                <!-- Test 2: Participant Export -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">2. Participant Export Test</h5>
                        <button class="btn btn-success btn-sm" onclick="testParticipantExport()">
                            <i class="fas fa-users me-1"></i>Test Export
                        </button>
                    </div>
                    <div class="card-body">
                        <p>This test exports sample participant data using the YBB Export API.</p>
                        <div id="participantResult"></div>
                    </div>
                </div>

                <!-- Test 3: Payment Export -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">3. Payment Export Test</h5>
                        <button class="btn btn-warning btn-sm" onclick="testPaymentExport()">
                            <i class="fas fa-credit-card me-1"></i>Test Export
                        </button>
                    </div>
                    <div class="card-body">
                        <p>This test exports sample payment data using the YBB Export API.</p>
                        <div id="paymentResult"></div>
                    </div>
                </div>

                <!-- Test 4: Templates -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">4. Templates Test</h5>
                        <button class="btn btn-info btn-sm" onclick="getTemplates()">
                            <i class="fas fa-file-alt me-1"></i>Get Templates
                        </button>
                    </div>
                    <div class="card-body">
                        <p>This test retrieves available export templates from the API.</p>
                        <div id="templatesResult"></div>
                    </div>
                </div>

                <!-- Test 5: Run All Tests -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">5. Run All Tests</h5>
                        <button class="btn btn-dark btn-sm" onclick="runAllTests()">
                            <i class="fas fa-play me-1"></i>Run All
                        </button>
                    </div>
                    <div class="card-body">
                        <p>Run all tests sequentially to verify complete integration.</p>
                        <div id="allTestsResult"></div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Setup Instructions</h5>
                    </div>
                    <div class="card-body">
                        <h6>1. Start the YBB Export API Service</h6>
                        <div class="code-block mb-3">
                            # Navigate to your Python Flask service directory<br>
                            cd /path/to/ybb-export-service<br><br>
                            # Install dependencies<br>
                            pip install -r requirements.txt<br><br>
                            # Start the service<br>
                            python -m flask run --port=5000<br><br>
                            # Or with gunicorn for production<br>
                            gunicorn -w 4 -b 0.0.0.0:5000 app:app
                        </div>

                        <h6>2. Environment Configuration</h6>
                        <p>Add the following to your <code>.env</code> file:</p>
                        <div class="code-block mb-3">
                            YBB_EXPORT_API_URL=http://localhost:5000<br>
                            YBB_EXPORT_API_TIMEOUT=300<br>
                            YBB_EXPORT_MAX_RECORDS=50000
                        </div>

                        <h6>3. Production Deployment</h6>
                        <ul>
                            <li>Deploy the Python Flask service to your production server</li>
                            <li>Update the <code>YBB_EXPORT_API_URL</code> to point to your production API</li>
                            <li>Ensure proper network connectivity between CodeIgniter and Flask service</li>
                            <li>Configure proper SSL certificates for HTTPS</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentExportId = null;

        function showResult(containerId, success, message, data = null) {
            const container = document.getElementById(containerId);
            const resultClass = success ? 'test-success' : 'test-error';
            
            let html = `<div class="test-result ${resultClass}">
                <strong>${success ? '✓' : '✗'} ${message}</strong>`;
            
            if (data) {
                html += `<br><small>Details: <code>${JSON.stringify(data, null, 2)}</code></small>`;
            }
            
            html += '</div>';
            container.innerHTML = html;
        }

        function showLoading(containerId) {
            const container = document.getElementById(containerId);
            container.innerHTML = `<div class="test-result test-info">
                <i class="fas fa-spinner fa-spin me-2"></i>Testing...
            </div>`;
        }

        async function testConnection() {
            showLoading('connectionResult');
            
            try {
                const response = await fetch('/test-export/test-connection');
                const result = await response.json();
                
                if (result.success) {
                    showResult('connectionResult', true, 'API connection successful', result.data);
                    document.getElementById('apiUrl').textContent = result.data.service || 'YBB Export API';
                } else {
                    showResult('connectionResult', false, result.message, result);
                }
            } catch (error) {
                showResult('connectionResult', false, 'Connection failed: ' + error.message);
            }
        }

        async function testParticipantExport() {
            showLoading('participantResult');
            
            try {
                const response = await fetch('/test-export/test-participant-export', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (result.success) {
                    currentExportId = result.data.export_id;
                    showResult('participantResult', true, 'Participant export successful', {
                        export_id: result.data.export_id,
                        record_count: result.data.record_count,
                        file_name: result.data.file_name
                    });
                } else {
                    showResult('participantResult', false, result.message, result);
                }
            } catch (error) {
                showResult('participantResult', false, 'Export failed: ' + error.message);
            }
        }

        async function testPaymentExport() {
            showLoading('paymentResult');
            
            try {
                const response = await fetch('/test-export/test-payment-export', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (result.success) {
                    showResult('paymentResult', true, 'Payment export successful', {
                        export_id: result.data.export_id,
                        record_count: result.data.record_count,
                        file_name: result.data.file_name
                    });
                } else {
                    showResult('paymentResult', false, result.message, result);
                }
            } catch (error) {
                showResult('paymentResult', false, 'Export failed: ' + error.message);
            }
        }

        async function getTemplates() {
            showLoading('templatesResult');
            
            try {
                const response = await fetch('/test-export/get-templates');
                const result = await response.json();
                
                if (result.success) {
                    showResult('templatesResult', true, 'Templates retrieved successfully', result.data);
                } else {
                    showResult('templatesResult', false, result.message, result);
                }
            } catch (error) {
                showResult('templatesResult', false, 'Templates retrieval failed: ' + error.message);
            }
        }

        async function runAllTests() {
            showLoading('allTestsResult');
            
            const tests = [
                { name: 'API Connection', func: testConnection },
                { name: 'Participant Export', func: testParticipantExport },
                { name: 'Payment Export', func: testPaymentExport },
                { name: 'Templates Retrieval', func: getTemplates }
            ];
            
            let results = [];
            
            for (const test of tests) {
                try {
                    await test.func();
                    results.push(`✓ ${test.name}: PASSED`);
                } catch (error) {
                    results.push(`✗ ${test.name}: FAILED - ${error.message}`);
                }
                
                // Wait 1 second between tests
                await new Promise(resolve => setTimeout(resolve, 1000));
            }
            
            const allPassed = results.every(r => r.includes('PASSED'));
            const resultClass = allPassed ? 'test-success' : 'test-error';
            
            document.getElementById('allTestsResult').innerHTML = `
                <div class="test-result ${resultClass}">
                    <strong>All Tests Completed</strong><br>
                    ${results.join('<br>')}
                </div>
            `;
        }

        // Auto-test connection on page load
        document.addEventListener('DOMContentLoaded', function() {
            testConnection();
        });
    </script>
</body>
</html>
