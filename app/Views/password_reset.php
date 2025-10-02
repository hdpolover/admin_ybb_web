<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => $pageTitle ?? 'Password Reset')); ?>
    <?= $this->include('partials/head-css') ?>
</head>
<body>
    <div class="auth-page-wrapper pt-5">
        <!-- auth page bg -->
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>
            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <!-- auth page content -->
        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div>
                                <h3 class="text-white fw-bold mb-0">YBB Foundation</h3>
                            </div>
                            <p class="mt-3 fs-15 fw-medium">Official Password Reset Service</p>
                        </div>
                    </div>
                </div>
                <!-- end row -->

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4">
                            <div class="card-body p-4">
                                
                                <?php if (isset($programData) && $programData): ?>
                                    <div class="text-center mb-3">
                                        <div class="badge bg-primary-subtle text-primary fs-13 mb-3">
                                            <i class="ri-calendar-check-line me-1"></i>
                                            <?= esc($programData->name ?? 'Program') ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (session()->getFlashdata('error')): ?>
                                    <div class="alert alert-borderless alert-danger" role="alert">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0 me-2">
                                                <i class="ri-error-warning-line fs-16"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <?= session()->getFlashdata('error') ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($error) && $error): ?>
                                    <div class="alert alert-borderless alert-danger" role="alert">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0 me-2">
                                                <i class="ri-error-warning-line fs-16"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <?= $error ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($success) && $success): ?>
                                    <div class="text-center mt-2 mb-4">
                                        <lord-icon src="https://cdn.lordicon.com/lupuorrc.json" trigger="loop" colors="primary:#0ab39c" class="avatar-xl"></lord-icon>
                                        <h5 class="text-success mt-3">Password Reset Successful!</h5>
                                        <p class="text-muted"><?= $success ?></p>
                                    </div>
                                    
                                    <div class="text-center">
                                        <?php if (isset($programData) && !empty($programData->web_url)): ?>
                                            <a href="https://<?= esc($programData->web_url) ?>" class="btn btn-success me-2" target="_blank">
                                                <i class="ri-external-link-line me-1"></i>
                                                Go to <?= esc($programData->name ?? 'Program') ?> Website
                                            </a>
                                            <a href="<?= base_url() ?>" class="btn btn-outline-primary">
                                                <i class="ri-login-box-line me-1"></i>
                                                Admin Login
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url() ?>" class="btn btn-success">
                                                <i class="ri-login-box-line me-1"></i>
                                                Go to Login
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($showForm) && $showForm): ?>
                                    <div class="text-center mt-2">
                                        <lord-icon src="https://cdn.lordicon.com/rhvddzym.json" trigger="loop" colors="primary:#0ab39c" class="avatar-xl"></lord-icon>
                                        <h5 class="text-primary mt-3">Reset Your Password</h5>
                                        <p class="text-muted mb-4">Enter your new password below</p>
                                    </div>

                                    <div class="p-2 mt-4">
                                        <form method="POST" action="<?= base_url('reset-password') ?>">
                                            <input type="hidden" name="token" value="<?= esc($token) ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Email Account</label>
                                                <input type="email" class="form-control" value="<?= esc($email) ?>" disabled>
                                                <small class="form-text text-muted">Resetting password for this account</small>
                                            </div>

                                            <div class="mb-3">
                                                <label for="password" class="form-label">New Password</label>
                                                <div class="position-relative auth-pass-inputgroup">
                                                    <input type="password" class="form-control pe-5 password-input" 
                                                           id="password" name="password" required minlength="8" 
                                                           placeholder="Enter new password">
                                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" 
                                                            type="button" id="password-addon">
                                                        <i class="ri-eye-fill align-middle"></i>
                                                    </button>
                                                </div>
                                                <small class="form-text text-muted">Must be at least 8 characters long</small>
                                            </div>

                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                                <div class="position-relative auth-pass-inputgroup">
                                                    <input type="password" class="form-control pe-5 password-input-confirm" 
                                                           id="confirm_password" name="confirm_password" required minlength="8" 
                                                           placeholder="Confirm your password">
                                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon-confirm" 
                                                            type="button" id="password-addon-confirm">
                                                        <i class="ri-eye-fill align-middle"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <button class="btn btn-success w-100" type="submit">
                                                    <i class="ri-key-2-line me-1"></i>Reset Password
                                                </button>
                                            </div>
                                        </form>

                                        <?php if (isset($programData) && $programData): ?>
                                            <div class="alert alert-borderless alert-info mt-4 mb-3" role="alert">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-shrink-0 me-2">
                                                        <i class="ri-information-line fs-16"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <strong>Program Information</strong><br>
                                                        <small>This account is for <strong><?= esc($programData->name ?? 'Program') ?></strong>, organized by YBB Foundation.</small>
                                                        <?php if (!empty($programData->web_url)): ?>
                                                            <div class="mt-2">
                                                                <a href="https://<?= esc($programData->web_url) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                                                    <i class="ri-external-link-line me-1"></i>
                                                                    Visit Program Website
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="alert alert-borderless alert-warning mb-3" role="alert">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-shrink-0 me-2">
                                                        <i class="ri-shield-check-line fs-16"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <strong>Security Notice</strong><br>
                                                        <small>This is the official YBB Foundation password reset service. This secure platform manages accounts for all YBB Foundation programs.</small>
                                                        <div class="d-flex gap-3 mt-2">
                                                            <small class="text-success"><i class="ri-lock-2-line me-1"></i>SSL Secured</small>
                                                            <small class="text-success"><i class="ri-verified-badge-line me-1"></i>Official Service</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!isset($showForm) || !$showForm): ?>
                                    <?php if (!isset($success)): ?>
                                        <div class="text-center mt-2">
                                            <lord-icon src="https://cdn.lordicon.com/wloilxuq.json" trigger="loop" colors="primary:#f7b84b" class="avatar-xl"></lord-icon>
                                            <h5 class="text-warning mt-3"><?= isset($error) ? 'Reset Link Invalid' : 'Reset Link Missing' ?></h5>
                                            <p class="text-muted mb-4">Need a new reset link? Visit the program website to request one.</p>
                                        </div>
                                        
                                        <div class="text-center">
                                            <?php if (isset($programData) && !empty($programData->web_url)): ?>
                                                <a href="https://<?= esc($programData->web_url) ?>" class="btn btn-primary" target="_blank">
                                                    <i class="ri-external-link-line me-1"></i>
                                                    Go to <?= esc($programData->name ?? 'Program') ?> Website
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= base_url() ?>" class="btn btn-primary">
                                                    <i class="ri-arrow-left-line me-1"></i>
                                                    Back to Login
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->

                        <div class="mt-4 text-center">
                            <p class="mb-0 text-white-50">
                                Remember your password? 
                                <?php if (isset($programData) && !empty($programData->web_url)): ?>
                                    <a href="https://<?= esc($programData->web_url) ?>" class="fw-semibold text-white text-decoration-underline" target="_blank">
                                        Go to <?= esc($programData->name ?? 'Program') ?> Website
                                    </a>
                                <?php else: ?>
                                    <a href="<?= base_url() ?>" class="fw-semibold text-white text-decoration-underline">
                                        Sign In
                                    </a>
                                <?php endif; ?>
                            </p>
                        </div>

                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">&copy;
                                <script>document.write(new Date().getFullYear())</script> 
                                YBB Foundation. Official Password Reset Service
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- particles js -->
    <script src="/assets/libs/particles.js/particles.js"></script>
    <!-- particles app js -->
    <script src="/assets/js/pages/particles.app.js"></script>
    <!-- password-addon init -->
    <script src="/assets/js/pages/password-addon.init.js"></script>

    <script>
        // Client-side password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePassword() {
                if (password && confirmPassword) {
                    if (password.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity("Passwords don't match");
                    } else {
                        confirmPassword.setCustomValidity('');
                    }
                }
            }
            
            if (password && confirmPassword) {
                password.addEventListener('change', validatePassword);
                confirmPassword.addEventListener('keyup', validatePassword);
            }

            // Additional password toggle for confirm field
            const confirmToggle = document.getElementById('password-addon-confirm');
            const confirmInput = document.querySelector('.password-input-confirm');
            
            if (confirmToggle && confirmInput) {
                confirmToggle.addEventListener('click', function() {
                    const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmInput.setAttribute('type', type);
                    
                    const icon = confirmToggle.querySelector('i');
                    if (type === 'text') {
                        icon.classList.remove('ri-eye-fill');
                        icon.classList.add('ri-eye-off-fill');
                    } else {
                        icon.classList.remove('ri-eye-off-fill');
                        icon.classList.add('ri-eye-fill');
                    }
                });
            }
        });
    </script>
</body>
</html>