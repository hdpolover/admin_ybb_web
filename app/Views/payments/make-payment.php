<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Make Payment')); ?>
    <?= $this->include('partials/head-css') ?>
    <style>
        .payment-form {
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Payments', 'title' => 'Make Payment')); ?>

                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Payment Details</h5>
                                </div>
                                <div class="card-body">
                                    <form id="paymentForm" class="payment-form">
                                        <div class="mb-3">
                                            <label for="participantId" class="form-label">Participant</label>
                                            <select class="form-select" name="participant_id" id="participantId" required>
                                                <option value="">Select Participant</option>
                                                <?php foreach ($participants as $participant): ?>
                                                    <option value="<?= $participant->id ?>"><?= $participant->full_name ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text currency-symbol">Rp</span>
                                                <input type="text" class="form-control" id="amount" name="amount" required placeholder="Enter amount">
                                            </div>
                                            <small class="form-text text-muted">Enter the amount without commas or dots.</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="currency" class="form-label">Currency</label>
                                            <select class="form-select" name="currency" id="currency" required>
                                                <option value="IDR" selected>Indonesian Rupiah (IDR)</option>
                                                <option value="USD">US Dollar (USD)</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Payment Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter payment description"></textarea>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="termsCheck" required>
                                                <label class="form-check-label" for="termsCheck">
                                                    I agree to the terms and conditions
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-center mb-4">
                                            <button type="submit" class="btn btn-primary" id="payButton" disabled>
                                                <i class="ri-secure-payment-line align-bottom me-1"></i> Pay Now
                                            </button>
                                        </div>
                                        
                                        <div id="paymentStatus" class="alert d-none"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>

    <!-- Midtrans Payment Integration -->
    <script src="<?= base_url('assets/js/midtrans-payment.js') ?>"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Midtrans payment handler
            const midtrans = new MidtransPayment();
            
            // Initialize Midtrans when the page loads
            midtrans.initialize().catch(error => {
                console.error('Failed to initialize Midtrans:', error);
            });
            
            // Update currency symbol when currency changes
            document.getElementById('currency').addEventListener('change', function() {
                const currencySymbol = document.querySelector('.currency-symbol');
                currencySymbol.textContent = this.value === 'USD' ? '$' : 'Rp';
            });
            
            // Enable/disable payment button based on terms checkbox
            document.getElementById('termsCheck').addEventListener('change', function() {
                document.getElementById('payButton').disabled = !this.checked;
            });
            
            // Format amount input
            document.getElementById('amount').addEventListener('input', function() {
                // Remove non-digit characters
                let value = this.value.replace(/[^\d]/g, '');
                
                // Format with thousand separator if not empty
                if (value) {
                    this.value = new Intl.NumberFormat().format(parseInt(value));
                }
            });
            
            // Handle payment form submission
            document.getElementById('paymentForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const payButton = document.getElementById('payButton');
                const statusEl = document.getElementById('paymentStatus');
                
                // Show loading state
                payButton.disabled = true;
                payButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                statusEl.className = 'alert d-none';
                
                try {
                    // Get form data
                    const participantId = parseInt(document.getElementById('participantId').value);
                    const currency = document.getElementById('currency').value;
                    const description = document.getElementById('description').value;
                    
                    // Parse amount (remove formatting)
                    let amount = document.getElementById('amount').value;
                    amount = parseFloat(amount.replace(/[^\d]/g, ''));
                    
                    // Create payment data
                    const paymentData = {
                        participant_id: participantId,
                        amount: amount,
                        currency: currency,
                        description: description
                    };
                    
                    // Process payment
                    const result = await midtrans.pay(paymentData);
                    
                    // Handle success
                    statusEl.className = 'alert alert-success';
                    statusEl.textContent = 'Payment processed successfully! Transaction ID: ' + result.transaction.transaction_id;
                    
                    // Reset form
                    document.getElementById('paymentForm').reset();
                    
                } catch (error) {
                    // Handle error
                    statusEl.className = 'alert alert-danger';
                    
                    // Check if it's a user cancellation
                    if (error.status === 'closed') {
                        statusEl.textContent = 'Payment was cancelled. You can try again when you are ready.';
                    } else {
                        statusEl.textContent = 'Payment failed: ' + (error.message || 'Unknown error occurred');
                    }
                    
                    console.error('Payment error:', error);
                } finally {
                    // Reset button state
                    payButton.disabled = false;
                    payButton.innerHTML = '<i class="ri-secure-payment-line align-bottom me-1"></i> Pay Now';
                }
            });
        });
    </script>

    <!-- App js -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>

</html>