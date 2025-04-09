/**
 * Midtrans Payment Integration
 * Uses the official Midtrans Snap.js library
 */

class MidtransPayment {
    /**
     * Initialize with optional client key
     * @param {string|null} clientKey - Optional Midtrans client key
     */
    constructor(clientKey = null) {
        this.clientKey = clientKey;
        this.isInitialized = false;
        this.baseUrl = window.location.origin;
        this.apiUrl = `${this.baseUrl}/api/payments`;
    }

    /**
     * Initialize Midtrans Snap
     * @returns {Promise} Promise that resolves when initialization is complete
     */
    async initialize() {
        if (this.isInitialized) {
            return Promise.resolve();
        }

        try {
            // If no client key provided, get it from API
            if (!this.clientKey) {
                const config = await this.getConfig();
                this.clientKey = config.clientKey;
                this.isProduction = config.isProduction;
            }

            // Load Snap.js
            return new Promise((resolve, reject) => {
                const snapUrl = this.isProduction
                    ? 'https://app.midtrans.com/snap/snap.js'
                    : 'https://app.sandbox.midtrans.com/snap/snap.js';

                const script = document.createElement('script');
                script.src = snapUrl;
                script.setAttribute('data-client-key', this.clientKey);
                script.onload = () => {
                    this.isInitialized = true;
                    resolve();
                };
                script.onerror = (error) => {
                    reject('Failed to load Midtrans Snap: ' + error);
                };
                document.head.appendChild(script);
            });
        } catch (error) {
            console.error('Failed to initialize Midtrans:', error);
            throw error;
        }
    }

    /**
     * Get configuration from API
     * @returns {Promise<Object>} Promise that resolves with configuration data
     */
    async getConfig() {
        try {
            const response = await fetch(`${this.apiUrl}/config`);
            if (!response.ok) {
                throw new Error('Failed to fetch Midtrans configuration');
            }
            const data = await response.json();
            return {
                clientKey: data.data.clientKey,
                isProduction: data.data.isProduction
            };
        } catch (error) {
            console.error('Error fetching Midtrans config:', error);
            throw error;
        }
    }

