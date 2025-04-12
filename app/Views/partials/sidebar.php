<!-- ========== App Menu ========== -->
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
                    <a class="nav-link menu-link" href="<?= base_url("dashboard") ?>">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-management">Management</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="<?= base_url("payments") ?>">
                        <i class="ri-money-dollar-circle-line"></i> <span>Payments</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarUsers" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarUsers">
                        <i class="ri-user-3-line"></i> <span>Users</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarUsers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?= base_url("users/participants") ?>" class="nav-link"> <i class="ri-user-follow-line"></i> Participants </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("users/ambassadors") ?>" class="nav-link"> <i class="ri-shield-user-line"></i> Ambassadors </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarSubmissions" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarSubmissions">
                        <i class="ri-file-list-3-line"></i> <span>Submissions</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarSubmissions">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?= base_url("submissions/essays") ?>" class="nav-link"> <i class="ri-draft-line"></i> Essays </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("submissions/agreements") ?>" class="nav-link"> <i class="ri-file-text-line"></i> Agreement Letters </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarDocuments" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarDocuments">
                        <i class="ri-folder-2-line"></i> <span>Documents</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarDocuments">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?= base_url("documents/acceptance-letters") ?>" class="nav-link"> <i class="ri-mail-check-line"></i> Letter of Acceptances </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("documents/certificates") ?>" class="nav-link"> <i class="ri-award-line"></i> Certificates </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-configuration">Configuration</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarProgramData" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarProgramData">
                        <i class="ri-settings-3-line"></i> <span>Program Master Data</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarProgramData">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?= base_url("program/details") ?>" class="nav-link"> <i class="ri-information-line"></i> Program Details </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("program/submission-form") ?>" class="nav-link"> <i class="ri-file-list-line"></i> Submission Form </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("program/documents") ?>" class="nav-link"> <i class="ri-file-paper-2-line"></i> Program Documents </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("program/payments") ?>" class="nav-link"> <i class="ri-secure-payment-line"></i> Program Payments </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("program/payment-methods") ?>" class="nav-link"> <i class="ri-bank-card-line"></i> Payment Methods </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("program/timelines") ?>" class="nav-link"> <i class="ri-calendar-todo-line"></i> Timelines </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("program/faqs") ?>" class="nav-link"> <i class="ri-question-answer-line"></i> FAQs </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarSettings" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarSettings">
                        <i class="ri-tools-fill"></i> <span>Settings</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarSettings">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?= base_url("settings/landing-page") ?>" class="nav-link"> <i class="ri-layout-line"></i> Landing Page </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url("settings/payment-methods") ?>" class="nav-link"> <i class="ri-bank-card-2-line"></i> Program Payment Methods </a>
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
