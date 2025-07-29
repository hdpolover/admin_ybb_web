/**
 * Enhanced Export Manager for YBB Export Service
 * 
 * Handles the enhanced export functionality including:
 * - Custom filename display
 * - Multi-file export handling
 * - Processing time tracking
 * - Download management
 */

// Global interval registry to track all active intervals
window.activeIntervals = window.activeIntervals || new Set();

// Override setInterval to track all intervals
const originalSetInterval = window.setInterval;
window.setInterval = function(callback, delay) {
    const intervalId = originalSetInterval(callback, delay);
    window.activeIntervals.add(intervalId);
    return intervalId;
};

// Override clearInterval to remove from tracking
const originalClearInterval = window.clearInterval;
window.clearInterval = function(intervalId) {
    originalClearInterval(intervalId);
    window.activeIntervals.delete(intervalId);
};

// Function to force clear all active intervals
window.forceStopAllIntervals = function() {
    console.log('Force stopping all active intervals:', Array.from(window.activeIntervals));
    window.activeIntervals.forEach(intervalId => {
        originalClearInterval(intervalId);
    });
    window.activeIntervals.clear();
};

class EnhancedExportManager {
    constructor() {
        this.currentExports = new Map();
        this.processingTimers = new Map();
        this.statusPollingInterval = null;
        this.currentProcessingTimer = null;
        this.initializeEventHandlers();
    }

    /**
     * Initialize event handlers
     */
    initializeEventHandlers() {
        // Export button handlers
        $(document).on('click', '.export-btn', (e) => {
            e.preventDefault();
            this.handleExportRequest(e.target);
        });

        // Download button handlers
        $(document).on('click', '.download-btn', (e) => {
            e.preventDefault();
            this.handleDownloadRequest(e.target);
        });

        // Status check handlers
        $(document).on('click', '.check-status-btn', (e) => {
            e.preventDefault();
            this.checkExportStatus($(e.target).data('export-id'));
        });
    }

