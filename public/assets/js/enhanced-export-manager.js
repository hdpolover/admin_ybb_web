/**
 * Enhanced Export Manager - Fixed Version
 * Handles all export operations with Sweet Alert notifications
 */

class EnhancedExportManager {
    constructor() {
        this.isProcessing = false;
        this.currentExports = new Map();
        this.statusPollingInterval = null;
        this.currentProcessingTimer = null;
        this.exportHistory = this.loadExportHistory();
        console.log('🚀 Enhanced Export Manager initialized with comprehensive metrics support and history tracking');
    }

    // Export History Management
    loadExportHistory() {
        try {
            const history = localStorage.getItem('ybb_export_history');
            return history ? JSON.parse(history) : [];
        } catch (e) {
            console.warn('Failed to load export history:', e);
            return [];
        }
    }

    saveExportHistory() {
        try {
            // Keep only last 20 exports
            const recentHistory = this.exportHistory.slice(-20);
            localStorage.setItem('ybb_export_history', JSON.stringify(recentHistory));
        } catch (e) {
            console.warn('Failed to save export history:', e);
        }
    }

    addToExportHistory(exportData) {
        const historyEntry = {
            id: exportData.export_id,
            timestamp: new Date().toISOString(),
            status: 'initiated',
            type: exportData.export_type || 'participants',
            template: exportData.template || 'standard',
            filters: exportData.filters || {},
            records_count: exportData.total_records || 0
        };
        
        this.exportHistory.push(historyEntry);
        this.saveExportHistory();
        console.log('📝 Added export to history:', historyEntry);
    }

    updateExportHistoryStatus(exportId, status, additionalData = {}) {
        const entry = this.exportHistory.find(e => e.id === exportId);
        if (entry) {
            entry.status = status;
            entry.updated_at = new Date().toISOString();
            
            // Add additional data like completion time, file size, etc.
            Object.assign(entry, additionalData);
            
            this.saveExportHistory();
            console.log(`📊 Updated export ${exportId} status to ${status}`);
        }
    }

    init() {
        this.intervalId = null;
        this.activeIntervals = [];
        // YBB Export Service base URL
        this.ybbApiBaseUrl = 'https://ybb-data-management-service-production.up.railway.app';
        console.log('🚀 Initializing Enhanced Export Manager...');
        this.clearAnyExistingIntervals();
        this.attachExportHandlers();
        console.log('✅ Enhanced Export Manager initialization complete');
    }

    clearAnyExistingIntervals() {
        console.log('🧹 Force stopping all active intervals:', this.activeIntervals);
        this.activeIntervals.forEach(id => {
            if (id) {
                clearInterval(id);
                clearTimeout(id);
            }
        });
        this.activeIntervals = [];

        // Nuclear option: clear a range of potential interval IDs
        for (let i = 1; i <= 1000; i++) {
            clearInterval(i);
            clearTimeout(i);
        }
    }

    attachExportHandlers() {
        console.log('🔗 Attaching export handlers...');
        
        // Remove any existing handlers first
        $(document).off('click.enhancedExport');
        
        // Use event delegation to handle dynamically added export buttons
        $(document).on('click.enhancedExport', '.export-btn', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(e.currentTarget);
            console.log('🖱️ Export button clicked:', $btn[0]);
            console.log('Button classes:', $btn[0].className);
            console.log('Button data:', $btn.data());
            
            if (this.isProcessing) {
                console.warn('⚠️ Export already in progress, ignoring click');
                return;
            }

            console.log('✅ Processing export request...');
            this.handleExportRequest($btn);
        });
        
        // Also attach direct handler for immediate buttons
        const exportButtons = document.querySelectorAll('.export-btn');
        console.log(`🔍 Found ${exportButtons.length} export buttons`);
        exportButtons.forEach((btn, index) => {
            console.log(`Button ${index + 1}:`, {
                id: btn.id,
                classes: btn.className,
                dataset: btn.dataset
            });
        });
        