    /**
     * Create a payment transaction
     * @param {Object} paymentData - Payment data object
     * @returns {Promise<Object>} Promise that resolves with transaction data
     */
    async createTransaction(paymentData) {
        try {
            const response = await fetch(`${this.apiUrl}/create`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(paymentData)
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.messages || 'Failed to create transaction');
            }

            const result = await response.json();
            return result.data;
        } catch (error) {
            console.error('Error creating transaction:', error);
            throw error;
        }
    }

    /**
     * Open Snap payment UI
     * @param {string} token - Snap token from createTransaction
     * @returns {Promise<Object>} Promise that resolves when payment is completed
     */
    async showPaymentDialog(token) {
        if (!this.isInitialized) {
            await this.initialize();
        }

        return new Promise((resolve, reject) => {
            window.snap.pay(token, {
                onSuccess: function(result) {
                    resolve({ status: 'success', data: result });
                },
                onPending: function(result) {
                    resolve({ status: 'pending', data: result });
                },
                onError: function(result) {
                    reject({ status: 'error', data: result });
                },
                onClose: function() {
                    reject({ status: 'closed', message: 'Payment dialog closed' });
                }
            });
        });
    }

    /**
     * Process payment end-to-end
     * @param {Object} paymentData - Payment data
     * @returns {Promise<Object>} Promise that resolves with payment result
     */
    async pay(paymentData) {
        try {
            // Check payment type
            if (paymentData.payment_type === 'manual') {
                // For manual payments, handle file upload
                return await this.processManualPayment(paymentData);
            } else {
                // For Midtrans payments, proceed with standard flow
                // Create transaction and get token
                const transaction = await this.createTransaction(paymentData);
                
                // Show payment dialog
                const result = await this.showPaymentDialog(transaction.token);
                
                return {
                    transaction: transaction,
                    result: result
                };
            }
        } catch (error) {
            console.error('Payment failed:', error);
            throw error;
        }
    }
      /**
     * Process manual payment with file upload
     * @param {Object} paymentData - Payment data including file
     * @returns {Promise<Object>} Promise that resolves with payment result
     */
    async processManualPayment(paymentData) {
        try {
            // Validate file if provided
            if (paymentData.payment_proof) {
                const file = paymentData.payment_proof;
                
                // Check file type
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
                if (!validTypes.includes(file.type)) {
                    throw new Error('Invalid file type. Only JPEG, PNG and PDF files are allowed.');
                }
                
                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    throw new Error('File size exceeds the 5MB limit.');
                }
            }
            
            // First create transaction without file to get payment ID
            const { payment_proof, payment_notes, ...paymentDataNoFile } = paymentData;
            
            // Create the initial transaction record
            const transaction = await this.createTransaction(paymentDataNoFile);
            
            // If there's a payment proof file, upload it
            if (payment_proof) {
                // Create form data for file upload
                const formData = new FormData();
                formData.append('payment_id', transaction.payment_id);
                formData.append('payment_proof', payment_proof);
                
                if (payment_notes) {
                    formData.append('notes', payment_notes);
                }
                
                // Upload proof
                const uploadResponse = await fetch(`${this.apiUrl}/upload-proof`, {
                    method: 'POST',
                    body: formData
                });
                
                if (!uploadResponse.ok) {
                    const error = await uploadResponse.json();
                    throw new Error(error.messages || 'Failed to upload payment proof');
                }
                
                const uploadResult = await uploadResponse.json();
                
                return {
                    transaction: transaction,
                    uploadResult: uploadResult,
                    result: { status: 'pending', message: 'Manual payment submitted successfully' },
                    bankDetails: transaction.bank_details || []
                };
            }
            
            // Return transaction details even without file upload
            return {
                transaction: transaction,
                result: { status: 'pending', message: 'Manual payment record created' },
                bankDetails: transaction.bank_details || []
            };
        } catch (error) {
            console.error('Manual payment processing failed:', error);
            throw error;
        }
    }

    /**
     * Check payment status
     * @param {number} paymentId - Payment ID
     * @returns {Promise<Object>} Promise that resolves with payment status
     */
    async checkStatus(paymentId) {
        try {
            const response = await fetch(`${this.apiUrl}/status/${paymentId}`);
            if (!response.ok) {
                throw new Error('Failed to check payment status');
            }
            const data = await response.json();
            return data.data;
        } catch (error) {
            console.error('Error checking payment status:', error);
            throw error;
        }
    }

    /**
     * Check status of manual payment specifically
     * @param {number} paymentId - Payment ID 
     * @returns {Promise<Object>} Promise with detailed manual payment status
     */
    async checkManualPaymentStatus(paymentId) {
        const status = await this.checkStatus(paymentId);
        
        // Add more specific information for manual payments
        if (status && status.payment_method_id === 2) { // Manual payment type
            const statusMap = {
                0: 'Created',
                1: 'Pending Review',
                2: 'Approved',
                3: 'Cancelled',
                4: 'Rejected'
            };
            
            return {
                ...status,
                statusText: statusMap[status.status_code] || 'Unknown',
                isManual: true,
                needsAction: status.status_code === 4, // Rejected payments need action
                canUploadProof: [0, 1, 4].includes(status.status_code) // Can upload proof if created, pending or rejected
            };
        }
        
        return status;
    }

    /**
     * Reupload payment proof for existing payment
     * Useful for rejected payments that need new proof
     * 
     * @param {number} paymentId - The ID of the payment
     * @param {File} paymentProof - The payment proof file
     * @param {string} notes - Optional notes to include
     * @returns {Promise<Object>} Promise with upload result
     */
    async reuploadPaymentProof(paymentId, paymentProof, notes = '') {
        try {
            // Validate file
            if (!paymentProof) {
                throw new Error('Payment proof file is required');
            }
            
            // Check file type
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
            if (!validTypes.includes(paymentProof.type)) {
                throw new Error('Invalid file type. Only JPEG, PNG and PDF files are allowed.');
            }
            
            // Check file size (max 5MB)
            if (paymentProof.size > 5 * 1024 * 1024) {
                throw new Error('File size exceeds the 5MB limit.');
            }
            
            // Create form data for file upload
            const formData = new FormData();
            formData.append('payment_id', paymentId);
            formData.append('payment_proof', paymentProof);
            
            if (notes) {
                formData.append('notes', notes);
            }
            
            // Upload proof
            const response = await fetch(`${this.apiUrl}/upload-proof`, {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.messages || 'Failed to upload payment proof');
            }
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Error reuploading payment proof:', error);
            throw error;
        }
    }
}

// Make available globally
window.MidtransPayment = MidtransPayment;