    /**
     * Handle export request
     */
    handleExportRequest(button) {
        const $btn = $(button);
        const exportType = $btn.data('export-type');
        
        // Prevent double-clicking
        if ($btn.hasClass('processing') || $btn.prop('disabled')) {
            console.log('Export already in progress, ignoring duplicate request');
            return;
        }
        
        const formData = this.collectFormData($btn);

        // Start processing timer
        const processingTimer = new ProcessingTimer();
        processingTimer.start();
        this.processingTimers.set(exportType, processingTimer);

        // Close any open modals immediately
        $('.modal').modal('hide');

        // Show SweetAlert loading indicator immediately
        const loadingSwal = Swal.fire({
            title: 'Preparing Export',
            html: `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mb-2"><strong>Processing your export request...</strong></p>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 100%"></div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> Please wait while we prepare your data...
                        <br><span id="loading-time">0</span>s elapsed
                    </small>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                // Update elapsed time
                const updateTime = () => {
                    const elapsed = Math.floor((Date.now() - processingTimer.startTime) / 1000);
                    const timeElement = document.getElementById('loading-time');
                    if (timeElement) {
                        timeElement.textContent = elapsed;
                    }
                };
                
                // Update every second
                const timeInterval = setInterval(updateTime, 1000);
                
                // Store interval for cleanup
                loadingSwal.timeInterval = timeInterval;
            }
        });

        // Show button loading state as backup
        this.showExportLoading($btn);

        // Determine the correct API endpoint
        const apiEndpoint = $btn.data('url') || `/exports/${exportType}`;
        
        console.log('=== EXPORT REQUEST START ===');
        console.log('Export type:', exportType);
        console.log('Making AJAX request to:', apiEndpoint);
        console.log('Request data:', formData);
        console.log('Request method: POST');

        // Make AJAX request
        $.ajax({
            url: apiEndpoint,
            method: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 30000, // 30 second timeout
            success: (response) => {
                console.log('=== AJAX SUCCESS RESPONSE ===');
                console.log('Raw AJAX response:', response);
                console.log('JSON.stringify(response):', JSON.stringify(response, null, 2));
                console.log('typeof response:', typeof response);
                console.log('Object.keys(response):', Object.keys(response));
                console.log('response.success:', response.success);
                console.log('response.status:', response.status);
                console.log('response.data:', response.data);
                console.log('=== ENHANCED FIELDS CHECK ===');
                console.log('response.recordCount:', response.recordCount);
                console.log('response.fileName:', response.fileName);
                console.log('response.fileSize:', response.fileSize);
                console.log('response.fileSizeFormatted:', response.fileSizeFormatted);
                console.log('response.processingTime:', response.processingTime);
                console.log('response.processingTimeMs:', response.processingTimeMs);
                console.log('response.recordsPerSecond:', response.recordsPerSecond);
                console.log('response.memoryUsedMb:', response.memoryUsedMb);
                console.log('response.metadata:', response.metadata);
                if (response.data) {
                    console.log('JSON.stringify(response.data):', JSON.stringify(response.data, null, 2));
                    console.log('Object.keys(response.data):', Object.keys(response.data));
                }
                console.log('=== END RAW AJAX RESPONSE ===');
                
                processingTimer.stop();
                
                // Clear the time interval
                if (loadingSwal.timeInterval) {
                    clearInterval(loadingSwal.timeInterval);
                }
                
                // Close loading SweetAlert
                Swal.close();
                
                this.handleExportSuccess(response, $btn, processingTimer);
            },
            error: (xhr) => {
                console.error('AJAX Error Response:', xhr);
                processingTimer.stop();
                
                // Clear the time interval
                if (loadingSwal.timeInterval) {
                    clearInterval(loadingSwal.timeInterval);
                }
                
                // Close loading SweetAlert
                Swal.close();
                
                this.handleExportError(xhr, $btn);
            }
        });
    }

    /**
     * Handle successful export response with status polling
     */
    handleExportSuccess(response, $btn, processingTimer) {
        this.hideExportLoading($btn);

        console.log('=== EXPORT SUCCESS RESPONSE ===');
        console.log('Full response:', response);
        console.log('Response success:', response.success);
        console.log('Response status:', response.status);
        console.log('Response exportId:', response.exportId);
        console.log('Response data:', response.data);
        console.log('Download URL from response.downloadUrl:', response.downloadUrl);
        console.log('Download URL from response.data?.download_url:', response.data?.download_url);

        if (response.success) {
            // Store export information
            this.currentExports.set(response.exportId, response);

            // Check if we have an export ID for status polling
            const exportId = response.data?.export_id || response.exportId;
            const downloadUrl = response.downloadUrl || response.data?.download_url;
            
            console.log('Determined exportId:', exportId);
            console.log('Determined downloadUrl:', downloadUrl);
            console.log('Export strategy:', response.exportStrategy || response.data?.export_strategy);
            
            // Check if export is already completed
            const isCompleted = !!(downloadUrl) || response.status === 'completed' || response.status === 'ready';
            console.log('Is export completed?', isCompleted);
            
            if (exportId && !isCompleted) {
                // Export is still processing, start polling
                console.log('🔄 Export needs processing, starting status polling for:', exportId);
                
                // Show success notification that export has started
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Export Processing',
                        text: `Your export has been initiated and is being processed. Export ID: ${exportId}`,
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
                
                this.startStatusPolling(exportId, processingTimer);
            } else if (exportId && isCompleted) {
                // Export is completed immediately, show results
                console.log('✅ Export completed immediately, showing results');
                processingTimer.stop(response.processingTime || response.data?.processing_time);
                
                // Show immediate success notification with SweetAlert2
                console.log('Attempting to show SweetAlert2 notification...');
                console.log('typeof Swal:', typeof Swal);
                console.log('window.Swal:', window.Swal);
                
                // Try multiple ways to access SweetAlert2
                const SwalInstance = window.Swal || window.swal || Swal;
                
                if (SwalInstance && typeof SwalInstance.fire === 'function') {
                    console.log('✅ SweetAlert2 is available, showing notification');
                    
                    // Debug: Log the complete response structure
                    console.log('=== DEBUGGING SWEETALERT DATA ===');
                    console.log('Full response object:', response);
                    console.log('JSON.stringify(response):', JSON.stringify(response, null, 2));
                    console.log('response.data:', response.data);
                    console.log('JSON.stringify(response.data):', JSON.stringify(response.data, null, 2));
                    console.log('response.recordCount:', response.recordCount);
                    console.log('response.fileName:', response.fileName);
                    console.log('response.processingTime:', response.processingTime);
                    console.log('response.fileSize:', response.fileSize);
                    console.log('response.fileSizeFormatted:', response.fileSizeFormatted);
                    console.log('response.created_at:', response.created_at);
                    console.log('response.expires_at:', response.expires_at);
                    console.log('response.downloadUrl:', response.downloadUrl);
                    console.log('=== RAW API RESPONSE ANALYSIS ===');
                    console.log('typeof response:', typeof response);
                    console.log('Object.keys(response):', Object.keys(response));
                    if (response.data) {
                        console.log('typeof response.data:', typeof response.data);
                        console.log('Object.keys(response.data):', Object.keys(response.data));
                    }
                    
                    // Extract comprehensive data from API response with multiple fallbacks
                    // Controller returns data at root level: recordCount, fileSize, etc.
                    // API returns data nested under 'data': record_count, file_size, etc.
                    const responseData = response.data || {};
                    const metadata = response.metadata || {};
                    console.log('Extracted responseData object:', responseData);
                    console.log('Extracted metadata object:', metadata);
                    
                    // Priority: Controller format first (root level), then API format (nested)
                    const recordCount = response.recordCount || responseData.record_count || 
                                       response.record_count || responseData.recordCount || 
                                       response.totalRecords || responseData.totalRecords || 0;
                    
                    const fileSize = response.fileSize || responseData.file_size || 
                                    response.file_size || responseData.fileSize || 
                                    response.size || responseData.size;
                    
                    const fileName = response.fileName || responseData.file_name || 
                                    response.filename || response.file_name ||
                                    responseData.filename || responseData.fileName || 'Export File';
                    
                    const exportType = metadata.export_type || responseData.export_type || 
                                      response.exportType || response.export_type || 
                                      response.type || responseData.type || 
                                      this.guessExportTypeFromContext() || 'Data';
                    
                    const createdAt = response.createdAt || responseData.created_at || 
                                     response.created_at || responseData.createdAt || 
                                     response.timestamp || responseData.timestamp;
                    
                    const expiresAt = response.expiresAt || responseData.expires_at || 
                                     response.expires_at || responseData.expiresAt || 
                                     response.expiry || responseData.expiry;
                    
                    const processingTime = response.processingTime || response.processing_time || 
                                          responseData.processing_time || responseData.processingTime || 
                                          processingTimer?.getServerTime();
                    
                    // Extract enhanced metrics from the new API response
                    const processingTimeMs = metadata.processing_time_ms || responseData.processing_time_ms || 
                                           response.processing_time_ms || null;
                    
                    const recordsPerSecond = metadata.records_per_second || responseData.records_per_second || 
                                           response.records_per_second || null;
                    
                    const memoryUsedMb = metadata.memory_used_mb || responseData.memory_used_mb || 
                                       response.memory_used_mb || null;
                    
                    const peakMemoryMb = metadata.peak_memory_mb || responseData.peak_memory_mb || 
                                       response.peak_memory_mb || null;
                    
                    const memoryEfficiency = metadata.memory_efficiency_kb_per_record || 
                                           responseData.memory_efficiency_kb_per_record || 
                                           response.memory_efficiency_kb_per_record || null;
                    
                    const fileSizeMb = responseData.file_size_mb || response.file_size_mb || 
                                     metadata.file_size_mb || null;
                    
                    // For multi-file exports
                    const archive = responseData.archive || response.archive || {};
                    const compressionRatio = archive.compression_ratio_percent || 
                                           metadata.compression_ratio_percent || null;
                    
                    const spaceSavedMb = metadata.space_saved_mb || responseData.space_saved_mb || 
                                       response.space_saved_mb || null;
                    
                    // Debug: Log extracted values
                    console.log('=== EXTRACTED VALUES ===');
                    console.log('recordCount:', recordCount, '(from response.recordCount:', response.recordCount, ', responseData.record_count:', responseData.record_count, ')');
                    console.log('fileSize:', fileSize, '(from response.fileSize:', response.fileSize, ', responseData.file_size:', responseData.file_size, ')');
                    console.log('fileName:', fileName, '(from response.fileName:', response.fileName, ', responseData.file_name:', responseData.file_name, ')');
                    console.log('exportType:', exportType, '(from response.exportType:', response.exportType, ', metadata.export_type:', metadata.export_type, ')');
                    console.log('createdAt:', createdAt, '(from response.createdAt:', response.createdAt, ', responseData.created_at:', responseData.created_at, ')');
                    console.log('expiresAt:', expiresAt, '(from response.expiresAt:', response.expiresAt, ', responseData.expires_at:', responseData.expires_at, ')');
                    console.log('processingTime:', processingTime, '(from response.processingTime:', response.processingTime, ')');
                    console.log('=== ENHANCED METRICS ===');
                    console.log('processingTimeMs:', processingTimeMs, 'recordsPerSecond:', recordsPerSecond);
                    console.log('memoryUsedMb:', memoryUsedMb, 'peakMemoryMb:', peakMemoryMb);
                    console.log('memoryEfficiency:', memoryEfficiency, 'compressionRatio:', compressionRatio);
                    
                    // Format file size for display
                    const fileSizeFormatted = response.fileSizeFormatted || 
                                            (fileSize ? this.formatFileSize(fileSize) : 
                                             (fileSizeMb ? fileSizeMb.toFixed(2) + ' MB' : 'Unknown'));
                    
                    // Format timestamps for display  
                    // Note: API may not provide created_at, so we use current time as fallback
                    const createdTime = createdAt ? new Date(createdAt).toLocaleString() : 'Just now';
                    const expiryTime = expiresAt ? new Date(expiresAt).toLocaleString() : '24 hours from now';
                    
                    // Format processing time with enhanced precision
                    const processingTimeFormatted = processingTimeMs ? 
                        processingTimeMs.toFixed(1) + 'ms' : 
                        (processingTime ? 
                            (typeof processingTime === 'number' ? processingTime.toFixed(1) + 's' : processingTime) : 
                            (processingTimer?.getServerTime() ? processingTimer.getServerTime().toFixed(1) + 's' : 'N/A'));
                    
                    // Format performance metrics
                    const performanceText = recordsPerSecond ? 
                        `${recordsPerSecond.toFixed(0)} records/sec` : 'N/A';
                    
                    const memoryText = memoryUsedMb ? 
                        `${memoryUsedMb.toFixed(1)} MB` : 'N/A';
                    
                    const efficiencyText = memoryEfficiency ? 
                        `${memoryEfficiency.toFixed(1)} KB/record` : 'N/A';
                    
                    console.log('=== FORMATTED VALUES ===');
                    console.log('fileSizeFormatted:', fileSizeFormatted, '(from response.fileSizeFormatted:', response.fileSizeFormatted, ', calculated from fileSize:', fileSize, ')');
                    console.log('createdTime:', createdTime, '(from createdAt:', createdAt, ')');
                    console.log('expiryTime:', expiryTime, '(from expiresAt:', expiresAt, ')');
                    console.log('processingTimeFormatted:', processingTimeFormatted, '(from processingTime:', processingTime, ', timer:', processingTimer?.getServerTime(), ')');
                    console.log('performanceText:', performanceText, 'memoryText:', memoryText, 'efficiencyText:', efficiencyText);
                    
                    // Create rich HTML content for SweetAlert with enhanced metrics
                    const htmlContent = `
                        <div class="export-success-details">
                            <div class="export-icon mb-3">
                                <i class="fas fa-file-excel text-success" style="font-size: 48px;"></i>
                            </div>
                            
                            <div class="export-stats row text-center mb-4">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number text-success" style="font-size: 24px; font-weight: bold;">
                                            ${recordCount.toLocaleString()}
                                        </div>
                                        <div class="stat-label text-muted">Records</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number text-info" style="font-size: 24px; font-weight: bold;">
                                            ${fileSizeFormatted}
                                        </div>
                                        <div class="stat-label text-muted">File Size</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number text-warning" style="font-size: 24px; font-weight: bold;">
                                            ${processingTimeFormatted}
                                        </div>
                                        <div class="stat-label text-muted">Processing Time</div>
                                    </div>
                                </div>
                            </div>
                            
                            ${recordsPerSecond || memoryUsedMb || compressionRatio ? `
                            <div class="enhanced-metrics row text-center mb-4">
                                ${recordsPerSecond ? `
                                <div class="col-4">
                                    <div class="metric-card bg-light rounded p-2">
                                        <div class="metric-value text-primary" style="font-size: 18px; font-weight: bold;">
                                            <i class="fas fa-tachometer-alt"></i> ${performanceText}
                                        </div>
                                        <div class="metric-label text-secondary small">Performance Rate</div>
                                    </div>
                                </div>
                                ` : ''}
                                ${memoryUsedMb ? `
                                <div class="col-4">
                                    <div class="metric-card bg-light rounded p-2">
                                        <div class="metric-value text-info" style="font-size: 18px; font-weight: bold;">
                                            <i class="fas fa-memory"></i> ${memoryText}
                                        </div>
                                        <div class="metric-label text-secondary small">Memory Used</div>
                                    </div>
                                </div>
                                ` : ''}
                                ${memoryEfficiency ? `
                                <div class="col-4">
                                    <div class="metric-card bg-light rounded p-2">
                                        <div class="metric-value text-success" style="font-size: 18px; font-weight: bold;">
                                            <i class="fas fa-chart-line"></i> ${efficiencyText}
                                        </div>
                                        <div class="metric-label text-secondary small">Memory Efficiency</div>
                                    </div>
                                </div>
                                ` : ''}
                                ${compressionRatio ? `
                                <div class="col-6">
                                    <div class="metric-card bg-light rounded p-2">
                                        <div class="metric-value text-warning" style="font-size: 18px; font-weight: bold;">
                                            <i class="fas fa-compress"></i> ${compressionRatio}%
                                        </div>
                                        <div class="metric-label text-secondary small">Compression Ratio</div>
                                    </div>
                                </div>
                                ` : ''}
                                ${spaceSavedMb ? `
                                <div class="col-6">
                                    <div class="metric-card bg-light rounded p-2">
                                        <div class="metric-value text-success" style="font-size: 18px; font-weight: bold;">
                                            <i class="fas fa-save"></i> ${spaceSavedMb.toFixed(2)} MB
                                        </div>
                                        <div class="metric-label text-secondary small">Space Saved</div>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                            ` : ''}
                            
                            <div class="export-metadata">
                                <div class="metadata-item mb-2">
                                    <i class="fas fa-file-alt text-primary"></i>
                                    <strong>File:</strong> <span class="text-muted">${fileName}</span>
                                </div>
                                <div class="metadata-item mb-2">
                                    <i class="fas fa-tag text-primary"></i>
                                    <strong>Type:</strong> <span class="text-muted">${exportType.charAt(0).toUpperCase() + exportType.slice(1)} Export</span>
                                </div>
                                <div class="metadata-item mb-2">
                                    <i class="fas fa-clock text-primary"></i>
                                    <strong>Created:</strong> <span class="text-muted">${createdTime}</span>
                                </div>
                                ${peakMemoryMb ? `
                                <div class="metadata-item mb-2">
                                    <i class="fas fa-chart-area text-warning"></i>
                                    <strong>Peak Memory:</strong> <span class="text-muted">${peakMemoryMb.toFixed(1)} MB</span>
                                </div>
                                ` : ''}
                                <div class="metadata-item mb-3">
                                    <i class="fas fa-hourglass-end text-warning"></i>
                                    <strong>Expires:</strong> <span class="text-muted">${expiryTime}</span>
                                </div>
                            </div>
                            
                            <div class="export-actions">
                                <p class="text-center text-muted mb-0">
                                    <i class="fas fa-info-circle"></i>
                                    Your export is ready for download and will be available until expiry.
                                </p>
                            </div>
                        </div>
                    `;
                    
                    // Enhanced success notification with rich details
                    SwalInstance.fire({
                        html: htmlContent,
                        title: '🎉 Export Complete!',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-download"></i> Download Now',
                        cancelButtonText: '<i class="fas fa-eye"></i> View Details',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        width: '600px',
                        customClass: {
                            popup: 'export-success-popup',
                            title: 'export-success-title',
                            htmlContainer: 'export-success-content'
                        },
                        showClass: {
                            popup: 'animate__animated animate__fadeInUp animate__faster'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutDown animate__faster'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show downloading feedback
                            SwalInstance.fire({
                                title: 'Downloading...',
                                text: `Preparing ${fileName} for download`,
                                icon: 'info',
                                timer: 2000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                            
                            // Use helper function for clean download
                            const downloadUrl = this.getDownloadUrl(response, exportId);
                            console.log('SweetAlert download - Using URL:', downloadUrl);
                            this.downloadFile(downloadUrl, response.fileName);
                        } else if (result.dismiss === SwalInstance.DismissReason.cancel) {
                            // Show detailed results
                            if (response.exportStrategy === 'multi_file' || response.data?.export_strategy === 'multi_file') {
                                this.showMultiFileExportResult(response, processingTimer);
                            } else {
                                this.showSingleFileExportResult(response, processingTimer);
                            }
                        }
                    });
                } else {
                    console.log('❌ SweetAlert2 not available, trying simple alert');
                    
                    // Simple browser alert as fallback
                    if (confirm(`Export Complete!\n\nSuccessfully exported ${response.recordCount || 0} records.\n\nClick OK to download now, or Cancel to view details.`)) {
                        // Use helper function for clean download
                        const downloadUrl = this.getDownloadUrl(response, exportId);
                        console.log('Fallback download - Using URL:', downloadUrl);
                        this.downloadFile(downloadUrl, response.fileName);
                    } else {
                        // Show detailed results
                        if (response.exportStrategy === 'multi_file' || response.data?.export_strategy === 'multi_file') {
                            this.showMultiFileExportResult(response, processingTimer);
                        } else {
                            this.showSingleFileExportResult(response, processingTimer);
                        }
                    }
                }
            } else {
                console.error('❌ No valid export ID found in response');
                this.showErrorMessage('Invalid export response: No export ID found');
            }
        } else {
            console.error('❌ Export response indicated failure:', response.message);
            this.showErrorMessage(response.message || 'Export failed');
        }
    }

    /**
     * Start polling for export status updates
     */
    startStatusPolling(exportId, processingTimer, pollInterval = 3000) {
        console.log('=== STARTING STATUS POLLING ===');
        console.log('Export ID:', exportId);
        console.log('Previous interval ID:', this.statusPollingInterval);
        
        // Force stop any existing polling - be very aggressive about this
        this.stopStatusPolling();
        
        // Clear any potential zombie intervals by clearing a range around the previous ID
        if (this.statusPollingInterval) {
            const prevId = parseInt(this.statusPollingInterval) || 0;
            for (let i = Math.max(1, prevId - 5); i <= prevId + 5; i++) {
                clearInterval(i);
            }
        }
        
        // Store the processing timer for later use
        this.currentProcessingTimer = processingTimer;
        
        // Start polling every 3 seconds
        this.statusPollingInterval = setInterval(() => {
            console.log('Status polling tick for export:', exportId);
            this.checkExportStatus(exportId);
        }, pollInterval);
        
        console.log('New polling interval started with ID:', this.statusPollingInterval);
        
        // Also check immediately
        this.checkExportStatus(exportId);
        
        // Stop polling after 5 minutes to prevent infinite polling
        setTimeout(() => {
            if (this.statusPollingInterval) {
                clearInterval(this.statusPollingInterval);
                this.statusPollingInterval = null;
                console.log('Status polling stopped after timeout');
                
                // Show timeout message
                this.showWarningMessage('Export is taking longer than expected. Please check back later or contact support.');
            }
        }, 300000); // 5 minutes
    }

    /**
     * Stop status polling
     */
    stopStatusPolling() {
        console.log('=== STOPPING STATUS POLLING ===');
        console.log('Current intervalId:', this.statusPollingInterval);
        if (this.statusPollingInterval) {
            clearInterval(this.statusPollingInterval);
            this.statusPollingInterval = null;
            console.log('Status polling stopped and cleared');
        } else {
            console.log('No active status polling interval to stop');
        }
        
        // Also stop any ProcessingTimer intervals
        if (window.processingTimer) {
            console.log('Stopping ProcessingTimer as well');
            window.processingTimer.stop();
        }
        
        // Nuclear option: force stop ALL intervals as a failsafe
        console.log('Force stopping all active intervals as failsafe');
        if (window.forceStopAllIntervals) {
            window.forceStopAllIntervals();
        }
        
        // Extra safeguard: clear a range of interval IDs
        console.log('Clearing interval ID range as extra safeguard');
        for (let i = 1; i <= 1000; i++) {
            try {
                originalClearInterval(i);
            } catch (e) {
                // Ignore errors
            }
        }
    }

    /**
     * Show single file export result with enhanced metrics
     */
    showSingleFileExportResult(response, processingTimer) {
        const processingTime = response.processingTime || processingTimer?.getServerTime();
        const clientTime = processingTimer?.getClientTime();
        const exportId = response.exportId || response.data?.export_id;
        const fileName = response.fileName || response.data?.file_name || 'Export File';
        const recordCount = response.recordCount || response.data?.record_count || 0;
        const responseData = response.data || {};
        const metadata = response.metadata || {};
        
        // Enhanced metrics extraction
        const processingTimeMs = metadata.processing_time_ms || responseData.processing_time_ms || 
                               response.processing_time_ms || null;
        
        const recordsPerSecond = metadata.records_per_second || responseData.records_per_second || 
                               response.records_per_second || null;
        
        const memoryUsedMb = metadata.memory_used_mb || responseData.memory_used_mb || 
                           response.memory_used_mb || null;
        
        const memoryEfficiency = metadata.memory_efficiency_kb_per_record || 
                               responseData.memory_efficiency_kb_per_record || 
                               response.memory_efficiency_kb_per_record || null;
        
        const fileSize = response.fileSize || responseData.file_size || 
                        response.file_size || responseData.fileSize;
        
        const fileSizeMb = responseData.file_size_mb || response.file_size_mb || 
                         metadata.file_size_mb || null;
        
        // Get the correct download URL using helper function
        const downloadUrl = this.getDownloadUrl(response, exportId);

        console.log('Showing single file export result for export ID:', exportId);
        console.log('Download URL will be:', downloadUrl);

        const html = `
            <div class="alert alert-success export-result" id="export-result-${exportId}">
                <div class="export-header">
                    <h4><i class="fas fa-check-circle text-success"></i> Export Completed Successfully</h4>
                </div>
                
                <div class="export-details">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-value">${recordCount.toLocaleString()}</div>
                                <div class="stat-label">Records</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-value">${fileSizeMb ? fileSizeMb.toFixed(2) + ' MB' : (fileSize ? this.formatFileSize(fileSize) : 'N/A')}</div>
                                <div class="stat-label">File Size</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-value">${processingTimeMs ? processingTimeMs.toFixed(1) + 'ms' : (processingTime ? processingTime + 's' : 'N/A')}</div>
                                <div class="stat-label">Processing Time</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-value">${clientTime ? clientTime + 's' : 'N/A'}</div>
                                <div class="stat-label">Total Time</div>
                            </div>
                        </div>
                    </div>
                    
                    ${recordsPerSecond || memoryUsedMb || memoryEfficiency ? `
                    <div class="enhanced-metrics-row row mt-3">
                        ${recordsPerSecond ? `
                        <div class="col-md-4">
                            <div class="metric-card bg-light rounded p-2 text-center">
                                <div class="metric-value text-primary">
                                    <i class="fas fa-tachometer-alt"></i> ${recordsPerSecond.toFixed(0)} records/sec
                                </div>
                                <div class="metric-label text-secondary small">Performance Rate</div>
                            </div>
                        </div>
                        ` : ''}
                        ${memoryUsedMb ? `
                        <div class="col-md-4">
                            <div class="metric-card bg-light rounded p-2 text-center">
                                <div class="metric-value text-warning">
                                    <i class="fas fa-memory"></i> ${memoryUsedMb.toFixed(1)} MB
                                </div>
                                <div class="metric-label text-secondary small">Memory Used</div>
                            </div>
                        </div>
                        ` : ''}
                        ${memoryEfficiency ? `
                        <div class="col-md-4">
                            <div class="metric-card bg-light rounded p-2 text-center">
                                <div class="metric-value text-success">
                                    <i class="fas fa-chart-line"></i> ${memoryEfficiency.toFixed(1)} KB/record
                                </div>
                                <div class="metric-label text-secondary small">Memory Efficiency</div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                    ` : ''}
                </div>

                <div class="export-summary">
                    <h6><i class="fas fa-file-excel text-success"></i> ${fileName}</h6>
                    <p class="mb-2">Your export has been generated successfully and is ready for download.</p>
                    
                    <div class="download-actions mt-3">
                        <a href="${downloadUrl}" class="btn btn-success btn-lg" target="_blank" download>
                            <i class="fas fa-download"></i> Download Export
                        </a>
                        <button class="btn btn-outline-secondary btn-sm ml-2 check-status-btn" data-export-id="${exportId}">
                            <i class="fas fa-sync"></i> Check Status
                        </button>
                    </div>
                </div>
            </div>
        `;

        this.displayResult(html);
    }

    /**
     * Show multi-file export result with enhanced metrics
     */
    showMultiFileExportResult(response, processingTimer) {
        const processingTime = response.processingTime || processingTimer?.getServerTime();
        const clientTime = processingTimer?.getClientTime();
        const archive = response.archive || {};
        const individualFiles = response.individualFiles || [];
        const responseData = response.data || {};
        const metadata = response.metadata || {};

        // Enhanced metrics extraction
        const processingTimeMs = metadata.processing_time_ms || responseData.processing_time_ms || 
                               response.processing_time_ms || null;
        
        const recordsPerSecond = metadata.records_per_second || responseData.records_per_second || 
                               response.records_per_second || null;
        
        const filesPerSecond = metadata.files_per_second || responseData.files_per_second || 
                             response.files_per_second || null;
        
        const memoryUsedMb = metadata.memory_used_mb || responseData.memory_used_mb || 
                           response.memory_used_mb || null;
        
        const spaceSavedMb = metadata.space_saved_mb || responseData.space_saved_mb || 
                           response.space_saved_mb || null;
        
        const compressionRatio = archive.compression_ratio_percent || 
                               metadata.compression_ratio_percent || null;

        const html = `
            <div class="alert alert-success export-result" id="export-result-${response.exportId}">
                <div class="export-header">
                    <h4><i class="fas fa-check-circle text-success"></i> Large Export Completed - Multiple Files Generated</h4>
                </div>
                
                <div class="export-details">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="stat-card">
                                <div class="stat-value">${response.recordCount.toLocaleString()}</div>
                                <div class="stat-label">Total Records</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stat-card">
                                <div class="stat-value">${response.totalFiles}</div>
                                <div class="stat-label">Files</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stat-card">
                                <div class="stat-value">${archive.file_size ? this.formatFileSize(archive.file_size) : 'N/A'}</div>
                                <div class="stat-label">Archive Size</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stat-card">
                                <div class="stat-value">${compressionRatio ? compressionRatio + '%' : 'N/A'}</div>
                                <div class="stat-label">Compression</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stat-card">
                                <div class="stat-value">${processingTimeMs ? processingTimeMs.toFixed(1) + 'ms' : (processingTime ? processingTime + 's' : 'N/A')}</div>
                                <div class="stat-label">Processing Time</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stat-card">
                                <div class="stat-value">${clientTime ? clientTime + 's' : 'N/A'}</div>
                                <div class="stat-label">Total Time</div>
                            </div>
                        </div>
                    </div>
                    
                    ${recordsPerSecond || memoryUsedMb || filesPerSecond || spaceSavedMb ? `
                    <div class="enhanced-metrics-row row mt-3">
                        ${recordsPerSecond ? `
                        <div class="col-md-3">
                            <div class="metric-card bg-light rounded p-2 text-center">
                                <div class="metric-value text-primary">
                                    <i class="fas fa-tachometer-alt"></i> ${recordsPerSecond.toFixed(0)}/sec
                                </div>
                                <div class="metric-label text-secondary small">Records Rate</div>
                            </div>
                        </div>
                        ` : ''}
                        ${filesPerSecond ? `
                        <div class="col-md-3">
                            <div class="metric-card bg-light rounded p-2 text-center">
                                <div class="metric-value text-info">
                                    <i class="fas fa-file"></i> ${filesPerSecond.toFixed(1)}/sec
                                </div>
                                <div class="metric-label text-secondary small">Files Rate</div>
                            </div>
                        </div>
                        ` : ''}
                        ${memoryUsedMb ? `
                        <div class="col-md-3">
                            <div class="metric-card bg-light rounded p-2 text-center">
                                <div class="metric-value text-warning">
                                    <i class="fas fa-memory"></i> ${memoryUsedMb.toFixed(1)} MB
                                </div>
                                <div class="metric-label text-secondary small">Memory Used</div>
                            </div>
                        </div>
                        ` : ''}
                        ${spaceSavedMb ? `
                        <div class="col-md-3">
                            <div class="metric-card bg-light rounded p-2 text-center">
                                <div class="metric-value text-success">
                                    <i class="fas fa-save"></i> ${spaceSavedMb.toFixed(2)} MB
                                </div>
                                <div class="metric-label text-secondary small">Space Saved</div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                    ` : ''}
                </div>

                <div class="export-summary">
                    <h5>Download Options</h5>
                    
                    <!-- Recommended ZIP Download -->
                    <div class="download-option recommended mb-3">
                        <h6><i class="fas fa-file-archive text-success"></i> Complete Archive (Recommended)</h6>
                        <p>Download all files in a single ZIP archive</p>
                        <div class="download-actions">
                            <a href="${this.getDownloadUrl(response, response.exportId, '/zip')}" class="btn btn-success btn-lg">
                                <i class="fas fa-download"></i> Download Complete ZIP Archive
                            </a>
                        </div>
                    </div>

                    <!-- Individual Files -->
                    <div class="download-option">
                        <h6><i class="fas fa-files text-info"></i> Individual Files</h6>
                        <p>Download specific batch files individually</p>
                        <div class="individual-files">
                            ${individualFiles.map((file, index) => `
                                <div class="file-item d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                    <div>
                                        <strong>Batch ${file.batch_number}</strong>: ${file.file_name}
                                        <br><small class="text-muted">${file.record_count} records • ${file.record_range}</small>
                                    </div>
                                    <a href="${this.getDownloadUrl(response, response.exportId, `/batch/${file.batch_number}`)}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-outline-secondary btn-sm check-status-btn" data-export-id="${response.exportId}">
                            <i class="fas fa-sync"></i> Check Status
                        </button>
                    </div>
                </div>
            </div>
        `;

        this.displayResult(html);
    }

    /**
     * Handle export error
     */
    handleExportError(xhr, $btn) {
        this.hideExportLoading($btn);

        console.error('Export request failed:', {
            status: xhr.status,
            statusText: xhr.statusText,
            responseText: xhr.responseText,
            responseJSON: xhr.responseJSON
        });

        let errorMessage = 'An error occurred during export';
        
        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        } else if (xhr.status === 500) {
            errorMessage = 'Server error occurred. Please try again.';
            // Try to extract more info from response
            if (xhr.responseText) {
                console.log('Full server response:', xhr.responseText);
                // Check if it's a login redirect
                if (xhr.responseText.includes('sign-in') || xhr.responseText.includes('login')) {
                    errorMessage = 'Session expired. Please refresh the page and login again.';
                }
            }
        } else if (xhr.status === 404) {
            errorMessage = 'Export service not found. Please contact support.';
        } else if (xhr.status === 0) {
            errorMessage = 'Network error. Please check your connection.';
        } else if (xhr.status === 403) {
            errorMessage = 'Access denied. Please check your permissions.';
        }

        // Show error with SweetAlert2 if available, otherwise use regular alert
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Export Failed',
                text: errorMessage,
                confirmButtonText: 'OK'
            });
        } else {
            // Fallback to Bootstrap alert
            const html = `
                <div class="alert alert-danger export-result">
                    <div class="export-header">
                        <h4><i class="fas fa-exclamation-triangle text-danger"></i> Export Failed</h4>
                    </div>
                    <div class="export-details">
                        <p>${errorMessage}</p>
                        <div class="mt-3">
                            <button class="btn btn-outline-secondary" onclick="location.reload()">
                                <i class="fas fa-redo"></i> Try Again
                            </button>
                        </div>
                    </div>
                </div>
            `;

            this.displayResult(html);
        }
    }

    /**
     * Check export status with enhanced error handling and exponential backoff
     */
    async checkExportStatus(exportId, retryCount = 0, maxRetries = 3) {
        console.log(`=== CHECKING EXPORT STATUS FOR: ${exportId} ===`);
        console.log('Current polling interval ID:', this.statusPollingInterval);
        console.log('Retry count:', retryCount, 'Max retries:', maxRetries);
        
        if (!exportId) {
            console.log('No exportId provided, returning');
            return;
        }
        
        // Check if this export is already completed
        if (window.completedExports && window.completedExports.has(exportId)) {
            console.log('🛑 Export already marked as completed, skipping status check');
            this.stopStatusPolling();
            return;
        }
        
        // Check if polling should stop
        if (!this.statusPollingInterval) {
            console.log('No active polling interval found, not checking status');
            return;
        }

        try {
            // Use the correct API endpoint from the documentation
            const response = await $.ajax({
                url: `/exports/status/${exportId}`,
                method: 'GET',
                dataType: 'json',
                timeout: 10000 // 10 second timeout
            });

            console.log('Export status response:', response);

            if (response.success) {
                console.log('Checking completion conditions:');
                console.log('- response.status:', response.status);
                console.log('- response.downloadUrl:', response.downloadUrl);
                console.log('- Is completed?', (response.status === 'completed' || response.status === 'ready' || response.downloadUrl));
                
                this.updateExportStatus(exportId, response);
                
                // If export is completed, stop polling - be VERY aggressive about this
                if (response.status === 'completed' || response.status === 'ready' || response.downloadUrl) {
                    console.log('🚨 EXPORT COMPLETED - STOPPING ALL POLLING NOW! 🚨');
                    console.log('Export ID:', exportId);
                    console.log('Status:', response.status);
                    console.log('Download URL:', response.downloadUrl);
                    
                    // Stop polling multiple times to be absolutely sure
                    for (let i = 0; i < 5; i++) {
                        setTimeout(() => {
                            console.log(`Stop attempt ${i + 1}/5`);
                            this.stopStatusPolling();
                        }, i * 100);
                    }
                    
                    // Also mark this export as completed to prevent future polling
                    window.completedExports = window.completedExports || new Set();
                    window.completedExports.add(exportId);
                    
                    // Show completion notification with enhanced details
                    if (typeof Swal !== 'undefined') {
                        // Debug: Log the complete response structure for polling completion
                        console.log('=== DEBUGGING POLLING COMPLETION DATA ===');
                        console.log('Full response object:', response);
                        console.log('response.data:', response.data);
                        
                        // Extract comprehensive data from API response with multiple fallbacks
                        // Priority: Controller format first (root level), then API format (nested)
                        const responseData = response.data || {};
                        const metadata = response.metadata || {};
                        
                        const recordCount = response.recordCount || responseData.record_count || 
                                           response.record_count || responseData.recordCount || 
                                           response.totalRecords || responseData.totalRecords || 0;
                        
                        const fileSize = response.fileSize || responseData.file_size || 
                                        response.file_size || responseData.fileSize || 
                                        response.size || responseData.size;
                        
                        const fileName = response.fileName || responseData.file_name || 
                                        response.filename || response.file_name ||
                                        responseData.filename || responseData.fileName || 'Export File';
                        
                        const exportType = metadata.export_type || responseData.export_type || 
                                          response.exportType || response.export_type || 
                                          response.type || responseData.type || 
                                          this.guessExportTypeFromContext() || 'Data';
                        
                        const createdAt = response.createdAt || responseData.created_at || 
                                         response.created_at || responseData.createdAt || 
                                         response.timestamp || responseData.timestamp;
                        
                        const expiresAt = response.expiresAt || responseData.expires_at || 
                                         response.expires_at || responseData.expiresAt || 
                                         response.expiry || responseData.expiry;
                        
                        const processingTime = response.processingTime || response.processing_time || 
                                              responseData.processing_time || responseData.processingTime;
                        
                        // Extract enhanced metrics from the new API response
                        const processingTimeMs = metadata.processing_time_ms || responseData.processing_time_ms || 
                                               response.processing_time_ms || null;
                        
                        const recordsPerSecond = metadata.records_per_second || responseData.records_per_second || 
                                               response.records_per_second || null;
                        
                        const memoryUsedMb = metadata.memory_used_mb || responseData.memory_used_mb || 
                                           response.memory_used_mb || null;
                        
                        const peakMemoryMb = metadata.peak_memory_mb || responseData.peak_memory_mb || 
                                           response.peak_memory_mb || null;
                        
                        const memoryEfficiency = metadata.memory_efficiency_kb_per_record || 
                                               responseData.memory_efficiency_kb_per_record || 
                                               response.memory_efficiency_kb_per_record || null;
                        
                        const fileSizeMb = responseData.file_size_mb || response.file_size_mb || 
                                         metadata.file_size_mb || null;
                        
                        // Debug: Log extracted values for polling
                        console.log('=== POLLING EXTRACTED VALUES ===');
                        console.log('recordCount:', recordCount, '(from response.recordCount:', response.recordCount, ', responseData.record_count:', responseData.record_count, ')');
                        console.log('fileSize:', fileSize, '(from response.fileSize:', response.fileSize, ', responseData.file_size:', responseData.file_size, ')');
                        console.log('fileName:', fileName, '(from response.fileName:', response.fileName, ', responseData.file_name:', responseData.file_name, ')');
                        console.log('exportType:', exportType, '(from response.exportType:', response.exportType, ', metadata.export_type:', metadata.export_type, ')');
                        console.log('createdAt:', createdAt, '(from response.createdAt:', response.createdAt, ', responseData.created_at:', responseData.created_at, ')');
                        console.log('expiresAt:', expiresAt, '(from response.expiresAt:', response.expiresAt, ', responseData.expires_at:', responseData.expires_at, ')');
                        console.log('processingTime:', processingTime, '(from response.processingTime:', response.processingTime, ')');
                        console.log('=== POLLING ENHANCED METRICS ===');
                        console.log('processingTimeMs:', processingTimeMs, 'recordsPerSecond:', recordsPerSecond);
                        console.log('memoryUsedMb:', memoryUsedMb, 'peakMemoryMb:', peakMemoryMb);
                        console.log('memoryEfficiency:', memoryEfficiency);
                        
                        // Format values for display
                        const fileSizeFormatted = response.fileSizeFormatted || 
                                                (fileSize ? this.formatFileSize(fileSize) : 
                                                 (fileSizeMb ? fileSizeMb.toFixed(2) + ' MB' : 'Unknown'));
                        const createdTime = createdAt ? new Date(createdAt).toLocaleTimeString() : 'Just now';
                        const expiryTime = expiresAt ? new Date(expiresAt).toLocaleString() : '24 hours from now';
                        const processingTimeFormatted = processingTimeMs ? 
                            processingTimeMs.toFixed(1) + 'ms' : 
                            (processingTime ? 
                                (typeof processingTime === 'number' ? processingTime.toFixed(1) + 's' : processingTime) : 'N/A');
                        
                        // Format performance metrics for polling
                        const performanceText = recordsPerSecond ? 
                            `${recordsPerSecond.toFixed(0)} records/sec` : 'N/A';
                        
                        const memoryText = memoryUsedMb ? 
                            `${memoryUsedMb.toFixed(1)} MB` : 'N/A';
                        
                        console.log('=== POLLING FORMATTED VALUES ===');
                        console.log('fileSizeFormatted:', fileSizeFormatted, '(from response.fileSizeFormatted:', response.fileSizeFormatted, ', calculated from fileSize:', fileSize, ')');
                        console.log('createdTime:', createdTime, '(from createdAt:', createdAt, ')');
                        console.log('expiryTime:', expiryTime, '(from expiresAt:', expiresAt, ')');
                        console.log('processingTimeFormatted:', processingTimeFormatted, '(from processingTime:', processingTime, ')');
                        console.log('performanceText:', performanceText, 'memoryText:', memoryText);
                        
                        // Create rich notification content with enhanced metrics
                        const notificationHtml = `
                            <div class="export-completion-details text-center">
                                <div class="completion-icon mb-3">
                                    <i class="fas fa-check-circle text-success" style="font-size: 64px; opacity: 0.9;"></i>
                                </div>
                                
                                <div class="completion-stats mb-4">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="stat-badge bg-success text-white rounded p-2 mb-2">
                                                <div class="stat-number h4 mb-0">${recordCount.toLocaleString()}</div>
                                                <small>Records Exported</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="stat-badge bg-info text-white rounded p-2 mb-2">
                                                <div class="stat-number h4 mb-0">${fileSizeFormatted}</div>
                                                <small>File Size</small>
                                            </div>
                                        </div>
                                    </div>
                                    ${recordsPerSecond || memoryUsedMb ? `
                                    <div class="row mt-2">
                                        ${recordsPerSecond ? `
                                        <div class="col-6">
                                            <div class="metric-badge bg-primary text-white rounded p-2 mb-2">
                                                <div class="metric-number h5 mb-0">
                                                    <i class="fas fa-tachometer-alt"></i> ${performanceText}
                                                </div>
                                                <small>Performance Rate</small>
                                            </div>
                                        </div>
                                        ` : ''}
                                        ${memoryUsedMb ? `
                                        <div class="col-6">
                                            <div class="metric-badge bg-warning text-white rounded p-2 mb-2">
                                                <div class="metric-number h5 mb-0">
                                                    <i class="fas fa-memory"></i> ${memoryText}
                                                </div>
                                                <small>Memory Used</small>
                                            </div>
                                        </div>
                                        ` : ''}
                                    </div>
                                    ` : ''}
                                </div>
                                
                                <div class="completion-metadata text-left">
                                    <div class="metadata-row d-flex align-items-center mb-2">
                                        <i class="fas fa-file-excel text-success me-2"></i>
                                        <span><strong>File:</strong> ${fileName}</span>
                                    </div>
                                    <div class="metadata-row d-flex align-items-center mb-2">
                                        <i class="fas fa-tag text-primary me-2"></i>
                                        <span><strong>Type:</strong> ${exportType.charAt(0).toUpperCase() + exportType.slice(1)} Export</span>
                                    </div>
                                    <div class="metadata-row d-flex align-items-center mb-2">
                                        <i class="fas fa-clock text-secondary me-2"></i>
                                        <span><strong>Completed:</strong> ${createdTime}</span>
                                    </div>
                                    ${processingTimeFormatted !== 'N/A' ? `
                                    <div class="metadata-row d-flex align-items-center mb-2">
                                        <i class="fas fa-stopwatch text-warning me-2"></i>
                                        <span><strong>Processing Time:</strong> ${processingTimeFormatted}</span>
                                    </div>
                                    ` : ''}
                                    ${peakMemoryMb ? `
                                    <div class="metadata-row d-flex align-items-center mb-2">
                                        <i class="fas fa-chart-area text-info me-2"></i>
                                        <span><strong>Peak Memory:</strong> ${peakMemoryMb.toFixed(1)} MB</span>
                                    </div>
                                    ` : ''}
                                    ${memoryEfficiency ? `
                                    <div class="metadata-row d-flex align-items-center mb-2">
                                        <i class="fas fa-chart-line text-success me-2"></i>
                                        <span><strong>Efficiency:</strong> ${memoryEfficiency.toFixed(1)} KB/record</span>
                                    </div>
                                    ` : ''}
                                    <div class="metadata-row d-flex align-items-center">
                                        <i class="fas fa-hourglass-end text-muted me-2"></i>
                                        <span><strong>Available Until:</strong> ${expiryTime}</span>
                                    </div>
                                </div>
                                
                                <div class="completion-message mt-3">
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Your export is ready and waiting for download!
                                    </p>
                                </div>
                            </div>
                        `;
                        
                        Swal.fire({
                            html: notificationHtml,
                            title: '🎉 Export Processing Complete!',
                            showConfirmButton: true,
                            confirmButtonText: '<i class="fas fa-eye"></i> View Results',
                            confirmButtonColor: '#28a745',
                            width: '500px',
                            customClass: {
                                popup: 'export-completion-popup',
                                title: 'export-completion-title',
                                htmlContainer: 'export-completion-content'
                            },
                            showClass: {
                                popup: 'animate__animated animate__bounceIn animate__faster'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOut animate__faster'
                            }
                        });
                    }
                    
                    return; // Stop polling
                }
                
            } else if (response.status === 'not_found' && response.isTemporary && retryCount < maxRetries) {
                // Handle temporary 404 errors with exponential backoff
                const delay = Math.min(1000 * Math.pow(2, retryCount), 10000); // Max 10 second delay
                console.log(`Export ${exportId} temporarily not found, retrying in ${delay}ms (attempt ${retryCount + 1}/${maxRetries})`);
                
                setTimeout(() => {
                    this.checkExportStatus(exportId, retryCount + 1, maxRetries);
                }, delay);
            } else if (response.status === 'not_found') {
                // Permanent not found - show user-friendly message
                console.log('Export not found, stopping polling');
                this.stopStatusPolling();
                this.showWarningMessage(`Export ${exportId} may have completed or expired. Please check your downloads or try starting a new export.`);
            } else {
                console.log('Export error response, stopping polling');
                this.stopStatusPolling();
                this.showErrorMessage(response.message || 'Failed to check export status');
            }
        } catch (error) {
            console.error('Error checking export status:', error);
            
            if (retryCount < maxRetries && (error.status === 404 || error.status === 500 || error.status === 0)) {
                // Network error or server error - retry with exponential backoff
                const delay = Math.min(2000 * Math.pow(2, retryCount), 15000); // Max 15 second delay
                console.log(`Network error checking export ${exportId}, retrying in ${delay}ms (attempt ${retryCount + 1}/${maxRetries})`);
                
                setTimeout(() => {
                    this.checkExportStatus(exportId, retryCount + 1, maxRetries);
                }, delay);
            } else {
                console.error('Max retries exceeded or permanent error, stopping polling');
                this.stopStatusPolling(); // Stop polling on permanent errors
                this.showErrorMessage('Unable to check export status. Please refresh the page or try again.');
            }
        }
    }

    /**
     * Update export status display - enhanced to handle completion
     */
    updateExportStatus(exportId, statusData) {
        console.log('Updating export status:', exportId, statusData);
        
        // Remove any existing processing indicators
        $('.processing-info').remove();
        
        // Check if export is completed
        if (statusData.status === 'completed' || statusData.status === 'ready' || statusData.downloadUrl) {
            // Stop polling since export is completed
            this.stopStatusPolling();
            
            // Stop the processing timer if it exists
            if (this.currentProcessingTimer) {
                this.currentProcessingTimer.stop(statusData.processingTime);
            }
            
            // Show completion result based on export type
            if (statusData.totalFiles > 1 || statusData.exportStrategy === 'multi_file') {
                this.showMultiFileExportResult(statusData, this.currentProcessingTimer);
            } else {
                this.showSingleFileExportResult(statusData, this.currentProcessingTimer);
            }
            
            // Re-enable export buttons
            $('.export-btn').prop('disabled', false).html('<i class="fas fa-download"></i> Export');
            
            return; // Exit early since we've shown the completion result
        }
        
        // For ongoing processing, just update the status
        const $result = $(`#export-result-${exportId}`);
        if ($result.length) {
            // Update existing status information
            let statusMessage = 'Processing...';
            
            switch(statusData.status) {
                case 'processing':
                    statusMessage = 'Processing export...';
                    break;
                case 'generating':
                    statusMessage = 'Generating files...';
                    break;
                case 'packaging':
                    statusMessage = 'Packaging export...';
                    break;
                case 'uploading':
                    statusMessage = 'Uploading files...';
                    break;
                default:
                    statusMessage = `Status: ${statusData.status}`;
            }
            
            const statusInfo = `
                <div class="status-update alert alert-info mt-2">
                    <small><i class="fas fa-info-circle"></i> ${statusMessage}</small>
                    ${statusData.recordCount ? `<br><small>Records: ${statusData.recordCount.toLocaleString()}</small>` : ''}
                </div>
            `;
            
            // Replace existing status updates or append if none exist
            const $existingUpdate = $result.find('.status-update').last();
            if ($existingUpdate.length) {
                $existingUpdate.replaceWith(statusInfo);
            } else {
                $result.find('.export-summary').append(statusInfo);
            }
        } else {
            // No existing result container, create a processing indicator
            const processingHtml = `
                <div class="processing-info alert alert-info mt-3" id="processing-info">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                        <span>Processing export request...</span>
                    </div>
                    <div class="progress mt-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 60%"></div>
                    </div>
                </div>
            `;
            this.displayResult(processingHtml);
        }
    }

    /**
     * Show loading state
     */
    showExportLoading($btn) {
        $btn.addClass('processing')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            
        // Show progress indicator
        const progressHtml = `
            <div class="processing-info alert alert-info mt-3" id="processing-info">
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                    <span>Processing export request...</span>
                </div>
                <div class="progress mt-2">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 100%"></div>
                </div>
                <div class="processing-details mt-2">
                    <small class="text-muted">
                        <span id="processing-time">0</span>s elapsed
                    </small>
                </div>
            </div>
        `;
        
        $('#export-results').html(progressHtml);
    }

    /**
     * Hide loading state
     */
    hideExportLoading($btn) {
        $btn.removeClass('processing')
            .prop('disabled', false)
            .html($btn.data('original-text') || 'Export');
            
        $('.processing-info').remove();
    }

    /**
     * Collect form data for export
     */
    collectFormData($btn) {
        let $form;
        
        // Check if button has a specific form selector
        const formSelector = $btn.data('form-selector');
        if (formSelector) {
            $form = $(formSelector);
            console.log('Using specific form selector:', formSelector, 'Found form:', $form.length > 0);
        } else {
            $form = $btn.closest('form');
            console.log('Using closest form, found:', $form.length > 0);
        }
        
        const formData = {};
        
        if ($form.length) {
            const serializedData = $form.serializeArray();
            serializedData.forEach(item => {
                formData[item.name] = item.value;
                console.log('Form data collected:', item.name, '=', item.value);
            });
        } else {
            console.warn('No form found for collecting CSRF token and form data!');
        }
        
        // Add any additional filters from data attributes
        $.each($btn.data(), (key, value) => {
            if (key.startsWith('filter-')) {
                formData[key.replace('filter-', '')] = value;
            }
        });
        
        console.log('Final form data:', formData);
        return formData;
    }

    /**
     * Display result HTML in a modal instead of inline
     */
    displayResult(html) {
        // Create or update a modal for export results
        let modalHtml = `
            <div class="modal fade" id="exportResultModal" tabindex="-1" role="dialog" aria-labelledby="exportResultModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exportResultModalLabel">
                                <i class="fas fa-file-export"></i> Export Results
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            ${html}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if it exists
        $('#exportResultModal').remove();
        
        // Add new modal to body
        $('body').append(modalHtml);
        
        // Show the modal
        $('#exportResultModal').modal('show');
        
        // Also update the inline results div for fallback
        $('#export-results').html(`
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Export results are displayed in the modal above.
                <button type="button" class="btn btn-sm btn-outline-primary ml-2" onclick="$('#exportResultModal').modal('show')">
                    <i class="fas fa-eye"></i> View Results
                </button>
            </div>
        `);
    }

    /**
     * Show error message
     */
    showErrorMessage(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonText: 'OK'
            });
        } else {
            const html = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> ${message}
                </div>
            `;
            this.displayResult(html);
        }
    }

    /**
     * Show warning message
     */
    showWarningMessage(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: message,
                confirmButtonText: 'OK'
            });
        } else {
            const html = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle"></i> ${message}
                </div>
            `;
            this.displayResult(html);
        }
    }

    /**
     * Get the correct download URL from the API response
     */
    getDownloadUrl(response, exportId, endpoint = '') {
        const apiDownloadUrl = response.downloadUrl || response.data?.download_url;
        const API_BASE = 'https://ybb-data-management-service-production.up.railway.app';
        
        if (apiDownloadUrl && apiDownloadUrl.startsWith('/api/ybb/export/')) {
            // This is a relative API URL, make it absolute
            return API_BASE + apiDownloadUrl + endpoint;
        } else if (apiDownloadUrl && apiDownloadUrl.startsWith('http')) {
            // Already a full URL
            return apiDownloadUrl + endpoint;
        } else {
            // Fallback to local route (shouldn't happen with new system)
            return `/exports/download/${exportId}${endpoint}`;
        }
    }

    /**
     * Download file using direct API URL with invisible link method
     */
    downloadFile(downloadUrl, filename = '') {
        console.log('Starting download from URL:', downloadUrl);
        
        const link = document.createElement('a');
        link.href = downloadUrl;
        if (filename) {
            link.download = filename;
        }
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        console.log('Download initiated for:', downloadUrl);
    }

    /**
     * Guess export type from current page context
     */
    guessExportTypeFromContext() {
        // Check URL path
        const path = window.location.pathname.toLowerCase();
        
        if (path.includes('participant')) {
            return 'participants';
        } else if (path.includes('payment')) {
            return 'payments';
        } else if (path.includes('ambassador')) {
            return 'ambassadors';
        }
        
        // Check page title
        const title = document.title.toLowerCase();
        if (title.includes('participant')) {
            return 'participants';
        } else if (title.includes('payment')) {
            return 'payments';
        } else if (title.includes('ambassador')) {
            return 'ambassadors';
        }
        
        // Check for any export buttons or forms that might indicate type
        const exportButtons = document.querySelectorAll('[data-export-type]');
        if (exportButtons.length > 0) {
            return exportButtons[0].getAttribute('data-export-type');
        }
        
        // Default fallback
        return 'data';
    }

    formatFileSize(bytes) {
        if (bytes >= 1073741824) {
            return (bytes / 1073741824).toFixed(2) + ' GB';
        } else if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        } else {
            return bytes + ' bytes';
        }
    }
}

/**
 * Processing Timer Class
 */
class ProcessingTimer {
    constructor() {
        this.startTime = null;
        this.serverProcessingTime = null;
        this.intervalId = null;
    }

    start() {
        this.startTime = Date.now();
        this.showProgress();
    }

    stop(serverTime = null) {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        this.serverProcessingTime = serverTime;
    }

    showProgress() {
        this.intervalId = setInterval(() => {
            const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
            $('#processing-time').text(elapsed);
        }, 1000);
    }

    getClientTime() {
        if (!this.startTime) return null;
        return ((Date.now() - this.startTime) / 1000).toFixed(1);
    }

    getServerTime() {
        return this.serverProcessingTime;
    }
}

// Initialize when document is ready
$(document).ready(() => {
    // Wait a bit for all scripts to load
    setTimeout(() => {
        window.exportManager = new EnhancedExportManager();
        
        // Store original button text for loading states
        $('.export-btn').each(function() {
            $(this).data('original-text', $(this).html());
        });
        
        // Test SweetAlert2 availability
        console.log('=== SWEETALERT2 TEST ===');
        console.log('typeof Swal:', typeof Swal);
        console.log('window.Swal:', window.Swal);
        console.log('typeof window.Swal:', typeof window.Swal);
        
        const SwalTest = window.Swal || window.swal || (typeof Swal !== 'undefined' ? Swal : null);
        console.log('SwalTest:', SwalTest);
        
        if (SwalTest && typeof SwalTest.fire === 'function') {
            console.log('✅ SweetAlert2 is available and ready');
            console.log('Swal.fire available:', typeof SwalTest.fire === 'function');
            
            // Add a test function
            window.testSweetAlert = function() {
                SwalTest.fire({
                    icon: 'success',
                    title: 'SweetAlert2 Test',
                    text: 'This confirms SweetAlert2 is working properly!',
                    timer: 3000
                });
            };
            console.log('🧪 Test function available: window.testSweetAlert()');
        } else {
            console.log('❌ SweetAlert2 is not available');
            console.log('Available window properties with "swal":', Object.keys(window).filter(key => key.toLowerCase().includes('swal')));
        }
    }, 500); // Wait 500ms for scripts to load
});
