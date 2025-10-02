<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="text-center mb-4"><?= $pageTitle ?></h1>
                
                <!-- Flash Messages -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- OAuth Status Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">OAuth Token Status</h5>
                    </div>
                    <div class="card-body">
                        <form id="oauthStatusForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="noreply@ybbfoundation.com">
                            </div>
                            <button type="submit" class="btn btn-primary">Check OAuth Status</button>
                        </form>
                        
                        <div id="oauthStatus" class="mt-3" style="display: none;">
                            <!-- Status will be displayed here -->
                        </div>
                        
                        <div class="mt-3">
                            <a href="<?= $baseUrl ?>auth/google/login" class="btn btn-success me-2">
                                <i class="fab fa-google"></i> Start OAuth Consent Flow
                            </a>
                            <button type="button" class="btn btn-warning" id="revokeOAuthBtn">
                                <i class="fas fa-ban"></i> Revoke OAuth Token
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Email Test Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Test Email Sending</h5>
                    </div>
                    <div class="card-body">
                        <form id="emailTestForm">
                            <div class="mb-3">
                                <label for="fromEmail" class="form-label">From Email</label>
                                <input type="email" class="form-control" id="fromEmail" name="fromEmail" value="noreply@ybbfoundation.com" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="toEmail" class="form-label">To Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="toEmail" name="toEmail" required placeholder="recipient@example.com">
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" value="OAuth Test Email">
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="4"><h1>Test Email</h1><p>This email was sent using OAuth2 via Gmail API from YBB Admin System.</p><p>Timestamp: <?= date('Y-m-d H:i:s') ?></p></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Send Test Email</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Check OAuth status
            $('#oauthStatusForm').on('submit', function(e) {
                e.preventDefault();
                
                const email = $('#email').val();
                
                $.ajax({
                    url: '<?= $baseUrl ?>email-test/check-oauth',
                    method: 'POST',
                    data: { email: email },
                    success: function(response) {
                        if (response.success) {
                            let statusHtml = '';
                            
                            if (response.hasToken) {
                                const statusClass = response.isExpired ? 'text-danger' : 'text-success';
                                const statusText = response.isExpired ? 'Expired' : 'Valid';
                                
                                statusHtml = `
                                    <div class="alert alert-info">
                                        <h6>OAuth Token Found</h6>
                                        <p><strong>Email:</strong> ${response.email}</p>
                                        <p><strong>Status:</strong> <span class="${statusClass}">${statusText}</span></p>
                                        <p><strong>Expires:</strong> ${response.expires_at}</p>
                                        <p><strong>Scope:</strong> ${response.scope || 'Not specified'}</p>
                                    </div>
                                `;
                            } else {
                                statusHtml = `
                                    <div class="alert alert-warning">
                                        <h6>No OAuth Token</h6>
                                        <p>${response.message}</p>
                                        <p>Please complete the OAuth consent flow using the button above.</p>
                                    </div>
                                `;
                            }
                            
                            $('#oauthStatus').html(statusHtml).show();
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to check OAuth status', 'error');
                    }
                });
            });
            
            // Send test email
            $('#emailTestForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = {
                    fromEmail: $('#fromEmail').val(),
                    toEmail: $('#toEmail').val(),
                    subject: $('#subject').val(),
                    message: $('#message').val()
                };
                
                $.ajax({
                    url: '<?= $baseUrl ?>email-test/test-email',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                html: `
                                    <p><strong>From:</strong> ${response.from}</p>
                                    <p><strong>To:</strong> ${response.to}</p>
                                    <p><strong>Subject:</strong> ${response.subject}</p>
                                `
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to send email', 'error');
                    }
                });
            });
            
            // Revoke OAuth token
            $('#revokeOAuthBtn').on('click', function() {
                const email = $('#email').val();
                
                Swal.fire({
                    title: 'Revoke OAuth Token?',
                    text: `This will remove the OAuth token for ${email}. You'll need to re-authorize to use Gmail API.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, revoke it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '<?= $baseUrl ?>auth/google/revoke',
                            method: 'POST',
                            data: { email: email },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Revoked!', response.message, 'success');
                                    // Refresh OAuth status
                                    $('#oauthStatusForm').submit();
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to revoke OAuth token', 'error');
                            }
                        });
                    }
                });
            });
            
            // Auto-check OAuth status on page load
            $('#oauthStatusForm').submit();
        });
    </script>
</body>
</html>