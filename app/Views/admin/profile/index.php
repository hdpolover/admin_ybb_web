<?= $this->include('partials/main') ?>

<head>
    <?= $this->include('partials/title-meta', ['title' => $title]) ?>
    <?= $this->include('partials/head-css') ?>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">My Profile</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                                        <li class="breadcrumb-item active">My Profile</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body p-4">
                                    <div class="text-center">
                                        <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                                            <?php 
                                            $userInitials = '';
                                            if ($user->name) {
                                                $nameParts = explode(' ', $user->name);
                                                $userInitials = strtoupper(substr($nameParts[0], 0, 1));
                                                if (count($nameParts) > 1) {
                                                    $userInitials .= strtoupper(substr($nameParts[1], 0, 1));
                                                }
                                            }
                                            ?>
                                            <!-- Static Profile Image Placeholder -->
                                            <div class="rounded-circle avatar-xl img-thumbnail d-flex align-items-center justify-content-center bg-primary text-white fw-bold user-profile-image" 
                                                 style="font-size: 2rem; width: 100px; height: 100px;">
                                                <?= $userInitials ?: 'AD' ?>
                                            </div>
                                        </div>
                                        <h5 class="fs-16 mb-1"><?= esc($user->name) ?></h5>
                                        <p class="text-muted mb-0"><?= esc(ucwords(str_replace('_', ' ', $user->role))) ?></p>
                                    </div>
                                </div>
                            </div>
                            <!--end card-->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-0">Account Information</h5>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Email:</label>
                                        <p class="text-muted mb-0"><?= esc($user->email) ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Role:</label>
                                        <p class="text-muted mb-0"><?= esc(ucwords(str_replace('_', ' ', $user->role))) ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Joined:</label>
                                        <p class="text-muted mb-0"><?= date('F j, Y', strtotime($user->created_at)) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end col-->
                        <div class="col-xl-9">
                            <div class="card">
                                <div class="card-header">
                                    <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab">
                                                <i class="fas fa-home"></i> Personal Details
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">
                                                <i class="far fa-user"></i> Change Password
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body p-4">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="personalDetails" role="tabpanel">
                                            <form id="profileForm">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="fullName" class="form-label">Full Name</label>
                                                            <input type="text" class="form-control" id="fullName" name="name" placeholder="Enter your fullname" value="<?= esc($user->name) ?>" required>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="emailInput" class="form-label">Email Address</label>
                                                            <input type="email" class="form-control" id="emailInput" name="email" placeholder="Enter your email" value="<?= esc($user->email) ?>" required>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="roleDisplay" class="form-label">Role <span class="text-muted">(Not editable)</span></label>
                                                            <div class="form-control-plaintext bg-light rounded p-2">
                                                                <?= esc(ucwords(str_replace('_', ' ', $user->role))) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="joinDateDisplay" class="form-label">Joined Date <span class="text-muted">(Not editable)</span></label>
                                                            <div class="form-control-plaintext bg-light rounded p-2">
                                                                <?= date('F j, Y', strtotime($user->created_at)) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-12">
                                                        <div class="hstack gap-2 justify-content-end">
                                                            <button type="submit" class="btn btn-primary">Update Profile</button>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                </div>
                                                <!--end row-->
                                            </form>
                                        </div>
                                        <!--end tab-pane-->
                                        <div class="tab-pane" id="changePassword" role="tabpanel">
                                            <form id="changePasswordForm">
                                                <div class="row g-2">
                                                    <div class="col-lg-4">
                                                        <div>
                                                            <label for="currentPassword" class="form-label">Current Password*</label>
                                                            <div class="position-relative auth-pass-inputgroup">
                                                                <input type="password" class="form-control pe-5 password-input" name="current_password" placeholder="Enter current password" id="currentPassword" required>
                                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-4">
                                                        <div>
                                                            <label for="newPassword" class="form-label">New Password*</label>
                                                            <div class="position-relative auth-pass-inputgroup">
                                                                <input type="password" class="form-control pe-5 password-input" name="new_password" placeholder="Enter new password" id="newPassword" required>
                                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon2"><i class="ri-eye-fill align-middle"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-4">
                                                        <div>
                                                            <label for="confirmPassword" class="form-label">Confirm Password*</label>
                                                            <div class="position-relative auth-pass-inputgroup">
                                                                <input type="password" class="form-control pe-5 password-input" name="confirm_password" placeholder="Confirm password" id="confirmPassword" required>
                                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon3"><i class="ri-eye-fill align-middle"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-12">
                                                        <div class="mb-3">
                                                            <a href="javascript:void(0);" class="link-primary text-decoration-underline">Forgot Password ?</a>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-12">
                                                        <div class="text-end">
                                                            <button type="submit" class="btn btn-success">Change Password</button>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                </div>
                                                <!--end row-->
                                            </form>
                                            <div class="mt-4 mb-3 border-bottom pb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h5 class="card-title">Password Requirements:</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex">
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="ri-checkbox-circle-line text-success fs-15 me-1"></i>At least 8 characters</li>
                                                    <li><i class="ri-checkbox-circle-line text-success fs-15 me-1"></i>At least one number (0-9)</li>
                                                </ul>
                                                <ul class="list-unstyled mb-0 ms-5">
                                                    <li><i class="ri-checkbox-circle-line text-success fs-15 me-1"></i>At least one letter (a-z)</li>
                                                    <li><i class="ri-checkbox-circle-line text-success fs-15 me-1"></i>At least one special character</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!--end tab-pane-->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <?= $this->include('partials/vendor-scripts') ?>
    
    <script>
        $(document).ready(function() {
            // Profile form submission
            $('#profileForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                $.ajax({
                    url: '<?= base_url('profile/update') ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            let errorText = response.message;
                            if (response.errors) {
                                errorText = Object.values(response.errors).join(', ');
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorText
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while updating profile.'
                        });
                    }
                });
            });

            // Change password form submission
            $('#changePasswordForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                $.ajax({
                    url: '<?= base_url('profile/change-password') ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            $('#changePasswordForm')[0].reset();
                        } else {
                            let errorText = response.message;
                            if (response.errors) {
                                errorText = Object.values(response.errors).join(', ');
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorText
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while changing password.'
                        });
                    }
                });
            });

            // Password visibility toggle
            $('.password-addon').on('click', function() {
                const input = $(this).siblings('.password-input');
                const icon = $(this).find('i');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('ri-eye-fill').addClass('ri-eye-off-fill');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('ri-eye-off-fill').addClass('ri-eye-fill');
                }
            });
        });
    </script>

</body>

</html>