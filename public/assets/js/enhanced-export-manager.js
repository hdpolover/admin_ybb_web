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
        this.intervalId = null;
        this.activeIntervals = [];
        // YBB Export Service base URL
        this.ybbApiBaseUrl = 'https://ybb-data-management-service-production.up.railway.app';
        this.init();
    }

    init() {
        console.log('Initializing Enhanced Export Manager...');
        this.attachExportHandlers();
        this.clearAnyExistingIntervals();
    }

    clearAnyExistingIntervals() {
        console.log('Force stopping all active intervals:', this.activeIntervals);
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
        // Use event delegation to handle dynamically added export buttons
        $(document).off('click.enhancedExport').on('click.enhancedExport', '.export-btn', (e) => {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            
            if (this.isProcessing) {
                console.warn('Export already in progress, ignoring click');
                return;
            }

            console.log('Export button clicked, processing...');
            this.handleExportRequest($btn);
        });
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
            const exportUrl = this.getFullApiUrl($btn.data('url') || '/api/ybb/export/participants');
            $.ajax({
                url: exportUrl,
                method: 'POST',
                data: JSON.stringify(ybbData),
                contentType: 'application/json',
                dataType: 'json',
                timeout: 30000,
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

        if (response.success) {
            // Store export information
            this.currentExports.set(response.exportId, response);

            // Check if we have an export ID for status polling
            const exportId = response.data?.export_id || response.exportId;
            const downloadUrl = response.data?.download_url || response.downloadUrl;
            
            console.log('Determined exportId:', exportId);
            console.log('Determined downloadUrl:', downloadUrl);
            
            // Check if export is already completed (YBB API returns status: 'success' when done)
            const isCompleted = response.status === 'success' && !!(downloadUrl);
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
                processingTimer.stop(response.processingTime || response.data?.processing_time);
                
                // Use the centralized success notification method
                this.showMainSuccessNotification(response, processingTimer);
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
        
        const SwalInstance = window.Swal || window.swal;
        const exportId = response.exportId || response.export_id;
        
        // Use helper method for robust data extraction
        const responseData = response.data || {};
        const metadata = response.metadata || {};
        
        const recordCount = this.extractValue(response, responseData, metadata, 
            ['record_count', 'recordCount', 'totalRecords'], 0);
        
        const fileSize = this.extractValue(response, responseData, metadata, 
            ['file_size', 'fileSize', 'file_size_bytes', 'size']);
        
        const fileName = this.extractValue(response, responseData, metadata, 
            ['file_name', 'fileName', 'filename']) || 'Export_File.xlsx';
        
        const exportType = this.extractValue(response, responseData, metadata, 
            ['exportType', 'export_type', 'type']) || this.guessExportTypeFromContext() || 'Data';
        
        const downloadUrl = this.getDownloadUrl(response, exportId);
        
        // Format file size
        const fileSizeFormatted = fileSize ? this.formatFileSize(fileSize) : 'Unknown size';
        
        console.log('Main success notification data:', {
            recordCount, fileName, fileSizeFormatted, exportType, downloadUrl
        });
        
        if (SwalInstance && typeof SwalInstance.fire === 'function') {
            try {
                const htmlContent = `
                    <div class="export-success-details">
                        <div class="row mb-3">
                            <div class="col-4">
                                <div class="stat-item text-center p-3 bg-light rounded">
                                    <div class="stat-number text-primary fw-bold fs-4">${recordCount.toLocaleString()}</div>
                                    <div class="stat-label text-muted small">Records</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item text-center p-3 bg-light rounded">
                                    <div class="stat-number text-success fw-bold fs-4">${fileSizeFormatted}</div>
                                    <div class="stat-label text-muted small">File Size</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item text-center p-3 bg-light rounded">
                                    <div class="stat-number text-info fw-bold fs-4">${exportType}</div>
                                    <div class="stat-label text-muted small">Export Type</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <div class="text-muted">
                                <i class="fas fa-file-excel text-success"></i>
                                <strong>${fileName}</strong>
                            </div>
                        </div>
                    </div>
                `;
                
                console.log('🎯 About to show SUCCESS SweetAlert with data:', {
                    recordCount, fileSize, fileName, exportType, downloadUrl
                });
                
                SwalInstance.fire({
                    html: htmlContent,
                    title: '🎉 Export Complete!',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-download"></i> Download Now',
                    cancelButtonText: '<i class="fas fa-eye"></i> View Details',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    width: 650,
                    customClass: {
                        popup: 'export-success-popup',
                        title: 'export-success-title',
                        htmlContainer: 'export-success-content'
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
                // Fallback to simple alert
                if (confirm(`Export Complete!\n\nSuccessfully exported ${recordCount.toLocaleString()} records.\n\nClick OK to download now, or Cancel to view details.`)) {
                    console.log('Error fallback download - Using URL:', downloadUrl);
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
            
            const statusUrl = this.getFullApiUrl(`/api/ybb/export/${exportId}/status`);
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
        
        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        } else if (xhr.responseText) {
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || response.error || errorMessage;
            } catch (e) {
                errorMessage = xhr.responseText;
            }
        }
        
        this.showErrorMessage(errorMessage);
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

    getDownloadUrl(response, exportId) {
        const possibleUrls = [
            response.downloadUrl,
            response.download_url,
            response.data?.download_url,
            response.data?.downloadUrl
        ];

        for (const url of possibleUrls) {
            if (url && typeof url === 'string' && url.trim()) {
                return url;
            }
        }

        return this.getFullApiUrl(`/api/ybb/export/${exportId}/download`);
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
}

// Initialize when document is ready
$(document).ready(() => {
    setTimeout(() => {
        window.exportManager = new EnhancedExportManager();
        console.log('Enhanced Export Manager initialized');
    }, 500);
});
