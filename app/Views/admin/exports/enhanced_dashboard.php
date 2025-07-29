<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Data Export - YBB Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .export-result {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .export-result.success {
            border-color: #28a745;
            background-color: #f8fff9;
        }

        .export-result.error {
            border-color: #dc3545;
            background-color: #fff8f8;
        }

        .export-header h4 {
            margin-bottom: 10px;
        }

        .export-details, .export-summary {
            margin: 15px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .stat-card {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 5px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
        }

        .download-option {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .download-option.recommended {
            border-color: #28a745;
            background-color: #f8fff9;
        }

        .download-option h6 {
            color: #007bff;
            margin-bottom: 10px;
        }

        .file-item {
            transition: background-color 0.2s;
        }

        .file-item:hover {
            background-color: #f8f9fa;
        }

        .export-filters {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .processing-info {
            border-left: 4px solid #007bff;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-download"></i> Enhanced Data Export Dashboard</h3>
                        <p class="text-muted mb-0">Export participants, payments, and ambassador data with enhanced filename support and multi-file handling</p>
                    </div>
                    <div class="card-body">
                        
                        <!-- API Health Check -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="mb-1">
                                                <i class="fas fa-heartbeat text-success"></i> YBB Export API Status
                                            </h6>
                                            <p class="mb-0">
                                                <strong>Endpoint:</strong> 
                                                <code>https://ybb-data-management-service-production.up.railway.app</code>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button id="test-connection-btn" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-wifi"></i> Test Connection
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div id="connection-status" class="mt-3" style="display: none;">
                                        <!-- Connection status will be displayed here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Export Filters -->
                        <div class="export-filters">
                            <h5>Export Filters</h5>
                            <form id="export-filters-form">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="program_id">Program</label>
                                            <select class="form-control" id="program_id" name="program_id" required>
                                                <option value="">Select Program</option>
                                                <option value="1">Japan Youth Summit 2025</option>
                                                <option value="2">Global Leadership Program</option>
                                                <option value="3">Innovation Workshop Series</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="category">Category</label>
                                            <select class="form-control" id="category" name="category">
                                                <option value="all">All Categories</option>
                                                <option value="fully_funded">Fully Funded</option>
                                                <option value="self_funded">Self Funded</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="form_status">Form Status</label>
                                            <select class="form-control" id="form_status" name="form_status">
                                                <option value="all">All Statuses</option>
                                                <option value="0">Draft</option>
                                                <option value="1">Submitted</option>
                                                <option value="2">Approved</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="payment_status">Payment Status</label>
                                            <select class="form-control" id="payment_status" name="payment_status">
                                                <option value="all">All Payment Statuses</option>
                                                <option value="0">Created</option>
                                                <option value="1">Pending</option>
                                                <option value="2">Successful</option>
                                                <option value="3">Cancelled</option>
                                                <option value="4">Rejected</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_from">Date From</label>
                                            <input type="date" class="form-control" id="date_from" name="date_from">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_to">Date To</label>
                                            <input type="date" class="form-control" id="date_to" name="date_to">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="button" class="btn btn-secondary" onclick="$('#export-filters-form')[0].reset()">
                                                    <i class="fas fa-undo"></i> Reset Filters
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Export Actions -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5><i class="fas fa-users"></i> Participants Export</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Export participant registration data including essays, personal information, and status details.</p>
                                        <div class="d-grid">
                                            <button type="button" 
                                                    class="btn btn-primary btn-lg export-btn" 
                                                    data-export-type="participants"
                                                    data-url="/admin/export/participants">
                                                <i class="fas fa-download"></i> Export Participants
                                            </button>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            Includes essays, personal data, and comprehensive registration information
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h5><i class="fas fa-credit-card"></i> Payments Export</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Export payment transaction data including participant details and payment methods.</p>
                                        <div class="d-grid">
                                            <button type="button" 
                                                    class="btn btn-success btn-lg export-btn" 
                                                    data-export-type="payments"
                                                    data-url="/admin/export/payments">
                                                <i class="fas fa-download"></i> Export Payments
                                            </button>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            Includes transaction details, participant context, and payment history
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h5><i class="fas fa-star"></i> Ambassadors Export</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Export ambassador program data and referral information.</p>
                                        <div class="d-grid">
                                            <button type="button" 
                                                    class="btn btn-info btn-lg export-btn" 
                                                    data-export-type="ambassadors"
                                                    data-url="/admin/export/ambassadors">
                                                <i class="fas fa-download"></i> Export Ambassadors
                                            </button>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            Includes ambassador profiles and referral tracking data
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Export Results -->
                        <div id="export-results"></div>

                        <!-- Features Demo -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5><i class="fas fa-star"></i> Enhanced Export Features</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-file-signature text-primary"></i> Descriptive Filenames</h6>
                                        <p>Files are automatically named with program, type, filters, and date:</p>
                                        <code class="d-block bg-light p-2 rounded">Japan_Youth_Summit_Participants_Complete_Registration_Data_26-07-2025.xlsx</code>
                                        
                                        <h6 class="mt-3"><i class="fas fa-layer-group text-success"></i> Intelligent Sheet Names</h6>
                                        <p>Excel sheets have meaningful names instead of generic "Sheet1":</p>
                                        <code class="d-block bg-light p-2 rounded">Participants Data Jul 2025</code>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-archive text-warning"></i> Multi-File Support</h6>
                                        <p>Large datasets automatically split into manageable files with ZIP archive:</p>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-file text-muted"></i> Batch files for individual access</li>
                                            <li><i class="fas fa-file-archive text-success"></i> Complete ZIP archive</li>
                                            <li><i class="fas fa-compress text-info"></i> Automatic compression</li>
                                        </ul>
                                        
                                        <h6 class="mt-3"><i class="fas fa-clock text-info"></i> Processing Time Tracking</h6>
                                        <p>Real-time processing time display and performance metrics.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Enhanced Export Manager -->
    <script src="/assets/js/enhanced-export-manager.js?v=<?= time() ?>&bust=<?= uniqid() ?>"></script>
    
    <script>
        // Emergency override to stop all intervals when completion is detected
        let originalAjax = $.ajax;
        $.ajax = function(options) {
            let originalSuccess = options.success;
            if (originalSuccess) {
                options.success = function(data) {
                    if (data && (data.status === 'completed' || data.status === 'ready' || data.downloadUrl)) {
                        console.log('🛑 EMERGENCY STOP: Completion detected in AJAX response');
                        console.log('Data:', data);
                        
                        // Nuclear option - clear ALL intervals
                        if (window.forceStopAllIntervals) {
                            window.forceStopAllIntervals();
                        }
                        
                        // Also clear any high-numbered intervals (likely our polling intervals)
                        for (let i = 1; i < 10000; i++) {
                            try {
                                clearInterval(i);
                            } catch (e) {
                                // Ignore errors
                            }
                        }
                    }
                    return originalSuccess.apply(this, arguments);
                };
            }
            return originalAjax.apply(this, arguments);
        };
        
        // Demo functionality - simulate different export scenarios
        $(document).ready(function() {
            // Initialize Enhanced Export Manager
            window.enhancedExportManager = new EnhancedExportManager();
            
            // Add demo data to simulate real exports
            $('#program_id').val('1'); // Default to first program
            
            // Test API Connection
            $('#test-connection-btn').on('click', function() {
                const $btn = $(this);
                const $status = $('#connection-status');
                
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testing...');
                $status.hide();
                
                $.ajax({
                    url: '/admin/exports/test-connection',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $status.html(`
                                <div class="alert alert-success alert-sm">
                                    <h6><i class="fas fa-check-circle"></i> Connection Successful</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Service:</strong> ${response.service}</p>
                                            <p class="mb-0"><strong>Status:</strong> <span class="badge bg-success">${response.status}</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Version:</strong> ${response.version}</p>
                                            <p class="mb-0"><strong>Last Check:</strong> ${new Date(response.timestamp).toLocaleString()}</p>
                                        </div>
                                    </div>
                                </div>
                            `).show();
                        } else {
                            $status.html(`
                                <div class="alert alert-danger alert-sm">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Connection Failed</h6>
                                    <p class="mb-0">${response.message}</p>
                                </div>
                            `).show();
                        }
                    },
                    error: function(xhr) {
                        $status.html(`
                            <div class="alert alert-danger alert-sm">
                                <h6><i class="fas fa-exclamation-triangle"></i> Connection Error</h6>
                                <p class="mb-1">Unable to connect to the export service. Please check the server status.</p>
                                <small class="text-muted">Error: ${xhr.status} ${xhr.statusText}</small>
                            </div>
                        `).show();
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-wifi"></i> Test Connection');
                    }
                });
            });
            
            // Show sample export result on page load for demonstration
            setTimeout(() => {
                const sampleResponse = {
                    success: true,
                    exportId: 'demo-export-123',
                    fileName: 'Japan_Youth_Summit_Participants_Complete_Registration_Data_26-07-2025.xlsx',
                    downloadUrl: '#demo-download',
                    recordCount: 1234,
                    processingTime: 3.2,
                    exportStrategy: 'single_file'
                };
                
                // Uncomment to show demo result on load
                // window.exportManager.showSingleFileExportResult(sampleResponse, { getServerTime: () => 3.2, getClientTime: () => 3.5 });
            }, 1000);
        });
    </script>
</body>
</html>
