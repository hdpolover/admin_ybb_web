<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="/reviewers/dashboard" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="/assets/ybb/ybb_white.png" alt="YBB Logo" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="/assets/ybb/ybb_white.png" alt="YBB Logo" height="17">
                        </span>
                    </a>

                    <a href="/reviewers/dashboard" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="/assets/ybb/ybb_white.png" alt="YBB Logo" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="/assets/ybb/ybb_white.png" alt="YBB Logo" height="17">
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

                <div class="dropdown ms-sm-3 header-item topbar-user">                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <div class="rounded-circle header-profile-user bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                <?= strtoupper(substr($currentUser->name, 0, 1)) ?>
                            </div>
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text"><?= $currentUser->name ?></span>
                                <span class="d-none d-xl-block ms-1 fs-12 text-muted user-name-sub-text">Reviewer</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">Welcome <?= $currentUser->name ?>!</h6>
                        <a class="dropdown-item" href="<?= base_url('reviewers/my-info') ?>"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('reviewers/sign-out') ?>" onclick="return confirm('Are you sure you want to sign out?')"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
