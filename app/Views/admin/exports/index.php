<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Export Dashboard - YBB Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-download me-2"></i>Data Export Dashboard</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item active">Exports</li>
                        </ol>
                    </nav>
                </div>

                <!-- Export Status Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Export Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-rocket me-2"></i>
                                    <strong>New Export System:</strong> We've upgraded to a Python Flask-based export service that can handle large datasets (50,000+ records) with improved performance and reliability.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                                <h5>Participants Export</h5>
                                                <p class="text-muted">Export participant data with custom filters</p>
                                                <button class="btn btn-primary" onclick="initiateExport('participants')">
                                                    <i class="fas fa-download me-2"></i>Export Participants
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <i class="fas fa-credit-card fa-3x text-success mb-3"></i>
                                                <h5>Payments Export</h5>
                                                <p class="text-muted">Export payment transactions and records</p>
                                                <button class="btn btn-success" onclick="initiateExport('payments')">
                                                    <i class="fas fa-download me-2"></i>Export Payments
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <i class="fas fa-star fa-3x text-warning mb-3"></i>
                                                <h5>Ambassadors Export</h5>
                                                <p class="text-muted">Export ambassador data and referrals</p>
                                                <button class="btn btn-warning" onclick="initiateExport('ambassadors')">
                                                    <i class="fas fa-download me-2"></i>Export Ambassadors
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Exports Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clock me-2"></i>Active Exports
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="activeExports">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-hourglass-half fa-2x mb-2"></i>
                                        <p>No active exports</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export History Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>Export History
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="exportHistory">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-file-download fa-2x mb-2"></i>
                                        <p>No export history available</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Progress Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Progress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="exportProgress">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p>Initiating export...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let activeExports = new Map();
        
        function initiateExport(type) {
            const modal = new bootstrap.Modal(document.getElementById('exportModal'));
            modal.show();
            
            // Make request to initiate export
            fetch(`/exports/${type}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    template: 'standard',
                    format: 'excel'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store export info
                    activeExports.set(data.exportId, {
                        type: type,
                        status: 'processing',
                        recordCount: data.recordCount,
                        estimatedTime: data.estimatedTime
                    });
                    
                    // Update modal with success message
                    document.getElementById('exportProgress').innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Export initiated successfully!<br>
                            <small>Export ID: ${data.exportId}</small><br>
                            <small>Records: ${data.recordCount}</small>
                            ${data.estimatedTime ? `<br><small>Estimated time: ${data.estimatedTime}</small>` : ''}
                        </div>
                        <p>You can close this dialog and check the progress in the Active Exports section.</p>
                    `;
                    
                    // Start polling for status
                    pollExportStatus(data.exportId);
                    
                    // Update active exports display
                    updateActiveExportsDisplay();
                    
                } else {
                    // Show error message
                    document.getElementById('exportProgress').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Export failed: ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Export error:', error);
                document.getElementById('exportProgress').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        An error occurred: ${error.message}
                    </div>
                `;
            });
        }
        
        function pollExportStatus(exportId) {
            const poll = () => {
                fetch(`/exports/status/${exportId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const exportInfo = activeExports.get(exportId);
                        if (exportInfo) {
                            exportInfo.status = data.data.status;
                            exportInfo.progress = data.data.progress;
                            updateActiveExportsDisplay();
                            
                            if (data.data.status === 'completed') {
                                // Stop polling
                                return;
                            } else if (data.data.status === 'failed') {
                                // Stop polling on failure
                                return;
                            }
                        }
                    }
                    
                    // Continue polling if not completed
                    if (activeExports.has(exportId)) {
                        setTimeout(poll, 3000); // Poll every 3 seconds
                    }
                })
                .catch(error => {
                    console.error('Status polling error:', error);
                    setTimeout(poll, 5000); // Retry after 5 seconds on error
                });
            };
            
            poll();
        }
        
        function updateActiveExportsDisplay() {
            const container = document.getElementById('activeExports');
            
            if (activeExports.size === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-hourglass-half fa-2x mb-2"></i>
                        <p>No active exports</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            activeExports.forEach((exportInfo, exportId) => {
                const statusClass = exportInfo.status === 'completed' ? 'success' : 
                                  exportInfo.status === 'failed' ? 'danger' : 'primary';
                
                html += `
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">${exportInfo.type.toUpperCase()} Export</h6>
                                    <small class="text-muted">Export ID: ${exportId}</small>
                                    <br><small class="text-muted">Records: ${exportInfo.recordCount}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-${statusClass}">${exportInfo.status}</span>
                                    ${exportInfo.progress ? `<br><small>${exportInfo.progress}%</small>` : ''}
                                    ${exportInfo.status === 'completed' ? `<br><button class="btn btn-sm btn-success mt-1" onclick="downloadExport('${exportId}')"><i class="fas fa-download"></i> Download</button>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        function downloadExport(exportId) {
            window.location.href = `/exports/download/${exportId}`;
        }
        
        // Load active exports on page load
        document.addEventListener('DOMContentLoaded', function() {
            // You could load existing active exports from server here
            updateActiveExportsDisplay();
        });
    </script>
</body>
</html>
