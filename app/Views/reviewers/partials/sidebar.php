<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="/reviewers/dashboard" class="logo logo-dark">
            <span class="logo-sm">
                <img src="/assets/ybb/ybb_white.png" alt="YBB Logo" height="60">
            </span>
            <span class="logo-lg">
                <img src="/assets/ybb/ybb_white.png" alt="YBB Logo" height="60">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="/reviewers/dashboard" class="logo logo-light">
            <span class="logo-sm">
                <img src="/assets/ybb/ybb_white.png" alt="YBB Logo" height="60">
            </span>
            <span class="logo-lg">
                <img src="/assets/ybb/ybb_white.png" alt="YBB Logo" height="60">
            </span>
        </a>
        <!-- Mobile Menu Toggle Button -->
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= url_is('reviewers/dashboard*') ? 'active' : '' ?>" href="<?= base_url("reviewers/dashboard") ?>">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>

                <li class="menu-title"><i class="ri-file-text-line"></i> <span data-key="t-reviews">Reviews</span></li>

                <!-- Abstracts & Papers -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= url_is('reviewers/abstracts-papers*') ? 'active' : '' ?>" href="<?= base_url("reviewers/abstracts-papers") ?>">
                        <i class="ri-file-text-line"></i> <span>Abstracts & Papers</span>
                    </a>
                </li>

                <li class="menu-title"><i class="ri-user-line"></i> <span data-key="t-profile">Profile</span></li>

                <!-- My Info -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= url_is('reviewers/my-info*') ? 'active' : '' ?>" href="<?= base_url("reviewers/my-info") ?>">
                        <i class="ri-user-settings-line"></i> <span>My Info</span>
                    </a>
                </li>

                <li class="menu-title"><i class="ri-logout-circle-line"></i> <span data-key="t-account">Account</span></li>

                <!-- Sign Out -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="<?= base_url("reviewers/sign-out") ?>" onclick="return confirm('Are you sure you want to sign out?')">
                        <i class="ri-logout-circle-line"></i> <span>Sign Out</span>
                    </a>
                </li>

            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
