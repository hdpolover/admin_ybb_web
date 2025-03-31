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
            // Create transaction and get token
            const transaction = await this.createTransaction(paymentData);
            
            // Show payment dialog
            const result = await this.showPaymentDialog(transaction.token);
            
            return {
                transaction: transaction,
                result: result
            };
        } catch (error) {
            console.error('Payment failed:', error);
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
}

// Make available globally
window.MidtransPayment = MidtransPayment;