        console.log('✅ Export handlers attached successfully');
    }

    async handleExportRequest($btn) {
        this.isProcessing = true;
        
        try {
            // Show loading state
            this.showExportLoading($btn);
            
            // Create processing timer
            const processingTimer = new ProcessingTimer();
            this.currentProcessingTimer = processingTimer;
            processingTimer.start();

            // Show loading SweetAlert
            const loadingSwal = Swal.fire({
                title: 'Processing Export...',
                html: `
                    <div class="export-loading">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p>Preparing your export request...</p>
                        <div class="progress mb-2">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                        </div>
                        <small class="text-muted">Processing time: <span id="processing-time">0</span> seconds</small>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    let seconds = 0;
                    loadingSwal.timeInterval = setInterval(() => {
                        seconds++;
                        $('#processing-time').text(seconds);
                    }, 1000);
                }
            });

            // Collect form data and prepare for YBB API
            const formData = this.collectFormData($btn);
            
            // Add CSRF token from form or meta tag
            this.addCSRFToken(formData);
            
            // Convert to YBB API format if needed
            const ybbData = this.prepareYbbApiData(formData);

            // Make AJAX request
            const exportUrl = $btn.data('url') || '/users/participants/export';
            console.log('🌐 Making AJAX request to:', exportUrl);
            $.ajax({
                url: exportUrl,
                method: 'POST',
                data: JSON.stringify(ybbData),
                contentType: 'application/json',
                dataType: 'json',
                timeout: 120000, // Increased to 2 minutes for large exports
                success: (response) => {
                    console.log('=== AJAX SUCCESS RESPONSE ===');
                    console.log('Raw AJAX response:', response);
                    
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

        } catch (error) {
            console.error('Error in export request:', error);
            this.handleExportError({ responseText: error.message }, $btn);
        } finally {
            this.isProcessing = false;
        }
    }

    handleExportSuccess(response, $btn, processingTimer) {
        this.hideExportLoading($btn);

        console.log('=== EXPORT SUCCESS RESPONSE ===');
        console.log('Full response:', response);
        console.log('Response.data keys:', Object.keys(response.data || {}));
        console.log('Response.data content:', response.data);

        if (response.success) {
            // Store export information
            this.currentExports.set(response.exportId, response);

            // Check if we have an export ID for status polling
            const exportId = response.data?.export_id || response.exportId || response.data?.id;
            let downloadUrl = response.data?.download_url || response.downloadUrl || response.data?.downloadUrl;
            
            // Add to export history
            if (exportId) {
                this.addToExportHistory({
                    export_id: exportId,
                    export_type: 'participants',
                    template: response.data?.template || 'standard',
                    total_records: response.data?.total_records || response.data?.records_exported || 0
                });
            }
            
            // Convert relative download URL to full Railway API URL only if it's not a CodeIgniter proxy URL
            if (downloadUrl && !downloadUrl.startsWith('http') && !downloadUrl.startsWith('/exports/')) {
                downloadUrl = this.getFullApiUrl(downloadUrl);
            }
            
            console.log('Determined exportId:', exportId);
            console.log('Determined downloadUrl:', downloadUrl);
            console.log('Response status:', response.status);
            console.log('Response.data status:', response.data?.status);
            
            // Check if export is already completed (since we have download_url, it's completed)
            const isCompleted = !!(downloadUrl && exportId);
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
                console.log('✅ Export completed immediately, showing main success notification');
                console.log('🎯 About to call showMainSuccessNotification with:', { response, processingTimer });
                processingTimer.stop(response.processingTime || response.data?.processing_time);
                
                // Use the centralized success notification method
                try {
                    console.log('🚀 Calling showMainSuccessNotification...');
                    this.showMainSuccessNotification(response, processingTimer);
                    console.log('✅ showMainSuccessNotification call completed');
                } catch (error) {
                    console.error('❌ Error in showMainSuccessNotification:', error);
                    console.error('❌ Error stack:', error.stack);
                    // Fallback notification
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Export Complete!',
                            text: `Successfully exported ${response.data?.record_count || 'unknown'} records. Click Download to get your file.`,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Download Now',
                            cancelButtonText: 'Close',
                            confirmButtonColor: '#28a745'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const url = this.getDownloadUrl(response, exportId);
                                if (url) {
                                    this.downloadFile(url, response.data?.file_name || 'export.xlsx');
                                }
                            }
                        });
                    }
                }
            } else {
                console.error('❌ No valid export ID found in response');
                this.showErrorMessage('Invalid export response: No export ID found');
            }
        } else {
            this.showErrorMessage(response.message || 'Export failed');
        }
    }

    /**
     * Show the main success notification with download/view options
     */
    showMainSuccessNotification(response, processingTimer) {
        console.log('=== SHOWING MAIN SUCCESS NOTIFICATION ===');
        console.log('Response data:', response);
        console.log('Response type:', typeof response);
        console.log('Response keys:', Object.keys(response || {}));
        
        // Check SweetAlert availability first
        const SwalInstance = window.Swal || window.swal;
        console.log('🍬 SweetAlert availability check:');
        console.log('  - window.Swal:', typeof window.Swal);
        console.log('  - window.swal:', typeof window.swal);
        console.log('  - SwalInstance:', typeof SwalInstance);
        console.log('  - SwalInstance.fire:', typeof SwalInstance?.fire);
        
        const exportId = response.exportId || response.export_id;
        
        // Add validation check
        if (!response) {
            console.error('❌ No response data provided to showMainSuccessNotification');
            this.showErrorMessage('No export data available');
            return;
        }
        
        // Use helper method for robust data extraction
        const responseData = response.data || {};
        const metadata = response.metadata || {};
        
        console.log('📊 Data extraction sources:', {
            responseData: Object.keys(responseData),
            metadata: Object.keys(metadata),
            directFromResponse: {
                recordCount: response.recordCount,
                fileSize: response.fileSize,
                fileName: response.fileName
            }
        });
        
        // Extract comprehensive data from the nested response.data structure
        const recordCount = this.extractValue(response, responseData, metadata, 
            ['record_count', 'recordCount', 'totalRecords'], 0);
        
        const fileSize = this.extractValue(response, responseData, metadata, 
            ['file_size', 'fileSize', 'file_size_bytes', 'size']);
        
        const fileName = this.extractValue(response, responseData, metadata, 
            ['file_name', 'fileName', 'filename']) || 'Export_File.xlsx';
        
        const exportType = this.extractValue(response, responseData, metadata, 
            ['exportType', 'export_type', 'type']) || this.guessExportTypeFromContext() || 'Data';
            
        // Extract performance metrics for detailed display
        const performanceMetrics = responseData.performance_metrics || {};
        const processingTime = performanceMetrics.processing_time_ms || performanceMetrics.total_processing_time_seconds * 1000 || null;
        const recordsPerSecond = performanceMetrics.records_per_second || null;
        const memoryUsed = performanceMetrics.memory_used_mb || performanceMetrics.peak_memory_mb || null;
        const exportStrategy = this.extractValue(response, responseData, metadata, 
            ['export_strategy', 'exportStrategy'], 'single_file');
        const template = this.extractValue(response, responseData, metadata, 
            ['template'], 'standard');
        const isChunked = responseData.is_chunked_export || false;
        const totalChunks = responseData.total_chunks || 1;
        const compressionUsed = metadata.compression_used || 'none';
        
            // Fix download URL extraction - make sure to convert relative URLs
            let downloadUrl = this.getDownloadUrl(response, exportId);
            
            // Don't prepend API base URL to CodeIgniter proxy URLs
            if (downloadUrl && !downloadUrl.startsWith('http') && !downloadUrl.startsWith('/exports/')) {
                downloadUrl = this.getFullApiUrl(downloadUrl);
            }        console.log('📋 Extracted values before processing:', {
            recordCount, fileSize, fileName, exportType, downloadUrl, exportId
        });
        
        // Format file size
        const fileSizeFormatted = fileSize ? this.formatFileSize(fileSize) : 'Unknown size';
        
        console.log('Main success notification data:', {
            recordCount, fileName, fileSizeFormatted, exportType, downloadUrl
        });
        
        if (SwalInstance && typeof SwalInstance.fire === 'function') {
            try {
                // Sanitize and validate data before building HTML
                const safeRecordCount = (recordCount || 0).toLocaleString();
                const safeFileSizeFormatted = this.escapeHtml(fileSizeFormatted || 'Unknown');
                const safeExportType = this.escapeHtml(exportType || 'XLSX');
                const safeFileName = this.escapeHtml(fileName || 'export.xlsx');
                
                // Build comprehensive metrics display
                const performanceSection = this.buildPerformanceMetricsHtml({
                    processingTime, recordsPerSecond, memoryUsed, exportStrategy, 
                    template, isChunked, totalChunks, compressionUsed
                });
                
                const htmlContent = `
                    <div class="export-success-details">
                        <!-- Main Stats Row -->
                        <div class="row mb-3">
                            <div class="col-4">
                                <div class="stat-item text-center p-3 bg-light rounded">
                                    <div class="stat-number text-primary fw-bold fs-4">${safeRecordCount}</div>
                                    <div class="stat-label text-muted small">Records Exported</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item text-center p-3 bg-light rounded">
                                    <div class="stat-number text-success fw-bold fs-4">${safeFileSizeFormatted}</div>
                                    <div class="stat-label text-muted small">File Size</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item text-center p-3 bg-light rounded">
                                    <div class="stat-number text-info fw-bold fs-4">${this.escapeHtml(template.toUpperCase())}</div>
                                    <div class="stat-label text-muted small">Template</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Performance Metrics Section -->
                        ${performanceSection}
                        
                        <!-- File Information -->
                        <div class="text-center mt-3 p-3 bg-light rounded">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-file-excel text-success fs-3"></i>
                                </div>
                                <div class="col">
                                    <div class="text-start">
                                        <div class="fw-bold text-dark">${safeFileName}</div>
                                        <div class="text-muted small">
                                            Strategy: ${this.escapeHtml(exportStrategy.replace('_', ' ').toUpperCase())}
                                            ${isChunked ? ` | Chunks: ${totalChunks}` : ''}
                                            ${compressionUsed !== 'none' ? ` | Compressed: ${compressionUsed.toUpperCase()}` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                console.log('🎯 About to show SUCCESS SweetAlert with data:', {
                    recordCount, fileSize, fileName, exportType, downloadUrl
                });
                
                // Additional validation before showing SweetAlert
                if (!downloadUrl) {
                    console.error('❌ No download URL available for SweetAlert');
                    this.showErrorMessage('Export completed but download URL is not available');
                    return;
                }
                
                SwalInstance.fire({
                    html: htmlContent,
                    title: '🎉 Export Complete!',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-download"></i> Download Now',
                    cancelButtonText: '<i class="fas fa-chart-line"></i> View Details',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    width: 750, // Increased width for metrics
                    customClass: {
                        popup: 'export-success-popup enhanced-metrics',
                        title: 'export-success-title',
                        htmlContainer: 'export-success-content',
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    didRender: () => {
                        // Add custom CSS for metrics display
                        const style = document.createElement('style');
                        style.textContent = `
                            .enhanced-metrics .metric-item {
                                transition: transform 0.2s ease;
                            }
                            .enhanced-metrics .metric-item:hover {
                                transform: translateY(-2px);
                                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                            }
                            .enhanced-metrics .stat-item:hover {
                                transform: translateY(-2px);
                                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                                transition: all 0.2s ease;
                            }
                            .enhanced-metrics .performance-metrics {
                                background: #f8f9fa;
                                border-radius: 8px;
                                padding: 15px;
                                margin: 10px 0;
                            }
                        `;
                        document.head.appendChild(style);
                    },
                    allowOutsideClick: false,
                    allowEscapeKey: true,
                    didOpen: () => {
                        console.log('✅ SUCCESS SweetAlert opened successfully!');
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
                        console.log('SweetAlert download - Using URL:', downloadUrl);
                        this.downloadFile(downloadUrl, fileName);
                    } else if (result.dismiss === SwalInstance.DismissReason.cancel) {
                        // Show detailed results
                        if (response.exportStrategy === 'multi_file' || response.data?.export_strategy === 'multi_file') {
                            this.showMultiFileExportResult(response, processingTimer);
                        } else {
                            this.showSingleFileExportResult(response, processingTimer);
                        }
                    }
                });
                
            } catch (error) {
                console.error('❌ Error showing SweetAlert:', error);
                console.error('❌ Error stack:', error.stack);
                console.error('❌ Response data when error occurred:', response);
                console.error('❌ HTML content that caused the error:', htmlContent);
                
                // Don't show an error message for successful exports - instead provide fallback
                console.log('🎉 Export completed successfully, using fallback notification due to SweetAlert display issue');
                
                // Still try to provide download functionality as fallback
                const downloadUrl = this.getDownloadUrl(response, exportId);
                if (downloadUrl) {
                    console.log('🔄 Attempting fallback download with URL:', downloadUrl);
                    
                    // Use a simpler SweetAlert or browser alert as fallback
                    try {
                        // Try a simple SweetAlert first
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Export Complete!',
                                text: `Successfully exported ${recordCount.toLocaleString()} records. Click Download to get your file.`,
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Download Now',
                                cancelButtonText: 'Close',
                                confirmButtonColor: '#28a745'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    this.downloadFile(downloadUrl, fileName);
                                }
                            });
                        } else {
                            // Fallback to browser confirm
                            if (confirm(`Export Complete!\n\nSuccessfully exported ${recordCount.toLocaleString()} records.\n\nClick OK to download now.`)) {
                                console.log('Fallback download - Using URL:', downloadUrl);
                                this.downloadFile(downloadUrl, fileName);
                            }
                        }
                    } catch (fallbackError) {
                        console.error('❌ Even fallback SweetAlert failed:', fallbackError);
                        // Last resort - browser confirm
                        if (confirm(`Export Complete!\n\nSuccessfully exported ${recordCount.toLocaleString()} records.\n\nClick OK to download now.`)) {
                            console.log('Final fallback download - Using URL:', downloadUrl);
                            this.downloadFile(downloadUrl, fileName);
                        }
                    }
                } else {
                    console.error('❌ No download URL available even for fallback');
                    // Only show an error if there's actually no download URL
                    this.showErrorMessage('Export completed but download URL is not available. Please contact support.');
                }
            }
        } else {
            console.log('❌ SweetAlert2 not available, trying simple alert');
            
            // Simple browser alert as fallback
            if (confirm(`Export Complete!\n\nSuccessfully exported ${recordCount.toLocaleString()} records.\n\nClick OK to download now, or Cancel to view details.`)) {
                console.log('Fallback download - Using URL:', downloadUrl);
                this.downloadFile(downloadUrl, fileName);
            } else {
                // Show detailed results
                if (response.exportStrategy === 'multi_file' || response.data?.export_strategy === 'multi_file') {
                    this.showMultiFileExportResult(response, processingTimer);
                } else {
                    this.showSingleFileExportResult(response, processingTimer);
                }
            }
        }
    }

    startStatusPolling(exportId, processingTimer, pollInterval = 3000) {
        console.log('Starting status polling for export:', exportId);
        
        let attemptCount = 0;
        const maxAttempts = 100;

        const pollFunction = async () => {
            attemptCount++;
            console.log(`Polling attempt ${attemptCount}/${maxAttempts} for export ${exportId}`);

            try {
                const statusResponse = await this.checkExportStatus(exportId);
                
                if (statusResponse && statusResponse.success) {
                    this.updateExportStatus(exportId, statusResponse);

                    if (statusResponse.status === 'success' || statusResponse.status === 'completed' || statusResponse.status === 'ready') {
                        console.log('🎯 Export completed via polling, stopping');
                        this.stopStatusPolling();
                        return;
                    }
                }

                if (attemptCount < maxAttempts) {
                    this.intervalId = setTimeout(pollFunction, pollInterval);
                    this.activeIntervals.push(this.intervalId);
                } else {
                    console.warn('Max polling attempts reached for export:', exportId);
                    this.stopStatusPolling();
                    this.showErrorMessage('Export status check timed out.');
                }

            } catch (error) {
                console.error('Error during status polling:', error);
                
                if (attemptCount < maxAttempts) {
                    const backoffInterval = Math.min(pollInterval * Math.pow(1.5, attemptCount / 10), 30000);
                    this.intervalId = setTimeout(pollFunction, backoffInterval);
                    this.activeIntervals.push(this.intervalId);
                } else {
                    console.error('Max polling attempts reached due to errors');
                    this.stopStatusPolling();
                    this.showErrorMessage('Export status check failed.');
                }
            }
        };

        this.intervalId = setTimeout(pollFunction, pollInterval);
        this.activeIntervals.push(this.intervalId);
    }

    stopStatusPolling() {
        console.log('=== STOPPING STATUS POLLING ===');
        console.log('Current intervalId:', this.intervalId);
        
        if (this.intervalId) {
            clearTimeout(this.intervalId);
            clearInterval(this.intervalId);
            console.log('Status polling stopped and cleared');
            this.intervalId = null;
        }

        console.log('Force stopping all active intervals as failsafe');
        this.clearAnyExistingIntervals();
    }

    async checkExportStatus(exportId, retryCount = 0, maxRetries = 3) {
        try {
            console.log(`🔄 Checking export status for: ${exportId}`);
            
            const statusUrl = `/exports/status/${exportId}`;
            console.log('🔍 Polling status at:', statusUrl);
            const response = await fetch(statusUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('✅ Status check successful:', data);
            return data;

        } catch (error) {
            console.error(`❌ Status check failed:`, error);
            
            if (retryCount < maxRetries) {
                const delay = Math.pow(2, retryCount) * 1000;
                return new Promise(resolve => {
                    setTimeout(async () => {
                        const result = await this.checkExportStatus(exportId, retryCount + 1, maxRetries);
                        resolve(result);
                    }, delay);
                });
            } else {
                throw error;
            }
        }
    }

    updateExportStatus(exportId, statusData) {
        console.log('🚨 EXPORT COMPLETED - STOPPING ALL POLLING NOW! 🚨');
        console.log('Export ID:', exportId);
        console.log('Status:', statusData.status);
        console.log('Download URL:', statusData.downloadUrl);

        // Check if export is completed (YBB API uses 'success' status)
        if (statusData.status === 'success' || statusData.status === 'completed' || statusData.status === 'ready' || statusData.downloadUrl) {
            // Stop polling
            this.stopStatusPolling();
            
            // Stop the processing timer
            if (this.currentProcessingTimer) {
                this.currentProcessingTimer.stop(statusData.processingTime);
            }
            
            console.log('🎯 EXPORT COMPLETED VIA POLLING - Showing main success SweetAlert');
            
            // Show the main success SweetAlert
            this.showMainSuccessNotification(statusData, this.currentProcessingTimer);
            
            // Re-enable export buttons
            $('.export-btn').prop('disabled', false).html('<i class="fas fa-download"></i> Export');
        }
    }

    showSingleFileExportResult(response, processingTimer) {
        const recordCount = response.recordCount || response.record_count || 0;
        const exportId = response.exportId || response.export_id;
        const downloadUrl = this.getDownloadUrl(response, exportId);

        const html = `
            <div class="alert alert-success export-result">
                <h4><i class="fas fa-check-circle text-success"></i> Export Completed Successfully</h4>
                <div class="export-details">
                    <p><strong>Records:</strong> ${recordCount.toLocaleString()}</p>
                    <p><strong>File Size:</strong> ${response.fileSizeFormatted || 'N/A'}</p>
                    <p><strong>Processing Time:</strong> ${processingTimer ? processingTimer.getClientTime() + 's' : 'N/A'}</p>
                    <a href="${downloadUrl}" class="btn btn-success">
                        <i class="fas fa-download"></i> Download File
                    </a>
                </div>
            </div>
        `;

        this.displayResult(html);
    }

    showMultiFileExportResult(response, processingTimer) {
        this.showSingleFileExportResult(response, processingTimer);
    }

    displayResult(html) {
        const SwalInstance = window.Swal || window.swal;
        
        if (SwalInstance && typeof SwalInstance.fire === 'function') {
            try {
                SwalInstance.fire({
                    title: '<i class="fas fa-file-export"></i> Export Results',
                    html: html,
                    width: 800,
                    showCloseButton: true,
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#6c757d'
                });
            } catch (error) {
                console.error('Error showing result popup:', error);
                alert('Export completed successfully!');
            }
        } else {
            alert('Export completed successfully!');
        }
    }

    handleExportError(xhr, $btn) {
        this.hideExportLoading($btn);
        
        let errorMessage = 'An error occurred during export.';
        let errorCode = 'UNKNOWN_ERROR';
        let details = null;
        
        // Check for timeout error
        if (xhr.statusText === 'timeout' || xhr.status === 0) {
            errorMessage = 'Export request timed out. For large datasets, the export may still be processing in the background. Please check your exports list or try again with smaller data chunks.';
            errorCode = 'REQUEST_TIMEOUT';
            console.warn('⏰ Export request timed out, but export may still be processing');
        } else if (xhr.responseJSON) {
            // Parse API-documented error response format
            const response = xhr.responseJSON;
            errorMessage = response.message || errorMessage;
            errorCode = response.error_code || errorCode;
            details = response.details || null;
            
            // Provide specific guidance based on documented error codes
            switch (errorCode) {
                case 'VALIDATION_ERROR':
                    errorMessage += ' Please check your data format and try again.';
                    break;
                case 'TEMPLATE_NOT_FOUND':
                    errorMessage += ' Please select a valid template (standard, detailed, summary, or complete).';
                    break;
                case 'DATASET_TOO_LARGE':
                    errorMessage += ' Try exporting smaller chunks or use filters to reduce the dataset size.';
                    break;
                case 'MEMORY_LIMIT_EXCEEDED':
                    errorMessage += ' The dataset is too large for single processing. It will be automatically chunked.';
                    break;
                case 'PROCESSING_TIMEOUT':
                    errorMessage += ' Please try again with a smaller dataset or contact support.';
                    break;
                case 'EXPORT_NOT_FOUND':
                case 'EXPORT_EXPIRED':
                    errorMessage += ' Please create a new export request.';
                    break;
                default:
                    if (xhr.status >= 500) {
                        errorMessage += ' Please try again in a few minutes or contact support if the issue persists.';
                    }
            }
        } else if (xhr.responseText) {
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || response.error || errorMessage;
                errorCode = response.error_code || errorCode;
            } catch (e) {
                errorMessage = xhr.responseText;
            }
        } else if (xhr.status) {
            errorMessage = `HTTP ${xhr.status}: ${xhr.statusText || 'Unknown error'}`;
            
            // Provide guidance based on HTTP status codes
            switch (xhr.status) {
                case 400:
                    errorMessage += ' Please check your request parameters.';
                    break;
                case 413:
                    errorMessage += ' The dataset is too large. Try reducing the size or use chunked processing.';
                    break;
                case 429:
                    errorMessage += ' Too many requests. Please wait a moment before trying again.';
                    break;
                case 503:
                    errorMessage += ' The service is temporarily unavailable. Please try again later.';
                    break;
            }
        }
        
        console.error('Export error details:', {
            status: xhr.status,
            statusText: xhr.statusText,
            responseText: xhr.responseText,
            errorCode: errorCode,
            errorMessage: errorMessage,
            details: details
        });
        
        // Show enhanced error message with code
        this.showEnhancedErrorMessage(errorMessage, errorCode, details);
    }

    showEnhancedErrorMessage(message, errorCode = null, details = null) {
        const SwalInstance = window.Swal || window.swal;
        
        if (SwalInstance && typeof SwalInstance.fire === 'function') {
            let htmlContent = `
                <div class="error-details">
                    <div class="text-center mb-3">
                        <i class="fas fa-exclamation-triangle text-warning fs-1"></i>
                    </div>
                    <p class="mb-3">${message}</p>
            `;
            
            if (errorCode && errorCode !== 'UNKNOWN_ERROR') {
                htmlContent += `
                    <div class="alert alert-light border text-start">
                        <small class="text-muted">
                            <strong>Error Code:</strong> ${errorCode}<br>
                            <strong>Request ID:</strong> ${Date.now().toString(36)}
                        </small>
                    </div>
                `;
            }
            
            if (details) {
                htmlContent += `
                    <div class="mt-3">
                        <details class="text-start">
                            <summary class="text-muted small">Technical Details</summary>
                            <pre class="small mt-2 text-muted">${JSON.stringify(details, null, 2)}</pre>
                        </details>
                    </div>
                `;
            }
            
            htmlContent += '</div>';
            
            SwalInstance.fire({
                html: htmlContent,
                title: 'Export Error',
                icon: 'error',
                confirmButtonText: 'Try Again',
                showCancelButton: true,
                cancelButtonText: 'Close',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                width: 600
            });
        } else {
            alert(`Export Error: ${message}`);
        }
    }

    showExportLoading($btn) {
        $btn.prop('disabled', true);
        $btn.data('original-html', $btn.html());
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    }

    hideExportLoading($btn) {
        $btn.prop('disabled', false);
        const originalHtml = $btn.data('original-html') || '<i class="fas fa-download"></i> Export';
        $btn.html(originalHtml);
    }

    collectFormData($btn) {
        const formData = {};
        const formSelector = $btn.data('form-selector') || '#exportForm';
        const $form = $(formSelector);
        
        if ($form.length) {
            const serializedData = $form.serializeArray();
            serializedData.forEach(item => {
                formData[item.name] = item.value;
            });
        }
        
        return formData;
    }

    prepareYbbApiData(formData) {
        // Convert form data to YBB API expected format
        // The API expects 'data' to be an array/list, not an object
        const exportConfig = {
            template: formData.template || 'standard',
            format: formData.format || 'excel',
            filters: {},
            filename: formData.filename || null,
            sheet_name: formData.sheet_name || 'Data'
        };

        // Add program_id as filter if present
        if (formData.program_id) {
            exportConfig.filters.program_id = formData.program_id;
        }

        // Add limit as filter if present
        if (formData.limit) {
            exportConfig.filters.limit = parseInt(formData.limit);
        }

        // Add other form fields as filters
        Object.keys(formData).forEach(key => {
            if (!['template', 'format', 'filename', 'sheet_name', 'program_id', 'limit'].includes(key) && 
                !key.startsWith('csrf_')) {
                exportConfig.filters[key] = formData[key];
            }
        });

        // Wrap the export configuration in an array as expected by the API
        const ybbData = {
            data: [exportConfig]  // API expects an array/list
        };

        console.log('Prepared YBB API data:', ybbData);
        return ybbData;
    }

    addCSRFToken(formData) {
        // Try to get CSRF token from multiple sources
        let csrfToken = null;
        let csrfName = null;

        // Method 1: Get from form field
        const $csrfField = $('input[name^="csrf_"]');
        if ($csrfField.length) {
            csrfName = $csrfField.attr('name');
            csrfToken = $csrfField.val();
        }

        // Method 2: Get from meta tags
        if (!csrfToken) {
            const csrfNameMeta = $('meta[name="csrf-token-name"]').attr('content');
            const csrfValueMeta = $('meta[name="csrf-token"]').attr('content');
            if (csrfNameMeta && csrfValueMeta) {
                csrfName = csrfNameMeta;
                csrfToken = csrfValueMeta;
            }
        }

        // Method 3: Try common CodeIgniter 4 names
        if (!csrfToken) {
            const commonNames = ['csrf_test_name', 'csrf_token', '_token'];
            for (const name of commonNames) {
                const $field = $(`input[name="${name}"]`);
                if ($field.length) {
                    csrfName = name;
                    csrfToken = $field.val();
                    break;
                }
            }
        }

        // Method 4: Get from global variables if available
        if (!csrfToken) {
            if (typeof csrfTokenName !== 'undefined' && typeof csrfHashValue !== 'undefined') {
                csrfName = csrfTokenName;
                csrfToken = csrfHashValue;
            }
        }

        if (csrfToken && csrfName) {
            formData[csrfName] = csrfToken;
            console.log('CSRF token added:', csrfName, '=', csrfToken);
        } else {
            console.warn('CSRF token not found - request may fail');
        }

        return formData;
    }

    showErrorMessage(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Export Error',
                text: message,
                confirmButtonColor: '#dc3545'
            });
        } else {
            alert('Error: ' + message);
        }
    }

    // Export Status Tracking Methods
    checkExportStatus(exportId) {
        if (!exportId) {
            console.warn('No export ID provided for status check');
            return;
        }
        
        console.log(`📋 Checking status for export: ${exportId}`);
        
        $.ajax({
            url: '/participants/export_status',
            method: 'GET',
            data: { export_id: exportId },
            timeout: 30000,
            success: (response) => {
                this.handleStatusResponse(response, exportId);
            },
            error: (xhr, status, error) => {
                console.error('Status check failed:', { xhr, status, error });
                this.showStatusError(exportId, error);
            }
        });
    }
    
    handleStatusResponse(response, exportId) {
        console.log('📊 Export status response:', response);
        
        if (response.success && response.data) {
            const status = response.data.status || 'unknown';
            const progress = response.data.progress || 0;
            const estimated_completion = response.data.estimated_completion;
            
            console.log(`Status: ${status}, Progress: ${progress}%`);
            
            switch (status.toLowerCase()) {
                case 'queued':
                case 'pending':
                    this.showStatusMessage(exportId, 'Export is queued for processing...', 'info', progress);
                    break;
                case 'processing':
                case 'running':
                    this.showStatusMessage(
                        exportId, 
                        `Export is processing... (${progress}% complete)`,
                        'info',
                        progress,
                        estimated_completion
                    );
                    break;
                case 'completed':
                case 'ready':
                    if (response.data.download_url) {
                        this.showCompletedExport(exportId, response.data);
                    } else {
                        this.showStatusMessage(exportId, 'Export completed successfully!', 'success', 100);
                    }
                    break;
                case 'failed':
                case 'error':
                    const errorMsg = response.data.error_message || 'Export failed';
                    this.showStatusMessage(exportId, errorMsg, 'error', progress);
                    break;
                default:
                    this.showStatusMessage(exportId, `Export status: ${status}`, 'info', progress);
            }
        } else {
            this.showStatusError(exportId, response.message || 'Unable to retrieve export status');
        }
    }
    
    showStatusMessage(exportId, message, type = 'info', progress = null, estimatedCompletion = null) {
        if (typeof Swal !== 'undefined') {
            let htmlContent = `
                <div class="status-display">
                    <div class="text-center mb-3">
                        <strong>Export ID:</strong> ${exportId}
                    </div>
                    <p class="mb-3">${message}</p>
            `;
            
            if (progress !== null && progress >= 0) {
                htmlContent += `
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped ${type === 'error' ? 'bg-danger' : 'bg-primary'}" 
                             role="progressbar" 
                             style="width: ${Math.min(progress, 100)}%"
                             aria-valuenow="${progress}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            ${Math.round(progress)}%
                        </div>
                    </div>
                `;
            }
            
            if (estimatedCompletion) {
                htmlContent += `
                    <div class="alert alert-light border text-center">
                        <small class="text-muted">
                            <strong>Estimated Completion:</strong> ${estimatedCompletion}
                        </small>
                    </div>
                `;
            }
            
            htmlContent += '</div>';
            
            const iconMap = {
                'info': 'info',
                'success': 'success',
                'error': 'error',
                'warning': 'warning'
            };
            
            Swal.fire({
                title: 'Export Status',
                html: htmlContent,
                icon: iconMap[type] || 'info',
                confirmButtonText: 'Check Again',
                showCancelButton: true,
                cancelButtonText: 'Close',
                width: 500
            }).then((result) => {
                if (result.isConfirmed && (type === 'info' || progress < 100)) {
                    // Auto-refresh status for ongoing processes
                    setTimeout(() => this.checkExportStatus(exportId), 2000);
                }
            });
        } else {
            alert(`Export Status: ${message}`);
        }
    }
    
    showCompletedExport(exportId, data) {
        if (typeof Swal !== 'undefined') {
            let htmlContent = `
                <div class="completed-export">
                    <div class="text-center mb-3">
                        <i class="fas fa-check-circle text-success fs-1"></i>
                    </div>
                    <p class="mb-3">Export completed successfully!</p>
                    <div class="text-center">
                        <strong>Export ID:</strong> ${exportId}<br>
            `;
            
            if (data.file_name) {
                htmlContent += `<strong>File:</strong> ${data.file_name}<br>`;
            }
            
            if (data.file_size) {
                htmlContent += `<strong>Size:</strong> ${this.formatFileSize(data.file_size)}<br>`;
            }
            
            if (data.records_exported) {
                htmlContent += `<strong>Records:</strong> ${data.records_exported}<br>`;
            }
            
            htmlContent += '</div>';
            
            if (data.download_url) {
                htmlContent += `
                    <div class="mt-3 text-center">
                        <a href="${data.download_url}" 
                           class="btn btn-success btn-lg" 
                           download="${data.file_name || 'export.xlsx'}">
                            <i class="fas fa-download me-2"></i>Download Export
                        </a>
                    </div>
                `;
            }
            
            htmlContent += '</div>';
            
            Swal.fire({
                title: 'Export Ready',
                html: htmlContent,
                icon: 'success',
                confirmButtonText: 'Close',
                width: 500
            });
        }
    }
    
    showStatusError(exportId, error) {
        console.error(`Status check error for export ${exportId}:`, error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Status Check Failed',
                text: `Unable to check status for export ${exportId}: ${error}`,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        } else {
            alert(`Status check failed for export ${exportId}: ${error}`);
        }
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Debug methods for testing
    testSweetAlert() {
        console.log('Testing SweetAlert2...');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'SweetAlert2 Test',
                text: 'SweetAlert2 is working correctly!',
                icon: 'success',
                confirmButtonText: 'Great!'
            });
        } else {
            console.error('SweetAlert2 not available');
            alert('SweetAlert2 not available');
        }
    }

    testExportSuccessNotification() {
        console.log('Testing export success notification...');
        const mockResponse = {
            success: true,
            exportId: 'test-123',
            message: 'Test export completed successfully',
            data: {
                recordCount: 1500,
                fileSize: 2048576,
                fileName: 'test_export.xlsx',
                downloadUrl: '#test-download',
                exportType: 'XLSX'
            }
        };
        const mockTimer = { getElapsedTime: () => '2.5 seconds' };
        this.showMainSuccessNotification(mockResponse, mockTimer);
    }

    // Utility method to escape HTML to prevent XSS and parsing issues
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    buildPerformanceMetricsHtml(metrics) {
        const {
            processingTime, recordsPerSecond, memoryUsed, exportStrategy, 
            template, isChunked, totalChunks, compressionUsed
        } = metrics;
        
        let performanceHtml = '';
        
        // Only show performance section if we have meaningful metrics
        if (processingTime || recordsPerSecond || memoryUsed) {
            const metricsItems = [];
            
            if (processingTime) {
                const timeDisplay = processingTime < 1000 
                    ? `${Math.round(processingTime)}ms`
                    : `${(processingTime / 1000).toFixed(1)}s`;
                metricsItems.push(`
                    <div class="col-md-4 col-6">
                        <div class="metric-item text-center p-2 bg-white rounded border">
                            <div class="metric-value text-warning fw-bold">${timeDisplay}</div>
                            <div class="metric-label text-muted small">Processing Time</div>
                        </div>
                    </div>
                `);
            }
            
            if (recordsPerSecond) {
                const rpsDisplay = recordsPerSecond > 1000 
                    ? `${(recordsPerSecond / 1000).toFixed(1)}K/s`
                    : `${Math.round(recordsPerSecond)}/s`;
                metricsItems.push(`
                    <div class="col-md-4 col-6">
                        <div class="metric-item text-center p-2 bg-white rounded border">
                            <div class="metric-value text-primary fw-bold">${rpsDisplay}</div>
                            <div class="metric-label text-muted small">Records/Sec</div>
                        </div>
                    </div>
                `);
            }
            
            if (memoryUsed) {
                const memoryDisplay = memoryUsed < 1 
                    ? `${Math.round(memoryUsed * 1024)}KB`
                    : `${memoryUsed.toFixed(1)}MB`;
                metricsItems.push(`
                    <div class="col-md-4 col-6">
                        <div class="metric-item text-center p-2 bg-white rounded border">
                            <div class="metric-value text-secondary fw-bold">${memoryDisplay}</div>
                            <div class="metric-label text-muted small">Memory Used</div>
                        </div>
                    </div>
                `);
            }
            
            if (metricsItems.length > 0) {
                performanceHtml = `
                    <div class="performance-metrics mb-3">
                        <div class="text-center mb-2">
                            <small class="text-muted fw-bold">📊 Performance Metrics</small>
                        </div>
                        <div class="row g-2">
                            ${metricsItems.join('')}
                        </div>
                    </div>
                `;
            }
        }
        
        return performanceHtml;
    }

    getDownloadUrl(response, exportId) {
        console.log('🔗 Getting download URL for:', { response, exportId });
        
        const possibleUrls = [
            response.downloadUrl,
            response.download_url,
            response.data?.download_url,
            response.data?.downloadUrl
        ];

        console.log('🔍 Checking possible URLs:', possibleUrls);

        for (const url of possibleUrls) {
            if (url && typeof url === 'string' && url.trim()) {
                // Convert YBB API URLs to CodeIgniter proxy URLs
                if (url.includes('/api/ybb/export/') && url.includes('/download')) {
                    // Extract export ID from the URL and create CodeIgniter proxy URL
                    const match = url.match(/\/api\/ybb\/export\/([^\/]+)\/download/);
                    if (match && match[1]) {
                        // Create absolute URL for CodeIgniter proxy
                        const proxyUrl = `${window.location.origin}/exports/download/${match[1]}`;
                        console.log('✅ Converted to CodeIgniter proxy URL:', proxyUrl);
                        return proxyUrl;
                    }
                } else if (url.startsWith('http')) {
                    // Already a full URL, use as-is
                    console.log('✅ Using full URL as-is:', url);
                    return url;
                } else if (url.startsWith('/exports/download/')) {
                    // Convert relative CodeIgniter proxy URL to absolute
                    const absoluteUrl = `${window.location.origin}${url}`;
                    console.log('✅ Converted relative CodeIgniter proxy URL to absolute:', absoluteUrl);
                    return absoluteUrl;
                }
            }
        }

        // Create absolute fallback URL
        const fallbackUrl = `${window.location.origin}/exports/download/${exportId}`;
        console.log('🔄 Using fallback URL:', fallbackUrl);
        return fallbackUrl;
    }

    downloadFile(downloadUrl, filename = '') {
        console.log('Initiating download:', downloadUrl);
        
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.style.display = 'none';
        
        if (filename) {
            link.download = filename;
        }
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    guessExportTypeFromContext() {
        if (window.location.href.includes('participants')) return 'Participants';
        if (window.location.href.includes('payments')) return 'Payments';
        if (window.location.href.includes('ambassadors')) return 'Ambassadors';
        return 'Data';
    }

    formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    extractValue(response, responseData, metadata, possibleKeys, defaultValue = null) {
        for (const source of [response, responseData, metadata]) {
            if (!source) continue;
            
            for (const key of possibleKeys) {
                if (source.hasOwnProperty(key) && source[key] !== null && source[key] !== undefined) {
                    return source[key];
                }
            }
        }
        return defaultValue;
    }

    /**
     * Convert relative API URLs to full YBB API URLs
     */
    getFullApiUrl(path) {
        // If it's already a full URL, return as-is
        if (path.startsWith('http://') || path.startsWith('https://')) {
            return path;
        }
        
        // Remove leading slash if present to avoid double slashes
        const cleanPath = path.startsWith('/') ? path.substring(1) : path;
        
        return `${this.ybbApiBaseUrl}/${cleanPath}`;
    }
}

