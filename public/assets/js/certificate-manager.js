/**
 * Certificate Manager for YBB Admin Panel
 * Integrates with Python-based certificate generation service
 */
class CertificateManager {
    constructor() {
        this.baseUrl = '/api/certificates';
        this.isLoading = false;
    }
    
    /**
     * Generate certificate for participant and award
     */
    async generateCertificate(participantId, awardId) {
        try {
            this.setLoading(true);
            
            const response = await fetch(`${this.baseUrl}/generate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    participant_id: participantId,
                    award_id: awardId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Handle successful generation
                this.downloadCertificate(result.data);
                this.showSuccessMessage(`Certificate generated successfully for ${result.data.participant_name}`);
                return result;
            } else {
                throw new Error(result.message || 'Certificate generation failed');
            }
            
        } catch (error) {
            console.error('Certificate generation error:', error);
            this.showErrorMessage('Certificate generation failed: ' + error.message);
            throw error;
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Regenerate existing certificate
     */
    async regenerateCertificate(certificateId) {
        try {
            this.setLoading(true);
            
            const response = await fetch(`${this.baseUrl}/${certificateId}/regenerate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.downloadCertificate(result.data);
                this.showSuccessMessage(`Certificate regenerated successfully for ${result.data.participant_name}`);
                return result;
            } else {
                throw new Error(result.message || 'Certificate regeneration failed');
            }
            
        } catch (error) {
            console.error('Certificate regeneration error:', error);
            this.showErrorMessage('Certificate regeneration failed: ' + error.message);
            throw error;
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Download certificate as PDF
     */
    downloadCertificate(certificateData) {
        try {
            // Convert base64 to blob and download
            const binaryString = atob(certificateData.file_data);
            const bytes = new Uint8Array(binaryString.length);
            
            for (let i = 0; i < binaryString.length; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            
            const blob = new Blob([bytes], { type: 'application/pdf' });
            const url = URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = certificateData.file_name;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            console.log('Certificate downloaded:', certificateData.file_name);
        } catch (error) {
            console.error('Download error:', error);
            this.showErrorMessage('Failed to download certificate');
        }
    }
    
    /**
     * Get participant certificates
     */
    async getParticipantCertificates(participantId) {
        try {
            const response = await fetch(`${this.baseUrl}/participant/${participantId}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.message || 'Failed to get participant certificates');
            }
        } catch (error) {
            console.error('Error getting participant certificates:', error);
            throw error;
        }
    }
    
    /**
     * Get program certificates
     */
    async getProgramCertificates(programId) {
        try {
            const response = await fetch(`${this.baseUrl}/program/${programId}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.message || 'Failed to get program certificates');
            }
        } catch (error) {
            console.error('Error getting program certificates:', error);
            throw error;
        }
    }
    
    /**
     * Check certificate service health
     */
    async checkHealth() {
        try {
            const response = await fetch(`${this.baseUrl}/health`);
            const result = await response.json();
            return result;
        } catch (error) {
            return {
                service: 'Certificate Generation Service',
                status: 'error',
                error: error.message
            };
        }
    }
    
    /**
     * Get available placeholders
     */
    async getPlaceholders() {
        try {
            const response = await fetch(`${this.baseUrl}/placeholders`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.message || 'Failed to get placeholders');
            }
        } catch (error) {
            console.error('Error getting placeholders:', error);
            throw error;
        }
    }
    
    /**
     * Revoke certificate
     */
    async revokeCertificate(certificateId) {
        try {
            const response = await fetch(`${this.baseUrl}/${certificateId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccessMessage('Certificate revoked successfully');
                return result;
            } else {
                throw new Error(result.message || 'Failed to revoke certificate');
            }
        } catch (error) {
            console.error('Error revoking certificate:', error);
            this.showErrorMessage('Failed to revoke certificate: ' + error.message);
            throw error;
        }
    }
    
    /**
     * Set loading state
     */
    setLoading(loading) {
        this.isLoading = loading;
        
        // Update UI loading indicators
        const buttons = document.querySelectorAll('.certificate-generate-btn, .certificate-regenerate-btn');
        buttons.forEach(btn => {
            if (loading) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            } else {
                btn.disabled = false;
                btn.innerHTML = btn.getAttribute('data-original-text') || 'Generate Certificate';
            }
        });
    }
    
    /**
     * Show success message
     */
    showSuccessMessage(message) {
        // Implementation depends on your notification system
        if (typeof Swal !== 'undefined') {
            Swal.fire('Success', message, 'success');
        } else if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            alert(message);
        }
    }
    
    /**
     * Show error message
     */
    showErrorMessage(message) {
        // Implementation depends on your notification system
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', message, 'error');
        } else if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }
    
    /**
     * Initialize certificate buttons on page
     */
    initializeCertificateButtons() {
        // Generate certificate buttons
        document.querySelectorAll('.certificate-generate-btn').forEach(btn => {
            btn.setAttribute('data-original-text', btn.innerHTML);
            
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                
                const participantId = btn.getAttribute('data-participant-id');
                const awardId = btn.getAttribute('data-award-id');
                
                if (participantId && awardId) {
                    await this.generateCertificate(participantId, awardId);
                } else {
                    this.showErrorMessage('Missing participant ID or award ID');
                }
            });
        });
        
        // Regenerate certificate buttons
        document.querySelectorAll('.certificate-regenerate-btn').forEach(btn => {
            btn.setAttribute('data-original-text', btn.innerHTML);
            
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                
                const certificateId = btn.getAttribute('data-certificate-id');
                
                if (certificateId) {
                    await this.regenerateCertificate(certificateId);
                } else {
                    this.showErrorMessage('Missing certificate ID');
                }
            });
        });
        
        // Revoke certificate buttons
        document.querySelectorAll('.certificate-revoke-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                
                const certificateId = btn.getAttribute('data-certificate-id');
                
                if (certificateId) {
                    if (confirm('Are you sure you want to revoke this certificate?')) {
                        await this.revokeCertificate(certificateId);
                        // Refresh the page or update the UI
                        location.reload();
                    }
                } else {
                    this.showErrorMessage('Missing certificate ID');
                }
            });
        });
    }
}

// Initialize certificate manager globally
window.certificateManager = new CertificateManager();

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.certificateManager.initializeCertificateButtons();
    
    // Check service health on load
    window.certificateManager.checkHealth().then(health => {
        console.log('Certificate service health:', health);
    });
});

// Export functions for use in inline scripts
window.generateCertificate = function(participantId, awardId) {
    return window.certificateManager.generateCertificate(participantId, awardId);
};

window.regenerateCertificate = function(certificateId) {
    return window.certificateManager.regenerateCertificate(certificateId);
};

window.revokeCertificate = function(certificateId) {
    return window.certificateManager.revokeCertificate(certificateId);
};
