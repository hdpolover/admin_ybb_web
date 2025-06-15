<?= $this->extend('layouts/reviewer') ?>

<?= $this->section('content') ?>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ri-check-line me-1"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ri-error-warning-line me-1"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-xxl-3">
        <div class="card">
            <div class="card-body p-4">                <div class="text-center">
                    <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                        <div class="rounded-circle avatar-xl bg-primary d-flex align-items-center justify-content-center text-white" style="width: 96px; height: 96px; font-size: 36px; font-weight: bold;">
                            <?= strtoupper(substr($reviewer->name, 0, 1)) ?>
                        </div>
                    </div>
                    <h5 class="fs-16 mb-1"><?= esc($reviewer->name) ?></h5>
                    <p class="text-muted mb-0">Reviewer</p>
                </div>
            </div>
        </div>
        <!--end card-->
        
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0">Statistics</h5>
                    </div>
                </div>
                
                <div class="mb-3 d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-3">
                        <span class="avatar-title bg-warning-subtle rounded fs-12">
                            <i class="ri-file-list-line text-warning"></i>
                        </span>
                    </div>                    <div class="flex-grow-1">
                        <h6 class="mb-0">Total Reviews</h6>
                        <p class="text-muted mb-0"><?= isset($stats['total_assigned']) ? $stats['total_assigned'] : '0' ?></p>
                    </div>
                </div>
                
                <div class="mb-3 d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-3">
                        <span class="avatar-title bg-success-subtle rounded fs-12">
                            <i class="ri-check-line text-success"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">Completed</h6>
                        <p class="text-muted mb-0"><?= isset($stats['total_completed']) ? $stats['total_completed'] : '0' ?></p>
                    </div>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-3">
                        <span class="avatar-title bg-info-subtle rounded fs-12">
                            <i class="ri-percent-line text-info"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">Completion Rate</h6>
                        <p class="text-muted mb-0"><?= isset($stats['completion_rate']) ? $stats['completion_rate'] . '%' : '0%' ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!--end card-->
    </div>
    <!--end col-->
    
    <div class="col-xxl-9">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab">
                            <i class="fas fa-home"></i>
                            Personal Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">
                            <i class="far fa-user"></i>
                            Change Password
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content">
                    <div class="tab-pane active" id="personalDetails" role="tabpanel">
                        <form action="/reviewers/my-info/update" method="POST">
                            <?= csrf_field() ?>
                            
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?= esc($reviewer->name) ?>" required>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= esc($reviewer->email) ?>" required>
                                    </div>
                                </div>
                                <!--end col-->                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="institution" class="form-label">Institution</label>
                                        <textarea class="form-control" id="institution" name="institution" rows="3" placeholder="Your institution or organization..."><?= esc($reviewer->institution ?? '') ?></textarea>
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
                        <form action="/reviewers/my-info/change-password" method="POST">
                            <?= csrf_field() ?>
                            
                            <div class="row g-2">
                                <div class="col-lg-4">
                                    <div>
                                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-4">
                                    <div>
                                        <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-4">
                                    <div>
                                        <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <div class="alert alert-info">
                                            <strong>Password Requirements:</strong>
                                            <ul class="mb-0 mt-2">
                                                <li>Minimum 6 characters</li>
                                                <li>Use a strong, unique password</li>
                                                <li>Don't share your password with anyone</li>
                                            </ul>
                                        </div>
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
                    </div>
                    <!--end tab-pane-->
                </div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>
<!--end row-->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password confirmation validation
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (newPasswordInput.value !== confirmPasswordInput.value) {
            confirmPasswordInput.setCustomValidity('Passwords do not match');
        } else {
            confirmPasswordInput.setCustomValidity('');
        }
    }
    
    newPasswordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validatePassword);
});
</script>
<?= $this->endSection() ?>
