<header id="page-topbar">
    <?php 
    // Get data from session that was loaded by BaseController
    $topbarData = session('topbar_data');
    $selectedProgram = $topbarData['selectedProgram'] ?? null;
    $activePrograms = $topbarData['activePrograms'] ?? [];
    $inactivePrograms = $topbarData['inactivePrograms'] ?? [];
    ?>
    
    <!-- Custom styles for improved topbar appearance -->
    <style>
        #page-topbar {
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        .program-selector-btn {
            border-radius: 8px;
            padding: 8px 12px;
            transition: all 0.2s;
            background-color: #f8f9fa;
            border: 1px solid #f0f0f0;
        }
        .program-selector-btn:hover {
            background-color: #f0f0f0;
            transform: translateY(-1px);
        }
        .program-logo-wrapper img, .program-icon-wrapper i {
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .dropdown-item {
            border-radius: 6px;
            margin: 2px 6px;
            padding: 8px 12px;
            transition: background-color 0.2s;
        }        .dropdown-programs-container {
            padding: 4px;
            overflow-x: hidden;
            width: 100%;
        }
        .dropdown-item.active {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
        }
        .topbar-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); }
            70% { transform: scale(1); }
            100% { transform: scale(0.95); }
        }
        .navbar-header {
            padding: 0 12px;
        }
        .btn-topbar {
            border-radius: 8px;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .btn-topbar:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            transform: translateY(-1px);
        }
        .header-profile-user {
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .profile-image-container {
            cursor: pointer;
        }
        .profile-image-container:hover .profile-upload-indicator {
            background-color: rgba(var(--bs-primary-rgb), 0.9) !important;
            transform: scale(1.1);
            transition: all 0.2s ease;
        }
        .profile-upload-overlay {
            transition: opacity 0.3s ease;
        }
        .profile-image-container .rounded-circle {
            transition: all 0.3s ease;
        }
        .profile-image-container:hover .rounded-circle {
            transform: scale(1.05);
        }
        .alert {
            animation: slideInRight 0.3s ease-out;
        }
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
    
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="/" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="/assets/images/logo-sm.png" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="/assets/images/logo-dark.png" alt="" height="17">
                        </span>
                    </a>

                    <a href="/" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="/assets/images/logo-sm.png" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="/assets/images/logo-light.png" alt="" height="17">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>
            <div class="d-flex align-items-center">
                <div class="d-flex align-items-center">
                    <!-- Clear Cache Button -->
                    <div class="ms-1 header-item d-none d-sm-flex">
                        <a href="<?= site_url('cache/clear/all') ?>" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Clear System Cache" onclick="return confirm('Are you sure you want to clear all system cache? This might affect performance temporarily.')">
                            <i class='ri-refresh-line fs-22'></i>
                        </a>
                    </div>

                    <!-- Program Selector Dropdown - Always visible -->
                    <div class="dropdown ms-1 topbar-head-dropdown header-item">
                        <button type="button" class="btn program-selector-btn" id="program-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <?php if (!empty($selectedProgram) && !empty($selectedProgram->logo_url)): ?>
                                    <img src="<?= esc($selectedProgram->logo_url) ?>" alt="Program Logo" class="rounded-circle header-profile-user me-2" style="height: 40px; width: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="program-icon-wrapper me-2 bg-light rounded-circle d-flex align-items-center justify-content-center" style="height: 40px; width: 40px;">
                                        <i class="ri-building-line fs-20 text-primary"></i>
                                    </div>
                                <?php endif; ?>                                <div class="text-start">
                                    <span class="fw-medium fs-14"><?= !empty($selectedProgram) ? esc($selectedProgram->name) : 'Select Program' ?></span>
                                    <p class="text-muted mb-0 fs-12">
                                        <?= !empty($selectedProgram) 
                                            ? (date('Y-m-d') . ' <i class="ri-time-line ms-1"></i>') 
                                            : 'Click to select a program' ?>
                                    </p>
                                </div>
                                <i class="ri-arrow-down-s-line ms-2 fs-18"></i>
                            </div>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="program-dropdown" style="width: 320px;">
                            <!-- Program Header -->
                            <div class="p-3 border-bottom">
                                <h6 class="mb-0 fs-15">Select Program</h6>
                                <p class="text-muted mb-0 fs-12">Choose a program to work with</p>
                            </div>

                            <!-- List of Programs -->
                            <div class="dropdown-programs-container" style="max-height: 350px; overflow-y: auto;">
                                <!-- Active Programs -->
                                <?php if (!empty($activePrograms)): ?>
                                    <h6 class="dropdown-header border-bottom bg-light-subtle">Active Programs</h6>
                                    <?php foreach ($activePrograms as $program): ?>
                                        <a class="dropdown-item d-flex align-items-center <?= (isset($selectedProgram) && $program->id == $selectedProgram->id) ? 'active' : '' ?>"
                                            href="<?= site_url('topbar/set-program/' . $program->id) ?>">
                                            <div class="d-flex align-items-center flex-grow-1">
                                                <?php if (!empty($program->logo_url)): ?>
                                                    <div class="program-logo-wrapper me-2">
                                                        <img src="<?= esc($program->logo_url) ?>" alt="<?= esc($program->name) ?>"
                                                            class="rounded-circle" style="height: 42px; width: 42px; object-fit: cover;">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="program-icon-wrapper me-2 bg-light-subtle rounded-circle d-flex align-items-center justify-content-center" style="height: 42px; width: 42px;">
                                                        <i class="ri-building-line fs-20 text-primary"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <span class="fw-medium"><?= esc($program->name) ?></span>
                                                    <?php if (!empty($program->category_name)): ?>
                                                        <p class="text-muted mb-0 fs-11"><?= esc($program->category_name) ?></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($program->short_description)): ?>
                                                        <p class="text-muted mb-0 fs-12"><?= esc(substr($program->short_description, 0, 30)) ?><?= (strlen($program->short_description) > 30) ? '...' : '' ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php if (isset($selectedProgram) && $program->id == $selectedProgram->id): ?>
                                                <i class="ri-checkbox-circle-fill text-success ms-2 fs-17"></i>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <!-- Inactive Programs (Collapsible) -->
                                <?php if (!empty($inactivePrograms)): ?>
                                    <div class="dropdown-divider"></div>                                    <div class="inactive-programs-section">
                                        <a class="dropdown-item d-flex align-items-center justify-content-between bg-light-subtle"
                                            data-bs-toggle="collapse" href="#inactiveProgramsCollapse" role="button"
                                            aria-expanded="false" aria-controls="inactiveProgramsCollapse"
                                            onclick="event.stopPropagation();">
                                            <span class="fw-medium">Inactive Programs</span>
                                            <i class="ri-arrow-down-s-line fs-18"></i>
                                        </a>
                                        <div class="collapse" id="inactiveProgramsCollapse">
                                            <?php foreach ($inactivePrograms as $program): ?>
                                                <a class="dropdown-item d-flex align-items-center ps-4 <?= (isset($selectedProgram) && $program->id == $selectedProgram->id) ? 'active' : '' ?>"
                                                    href="<?= site_url('topbar/set-program/' . $program->id) ?>">
                                                    <div class="d-flex align-items-center flex-grow-1">
                                                        <?php if (!empty($program->logo_url)): ?>
                                                            <div class="program-logo-wrapper me-2">
                                                                <img src="<?= esc($program->logo_url) ?>" alt="<?= esc($program->name) ?>"
                                                                    class="rounded-circle" style="height: 42px; width: 42px; object-fit: cover; opacity: 0.8;">
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="program-icon-wrapper me-2 bg-light-subtle rounded-circle d-flex align-items-center justify-content-center" style="height: 42px; width: 42px;">
                                                                <i class="ri-building-line fs-20 text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <span class="fw-medium text-muted"><?= esc($program->name) ?></span>
                                                            <?php if (!empty($program->category_name)): ?>
                                                                <p class="text-muted mb-0 fs-11"><?= esc($program->category_name) ?></p>
                                                            <?php endif; ?>
                                                            <?php if (!empty($program->short_description)): ?>
                                                                <p class="text-muted mb-0 fs-12"><?= esc(substr($program->short_description, 0, 30)) ?><?= (strlen($program->short_description) > 30) ? '...' : '' ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <?php if (isset($selectedProgram) && $program->id == $selectedProgram->id): ?>
                                                        <i class="ri-checkbox-circle-fill text-success ms-2 fs-17"></i>
                                                    <?php endif; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- View All Programs -->
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-center bg-light-subtle fw-medium py-2" href="<?= site_url('welcome') ?>">
                                <i class="ri-apps-2-line me-1"></i> View All Programs
                            </a>
                        </div>
                    </div>

                    <div class="dropdown ms-sm-3 header-item topbar-user">
                        <button type="button" class="btn program-selector-btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="d-flex align-items-center">
                                <?php 
                                $currentUser = $topbarData['currentUser'] ?? null;
                                $userAvatar = $currentUser->avatar ?? null;
                                $userName = $currentUser->name ?? 'Admin User';
                                $userRole = $currentUser->role ?? 'user';
                                $displayRole = ucwords(str_replace('_', ' ', $userRole));
                                $userInitials = '';
                                if ($userName) {
                                    $nameParts = explode(' ', $userName);
                                    $userInitials = strtoupper(substr($nameParts[0], 0, 1));
                                    if (count($nameParts) > 1) {
                                        $userInitials .= strtoupper(substr($nameParts[1], 0, 1));
                                    }
                                }
                                ?>
                                <!-- Profile Image Container with Placeholder -->
                                <div class="profile-image-container position-relative">
                                    <?php if (!empty($userAvatar) && file_exists(FCPATH . ltrim($userAvatar, '/'))): ?>
                                        <img class="rounded-circle header-profile-user" src="<?= $userAvatar ?>" alt="<?= esc($userName) ?>" style="width: 42px; height: 42px; object-fit: cover;">
                                    <?php else: ?>
                                        <!-- Default Avatar Placeholder -->
                                        <div class="rounded-circle header-profile-user bg-primary text-white d-flex align-items-center justify-content-center fw-medium" 
                                             style="width: 42px; height: 42px; font-size: 16px;">
                                            <?= $userInitials ?: 'AD' ?>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Upload indicator icon -->
                                    <div class="profile-upload-indicator position-absolute bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 16px; height: 16px; bottom: -2px; right: -2px; font-size: 10px; cursor: pointer;" 
                                         title="Change Profile Picture">
                                        <i class="ri-camera-line"></i>
                                    </div>
                                </div>
                                <span class="text-start ms-xl-2">
                                    <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text"><?= esc($userName) ?></span>
                                    <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text text-muted"><?= esc($displayRole) ?></span>
                                </span>
                                <i class="ri-arrow-down-s-line d-none d-xl-inline-block ms-2 fs-16"></i>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="width: 250px">
                            <!-- item-->
                            <div class="p-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="profile-image-container position-relative me-3">
                                        <?php if (!empty($userAvatar) && file_exists(FCPATH . ltrim($userAvatar, '/'))): ?>
                                            <img class="rounded-circle" src="<?= $userAvatar ?>" alt="<?= esc($userName) ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <!-- Default Avatar Placeholder in dropdown -->
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-medium" 
                                                 style="width: 50px; height: 50px; font-size: 18px;">
                                                <?= $userInitials ?: 'AD' ?>
                                            </div>
                                        <?php endif; ?>
                                        <!-- Upload overlay on hover -->
                                        <div class="profile-upload-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 rounded-circle d-flex align-items-center justify-content-center text-white opacity-0 transition-opacity" 
                                             style="cursor: pointer; transition: opacity 0.3s ease;" 
                                             onclick="document.getElementById('profileImageInput').click()"
                                             onmouseover="this.style.opacity='1'" 
                                             onmouseout="this.style.opacity='0'">
                                            <i class="ri-camera-line fs-16"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fs-15"><?= esc($userName) ?></h6>
                                        <p class="mb-0 text-muted fs-13"><?= esc($displayRole) ?></p>
                                        <small class="text-muted"><?= esc($currentUser->email ?? '') ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="p-2">
                                <a class="dropdown-item d-flex align-items-center" href="<?= site_url('profile') ?>">
                                    <i class="ri-user-line text-muted fs-16 align-middle me-2"></i> 
                                    <span class="align-middle">My Profile</span>
                                </a>
                                <?php if ($currentUser && $currentUser->role === 'super_admin'): ?>
                                <a class="dropdown-item d-flex align-items-center" href="<?= base_url('settings/main-config') ?>">
                                    <i class="ri-settings-3-line text-muted fs-16 align-middle me-2"></i> 
                                    <span class="align-middle">System Settings</span>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="<?= base_url('settings/admin-management') ?>">
                                    <i class="ri-admin-line text-muted fs-16 align-middle me-2"></i> 
                                    <span class="align-middle">Admin Management</span>
                                </a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item d-flex align-items-center" href="<?= base_url('auth/signOut') ?>">
                                    <i class="ri-logout-box-line text-muted fs-16 align-middle me-2"></i> 
                                    <span class="align-middle">Sign Out</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</header>

