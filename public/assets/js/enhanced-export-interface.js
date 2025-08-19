/**
 * Enhanced Export Interface
 * 
 * Integrates with the performance display module to provide
 * a comprehensive export experience with real-time feedback
 */

class EnhancedExportInterface {
    constructor() {
        this.isExporting = false;
        this.currentExportId = null;
        this.progressInterval = null;
        this.init();
    }
    
    init() {
        this.setupExportHandlers();
        this.enhanceExportForm();
    }
    
    setupExportHandlers() {
        // Intercept existing export form submissions
        const exportForms = document.querySelectorAll('form[action*="export"], form[id*="export"], .export-form');
        
        exportForms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleExportSubmission(form);
            });
        });
        
        // Handle export buttons
        const exportButtons = document.querySelectorAll('[onclick*="export"], .export-btn, .btn-export');
        
        exportButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleExportButtonClick(button);
            });
        });
    }
    
    enhanceExportForm() {
        // Add export strategy selection to forms
        const exportForms = document.querySelectorAll('form[action*="export"], .export-form');
        
        exportForms.forEach(form => {
            if (!form.querySelector('.export-strategy-selection')) {
                this.addExportStrategySelection(form);
            }
        });
    }
    
    addExportStrategySelection(form) {
        const strategyHtml = `
            <div class="export-strategy-selection card mt-3" style="background: #f8f9fa;">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs"></i> Export Options
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Export Strategy:</label>
                            <select name="export_strategy" class="form-select export-strategy-select">
                                <option value="auto">Auto (Recommended)</option>
                                <option value="single">Single File</option>
                                <option value="chunked">Multiple Files (Chunked)</option>
                            </select>
                            <small class="text-muted">Auto selects the best strategy based on data size</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Performance Tracking:</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="track_performance" checked>
                                <label class="form-check-label">
                                    Enable detailed performance metrics
                                </label>
                            </div>
                            <small class="text-muted">Provides detailed export statistics</small>
                        </div>
                    </div>
                    
                    <div class="export-preview mt-3" style="display: none;">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i>
                            <span class="preview-text">Estimating export requirements...</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Insert before submit button or at end of form
        const submitButton = form.querySelector('[type="submit"], .btn-primary');
        if (submitButton) {
            submitButton.insertAdjacentHTML('beforebegin', strategyHtml);
        } else {
            form.insertAdjacentHTML('beforeend', strategyHtml);
        }
        
        // Add event listeners for strategy selection
        const strategySelect = form.querySelector('.export-strategy-select');
        if (strategySelect) {
            strategySelect.addEventListener('change', () => {
                this.updateExportPreview(form);
            });
        }
    }
    
    updateExportPreview(form) {
        const preview = form.querySelector('.export-preview');
        const strategySelect = form.querySelector('.export-strategy-select');
        
        if (!preview || !strategySelect) return;
        
        const strategy = strategySelect.value;
        const previewText = preview.querySelector('.preview-text');
        
        let message = '';
        switch (strategy) {
            case 'auto':
                message = 'System will automatically choose the best export method based on your data size and system performance.';
                break;
            case 'single':
                message = 'All data will be exported to a single Excel file. Recommended for smaller datasets (<10,000 records).';
                break;
            case 'chunked':
                message = 'Data will be split into multiple files for better performance. Recommended for large datasets.';
                break;
        }
        
        previewText.textContent = message;
        preview.style.display = 'block';
        
        // Add fade-in effect
        preview.style.opacity = '0';
        setTimeout(() => {
            preview.style.opacity = '1';
        }, 100);
    }
    
    async handleExportSubmission(form) {
        if (this.isExporting) {
            this.showMessage('An export is already in progress. Please wait...', 'warning');
            return;
        }
        
        this.isExporting = true;
        this.disableExportControls();
        
        try {
            // Show initial progress
            this.showExportProgress();
            
            // Collect form data
            const formData = new FormData(form);
            
            // Add performance tracking flag
            if (!formData.has('track_performance')) {
                formData.append('track_performance', 'true');
            }
            
            // Get the form action URL
            const actionUrl = form.action || form.getAttribute('action') || window.location.href;
            
            // Submit export request
            const response = await fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.currentExportId = result.exportId;
                
                // Start progress monitoring
                this.startProgressMonitoring();
                
                // Dispatch completion event
                document.dispatchEvent(new CustomEvent('exportCompleted', {
                    detail: result
                }));
                
                this.showMessage('Export completed successfully!', 'success');
            } else {
                throw new Error(result.message || 'Export failed');
            }
            
        } catch (error) {
            console.error('Export error:', error);
            this.showMessage(`Export failed: ${error.message}`, 'error');
            exportPerformance.hide();
        } finally {
            this.isExporting = false;
            this.enableExportControls();
            this.stopProgressMonitoring();
        }
    }
    
    async handleExportButtonClick(button) {
        // For buttons that don't have forms, create a synthetic form submission
        const exportParams = this.extractExportParams(button);
        
        if (exportParams) {
            const syntheticForm = document.createElement('form');
            syntheticForm.style.display = 'none';
            syntheticForm.action = exportParams.url;
            
            // Add parameters as hidden inputs
            Object.entries(exportParams.data).forEach(([key, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                syntheticForm.appendChild(input);
            });
            
            // Add performance tracking
            const perfInput = document.createElement('input');
            perfInput.type = 'hidden';
            perfInput.name = 'track_performance';
            perfInput.value = 'true';
            syntheticForm.appendChild(perfInput);
            
            document.body.appendChild(syntheticForm);
            
            await this.handleExportSubmission(syntheticForm);
            
            document.body.removeChild(syntheticForm);
        }
    }
    
    extractExportParams(button) {
        // Try to extract export parameters from button attributes
        const onclick = button.getAttribute('onclick');
        const href = button.getAttribute('href');
        const dataUrl = button.getAttribute('data-url');
        
        if (onclick) {
            // Parse onclick for export parameters
            const matches = onclick.match(/export\s*\(\s*['"]([^'"]*)['"]/);
            if (matches) {
                return {
                    url: matches[1],
                    data: this.parseUrlParams(matches[1])
                };
            }
        }
        
        if (href && href !== '#') {
            return {
                url: href,
                data: this.parseUrlParams(href)
            };
        }
        
        if (dataUrl) {
            return {
                url: dataUrl,
                data: this.parseUrlParams(dataUrl)
            };
        }
        
        return null;
    }
    
    parseUrlParams(url) {
        const params = {};
        const urlObj = new URL(url, window.location.origin);
        
        urlObj.searchParams.forEach((value, key) => {
            params[key] = value;
        });
        
        return params;
    }
    
    showExportProgress() {
        document.dispatchEvent(new CustomEvent('exportProgress', {
            detail: {
                status: 'processing',
                percentage: 0,
                message: 'Initializing export...'
            }
        }));
    }
    
    startProgressMonitoring() {
        let progress = 0;
        const messages = [
            'Preparing data...',
            'Processing records...',
            'Generating Excel files...',
            'Optimizing file size...',
            'Finalizing export...'
        ];
        
        this.progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += Math.random() * 15;
                const messageIndex = Math.floor((progress / 100) * messages.length);
                
                document.dispatchEvent(new CustomEvent('exportProgress', {
                    detail: {
                        status: 'processing',
                        percentage: Math.min(Math.round(progress), 90),
                        message: messages[messageIndex] || messages[messages.length - 1]
                    }
                }));
            }
        }, 1000);
    }
    
    stopProgressMonitoring() {
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }
        
        // Final progress update
        document.dispatchEvent(new CustomEvent('exportProgress', {
            detail: {
                status: 'completed',
                percentage: 100,
                message: 'Export completed!'
            }
        }));
    }
    
    disableExportControls() {
        const controls = document.querySelectorAll('.export-btn, .btn-export, [onclick*="export"], .export-strategy-select');
        controls.forEach(control => {
            control.disabled = true;
            if (control.classList.contains('btn')) {
                control.classList.add('disabled');
            }
        });
    }
    
    enableExportControls() {
        const controls = document.querySelectorAll('.export-btn, .btn-export, [onclick*="export"], .export-strategy-select');
        controls.forEach(control => {
            control.disabled = false;
            if (control.classList.contains('btn')) {
                control.classList.remove('disabled');
            }
        });
    }
    
    showMessage(message, type) {
        // Create or update message display
        let messageContainer = document.getElementById('export-message-container');
        
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.id = 'export-message-container';
            messageContainer.style.position = 'fixed';
            messageContainer.style.top = '20px';
            messageContainer.style.right = '20px';
            messageContainer.style.zIndex = '9999';
            document.body.appendChild(messageContainer);
        }
        
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        const html = `
            <div class="alert ${alertClass} alert-dismissible fade show export-message" role="alert">
                <i class="fas fa-${this.getMessageIcon(type)}"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        messageContainer.innerHTML = html;
        
        // Auto-dismiss after 5 seconds for success messages
        if (type === 'success') {
            setTimeout(() => {
                const alert = messageContainer.querySelector('.alert');
                if (alert) {
                    alert.classList.remove('show');
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 300);
                }
            }, 5000);
        }
    }
    
    getMessageIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// Initialize the enhanced export interface
document.addEventListener('DOMContentLoaded', function() {
    const enhancedExport = new EnhancedExportInterface();
    
    // Make it globally available
    window.enhancedExport = enhancedExport;
});
