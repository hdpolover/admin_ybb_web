<!-- ========== App Menu ========== -->
<?php
// Using CodeIgniter's built-in url_is() function for menu active states
// No need for custom helper functions anymore
?>
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="/" class="logo logo-dark">
            <span class="logo-sm">
                <img src="/assets/images/logo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="/assets/images/logo-dark.png" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="/" class="logo logo-light">
            <span class="logo-sm">
                <img src="/assets/images/logo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="/assets/images/logo-light.png" alt="" height="17">
            </span>
        </a>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link menu-link <?= url_is('dashboard*') ? 'active' : '' ?>" href="<?= base_url("dashboard") ?>">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>

                <li class="menu-title"><i class="ri-money-dollar-circle-line"></i> <span data-key="t-financial">Financial</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= url_is('payments*') ? 'active' : '' ?>" href="<?= base_url("payments") ?>">
                        <i class="ri-bank-card-line"></i> <span>Payments</span>
                    </a>
                </li>

                <li class="menu-title"><i class="ri-user-line"></i> <span data-key="t-user-management">User Management</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= url_is('users*') ? 'active' : '' ?>" href="#sidebarUsers" data-bs-toggle="collapse" role="button"
                        aria-expanded="<?= url_is('users*') ? 'true' : 'false' ?>" aria-controls="sidebarUsers">
                        <i class="ri-user-3-line"></i> <span>Users</span>
                    </a>
                    <div class="collapse menu-dropdown <?= url_is('users*') ? 'show' : '' ?>" id="sidebarUsers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?= base_url("users/participants") ?>" class="nav-link <?= url_is('users/participants*') ? 'active' : '' ?>"> <i class="ri-user-follow-line"></i> Participants </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("users/ambassadors") ?>" class="nav-link <?= url_is('users/ambassadors*') ? 'active' : '' ?>"> <i class="ri-shield-user-line"></i> Ambassadors </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="menu-title"><i class="ri-file-paper-line"></i> <span data-key="t-program-content">Program Content</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= url_is('submissions*') ? 'active' : '' ?>" href="#sidebarSubmissions" data-bs-toggle="collapse" role="button"
                        aria-expanded="<?= url_is('submissions*') ? 'true' : 'false' ?>" aria-controls="sidebarSubmissions">
                        <i class="ri-file-list-3-line"></i> <span>Submissions</span>
                    </a>
                    <div class="collapse menu-dropdown <?= url_is('submissions*') ? 'show' : '' ?>" id="sidebarSubmissions">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?= base_url("submissions/essays") ?>" class="nav-link <?= url_is('submissions/essays*') ? 'active' : '' ?>"> <i class="ri-draft-line"></i> Essays </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("submissions/agreements") ?>" class="nav-link <?= url_is('submissions/agreements*') ? 'active' : '' ?>"> <i class="ri-file-text-line"></i> Agreement Letters </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= url_is('documents*') ? 'active' : '' ?>" href="#sidebarDocuments" data-bs-toggle="collapse" role="button"
                        aria-expanded="<?= url_is('documents*') ? 'true' : 'false' ?>" aria-controls="sidebarDocuments">
                        <i class="ri-folder-2-line"></i> <span>Documents</span>
                    </a>
                    <div class="collapse menu-dropdown <?= url_is('documents*') ? 'show' : '' ?>" id="sidebarDocuments">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?= base_url("documents/program-documents") ?>" class="nav-link <?= url_is('documents/program-documents*') ? 'active' : '' ?>"> <i class="ri-file-paper-2-line"></i> Program Documents </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("documents/certificates") ?>" class="nav-link <?= url_is('documents/certificates*') ? 'active' : '' ?>"> <i class="ri-award-line"></i> Certificates </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= url_is('announcements*') ? 'active' : '' ?>" href="<?= base_url("announcements") ?>">
                        <i class="ri-megaphone-line"></i> <span>Announcements</span>
                    </a>
                </li>



                <li class="menu-title"><i class="ri-settings-line"></i> <span data-key="t-configuration">Configuration</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= url_is('master-data*') ? 'active' : '' ?>" href="#sidebarProgramData" data-bs-toggle="collapse" role="button"
                        aria-expanded="<?= url_is('master-data*') ? 'true' : 'false' ?>" aria-controls="sidebarProgramData">
                        <i class="ri-database-2-line"></i> <span>Master Data</span>
                    </a>
                    <div class="collapse menu-dropdown <?= url_is('master-data*') ? 'show' : '' ?>" id="sidebarProgramData">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?= base_url("master-data/program-details") ?>" class="nav-link <?= url_is('master-data/program-details*') ? 'active' : '' ?>"> <i class="ri-information-line"></i> Program Details </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("master-data/submission-form") ?>" class="nav-link <?= url_is('master-data/submission-form*') ? 'active' : '' ?>"> <i class="ri-file-list-line"></i> Submission Form </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("master-data/payments") ?>" class="nav-link <?= url_is('master-data/payments*') ? 'active' : '' ?>"> <i class="ri-secure-payment-line"></i> Program Payments </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("master-data/payment-methods") ?>" class="nav-link <?= url_is('master-data/payment-methods*') ? 'active' : '' ?>"> <i class="ri-bank-card-line"></i> Payment Methods </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("master-data/timelines") ?>" class="nav-link <?= url_is('master-data/timelines*') ? 'active' : '' ?>"> <i class="ri-calendar-todo-line"></i> Timelines </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("master-data/faqs") ?>" class="nav-link <?= url_is('master-data/faqs*') ? 'active' : '' ?>"> <i class="ri-question-answer-line"></i> FAQs </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= url_is('settings*') ? 'active' : '' ?>" href="#sidebarSettings" data-bs-toggle="collapse" role="button"
                        aria-expanded="<?= url_is('settings*') ? 'true' : 'false' ?>" aria-controls="sidebarSettings">
                        <i class="ri-tools-fill"></i> <span>Settings</span>
                    </a>
                    <div class="collapse menu-dropdown <?= url_is('settings*') ? 'show' : '' ?>" id="sidebarSettings">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?= base_url("settings/landing-page") ?>" class="nav-link <?= url_is('settings/landing-page*') ? 'active' : '' ?>"> <i class="ri-layout-line"></i> Landing Page </a>
                            </li>
                        </ul>
                    </div>
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