class ProcessingTimer {
    constructor() {
        this.startTime = null;
        this.serverProcessingTime = null;
    }

    start() {
        this.startTime = Date.now();
    }

    stop(serverTime = null) {
        this.serverProcessingTime = serverTime;
    }

    getClientTime() {
        if (!this.startTime) return null;
        return ((Date.now() - this.startTime) / 1000).toFixed(1);
    }

    getServerTime() {
        return this.serverProcessingTime;
    }

    // Export History UI Methods
    showExportHistory() {
        if (this.exportHistory.length === 0) {
            this.showInfoMessage('No export history available', 'You haven\'t performed any exports yet.');
            return;
        }

        const historyHtml = this.buildExportHistoryHtml();
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Export History',
                html: historyHtml,
                width: 900,
                customClass: {
                    container: 'export-history-modal'
                },
                showCloseButton: true,
                showConfirmButton: false,
                didOpen: () => {
                    // Add click handlers for status check buttons
                    document.querySelectorAll('.check-export-status').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const exportId = e.target.dataset.exportId;
                            this.checkExportStatus(exportId);
                        });
                    });
                }
            });
        }
    }

    buildExportHistoryHtml() {
        const sortedHistory = this.exportHistory
            .sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
            .slice(0, 15); // Show last 15 exports

        let html = `
            <div class="export-history-container">
                <div class="mb-3 text-muted text-center">
                    <small>Showing ${sortedHistory.length} recent exports</small>
                </div>
                <div class="export-history-list">
        `;

        sortedHistory.forEach(entry => {
            const statusIcon = this.getStatusIcon(entry.status);
            const statusClass = this.getStatusClass(entry.status);
            const timeAgo = this.getTimeAgo(entry.timestamp);
            
            html += `
                <div class="export-history-item border rounded p-3 mb-2">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <span class="status-indicator ${statusClass}">
                                <i class="fas ${statusIcon}"></i>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <div class="export-details">
                                <strong>Export ID:</strong> ${entry.id}<br>
                                <small class="text-muted">
                                    ${entry.type} • ${entry.template} template • ${timeAgo}
                                </small>
                                ${entry.records_count ? `<br><small class="text-info">${entry.records_count} records</small>` : ''}
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <span class="badge badge-${this.getStatusBadgeClass(entry.status)}">
                                ${entry.status.toUpperCase()}
                            </span>
                        </div>
                        <div class="col-md-2 text-center">
                            <button class="btn btn-sm btn-outline-primary check-export-status" 
                                    data-export-id="${entry.id}" title="Check Status">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
                </div>
                <div class="mt-3 text-center">
                    <button class="btn btn-sm btn-outline-secondary" onclick="localStorage.removeItem('ybb_export_history'); location.reload();">
                        <i class="fas fa-trash-alt me-1"></i>Clear History
                    </button>
                </div>
            </div>
            <style>
                .export-history-container {
                    max-height: 500px;
                    overflow-y: auto;
                }
                .export-history-item:hover {
                    background-color: #f8f9fa;
                }
                .status-indicator {
                    display: inline-block;
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    line-height: 30px;
                    color: white;
                }
                .status-indicator.success { background-color: #28a745; }
                .status-indicator.processing { background-color: #007bff; }
                .status-indicator.error { background-color: #dc3545; }
                .status-indicator.pending { background-color: #ffc107; color: #000; }
            </style>
        `;

        return html;
    }

    getStatusIcon(status) {
        const icons = {
            'initiated': 'fa-clock',
            'queued': 'fa-hourglass-start',
            'pending': 'fa-hourglass-half',
            'processing': 'fa-spinner fa-spin',
            'running': 'fa-cog fa-spin',
            'completed': 'fa-check',
            'ready': 'fa-check-circle',
            'failed': 'fa-times',
            'error': 'fa-exclamation-triangle'
        };
        return icons[status] || 'fa-question';
    }

    getStatusClass(status) {
        const classes = {
            'initiated': 'pending',
            'queued': 'pending',
            'pending': 'pending',
            'processing': 'processing',
            'running': 'processing',
            'completed': 'success',
            'ready': 'success',
            'failed': 'error',
            'error': 'error'
        };
        return classes[status] || 'pending';
    }

    getStatusBadgeClass(status) {
        const classes = {
            'initiated': 'secondary',
            'queued': 'warning',
            'pending': 'warning',
            'processing': 'info',
            'running': 'info',
            'completed': 'success',
            'ready': 'success',
            'failed': 'danger',
            'error': 'danger'
        };
        return classes[status] || 'secondary';
    }

    getTimeAgo(timestamp) {
        const now = new Date();
        const time = new Date(timestamp);
        const diffMs = now - time;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} min ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays < 7) return `${diffDays}d ago`;
        
        return time.toLocaleDateString();
    }

    showInfoMessage(title, message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: message,
                icon: 'info',
                confirmButtonText: 'OK'
            });
        } else {
            alert(`${title}: ${message}`);
        }
    }
}

// Initialize when document is ready
$(document).ready(() => {
    setTimeout(() => {
        window.exportManager = new EnhancedExportManager();
        console.log('Enhanced Export Manager initialized');
    }, 500);
});