<!-- Hidden File Input for Profile Image Upload -->
<input type="file" id="profileImageInput" accept="image/*" style="display: none;" onchange="handleProfileImageUpload(this)">

<script>
function handleProfileImageUpload(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file.');
            return;
        }
        
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Please select an image smaller than 2MB.');
            return;
        }
        
        // Preview the image
        const reader = new FileReader();
        reader.onload = function(e) {
            // Update all profile image containers with the new image
            const profileImages = document.querySelectorAll('.profile-image-container img');
            const profilePlaceholders = document.querySelectorAll('.profile-image-container > div:not(.profile-upload-overlay):not(.profile-upload-indicator)');
            
            profileImages.forEach(img => {
                img.src = e.target.result;
                img.style.display = 'block';
            });
            
            // Hide placeholders and show images
            profilePlaceholders.forEach(placeholder => {
                if (placeholder.classList.contains('bg-primary')) {
                    placeholder.style.display = 'none';
                    // Create or update img element
                    let img = placeholder.parentNode.querySelector('img');
                    if (!img) {
                        img = document.createElement('img');
                        img.className = 'rounded-circle';
                        img.style.width = placeholder.style.width;
                        img.style.height = placeholder.style.height;
                        img.style.objectFit = 'cover';
                        placeholder.parentNode.insertBefore(img, placeholder);
                    }
                    img.src = e.target.result;
                    img.style.display = 'block';
                }
            });
        };
        reader.readAsDataURL(file);
        
        // Here you would typically upload the file to the server
        // For now, we'll just show a success message
        setTimeout(() => {
            // Show success notification (you can replace this with your notification system)
            const notification = document.createElement('div');
            notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
            notification.innerHTML = `
                <i class="ri-check-line me-2"></i>
                Profile picture updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);
            
            // Auto-remove notification after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }, 500);
    }
}
</script>
